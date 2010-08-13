<?php
namespace Nov\CouchDb;
class Resulset
{
    private $_data;

    function __construct($data)
    {
        $this->_data = $data;
    }

    function asArray()
    {
        return (array) json_decode($this->_data);
    }

    function asJson()
    {
        return $this->_data;
    }

    function asObject()
    {
        return json_decode($this->_data);
    }
}