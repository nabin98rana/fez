<?php

$ARGV = $_SERVER['argv'];
$base = $ARGV[2];
include_once($base.'config.inc.php');
include_once(APP_INC_PATH.'class.background_process.php');

$bgp_id = $ARGV[1];
//print_r($ARGV);

$dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
$stmt = "SELECT * FROM {$dbtp}background_process WHERE bgp_id='{$bgp_id}'";
$res = $GLOBALS['db_api']->dbh->getAll($stmt,DB_FETCHMODE_ASSOC);
if (PEAR::isError($res)) {
    Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
}

include_once(APP_INC_PATH.$res[0]['bgp_include']);
$bgp = unserialize($res[0]['bgp_serialized']);

$bgp->setAuth();

$bgp->run();

?>
