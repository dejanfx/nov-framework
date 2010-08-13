<?php
namespace Orm\test\tbl1;
use Nov\Db\Orm\Instance\Interfaces;

use Orm\test;
use Nov;

class Triggers extends Nov\Db\Orm\Trigger
{
    function triggerPostUpdate($where, $values)
    {
    	echo __CLASS__ . "::" . __FUNCTION__ . "\n";
    	var_export($where);
    	echo "\n";
    	var_export($values);
    	echo "\n";
        test\tbl1::factory($this->getDb())
            ->delete()
            ->where($where)
            ->exec();
    }
    
    function triggerPostInsert($values)
    {
        var_export($values);
        echo "\n";
    	echo __CLASS__ . "::" . __FUNCTION__ . "\n";
    }
    
    function triggerPostDelete($where)
    {
    	var_export($where);
    	echo "\n";
    	echo __CLASS__ . "::" . __FUNCTION__ . "\n";
    }
}