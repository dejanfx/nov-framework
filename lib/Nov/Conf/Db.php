<?php
namespace Nov\Conf;

class Db
{
    const CONF_ERROR = 'CONF_ERROR';

    private static $_instance = array();
    
    /**
     * @param string $key
     * @return \Nov\Conf\Db
     */
    public static function singleton($key)
    {
    	if (!isset(self::$_instance[$key])) {
    		self::$_instance[$key] = new self($key);
    	}
    	
    	return self::$_instance[$key];
    }
    
    private $_key = null;
    protected function __construct($key) 
    {
    	$this->_key = $key;
    }
    
    public static function getDb($key=null, $value = null)
    {
        return self::_get($key, $value, static::$_dbs);
    }
    
    private static function _get($key, $value, $var)
    {
        if (is_null($key)) {
            return $var;
        } else {
            if (isset($var[$key])) {
                //@TODO check if $var[$key][$value] exits
                return is_null($value) ? $var[$key] : $var[$key][$value];
            } else {
                return null;
            }
            return isset($var[$key]) ? $var[$key] : null;
        }
    }
    
    public static function getDbConf($key, $key2=null)
    {
        if (!array_key_exists($key, \NovConf::$_dbs)) {
            throw new Nov\Exception(self::CONF_ERROR);
        }
        if (is_null($key2)) {
            return \NovConf::$_dbs[$key];
        } else {
            if (array_key_exists($key2, \NovConf::$_dbs[$key])) {
                return \NovConf::$_dbs[$key][$key2];
            } else {
                return null;
            }
        }
    }
}