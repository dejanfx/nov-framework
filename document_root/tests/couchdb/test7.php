<?php
use Nov\CouchDb;
use Nov\CouchDb\Fs;
require_once ("Nov/Loader.php");
Nov\Loader::init();
define('FSCDB', \NovConf::CDB1);

stream_wrapper_register("novCouchDb", "Nov\CouchDb\Fs\StreamWrapper") or die("Failed to register protocol");
$file = "novCouchDb://fs/home/gonzalo/new.txt";
$file2 = "novCouchDb://fs/home/gonzalo/new2.txt";
$f = fopen($file, 'w+');
fwrite($f, "***12345dkkydd678901");
fclose($f);
move_uploaded_file($file, $file2);

if (is_file($file)) {
    echo "1";
}

$f = fopen($file, 'r');

$a = fread($f, filesize($file));
fclose($f);

unlink($file);

echo $a;
