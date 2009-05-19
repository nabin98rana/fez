<?php

/** 
 * Script to update the summary statistics tables
 *
 * NOTE: can take over seven hours on first time run
 *
 */
include_once('../config.inc.php');
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.statistics.php");

Statistics::updateSummaryTables();
