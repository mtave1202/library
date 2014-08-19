<?php
/**
 * テーブルのShardingをするModel
 * 
 * @author Daisuke Abeyama
 */
namespace library;
class ShardingModel extends Model {
    
    protected $_sharding = 1;
    protected $_sharding_key = 'id';
    
    /**
     * primary_keyを元にレコードを取得
     * @param type $ids
     * @return type
     */
    public function primary($ids)
    {
        $re = array();
        $list = $this->getTableNames($ids);
        foreach($list as $v) {
            $ids = $v[0];
            $tableName = $v[1];
            $query = 'SELECT * FROM ' . $table_name;
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
            $re += $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $re;
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
     * テーブル名を取得する
     * @param type $id
     */
    public function getTableName($id)
    {
        return current($this->getTableNames(array($this->_sharding_key => $id)));
    }
    
    public function getTableNames($ids)
    {
        $list = array();
        foreach($ids as $id) {
            if(is_array($id)) {
                $id = $id[$this->_sharding_key];
            }
            $sId = $id % $this->_sharding;
            if(!array_key_exists($sId,$list)) {
                $list[$sId] = array();
                $list[$sId][0] = array();
                $list[$sId][1] = $this->_table_name . "_" . $sId;
            }
            $list[$sId][0][] = $id;
        }
        return $list;
    }
}
