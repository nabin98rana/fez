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


/**
 * Class to handle the business logic related to the administration
 * of users and permissions in the system.
 *
 * @version 1.0
 * @author Jo�o Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.prefs.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.group.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.setup.php");
//include_once(APP_INC_PATH . "private_key.php");

// definition of roles
// @@@ - CK - Added Power User so WSS can alter other collections stuff without being an administrator
/*
$roles = array(
    1 => "Viewer",
    2 => "Reporter",
    3 => "Standard User",
    4 => "Manager",
    5 => "Administrator"
);
*/
class User
{
    /**
     * Method used to lookup the user ID of a given username.
     *
     * @access  public
     * @param   string $email The email address associated with the user account
     * @return  integer The user ID
     */
    function getUserIDByUsername($username)
    {
        static $returns;

        if (!empty($returns[$username])) {
            return $returns[$username];
        }

        $stmt = "SELECT
                    usr_id
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_username='" . Misc::escapeString($username) . "'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
        	if ($GLOBALS['app_cache']) {
                $returns[$username] = $res;
            }
			if (!is_numeric($res)) {
				return 0; // added so auth index would continue with other auth parts without a number, this may change with eduPersonTargetedID
            } else {			
				return $res;
			}
        }
    }


    /**
     * Method used to check whether an user is set to status active 
     * or not.
     *
     * @access  public
     * @param   string $status The status of the user
     * @return  boolean
     */
    function isActiveStatus($status)
    {
        if ($status == 'active') {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Method used to get the list of all active users available in the system 
     * as an associative array of user IDs => user full names.
     *
     * @access  public
     * @param   integer $role The role ID of the user
     * @return  array The associative array of users
     */
    function getActiveAssocList($role = NULL)
    {
        $stmt = "SELECT
                    usr_id,
                    usr_full_name
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_status='active' AND
                    usr_id != " . APP_SYSTEM_USER_ID;
        if ($role != NULL) {
            $stmt .= " AND usr_role > ".$role;
        }
        $stmt .= "
                 ORDER BY
                    usr_full_name ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to check whether an user is an administrator.
     *
     * @access  public
     * @param   string $username The username of the user
     * @return  boolean
     */
    function isUserAdministrator($username)
    {
        $stmt = "SELECT
                    usr_administrator
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_username='".Misc::escapeString($username)."'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            return false;
        } else {
			if ($res['usr_administrator'] == 1) {
				return true;
			} else {
				return false;
			}
        }
    }

    /**
     * Method used to check whether an user is a super administrator.
     *
     * @access  public
     * @param   string $username The username of the user
     * @return  boolean
     */
    function isUserSuperAdministrator($username)
    {
        $stmt = "SELECT
                    usr_super_administrator
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_username='".Misc::escapeString($username)."'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            return false;
        } else {
			if ($res['usr_super_administrator'] == 1) {
				return true;
			} else {
				return false;
			}
        }
    }

    /**
     * Method used to check whether an user is an administrator.
     *
     * @access  public
     * @param   string $usr_id The user id in the table
     * @return  boolean
     */
    function getShibLoginCount($usr_id)
    {
        $stmt = "SELECT
                    usr_shib_login_count
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_id='".$usr_id."'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            return false;
        } else {
			return $res;
		}
    }

    /**
     * Method used to get the account details of a specific user.
     *
     * @access  public
     * @param   string $username The username
     * @return  array The account details
     */
    function getDetails($username)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_username='".Misc::escapeString($username)."'";

        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			$usr_id = User::getUserIDByUsername($username);
            $res["usr_groups"] = Group::getGroupColList($usr_id);
        	return $res;
		}
    }

    /**
     * Method used to get the account details of a specific user.
     *
     * @access  public
     * @param   integer $uid The user ID number
     * @return  array The account details
     */
    function getDetailsByID($id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_id=".$id;

        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            $res["usr_groups"] = Group::getGroupColList($id);		

            return $res;
        }
    }


    /**
     * Method used to get the displayname of a specific user.
     *
     * @access  public
     * @param   integer $uid The user ID number
     * @return  array The user display name
     */
    function getDisplayNameByID($id)
    {
        $stmt = "SELECT
                    usr_full_name
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_id=".$id;

        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the full name of the specified user.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  string The user' full name
     */
    function getFullName($usr_id)
    {
        static $returns;

        if (!is_numeric($usr_id)) {
          return "";
        }

        if (!is_array($usr_id)) {
            $items = array($usr_id);
        } else {
            $items = $usr_id;
        }

        $key = md5(serialize($usr_id));
        if (!empty($returns[$key])) {
            return $returns[$key];
        }

        $stmt = "SELECT
                    usr_full_name
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_id IN (" . implode(', ', $items) . ")";
        if (!is_array($usr_id)) {
            $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        } else {
            $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        }
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
        	if ($GLOBALS['app_cache']) {
                $returns[$key] = $res;
            }
            return $res;
        }
    }


    /**
     * Returns the status of the user associated with the given LDAP username.
     *
     * @@@ Added by Christiaan for UQ Username/LDAP support
     * @access  public
     * @param   string $username The ldap username
     * @return  string The user status
     */		 
    function getStatusByUsername($username)
    {

        static $returns;

        if (isset($returns[$username])) {
            return $returns[$username];
        }

        $stmt = "SELECT
                    usr_status
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_username='" . Misc::escapeString($username) . "'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
        	if ($GLOBALS['app_cache']) {
                $returns[$username] = $res;
            }
            return $res;
        }
    }

    /**
     * Method used to change the status of users, making them inactive
     * or active.
     *
     * @access  public
     * @return  boolean
     */
    function changeStatus()
    {
        // check if the user being inactivated is the last one
        $stmt = "SELECT
                    COUNT(*)
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_status='active'";
        $total_active = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (($total_active < 2) && ($_POST["status"] == "inactive")) {
            return false;
        }

        $items = @implode(", ", $_POST["items"]);
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "user
                 SET
                    usr_status='" . $_POST["status"] . "'
                 WHERE
                    usr_id IN (".$items.")";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }

    function remove()
    {
        $items = @implode(", ", $_POST["items"]);
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_id IN (".$items.")";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Method used to update the account password for a specific user.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @param   boolean $send_notification Whether to send the notification email or not, disabled for now
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function updatePassword($usr_id, $send_notification = FALSE)
    {
        if ($_POST['new_password'] != $_POST['confirm_password']) {
            return -2;
        }
        if (strlen($_POST['new_password']) < 6) {
            return -3;
        }
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "user
                 SET
                    usr_password='" . md5($_POST["new_password"]) . "'
                 WHERE
                    usr_id=".$usr_id;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            if ($send_notification) {
//                Notification::notifyUserPassword($usr_id, $_POST["new_password"]);
            }
            return 1;
        }
    } 


    /**
     * Method used to update the account username for a specific user.
     *
     * @access  public
     * @param   string $new_username The new username
     * @param   string $old_username The old username to search for
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function updateUsername($new_username, $old_username)
    {
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "user
                 SET
                    usr_username='" . Misc::escapeString($new_username) . "'
                 WHERE
                    usr_username='" . Misc::escapeString($old_username) . "'";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return 1;
        }
    }

    /**
     * Method used to update the Shibboleth account username for a specific user.
     *
     * @access  public
     * @param   string $new_username The existing un usually based on the prefix of EduPerson PrincipalName (before the @ eg youruser@yourinst.edu)
     * @param   string $old_username The shib username
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function updateShibUsername($username, $shib_username)
    {
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "user
                 SET
                    usr_shib_username='" . Misc::escapeString($shib_username) . "'
                 WHERE
                    usr_username='" . Misc::escapeString($username) . "'";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return 1;
        }
    }

    /**
     * Method used to update the account full name for a specific user.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function updateFullName($usr_id)
    {
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "user
                 SET
                    usr_full_name='" . Misc::escapeString($_POST["full_name"]) . "'
                 WHERE
                    usr_id=".$usr_id;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            $_SESSION['fullname'] = $_POST["full_name"];
            return 1;
        }
    } 

    /**
     * Method used to update the account email for a specific user.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function updateEmail($usr_id)
    {
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "user
                 SET
                    usr_email='" . Misc::escapeString($_POST["email"]) . "'
                 WHERE
                    usr_id=".$usr_id;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			$_SESSION['email'] = $_POST["email"];		
            return 1;
        }
    }

    /**
     * Method used to update the login details (login count, last login date) for a specific user.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function updateLoginDetails($usr_id)
    {
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "user
                 SET
                    usr_login_count=usr_login_count + 1,
					usr_last_login_date='" . Date_API::getCurrentDateGMT() . "'
                 WHERE
                    usr_id=".$usr_id;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return 1;
        }
    }

    /**
     * Method used to update the login details for shibboleth (login count, last login date) for a specific user.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function updateShibLoginDetails($usr_id)
    {
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "user
                 SET
                    usr_shib_login_count=usr_shib_login_count + 1,
					usr_last_login_date='" . Date_API::getCurrentDateGMT() . "'
                 WHERE
                    usr_id=".$usr_id;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return 1;
        }
    }
    
    
    function updateShibAttribs($usr_id)
    {
        $dbRes = 1;
        
        foreach ($_SESSION[APP_SHIB_ATTRIBUTES_SESSION] as $shib_name => $shib_value)
        {
            if ( is_numeric(strpos($shib_name, "Shib-EP")) && $shib_value != '' ) {
                $stmt = "REPLACE INTO
                            " . APP_TABLE_PREFIX . "user_shibboleth_attribs
                            (
                            usa_usr_id,
                            usa_shib_name,
                            usa_shib_value
                            )
                         VALUES (
                            $usr_id,
                            '$shib_name',
        					'$shib_value'
        				)";
                $res = $GLOBALS["db_api"]->dbh->query($stmt);
                
                if (PEAR::isError($res)) {
                    Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                    $dbRes = -1;
                }
            }
        }
        
        return $dbRes;
    }
    
    function loadShibAttribs($usr_id)
    {
        $stmt = "SELECT * 
                 FROM " . APP_TABLE_PREFIX . "user_shibboleth_attribs
                 WHERE usa_usr_id = " . $usr_id;
                
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
                
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        }
        
        foreach ($res as $row) {
            $_SESSION[APP_SHIB_ATTRIBUTES_SESSION][$row['usa_shib_name']] = $row['usa_shib_value'];
        }
        
        return true;
    }

    /**
     * Method used to update the account details for a specific user.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function update($superAdmin = 0)
    {
        // system account should not be updateable
/*        if ($_POST["id"] == APP_SYSTEM_USER_ID) {
            return 1;
        }*/
		if (@$_POST["administrator"]) {
			$usr_administrator = 1;
		} else {
			$usr_administrator = 0;
		}
        if ($superAdmin) {
            if (@$_POST["super_administrator"]) {
                $usr_super_administrator = 1;
            } else {
                $usr_super_administrator = 0;
            }
            $superAdminUpdateStatement = "usr_super_administrator=" . $usr_super_administrator . ", ";
        } else {
            $superAdminUpdateStatement = "";
        }
		if (@$_POST["ldap_authentication"]) {
			$ldap_authentication = 1;
		} else {
			$ldap_authentication = 0;
		}

        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "user
                 SET
                    usr_username='" . Misc::escapeString($_POST["username"]) . "',
                    usr_full_name='" . Misc::escapeString($_POST["full_name"]) . "',
                    usr_email='" . Misc::escapeString($_POST["email"]) . "',
                    usr_administrator=" . $usr_administrator . ",
                    " . $superAdminUpdateStatement . "
                    usr_ldap_authentication=" . $ldap_authentication;

        if ((!empty($_POST["password"])) && (($_POST["change_password"]))) {
            $stmt .= ",
                    usr_password='" . md5($_POST["password"]) . "'";
        } 
        $stmt .= "
                 WHERE
                    usr_id=" . $_POST["id"];
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            // update the collection associations now
            $stmt = "DELETE FROM
                        " . APP_TABLE_PREFIX . "group_user
                     WHERE
                        gpu_usr_id=" . $_POST["id"];
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            } else {
                for ($i = 0; $i < count($_POST["groups"]); $i++) {
                    $stmt = "INSERT INTO
                                " . APP_TABLE_PREFIX . "group_user
                             (
                                gpu_grp_id,
                                gpu_usr_id
                             ) VALUES (
                                " . $_POST["groups"][$i] . ",
                                " . $_POST["id"] . "
                             )";
                    $res = $GLOBALS["db_api"]->dbh->query($stmt);
                    if (PEAR::isError($res)) {
                        Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                        return -1;
                    }
                }
            }
            return 1;
        }
    }


    /**
     * Method used to add a new user to the system.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function insert()
    {
		if (@$_POST["administrator"]) {
			$usr_administrator = 1;
		} else {
			$usr_administrator = 0;
		}

		if (@$_POST["super_administrator"]) {
			$usr_super_administrator = 1;
		} else {
			$usr_super_administrator = 0;
		}

		if (@$_POST["ldap_authentication"]) {
			$ldap_authentication = 1;
		} else {
			$ldap_authentication = 0;
		}

        $prefs = Prefs::getDefaults();
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "user
                 (
                    usr_created_date,
                    usr_full_name,
                    usr_email,
                    usr_administrator,
                    usr_super_administrator,
                    usr_ldap_authentication,
                    usr_preferences,
                    usr_username";
        if (!empty($_POST["password"]))  {
            $stmt .= ",usr_password";
        } 

			$stmt .= "
                 ) VALUES (
                    '" . Date_API::getCurrentDateGMT() . "',
                    '" . Misc::escapeString($_POST["full_name"]) . "',
                    '" . Misc::escapeString($_POST["email"]) . "',
                    " . $usr_administrator . ",
                    " . $usr_super_administrator . ",
                    " . $ldap_authentication . ",
                    '" . Misc::escapeString($prefs) . "',
                    '" . Misc::escapeString($_POST["username"]) . "'";
	    if (!empty($_POST["password"]))  {					
			$stmt .= ",'" . md5($_POST["password"]) . "'";
		}
			$stmt .= "
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            $new_usr_id = $GLOBALS["db_api"]->get_last_insert_id();
            // add the group associations!
            for ($i = 0; $i < count($_POST["groups"]); $i++) {
                Group::associateUser($_POST["groups"][$i], $new_usr_id);
            } 
            return 1;
        }
    }

    /**
     * Method used to add a new user to the system from their login.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function insertFromLogin()
    {
		$usr_administrator = 0;

		$ldap_authentication = 0;

        $prefs = Prefs::getDefaults();
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "user
                 (
                    usr_created_date,
                    usr_full_name,
                    usr_email,
                    usr_administrator,
                    usr_ldap_authentication,
                    usr_preferences,
                    usr_username,
                    usr_password,					
                    usr_login_count,
                    usr_last_login_date
                 ) VALUES (
                    '" . Date_API::getCurrentDateGMT() . "',
                    '" . Misc::escapeString(ucwords($_POST["fullname"])) . "',
                    '" . Misc::escapeString($_POST["email"]) . "',
                    " . $usr_administrator . ",
                    " . $ldap_authentication . ",
                    '" . Misc::escapeString($prefs) . "',
                    '" . Misc::escapeString($_POST["username"]) . "',
                    '" . md5(Misc::escapeString($_POST["passwd"])) . "',
					1,
                    '" . Date_API::getCurrentDateGMT() . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            $new_usr_id = $GLOBALS["db_api"]->get_last_insert_id();
            // send email to user
//            Notification::notifyNewUser($new_usr_id, "");
            return 1;
        }
    }

    /**
     * Method used to add a new user to the system from their LDAP details.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function insertFromShibLogin($usr_username, $usr_full_name, $usr_email, $shib_username)
    {
		$usr_administrator = 0;
		$ldap_authentication = 1;

        $prefs = Prefs::getDefaults();
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "user
                 (
                    usr_created_date,
                    usr_full_name,
                    usr_email,
                    usr_administrator,
                    usr_ldap_authentication,
                    usr_preferences,
                    usr_username,
                    usr_shib_username,
                    usr_login_count,
                    usr_shib_login_count,
                    usr_last_login_date
                 ) VALUES (
                    '" . Date_API::getCurrentDateGMT() . "',
                    '" . Misc::escapeString(ucwords(strtolower($usr_full_name))) . "',
                    '" . $usr_email . "',
                    " . $usr_administrator . ",
                    " . $ldap_authentication . ",
                    '" . Misc::escapeString($prefs) . "',
                    '" . Misc::escapeString($usr_username) . "',
                    '" . Misc::escapeString($shib_username) . "',
					1,
					1,
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
     * Method used to add a new user to the system from an ePrints import. 
     * As we can't use the eprints passwords we make them all ldap accounts and the fez sysadmin will 
     * have to convert their user names to their inst ldap usernames or regen passwords (fez uses md5 pw's).
     * If they are turned into ldap accounts then the shibboleth automatic conversion of accounts will work
     * if they login with shibboleth. A lot easier if the ePrints usernames are the user's LDAP usernames.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function insertFromEprints($usr_username, $usr_full_name, $usr_email, $eprints_usr_id)
    {

		$usr_administrator = 0;

		$ldap_authentication = 1;

        $prefs = Prefs::getDefaults();
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "user
                 (
                    usr_created_date,
                    usr_full_name,
                    usr_email,
                    usr_administrator,
                    usr_ldap_authentication,
                    usr_preferences,
                    usr_username,
                    usr_shib_username,
                    usr_login_count,
                    usr_external_usr_id,
                    usr_last_login_date
                 ) VALUES (
                    '" . Date_API::getCurrentDateGMT() . "',
                    '" . Misc::escapeString(ucwords(strtolower($usr_full_name))) . "',
                    '" . $usr_email . "',
                    " . $usr_administrator . ",
                    " . $ldap_authentication . ",
                    '" . Misc::escapeString($prefs) . "',
                    '" . Misc::escapeString($usr_username) . "',
                    '" . Misc::escapeString($usr_username) . "',
					0,
					".$eprints_usr_id.",					
                    '" . Date_API::getCurrentDateGMT() . "'
                 ) on duplicate key update usr_external_usr_id = $eprints_usr_id";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            $new_usr_id = $GLOBALS["db_api"]->get_last_insert_id();
            // send email to user
//            Notification::notifyNewUser($new_usr_id, "");
            return 1;
        }
    }	
	
    /**
     * Method used to add a new user to the system from their LDAP details.
     *
     * @access  public
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function insertFromLDAPLogin()
    {
		$usr_administrator = 0;
		$ldap_authentication = 1;
		$userDetails = User::GetUserLDAPDetails($_POST["username"], $_POST["passwd"]);

        $prefs = Prefs::getDefaults();
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "user
                 (
                    usr_created_date,
                    usr_full_name,
                    usr_email,
                    usr_administrator,
                    usr_ldap_authentication,
                    usr_preferences,
                    usr_username,
                    usr_login_count,
                    usr_last_login_date
                 ) VALUES (
                    '" . Date_API::getCurrentDateGMT() . "',
                    '" . Misc::escapeString(ucwords(strtolower($userDetails['displayname']))) . "',
                    '" . $userDetails['email'] . "',
                    " . $usr_administrator . ",
                    " . $ldap_authentication . ",
                    '" . Misc::escapeString($prefs) . "',
                    '" . Misc::escapeString($_POST["username"]) . "',
					1,
                    '" . Date_API::getCurrentDateGMT() . "'
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            $new_usr_id = $GLOBALS["db_api"]->get_last_insert_id();
            // send email to user
//            Notification::notifyNewUser($new_usr_id, "");
            return 1;
        }
    }

    /**
     * Method used to get a user LDAP details.
     *
     * @access  public
	 * @param $username The LDAP username
	 * @param $password The LDAP password
     * @return array $userdetails An array of the user LDAP details
     */
    function GetUserLDAPDetails($username, $password)  {
		$success;
		$ldap_conn;
		$ldap_result;
		$ldap_info;
		$ldap_infoadmin;
		$userdetails = array();
		$success = 'true';
		$filter = "(samaccountname=".$username.")";
		$ldap_conn = ldap_connect(LDAP_SERVER, LDAP_PORT);
		ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
		$ldap_bind = @ldap_bind($ldap_conn, LDAP_PREFIX."\\".$username, $password);
		if ($ldap_bind) {
			$ldap_result = ldap_search($ldap_conn, LDAP_ROOT_DN, $filter);
			// retrieve all the entries from the search result
			$ii=0;
			if( $ldap_result ) {
				$info = ldap_get_entries( $ldap_conn, $ldap_result );
				for ($i=0; $ii<$info[$i]["count"]; $ii++) {
					$data = $info[$i][$ii];
					for( $j=0; $j<$info[$i][$data]["count"]; $j++ ) {
						if( $data == "mail" ) {
							$userdetails['email'] = $info[$i][$data][$j];
						}
						if( $data == "displayname" ) {
							$userdetails['displayname'] = $info[$i][$data][$j];
						}
						if( $data == "distinguishedname" ) {
							$userdetails['distinguishedname'] = $info[$i][$data][$j];
						}
	
					}	
				}		
			}

		} else {
			echo ldap_error( $ldap_conn );
			exit;
		}

		ldap_close( $ldap_conn );
	    return $userdetails;
    } 


    /**
     * Method used to get the list of users available in the system.
     *
     * @access  public
     * @return  array The list of users
     */
    function getList($current_row = 0, $max = 25, $order_by = 'usr_full_name', $filter="", $isSuperAdmin = false)
    {
    	$order_by = "usr_id DESC";    	
    	$where_stmt = "";
    	$extra_stmt = "";
    	$extra_order_stmt = "";    	    	
    	$filter = Misc::escapeString($filter);
    	if (!empty($filter)) {
	    	$where_stmt .= " WHERE match(usr_full_name, usr_given_names, usr_family_name, usr_username, usr_shib_username) AGAINST ('*".$filter."*' IN BOOLEAN MODE) ";
	    	$extra_stmt = " , match(usr_full_name, usr_given_names, usr_family_name, usr_username, usr_shib_username) AGAINST ('".$filter."') as Relevance ";
	    	$extra_order_stmt = " Relevance DESC, ";    	    		    	
    	}
    	
    	if(!$isSuperAdmin){
    		if($where_stmt) {
    		    $where_stmt .= " AND usr_super_administrator != 1";
    		} else {
    			$where_stmt = " WHERE usr_super_administrator != 1";
    		}
    	}
    	
		$start = $current_row * $max;
        $stmt = "SELECT SQL_CALC_FOUND_ROWS 
					* ".$extra_stmt."
                 FROM
                    " . APP_TABLE_PREFIX . "user
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
			foreach ($res as $key => $row) {
			  $res[$key]["usr_last_login_date"] = Date_API::getFormattedDate($res[$key]["usr_last_login_date"]);
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
    }    
    
    

    /**
     * Method used to get an associative array of the user ID and 
     * full name of the users available in the system.
     *
     * @access  public
     * @return  array The list of users
     */
    function getAssocList()
    {
        $stmt = "SELECT
                    usr_id,
                    usr_full_name
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 ORDER BY
                    usr_full_name ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }



    /**
     * Method used to get an associative array of the user ID and 
     * full name of all administrator users available in the system.
     *
     * @access  public
     * @return  array The list of admin users
     */
    function getAdminsAssocList()
    {
        $stmt = "SELECT
                    usr_id,
                    usr_full_name
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_administrator = 1 
                 ORDER BY
                    usr_full_name ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }



    /**
     * Method used to get an associative array of the user ID and 
     * full name of all super admins available in the system.
     *
     * @access  public
     * @return  array The list of admin users
     */
    function getSuperAdminsAssocList()
    {
        $stmt = "SELECT
                    usr_id,
                    usr_full_name
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_super_administrator = 1 
                 ORDER BY
                    usr_full_name ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }



    /**
     * Method used to get the full name and email for the specified
     * user.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  array The email and full name
     */
    function getNameEmail($usr_id)
    {
        static $returns;

        if (!empty($returns[$usr_id])) {
            return $returns[$usr_id];
        }

        $stmt = "SELECT
                    usr_full_name,
                    usr_email
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_id=".$usr_id;
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
        	if ($GLOBALS['app_cache']) {
                $returns[$usr_id] = $res;
            }
            return $res;
        }
    } 

    /**
     * Method used to get the full name and email for the specified
     * user.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  array The email and full name
     */
    function getIDByExtID($ext_id)
    {
        static $returns;

        if (!empty($returns[$ext_id])) {
            return $returns[$ext_id];
        }

        $stmt = "SELECT
                    usr_id
                 FROM
                    " . APP_TABLE_PREFIX . "user
                 WHERE
                    usr_external_usr_id=".$ext_id;
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
        	if ($GLOBALS['app_cache']) {
                $returns[$ext_id] = $res;
            }
            return $res;
        }
    } 
	
	
    /**
     * Method used to get the appropriate 'From' header for a 
     * specified user.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  string The formatted 'From' header
     */
    function getFromHeader($usr_id)
    {
        $info = User::getNameEmail($usr_id);
        return $info["usr_full_name"] . " <" . $info["usr_email"] . ">";
    }
}

// benchmarking the included file (aka setup time)
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included User Class');
}
?>
