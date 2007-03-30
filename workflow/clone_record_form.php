<?php
/*
 * Fez
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 27/03/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
 
$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", 'clone_record_form');

Auth::checkAuthentication(APP_SESSION);
$tpl->setAuthVars();

$wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
$pid = $wfstatus->pid;
$tpl->assign("pid", $pid);
$wfstatus->setTemplateVars($tpl);
if (@$_REQUEST["cat"] == "submit") {
    $new_xdis_id = $_REQUEST['new_xdis_id'];
    $wfstatus->assign('new_xdis_id', $new_xdis_id);
    $is_succession = $_REQUEST['is_succession'];
    $wfstatus->assign('is_succession', $is_succession);
    $wfstatus->assign('clone_binary_datastreams', $_REQUEST['clone_binary_datastreams']);
}
$wfstatus->checkStateChange();

$xdis_list = XSD_Display::getAssocListDocTypes();
$record = new RecordGeneral($pid);
$xdis_id = $record->getXmlDisplayId();

$tpl->assign(compact('xdis_id','xdis_list'));

$tpl->displayTemplate(); 
?>