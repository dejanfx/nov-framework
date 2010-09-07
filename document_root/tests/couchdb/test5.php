<?php
require_once ("Nov/Loader.php");
Nov\Loader::init();

define('FSCDB', \NovConf::CDB1);
include ("Nov/CouchDb/Fs/Monkeypatch.php");

include('test6.php');