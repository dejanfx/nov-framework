<?php
namespace Nov\Db\Orm\Instance\Interfaces;
interface Select
{
    /**
     * @param array $where
     * @return \Nov\Db\Orm\Instance\Interfaces\Select
     */
    public function where($where=array());
    
    /**
     * @param array|string $values
     * @return \Nov\Db\Orm\Instance\Interfaces\Select
     */
    public function orderBy($orderBy=array());
    
    /**
     * @param array|string $values
     * @return \Nov\Db\Orm\Instance\Interfaces\Select
     */
    public function groupBy($groupBy=array());
    
    public function exec($fetchType = Nov\Db::FETCH_ALL);
}