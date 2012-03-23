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
include_once(APP_INC_PATH . "class.org_structure.php");
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
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($aut_id) || !is_numeric($aut_id)) {
      return false;
    }

    $stmt = "SELECT
                    COUNT(*) AS total
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER');
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
   * Method used to get the author ID of the given author title.
   *
   * @access  public
   * @param   string $aut_title The author title
   * @return  integer The author ID
   */
  function getID($aut_title)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    aut_id
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_title=".$db->quote($aut_title);
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }
  
  /**
   * Method used to get the author ID of the given ResearcherID.
   *
   * @access  public
   * @param   string $rid The ResearcherID
   * @return  integer The author ID
   */
  public static function getIDByResearcherID($rid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    aut_id
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_researcher_id=".$db->quote($rid);
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  /**
   * Method used to get the author ID of the given author name. Use carefully as if there are more than one 
   * match it will only return the first.
   *
   * @access  public
   * @param   string $aut_fname The author first name
   * @param   string $aut_lname The author last name
   * @return  integer The author ID
   */
  function getIDByName($aut_fname, $aut_lname)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    aut_id
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE ";
    if (is_numeric(strpos($aut_fname, "."))) {
      $aut_fname = substr($aut_fname, 0, strpos($aut_fname, "."));
      $stmt .= " aut_fname like ".$db->quote($aut_fname. '%')." and aut_lname=".$db->quote($aut_lname);
    } else {
      $stmt .= " aut_fname = ".$db->quote($aut_fname)." and aut_lname=".$db->quote($aut_lname);
    }
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }

  /**
   * Method used to get the author ID of the given author username. Use carefully as if there are more than 
   * one match it will only return the first.
   *
   * @access  public
   * @param   string $username The author username
   * @return  integer The author ID
   */
  function getIDByUsername($username, $exclude = '')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                aut_id
             FROM
                " . APP_TABLE_PREFIX . "author
             WHERE
                aut_org_username = ".$db->quote($username) . " ";
    if ($exclude != '') {
      $stmt .= "AND aut_id != " . $db->quote($exclude) . "";
    }
      
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }
  
  /**
   * Method used to get the author ID of the given author org staff ID.
   *
   * @access  public
   * @param   string $orgStaffID The author org staff ID
   * @return  integer The author ID
   */
  function getIDByOrgStaffID($orgStaffID, $exclude = '')
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "
              SELECT
                aut_id
              FROM
                " . APP_TABLE_PREFIX . "author
              WHERE 
                aut_org_staff_id = " . $db->quote($orgStaffID) . "
            ";
    if ($exclude != '') {
      $stmt .= "AND aut_id != " . $db->quote($exclude) . "";
    }
    
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
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
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($aut_id) || !is_numeric($aut_id)) {
      return "";
    }

    static $returns;

    if (!empty($returns[$aut_id])) {
      return $returns[$aut_id];
    }

    $stmt = "SELECT
                    aut_title
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER');
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    if ($GLOBALS['app_cache']) {
      // make sure the static memory var doesnt grow too large and cause a fatal out of memory error
      if (!is_array($returns) || count($returns) > 10) {
        $returns = array();
      }
      $returns[$aut_id] = $res;
    }
    return $res;
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
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($aut_id) || !is_numeric($aut_id)) {
      return "";
    }

    $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER');
    try {
      $res = $db->fetchRow($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }

    $res["grp_users"] = Group::getUserColList($res["aut_id"]);
    return $res;
  }
  
  function getDetailsByUsername($aut_org_username)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($aut_org_username)) {
       return "";   
    }
    
    $stmt = "SELECT
                *
             FROM
                " . APP_TABLE_PREFIX . "author
             WHERE
                aut_org_username=".$db->quote($aut_org_username)."
             OR aut_mypub_url=".$db->quote($aut_org_username);
    try {
      $res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    if (is_numeric($res["aut_id"])) {
      $res["grp_users"] = Group::getUserColList($res["aut_id"]);
    }
    return $res;
  }

  /**
   * Method used to remove a given set of authors from the system.
   *
   * @access  public
   * @return  boolean
   */
  function remove()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id IN (".Misc::arrayToSQLBindStr($_POST["items"]).")";
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
   * Method used to update the details of the author information.
   *
   * @access  public
   * @return  integer 1 if the update worked, -1 otherwise
   */
  function update()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (Validation::isWhitespace($_POST["lname"])) {
      return -2;
    }
    
    if (trim($_POST["org_staff_id"] !== "")) {
      if (author::getIDByOrgStaffID(trim($_POST["org_staff_id"]), $_POST["id"])) {
        return -3;
      }
    }
    
    if (trim($_POST["org_username"] !== "")) {
      if (author::getIDByUsername(trim($_POST["org_username"]), $_POST["id"])) {
        return -4;
      }
    }
    
    $rid = "";
    // RIDs are always 11 chars
    if (strlen(trim($_POST["researcher_id"])) == 11 || strlen(trim($_POST["researcher_id"])) == 0) {
      $rid = " aut_researcher_id=  ". $db->quote(trim($_POST["researcher_id"])) . ",";
    }
    
	//strip html tags from $_POST["description"] except for <b><i> etc
	$tags = '<b><i><sup><sub><em><strong><u><br>';
	$stripped_description = strip_tags($_POST["description"], $tags);	
    
    $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "author
                 SET
                    aut_title=" . $db->quote(trim($_POST["title"])) . ", ".$rid."
                    aut_fname=" . $db->quote(trim($_POST["fname"])) . ",
                    aut_mname=" . $db->quote(trim($_POST["mname"])) . ",
                    aut_lname=" . $db->quote(trim($_POST["lname"])) . ",
                    aut_display_name=" . $db->quote(trim($_POST["dname"])) . ",
                    aut_position=" . $db->quote(trim($_POST["position"])) . ",
					aut_email=" . $db->quote(trim($_POST["email"])) . ",
                    aut_cv_link=" . $db->quote(trim($_POST["cv_link"])) . ",
                    aut_homepage_link=" . $db->quote(trim($_POST["homepage_link"])) . ",
                    aut_ref_num=" . $db->quote(trim($_POST["aut_ref_num"])) . ",
                    aut_scopus_id=" . $db->quote(trim($_POST["scopus_id"])).",
					aut_people_australia_id=" . $db->quote(trim($_POST["people_australia_id"])).",
                    aut_mypub_url=" . $db->quote(trim($_POST["mypub_url"])).",
					aut_description=" . $db->quote($stripped_description) . ",						
                    aut_update_date=" . $db->quote(Date_API::getCurrentDateGMT());
    if (trim($_POST["org_staff_id"] !== "")) {
      $stmt .= ",aut_org_staff_id=" . $db->quote(trim($_POST["org_staff_id"])) . " ";
    } else {
      $stmt .= ",aut_org_staff_id=null ";
    }
    if (trim($_POST["org_username"] !== "")) {
      $stmt .= ",aut_org_username=" . $db->quote(trim($_POST["org_username"])) . " ";
    } else {
      $stmt .= ",aut_org_username=null ";
    }
    $stmt .= "WHERE
                    aut_id=" . $db->quote($_POST["id"], 'INTEGER');
    try {
      $db->exec($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return -1;
    }
    return 1;
  }
  
  
  function updateMyPubURL($username, $mypub_url)
  { 
    $log = FezLog::get();
    $db = DB_API::get();
  
    if (Validation::isWhitespace($mypub_url)) {
      return -1;
    }
    if (Validation::isUserFileName($mypub_url) == true) {
      return -2;
    }
    $stmt = "UPDATE
                " . APP_TABLE_PREFIX . "author
             SET
                aut_mypub_url=? ";
    $stmt .= "WHERE
                aut_org_username=?";
        
    try {
      $db->query($stmt, array($mypub_url, $username));
    }
    catch(Exception $ex) {
      $log->err($ex);
      return -1;
    }
    return 1;
  }  


  /**
   * Method used to add a new author to the system.
   *
   * @access  public
   * @return  integer 1 if the update worked, -1 or -2 otherwise
   */
  function insert()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (Validation::isWhitespace(trim($_POST["lname"]))) {
      return -2;
    }
    if (trim($_POST["org_staff_id"] !== "")) {
      if (author::getIDByOrgStaffID(trim($_POST["org_staff_id"]))) {
        return -3;
      }
    }
    
    if (trim($_POST["org_username"] !== "")) {
      if (author::getIDByUsername(trim($_POST["org_username"]))) {
        return -4;
      }
    }
    
    $insert = "INSERT INTO
                    " . APP_TABLE_PREFIX . "author
                 (
                    aut_title,
          aut_fname,
          aut_lname,
                    aut_created_date,
                    aut_display_name";

    if (trim($_POST["org_staff_id"] !== "")) {
      $insert .= ", aut_org_staff_id ";
    }
    if (trim($_POST["org_username"] !== "")) {
      $insert .= ", aut_org_username ";
    }
    if ($_POST["mname"] !== "") {
      $insert .= ", aut_mname ";
    }
    if ($_POST["position"] !== "") {
      $insert .= ", aut_position ";
    }
	if ($_POST["email"] !== "") {
      $insert .= ", aut_email ";
    }	
    if ($_POST["cv_link"] !== "") {
      $insert .= ", aut_cv_link ";
    }
    if ($_POST["homepage_link"] !== "") {
      $insert .= ", aut_homepage_link "; 
    }
    if ($_POST["aut_ref_num"] !== "") {
      $insert .= ", aut_ref_num ";
    }
    if ($_POST["researcher_id"] !== "") {
      $insert .= ", aut_researcher_id ";
    }
    if ($_POST["scopus_id"] !== "") {
      $insert .= ", aut_scopus_id ";
    }
    if ($_POST["people_australia_id"] !== "") {
      $insert .= ", aut_people_australia_id ";
    }	
    if ($_POST["mypub_url"] !== "") {
      $insert .= ", aut_mypub_url ";
    }
	if ($_POST["description"] !== "") {
      $insert .= ", aut_description ";
    }	

    $values = ") VALUES (
                    " . $db->quote(trim($_POST["title"])) . ",
          " . $db->quote(trim($_POST["fname"])) . ",
          " . $db->quote(trim($_POST["lname"])) . ",
                    " . $db->quote(Date_API::getCurrentDateGMT()) . "
                  ";

    if ($_POST["dname"] !== "") {
      $values .= ", " . $db->quote(trim($_POST["dname"]));
    } else {
      $values .= ", " . $db->quote(trim($_POST["fname"]) . ' ' . trim($_POST["lname"]));
    }

    if (trim($_POST["org_staff_id"] !== "")) {
      $values .= ", " . $db->quote(trim($_POST["org_staff_id"]));
    }
    if (trim($_POST["org_username"] !== "")) {
      $values .= ", " . $db->quote(trim($_POST["org_username"]));
    }
    if ($_POST["mname"] !== "") {
      $values .= ", " . $db->quote(trim($_POST["mname"]));
    }
    if ($_POST["position"] !== "") {
      $values .= ", " . $db->quote(trim($_POST["position"]));
    }
    if ($_POST["email"] !== "") {
      $values .= ", " . $db->quote(trim($_POST["email"]));
    }	
    if ($_POST["cv_link"] !== "") {
      $values .= ", " . $db->quote(trim($_POST["cv_link"]));
    }
    if ($_POST["homepage_link"] !== "") {
      $values .= ", " . $db->quote(trim($_POST["homepage_link"]));
    }
    if ($_POST["aut_ref_num"] !== "") {
      $values .= ", " . $db->quote(trim($_POST["aut_ref_num"]));
    }
    if ($_POST["researcher_id"] !== "") {
      $values .= ", " . $db->quote(trim($_POST["researcher_id"]));
    }
    if ($_POST["scopus_id"] !== "") {
      $values .= ", " . $db->quote(trim($_POST["scopus_id"]));
    }
    if ($_POST["people_australia_id"] !== "") {
      $values .= ", " . $db->quote(trim($_POST["people_australia_id"]));
    }	
    if ($_POST["mypub_url"] !== "") {
        $values .= ", " . $db->quote(trim($_POST["mypub_url"]));
    }
    if ($_POST["description"] !== "") {
	  $stripped_description = strip_tags(trim($_POST["description"]), $tags); //strip HTML tags
      $values .= ", " . $db->quote($stripped_description);
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


  /**
   * Method used to get the list of authors available in the
   * system.
   *
   * @access  public
   * @return  array The list of authors
   */
  function getList($current_row = 0, $max = 25, $order_by = 'aut_lname', $filter="", $staff_id = "")
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $where_stmt = "";
    $extra_stmt = "";
    $extra_order_stmt = "";
    if (!empty($filter)) {
      // For the Author table we are going to keep it in MyISAM if you are using MySQL because there is no 
      // table locking issue with this table like with others.
      if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
        $where_stmt .= " WHERE ";
        $names = explode(" ", $filter);
        $nameCounter = 0;
        foreach ($names as $name) {
          $nameCounter++;
          if ($nameCounter > 1) {
            $where_stmt .= " AND ";
          }
			    if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { 
						$where_stmt .= " (aut_fname ILIKE ".$db->quote('%'.$name.'%')." OR aut_lname ILIKE ".$db->quote('%'.$name.'%')." OR aut_org_username ILIKE ".$db->quote($name.'%').") ";
					} else {
          	$where_stmt .= " (aut_fname LIKE ".$db->quote($name.'%')." OR aut_lname LIKE ".$db->quote($name.'%')." OR aut_org_username LIKE ".$db->quote($name.'%').") ";
					}
        }
      } else {
        $where_stmt .= " WHERE MATCH(aut_fname, aut_lname) AGAINST (".$db->quote('*'.$filter.'*')." IN BOOLEAN MODE) OR aut_org_username LIKE ".$db->quote($filter.'%')." ";
        $extra_stmt = " , MATCH(aut_fname, aut_lname) AGAINST (".$db->quote($filter).") as Relevance ";
        $extra_order_stmt = " Relevance DESC, ";
      }
    } else if (!empty($staff_id)) {
      $where_stmt .= " WHERE aut_org_staff_id = ".$db->quote($staff_id);
    }
      
    $start = $current_row * $max;
    if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
      $stmt = "SELECT ";
    } else {
      $stmt = "SELECT SQL_CALC_FOUND_ROWS ";
    }
    $stmt .= "
          * ".$extra_stmt."
                 FROM
                    " . APP_TABLE_PREFIX . "author
        ".$where_stmt."
                 ORDER BY ".$extra_order_stmt."
                    ".$order_by."
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
                      " . APP_TABLE_PREFIX . "author
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
                    "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page
    )
    );

  }

  function getPositionsByOrgStaffID($org_staff_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    pos_title, org_title, YEAR(DATE(dt_from)) AS dt_from, YEAR(DATE(dt_to)) AS dt_to
          FROM " . APP_TABLE_PREFIX . "author
          LEFT JOIN hr_position_vw on wamikey = aut_org_staff_id
          LEFT JOIN " . APP_TABLE_PREFIX . "org_structure on aou = org_extdb_id AND org_extdb_name = 'hr'
          WHERE aut_org_staff_id != '' AND aut_org_staff_id = ".$db->quote($org_staff_id, 'INTEGER');
    try {
      $res = $db->fetchAll($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }

    return $res;
  }
  
  function getPositionsByOrgUsername($org_username)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    pos_title, org_title, YEAR(DATE(dt_from)) AS dt_from, YEAR(DATE(dt_to)) AS dt_to
          FROM " . APP_TABLE_PREFIX . "author
          LEFT JOIN hr_position_vw on user_name = aut_org_username
          LEFT JOIN " . APP_TABLE_PREFIX . "org_structure on aou = org_extdb_id AND org_extdb_name = 'hr'
          WHERE aut_org_username != '' AND aut_org_username = ".$db->quote($org_username);

    try {
      $res = $db->fetchAll($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }

    return $res;
  }
  
  function getPositions($org_username)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    pos_title, org_title, YEAR(DATE(dt_from)) AS dt_from, YEAR(DATE(dt_to)) AS dt_to
          FROM " . APP_TABLE_PREFIX . "author
          LEFT JOIN hr_position_vw on user_name = aut_org_username
          LEFT JOIN " . APP_TABLE_PREFIX . "org_structure on aou = org_extdb_id AND org_extdb_name = 'hr'
          WHERE aut_org_username = ".$db->quote($org_username);

    try {
      $res = $db->fetchAll($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }

    return $res;
  }


  /**
   * Method used to get the list of authors available in the
   * system.
   *
   * @access  public
   * @return  array The list of authors
   */
  function getListByStaffIDList($current_row = 0, $max = 25, $order_by = 'aut_lname', $staff_ids = array())
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (!is_array($staff_ids)) {
      return false;
    }

    if (count($staff_ids) == 0) {
      return false;
    }

    $where_stmt = "";
    $extra_stmt = "";
    $bind_params = array();

    if (!empty($staff_ids)) {
      $where_stmt .= " WHERE aut_org_staff_id IN  (".Misc::arrayToSQLBindStr($staff_ids) . ")";
      $bind_params = array_merge($bind_params, $staff_ids);
    }

    $start = $current_row * $max;
    $stmt = "SELECT SQL_CALC_FOUND_ROWS
                 FROM
                    " . APP_TABLE_PREFIX . "author
        ".$where_stmt."
                 ORDER BY ".$db->quote($order_by)."
         LIMIT ".$db->quote($max, 'INTEGER')." OFFSET ".$db->quote($start, 'INTEGER');

    try {
      $res = $db->fetchAll($stmt, $bind_params);
      $total_rows = $db->fetchOne('SELECT FOUND_ROWS()');
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    foreach ($res as $key => $row) {
      $res[$key]['positions'] = array();
      $res[$key]['positions'] = Author::getPositionsByOrgStaffID($res[$key]['aut_org_staff_id']);
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
                    "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page
    )
    );
  }
  
  
  /**
   * Method used to get the list of authors matching the specified
   * ResearcherID.
   *
   * @access  public
   * @param integer $current_row The row to start from
   * @param integer $max The max number of rows to return
   * @param string $order_by The column to sort results on
   * @param array $researcher_ids The ResearcherIDs to search for
   * @return  array The list of matching authors
   */
  function getListByResearcherIDs($current_row = 0, $max = 25, $order_by = 'aut_lname', $researcher_ids = array())
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    if (!is_array($researcher_ids)) {
      return false;
    }
    
    if (count($researcher_ids) == 0) {
      return false;
    }
  
    $where_stmt = "";
    $extra_stmt = "";
    $extra_order_stmt = "";
    $bind_params = array();
    if (!empty($researcher_ids)) {
      $where_stmt .= " WHERE aut_researcher_id in  (".Misc::arrayToSQLBindStr($researcher_ids).")";
      $bind_params = $researcher_ids;
    }
    
    $start = $current_row * $max;
    $stmt = "SELECT SQL_CALC_FOUND_ROWS * ".$extra_stmt."
             FROM " . APP_TABLE_PREFIX . "author
             ".$where_stmt."
             ORDER BY ".$extra_order_stmt."
             ".$order_by."
             LIMIT ".$start.", ".$max;
        
    try {
      $res = $db->fetchAll($stmt, $bind_params);
      $total_rows = $db->fetchOne('SELECT FOUND_ROWS()');
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    
    foreach ($res as $key => $row) {
      $res[$key]['positions'] = array();
      $res[$key]['positions'] = Author::getPositionsByOrgStaffID($res[$key]['aut_org_staff_id']);
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
            "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
            "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
            "last_page"     => $last_page
        )
    );
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
    $log = FezLog::get();
    $db = DB_API::get();

    static $returns;

    if (!empty($returns[$aut_id])) {
      return $returns[$aut_id];
    }

    $stmt = "SELECT
                    aut_id,
                    concat_ws(', ',   aut_lname, aut_mname, aut_fname, aut_id) as aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_lname";

    try {
      $res = $db->fetchPairs($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }

    if ($GLOBALS['app_cache']) {
      // make sure the static memory var doesnt grow too large and cause a fatal out of memory error
      if (!is_array($returns) || count($returns) > 10) { 
        $returns = array();
      }
      $returns[$aut_id] = $res;
    }
    return $res;
  }


  /**
   * Method used to get an associative array of author ID and ResearcherID
   * of all authors with a ResearcherID
   *
   * @access  public
   * @return  array The list of authors
   */
  function getAllWithResearcherId()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    aut_id,
                    aut_researcher_id
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_researcher_id IS NOT NULL 
                      AND aut_researcher_id != '' 
                      AND aut_researcher_id NOT LIKE 'ERR:%'
                      AND aut_researcher_id != '-1';";
    try {
      $res = $db->fetchPairs($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    
    return $res;
  }

    /**
   * Method used to get an associative array of author ID and ResearcherID
   * of all authors with a ResearcherID who are current staff
   *
   * @access  public
   * @return  array The list of authors
   */
  function getAllCurrentStaffWithResearcherId()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    aut_id,
                    aut_researcher_id
                 FROM
                    fez_author
                 INNER JOIN hr_position_vw ON USER_NAME = aut_org_username
                 WHERE
                    (DT_TO >= NOW() OR DT_TO = '0000-00-00')
                    AND aut_researcher_id IS NOT NULL
                    AND aut_researcher_id != ''
                    AND aut_researcher_id NOT LIKE 'ERR:%'
                    AND aut_researcher_id != '-1'
                    GROUP BY aut_id, aut_researcher_id";
    try {
      $res = $db->fetchPairs($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }

    return $res;
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
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    aut_id, ";
		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt .= "concat_ws(TEXT(', '),   TEXT(aut_lname), TEXT(aut_fname), TEXT(aut_id)) as aut_fullname";
		} else {
			$stmt .= "concat_ws(', ',   aut_lname, aut_fname, aut_id) as aut_fullname";
		}
	  $stmt .= "
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_fullname";
    try {
      $res = $db->fetchPairs($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    
    $log->debug('Exiting getAssocListAll');
    return $res;
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
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    aut_id,
                    concat_ws(' ',   aut_fname, aut_lname) AS aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_fullname";
    try {
      $res = $db->fetchPairs($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }

    return $res;
  }
  
  
  /**
   * Method used to get the list of authors available in the 
   * system.
   *
   * @access  public
   * @return  array The list of authors
   */
  function getListByAutIDList($current_row = 0, $max = 25, $order_by = 'aut_lname', $aut_ids = array())
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    if ((!is_array($aut_ids)) || count($aut_ids) == 0) {
      return false;
    }
            
    $start = $current_row * $max;
    $stmt = "SELECT SQL_CALC_FOUND_ROWS * 
             FROM " . APP_TABLE_PREFIX . "author
             WHERE aut_id in  (".Misc::arrayToSQLBindStr($aut_ids).")
             ORDER BY ".$order_by."
             LIMIT ".$start.", ".$max;
        
    try {
      $res = $db->fetchAll($stmt, $aut_ids);
    }
    catch(Exception $ex) {
      
      $log->err($ex);
      return '';
    }
    
    try {
      $total_rows = $db->fetchOne('SELECT FOUND_ROWS()');
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
  
    foreach ($res as $key => $row) {
      $res[$key]['positions'] = array();
      if ($res[$key]['aut_org_staff_id'] != '') {
        $res[$key]['positions'] = Author::getPositionsByOrgStaffID($res[$key]['aut_org_staff_id']);
      } else if ($res[$key]['aut_org_username'] != '') {
        $res[$key]['positions'] = Author::getPositionsByOrgUsername($res[$key]['aut_org_username']);
      } else {
        $log->err('No position found');
        return '';
      }
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
                    "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page
                )
            );        
  }
    
    
    
  /**
   * Method used to search and suggest all the authors names for a given string.
   *
   * @access  public
   * @return  array List of authors
   */
  function suggest($term, $assoc = false)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp = APP_TABLE_PREFIX;

    // some function like concat_ws might not be supportd in all databases, however postgresql does 
    // have a mysql_compat plugin library that adds these..
    // Could be done in the code later if it is a problem
    $stmt = "SELECT aut_id as id, concat_ws(' - ', aut_org_username, aut_org_staff_id)  as username, 
             aut_fullname as name  FROM (
                SELECT aut_id, aut_org_username, aut_org_staff_id, aut_display_name as aut_fullname";

    // For the Author table we are going to keep it in MyISAM if you are using MySQL because there is no 
    // table locking issue with this table like with others.
    // TODO: For postgres it might be worth adding a condition here to use TSEARCH2 which is close to fulltext 
    // indexing in MySQL MyISAM
    
    if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
      $stmt .= " , MATCH(aut_display_name) AGAINST (".$db->quote($term).") as Relevance ";
    }
    $stmt .= " FROM ".$dbtp."author";
    
    if (is_numeric($term)) {
      $stmt .= " WHERE (aut_id=".$db->quote($term, 'INTEGER');
    } else if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
      $stmt .= " WHERE (MATCH (aut_display_name) AGAINST (".$db->quote('*'.$term.'*')." IN BOOLEAN MODE)
                 OR MATCH (aut_org_username) AGAINST (".$db->quote($term)." IN BOOLEAN MODE)";
    } else {
      $stmt .= " WHERE (";
      $names = explode(" ", $term);
      $nameCounter = 0;
      foreach ($names as $name) {
        $nameCounter++;
        if ($nameCounter > 1) {
          $stmt .= " AND ";
        }
        if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) {
          $stmt .= " (aut_fname ILIKE ".$db->quote('%'.$name.'%')." 
                      OR aut_lname ILIKE ".$db->quote('%'.$name.'%')."
                      OR aut_org_username = ".$db->quote($name).") ";
        } else {
          $stmt .= " (aut_fname LIKE ".$db->quote($name.'%')."
                     OR aut_lname LIKE ".$db->quote($name.'%')."
                     OR aut_org_username = ".$db->quote($name).") ";
        }
      }
    }

    $stmt .= " ) ";

    if (APP_AUTHOR_SUGGEST_MODE == 2) {
      $stmt .= " AND ((aut_org_username IS NOT NULL AND aut_org_username != '') OR (aut_org_staff_id IS NOT NULL AND aut_org_staff_id != ''))";
    }
    
    if (is_numeric($term)) {
      $stmt .= " LIMIT 60 OFFSET 0) as tempsuggest";
    } else if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
      $stmt .= " ORDER BY Relevance DESC, aut_fullname LIMIT 0,60) as tempsuggest";
    } else {
      $stmt .= " LIMIT 60 OFFSET 0) as tempsuggest";
    }

    try {
      if ($assoc) {
        $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
      } else {
        $res = $db->fetchAssoc($stmt);
      }
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }

    return $res;
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
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    aut_id,
                    aut_display_name as aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_title";
    try {
      $res = $db->fetchPairs($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }

  function getFullname($aut_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($aut_id) || !is_numeric($aut_id)) {
      return "";
    }
      
    $stmt = "SELECT
                    aut_display_name as aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER')."
                 ORDER BY
                    aut_title";
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }

  function getDisplayName($aut_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($aut_id) || !is_numeric($aut_id)) {
      return "";
    }
      
    $stmt = "SELECT
                    aut_display_name
                 FROM
                    " . APP_TABLE_PREFIX . "author
                    WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER')."
                 ORDER BY
                    aut_title";
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }

    return $res;
  }
  
  function getLastname($aut_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($aut_id) || !is_numeric($aut_id)) {
      return "";
    }
      
    $stmt = "SELECT
                    aut_lname
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER')."
                 ORDER BY
                    aut_title";
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }
  
  function getFirstname($aut_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    if (empty($aut_id) || !is_numeric($aut_id)) {
      return '';
    }
            
    $stmt = "SELECT
                aut_fname
             FROM
                " . APP_TABLE_PREFIX . "author
             WHERE
                aut_id=".$db->quote($aut_id, 'INTEGER')."
             ORDER BY
                aut_title";
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }

  function getDisplayNameUserName($aut_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($aut_id) || !is_numeric($aut_id)) {
      return "";
    }
      
    $stmt = "SELECT
                    aut_id, aut_display_name, concat_ws(' - ', aut_org_username, aut_org_staff_id)  as aut_org_username
                 FROM
                    " . APP_TABLE_PREFIX . "author
                    WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER');

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
   * Gets the first author's surname for a particular pid
   *
   * @return string
   **/
  public function getFirstAuthorInDocument($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $prefix = APP_TABLE_PREFIX;

    $q = "SELECT rek_author FROM {$prefix}record_search_key_author WHERE rek_author_pid = ? AND rek_author_order = 1";
    
    try {
      $res = $db->fetchOne($q, $pid);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
	// grab just the surname
	$regex = '/([^,.]+).*/i';
	$res = preg_replace($regex, '$1', $res);
    return $res;
  }
  
  /**
   * Gets the first author's full name for a particular pid (returns the name as a lowercase string without punctuation)
   *
   * @param string $pid
   * @return string
   **/
  public function getFirstAuthorInFez($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $prefix = APP_TABLE_PREFIX;

    $q = "SELECT aut_lname, aut_fname FROM {$prefix}record_search_key_author_id JOIN {$prefix}author ON ".
         "aut_id = rek_author_id ".
         "WHERE rek_author_id_pid = ? AND rek_author_id_order = 1";
    
    try {
      $res = $db->fetchRow($q, $pid);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }

    if ($res['aut_lname']) {
      return "{$res['aut_lname']}, {$res['aut_fname']}";
    } else {
      return '';
    }
  }

  function getOrgStaffId($aut_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($aut_id) || !is_numeric($aut_id)) {
      return "";
    }
      
    $stmt = "SELECT
                    aut_org_staff_id
                 FROM
                    " . APP_TABLE_PREFIX . "author
                    WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER')."
                 ORDER BY
                    aut_title";
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }
  
  
  function getOrgUsername($aut_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (empty($aut_id) || !is_numeric($aut_id)) {
      return "";
    }
      
    $stmt = "SELECT
                    aut_org_username
                 FROM
                    " . APP_TABLE_PREFIX . "author
                    WHERE
                    aut_id=".$db->quote($aut_id, 'INTEGER')."
                 ORDER BY
                    aut_title";
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }

  function getIDsByOrgStaffIds($org_staff_ids=array())
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    aut_id
                 FROM
                    " . APP_TABLE_PREFIX . "author
                    WHERE
                    aut_org_staff_id IN (".Misc::arrayToSQLBindStr($org_staff_ids).")";
    try {
      $res = $db->fetchCol($stmt, $org_staff_ids);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
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
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    aut_id, ";
		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt .= " concat_ws(TEXT(', '), TEXT(aut_lname), TEXT(aut_mname), concat_ws(TEXT(', '), TEXT(aut_fname), TEXT(aut_id))) as aut_fullname ";			
		} else {
			$stmt .= " concat_ws(', ',   aut_lname, aut_mname, aut_fname, aut_id) as aut_fullname ";
		}
		$stmt .= "
                 FROM
                    " . APP_TABLE_PREFIX . "author
                 ORDER BY
                    aut_title";
    try {
      $res = $db->fetchPairs($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }
  
  
  /**
   * Gets a list of alternative names this author used
   *
   * @param integer $authorId
   * @return array if results found, or an empty string if no results found
   */
  function getAlternativeNamesList($authorId)
  {
    $log = FezLog::get();
    $db = DB_API::get();
  
    $query = "SELECT rek_author, count(*) as paper_count FROM " . APP_TABLE_PREFIX . "record_search_key_author aut ";
    $query .= "JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id autid ";
    $query .= "ON (rek_author_pid = rek_author_id_pid AND aut.rek_author_order = autid.rek_author_id_order) ";
    $query .= "WHERE autid.rek_author_id = ".$db->quote($authorId, 'INTEGER');
    $query .= " GROUP BY rek_author ";
    $query .= "ORDER BY 2 desc ";

    try {
      $res = $db->fetchPairs($query);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }

  function getAlternativeNames($authorId)
  {
    $log = FezLog::get();
    $db = DB_API::get();
  
    $query = "SELECT rek_author FROM " . APP_TABLE_PREFIX . "record_search_key_author aut ";
    $query .= "JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id autid ";
    $query .= "ON (rek_author_pid = rek_author_id_pid AND aut.rek_author_order = autid.rek_author_id_order) ";
    $query .= "WHERE autid.rek_author_id = ".$db->quote($authorId, 'INTEGER');
    $query .= " GROUP BY rek_author ";

    try {
      $res = $db->fetchCol($query);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return '';
    }
    return $res;
  }


  /**
   * Method used to set the ResearcherID for an author.
   *
   * @access  public
   * @param array $profile The researcher profile returned by the upload service
   * 
   * @return  bool True if ResearcherID is set else false
   */
  public static function setResearcherIdByRidProfile($profile) 
  {
    $log = FezLog::get();
    $db = DB_API::get();
  
    $email = $profile->email;
    $researcher_id = $profile->researcherID;
    $employee_id = $profile->employeeID;
    $password = $profile->{'temp-password'};    
    $aut_org_username = $employee_id;
    
    if ($aut_org_username) {
      $stmt = "UPDATE
                  " . APP_TABLE_PREFIX . "author
               SET
                  aut_researcher_id=" . $db->quote($researcher_id) . "
               WHERE
                  aut_org_username=" . $db->quote($aut_org_username);
      
      try {
        $db->query($stmt, array());
      }
      catch(Exception $ex) {
        $log->err($ex);
        return false;
      }
      
      return Author::setRIDPassword($researcher_id, $password);
    } else {
      $log->err('Unable to retrieve author org username from RID profile');
      return false;
    }
  }
    
    
  /**
   * Method used to set the ResearcherID for an author.
   *
   * @access  public
   * @param string $aut_org_username The author id of the author
   * @param string $aut_org_username The ResearcherID of the author
   * 
   * @return  bool True if ResearcherID is set else false
   */
  public static function setResearcherIdByAutId($aut_id, $researcher_id) 
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "author
                 SET
                    aut_researcher_id=?
                 WHERE
                    aut_id=?";
    
    try {
      $db->query($stmt, array($researcher_id, $aut_id));
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return true;
  }
    
  /**
   * Method used to set the ResearcherID for an author.
   *
   * @param string $aut_org_username The org username of the author
   * @param string $aut_org_username The ResearcherID of the author
   * 
   * @return  bool True if ResearcherID is set else false
   */
  public static function setResearcherIdByOrgUsername($aut_org_username, $researcher_id) 
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $stmt = "UPDATE
                " . APP_TABLE_PREFIX . "author
             SET
                aut_researcher_id=?
             WHERE
                aut_org_username=?";
    
    try {
      $db->query($stmt, array($researcher_id, $aut_org_username));
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return true;
  }
    
  /**
   * Method used to set generate a ResearcherID password used when logging into the site.
   *
   * @param string $aut_researcher_id The researcher id we are generating the password for
   * @param string $password The plain text password to set
   * @return bool True if set else false
   */
  public static function setRIDPassword($researcher_id, $password) 
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "author
                 SET
                    aut_rid_password=" . $db->quote($password) . "
                 WHERE
                    aut_researcher_id=" . $db->quote($researcher_id);
              
    try {
      $db->query($stmt, array($mypub_url, $username));
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    
    return true;    
  }
    
  /**
   * Method used to set get the ResearcherID password.
   *
   * @param array $aut_researcher_id The researcher id we are generating the password for
   * @return  mixed The plain text password if generated else false
   */
  public static function getRIDPassword($aut_researcher_id) 
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $stmt = "SELECT
                  aut_rid_password
               FROM
                  " . APP_TABLE_PREFIX . "author
               WHERE
                  aut_researcher_id=".$db->quote($aut_researcher_id);
    
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    
    return $res;    
  }
  
  /**
   * Method used to determine if the specified author has also ever edited anything.
   *
   * @param integer $aut_id The author id of the author we are interested in
   * @return  bool True if the author has edited something, false if they haven't
   */
  function isAuthorAlsoAnEditor($authorID)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $stmt = "
      SELECT
        *
      FROM
        " . APP_TABLE_PREFIX . "record_search_key_contributor_id
      WHERE
        rek_contributor_id = " . $db->quote($authorID) . ";
    ";
    
    try {
      $res = $db->fetchOne($stmt);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    
    return $res;
  }
  

  function najaxGetMeta()
  {
    NAJAX_Client::mapMethods($this, array('getFullname','getDisplayName' ));
    NAJAX_Client::publicMethods($this, array('getFullname','getDisplayName'));
  }
  
/**
   * Method used to grab 1st author/contributor_id for a record
   *
   * CONCAT operator used to ensure authors are given preference
   * fetchAll instead of fetchOne used to improve usability of function
   */
  function getFirstAuthorIDinFez($pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
	$stmt = "SELECT rek_author_id, CONCAT('a',rek_author_id_order) as rek_author_id_order
		FROM " . APP_TABLE_PREFIX . "record_search_key_author_id 
		WHERE rek_author_id_pid = '" . $pid . "' AND rek_author_id > 0
		
		UNION 
		
		SELECT rek_contributor_id, CONCAT('c',rek_contributor_id_order)
		FROM " . APP_TABLE_PREFIX . "record_search_key_contributor_id 
		WHERE rek_contributor_id_pid = '" . $pid . "' AND rek_contributor_id > 0
		
		ORDER BY rek_author_id_order";
				
    try {
      $res = $db->fetchAll($stmt);
	  $log->err($res);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    
    return $res;
  }

    /**
     * Returns ResearcherID Registration record(s) for a specific Author.
     * When $getOne parameter is true, the method returns the first record of SQL query.
     * Otherwise, the method returns all records from the SQL query.
     * 
     * @param int $aut_id Author ID.
     * @param boolean $getOne A flag to indicate whether to return a single record. True to return one record, false otherwise.
     * @return array|boolean Array of record(s) or False when error encounter during mysql query.  
     */
    public function getRIDRegistrationResponse($aut_id = 0, $getOne = true)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT *
                FROM " . APP_TABLE_PREFIX . "rid_registrations 
                WHERE rre_aut_id = " . $db->quote($aut_id, 'INTEGER') . "
                ORDER BY rre_created_date DESC ";
        
        try {
            if ($getOne){
                $res = $db->fetchRow($stmt);
            } else {
                $res = $db->fetchAll($stmt);
            }
            $log->err($res);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return false;
        }
        
        
        // Get formatted datetime fields with user's preferred timezone. 
        // Get success status of RID response.
        $timezone = Date_API::getPreferredTimezone();
        foreach ($res as $key => $row) {
            if (is_array($row)){
                $res[$key]["success_response"] = Author::_isSuccessRIDRegistrationResponse($res[$key]);
                $res[$key]["rre_created_date_formatted"]    = Date_API::getFormattedDate($res[$key]["rre_created_date"], $timezone);
                $res[$key]["rre_updated_date_formatted"]    = Date_API::getFormattedDate($res[$key]["rre_updated_date"], $timezone);
            }else {
                $res["success_response"] = Author::_isSuccessRIDRegistrationResponse($res);
                $res["rre_created_date_formatted"]    = Date_API::getFormattedDate($res["rre_created_date"], $timezone);
                $res["rre_updated_date_formatted"]    = Date_API::getFormattedDate($res["rre_updated_date"], $timezone);
                break;
            }
        }
        
        return $res;
    }
    
    
    /**
     * String match the response received from ReseacherID service and find a match for success string pattern.
     * @param array $record
     * @return boolean True when success = 1, otherwise returns false. 
     */
    protected function _isSuccessRIDRegistrationResponse($record = array())
    {
        if (isset($record["rre_response"]) && strstr($record["rre_response"], "[success] => 1")){
            return true;
        }
        return false;
    }
}
