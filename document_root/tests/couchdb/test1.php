<?php
echo "<pre>";
require_once("Nov/Loader.php");
\Nov\Loader::init();

use \Nov\CouchDb;

/*
$cdb = new Nov\CouchDb('localhost', 5984);
$nombre = $cdb->db('ris_users')->select('gonzalo')->asObject()->name;
$apellido = $cdb->db('ris_users')->select('gonzalo')->asObject()->surname;

echo "Hola {$nombre} {$apellido}.<p/>";

*/


//echo "Apellido = ". $gonzaloCdb->select('gonzalo')->asObject()->name . "<p/>";

$cdb =  CouchDb::factory(NovConf::CDB1)->db('ris_users');



echo "INSERT\n";
try {
    var_dump($cdb->insert('xxx', array('name' => 'xxx'))->asObject());
} catch (CouchDb\Exception\DupValOnIndex $e) {
    echo "Already created\n";
}

echo "SELECT\n";
var_dump($cdb->select('xxx')->asObject());

echo "UPDATE\n";
var_dump($cdb->update('xxx', array('name' => 'xxx1'))->asObject());

echo "SELECT\n";
var_dump($cdb->select('xxx')->asObject());

echo "DELETE\n";
var_dump($cdb->delete('xxx')->asObject());

var_dump($cdb->view('auth','email', 'gonzalo123@gmail.com')->asObject());
echo "</pre>";

