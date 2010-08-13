<?php
namespace Nov\Db\Orm\Instance\Interfaces;
interface Update
{
    /**
     * @param array $where
     * @return \Nov\Db\Orm\Instance\Interfaces\Update
     */
    public function where($where=array());

    public function exec($fetchType = Nov\Db::FETCH_ALL);
}