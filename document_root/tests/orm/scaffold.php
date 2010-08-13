<?php
// http://Nov2/tests/orm/scaffold.php
error_reporting(-1);
require_once("Nov/Loader.php");
\Nov\Loader::init();

use Nov\Db\Orm;
$scaffold = new Orm\Scaffold(Nov\Db::factory(NovConf::PG1), 'test');
$scaffold->buildAll(NovBASEPATH.'/Orm');

/*
$db = Nov_Db::singleton(Conf_Db::PG1);

$data = Orm_Pg1_demo_tblerislog::factory($db)->select()->exec();
$scaffold = new Nov_Db_Orm_Scaffold($db, 'Pg1');

$scaffold->addTable('demo.tblerislog');

$scaffold->buildAll(NovBASEPATH.'Orm');
*/