<?php
namespace Nov\Db\Orm\Instance\Interfaces;
interface Insert
{  
    public function exec($fetchType = Nov\Db::FETCH_ALL);
}