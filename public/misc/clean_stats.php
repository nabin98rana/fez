<?php
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once APP_INC_PATH.'class.statistics.php';
Statistics::cleanupFalseHits();