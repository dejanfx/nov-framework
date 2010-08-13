<?php
namespace Nov\Db\Orm\Scaffold;
use Nov\Db\Orm;
class Sqlite implements InterfaceObject
{
    static function run(Orm\Scaffold &$scaffold)
    {
        $sql = "SELECT type, name from sqlite_master where  type in ('table', 'view') and name <> 'sqlite_sequence'";
        $stmt = $scaffold->getDb()->prepare($sql);
        $stmt->execute();
        
        $rows = (array) $stmt->fetchAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                switch ($row['type']) {
                    case 'table':
                        $scaffold->addTable($row['name']);
                        break;
                    case 'view':
                        $scaffold->addView($row['name']);
                        break;
                }
            }
        }
    }
}