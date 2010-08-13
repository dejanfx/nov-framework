<?php
namespace Nov\Db\Orm\Scaffold;
use Nov\Db\Orm;
interface InterfaceObject
{
    static function run(Orm\Scaffold &$scaffold);
}