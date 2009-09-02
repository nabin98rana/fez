<?php
/**
 * Background process to trigger the fulltext indexing process. It
 * can happen that the indexing process crashes due to many factors
 * (file/disk size, memory limits, PDF problems, ...)
 * 
 * In this case, problematic PID has been removed from the queue and
 * probably errors have been written to the error log. This file
 * just goes and checks on the queue again. 
 * 
 * It should run about every 1-2 hours, depending on the number of changes
 * in the system.
 * 
 */


include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");


FulltextQueue::triggerUpdate();
