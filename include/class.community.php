<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
//include_once(APP_INC_PATH . "class.filter.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.status.php");

class Community
{

    /**
     * Method used to get the list of collections available in the 
     * system.
     *
     * @access  public
     * @return  array The list of collections
     */
    function getCommunityXDIS_ID()
    {
		// will make this more dynamic later. (probably feed from a mysql table which can be configured in the gui admin interface).
		$community_xdis_id = 11;
		return $community_xdis_id;
    }

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
    function getDetails($community_pid)
    {
        $stmt = "SELECT
                    * 
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1
                 WHERE
				    r1.rmf_xsdmf_id = x1.xsdmf_id and
                    rmf_rec_pid = '".$community_pid."'";
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


//		$details = Fedora_API::getObjectXML($community_pid);
//		return $details;
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
     * Method used to get the list of communitys available in the 
     * system.
     *
     * @access  public
     * @return  array The list of communities
     */
    function getList($current_row = 0, $max = 25)
    {
	
        $start = $current_row * $max;

        $stmt = "SELECT
            * 
            FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1
            inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 
            ON r1.rmf_xsdmf_id = x1.xsdmf_id  
            left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 
            on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
            WHERE
            rmf_rec_pid in (
                    SELECT r2.rmf_rec_pid 
                    FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2  						
                    WHERE s2.sek_title = 'Object Type' AND x2.xsdmf_id = r2.rmf_xsdmf_id
                    AND s2.sek_id = x2.xsdmf_sek_id AND r2.rmf_varchar = '1')		
            AND rmf_rec_pid IN (
                    SELECT rmf_rec_pid FROM 
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field AS rmf
                    INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields AS xdm
                    ON rmf.rmf_xsdmf_id = xdm.xsdmf_id
                    WHERE rmf.rmf_varchar=2
                    AND xdm.xsdmf_element='!sta_id'
                    )
            ";
	
		$returnfields = array("title", "description", "ret_id", "xdis_id", "sta_id", "Editor", "Creator", "Lister", "Viewer", "Approver", "Community Administrator", "Annotator", "Comment_Viewer", "Commentor");
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        //$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
		$return = array();

		foreach ($res as $result) {		
			if (in_array($result['xsdsel_title'], $returnfields) 
                    && ($result['xsdmf_element'] != '!rule!role!name') 
                    && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) ) {
				$return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']][]
                   = $result['rmf_'.$result['xsdmf_data_type']]; // need to array_push because there can be multiple groups/users for a role
			}
			if (in_array($result['xsdmf_fez_title'], $returnfields)) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$return[$result['rmf_rec_pid']][$result['xsdmf_fez_title']][]
                   =  $result['rmf_'.$result['xsdmf_data_type']];
				sort($return[$result['rmf_rec_pid']][$result['xsdmf_fez_title']]);
			}
		}
//		$return = Auth::getIndexAuthorisationGroups($return);

        $return = array_values($return);

		$return = Auth::getIndexAuthorisationGroups($return);
//		print_r($roles);
		$hidden_rows = count($return);
		$return = Misc::cleanListResults($return);

//		print_r($return);
		$total_rows = count($return);
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
		$return = Misc::limitListResults($return, $start, ($start + $max));
//		print_r($return);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return array(
                "list" => $return,
                "info" => array(
                    "current_page"  => $current_row,
                    "start_offset"  => $start,
                    "end_offset"    => $total_rows_limit,
                    "total_rows"    => $total_rows,
                    "total_pages"   => $total_pages,
                    "previous_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page,
                    "hidden_rows"     => $hidden_rows - $total_rows
                )
            );

//            return $return;
        }

    }


/*    function getList()
    {
		$itql = "select \$object \$title \$description \$type from <#ri>
					where  (\$object <rdf:type> <fedora-model:FedoraObject>) and
                    (\$object <dc:type> 'Fez_Community') and
					(\$object <dc:title> \$title) and 
					(\$object <dc:description> \$description) and
                    (\$object <dc:type> \$type)";

//		echo $itql;

		$returnfields = array();
		array_push($returnfields, "pid"); 
		array_push($returnfields, "title");
		array_push($returnfields, "description");
		array_push($returnfields, "type");

		$details = Fedora_API::getITQLQuery($itql, $returnfields);
        $details = Auth::ProcessListResults($details);
		return $details;
    } */


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

        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1
                 WHERE
				    r1.rmf_xsdmf_id = x1.xsdmf_id and
                    rmf_rec_pid in (
						SELECT r2.rmf_rec_pid 
						FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
						WHERE rmf_xsdmf_id = 239 AND rmf_varchar = '1')
					
					
					";
//		echo $stmt;			
		$returnfields = array("title");
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        //$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
//		print_r($res);
		$return = array();
		foreach ($res as $result) {
			if (in_array($result['xsdmf_fez_title'], $returnfields)) {
				$return[$result['rmf_rec_pid']] = $result['rmf_'.$result['xsdmf_data_type']];
			}
		}
//		$return = array_values($return);
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


//		$details = Fedora_API::getListByTypeObjectsXMLAssoc("Fez_Community");
//		echo "collection details -> ";
//		print_r($details);
//		return $details;
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
