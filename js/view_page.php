<?php

include_once('../config.inc.php');
include_once(APP_INC_PATH.'class.template.php');

header("Content-type: text/javascript");

$tpl = new Template_API();
$tpl->setTemplate("js/view_page.tpl.js");
$tpl->displayTemplate();

?>