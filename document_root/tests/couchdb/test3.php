<?php
namespace Nov\CouchDb\Fs\Monkeypatch;
include ("Nov/CouchDb/Fs/Monkeypatch.php");

require_once ("Nov/Loader.php");
\Nov\Loader::init();

define('FSCDB', \NovConf::CDB1);

$file = "./a.txt";
echo "<pre>";



$f = fopen($file, 'w+');
fwrite($f, "***12345dkkydd678901");
fclose($f);


$f = fopen($file, 'r');
$a = fread($f, filesize($file));
fclose($f);


unlink($file);

echo $a;
echo "</pre>";
?>