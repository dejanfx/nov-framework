<?php
namespace Nov\Db\Orm\Instance\Interfaces;
interface Delete
{
    /**
     * @param array $where
     * @return \Nov\Db\Orm\Instance\Interfaces\Delete
     */
    public function where($where=array());
    
    public function exec($fetchType = Nov\Db::FETCH_ALL);
}