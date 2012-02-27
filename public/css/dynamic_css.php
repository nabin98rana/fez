<?php
include_once('../config.inc.php');
include_once(APP_INC_PATH.'class.template.php');

/**********************************************************************
 *                                NOTE
 * Most of the dynamic content is related to colour scheme and relative
 * URL's. This content will rarely change, however if it does 
 * dynamic_css.tpl.css will need to be changed and SAVED so that the last 
 * modified timestemp is changed and the webserver will tell users to 
 * download a new copy of this file.
 ***********************************************************************/

$css_file = APP_PATH.'templates/en/css/dynamic_css.tpl.css';

$last_modified_time = filemtime($css_file);
$etag = md5_file($css_file);

header("Cache-Control: public");
header("Expires:");
header("Pragma:");
header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
header("Etag: $etag");

if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time ||
    trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
    header("HTTP/1.1 304 Not Modified");
    exit;
}

header("Content-type: text/css");

$tpl = new Template_API();
$tpl->setTemplate("css/dynamic_css.tpl.css");
$tpl->smarty->left_delimiter = '[[';
$tpl->smarty->right_delimiter = ']]';
$tpl->displayTemplate();

?>