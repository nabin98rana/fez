<?php
//include_once(config.inc.php);
include_once("/var/www/fez/config.inc.php");
//include_once("/var/www/fez/include/db_access.php");


include_once("/var/www/fez/include/class.statistics.php");
Statistics::cleanupFalseHits();
//print_r($GLOBALS);
//session_start();
//echo "here";
?>
