<?php
namespace Nov\Db\Orm;
abstract class View extends AbstractObject
{
    /**
     * Creates a new instance of Nov_Orm_Instance
     *
     * @param \Nov\Orm\PDO|string $db
     * @return \Nov\Db\Orm\Instance\View
     */
    public static function factory($db)
    {
        $class = get_called_class();
        return new Instance\View($class, new $class($db), $db);
    }
}