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
 * of custom fields in the system.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.workflow_state.php");
include_once(APP_INC_PATH . "class.workflow_status.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");


class Workflow
{
    /**
     * Method used to remove a given list of custom fields.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                 WHERE
                    wfl_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {

		  return true;
        }
    }


    /**
     * Method used to add a new custom field to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
        global $HTTP_POST_VARS;

		
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                 (
                    wfl_title,
                    wfl_version,
                    wfl_description,
                    wfl_roles
                 ) VALUES (
                    '" . Misc::escapeString($HTTP_POST_VARS["wfl_title"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["wfl_version"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["wfl_description"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["wfl_roles"]) . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			//
        }
    }

    /**
     * Method used to add a new custom field to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($wfl_id)
    {
//		echo $HTTP_POST_VARS["xsd_source"];
        global $HTTP_POST_VARS;

		
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                 SET 
                    wfl_title = '" . Misc::escapeString($HTTP_POST_VARS["wfl_title"]) . "',
                    wfl_version = '" . Misc::escapeString($HTTP_POST_VARS["wfl_version"]) . "',
                    wfl_description = '" . Misc::escapeString($HTTP_POST_VARS["wfl_description"]) . "',
                    wfl_roles = '" . Misc::escapeString($HTTP_POST_VARS["wfl_roles"]) . "'
                 WHERE wfl_id = $wfl_id";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			//
        }
    }

    /**
     * Method used to get the title of a specific reminder.
     *
     * @access  public
     * @param   integer $rem_id The reminder ID
     * @return  string The title of the reminder
     */
    function getTitle($wfl_id)
    {
        $stmt = "SELECT
                    wfl_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                 WHERE
                    wfl_id=$wfl_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }

    function checkForImageFile($filename) {  	 
        $image_extensions = array("tiff", "tif", "jpg", "jpeg", "gif", "png"); 	 
        $filename_ext = strtolower(substr($filename, (strrpos($filename, ".") + 1))); 	 
        //echo "file -> ".$filename_ext; 	 
        if (in_array($filename_ext, $image_extensions)) { 	 
            $getString = "http://".APP_HOSTNAME."/webservices/wfb.thumbnail.php?image=".urlencode($filename)."&height=50&width=50&ext=jpg"; 	 
//            echo $getString; 	 
            $http_req = new HTTP_Request($getString, array("http" => "1.0")); 	 
            $http_req->setMethod("GET"); 	 
            $http_req->sendRequest(); 	 
            $xml = $http_req->getResponseBody(); 	 
//            return "thumbnail_".substr($filename, 0, strrpos($filename, ".")).".jpg"; 	 
            if (is_numeric(strpos($filename, "/"))) { 	 
                return APP_TEMP_DIR."thumbnail_".str_replace(" ", "_", substr(substr($filename, 0, strrpos($filename, ".")), strrpos($filename, "/")+1)).".jpg"; 	 
            } else { 	 
                return APP_TEMP_DIR."thumbnail_".str_replace(" ", "_", substr($filename, 0, strrpos($filename, "."))).".jpg"; 	 
            } 	 
        } else { 	 
            return false; 	 
        }         	 
    } 	 
  	 
    function checkForPresMD($filename) { 	 
//        $image_extensions = array("tiff", "tif", "jpg", "jpeg", "gif", "png"); 	 
//        $filename_ext = strtolower(substr($filename, (strrpos($filename, ".") + 1))); 	 
        //echo "file -> ".$filename_ext; 	 
        if (is_numeric(strpos($filename, "."))) { 	 
            $getString = APP_RELATIVE_URL."webservices/wfb.presmd.php?file=".urlencode($filename); 	 
//            echo $getString; 	 
            $http_req = new HTTP_Request($getString, array("http" => "1.0")); 	 
            $http_req->setMethod("GET"); 	 
            $http_req->sendRequest(); 	 
            $xml = $http_req->getResponseBody(); 	 
            if (is_numeric(strpos($filename, "/"))) { 	 
                return APP_TEMP_DIR."presmd_".str_replace(" ", "_", substr(substr($filename, 0, strrpos($filename, ".")), strrpos($filename, "/")+1)).".xml"; 	 
            } else { 	 
                return APP_TEMP_DIR."presmd_".str_replace(" ", "_", substr($filename, 0, strrpos($filename, "."))).".xml"; 	 
            } 	 
        } else { 	 
            return false; 	 
        }         	 
    }

    /**
     * Method used to get the list of custom fields available in the 
     * system.
     *
     * @access  public
     * @return  array The list of custom fields
     */
    function getList()
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                    ";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
                $t = array();
                for ($i = 0; $i < count($res); $i++) {
                    // ignore workflow templates that have no states yet...
                    $states = Workflow_State::getList($res[$i]['wfl_id']);
					$res[$i]['total_states'] = count($states);

                    if (count($states) == 0) {
	                    $t[] = $res[$i];
                        continue;
                    }
                    $res[$i]['states'] = $states;
                    $t[] = $res[$i];
                }
                return $t;
            }
        }
    }


    /**
     * Method used to get the details of a specific custom field.
     *
     * @access  public
     * @param   integer $fld_id The custom field ID
     * @return  array The custom field details
     */
    function getDetails($wfl_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "workflow
                 WHERE
                    wfl_id=$wfl_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    function start($wft_id, $pid, $xdis_id)
    {
        $wfstatus = new WorkflowStatus($pid, $wft_id, $xdis_id);
        $wfstatus->run();
    }

    function processIngestTrigger($pid, $dsID, $mimetype)
    {
        // find first matching trigger
        $record = new RecordObject($pid);
        $wft_details = $record->getIngestTrigger($mimetype);
        if (!empty($wft_details)) {
            // run it
            $dsInfo = array(
                    'ID' => $dsID,
                    'MIMETYPE' => $mimetype);
            $wfstatus = new WorkflowStatus($record->pid, $wft_details['wft_id'], $record->xdis_id, $dsInfo);
            $wfstatus->run();
        }
    }


    function canTrigger($wfl_id, $pid)
    {
        $wfl = Workflow::getDetails($wfl_id);
        if (!empty($wfl['wfl_roles'])) {
            $wfl_roles = preg_split("/[\s,]+/", $wfl['wfl_roles']);
            $pid_roles = Auth::getAuthorisationGroups($pid);
            foreach ($wfl_roles as $wfl_role) {
                if (in_array($wfl_role, $pid_roles)) {
                    return true;
                }
            }
        } else {
            return true;
        }
        return false;
    }



}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Doc_Type_XSD Class');
}
?>
