<?php
/*
 * 
 */

/**
 * Description of Model
 *
 * @author daisuke abeyama
 */
namespace library;
class Config {
    
    const DEFAULT_DB = 'default';
    const DB_CONFIG_USERNAME = 'user_name';
    const DB_CONFIG_PASSWORD = 'password';
    const DB_DSN = '';
    public static $_default_db_config = array(
        self::DB_CONFIG_USERNAME => '',
        self::DB_CONFIG_PASSWORD => '',
        self::DB_DSN => '',
    );
    protected $_db_config = null;
    protected $_table_config = array();
    protected $_connections = array();
    protected $_model_dirs = array();
    protected $_push_config = array();
    protected $_emoji_config = array();
    
    function __construct($config) {
        $this->_db_config    = isset($config['db_config']) ? $config['db_config'] : null;
        $this->_table_config = isset($config['table_config']) ? $config['table_config'] : array();
        if(isset($config['model_dirs'])) {
            $this->setModelDirs($config['model_dirs']);
        }
        $this->_push_config  = isset($config['push_config']) ? $config['push_config'] : array();
        $this->_emoji_config  = isset($config['emoji_config']) ? $config['emoji_config'] : array();
    }
    
    public function getDbConfig()
    {
        return $this->_db_config;
    }
    public function setDbConfig($db_config)
    {
        $this->_db_config = $db_config;
    }
    
    public function getTableConfig()
    {
        return $this->_table_config;
    }
    public function setTableConfig($table_config)
    {
        return $this->_table_config = $table_config;
    }
    
    public function getModelDirs()
    {
        return $this->_model_dirs;
    }
    public function setModelDirs($model_dirs)
    {
        $this->_model_dirs = $model_dirs;
        foreach($this->_model_dirs as $namespace => $dir) {
            if(substr($dir,-1) === DIRECTORY_SEPARATOR) {
                $this->_model_dirs[$namespace] = substr($dir,0,-1);
            }
        }
    }
    
    public function getPushConfig()
    {
        return $this->_push_config;
    }
    public function setPushConfig($push_config)
    {
        return $this->_push_config = $push_config;
    }
    
    public function getEmojiConfig()
    {
        return $this->_push_config;
    }
    public function setEmojiConfig($emoji_config)
    {
        return $this->_emoji_config = $emoji_config;
    }
}
