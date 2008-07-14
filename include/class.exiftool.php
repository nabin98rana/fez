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
                    '" . Misc::escapeString($exif_array["pid"]) . "',
                    '" . Misc::escapeString($exif_array["dsid"]) . "',
                    '" . Misc::escapeString($exif_array["file_size"]) . "',
                    '" . Misc::escapeString($exif_array["file_size_human"]) . "',
                    '" . Misc::escapeString($exif_array["image_width"]) . "',
                    '" . Misc::escapeString($exif_array["image_height"]) . "',
                    '" . Misc::escapeString($exif_array["mime_type"]) . "',
                    '" . Misc::escapeString($exif_array["camera_model_name"]) . "',
                    '" . Misc::escapeString($exif_array["make"]) . "',
                    '" . Misc::escapeString($exif_array["file_type"]) . "',
                    '" . Misc::escapeString($exif_array["play_duration"]) . "',
                    '" . Misc::escapeString($exif_array["exif_all"]) . "'
                 ";

		if (is_integer($exif_array["page_count"])) {
			$insert .= ",exif_page_count";
			$values .= ",'" . Misc::escapeString($exif_array["page_count"]) . "'";
		}

		$insert .= ",exif_create_date";
		if ($exif_array["create_date"] == "" || empty($exif_array["create_date"])) {
			$values .= ",NULL";
		} else {
			$values .= ",'" . Misc::escapeString($exif_array["create_date"]) . "'";
		}
		$values .= ")";

		$stmt = $insert . $values;
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
           return 1;
        }
    }


    function remove($pid, $dsID)
    {
		if ($pid == "" || $dsID == "") { 
			return false;
		}
	
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "exif
                 WHERE
                    exif_pid = '" . Misc::escapeString($pid) . "'
					AND exif_dsid = '" . Misc::escapeString($dsID) . "'";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }

    function getDetails($pid, $dsID)
    {
        $stmt = "SELECT
                    * FROM
                    " . APP_TABLE_PREFIX . "exif
                 WHERE
                    exif_pid = '" . Misc::escapeString($pid) . "'
					AND exif_dsid = '" . Misc::escapeString($dsID) . "'";
        
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    function hasExifData($pid)
    {
        $stmt = "SELECT
                    exif_pid FROM
                    " . APP_TABLE_PREFIX . "exif
                 WHERE
                    exif_pid = '" . Misc::escapeString($pid) . "' LIMIT 1 OFFSET 0";
        
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if ($res == $pid) {
				return true;
			} else {
				return false;
			}
        }
    }




    function saveExif($pid, $dsID) {
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
	function extractMetadata($path) {
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
    
}

?>
