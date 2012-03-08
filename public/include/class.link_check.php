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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

// We may want to redefine these values as configuration variables down the line ...
define('LINK_CHECK_TIMEOUT', 10);
define('LINK_CHECK_USER_AGENT', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
define('LINK_CHECK_LINK_DEATH_WINDOW', 7); // After 90 days, well assume a link needs to be purged from the link reports table.

class LinkCheck
{
	function findLinks()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		echo "Retrieving list of URLs in the system ... ";

		$query = "
					SELECT 
						DISTINCT(rek_link) AS url
					FROM
						" . APP_TABLE_PREFIX . "record_search_key_link
					ORDER BY
						rek_link_pid, rek_link_order
					;
				";
		try {
			$res = $db->fetchAll($query);
		}
		
		catch(Exception $ex) {
			$log->err($ex);
			return;
		}
		
		echo "done.\n";
		
		return $res;
	}
	
	
	
	function getLinkReport($current_row = 0, $max = 25, $filter_pid = "", $filter_code = "")
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$start = $current_row * $max;
        $stmtWhere = "";

		$stmtSelect = "
					SELECT
						rek_link_pid AS pid,
						rek_link AS url,
						lsr_status AS url_status,
						lsr_timestamp AS last_checked
						";
		$stmtFrom = "
					FROM
						" . APP_TABLE_PREFIX . "record_search_key_link
						
					LEFT JOIN
						" . APP_TABLE_PREFIX . "link_status_reports
						ON " . APP_TABLE_PREFIX . "record_search_key_link.rek_link = " . APP_TABLE_PREFIX . "link_status_reports.lsr_url
					";
		
		if ($filter_pid != '' || $filter_code != '') {
			$stmtWhere = "WHERE ";
		}
		if ($filter_pid != '') {
			$stmtWhere .= "rek_link_pid = " . $db->quote($filter_pid) . " ";
		}
		
		if ($filter_code != '' && $filter_pid != '') {
			$stmtWhere .= "AND ";
		}
		if ($filter_code != '') {
			$stmtWhere .= "lsr_status = " . $db->quote($filter_code) . " ";
		}
		
		$stmtLimit = "
					ORDER BY
						rek_link_pid ASC,
						rek_link_order ASC
					LIMIT " . $db->quote($max, 'INTEGER') . " OFFSET " . $db->quote($start, 'INTEGER') . "
					;
					";
		
		$stmt = $stmtSelect . $stmtFrom . $stmtWhere . $stmtLimit;		
		
		try {
			$res = $db->fetchAll($stmt);
		}
		
		catch(Exception $ex) {
			$log->err($ex);
			return;
		}
		
		/* Page count stuff */
		$stmt = "SELECT COUNT(*) " . $stmtFrom . $stmtWhere;
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
			"list" => $res,
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
	
	
	
	public function testLink($url)
	{
		$ch = curl_init(); // Get cURL handle
		$opts = array(CURLOPT_RETURNTRANSFER => true, // Do not output to browser
		CURLOPT_URL => $url, // Set URL
		CURLOPT_NOBODY => true, // Do a HEAD request only
		CURLOPT_USERAGENT => LINK_CHECK_USER_AGENT,
		CURLOPT_TIMEOUT => LINK_CHECK_TIMEOUT); // Set timeout
		curl_setopt_array($ch, $opts); 
		curl_exec($ch); // Do eeet!
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch); // Close handle
		
		return $status;
	}
	
	
	
	/**
	 * Writes the HTTP status of the specified link to the database.
	 */
	public function saveLinkStatusResult($result)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "
				REPLACE INTO
					" . APP_TABLE_PREFIX . "link_status_reports
				(
					lsr_url,
					lsr_status,
					lsr_timestamp
				) VALUES (
					" . $db->quote($result['url']) . ",
					" . $db->quote($result['status']) . ",
					" . $db->quote(Date_API::getCurrentDateGMT()) . 
			");
		";
		
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
		echo "* " . $result['url'] . " :: " . $result['status'] . "\n"; // LKDB
		return true;
	}
	
	
	
	public function purgeOldLinks()
	{
		$log = FezLog::get();
		$db = DB_API::get();
				
		$stmt = "
				DELETE
				FROM
					" . APP_TABLE_PREFIX . "link_status_reports
				WHERE 
					lsr_timestamp <= DATE_SUB(NOW(), INTERVAL " . LINK_CHECK_LINK_DEATH_WINDOW . " DAY)
				;
				";
		
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
				
		return true;
	}

}