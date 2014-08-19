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
    const DB_CONFIG_DBNAME   = 'db_name';
    const DB_CONFIG_HOST     = 'host';
    const DB_CONFIG_USERNAME = 'user_name';
    const DB_CONFIG_PASSWORD = 'password';
    const DB_CONFIG_CHARSET  = 'charset';
    public static $_default_db_config = array(
        self::DB_CONFIG_DBNAME => '',
        self::DB_CONFIG_HOST => '',
        self::DB_CONFIG_USERNAME => '',
        self::DB_CONFIG_PASSWORD => '',
        self::DB_CONFIG_CHARSET => 'utf8'
    );
    protected $_db_config = null;
    protected $_table_config = array();
    protected $_connections = array();
    protected $_model_dir = "";
    protected $_push_config = array();
    
    function __construct($config) {
        $this->_db_config    = isset($config['db_config']) ? $config['db_config'] : null;
        $this->_table_config = isset($config['table_config']) ? $config['table_config'] : array();
        $this->_model_dir    = isset($config['model_dir']) ? $config['model_dir'] : "";
        $this->_push_config  = isset($config['push_config']) ? $config['push_config'] : array();
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
    
    public function getModelDir()
    {
        return $this->_model_dir;
    }
    public function setModelDir($model_dir)
    {
        $this->_model_dir = $model_dir;
    }
    
    public function getPushConfig()
    {
        return $this->_push_config;
    }
    public function setPushConfig($push_config)
    {
        return $this->_push_config = $push_config;
    }
}
