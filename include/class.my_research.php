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
	 * This function dispatches to the appropriate possiblie publications functionality
	 */
	function possiblePubsDispatcher()
	{
		$tpl = new Template_API();
		$tpl->setTemplate("myresearch/index.tpl.html");
		
		Auth::checkAuthentication(APP_SESSION);
		$username = Auth::getUsername();
		
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
			$flagged = MyResearch::getPossibleFlaggedPubs($username);
			$tpl->assign("flagged", $flagged);
		}
		
		$tpl->assign("action", $action);
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
		$body = "Here is the body of the email. Include all the appropriate details here for letting the data team know what they have to do."; // LKDB / TODO
		Eventum::lodgeJob($subject, $body);
		
		return;
	}
	
	
	
	/**
	 * This function is invoed when a user marks a publication as belonging to a particular author.
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
	
}
