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
 * Class to handle system object status's.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");


class Object_Type
{

    /**
     * Method used to remove a given list of statuss.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        $items = @implode(", ", $_POST["items"]);
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "object_type
                 WHERE
                    ret_id IN (".$items.")";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }


    /**
     * Method used to add a new status to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "object_type
                 (
                    ret_title,
					ret_order,
					ret_color
                 ) VALUES (
                    '" . Misc::escapeString($_POST["ret_title"]) . "',
					'" . Misc::escapeString($_POST["ret_order"]) . "',
					'" . Misc::escapeString($_POST["ret_color"]) . "'					
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }

    /**
     * Method used to update details of a status.
     *
     * @access  public
     * @param   integer $ret_id The status ID
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($ret_id)
    {
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "object_type
                 SET 
                    ret_title = '" . Misc::escapeString($_POST["ret_title"]) . "',
					ret_order = '" . Misc::escapeString($_POST["ret_order"]) . "',
					ret_color = '" . Misc::escapeString($_POST["ret_color"]) . "'
                 WHERE ret_id = ".$ret_id;

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }


    /**
     * Method used to get the title of a specific status.
     *
     * @access  public
     * @param   integer $ret_id The status ID
     * @return  string The title of the status
     */
    function getTitle($ret_id)
    {
        $stmt = "SELECT
                    ret_title
                 FROM
                    " . APP_TABLE_PREFIX . "object_type
                 WHERE
                    ret_id=".$ret_id;
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the title of a specific status.
     *
     * @access  public
     * @param   integer $ret_id The status ID
     * @return  string The title of the status
     */
    function getID($ret_title)
    {
        $stmt = "SELECT
                    ret_id
                 FROM
                    " . APP_TABLE_PREFIX . "object_type
                 WHERE
                    ret_title='".$ret_title."'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of statuss available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of statuss in an associative array (for drop down lists).
     */
    function getAssocList()
    {
        $stmt = "SELECT
                    ret_id,
					CONCAT('(',ret_id,') ',ret_title) as ret_title
                 FROM
                    " . APP_TABLE_PREFIX . "object_type
				 WHERE ret_id not in  (0,4)
                 ORDER BY				 
                    ret_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of statuss available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of statuss in an associative array (for drop down lists).
     */
    function getAssocListAll()
    {
        $stmt = "SELECT
                    ret_id,
					CONCAT('(',ret_id,') ',ret_title) as ret_title
                 FROM
                    " . APP_TABLE_PREFIX . "object_type
                 ORDER BY
                    ret_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
    /**
     * Method used to get the list of statuss available in the 
     * system.
     *
     * @access  public
     * @return  array The list of statuss 
     */
    function getList()
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "object_type
                 ORDER BY
                    ret_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
                return $res;
            }
        }
    }

    /**
     * Method used to get the details of a specific status.
     *
     * @access  public
     * @param   integer $ret_id The status ID
     * @return  array The status details
     */
    function getDetails($ret_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "object_type
                 WHERE
                    ret_id=".$ret_id;
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

}

// benchmarking the included file (aka setup time)
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Object Type Class');
}
?>
