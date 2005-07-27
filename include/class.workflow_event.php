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
include_once(APP_INC_PATH . "class.workflow_event_action.php");
include_once(APP_INC_PATH . "class.mail.php");
//include_once(APP_INC_PATH . "class.issue.php");
//include_once(APP_INC_PATH . "class.validation.php");

class Workflow_Event
{
    // XXX: put documentation here
    function changeRank($wfe_id, $rank_type)
    {
        // check if the current rank is not already the first or last one
        $ranking = Reminder_Action::getRanking();
        $ranks = array_values($ranking);
        $ids = array_keys($ranking);
        $last = end($ids);
        $first = reset($ids);
        if ((($rank_type == 'asc') && ($wfe_id == $first)) ||
                (($rank_type == 'desc') && ($wfe_id == $last))) {
            return false;
        }

        if ($rank_type == 'asc') {
            $diff = -1;
        } else {
            $diff = 1;
        }
        $new_rank = $ranking[$wfe_id] + $diff;
        if (in_array($new_rank, $ranks)) {
            // switch the rankings here...
            $index = array_search($new_rank, $ranks);
            $replaced_wfe_id = $ids[$index];
            $stmt = "UPDATE
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action
                     SET
                        wfe_rank=" . $ranking[$wfe_id] . "
                     WHERE
                        wfe_id=" . $replaced_wfe_id;
            $GLOBALS["db_api"]->dbh->query($stmt);
        }
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action
                 SET
                    wfe_rank=" . $new_rank . "
                 WHERE
                    wfe_id=" . $wfe_id;
        $GLOBALS["db_api"]->dbh->query($stmt);
    }


    // XXX: put documentation here
    function getRanking()
    {
        $stmt = "SELECT
                    wfe_id,
                    wfe_rank
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action
                 ORDER BY
                    wfe_rank ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the title of a specific reminder action.
     *
     * @access  public
     * @param   integer $wfe_id The reminder action ID
     * @return  string The title of the reminder action
     */
    function getTitle($wfe_id)
    {
        $stmt = "SELECT
                    (concat(wet_title,' - ',wec_title))
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_event left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_event_condition on (wfe_wec_id = wec_id) left join
	                " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_event_type on (wfe_wet_id = wet_id)
                 WHERE
                    wfe_id=$wfe_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the details for a specific reminder action.
     *
     * @access  public
     * @param   integer $wfe_id The reminder action ID
     * @return  array The details for the specified reminder action
     */
    function getDetails($wfe_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_event
                 WHERE
                    wfe_id=$wfe_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
/*            // get the user list, if appropriate
            if (Reminder_Action::isUserList($res['wfe_rmt_id'])) {
                $res['user_list'] = Reminder_Action::getUserList($res['wfe_id']);
            } */
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

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_event
                 (
                    wfe_wfl_id,
                    wfe_wet_id,
                    wfe_wec_id,
                    wfe_created_date,
                    wfe_rank
                 ) VALUES (
                    " . $HTTP_POST_VARS['wfl_id'] . ",
                    " . $HTTP_POST_VARS['wfe_wet_id'] . ",
                    " . $HTTP_POST_VARS['wfe_wec_id'] . ",
                    '" . Date_API::getCurrentDateGMT() . "',
                    '" . $HTTP_POST_VARS['wfe_rank'] . "'
                 )";		
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
/*            $new_wfe_id = $GLOBALS["db_api"]->get_last_insert_id();
            // add the user list, if appropriate
            if (Reminder_Action::isUserList($HTTP_POST_VARS['type'])) {
                Reminder_Action::associateUserList($new_wfe_id, $HTTP_POST_VARS['user_list']);
            } */
            return 1;
        }
    }


  /**
     * Method used to create a new reminder action.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 or -2 otherwise
     */
    function insertFromCreateIssue($rem_id, $title)
    {
        global $HTTP_POST_VARS;

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action
                 (
                    wfe_rem_id,
                    wfe_rmt_id,
                    wfe_created_date,
                    wfe_title,
                    wfe_rank
                 ) VALUES (
                    " . $rem_id . ",
                    1,
                    '" . Date_API::getCurrentDateGMT() . "',
                    '" . $title . "',
                    '1'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            $new_wfe_id = $GLOBALS["db_api"]->get_last_insert_id();
            // for these cases no need to add user list as we are emailing all the assignees
            return 1;
        }
    }


    // XXX: put documentation here
    function getUserList($wfe_id)
    {
        $stmt = "SELECT
                    ral_usr_id,
                    ral_email
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action_list
                 WHERE
                    ral_wfe_id=$wfe_id";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            $t = array();
            for ($i = 0; $i < count($res); $i++) {
                if (Validation::isEmail($res[$i]['ral_email'])) {
                    $t[$res[$i]['ral_email']] = $res[$i]['ral_email'];
                } else {
                    $t[$res[$i]['ral_usr_id']] = User::getFullName($res[$i]['ral_usr_id']);
                }
            }
            return $t;
        }
    }


    // XXX: put documentation here
    function associateUserList($wfe_id, $user_list)
    {
        for ($i = 0; $i < count($user_list); $i++) {
            $usr_id = 0;
            $email = '';
            if (!Validation::isEmail($user_list[$i])) {
                $usr_id = $user_list[$i];
            } else {
                $email = $user_list[$i];
            }
            $stmt = "INSERT INTO
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action_list
                     (
                        ral_wfe_id,
                        ral_usr_id,
                        ral_email
                     ) VALUES (
                        $wfe_id,
                        $usr_id,
                        '" . Misc::escapeString($email) . "'
                     )";
            $GLOBALS["db_api"]->dbh->query($stmt);
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

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_event
                 SET
                    wfe_last_updated_date='" . Date_API::getCurrentDateGMT() . "',
                    wfe_rank='" . $HTTP_POST_VARS['rank'] . "',
                    wfe_title='" . Misc::escapeString($HTTP_POST_VARS['title']) . "',
                    wfe_wet_id=" . $HTTP_POST_VARS['wfe_wet_id'] . ",
                    wfe_wec_id=" . $HTTP_POST_VARS['wfe_wec_id'] . "
                 WHERE
                    wfe_id=" . $HTTP_POST_VARS['id'];
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
/*            // remove any user list associated with this reminder action
            Reminder_Action::clearActionUserList($HTTP_POST_VARS['id']);
            // add the user list back in, if appropriate
            if (Reminder_Action::isUserList($HTTP_POST_VARS['type'])) {
                Reminder_Action::associateUserList($HTTP_POST_VARS['id'], $HTTP_POST_VARS['user_list']);
            } */
            return 1;
        }
    }


    // XXX: put documentation here
    function isUserList($rmt_id)
    {
        $stmt = "SELECT
                    rmt_type
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action_type
                 WHERE
                    rmt_id=$rmt_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            $user_list_types = array(
                'sms_list',
                'email_list'
            );
            if (!in_array($res, $user_list_types)) {
                return false;
            } else {
                return true;
            }
        }
    }


    // XXX: put documentation here
    function clearActionUserList($wfe_id)
    {
        if (!is_array($wfe_id)) {
            $wfe_id = array($wfe_id);
        }
        $items = @implode(", ", $wfe_id);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action_list
                 WHERE
                    ral_wfe_id IN ($items)";
        $GLOBALS["db_api"]->dbh->query($stmt);
    }


    /**
     * Method used to remove reminder actions by using the administrative
     * interface of the system.
     *
     * @access  public
     * @return  boolean
     */
    function remove($action_ids)
    {
        $items = @implode(", ", $action_ids);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action
                 WHERE
                    wfe_id IN ($items)";
        $GLOBALS["db_api"]->dbh->query($stmt);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_history
                 WHERE
                    rmh_wfe_id IN ($items)";
        $GLOBALS["db_api"]->dbh->query($stmt);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_level_condition
                 WHERE
                    rlc_wfe_id IN ($items)";
        $GLOBALS["db_api"]->dbh->query($stmt);
        Reminder_Action::clearActionUserList($action_ids);
    }


    /**
     * Method used to get an associative array of action types.
     *
     * @access  public
     * @return  array The list of action types
     */
    function getEventTypeList()
    {
        $stmt = "SELECT
                    wet_id,
                    wet_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_event_type
                 ORDER BY
                    wet_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }
    }

    /**
     * Method used to get an associative array of action types.
     *
     * @access  public
     * @return  array The list of action types
     */
    function getEventConditionList()
    {
        $stmt = "SELECT
                    wec_id,
                    wec_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_event_condition
                 ORDER BY
                    wec_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of reminder actions to be displayed in the 
     * administration section.
     *
     * @access  public
     * @param   integer $rem_id The reminder ID
     * @return  array The list of reminder actions
     */
    function getAdminList($rem_id)
    {
        $stmt = "SELECT
                    wfe_rem_id,
                    wfe_id,
                    wfe_title,
                    rmt_title,
                    wfe_rank
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action_type
                 WHERE
                    wfe_rmt_id=rmt_id AND
                    wfe_rem_id=$rem_id
                 ORDER BY
                    wfe_rank ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            for ($i = 0; $i < count($res); $i++) {
                $res[$i]['total_conditions'] = count(Reminder_Condition::getList($res[$i]['wfe_id']));
            }
            return $res;
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
    function getList($wfl_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_event left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_event_condition on (wfe_wec_id = wec_id) left join
	                " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow_event_type on (wfe_wet_id = wet_id)				
                 WHERE
                    wfe_wfl_id=$wfl_id 
                 ORDER BY
                    wfe_rank ASC";

        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            if (empty($res)) {
                return array();
            } else {
                $t = array();
                for ($i = 0; $i < count($res); $i++) {
                    // ignore workflow templates that have no events yet...
					$res[$i]['wfe_created_date'] = Date_API::getFormattedDate($res[$i]["wfe_created_date"]);
                    $actions = Workflow_Event_Action::getList($res[$i]['wfe_id']);
					$res[$i]['total_actions'] = count($actions);

                    if (count($actions) == 0) {
	                    $t[] = $res[$i];
                        continue;
                    }
                    $res[$i]['actions'] = $actions;
                    $t[] = $res[$i];
                }
                return $t;
            }
        }
    }


    /**
     * Method used to get the title of a reminder action type.
     *
     * @access  public
     * @param   integer $rmt_id The reminder action type
     * @return  string The action type title
     */
    function getActionType($rmt_id)
    {
        $stmt = "SELECT
                    rmt_type
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_action_type
                 WHERE
                    rmt_id=$rmt_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }


    /**
     * Method used to save a history entry about the execution of the current
     * reminder.
     *
     * @access  public
     * @param   integer $issue_id The issue ID
     * @param   integer $wfe_id The reminder action ID
     * @return  boolean
     */
    function saveHistory($issue_id, $wfe_id)
    {
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "reminder_history
                 (
                    rmh_iss_id,
                    rmh_wfe_id,
                    rmh_created_date
                 ) VALUES (
                    $issue_id,
                    $wfe_id,
                    '" . Date_API::getCurrentDateGMT() . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }


    /**
     * Method used to perform a specific action to an issue.
     *
     * @access  public
     * @param   integer $issue_id The issue ID
     * @param   array $reminder The reminder details
     * @param   array $action The action details
     * @return  boolean
     */
    function perform($issue_id, $reminder, $action)
    {
        $type = '';
        // - see which action type we're talking about here...
        $action_type = Reminder_Action::getActionType($action['wfe_rmt_id']);
        if (Reminder::isDebug()) {
           echo "  - Performing action '$action_type' for issue #$issue_id\n";
        }
        switch ($action_type) {
            case 'email_assignee':
                $type = 'email';
                $assignees = Issue::getAssignedUserIDs($issue_id);
                $to = array();
                foreach ($assignees as $assignee) {
                    $to[] = User::getFromHeader($assignee);
                }
                // if there are no recipients, then just skip to the next action
                if (count($to) == 0) {
                    if (Reminder::isDebug()) {
                        echo "  - No assigned users could be found\n";
                    }
                    return false;
                }
                break;
            case 'email_list':
                $type = 'email';
                $list = Reminder_Action::getUserList($action['wfe_id']);
                $to = array();
                foreach ($list as $key => $value) {
                    if (Validation::isEmail($key)) {
                        $to[] = $key;
                    } else {
                        $to[] = User::getFromHeader($key);
                    }
                }
                break;
            case 'email_leader':
                $type = 'email';
                $proj = Project::getDetails($reminder['rem_prj_id']);
                $to = array(User::getFromHeader($proj['prj_lead_usr_id']));
                break;
            case 'sms_assignee':
                $type = 'sms';
                $assignees = Issue::getAssignedUserIDs($issue_id);
                $to = array();
                foreach ($assignees as $assignee) {
                    $sms_email = User::getSMS($assignee);
                    if (!empty($sms_email)) {
                        $to[] = $sms_email;
                    }
                }
                // if there are no recipients, then just skip to the next action
                if (count($to) == 0) {
                    if (Reminder::isDebug()) {
                        echo "  - No assigned users with SMS email address could be found\n";
                    }
                    return false;
                }
                break;
            case 'sms_list':
                $type = 'sms';
                $list = Reminder_Action::getUserList($action['wfe_id']);
                $to = array();
                foreach ($list as $key => $value) {
                    if (Validation::isEmail($key)) {
                        $to[] = $key;
                    } else {
                        $sms_email = User::getSMS($key);
                        if (!empty($sms_email)) {
                            $to[] = $sms_email;
                        }
                    }
                }
                // if there are no recipients, then just skip to the next action
                if (count($to) == 0) {
                    if (Reminder::isDebug()) {
                        echo "  - No assigned users with SMS email address could be found\n";
                    }
                    return false;
                }
                break;
        }
        // - save a history entry about this action
        Reminder_Action::saveHistory($issue_id, $action['wfe_id']);

        $conditions = Reminder_Condition::getAdminList($action['wfe_id']);
        // - perform the action
        if ($type == 'email') {
            $tpl = new Template_API;
            $tpl->setTemplate('reminders/email_alert.tpl.text');
            $tpl->bulkAssign(array(
                "data"         => Notification::getIssueDetails($issue_id),
                "reminder"     => $reminder,
                "conditions"   => $conditions
            ));
            $text_message = $tpl->getTemplateContents();

            foreach ($to as $address) {
                // send email (use PEAR's classes)
                $mail = new Mail_API;
                $mail->setTextBody($text_message);
                $setup = $mail->getSMTPSettings();
                $mail->send($setup["from"], $address, APP_SHORT_NAME . ": Reminder Alert for Issue #$issue_id");
            }
        } elseif ($type == 'sms') {
            $tpl = new Template_API;
            $tpl->setTemplate('reminders/sms_alert.tpl.text');
            $tpl->bulkAssign(array(
                "data"         => Notification::getIssueDetails($issue_id),
                "reminder"     => $reminder,
                "conditions"   => $conditions
            ));
            $text_message = $tpl->getTemplateContents();

            foreach ($to as $address) {
                // send email (use PEAR's classes)
                $mail = new Mail_API;
                $mail->setTextBody($text_message);
                $setup = $mail->getSMTPSettings();
                $mail->send($setup["from"], $address, "Reminder Alert for Issue #$issue_id");
            }
        }
        // - eventum saves the day once again
        return true;
    }
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Reminder_Action Class');
}
?>
