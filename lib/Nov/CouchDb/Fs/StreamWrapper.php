<?php
namespace Nov\CouchDb\Fs;

class StreamWrapper {
    var $position;
    var $varname;
    /**
     * @var \Nov\CouchDb\Fs\Resource
     */
    var $fs;

    /**
     * @param string $path
     * @return \Nov\CouchDb\Fs
     */
    private static function _getFs($path, $_url)
    {
        return \Nov\CouchDb\Fs::factory(FSCDB, $_url['host']);
    }
    
    function stream_open($path, $mode, $options, &$opened_path)
    {
        $_url = parse_url($path);
        $_path = $_url['path'];
        $fs = self::_getFs($path, $_url);
        $_type = strtolower(substr($mode, 0, 1));
        switch ($_type) {
            case 'r':
                $this->fs = $fs->open($_path);
                break;
            case 'w':
                $this->fs = $fs->open($_path, true, true);
                break;
        }
        
        return true;
    }

    function stream_read($count=null)
    {
        if (is_null($count)) {
            return $this->fs->raw();
        } else {
            return $this->fs->raw(0, $count);
        }
    }

    function stream_write($data, $lenght=null)
    {
        if (is_null($lenght)) {
            $this->fs->write($data);
        } else {
            $this->fs->write(mb_substr($data, 0, $lenght));
        }

        return strlen($data);
    }

    public function unlink($path)
    {
        $_url = parse_url($path);
        $_path = $_url['path'];
        
        $fs = self::_getFs($path, $_url)->open($_path);
        $fs->delete();
    } 

    public function url_stat($path , $flags)
    {
        $_url = parse_url($path);
        $_path = $_url['path'];
        $fs = self::_getFs($path, $_url)->open($_path);
        
        $size = $fs->getLenght();
        // tamaño en bytes
        $out[7] = $size;
        $out['size'] = $size;
        
        // número de dispositivo
        $out[0] = 0;   
        $out['dev'] = 0; 
        // número de i-nodo *
        $out[1] = 0;   
        $out['ino'] = 0; 
        // modo de protección del i-nodo
        $out[2] = 0100666;   
        $out['mode'] = 0100666;    
        // número de enlaces
        $out[3] = 0;   
        $out['nlink'] = 0;
        // ID de usuario del propietario
        $out[4] = 0;   
        $out['uid'] = 0; 
        // ID de grupo del propietario *
        $out[5] = 0;   
        $out['gid'] = 0;
        // tipo de dispositivo, si es un dispositivo i-nodo
        $out[6] = 0;   
        $out['rdev'] = 0;    
        // tamaño en bytes
        $out[7] = 0;   
        $out['size'] = 0;   
        // momento del último acceso (tiempo Unix)
        $out[8] = 0;   
        $out['atime'] = 0;  
        // momento de la última modificación (tiempo Unix)
        $out[9] = 0;   
        $out['mtime'] = 0;  
        // momento de la última modificación del i-nodo (tiempo Unix) 
        $out[10] = 0;  
        $out['ctime'] = 0;  
        // momento de la última modificación del i-nodo (tiempo Unix)
        $out[11] = 0;  
        $out['blksize'] = 0; 
        // número de bloques de 512 bytes asignados
        $out[12] = 0;  
        $out['blocks'] = 0;
        
        return $out;
    } 
    
    public function __construct()
    {
    }

    public function dir_closedir()
    {
    } 

    public function dir_opendir($path , $options)
    {
    } 

    public function dir_readdir()
    {
    } 

    public function dir_rewinddir()
    {
    } 

    public function mkdir($path , $mode , $options)
    {
    } 

    public function rename($path_from , $path_to)
    {
    } 

    public function rmdir($path , $options)
    {
    } 

    public function stream_cast($cast_as)
    {
    } 

    public function stream_close()
    {
    } 

    public function stream_eof()
    {
    } 

    public function stream_flush()
    {
    } 

    public function stream_lock($operation)
    {
    } 

    public function stream_seek($offset , $whence = SEEK_SET)
    {
    } 

    public function stream_set_option($option , $arg1 , $arg2)
    {
    } 

    public function stream_stat()
    {
    } 

    public function stream_tell()
    {
    } 
}