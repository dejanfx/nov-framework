<?php
namespace Nov;
class Types
{
    const STR = 'string';
    const INT = 'integer';
    const ARR = 'array';
    
    static function convertNov2PDO($novType)
    {
        switch ($novType) {
            case self::STR:
                return PDO::PARAM_STR;
                break;
            case self::INT:
                return PDO::PARAM_INT;
                break;
        }
    }
}