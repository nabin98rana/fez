<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.wok_queue.php");
$q = WokQueue::get();
$q->triggerUpdate();


