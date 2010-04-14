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
 * Class to handle the logic behind the internationalization issues
 * of the application.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */

// this will eventually be used to support more than one language
$avail_langs = array(
    "en"
);
@define("APP_DEFAULT_LANG" , "en");

class Language
{
	/**
	 * Method used to set the appropriate preference of the language
	 * for the application.
	 *
	 * @access  public
	 * @return  void
	 */
	function setPreference()
	{
		global $app_lang, $avail_langs;

		session_name(APP_SESSION);
		session_start();
		if (!empty($_GET["lang"])) {
			session_register("app_lang");
			if (!in_array($_GET["lang"], $avail_langs)) {
				$app_lang = APP_DEFAULT_LANG;
			} else {
				$app_lang = $_GET["lang"];
			}
		}
		if (empty($app_lang)) {
			$app_lang = APP_DEFAULT_LANG;
		}
		@define("APP_CURRENT_LANG", $app_lang);
	}
	
	
	
	/**
	 * Method used to get the list of languages available in the system.
	 *
	 * @access  public
	 * @return  array The list of languages
	 */
	function getList()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "
					SELECT
						*
					FROM
						" . APP_TABLE_PREFIX . "language
					ORDER BY
						lng_english_name ASC
		";
		
		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res;
	}	
	
	
	
	/**
	 * Method used to get the details for a given language code.
	 *
	 * @access  public
	 * @param   string $lng_code The language code
	 * @return  array The language details
	 */
	function getDetails($lng_code)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if(empty($lng_code)) {
			return "";
		}

		$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "language
                 WHERE
                    lng_alpha3_bibliographic = " . $db->quote($lng_code, 'STRING');
        
		try {
			$res = $db->fetchRow($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $res;
	}
	
	
	
	/**
	 * Method used to get the english name of a specific language code.
	 *
	 * @access  public
	 * @param   string $lng_code The language code
	 * @return  string The english name of the language
	 */
	function getTitle($lng_code)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if(empty($lng_code)) {
			return "";
		}

		$stmt = "SELECT
                    lng_english_name
                 FROM
                    " . APP_TABLE_PREFIX . "language
                 WHERE
                    lng_alpha3_bibliographic = " . $db->quote($lng_code, 'STRING');
        
		try {
			$res = $db->fetchRow($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if ($res['lng_english_name'] != '') {
			return $res['lng_english_name'];
		} else {
			return $lng_code;
		}
	}
	
	
	
	/**
	 * Method used to update the details of the language.
	 *
	 * @access  public
	 * @return  integer 1 if the update worked, -1 otherwise
	 */
	function update()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (Validation::isWhitespace($_POST["en_name"])) {
			return -2;
		} elseif ($_POST["alpha3_ter"] != '' && strlen($_POST["alpha3_ter"]) != 3) {
			return -3;
		}  elseif ($_POST["alpha2"] != '' && strlen($_POST["alpha2"]) != 2) {
			return -4;
		} elseif ($_POST["ascl"] != '' && strlen($_POST["ascl"]) != 4) {
			return -5;
		}
		
		$stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "language
                 SET
                    lng_english_name=" . $db->quote($_POST["en_name"]) . ",
                    lng_french_name=" . $db->quote($_POST["fr_name"]) . ",
                    lng_alpha3_terminologic=" . $db->quote($_POST["alpha3_ter"]) . ",
                    lng_alpha2=" . $db->quote($_POST["alpha2"]) . ",
                    lng_ascl_code=" . $db->quote($_POST["ascl"]) . " ";
		$stmt .= "WHERE
                    lng_alpha3_bibliographic=" . $db->quote($_POST["id"], 'STRING');
                    
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
	 * Method used to remove a given set of languages from the system.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function remove()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "language
                 WHERE
                    lng_alpha3_bibliographic IN (".Misc::arrayToSQLBindStr($_POST["items"]).")";
		try {
			$db->query($stmt, $_POST['items']);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}
	
	
	
	/**
	 * Method used to add a new language to the system.
	 *
	 * @access  public
	 * @return  integer 1 if the update worked, -1 or -2 otherwise
	 */
	function insert()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (Validation::isWhitespace($_POST["en_name"])) {
			return -2;
		} elseif ($_POST["alpha3_ter"] != '' && strlen($_POST["alpha3_ter"]) != 3) {
			return -3;
		}  elseif ($_POST["alpha2"] != '' && strlen($_POST["alpha2"]) != 2) {
			return -4;
		} elseif ($_POST["ascl"] != '' && strlen($_POST["ascl"]) != 4) {
			return -5;
		}
		
		$stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "language
                 (
                    lng_alpha3_bibliographic,
					lng_alpha3_terminologic,
					lng_alpha2,
                    lng_english_name,
                    lng_french_name,
                    lng_ascl_code
                 ) VALUES (
                    " . $db->quote($_POST["alpha3_bib"]) . ",
					" . $db->quote($_POST["alpha3_ter"]) . ",					
					" . $db->quote($_POST["alpha2"]) . ",
					" . $db->quote($_POST["en_name"]) . ",
					" . $db->quote($_POST["fr_name"]) . ",
					" . $db->quote($_POST["ascl"]) . "
				 )
                  ";
		
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}

}
