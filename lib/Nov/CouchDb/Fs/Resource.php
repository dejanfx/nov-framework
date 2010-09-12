<?php
namespace Nov\CouchDb\Fs;

use Nov\CouchDb;

use Nov;

class Resource
{
    private $_name;
    private $_info;
    private $_raw;
    /**
     * @var \Nov\CouchDb
     */
    private $_cdb;
    
    /**
     * 
     * @param \Nov\CouchDb $cdb 
     * @param string $path
     * @param string $name
     * @param string $info
     * @return \Nov\CouchDb\Fs\Resource
     */
    public static function factory(Nov\CouchDb $cdb, $path, $name, $info)
    {
        return new self($cdb, $path, $name, $info);
    }
    
    protected function __construct(Nov\CouchDb $cdb, $path, $name, $info)
    {
        $this->_info = $info;
        $this->_name = $name;
        $this->_path = $path;
        $this->_cdb  = $cdb;
    }
    
    public function getPath()
    {
        return $this->_path;
    }

    public function raw($start=null, $bytes=null) 
    {
        $out = $this->_cdb->getAttach($this->_path, $this->_name);
        if (!is_null($start)) {
            // not tested
            return mb_substr($out, $start, $bytes);
        } else {
            return $out;
        }
        
    }

    public function getLenght()
    {
        return $this->_info->length;
    }

    public function getContentType()
    {
        return $this->_info->content_type;
    }

    /**
     * @param string $pathTo
     * @return \Nov\CouchDb\Resulset
     */
    public function copy($pathTo)
    {
        return $this->_cdb->copy($this->_path, $pathTo);
    }

    /**
     * @param string $pathTo
     * @return \Nov\CouchDb\Resulset
     */
    public function move($pathTo)
    {
        $this->_cdb->copy($this->_path, $pathTo);
        $this->_cdb->delete($this->_path);
        return $this->_cdb->select($pathTo);
    }

    /**
    * @return \Nov\CouchDb\Resulset
     */
    public function delete()
    {
        return $this->_cdb->delete($this->_path);
    }
    
    public function write($raw, $contentType=null, $stat = array())
    {
        $this->_cdb->addAttach($this->_path, basename($this->_path), $raw, $contentType);
        if (count($stat) > 0) {
        	$this->_cdb->update($this->_path, $stat);
        }
        return $this->_cdb->select($this->_path);
    }
    
}