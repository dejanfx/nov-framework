<?php
class NovConf
{
    const CDB1 = 'CDB1';
    const PG1  = 'PG1';
    
    public static $_dbs = array(
    	self::PG1  => array(
            'driver'   => 'pgsql',
            'dsn'      => 'pgsql:dbname=pg1;host=localhost',
            'username' => 'nov',
            'password' => 'nov',
        ),
        self::CDB1  => array(
            'driver'   => 'couchdb',
            'host'     => 'localhost',
            'port'     => 5984,
            'protocol' => 'http',
            'username' => null,
            'password' => null,
        ),
    );
}
