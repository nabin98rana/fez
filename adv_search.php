<?php

include_once('config.inc.php');
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.template.php");

$tpl = new Template_API();
$tpl->setTemplate("adv_search.tpl.html");

$tpl->displayTemplate();
?>

