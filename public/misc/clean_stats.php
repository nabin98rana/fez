<?php
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once("/var/www/fez/include/class.statistics.php");
Statistics::cleanupFalseHits();
//print_r($GLOBALS);
//session_start();
//echo "here";
?>
