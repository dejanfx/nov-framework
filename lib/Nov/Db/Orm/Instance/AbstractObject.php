<?php
namespace Nov\Db\Orm\Instance;
use Nov;
abstract class AbstractObject
{
    private $_class = null;
    private $_instance = null;
    private $_triggerClass = null;
    
    function __construct($class, \Nov\Db\Orm\AbstractObject $instance, $db=null)
    {
        $this->_class    = $class;
        $this->_instance = $instance;
        $triggerClassName = $this->_class . "\Triggers";
        
        $classFile = str_replace('\\', '/', $triggerClassName).'.php';
        if (is_file(NovBASEPATH. '/'.$classFile) && class_exists($triggerClassName)) {
            $this->_triggerClass = new $triggerClassName($this);
        }
        if (!is_null($db)) {
            $this->_db = $db;
        }
    }
    
    private $_where = null;
    const MALFORMED_WHERE = 'MALFORMED_WHERE';
    const MALFORMED_WHERE_KEY = 'MALFORMED_WHERE_KEY';
    /**
     * @param array $where
     * @return \Nov\Db\Orm\Instance\AbstractObject
     */
    public function where($where=array())
    {
        if (!is_array($where)) {
            throw new Nov\Db\Orm\Exception(self::MALFORMED_WHERE);
        }
        $this->_where = $where;
        return $this;
    }
    
    private $_orderBy = null;
    /**
     * @param array|string $values
     * @return \Nov\Db\Orm\Instance\AbstractObject
     */
    public function orderBy($orderBy=array())
    {
        if (is_string($orderBy)) {
            $orderBy = array($orderBy);
        }
        $this->_orderBy = $orderBy;
        return $this;
    }
    
    private $_groupBy = null;
    /**
     * @param array|string $values
     * @return \Nov\Db\Orm\Instance\AbstractObject
     */
    public function groupBy($groupBy=array())
    {
        if (is_string($groupBy)) {
            $groupBy = array($groupBy);
        }
        $this->_groupBy = $groupBy;
        return $this;
    }
    
    const MALFORMED_VALUES = 'MALFORMED_VALUES';
    const KEY_NOT_IN_INSERT = 'KEY_NOT_IN_INSERT';
    
    private $_values = array();
    private $_type   = null;
    public function getType()
    {
        return $this->_type;
    }
    
    const _INSERT = 'INSERT';
    const _SELECT = 'SELECT';
    const _UPDATE = 'UPDATE';
    const _DELETE = 'DELETE';
    
    private $_sql = null;
    public function getSql()
    {
        return $this->_sql;
    }
    
    public function _composeSql()
    {
        switch ($this->_type) {
            case self::_INSERT:
                if ($this instanceof Nov\Db\Orm\Instance\View) {
                    throw new Nov\Db\Orm\Exception(Nov\Db\Orm\Exception::VIEW_ERROR);
                }
                $_values = array();
                foreach (array_keys($this->_values) as $key) {
                    $_values[] = ":{$key}";
                }
                $this->_sql = "INSERT INTO {$this->_getFrom()} (" . implode(', ', array_keys($this->_values)) . ") VALUES (" . implode(', ', $_values) . ")";
                break;
            case self::_SELECT:
                $this->_sql = "SELECT " . $this->_buildSelect() . " FROM " . $this->_getFrom() . $this->_getWhere() . $this->_getOrderBy() . $this->_getGroupBy();
                break;
            case self::_UPDATE:
                if ($this instanceof Nov\Db\Orm\Instance\View) {
                    throw new Nov\Db\Orm\Exception(Nov\Db\Orm\Exception::VIEW_ERROR);
                }
                $_values = array();
                foreach (array_keys($this->_values) as $key) {
                    $_values[] = "{$key} = :{$key}";
                }
                $this->_sql = "UPDATE {$this->_getFrom()} SET " . implode(', ', $_values) . "{$this->_getWhere()}";
                break;
            case self::_DELETE:
                if ($this instanceof Nov\Db\Orm\Instance\View) {
                    throw new Nov\Db\Orm\Exception(Nov\Db\Orm\Exception::VIEW_ERROR);
                }
                $this->_sql = "DELETE FROM " . $this->_getFrom() . $this->_getWhere();
                break;
        }
    }
    
    const VIEW_ALIAS = 'ormView';
    private function _getFrom()
    {
        if (!is_null($this->_instance->getSchema())) {
            $_object = $this->_instance->getSchema() . '.' . $this->_instance->getObject();
        } else {
            $_object = $this->_instance->getObject();
        }

        //if ($this instanceof Nov\Db\Orm\Instance\View) {
        //    return "( " . $_object . " ) " . self::VIEW_ALIAS;
        //} else {
        return $_object;
        //}
        
    }
    
    public function getValues()
    {
        switch ($this->_type) {
            case self::_INSERT:
                return $this->_joinValues($this->_values);
                break;
            case self::_SELECT:
                //$stmt = $this->getDb()->getStmt($this->_sql);
                return $this->_joinValues(array(), $this->_where);
                break;
            case self::_UPDATE:
                return $this->_joinValues($this->_values, $this->_where);
                break;
            case self::_DELETE:
                return $this->_joinValues(array(), $this->_where);
                break;
        }
    }
    
    private function _execute()
    {
        $sql    = $this->_sql;
        $values = $this->getValues();
        $this->getDb()->getStmt($sql)->execute($values);
    }
    
    private function _fetch($fetchType)
    {
        $out = null;
        if ($fetchType != Nov\Db::FETCH_NONE) {
            $out = $this->getDb()->getStmt($this->_sql)->fetchALL(\PDO::FETCH_CLASS, 'Nov\Db\Orm\Record');
            switch ($fetchType) {
                case Nov\Db::FETCH_ONE:
                    $row = $out[0];
                    foreach ($row as $key => $value) {
                        break;
                    }
                    $out = $value;
                    break;
                case Nov\Db::FETCH_ROW:
                    $out = $out[0];
                    break;
                default:  
            }
        }
        return $out;
    }
    
    public function getDb()
    {
        return $this->_db;    
    }
    
    public function setDb($db)
    {
        $this->_db = $db;    
    }
    
    public function exec($fetchType = Nov\Db::FETCH_ALL)
    {
        if (is_string($this->getDb())) {
            $this->setDb(Nov\Db\PDO::factory($this->getDb()));
        }
        $this->_composeSql();
        
        if ($this->_type == self::_SELECT) {
            $this->_execute();
        } else {
            if ($this instanceof Nov\Db\Orm\Instance\Table) {
                $this->_triggerPre();
                $this->_execute();
                $this->_triggerPost();
            }
        }
        return $this->_fetch($fetchType);
    }
    
    private function _triggerPre()
    {
        switch ($this->_type) {
            case self::_INSERT:
                $this->triggerPreInsert($this->_values);
                break;
            case self::_UPDATE:
                $this->triggerPreUpdate($this->_where, $this->_values);
                break;
            case self::_DELETE:
                $this->triggerPreDelete($this->_where);
                break;
        }
    }
    
    private function _triggerPost()
    {
        switch ($this->_type) {
            case self::_INSERT:
                $this->triggerPostInsert($this->_values);
                break;
            case self::_UPDATE:
                $this->triggerPostUpdate($this->_where, $this->_values);
                break;
            case self::_DELETE:
                $this->triggerPostDelete($this->_where);
                break;
        }
    }
    
    private function _buildSelect()
    {
        if (count($this->_values) == 0) {
            return '*';
        } else {
            $_values = array();
            foreach ($this->_values as $key => $value) {
                if (is_integer($key)) {
                    $_values[] = "{$value}";
                } else {
                    $_values[] = "{$value} {$key}";
                }
            }
            return implode(', ', $_values);
        }
    }
    
    /**
     * @return \Nov\Db\Orm\Instance\Interfaces\Insert
     */
    public function insert($values)
    {
        if (!is_array($values) && count($values)>0) {
            throw new Nov\Db\Orm\Exception(self::MALFORMED_VALUES);
        }
        if (!is_null($this->_where)) {
            throw new Nov\Db\Orm\Exception(self::MALFORMED_WHERE);
        }
        
        $this->_type = self::_INSERT;
        $this->_values = $values;
        return $this;
    }
    
    /**
     * @return \Nov\Db\Orm\Instance\Interfaces\Insert
     */
    public function delete()
    {
        $this->_type = self::_DELETE;
        return $this;
    }
    
    /**
     * @param array $values
     * @return \Nov\Db\Orm\Instance\Interfaces\Update
     */
    public function update($values)
    {
        if (!is_array($values) && count($values)>0) {
            throw new Nov\Db\Orm\Exception(self::MALFORMED_VALUES);
        }
        $this->_type = self::_UPDATE;
        $this->_values = $values;
        return $this;
    }
    
    /**
     * @param array $values
     * @return \Nov\Db\Orm\Instance\Interfaces\Select
     */
    public function select($values=array())
    {
        if (!is_array($values)) {
            throw new Nov\Db\Orm\Exception(self::MALFORMED_VALUES);
        }
        $this->_type = self::_SELECT;
        $this->_values = $values;
        
        return $this;
    }
    
    private function _joinValues($values, $where=array())
    {
        $out = $values;
        if (count((array) $where) > 0) {
            foreach ($where as $key => $value) {
                if ($value instanceof Nov\Db\Orm\Operators) {
                    $out["W_{$key}_{$value->getVar1()}"] = $value->getVar2();
                } else {
                    $out["W_{$key}"] = $value;
                }
            }
        }
        return $out;
    }
    
    private function _getGroupBy()
    {
        if (count((array) $this->_groupBy)>0) {
            return ' GROUP BY ' . implode(', ', _groupBy);
        } else {
            return null;
        }
    }
    
    private function _getOrderBy()
    {
        if (count((array) $this->_orderBy)>0) {
            return ' ORDER BY ' . implode(', ', _orderBy);
        } else {
            return null;
        }
    }
    
    private function _getWhere()
    {
        if (count((array) $this->_where)>0) {
            $_where = array();
            foreach ($this->_where as $key => $value) {
                if ($value instanceof Nov\Db\Orm\Operators) {
                    switch ($value->getType()) {
                        case Nov\Db\Orm\Operators::EQUAL:
                            $_where[] = "{$value->getVar1()} = :W_{$key}_{$value->getVar1()}";
                            break;
                        case Nov\Db\Orm\Operators::DISTINCT:
                            $_where[] = "{$value->getVar1()} != :W_{$key}_{$value->getVar1()}";
                            break;
                        case Nov\Db\Orm\Operators::MAYOR:
                            $operator = $value->getEqualToo() ? '>=' : '>';
                            $_where[] = "{$value->getVar1()} {$operator} :W_{$key}_{$value->getVar1()}";
                            break;
                        case Nov\Db\Orm\Operators::MINOR:
                            $operator = $value->getEqualToo() ? '<=' : '>';
                            $_where[] = "{$value->getVar1()} {$operator} :W_{$key}_{$value->getVar1()}";
                            break;
                    }
                } else {
                    $_where[] = "{$key} = :W_{$key}";
                }
            }
            return ' WHERE ' . implode(' AND ', $_where);
        } else {
            return null;
        }
    }
    
    protected function triggerPreUpdate($where, $values){
        $out = null;
        if (!is_null($this->_triggerClass) && is_callable(array($this->_triggerClass, 'triggerPreUpdate'))) {
            $out = $this->_triggerClass->triggerPreUpdate($where, $values);
        }
        return $out;
    }
    
    protected function triggerPreInsert($values){
        $out = null;
        if (!is_null($this->_triggerClass) && is_callable(array($this->_triggerClass, 'triggerPreInsert'))) {
            $out = $this->_triggerClass->triggerPreInsert($values);
        }
        return $out;
    }
    
    protected function triggerPreDelete($where){
        $out = null;
        if (!is_null($this->_triggerClass) && is_callable(array($this->_triggerClass, 'triggerPreDelete'))) {
            $out = $this->_triggerClass->triggerPreDelete($where);
        }
        return $out;
    }
    
    protected function triggerPostUpdate($where, $values){
        $out = null;
        if (!is_null($this->_triggerClass) && is_callable(array($this->_triggerClass, 'triggerPostUpdate'))) {
            $out = $this->_triggerClass->triggerPostUpdate($where, $values);
        }
        return $out;
    }
    
    protected function triggerPostInsert($values){
        $out = null;
        if (!is_null($this->_triggerClass) && is_callable(array($this->_triggerClass, 'triggerPostInsert'))) {
            $out = $this->_triggerClass->triggerPostInsert($values);
        }
        return $out;
    }
    
    protected function triggerPostDelete($where){
        $out = null;
        if (!is_null($this->_triggerClass) && is_callable(array($this->_triggerClass, 'triggerPostDelete'))) {
            $out = $this->_triggerClass->triggerPostDelete($where);
        }
        return $out;
    }
}