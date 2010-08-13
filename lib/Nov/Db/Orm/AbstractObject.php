<?php
namespace Nov\Db\Orm;
abstract class AbstractObject
{
    /**
     *
     * @return string
     */
    public function getObject()
    {
        return $this->_object;
    }
    
    /**
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->_schema;
    }
}