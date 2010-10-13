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

include_once(APP_INC_PATH . "class.eventum.php");

/**
 * MyResearch
 * This is a static class that handles My Research functionality, such as record correction flagging.
 */
class MyResearch
{
	/**
	 * Dispatch to the appropriate functionality for the requested page.
	 */
	function dispatcher($type) {
		$tpl = new Template_API();
		$tpl->setTemplate("myresearch/index.tpl.html");

		Auth::checkAuthentication(APP_SESSION);
		$username = Auth::getUsername();
		$actingUser = Auth::getActingUsername();
		$author_id = Author::getIDByUsername($actingUser);
		$actingUserArray = Author::getDetailsByUsername($actingUser);
		$actingUserArray['org_unit_description'] = MyResearch::getHRorgUnit($actingUser);

		$tpl->assign("type", $type);

		$isUser = Auth::getUsername();
		$isAdministrator = User::isUserAdministrator($isUser);
		$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
		$isUPO = User::isUserUPO($isUser);

		$tpl->assign("isUser", $isUser);
		$tpl->assign("isAdministrator", $isAdministrator);
		$tpl->assign("isSuperAdministrator", $isSuperAdministrator);
		$tpl->assign("isUPO", $isUPO);
		$tpl->assign("active_nav", "my_fez");

		// Determine what we're actually doing here.
		$action = @$_POST['action'];

		if ($type == "possible") {
			if ($action == 'claim-add') {
				MyResearch::possiblePubsClaim();
			} elseif ($action == 'claim') {
				$recordDetails = Record::getDetailsLite(@$_POST['pid']);
				$tpl->assign("pid", $recordDetails[0]['rek_pid']);
				$tpl->assign("citation", $recordDetails[0]['rek_citation']);
			} elseif ($action == 'hide') {
				MyResearch::hide(@$_POST['pid']);
			} elseif ($action == 'hide-bulk') {
				MyResearch::bulkHide();
			}
			
			$flagged = MyResearch::getPossibleFlaggedPubs($actingUser);
		} elseif ($type == "claimed") {
			if ($action == 'not-mine') {
				MyResearch::claimedPubsDisown(@$_POST['pid']);
			} elseif ($action == 'not-mine-bulk') {	
				MyResearch::handleBulkDisown();
			} elseif ($action == 'correction') {
				$recordDetails = Record::getDetailsLite(@$_POST['pid']);
				$tpl->assign("pid", $recordDetails[0]['rek_pid']);
				$tpl->assign("citation", $recordDetails[0]['rek_citation']);
			} elseif ($action == 'correction-add') {
				MyResearch::claimedPubsCorrect(@$_POST['pid']);
			}
			$flagged = MyResearch::getClaimedFlaggedPubs($actingUser);
		}

		/*
	     * These are the only $params(ie. $_GET) vars that can be passed to this page.
	     * Strip out any that aren't in this list
	     */
	    $args = array(
	        'browse'        =>  'string',
	        'author_id'     =>  'numeric',
	        'hide_closed'     =>  'numeric',
	        'collection_pid'=>  'string',
	        'community_pid' =>  'string',
	        'cat'           =>  'string',
	        'author'        =>  'string',
	        'tpl'           =>  'numeric',
	        'year'          =>  'numeric',
	        'rows'          =>  'numeric',
	        'pager_row'     =>  'numeric',
	        'sort'          =>  'string',
	        'sort_by'       =>  'string',
	        'search_keys'   =>  'array',
	        'order_by'      =>  'string',
	        'sort_order'    =>  'string',
	        'value'         =>  'string',
	        'operator'      =>  'string',
	        'custom_view_pid' =>  'string',
	        'form_name'     =>  'string',
	    );
		
		$params = $_GET;
	    foreach ($args as $getName => $getType) {            
	        if( Misc::sanity_check($params[$getName], $getType) !== false ) {
	            $allowed[$getName] = $params[$getName];
	        }
	    }
	    $params = $allowed;

		$cookie_key = "my_research_possible_list";
		$options = array();
		$options = Pager::saveSearchParams($params, $cookie_key);

		$pager_row = $params['pager_row'];
		if (empty($pager_row)) {
			$pager_row = 0;
		}
		
		$rows = $params['rows'];
		if (empty($rows)) {
			if(!empty($_SESSION['rows'])) {
				$rows = $_SESSION['rows'];
			} else {
				$rows = APP_DEFAULT_PAGER_SIZE;
			}
		} else {
			$_SESSION['rows'] = $rows;
		}

		$order_dir = 'ASC';

		if ($max == "ALL") {
			$max = 9999999;
		}
		//$current_row = ($current_row/100);
		$citationCache = true;
		$getSimple = true;

		if ($type == "claimed") {
			$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
			$filter["searchKey".Search_key::getID("Object Type")] = 3; 
			$filter["searchKey".Search_Key::getID("Author ID")] = $author_id; 
		} elseif ($type == "possible") {
			$lastname = Author::getLastName($author_id);
			$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
			$filter["searchKey".Search_key::getID("Object Type")] = 3; 
			$filter["searchKey".Search_Key::getID("Author")] = $lastname;
			$filter["manualFilter"] = " !author_id_mi:".$author_id;
			if ($options['hide_closed'] == 0) {
				$hidePids = MyResearch::getHiddenPidsByUsername($actingUser);
				if (count($hidePids) > 0) {
						$filter["manualFilter"] .= " AND !pid_t:('".str_replace(':', '\:', implode("' OR '", $hidePids))."')";
				}
			}
		}

		$return = Record::getListing($options, array(9,10), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter, "AND", true, false, false, 10, 1);


    $facets = @$return['facets'];

    /*
     * We dont want to display facets that a user
     * has already searched by
     */
    if(isset($facets)) {
            
        foreach ($facets as $sek_id => $facetData) {
            if(!empty($options['searchKey'.$sek_id])) {
                unset($facets[$sek_id]);
            }
        }
            
    }

		$tpl->assign("facets", $facets);
		$tpl->assign("list", $return['list']);
		$tpl->assign("list_info", $return['info']);
		$tpl->assign("flagged", $flagged);
		$tpl->assign("action", $action); 
		$tpl->assign("options", $options);
		$tpl->assign("acting_user", $actingUserArray);
		$tpl->assign("actual_user", $username);

		/*
		 * These options are used in a dropdown box to allow the 
		 * user to sort a list
		 */
		$sort_by_list = array(
			"searchKey".Search_Key::getID("Title") => 'Title',
			"searchKey".Search_Key::getID("Description") => 'Description',
			"searchKey".Search_Key::getID("File Downloads") => 'File Downloads',
			"searchKey".Search_Key::getID("Date") => 'Date',
			"searchKey".Search_Key::getID("Created Date") => 'Created Date',
			"searchKey".Search_Key::getID("Updated Date") => 'Updated Date',
			"searchKey".Search_Key::getID("Sequence") => 'Sequence',
			"searchKey".Search_Key::getID("Thomson Citation Count") => 'Thomson Citation Count',
			"searchKey".Search_Key::getID("Scopus Citation Count") => 'Scopus Citation Count'
		);

		if (Auth::isValidSession($_SESSION)) {
			$sort_by_list["searchKey".Search_Key::getID("GS Citation Count")] = "Google Scholar Citation Count";
		}
		
		$tpl->assign('sort_by_list', $sort_by_list);
		
		
		if(count($params) > 0) {
        
        $exclude[] = 'rows';
        $tpl->assign('url_wo_rows', Misc::query_string_encode($params,$exclude));
        array_pop($exclude);
        
        $exclude[] = 'tpl';
        $tpl->assign('url_wo_tpl',  Misc::query_string_encode($params,$exclude));
        array_pop($exclude);
        
        $exclude[] = 'sort';
        $exclude[] = 'sort_by';
        $tpl->assign('url_wo_sort', Misc::query_string_encode($params,$exclude));
    }
		
		// Hack to get SCRIPT_URL without querystrings.
		// Usually we could get this info from $_SERVER['SCRIPT_URL'], but can't since 
		// we are doing rewrite rules on a per-directory basis via .htaccess file
		$PAGE_URL = preg_replace('/(\?.*)/','',$_SERVER['REQUEST_URI']);
		$tpl->assign('PAGE_URL', $PAGE_URL);
		$tpl->assign('list_type', $type);
   	$terms = @$return['info']['search_info'];
    $tpl->assign('terms', $terms);
   	$tpl->assign("list_heading", "My $type Research");        	 
		$sort_by = $options["sort_by"];
    $tpl->assign('rows', $rows);
    $tpl->assign('sort_order', $options["sort_order"]);
    $tpl->assign('sort_by_default', $sort_by);
		$tpl->displayTemplate();
		
		return;

	}	
	
	
	
	/**********************************
	 * POSSIBLE PUBLICATION FUNCTIONS *
	 **********************************/
	 
	/**
	 * This function handles the various actions associated with claiming a possible publication.
	 */
	function possiblePubsClaim()
	{
		// 1. Mark the publication claimed in the database
		$pid = @$_POST['pid'];
		$author = Auth::getActingUsername();
		$user = Auth::getUsername();
		$correction = @$_POST['correction'];
		MyResearch::markPossiblePubAsMine($pid, $author, $user, $correction);
		
		// 2. Send an email to Eventum about it
		$authorDetails = Author::getDetailsByUsername($author);
		$userDetails = User::getDetails($user);
		$authorID = $authorDetails['aut_id'];
		$authorName = $authorDetails['aut_display_name'];
		$userName = $userDetails['usr_full_name'];
		$userEmail = $userDetails['usr_email'];

		$subject = "My Research :: Claimed Publication :: " . $pid . " :: " . $author;
		
		$body = "Record: http://" . APP_HOSTNAME . APP_RELATIVE_URL . "view/" . $pid . "\n\n";
		if ($author == $user) {
			$body .= $authorName . " (" . $authorID . ") has claimed to be an author of this publication.\n\n";
		} else {
			$body .= "User "  . $userName . " has indicated that " . $authorName . " (" . $authorID . ") is an author of this publication.\n\n";
		}
		
		if ($correction != '') {
			$body .= "Additionally, the following correction information was supplied:\n\n" . $correction;
		}
		
		Eventum::lodgeJob($subject, $body, $userEmail);
		
		return;
	}
	
	
	
	/**
	 * This function is invoked when a user marks a publication as belonging to a particular author.
	 */	
	function markPossiblePubAsMine($pid, $author, $user, $correction = '')
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_possible_flagged
				(
					mrp_pid,
					mrp_author_username,
					mrp_user_username,
					mrp_timestamp,
					mrp_type,
					mrp_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($author) . ",
					" . $db->quote($user) . ",
					" . $db->quote(Date_API::getCurrentDateGMT()) . ",
					" . $db->quote('C') . ",
					" . $db->quote($correction) . ");";
		try {
			$db->exec($stmt);
		}
		
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
				
		return 1;
	}
	
	
	
	/**
	 * This function hides a possible publication from the user.
	 */	
	function hide($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$author = Auth::getActingUsername();
		$user = Auth::getUsername();
		$correction = '';
		
		$stmt = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_possible_flagged
				(
					mrp_pid,
					mrp_author_username,
					mrp_user_username,
					mrp_timestamp,
					mrp_type,
					mrp_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($author) . ",
					" . $db->quote($user) . ",
					" . $db->quote(Date_API::getCurrentDateGMT()) . ",
					" . $db->quote('H') . ",
					" . $db->quote($correction) . ");";
		try {
			$db->exec($stmt);
		}
		
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
			
		return 1;
	}
	
	
	
	/**
	 * Hide a whole bunch of PIDs at once.
	 */
	function bulkHide()
	{
		$pids = explode(",", @$_POST['bulk-hide-pids']);
		
		foreach ($pids as $pid) {
			MyResearch::hide($pid);
		}
		
		return;
	}
	

	public static function getHiddenPidsByUsername($username)
	{
		$log = FezLog::get();

		$res = array();
		$db = DB_API::get();
		
		$stmt = "SELECT mrp_pid FROM " . APP_TABLE_PREFIX . "my_research_possible_flagged
							WHERE mrp_type = 'H' and mrp_author_username = " . $db->quote($username) . "";

		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);	
		}
		return $res;
	}
	
	
	/**
	 * Get all flagged publications for a given user.
	 */
	function getPossibleFlaggedPubs($username)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
					mrp_pid,
					mrp_type,
					mrp_correction,
					mrp_user_username
				FROM
					" . APP_TABLE_PREFIX . "my_research_possible_flagged
				WHERE
					mrp_author_username = " . $db->quote($username) . "";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		// Reformat the results so that we can easily comapre them to the record index.
		$ret = array();	
		foreach ($res as $row) {
			$ret[$row['mrp_pid']]['type'] = $row['mrp_type'];
			$ret[$row['mrp_pid']]['correction'] = $row['mrp_correction'];
			$ret[$row['mrp_pid']]['user'] = $row['mrp_user_username'];
		}
		
		return $ret;
	}
	
	
	
	/*********************************
	 * CLAIMED PUBLICATION FUNCTIONS *
	 *********************************/
	
	/**
	 * This function dispatches to the appropriate claimed publications functionality
	 */
	function claimedPubsDispatcher()
	{
			MyResearch::dispatcher("claimed");
	}
	
	/**
	 * Get all flagged claimed publications for a given user.
	 */
	function getClaimedFlaggedPubs($username)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
					mrc_pid,
					mrc_type,
					mrc_correction,
					mrc_user_username
				FROM
					" . APP_TABLE_PREFIX . "my_research_claimed_flagged
				WHERE
					mrc_author_username = " . $db->quote($username) . "";
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		// Reformat the results so that we can easily comapre them to the record index.
		$ret = array();	
		foreach ($res as $row) {
			$ret[$row['mrc_pid']]['type'] = $row['mrc_type'];
			$ret[$row['mrc_pid']]['correction'] = $row['mrc_correction'];
			$ret[$row['mrc_pid']]['user'] = $row['mrc_user_username'];
		}
		
		return $ret;
	}
	
	
	
	/**
	 * Get the list of PIDs we are disowning, pass off to the singular disown function.
	 */
	function handleBulkDisown()
	{
		$pids = explode(",", @$_POST['bulk-not-mine-pids']);
		
		foreach ($pids as $pid) {
			MyResearch::claimedPubsDisown($pid);
		}
		
		return;
	}
	
	
	
	/**
	 * Fire relevant subroutines for disowning a publication.
	 */
	function claimedPubsDisown($pid)
	{
		// 1. Mark the publication claimed in the database
		$author = Auth::getActingUsername();
		$user = Auth::getUsername();
		MyResearch::markClaimedPubAsNotMine($pid, $author, $user);
		
		// 2. Send an email to Eventum about it
		$authorDetails = Author::getDetailsByUsername($author);
		$userDetails = User::getDetails($user);
		$authorID = $authorDetails['aut_id'];
		$authorName = $authorDetails['aut_display_name'];
		$userName = $userDetails['usr_full_name'];
		$userEmail = $userDetails['usr_email'];
		
		$subject = "My Research :: Disowned Publication :: " . $pid . " :: " . $author;
		
		$body = "Record: http://" . APP_HOSTNAME . APP_RELATIVE_URL . "view/" . $pid . "\n\n";
		if ($author == $user) {
			$body .= $authorName . " (" . $authorID . ") has indicated that they are not the author of this publication.";
		} else {
			$body .= "User "  . $userName . " has indicated that " . $authorName . " (" . $authorID . ") is not the author of this publication.";
		}
		
		Eventum::lodgeJob($subject, $body, $userEmail);
		
		return;
	}
	
	
	
	/**
	 * Fire relevant subroutines for correcting a claimed publication.
	 */
	function claimedPubsCorrect($pid)
	{
		// 1. Mark the publication claimed in the database
		$author = Auth::getActingUsername();
		$user = Auth::getUsername();
		$correction = @$_POST['correction'];
		MyResearch::markClaimedPubAsNeedingCorrection($pid, $author, $user, $correction);
		
		// 2. Send an email to Eventum about it
		$authorDetails = Author::getDetailsByUsername($author);
		$userDetails = User::getDetails($user);
		$authorID = $authorDetails['aut_id'];
		$authorName = $authorDetails['aut_display_name'];
		$userName = $userDetails['usr_full_name'];
		$userEmail = $userDetails['usr_email'];
		
		$subject = "My Research :: Correction Required :: " . $pid . " :: " . $author;
		
		$body = "Record: http://" . APP_HOSTNAME . APP_RELATIVE_URL . "view/" . $pid . "\n\n";
		if ($author == $user) {
			$body .= $authorName . " (" . $authorID . ") has supplied the following correction information:\n\n";
		} else {
			$body .= "User "  . $userName . ", acting on behalf of " . $authorName . ", has supplied the following correction information:\n\n";
		}
		$body .= $correction;
		
		Eventum::lodgeJob($subject, $body, $userEmail);
		
		return;
	}
		
	/**
	 * This function is invoked when a user marks a claimed publication as not being theirs.
	 */	
	function markClaimedPubAsNotMine($pid, $author, $user)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$correction = '';
		
		$stmt = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_claimed_flagged
				(
					mrc_pid,
					mrc_author_username,
					mrc_user_username,
					mrc_timestamp,
					mrc_type,
					mrc_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($author) . ",
					" . $db->quote($user) . ",
					" . $db->quote(Date_API::getCurrentDateGMT()) . ",
					" . $db->quote('D') . ",
					" . $db->quote($correction) . ");";
		try {
			$db->exec($stmt);
		}
		
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}

		
		return 1;
	}
	
	
	
	/**
	 * This function is invoked when a user marks a claimed publication as not being theirs.
	 */	
	function markClaimedPubAsNeedingCorrection($pid, $author, $user, $correction)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_claimed_flagged
				(
					mrc_pid,
					mrc_author_username,
					mrc_user_username,
					mrc_timestamp,
					mrc_type,
					mrc_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($author) . ",
					" . $db->quote($user) . ",
					" . $db->quote(Date_API::getCurrentDateGMT()) . ",
					" . $db->quote('C') . ",
					" . $db->quote($correction) . ");";
		try {
			$db->exec($stmt);
		}
		
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}

		
		return 1;
	}
	
	
	
	/*****************
	 * UPO FUNCTIONS *
	 *****************/
	 
	 /**
	 * Shows a list of all authors within a given AOU.
	 */	
	 function listAuthorsByAOU($orgID)
	 {
	 	$log = FezLog::get();
		$db = DB_API::get();
		
	 	$stmt = "
				SELECT
					DISTINCT(aut_org_username) AS username,
					aut_fname AS first_name,
					UCASE(aut_lname) AS last_name
				FROM
					" . APP_TABLE_PREFIX . "author,
					hr_position_vw
				WHERE
					hr_position_vw.USER_NAME = fez_author.aut_org_username
					AND AOU = " . $db->quote($orgID) . "
					AND (aut_org_username IS NOT NULL
					OR aut_org_staff_id IS NOT NULL)
				ORDER BY
					aut_lname ASC,
					aut_fname ASC;
	 	";
	 	
	 	try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $res;
	 }
	 
	 
	 
	 /**
	 * Gets the default Org Unit for a particular user.
	 */	
	 function getDefaultAOU($username)
	 {
	 	$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "
				SELECT
					AOU AS aou
				FROM
					hr_position_vw
				WHERE
					USER_NAME = " . $db->quote($username) . "
				LIMIT 1
				";
		
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res['aou'];
	 }
	 
	 
	 
	 /**
	 * Gets the org unit description for a given username.
	 */	
	 function getHRorgUnit($username)
	 {
	 	$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "
				SELECT
					aurion_org_desc AS org_description
				FROM
					hr_position_vw,
					hr_org_unit_distinct_manual
				WHERE
					hr_position_vw.AOU = hr_org_unit_distinct_manual.aurion_org_id
				AND
					USER_NAME = " . $db->quote($username) . "
				LIMIT 1
				";
		
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res['org_description'];
	 }

}
