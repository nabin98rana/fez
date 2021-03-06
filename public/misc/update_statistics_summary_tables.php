<?php

/** 
 * Script to update the summary statistics tables
 *
 * NOTE: can take over seven hours on first time run
 *
 */
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.statistics.php");

Statistics::updateSummaryTables();
