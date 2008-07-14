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
 * Class to handle fez sessions in a database
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 */
//include_once('config.inc.php');
//require_once(APP_INC_PATH . "db_access.php");
//include_once(APP_INC_PATH . "class.misc.php");
//include_once(APP_INC_PATH . "class.error_handler.php");
//include_once(APP_INC_PATH . "class.template.php");

class SessionManager {

   var $life_time;
   var $db_api;
   var $valid;

   function SessionManager($db) {

      // Read the maxlifetime setting from PHP
      $this->life_time = get_cfg_var("session.gc_maxlifetime");
	  $this->db_api = $db;
	  $this->valid = true;
	  
      // Register this object as the session handler
      session_set_save_handler( 
        array( &$this, "open" ), 
        array( &$this, "close" ),
        array( &$this, "read" ),
        array( &$this, "write"),
        array( &$this, "destroy"),
        array( &$this, "gc" )
      );

   }

   function open( $save_path, $session_name ) {

      global $sess_save_path;

      $sess_save_path = $save_path;
      
      if(!isset($_COOKIE[$session_name]) || strlen($_COOKIE[$session_name]) == 0) {
      	$this->valid = false;
      }

      // Don't need to do anything. Just return TRUE.
      return true;
   }

   function close() {
      return true;
   }

   function read( $id ) {

      if(!$this->valid) {
      	return '';
      }
      
      $dbtp =  APP_TABLE_PREFIX;
      // Set empty result
      $data = '';

      // Fetch session data from the selected database

      $time = time();
      $newid = Misc::escapeString($id);
      $sql = "SELECT session_data FROM {$dbtp}sessions WHERE session_id = '$newid' AND expires > $time";
//	Error_Handler::logError(" ".$sql,__FILE__,__LINE__);
      $res = $this->db_api->dbh->getOne($sql);
      if (PEAR::isError($res)) {
          Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
      } else {
       return $res;
	  }
   }

   function write( $id, $data ) {

      if(!$this->valid) {
        return '';
      }
      
      $dbtp =  APP_TABLE_PREFIX;
      // Build query                
      $time = time() + $this->life_time;

	  $newid = Misc::escapeString($id);
	  $newdata = Misc::escapeString($data);
 	  $session_ip = Misc::escapeString(@$_SERVER['REMOTE_ADDR']);
//       $ip = getenv("REMOTE_ADDR");
      $sql = "REPLACE {$dbtp}sessions (session_id,session_data,expires,session_ip) VALUES ('$newid', '$newdata', $time, '$session_ip')";
//print_r($GLOBALS);
      $res = $this->db_api->dbh->query($sql);
      if (PEAR::isError($res)) {
          Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
      } else {
      	return TRUE;
	  }
   }

   function destroy( $id ) {

   	  $dbtp =  APP_TABLE_PREFIX;
      // Build query
	  $newid = Misc::escapeString($id);
      $sql = "DELETE FROM {$dbtp}sessions WHERE session_id = '$newid'";
      $res = $this->db_api->dbh->query($sql);
      //db_query($sql);
      if (PEAR::isError($res)) {
          Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
      } else {
      	  return TRUE;
	  }
   }

   function gc() {

      // Garbage Collection
      $dbtp =  APP_TABLE_PREFIX;
      // Build DELETE query.  Delete all records who have passed the expiration time
      $sql = "DELETE FROM {$dbtp}sessions WHERE expires < UNIX_TIMESTAMP();";
      $res = $this->db_api->dbh->query($sql);
      if (PEAR::isError($res)) {
          Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
      } else {
          return true;
  	  }
   }

}

?>
