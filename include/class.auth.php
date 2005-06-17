<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004 MySQL AB                                    |
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
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: s.class.auth.php 1.37 04/01/16 23:25:25-00:00 jpradomaia $
//


/**
 * Class to handle authentication issues.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "private_key.php");

class Auth
{
    /**
     * Method used to get the requested URI for the 'current' page the user is
     * trying to access. This is used to get the appropriate URL and save it
     * if the user does not have the login session.
     *
     * @access  public
     * @return  string The requested URI for the current page
     */
    function getRequestedURL()
    {
        global $HTTP_SERVER_VARS;
        return $HTTP_SERVER_VARS["REQUEST_URI"];
    }


    /**
     * Method used to check for the appropriate authentication for a specific
     * page. It will check for the session name provided and redirect the user
     * to another page if needed.
     *
     * @access  public
     * @param   string $session_name The name of the session to check for
     * @param   string $failed_url The URL to redirect to if the user is not authenticated
     * @param   boolean $is_popup Flag to tell the function if the current page is a popup window or not
     * @return  void
     */
    function checkAuthentication($session_name, $failed_url = NULL, $is_popup = false)
    {
        global $HTTP_SESSION_VARS, $HTTP_SERVER_VARS;

        if ($failed_url == NULL) {
            $failed_url = APP_RELATIVE_URL . "login.php?err=5";
        }
        if (!isset($HTTP_SESSION_VARS[$session_name])) {
            $failed_url .= "&url=" . Auth::getRequestedURL();
            Auth::redirect($failed_url, $is_popup);
        }
        $session = $HTTP_SESSION_VARS[$session_name];
        $session = unserialize(base64_decode($session));
        if (!Auth::isValidSession($session)) {
            Auth::removeSession($session_name);
            Auth::redirect($failed_url, $is_popup);
        }
		if ($session['ipaddress'] != $HTTP_SERVER_VARS['REMOTE_ADDR']) {
            Auth::removeSession($session_name);
            Auth::redirect(APP_RELATIVE_URL . "login.php?err=20", $is_popup);
		}

/*        if (Auth::isPendingUser($session["username"])) {
            Auth::removeSession($session_name);
            Auth::redirect(APP_RELATIVE_URL . "login.php?err=9", $is_popup);
        }
        if (!Auth::isActiveUser($session["username"])) {
            Auth::removeSession($session_name);
            Auth::redirect(APP_RELATIVE_URL . "login.php?err=7", $is_popup);
        }
*/
        // check whether the collection selection is set or not
/*        $col_id = Auth::getCurrentCollection();
        if (empty($col_id)) {
            Auth::removesession($session_name);
            Auth::redirect(APP_RELATIVE_URL . "login.php?err=12&username=" . $session['username'], $is_popup);
        }
*/
        // if the current session is still valid, then renew the expiration
        Auth::createLoginSession($session_name, $session['username'], $session['fullname'], $session['email'], $session['autologin']);
        // renew the collection session as well
//        $col_session = Auth::getSessionInfo(APP_COLLECTION_session);
//        Auth::setCurrentCollection($col_id, $col_session["remember"]);
    }


	function getParentACMLs($array, $parents) {
		if (!is_array($parents)) {
			return false;
		}
		$ACMLArray = &$array;
//		$ACMLArray = array();
		foreach ($parents as $parent) {

			$xdis_array = Fedora_API::callGetDatastreamContentsField($parent['pid'], 'eSpaceMD', array('xdis_id'));
			$xdis_id = $xdis_array[0]['xdis_id'];
			$parentACML = Record::getACML($parent['pid'], $xdis_id);		

//			echo $parent['pid'] ." - ".$xdis_id. " -> "; print_r($parentACML); echo "\n\n";
			if ($parentACML != false) {
				array_push($ACMLArray, $parentACML);
			} else {
				$superParents = Record::getParents($parent['pid']);
//				print_r($superParents); echo "\n\n";
				if ($superParents != false) {
					Auth::getParentACMLs(&$ACMLArray, $superParents);
				}
			}
		}
//		return $ACMLArray;
	}

/*	function isViewRestricted($pid, $xdis_id) {
		$authGroups = Auth::getAuthorisationGroups($pid, $xdis_id);		
		print_r($authGroups);

	}*/

	function checkAuthorisation($pid, $xdis_id, $acceptable_roles, $failed_url) {
		global $HTTP_SESSION_VARS;
		session_start();
		$isAdministrator = User::isUserAdministrator(Auth::getUsername());
		if ($isAdministrator == true) {
			return true;
		}
		if (!is_array($acceptable_roles) || empty($pid) || empty($xdis_id)) {
			return false;
		}


		$userPIDAuthGroups = Auth::getAuthorisationGroups($pid, $xdis_id);
		$auth_ok = false;
//		if $userPIDAuthGroups 
		foreach ($acceptable_roles as $role) {
			if (in_array($role, $userPIDAuthGroups)) {
				$auth_ok = true;
			}
		}
		if ($auth_ok != true) {
			if (!isset($HTTP_SESSION_VARS[APP_SESSION])) {
			    Auth::redirect(APP_RELATIVE_URL . "login.php?err=21&url=".$failed_url, $is_popup);
			} else {
				return false;	
			}
		} else {
			return true;
		}

	}

	function getAuthorisationGroups ($pid, $xdis_id) {
		global $HTTP_SESSION_VARS;
		session_start();
//		echo "PID = $pid, xdis_id = $xdis_id\n\n";
		$info = Auth::getSessionInfo(APP_SESSION);
		$userPIDAuthGroups = array();

		$acmlBase = Record::getACML($pid, $xdis_id);
		$ACMLArray = array();
		if ($acmlBase === false) {
//			echo "GOING IN! with $pid";
			$parents = Record::getParents($pid);
//			print_r($parents);
//			echo "GOING IN!";
			Auth::getParentACMLs(&$ACMLArray, $parents);
		} else {
			$ACMLArray[0] = $acmlBase;
		}
/*		echo "\n\nACML Array -> ";
		print_r($ACMLArray);
		echo "<- \n\n";   */
//		echo "LDAP GROUPS -> ";
//		print_r($HTTP_SESSION_VARS['ESPACE_GROUPS']['LDAP_GROUPS']);
		$NonRestrictedRoles = array("Viewer","Lister","Comment_Viewer");
		$userPIDAuthGroups = $NonRestrictedRoles;
		foreach ($ACMLArray as $acml) {
			foreach ($acml as $role => $groups) {
				if (in_array($role, $userPIDAuthGroups)) { // if the role is in the ACML then it is restricted so remove it
					$userPIDAuthGroups = Misc::array_clean($userPIDAuthGroups, $role);
				}
				foreach ($groups as $group_name => $group_values) {
					foreach ($group_values as $group_value) {
						// @@@ CK - Put a check in here to say if the role has already been found then don't check for it again
						if ($group_name == 'AD_Group') {
							if (in_array($group_value, $HTTP_SESSION_VARS[APP_LDAP_GROUPS_SESSION]) && (!in_array($role, $userPIDAuthGroups)) ) {
								array_push($userPIDAuthGroups, $role);
							}
						} elseif ($group_name == 'in_AD') {
//							echo "$role AD VALUE -> ".$group_value."\n\n";
							if (($group_value == 'on') && (isset($HTTP_SESSION_VARS[APP_SESSION])) && (!in_array($role, $userPIDAuthGroups)) ) {							
								array_push($userPIDAuthGroups, $role);
							}
						} elseif ($group_name == 'in_eSpace') { // note used as yet..
							if (($group_value == 'on') && (isset($HTTP_SESSION_VARS[APP_SESSION])) && (!in_array($role, $userPIDAuthGroups)) ) {							
								array_push($userPIDAuthGroups, $role);
							}	
						} elseif ($group_name == 'AD_User') {
							if (($group_value == $info['username']) && (!in_array($role, $userPIDAuthGroups)) ) {
								array_push($userPIDAuthGroups, $role);
							}	
						} elseif ($group_name == 'eSpace_Group') { //not implemented yet
			
						} elseif ($group_name == 'eSpace_User') { //not implemented yet
			
						}
					}
				}				
			}
		}
		return $userPIDAuthGroups;
	}

    /**
     * Method to check whether an user is pending its confirmation 
     * or not.
     *
     * @access  public
     * @param   string $email The email address to be checked
     * @return  boolean
     */
    function isPendingUser($username)
    {
// OLD        $status = User::getStatusByEmail($email);

        $status = User::getStatusByUsername($username);

        if ($status != 'pending') {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Method to check whether an user is active or not.
     *
     * @access  public
     * @param   string $email The email address to be checked
     * @return  boolean
     */
    function isActiveUser($username)
    {
        $status = User::getStatusByUsername($username);
        if ($status != 'active') {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Method to check if the user has session support enabled in his browser or
     * not.
     *
     * @access  public
     * @param   string $session_name The name of the session to check for
     * @return  boolean
     */
    function hasSessionSupport($session_name)
    {
        global $HTTP_SESSION_VARS;
        if (@!in_array($session_name, array_keys($HTTP_SESSION_VARS))) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Method to check if the user has a valid session.
     *
     * @access  public
     * @param   string $session_name The name of the session to check for
     * @return  boolean
     */
    function hasValidSession($session_name)
    {
        global $HTTP_SESSION_VARS;

        $session = @$HTTP_SESSION_VARS[$session_name];
        $session = unserialize(base64_decode($session));
        if (!Auth::isValidSession($session)) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Method used to get the unserialized contents of the specified session
     * name.
     *
     * @access  public
     * @param   string $session_name The name of the session to check for
     * @return  array The unserialized contents of the session
     */
    function getSessionInfo($session_name)
    {   //var_dump($session_name);
        global $HTTP_SESSION_VARS;
		session_start();
        $session = @$HTTP_SESSION_VARS[$session_name];
//		print_r(unserialize(base64_decode($session)));
        return unserialize(base64_decode($session));
    }


    /**
     * Method used to check whether a session is valid or not.
     *
     * @access  public
     * @param   array $session The unserialized contents of the session
     * @return  boolean
     */
    function isValidSession($session)
    {
        if ((empty($session["username"])) || (empty($session["hash"])) ||
           ($session["hash"] != md5($GLOBALS["private_key"] . md5($session["login_time"]) . $session["username"]))) {
//            echo "isvalisession about to return false";
            return false;
        } else {
			// @@@ CK - 6/6/2005 - Removed check for eSpace UserID because we wan't to authenticate against AD users that are not eSpace users
            //$usr_id = User::getUserIDByUsername(@$session["username"]);
/*            if (empty($usr_id)) {
                return false;
            } else {
                return true;
            } */
			return true;
        }
    }


    /**
     * Method used to create the login session in the user's machine.
     *
     * @access  public
     * @param   string $session_name The session name to be created
     * @param   string $email The email address to be stored in the session
     * @param   integer $autologin Flag to indicate whether this user should be automatically logged in or not
     * @return  void
     */
    function createLoginSession($session_name, $username, $fullname, $email, $autologin = 0)
    {
		global $HTTP_SERVER_VARS;
		$ipaddress = $HTTP_SERVER_VARS['REMOTE_ADDR'];
        $time = time();
        $session = array(
            "username"   => $username,
            "fullname"   => $fullname,
            "email"   => $email,
            "ipaddress"   => $ipaddress,
            "login_time" => $time,
            "hash"       => md5($GLOBALS["private_key"] . md5($time) . $username),
            "autologin"  => $autologin
        );
//		print_r($session);
        $session = base64_encode(serialize($session));
        Auth::setSession($session_name, $session);
    }



	function setSession($session_name, $session) {
		global $HTTP_SESSION_VARS;
		session_start();		
		$HTTP_SESSION_VARS[$session_name] = $session;
	}


    /**
     * Method used to redirect people to another URL.
     *
     * @access  public
     * @param   string $new_url The URL the user should be redirected to
     * @param   boolean $is_popup Whether the current window is a popup or not
     * @return  void
     */
    function redirect($new_url, $is_popup = false)
    {
        if ($is_popup) {
            $html = '<script language="JavaScript">
                     <!--
                     window.opener.location.href = "' . $new_url . '";
                     window.close();
                     //-->
                     </script>';
            echo $html;
            exit;
        } else {
            header("Refresh: 0; URL=$new_url");
            exit;
        }
    }


    /**
     * Method used to remove a session from the user's browser.
     *
     * @access  public
     * @param   string $session_name The name of the session that needs to be deleted
     * @return  void
     */
    function removeSession($session_name)
    {
		// Initialize the session.
		// If you are using session_name("something"), don't forget it now!
		session_start();
		
		// Unset all of the session variables.
		$_SESSION = array();
		
		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (isset($_COOKIE[session_name()])) {
		   setcookie(session_name(), '', time()-42000, '/');
		}
		
		// Finally, destroy the session.
		session_destroy();
//        Auth::setSession($session_name, "", time()-36000, APP_RELATIVE_URL);
    }


    /**
     * Checks whether an user exists or not in the database.
     *
     * @access  public
     * @param   string $email The email address to check for
     * @return  boolean
     */
    function userExists($username)
    {
        if (empty($username)) {
            return false;
          } else {
            $stmt = "SELECT usr_username FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user WHERE usr_username='$username'";
            $info = $GLOBALS["db_api"]->dbh->getOne($stmt);
            if (PEAR::isError($info)) {
                Error_Handler::logError(array($info->getMessage(), $info->getDebugInfo()), __FILE__, __LINE__);
                return false;
            } elseif (empty($info)) {
                return false;
            } else {
                return true;
            }
        }
    }


    /**
     * Checks whether the provided password match against the email 
     * address provided.
     *
     * @access  public
     * @param   string $email The email address to check for
     * @param   string $password The password of the user to check for
     * @return  boolean
     */
    function isCorrectPassword($username, $password)
    {
		// @@@ CK - 9/6/2005 - will have to add extra logic here for non-ldap (espace or other) users. 
		return Auth::ldap_authenticate($username, $password);
		
/*        $stmt = "SELECT usr_username FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user WHERE usr_username='$username'";
        $passwd = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($passwd)) {
            Error_Handler::logError(array($passwd->getMessage(), $passwd->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            if ($passwd != md5($password)) {
                return false;
            } else {
                return true;
            }
        }
*/
    }


    /**
     * Gets the current user ID.
     *
     * @access  public
     * @return  integer The ID of the user
     */
    function getUserID()
    {
        $info = Auth::getSessionInfo(APP_SESSION);
        if (empty($info)) {
            return '';
        } else {
            return @User::getUserIDByUsername($info["username"]);
//            return @User::getUserIDByEmail($info["email"]);
        }
    }


    /**
     * Gets the current user ID.
     *
     * @access  public
     * @return  integer The ID of the user
     */
    function getUsername()
    {
        $info = Auth::getSessionInfo(APP_SESSION);
        if (empty($info)) {
            return '';
        } else {
            return $info["username"];
        }
    }
    /**
     * Gets the current user ID.
     *
     * @access  public
     * @return  integer The ID of the user
     */
    function getUserFullName()
    {
        $info = Auth::getSessionInfo(APP_SESSION);
        if (empty($info)) {
            return '';
        } else {
            return $info["fullname"];
        }
    }
    /**
     * Gets the current user ID.
     *
     * @access  public
     * @return  integer The ID of the user
     */
    function getUserEmail()
    {
        $info = Auth::getSessionInfo(APP_SESSION);
        if (empty($info)) {
            return '';
        } else {
            return $info["email"];
        }
    }

    /**
     * Gets the current user ID.
     *
     * @@@ CK - Needed back in by the end of route_notes.php so the notes could come from the right user
     * @access  public
     * @return  integer The ID of the user
     */
/*    function getUserIDByEmail()
    {
        $info = Auth::getSessionInfo(APP_SESSION);
        if (empty($info)) {
            return '';
        } else {
//            return @User::getUserIDByUsername($info["username"]);
            return @User::getUserIDByEmail($info["email"]);
        }
    }
*/

    /**
     * Gets the current selected collection from the collection session.
     *
     * @access  public
     * @return  integer The collection ID
     */
/*    function getCurrentCollection()
    {
        $session = Auth::getSessionInfo(APP_COLLECTION_session);
        if (empty($session)) {
            return "";
        }
        $usr_id = Auth::getUserID();
        $collections = Collection::getAssocList($usr_id);
        if (!in_array($session["col_id"], array_keys($collections))) {
            Auth::redirect(APP_RELATIVE_URL . "select_collection.php?err=1");
        }
        return $session["col_id"];
    }
*/


    function GetUsersLDAPGroups($username, $password)  {
	//PRE:
	// - $group, $username Parameters are set.
	//POST:
	// - Returns an array of groups the user belongs to

		$memberships = array();
		$success;
		$useringroupcount;
		$useringroupcount = 0;
		$ldap_conn;
		$ldap_result;
		$ldap_info;
		$ldap_infoadmin;
		$usersgroups = array();
		$success = 'true';
		$filter = "(samaccountname=".$username.")";
		$ldap_conn = ldap_connect(LDAP_SERVER, LDAP_PORT);
	    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    	ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
		$ldap_bind = ldap_bind($ldap_conn, LDAP_PREFIX."\\".$username, $password);
		if ($ldap_bind) {
			$ldap_result = ldap_search($ldap_conn, LDAP_ROOT_DN, $filter);
			// retrieve all the entries from the search result
			$ii=0;
			if ($ldap_result) {
				$info = ldap_get_entries($ldap_conn, $ldap_result);
				for ($i=0; $ii<$info[$i]["count"]; $ii++) {
					$data = $info[$i][$ii];
					for($j=0; $j<$info[$i][$data]["count"]; $j++) {	
						if ($data == "memberof") {
							array_push($memberships, $info[$i][$data][$j] );
						}
					}
				}
				foreach($memberships as $item) {
					list($CNitem, $rest) = split(",", $item);
					list($tag, $group) = split("=", $CNitem);
//					echo $username." is a member of group: $group<br>\n";
					array_push($usersgroups, $group);
				}
			} else {
				echo ldap_error($ldap_conn);
				exit;
			}
		} 	
		// close connection to ldap server
		ldap_close($ldap_conn);
//		echo "USER LDAP GROUPS -> ";
//		print_r($usersgroups);
//		session_start();
		Auth::setSession(APP_LDAP_GROUPS_SESSION, $usersgroups);
//		$HTTP_SESSION_VARS['ESPACE_GROUPS']['LDAP_GROUPS'] = $usersgroups;
//		$_SESSION['ESPACE_GROUPS']['LDAP_GROUPS'] = $usersgroups; // set the session session for ldap groups
//		print_r($HTTP_SESSION_VARS['ESPACE_GROUPS']['LDAP_GROUPS']);
		return $usersgroups;
    } //end of GetUserGroups function.





    /**
     * Gets the current collection name from the user's collection session.
     *
     * @access  public
     * @return  string The current collection name
     */
/*    function getCurrentCollectionName()
    {
        $proj_id = Auth::getCurrentCollection();
        if (!empty($proj_id)) {
            return Collection::getName($proj_id);
        }
    }
*/

    /**
     * Sets the current selected collection for the user session.
     *
     * @access  public
     * @param   integer $collection The collection ID
     * @param   integer $remember Whether to automatically remember the setting or not
     * @return  void
     */
/*    function setCurrentCollection($collection, $remember)
    {
        $session = array(
            "col_id"   => $collection,
            "remember" => $remember
        );
        $session = base64_encode(serialize($session));
        Auth::setSession(APP_COLLECTION_session, $session, APP_COLLECTION_session_EXPIRE, APP_RELATIVE_URL);
    }
*/
        # --------------------
        # Added by Christiaan 28/6/2004
        # Attempt to bind/authenticate the user against the LDAP directory
        # return true on successful authentication, false otherwise
        function ldap_authenticate( $p_user_id, $p_password ) {
                $t_authenticated 		= false;
				$t_username             = $p_user_id;

/*              $t_search_filter        = "(&(samaccountname=$t_username))";
                $t_search_attrs         = array( 'samaccountname', 'dn' ); */
                $t_ds                   = ldap_connect(LDAP_SERVER, LDAP_PORT);

                # Attempt to bind with the DN and password
                $t_br = ldap_bind( $t_ds, LDAP_PREFIX."\\".$t_username, $p_password );
                if ($t_br) {
                  $t_authenticated = true;
                }
                ldap_unbind( $t_ds );
                return $t_authenticated; 
//                return true; // switch this on and comment the rest out for debugging/development

        }




}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Auth Class');
}




?>
