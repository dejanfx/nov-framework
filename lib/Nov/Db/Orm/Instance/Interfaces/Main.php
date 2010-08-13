<?php
namespace Nov\Db\Orm\Instance\Interfaces;
interface Main
{
	/**
     * @param array $values
     * @return \Nov\Db\Orm\Instance\Interfaces\Update
     */
    public function update($values);
    
    /**
     * @return \Nov\Db\Orm\Instance\Interfaces\Insert
     */
    public function delete();
    
    /**
     * @param array $values
     * @return \Nov\Db\Orm\Instance\Interfaces\Select
     */
    public function select($values=array());
    
    /**
     * @return \Nov\Db\Orm\Instance\Interfaces\Insert
     */
    public function insert($values);
}