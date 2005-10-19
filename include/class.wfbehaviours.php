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

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");


/**
 * Definitions of scripts that the workflow states can run.
 * This is just a mapping of scripts into the database with a few flags to say how they must be run
 */
class WF_Behaviour
{

    /**
     * Method used to get an associative array of action types.
     *
     * @access  public
     * @return  array The list of action types
     */
    function getTitles()
    {
        $stmt = "SELECT
                    wfb_id,
                    wfb_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "wfbehaviour
                 ORDER BY
                    wfb_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }
    }






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
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "wfbehaviour
                 WHERE
                    wfb_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } 
    }


    /**
     * Insert a workflow behaviour
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
        global $HTTP_POST_VARS;

		
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "wfbehaviour
                 (
                    wfb_title,
                    wfb_version,
                    wfb_description,
                    wfb_script_name,
                    wfb_auto
                 ) VALUES (
                    '" . Misc::escapeString($HTTP_POST_VARS["wfb_title"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["wfb_version"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["wfb_description"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["wfb_script_name"]) . "',
                    '" . Misc::checkBox(@$HTTP_POST_VARS["wfb_auto"]) . "'
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
     * Update a workflow behaviour
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($wfb_id)
    {
//		echo $HTTP_POST_VARS["xsd_source"];
        global $HTTP_POST_VARS;

		
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "wfbehaviour
                 SET 
                    wfb_title = '" . Misc::escapeString($HTTP_POST_VARS["wfb_title"]) . "',
                    wfb_version = '" . Misc::escapeString($HTTP_POST_VARS["wfb_version"]) . "',
                    wfb_description = '" . Misc::escapeString($HTTP_POST_VARS["wfb_description"]) . "',
                    wfb_script_name = '" . Misc::escapeString($HTTP_POST_VARS["wfb_script_name"]) . "',
                    wfb_auto = '" . Misc::checkBox(@$HTTP_POST_VARS["wfb_auto"]) . "'
                 WHERE wfb_id = $wfb_id";
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
     * get a list of workflow behaviours
     *
     * @access  public
     * @return  array The list of custom fields
     */
    function getList($wherestr='')
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "wfbehaviour wfb
                    $wherestr
                 ORDER BY
                    wfb.wfb_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } 
        return $res;
    }




    /**
     * get details of a workflow behaviour
     *
     * @access  public
     * @param   integer $wfb_id The workflow behaviour id
     * @return  array The workflow behaviour details
     */
    function getDetails($wfb_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "wfbehaviour
                 WHERE
                    wfb_id=$wfb_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } 
        return $res;
    }


    /**
     * get the list of automatic behaviours
     */
    function getListAuto()
    {
        return WF_Behaviour::getList(" WHERE wfb.wfb_auto='1' ");
    }

    /**
     * get the list of manual behaviours
     */
    function getListManual()
    {
        return WF_Behaviour::getList(" WHERE wfb.wfb_auto='0' ");
    }



}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Doc_Type_XSD Class');
}
?>
