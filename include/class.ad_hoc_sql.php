<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007, 2008 The University of Queensland,   |
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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au                      |
// |          Rhys Palmer <r.palmer@library.uq.edu.au                     |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle Ad Hoc SQL Pid array lists
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 */

class Ad_Hoc_SQL
{
    
    function getList()
    {
        $stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "ad_hoc_sql";
        
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } 
        
        if (empty($res)) {
            return array();
        }
        
        return $res;
    }

	function getPIDS($ahs_id) {
		$details = Ad_Hoc_SQL::getDetails($ahs_id);
		$stmt = $details['ahs_query'];
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
			return $res;
		} 
	}


    function getResultSet($ahs_id, $page = 0, $max = 50)
    {
	
		$details = Ad_Hoc_SQL::getDetails($ahs_id);
		$stmtCount = $details['ahs_query_count'];
		
       	$res = $GLOBALS["db_api"]->dbh->getOne($stmtCount);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
			if (is_numeric($res)) {
				$count = $res;				
			} else {
				return false;
			}
		}
		
		
		$stmtShow = $details['ahs_query_show'];
		if (!is_numeric($page) || !is_numeric($max)) {
			$page = 0;
			$max = 50;
		}
		$offset = $page * $max;
		$limit = $max;
	 	$stmtShow .= " LIMIT ".$limit." OFFSET ".$offset;
	       
        $res = $GLOBALS["db_api"]->dbh->getAll($stmtShow, DB_FETCHMODE_ASSOC);
//echo $stmtShow;
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } 
        
        if (empty($res)) {
            return array();
        }
		$start = $page;
		$total_rows = $count;
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
        return array(
            "list" => $res,
            "info" => array(
                "current_page"  => $page,
                "start_offset"  => $start,
                "end_offset"    => $start + ($total_rows_limit),
                "total_rows"    => $total_rows,
                "total_pages"   => $total_pages,
                "prev_page" => ($page == 0) ? "-1" : ($page - 1),
                "next_page"     => ($page == $last_page) ? "-1" : ($page + 1),
                "last_page"     => $last_page
            )
        );



        return $res;
    }

    
    function getDetails($ahs_id)
    {
		if (!is_numeric($ahs_id)) {
			return false;
		}
	
        $stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "ad_hoc_sql
                 WHERE ahs_id = ". $ahs_id;
        
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
    
    function insert()
    {
		if ((is_numeric(stripos($_POST["ahs_query"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query"], "UPDATE")))) {
			return false;
		}
		if ((is_numeric(stripos($_POST["ahs_query_count"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query_count"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query_count"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query_count"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query_count"], "UPDATE")))) {
			return false;
		}
		if ((is_numeric(stripos($_POST["ahs_query_show"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query_show"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query_show"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query_show"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query_show"], "UPDATE")))) {
			return false;
		}
	
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "ad_hoc_sql
                 (
                    ahs_name,
					ahs_query,
					ahs_query_count,
					ahs_query_show
                 ) VALUES (
                    '" . Misc::escapeString($_POST["ahs_name"]) . "',
                    '" . Misc::escapeString($_POST["ahs_query"]) . "',
                    '" . Misc::escapeString($_POST["ahs_query_count"]) . "',
                    '" . Misc::escapeString($_POST["ahs_query_show"]) . "'
                 )";
		
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            
           return 1;
                       
        }
    }


    function getAssocList()
    {

        $stmt = "SELECT
                    ahs_id,
                    ahs_name
                 FROM
                    " . APP_TABLE_PREFIX . "ad_hoc_sql
                 ORDER BY
                    ahs_id ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    
    function update($ahs_id)
    {
	
		if ((is_numeric(stripos($_POST["ahs_query"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query"], "UPDATE")))) {
			return false;
		}
		if ((is_numeric(stripos($_POST["ahs_query_count"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query_count"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query_count"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query_count"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query_count"], "UPDATE")))) {
			return false;
		}
		if ((is_numeric(stripos($_POST["ahs_query_show"], "DROP"))) || (is_numeric(stripos($_POST["ahs_query_show"], "DELETE"))) || (is_numeric(stripos($_POST["ahs_query_show"], "TRUNCATE"))) || (is_numeric(stripos($_POST["ahs_query_show"], "INSERT"))) || (is_numeric(stripos($_POST["ahs_query_show"], "UPDATE")))) {
			return false;
		}



        $stmt = "UPDATE " . APP_TABLE_PREFIX . "ad_hoc_sql
                 SET 
                    ahs_name = '" . Misc::escapeString($_POST["ahs_name"]) . "',
                    ahs_query = '" . Misc::escapeString($_POST["ahs_query"]) . "',
                    ahs_query_count = '" . Misc::escapeString($_POST["ahs_query_count"]) . "',
                    ahs_query_show = '" . Misc::escapeString($_POST["ahs_query_show"]) . "'
				 WHERE ahs_id = ".$ahs_id;

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            
            return 1;
            
        }
    }
    
    function remove()
    {
        $items = @implode("', '", $_POST["items"]);
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "ad_hoc_sql
                 WHERE
                    ahs_id IN ('".$items."')";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }
    
}

?>