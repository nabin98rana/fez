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
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.custom_view.php");
include_once(APP_INC_PATH . "class.misc.php");

/**
 * This class exists for interacting with the configuration settings sub-system.
 */

class Configuration
{
  /**
   * getConfAll
   *
   * Returns an associative array of all core configuration names/values in the config table.
   */
  function getConfAll() 
  {
    $db = DB_API::get();
          
    $stmt = "SELECT
                config_name, config_value
             FROM
                " . APP_TABLE_PREFIX . "config 
             WHERE
                config_module = 'core'";
              
    try {
      $res = $db->fetchAssoc($stmt);
    }
    catch(Exception $ex) {   
      return false;
    }
              
    if (empty($res)) {
      return array();
    } else {
      // Create a simple associative array of name/value pairs.
      $returnArray = array();
      foreach ($res as $item) {
          $returnArray[$item['config_name']] = $item['config_value'];
      }
      return $returnArray;
    }
  }


  /**
   * registerConf
   *
   * Registers global variables / definitions that Fez requires to operate happily. Once we have 
   * set-up all values located in the config table, we manually assemble a number of compound
   * variables. This function is roughly analogous to #including the old config.inc.php.
   */
  function registerConf() 
  {
    $settings = Configuration::getConfAll();

    foreach ($settings as $name => $value) {
      @define(strtoupper($name), $value);
    }

    $custom_view_pid = (isset($_GET['custom_view_pid'])) ? $_GET['custom_view_pid'] : null;
    if (!empty($custom_view_pid)) {
      $customView = Custom_View::getCommCview($custom_view_pid);			
      if (! $customView) {
        $error_type = 'Unable to get custom view from DB';
        include_once(APP_PATH . "offline.php");
        exit;
      }
    }
    else 
    {
        $customView = null;
    }

    // Assemble compound variables
    define("APP_CYCLE_COLORS", "#" . APP_CYCLE_COLOR_ONE . "," . "#" . APP_CYCLE_COLOR_TWO);
    define("APP_TPL_PATH", APP_PATH . "templates/");
    define("APP_THUMBS_PATH", APP_INC_PATH . "thumbs/");
    define("APP_JPGRAPH_PATH", APP_INC_PATH . "jpgraph/");
    define("APP_SETUP_PATH", APP_PATH);
    define("APP_SETUP_FILE", APP_SETUP_PATH . "setup.conf.php");
    define("APP_DELETE_DIR", APP_TEMP_DIR);
    define("APP_JHOVE_TEMP_DIR", APP_TEMP_DIR);
    define("APP_LOCKS_PATH", APP_TEMP_DIR);

    if ($customView) {
      define("APP_BASE_URL", "http://" . $customView['cvcom_hostname'] . APP_RELATIVE_URL);
      define("APP_CUSTOM_VIEW_ID", $customView['cview_id']);
      define("APP_CUSTOM_VIEW_PID", $custom_view_pid);
    } else {
      define("APP_CUSTOM_VIEW_ID", "");
      define("APP_CUSTOM_VIEW_PID", "");
      define("APP_BASE_URL", "http://" . APP_HOSTNAME . APP_RELATIVE_URL);
    }

    define("APP_RQF_REALLY_AUTO_MERGE", false);
    define("APP_DEFAULT_TIMEZONE", "UTC");
    define("APP_SHORT_NAME", APP_NAME);
    define("APP_SITE_NAME", APP_NAME);

    // Cookie & session-related stuff
    define("APP_COOKIE_EXPIRE", time() + (60 * 60 * 8));
    define("APP_COLLECTION_COOKIE", APP_COOKIE . "_collection");
    define("APP_COLLECTION_COOKIE_EXPIRE", time() + (60 * 60 * 24));
    define("APP_SESSION", APP_COOKIE);
    define("APP_INTERNAL_GROUPS_SESSION", APP_SESSION . "_internal_groups");
    define("APP_LDAP_GROUPS_SESSION", APP_SESSION . "_ldap_groups");
    define("APP_SHIB_ATTRIBUTES_SESSION", APP_SESSION . "_shib_attributes");
    define("APP_SESSION_EXPIRE", time() + (60 * 60 * 8));
    define("APP_SHIB_HOME_IDP_COOKIE", APP_SESSION . '_shib_home_idp_cookie');
    define("APP_SHIB_HOME_IDP_COOKIE_EXPIRE", time() + (60 * 60 * 24 * 30 * 48));
    define("APP_LIST_COOKIE", APP_COOKIE . '_list');
    define("APP_LIST_COOKIE_EXPIRE", time() + (60 * 60 * 24 * 30 * 48));
    define("APP_EMAIL_LIST_COOKIE", APP_COOKIE . '_email_list');
    define("APP_EMAIL_LIST_COOKIE_EXPIRE", time() + (60 * 60 * 24 * 30 * 48));
    define("APP_LIST_SESSION", APP_SESSION . '_list');
    define("APP_LIST_SESSION_EXPIRE", time() + (60 * 60 * 24 * 30 * 48));
    define("APP_EMAIL_LIST_SESSION", APP_SESSION . '_email_list');
    define("APP_EMAIL_LIST_SESSION_EXPIRE", time() + (60 * 60 * 24 * 30 * 48));

    // This list of roles control which roles can assume the roles of others, e.g. the
    // Community_Admin role can do all the roles an Editor can do.
    define('APP_VIEWER_ROLES', "Viewer,Community_Administrator,Editor,Creator,Annotator,Approver"); 
    define('APP_EDITOR_ROLES', "Community_Administrator,Editor,Approver");
    define('APP_CREATOR_ROLES', "Creator,Community_Administrator,Editor,Approver");
    define('APP_APPROVER_ROLES', "Community_Administrator,Approver");
    define('APP_DELETER_ROLES', "Community_Administrator");
    define('APP_LISTER_ROLES', "Lister,Viewer,Community_Administrator,Editor,Creator,Annotator,Approver"); 
    define('APP_VIEW_VERSIONS_ROLES', "");  // if none, available to Admins only
    // define('APP_REVERT_VERSIONS_ROLES', "");  // not implemented yet

    define('APP_VIEWER_ROLE_IDS', "10,6,8,7,1,2"); 
    define('APP_EDITOR_ROLE_IDS', "6,8,2");
    define('APP_CREATOR_ROLE_IDS', "7,6,8,2");
    define('APP_APPROVER_ROLE_IDS', "6,2");
    define('APP_DELETER_ROLE_IDS', "6");
    define('APP_LISTER_ROLE_IDS', "9,10,6,8,7,1,2"); 
    define('APP_VIEW_VERSIONS_ROLE_IDS', "");  // if none, available to Admins only
    // define('APP_REVERT_VERSIONS_ROLE_IDS', "");  // not implemented yet

    // Fedora stuff
    if (APP_FEDORA_SETUP == 'sslall') {
      define("APP_FEDORA_APIA_PROTOCOL_TYPE", "https://");
      define("APP_FEDORA_APIM_PROTOCOL_TYPE", "https://");
      define(
          "APP_BASE_FEDORA_APIA_DOMAIN", APP_FEDORA_APIA_PROTOCOL_TYPE.APP_FEDORA_USERNAME.":".
          APP_FEDORA_PWD."@".APP_FEDORA_SSL_LOCATION
      );
      define(
          "APP_BASE_FEDORA_APIM_DOMAIN", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_USERNAME.":".
          APP_FEDORA_PWD."@".APP_FEDORA_SSL_LOCATION
      );
      define("APP_SIMPLE_FEDORA_APIA_DOMAIN", APP_FEDORA_APIA_PROTOCOL_TYPE.APP_FEDORA_SSL_LOCATION);
      define("APP_SIMPLE_FEDORA_APIM_DOMAIN", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_SSL_LOCATION);
    } else if (APP_FEDORA_SETUP == 'sslapim') {
      define("APP_FEDORA_APIA_PROTOCOL_TYPE", "https://");
      define("APP_FEDORA_APIM_PROTOCOL_TYPE", "https://");
      define("APP_BASE_FEDORA_APIA_DOMAIN", APP_FEDORA_APIA_PROTOCOL_TYPE.APP_FEDORA_SSL_LOCATION);
      define(
          "APP_BASE_FEDORA_APIM_DOMAIN", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_USERNAME.":".
          APP_FEDORA_PWD."@".APP_FEDORA_SSL_LOCATION
      );
      define("APP_SIMPLE_FEDORA_APIA_DOMAIN", APP_FEDORA_APIA_PROTOCOL_TYPE.APP_FEDORA_SSL_LOCATION);
      define("APP_SIMPLE_FEDORA_APIM_DOMAIN", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_SSL_LOCATION);
    } else if (APP_FEDORA_SETUP == 'nosslall') {
      define("APP_FEDORA_APIA_PROTOCOL_TYPE", "http://");
      define("APP_FEDORA_APIM_PROTOCOL_TYPE", "http://");
      define(
          "APP_BASE_FEDORA_APIA_DOMAIN", APP_FEDORA_APIA_PROTOCOL_TYPE.APP_FEDORA_USERNAME.":".
          APP_FEDORA_PWD."@".APP_FEDORA_LOCATION
      );
      define(
          "APP_BASE_FEDORA_APIM_DOMAIN", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_USERNAME.":".
          APP_FEDORA_PWD."@".APP_FEDORA_LOCATION
      );
      define("APP_SIMPLE_FEDORA_APIA_DOMAIN", APP_FEDORA_APIA_PROTOCOL_TYPE.APP_FEDORA_LOCATION);
      define("APP_SIMPLE_FEDORA_APIM_DOMAIN", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_LOCATION);
    } else if (APP_FEDORA_SETUP == 'nosslapim') {
      define("APP_FEDORA_APIA_PROTOCOL_TYPE", "http://");
      define("APP_FEDORA_APIM_PROTOCOL_TYPE", "http://");
      define("APP_BASE_FEDORA_APIA_DOMAIN", APP_FEDORA_APIA_PROTOCOL_TYPE.APP_FEDORA_LOCATION);
      define(
          "APP_BASE_FEDORA_APIM_DOMAIN", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_USERNAME.":".
          APP_FEDORA_PWD."@".APP_FEDORA_LOCATION
      );
      define("APP_SIMPLE_FEDORA_APIA_DOMAIN", APP_FEDORA_APIA_PROTOCOL_TYPE.APP_FEDORA_LOCATION);
      define("APP_SIMPLE_FEDORA_APIM_DOMAIN", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_LOCATION);
    }
    define("APP_FEDORA_UPLOAD_URL", APP_BASE_FEDORA_APIM_DOMAIN . "/management/upload"); // Upload URL
    define("APP_FEDORA_GET_URL", APP_BASE_FEDORA_APIA_DOMAIN . "/get");                  // Get datastream URL
    define("APP_FEDORA_SEARCH_URL", APP_BASE_FEDORA_APIA_DOMAIN . "/search");            // Search URL
    define("APP_FEDORA_RISEARCH_URL", APP_BASE_FEDORA_APIA_DOMAIN . "/risearch");        // Resource Index Search URL
    define("APP_FEDORA_OAI_URL", APP_BASE_FEDORA_APIA_DOMAIN . "/oai");                  // OAI URL
    define("APP_FEDORA_ACCESS_API", APP_BASE_FEDORA_APIA_DOMAIN . "/services/access");
    define("APP_FEDORA_MANAGEMENT_API", APP_BASE_FEDORA_APIM_DOMAIN . "/services/management");
    // define("APP_FEDORA_ACCESS_WSDL_API", APP_SIMPLE_FEDORA_APIA_DOMAIN . "/wsdl?api=API-A");
    // define("APP_FEDORA_MANAGEMENT_WSDL_API", APP_SIMPLE_FEDORA_APIM_DOMAIN . "/wsdl?api=API-M");
    define("APP_FEDORA_ACCESS_WSDL_API", APP_FEDORA_ACCESS_API . "?wsdl");
    define("APP_FEDORA_MANAGEMENT_WSDL_API", APP_FEDORA_MANAGEMENT_API . "?wsdl");


    // OS-specific tweaks (Formerly Bill vs Linus).
    if (stristr(PHP_OS, 'darwin')) {
      // Darwin
      define("APP_DELETE_CMD", 'rm -f ');
    } else if (stristr(PHP_OS, 'win')) {
      // Windows
      define("APP_DELETE_CMD", 'del ');
    } else {
      // Nix
      define("APP_DELETE_CMD", 'rm -f ');
    }
    return;
  }

  /**
   * checkConf
   *
   * This is where we satisfy ourselves that sensible / compliant values have been set for 
   * as many of the config variables we care to check. It would be ideal to bind the sanity
   * checks into this function at some point, but that will need to be a project for further 
   * down the line.
   */
  function checkConf() 
  {
      return;
  }

  /**
   * saveConf
   *
   * This method examines all core configuration values that the system already knows about, and 
   * updates them all in turn with the POST data from the configuration page. It is important to
   * ensure that a HTML input field exists for every value in the config table!
   * 
   * Returns NULL if success
   * Returns array of error tokens if problem.
   */
  function saveConf() 
  {
    $db = DB_API::get();
  
    $originalSettings = Configuration::getConfAll();
    $problemUpdates = array();

    // For each config variable we know about, update it with the value from the form.
    foreach ($originalSettings as $key => $value) {
      try {
        $stmt = "UPDATE " . APP_TABLE_PREFIX . "config SET config_value = " . $db->quote($_POST[$key]) . 
                " WHERE config_name = " . $db->quote($key) . " AND config_module = 'core'";
        $db->exec($stmt);
      }
      catch(Exception $ex) {				
        array_push($problemUpdates, $key);
      }            
    }
    return $problemUpdates;
  }
}
