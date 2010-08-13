<?php
namespace Nov\Db\Orm;
class Exception extends \Exception
{
    const MALFORMED_WHERE = 'MALFORMED_WHERE';
    const MALFORMED_WHERE_KEY = 'MALFORMED_WHERE_KEY';
    const MALFORMED_VALUES = 'MALFORMED_VALUES';
    const KEY_NOT_IN_INSERT = 'KEY_NOT_IN_INSERT';
    
    const VIEW_ERROR = 'VIEW_ERROR';
};