<?php
use Nov\CouchDb\Fs;
use Nov\CouchDb\Fs\Exception;
require_once ("Nov/Loader.php");
Nov\Loader::init();

echo "<pre>";
$fs = Fs::factory(NovConf::CDB1);
try {
	$fs->delete("/home/gonzalo/aaa.txt");
} catch (Exception\FileNotFound  $e) { // **** Fs\Exception\FileNotFound
	echo $e->getMessage() . "\n";
}
try {
    echo "1";
	$fs->open("/home/gonzalo/aaa.txt", true)
	->write("asasasasasas", "application/octet-stream");
} catch (Exception\FileNotFound $e) { // **** Fs\Exception\FileNotFound
	echo $e->getMessage() . "\n";
} catch (Exception\WriteError $e) { // **** Fs\Exception\WriteError
	echo $e->getMessage() . "\n";
} catch (Exception $e) {
	echo $e->getMessage() . "\n";	
}

$res = $fs->open("/home/gonzalo/aaa.txt");


echo $res->getLenght() . "\n";
echo $res->getContentType(). "\n";
echo "1 --- \n";
$to = uniqid();
echo var_export($res->move($to));
echo "2 --- \n";

$res = $fs->open($to);
echo $res->raw();
echo "3 --- \n";
$res->delete();
echo "</pre>";

