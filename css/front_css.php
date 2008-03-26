<?php
include_once('../config.inc.php');
include_once(APP_INC_PATH.'class.template.php');

header("Content-type: text/css");

$tpl = new Template_API();
$tpl->setTemplate("css/front.tpl.css");
$tpl->smarty->left_delimiter = '[[';
$tpl->smarty->right_delimiter = ']]';
$tpl->displayTemplate();

?>
