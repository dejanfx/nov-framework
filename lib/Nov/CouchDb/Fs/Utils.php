<?php
namespace Nov\CouchDb\Fs;

class Utils
{
    private function flush (){
        echo(str_repeat(' ',256));
        // check that buffer is actually set before flushing
        if (ob_get_length()){            
            @ob_flush();
            @flush();
            @ob_end_flush();
        }    
        @ob_start();
    }
    public static function cdb2Fs($cdbId, $path, $db=null)
    {
        $fs = \Nov\CouchDb\Fs::factory($cdbId, $db);
        $cdb = $fs->getCdb();
        $out = $cdb->select('_all_docs')->asObject();
        if ((integer) $out->total_rows > 0) {
            foreach ($out->rows as $row) {
                $item = $cdb->select($row->id)->asObject();
                
                $raw = $fs->open($row->id)->raw();
                echo "{$row->id}\n";
                $this->flush();
                $file = $path . $row->id;
                $dir = dirname($file);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                
                $f = \fopen($file, 'wb+');
                \fwrite($f, $raw);
                \fclose($f);
            }
        }
    }
    
    public static function fs2cdb($path, $cdbId, $db=null) 
    {
        echo $path;
        $ite=new \RecursiveDirectoryIterator($path);
        $finfo = finfo_open(FILEINFO_MIME);
        $fs = \Nov\CouchDb\Fs::factory($cdbId, $db);
        foreach (new \RecursiveIteratorIterator($ite) as $filename=>$cur) {
            if (is_file($filename)) {
                echo "{$filename}<br/>";
                $relativePath = str_replace($path, DIRECTORY_SEPARATOR, $filename);
                $ct = finfo_file($finfo, $filename);
                $raw = file_get_contents($filename);
                $fs->open($relativePath, true, true)->write($raw, $ct);
                echo "{$relativePath}\n";
                $this->flush();
            }
        }
    }
}