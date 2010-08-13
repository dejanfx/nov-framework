<?php
namespace Nov\Db;
use Nov\Conf;

class PDO extends \PDO 
{
    private $stmts = array();
    
    public function getStmt($sql)
    {
        if (!isset($this->stmts[$sql])) {
            $this->stmts[$sql] = $this->prepare($sql);
        }
        return $this->stmts[$sql];
    }
    
    private $_currentTransaction = null;
    function beginTransaction()
    {
        $this->_currentTransaction = parent::beginTransaction();
        return $this->getCurrentTransaction();
    }
    
    function getCurrentTransaction()
    {
        return $this->_currentTransaction;
    }
    
    function commit()
    {
        $out = parent::commit();
        unset($this->_currentTransaction);
        return $out;
    }
    
    function rollBack()
    {
        $out = parent::rollBack();
        unset($this->_currentTransaction);
        return $out;
    }
    
    public function getNode()
    {
        return $this->node;
    }
    
    public function getDriver()
    {
        return $this->_driver;
    }
    
    private $node = null;
    private $_driver = null;
    function __construct($driver, $node, $dsn, $username=null, $password=null, $driver_options=null)
    {
        $this->node = $node;
        $this->_driver = $driver;
        parent::__construct($dsn, $username, $password, $driver_options);
        $this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
    }
    
    /**
     * factory
     *
     * @param string $key
     * @return \Nov\Db\PDO
     */
    static function factory($key)
    {
    	$conf = Conf\Db::singleton($key);
        $driver         = $conf->getDbConf($key, 'driver');
        $dsn            = $conf->getDbConf($key, 'dsn');
        $username       = $conf->getDbConf($key, 'username');
        $password       = $conf->getDbConf($key, 'password');
        $driver_options = $conf->getDbConf($key, 'driver_options');
        return new self($driver, $key, $dsn, $username, $password, $driver_options);
    }
    
    static $_instance = array();
    
    /**
     * singleton
     *
     * @param string $key
     * @return \Nov\Db\PDO
     */
    static function singleton($key)
    {
        if (!array_key_exists($key, self::$_instance)) {
            self::$_instance[$key] = self::factory($key);
        }
        return self::$_instance[$key];
    }
}
