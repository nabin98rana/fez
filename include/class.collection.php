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
// @(#) $Id: s.class.collection.php 1.36 04/01/07 20:59:37-00:00 jpradomaia $
//


/**
 * Class to handle the business logic related to the administration
 * of collections in the system.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.auth.php");
//include_once(APP_INC_PATH . "class.filter.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.status.php");

class Collection
{
    /**
     * Method used to get the outgoing email sender address associated with
     * a given collection.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @return  array The outgoing sender information
     */
    function getOutgoingSenderAddress($col_id)
    {
        $stmt = "SELECT
                    col_outgoing_sender_name,
                    col_outgoing_sender_email
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 WHERE
                    col_id=$col_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array(
                'name'  => '',
                'email' => ''
            );
        } else {
            if (!empty($res)) {
                return array(
                    'name'  => $res['col_outgoing_sender_name'],
                    'email' => $res['col_outgoing_sender_email']
                );
            } else {
                return array(
                    'name'  => '',
                    'email' => ''
                );
            }
        }
    }


    /**
     * Method used to get the initial status that should be set to a new issue
     * created and associated with a given collection.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @return  integer The status ID
     */
    function getInitialStatus($col_id)
    {
        $stmt = "SELECT
                    col_initial_sta_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 WHERE
                    col_id=$col_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the options related to the anonymous posting
     * of new issues.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @return  array The anonymous posting options
     */
    function getAnonymousPostOptions($col_id)
    {
        $stmt = "SELECT
                    col_anonymous_post_options
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 WHERE
                    col_id=$col_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (!is_string($res)) {
                $res = (string) $res;
            }
            return @unserialize($res);
        }
    }


    /**
     * Method used to update the anonymous posting related options.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function updateAnonymousPost($col_id)
    {
        global $HTTP_POST_VARS;

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 SET
                    col_anonymous_post='" . $HTTP_POST_VARS["anonymous_post"] . "',
                    col_anonymous_post_options='" . @serialize($HTTP_POST_VARS["options"]) . "'
                 WHERE
                    col_id=$col_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return 1;
        }
    }


    /**
     * Method used to get the list of collections that allow anonymous
     * posting of new issues.
     *
     * @access  public
     * @return  array The list of collections
     */
    function getAnonymousList()
    {
        $stmt = "SELECT
                    col_id,
                    col_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 WHERE
                    col_anonymous_post='enabled'
                 ORDER BY
                    col_title";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to check whether a collection exists or not.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @return  boolean
     */
    function exists($col_id)
    {
        $stmt = "SELECT
                    COUNT(*) AS total
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 WHERE
                    col_id=$col_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            if ($res > 0) {
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * Method used to get the collection ID of the given collection title.
     *
     * @access  public
     * @param   string $col_title The collection title
     * @return  integer The collection ID
     */
    function getID($col_title)
    {
        $stmt = "SELECT
                    col_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 WHERE
                    col_title='$col_title'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the title of a given collection ID.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @return  string The collection title
     */
    function getName($col_id)
    {
        static $returns;

        if (!empty($returns[$col_id])) {
            return $returns[$col_id];
        }

        $stmt = "SELECT
                    col_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 WHERE
                    col_id=$col_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            $returns[$col_id] = $res;
            return $res;
        }
    }


    /**
     * Method used to get the details for a given collection ID.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @return  array The collection details
     */
    function getDetails($collection_pid)
    {
		$details = Fedora_API::getObjectXML($collection_pid);
		return $details;
    }


    /**
     * Method used to remove a given set of collections from the system.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 WHERE
                    col_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            Collection::removeUserByCollections($HTTP_POST_VARS["items"]);
            Category::removeByCollections($HTTP_POST_VARS["items"]);
//            Release::removeByCollections($HTTP_POST_VARS["items"]);
//            Filter::removeByCollections($HTTP_POST_VARS["items"]);
//            Email_Account::removeAccountByCollections($HTTP_POST_VARS["items"]);
            Record::removeByCollections($HTTP_POST_VARS["items"]);
            Custom_Field::removeByCollections($HTTP_POST_VARS["items"]);
            $statuses = array_keys(Status::getAssocStatusList($HTTP_POST_VARS["items"]));
            foreach ($HTTP_POST_VARS["items"] as $col_id) {
                Status::removeCollectionAssociations($statuses, $col_id);
            }
            return true;
        }
    }


    /**
     * Method used to remove all collection/user associations for a given
     * set of collections.
     *
     * @access  public
     * @param   array $ids The collection IDs
     * @return  boolean
     */
    function removeUserByCollections($ids)
    {
        $items = @implode(", ", $ids);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection_user
                 WHERE
                    pru_col_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }


    /**
     * Method used to update the details of the collection information.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function update()
    {
        global $HTTP_POST_VARS;

        if (Validation::isWhitespace($HTTP_POST_VARS["title"])) {
            return -2;
        }
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 SET
                    col_title='" . Misc::escapeString($HTTP_POST_VARS["title"]) . "',
                    col_status='" . Misc::escapeString($HTTP_POST_VARS["status"]) . "',
                    col_lead_usr_id=" . $HTTP_POST_VARS["lead_usr_id"] . ",
                    col_initial_sta_id=" . $HTTP_POST_VARS["initial_status"] . ",
                    col_outgoing_sender_name='" . Misc::escapeString($HTTP_POST_VARS["outgoing_sender_name"]) . "',
                    col_outgoing_sender_email='" . Misc::escapeString($HTTP_POST_VARS["outgoing_sender_email"]) . "',
                    col_remote_invocation='" . Misc::escapeString($HTTP_POST_VARS["remote_invocation"]) . "'
                 WHERE
                    col_id=" . $HTTP_POST_VARS["id"];
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            Collection::removeUserByCollections(array($HTTP_POST_VARS["id"]));
            for ($i = 0; $i < count($HTTP_POST_VARS["users"]); $i++) {
                Collection::associateUser($HTTP_POST_VARS["id"], $HTTP_POST_VARS["users"][$i]);
            }
            $statuses = array_keys(Status::getAssocStatusList($HTTP_POST_VARS["id"]));
            if (count($statuses) > 0) {
                Status::removeCollectionAssociations($statuses, $HTTP_POST_VARS["id"]);
            }
            foreach ($HTTP_POST_VARS['statuses'] as $sta_id) {
                Status::addCollectionAssociation($sta_id, $HTTP_POST_VARS["id"]);
            }
            return 1;
        }
    }


    /**
     * Method used to associate an user to a collection.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @param   integer $usr_id The user ID
     * @return  boolean
     */
    function associateUser($col_id, $usr_id)
    {
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection_user
                 (
                    pru_usr_id,
                    pru_col_id
                 ) VALUES (
                    $usr_id,
                    $col_id
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
     * Method used to add a new collection to the system.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 or -2 otherwise
     */
    function insert()
    {
        global $HTTP_POST_VARS;

        if (Validation::isWhitespace($HTTP_POST_VARS["title"])) {
            return -2;
        }
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 (
                    col_created_date,
                    col_title,
                    col_status,
                    col_lead_usr_id,
                    col_initial_sta_id,
                    col_outgoing_sender_name,
                    col_outgoing_sender_email,
                    col_remote_invocation
                 ) VALUES (
                    '" . Date_API::getCurrentDateGMT() . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["title"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["status"]) . "',
                    " . $HTTP_POST_VARS["lead_usr_id"] . ",
                    " . $HTTP_POST_VARS["initial_status"] . ",
                    '" . Misc::escapeString($HTTP_POST_VARS["outgoing_sender_name"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["outgoing_sender_email"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["remote_invocation"]) . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            $new_col_id = $GLOBALS["db_api"]->get_last_insert_id();
            for ($i = 0; $i < count($HTTP_POST_VARS["users"]); $i++) {
                Collection::associateUser($new_col_id, $HTTP_POST_VARS["users"][$i]);
            }
            foreach ($HTTP_POST_VARS['statuses'] as $sta_id) {
                Status::addCollectionAssociation($sta_id, $new_col_id);
            }
            return 1;
        }
    }

    /**
     * Method used to get the list of collections available in the 
     * system.
     *
     * @access  public
     * @return  array The list of collections
     */
    function getParents($collection_pid)
    {

		$itql = "select \$collTitle \$collDesc \$title \$description \$object from <#ri>
					where  (<info:fedora/".$collection_pid."> <dc:title> \$collTitle) and
                    (<info:fedora/".$collection_pid."> <dc:description> \$collDesc) and
					(<info:fedora/".$collection_pid."> <fedora-rels-ext:isMemberOf> \$object ) and
					((\$object <dc:title> \$title) or
					(\$object <dc:description> \$description))
					order by \$title asc";

/*		$itql = "select \$object \$title \$identifier \$description \$member from <#ri>

					where  (<info:fedora/".$community_pid."> <dc:title> \$title) and

					where  (\$object <rdf:type> <fedora-model:FedoraObject>) and

					(\$member <fedora-rels-ext:isMemberOf> <info:fedora/".$community_pid.">) and
                    (\$object <dc:type> 'eSpace_Collection') and
					((\$object <dc:title> \$title) or 
					(\$object <dc:description> \$description) or
					(\$object <dc:identifier> \$identifier))
					order by \$title asc";
*/
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
     * Method used to get the list of collections available in the 
     * system.
     *
     * @access  public
     * @return  array The list of collections
     */
    function getList($community_pid)
    {

		$itql = "select \$collTitle \$collDesc \$title \$description \$object from <#ri>
					where  (<info:fedora/".$community_pid."> <dc:title> \$collTitle) and
                    (<info:fedora/".$community_pid."> <dc:description> \$collDesc) and
					(\$object <fedora-rels-ext:isMemberOf> <info:fedora/".$community_pid.">) and
					((\$object <dc:title> \$title) or
					(\$object <dc:description> \$description))
					order by \$title asc";

/*		$itql = "select \$object \$title \$identifier \$description \$member from <#ri>

					where  (<info:fedora/".$community_pid."> <dc:title> \$title) and

					where  (\$object <rdf:type> <fedora-model:FedoraObject>) and

					(\$member <fedora-rels-ext:isMemberOf> <info:fedora/".$community_pid.">) and
                    (\$object <dc:type> 'eSpace_Collection') and
					((\$object <dc:title> \$title) or 
					(\$object <dc:description> \$description) or
					(\$object <dc:identifier> \$identifier))
					order by \$title asc";
*/
//		echo $itql;
		$returnfields = array();
		array_push($returnfields, "pid"); 
		array_push($returnfields, "title");
		array_push($returnfields, "identifier");
		array_push($returnfields, "description");

		$details = Fedora_API::getITQLQuery($itql, $returnfields);
		foreach ($details as $key => $row) {
			$xdis_array = Fedora_API::callGetDatastreamContentsField ($row['pid'], 'eSpaceMD', array('xdis_id'));
			$xdis_id = $xdis_array[0]['xdis_id'];
			$rowAuthGroups = Auth::getAuthorisationGroups($row['pid'], $xdis_id);
			// get only the roles which are of relevance/use on the listing screen. This logic may be changed later.
			$details[$key]['isCommunityAdministrator'] = in_array('Community Administrator', $rowAuthGroups);
			$details[$key]['isEditor'] = in_array('Editor', $rowAuthGroups);
			$details[$key]['isCreator'] = in_array('Creator', $rowAuthGroups);
			$details[$key]['isViewer'] = in_array('Viewer', $rowAuthGroups);
			$details[$key]['isLister'] = in_array('Lister', $rowAuthGroups);
//			$details[$key]['isApprover'] = in_array('Approver', $rowAuthGroups); // probably not necessary at the listing stage
		}
		return $details;
    }

    /**
     * Method used to get the list of collections available in the 
     * system.
     *
     * @access  public
     * @return  array The list of collections
     */
    function getCollectionXDIS_ID()
    {
		// will make this more dynamic later. (probably feed from a mysql table which can be configured in the gui admin interface).
		$collection_xdis_id = 9;
		return $collection_xdis_id;
    }

    /**
     * Method used to get the list of collection records available in the 
     * system.
     *
     * @access  public
     * @return  array The list of collection records with the given collection pid
     */
    function getListing($collection_pid)
    {
		$itql = "select \$collTitle \$collDesc \$title \$identifier \$description \$object from <#ri>
					where  (<info:fedora/".$collection_pid."> <dc:title> \$collTitle) and
                    (<info:fedora/".$collection_pid."> <dc:description> \$collDesc) and
					(\$object <fedora-rels-ext:isMemberOf> <info:fedora/".$collection_pid.">) and
					((\$object <dc:identifier> \$identifier) or
					(\$object <dc:title> \$title) or
					(\$object <dc:description> \$description))
					order by \$title asc";

//		echo $itql;
		$returnfields = array();
		array_push($returnfields, "pid"); 
		array_push($returnfields, "title");
		array_push($returnfields, "identifier");
		array_push($returnfields, "description");
		array_push($returnfields, "creator");

		$details = Fedora_API::getITQLQuery($itql, $returnfields);
		foreach ($details as $key => $row) {
			$xdis_array = Fedora_API::callGetDatastreamContentsField ($row['pid'], 'eSpaceMD', array('xdis_id'));
			$xdis_id = $xdis_array[0]['xdis_id'];
			$rowAuthGroups = Auth::getAuthorisationGroups($row['pid'], $xdis_id);
			$details[$key]['isEditor'] = in_array('Editor', $rowAuthGroups);
			$details[$key]['isApprover'] = in_array('Approver', $rowAuthGroups);
			$details[$key]['isViewer'] = in_array('Viewer', $rowAuthGroups);
			$details[$key]['isLister'] = in_array('Lister', $rowAuthGroups);
		}

		return $details;
    }


    /**
     * Method used to get an associative array of collection ID and title
     * of all collections available in the system.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  array The list of collections
     */
    function getAssocList()
    {
		$details = Fedora_API::getListByTypeObjectsXMLAssoc("eSpace_Collection");
//		echo "collection details -> ";
//		print_r($details);
		return $details;
    }


    /**
     * Method used to get the list of users associated with a given collection.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @param   string $status The desired user status
     * @param   integer $role The role ID of the user
     * @return  array The list of users
     */
    function getUserAssocList($col_id, $status = NULL, $role = NULL)
    {
		//@@@ CK - 18/1/2005 - Made the list of users shown to workstation support ($col_id of 3) show users in server team (col_id 4) as well
		// this change was made due to a request by Wendy in Eventum job number 1358.
		if ($col_id == 3) {
			$col_id = "3, 4";
		}
        $stmt = "SELECT
                    usr_id,
                    usr_full_name
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection_user
                 WHERE
                    pru_col_id in ($col_id) AND
                    pru_usr_id=usr_id AND
                    usr_id != " . APP_SYSTEM_USER_ID;
        if ($status != NULL) {
            $stmt .= " AND usr_status='active' ";
        }
        if ($role != NULL) {
            $stmt .= " AND usr_role > $role ";
        }
        $stmt .= "
                 ORDER BY
                    usr_full_name ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get a list of user IDs associated with a given
     * collection.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @return  array The list of user IDs
     */
    function getUserColList($col_id)
    {
        $stmt = "SELECT
                    usr_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection_user
                 WHERE
                    cou_col_id=$col_id AND
                    cou_usr_id=usr_id
                 ORDER BY
                    usr_full_name ASC";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get an associative array of collection ID and title
     * of all collections that exist in the system.
     *
     * @access  public
     * @return  array List of collections
     */
    function getAll()
    {
        $stmt = "SELECT
                    col_id,
                    col_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 ORDER BY
                    col_title";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get an associative array of collection ID and title
     * of all collections that exist in the system.
     *
     * @@@ CK - 22/10/2004 - Added this so the lists of collection could be sorted by a priority, eg server team first, then wss, then litlos
     *
     * @access  public
     * @return  array List of collections sorted by col_sorted_priority
     */
    function getAllSorted()
    {
        $stmt = "SELECT
                    col_id,
                    col_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 ORDER BY
                    col_sort_priority, col_title";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get an associative array of collection ID and title
     * of all collections that exist in the system except for the current persons team.
     *
	 * @@@ 12/07/04 CK - added this function
     * @access  public
     * @return  array List of collections
     */
    function getAllExcept($excluded_col_id)
    {
        $stmt = "SELECT
                    col_id,
                    col_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
				 WHERE col_id <> $excluded_col_id
                 ORDER BY
                    col_title";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get an associative array of collection ID and title
     * of all collections that exist in the system except for the current persons team.
     *
	 * @@@ 22/07/04 CK - added this function, same as getAllExcept, but collection sorted by col_sorted_priority
     * @access  public
     * @return  array List of collections
     */
    function getAllExceptSorted($excluded_col_id)
    {
        $stmt = "SELECT
                    col_id,
                    col_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
				 WHERE col_id <> $excluded_col_id
                 ORDER BY
                    col_sort_priority, col_title";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get a list of names and emails that are 
     * associated with a given collection and issue.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @param   integer $issue_id The issue ID
     * @return  array List of names and emails
     */
    function getAddressBook($col_id, $issue_id = FALSE)
    {
        $stmt = "SELECT
                    usr_full_name,
                    usr_email
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection_user
                 WHERE
                    pru_col_id=$col_id AND
                    pru_usr_id=usr_id AND
                    usr_id != " . APP_SYSTEM_USER_ID . "
                 ORDER BY
                    usr_full_name ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            $temp = array();
            for ($i = 0; $i < count($res); $i++) {
				if (!is_numeric(strpos($res[$i]["usr_full_name"], "AskIT"))) { //@@@ CK - 3/12/2004 -> added to remove any AskIT users from the address book as they don't have emails
					$key = $res[$i]["usr_full_name"] . " <" . $res[$i]["usr_email"] . ">";
					$temp[$key] = $res[$i]["usr_full_name"];
				}
            }
            return $temp;
        }
    }


    /**
     * Method used to get the list of collections that allow remote 
     * invocation of issues.
     *
     * @access  public
     * @return  array The list of collections
     */
    function getRemoteAssocList()
    {
        $stmt = "SELECT
                    col_id,
                    col_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection
                 WHERE
                    col_remote_invocation='enabled'
                 ORDER BY
                    col_title";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the list of collections assigned to a given user that 
     * allow remote invocation of issues.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  array The list of collections
     */
    function getRemoteAssocListByUser($usr_id)
    {
        static $returns;

        if (!empty($returns[$usr_id])) {
            return $returns[$usr_id];
        }

        $stmt = "SELECT
                    col_id,
                    col_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection_user
                 WHERE
                    col_id=cou_col_id AND
                    cou_usr_id=$usr_id AND
                    col_remote_invocation='enabled'
                 ORDER BY
                    col_title";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            $returns[$usr_id] = $res;
            return $res;
        }
    }


    /**
     * Method used to get the list of users associated with a given collection.
     *
     * @access  public
     * @param   integer $col_id The collection ID
     * @param   string $status The desired user status
     * @return  array The list of users
     */
    function getUserEmailAssocList($col_id, $status = NULL, $role = NULL)
    {
        static $returns;

        if (!empty($returns[$col_id])) {
            return $returns[$col_id];
        }

        $stmt = "SELECT
                    usr_id,
                    usr_email
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "collection_user
                 WHERE
                    cou_col_id=$col_id AND
                    cou_usr_id=usr_id";
        if ($status != NULL) {
            $stmt .= " AND usr_status='active' ";
        }
        if ($role != NULL) {
            $stmt .= " AND usr_role > $role ";
        }
        $stmt .= "
                 ORDER BY
                    usr_email ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            $returns[$col_id] = $res;
            return $res;
        }
    }
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Collection Class');
}
?>
