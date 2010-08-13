<?php
namespace Nov\Db\Orm;
abstract class Table extends AbstractObject
{
    /**
     * Creates a new instance of Nov_Orm_Instance
     *
     * @param \Nov\Orm\PDO|string $db
     * @return \Nov\Db\Orm\Instance\Interfaces\Main
     */
    public static function factory($db)
    {
        $class = get_called_class();
        return new Instance\Table($class, new $class($db), $db);
    }
}