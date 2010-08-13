<?php
namespace Nov;

class Db extends \Nov\Db\PDO{
    const FETCH_ALL  = 0;
    const FETCH_ROW  = 1;
    const FETCH_ONE  = 2;
    const FETCH_NONE = 3;
}