<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 15/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");


$button = Misc::GETorPOST_prefix('workflow_button_');
if (!empty($button)) {
    Auth::redirect(APP_BASE_URL.'my_processes.php');
}

$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign('type',"no_action");


Auth::checkAuthentication(APP_SESSION);

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
$pid = $wfstatus->pid;
$tpl->assign("pid", $pid);

$wfstatus->setTemplateVars($tpl);
// override the button list to force exit from workflow to the my_background processes page
$tpl->assign('workflow_buttons', array(array(
                    'wfs_id' => -2, // go to my background processes
                    'wfs_title' => 'Ok'
                    )));




$tpl->assign('no_bottom_buttons', true);

$tpl->displayTemplateRecord($pid);
?>
