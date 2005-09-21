<?php

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", 'end');

$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
$tpl->assign("isAdministrator", $isAdministrator);


// sometime in the future, this page should display a summary of the process record produced by the 
// workflow just finished - we would supply the PID and process record ID in the GET params.
// TODO: implement process records for workflows!

$wfl_title = Misc::GETorPOST('wfl_title');
$wft_type = Misc::GETorPOST('wft_type');
$parent_title = Misc::GETorPOST('parent_title');
$record_title = Misc::GETorPOST('record_title');
$tpl->assign(compact('wfl_title','wft_type','parent_title','record_title'));


$tpl->displayTemplate();


?>
