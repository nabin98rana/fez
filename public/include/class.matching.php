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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH . "class.matching_journals.php");
include_once(APP_INC_PATH . "class.matching_conferences.php");

class matching
{
	/**
	 * Returns all matches (automatic, manual, and black-listed items).
	 */
	function getAllMatches($current_row = 0, $max = 25, $filter = "", $filterYear = '')
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$start = $current_row * $max;

		$stmtSelect = "
			SELECT
				pid,
				jnl_era_year,
				cnf_era_year,
				jnl_id,
				cnf_id,
				rek_title AS record_title,
				jnl_era_id AS journal_era_id,
				jnl_journal_name AS journal_name,
				mtj_status AS journal_match_status,
				cnf_era_id AS conference_era_id,
				cnf_conference_name AS conference_name,
				mtc_status AS conference_match_status
		";

		$stmtFrom = "
			 FROM
				(SELECT
					mtj_pid AS pid
				FROM
					" . APP_TABLE_PREFIX . "matched_journals
				UNION
				SELECT
					mtc_pid AS pid
				FROM
					" . APP_TABLE_PREFIX . "matched_conferences) AS Q0
			LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key ON Q0.pid = " . APP_TABLE_PREFIX . "record_search_key.rek_pid
			LEFT JOIN " . APP_TABLE_PREFIX . "matched_journals ON Q0.pid = " . APP_TABLE_PREFIX . "matched_journals.mtj_pid
			LEFT JOIN " . APP_TABLE_PREFIX . "journal ON " . APP_TABLE_PREFIX . "matched_journals.mtj_jnl_id = " . APP_TABLE_PREFIX . "journal.jnl_id
			LEFT JOIN " . APP_TABLE_PREFIX . "matched_conferences ON Q0.pid = " . APP_TABLE_PREFIX . "matched_conferences.mtc_pid
			LEFT JOIN " . APP_TABLE_PREFIX . "conference ON " . APP_TABLE_PREFIX . "matched_conferences.mtc_cnf_id = " . APP_TABLE_PREFIX . "conference.cnf_id
		";


		if ($filter != '') {
			$stmtFrom .= "WHERE Q0.pid = " . $db->quote($filter);
		}

        if ($filterYear != '') {
            $stmtFrom .= (strpos($stmtFrom, 'WHERE') === FALSE) ? ' WHERE ' : ' AND ';
            $stmtFrom .= " ( ".APP_TABLE_PREFIX . "journal.jnl_era_year = " . $db->quote($filterYear) . " OR " . APP_TABLE_PREFIX . "conference.cnf_era_year = " . $db->quote($filterYear)." ) ";
        }

		$stmtLimit = "
			ORDER BY pid ASC
			LIMIT " . $db->quote($max, 'INTEGER') . " OFFSET " . $db->quote($start, 'INTEGER') . ";";

		$stmt = $stmtSelect . $stmtFrom . $stmtLimit;

		try {
			$result = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		/* Get page count stuff */
		$stmt = "SELECT COUNT(*) " . $stmtFrom;
		try {
			$total_rows = $db->fetchOne($stmt);
		} catch(Exception $ex) {
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
			"list" => $result,
			"list_info" => array(
				"current_page"  => $current_row,
				"start_offset"  => $start,
				"end_offset"    => $total_rows_limit,
				"total_rows"    => $total_rows,
				"total_pages"   => $total_pages,
				"prev_page" 	=> ($current_row == 0) ? "-1" : ($current_row - 1),
				"next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
				"last_page"     => $last_page
			)
		);
	}

	/**
	 * Get a list of all PIDs that are not to be mapped.
	 */
	function getMatchingExceptions($type)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if ($type == 'J') {
			$table = "journals";
			$prefix = "mtj";
		} elseif ($type == 'C') {
			$table = "conferences";
			$prefix = "mtc";
		}

		$stmt = "
			SELECT
				" . $prefix . "_pid AS pid
			FROM
				fez_matched_" . $table . "
			WHERE
				" . $prefix . "_status != 'A'
		    GROUP BY
				pid
		";

		try {
			$result = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $result;
	}

	/**
	 * Save an existing mapping.
	 */
	function save()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$type = $_POST['type'];
		$pid = $_POST['pid'];
		$matching_id = $_POST['matching_id'];
		$status = $_POST['status'];

		if ($type == 'J') {
			$table = "journals";
			$prefix = "mtj";
            $suffix = "_jnl_id";
		} elseif ($type == 'C') {
			$table = "conferences";
			$prefix = "mtc";
            $suffix = "_cnf_id";
		}

		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "matched_" . $table . "
                 SET
                    " . $prefix . $suffix. " = " . $db->quote($matching_id, 'INTEGER') . ",
                    " . $prefix . "_status = " . $db->quote($status) . "
                 WHERE
                    " . $prefix . "_pid = " . $db->quote($pid) . ";";
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}

		header("Location: http://" . APP_HOSTNAME . "/manage/matching.php");
		exit;
	}


	/**
	 * Add a brand new mapping.
	 */
	function add()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$type = $_POST['type'];
		$pid = $_POST['pid'];
		$matching_id = $_POST['matching_id'];
		$status = $_POST['status'];
		$suffix = "";

        if (is_array($matching_id) && ((count($matching_id) > 3 && $type == 'J') || (count($matching_id) > 2 && $type == 'C'))) { //catch users bypassing javascript
          return false;
        }

        if ($type == 'J') {
			$table = "journals";
			$prefix = "mtj";
            $suffix = "_jnl_id";
            RJL::removeMatchByPID($pid);
		} elseif ($type == 'C') {
			$table = "conferences";
			$prefix = "mtc";
            $suffix = "_cnf_id";
            RCL::removeMatchByPID($pid);
		}

		foreach ($matching_id as $mid) {
            $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "matched_" . $table . "
                    (
                    " . $prefix . "_pid,
                    " . $prefix . $suffix . ",
                    " . $prefix . "_status
                    ) VALUES (
                    " . $db->quote($pid) . ",
                    " . $db->quote($mid, 'INTEGER') . ",
                    " . $db->quote($status) . "
                    );";

            try {
                $db->exec($stmt);
            }
            catch(Exception $ex) {
                $log->err($ex);
                return -1;
            }
        }

		header("Location: http://" . APP_HOSTNAME . "/manage/matching.php");
		exit;
	}

    function suggest($term, $type, $year = null)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $term = str_replace(' ', '%', $term);
        if($type == 'C') {
            $stmt = "SELECT cnf_id, CONCAT(cnf_conference_name, ' (', cnf_era_year,')')  AS 'conference_name'
                    FROM " . APP_TABLE_PREFIX . "conference
                    WHERE
                    ( cnf_conference_name LIKE ".$db->quote("%".$term."%")."
                            OR cnf_id = ".$db->quote($term)." ) ";
            $stmt .= empty($year) ? " LIMIT 30" : " AND cnf_era_year = ".$db->quote($year)." LIMIT 30";
        } else {
            $stmt = "SELECT jnl_id, CONCAT(jnl_journal_name, ' (', jnl_era_year,')')  AS 'journal_name'
                    FROM " . APP_TABLE_PREFIX . "journal
                    WHERE
                    ( jnl_journal_name LIKE ".$db->quote("%".$term."%")."
                            OR jnl_id = ".$db->quote($term)." ) ";
            $stmt .= empty($year) ? " LIMIT 30" : " AND jnl_era_year = ".$db->quote($year)." LIMIT 30";

        }

        try {
            $res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;

    }

    function addJournalMapping($pid, $eraJournalId, $year) {
        $log = FezLog::get();
        $db = DB_API::get();

        $JournalId = Matching:: getJournalIdFromEraId($eraJournalId, $year);

        if(empty($JournalId)) {
            return false;
        }

        $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "matched_journals (mtj_pid, mtj_jnl_id, mtj_status) VALUES (" . $db->quote($pid) . ", $db->quote($JournalId), 'M') ON DUPLICATE KEY UPDATE mtj_jnl_id = ".$db->quote($JournalId);
        try {
            $db->query($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return true;
    }

    function getJournalIdFromEraId($eraJournalId, $year) {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT jnl_id FROM " . APP_TABLE_PREFIX . "journal WHERE jnl_era_id = " . $db->quote($eraJournalId) . " AND jnl_era_year = " . $db->quote($year) . "";
        try {
            $res = $db->fetchOne($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;

    }

    function addConferenceMapping($pid, $eraConferenceId, $year) {
        $log = FezLog::get();
        $db = DB_API::get();

        $ConferenceId = Matching:: getConferenceIdFromEraId($eraConferenceId, $year);

        if(empty($ConferenceId)) {
            return false;
        }

        $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "matched_conferences (mtc_pid, mtc_cnf_id, mtc_status) VALUES (" . $db->quote($pid) . ", $db->quote($ConferenceId), 'M') ON DUPLICATE KEY UPDATE mtc_cnf_id = ".$db->quote($ConferenceId);
        try {
            $db->query($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return true;
    }

    function getConferenceIdFromEraId($eraConferenceId, $year) {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT cnf_id FROM " . APP_TABLE_PREFIX . "conference WHERE cnf_era_id = " . $db->quote($eraConferenceId) . " AND cnf_era_year = " . $db->quote($year) . "";
        try {
            $res = $db->fetchOne($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        return $res;

    }

}
