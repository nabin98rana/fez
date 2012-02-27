<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2010 The University of Queensland,         |
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
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//

include_once(APP_INC_PATH . "class.org_structure.php");

class author_era_affiliations
{
    function getAuthorsAll($pid)
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

        $stmt = "SELECT * FROM __era_ro_uq_asc_req ".
               "LEFT JOIN ". APP_TABLE_PREFIX ."author ".
               "ON aut_org_staff_id = staff_id ".
               "LEFT JOIN ". APP_TABLE_PREFIX ."author_affiliation_era ".
               "ON aae_pid = pid ".
               "AND aae_staff_id = staff_id ".
               "WHERE pid = ".$db->quote($pid);
   		try {
   			$res = $db->fetchAll($stmt);
   		}
   		catch(Exception $ex) {
   			$log->err($ex);
   			return array();
   		}

   		return $res;
   	}

	function save($aae_id, $pid, $aae_status_id_lookup, $aae_comment, $staff_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();

        if (empty($pid)) {
            return -1;
        }

        $complete = TRUE;
        $completeIsUq = FALSE;
        //data from smart iterations so starts at one.
        for($i=1;$i < count($staff_id)+1; $i++)
        {
            if (!is_numeric($staff_id[$i])) {
                return -1;
            }

            //If one author is confirmed then pid confirmed
            $completeIsUq = $completeIsUq || ( ($aae_status_id_lookup[$i] == 2) || ($aae_status_id_lookup[$i] == 5) );

            //See if all authors have been checked complete

            $thisComplete = ( ($aae_status_id_lookup[$i] == 2) || ($aae_status_id_lookup[$i] == 3)||
                                       ($aae_status_id_lookup[$i] == 5)|| ($aae_status_id_lookup[$i] == 5) ||
                                       ($aae_status_id_lookup[$i] == 7) ||($aae_status_id_lookup[$i] == 8) ||($aae_status_id_lookup[$i] == 0) );
            $complete = $complete && $thisComplete;
            $thisCompleteYesNo = ($thisComplete) ? "Y" : "N";
            if (empty($aae_id[$i])) {
                $stmt = "INSERT ";
            } else {
                $stmt = "UPDATE ";
            }

            // Write the new record
            $stmt .= APP_TABLE_PREFIX . "author_affiliation_era SET
                aae_pid=".$db->quote($pid).",
                aae_status_id_lookup=".$db->quote($aae_status_id_lookup[$i], 'INTEGER').",
                aae_comment=".$db->quote($aae_comment[$i]).",
                aae_staff_id=".$db->quote($staff_id[$i]). ",
                aae_is_request_complete=".$db->quote($thisCompleteYesNo);
            if (!empty($aae_id[$i])) {
               $stmt .= " WHERE aae_id=".$db->quote($aae_id[$i], 'INTEGER')."";
            }
            try {
                $db->query($stmt);
            }
            catch(Exception $ex) {
                $log->err($ex);
                return -1;
            }
        }

        $completeYesNo = ($complete || $completeIsUq) ? "Y" : "N";
        {
            $stmt = "UPDATE ".APP_TABLE_PREFIX."author_affiliation_era
                    SET aae_is_pid_request_complete = '$completeYesNo'
                    WHERE aae_pid = ".$db->quote($pid);
            try {
                $db->query($stmt);
            }
            catch(Exception $ex) {
                $log->err($ex);
                return -1;
            }
        }
		return $db->lastInsertId(APP_TABLE_PREFIX.'author_affiliation_era', 'aae_id');
	}

    /**
   	 * Method used to get the list of organsational structures available in the
   	 * system returned in an associative array for drop down lists. This method
   	 * returns only those org units that are tagged as coming from HR.
   	 *
   	 * @access  public
   	 * @return  array The list of ERA organsational structures in an associative array (for drop down lists).
   	 */
   	function getAssocListEraAffiliation()
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

   		$stmt = "SELECT
                       uq_assoc_status_id,
   					   uq_assoc_status_name
                    FROM
                        __era_ro_uq_asc_stat
                    WHERE uq_assoc_status_id != '0'
                        ";
   		try {
   			$res = $db->fetchPairs($stmt);
   		}
   		catch(Exception $ex) {
   			$log->err($ex);
   			return '';
   		}
   		return $res;
   	}

    //this returns the xsdmf_id for the HERDC note gived the xdis_id when then is used to
    //find the is anchor for a page link
    function returnHERDCLink($xdis_id)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $xdis_list = XSD_Relationship::getListByXDIS($xdis_id);
        array_push($xdis_list, array("0" => $xdis_id));
        $xdis_str = Misc::sql_array_to_string($xdis_list);

        $stmt = "SELECT
                        xsdmf_id
                     FROM
                        " . APP_TABLE_PREFIX . "xsd_display_matchfields
                     WHERE
                         xsdmf_title = 'herdc notes' AND xsdmf_xdis_id in (".$xdis_str.") ";
        try {
            $res = $db->fetchOne($stmt);
        }

        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }
        return $res;
    }

    function getList($current_row = 0, $max = 2, $order_by = 'aae_pid', $filter=false, $search=false)
   	{
        $log = FezLog::get();
        $db = DB_API::get();

        if ($order_by == 'aae_is_pid_request_complete')
        {
            $order_by = '-aae_is_pid_request_complete';

        }
        $where = ($filter == false) ? "WHERE 1 " : "WHERE (aae_is_pid_request_complete != 'Y' OR aae_is_pid_request_complete IS NULL) " ;
        if (!empty($search['value']))
           {
               $where.= "AND $search[on] like ".$db->quote("%".$search[value]."%");
           }
        $start = $current_row * $max;
   		$stmt = "
   					SELECT
   						SQL_CALC_FOUND_ROWS *
   					FROM
   						__era_ro_uq_asc_req
   					LEFT JOIN " . APP_TABLE_PREFIX . "author_affiliation_era
   					ON pid = aae_pid
   					AND staff_id = aae_staff_id
   					LEFT JOIN " . "__era_ro_uq_asc_stat
                       ON aae_status_id_lookup = __era_ro_uq_asc_stat.uq_assoc_status_id
                    LEFT JOIN " . APP_TABLE_PREFIX . "author
                              ON staff_id = aut_org_staff_id ".
                    $where."
   					ORDER BY
                        ".$order_by."
                    LIMIT ".
                        $db->quote($max, 'INTEGER')." OFFSET ".$db->quote($start, 'INTEGER');
   		try {
   			$res = $db->fetchAll($stmt);
   		}
   		catch(Exception $ex) {
   			$log->err($ex);
   			return '';
   		}
        $stmt = " SELECT FOUND_ROWS()";

        try {
            $total_rows = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }


        if (($start + $max) < $total_rows) {
         			$total_rows_limit = $start + $max;
         		} else {
         			$total_rows_limit = $total_rows;
         		}
         		$total_pages = ceil($total_rows / $max);
         		$last_page = $total_pages - 1;

        return array(
         		                "list" => $res,
         		                "list_info" => array(
         		                    "current_page"  => $current_row,
         		                    "start_offset"  => $start,
         		                    "end_offset"    => $total_rows_limit,
         		                    "total_rows"    => $total_rows,
         		                    "total_pages"   => $total_pages,
         		                    "prev_page"     => ($current_row == 0) ? "-1" : ($current_row - 1),
         		                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
         		                    "last_page"     => $last_page
         				        )
         				);

   	}

   	/**
   	 * Method used to remove a given set of pids from the requests.
   	 *
   	 * @access  public
   	 * @return  boolean
   	 */
   	function remove($iaaPids)
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

   		$stmt = "DELETE FROM
                       " . APP_TABLE_PREFIX . "interact_author_affilation_requests
                    WHERE
                        IN (".Misc::arrayToSQLBindStr($iaaPids).")";
   		try {
   			$db->query($stmt);
   		}
   		catch(Exception $ex) {
   			$log->err($ex);
   			return false;
   		}
   		return true;
   	}

}
