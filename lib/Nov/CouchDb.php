<?php
namespace Nov;

class CouchDb
{
    private $_protocol;
    private $_host;
    private $_port;
    private $_user;
    private $_password;

    /**
     * factory
     *
     * @param string $key
     * @return \Nov\CouchDb
     */
    static function factory($key)
    {
        $conf = Conf\Db::singleton($key);
 
        if (strtolower($conf->getDbConf($key, 'driver')) == 'couchdb') {
            $host     = $conf->getDbConf($key, 'host');
            $port     = $conf->getDbConf($key, 'port');
            $protocol = $conf->getDbConf($key, 'protocol');
            $username = $conf->getDbConf($key, 'username');
            $password = $conf->getDbConf($key, 'password');
        } else {
            throw new CouchDb\Exception('driver not compatible with couchdb', CouchDb\Exception::ERROR_DRIVER_NOT_COMPATIBLE);
        }
        return new self($host, $port, $protocol, $username, $password);
    }
    
    private static $_instance = array();
    static function singleton($key)
    {
        if (!isset(self::$_instance[$key])) {
            self::$_instance[$key] = self::factory($key);
        }
        return self::$_instance[$key];
    }
    
    public function __construct($host, $port=Http::DEF_PORT , $protocol=Http::HTTP, $user = null, $password=null)
    {
        $this->_host     = $host;
        $this->_port     = $port;
        $this->_protocol = $protocol;
        $this->_user     = $user;
        $this->_password = $password;
    }

    private $_db;
    /**
     * @param string $db
     * @return CouchDb
     */
    public function db($db)
    {
        $this->_db = $db;
        return $this;
    }
    
    private function _manageExceptions(Http\Exception $e)
    {
        switch ($e->getCode()) {
            case Http\Exception::NOT_FOUND:
                throw new CouchDb\Exception\NoDataFound('No Data Found');
                break;
            case Http\Exception::CONFLICT:
                throw new CouchDb\Exception\DupValOnIndex('Dup Val On Index');
                break;
            default:
                throw new CouchDb\Exception($e->getMessage(), $e->getCode());
                break;
            
        }
    }
    /**
     * @param string $key
     * @return \Nov\CouchDb\Resulset
     */
    public function select($key)
    {
        $key = urlencode($key);
        try {
            $out = Http::connect($this->_host, $this->_port, $this->_protocol)
                ->setCredentials($this->_user, $this->_password)
                ->doGet("/{$this->_db}/{$key}");
        } catch (Http\Exception $e) {
            $this->_manageExceptions($e);
        }
        return new CouchDb\Resulset($out);
    }

    /**
     * @param string $key
     * @param array $values
     * @return \Nov\CouchDb\Resulset
     */
    public function insert($key, $values)
    {
        $key = urlencode($key);
        try {
            $out = Http::connect($this->_host, $this->_port, $this->_protocol)
                ->setCredentials($this->_user, $this->_password)
                ->setHeaders(array('Content-Type' =>  'application/json'))
                ->doPut("/{$this->_db}/{$key}", json_encode($values));
        } catch (Http\Exception $e) {
            $this->_manageExceptions($e);
        }
        return new CouchDb\Resulset($out);
    }

    /**
     * @param string $key
     * @param array $values
     * @return \Nov\CouchDb\Resulset
     */
    public function update($key, $values)
    {
        $key = urlencode($key);
        try {
            $http = Http::connect($this->_host, $this->_port, $this->_protocol)
                ->setCredentials($this->_user, $this->_password);
            $out = $http->doGet("/{$this->_db}/{$key}");
            $reg = (array) json_decode($out);

            foreach ($values as $k => $v) {
                $reg[$k] = $v;
            }
            $out = $http->setHeaders(array('Content-Type' =>  'application/json'))
                ->doPut("/{$this->_db}/{$key}", json_encode($reg));
        } catch (Http\Exception $e) {
            $this->_manageExceptions($e);
        }
        return new CouchDb\Resulset($out);
    }
    
    /**
     * @param string $key
     * @param string $file
     * @param string $data
     * @return \Nov\CouchDb\Resulset
     */
    public function updateAttach($key, $file, $data)
    {
        $key = urlencode($key);
        try {
            $http = Http::connect($this->_host, $this->_port, $this->_protocol)
                ->setCredentials($this->_user, $this->_password);
            $out = $http->doGet("/{$this->_db}/{$key}");
            $reg = json_decode($out);
            
            $contentType = $reg->_attachments->$file->content_type;
            $out = $http->setHeaders(array('Content-Type' => $contentType))
                ->doPut("/{$this->_db}/{$key}/{$file}?rev={$reg->_rev}", $data);
        } catch (Http\Exception $e) {
            $this->_manageExceptions($e);
        }
        return new CouchDb\Resulset($out);
    }

    /**
     * @param string $key
     * @return string
     */
    public function getAttach($key, $file)
    {
        $key = urlencode($key);
        $file = urlencode($file);
        $a = "/{$this->_db}/{$key}/{$file}";
        try {
            $out = Http::connect($this->_host, $this->_port, $this->_protocol)
                ->setCredentials($this->_user, $this->_password)
                ->doGet("/{$this->_db}/{$key}/{$file}");
        } catch (Http\Exception $e) {
            $this->_manageExceptions($e);
        }
        return $out;
    }
    
    /**
     * @param string $key
     * @param string $file
     * @param string $data
     * @param string $contentType
     * @return \Nov\CouchDb\Resulset
     */
    public function addAttach($key, $file, $data, $contentType)
    {
        $out = null;
        $key = urlencode($key);
        $file = urlencode($file);
        try {
            $http = Http::connect($this->_host, $this->_port, $this->_protocol)
                ->setCredentials($this->_user, $this->_password);
            $out = $http->doGet("/{$this->_db}/{$key}");
            $reg = json_decode($out);
            
            $out = $http->setHeaders(array('Content-Type' => $contentType))
                ->doPut("/{$this->_db}/{$key}/{$file}?rev={$reg->_rev}", $data);
        } catch (Http\Exception $e) {
            $this->_manageExceptions($e);
        }
        return new CouchDb\Resulset($out);
    }
    
    /**
     * @param string $key
     * @return \Nov\CouchDb\Resulset
     */
    public function delete($key)
    {
        $key = urlencode($key);
        try {
            $http = Http::connect($this->_host, $this->_port, $this->_protocol)
                ->setCredentials($this->_user, $this->_password);
            $out = $http->doGet("/{$this->_db}/{$key}");
            $reg = json_decode($out);
            $out = $http->doDelete("/{$this->_db}/{$key}", array('rev' => $reg->_rev));
        } catch (Http\Exception $e) {
            $this->_manageExceptions($e);
        }
        return new CouchDb\Resulset($out);
    }
    
    /**
     * @param string $key
     * @param string $new
     * @return \Nov\CouchDb\Resulset
     */
    public function copy($key, $new)
    {
        $key = urlencode($key);
        try {
            $out = Http::connect($this->_host, $this->_port, $this->_protocol)
                ->setCredentials($this->_user, $this->_password)
                ->setHeaders(array('Destination' => $new))
                ->doCustom('COPY', "/{$this->_db}/{$key}");
        } catch (Http\Exception $e) {
            var_export($e);
            $this->_manageExceptions($e);
        }
        return new CouchDb\Resulset($out);
    }
    
    /**
     * @param string $design
     * @param string $view
     * @param string $key
     * @return \Nov\CouchDb\Resulset
     */
    public function view($design, $view, $key=null)
    {
        $design = urlencode($design);
        $view   = urlencode($view);
        try {
            $uri = "/{$this->_db}/_design/{$design}/_view/{$view}";
            if (!is_null($key)) {
                $params = array('key' => "\"{$key}\"");
            } else {
                $params = array();
            }
            $out = Http::connect($this->_host, $this->_port, $this->_protocol)
                ->setCredentials($this->_user, $this->_password)
                ->doGet($uri, $params);
        } catch (Http\Exception $e) {
            var_dump($e->getMessage());
            $this->_manageExceptions($e);
        }
        return new CouchDb\Resulset($out);
    }
}