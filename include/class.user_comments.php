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
 * of user comments.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Jonathan Harker <jonathan@catalyst.net.nz>
 */

include_once APP_INC_PATH . "class.error_handler.php";
include_once APP_INC_PATH . "class.fedora_api.php";
include_once APP_INC_PATH . "class.auth.php";
include_once APP_INC_PATH . "class.db_api.php";

class UserComments
{
	/**
	 * Array of comments for the given PID. Two dimensional associative array.
	 * The first index is the comment number 0 to n.
	 * The second index is one of:
	 *   userid,    pid, comment, date_created, formatted_date_created,
	 *   usr_username,  usr_full_name, usr_email
	 *
	 * For example:
	 *
	 *  $email = $myusercomments->comments[0]['usr_email'];
	 */
	var $comments;

	/**
	 * The current PID, set in getUserComments.
	 */
	var $pid;

	/**
	 * Database prefix placeholder.
	 */
	var $prefix;

	/**
	 * Overall rating calculated from the average of all rating comments.
	 * A number from 0 - 100.
	 */
	var $overall_rating;

	/**
	 * Number of user ratings contributing to the overall rating.
	 */
	var $number_of_ratings;

	/**
	 * Constructor.
	 *
	 * @param string $thepid The PID to retreive comments.
	 */
	function UserComments($thepid) 
	{
		$this->prefix = "" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
		$this->getUserComments($thepid);
	}

	/**
	 * Populates the comments attached to a given PID. Populates the comments
	 * property with an associative array.
	 *
	 * @access  public
	 * @param   string  $thepid  The PID
	 * @return  boolean          Success or failure of the comment query.
	 */
	function getUserComments($thepid) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		// clear old values
		$this->pid = $thepid;
		$this->comments = array();

		// fetch from the database
		$stmt = "SELECT usc_userid, usc_pid, usc_comment, usc_rating, usc_date_created, " .
                "       usr_username, usr_full_name, usr_email " .
                "FROM " . APP_TABLE_PREFIX . "user_comments " .
                "JOIN " . APP_TABLE_PREFIX . "user on usc_userid = usr_id " .
                "WHERE usc_pid = " . $db->quote($thepid).
                " ORDER BY usc_date_created ASC";
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		// populate comments property
		$this->overall_rating = 0;
		$this->number_of_ratings = 0;

		foreach ($res as $row) {
			$row['formatted_date_created'] = strftime("%H:%M, %A %e %B %Y", strtotime($row['usc_date_created']));
			$row['comment'] = stripslashes($row['usc_comment']);
			$this->comments[] = $row;
			if ($row['usc_rating'] > 0) {
				$this->number_of_ratings ++;
				$this->overall_rating += $row['usc_rating'] * 20;
			}
		}

		if ($this->number_of_ratings > 0) { // div by 0
			$this->overall_rating = round($this->overall_rating / $this->number_of_ratings);
		}

		return true;
	}

	/**
	 * Adds a user comment to the current PID.
	 *
	 * @access  public
	 * @param   string  $comment The comment text
	 * @param   integer $usr_id  The user id of the comment. If not specified,
	 *                           defaults to the currently logged in user.
	 * @return  boolean          Success or failure of the database insert.
	 */
	function addUserComment($comment, $rating = 0, $usr_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		 
		if (empty($usr_id)) {
			return false;
		}
		 
		// TODO: check if the user has view permissions first

		$stmt = "INSERT INTO ". APP_TABLE_PREFIX ."user_comments (usc_pid, usc_userid, usc_comment, usc_rating, usc_date_created) " .
                "VALUES (
                ".$db->quote($this->pid).", 
                ".$db->quote($usr_id, 'INTEGER').", 
                ".$db->quote(nl2br(htmlentities($comment))).", 
                ".$db->quote($rating, 'INTEGER').", 
                ".$db->quote(date('Y-m-d H:i:s')).")";
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		$comment['usc_id'] = $db->lastInsertId(APP_TABLE_PREFIX ."user_comments", "usc_id");

		$this->comments[] = $comment;
		$this->number_of_ratings ++;
		$this->overall_rating += $rating * 20;
		$this->overall_rating = round($this->overall_rating / $this->number_of_ratings);

		return true;
	}

	/**
	 * Returns an XML string containing a complete FezComments XML record
	 * of all comments.
	 */
	function getCommentsXML() 
	{
		if (empty($this->comments)) {
			return false;
		}

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<FezComments xmlns="http://www.library.uq.edu.au/escholarship">
 <overall_rating>' . $this->overall_rating . '</overall_rating>
';

		foreach ($this->comments as $comment) {
			$xml .= " <comment>
  <id>{$comment['usc_id']}</id>
  <user_id>{$comment['usc_userid']}</user_id>
  <user_full_name>{$comment['usr_full_name']}</user_full_name>
  <pid>{$comment['usc_pid']}</pid>
  <text>" . $comment['usc_comment'] ."</text>
  <rating>{$comment['usc_rating']}</rating>
  <date_created>{$comment['usc_date_created']}</date_created>
 </comment>
";
		}
		$xml .= '</FezComments>';
		return $xml;
	}

	/**
	 * Attaches the comments as a FezComments XML datastream to the Fedora
	 * object corresponding to the PID.
	 */
	function uploadCommentsToFedora() 
	{
		# try uploading FezComments datastream to Fedora
		$uc_xml = $this->getCommentsXML();

		try {
			if (Fedora_API::datastreamExists($this->pid, "FezComments")) {
				Fedora_API::callModifyDatastreamByValue($this->pid, "FezComments", "A", "Fez Comments Datastream", $uc_xml, "text/xml", "inherit");
			} else {
				Fedora_API::getUploadLocation($this->pid, "FezComments", $uc_xml, "Fez Comments Datastream", "text/xml", "M", null, "false");
			}
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

}
