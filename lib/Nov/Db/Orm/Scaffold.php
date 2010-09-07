<?php
namespace Nov\Db\Orm;
use Nov;
class Scaffold
{
    /**
     * @var \Nov\Db\PDO
     */
    private $_db = null;
    private $_schema = null;
    
    function __construct($db, $schema=null)
    {
        $this->_db     = $db;
        $this->_schema = $schema;
    }
    
    public function getSchema()
    {
        return $this->_schema;    
    }
    
    const TYPE_SP = 'Stored Procedure';
    const TYPE_TABLE = 'Table';
    const TYPE_VIEW  = 'View';
    
    private $_items = array();
    
   
    private function _add($type, $object)
    {
        if (!is_null($this->_schema) && $this->_db->getDriver() != 'sqlite') {
            $_schema = $this->_schema . '.';    
        } else {
            $_schema = null;
        }
        $sql = "SELECT * from {$_schema}{$object}";
       
        $stmt = $this->_db->prepare($sql);
        $stmt->execute();
        
        //$row = $stmt->fetch();
        $columnCount = $stmt->columnCount();
        $conf = array();
        for ($i=0;$i < $columnCount; $i++) {
            $meta = $stmt->getColumnMeta($i);
            
            $conf['columns'][$meta['name']] = $meta;
        }
        echo "[ok] {$type} - $object\n";
        $this->_items[] = array('type' => $type, 'name' => $object, 'conf' => $conf);
    }
    
    public function addStoredProcedure($name, $params, $types, $out, $outMode, $schema, $recordOutput, $outputType)
    {
        $conf = array();
        if (count((array) $params)>0) {
            foreach (array_keys($types) as $i) {
                $conf['params'][$params[$i]] = array('type' => $types[$i]);
            }
        }
        
        $conf['out']     = $out;
        $conf['outMode'] = $outMode;
        $conf['recordOutput'] = $recordOutput;
        $conf['outputType'] = $outputType;
        echo "[ok] $name\n";
        $this->_items[] = array('schema' => $schema, 'type' => self::TYPE_SP, 'name' => $name, 'conf' => $conf);
    }
    
    public function addTable($tableName)
    {
        $this->_object(self::TYPE_TABLE, $tableName);
    }
    
    public function addView($viewName)
    {
        $this->_object(self::TYPE_VIEW, $viewName);
    }
    
    private function _object($type, $object)
    {
        if (is_array($object)) {
            foreach ($object as $_object) {
                $this->_add($type, $_object);
            }
        } else {
            $this->_add($type, $object);
        }
    }
    
    public function getDb()
    {
        return $this->_db;
    }
    
    public function getObjectList()
    {
        switch ($this->_db->getDriver()) {
            case 'sqlite':
                Nov\Db\Orm\Scaffold\Sqlite::run($this);
                break;
            case 'pgsql':
                Nov\Db\Orm\Scaffold\Pgsql::run($this);
                break;
        }
    }
    
    private $_sp = null;
    public function buildAll($pathTo)
    {
        echo "\n";
        echo "buildAll ...\n";
        echo "\n";

        $this->getObjectList();  
        echo "...\n";
        
        if (count($this->_items) > 0) {
            foreach ($this->_items as $item) {
                $this->buildItem($pathTo, $item);
            }
        }
        
        if (count((array) $this->_sp) > 0) {
            foreach ($this->_sp as $schema => $data) {
                $this->buildSP($pathTo, $schema, $data);
            }
        }
    }
    
    private function _getNovType($nativeType)
    {
        $novType = null;
        switch (strtoupper($nativeType)) {
            case 'INTEGER':
                $novType ='INT';
                break;
            case 'STRING':
            default:
                $novType = 'STR';
                break;
        }
        return $novType;
    }
    
    private function _makeFile($pathTo, $name, $class)
    {
        $pathTo = realpath($pathTo);
        $dir = $pathTo.'/'.$this->_schema;
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $fileName = $dir.'/' . $name.'.php';
        if (is_file($fileName)) {
            unlink($fileName);
        }
        $f = fopen($fileName, 'w+');
        fwrite($f, $class, strlen($class));
        fclose($f);
        echo "[OK] {$name} on {$fileName}\n";
    }
    
    private function buildItem($pathTo, $item)
    {
        $out = null;
        switch ($item['type']) {
            case self::TYPE_TABLE:
            case self::TYPE_VIEW:
                $out = $this->_buildTableView($pathTo, $item);
                break;
            case self::TYPE_SP:
                $out = $this->_buildStoredProcedure($pathTo, $item);
        }
        return $out;
    }
    
    private function _buildStoredProcedure($pathTo, $item)
    {
        $p = array();
        $schema = $item['schema'];
        $input = $output = array();
        $params = (array) $item['conf']['params'];
        if (count($params) > 0) {
            foreach ($params as $param => $paramConf) {
                $p[] = $param;
                $input[] = "'{$param}' => Nov_Types::" . $this->_getNovType($paramConf['type']);
            }
        }
        
        $params = (array) $item['conf']['out'];
        if (count($params) > 0) {
            foreach ($params as $param => $type) {
                $output[] = "'{$param}' => Nov_Types::" . $this->_getNovType($type);
            }
        }
        
        $this->_sp[$schema][$item['name']][] = array(
            'input'  => $input,
            'output' => $output,
            'params' => $p,
            'p2'     => $item['conf']['params'],
            'o2'     => $item['conf']['out'],
            'recordOutput' => $item['conf']['recordOutput'],
            'outputType'   => $item['conf']['outputType'],
            );
        $a = $this->_sp;
        //@TODO
    }
    
    private function buildSP($pathTo, $schema, $data)
    {
        foreach ($data as $function => $fcts) {
            foreach ($fcts as $i => $conf) {
                $fname = (count($fcts) > 1) ? "{$function}_{$i}" : $function;
                $input  = implode(",\n                ", $conf['input']);
                $output = implode(",\n                ", $conf['output']);
                $_fctsParams[$fname] = "'{$fname}' => array(
            'input'  => array(
                {$input}
                ), 
            'output' => array(
                {$output}
                )
            ),";
                $ffArr = array();
                if (count((array) $conf['o2']) > 0) {
                    foreach (array_keys($conf['o2']) as $oV) {
                        $ffArr[] = "function {$oV}()
    {
        \$key = key(Orm_{$schema}_sp::\$_functions['{$fname}']['output']);
        return \$this->_isObject ? \$this->_recordset->\$key : \$this->_recordset[\$key];
    }";
                    }
                    $ff = implode("\n    ", $ffArr);
                }
                if ($conf['recordOutput']) {
                    $classes[] = <<<EOD
namespace sp\Functions\{$fname} {
    class Record extends Nov_Db_Orm_Record
    {
        /**
         * @param Nov_Orm_Record \$recordset
         * @return Orm_{$schema}_sp_{$fname}_Record
         */
        static function factory(\$recordset)
        {
            return new Orm_{$schema}_sp_{$fname}_Record(\$recordset);
        }
        
        private \$_recordset;
        function __construct(\$recordset)
        {
            \$this->_isObject = \$recordset instanceof Nov_Db_Orm_Record;
            \$this->_recordset = \$recordset;
        }
        
        {$ff}
    }
}
EOD;
                }
                if (count((array) $conf['params']) > 0) {
                    $fp = '$' . implode(', $', $conf['params']);
                    $phpDocParamArr = array();
                    foreach ($conf['p2'] as $pname => $pConf) {
                        $pType = $pConf['type'];
                        $phpDocParamArr[] = "* @param {$pType} {$pname}";
                    }
                    $phpDocParam = implode("\n     ", $phpDocParamArr);
                } else {
                    $fp = null;
                }
                if ($conf['recordOutput']) {
                    $returnType = "Orm_{$schema}_sp";
                    $fReturn = "\$this";
                    $defaultFetchMode = "Nov\\Db::FETCH_ALL";
                } else {
                    $returnType = $conf['outputType'];
                    $fReturn = "\$this->exec()";
                    $defaultFetchMode = "Nov\\Db::FETCH_ONE";
                }
                $_fctsBody[] = <<<EOD
    /**
     {$phpDocParam}
     * @return {$returnType}
     */
    public function {$fname}({$fp})
    {
        \$this->_defaultFetchMode = {$defaultFetchMode};
        \$this->_function = __function__;
        \$this->_args = func_get_args();
        return {$fReturn};
    }
EOD;
            }
        }
       
        $cls   = implode("\n", $classes);
        $_fcts = "public static \$_functions = array(
        " . implode("\n        ", $_fctsParams) ."
        );";

        $fctsJoined = implode("\n    ", $_fctsBody);
        $class = <<<EOD
<?php
namespace Orm\\{$this->_schema};
use \\Nov;
class sp extends Nov\Db\Orm\Sp
{
    public static \$_schema = '{$schema}';
    {$_fcts}
     /**
     * Creates a new instance of Nov_Orm_Instance
     *
     * @param Nov_Orm_PDO|string \$db
     * @return \Orm\{$schema}\sp\Functions
     */
    public static function factory(\$db=null)
    {
        return new sp\Functions(\$db, self::\$_functions, self::\$_schema);
    }
}

{$cls}


class sp\Functions extends Nov\Db\Orm\Instance\Sp
{   
{$fctsJoined}
}        
EOD;

        $this->_makeFile($pathTo, 'sp', $class);
    }
    
    private function _buildTableView($pathTo, $item)
    {
        $objectType = $item['type'];
        $className = str_replace('.', '_', $item['name']);
        if (isset($item['conf']['columns']) && count($item['conf']['columns']) > 0) {
            foreach ($item['conf']['columns'] as $columName => $conf) {
                $_consts[] = '    const ' . strtoupper($columName) . ' = "' . $columName . '";';
                $_conf[]   = '        self::' . strtoupper($columName) . ' => array(Nov\Types::' . $this->_getNovType($conf['native_type']) . '),';
                $_fcts[]   = '    function ' . $columName . '()
    {
        return $this->_isObject ? $this->_recordset->{' . $className . '::' . strtoupper($columName) . '} : $this->_recordset[' . $className . '::' . strtoupper($columName) . '];
    }';
            }
        }
        
        $consts = implode("\n", $_consts);
        $conf   = implode("\n", $_conf);
        $fcts   = implode("\n\n", $_fcts);
        
        $schema = is_null($this->_schema) || $this->_db->getDriver() == 'sqlite'  ? 'null' : "'{$this->_schema}'";
        $class = <<<EOD
<?php
namespace Orm\\{$this->_schema};
use \\Nov;
class {$className} extends Nov\\Db\\Orm\\{$objectType}
{
    protected \$_schema = {$schema};
    protected \$_object = "{$item['name']}";

{$consts}
    
    protected \$_conf = array(
{$conf}
        );
}

class {$className}_Record
{
    /**
     * @param \\Nov\\Orm\\Record \$recordset
     * @return \\Orm\\{$this->_schema}\\{$className}_Record
     */
    static function factory(\$recordset)
    {
        return new {$className}_Record(\$recordset);
    }
    
    private \$_recordset;
    function __construct(\$recordset)
    {
        \$this->_isObject = \$recordset instanceof Nov\\Db\\Orm\\Record;
        \$this->_recordset = \$recordset;
    }
    
{$fcts}
}
EOD;
        $this->_makeFile($pathTo, $item['name'], $class);
    }
}