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
    protected $_table_name = '';
    protected $_primary_key = 'id';
    
    function __construct(Config $config)
    {
        $this->_config = $config;
        if(array_key_exists($this->_table_name,$this->_config->getTableConfig())) {
            $db_key = $this->_config->getTableConfig()[$this->_table_name];
            $this->_con = $this->_config->getConnection($db_key);
        } else {
            $this->_con = $this->_config->getConnection(Config::DEFAULT_DB);
        }
    }
    
    /**
     * primary_keyを元にレコードを取得
     * @param type $ids
     * @return type
     */
    public function primary($ids)
    {
        $query = 'SELECT * FROM ' . $this->_table_name;
        $query.= ' WHERE ' . $this->_primary_key . ' IN (' . implode(',',$ids) . ')';
        $stmt = $this->_con->prepare($query);
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
        if(empty($ret)) {
            return current($ret);
        }
        return null;
    }
    
    public function insert($keys,$values)
    {
        $query = 'INSERT INTO ' . $this->_table_name;
        $query.= " (".implode(',',$keys).") VALUES ";
        $_values = array();
        $_params = array();
        foreach($values as $value) {
            $li = array();
            for($i=0;$i<count($keys);$i++) {
                $_params[] = $value[$keys[$i]];
                $li[] = '?';
            }
            $_values[] = '(' . implode(',',$li) . ')';
        }
        $query.= implode(',',$values);
        $stmt = $this->_con->prepare($query);
        $stmt->execute($_params);
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
        $stmt = $this->_con->preapre($query);
        $stmt->execute($values);
    }
    
    public function updatePrimaryOne($values,$id)
    {
        $this->updatePrimary($values,array($id));
    }
    
    public function deletePrimary($ids)
    {
        $query = 'DELETE FROM ' . $this->_table_name;
        $query.= ' WHERE ' . $this->_primary_key . ' IN (' . implode(',',$ids) . ')';
        $stmt = $this->_con->prepare($query);
        $stmt->execute();
    }
    
    public function deletePrimaryOne($id)
    {
        return $this->deletePrimary(array($id));
    }
    
}
