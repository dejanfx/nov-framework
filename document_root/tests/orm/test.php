<?php
// http://Nov2/tests/orm/test.php

error_reporting(-1);
require_once("Nov/Loader.php");
Nov\Loader::init();

use Nov\Db\Orm\Instance;
use Orm\test;
echo "<pre>";
$db = Nov\Db::factory(NovConf::PG1);
$db->beginTransaction();

$values = array('id' => 'max(' . test\tbl1::ID . ')');
$id = test\tbl1::factory($db)->select($values)->exec(Nov\Db::FETCH_ONE);
$id++;
$values = array(
    test\tbl1::ID => $id, 
    test\tbl1::FIELD1 => "user_{$id}"
    );
test\tbl1::factory($db)->insert($values)->exec();

test\tbl1::factory($db)->update(array(test\tbl1::FIELD1 => 'xxx'))->where(array(test\tbl1::ID => $id))->exec();
$db->commit();


$all = test\tbl1::factory($db)->select()->exec();

foreach ($all as $reg) {
    $ar = new test\tbl1_Record($reg);
    echo $ar->id();
    echo "::";
    echo $ar->field1();
    echo "\n";
}

$all = test\vw1::factory($db)->select()->exec();
foreach ($all as $reg) {
    $ar = new test\vw1_Record($reg);
    echo $ar->id();
    echo "::";
    echo $ar->field1();
    echo "\n";
}
echo "</pre>";