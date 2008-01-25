<?php

include_once(APP_INC_PATH . "class.org_structure.php");


class AuthorAffiliations
{
	function getList($pid, $status = 1)
	{
		$stmt = "SELECT * FROM ". APP_TABLE_PREFIX ."author_affiliation " .
			"WHERE af_pid = '".$pid."' " .
			"AND af_status = " . $status . " " .
			"ORDER BY af_author_id";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }
        // Add text versions of the author and school
        foreach ($res as $key => $item) {
        	$res[$key]['author_name'] = Author::getFullname($item['af_author_id']);
        	$res[$key]['org_title'] = Org_Structure::getTitle($item['af_org_id']);
            $res[$key]['af_percent_affiliation'] = $item['af_percent_affiliation'] / 1000;
        }
        return $res;
	}


	function getListAll($pid)
	{
		$stmt = "SELECT * FROM ". APP_TABLE_PREFIX ."author_affiliation " .
			"WHERE af_pid = '".$pid."' " .
			"ORDER BY af_author_id";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }
        // Add text versions of the author and school
        foreach ($res as $key => $item) {
        	$res[$key]['author_name'] = Author::getFullname($item['af_author_id']);
        	$res[$key]['org_title'] = Org_Structure::getTitle($item['af_org_id']);
            $res[$key]['af_percent_affiliation'] = $item['af_percent_affiliation'] / 1000;
        }
        return $res;
	}


    /**
     * Method used to remove an author affiliation from the system.
     *
     * @access  public
     * @return  boolean
     */
    function remove($af_id)
    {
		if (!is_numeric($af_id)) {
			return false;
		}
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "author_affiliation
                 WHERE
                    af_id = ".$af_id;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }

	
	function save($af_id, $pid, $af_author_id, $af_percent_affiliation, $af_org_id)
	{
		if (!is_numeric($af_author_id) || !is_numeric($af_percent_affiliation)) {
			return -1;
		}
		if (($af_percent_affiliation > 100) || ($af_percent_affiliation < 0)) {
			return -1;
		}
		if (empty($af_id)) {
			$stmt = "INSERT ";
		} else {
			$stmt = "UPDATE ";
		}

        $af_percent_affiliation = $af_percent_affiliation * 1000;

		$stmt .= APP_TABLE_PREFIX . "author_affiliation SET 
			af_pid='".$pid."',
			af_author_id=".$af_author_id.",
			af_percent_affiliation=".$af_percent_affiliation.",
			af_org_id=".$af_org_id.",
			af_status=0
		";
		if (!empty($af_id)) {
			$stmt .= "WHERE af_id=".$af_id."";
		}
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
		
        return $GLOBALS["db_api"]->dbh->getLastInsertId(APP_TABLE_PREFIX . "author_affiliation", 'af_id');
	}


    /**
     * Method used to examine all affiliations recorded for a particular PID, and
	 * set the "OK" flag, depending on whether or not affilatiosn for a particular 
	 * author add to exactly 100%.
     *
     * @access  public
     * @return  int Success or otherwise of the operation
     */

	function validateAffiliations($pid) {

		$stmt .= "SELECT af_author_id, SUM(af_percent_affiliation) AS total_percentage " .
				 "FROM " . APP_TABLE_PREFIX . "author_affiliation " .
				 "WHERE af_pid = '" . $pid . "' " .
				 "GROUP BY af_author_id";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }

        foreach ($res as $key => $item) {
			$stmt = "UPDATE " . APP_TABLE_PREFIX . "author_affiliation ";
			if ($item['total_percentage'] == 100000) {
				$stmt .= "SET af_status = 1 ";
			} else {
				$stmt .= "SET af_status = 0 ";
			}
			$stmt .= "WHERE af_author_id = " . $item['af_author_id'] . " " .
					 "AND af_pid = '" . $pid . "'";
			$res = $GLOBALS["db_api"]->dbh->query($stmt);
			if (PEAR::isError($res)) {
				Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				return -1;
			}
        }
		return 1;
	}


    /**
     * Method used to retrieve an array of HR data for a given author.
     *
     * @access  public
     * @return  array The associative array of fields from the HR view
     */
	function getPresetAffiliations($authorID) {

		/* -- Original way of doing it
		$stmt = "SELECT " . 
					"aut_display_name AS name, " .
					"WAMIKEY AS wamikey, " .
					"FTE AS fte, " .
					"AOU AS aou, " .
					"PAYPOINTLONG AS paypointlong, " .
					"STATUS AS status, " .
					"PAYROLL_FLAG AS payroll_flag, " .
					"AWARD AS award " .
				"FROM " . APP_TABLE_PREFIX . "author, hr_position_vw " .
				"WHERE " . APP_TABLE_PREFIX . "author.aut_org_staff_id = hr_position_vw.WAMIKEY " .
				"AND aut_id = " . $authorID . "";

		*/

		$stmt = "SELECT aut_display_name AS name, t1.aut_id AS aut_id, WAMIKEY AS wamikey, FTE AS fte, AOU AS aou, STATUS AS status, PAYROLL_FLAG AS payroll_flag, AWARD AS award, org_title AS org_title, org_id AS org_id " .
				"FROM " . APP_TABLE_PREFIX . "author AS t1 " .
				"INNER JOIN hr_position_vw " .
				"ON aut_org_staff_id = WAMIKEY " .
				"LEFT JOIN " . APP_TABLE_PREFIX . "org_structure " .
				"ON AOU = org_extdb_id " .
				"WHERE aut_id = " . $authorID . " " .
				"AND org_extdb_name = 'hr'";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }

		/* This is the bit where we calculate what the percentages should be. This code follows 
		   Adam Nielsen's algorithm as closely as I could manage to figure it out. It may require
		   some fine-tuning. */

		$worthyFTEs = array();			// This is the array of paid appointments
		$unpaidAppointments =  array();	// This is the array of unpaid appointments

		// First parse. Examine everything.
        foreach ($res as $key => $item) {
			// Is this a paid appointment?
			if ($res[$key]['payroll_flag'] !== "T") {
				$res[$key]['percentage'] = 0;
				array_push($unpaidAppointments, $res[$key]['fte']);
			} else {
				array_push($worthyFTEs, $res[$key]['fte']);
			}
        }

		$numPaidAppointments = sizeof($worthyFTEs);
		$numUnpaidAppointments = sizeof($unpaidAppointments);
		$sumFTEs = array_sum($worthyFTEs);
		$sumUnpaids = array_sum($unpaidAppointments);

		// Second parse. Write out the important stuff.
		if ($numPaidAppointments > 0) {
			foreach ($res as $key => $item) {
				if ($res[$key]['payroll_flag'] == "T") {
					$res[$key]['percentage'] = round(($res[$key]['fte'] / $sumFTEs) * 100, 3);
				}
			}
		}

		// If solitary unpaid appointment, scale to 100%
		if ($numUnpaidAppointments == 1 && $numPaidAppointments == 0) {
			foreach ($res as $key => $item) {
				if ($res[$key]['payroll_flag'] !== "T") {
					$res[$key]['percentage'] = 100;
				}
			}
		}

        return $res;
	}

}

?>
