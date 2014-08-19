<?php
/**
 * データ管理用ModelのBaseとなるクラス。
 * 基本的に1テーブルに対して1クラスを作成する。
 * 
 * クエリを発行する際、セキュリティ面を考え値は全てbindする。
 * 
 * @author Daisuke Abeyama
 */
namespace library;
class Model {
    /**
     * @var Config
     */
    protected $_config = null;
    /**
     * @var \PDO
     */
    protected $_con = null;
    /**
     * Config->$_db_configで指定された接続情報のキー
     * @var type 
     */
    protected $_db_key = null;
    /**
     * テーブル名
     * @var string 
     */
    protected $_table_name = '';
    /**
     * 主キー情報
     * 複合キーの場合は配列に
     * @var string|Array
     */
    protected $_primary_key = 'id'; 
    /**
     * テーブルカラムのデータタイプ。
     * PDO::PARAM_XXXで指定
     * @var array
     */
    protected $_data_types = array(
        'id' => \PDO::PARAM_INT,
    );
    
    /**
     * @var Storage
     */
    protected $_storage = null;
    
    protected $_sharding = 1;
    
    function __construct(Storage $storage)
    {
        $this->_storage = $storage;
        $this->_config = $storage->getConfig();
        if(array_key_exists($this->_table_name,$this->_config->getTableConfig())) {
            $table_config = $this->_config->getTableConfig();
            $this->_db_key = $table_config[$this->_table_name];
        } else {
            $this->_db_key = Config::DEFAULT_DB;
        }
        $this->_con = $storage->getConnection($this->_db_key);
    }
    
    function checkKey($arr)
    {
        if(!is_array($this->_primary_key)) {
            throw \Exception('呼び出しエラー');
        }
        $check = true;
        $count = count($this->_primary_key);
        for($i=0;$i<$count;++$i) {
            if(!array_key_exists($this->_primary_key[$i],$arr)) {
                $check = false;
                break;
            }
        }
        return $check;
    }
    
    /**
     * 複合主キーの場合のwhere句とbindsの生成。
     * bindsの内容は
     * array(
     *   ":{primary_key}$i" => array(値,PDO:PRAM_XXX),
     *   ":{primary_key}$i" => array(値,PDO:PRAM_XXX),
     *   ...
     * );
     * primary_key : 複合主キーの一つ。
     * $i : 連番
     * @param type $ids
     * @return type
     * @throws type
     */
    function createCompsiteKeyBinds($ids,$binds=array())
    {
        $wheres = array();
        foreach($ids as $key => $values) {
            if($this->checkKey($values)) {
                $str = array();
                $count = count($this->_primary_key);
                for($i=0;$i<$count;++$i) {
                    $pkey = $this->_primary_key[$i];
                    $bkey = ":P_".$pkey.$key;
                    $str[] = $pkey . " = " . $bkey;
                    $binds[$bkey] = array($values[$pkey],$this->_data_types[$pkey]);
                }
                $str = "(" . implode(' AND ',$str) . ")";
                $wheres[] = $str;
            } else {
                throw new \InvalidArgumentException();
            }
        }
        return array($wheres,$binds);
    }
    
    public function getAll()
    {
        $query = 'SELECT * FROM ' . $this->_table_name;
        $stmt = $this->_con->query($query);
        return $stmt->fetchAll();
    }
    
    /**
     * primary_keyを元にレコードを取得
     * @param type $ids
     * @return type
     */
    public function primary($ids)
    {
        $query = 'SELECT * FROM ' . $this->_table_name;
        if(is_array($this->_primary_key)) {
            //複合主キーの場合
            list($wheres,$binds) = $this->createCompsiteKeyBinds($ids);
            $query .= ' WHERE ' . implode(' OR ',$wheres);
            $stmt = $this->_con->prepare($query);
            foreach($binds as $key => $params) {
                $stmt->bindValue($key,$params[0],$params[1]);
            }
        } else {
            //単一主キーの場合
            $query .= ' WHERE ' . $this->_primary_key . ' IN (' . implode(',',array_fill(0,count($ids),'?')) . ')';
            $stmt = $this->_con->prepare($query);
            foreach($ids as $i => $id) {
                $stmt->bindValue($i+1,$id,$this->_data_types[$this->_primary_key]);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * primary_keyを元にレコード1件取得
     * @param type $id
     * @return null
     */
    public function primaryOne($id)
    {
        $ret = $this->primary(array($id));
        if(!empty($ret)) {
            return current($ret);
        }
        return null;
    }
    
    /**
     * insert文発行
     * $key = array($key1,$key2);
     * $values = array(
     *      array(
     *          $key1 => $value1,
     *          $key2 => $value2,
     *      ),
     *      array(
     *          $key1 => $value3,
     *          $key2 => $value4,
     *      ),
     * );
     * @param array $keys insertするカラム
     * @param array $params 実値
     * @return bool
     */
    public function insert($keys,$params)
    {
        $values = array();
        $binds = array();
        foreach($params as $param) {
            $str = array();
            $count = count($keys);
            for($i=0;$i<$count;++$i) {
                $column = $keys[$i];
                $key = ':'.$column . $i;
                $str[] = $key;
                $binds[$key] = array($param[$column],$this->_data_types[$column]);
            }
            $values[] = "(" . implode(',',$str) . ")";
        }
        $sql = "INSERT INTO " . $this->_table_name . " (". implode(",",$keys) .") ";
        $sql.= "VALUES ".implode(",",$values);
        $stmt = $this->_con->prepare($sql);
        foreach($binds as $key => $bind) {
            $stmt->bindValue($key,$bind[0],$bind[1]);
        }
        return $stmt->execute();
    }
    
    /**
     * 1件insert
     * $paramの内容は以下の形を想定
     * array(
     *      $key1 => $value1,
     *      $key2 => $value2,
     * );
     * @param array $param
     * @return bool
     */
    public function insertOne($param)
    {
        return $this->insert(array_keys($param),array($param));
    }
    
    /**
     * 主キーを使った更新
     * @param array $values 更新カラムと値の配列
     * @param string|array $ids 主キー
     * @return type
     */
    public function updatePrimary($values,$ids = array())
    {
        $query = 'UPDATE ' . $this->_table_name . ' SET ';
        $_sets = array();
        $binds = array();
        
        foreach($values as $key => $value) {
            $_sets[] = $key . " = :".$key;
            $binds[':'.$key] = array($value,$this->_data_types[$key]);
        }
        
        $query.= implode(',',$_sets);
        if(!empty($ids)) {
            if(is_array($this->_primary_key)) {
                //複合主キーの場合
                list($wheres,$binds) = $this->createCompsiteKeyBinds($ids,$binds);
                $query .= ' WHERE ' . implode(' OR ',$wheres);
            } else {
                //単一主キーの場合
                $wheres = array();
                $count = count($ids);
                for($i=0;$i<$count;++$i) {
                    $key = ':P_'.$this->_primary_key.$i;
                    $wheres[] = $this->_primary_key . " = " .$key;
                    $binds[$key] = array($ids[$i],$this->_data_types[$this->_primary_key]);
                }
                $query .= ' WHERE ' . implode(' OR ',$wheres);
            }
        }
        
        $stmt = $this->_con->prepare($query);
        foreach($binds as $key => $bind) {
            $stmt->bindValue($key,$bind[0],$bind[1]);
        }
        return $stmt->execute();
    }
    
    public function updatePrimaryOne($values,$id)
    {
        return $this->updatePrimary($values,array($id));
    }
    
    public function deletePrimary($ids)
    {
        $query = 'DELETE FROM ' . $this->_table_name;
        if(is_array($this->_primary_key)) {
            //複合主キーの場合
            list($wheres,$binds) = $this->createCompsiteKeyBinds($ids);
            $query .= ' WHERE ' . implode(' OR ',$wheres);
            $stmt = $this->_con->prepare($query);
            foreach($binds as $key => $params) {
                $stmt->bindValue($key,$params[0],$params[1]);
            }
        } else {
            //単一主キーの場合
            $query .= ' WHERE ' . $this->_primary_key . ' IN (' . implode(',',array_fill(0,count($ids),'?')) . ')';
            $stmt = $this->_con->prepare($query);
            foreach($ids as $i => $id) {
                $stmt->bindValue($i+1,$id,$this->_data_types[$this->_primary_key]);
            }
        }
        return $stmt->execute();
    }
    
    public function deletePrimaryOne($id)
    {
        return $this->deletePrimary(array($id));
    }
    
    public function lastInsertId()
    {
        return $this->_con->lastInsertId();
    }
    
    /**
     * テーブル名を取得する
     * shardingが行われている場合はsharingしたテーブル名を返す
     * @param type $id
     */
    public function getTableName($id = 0)
    {
        if($this->_sharding === 1) {
            return $this->_table_name;
        }
        $sId = $id % $this->_sharding;
        return $this->_table_name . "_" . $sId;
    }
}
