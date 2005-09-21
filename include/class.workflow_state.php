<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004 MySQL AB                                    |
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
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: s.class.reminder_action.php 1.2 04/01/19 15:15:25-00:00 jpradomaia $
//


/**
 * Class to handle the business logic related to the reminder emails
 * that the system sends out.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
//include_once(APP_INC_PATH . "class.reminder_condition.php");
//include_once(APP_INC_PATH . "class.notification.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.mail.php");
//include_once(APP_INC_PATH . "class.issue.php");
//include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.workflow_state_link.php");

class Workflow_State
{
 
    /**
     * Method used to get the details for a specific reminder action.
     *
     * @access  public
     * @param   integer $wfe_id The reminder action ID
     * @return  array The details for the specified reminder action
     */
    function getDetails($wfs_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state 
                 WHERE
                    wfs_id=$wfs_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            $res['next_ids'] = WorkflowStateLink::getListNext($wfs_id);
            $res['prev_ids'] = WorkflowStateLink::getListPrev($wfs_id);
            return $res;
        }
    }


    /**
     * Method used to create a new reminder action.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 or -2 otherwise
     */
    function insert()
    {
        global $HTTP_POST_VARS;
        $wfs_auto = Misc::checkBox(@$HTTP_POST_VARS['wfs_auto']);
        $wfs_wfb_id = $wfs_auto ? $HTTP_POST_VARS['wfs_wfb_id'] : $HTTP_POST_VARS['wfs_wfb_id2'];

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state
                 (
                    wfs_wfl_id,
                    wfs_title,
                    wfs_description,
                    wfs_auto,
                    wfs_wfb_id,
                    wfs_assigned_role_id,
                    wfs_start,
                    wfs_end
                 ) VALUES (
                    '" . $HTTP_POST_VARS['wfs_wfl_id'] . "',
                    '" . Misc::escapeString($HTTP_POST_VARS['wfs_title']) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS['wfs_description']) . "',
                    '$wfs_auto',
                    '$wfs_wfb_id',
                    '" . $HTTP_POST_VARS['wfs_assigned_role_id'] . "',
                    '" . Misc::checkBox(@$HTTP_POST_VARS['wfs_start']) . "',
                    '" . Misc::checkBox(@$HTTP_POST_VARS['wfs_end']) . "'
                 )";		
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return WorkflowStateLink::insertPost(mysql_insert_id());
        }
    }


    /**
     * Method used to update the details of a specific reminder action.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 or -2 otherwise
     */
    function update()
    {
        global $HTTP_POST_VARS;
        $wfs_auto = Misc::checkBox(@$HTTP_POST_VARS['wfs_auto']);
        $wfs_wfb_id = $wfs_auto ? $HTTP_POST_VARS['wfs_wfb_id'] : $HTTP_POST_VARS['wfs_wfb_id2'];

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state
                 SET
                    wfs_title='" . Misc::escapeString($HTTP_POST_VARS['wfs_title']) . "',
                    wfs_description='" . Misc::escapeString($HTTP_POST_VARS['wfs_description']) . "',
                    wfs_auto='$wfs_auto',
                    wfs_wfb_id='$wfs_wfb_id',
                    wfs_assigned_role_id='" . $HTTP_POST_VARS['wfs_assigned_role_id'] . "',
                    wfs_start='".Misc::checkBox(@$HTTP_POST_VARS['wfs_start'])."',
                    wfs_end='".Misc::checkBox(@$HTTP_POST_VARS['wfs_end'])."'
                 WHERE
                    wfs_id=" . $HTTP_POST_VARS['id'];
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return WorkflowStateLink::updatePost();
        }
    }

    /**
     * Method used to remove a given list of custom fields.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state
                 WHERE
                    wfs_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return WorkflowStateLink::removePost();
        }
    }

   /**
     * Method used to get the list of reminder actions associated with a given
     * reminder ID.
     *
     * @access  public
     * @param   integer $reminder_id The reminder ID
     * @return  array The list of reminder actions
     */
    function getList($wfl_id, $andstr='')
    {
        if ($wfl_id) {
            $wherestr = " wfs_wfl_id=$wfl_id ";
        } else {
            $wherestr = " 1 ";
        }

        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_state AS ws
                    LEFT JOIN ".APP_DEFAULT_DB.".".APP_TABLE_PREFIX."wfbehaviour AS wb ON ws.wfs_wfb_id=wb.wfb_id
                 WHERE
                     $wherestr $andstr 
                    ";

        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            if (empty($res)) {
                return array();
            } else {
                // get all the links from these to others
                $nexts = WorkflowStateLink::getNextByWkFlow($wfl_id);
                $prevs = WorkflowStateLink::getPrevByWkFlow($wfl_id);
                foreach ($res as &$row) {
                    // items that come from this id
                    $row['next_ids'] = @$nexts[$row['wfs_id']];
                    // items that go to this id
                    $row['prev_ids'] = @$prevs[$row['wfs_id']];
                    $row['wfs_assigned_role'] = Auth::getDefaultRoleName($row['wfs_assigned_role_id']);
                }
                return $res;
            }
        }
    }

    function getStartState($wfl_id)
    {
        $states = Workflow_State::getList($wfl_id, " AND wfs_start=1 ");
        return $states[0];
    }

    function getDetailsNext($wfs_id)
    {
        $ids = WorkflowStateLink::getListNext($wfs_id);
        $ids_str = implode(',', $ids);
        $states = Workflow_State::getList(null, " AND wfs_id IN ($ids_str) ");
        return $states;
    }



}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Reminder_Action Class');
}
?>
