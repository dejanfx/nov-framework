<?php 
namespace Nov\Db\Orm\Scaffold;  
use Nov\Db\Orm;
class Pgsql implements InterfaceObject
{
    static function run(Orm\Scaffold &$scaffold)
    {
        self::_getObjects('tables', $scaffold);
        self::_getObjects('views', $scaffold);
        self::_getStoredProcedures($scaffold);
    }
    
    private static function _getArgs($string)
    {
        $string = str_replace('{', null, $string);
        $string = str_replace('}', null, $string);
        return explode(',', $string);
    }
    private static function _getStoredProcedures(Orm\Scaffold &$scaffold)
    {
        $sql = "
            select 
                proname, proargnames, proargtypes, proargmodes, proallargtypes, prorettype
            from 
                pg_catalog.pg_proc proc, 
                pg_catalog.pg_namespace namespace
             
            where
                namespace.oid = proc.pronamespace and
                namespace.nspname = :NSPNAME";
        $stmt = $scaffold->getDb()->prepare($sql);
        $schema = $scaffold->getSchema();
        $stmt->bindParam(':NSPNAME', $schema);
        $stmt->execute();
        
        $rows = (array) $stmt->fetchAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $types = array();
                $params = self::_getArgs($row['proargnames']);
                $proallargtypes = self::_getArgs($row['proallargtypes']);
                $modes = self::_getArgs($row['proargmodes']);
                $_types = (array) explode(' ', $row['proargtypes']);
                foreach ($_types as $_t) {
                    $types[] = self::_getNovType($_t);
                }
                $name = $row['proname'];
                $out = $outMode = array(); 
                if (count((array) $modes) > 0) {
                    foreach ($modes as $i => $m) {
                        if (in_array($m, array('b', 'o'))) {
                            $outMode[$params[$i]] = $m;
                            $out[$params[$i]] = self::_getNovType($proallargtypes[$i]);
                        }
                    }
                }
                $recordOutput = false;
                
                switch ($row['prorettype']) {
                    case 2249: // record
                        $recordOutput = true;
                        break;
                    default:
                       $out[$name] = self::_getNovType($row['prorettype']); 
                }
                $outputType = self::_getNovType($row['prorettype']);
                $scaffold->addStoredProcedure($name, $params, $types, $out, $outMode, $schema, $recordOutput, $outputType);
            }
        }
    }
    
    private static function _getNovType($type)
    {
        switch ($type) {
            case 23:
                return Nov\Types::INT;
                break;
            case 1043:
                return Nov\Types::INT;
                break;
        }
    }
    
    private static function _getObjects($type, Orm\Scaffold &$scaffold)
    {
        $sql = "select * from pg_catalog.pg_{$type} where schemaname=:SCHEMA";
        $stmt = $scaffold->getDb()->prepare($sql);
        $schema = $scaffold->getSchema();
        $stmt->bindParam(':SCHEMA', $schema);
        $stmt->execute();
        
        $rows = (array) $stmt->fetchAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
            	if ($type == 'views') {
                    $key = 'viewname';
                    $scaffold->addView($row[$key]);
            	} else {
            		$key = 'tablename';
                    $scaffold->addTable($row[$key]);
            	}
            }
        }
    }
}