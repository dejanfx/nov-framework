<?php
namespace Nov\CouchDb\Fs\Monkeypatch;

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