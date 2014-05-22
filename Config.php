<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
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
    
    protected $_db_config = null;
    protected $_default_db_config = array(
        self::DB_CONFIG_DBNAME => '',
        self::DB_CONFIG_HOST => '',
        self::DB_CONFIG_USERNAME => '',
        self::DB_CONFIG_PASSWORD => '',
        self::DB_CONFIG_CHARSET => 'utf-8'
    );
    protected $_table_config = array();
    protected $_connections = array();
    protected $_model_dir = "";
    
    function __construct($db_config,$table_config,$model_dir = "") {
        $this->_db_config = $db_config;
        $this->_table_config = $table_config;
        $this->_model_dir = $model_dir;
        /*
        foreach($this->_db_config as $key => $value) {
            $this->_connections[$key] = null;
        }
         */
        $this->createConnections();
    }
    
    function createConnections()
    {
        foreach($this->_db_config as $name => $config)
        {
            if(!empty(array_diff_key($this->_default_db_config,$config))) {
                throw new \InvalidArgumentException("check db_config");
            }
            try {
                $dsn = 'mysql:host='.$config[self::DB_CONFIG_HOST].';dbname='.$config[self::DB_CONFIG_DBNAME].';charset='.$config[self::DB_CONFIG_CHARSET];
                $pdo = new \PDO($dsn,$config[self::DB_CONFIG_USERNAME],$config[self::DB_CONFIG_PASSWORD],array(\PDO::ATTR_EMULATE_PREPARES => false));
            } catch(PDOException $e) {
                exit('接続失敗:'.$e->getMessage());
            }
            $this->_connections[$name] = $pdo;
        }
    }
    
    /**
     * 指定されたkeyに対応するPDO接続を確立する。
     * @param type $key
     * @return mixid
     * @throws \InvalidArgumentException
     */
    public function createConection($key)
    {
        if(isset($this->_connections[$key]) && $this->_connections[$key]) {
            return;
        }
        
        if(!array_key_exists($key,$this->_db_config)) {
            throw new \InvalidArgumentException("check db_config");
        }
        $config = $this->_db_config[$key];
        try {
            $dsn = 'mysql:host='.$config[self::DB_CONFIG_HOST].';dbname='.$config[self::DB_CONFIG_DBNAME].';';
            $pdo = new \PDO($dsn,$config[self::DB_CONFIG_USERNAME],$config[self::DB_CONFIG_PASSWORD]);
            if(isset($config[self::DB_CONFIG_CHARSET])) {
                $pdo->query('SET NAMES ' . $config[self::DB_CONFIG_CHARSET]);
            }
        } catch(PDOException $e) {
            exit('接続失敗:'.$e->getMessage());
            $pdo = null;
        }
        $this->_connections[$key] = $pdo;
    }
    
    public function getDbConfig()
    {
        return $this->_db_config;
    }
    public function getTableConfig()
    {
        return $this->_table_config;
    }
    public function getModelDir()
    {
        return $this->_model_dir;
    }
    public function getConnections()
    {
        return $this->_connections;
    }
    public function getConnection($key)
    {
        if(array_key_exists($key,$this->_connections)) {
            return $this->_connections[$key];
        }
        return null;
    
    }
}
