<?php
namespace Nov\CouchDb\Fs\Monkeypatch;

use Nov\CouchDb\Fs;

use Nov\CouchDb;

function unlink($filename)
{
    echo "111";
    $fs = CouchDb\Fs::factory(FSCDB);
    $fs->delete($filename);
}

/**
 * 
 * @param string $filename
 * @param string $type
 * @return \Nov\CouchDb\Fs\Resource
 */
function fopen($filename, $type='r')
{
    $out = null;
    $fs = CouchDb\Fs::factory(FSCDB);
    $_type = strtolower(substr($type, 0, 1));
    switch ($_type) {
        case 'r':
            $out = $fs->open($filename);
            break;
        case 'w':
            $out = $fs->open($filename, true, true);
            break;
    }
	return $out;
}
function filesize($filename)
{
    $out = null;
    $fs = CouchDb\Fs::factory(FSCDB);
    $res = $fs->open($filename);
    return $res->getLenght();
}

function fwrite(\Nov\CouchDb\Fs\Resource &$f, $data, $lenght=null)
{
    if (is_null($lenght)) {
        $out = $f->write($data);
    } else {
        $out = $f->write(mb_substr($data, 0, $lenght));
    }
    $path = $f->getPath(); 
    $fs = CouchDb\Fs::factory(FSCDB);
    $f = $fs->open($path);
}

function fclose(\Nov\CouchDb\Fs\Resource $f)
{
}

function fread(\Nov\CouchDb\Fs\Resource $f, $lenght=null)
{
    if (is_null($lenght)) {
        return $f->raw();
    } else {
        return $f->raw(0, $lenght);
    }
}