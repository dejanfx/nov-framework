<?php
namespace Nov;
date_default_timezone_set('Europe/Madrid');
class Loader
{
    static function init()
    {
        define('NovBASEPATH', realpath(dirname ( __FILE__ ).'/../../'));
        $autoload = function ($class) {  
            // convert namespace to full file path
            $class = str_replace('\\', '/', $class) . '.php';
            require_once($class);  
            };
        spl_autoload_register($autoload);
        require_once(NovBASEPATH . '/Conf/NovConf.php');
    }
}
