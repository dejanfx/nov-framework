<?php
require_once ("Nov/Loader.php");
\Nov\Loader::init();
echo "<pre>";
\Nov\CouchDb\Fs\Utils::fs2cdb("/mnt/data/work/gwe/nov-framework2/tests/", NovConf::CDB2);
\Nov\CouchDb\Fs\Utils::cdb2fs(NovConf::CDB1, "/mnt/data/work/gwe/nov-framework2/lib233/");
echo "</pre>";