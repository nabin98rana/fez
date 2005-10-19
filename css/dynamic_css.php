<?php
include_once('../config.inc.php');
include_once(APP_INC_PATH.'class.template.php');

$tpl = new Template_API();
$tpl->setTemplate("css/dynamic_css.tpl.css");
$tpl->smarty->left_delimiter = '[[';
$tpl->smarty->right_delimiter = ']]';
$tpl->displayTemplate();

?>
