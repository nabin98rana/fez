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
include_once(APP_INC_PATH . "class.date.php");


class News
{

    /**
     * Method used to add a news entry to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
        global $HTTP_POST_VARS;

        if (Validation::isWhitespace($HTTP_POST_VARS["title"])) {
            return -2;
        }
        if (Validation::isWhitespace($HTTP_POST_VARS["message"])) {
            return -3;
        }
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "news
                 (
                    nws_usr_id,
                    nws_created_date,
                    nws_title,
                    nws_message,";
			if ($HTTP_POST_VARS["status"] == "active") {
				$stmt .= "nws_published_date,";
			}
			$stmt .= "
                    nws_status
                 ) VALUES (
                    " . Auth::getUserID() . ",
                    '" . Date_API::getCurrentDateGMT() . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["title"]) . "',
                    '" . Misc::escapeString($HTTP_POST_VARS["message"]) . "',";
				if ($HTTP_POST_VARS["status"] == "active") {
					$stmt .= "
					'" . Date_API::getCurrentDateGMT() . "',";
				}

					$stmt .= "
                    '" . Misc::escapeString($HTTP_POST_VARS["status"]) . "'
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
     * Method used to remove a news entry from the system.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "news
                 WHERE
                    nws_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }


    /**
     * Method used to update a news entry in the system.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function update()
    {
        global $HTTP_POST_VARS;

		// get existing details for the publish date condition
		$existing_res = News::getDetails($HTTP_POST_VARS["id"]);


        if (Validation::isWhitespace($HTTP_POST_VARS["title"])) {
            return -2;
        }
        if (Validation::isWhitespace($HTTP_POST_VARS["message"])) {
            return -3;
        }
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "news
                 SET
                    nws_title='" . Misc::escapeString($HTTP_POST_VARS["title"]) . "',
                    nws_message='" . Misc::escapeString($HTTP_POST_VARS["message"]) . "',
                    nws_status='" . Misc::escapeString($HTTP_POST_VARS["status"]) . "',
					";
				if (($HTTP_POST_VARS["status"] == "active") && ($existing_res['published_date'] != '0000-00-00 00:00:00')) {
					$stmt .= "
					nws_published_date = '" . Date_API::getCurrentDateGMT() . "',";
				}
					$stmt .= "
                    nws_updated_date='" . Date_API::getCurrentDateGMT() . "'					
                 WHERE
                    nws_id=" . $HTTP_POST_VARS["id"];
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return 1;
        }
    }


    /**
     * Method used to get the details of a news entry for a given news ID.
     *
     * @access  public
     * @param   integer $nws_id The news entry ID
     * @return  array The news entry details
     */
    function getDetails($nws_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "news
                 WHERE
                    nws_id=$nws_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the list of news entries available in the system.
     *
     * @access  public
     * @return  array The list of news entries
     */
    function getList()
    {
        $stmt = "SELECT
					*
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "news,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user					
				 WHERE nws_status = 'active' and usr_id = nws_usr_id
                 ORDER BY
                    nws_created_date DESC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			foreach ($res as $key => $row) {			
			  $res[$key]["nws_created_date"] = Date_API::getFormattedDate($res[$key]["nws_created_date"]);
			  $res[$key]["nws_updated_date"] = Date_API::getFormattedDate($res[$key]["nws_updated_date"]);
			  $res[$key]["nws_published_date"] = Date_API::getFormattedDate($res[$key]["nws_published_date"]);
			}
            return $res;
        }
    }

    /**
     * Method used to get the list of news entries available in the system.
     *
     * @access  public
     * @return  array The list of news entries
     */
    function getListAll()
    {
        $stmt = "SELECT
					*
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "news,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user					
				 WHERE usr_id = nws_usr_id
                 ORDER BY
                    nws_created_date DESC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			foreach ($res as $key => $row) {			
			  $res[$key]["nws_created_date"] = Date_API::getFormattedDate($res[$key]["nws_created_date"]);
			  $res[$key]["nws_updated_date"] = Date_API::getFormattedDate($res[$key]["nws_updated_date"]);
			  $res[$key]["nws_published_date"] = Date_API::getFormattedDate($res[$key]["nws_published_date"]);
			}
            return $res;
        }
    }

}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included News Class');
}
?>