<?php
    include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
    include_once APP_INC_PATH.'class.statistics.php';
    $interval = array();
    Statistics::cleanupFalseHits($interval, '2011-09-23 09:26:55');