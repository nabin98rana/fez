<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Record Tracking System                                      |
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
// @(#) $Id: s.class.Record.php 1.114 04/01/19 15:15:25-00:00 jpradomaia $
//


/**
 * Class designed to handle all business logic related to the Records in the
 * system, such as adding or updating them or listing them in the grid mode.
 *
 * @author  João Prado Maia <jpm@mysql.com>
 * @version $Revision: 1.114 $
 */

include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");


//@@@ CK - 28/10/2004 - Modified the list headings to be like the actual list headings so the CSV would show the same thing
$list_headings = array(
    'Date of Issue',
    'Title',
    'Authors'
);

/**
  * Record
  * Static class for accessing record related queries
  * See RecordObject for an object oriented representation of a record.
  */
class Record
{


    function getIndexParents($pid)
    {

        $stmt = "SELECT
                    * 
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
                 WHERE
				    r1.rmf_xsdmf_id = x1.xsdmf_id and 
                    rmf_rec_pid in (
						SELECT r2.rmf_varchar 
						FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
						WHERE (rmf_xsdmf_id = 91 AND rmf_varchar = '2' AND r2.rmf_rec_pid = '".$pid."') OR
							(rmf_xsdmf_id = 149 AND rmf_varchar = '3' AND r2.rmf_rec_pid = '".$pid."')
						)
					";
//		echo $stmt;			
		$returnfields = array("title", "description", "ret_id", "xdis_id", "sta_id", "Editor", "Creator", "Lister", "Viewer", "Approver", "Community Administrator", "Annotator", "Comment_Viewer", "Commentor");
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        //$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
//		print_r($res);
		$return = array();
		
		foreach ($res as $result) {		
			if (in_array($result['xsdsel_title'], $returnfields) && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) ) {
				if (!is_array($return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']])) {
					$return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']] = array();
				}
				array_push($return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']], $result['rmf_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
			}
			if (in_array($result['xsdmf_fez_title'], $returnfields)) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$return[$result['rmf_rec_pid']][$result['xsdmf_fez_title']] = $result['rmf_'.$result['xsdmf_data_type']];
			}
		}
//		print_r($return);
//		$return = Auth::getIndexAuthorisationGroups($return);
		
		
		//get groups after you get all the acmls? yeah probably
//		$return = array_values($return);
//		$return = Auth::getIndexAuthorisationGroups($return);

		return $return;
		

    }

    function getParents($pid)
    {

		$itql = "select \$collTitle \$collDesc \$title \$description \$object from <#ri>
					where  (<info:fedora/".$pid."> <dc:title> \$collTitle) and
                    (<info:fedora/".$pid."> <dc:description> \$collDesc) and
					(<info:fedora/".$pid."> <fedora-rels-ext:isMemberOf> \$object ) and
					((\$object <dc:title> \$title) or
					(\$object <dc:description> \$description))
					order by \$title asc";

//		echo $itql;
		$returnfields = array();
		array_push($returnfields, "pid"); 
		array_push($returnfields, "title");
		array_push($returnfields, "identifier");
		array_push($returnfields, "description");

		$details = Fedora_API::getITQLQuery($itql, $returnfields);
//		print_r($details);
		return $details;
    }



    /**
     * Method used to get the full list of date fields available to Records, to
     * be used when customizing the Record listing screen in the 'last status
     * change date' column.
     *
     * @access  public
     * @return  array The list of available date fields
     */
    function getDateFieldsAssocList()
    {
        return array(
            'rec_created_date'              => 'Created Date',
            'rec_updated_date'              => 'Last Updated Date',
            'rec_last_response_date'        => 'Last Response Date',
            'rec_closed_date'               => 'Closed Date'
        );
    }


    /**
     * Method used to get the full list of Record IDs and their respective 
     * titles associated to a given project.
     *
     * @access  public
     * @param   integer $col_id The project ID
     * @return  array The list of Records
     */
    function getAssocListByCollection($col_id)
    {
        $stmt = "SELECT
                    rec_id,
                    rec_summary
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_col_id=$col_id
                 ORDER BY
                    rec_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the status of a given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer The status ID
     */
    function getStatusID($record_id)
    {
        static $returns;

        if (!empty($returns[$record_id])) {
            return $returns[$record_id];
        }

        $stmt = "SELECT
                    rec_sta_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            $returns[$record_id] = $res;
            return $res;
        }
    }


    /**
     * Method used to get the status of a given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer The category ID
     */
    function getCategoryID($record_id)
    {
        static $returns;

        if (!empty($returns[$record_id])) {
            return $returns[$record_id];
        }

        $stmt = "SELECT
                    rec_prc_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            $returns[$record_id] = $res;
            return $res;
        }
    }

    /**
     * Method used to get the status of a given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer The category ID
     */
    function getSubCategoryID($record_id)
    {
        static $returns;

        if (!empty($returns[$record_id])) {
            return $returns[$record_id];
        }

        $stmt = "SELECT
                    rec_prsc_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            $returns[$record_id] = $res;
            return $res;
        }
    }


    /**
     * Method used to get the status of a given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer The count of how many Records of the given ID exist (0 or 1)
     */
    function getExistsID($record_id)
    {
        $stmt = "SELECT COUNT(rec_id)
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            $returns[$record_id] = $res;
            return $res;
        }
    }



    /**
     * Method used to set the status of a given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $status_id The new status ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function setStatus($record_id, $status_id)
    {
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_sta_id=$status_id,
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "'
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return 1;
        }
    }

    /**
	 * @@@ CK - Added for escalation support - not needed for any other reason
     * Method used to chnage the project of a given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $status_id The new project ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function setCollection($record_id, $col_id)
    {
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_col_id=$col_id,
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "'
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return 1;
        }
    }



    /**
     * Method used to get the project associated to a given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer The project ID
     */
    function getCollectionID($record_id)
    {
        static $returns;

        if (!empty($returns[$record_id])) {
            return $returns[$record_id];
        }

        $stmt = "SELECT
                    rec_col_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            $returns[$record_id] = $res;
            return $res;
        }
    }


    /**
     * Method used to remotely set a lock to a given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $usr_id The user ID
     * @param   boolean $force_lock Whether we should force the lock or not
     * @return  integer The status ID
     */
    function remoteLock($record_id, $usr_id, $force_lock)
    {
        if ($force_lock != 'yes') {
            // check if the Record is not already locked by somebody else
            $stmt = "SELECT
                        rec_lock_usr_id
                     FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                     WHERE
                        rec_id=$record_id";
            $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            } else {
                if (!empty($res)) {
                    if ($res == $usr_id) {
                        return -2;
                    } else {
                        return -3;
                    }
                }
            }
        }

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "',
                    rec_lock_usr_id=$usr_id
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // clear up the assignments for this Record, and then assign it to the current user
            Record::deleteUserAssociations($record_id, $usr_id);
            Record::addUserAssociation($record_id, $usr_id, false);
            // save a history entry about this...
            History::add($record_id, $usr_id, History::getTypeID('remote_locked'), "Record remotely locked by " . User::getFullName($usr_id));
            Notification::subscribeUser($record_id, $usr_id, Notification::getAllActions(), false);
            return 1;
        }
    }


    /**
     * Method used to remotely remove a lock on a given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $usr_id The user ID
     * @return  integer The status ID
     */
    function remoteUnlock($record_id, $usr_id)
    {
        // check if the Record is not already locked by somebody else
        $stmt = "SELECT
                    rec_lock_usr_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            if (empty($res)) {
                return -2;
            }
        }

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "',
                    rec_lock_usr_id=NULL
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // save a history entry about this...
            History::add($record_id, $usr_id, History::getTypeID('remote_unlock'), "Record remotely unlocked by " . User::getFullName($usr_id));
            return 1;
        }
    }


    /**
     * Method used to remotely assign a given Record to an user.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $usr_id The user ID of the person performing the change
     * @param   boolean $assignee The user ID of the assignee
     * @return  integer The status ID
     */
    function remoteAssign($record_id, $usr_id, $assignee)
    {
        if ($usr_id != $assignee) {
            $current = Record::getDetails($record_id);
            Notification::notifyIRCAssignmentChange($record_id, $usr_id, $current['assigned_users'], array($assignee), true);
        }
        // clear up the assignments for this Record, and then assign it to the current user
        Record::deleteUserAssociations($record_id, $usr_id);
        $res = Record::addUserAssociation($record_id, $assignee, false);
        if ($res != -1) {
            // save a history entry about this...
            History::add($record_id, $usr_id, History::getTypeID('remote_assigned'), "Record remotely assigned to " . User::getFullName($assignee) . " by " . User::getFullName($usr_id));
            Notification::subscribeUser($record_id, $assignee, Notification::getAllActions(), false);
        }
        return $res;
    }


    /**
     * Method used to remotely set the status of a given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $usr_id The user ID of the person performing this change
     * @param   integer $new_status The new status ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function setRemoteStatus($record_id, $usr_id, $new_status)
    {
        $sta_id = Status::getStatusID($new_status);

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_sta_id=$sta_id,
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "'
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // record history entry
            $info = User::getNameEmail($usr_id);
            History::add($record_id, $usr_id, History::getTypeID('remote_status_change'), "Status remotely changed to '$new_status' by " . $info['usr_full_name']);
            return 1;
        }
    }


    /**
     * Method used to get all Records associated with a status that doesn't have
     * the 'closed' context.
     *
     * @access  public
     * @param   integer $col_id The project ID to list Records from
     * @param   integer $email The email address associated with the user requesting this information
     * @param   boolean $show_all_Records Whether to show all open Records, or just the ones assigned to the given email address
     * @param   integer $status_id The status ID to be used to restrict results
     * @return  array The list of open Records
     */
    function getOpenRecords($col_id, $email, $show_all_Records, $status_id)
    {
        $usr_id = User::getUserIDByEmail($email);
        if (empty($usr_id)) {
            return '';
        }
        $collections = Collection::getRemoteAssocListByUser($usr_id);
        if (@count($collections) == 0) {
            return '';
        }

        $stmt = "SELECT
                    rec_id,
                    rec_summary,
                    sta_title,
                    usr_full_name
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "status
                 LEFT JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user
                 ON
                    isu_rec_id=rec_id
                 LEFT JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user
                 ON
                    isu_usr_id=usr_id
                 WHERE ";
        if (!empty($status_id)) {
            $stmt .= " sta_id=$status_id AND ";
        }
        $stmt .= "
                    rec_col_id=$col_id AND
                    sta_id=rec_sta_id AND
                    sta_is_closed=0";
        if ($show_all_Records == false) {
            $stmt .= " AND
                    usr_id=$usr_id";
        }
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }


    /**
     * Method used to build the required parameters to simulate an email reply
     * to the user who reported the Record, using the Record details like summary
     * and description as email fields.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  array The email parameters
     */
    function getReplyDetails($record_id)
    {
        $stmt = "SELECT
                    UNIX_TIMESTAMP(rec_created_date) AS created_date_ts,
                    usr_full_name AS reporter,
                    usr_email AS reporter_email,
                    rec_description AS description,
                    rec_summary AS sup_subject
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user
                 WHERE
                    rec_usr_id=usr_id AND
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            $res['reply_subject'] = 'Re: [#' . $record_id . '] ' . $res["sup_subject"];
            return $res;
        }
    }


    /**
     * Method used to get the user currently locking the given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer The user ID
     */
    function getLockedUserID($record_id)
    {
        $stmt = "SELECT
                    rec_lock_usr_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_id=$record_id";
        return $GLOBALS["db_api"]->dbh->getOne($stmt);
    }


    /**
     * Method used to lock a given Record to a specific user.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $usr_id The user ID
     * @return  boolean
     */
    function lock($record_id, $usr_id)
    {
        $lock_usr_id = Record::getLockedUserID($record_id);
        if (!empty($lock_usr_id)) {
            if ($lock_usr_id == $usr_id) {
                return -3;
            } else {
                return -2;
            }
        }

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "',
                    rec_lock_usr_id=$usr_id
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // clear up the assignments for this Record, and then assign it to the current user
            Record::deleteUserAssociations($record_id, $usr_id);
            Record::addUserAssociation($record_id, $usr_id);
            // save a history entry about this...
            History::add($record_id, $usr_id, History::getTypeID('Record_locked'), "Record locked by " . User::getFullName($usr_id));
            Notification::subscribeUser($record_id, $usr_id, Notification::getAllActions());
            return 1;
        }
    }


    /**
     * Method used to unlock a given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $usr_id The user ID of the person performing this change
     * @return  boolean
     */
    function unlock($record_id, $usr_id)
    {
        $lock_usr_id = Record::getLockedUserID($record_id);
        if (empty($lock_usr_id)) {
            return -2;
        }

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "',
                    rec_lock_usr_id=NULL
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // save a history entry about this...
            History::add($record_id, $usr_id, History::getTypeID('Record_unlocked'), "Record unlocked by " . User::getFullName($usr_id));
            return 1;
        }
    }


    /**
     * Method used to record the last updated timestamp for a given
     * Record ID.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  boolean
     */
    function markAsUpdated($record_id)
    {
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "'
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }


    /**
     * Method used to check whether a given Record has duplicates 
     * or not.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  boolean
     */
    function hasDuplicates($record_id)
    {
        $stmt = "SELECT
                    COUNT(rec_id)
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_duplicated_rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            if ($res == 0) {
                return false;
            } else {
                return true;
            }
        }
    }


    /**
     * Method used to update the duplicated Records for a given 
     * Record ID.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function updateDuplicates($record_id)
    {
        global $HTTP_POST_VARS;
        $ids = Record::getDuplicateList($record_id);
        if ($ids == '') {
            return -1;
        }
        $ids = @array_keys($ids);
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "',
                    rec_prc_id=" . $HTTP_POST_VARS["category"] . ",";
        if (@$HTTP_POST_VARS["keep"] == "no") {
            $stmt .= "rec_pre_id=" . $HTTP_POST_VARS["release"] . ",";
        }
        $stmt .= "
                    rec_pri_id=" . $HTTP_POST_VARS["priority"] . ",
                    rec_sta_id=" . $HTTP_POST_VARS["status"] . ",
                    rec_res_id=" . $HTTP_POST_VARS["resolution"] . "
                 WHERE
                    rec_id IN (" . implode(", ", $ids) . ")";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // record the change
            for ($i = 0; $i < count($ids); $i++) {
                History::add($ids[$i], Auth::getUserID(), History::getTypeID('duplicate_update'), 
                    "The details for Record #$record_id were updated by " . User::getFullName(Auth::getUserID()) . " and the changes propagated to the duplicated Records.");
            }
            return 1;
        }
    }


    /**
     * Method used to get a list of the duplicate Records for a given 
     * Record ID.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  array The list of duplicates
     */
    function getDuplicateList($record_id)
    {
        $stmt = "SELECT
                    rec_id,
                    rec_summary
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_duplicated_rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            if (@count($res) == 0) {
                return '';
            } else {
                return $res;
            }
        }
    }


    /**
     * Method used to clear the duplicate status of an Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function clearDuplicateStatus($record_id)
    {
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "',
                    rec_duplicated_rec_id=NULL
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // record the change
            History::add($record_id, Auth::getUserID(), History::getTypeID('duplicate_removed'), "Duplicate flag was reset by " . User::getFullName(Auth::getUserID()));
            return 1;
        }
    }


    /**
     * Method used to mark an Record as a duplicate of an existing one.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function markAsDuplicate($record_id)
    {
        global $HTTP_POST_VARS;

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "',
                    rec_duplicated_rec_id=" . $HTTP_POST_VARS["duplicated_Record"] . "
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            if (!empty($HTTP_POST_VARS["comments"])) {
                // add note with the comments of marking an Record as a duplicate of another one
                $HTTP_POST_VARS['title'] = 'Record duplication comments';
                $HTTP_POST_VARS["note"] = $HTTP_POST_VARS["comments"];
                Note::insert(Auth::getUserID(), $record_id);
            }
            // record the change
            History::add($record_id, Auth::getUserID(), History::getTypeID('duplicate_added'), 
                    "Record marked as a duplicate of Record #" . $HTTP_POST_VARS["duplicated_Record"] . " by " . User::getFullName(Auth::getUserID()));
            return 1;
        }
    }


    /**
     * Method used to get an associative array of user ID => user 
     * status associated with a given Record ID.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  array The list of users
     */
    function getAssignedUsersStatus($record_id)
    {
        $stmt = "SELECT
                    usr_id,
                    usr_status
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user
                 WHERE
                    isu_rec_id=$record_id AND
                    isu_usr_id=usr_id";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the list of Records as an associative array
     * associated with a given release.
     *
     * @access  public
     * @param   integer $pre_id The release ID
     * @return  array The list of Records
     */
    function getAssocListByRelease($pre_id)
    {
        $stmt = "SELECT
                    rec_id,
                    rec_summary
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_col_id=" . Auth::getCurrentCollection() . " AND
                    rec_pre_id=$pre_id
                 ORDER BY
                    rec_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the summary associated with a given Record ID.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  string The Record summary
     */
    function getTitle($record_id)
    {
        $stmt = "SELECT
                    rec_summary
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the Record ID associated with a specific summary.
     *
     * @access  public
     * @param   string $summary The summary to look for
     * @return  integer The Record ID
     */
    function getRecordID($summary)
    {
        $stmt = "SELECT
                    rec_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_summary='" . Misc::escapeString($summary) . "'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return 0;
        } else {
            if (empty($res)) {
                return 0;
            } else {
                return $res;
            }
        }
    }


    /**
     * Method used to add a new anonymous based Record in the system.
     *
     * @access  public
     * @return  integer The new Record ID
     */
    function addAnonymousReport()
    {
        global $HTTP_POST_VARS, $HTTP_POST_FILES;

        $options = Collection::getAnonymousPostOptions($HTTP_POST_VARS["project"]);
        $initial_status = Collection::getInitialStatus($HTTP_POST_VARS["project"]);
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 (
                    rec_col_id,
                    rec_prc_id,
                    rec_pre_id,
                    rec_pri_id,
                    rec_usr_id,";
        if (!empty($initial_status)) {
            $stmt .= "rec_sta_id,";
        }
        $stmt .= "
                    rec_created_date,
                    rec_summary,
                    rec_description
                 ) VALUES (
                    " . $HTTP_POST_VARS["project"] . ",
                    " . $options["category"] . ",
                    0,
                    " . $options["priority"] . ",
                    " . $options["reporter"] . ",";
        if (!empty($initial_status)) {
            $stmt .= "$initial_status,";
        }
        $stmt .= "
                    '" . Date_API::getCurrentDateGMT() . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["summary"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["description"]) . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return $res;
        } else {
            $new_Record_id = $GLOBALS["db_api"]->get_last_insert_id();
            // log the creation of the Record
            History::add($new_Record_id, APP_SYSTEM_USER_ID, History::getTypeID('Record_opened_anon'), 'Record opened anonymously');
            // now add the user/Record association
            $assign = array();
            $users = $options["users"];
            for ($i = 0; $i < count($users); $i++) {
                Notification::insert($new_Record_id, $users[$i]);
                Record::addUserAssociation($new_Record_id, $users[$i]);
                $assign[] = $users[$i];
            }
            if (count($assign)) {
                Notification::notifyAssignedUsers($assign, $new_Record_id);
            }
            // also notify any users that want to receive emails anytime a new Record is created
            Notification::notifyNewRecord($HTTP_POST_VARS['project'], $new_Record_id, $assign);
            // now process any files being uploaded
            $found = 0;
            for ($i = 0; $i < count(@$HTTP_POST_FILES["file"]["name"]); $i++) {
                if (!@empty($HTTP_POST_FILES["file"]["name"][$i])) {
                    $found = 1;
                    break;
                }
            }
            if ($found) {
                $attachment_id = Attachment::add($new_Record_id, $options["reporter"], 'files uploaded anonymously');
                for ($i = 0; $i < count(@$HTTP_POST_FILES["file"]["name"]); $i++) {
                    $filename = @$HTTP_POST_FILES["file"]["name"][$i];
                    if (empty($filename)) {
                        continue;
                    }
                    $blob = Misc::getFileContents($HTTP_POST_FILES["file"]["tmp_name"][$i]);
                    if (!empty($blob)) {
                        Attachment::addFile($attachment_id, $new_Record_id, $filename, $HTTP_POST_FILES["file"]["type"][$i], $blob);
                    }
                }
            }
            // need to process any custom fields ?
            if (@count($HTTP_POST_VARS["custom_fields"]) > 0) {
                foreach ($HTTP_POST_VARS["custom_fields"] as $fld_id => $value) {
                    Custom_Field::associateRecord($new_Record_id, $fld_id, $value);
                }
            }
            return $new_Record_id;
        }
    }


    /**
     * Method used to remove all Records associated with a specific list of
     * collections.
     *
     * @access  public
     * @param   array $ids The list of collections to look for
     * @return  boolean
     */
    function removeByCollections($ids)
    {
        $items = @implode(", ", $ids);
        $stmt = "SELECT
                    rec_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_col_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            Record::deleteAssociations($res);
            Attachment::removeByRecords($res);
            SCM::removeByRecords($res);
            Impact_Analysis::removeByRecords($res);
            Record::deleteUserAssociations($res);
            Note::removeByRecords($res);
            Time_Tracking::removeByRecords($res);
            Notification::removeByRecords($res);
            Custom_Field::removeByRecords($res);
            Phone_Support::removeByRecords($res);
            History::removeByRecords($res);
            // now really delete the Records
            $items = implode(", ", $res);
            $stmt = "DELETE FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                     WHERE
                        rec_id IN ($items)";
            $GLOBALS["db_api"]->dbh->query($stmt);
            return true;
        }
    }


    /**
     * Method used to close off an Record.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @param   integer $record_id The Record ID
     * @param   bool $send_notification Whether to send a notification about this action or not
     * @param   integer $resolution_id The resolution ID
     * @param  @@@ Added By CK - 19/7/2004 integer $resolution_location_id The resolution location ID
     * @param   integer $status_id The status ID
     * @param   string $reason The reason for closing this Record
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function close($usr_id, $record_id, $send_notification, $resolution_id, $resolution_location_id, $status_id, $reason)
    {
        global $HTTP_POST_VARS;

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "',
                    rec_closed_date='" . Date_API::getCurrentDateGMT() . "',
                    rec_res_id=$resolution_id,
                    rec_resloc_id=$resolution_location_id,
                    rec_sta_id=$status_id
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // unlock the Record, if needed
            Record::unlock($record_id, $usr_id);
            // add note with the reason to close the Record
            $HTTP_POST_VARS['title'] = 'Record closed comments';
            $HTTP_POST_VARS["note"] = $reason;
            Note::insert($usr_id, $record_id);
            // record the change
            History::add($record_id, $usr_id, History::getTypeID('Record_closed'), "Record updated to status '" . Status::getStatusTitle($status_id) . "' by " . User::getFullName($usr_id));
            if ($send_notification) {
                // send notifications for the Record being closed
                Notification::notify($record_id, 'closed');
            }
            return 1;
        }
    }


    /**
     * Method used to update the details of a specific Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer 1 if the update worked, -1 or -2 otherwise
     */
    function update($pid)
    {
        $record = new RecordObject($pid);
        if ($record->fedoraInsertUpdate()) {
            return 1;
        } else {
            return -1;
        }
    }

   /**
     * Method used to update the details of a specific Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer 1 if the update worked, -1 or -2 otherwise
     */
    function updateAdminDatastream($pid, $xdis_id)
    {
        $record = new RecordObject($pid);
        if ($record->updateAdminDatastream($xdis_id)) {
            return 1;
        } else {
            return -1;
        }
    }


    function incrementFileDownloads($pid)
    {
        $record = new RecordObject($pid);
        if ($record->incrementFileDownloads()) {
            return 1;
        } else {
            return -1;
        }
    }

    /**
     * Method used to associate an existing Record with another one.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $record_id The other Record ID
     * @return  void
     */
    function addAssociation($record_id, $associated_id, $usr_id)
    {
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_association
                 (
                    isa_Record_id,
                    isa_associated_id
                 ) VALUES (
                    $record_id,
                    $associated_id
                 )";
        $GLOBALS["db_api"]->dbh->query($stmt);
        History::add($record_id, $usr_id, History::getTypeID('Record_associated'), "Record associated to #$associated_id by " . User::getFullName($usr_id));
    }


    /**
     * Method used to remove the Record associations related to a specific Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  void
     */
    function deleteAssociations($record_id, $usr_id = FALSE)
    {
        if (is_array($record_id)) {
            $record_id = implode(", ", $record_id);
        }
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_association
                 WHERE
                    isa_Record_id IN ($record_id)";
        $GLOBALS["db_api"]->dbh->query($stmt);
        if ($usr_id) {
            History::add($record_id, $usr_id, History::getTypeID('Record_all_unassociated'), 'Record associations removed by ' . User::getFullName($usr_id));
        }
    }


    /**
     * Method used to remove a Record association from an Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $associated_id The associated Record ID to remove.
     * @return  void
     */
    function deleteAssociation($record_id, $associated_id)
    {
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_association
                 WHERE
                    isa_Record_id = $record_id AND
                    isa_associated_id = $associated_id";
        $GLOBALS["db_api"]->dbh->query($stmt);
        History::add($record_id, Auth::getUserID(), History::getTypeID('Record_unassociated'), 
                "Record association #$associated_id removed by " . User::getFullName(Auth::getUserID()));
    }


    /**
     * Method used to assign an Record with an user.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $usr_id The user ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function addUserAssociation($record_id, $usr_id, $add_history = TRUE)
    {
		global $HTTP_POST_VARS;
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user
                 (
                    isu_rec_id,
                    isu_usr_id,
                    isu_assigned_date
                 ) VALUES (
                    $record_id,
                    $usr_id,
                    '" . Date_API::getCurrentDateGMT() . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            if ($add_history) {
				$col_id = Auth::getCurrentCollection();
				if ($col_id != 2) { // @@@ CK - If not ASKIT, as they can have generic usernames 
					History::add($record_id, Auth::getUserID(), History::getTypeID('user_associated'), 
						'Record assigned to ' . User::getFullName($usr_id) . ' by ' . User::getFullName(Auth::getUserID()));
				} else {
					History::add($record_id, Auth::getUserID(), History::getTypeID('user_associated'), 
						'Record assigned to ' . User::getFullName($usr_id) . ' by ' . User::getFullName($HTTP_POST_VARS['loggedby']));
				}
            }
			// @@@ CK - 14/10/2004 - If a Server team user is assigned to an Record then they want to get emailed
			// it seems this functionality can be set by users in their preferences.. will try it
//			if (User::getPrimaryTeam($usr_id) == 4) {
	//			Notifications::notifySubscribers($record_id, $emails, $type, $data, $subject, true);
//			}
            return 1;
        }
    }


    /**
     * Method used to delete all user assignments for a specific Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $usr_id The user ID of the person performing the change
     * @return  void
     */
    function deleteUserAssociations($record_id, $usr_id = FALSE)
    {
        if (is_array($record_id)) {
            $record_id = implode(", ", $record_id);
        }
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user
                 WHERE
                    isu_rec_id IN ($record_id)";
        $GLOBALS["db_api"]->dbh->query($stmt);
        if ($usr_id) {
            History::add($record_id, $usr_id, History::getTypeID('user_all_unassociated'), 'Record assignments removed by ' . User::getFullName($usr_id));
        }
    }


    /**
     * Method used to delete a single user assignments for a specific Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $usr_id The user to remove.
     * @return  void
     */
    function deleteUserAssociation($record_id, $usr_id)
    {
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user
                 WHERE
                    isu_rec_id = $record_id AND
                    isu_usr_id = $usr_id";
        $GLOBALS["db_api"]->dbh->query($stmt);
        History::add($record_id, Auth::getUserID(), History::getTypeID('user_unassociated'), 
            User::getFullName($usr_id) . ' removed from Record by ' . User::getFullName(Auth::getUserID()));
    }


    // XXX: put documentation here
    function createFromEmail($col_id, $usr_id, $sender, $summary, $description, $category, $priority, $reporter, $assignment, $to_address)
    {
        $initial_status = Collection::getInitialStatus($col_id);
        // add new Record
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 (
                    rec_col_id,
                    rec_prc_id,
                    rec_pri_id,
                    rec_usr_id,";
        if (!empty($initial_status)) {
            $stmt .= "rec_sta_id,";
        }
        $stmt .= "
                    rec_created_date,
                    rec_summary,
                    rec_description
                 ) VALUES (
                    " . $col_id . ",
                    " . $category . ",
                    " . $priority . ",
                    " . $reporter . ",";
        if (!empty($initial_status)) {
//            $stmt .= "$initial_status,";
			// @@@ CK - Added to make all Email started Records began as open
			$tmpStatus = Status::getStatusID('Open');
            $stmt .= $tmpStatus.","; 
        }
        $stmt .= "
                    '" . Date_API::getCurrentDateGMT() . "',
                    '" . Misc::escapeString($summary) . "',
                    '" . Misc::escapeString($description) . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            $new_Record_id = $GLOBALS["db_api"]->get_last_insert_id();
            // log the creation of the Record
            History::add($new_Record_id, $usr_id, History::getTypeID('Record_opened'), 'Record opened by ' . $sender);
            // now add the user/Record association
            $users = array();
            if (count($assignment) > 0) {
                for ($i = 0; $i < count($assignment); $i++) {
                    Notification::insert($new_Record_id, $assignment[$i]);
                    Record::addUserAssociation($new_Record_id, $assignment[$i]);
                    if ($assignment[$i] != $usr_id) {
                        $users[] = $assignment[$i];
                    }
                }
            } else {
                // try using the round-robin feature instead
                $assignee = Round_Robin::getNextAssignee($col_id);
                // assign the Record to the round robin person
                if (!empty($assignee)) {
                    Record::addUserAssociation($new_Record_id, $assignee, false);
                    History::add($new_Record_id, APP_SYSTEM_USER_ID, History::getTypeID('rr_Record_assigned'), 'Record auto-assigned to ' . User::getFullName($assignee) . ' (RR)');
                    $users[] = $assignee;
                }
            }
            if (count($users)) {
                Notification::notifyAssignedUsers($users, $new_Record_id);
            }
            // also notify any users that want to receive emails anytime a new Record is created
            Notification::notifyNewRecord($col_id, $new_Record_id, $users);
			// @@@ CK - autoreply to the submitting user with the Record number and confirmation
			if (strpos(strtolower($to_address), "webmaster") === false) { 
				Notification::notifyAutoReply($new_Record_id, $sender, $col_id);
			}
			// @@@ CK - make the Submitted via custom field value 'email' - 3 = email
			Custom_Field::associateRecord($new_Record_id, 1, "3"); 
			// @@@ CK -9/9/2004 make Iniating Reporter the sender
			Custom_Field::associateRecord($new_Record_id, 8, $sender); 
			// @@@ CK -20/10/2004 make Iniating Reporter Email the sender as well
			Custom_Field::associateRecord($new_Record_id, 10, $sender); 

            return $new_Record_id;

        }
    }



    /**
     * Method used to add a new Record using the normal report form.
     *
     * @access  public
     * @return  integer The new Record ID
     */
    function insert()
    {
        $record = new RecordObject();
        return $record->fedoraInsertUpdate();
    }

    /**
     * Method used to add a new quick Record using the quick report form.
     *
     * @access  public
     * @return  integer The new Record ID
     */

	function insertIndexBatch($pid, $indexArray, $datastreamXMLHeaders) {

		// first delete all indexes about this pid
		Record::removeIndexRecord($pid, 'keep');
		if (!is_array($indexArray)) {
			return false;
		}

//		array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $value)
		foreach ($indexArray as $index) {
			if ($index[1] == 1)  { // if this xsdmf is designated to be indexed then insert it as long as it has a value
				foreach ($datastreamXMLHeaders as $dsKey => $dsHeader) { // get the real ds names for the file uploads
					if ($index[6] == $dsKey) {
						$index[6] = $dsHeader['ID'];
					}
				}
				if ($index[6] != "") {
					Record::insertIndexMatchingField($index[0], $index[2], $index[5], $index[6]);
				}
			}
		}
	
	}




    function removeIndexRecord($pid, $dsDelete='all')
    {
 
//		echo "monkey = ".$initial_status;
        // add new Record
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
				 WHERE rmf_rec_pid = '" . $pid . "'";
		if ($dsDelete=='keep') {
			$stmt .= " and rmf_xsdmf_id not in (select distinct(xsdmf_id) from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_fez_title = 'datastream_id')";
		}
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return $pid;
        }
    }

    function removeIndexRecordByValue($pid, $value, $data_type='varchar')
    {
 
//		echo "monkey = ".$initial_status;
        // add new Record
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
				 WHERE rmf_rec_pid = '" . $pid . "' and rmf_".$data_type."='".$value."'";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return $pid;
        }
    }

    function removeIndexRecordByXSDMF_ID($pid, $xsdmf_id)
    {
 
//		echo "monkey = ".$initial_status;
        // add new Record
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
				 WHERE rmf_rec_pid = '" . $pid . "' and rmf_xsdmf_id=".$xsdmf_id;
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return $pid;
        }
    }


    function insertIndexMatchingField($pid, $xsdmf_id, $data_type, $value)
    {
 
//		echo "monkey = ".$initial_status;
        // add new Record
        $xsdsel_id = '';
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
                 (
				 	rmf_rec_pid,
                    rmf_xsdmf_id,";
				if ($xsdsel_id != "") {
				  $stmt .= "rmf_xsdsel_id,";
				}
				$stmt .= "                    
					rmf_".$data_type."
                 ) VALUES (
                    '" . $pid . "',
                    " . $xsdmf_id . ",";
				if ($xsdsel_id != "") {
                	$stmt .= $xsdsel_id . ", ";
				}
					if ($data_type != "int") {
            			$stmt .= "'".Misc::escapeString($value) . "')";
					} else {
            			$stmt .= $value . ")";
					}
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return $pid;
        }
    }

    /**
     * Method used to add a instant Records using the quick report form.
     *
     * @access  public
     * @return  integer The new Record ID
     */
    function instantInsert()
    {
        global $HTTP_POST_VARS, $HTTP_POST_FILES;

        $col_id = Auth::getCurrentCollection();
		$btnQuickCat = Category::getID(substr($HTTP_POST_VARS["btnQuick"], 0, strpos(trim($HTTP_POST_VARS["btnQuick"]), "--")-1), $col_id);
		$btnQuickSubcat = Subcategory::getID(trim(substr($HTTP_POST_VARS["btnQuick"], (strpos($HTTP_POST_VARS["btnQuick"], "--")+2))), $btnQuickCat);
		if (!is_numeric($btnQuickSubcat)) {
			$btnQuickSubcat = 0;
		}
//		echo "cat = ".$btnQuickCat;
//		echo "subcat = ".$btnQuickSubCat;

        $missing_fields = array();
        if ($btnQuickCat == '-1') {
            $missing_fields[] = "Category";
        }

        $usr_id = Auth::getUserID();

        $initial_status = Collection::getInitialStatus($col_id);
        // add new Record
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 (
                    rec_col_id,
                    rec_prc_id,
                    rec_prsc_id,
                    rec_usr_id,";
		

		if ((count($HTTP_POST_VARS["users"]) == 0) || ($col_id == 2)) { // IF QUICK Record ASSIGNED THEN KEEP IT OPEN, OTHERWISE MAKE IT DEFAULT (CLOSED) - CK
			$stmt .= "rec_closed_date,";
		}
        $stmt .= "rec_sta_id,";     
        $stmt .= "
                    rec_created_date
                 ) VALUES (
                    " . $col_id . ",
                    " . $btnQuickCat . ",
                    " . $btnQuickSubcat . ",";
		if ($col_id == 2) {
				$stmt .=  $HTTP_POST_VARS["loggedby"] . ",";
		} else {
                $stmt .=    $usr_id . ",";
		}

		if ((count($HTTP_POST_VARS["users"]) > 0) && ($col_id != 2)) { // IF QUICK Record ASSIGNED (And not ASKIT) THEN KEEP IT OPEN, OTHERWISE MAKE IT DEFAULT (CLOSED) - CK
			
            $stmt .= "'".Status::getStatusID('Open')."',";			
		} elseif (!empty($initial_status)) {
            $stmt .= "'".Date_API::getCurrentDateGMT() . "',";
            $stmt .= "$initial_status,";
        }

        $stmt .= "
                    '" . Date_API::getCurrentDateGMT() . "'
                 )";
		
        $res = $GLOBALS["db_api"]->dbh->query($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            $new_Record_id = $GLOBALS["db_api"]->get_last_insert_id();
            // log the creation of the Record
			if ($col_id != 2) { // AskIT are different.. They can have generic usernames so you have to use thet logged by field for them.
				History::add($new_Record_id, Auth::getUserID(), History::getTypeID('Record_opened'), 'Instant Record opened by ' . User::getFullName(Auth::getUserID()));
				Time_Tracking::recordRemoteEntry($new_Record_id, $usr_id, 37, 'Instant Record Logged', 1); //@@@ CK - assuming cat_id 37 is Quick Record
			} else {
	            History::add($new_Record_id, $HTTP_POST_VARS["loggedby"], History::getTypeID('Record_opened'), 'Instant Record logged by ' . User::getFullName($HTTP_POST_VARS["loggedby"]));
//				Time_Tracking::recordRemoteEntry($new_Record_id, $HTTP_POST_VARS["users"], 37, 'Instant Record Logged', 1); //@@@ CK - assuming cat_id 37 is Quick Record
				// @@@ CK - 10/9/2004 - Assigning loggin user
				Record::addUserAssociation($new_Record_id, $HTTP_POST_VARS["users"]);
				// @@@ CK - 10/9/2004 - Adding 1 min of time tracking to quick Record
				Time_Tracking::recordRemoteEntry($new_Record_id, $HTTP_POST_VARS["users"], 37, 'Instant Record Logged', 1); //@@@ CK - assuming cat_id 37 is Quick Record

			}

            // now add the user/Record association
            $users = array();

            if (count($HTTP_POST_VARS["users"]) > 0) {
                for ($i = 0; $i < count($HTTP_POST_VARS["users"]); $i++) {
                    Notification::insert($new_Record_id, $HTTP_POST_VARS["users"]);
//					print_r($HTTP_POST_VARS["users"]);
                    Record::addUserAssociation($new_Record_id, $HTTP_POST_VARS["users"]);
                    if ($HTTP_POST_VARS["users"] != $usr_id) {
                        $users[] = $HTTP_POST_VARS["users"];
                    }
                }
            } else {
                // try using the round-robin feature instead
                $assignee = Round_Robin::getNextAssignee($col_id);
                // assign the Record to the round robin person
                if (!empty($assignee)) {
                    Record::addUserAssociation($new_Record_id, $assignee, false);
                    History::add($new_Record_id, APP_SYSTEM_USER_ID, History::getTypeID('rr_Record_assigned'), 'Record auto-assigned to ' . User::getFullName($assignee) . ' (RR)');
                    $users[] = $assignee;
                }
            }
            if (count($users)) {
                Notification::notifyAssignedUsers($users, $new_Record_id);
            }

            // now process any files being uploaded
            $found = 0;
            for ($i = 0; $i < count(@$HTTP_POST_FILES["file"]["name"]); $i++) {
                if (!@empty($HTTP_POST_FILES["file"]["name"][$i])) {
                    $found = 1;
                    break;
                }
            }
            if ($found) {
                $attachment_id = Attachment::add($new_Record_id, $usr_id, '');
                for ($i = 0; $i < count(@$HTTP_POST_FILES["file"]["name"]); $i++) {
                    $filename = @$HTTP_POST_FILES["file"]["name"][$i];
                    if (empty($filename)) {
                        continue;
                    }
                    $blob = Misc::getFileContents($HTTP_POST_FILES["file"]["tmp_name"][$i]);
                    if (!empty($blob)) {
                        Attachment::addFile($attachment_id, $new_Record_id, $filename, $HTTP_POST_FILES["file"]["type"][$i], $blob);
                    }
                }
            }
            // need to associate any emails ?
            if (!empty($HTTP_POST_VARS["attached_emails"])) {
                $items = explode(",", $HTTP_POST_VARS["attached_emails"]);
                Support::associate($usr_id, $new_Record_id, $items);
            }
            // need to process any custom fields ?
            if (@count($HTTP_POST_VARS["instant_custom_fields"]) > 0) {
                foreach ($HTTP_POST_VARS["instant_custom_fields"] as $fld_id => $value) {
                    Custom_Field::associateRecord($new_Record_id, $fld_id, $value);
                }
            }
            // now subscribe the reporter of this Record (if needed)
            if (@$HTTP_POST_VARS["receive_notifications"] == 1) {
                // get the actual preference for this subscription
                if ($HTTP_POST_VARS["choice"] == 'default') {
                    Notification::insert($new_Record_id, $usr_id);
                } else {
                    Notification::subscribeReporter($new_Record_id, $usr_id, $HTTP_POST_VARS["actions"]);
                }
            }
            // also notify any users that want to receive emails anytime a new Record is created
            Notification::notifyNewRecord($col_id, $new_Record_id, $users);
            return $new_Record_id;
        } 
    }


    /**
     * Method used to get the current listing related cookie information.
     *
     * @access  public
     * @return  array The Record listing information
     */
    function getCookieParams()
    {
        global $HTTP_COOKIE_VARS;
        return @unserialize(base64_decode($HTTP_COOKIE_VARS[APP_LIST_COOKIE]));
    }


    /**
     * Method used to get a specific parameter in the Record listing cookie.
     *
     * @access  public
     * @param   string $name The name of the parameter
     * @return  mixed The value of the specified parameter
     */
    function getParam($name)
    {
        global $HTTP_POST_VARS, $HTTP_GET_VARS;
        $cookie = Record::getCookieParams();

        if (isset($HTTP_GET_VARS[$name])) {
            return $HTTP_GET_VARS[$name];
        } elseif (isset($HTTP_POST_VARS[$name])) {
            return $HTTP_POST_VARS[$name];
        } elseif (isset($cookie[$name])) {
            return $cookie[$name];
        } else {
            return "";
        }
    }


    /**
     * Method used to save the current search parameters in a cookie.
     *
     * @access  public
     * @return  array The search parameters
     */
    function saveSearchParams()
    {	
		// @@@ CK 21/7/2004 - Added this global for the custom fields check.
			
        $sort_by = Record::getParam('sort_by');
        $sort_order = Record::getParam('sort_order');
        $rows = Record::getParam('rows');
        $cookie = array(
            'rows'           => $rows ? $rows : APP_DEFAULT_PAGER_SIZE,
//            'pagerRow'       => Record::getParam('pagerRow'), //stop saving the page in the cookie..
            'hide_closed'    => Record::getParam('hide_closed'),
            "sort_by"        => $sort_by ? $sort_by : "rec_id",
            "sort_order"     => $sort_order ? $sort_order : "DESC",
            // quick filter form
            'keywords'       => Record::getParam('keywords'),
            'collections'       => Record::getParam('collections'),
//            'time_tracking_category' => Record::getParam('time_tracking_category'),
            'users'          => Record::getParam('users'),
            'status'         => Record::getParam('status'),
//            'has_attachments' => Record::getParam('has_attachments'), // @@@ CK - added 7/9/2004
//            'priority'       => Record::getParam('priority'),
//            'category'       => Record::getParam('category'),
            // advanced search form
            'show_authorized_Records'        => Record::getParam('show_authorized_records'),
            'show_notification_list_Records' => Record::getParam('show_notification_list_records'),
        );
			$existing_cookie = Record::getCookieParams();
						//print_r($existing_cookie);

			global $HTTP_POST_VARS, $HTTP_GET_VARS;
            // need to process any custom fields ?
/*            $custom_count = Record::getParam('custom_count');
			if (empty($custom_count) || !is_numeric($custom_count)) {
	            $custom_count = @count($HTTP_GET_VARS["custom_fields"]);
			}
			for($x=0;$x<$custom_count;$x++) {
				
			}
*/
//			$custom_count = Custom_Field::getMaxID();
//			$tempArray = array('custom_count' => $custom_count);
//			$cookie = array_merge ($cookie, $tempArray);

/*            if ($custom_count > 0) {
				$from_cookie = false;		
		    	if (isset($HTTP_GET_VARS['custom_fields'])) {
					$customArray = $HTTP_GET_VARS['custom_fields'];
  				} elseif (isset($HTTP_POST_VARS['custom_fields'])) {
					$customArray = $HTTP_POST_VARS['custom_fields'];
				} else {
					$from_cookie = true;
					for($x=0;$x<$custom_count;$x++) {
						$existing_cookie = Record::getCookieParams();
						if (isset($existing_cookie['custom'.$x])) {
							$customArray = array_merge($customArray, array('custom'.$x => $existing_cookie['custom'.$x]));
						}
					}   
			    }
                foreach ($customArray as $fld_id => $value) {				
					if ($from_cookie == true) {
/*						if ($fld_id == 2) {
							foreach ($value as $branch => $branchValue) {
								
							}
						} else { */ 
/*
							$tempArray = array($fld_id => $value);				
//						}
					} else {
						$tempArray = array('custom'.$fld_id => $value);				
					}
					$cookie = array_merge ($cookie, $tempArray);
					if (!empty($fld_id)) {

					}
                }
			}
*/
        // now do some magic to properly format the date fields
/*        $date_fields = array(
            'created_date',
            'updated_date',
            'last_response_date',
            'first_response_date',
            'closed_date'
        );
        foreach ($date_fields as $field_name) {
            $field = Record::getParam($field_name);
            if (empty($field)) {
                continue;
            }
            $end_field_name = $field_name . '_end';
            $end_field = Record::getParam($end_field_name);
            @$cookie[$field_name] = array(
                'Year'        => $field['Year'],
                'Month'       => $field['Month'],
                'Day'         => $field['Day'],
                'start'       => $field['Year'] . '-' . $field['Month'] . '-' . $field['Day'],
                'filter_type' => $field['filter_type'],
                'end'         => $end_field['Year'] . '-' . $end_field['Month'] . '-' . $end_field['Day']
            );
            @$cookie[$end_field_name] = array(
                'Year'        => $end_field['Year'],
                'Month'       => $end_field['Month'],
                'Day'         => $end_field['Day']
            );
        }*/
        $encoded = base64_encode(serialize($cookie));
        setcookie(APP_LIST_COOKIE, $encoded, APP_LIST_COOKIE_EXPIRE);
		//print_r($cookie);
        return $cookie;
    }


    /**
     * Method used to get the current sorting options used in the grid layout
     * of the Record listing page.
     *
     * @@@ CK - 28/10/2004 - Added library branch sorting
     *
     * @access  public
     * @param   array $options The current search parameters
     * @return  array The sorting options
     */
    function getSortingInfo($options)
    {
        global $HTTP_SERVER_VARS;
		// @@@ CK - 18/1/2005 - need to work in assigned_users somehow.
        $fields = array(
            "rec_id",
            "rec_date",
            "rec_summary"
        );
        $items = array(
            "links"  => array(),
            "images" => array()
        );
        for ($i = 0; $i < count($fields); $i++) {
            if ($options["sort_by"] == $fields[$i]) {
                $items["images"][$fields[$i]] = "images/" . strtolower($options["sort_order"]) . ".gif";
                if (strtolower($options["sort_order"]) == "asc") {
                    $sort_order = "desc";
                } else {
                    $sort_order = "asc";
                }
                $items["links"][$fields[$i]] = $HTTP_SERVER_VARS["PHP_SELF"] . "?sort_by=" . $fields[$i] . "&sort_order=" . $sort_order;
            } else {
                $items["links"][$fields[$i]] = $HTTP_SERVER_VARS["PHP_SELF"] . "?sort_by=" . $fields[$i] . "&sort_order=asc";
            }
        }
//		print_r($items);
        return $items;
    }


    /**
     * Method used to get the list of Records to be displayed in the grid layout.
     *
     * @access  public
     * @param   integer $col_id The current project ID
     * @param   array $options The search parameters
     * @param   integer $current_row The current page number
     * @param   integer $max The maximum number of rows per page
     * @return  array The list of Records to be displayed
     */
    function getListing($options, $current_row = 0, $max = 5, $get_reporter = FALSE)
    {
		$details = Fedora_API::getListObjectsXML($options, $max);

		foreach ($details as $darray_key => $darray) {
			foreach ($darray as $dkey => $dvalue) { // turn any array values into a comma seperated string value
				if (is_array($dvalue)) {
					$details[$darray_key][$dkey] = implode(", ", $dvalue);
				}
			}
		}	
        $details = Auth::ProcessListResults($details);
		return $details;
    }


    /**
     * Retrieves the last action dates for the given list of Records.
     *
     * @access  public
     * @param   array $result The list of Records
     * @see     Record::getListing()
     */
    function getLastActionDates(&$result)
    {
        $ids = array();
        for ($i = 0; $i < count($result); $i++) {
            $ids[] = $result[$i]["rec_id"];
        }
        $ids = implode(", ", $ids);

        // get the latest file
        $stmt = "SELECT
                    iat_rec_id,
                    UNIX_TIMESTAMP(MAX(iat_created_date))
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_attachment
                 WHERE
                    iat_rec_id IN ($ids)
                 GROUP BY
                    iat_rec_id";
        $files = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        // get latest email
        $stmt = "SELECT
                    sup_rec_id,
                    UNIX_TIMESTAMP(MAX(sup_date))
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "support_email
                 WHERE
                    sup_rec_id IN ($ids)
                 GROUP BY
                    sup_rec_id";
        $emails = $GLOBALS["db_api"]->dbh->getOne($stmt);
        // get latest draft
        $stmt = "SELECT
                    emd_rec_id,
                    UNIX_TIMESTAMP(MAX(emd_updated_date))
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "email_draft
                 WHERE
                    emd_rec_id IN ($ids)
                 GROUP BY
                    emd_rec_id";
        $drafts = $GLOBALS["db_api"]->dbh->getOne($stmt);
        // get latest phone call
        $stmt = "SELECT
                    phs_rec_id,
                    UNIX_TIMESTAMP(MAX(phs_created_date))
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "phone_support
                 WHERE
                    phs_rec_id IN ($ids)
                 GROUP BY
                    phs_rec_id";
        $calls = $GLOBALS["db_api"]->dbh->getOne($stmt);
        // get last note
        $stmt = "SELECT
                    not_rec_id,
                    UNIX_TIMESTAMP(MAX(not_created_date))
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "note
                 WHERE
                    not_rec_id IN ($ids)
                 GROUP BY
                    not_rec_id";
        $notes = $GLOBALS["db_api"]->dbh->getOne($stmt);
        // now sort out the fields for each Record
        for ($i = 0; $i < count($result); $i++) {
            // check attachments, notes, emails, updated date
            $date_fields = array(
                'created'         => $result[$i]['rec_created_date'],
                'updated'         => $result[$i]['rec_updated_date'],
                'staff response'  => $result[$i]['rec_last_response_date'],
                'closed'          => $result[$i]['rec_closed_date']
            );
            @$date_fields['file'] = $files[$result[$i]['rec_id']];
            @$date_fields['email'] = $emails[$result[$i]['rec_id']];
            @$date_fields['draft'] = $drafts[$result[$i]['rec_id']];
            @$date_fields['phone call'] = $calls[$result[$i]['rec_id']];
            @$date_fields['note'] = $notes[$result[$i]['rec_id']];
            asort($date_fields);
            // need to show something else besides the updated date field, if there are other fields with the same timestamp
            $stamps = array_values($date_fields);
            if ($stamps[count($stamps)-1] == $stamps[count($stamps)-2]) {
                $keys = array_keys($date_fields);
                if (($keys[count($keys)-1] == 'updated') || 
                        ($keys[count($keys)-2] == 'updated')) {
                    unset($date_fields['updated']);
                }
            }
            $original_date_fields = $date_fields;
            $latest_field = array_pop($date_fields);
            if (empty($latest_field)) {
                $result[$i]['last_action_date'] = '';
            } else {
                $flipped = @array_flip($original_date_fields);
                // use the pear classes to get the date difference
                $date = new Date($latest_field);
                $current = new Date(Date_API::getCurrentDateGMT());
                $result[$i]['last_action_date'] = sprintf("%s: %s ago", ucwords($flipped[$latest_field]),
                        Date_API::getFormattedDateDiff($current->getDate(DATE_FORMAT_UNIXTIME), $date->getDate(DATE_FORMAT_UNIXTIME)));
            }
        }
    }


    /**
     * Retrieves the last status change date for the given Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   array $row The associative array of data
     * @return  string The formatted last status change date
     * @see     Record::getListing()
     */
    function getLastStatusChangeDate($record_id, $row)
    {
        // get target date and label for the given status id
        if (empty($row['rec_sta_id'])) {
            return '';
        } else {
            list($label, $date_field_name) = Status::getCollectionStatusCustomization($row['rec_col_id'], $row['rec_sta_id']);
            if ((empty($label)) || (empty($date_field_name))) {
                return '';
            }
            $current = new Date(Date_API::getCurrentDateGMT());
            $desc = "$label: %s ago";
            $target_date = $row[$date_field_name];
            if (empty($target_date)) {
                return '';
            }
            $date = new Date($target_date);
            return sprintf($desc, Date_API::getFormattedDateDiff($current->getDate(DATE_FORMAT_UNIXTIME), $date->getDate(DATE_FORMAT_UNIXTIME)));
        }
    }



    /**
     * Method used to get the list of Records to be displayed in the grid layout.
     *
     * @access  public
     * @param   array $options The search parameters
     * @return  string The where clause
     */
    function buildCustomFieldJoins($options)
    {
//		echo "Record count = ".$options["custom_count"];
//		echo "Record count = ".$options["custom1"];
		$custom_join = array();
		$custom_join[0] = ""; // initialise the return sql custom fields join string
		$custom_join[1] = 0; // initialise the max count of extra custom field joins
        if (!empty($options["custom_count"])) {
	        if ($options["custom_count"] > 0) {
//				$stmt .= " AND ("; 
				$firstloop = true;
			  // @@@ CK - 23/8/2004 Need to change this 9 (9 is if 8 is highest customfield id) to something dynamic..
				for($x=1;$x < 9;$x++) {

					if (!empty($options["custom".$x])) {
						if ($firstloop != true) {
//							$stmt .= " OR ";
						}
						$join_like = "";
						if ($x == 6 || $x == 8 || $x == 4) { //@@@ CK - 6 is Name of User so check each comma seperated group, and 4 is Serial number which could be seperated by commas, 8 is Initiating Reporter
							$groups = explode(",", $options["custom".$x]);
							$first_group = true;
							foreach ($groups as $group) {
								$group = trim($group);
								if (!empty($group)) {	
									if ($first_group != true) {
										$join_like .= ") OR (";
									} else {
										$join_like .= "(";
									}
									$words = explode(" ", $group);
									$first_word = true;
									foreach ($words as $word) {
										$word = trim($word);
										if ($first_word != true) {
		//									$join_like .= " OR "; // @@@ CK - 6/9/2004 - changed to AND so that wouldnt get too many results
											$join_like .= " AND ";
										}
										$join_like .= " c".$x.".icf_value like '%" . $word . "%' ";
										$first_word = false;
									}
									$first_group = false;
								}
							}
							$join_like .= ")"; 
							$checkNone = $options["custom".$x];

						} elseif ($x == 2) {
							if ($options["custom".$x]) {
								if (in_array("", $options["custom".$x])) {
									$checkNone = "all";
//									echo "MONKEY";
									$join_like = "";
								} elseif (in_array("none", $options["custom".$x])) {
									$checkNone = "none"; //not set

//									$join_like = "c".$x.".icf_value in (" . implode(", ", $options["custom".$x]).")";
								} else {
									$join_like = "c".$x.".icf_value in ('" . implode("', '", $options["custom".$x])."')";
								}
							} // @@@ CK - 4/11/2004 - fixing this now..
						} else { // not 6 or 4 or 8
//							if ($options["custom".$x] != "none") {
								$join_like = "c".$x.".icf_value ='" . $options["custom".$x]."'";  
//							}
							$checkNone = $options["custom".$x];
						}
//						$stmt .= "(icf_fld_id = ".$x." AND icf_value ='" . $options["custom".$x]."')";
//$custom_join[0] .= " LEFT JOIN e" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_custom_field AS c".$x." ON c".($custom_join[1]).".icf_rec_id=c".$x.".icf_rec_id AND c".$x.".icf_fld_id = ".$x." AND (".$join_like.")";
if (($checkNone != "none") && ($checkNone != "all")) {
	$custom_join[0] .= " LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_custom_field AS c".$x." ON rec_id=c".$x.".icf_rec_id AND c".$x.".icf_fld_id = ".$x." AND (".$join_like.")";
} elseif ($checkNone == "none") {
	$custom_join[0] .= " LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_custom_field AS c".$x." ON rec_id=c".$x.".icf_rec_id AND c".$x.".icf_fld_id = ".$x;
	$custom_join[0] .= " LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field AS c".($x+1000)." ON c".($x+1000).".icf_rec_id=c".$x.".icf_rec_id";
	$custom_join[0] .= " LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_custom_field AS c".($x+2000)." ON c".($x+2000).".icf_rec_id=c".$x.".icf_rec_id";
}
$custom_join[1] = $x;
						$firstloop = false;
					}
				}
//				$stmt .= ")";
			}
		}

		return $custom_join;
	}

    /**
     * Method used to get the list of Records to be displayed in the grid layout.
     *
     * @access  public
     * @param   array $options The search parameters
     * @return  string The where clause
     */
    function buildWhereClause($options)
    {
        $usr_id = Auth::getUserID();

        $stmt = '';
        if (!empty($options["users"])) {
            $stmt .= " AND (
                    isu_usr_id";
            if ($options['users'] == '-1') {
                $stmt .= ' IS NULL';
            } elseif ($options['users'] == '-2') {
                $stmt .= ' IS NULL OR isu_usr_id=' . Auth::getUserID();
            } else {
                $stmt .= '=' . $options["users"];
            }
            $stmt .= ')';
        }
        if (!empty($options["show_authorized_Records"])) {
            $stmt .= " AND (iur_usr_id=" . Auth::getUserID() . ")";
        }
        if (!empty($options["show_notification_list_Records"])) {
            $stmt .= " AND (sub_usr_id=" . Auth::getUserID() . ")";
        }
        if (!empty($options["keywords"])) {
            $stmt .= " AND (" . Misc::prepareBooleanSearch('rec_summary', $options["keywords"]);
//            $stmt .= " OR " . Misc::prepareBooleanSearch('rec_description', $options["keywords"]) . ")";
			// @@@ CK - Added so the keyword search also searches the closed resolution internal notes so system can act like a knowledge base
            $stmt .= " OR " . Misc::prepareBooleanSearch('not_note', $options["keywords"]);
			// @@@ CK - Added so the keyword search also searches the time tracking so system can act like a knowledge base
            $stmt .= " OR " . Misc::prepareBooleanSearch('ttr_summary', $options["keywords"]);
			// @@@ CK - Added so the keyword search also searches the emails subjects so system can act like a knowledge base
            $stmt .= " OR " . Misc::prepareBooleanSearch('sup_subject', $options["keywords"]);
			// @@@ CK - Added so the keyword search also searches the emails bodies so system can act like a knowledge base
            $stmt .= " OR " . Misc::prepareBooleanSearch('seb_body', $options["keywords"]);

            $stmt .= " OR " . Misc::prepareBooleanSearch('rec_description', $options["keywords"]) . ")";
        }
        if (!empty($options["priority"])) {
            $stmt .= " AND rec_pri_id=" . $options["priority"];
        }
        if (!empty($options["status"])) {
            $stmt .= " AND rec_sta_id=" . $options["status"];
        }
        if (!empty($options["category"])) {
            $stmt .= " AND rec_prc_id=" . $options["category"];
        }
		// @@@ CK - 7/9/2004 - Added so could sort by has/has no attachments
        if (!empty($options["has_attachments"])) {
//            $stmt .= " AND has_attachments='" . $options["has_attachments"] . "'";
            $stmt .= " AND if(isnull(iat_rec_id), 'no', 'yes')='" . $options["has_attachments"] . "'";
        }

		// @@@ CK - 7/9/2004 - Added so could sort by timetracking category
        if (!empty($options["time_tracking_category"])) {
//            $stmt .= " AND has_attachments='" . $options["has_attachments"] . "'";
            $stmt .= " AND ttr_ttc_id='" . $options["time_tracking_category"] . "'";
        }


//		echo "Record count = ".$options["custom_count"];
//		echo "Record count = ".$options["custom1"];
/*        if (!empty($options["custom_count"])) {
	        if ($options["custom_count"] > 0) {
				$stmt .= " AND (";
				$firstloop = true;
				for($x=0;$x < $options["custom_count"];$x++) { 
					if (!empty($options["custom".$x])) {
						if ($firstloop != true) {
							$stmt .= " OR ";
						}
						$stmt .= "(icf_fld_id = ".$x." AND icf_value ='" . $options["custom".$x]."')";
						$firstloop = false;
					}
				}
				$stmt .= ")";
			}
		}
*/

        if (!empty($options["hide_closed"])) {
            $stmt .= " AND sta_is_closed=0";
        }
        // now for the date fields
        $date_fields = array(
            'created_date',
            'updated_date',
            'last_response_date',
            'first_response_date',
            'closed_date'
        );
        foreach ($date_fields as $field_name) {
            if (!empty($options[$field_name])) {
                switch ($options[$field_name]['filter_type']) {
                    case 'greater':
                        $stmt .= " AND rec_$field_name >= '" . $options[$field_name]['start'] . "'";
                        break;
                    case 'less':
                        $stmt .= " AND rec_$field_name <= '" . $options[$field_name]['start'] . "'";
                        break;
                    case 'between':
                        $stmt .= " AND rec_$field_name BETWEEN '" . $options[$field_name]['start'] . "' AND '" . $options[$field_name]['end'] . "'";
                        break;
                }
            }
        }

        return $stmt;
    }


    /**
     * Method used to get the previous and next Records that are available 
     * according to the current search parameters.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   array $options The search parameters
     * @return  array The list of Records
     */
    function getSides($record_id, $options)
    {
        $usr_id = Auth::getUserID();
		// @@@ CK - changed to check role by primary project
		// @@@ CK - 6/8/2004 - added other join tables so the knowledge based search could work with this sides function
//        $role_id = User::getRoleByUser($usr_id);
        $role_id = User::getRoleByUserCollection($usr_id, $col_id);

        $stmt = "SELECT
                   rec_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record";

		$customJoinArray = array();
		
		$customJoinArray = Record::buildCustomFieldJoins($options);
		$stmt .= $customJoinArray[0];
        $stmt .= "

 LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_custom_field as c998 ON rec_id=c998.icf_rec_id and c998.icf_fld_id=2 

LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "custom_field_option as c999 on c998.icf_value=c999.cfo_id and c998.icf_fld_id=c999.cfo_fld_id ";



        if (!empty($options["users"])) {
            $stmt .= "
                 LEFT JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user
                 ON
                    isu_rec_id=rec_id";
        }
        if (!empty($options["show_authorized_Records"])) {
             $stmt .= "
                 LEFT JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user_replier
                 ON
                    iur_rec_id=rec_id";
        }
        if (!empty($options["show_notification_list_Records"])) {
            $stmt .= "
                 LEFT JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "subscription
                 ON
                    sub_rec_id=rec_id";
        }
        $stmt .= "
                 LEFT JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "status
                 ON
                    rec_sta_id=sta_id
                 LEFT JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "priority
                 ON
                    rec_pri_id=pri_id

				LEFT JOIN 
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "note
				ON 
					rec_id=not_rec_id
				LEFT JOIN 
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "time_tracking
				ON 
					rec_id=ttr_rec_id
				LEFT JOIN 
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "support_email
				ON 
					rec_id=sup_rec_id
				LEFT JOIN 
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "support_email_body
				ON 
					sup_id=seb_sup_id
";
//	if any selected custom_fields
        $stmt .= "
                 LEFT JOIN
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_custom_field as c0
                 ON
                    rec_id=c0.icf_rec_id";


/*		$customJoinArray = array();
		
		$customJoinArray = Record::buildCustomFieldJoins($options);
		$stmt .= $customJoinArray[0];

        $stmt .= "
                 WHERE
                    rec_col_id=" . Auth::getCurrentCollection();

		if ($customJoinArray[1] == 0) {
			$notNullC = 1;
		} else {
			$notNullC = $customJoinArray[1];
	  	    $stmt .= " AND not isnull(c".$notNullC.".icf_fld_id)";
		}
*/


		if (!empty($options["collections"]) && (strtolower($options["collections"]) != "all")) { // @@@ CK - may need more security here one day? shouldnt matter initally with starter team set (we dont mind everyone seeing everything)
			//        $stmt .= "  WHERE rec_col_id=$col_id"; // @@@ CK - 23/8/2004 Old sql version - new one can search through any team
			//           									    no matter what team is currently active
			        $stmt .= "  WHERE rec_col_id=".$options["collections"]; 
		} elseif (strtolower($options["collections"]) == "all") {
			        $stmt .= "  WHERE rec_col_id > -1";  
		} else { // if no cookie set use default project only
					$col_id = Auth::getCurrentCollection();
			        $stmt .= "  WHERE rec_col_id=$col_id";  
		}

/*		if ($customJoinArray[1] == 0) {
			$notNullC = 1;
		} else {
			$notNullC = $customJoinArray[1];
			for($x=1;$x < ($customJoinArray[1]+1);$x++) {
				if (!empty($options["custom".$x])) {
					if ($options["custom".$x] == "none") {					
				  	    $stmt .= " AND (((isnull(c".($x+1000).".icf_value)) or (c".($x+2000).".icf_fld_id = ".$x." AND c".($x+2000).".icf_value = '-1')))";
//								  WHERE ((isnull(c1002.icf_value)) or (c2002.icf_fld_id = 2 AND c2002.icf_value = '-1'))
					} else {
				  	    $stmt .= " AND not isnull(c".$x.".icf_fld_id) ";
					}
				}
			}
		}*/
		if ($customJoinArray[1] == 0) {
			$notNullC = 1;
		} else {
			$notNullC = $customJoinArray[1];
			for($x=1;$x < ($customJoinArray[1]+1);$x++) {
				if (!empty($options["custom".$x])) {
					if ($x == 2) {
						if (in_array("none", $options["custom".$x]) && (!in_array("", $options["custom".$x]))) {						
							$stmt .= " AND (((isnull(c".($x+1000).".icf_value)) or (c".($x+2000).".icf_fld_id = ".$x." AND c".($x+2000).".icf_value = '-1')))";
						} elseif (!in_array("", $options["custom".$x])) { //@@@ CK - If "" (any/all) not in the selected array then use this sql clause
							$stmt .= " AND not isnull(c".$x.".icf_fld_id) ";
						} else {
							// add nothing to the where clause if 'all' library branches selected
						}
						
					} else {
						if ($options["custom".$x] == "none") {					
							$stmt .= " AND (((isnull(c".($x+1000).".icf_value)) or (c".($x+2000).".icf_fld_id = ".$x." AND c".($x+2000).".icf_value = '-1')))";
	//								  WHERE ((isnull(c1002.icf_value)) or (c2002.icf_fld_id = 2 AND c2002.icf_value = '-1'))
						} else {
							$stmt .= " AND not isnull(c".$x.".icf_fld_id) ";
						}

					}

				}
			}
		}



//                 WHERE
//                    rec_col_id=" . Auth::getCurrentCollection();
        $stmt .= Record::buildWhereClause($options);
		// @@@ CK 26/10/2004 - Replaced Select Distinct with this group by, which improves performance
        $stmt .= "
                 GROUP BY rec_id ";

		if (!in_array(trim($options["sort_by"]), array("assigned_users", "time_spent", "last_action_date"))) {
			$stmt .= "
					 ORDER BY
						" . $options["sort_by"] . " " . $options["sort_order"];
		}
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            // COMPAT: the next line requires PHP >= 4.0.5
            $index = array_search($record_id, $res);
            if (!empty($res[$index+1])) {
                $next = $res[$index+1];
            }
            if (!empty($res[$index-1])) {
                $previous = $res[$index-1];
            }
            return array(
                "next"     => @$next,
                "previous" => @$previous
            );
        }
    }


    /**
     * Method used to get the full list of user IDs assigned to a specific 
     * Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  array The list of user IDs
     */
    function getAssignedUserIDs($record_id)
    {
        $stmt = "SELECT
                    usr_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user
                 WHERE
                    isu_rec_id=$record_id AND
                    isu_usr_id=usr_id";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }
    }


    /**
     * Method used to see if a user is assigned to an Record.
     * 
     * @access  public
     * @param   integer $record_id The Record ID
     * @param   integer $user_id An integer containg the ID of the user.
     * @return  boolean true if the user(s) are assigned to the Record.
     */
    function isAssignedToUser($record_id, $usr_id)
    {
        $assigned_users = Record::getAssignedUserIDs($record_id);
        if (in_array($usr_id, $assigned_users)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Method used to get the full list of reporters associated with a given 
     * list of Records.
     *
     * @access  public
     * @param   array $result The result set
     * @return  void
     */
    function getReportersByRecords(&$result)
    {
        $ids = array();
        for ($i = 0; $i < count($result); $i++) {
            $ids[] = $result[$i]["rec_id"];
        }
        $ids = implode(", ", $ids);
        $stmt = "SELECT
                    rec_id,
                    CONCAT(usr_full_name, ' <', usr_email, '>') AS usr_full_name
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user
                 WHERE
                    rec_usr_id=usr_id AND
                    rec_id IN ($ids)";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
            // now populate the $result variable again
            for ($i = 0; $i < count($result); $i++) {
                @$result[$i]['reporter'] = $res[$result[$i]['rec_id']];
            }
        }
    }


    /**
     * Method used to get the full list of assigned users by a list
     * of Records. This was originally created to optimize the Record
     * listing page.
     *
     * @access  public
     * @param   array $result The result set
     * @return  void
     */
    function getAssignedUsersByRecords(&$result)
    {
        $ids = array();
        for ($i = 0; $i < count($result); $i++) {
            $ids[] = $result[$i]["rec_id"];
        }
        $ids = implode(", ", $ids);
        $stmt = "SELECT
                    isu_rec_id,
                    usr_full_name
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user
                 WHERE
                    isu_usr_id=usr_id AND
                    isu_rec_id IN ($ids)";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
            $t = array();
            for ($i = 0; $i < count($res); $i++) {
                if (!empty($t[$res[$i]['isu_rec_id']])) {
                    $t[$res[$i]['isu_rec_id']] .= ', ' . $res[$i]['usr_full_name'];
                } else {
                    $t[$res[$i]['isu_rec_id']] = $res[$i]['usr_full_name'];
                }
            }
            // now populate the $result variable again
            for ($i = 0; $i < count($result); $i++) {
                @$result[$i]['assigned_users'] = $t[$result[$i]['rec_id']];
            }
        }
    }


    /**
     * Method used to get the full list of users (the full names) assigned to a
     * specific Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  array The list of users
     */
    function getAssignedUsers($record_id)
    {
        $stmt = "SELECT
                    usr_full_name
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user
                 WHERE
                    isu_rec_id=$record_id AND
                    isu_usr_id=usr_id";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }
    }


	function getIndexACML($pid, $xdis_id) {
    $stmt = "SELECT
				* 
			 FROM
				" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
				" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1,
				" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1
			 WHERE
				r1.rmf_rec_pid = '".$pid."'";
//		echo $stmt;
		$returnfields = array("title", "description", "ret_id", "xdis_id", "sta_id");
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        //$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
//		print_r($res);
		$return = array();
		foreach ($res as $result) {
			if (in_array($result['xsdmf_fez_title'], $returnfields)) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$return[$result['rmf_rec_pid']][$result['xsdmf_fez_title']] = $result['rmf_'.$result['xsdmf_data_type']];
			}
		}
		$return = array_values($return);
//		print_r($return);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
/*            for ($i = 0; $i < count($res); $i++) {
                $res[$i]["projects"] = @implode(", ", array_values(XSD_HTML_Match::getAssociatedCollections($res[$i]["fld_id"])));
                if (($res[$i]["fld_type"] == "combo") || ($res[$i]["fld_type"] == "multiple")) {
                    $res[$i]["field_options"] = @implode(", ", array_values(XSD_HTML_Match::getOptions($res[$i]["fld_id"])));
                }
            }
*/
            return $return;
        }
	}
	
	function getACML($pid, $xdis_id) {
		$parents_details = Record::getParents($pid);

		$DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, 'FezACML');
		$xmlACML = @$DSResultArray['stream'];		

		if ($xmlACML != "") {
			$xmldoc= new DomDocument();
			$xmldoc->preserveWhiteSpace = false;
			$xmldoc->loadXML($xmlACML);
            return $xmldoc;
		} else {
			return false;
		}
	}


    /**
     * Method used to get the details for a specific Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  array The details for the specified Record
     */
    function getDetails($pid, $xdis_id)
    {

		// Get the Datastreams.
		$datastreamTitles = XSD_Loop_Subelement::getDatastreamTitles($xdis_id);
		foreach ($datastreamTitles as $dsValue) {
			$DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $dsValue['xsdsel_title']);
            if (isset($DSResultArray['stream'])) {
                $xmlDatastream = $DSResultArray['stream'];
                $xsd_id = XSD_Display::getParentXSDID($dsValue['xsdmf_xdis_id']);
                $xsd_details = Doc_Type_XSD::getDetails($xsd_id);
                $xsd_element_prefix = $xsd_details['xsd_element_prefix'];
                $xsd_top_element_name = $xsd_details['xsd_top_element_name'];

                $xmlnode = new DomDocument();
                $xmlnode->loadXML($xmlDatastream);
                $array_ptr = array();
                Misc::dom_xml_to_simple_array($xmlnode, $array_ptr, $xsd_top_element_name, $xsd_element_prefix, $xsdmf_array, $xdis_id);
            }
		}

		return $xsdmf_array;
    }


    /**
     * Method used to assign a list of Records to a specific user.
     *
     * @access  public
     * @return  boolean
     */
    function assign()
    {
        global $HTTP_POST_VARS;

        for ($i = 0; $i < count($HTTP_POST_VARS["item"]); $i++) {
            // check if the bug is already assigned to this person
            $stmt = "SELECT
                        COUNT(*) AS total
                     FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user
                     WHERE
                        isu_rec_id=" . $HTTP_POST_VARS["item"][$i] . " AND
                        isu_usr_id=" . $HTTP_POST_VARS["users"];
            $total = $GLOBALS["db_api"]->dbh->getOne($stmt);
            if ($total > 0) {
                continue;
            } else {
                // add the assignment
                $stmt = "INSERT INTO
                            " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_user
                         (
                            isu_rec_id,
                            isu_usr_id
                         ) VALUES (
                            " . $HTTP_POST_VARS["item"][$i] . ",
                            " . $HTTP_POST_VARS["users"] . "
                         )";
                $GLOBALS["db_api"]->dbh->query($stmt);
                // add the assignment to the history of the Record
                $summary = 'Record assigned to ' . User::getFullName($HTTP_POST_VARS["users"]) . ' by ' . User::getFullName(Auth::getUserID());
                History::add($HTTP_POST_VARS["item"][$i], Auth::getUserID(), History::getTypeID('user_associated'), $summary);
            }
        }
        return true;
    }


    /**
     * Method used to set the initial impact analysis for a specific Record
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function setImpactAnalysis($record_id)
    {
        global $HTTP_POST_VARS;

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 SET
                    rec_updated_date='" . Date_API::getCurrentDateGMT() . "',
                    rec_developer_est_time=" . $HTTP_POST_VARS["dev_time"] . ",
                    rec_impact_analysis='" . Misc::escapeString($HTTP_POST_VARS["impact_analysis"]) . "'
                 WHERE
                    rec_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // add the impact analysis to the history of the Record
            $summary = 'Initial Impact Analysis for Record set by ' . User::getFullName(Auth::getUserID());
            History::add($record_id, Auth::getUserID(), History::getTypeID('impact_analysis_added'), $summary);
            return 1;
        }
    }


    /**
     * Method used to get the full list of Record IDs that area available in the
     * system.
     *
     * @access  public
     * @param   string $extra_condition An extra condition in the WHERE clause
     * @return  array The list of Record IDs
     */
    function getColList($extra_condition = NULL)
    {
        $stmt = "SELECT
                    rec_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_col_id=" . Auth::getCurrentCollection();
        if (!empty($extra_condition)) {
            $stmt .= " AND $extra_condition ";
        }
        $stmt .= "
                 ORDER BY
                    rec_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the full list of Record IDs that area available in the
     * system. - CK - This gets all Collections Records
     *
     * @access  public
     * @param   string $extra_condition An extra condition in the WHERE clause
     * @return  array The list of Record IDs
     */
    function getColListAll($extra_condition = NULL)
    {
        $stmt = "SELECT
                    rec_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record";
        if (!empty($extra_condition)) {
            $stmt .= " WHERE $extra_condition ";
        }
        $stmt .= "
                 ORDER BY
                    rec_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the full list of Record IDs and their respective 
     * titles.
     *
     * @access  public
     * @param   string $extra_condition An extra condition in the WHERE clause
     * @return  array The list of Records
     */
    function getAssocList($extra_condition = NULL)
    {
        $stmt = "SELECT
                    rec_id,
                    rec_summary
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record
                 WHERE
                    rec_col_id=" . Auth::getCurrentCollection();
        if (!empty($extra_condition)) {
            $stmt .= " AND $extra_condition ";
        }
        $stmt .= "
                 ORDER BY
                    rec_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the full list of Record IDs and their respective 
     * titles.
     *
     * @access  public
     * @param   string $extra_condition An extra condition in the WHERE clause
     * @return  array The list of Records
     */
    function getAssocListAll($extra_condition = NULL)
    {
        $stmt = "SELECT
                    rec_id,
                    rec_summary
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record";
        if (!empty($extra_condition)) {
            $stmt .= " WHERE $extra_condition ";
        }
        $stmt .= "
                 ORDER BY
                    rec_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the list of Records associated to a specific Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  array The list of associated Records
     */
    function getAssociatedRecords($record_id)
    {
        $stmt = "SELECT
                    isa_associated_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record_association
                 WHERE
                    isa_Record_id=$record_id";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to check whether an Record was already closed or not.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  boolean
     */
    function isClosed($record_id)
    {
        $stmt = "SELECT
                    COUNT(*)
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "Record,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "status
                 WHERE
                    rec_id=$record_id AND
                    rec_sta_id=sta_id AND
                    sta_is_closed=1";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            if ($res == 0) {
                return false;
            } else {
                return true;
            }
        }
    }

    function getRecordXDIS_ID() {
		// will make this more dynamic later. (probably feed from a mysql table which can be configured in the gui admin interface).
		$xdis_id = 5;
		return $xdis_id;
    }

    /**
     * getAssocChildren
     * Finds children of the pid that don't have their own ACML set
     */
    function getAssocChildren($checkpids, &$checkedpids)
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX; // Database and table prefix
        $newpids = array();
        // strip out pids we've already checked
        $checkpids = array_diff($checkpids, $checkedpids);
        $pids_str = implode("','", $checkpids);
        // find the child items
        // ruling out items that have an ACML set
        $stmt = "SELECT DISTINCT rmf1.rmf_rec_pid as pid FROM {$dbtp}record_matching_field rmf1
            INNER JOIN {$dbtp}record_matching_field rmf2 ON rmf2.rmf_rec_pid=rmf1.rmf_rec_pid
            WHERE rmf1.rmf_varchar IN ('$pids_str')
            AND rmf1.rmf_xsdmf_id IN 
            (SELECT xsdmf_id FROM {$dbtp}xsd_display_matchfields
             WHERE
             xsdmf_element='!description!isMemberOf!resource')
            AND NOT(rmf2.rmf_xsdmf_id IN
            (SELECT xsdmf_id FROM {$dbtp}xsd_display_matchfields
             WHERE
             xsdmf_element LIKE '!rule!role!%User'))
            ";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        $checkedpids = array_unique($checkedpids + $checkpids);
        $newpids = array_unique($newpids + $res);
        if (!empty($newpids)) {
            $newpids = array_unique($newpids + Record::getAssocChildren($newpids, $checkedpids));
        }
        return $newpids;
    }

    /**
      * getAssociated
      * Find all issues where the user has a role in the ACML
      * This includes cascading affect of ACML
      */
    function getAssociated($username)
    {
        // find PIDs and role_names where the current user belongs to an ACML role
        // The following query doesn't check for group membership yet (could use a rmf_varchar IN ($groups_list_str) 
        // where the $groups_list_str is obtained from Auth or User class.
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX; // Database and table prefix
        $stmt = "SELECT rmf1.rmf_rec_pid as pid , xdm1.xsdmf_parent_key_match as role, 
            count(rmf2.rmf_rec_pid) AS subs FROM 
        {$dbtp}record_matching_field rmf1
        INNER JOIN {$dbtp}xsd_display_matchfields xdm1 ON rmf1.rmf_xsdmf_id=xdm1.xsdmf_id
        LEFT JOIN {$dbtp}record_matching_field rmf2 ON rmf1.rmf_rec_pid=rmf2.rmf_varchar
            WHERE
            rmf1.rmf_varchar='$username'
            AND (xsdmf_element='!rule!role!AD_User'
                    OR xsdmf_element='!rule!role!Fez_User')
            AND rmf2.rmf_xsdmf_id IN 
            (SELECT xsdmf_id FROM fez_xsd_display_matchfields
             WHERE
             xsdmf_element='!description!isMemberOf!resource')
            GROUP BY rmf1.rmf_rec_pid
        ";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        foreach ($res as $row) {
            if ($row['subs'] > 0) {
                $checkedpids = array();
                $pids = Record::getAssocChildren(array($row['pid']), $checkedpids);
                foreach ($pids as $child) {
                    $res[] = array('pid' => $child, 'role' => $row['role']); 
                }
            }
        }
        return $res;


    }

    /**
     * getAssigned
     * Find records where the user is assigned to the issue.  That's where the issue is marked with a 
     * status and a role that the user is part of
     */
    function getAssigned($username)
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX; // Database and table prefix
        // for each PID  / role_name combo, check for matches in the status_roles table
        // i.e. find the sta_id for each of the pids and see if there is a role match for that sta_id in the 
        // status_roles table.
        $pidroles = Record::getAssociated($username);
        $roles = Misc::collate2ColArray($pidroles, 'role', 'pid', true);
        $result = array();
        foreach ($roles as $role => $pids) {
            $pids_str = implode("','", $pids);
            $stmt = "SELECT rmf1.rmf_rec_pid as pid, sr.str_sta_id as sta_id, sr.str_role_name as role, 
                s1.sta_title as sta_title, rmf2.rmf_varchar as title FROM
            {$dbtp}record_matching_field rmf1 
            INNER JOIN {$dbtp}status_roles sr ON sr.str_sta_id=rmf1.rmf_varchar
            LEFT JOIN {$dbtp}status s1 ON sr.str_sta_id=s1.sta_id
            LEFT JOIN {$dbtp}record_matching_field rmf2 ON rmf1.rmf_rec_pid=rmf2.rmf_rec_pid
                WHERE
                sr.str_role_name='$role'
                AND rmf1.rmf_rec_pid IN ('$pids_str')
                AND rmf1.rmf_xsdmf_id IN (SELECT xsdmf_id FROM {$dbtp}xsd_display_matchfields
                        WHERE xsdmf_element='!sta_id')
                AND rmf2.rmf_xsdmf_id IN (SELECT xsdmf_id FROM {$dbtp}xsd_display_matchfields
                        WHERE xsdmf_element='!dc:title')
                ";
            $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            }
            $result = $result + $res;
        }
        return $result;
    }
}


/**
 * class RecordGeneral
 * For general record stuff - shared by collections and communities as well as records. 
 */
class RecordGeneral
{
    var $pid;
    var $xdis_id;
    var $no_xdis_id = false;  // true if we couldn't find the xdis_id
    var $viewer_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator"); 
    var $editor_roles;
    var $creator_roles;
    var $checked_auth = false;
    var $auth_groups;

    /**
     * RecordObject
     * If instantiated with a pid, then this object is linked with the record with the pid, otherwise we are inserting
     * a record.
     */
    function RecordGeneral($pid=null)
    {
        $this->pid = $pid;
        $this->editor_roles = Misc::array_clean($this->viewer_roles, "Viewer");
        $this->creator_roles = $this->editor_roles;
    }

    /**
      * refresh
      * Reset the status of the record object so that all values will be re-queried from the database.
      * Call this function if the database is expected to have changed in relation to this record.
      */
    function refresh()
    {
        $this->checked_auth = false;
    }

    /**
     * getXmlDisplayId
     * Retrieve the display id for this record
     */
    function getXmlDisplayId() {
        if (!$this->no_xdis_id) {
            if (is_null($this->xdis_id)) {
                $xdis_array = Fedora_API::callGetDatastreamContentsField($this->pid, 'FezMD', array('xdis_id'));
                if (isset($xdis_array['xdis_id'][0])) {
                    $this->xdis_id = $xdis_array['xdis_id'][0];
                } else {
                    $this->no_xdis_id = true;
                    return null;
                }
            }
            return $this->xdis_id;
        }
        return null;
    }



    /**
     * getAuth
     * Retrieve the authroisation groups allowed for this record with the current user.
     */
    function getAuth() {
        if (!$this->checked_auth) {
            $this->getXmlDisplayId();
            $this->auth_groups = Auth::getAuthorisationGroups($this->pid, $this->xdis_id);
            $this->checked_auth = true;
        }
		
        return $this->auth_groups;
    }

    /**
     * checkAuth
     * Find out if the current user can perform the given roles for this record
     */
    function checkAuth($roles, $redirect=true) {
        global $HTTP_SERVER_VARS;
        $this->getAuth();
		return Auth::checkAuthorisation($this->pid, $this->xdis_id, $roles, 
                    $HTTP_SERVER_VARS['PHP_SELF']."?".$HTTP_SERVER_VARS['QUERY_STRING'], $this->auth_groups, $redirect); 
    }
    
    /**
     * canView 
     * Find out if the current user can view this record
     */
    function canView($redirect=true) {
        return $this->checkAuth($this->viewer_roles, $redirect);
    }
    
    /**
     * canEdit
     * Find out if the current user can edit this record
     */
    function canEdit($redirect=false) {
        return $this->checkAuth($this->editor_roles, $redirect);
    }

    /**
     * canCreate
     * Find out if the current user can create this record
     */
    function canCreate($redirect=false) {
        return $this->checkAuth($this->creator_roles, $redirect);
    }


}

/**
  * RecordObject
  * Manages the interface to the database and fedora for records.
  * Stores local copies of record properties to save multiple accesses to the database.
  */
class RecordObject extends RecordGeneral
{
    var $created_date;
    var $updated_date;	
    var $file_downloads; //for statistics of file datastream downloads from eserv.php
    var $default_xdis_id = 5;
    var $display;
    var $details;
    var $record_parents;
    
    /**
     * getXmlDisplayId
     * Retrieve the display id for this record
     */
    function getObjectDates() {
		$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
		if (isset($xdis_array['created_date'][0])) {
			$this->created_date = $xdis_array['created_date'][0];
		} else {
			$this->created_date = NULL;
		}
		if (isset($xdis_array['updated_date'][0])) {
			$this->updated_date = $xdis_array['updated_date'][0];
		} else {
			$this->updated_date = NULL;
		}

    }

    /**
     * getFileDownloadsCount
     * Retrieve the count of file downloads for this record
     */
    function getFileDownloadsCount() {
		$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
//		print_r($xdis_array);
		if (is_numeric(trim($xdis_array['file_downloads'][0]))) {
			$this->file_downloads = trim($xdis_array['file_downloads'][0]);
		} else {
			$this->file_downloads = 0;
		}
    }
    
    /**
     * updateAdminDatastream
     * Used to assocaiate a display for this record
     */
    function updateAdminDatastream($xdis_id) {
		$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
        $this->xdis_id = $xdis_id;
		$newXML = '<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">';
		$foundElement = false;
		foreach ($xdis_array as $xkey => $xdata) {
			foreach ($xdata as $xinstance) {
				if ($xkey == "xdis_id") {
					$foundElement = true;
					$newXML .= "<".$xkey.">".$this->xdis_id."</".$xkey.">";				
				} elseif ($xinstance != "") {
					$newXML .= "<".$xkey.">".$xinstance."</".$xkey.">";
				}
			}
		}
		if ($foundElement != true) {
			$newXML .= "<xdis_id>".$this->xdis_id."</xdis_id>";
		}
		$newXML .= "</FezMD>";
//		echo $newXML;
		if ($newXML != "") {
			Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", true);
			$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement("!xdis_id", 15);
			Record::removeIndexRecordByXSDMF_ID($this->pid, $xsdmf_id);
			Record::insertIndexMatchingField($this->pid, $xsdmf_id, "varchar", $this->xdis_id);
		}
    }

    function incrementFileDownloads() {
		$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
//		print_r($xdis_array);
		if (isset($xdis_array['file_downloads'][0])) {
			$this->file_downloads = $xdis_array['file_downloads'][0];
		} else {
			$this->file_downloads = 0;
		}
		$this->file_downloads++;
		$newXML = '<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">';
		$foundElement = false;
		foreach ($xdis_array as $xkey => $xdata) {
			foreach ($xdata as $xinstance) {
				if ($xkey == "file_downloads") {
					$foundElement = true;
					$newXML .= "<".$xkey.">".$this->file_downloads."</".$xkey.">";				
				} elseif ($xinstance != "") {
					$newXML .= "<".$xkey.">".$xinstance."</".$xkey.">";
				}
			}
		}
		if ($foundElement != true) {
			$newXML .= "<file_downloads>".$this->file_downloads."</file_downloads>";
		}
		$newXML .= "</FezMD>";
//		echo $newXML;
		if ($newXML != "") {
			Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", true);
			$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement("!file_downloads", 15);
			Record::removeIndexRecordByXSDMF_ID($this->pid, $xsdmf_id);
			Record::insertIndexMatchingField($this->pid, $xsdmf_id, "int", $this->file_downloads);
	    }
    }

    /**
     * fedoraInsertUpdate 
     * Process a submitted record insert or update form
     */
    function fedoraInsertUpdate()
    {
        global $HTTP_POST_VARS, $HTTP_POST_FILES;

        // If pid is null then we need to ingest the object as well
        // otherwise we are updating an existing object
        $ingestObject = false;
		$existingDatastreams = array();
        if (empty($this->pid)) {
            $this->pid = Fedora_API::getNextPID();
            $ingestObject = true;
			$this->created_date = date("Y-m-d H:i:s");
			$this->updated_date = $created_date;
        } else {
			$existingDatastreams = Fedora_API::callGetDatastreams($this->pid);
			$this->getObjectDates();
			if (empty($this->created_date)) {
				$this->created_date = date("Y-m-d H:i:s");
			}
			$this->updated_date = date("Y-m-d H:i:s");
		}
        $pid = $this->pid;

            
        if (empty($this->xdis_id)) {
            $this->xdis_id = $HTTP_POST_VARS["xdis_id"];
        }
        $xdis_id = $this->xdis_id;
        $this->getDisplay();
        $display = &$this->display;
        list($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema) 
            = $display->getXsdAsReferencedArray();

		$xmlObj = '<?xml version="1.0"?>'."\n";
		$xmlObj .= "<".$xsd_element_prefix.$xsd_top_element_name." ";
		$xmlObj .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid); // for the pid, fedora uri etc
		$xmlObj .= $xml_schema;
		$xmlObj .= ">\n";

 		// @@@ CK - 6/5/2005 - Added xdis so xml building could search using the xml display ids
		$indexArray = array();
		if (!is_numeric($this->file_downloads)) {
			$this->getFileDownloadsCount();
		} 
		$file_downloads = $this->file_downloads;

		$xmlObj = Misc::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "", $indexArray, $file_downloads, $this->created_date, $this->updated_date);

		$xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">";

		$datastreamTitles = $display->getDatastreamTitles();
		$params = array();
//		echo $xmlObj;
		$datastreamXMLHeaders = Misc::getDatastreamXMLHeaders($datastreamTitles, $xmlObj, $existingDatastreams);
//		print_r($datastreamTitles);
		
		print_r($datastreamXMLHeaders);
		if (is_array($datastreamXMLHeaders["File_Attachment0"])) { // it must be a multiple file upload so remove the generic one
			$datastreamXMLHeaders = Misc::array_clean_key($datastreamXMLHeaders, "File_Attachment", true, true);
		}
		if (is_array($datastreamXMLHeaders["Link0"])) { // it must be a multiple file upload so remove the generic one
			$datastreamXMLHeaders = Misc::array_clean_key($datastreamXMLHeaders, "Link", true, true);
		}

//		print_r($datastreamXMLHeaders);
//		echo $xmlObj;
		$datastreamXMLContent = Misc::getDatastreamXMLContent($datastreamXMLHeaders, $xmlObj);
//		print_r($datastreamXMLContent);

        if ($ingestObject) {
            // Actually Ingest the object Into Fedora
            // We only have to do this when first creating the object, subsequent updates should just work with the 
            // datastreams.
            // will have to exclude the non X control group xml and add the datastreams after the base ingestion.

//            $xmlObj = Misc::removeNonXMLDatastreams($datastreamTitles, $xmlObj);
            $xmlObj = Misc::removeNonXMLDatastreams($datastreamXMLHeaders, $xmlObj);

            $config = array(
                    'indent'         => true,
                    'input-xml'   => true,
                    'output-xml'   => true,
                    'wrap'           => 200);

            $tidy = new tidy;
            $tidy->parseString($xmlObj, $config, 'utf8');
            $tidy->cleanRepair();
            $xmlObj = $tidy;
            Fedora_API::callIngestObject($xmlObj);
        }
//		print_r($indexArray);
//			echo $xmlObj;        
		$convert_check = false;
//		print_r($datastreamXMLHeaders);

//		print_r($datastreamXMLHeaders);
//		print_r($datastreamXMLContent);
//		print_r($indexArray);
		Record::insertIndexBatch($pid, $indexArray, $datastreamXMLHeaders);
//		echo "INDEX ARRAY -> ";
//		print_r($indexArray);
//		print_r($datastreamTitles);
//		print_r($datastreamXMLHeaders);
//		foreach ($datastreamTitles as $dsTitle) {
		foreach ($datastreamXMLHeaders as $dsKey => $dsTitle) {
			$dsIDName = $dsTitle['ID'];

			if (is_numeric(strpos($dsIDName, "."))) {
				$filename_ext = strtolower(substr($dsIDName, (strrpos($dsIDName, ".") + 1)));
				$dsIDName = substr($dsIDName, 0, strrpos($dsIDName, ".") + 1).$filename_ext;
			}

			if (Fedora_API::datastreamExists($pid, $dsTitle['ID'])) {
				Fedora_API::callModifyDatastreamByValue($pid, $dsIDName, $dsTitle['STATE'], $dsTitle['LABEL'], $datastreamXMLContent[$dsKey], $dsTitle['MIMETYPE'], $dsTitle['VERSIONABLE']);
			} else {
				if ($dsTitle['CONTROL_GROUP'] == "R") { // if its a redirect we don't need to upload the file
//				    echo "R content = ".$datastreamXMLContent[$dsKey];
					Fedora_API::callAddDatastream($pid, $datastreamXMLContent[$dsKey], $dsTitle['LABEL'], $dsTitle['STATE'], $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
				} else {
					Fedora_API::getUploadLocation($pid, $dsIDName, $datastreamXMLContent[$dsKey], $dsTitle['LABEL'], $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
				}
			}

            
			$presmd_check = Workflow::checkForPresMD($dsIDName);
			if ($presmd_check != false) {
				Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check, $presmd_check, "text/xml", "X");
			}


            Workflow::processIngestTrigger($pid, $xdis_id, $dsTitle);
            
            // workflow step 1
			// Now check for post upload workflow events like thumbnail resizing of images
			$convert_check = Workflow::checkForImageFile($dsIDName);
			if ($convert_check != false) {
				Fedora_API::getUploadLocationByLocalRef($pid, $convert_check, $convert_check, $convert_check, $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
				if (is_numeric(strpos($convert_check, "/"))) {
					$convert_check = substr($convert_check, strrpos($convert_check, "/")+1); // take out any nasty slashes from the ds name itself
				}
				$convert_check = str_replace(" ", "_", $convert_check);
				Record::insertIndexMatchingField($pid, 122, "varchar", $convert_check); // add the thumbnail to the fez index
			}


		} 
		return $pid;
    }
    
    /**
     * getDisplay
     * Get a display object for this record
     */ 
    function getDisplay()
    {
        $this->getXmlDisplayId();
        if (!empty($this->xdis_id)) {
            if (is_null($this->display)) {
                $this->display = new XSD_DisplayObject($this->xdis_id);
            }
            return $this->display;
        } else {
            return null;
        }
    }

    /**
     * getDetails
     * Users a more object oriented approach with the goal of storing query results so that we don't need to make 
     * so many queries to view a record.
     */
    function getDetails()
    {
        if (is_null($this->details)) {
            // Get the Datastreams.
            $this->getDisplay();
            $this->details = $this->display->getXSDMF_Values($this->pid);
        }
        return $this->details;
    }

    /**
      * getTitle
      * Get the dc:title for the record
      */
    function getTitle()
    {
        $this->getDetails();
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!dc:title'); 
        return $this->details[$xsdmf_id];
    }

    function getDCType()
    {
        $this->getDetails();
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!dc:type'); 
        return $this->details[$xsdmf_id];
    }

    function isCollection()
    {
        return ($this->getDCType() == 'Fez_Collection') ? true : false;
    }

    function isCommunity()
    {
        return ($this->getDCType() == 'Fez_Community') ? true : false;
    }


    function getParents()
    {
        if (!$this->record_parents) {
            $this->record_parents = Record::getParents($this->pid);
        }
        return $this->record_parents;
    }

    function getWorkflowsByTrigger($trigger)
    {
        $this->getParents();
        $triggers = WorkflowTrigger::getListByTrigger($this->pid, $trigger);
        foreach ($this->record_parents as $ppid) {
            $triggers = array_merge($triggers, WorkflowTrigger::getListByTrigger($ppid, $trigger));
        }
        // get defaults
        $triggers = array_merge($triggers, WorkflowTrigger::getListByTrigger(-1, $trigger));
        return $triggers;
    }

    function getWorkflowsByTriggerAndXDIS_ID($trigger, $xdis_id)
    {
        $this->getParents();
        $triggers = WorkflowTrigger::getListByTriggerAndXDIS_ID($this->pid, $trigger, $xdis_id);
        foreach ($this->record_parents as $ppid) {
            $triggers = array_merge($triggers, WorkflowTrigger::getListByTriggerAndXDIS_ID($ppid, $trigger, $xdis_id));
        }
        // get defaults
        $triggers = array_merge($triggers, WorkflowTrigger::getListByTriggerAndXDIS_ID(-1, $trigger, $xdis_id));
        return $triggers;
    }


}


// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Record Class');
}
?>
