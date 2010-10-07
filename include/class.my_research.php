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
	/**********************************
	 * POSSIBLE PUBLICATION FUNCTIONS *
	 **********************************/
	 
	/**
	 * This function dispatches to the appropriate possible publications functionality
	 */
	function possiblePubsDispatcher()
	{
		$tpl = new Template_API();
		$tpl->setTemplate("myresearch/index.tpl.html");
		
		Auth::checkAuthentication(APP_SESSION);
		$username = Auth::getUsername();
		$actingUser = Auth::getActingUsername();
		$actingUser = Author::getDetailsByUsername($actingUser);
		
		$tpl->assign("type", "possible");
		
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
		
		if ($action == 'claim-add') {
			MyResearch::possiblePubsClaim();
		} elseif ($action == 'claim') {
			$recordDetails = Record::getDetailsLite(@$_POST['pid']);
			$tpl->assign("pid", $recordDetails[0]['rek_pid']);
			$tpl->assign("citation", $recordDetails[0]['rek_citation']);
		} else {
			//$list = xxxfunctionCall(); // TODO :: Write the function which gets the list of records, pass to tpl
			$flagged = MyResearch::getPossibleFlaggedPubs($username);
			$tpl->assign("flagged", $flagged);
		}
		
		$tpl->assign("action", $action);
		$tpl->assign("acting_user", $actingUser);
		
		$tpl->displayTemplate();
		
		return;
	}
	
	
	
	function possiblePubsClaim()
	{
		// 1. Mark the publication claimed in the database
		$pid = @$_POST['pid'];
		$username = "UQ_USER_NAME"; // LKDB / TODO
		$correction = @$_POST['correction'];
		MyResearch::markPossiblePubAsMine($pid, $username, $correction);
		
		// 2. Send an email to Eventum about it
		$subject = "ESPACE :: Claimed Publication";
		$body = "PID: " . $pid . "\n";
		$body .= "Author claim by: " . $username;
		if ($correction != '') {
			$body .= "\n\nCorrection information:\n\n" . $correction;
		}
		Eventum::lodgeJob($subject, $body);
		
		return;
	}
	
	
	
	/**
	 * This function is invoked when a user marks a publication as belonging to a particular author.
	 */	
	function markPossiblePubAsMine($pid, $username, $correction = '')
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_possible_flagged
				(
					mrp_pid,
					mrp_author_username,
					mrp_timestamp,
					mrp_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($username) . ",
					" . $db->quote(Date_API::getCurrentDateGMT()) . ",
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
	 * Get all flagged publications for a given user.
	 */
	function getPossibleFlaggedPubs($username)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
					mrp_pid,
					mrp_correction
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
			$ret[$row['mrp_pid']] = $row['mrp_correction'];
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
		$tpl = new Template_API();
		$tpl->setTemplate("myresearch/index.tpl.html");
		
		Auth::checkAuthentication(APP_SESSION);
		$username = Auth::getUsername();
		$actingUser = Auth::getActingUsername();
		$actingUser = Author::getDetailsByUsername($actingUser);
		
		$tpl->assign("type", "claimed");
		
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
		} else {
			//$list = xxxfunctionCall(); // TODO :: Write the function which gets the list of records, pass to tpl
			$flagged = MyResearch::getClaimedFlaggedPubs($username);
			$tpl->assign("flagged", $flagged);
		}
		
		$tpl->assign("action", $action);
		$tpl->assign("acting_user", $actingUser);
		
		$tpl->displayTemplate();
		
		return;
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
					mrc_correction
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
		$username = "UQ_USER_NAME"; // LKDB / TODO
		MyResearch::markClaimedPubAsNotMine($pid, $username);
		
		// 2. Send an email to Eventum about it
		$subject = "ESPACE :: Disowned Publication";
		$body = "PID: " . $pid . "\n";
		$body .= "Author to remove: " . $username;
		Eventum::lodgeJob($subject, $body);
		
		return;
	}
	
	
	
	/**
	 * Fire relevant subroutines for correcting a claimed publication.
	 */
	function claimedPubsCorrect($pid)
	{
		// 1. Mark the publication claimed in the database
		$username = "UQ_USER_NAME"; // LKDB / TODO
		$correction = '';
		MyResearch::markClaimedPubAsNeedingCorrection($pid, $username, @$_POST['correction']);
		
		// 2. Send an email to Eventum about it
		$subject = "ESPACE :: Correction Required";
		$body = "PID: " . $pid . "\n";
		$body .= "\n\nCorrection information:\n\n" . $correction;
		Eventum::lodgeJob($subject, $body);
		
		return;
	}
	
	
	
	/**
	 * This function is invoked when a user marks a claimed publication as not being theirs.
	 */	
	function markClaimedPubAsNotMine($pid, $username)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$correction = '';
		
		$stmt = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_claimed_flagged
				(
					mrc_pid,
					mrc_author_username,
					mrc_timestamp,
					mrc_type,
					mrc_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($username) . ",
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
	function markClaimedPubAsNeedingCorrection($pid, $username, $correction)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_claimed_flagged
				(
					mrc_pid,
					mrc_author_username,
					mrc_timestamp,
					mrc_type,
					mrc_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($username) . ",
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

}
