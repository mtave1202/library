<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Model
 *
 * @author Administrator
 */
namespace library;
class Models {
    const DEFAULT_MODEL_DIR = "Model";
    const DEFAULT_NAMESPACE = 'library';
    /**
     * @var Config
     */
    protected $_config = null;
    /**
     *
     * @var array
     */
    protected $_models = array();
    
    function __construct(Config $config)
    {
        $this->_config = $config;
        $this->createModels();
    }
    
    protected function createModels()
    {
        $default_dir = __DIR__ . DIRECTORY_SEPARATOR .  self::DEFAULT_MODEL_DIR;
        
        //基本となるModel継承クラス
        $dir_iterator = new \RecursiveDirectoryIterator(
                $default_dir,
                \FilesystemIterator::CURRENT_AS_FILEINFO | // current()メソッドでSplFileInfoのインスタンスを返す
                \FilesystemIterator::KEY_AS_PATHNAME | // key()メソッドでパスを返す
                \FilesystemIterator::SKIP_DOTS // .(カレントディレクトリ)および..(親ディレクトリ)をスキップ
        );
        $iterator = new \RecursiveIteratorIterator(
                $dir_iterator,
                \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $path_name => $path) {
            $file_name = $path->getFilename();
            $path_name = $path->getPathName();
            if(preg_match("/^[a-zA-Z]{1}\w*\.php$/",$file_name)) {
                include_once($path_name);
                $pathinfo = pathinfo($path_name);
                $dir_name = str_replace($default_dir,'',$pathinfo['dirname']);
                $dir_name = str_replace('/','_',$dir_name);
                if(!empty($dir_name) && $dir_name[0] === '_') {
                    $dir_name = substr($dir_name,1,strlen($dir_name)-1);
                }
                
                $variable_name  = empty($dir_name) ? '' : $dir_name . '_';
                $variable_name .= $pathinfo['filename'];
                
                $class_name = self::DEFAULT_NAMESPACE . '\\' . self::DEFAULT_MODEL_DIR . '_' . $variable_name;
                
                if(class_exists($class_name)) {
                    $this->_models[$variable_name] = new $class_name($this->_config);
                }
            }
        }
        
        //実装先毎のModel継承クラス
        $model_dir = $this->_config->getModelDir();
        if(!$model_dir || !is_dir($model_dir)) {
            return;
        }
        $dir_iterator = new \RecursiveDirectoryIterator(
                $model_dir,
                \FilesystemIterator::CURRENT_AS_FILEINFO | // current()メソッドでSplFileInfoのインスタンスを返す
                \FilesystemIterator::KEY_AS_PATHNAME | // key()メソッドでパスを返す
                \FilesystemIterator::SKIP_DOTS // .(カレントディレクトリ)および..(親ディレクトリ)をスキップ
        );
        $iterator = new \RecursiveIteratorIterator(
                $dir_iterator,
                \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $path_name => $path) {
            $file_name = $path->getFilename();
            $path_name = $path->getPathName();
            if(preg_match("/^[a-zA-Z]{1}\w*\.php$/",$file_name)) {
                include_once($path_name);
                $pathinfo = pathinfo($path_name);
                $dir_name = str_replace($model_dir,'',$pathinfo['dirname']);
                $dir_name = str_replace('/','_',$dir_name);
                if(!empty($dir_name) && $dir_name[0] === '_') {
                    $dir_name = substr($dir_name,1,strlen($dir_name)-1);
                }
                
                $variable_name  = empty($dir_name) ? '' : $dir_name . '_';
                $variable_name .= $pathinfo['filename'];
                $class_name  = self::DEFAULT_MODEL_DIR . '_' . $variable_name;
                if(class_exists($class_name)) {
                    $this->_models[$variable_name] = new $class_name($this->_config);
                }
            }
        }
    }
    
    function __get($name)
    {
        if(array_key_exists($name,$this->_models)) {
            return $this->_models[$name];
        }
        throw new Exception('アクセス権限なし');
    }
}
