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
    
    public function getSharding()
    {
        return $this->_sharding;
    }
    
    public function getShardingKey()
    {
        return $this->_sharding_key;
    }
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
            $query = 'SELECT * FROM ' . $tableName;
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
            while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                array_push($re,$row);
            }
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
                $id_ = $id[$this->_sharding_key];
            } else {
                $id_ = $id;
            }
            $sId = $id_ % $this->_sharding;
            if(!array_key_exists($sId,$list)) {
                $list[$sId] = array();
                $list[$sId][0] = array();
                $list[$sId][1] = $this->_table_name . "_" . $sId;
            }
            if(is_array($this->_primary_key)) {
                $ids = array();
                foreach($this->_primary_key as $key) {
                    $ids[$key] = $id[$key];
                }
                $list[$sId][0][] = $ids;
            } else {
                if(is_array($id)) {
                    $list[$sId][0][] = $id[$this->_primary_key];
                } else {
                    $list[$sId][0][] = $id;
                }
            }
        }
        return $list;
    }
    
    /**
     * insert文発行
     * $key = array($key1,$key2);
     * $allParams = array(
     *      $shardingKeyValue => array( 
     *          array(
     *              $key1 => $value1,
     *              $key2 => $value2,
     *          ),
     *          array(
     *              $key1 => $value3,
     *              $key2 => $value4,
     *          ),
     *      ),
     * );
     * @param array $keys insertするカラム
     * @param array $allParams insertする値
     * @return bool
     */
    public function insert($keys,$allParams)
    {
        $allValues = array();
        $i = 0;
        foreach($allParams as $shardingKeyValue => $params) {
            $binds = array();
            $strs = array();
            $count = count($keys);
            list($_,$tableName) = $this->getTableName($shardingKeyValue);
            foreach($params as $param) {
                $str = array();
                for($i=0;$i<$count;++$i) {
                    if(!isset($keys[$i])||!isset($this->_data_types[$keys[$i]])) {
                        throw new \InvalidArgumentException();
                    }
                    $column = $keys[$i];
                    $key = ':'.$column . "_" . count($strs) . "_" . $i;
                    $str[] = $key;
                    $binds[$key] = array($param[$column],$this->_data_types[$column]);
                }
                $strs[] = "(" . implode(',',$str) . ")";
            }
            $allValues[$tableName] = array(implode(',',$strs),$binds);
        }
        foreach($allValues as $tableName => $v) {
            $values = $v[0];
            $binds = $v[1];
            $sql = "INSERT INTO " . $tableName . " (". implode(",",$keys) .") ";
            $sql.= "VALUES ".$values;
            $stmt = $this->_con->prepare($sql);
            foreach($binds as $key => $bind) {
                $stmt->bindValue($key,$bind[0],$bind[1]);
            }
            $stmt->execute();
        }
        return true;
    }
    
    /**
     * 1件insert
     * $paramの内容は以下の形を想定
     * array(
     *      $key1 => $value1,
     *      $key2 => $value2,
     * );
     * @param int $shardingKeyValue
     * @param array $param
     * @return bool
     */
    public function insertOne($shardingKeyValue,$param)
    {
        return $this->insert(array_keys($param),array($shardingKeyValue => array($param)));
    }
    
    /**
     * 主キーを使った更新
     * $ids = array(
     *     $shardingKeyValue => array($id,$id...),
     *     $shardingKeyValue => array($id,$id...),
     * ),
     * @param array $values 更新カラムと値の配列
     * @param string|array $allIds 主キー
     * @return type
     */
    public function updatePrimary($values,$allIds)
    {
        $tables = array();
        $_sets = array();
        $binds = array();
        
        foreach($values as $key => $value) {
            $_sets[] = $key . " = :".$key;
            $binds[':'.$key] = array($value,$this->_data_types[$key]);
        }
        
        //同じテーブルのIDを纏める。
        foreach($allIds as $shadingKeyValue => $ids) {
            list($_,$tableName) = $this->getTableName($shadingKeyValue);
            if(!array_key_exists($tableName,$tables)) {
                $tables[$tableName] = array();
            }
            foreach($ids as $id) {
                $tables[$tableName][] = $id;
            }
        }
        foreach($tables as $tableName => $ids) {
            $query = 'UPDATE ' . $tableName . ' SET ';
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
            $stmt->execute();
        }
        return true;
    }
    
    public function updatePrimaryOne($values,$id,$shardingKeyValue=null)
    {
        if(is_null($shardingKeyValue)) {
            $shardingKeyValue = isset($id[$this->_sharding_key]) ? $id[$this->_sharding_key] : null;
        }
        if(is_null($shardingKeyValue)) {
            throw new \InvalidArgumentException();
        }
        return $this->updatePrimary($values,array($shardingKeyValue => array($id)));
    }
}
