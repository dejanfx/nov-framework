<?php
namespace Nov\CouchDb;
use Nov;
class Fs
{
    private static $_db = 'fs';
    /**
     * 
     * @param string $cdbId
     * @param string $db 
     * @return \Nov\CouchDb\Fs
     */
    
    static function factory($cdbId, $db=null)
    {
        $db = is_null($db) ? self::$_db : $db;
        return new self(Nov\CouchDb::factory($cdbId)->db($db)); 
    }
    
    /**
     * @return \Nov\CouchDb
     */
    public function getCdb()
    {
        return $this->_cdb;
    }
    /**
     * @var $_cdb \Nov\CouchDb
     */
    private $_cdb = null;
    protected function __construct(Nov\CouchDb $cdb)
    {
        $this->_cdb = $cdb;
    }
    
    /**
     * @param string $path
     * @param boolean $create 
     * @param boolean $silentMode
     * @return \Nov\CouchDb\Fs\Resource
     */
    public function open($path, $create=false, $silentMode=false)
    {
        if ($create) {
            try {
                $this->_cdb->insert($path, array('path' => dirname($path)));
            } catch (\Exception $e) {
                if ($silentMode === false) {
                    throw new Fs\Exception\FileExists('File Exits');
                }
            }        
        }
        try {
            $out = $this->_cdb->select($path)->asObject();
        } catch (\Exception $e) {
            throw new Fs\Exception\FileNotFound('FileNotFound');
        }
        if (!isset($out->_attachments)) {
            return Fs\Resource::factory($this->_cdb, $path, null, null);
        } else {
            $attachs = (array) $out->_attachments;
            if (count($attachs) == 0) {
                throw new Fs\Exception('Not found', Fs\Exception\FileNotFound);
            } elseif (count($attachs) > 1) {
                throw new Fs\Exception('System Error', Fs\Exception::SYSTEM_ERROR);
            } else {
                foreach ($attachs as $name => $info) {}
                return Fs\Resource::factory($this->_cdb, $path, $name, $info);
            }
        }
    }
    
    public function delete($path) 
    {
        $this->open($path)->delete();
    }
    
    public function copy($path, $pathTo)
    {
        $this->open($path)->copy($pathTo);
    }
    
    public function move($path, $pathTo)
    {
        $this->open($path)->copy($pathTo);
        return $this->delete($path);
    }
}