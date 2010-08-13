<?php
namespace Nov\Db\Orm;
class Trigger
{
    /**
     * @var \Nov\Db\Orm\Instance
     */
    protected $_parentClass = null;
    function getDb()
    {
        return $this->_parentClass->getDb();
    }
    
    function __construct($parentClass)
    {
        $this->_parentClass = $parentClass;
    }
}