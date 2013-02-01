<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.scopus_queue.php");
include_once(APP_INC_PATH . "class.scopus_record.php");
$q = ScopusQueue::get();
$q->triggerUpdate();