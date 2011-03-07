<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
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
include_once(APP_INC_PATH."class.db_api.php");
include_once(APP_INC_PATH.'class.wfbehaviours.php');
include_once(APP_INC_PATH.'class.workflow_state.php');
include_once(APP_INC_PATH.'class.workflow_trigger.php');
include_once(APP_INC_PATH.'class.foxml.php');
include_once(APP_INC_PATH.'class.bgp_generate_duplicates_report.php');
include_once(APP_INC_PATH.'class.bgp_duplicates_report_merge_isi_loc.php');
include_once(APP_INC_PATH.'class.bgp_publish.php');
include_once(APP_INC_PATH.'class.template.php');
include_once(APP_INC_PATH.'class.mail.php');

/**
 * for tracking status of objects in workflows.  This is like the runtime 
 * part of the workflows.
 */
class WorkflowStatus
{
  var $wfs_id;
  var $pid;
  var $pids = array();
  var $dsID;
  var $outcome = "";
  var $outcomeDetail = "";
  var $xdis_id;
  var $new_xdis_id;
  var $wft_id;
  var $custom_view_pid;
  var $wfl_details;
  var $wfs_details;
  var $wft_details;
  var $wfb_details;
  var $dsInfo;
  var $change_on_refresh;
  var $end_on_refresh;
  var $parents_list;
  var $parent_pid;
  var $vars = array(); // associative array for storing workflow 
                       // variables between states
  var $rec_obj;
  var $href;
  var $states_done = array();
  var $record_title;
  var $extra_history_detail = "";
  //var $request_params; // copy of $_REQUEST when workflow was started

  /**
   * Constructor.   These variables don't really need to be set depending on
   * how the workflow is triggered.  Sometimes nothing is known at the start
   * of the workflow lik ehwen the user clicks 'create' on the My_Fez page.
   */
  function WorkflowStatus(
    $pid=null, $wft_id=null, $xdis_id=null, $dsInfo=null, $dsID='', 
    $pids=array(), $custom_view_pid=null
  )
  {
    $this->pid = $pid;
    $this->pids = $pids;
    $this->dsID = $dsID;
    $this->wft_id = $wft_id;
    $this->xdis_id = $xdis_id;
    $this->dsInfo = $dsInfo;
    $this->custom_view_pid = $custom_view_pid;
    //$this->request_params = $_REQUEST;
    $this->id = $this->newDBSession();
    $this->setSession();
  }

  function newDBSession()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $usr_id = Auth::getUserID();
    $dbtp =  APP_TABLE_PREFIX;
    $stmt = "INSERT INTO ".$dbtp."workflow_sessions (wfses_usr_id, ".
                "wfses_listing, wfses_date) " .
                "VALUES (".$db->quote($usr_id, 'INTEGER').",'',NOW())";

    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return -1;
    }
    return $db->lastInsertId($dbtp."workflow_sessions", "wfses_id");
  }

  /**
   * Get an object for the pid that the workflow is working on
   */
  function getRecordObject()
  {
    if (!$this->rec_obj || $this->rec_obj->pid != $this->pid) {
      $this->rec_obj = new RecordObject($this->pid);
    }
    return $this->rec_obj;
  }

  function getRecordTitle()
  {
    if (empty($this->record_title)) {
      if ($this->pid && !is_numeric($this->pid)) {
        $rec = $this->getRecordObject();
        $this->record_title = $rec->getTitle();
      }
    }
    return $this->record_title;
  }

  /**
   * Saves this object in the workflow_sessions db table
   */
  function setSession()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $this->getWorkflowDetails();
    $this->getStateDetails();
    $title = $this->wfl_details['wfl_title'].": ".
             $this->wfs_details['wfs_title'];
    if ($this->pid && !is_numeric($this->pid)) {
      $title .= " on ".$this->pid.": ". $this->getRecordTitle();
    }
    // $date = Date_API::getCurrentDateGMT();
    $date = date('Y-m-d H:i:s');
    $usr_id = Auth::getUserID();
    $id = $this->id;
    // get rid of some stuff to cut the size of the serialised object down
    $wfs_details = $this->wfs_details;
    $wfl_details = $this->wfl_details;
    $wfb_details = $this->wfb_details;
    $rec_obj = $this->rec_obj;
    $this->wfs_details = null;
    $this->wfl_details = null;
    $this->wfb_details = null;
    $this->rec_obj = null;
    $blob = serialize($this);    
    $dbtp =  APP_TABLE_PREFIX;
    $stmt = "UPDATE ".$dbtp."workflow_sessions " .
                "SET wfses_object=?, wfses_listing=?, wfses_date=?, ".
                "wfses_pid=? WHERE wfses_id=? AND wfses_usr_id=?";
    $log->debug($stmt);
    try {
      $db->query($stmt, array($blob,$title,$date,$this->pid,$id,$usr_id));
      $res = 1;
    }
    catch(Exception $ex) {
      $log->err($ex);
      $res = -1;
    }

    $this->wfs_details = $wfs_details;
    $this->wfl_details = $wfl_details;
    $this->wfb_details = $wfb_details;
    $this->rec_obj = $rec_obj;
    return $res;
  }

  /**
   * Deletes this object from the session variable
   */
  function clearSession()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $usr_id = Auth::getUserID();
    $id = $this->id;
    $dbtp =  APP_TABLE_PREFIX;
    $stmt = "DELETE FROM ".$dbtp."workflow_sessions " .
                "WHERE wfses_id=".$db->quote($id, 'INTEGER').
                " AND wfses_usr_id=".$db->quote($usr_id, 'INTEGER');
    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return -1;
    }
    return 1;
  }

  /**
   * Clear member copies of the workflow state details
   */
  function clearStateDetails()
  {
    $this->wfs_details = null;
    $this->wfb_details = null;
  }

  /**
   * Sets the currently executing state
   * @param integer $wfs_id The new state to be in
   */
  function setState($wfs_id)
  {
    if ($this->wfs_id != $wfs_id) {
      $this->wfs_id = $wfs_id;
      $this->clearStateDetails();
    }
  }

  /**
   * Get a member copy of the trigger that started the workflow.
   */
  function getTriggerDetails()
  {
    if (!$this->wft_details) {
      $this->wft_details = WorkflowTrigger::getDetails($this->wft_id);
      $wft_type = WorkflowTrigger::getTriggerName(
          $this->wft_details['wft_type_id']
      );
      $this->parent_pid = null;
      $this->parents_list = null;
      if ($wft_type == 'Create') {
        $this->parent_pid = $this->pid;
        // CK added null'ing the pid var if its a create workflow 23/10/2008
        $this->pid = null;
      } elseif (($wft_type != 'Ingest') && ($this->dsID == "")) {
        $this->getRecordObject();
        $this->parents_list = $this->rec_obj->getParents();
      }
    }
    return $this->wft_details;
  }

  /**
   * get a member of copy fo the current state
   */
  function getStateDetails()
  {
    if (!$this->wfs_details) {
      if (!$this->wfs_id) {
        // need to start the workflow
        $this->getTriggerDetails();
        $this->wfs_details = Workflow_State::getStartState(
            $this->wft_details['wft_wfl_id']
        );
        $this->setState($this->wfs_details['wfs_id']);
      }
      $this->wfs_details = Workflow_State::getDetails($this->wfs_id);
    }
    return $this->wfs_details;
  }

  /**
   * get a member copy of the current workflow
   */
  function getWorkflowDetails()
  {
    if (!$this->wfl_details) {
      $this->getTriggerDetails();
      $this->wfl_details = Workflow::getDetails(
          $this->wft_details['wft_wfl_id']
      );
    }
    return $this->wfl_details;
  }

  /**
   * Get a member copy of the behaviour details for the current state
   */
  function getBehaviourDetails()
  {
    if (!$this->wfb_details) {
      $this->getStateDetails();
      $this->wfb_details = WF_Behaviour::getDetails(
          $this->wfs_details['wfs_wfb_id']
      );
    }
    return $this->wfb_details;
  }

  /**
   * Get the extra PREMIS detail for the current workflow.
   */
  function getHistoryDetail()
  {
    return $this->extra_history_detail;
  }


  /**
   * Move to the next state from an automatic state.  Automatic states can 
   * only go to one proceeding state.
   */
  function auto_next()
  {
    $log = FezLog::get();

    //Error_Handler::logError($this->wfs_id);
    if (empty($this->wfs_id)) {
      // we must have just chained to a new workflow so run the first state.
      $this->run();
    } else {
      $this->getStateDetails();
      if (!$this->wfs_details['wfs_end'] == 1) {
        // goto next state
        $this->setState($this->wfs_details['next_ids'][0]);
        $this->run();
      } else {
        $this->theend();
      }
    }
  }

  function addToStateHistory()
  {
    $history_end = Misc::array_last($this->states_done);
    if (
        empty($history_end) || 
        $history_end['wfs_id'] != $this->wfs_details['wfs_id']
    ) {
      $this->states_done[] = array_merge(
          $this->wfs_details, $this->wfb_details
      );
    }
  }

  /**
   * Perform the action for the current state.  This will be either displaying
   * a form or running a script.
   */
  function run()
  {
    $log = FezLog::get();

    $this->getBehaviourDetails();
    $this->getTriggerDetails();
    $this->getStateDetails();
    $this->addToStateHistory();
    $this->setSession();

    if ($this->wfb_details['wfb_auto']) {
      include(APP_PATH.'workflow/'.$this->wfb_details['wfb_script_name']);
      $this->auto_next();
    } else {
      if (!$GLOBALS['auth_isBGP']) {
        header(
            "Location: ".APP_RELATIVE_URL.'workflow/'.
            $this->wfb_details['wfb_script_name']
            ."?id=".$this->id."&wfs_id=".$this->wfs_id
        );
      }
      exit;
    }
  }

  /**
   * The end of the workflow has been reached.  Tidy up some variables and 
   * display a summary page.
   */
  function theend($redirect=true)
  {
    $this->getWorkflowDetails();
    $wfl_title = $this->wfl_details['wfl_title'];
    $wft_type = WorkflowTrigger::getTriggerName(
        $this->wft_details['wft_type_id']
    );
    $dsID = $this->dsID;
    $pid = $this->pid;
    $parent_pid = $this->parent_pid;
    $custom_view_pid = $this->custom_view_pid;
    $parents_list = serialize($this->parents_list);
    $href= $this->href;
    $args = compact(
        'wfl_title', 'wft_type', 'parent_pid', 'pid', 'dsID', 'parents_list',
        'action', 'href', 'custom_view_pid'
    );
    $argstrs = array();
    $outcome = $this->getvar('outcome');
    if ($outcome == "") {
      $outcome = "Finished";
    }
    $outcome_details = $this->getvar('outcome_details');
    foreach ($args as $key => $arg) {
      $argstrs[] = $key."=".urlencode($arg);
    }
    $querystr=implode('&', $argstrs);
    if (($wft_type != 'Delete') && !empty($this->pid)) {
      History::addHistory(
          $pid, $this->wfl_details['wfl_id'], $outcome, 
          $outcome_details, true, "", $this->extra_history_detail
      );
    } elseif (!empty($this->parents_list)) {
      foreach ($this->parents_list as $parent_pid) {
        History::addHistory(
            $parent_pid, $this->wfl_details['wfl_id'], "", 
            "Deleted child ".$pid, true, "", $this->extra_history_detail
        );
      }
    }
    $this->clearSession();
    if (($wft_type != 'Ingest') && ($redirect == true)) {
      header("Location: ".APP_RELATIVE_URL."workflow/end.php?".$querystr);
      exit;
    }
  }

  /**
   * Set the pid of the record created as part of this workflow
   */
  function setCreatedPid($pid)
  {
    // commented out assigned the parent pid to the pid of object in question 
    // cause why would you ever want to do this? - CK 23/10/2008
    /*		
    if (empty($this->parent_pid)) {
      $this->parent_pid = $pid;
    }
    */
    $this->pid = $pid;
  }

  /**
   * Set the extended PREMIS logging detail. This will be used when writing 
   * to the event log.
   */
  function setHistoryDetail($detail = null)
  {
    if (is_null($detail) || $detail == '') {
      $this->extra_history_detail = null;
    } else {
      $this->extra_history_detail = $detail;
    }
  }

  /**
   * When the current page refreshes, the workflow state should go to a new 
   * state. This is used when the workflow form has done soemthing in a popup 
   * and needs to make the workflow progress.  When the popup finishes, it 
   * refreshes the main window and the workflow state changes.
   */
  function setStateChangeOnRefresh($end=false)
  {
    $this->change_on_refresh = true;
    if ($end) {
      $this->end_on_refresh = true;
    }
    $this->setSession();
  }

  /**
   * Check if a button to move to the next state has been clicked or in the 
   * case of a refresh, whether there is to be a state change on refresh.
   * This method causes the next state to run.
   */
  function checkStateChange($ispopup=false)
  {
    $button = Misc::GETorPOST_prefix('workflow_button_');
    if ($button) {
      $this->getStateDetails();
      if ($button != -1) {
        $this->setState($button);
        if (!$ispopup) {
          $this->run();
        } else {
          $this->setStateChangeOnRefresh();
        }
      } else {
        // have reached the end of the workflow
        if (!$ispopup) {
          $this->theend();
        } else {
          $this->setStateChangeOnRefresh(true);
        }
      }
    } else {
      $this->getStateDetails();
      if ($this->change_on_refresh) {
        $this->change_on_refresh = false;
        if ($this->end_on_refresh) {
          $this->end_on_refresh = false;
          $this->theend();
        } else {
          $this->run();
        }
      }
    }
  }

  /**
   * Gets the list of next states as a list of wfs_id and label pairs.  
   * The list is used to make buttons that will allow the user to choose 
   * the next state in the workflow
   */
  function getButtons()
  {
    $this->getStateDetails();
    $this->getWorkflowDetails();
    $next_states = Workflow_State::getDetailsNext($this->wfs_id);
    $button_list = array();

    // If this is a create form, there is no pid yet, so use the parent pids 
    // security runs for testing what states can be entered, and therefore 
    // which buttons can be shown (eg publish button, approver only).
    if (empty($this->pid)) {
      $auth_pid = $this->parent_pid;
    } else {
      $auth_pid = $this->pid;
    }

    foreach ($next_states as $next) {
      if (Workflow_State::canEnter($next['wfs_id'], $auth_pid)) {
        // transparent states are hidden from the user so we make the button
        // have the text of the next non-transparent state.  Only auto states 
        // can be transparent.
        if ($next['wfs_auto'] && $next['wfs_transparent']) {
          $next2 = $next;
          while (
              !$next2['wfs_end'] == 1 
              && $next2['wfs_auto'] == 1 
              && $next2['wfs_transparent'] == 1
          ) {
            $next2_list = Workflow_State::getDetailsNext($next2['wfs_id']);
            // this list should only have one item since an auto state can only
            // have one next state
            $next2 = $next2_list[0];
          }
          if (
              $next2['wfs_end'] == 1 
              && $next2['wfs_auto'] == 1 
              && $next2['wfs_transparent'] == 1
          ) {
            $title = $this->wfl_details['wfl_end_button_label'];
          } else {
            $title = $next2['wfs_title'];
          }
          // note the wfs_id is that of the transparent state - only the title
          // is treated differently
          $button_list[$next['wfs_display_order']][] = array(
                            'wfs_id' => $next['wfs_id'],
                            'wfs_title' => $title
          );
        } else {
          $button_list[$next['wfs_display_order']][] = array(
                            'wfs_id' => $next['wfs_id'],
                            'wfs_title' => $next['wfs_title']
          );
        }
      }
    }
    if ($this->wfs_details['wfs_end'] == 1) {
      $button_list[999999][] = array(
                    'wfs_id' => -1,
                    'wfs_title' => $this->wfl_details['wfl_end_button_label']
      );
    }

    // sort the button list to give priority to display order items
    ksort($button_list, SORT_NUMERIC);
    $returnButtonList = array();
    foreach ($button_list as $priority => $buttons) {
      foreach ($buttons as $button) {
        $returnButtonList[] = $button;
      }
    }

    return $returnButtonList;
  }

  /**
   * Get the display id for the record currently being worked on
   */
  function getXDIS_ID()
  {
    if (!$this->xdis_id) {
      $this->getRecordObject();
      $this->xdis_id = $this->rec_obj->getXmlDisplayId();
    }
    return $this->xdis_id;
  }

  /**
   * Assign a workflow variable.  This is a mechanism for saving variables
   * in one part of a workflow to be used in another.  The variables are saved
   * in the workflow which persists through the session.
   */
  function assign($name, $value)
  {
    $this->vars[$name] = $value;
  }

  /**
   * Retrieve a variable set with the assign method.
   */
  function getVar($name)
  {
    return @$this->vars[$name];
  }

  /**
   * Set a standard set of variables used by the workflow template. 
   * This includes the next state buttons progress lists.
   */
  function setTemplateVars(&$tpl)
  {

    $tpl->assign("id", $this->id);
    $tpl->assign("custom_view_pid", $this->custom_view_pid);
    $tpl->assign('workflow_buttons', $this->getButtons());
    $this->getWorkflowDetails();
    $tpl->assign('wfl_title', $this->wfl_details['wfl_title']);
    $tpl->assign('states_done', $this->states_done);
    $this->getStateDetails();
    $tpl->assign('wfs_title', $this->wfs_details['wfs_title']);
    $tpl->assign('wfs_description', $this->wfs_details['wfs_description']);
    $tpl->assign('href', $this->href);
  }

  function setNewWorkflow($new_wft_id)
  {
    $this->wft_id= $new_wft_id;
    $this->wfs_id = null;
    $this->wfs_details = null;
    $this->wfl_details = null;
    $this->wfb_details = null;
    $this->wft_details = null;
  }

}

/**
 * Manages the instantiation of a worflow status from the session
 */
class WorkflowStatusStatic
{
  /**
   * Get an instance of a workflow runtime from the session.
   * @param integer $id The workflow id to be retrieved.
   * @param integer $usr_id The id of the user
   * @param integer $wfs_id The workflow state to go to (e.g. when linking link 
   *                         to a previous workflow step)
   * @return object WorkflowStatus object.
   */
  function getSession($id = null, $usr_id = null, $wfs_id = null)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($id)) {
      $id = Misc::GETorPOST('id');
    }
    if (empty($usr_id)) {
      $usr_id = Auth::getUserID();
    }
    if (empty($wfs_id)) {
      $wfs_id = Misc::GETorPOST('wfs_id');
    }
    $obj = null;
    $dbtp =  APP_TABLE_PREFIX;
    $stmt = "SELECT wfses_object FROM ".$dbtp."workflow_sessions " .
            "WHERE wfses_usr_id=".$db->quote($usr_id, 'INTEGER').
            " AND wfses_id=".$db->quote($id, 'INTEGER');
    try {
      $log->debug($stmt);
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return null;
    }
    if (empty($res)) {
      return null;
    }
    $obj = unserialize($res);
    if (!is_object($obj) || get_class($obj) != 'WorkflowStatus' ) {
      $log->err(
          "Workflow object is corrupt. get_class: ".get_class($obj).
          "print_r: ".print_r($obj, true), __FILE__, __LINE__
      );
      return null;
    }
    if (!$obj->change_on_refresh && !empty($wfs_id)) {
      $obj->setState($wfs_id);
    }
    if (is_null($_GET['custom_view_pid'])) {
      $_GET['custom_view_pid'] = $obj->custom_view_pid;
    }
    if (is_null($_REQUEST['custom_view_pid'])) {
      $_REQUEST['custom_view_pid'] = $obj->custom_view_pid;
    }

    $obj->setHistoryDetail(trim(@$_POST['edit_reason']));

    return $obj;
  }

  function getList($usr_id = null)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX;
    if (!empty($usr_id)) {
      $where_user = "wfses_usr_id=".$db->quote($usr_id, 'INTEGER');
    } else {
      $where_user = '1';
    }
    $stmt = "SELECT wfses_id,wfses_date, wfses_listing FROM ".$dbtp.
            "workflow_sessions "."WHERE ".$where_user .
            " ORDER BY wfses_id ASC ";  
    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return array();
    }

    // format the date output
    foreach ($res as $key => $item) {
      $res[$key]['wfses_date'] = date(
          'D, d M Y, H:i:s', strtotime($item['wfses_date'])
      );
    }
    return $res;
  }

  /**
   * Gets a count of the number of outstanding workflows for the specified pid
   *
   * @param string $pid
   * @return integer
   **/
  public function getCountForPid($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $details = self::getWorkflowDetailsForPid($pid);
    return count($details);
  }

  /**
   * Gets details of the various workflows that are currently outstanding on 
   * this pid
   *
   * @return void
   **/
  public function getWorkflowDetailsForPid($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $timeout = APP_SESSION_TIMEOUT;
    $workflowTimeout = 60;
    $prefix = APP_TABLE_PREFIX;
		$pid = "$pid"; //cast to string
    $q = "SELECT wfses_id AS workflowId, wfses_listing AS workflowTitle, ".
          "wfses_date AS dateStarted, usr_full_name AS username, sess.updated ".
          "AS sessionLastUpdated ".
          "FROM {$prefix}workflow_sessions ".
          "JOIN {$prefix}user ON (wfses_usr_id = usr_id) ".
          "JOIN {$prefix}sessions AS sess ON (sess.user_id = wfses_usr_id AND ".
          "sess.session_ip IS NOT NULL) ";
          // only users with a current session
					if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) { //eg if postgresql etc
						$q .= "WHERE (sess.updated + INTERVAL '{$timeout} seconds') > ".
	          "NOW() ". 
	          // and the workflow was started less than an hour ago
	          "AND (wfses_date + INTERVAL '{$workflowTimeout} minutes') > ".
	          "NOW() ";
					} else {
						$q .= "WHERE DATE_ADD(sess.updated, INTERVAL {$timeout} SECOND) > ".
	          "NOW() ". 
	          // and the workflow was started less than an hour ago
	          "AND DATE_ADD(wfses_date, INTERVAL {$workflowTimeout} MINUTE) > ".
	          "NOW() ";
					}
          // find only for the specified pid
				$q .= 
          "AND wfses_pid = ? ";

    $results = $db->fetchAll($q, $pid);
    return $results;
  }

  /**
   * Removes the workflow session for current user with passed id.
   * @param integer $id - database id of workflow session
   * @param integer $usr_id - database id of user
   * @return integer 1 for success, -1 for failure.
   */
  function remove($id = null, $usr_id = null)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp =  APP_TABLE_PREFIX;
    if (empty($id)) {
      $id = Misc::GETorPOST('id');
    }
    if (empty($usr_id)) {
      $usr_id = Auth::getUserID();
    }
    $stmt = "DELETE FROM ".$dbtp."workflow_sessions " .
                "WHERE wfses_usr_id=".$db->quote($usr_id, 'INTEGER').
                "  AND wfses_id=".$db->quote($id, 'INTEGER');
    try {
      $db->query($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return -1;
    }
    return 1;
  }
}
