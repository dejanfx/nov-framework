<?php
namespace Nov;
date_default_timezone_set('Europe/Madrid');
class Loader
{
	const CONF_PATH = 'CONF_PATH';
    static function init($conf = array())
    {
        define('NovBASEPATH', realpath(dirname ( __FILE__ ).'/../../'));
        $autoload = function ($class) {  
            // convert namespace to full file path
            $class = str_replace('\\', '/', $class) . '.php';
            require_once($class);  
            };
        spl_autoload_register($autoload);
        if (array_key_exists(self::CONF_PATH, $conf)) {
            require_once($conf[self::CONF_PATH]);
        } else {
        	require_once(NovBASEPATH . '/Conf/NovConf.php');
        }
    }
}
