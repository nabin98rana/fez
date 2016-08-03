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
// |          Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle Auth indexes
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 */
class AuthRules
{
  function getOrCreateRuleGroupArIds($ArIdGroup, $clearcache = false)
  {
    $db = DB_API::get();
    $log = FezLog::get();

    $ArIdGroupList = implode(",", array_filter($ArIdGroup));

    if (empty($ArIdGroupList)) {
      return false;
    }

    $dbtp = APP_TABLE_PREFIX;
    $stmt = "SELECT ar_rule as rule, ar_value as value
               FROM " . $dbtp . "auth_rules
               WHERE ar_id IN (" . $ArIdGroupList . ")";
    try {
      $res = $db->fetchAll($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      $res = null;
    }
    return AuthRules::getOrCreateRuleGroup($res, $clearcache);
  }

  function getOrCreateRuleGroup($group, $clearcache = false)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    static $gcache;

    if ($clearcache) {
      $gcache = array();
    }
    $rmd5 = AuthRules::getMd5($group);
    // check cache for rule group
    if (isset($gcache[$rmd5])) {

      return $gcache[$rmd5];
    }
    $dbtp = APP_TABLE_PREFIX;

    // does rule exist in table
    $stmt = "SELECT arg_id, arg_md5 
                 FROM " . $dbtp . "auth_rule_groups 
                 WHERE arg_md5=?";
    $res = null;
    try {
      $res = $db->fetchRow($stmt, array($rmd5));
    } catch (Exception $ex) {
      $log->err($ex);
      $res = null;
    }

    if (empty($res)) {
      // rule group doesn't exist so add it
      $stmt = "INSERT INTO " . $dbtp . "auth_rule_groups (arg_md5) VALUES (?) ";

      try {
        $db->query($stmt, array($rmd5));
      } catch (Exception $ex) {
        $log->err($ex);

        return -1;
      }
      $arg_id = $db->lastInsertId($dbtp . "auth_rule_groups", "arg_id");

      $values = array();
      $values_sql = array();
      $loop = 0;
      foreach ($group as $row) {
        $ar_id = AuthRules::getOrCreateRule($row['rule'], $row['value']);
        if (! AuthRules::groupRuleExists($arg_id, $ar_id)) {
          $values[$loop][] = $arg_id;
          $values[$loop][] = $ar_id;
          $values_sql[] = '(?,?)';
          $loop++;
        }
      }

      // make an insert statement
      if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
        $stmt = "INSERT INTO " . $dbtp . "auth_rule_group_rules (argr_arg_id,argr_ar_id) VALUES " . implode(', ', $values_sql);
      } else {
        $stmt = "INSERT INTO " . $dbtp . "auth_rule_group_rules (argr_arg_id,argr_ar_id) VALUES " . implode(', ', $values_sql[0]);
      }
      try {
        if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
          $values = Misc::array_flatten($values, '', TRUE);
          $db->query($stmt, $values);
        } else {
          foreach ($values as $value) {
            $db->query($stmt, $value);
          }
        }
      } catch (Exception $ex) {
        $log->err($ex);

        return -1;
      }
    } else {
      $arg_id = $res['arg_id'];
    }
    if (!is_array(@$gcache) || count(@$gcache) > 10) {
      $gcache = array();
    }

    $gcache[$rmd5] = $arg_id;

    return $arg_id;
  }

  /**
   * @param int $arg_id
   * @param int $ar_id
   * @return bool
   */
  function groupRuleExists($arg_id, $ar_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    $dbtp = APP_TABLE_PREFIX;

    // does group rule exist in table
    $stmt = "SELECT argr_arg_id 
                 FROM " . $dbtp . "auth_rule_group_rules 
                 WHERE argr_arg_id=? AND argr_ar_id=?";
    $res = null;
    try {
      $res = $db->fetchRow($stmt, array($arg_id, $ar_id));
    } catch (Exception $ex) {
      $log->err($ex);
      $res = null;
    }
    return (!empty($res));
  }

  /**
   * Get's an md5 that should be the same for rule groups that have the same rules regardless of order
   */
  function getMd5($group)
  {
    $log = FezLog::get();


    $row_strs = array();
    foreach ($group as $row) {
      $row_strs[] = trim($row['rule']) . trim($row['value']);
    }
    asort($row_strs);
    $row_strs = array_unique($row_strs);

    return md5(implode(';', $row_strs));
  }

  function getOrCreateRule($rule, $value)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $dbtp = APP_TABLE_PREFIX;
    // does rule exist in table
    $stmt = "SELECT ar_id " .
      "FROM {$dbtp}auth_rules " .
      "WHERE ar_rule=? and ar_value=? ORDER BY ar_id ASC";
    $res = null;
    try {
      $res = $db->fetchOne($stmt, array($rule, $value));
    } catch (Exception $ex) {
      $log->err($ex);
      $res = null;
    }

    if (empty($res)) {
      // if the rule is not yet in the table, then add it
      $stmt = "INSERT INTO " . $dbtp . "auth_rules (ar_rule,ar_value) VALUES (?,?) ";
      try {
        $db->query($stmt, array($rule, $value));
      } catch (Exception $ex) {
        $log->err($ex);

        return -1;
      }

      return $db->lastInsertId($dbtp . "auth_rules", "ar_id");
    } else {

      return $res;
    }

  }

  /**
   * Periodically call this function to truncate the user group cache table.
   */
  function truncateUserGroupCache()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "
                DELETE FROM 
                    " . APP_TABLE_PREFIX . "auth_rule_group_users
                WHERE 
                    argu_usr_id IN
                    (SELECT
                        usr_id
                    FROM
                        " . APP_TABLE_PREFIX . "user
                    WHERE ";
    if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) { //eg if postgresql etc
      $stmt .= " usr_last_login_date <  (NOW() - INTERVAL '" . APP_USER_GROUP_CACHE_EXPIRY . " days'))";
    } else {
      $stmt .= " usr_last_login_date < DATE_SUB(NOW(), INTERVAL " . APP_USER_GROUP_CACHE_EXPIRY . " DAY))";
    }

    try {
      $db->exec($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return -1;
    }
    return true;
  }

}
