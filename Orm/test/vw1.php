<?php
namespace Orm\test;
use \Nov;
class vw1 extends Nov\Db\Orm\View
{
    protected $_schema = 'test';
    protected $_object = "vw1";

    const ID = "id";
    const FIELD1 = "field1";
    const FIELD3 = "field3";
    
    protected $_conf = array(
        self::ID => array(Nov\Types::STR),
        self::FIELD1 => array(Nov\Types::STR),
        self::FIELD3 => array(Nov\Types::STR),
        );
}

class vw1_Record
{
    /**
     * @param \Nov\Orm\Record $recordset
     * @return \Orm\test\vw1_Record
     */
    static function factory($recordset)
    {
        return new vw1_Record($recordset);
    }
    
    private $_recordset;
    function __construct($recordset)
    {
        $this->_isObject = $recordset instanceof Nov\Db\Orm\Record;
        $this->_recordset = $recordset;
    }
    
    function id()
    {
        return $this->_isObject ? $this->_recordset->{vw1::ID} : $this->_recordset[vw1::ID];
    }

    function field1()
    {
        return $this->_isObject ? $this->_recordset->{vw1::FIELD1} : $this->_recordset[vw1::FIELD1];
    }

    function field3()
    {
        return $this->_isObject ? $this->_recordset->{vw1::FIELD3} : $this->_recordset[vw1::FIELD3];
    }
}