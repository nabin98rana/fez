<?php

include_once("config.inc.php");

include_once(APP_INC_PATH . "db_access.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.graphviz.php");

$tpl = new Template_API();
$tpl->setTemplate("testgraphviz.tpl.html");
$dot = @$_POST['dot'];
$tpl->assign('dot', $dot);
if ($dot) {
    $encoded_dot = @base64_encode($_POST['dot']);
    $tpl->assign('encoded_dot', $encoded_dot);
    $map = Graphviz::getCMAPX($dot);
    $tpl->assign('cmapx', $map); 
    $map_name = Graphviz::getGraphName($dot);
    $tpl->assign('map_name', $map_name); 
}

$tpl->displayTemplate();
?>

