<?php
namespace Orm\test;
use \Nov;
class tbl1 extends Nov\Db\Orm\Table
{
    protected $_schema = 'test';
    protected $_object = "tbl1";

    const ID = "id";
    const FIELD1 = "field1";
    const FIELD3 = "field3";
    
    protected $_conf = array(
        self::ID => array(Nov\Types::STR),
        self::FIELD1 => array(Nov\Types::STR),
        self::FIELD3 => array(Nov\Types::STR),
        );
}

class tbl1_Record
{
    /**
     * @param \Nov\Orm\Record $recordset
     * @return \Orm\test\tbl1_Record
     */
    static function factory($recordset)
    {
        return new tbl1_Record($recordset);
    }
    
    private $_recordset;
    function __construct($recordset)
    {
        $this->_isObject = $recordset instanceof Nov\Db\Orm\Record;
        $this->_recordset = $recordset;
    }
    
    function id()
    {
        return $this->_isObject ? $this->_recordset->{tbl1::ID} : $this->_recordset[tbl1::ID];
    }

    function field1()
    {
        return $this->_isObject ? $this->_recordset->{tbl1::FIELD1} : $this->_recordset[tbl1::FIELD1];
    }

    function field3()
    {
        return $this->_isObject ? $this->_recordset->{tbl1::FIELD3} : $this->_recordset[tbl1::FIELD3];
    }
}