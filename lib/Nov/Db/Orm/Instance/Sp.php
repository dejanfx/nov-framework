<?php
namespace Nov\Db\Orm\Instance;
class Sp
{
    private $_db    = null;
    private $_conf   = null;
    private $_schema = null;
    
    function __construct($db, $conf, $schema)
    {
        $this->_conf   = $conf;
        $this->_db     = $db;
        $this->_schema = $schema;
    }
    
    private function _execFunction($functionName, $args)
    {
        if (is_string($this->_db)) {
            $db = Nov\Db\PDO::factory($this->_db);
        } else {
            $db = $this->_db;
        }
        switch ($db->getDriver()) {
            case 'pgsql':
                if (count((array) $this->_conf[$functionName]['input']) > 0) {
                    $_params = array();
                    foreach ($this->_conf[$functionName]['input'] as $param => $type) {
                        $_params[] = ":{$param}";
                    }
                    $params = implode(', ', $_params);
                } else {
                    $params = null;
                }
                
                if (!is_null($this->_schema)) {
                    $_schema = $this->_schema . '.';
                } else {
                    $_schema = null;
                }
                $sql = "SELECT * from {$_schema}{$functionName}({$params})";
                $stmt = $db->prepare($sql);
                $i = 0;
                if (count((array) $this->_conf[$functionName]['input']) > 0) {
                    foreach ($this->_conf[$functionName]['input'] as $param => $type) {
                        $stmt->bindParam(':' . $param, $args[$i], Nov\Types::convertNov2PDO($type));
                        $i++;
                    }
                }
                $stmt->execute();
                $out = $stmt->fetchALL(PDO::FETCH_CLASS, Nov_Db_Orm_Record);
                break;
        }
        return $out;
    }
    
    protected $_function = null;
    protected $_args = null;
    protected $_fetchMode = null;
    public function exec($fetchType = null)
    {
        if (is_null($fetchType)) {
            $fetchType = $this->_defaultFetchMode;
        }
        
        $out = $this->_execFunction($this->_function, $this->_args); 
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
        return $out;
    }
}