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
 * Class to handle system object status's.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");

class Status
{

    /**
     * Method used to remove a given list of statuss.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "status
                 WHERE
                    sta_id IN (".$items.")";
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
    function insert($params = array())
    {
        global $HTTP_POST_VARS;
        if (empty($params)) {
            $params = $HTTP_POST_VARS;
        }
		
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "status
                 (
                    sta_title,
					sta_order,
					sta_color
                 ) VALUES (
                    '" . Misc::escapeString($params["sta_title"]) . "',
					" . Misc::escapeString($params["sta_order"]) . ",
					'" . Misc::escapeString($params["sta_color"]) . "'					
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
     * @param   integer $sta_id The status ID
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($sta_id)
    {
        global $HTTP_POST_VARS;

        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "status
                 SET 
                    sta_title = '" . Misc::escapeString($HTTP_POST_VARS["sta_title"]) . "',
					sta_order = '" . Misc::escapeString($HTTP_POST_VARS["sta_order"]) . "',
					sta_color = '" . Misc::escapeString($HTTP_POST_VARS["sta_color"]) . "'
                 WHERE sta_id = ".$sta_id;

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
     * @param   integer $sta_id The status ID
     * @return  string The title of the status
     */
    function getTitle($sta_id)
    {
        $stmt = "SELECT
                    sta_title
                 FROM
                    " . APP_TABLE_PREFIX . "status
                 WHERE
                    sta_id=".$sta_id;
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the ID of a specific status.
     *
     * @access  public
     * @param   integer $sta_title The status title
     * @return  string The ID of the status
     */
    function getID($sta_title)
    {
        $stmt = "SELECT
                    sta_id
                 FROM
                    " . APP_TABLE_PREFIX . "status
                 WHERE
                    sta_title='".$sta_title."'";
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
                    sta_id,
					sta_title
                 FROM
                    " . APP_TABLE_PREFIX . "status
                 ORDER BY
                    sta_title ASC";
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
    function getUnpublishedAssocList()
    {
        $stmt = "SELECT
                    sta_id,
					sta_title
                 FROM
                    " . APP_TABLE_PREFIX . "status
				 WHERE sta_title != 'Published' 
                 ORDER BY
                    sta_title ASC";
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
                    " . APP_TABLE_PREFIX . "status
                 ORDER BY
                    sta_order ASC";
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
     * @param   integer $sta_id The status ID
     * @return  array The status details
     */
    function getDetails($sta_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "status
                 WHERE
                    sta_id=".$sta_id;
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
    $GLOBALS['bench']->setMarker('Included Status Class');
}
?>
