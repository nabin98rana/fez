<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005-2009, The University of Queensland,               |
// | Australian Partnership for Sustainable Repositories,                 |
// | eScholarship Project                                                 |
// |                                                                      |
// | Some of the Fez code was derived from Eventum (Copyright 2003, 2004  |
// | MySQL AB - http://dev.mysql.com/downloads/other/eventum/ - GPL)      |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.history.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.background_process_list.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");
include_once(APP_INC_PATH . "class.author_era_affiliations.php");
include_once(APP_INC_PATH . "class.exiftool.php");
include_once(APP_INC_PATH . "class.api.php");

Auth::checkAuthentication(APP_SESSION, 'index.php?err=5', TRUE);

$tpl = new Template_API();

$log = FezLog::get();

$isAdministrator = Auth::isAdministrator();
$usr_id = Auth::getUserID();

//Perform some input validation.
if (array_key_exists('cat', $_REQUEST) && !empty($_REQUEST["cat"]) && !$cat = Fez_Validate::run('Fez_Validate_Simpleparam', $_REQUEST["cat"])) {
  if (APP_API) {
    API::reply(400, API::makeResponse(400, "Parameter validation failed."), APP_API);
  }
  exit;
}

if (array_key_exists('dsID', $_REQUEST) && !empty($_REQUEST["dsID"]) && !$dsID = Fez_Validate::run('Fez_Validate_Dsid', $_REQUEST["dsID"])) {
  if (APP_API) {
    API::reply(400, API::makeResponse(400, "Parameter validation failed."), APP_API);
  }
  exit;
}

if (array_key_exists('pid', $_REQUEST) && !empty($_REQUEST['pid']) && ($_REQUEST['pid']) != '-1' && !$pid = Fez_Validate::run('Fez_Validate_Pid', $_REQUEST['pid'])) {
  if (APP_API) {
    API::reply(400, API::makeResponse(400, "Parameter validation failed."), APP_API);
  }
  exit;
}

if (APP_API) {
  switch (HTTP_METHOD) {
    case 'DELETE':
    case 'POST':
      // We're letting them to POST to delete, so only populate the POST if it's not a delete or purge
      // Through the API we currently support deleting attachments, purging attachments
      // this file also handles processing of 'update_security'.
      $tpl->setTemplate("popup.tpl.xml");
      if ($cat == 'update_security') {
        $record = new RecordObject($pid);
        $record->getDisplay();
        if ($dsID != "") {
          $FezACML_xdis_id = XSD_Display::getID('FezACML for Datastreams');
          $xsd_display_fields = $record->display->getMatchFieldsList(array(), array("FezACML for Datastreams"));  // Specify FezACML as the only display needed for security
          $details = $record->getDetails($dsID, $FezACML_xdis_id);
          $record->clearDetails();
        }
        else {
          $xsd_display_fields = $record->display->getMatchFieldsList(array(), array("FezACML"));  // Specify FezACML as the only display needed for security
          $FezACML_xdis_id = XSD_Display::getID('FezACML');
          $details = $record->getDetails("", $FezACML_xdis_id);
        }
        // Populate the POST variable based on the xsd display fields and details of the record (or datastream record)
        API::populateThePOST($xsd_display_fields, $details, FALSE);
        // We'll also want to set the cat in POST. It is accessed directly when updating datastream details.
        $_POST['cat'] = $_GET['cat'];
      }
      break;

    default:
      $arr = API::makeResponse('FAIL', "Method not allowed.");
      API::reply(405, $arr, APP_API); //method not allowed
      exit;
  }
}
else {
  $tpl->setTemplate("popup.tpl.html");
}

if (APP_FEDORA_BYPASS == 'ON') {
  if (defined('AWS_S3_ENABLED') && AWS_S3_ENABLED != 'true') {
    $now = Date_API::getCurrentDateGMT();
    $dbgRec = new RecordObject($_GET['pid']);
  }
}

switch ($cat) {
  case 'file_manager': {
    $wfstatus = &WorkflowStatusStatic::getSession(); // restores WorkflowStatus object from the session
    $wfstatus->assign('folder', $_POST['currentFolderPath']);
    $wfstatus->assign('files', $_POST['check']);
    $wfstatus->setSession();

    $tpl->assign("file_manager_result", 1);
    break;
  }
  case 'purge_datastream': {
    $record = new RecordObject($pid);
    if ($record->canEdit()) {
      $res = Fedora_API::callPurgeDatastream($pid, $dsID);
      $stream = "stream_" . str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))) . ".flv";
      $thumbnail = "thumbnail_" . str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))) . ".jpg";
      $web = "web_" . str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))) . ".jpg";
      $preview = "preview_" . str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))) . ".jpg";
      $FezACML_DS = "FezACML_" . str_replace(" ", "_", $dsID) . ".xml";
      $PresMD_DS = "presmd_" . str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))) . ".xml";
      if (Fedora_API::datastreamExists($pid, $stream)) {
        Fedora_API::callPurgeDatastream($pid, $stream);
      }
      if (Fedora_API::datastreamExists($pid, $thumbnail)) {
        Fedora_API::callPurgeDatastream($pid, $thumbnail);
      }
      if (Fedora_API::datastreamExists($pid, $preview)) {
        Fedora_API::callPurgeDatastream($pid, $preview);
      }
      if (Fedora_API::datastreamExists($pid, $web)) {
        Fedora_API::callPurgeDatastream($pid, $web);
      }
      if (Fedora_API::datastreamExists($pid, $FezACML_DS)) {
        Fedora_API::callPurgeDatastream($pid, $FezACML_DS);
      }
      if (Fedora_API::datastreamExists($pid, $PresMD_DS)) {
        Fedora_API::callPurgeDatastream($pid, $PresMD_DS);
      }
      if (APP_FEDORA_BYPASS != 'ON') {
        Record::setIndexMatchingFields($pid);
      }
      // Remove details from the exif table
      Exiftool::remove($pid, $dsID);
      if (count($res) == 1) {
        $res = 1;
      }
      else {
        $res = -1;
      }
      $tpl->assign("purge_datastream_result", $res);
    }
    else {
      $tpl->assign("purge_datastream_result", -1);
    }
    break;
  }
  case 'delete_datastream': {
    if (defined('AWS_S3_ENABLED') && AWS_S3_ENABLED != 'true' && (APP_FEDORA_BYPASS == 'ON')) {
      $dbgRec->forceInsertUpdate(array('removeFiles' => array($dsID)));
    }
    else {
      $record = new RecordObject($pid);
      if ($record->canEdit()) {
        $res = Fedora_API::deleteDatastream($pid, $dsID);
        $stream = "stream_" . str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))) . ".flv";
        $thumbnail = "thumbnail_" . str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))) . ".jpg";
        $web = "web_" . str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))) . ".jpg";
        $preview = "preview_" . str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))) . ".jpg";
        $FezACML_DS = "FezACML_" . str_replace(" ", "_", $dsID) . ".xml";
        $PresMD_DS = "presmd_" . str_replace(" ", "_", substr($dsID, 0, strrpos($dsID, "."))) . ".xml";
        if (Fedora_API::datastreamExists($pid, $stream)) {
          Fedora_API::deleteDatastream($pid, $stream);
        }
        if (Fedora_API::datastreamExists($pid, $thumbnail)) {
          Fedora_API::deleteDatastream($pid, $thumbnail);
        }
        if (Fedora_API::datastreamExists($pid, $preview)) {
          Fedora_API::deleteDatastream($pid, $preview);
        }
        if (Fedora_API::datastreamExists($pid, $web)) {
          Fedora_API::deleteDatastream($pid, $web);
        }
        if (Fedora_API::datastreamExists($pid, $FezACML_DS)) {
          Fedora_API::deleteDatastream($pid, $FezACML_DS);
        }
        if (Fedora_API::datastreamExists($pid, $PresMD_DS)) {
          Fedora_API::deleteDatastream($pid, $PresMD_DS);
        }
        if (APP_FEDORA_BYPASS != 'ON') {
          Record::setIndexMatchingFields($pid);
        }
        // Remove details from the exif table
        Exiftool::remove($pid, $dsID);
        if (count($res) == 1) {
          $res = 1;
        }
        else {
          $res = -1;
        }
        $tpl->assign("delete_datastream_result", $res);
      }
      else {
        $tpl->assign("delete_datastream_result", -1);
      }
    }
    break;
  }
  case 'update_form': {
    $id = $_REQUEST['id'];
    $wfstatus = WorkflowStatusStatic::getSession($id); // restores WorkflowStatus object from the session
    $pid = $wfstatus->pid;
    $res = Record::update($pid, array("FezACML"), array(""));
    $tpl->assign("update_form_result", $res);
    $wfstatus->checkStateChange(TRUE);
    break;
  }
  case 'update_security': {
    $id = $_REQUEST['id'];
    $wfstatus = WorkflowStatusStatic::getSession($id); // restores WorkflowStatus object from the session
    $pid = $wfstatus->pid;
    $dsID = $wfstatus->dsID;

    if ($dsID != "") {
      $res = Record::editDatastreamSecurity($pid, $dsID);
    }
    else {
      $res = Record::update($pid, array(""), array("FezACML"));
    }
    $wfstatus->checkStateChange(TRUE);
    $tpl->assign("update_form_result", $res);
    break;
  }
  case 'delete_objects': {
    // add a history comment if one has been included
    $historyComment = $_REQUEST['historyComment'];
    if (!$historyComment) {
      $historyComment = NULL;
    }

    // first delete all indexes about this pid
    $items = $_REQUEST['items'];
    if (empty($items)) { // is named pids on the list form
      $items = $_REQUEST['pids'];
    }
    foreach ($items as $pid) {
      $doi = Record::getSearchKeyIndexValue($pid, 'DOI', FALSE);
      if (stripos($doi, CROSSREF_DOI_PREFIX) === FALSE) {
        $rec_obj = new RecordObject($pid);
        if ($rec_obj->canDelete()) {
          $rec_obj->markAsDeleted();
          History::addHistory($pid, NULL, '', '', TRUE, 'Bulk Deleted', $historyComment);

          if (APP_SOLR_INDEXER == "ON") {
            FulltextQueue::singleton()->remove($pid);
          }
        }
      }
    }
    if (APP_SOLR_INDEXER == "ON") {
      FulltextQueue::singleton()->commit();
      FulltextQueue::singleton()->triggerUpdate();
    }
    $tpl->assign("delete_object_result", 1);
    break;
  }
  case 'delete_background_processes': {
    if ($isAdministrator) {
      $items = Misc::GETorPOST('items');
      $bgpl = new BackgroundProcessList();
      $res = $bgpl->delete($items);
      $tpl->assign('generic_result', $res);
      $tpl->assign("generic_action", 'delete');
      $tpl->assign("generic_type", 'background processes');
    }
    break;
  }
  case 'publish_objects': {
    $items = $_REQUEST['pids'];
    foreach ($items as $pid) {
      $rec_obj = new RecordObject($pid);
      if ($rec_obj->canApprove()) {
        $res = $rec_obj->setStatusId(2);
        History::addHistory($pid, NULL, '', '', TRUE, 'Bulk Published');
      }
    }
    $tpl->assign('generic_result', $res);
    $tpl->assign("generic_action", 'publish');
    $tpl->assign("generic_type", 'records');
    break;
  }
  case 'new_workflow_triggers': {
    if ($isAdministrator) {
      $tpl->assign("generic_result", WorkflowTrigger::insert());
      $tpl->assign("generic_action", 'add');
      $tpl->assign("generic_type", 'workflow trigger');
    }
    break;
  }
  case 'edit_workflow_triggers': {
    if ($isAdministrator) {
      $tpl->assign("generic_result", WorkflowTrigger::update());
      $tpl->assign("generic_action", 'update');
      $tpl->assign("generic_type", 'workflow trigger');
    }
    break;
  }
  case 'list_action_workflow_triggers': {
    if ($isAdministrator) {
      if (Misc::GETorPOST('delete')) {
        $tpl->assign("generic_result", WorkflowTrigger::remove());
        $tpl->assign("generic_action", 'delete');
      }
      $tpl->assign("generic_type", 'workflow trigger');
    }
    break;
  }
  case 'save_era_aa': {
    $wfstatus = WorkflowStatusStatic::getSession($id); // restores WorkflowStatus object from the session
    $pid = $wfstatus->pid;
    $saveResult = author_era_affiliations::save($_POST['aae_id'], $pid, $_POST['aae_status_id_lookup'], $_POST['af_era_comment'], $_POST['staff_id']);
    $tpl->assign('update_form_result', $saveResult);
    $wfstatus->checkStateChange(TRUE);
    break;
  }

  case 'update_security_fedora_bypass': {
    $id = $_REQUEST['id'];
    $toDeletes = $_REQUEST['items'];
    $role = $_REQUEST['role'];
    $groupsType = $_REQUEST['groups_type'];
    $group = $_REQUEST['group'];
    $did = $_REQUEST['did'];
    $datastream_policy = $_REQUEST['datastream_policy'];
    $wfstatus = WorkflowStatusStatic::getSession($id); // restores WorkflowStatus object from the session
    $pid = $wfstatus->pid;
    //$dsID = $wfstatus->dsID;

    if ($datastream_policy) {
      FezACML::updateDatastreamQuickRule($pid, $datastream_policy);
    }
    if ($did != "") {
      if ($_REQUEST['inherit']) {
        AuthNoFedoraDatastreams::setInherited($did);
      }
      else {
        AuthNoFedoraDatastreams::deleteInherited($did);
        AuthNoFedoraDatastreams::recalculatePermissions($did);
      }
      if ($_REQUEST['copyright']) {
        AuthNoFedoraDatastreams::setCopyright($did);
      }
      else {
        AuthNoFedoraDatastreams::deleteCopyright($did);
      }
      if ($_REQUEST['watermark']) {
        AuthNoFedoraDatastreams::setWatermark($did);
      }
      else {
        AuthNoFedoraDatastreams::deleteWatermark($did);
      }

      if (is_array($toDeletes)) {
        foreach ((array) $toDeletes as $toDelete) {
          $toDeleteinfo = explode(",", $toDelete);
          AuthNoFedoraDatastreams::deleteSecurityPermissions($did, $toDeleteinfo[0], $toDeleteinfo[1]);
        }
        AuthNoFedoraDatastreams::recalculatePermissions($did);
      }
      if (!empty($group)) {
        $arId = AuthRules::getOrCreateRule("!rule!role!" . $groupsType, $group);
        AuthNoFedoraDatastreams::addSecurityPermissions($did, $role, $arId);
        AuthNoFedoraDatastreams::recalculatePermissions($did);
      }
    }
    else {
      if ($_REQUEST['inherit']) {
        AuthNoFedora::setInherited($pid);
      }
      else {
        AuthNoFedora::deleteInherited($pid);
      }
      if (is_array($toDeletes)) {
        foreach ((array) $toDeletes as $toDelete) {
          $toDeleteinfo = explode(",", $toDelete);
          AuthNoFedora::deleteSecurityPermissions($pid, $toDeleteinfo[0], $toDeleteinfo[1]);
        }
        AuthNoFedoraDatastreams::recalculatePermissions($did);
      }
      if (!empty($group)) {
        $arId = AuthRules::getOrCreateRule("!rule!role!" . $groupsType, $group);
        AuthNoFedora::addSecurityPermissions($pid, $role, $arId);
        AuthNoFedoraDatastreams::recalculatePermissions($did);
      }
    }
    $tpl->assign("update_form_result", $res);
    $wfstatus->checkStateChange(TRUE);
    break;
  }
    if (APP_FILECACHE == "ON") {
      $cache = new fileCache($pid, 'pid=' . $pid);
      $cache->poisonCache();
    }
}

$tpl->assign("current_user_prefs", Prefs::get($usr_id));

$tpl->displayTemplate();
