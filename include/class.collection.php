<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | eSpace - Digital Repository                                          |
// +----------------------------------------------------------------------+
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
// |																	  |
// | Some code and structure is derived from Eventum (GNU GPL - MySQL AB) |
// | http://dev.mysql.com/downloads/other/eventum/index.html			  |
// | Eventum is primarily authored by João Prado Maia <jpm@mysql.com>     |
// +----------------------------------------------------------------------+
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>        |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle the business logic related to the administration
 * of collections in the system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.status.php");

class Collection
{

    /**
     * Method used to get the details for a given collection ID.
     *
     * @access  public
     * @param   integer $collection_pid The collection persistant identifier
     * @return  array The collection details
     */
    function getDetails($collection_pid)
    {
        $stmt = "SELECT
                    * 
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1
                 WHERE
				    r1.rmf_xsdmf_id = x1.xsdmf_id and
                    rmf_rec_pid = '".$collection_pid."'";
		$returnfields = array("title", "description", "ret_id", "xdis_id", "sta_id");
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$return = array();
		foreach ($res as $result) {
			if (in_array($result['xsdmf_espace_title'], $returnfields)) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$return[$result['rmf_rec_pid']][$result['xsdmf_espace_title']] = $result['rmf_'.$result['xsdmf_data_type']];
			}
		}
		$return = array_values($return);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $return;
        }
    }

    /**
     * Method used to get the parents of a given collection available in the 
     * system.
     *
     * @access  public
     * @return  array The list of parent communities
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

		$returnfields = array();
		array_push($returnfields, "pid"); 
		array_push($returnfields, "title");
		array_push($returnfields, "identifier");
		array_push($returnfields, "description");

		$details = Fedora_API::getITQLQuery($itql, $returnfields);
		return $details;
    }

    /**
     * Method used to get the list of collections available in the 
     * system.
     *
     * @access  public
     * @return  array The list of collections
     */
    function getList($community_pid=false)
    {

        // Should we restrict the list to a community.
        if ($community_pid) {
            $community_where = "	and r2.rmf_rec_pid in (
	 						SELECT r3.rmf_rec_pid 
							FROM  
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r3,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x3,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s3
							WHERE x3.xsdmf_sek_id = s3.sek_id AND s3.sek_title = 'isMemberOf' AND x3.xsdmf_id = r3.rmf_xsdmf_id 
							  AND r3.rmf_varchar = '$community_pid'
							)";
        } else {
            // list all collections 
            $community_where = "";
        }

        $stmt = "SELECT
                    * 
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
                 WHERE
				    r1.rmf_xsdmf_id = x1.xsdmf_id and
                    r1.rmf_rec_pid in (
						SELECT r2.rmf_rec_pid 
						FROM  
						  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
						  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,
 						  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2				  
						WHERE r2.rmf_xsdmf_id = x2.xsdmf_id AND x2.xsdmf_sek_id = s2.sek_id AND s2.sek_title = 'Object Type' AND r2.rmf_varchar = '2' $community_where )
					";

		$returnfields = array("title", "description", "ret_id", "xdis_id", "sta_id", "Editor", "Creator", "Lister", "Viewer", "Approver", "Community Administrator", "Annotator", "Comment_Viewer", "Commentor");
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$return = array();

		foreach ($res as $result) {		
			if (in_array($result['xsdsel_title'], $returnfields) && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) ) {
				if (!is_array($return[$result['rmf_rec_pid']]['eSpaceACML'][$result['xsdsel_title']][$result['xsdmf_element']])) {
					$return[$result['rmf_rec_pid']]['eSpaceACML'][$result['xsdsel_title']][$result['xsdmf_element']] = array();
				}
				array_push($return[$result['rmf_rec_pid']]['eSpaceACML'][$result['xsdsel_title']][$result['xsdmf_element']], $result['rmf_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
			}
			if (in_array($result['xsdmf_espace_title'], $returnfields)) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$return[$result['rmf_rec_pid']][$result['xsdmf_espace_title']] = $result['rmf_'.$result['xsdmf_data_type']];
			}
		}

		foreach ($return as $pid_key => $row) {
			if (!is_array(@$row['eSpaceACML'])) {
				$parentsACMLs = array();
				Auth::getIndexParentACMLs(&$parentsACMLs, $pid_key);
				$return[$pid_key]['eSpaceACML'] = $parentsACMLs;
			}
		}
		$return = array_values($return);
		$return = Auth::getIndexAuthorisationGroups($return);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $return;
        }
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
    function getListing($collection_pid, $current_row = 0, $max = 30)
    {
        $isMemberOf_xsdmf_id = 149;
        $ret_id_xsd_mf = 236; // eSpaceMD Display, 
		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;


        $stmt = "SELECT
                    * 
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
				INNER JOIN (
						SELECT distinct r2.rmf_rec_pid 
						FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2  							  
						WHERE r2.rmf_xsdmf_id = x2.xsdmf_id AND s2.sek_id = x2.xsdmf_sek_id AND s2.sek_title = 'Object Type' AND r2.rmf_varchar = '3' and r2.rmf_rec_pid in (
	 						SELECT distinct r3.rmf_rec_pid 
							FROM  
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r3,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x3,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s3							  							  
							WHERE r3.rmf_xsdmf_id = x3.xsdmf_id AND s3.sek_id = x3.xsdmf_sek_id AND s3.sek_title = 'isMemberOf' AND r3.rmf_varchar = '".$collection_pid."'
						 	) limit $start, $max
						) as r2 on r1.rmf_rec_pid = r2.rmf_rec_pid
                 WHERE
				    r1.rmf_xsdmf_id = x1.xsdmf_id
				 ORDER BY
				 	r1.rmf_rec_pid";


        $stmtAll = "SELECT
                    * 
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
				INNER JOIN (
						SELECT distinct r2.rmf_rec_pid 
						FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2 
						WHERE r2.rmf_xsdmf_id = x2.xsdmf_id AND s2.sek_id = x2.xsdmf_sek_id AND s2.sek_title = 'Object Type' AND r2.rmf_varchar = '3' and r2.rmf_rec_pid in (
	 						SELECT distinct r3.rmf_rec_pid 
							FROM  
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r3,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x3,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s3							  							  
							WHERE r3.rmf_xsdmf_id = x3.xsdmf_id AND s3.sek_id = x3.xsdmf_sek_id AND s3.sek_title = 'isMemberOf' AND r3.rmf_varchar = '".$collection_pid."'
						 	) 
						) as r2 on r1.rmf_rec_pid = r2.rmf_rec_pid					
                 WHERE
				    r1.rmf_xsdmf_id = x1.xsdmf_id
				 ORDER BY
				 	r1.rmf_rec_pid";

		$returnfields = array("title", "date", "type", "description", "identifier", "creator", "ret_id", "xdis_id", "sta_id", "Editor", "Creator", "Lister", "Viewer", "Approver", "Community Administrator", "Annotator", "Comment_Viewer", "Commentor");
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        $total_rows = Pager::getTotalRowsDistinct($stmtAll, "r1.rmf_rec_pid");
        $total_rows_limit = Pager::getTotalRowsDistinct($stmt, "r1.rmf_rec_pid");
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
		
		$return = array();
		foreach ($res as $result) {
			if (in_array($result['xsdsel_title'], $returnfields) && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) ) {
				if (!is_array($return[$result['rmf_rec_pid']]['eSpaceACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
					$return[$result['rmf_rec_pid']]['eSpaceACML'][0][$result['xsdsel_title']][$result['xsdmf_element']] = array();
				}
				array_push($return[$result['rmf_rec_pid']]['eSpaceACML'][0][$result['xsdsel_title']][$result['xsdmf_element']], $result['rmf_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
			}
			if (in_array($result['xsdmf_espace_title'], $returnfields)) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$return[$result['rmf_rec_pid']][$result['xsdmf_espace_title']] = $result['rmf_'.$result['xsdmf_data_type']];
			}
			// get thumbnails
			if ($result['xsdmf_espace_title'] == "datastream_id") {
				if (is_numeric(strpos($result['rmf_varchar'], "thumbnail_"))) {
					if (!is_array(@$return[$result['rmf_rec_pid']]['thumbnails'])) {
						$return[$result['rmf_rec_pid']]['thumbnails'] = array();
					}
					array_push($return[$result['rmf_rec_pid']]['thumbnails'], $result['rmf_varchar']);
				} else {
					if (!is_array(@$return[$result['rmf_rec_pid']]['datastreams'])) {
						$return[$result['rmf_rec_pid']]['datastreams'] = array();
					}
					array_push($return[$result['rmf_rec_pid']]['datastreams'], $result['rmf_varchar']);
				}
			}
		}
		
		foreach ($return as $pid_key => $row) {
			//if there is only one thumbnail DS then use it
			if (count($row['thumbnails']) == 1) {
				$return[$pid_key]['thumbnail'] = $row['thumbnails'][0];
			} else {
				$return[$pid_key]['thumbnail'] = 0;
			}

			if (!is_array(@$row['eSpaceACML'])) {
				$parentsACMLs = array();
				Auth::getIndexParentACMLs(&$parentsACMLs, $pid_key);
				$return[$pid_key]['eSpaceACML'] = $parentsACMLs;
			}
		}
		
		$return = array_values($return);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {

            return array(
                "list" => $return,
                "info" => array(
                    "current_page"  => $current_row,
                    "start_offset"  => $start,
                    "end_offset"    => $start + ($total_rows_limit),
                    "total_rows"    => $total_rows,
                    "total_pages"   => $total_pages,
                    "previous_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page
                )
            );


        }

/*		$itql = "select \$collTitle \$collDesc \$title \$identifier \$description \$object \$type from <#ri>
					where  (<info:fedora/".$collection_pid."> <dc:title> \$collTitle) and
                    (<info:fedora/".$collection_pid."> <dc:description> \$collDesc) and
					(\$object <fedora-rels-ext:isMemberOf> <info:fedora/".$collection_pid.">) and
					(\$object <dc:identifier> \$identifier) and
					(\$object <dc:title> \$title) and
					(\$object <dc:description> \$description) and
                    (\$object <dc:type> \$type)
					order by \$title asc";

//		echo $itql;
		$returnfields = array('pid', 'title', 'identifier', 'description', 'creator', 'type');

		$details = Fedora_API::getITQLQuery($itql, $returnfields);
        $details = Auth::ProcessListResults($details);

		return $details; */
    }

    function getCount($collection_pid)
    {
        // Member of Collections, Fedora Records RELS-EXT Display, /RDF/description/isMemberOf/resource
        $isMemberOf_xsdmf_id = 149; 
        $stmt = "SELECT count(distinct rmf_rec_pid) as items
            FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r3
            WHERE r3.rmf_xsdmf_id = $isMemberOf_xsdmf_id AND r3.rmf_varchar = '$collection_pid'	";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return 0;
        }
        return $res;
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
						WHERE rmf_xsdmf_id = 242 AND rmf_varchar = '2')
					
					
					";
		$returnfields = array("title");
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$return = array();
		foreach ($res as $result) {
			if (in_array($result['xsdmf_espace_title'], $returnfields)) {
				$return[$result['rmf_rec_pid']] = $result['rmf_'.$result['xsdmf_data_type']];
			}
		}
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $return;
        }

    }


}

/*class CollectionObject extends RecordGeneral
{

}
*/


// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Collection Class');
}
?>
