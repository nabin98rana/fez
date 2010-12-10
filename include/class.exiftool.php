<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 - 2008 The University of Queensland,              |
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
class Exiftool
{

	function insert($exif_array)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (!is_array($exif_array)) {
			return -1;
		}

		if ($exif_array["exif_all"] == "") {
			return -1;
		}

		$insert = "INSERT INTO
                    " . APP_TABLE_PREFIX . "exif
                 (
                    exif_pid,
                    exif_dsid,
					exif_file_size,
					exif_file_size_human,
					exif_image_width,
					exif_image_height,
					exif_mime_type,
					exif_camera_model_name,
					exif_make,
					exif_file_type,
					exif_play_duration,
					exif_all  
					";

		$values = ") VALUES (
                    " . $db->quote($exif_array["pid"]) . ",
                    " . $db->quote($exif_array["dsid"]) . ",
                    " . $db->quote($exif_array["file_size"]) . ",
                    " . $db->quote($exif_array["file_size_human"]) . ",
                    " . $db->quote($exif_array["image_width"], 'INTEGER') . ",
                    " . $db->quote($exif_array["image_height"], 'INTEGER') . ",
                    " . $db->quote($exif_array["mime_type"]) . ",
                    " . $db->quote($exif_array["camera_model_name"]) . ",
                    " . $db->quote($exif_array["make"]) . ",
                    " . $db->quote($exif_array["file_type"]) . ",
                    " . $db->quote($exif_array["play_duration"]) . ",
                    " . $db->quote($exif_array["exif_all"]) . "
                 ";

		if (is_integer($exif_array["page_count"])) {
			$insert .= ",exif_page_count";
			$values .= "," . $db->quote($exif_array["page_count"], 'INTEGER');
		}

		$insert .= ",exif_create_date";
		if ($exif_array["create_date"] == "" || empty($exif_array["create_date"])) {
			$values .= ",NULL";
		} else {
			$exif_array["create_date"] = date( 'Y-m-d H:i:s', strtotime($exif_array["create_date"]));
			if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) { //eg if postgresql etc
				$values .= ", TIMESTAMP " . $db->quote($exif_array["create_date"])." ";
			} else {
				$values .= ", " . $db->quote($exif_array["create_date"]);
			}
		}
		$values .= ")";

		$stmt = $insert . $values;
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}


	function remove($pid, $dsID)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if ($pid == "" || $dsID == "") {
			return false;
		}

		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "exif
                 WHERE
                    exif_pid = " . $db->quote($pid) . "
					AND exif_dsid = " . $db->quote($dsID);
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return true;
	}

	function getDetails($pid, $dsID)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    * FROM
                    " . APP_TABLE_PREFIX . "exif
                 WHERE
                    exif_pid = " . $db->quote($pid) . "
					AND exif_dsid = " . $db->quote($dsID);
		
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	function hasExifData($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT
                    exif_pid FROM
                    " . APP_TABLE_PREFIX . "exif
                 WHERE
                    exif_pid = " . $db->quote($pid) . " LIMIT 1 OFFSET 0";
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if ($res == $pid) {
			return true;
		} else {
			return false;
		}
	}




	function saveExif($pid, $dsID) 
	{
		if (APP_EXIFTOOL_SWITCH == "ON") {
			$exif_array = Exiftool::extractMetadata(APP_TEMP_DIR.$dsID);
			if (!is_array($exif_array)) {
				return false;
			} else {
				Exiftool::remove($pid, $dsID);
				$exif_array['pid'] = $pid;
				$exif_array['dsid'] = $dsID;
				return Exiftool::insert($exif_array);
			}
		}
	}


	/**
	 * Returns the exiftool information as an array - credit goes to the Drupal MAQUM module for this function
	 */
	function extractMetadata($path) 
	{
		$temp = shell_exec(APP_EXIFTOOL_CMD . ' -n --list '.escapeshellarg($path));
		$info['exif_all'] = $temp;
		$temp = explode("\n", $temp);
		foreach ($temp as $item) {
			$pos = strpos($item, ':');
			$info[str_replace(" ", "_", strtolower(trim(substr($item, 0, $pos))))] = trim(substr($item, $pos+1));
		}
		if (array_key_exists('file_size', $info)) {
			$info['file_size_human'] = Misc::size_hum_read($info['file_size']);
		}
		return $info;
	}
	
	/**
	 * renames a datastream id in the exif data if it exists
	 *
	 * @param string $pid
	 * @param string $oldDsID
	 * @param string $newDsID
	 * @return void
	 **/
	public function renameFile($pid, $oldDsID, $newDsID) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$details = self::getDetails($pid, $oldDsID);
		if ($details != '') {
			$sql = "UPDATE " . APP_TABLE_PREFIX . "exif SET exif_dsid = ? WHERE exif_pid = ? AND exif_dsid = ? ";
			$db->query($sql, array($newDsID, $pid, $oldDsID));
		}		
	}

}
