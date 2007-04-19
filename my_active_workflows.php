<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 19/04/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");

$tpl = new Template_API();
$tpl->setTemplate("my_fez.tpl.html");
$tpl->assign('myFezView', "MWF");

$list = WorkflowStatusStatic::getList(Auth::getUserID());

$tpl->assign(compact('list'));
$tpl->displayTemplate();
 
?>
