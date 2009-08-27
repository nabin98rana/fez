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
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//

include_once(APP_INC_PATH . "class.org_structure.php");

class AuthorAffiliations
{
	function getList($pid, $status = 1) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT af.* FROM ". APP_TABLE_PREFIX ."author_affiliation af " .
			"INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON rek_author_id_pid = af_pid " .
			"AND af_author_id = rek_author_id " .
			"WHERE af_pid = ".$db->quote($pid)." " .
			"AND af_status = " . $db->quote($status, 'INTEGER') . " " .
			"ORDER BY af_author_id";

		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}

		// Add text versions of the author and school
		foreach ($res as $key => $item) {
			$res[$key]['author_name'] = Author::getFullname($item['af_author_id']);
			$res[$key]['org_title'] = Org_Structure::getTitle($item['af_org_id']);
			$res[$key]['af_percent_affiliation'] = $item['af_percent_affiliation'] / 1000;
			$res[$key]['error_desc'] = "Percentages for each author must add to 100%";
			$res[$key]['error'] = "percentage";
		}
		return $res;
	}


	function getListAll($pid) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT af.* FROM ". APP_TABLE_PREFIX ."author_affiliation af " .
			"INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON rek_author_id_pid = af_pid " .
			"AND af_author_id = rek_author_id " .
			"WHERE af_pid = ".$db->quote($pid)." " .
			"ORDER BY af_author_id";
		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
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
	 * Method used to find all affiliations recorded for a particular author in a particular school.
	 *
	 * @access  public
	 * @return  array The list of affiliations.
	 */
	function getListAuthorSchool($af_author_id, $pid, $af_org_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT af_id FROM ". APP_TABLE_PREFIX ."author_affiliation " .
			"WHERE af_pid = " . $db->quote($pid) . " " .
			"AND af_author_id = " . $db->quote($af_author_id, 'INTEGER') . " " .
			"AND af_org_id = " . $db->quote($af_org_id, 'INTEGER');
		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
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
		$log = FezLog::get();
		$db = DB_API::get();

		if (!is_numeric($af_id)) {
			return false;
		}
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "author_affiliation
                 WHERE
                    af_id = ".$db->quote($af_id, 'INTEGER');
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}


	function save($af_id, $pid, $af_author_id, $af_percent_affiliation, $af_org_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (!is_numeric($af_author_id) || !is_numeric($af_percent_affiliation)) {
			return -1;
		}
		if (($af_percent_affiliation > 100) || ($af_percent_affiliation < 0)) {
			return -1;
		}

		$af_percent_affiliation = $af_percent_affiliation * 1000;

		if (empty($af_id)) {
			$stmt = "INSERT ";
			// See if there is anything existing for this author / pid / school
			$existingPercentage = AuthorAffiliations::getExistingAuthorSchoolPercentages($af_author_id, $pid, $af_org_id);
			if ($existingPercentage > 0) {
				$af_percent_affiliation = $af_percent_affiliation + $existingPercentage;
				$affiliationsToDelete = AuthorAffiliations::getListAuthorSchool($af_author_id, $pid, $af_org_id);
				foreach ($affiliationsToDelete as $affiliationToDelete) {
					AuthorAffiliations::remove($affiliationToDelete['af_id']);
				}
			}
		} else {
			$stmt = "UPDATE ";
		}

		// Write the new record
		$stmt .= APP_TABLE_PREFIX . "author_affiliation SET
			af_pid=".$db->quote($pid).",
			af_author_id=".$db->quote($af_author_id, 'INTEGER').",
			af_percent_affiliation=".$db->quote($af_percent_affiliation, 'INTEGER').",
			af_org_id=".$db->quote($af_org_id, 'INTEGER').",
			af_status=0
		";
		if (!empty($af_id)) {
			$stmt .= "WHERE af_id=".$db->quote($af_id, 'INTEGER')."";
		}

		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return $db->lastInsertId(APP_TABLE_PREFIX.'author_affiliation', 'af_id');
	}


	/**
	 * Method used to examine all affiliations recorded for a particular PID, and
	 * set the "OK" flag, depending on whether or not affilatiosn for a particular
	 * author add to exactly 100%.
	 *
	 * @access  public
	 * @return  int Success or otherwise of the operation
	 */

	function validateAffiliations($pid) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT af_author_id, SUM(af_percent_affiliation) AS total_percentage " .
				 "FROM " . APP_TABLE_PREFIX . "author_affiliation " .
				 "WHERE af_pid = " . $db->quote($pid) . " " .
				 "GROUP BY af_author_id";

		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}

		foreach ($res as $key => $item) {
			$stmt = "UPDATE " . APP_TABLE_PREFIX . "author_affiliation ";
			if ($item['total_percentage'] == 100000) {
				$stmt .= "SET af_status = 1 ";
			} else {
				$stmt .= "SET af_status = 0 ";
			}
			$stmt .= "WHERE af_author_id = " . $db->quote($item['af_author_id'], 'INTEGER') . " " .
					 "AND af_pid = " . $db->quote($pid);
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
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
	function getPresetAffiliations($authorID) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

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
				"WHERE aut_id = " . $db->quote($authorID, 'INTEGER') . " " .
				"AND (org_extdb_name = 'hr' " .
				"OR org_extdb_name = 'rrtd')";

		try {
			$res = $db->fetchAll($stmt, array($ahs_id));
		}
		catch(Exception $ex) {
			$log->err($ex);
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


	/**
	 * Method used to retrieve total percentage of existing affiliations for
	 * a given author + school.
	 *
	 * @access  public
	 * @return  int The total percentage (if any) assigned to this author in this school.
	 */
	function getExistingAuthorSchoolPercentages($authorID, $pid, $unitID) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt .= "SELECT SUM(af_percent_affiliation) AS total_percentage " .
					"FROM " . APP_TABLE_PREFIX . "author_affiliation " .
					"WHERE af_pid = " . $db->quote($pid) . " " .
					"AND af_author_id = " . $db->quote($authorID, 'INTEGER') . " " .
					"AND af_org_id = " . $db->quote($unitID, 'INTEGER');

		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return 0;
		}
		return $res[0]['total_percentage'];
	}


	/**
	 * Method used to retrieve an array of orphaned affiliations. These are affiliations
	 * for which the author has subsequently been removed form the record.
	 *
	 * @access  public
	 * @return  array The associative array of orphaned affiliations.
	 */
	function getOrphanedAffiliations($pid) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT af.* FROM ". APP_TABLE_PREFIX ."author_affiliation af " .
			"LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON rek_author_id_pid = af_pid " .
			"AND af_author_id = rek_author_id " .
			"WHERE af_pid = ".$db->quote($pid)." AND rek_author_id_pid IS NULL " .
			"ORDER BY af_author_id";

		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}

		foreach ($res as $key => $item) {
			$res[$key]['author_name'] = Author::getFullname($item['af_author_id']);
			$res[$key]['org_title'] = Org_Structure::getTitle($item['af_org_id']);
			$res[$key]['af_percent_affiliation'] = $item['af_percent_affiliation'] / 1000;
			$res[$key]['error_desc'] = "Author for this affiliation has been deleted";
			$res[$key]['error'] = "orphan";
		}
		return $res;

	}


	/**
	 * Method used to search and suggest all the org unit names for a given string.
	 *
	 * @access  public
	 * @return  array List of organisational units
	 */
	function suggestOrgUnits($term, $assoc = false) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT org_id AS id, org_extdb_id AS aou, org_title AS name " .
				"FROM " . APP_TABLE_PREFIX . "org_structure " .
				"WHERE org_title LIKE ". $db->quote('%'.$term.'%')." " .
				"AND (org_extdb_name = 'hr' " .
				"OR org_extdb_name = 'rrtd') " .
				"ORDER BY org_title LIMIT 0, 20";

		try {
			if($assoc) {
				$res = $db->fetchAll($stmt);
			} else {
				$res = $db->fetchAssoc($stmt);
			}
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $res;

	}


	/**
	 * Method used to retrieve the list of PIDs with orphaned affiliations. Scans the
	 * entire repository and reports everything it can find.
	 *
	 * @access  public
	 * @return  array The associative array of PIDs and their titles.
	 */
	function getOrphanedAffiliationsAll() 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT af_pid, rek_title FROM ". APP_TABLE_PREFIX ."author_affiliation af " .
			"LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON rek_author_id_pid = af_pid " .
			"AND af_author_id = rek_author_id " .
			"INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = af_pid " .
			"WHERE rek_author_id_pid IS NULL ";

		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}


	/**
	 * Method used to retrieve the list of PIDs with incorrect percentages (percentages that
	 * do not total either 0% or 100% for a given author). We don't actually do the calculation
	 * on the fly, but rather, examine a flag that should have been zeroed if the percentages
	 * did not add up properly at data entry time.
	 *
	 * @access  public
	 * @return  array The associative array of PIDs and their titles.
	 */
	function getBadSums() 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT DISTINCT(af_pid), rek_title " .
				"FROM " . APP_TABLE_PREFIX . "author_affiliation, " . APP_TABLE_PREFIX . "record_search_key " .
				"WHERE af_pid = rek_pid " .
				"AND af_status = 0";

		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}

}
