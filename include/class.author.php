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
 * of authors in the system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.status.php");

class Author
{
 

    /**
     * Method used to check whether a author exists or not.
     *
     * @access  public
     * @param   integer $aut_id The author ID
     * @return  boolean
     */
    function exists($aut_id)
    {
        $stmt = "SELECT
                    COUNT(*) AS total
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$aut_id;
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            if ($res > 0) {
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * Method used to get the author ID of the given author title.
     *
     * @access  public
     * @param   string $aut_title The author title
     * @return  integer The author ID
     */
    function getID($aut_title)
    {
        $stmt = "SELECT
                    aut_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_title='".$aut_title."'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
	
    /**
     * Method used to get the author ID of the given author name. Use carefully as if there are more than one match it will only return the first.
     *
     * @access  public
     * @param   string $aut_fname The author first name
     * @param   string $aut_lname The author last name
     * @return  integer The author ID
     */
    function getIDByName($aut_fname, $aut_lname)
    {
//		$aut_fname = str_replace(".", "", $aut_fname);

        $stmt = "SELECT
                    aut_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 WHERE ";
		if (is_numeric(strpos($aut_fname, "."))) {
			$aut_fname = substr($aut_fname, 0, strpos($aut_fname, "."));			
			$stmt .= " aut_fname like '".Misc::escapeString($aut_fname)."%' and aut_lname='".Misc::escapeString($aut_lname)."'";
		} else {
			$stmt .= " aut_fname = '".Misc::escapeString($aut_fname)."' and aut_lname='".Misc::escapeString($aut_lname)."'";						
		}
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the title of a given author ID.
     *
     * @access  public
     * @param   integer $aut_id The author ID
     * @return  string The author title
     */
    function getName($aut_id)
    {
        static $returns;

        if (!empty($returns[$aut_id])) {
            return $returns[$aut_id];
        }

        $stmt = "SELECT
                    aut_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$aut_id;
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
        	if ($GLOBALS['app_cache']) {
                $returns[$aut_id] = $res;
            }
            return $res;
        }
    }


    /**
     * Method used to get the details for a given author ID.
     *
     * @access  public
     * @param   integer $aut_id The author ID
     * @return  array The author details
     */
    function getDetails($aut_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$aut_id;
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            $res["grp_users"] = Group::getUserColList($res["aut_id"]);
            return $res;
        }
    }


    /**
     * Method used to remove a given set of authors from the system.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id IN (".$items.")";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true; 
        }
    }




    /**
     * Method used to update the details of the author information.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function update()
    {
        global $HTTP_POST_VARS;

        if (Validation::isWhitespace($HTTP_POST_VARS["lname"])) {
            return -2;
        }
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 SET
                    aut_title='" . Misc::escapeString($HTTP_POST_VARS["title"]) . "',
                    aut_fname='" . Misc::escapeString($HTTP_POST_VARS["fname"]) . "',
                    aut_mname='" . Misc::escapeString($HTTP_POST_VARS["mname"]) . "',
                    aut_lname='" . Misc::escapeString($HTTP_POST_VARS["lname"]) . "',
                    aut_position='" . Misc::escapeString($HTTP_POST_VARS["position"]) . "',
                    aut_cv_link='" . Misc::escapeString($HTTP_POST_VARS["cv_link"]) . "',																				
                    aut_homepage_link='" . Misc::escapeString($HTTP_POST_VARS["homepage_link"]) . "'														
                 WHERE
                    aut_id=" . $HTTP_POST_VARS["id"];
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return 1;
        }
    }


    /**
     * Method used to add a new author to the system.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 or -2 otherwise
     */
    function insert()
    {
        global $HTTP_POST_VARS;

        if (Validation::isWhitespace($HTTP_POST_VARS["lname"])) {
            return -2;
        }
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 (
                    aut_title,
					aut_fname,
					aut_mname,
					aut_lname,
					aut_position,
					aut_cv_link,
					aut_homepage_link,
                    aut_created_date					
                 ) VALUES (

                    '" . Misc::escapeString($HTTP_POST_VARS["title"]) . "',
					'" . Misc::escapeString($HTTP_POST_VARS["fname"]) . "',					
					'" . Misc::escapeString($HTTP_POST_VARS["mname"]) . "',
					'" . Misc::escapeString($HTTP_POST_VARS["lname"]) . "',
					'" . Misc::escapeString($HTTP_POST_VARS["position"]) . "',
					'" . Misc::escapeString($HTTP_POST_VARS["cv_link"]) . "',					
					'" . Misc::escapeString($HTTP_POST_VARS["homepage_link"]) . "',					
                    '" . Date_API::getCurrentDateGMT() . "'
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
     * Method used to get the list of authors available in the 
     * system.
     *
     * @access  public
     * @return  array The list of authors
     */
    function getList($current_row = 0, $max = 25, $order_by = 'aut_lname', $filter="")
    {

    	$where_stmt = "";
    	$extra_stmt = "";
    	$extra_order_stmt = "";    	    	
    	$filter = Misc::escapeString($filter);
    	if (!empty($filter)) {
	    	$where_stmt .= " WHERE match(aut_fname, aut_lname) AGAINST ('*".$filter."*' IN BOOLEAN MODE) ";
	    	$extra_stmt = " , match(aut_fname, aut_lname) AGAINST ('".$filter."') as Relevance ";
	    	$extra_order_stmt = " Relevance DESC, ";    	    		    	
    	}
    	
		$start = $current_row * $max;
        $stmt = "SELECT SQL_CALC_FOUND_ROWS 
					* ".$extra_stmt."
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
				".$where_stmt."
                 ORDER BY ".$extra_order_stmt."
                    ".$order_by."
				 LIMIT ".$start.", ".$max;
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$total_rows = $GLOBALS["db_api"]->dbh->getOne('SELECT FOUND_ROWS()');        
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if (($start + $max) < $total_rows) {
				$total_rows_limit = $start + $max;
			} else {
			   $total_rows_limit = $total_rows;
			}
			$total_pages = ceil($total_rows / $max);
			$last_page = $total_pages - 1;			
            return array(
                "list" => $res,
                "list_info" => array(
                    "current_page"  => $current_row,
                    "start_offset"  => $start,
                    "end_offset"    => $total_rows_limit,
                    "total_rows"    => $total_rows,
                    "total_pages"   => $total_pages,
                    "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page
                )
            );

        }
    }


    /**
     * Method used to get an associative array of author ID and concatenated title, first name, lastname
     * of all authors available in the system.
     *
     * @access  public
     * @param   integer $aut_id The author ID
     * @return  array The list of authors
     */
    function getAssocList($aut_id)
    {
        static $returns;

        if (!empty($returns[$aut_id])) {
            return $returns[$aut_id];
        }

        $stmt = "SELECT
                    aut_id,
                    concat_ws(', ',   aut_lname, aut_mname, aut_fname, aut_id) as aut_fullname
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_lname";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
        	if ($GLOBALS['app_cache']) {
                $returns[$aut_id] = $res;
            }
            return $res;
        }
    }



    /**
     * Method used to get an associative array of author ID and title
     * of all authors available in the system.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  array The list of authors
     */
    function getAssocListAll()
    {

        $stmt = "SELECT
                    aut_id,
                    concat_ws(', ',   aut_lname, aut_fname, aut_id) as aut_fullname
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_fullname";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
 
    /**
     * Method used to get an associative array of author ID and title
     * of all authors available in the system.
     *
     * @access  public
     * @return  array The list of authors
     */
    function getAssocListAllBasic()
    {

        $stmt = "SELECT
                    aut_id,
                    concat_ws(' ',   aut_fname, aut_lname) as aut_fullname
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_fullname";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
    /**
     * Method used to search and suggest all the authors names for a given string.
     *
     * @access  public
     * @return  array List of authors
     */
	function suggest($term) {
		$dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
		$term = Misc::escapeString($term);
		$stmt = "
			  SELECT aut_id, aut_fullname FROM
			 (SELECT
				distinct aut_id,
				concat_ws(' ', aut_fname, aut_mname, aut_lname) as aut_fullname, 
				MATCH(aut_fname, aut_lname) AGAINST ('".$term."') as Relevance FROM ".$dbtp."author
			 WHERE MATCH (aut_fname, aut_lname) AGAINST ('*".$term."*' IN BOOLEAN MODE)
			 ORDER BY Relevance DESC, aut_fullname, aut_id LIMIT 0,20) as temp";
	    $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
	} 

    /**
     * Method used to get an associative array of author ID and title
     * of all authors that exist in the system that are active.
     *
     * @access  public
     * @return  array List of authors
     */
    function getAll()
    {
        $stmt = "SELECT
                    aut_id,
                    concat_ws(' ', aut_title, aut_fname, aut_mname, aut_lname) as aut_fullname
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_title";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    function getFullname($aut_id) 
    {
        $stmt = "SELECT
                    concat_ws(' ', aut_title, aut_fname, aut_mname, aut_lname) as aut_fullname
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                    WHERE
                    aut_id='".$aut_id."'
                 ORDER BY
                    aut_title";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }

    }

    function getOrgStaffId($aut_id) 
    {
        $stmt = "SELECT
                    aut_org_staff_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                    WHERE
                    aut_id='".$aut_id."'
                 ORDER BY
                    aut_title";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }

    }


    /**
     * Method used to get an associative array of author ID and title
     * of all authors that exist in the system that are active.
     *
     * @access  public
     * @return  array List of authors
     */
    function getActiveAssocList()
    {
        $stmt = "SELECT
                    aut_id,
                    concat_ws(' ', aut_title, aut_fname, aut_mname, aut_lname) as aut_fullname
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_title";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Group Class');
}
?>
