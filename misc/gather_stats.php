<?php
include_once("../config.inc.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.statistics.php");

Statistics::gatherStats();


?>
