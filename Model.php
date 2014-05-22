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
class Model {
    /**
     * @var Config
     */
    protected $_config = null;
    /**
     *
     * @var \PDO
     */
    protected $_con = null;
    protected $_db_key = null;
    protected $_table_name = '';
    protected $_primary_key = 'id';
    protected $_models = null;
    
    function __construct(Config $config,Models $models)
    {
        $this->_models = $models;
        $this->_config = $config;
        if(array_key_exists($this->_table_name,$this->_config->getTableConfig())) {
            $table_config = $this->_config->getTableConfig();
            $this->_db_key = $table_config[$this->_table_name];
        } else {
            $this->_db_key = Config::DEFAULT_DB;
        }
        $this->_con = $this->_config->getConnection($this->_db_key);
    }
    
    public function getAll()
    {
        return $this->primary();
    }
    
    /**
     * primary_keyを元にレコードを取得
     * @param type $ids
     * @return type
     */
    public function primary($ids = array())
    {
        $query = 'SELECT * FROM ' . $this->_table_name;
        if(!empty($ids)) {
            $query.= ' WHERE ' . $this->_primary_key . ' IN (' . implode(',',$ids) . ')';
        }
        $stmt = $this->_con->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
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
     * Insert文発行
     * @param array $values
     * @return type
     */
    public function insert($values)
    {
        $_keys = array();
        $_quotes = array();
        $_values = array();
        foreach($values as $key => $value) {
            $_keys[] = '`'.$key.'`';
            $_quotes[] = '?';
            $_values[] = $value;
        }
        $sql = "INSERT INTO " . $this->_table_name . " (". implode($_keys,",") .") ";
        $sql.= "VALUES (".implode($_quotes,",").")";
        $stmt = $this->_con->prepare($sql);
        return $stmt->execute($_values);
    }
    
    public function updatePrimary($values,$ids = array())
    {
        $query = 'UPDATE ' . $this->_table_name . ' SET ';
        $_sets = array();
        $_values = array();
        foreach($values as $key => $value) {
            $_sets[] = $key . " = :".$key;
            $values[':'.$key] = $value;
        }
        $query.= implode(',',$_sets);
        if(!empty($ids)) {
            $query.= ' WHERE ' . $this->_primary_key . ' IN (' . implode(',',$ids) . ')'; 
        }
        $stmt = $this->_con->prepare($query);
        return $stmt->execute($values);
    }
    
    public function updatePrimaryOne($values,$id)
    {
        return $this->updatePrimary($values,array($id));
    }
    
    public function deletePrimary($ids)
    {
        $query = 'DELETE FROM ' . $this->_table_name;
        $query.= ' WHERE ' . $this->_primary_key . ' IN (' . implode(',',$ids) . ')';
        $stmt = $this->_con->prepare($query);
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
}
