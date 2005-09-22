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
$parent_pid = Misc::GETorPOST('parent_pid');
$pid = Misc::GETorPOST('pid');
$record = new RecordObject($pid);
$record_title = $record->getTitle();
$parent_title = '';
if ($parent_pid) {
    if ($parent_pid == -1) {
        $view_parent_url = APP_RELATIVE_URL."list.php";
        $parent_title = "Repository";
    } else {
        $precord = new RecordObject($parent_pid);
        if ($precord->isCommunity()) {
            $view_parent_url = APP_RELATIVE_URL."list.php?community_pid=$parent_pid";
        } else {
            $view_parent_url = APP_RELATIVE_URL."list.php?collection_pid=$parent_pid";
        }
        $parent_title = $precord->getTitle();
    }
} 
$view_record_url = APP_RELATIVE_URL."view.php?pid=$pid";
$tpl->assign(compact('wfl_title','wft_type','parent_title','record_title', 'view_record_url', 'view_parent_url'));


$tpl->displayTemplate();


?>
