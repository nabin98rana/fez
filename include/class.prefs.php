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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle the business logic related to the user preferences
 * available in the application.
 *
 * @version 1.0
 * @author Jo�o Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.date.php");

class Prefs
{
	/**
	 * Method used to get the system-wide default preferences.
	 *
	 * @access  public
	 * @return  string The serialized array of the default preferences
	 */
	function getDefaults()
	{
		return serialize(array(
            'updated'                 => 0,
            'closed'                  => 0,
            'emails'                  => 1, // @@@ CK - changed so default notifications is 'emails are associated'
            'files'                   => 0,
            'close_popup_windows'     => 1, //@@@ CK added so default user popup is date
            'receive_assigned_emails' => 1,
            'receive_new_emails'      => 0,
            'timezone'                => APP_DEFAULT_USER_TIMEZONE,
		//            'timezone'                => Date_API::getDefaultTimezone(),
            'list_refresh_rate'       => APP_DEFAULT_REFRESH_RATE,
            'emails_refresh_rate'     => APP_DEFAULT_REFRESH_RATE,
            'front_page'     => "front_page",
            'email_signature'         => '',
            'auto_append_sig'         => 'no',
			'remember_search_params'  => 'no'
            ));
	}


	/**
	 * Method used to get the preferences set by a specific user.
	 *
	 * @access  public
	 * @param   integer $usr_id The user ID
	 * @return  array The preferences
	 */
	function get($usr_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		static $returns;

		if (!empty($returns[$usr_id])) {
			return $returns[$usr_id];
		}

		$stmt = "SELECT
                    usr_preferences
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_id=".$db->quote($usr_id, 'INTEGER');
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return '';
		}
		$res = @unserialize($res);
		// check for the refresh rate variables, and use the default values if appropriate
		if (empty($res['list_refresh_rate'])) {
			$res['list_refresh_rate'] = APP_DEFAULT_REFRESH_RATE;
		}
		if (empty($res['emails_refresh_rate'])) {
			$res['emails_refresh_rate'] = APP_DEFAULT_REFRESH_RATE;
		}
		$returns[$usr_id] = $res;
		return $returns[$usr_id];
	}


	/**
	 * Method used to get the email notification related preferences
	 * for a specific user.
	 *
	 * @access  public
	 * @param   integer $usr_id The user ID
	 * @return  array The preferences
	 */
	function getNotification($usr_id)
	{
		$prefs = Prefs::get($usr_id);
		$info = User::getNameEmail($usr_id);
		$prefs["sub_email"] = $info["usr_email"];
		return $prefs;
	}


	/**
	 * Method used to set the preferences for a specific user.
	 *
	 * @access  public
	 * @param   integer $usr_id The user ID
	 * @return  integer 1 if the update worked, -1 otherwise
	 */
	function set($usr_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		// if the user is trying to upload a new signature, override any changes to the textarea
		if (!empty($_FILES["file_signature"]["name"])) {
			$_POST['signature'] = Misc::getFileContents($_FILES["file_signature"]["tmp_name"]);
		}

		$data = serialize(array(
            'updated'                 => @$_POST['updated'],
            'closed'                  => @$_POST['closed'],
            'emails'                  => @$_POST['emails'],
            'files'                   => @$_POST['files'],
            'close_popup_windows'     => $_POST['close_popup_windows'],
            'receive_assigned_emails' => $_POST['receive_assigned_emails'],
            'receive_new_emails'      => $_POST['receive_new_emails'],
            'timezone'                => $_POST['timezone'],
            'list_refresh_rate'       => $_POST['list_refresh_rate'],
            'emails_refresh_rate'     => $_POST['emails_refresh_rate'],
            'email_signature'         => @$_POST['signature'],
            'front_page'              => @$_POST['front_page'],
            'auto_append_sig'         => @$_POST['auto_append_sig'],
			'remember_search_params'  => @$_POST['remember_search_params']
		));
		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "user
                 SET
                    usr_preferences=" . $db->quote($data) . "
                 WHERE
                    usr_id=".$db->quote($usr_id, 'INTEGER');
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err(array('Message' => $ex->getMessage(), 'File' => __FILE__, 'Line' => __LINE__));
			return -1;
		}
		return 1;
	}
}
