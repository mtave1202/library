<?php
/**
 * DataStorageを管理するクラス
 * 現状Model管理のみ
 * @author Daisuke Abeyama
 */
namespace library;
class Storage {
    /**
     * この2つの定数は使用する際に変更する。
     */
    const DEFAULT_MODEL_DIR = "Model";
    const DEFAULT_NAMESPACE = "library";
    
    /**
     * @var Config
     */
    protected $_config = null;
    
    /**
     * Model_XXXクラスを収容する配列
     * @var array
     */
    protected $_models = array();
    protected $_modelPaths = array();
    protected $_connections = array();
    protected $_transaction = false;
    
    function __construct(Config $config)
    {
        $this->_config = $config;
        $this->createConnections();
        $this->createModels();
    }
    
    public function createConnections()
    {
        $db_config = $this->_config->getDbConfig();
        foreach($db_config as $key => $config)
        {
            $this->_connections[$key] = null;
            //$this->createConnection($key);
        }
    }
    
     /**
     * 指定されたkeyに対応するPDO接続を確立する。
     * @param type $key
     * @return mixid
     * @throws \InvalidArgumentException
     */
    public function createConnection($key)
    {
        $db_config = $this->_config->getDbConfig();
        if(!isset($db_config[$key])) {
            throw new \InvalidArgumentException('指定されたkeyのDB接続情報は存在しません');
        }
        $config = $db_config[$key] + Config::$_default_db_config;
        try {
            $dsn = $config[Config::DB_DSN];
            $pdo = new \PDO($dsn,$config[Config::DB_CONFIG_USERNAME],$config[Config::DB_CONFIG_PASSWORD],array(\PDO::ATTR_EMULATE_PREPARES => false));
        } catch(PDOException $e) {
            exit('接続失敗:'.$e->getMessage());
            $pdo = null;
        }
        $this->_connections[$key] = $pdo;
    }
    
    public function getConnections()
    {
        return $this->_connections;
    }
    /**
     * PDO情報を返す
     * @param string $key 取得する接続情報のキー
     * @return \PDO
     */
    public function getConnection($key)
    {
        if(array_key_exists($key,$this->_connections)) {
            if(!$this->_connections[$key]) {
                $this->createConnection($key);
                if($this->_transaction && !$this->_connections[$key]->inTransaction()) {
                    $this->_connections[$key]->beginTransaction();
                }
            }
            return $this->_connections[$key];
        }
        return null;
    }
    
    /**
     * 用意済みのModelをincludeからの生成
     * @return type
     */
    protected function createModels()
    {
        $class_list = array();
        //基本となるModel継承クラス
        $default_dir = __DIR__ . DIRECTORY_SEPARATOR .  self::DEFAULT_MODEL_DIR;
        $this->getModelClasses($class_list, $default_dir, self::DEFAULT_NAMESPACE);
        
        //実装先毎のModel継承クラス
        $model_dirs = $this->_config->getModelDirs();
        foreach($model_dirs as $namespace => $model_dir) {
            $this->getModelClasses($class_list, $model_dir, $namespace);
        }
        foreach($class_list as $variable_name => $class_name){
            if(class_exists($class_name)) {
                $this->_modelPaths[$variable_name] = $class_name;
                //$this->_models[$variable_name] = new $class_name($this);
            } else {
                error_log('notfound:'.$class_name);
            }
        }
    }
    
    /**
     * 指定されたディレクトリの中のphpファイルを取得
     * class_listにモデル名をキーとしたクラス一覧を返す
     * @param type $class_list
     * @param type $dir
     * @param type $namespace
     */
    function getModelClasses(&$class_list,$dir,$namespace = "")
    {
        foreach ($this->getRIIterator($dir) as $path_name => $path) {
            $file_name = $path->getFilename();
            $path_name = $path->getPathName();
            if(preg_match("/^[a-zA-Z]{1}\w*\.php$/",$file_name)) {
                //存在したphpファイルをincludeする。
                //include_once($path_name);
                $pathinfo = pathinfo($path_name);
                
                $dir_name = str_replace(DIRECTORY_SEPARATOR,'_',str_replace($dir,'',$pathinfo['dirname']));
                if(!empty($dir_name) && $dir_name[0] === '_') {
                    $dir_name = substr($dir_name,1,strlen($dir_name)-1);
                }
                
                $variable_name  = empty($dir_name) ? '' : $dir_name . '_';
                $variable_name .= $pathinfo['filename'];
                $n = $namespace ? $namespace . '\\' : '';
                $class_name = $n . 'Model_' . $variable_name;
                $class_list[$variable_name] = $class_name;
            }
        }
    }
    
    /**
     * 指定されたディレクトリからRecursiveIteratorIteratorを返す
     * @param string $dir
     * @return \RecursiveIteratorIterator
     */
    function getRIIterator($dir)
    {
        $ret = array();
        if(is_dir($dir)) {
            $dir_iterator = new \RecursiveDirectoryIterator(
                    $dir,
                    \FilesystemIterator::CURRENT_AS_FILEINFO | // current()メソッドでSplFileInfoのインスタンスを返す
                    \FilesystemIterator::KEY_AS_PATHNAME | // key()メソッドでパスを返す
                    \FilesystemIterator::SKIP_DOTS // .(カレントディレクトリ)および..(親ディレクトリ)をスキップ
            );
            $ret = new \RecursiveIteratorIterator(
                    $dir_iterator,
                    \RecursiveIteratorIterator::LEAVES_ONLY // ファイル名のみ
            );
        }
        return $ret;
    }
    
    /**
     * $this->XXXの形で生成したModelを取得出来るように拡張
     * @param string $name
     * @return Model
     * @throws Exception
     */
    function __get($name)
    {
        if(array_key_exists($name,$this->_modelPaths)) {
            if(!isset($this->_models[$name])) {
                $this->_models[$name] = new $this->_modelPaths[$name]($this);
            }
            return $this->_models[$name];
        }
        throw new \Exception('アクセス権限なし');
    }
    
    public function getConfig()
    {
        return $this->_config;
    }
    
    /**
     * クエリを発行する
     * @param string $query
     * @param array $binds
     * @param string $db_key = 'default'
     * @return array 0:boolean 1:PDOStatement|error message
     */
    public function query($query,$binds,$db_key = 'default')
    {
        if(!array_key_exists($db_key,$this->_connections)) {
            throw new \InvalidArgumentException;
        }
        $con = $this->_connections[$db_key];
        try {
            $stmt = $con->prepare($query);
            foreach($binds as $key => $params) {
                $stmt->bindValue($key,$params[0],$params[1]);
            }
            $stmt->execute();
        }catch(\Exception $e) {
            return array(false,$e->getMessage());
        }
        return array(true,$stmt);
    }
    
    public function isTransaction()
    {
        return $this->_transaction === true;
    }
    
    public function beginTransaction()
    {    
        if($this->_transaction) {
            throw new \Exception('transaction is already started');
        }
        foreach($this->_connections as $key => $con)
        {
            if($con) {
                $con->beginTransaction();
            }
        }
        $this->_transaction = true;
    }
    
    public function commit()
    {
        if(!$this->_transaction) {
            throw new Exception('transaction is not started');
        }
        foreach($this->_connections as $key => $con)
        {
            if($con) {
                $con->commit();
            }
        }
        $this->_transaction = false;
    }
    
    public function rollback()
    {
        if(!$this->_transaction) {
            throw new \Exception('transaction is not started');
        }
        foreach($this->_connections as $con)
        {
            if($con) {
                $con->rollback();
            }
        }
        $this->_transaction = false;
    }
}
