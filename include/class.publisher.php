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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// |          Aaron Brown  <a.brown@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+
//
//
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");

class Publisher
{
    /**
     * Method used to check whether a publisher exists or not.
     *
     * @access  public
     * @param   integer $pub_id The publisher ID
     * @return  boolean
     */
    function exists($pub_id)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if(empty($pub_id) || !is_numeric($pub_id)) {
            return false;
        }

        $stmt = "SELECT
                    COUNT(*) AS total
                 FROM
                    " . APP_TABLE_PREFIX . "publisher
                 WHERE
                    pub_id = ".$db->quote($pub_id, 'INTEGER');
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        if ($res > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Method used to get the list of publishers available in the system.
     *
     * @access  public
     * @return  array The list of publishers
     */
    function getList($current_row = 0, $max = 25, $order_by = ' pub_name', $filter="")
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $where_stmt = "";
        if (!empty($filter)) {
            $where_stmt .= " WHERE pub_name LIKE '%" . $filter . "%' ";
        }

        $start = $current_row * $max;

        $stmt = "SELECT
					SQL_CALC_FOUND_ROWS *
                 FROM
                    " . APP_TABLE_PREFIX . "publisher
				" . $where_stmt . "
                 ORDER BY
                 	pub_name ASC
				 LIMIT ".$db->quote($max, 'INTEGER')." OFFSET ".$db->quote($start, 'INTEGER');

        try {
            $res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }

        if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
            $stmt = "SELECT COUNT(*)
	                 FROM
	                    " . APP_TABLE_PREFIX . "publisher
					".$where_stmt;
        } else {
            $stmt = 'SELECT FOUND_ROWS()';
        }

        try {
            $total_rows = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }

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
                "prev_page" 	=> ($current_row == 0) ? "-1" : ($current_row - 1),
                "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                "last_page"     => $last_page
            )
        );
    }



    /**
     * Method used to get the details for a given publisher ID.
     *
     * @access  public
     * @param   integer $pub_id The publisher ID
     * @return  array The publisher details
     */
    function getDetails($pub_id)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if(empty($pub_id) || !is_numeric($pub_id)) {
            return "";
        }

        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "publisher
                 WHERE
                    pub_id = ".$db->quote($pub_id, 'INTEGER');
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
     * Method used to update the details of the publisher.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function update()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if (Validation::isWhitespace($_POST["name"])) {
            return -2;
        }
        $isUser = Auth::getUsername();
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "publisher
                 SET
                    pub_name = " . $db->quote($_POST["name"]) . ",
                    pub_updated_date = " . $db->quote(Date_API::getCurrentDateGMT()) .",
                    pub_details_by = " . $db->quote($isUser) ."
                 WHERE
                    pub_id = " . $db->quote($_POST["id"], 'INTEGER');
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
     * Method used to add a new publisher to the system.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 or -2 otherwise
     */
    function insert()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if (Validation::isWhitespace($_POST["name"])) {
            return -2;
        }
        $isUser = Auth::getUsername();

        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "publisher
                  (
                    pub_name,
					pub_created_date,
					pub_updated_date,
					pub_details_by
				  ) VALUES (
                    " . $db->quote($_POST["name"]) . ",
                    " . $db->quote(Date_API::getCurrentDateGMT()) . ",
                    " . $db->quote(Date_API::getCurrentDateGMT()) . ",
                    " . $db->quote($isUser) . "
                   )";

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
     * Method used to remove a given set of publishers from the system.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "publisher
                 WHERE
                    pub_id IN (" . Misc::arrayToSQLBindStr($_POST["items"]) . ")";
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
     * Get the complete list of publishers.
     */
    function getpublishers()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "
			SELECT
			    pub_id as matching_id,
				pub_name AS title
			FROM
				" . APP_TABLE_PREFIX . "publisher
			ORDER BY
				pub_name ASC;
		";

        try {
            $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }

        return $res;
    }

    /**
     * Method used to find suggested publisher names for the suggestor
     *
     * @access  public
     * @param   string $query The Publisher
     * @return  boolean
     */
    function suggest($term)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT
                    pub_id as id, pub_name, concat(pub_name,' (',  pub_id, ')')  as name
                 FROM
                    " . APP_TABLE_PREFIX . "publisher
                 WHERE
                    pub_name like ".$db->quote("%".$term."%")."
                 OR
                    pub_id like ".$db->quote("%".$term."%")."
                 LIMIT 10";
        try {
            $res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }

        return $res;
    }

    function getDisplayName($pub_id)
    {
      $log = FezLog::get();
      $db = DB_API::get();

      if (empty($pub_id) || !is_numeric($pub_id)) {
        return "";
      }

      $stmt = "SELECT
                      pub_id, pub_name, concat_ws(pub_name,' (',  pub_id, ')')  as pub_name
                   FROM
                      " . APP_TABLE_PREFIX . "publisher
                      WHERE
                      pub_id=".$db->quote($pub_id, 'INTEGER');

      try {
        $res = $db->fetchRow($stmt);
      }
      catch(Exception $ex) {
        $log->err($ex);
        return '';
      }
      return $res;
    }
}
