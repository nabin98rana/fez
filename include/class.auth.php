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
 * Class to handle authentication and authorisation issues.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "private_key.php");

global $NonRestrictedRoles;
$NonRestrictedRoles = array("Viewer","Lister","Comment_Viewer");
global $defaultRoles;
$defaultRoles = array("Editor", "Creator", "Lister", "Viewer", "Approver", "Community Administrator", "Annotator", "Comment_Viewer", "Commentor");

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
        global $HTTP_SERVER_VARS;

        session_name(APP_SESSION);
        @session_start();

        if (empty($failed_url)) {
            $failed_url = APP_RELATIVE_URL . "login.php?err=5";
        } else {
            $failed_url = APP_RELATIVE_URL . "login.php?err=21&url=".$failed_url;
		}
        
        if (!Auth::isValidSession($_SESSION)) {
            Auth::removeSession($session_name);
            Auth::redirect($failed_url, $is_popup);
        }

        // if the current session is still valid, then renew the expiration
        Auth::createLoginSession($_SESSION['username'], $_SESSION['fullname'], $_SESSION['email'], $_SESSION['autologin']);
    }

    /**
     * Method used to get the list of FezACML roles using in any XSD Display.
     *
     * @access  public
     * @return  array The list of FezACML roles
     */
    function getAllRoles()
    {
        $stmt = "SELECT distinct xsdsel_title 
			from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1
			inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on x1.xsdmf_id = xsdsel_xsdmf_id
			inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d1 on d1.xdis_id = x1.xsdmf_xdis_id
			inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd x2 on x2.xsd_id = xdis_xsd_id and x2.xsd_title = 'FezACML'";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			return $res;
		}
		
	}
		

    /**
     * Method used to get the ACML details for a records parent objects, using the Fez index.
	 * This method is usually only triggered when an object does not have its own ACML set against it.
     *
     * NOTE: This is a RECURSIVE function, as it keeps going up the record hierarchy if it can't find an ACML at each level.
     *	 
     * @access  public
     * @param   array $array The array of ACMLs that will be built and passed back by reference to the calling function.
     * @param   string $pid The persistant identifier of the object
     * @return  void (returns array by reference).
     */	
	function getIndexParentACMLs(&$array, $pid) {
		$ACMLArray = &$array;
		static $returns;
        if (!empty($returns[$pid])) { // check if this has already been found and set to a static variable		
			array_push($ACMLArray, $returns[$pid]); //add it to the acml array and dont go any further up the hierarchy
        } else {
		
			$pre_stmt =  "SELECT r2.rmf_varchar 
							FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
								  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,							
								  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
							WHERE (s2.sek_title = 'isMemberOf' AND r2.rmf_xsdmf_id = x2.xsdmf_id AND s2.sek_id = x2.xsdmf_sek_id AND r2.rmf_rec_pid = '".$pid."')";
			$res = $GLOBALS["db_api"]->dbh->getCol($pre_stmt);							
			$parent_pid_string = implode("', '", $res);
			$stmt = "SELECT 
						* 
					 FROM
						" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
					    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key k1,
						" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d1,
						" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 left join
						" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)

					 WHERE
						r1.rmf_xsdmf_id = x1.xsdmf_id and ((d1.xdis_id = x1.xsdmf_xdis_id and d1.xdis_title = 'FezACML') or (k1.sek_title = 'isMemberOf' AND r1.rmf_xsdmf_id = x1.xsdmf_id AND k1.sek_id = x1.xsdmf_sek_id)) and
						r1.rmf_rec_pid in ('$parent_pid_string')
						";
//			$returnfields = array("Editor", "Creator", "Lister", "Viewer", "Approver", "Community Administrator", "Annotator", "Comment_Viewer", "Commentor");
			$returnfields = Auth::getAuthRoles();
			$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
			$return = array();	
			foreach ($res as $result) {
				if (!is_array(@$return[$result['rmf_rec_pid']])) {
					$return[$result['rmf_rec_pid']]['exists'] = array();
				}
				if (in_array($result['xsdsel_title'], $returnfields) && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) ) {
					if (!is_array(@$return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']])) {
						$return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']] = array();
					}
					if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']])) {
						array_push($return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']], $result['rmf_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
					}
				}
			}
			foreach ($return as $key => $record) {
				if (is_array(@$record['FezACML'])) {
					if (empty($returns[$pid])) {
						$returns[$pid] = $record['FezACML'];
					}
					array_push($ACMLArray, $record['FezACML']); //add it to the acml array and dont go any further up the hierarchy
				} else {
					Auth::getIndexParentACMLs($ACMLArray, $key); //otherwise go up the hierarchy recursively
				}
			}
		}
	}

    /**
     * Method used to loop over a set of known parents of an object to get the ACML details.
	 * This method is usually only triggered when an object does not have its own ACML set against it.
     *
     * @access  public
     * @param   array $array The array of ACMLs that will be built and passed back by reference to the calling function.
     * @param   string $pid The persistant identifier of the object
     * @param   array $parents The array of parent PIDS to loop over
     * @return  false if an array of parents is not set in the parameter, returns array by reference.
     */	
	function getIndexParentACMLMemberList(&$array, $pid, $parents) {
		if (!is_array($parents)) {
			return false;
		}
		foreach ($parents as $parent) {
			Auth::getIndexParentACMLMember(&$array, $parent);
		}
	}

    /**
     * Method used to get the ACML details for a records parent objects, using the Fez index.
	 * This method is usually only triggered when an object does not have its own ACML set against it.
	 * Differs from the "non-member" version as you already know the pids of the member parents when using this function.
     *
     * NOTE: This is a RECURSIVE function, as it keeps going up the record hierarchy if it can't find an ACML at each level.
     *	 
     * @access  public
     * @param   array $array The array of ACMLs that will be built and passed back by reference to the calling function.
     * @param   string $pid The persistant identifier of the object
     * @return  void (returns array by reference).
     */	
	function getIndexParentACMLMember(&$array, $pid) {
		$ACMLArray = &$array;
		static $returns;
        if (is_array(@$returns[$pid])) {		
			$ACMLArray = $returns[$pid]; //add it to the acml array and dont go any further up the hierarchy
        } else {								
			$stmt = "SELECT 
						* 
					 FROM
						" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1
						inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d1 on (d1.xdis_id = x1.xsdmf_xdis_id and r1.rmf_rec_pid ='".$pid."')
						inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd x2 on (x2.xsd_id = d1.xdis_xsd_id and x2.xsd_title = 'FezACML')
						inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on (r1.rmf_xsdmf_id = x1.xsdmf_id)
						left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
						left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key k1 on (k1.sek_title = 'isMemberOf' AND r1.rmf_xsdmf_id = x1.xsdmf_id AND k1.sek_id = x1.xsdmf_sek_id)";
//			echo "\n\n".$stmt;
            global $defaultRoles;
			$returnfields = $defaultRoles;
			$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
			$return = array();
			foreach ($res as $result) {
				if (!is_array(@$return[$result['rmf_rec_pid']])) {
					$return[$result['rmf_rec_pid']]['exists'] = array();
				}
				if (in_array($result['xsdsel_title'], $returnfields) && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) ) {
					if (!is_array(@$return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']])) {
						$return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']] = array();
					}
					if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']])) {
						array_push($return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']], $result['rmf_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
					}
				}
			}
			foreach ($return as $key => $record) {	
				if (is_array(@$record['FezACML'])) {
					if (!is_array($returns[$pid])) {
						$returns[$pid] = $record['FezACML'];
					}
					array_push($ACMLArray, $record['FezACML']); //add it to the acml array and dont go any further up the hierarchy
				} else {
					Auth::getIndexParentACMLs($ACMLArray, $key);
					$returns[$pid] = $ACMLArray;
				}
			}
		}
	}

    /**
     * Method used to get the ACML details for a records parent objects, using the Fez Fedora connection.
	 * This method is usually only triggered when an object does not have its own ACML set against it.
	 * This way of getting the security directly from the Fedora connection is only called when a user
	 * directly accessed the object, eg with update and view, otherwise they will use the index.
     *
     * NOTE: This is a RECURSIVE function, as it keeps going up the record hierarchy if it can't find an ACML at each level.
     *	 
     * @access  public
     * @param   array $array The array of ACMLs that will be built and passed back by reference to the calling function.
     * @param   array $parents The array of parent PIDS to loop over
     * @return  void (returns array by reference).
     */	
	function getParentACMLs(&$array, $parents) {
		if (!is_array($parents)) {
			return false;
		}
		$ACMLArray = &$array;
		foreach ($parents as $parent) {
			$xdis_array = Fedora_API::callGetDatastreamContentsField($parent['pid'], 'FezMD', array('xdis_id'));
			$xdis_id = $xdis_array['xdis_id'][0];
			$parentACML = Record::getACML($parent['pid']);
		
			if ($parentACML != false) {
				array_push($ACMLArray, $parentACML);
			} else {
				$superParents = Record::getParents($parent['pid']);
				if ($superParents != false) {
					Auth::getParentACMLs(&$ACMLArray, $superParents);
				}
			}
		}
	}
	

   /**
     * isAdministrator
     * Checks if the current user is the administrator.
     * @returns boolean true if access is ok.
     */
    function isAdministrator()
    {
        $answer = false;
        if (Auth::isValidSession($_SESSION)) {
            if (!isset($_SESSION['isAdministrator'])) {
                $_SESSION['isAdministrator'] = User::isUserAdministrator(Auth::getUsername());
            }
            $answer = $_SESSION['isAdministrator'];
        }
        return $answer;
    }
   /**
    * checkAuthorisation
	* Can the user access the object?
    *
    * @access  public
    * @param   string $pid The persistant identifier of the object
    * @param   array $acceptable_roles The array of roles that will be accepted to access the object.
    * @param   string $failed_url The URL to redirect back to once the user has logged in, if they are not logged in.
    * @param   array $userPIDAuthGroups The array of groups this user belongs to.
    * @param   boolean $userPIDAuthGroups OPTIONAL (default is true) whether to redirect to the login page or not.
    * @returns boolean true if access is ok.
    */
    function checkAuthorisation($pid, $acceptable_roles, $failed_url, $userPIDAuthGroups=null, $redirect=true) {
        session_name(APP_SESSION);
        @session_start();
		$isAdministrator = Auth::isAdministrator();
		if ($isAdministrator) {
			return true;
		}
		if (!is_array($acceptable_roles) || empty($pid) ) {
			return false;
		}		
        // find out which role groups this user belongs to
        if (is_null($userPIDAuthGroups)) {
            $userPIDAuthGroups = Auth::getAuthorisationGroups($pid);
        }
		$auth_ok = false;
		foreach ($acceptable_roles as $role) {
			if (in_array($role, $userPIDAuthGroups)) {
				$auth_ok = true;
			}
		}
		if ($auth_ok != true) {
            // Perhaps the user hasn't logged in
			if (!Auth::isValidSession($_SESSION)) {
				if ($redirect != false) {
				    Auth::redirect(APP_RELATIVE_URL . "login.php?err=21&url=".$failed_url, $is_popup);
				}
			} else {
				return false;	
			}
		} else {
			return true;
		}
	}

    /**
     * getIndexAuthorisationGroups
	 * This method gets the roles (or authorisation groups) the user has, based on the given ACMLs using the Fez Index.
	 * This is usually used when the user is searching, listing, browsing when an index would speed up the process.
     *
     * @access  public
     * @param   array $indexArray The array of ACMLs found for the object.
     * @returns array $indexArray The input array, but with results of the security check for roles.
     */
	function getIndexAuthorisationGroups($indexArray) {
        // Usually everyone can list, view and view comments, this is set in the global "non restricted roles".

		global $NonRestrictedRoles;
		foreach ($indexArray as $indexKey => $indexRecord) {
			$userPIDAuthGroups = $NonRestrictedRoles;
			if (!is_array($indexRecord['FezACML'])) {
//				return false;
				// if it doesnt have its own acml record try and get rights from its parents
//				Auth::getIndexAuthorisationGroups();
				// 1. get the parents records with their fez acml's
				// 2. if at least one of them have an fez acml then use it otherwise get the parents parents

			}
			foreach ($indexRecord['FezACML'] as $FezACML) { // can have multiple fez acmls if got from parents
				foreach ($FezACML as $role_name => $role) {	
					if (in_array($role_name, $userPIDAuthGroups)) {
						$userPIDAuthGroups = Misc::array_clean($userPIDAuthGroups, $role_name, false, true);
					}
					foreach ($role as $rule_name => $rule) {
						foreach ($rule as $ruleRecord) {
							// if the role is in the ACML then it is restricted so remove it


							// @@@ CK - if the role has already been 
							// found then don't check for it again
							if (!in_array($role_name, $userPIDAuthGroups)) {
								switch ($rule_name) {
									case '!rule!role!AD_Group': 
										if (@in_array($ruleRecord, $_SESSION[APP_LDAP_GROUPS_SESSION])) {
											array_push($userPIDAuthGroups, $role_name);
										}
										break;
									case '!rule!role!in_AD':
										if (($ruleRecord == 'on') && Auth::isValidSession($_SESSION)
												&& Auth::isInAD()) {
											array_push($userPIDAuthGroups, $role_name);
										}
										break;
									case '!rule!role!in_Fez':
										if (($ruleRecord == 'on') && Auth::isValidSession($_SESSION) 
												&& Auth::isInDB()) {
											array_push($userPIDAuthGroups, $role_name);
										}	
										break;
									case '!rule!role!AD_User':
										if (Auth::isValidSession($_SESSION) 
												&& $ruleRecord == Auth::getUsername()) {
											array_push($userPIDAuthGroups, $role_name);
										}
										break;
									case '!rule!role!Fez_Group':
										if (@in_array($ruleRecord, $_SESSION[APP_INTERNAL_GROUPS_SESSION])) {
											array_push($userPIDAuthGroups, $role_name);
										}
										break;	
									case '!rule!role!Fez_User':
										if (Auth::isValidSession($_SESSION)
												&& $ruleRecord == Auth::getUserID()) {
											array_push($userPIDAuthGroups, $role_name);
										}
										break;
									default:
										break;
								}
							}												
						}
					}
				}			
			}		
			$indexArray[$indexKey]['isCommunityAdministrator'] = in_array('Community Administrator', $userPIDAuthGroups); //editor is only for the children. To edit the actual community record details you need to be a community admin
			$indexArray[$indexKey]['isEditor'] = in_array('Editor', $userPIDAuthGroups);
			$indexArray[$indexKey]['isViewer'] = in_array('Viewer', $userPIDAuthGroups);
			$indexArray[$indexKey]['isLister'] = in_array('Lister', $userPIDAuthGroups);
		}
		return $indexArray;		
	}

    /**
     * getAuthorisationGroups
	 * This method gets the roles (or authorisation groups) the user has, based on the given ACMLs using the Fez Fedora connection.
	 * It performs some of the lookups using XPATH searches. This is called when the user is working directly with the object
	 * eg view, update, edit etc.
     *
     * @access  public
     * @param   string $pid The persistent identifier of the object
     * @returns array $userPIDAuthGroups The authorisation groups (roles) the user belongs to against this object.
    */
	function getAuthorisationGroups($pid) {
        static $roles_cache;

        if (isset($roles_cache[$pid])) {
            return $roles_cache[$pid];
        }

        $userPIDAuthGroups = array();

        $acmlBase = Record::getACML($pid);
        $ACMLArray = array();
        if ($acmlBase == false) {
            $parents = Record::getParents($pid);
            Auth::getParentACMLs(&$ACMLArray, $parents);
        } else {
            $ACMLArray[0] = $acmlBase;
        }
        // Usually everyone can list, view and view comments
        global $NonRestrictedRoles;
        $userPIDAuthGroups = $NonRestrictedRoles;
        // loop through the ACML docs found for the current pid or in the ancestry
        foreach ($ACMLArray as &$acml) {
            // Use XPath to find all the roles that have groups set and loop through them
            $xpath = new DOMXPath($acml);
            $roleNodes = $xpath->query('/FezACML/rule/role');
            foreach ($roleNodes as $roleNode) {
                $role = $roleNode->getAttribute('name');
                //echo $acml->saveXML($roleNode);
                // Use XPath to get the sub groups that have values
                $groupNodes = $xpath->query('./*[string-length(normalize-space()) > 0]', $roleNode); /* */
                if ($groupNodes->length) {
                    //echo $role;
                    // if the role is in the ACML then it is restricted so remove it
                    if (in_array($role, $userPIDAuthGroups)) {
                        $userPIDAuthGroups = Misc::array_clean($userPIDAuthGroups, $role, false, true);
                    }
                }
                foreach ($groupNodes as $groupNode) {
                    $group_type = $groupNode->nodeName;
                    //echo $group_type;
                    $group_values = explode(',', $groupNode->nodeValue);
                    foreach ($group_values as $group_value) {
                        $group_value = trim($group_value, ' ');
                        // @@@ CK - if the role has already been
                        // found then don't check for it again
                        if (!in_array($role, $userPIDAuthGroups)) {
                            switch ($group_type) {
                                case 'AD_Group':
                                    if (@in_array($group_value, $_SESSION[APP_LDAP_GROUPS_SESSION])) {
                                        array_push($userPIDAuthGroups, $role);
                                    }
                                    break;
                                case 'in_AD':
                                    if (($group_value == 'on') && Auth::isValidSession($_SESSION)
                                            && Auth::isInAD()) {
                                        array_push($userPIDAuthGroups, $role);
                                    }
                                    break;
                                case 'in_Fez':
                                    if (($group_value == 'on') && Auth::isValidSession($_SESSION)
                                            && Auth::isInDB()) {
                                        array_push($userPIDAuthGroups, $role);
                                    }    
                                    break;
                                case 'AD_User':
                                    if (Auth::isValidSession($_SESSION)
                                            && $group_value == Auth::getUsername()) {
                                        array_push($userPIDAuthGroups, $role);
                                    }
                                    break;
                                case 'Fez_Group':
                                    if (@in_array($group_value, $_SESSION[APP_INTERNAL_GROUPS_SESSION])) {
                                        array_push($userPIDAuthGroups, $role);
                                    }
                                    break;

                                case 'Fez_User':
                                    if (Auth::isValidSession($_SESSION)
                                            && $group_value == Auth::getUserID()) {
                                        array_push($userPIDAuthGroups, $role);
                                    }
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                }
            }
        }
        $roles_cache[$pid] = $userPIDAuthGroups;
        return $userPIDAuthGroups;
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
        session_name($session_name);
        @session_start();
        return Auth::isValidSession($_SESSION);
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
        global $HTTP_SERVER_VARS;
        if ((empty($session["username"])) || (empty($session["hash"])) 
                || ($session["hash"] != md5($GLOBALS["private_key"] . md5($session["login_time"]) 
                        . $session["username"]))
                || ($session['ipaddress'] != $HTTP_SERVER_VARS['REMOTE_ADDR'])) {
            return false;
        } else {
			return true;
        }
    }


    /**
     * Method used to create the login session in the user's machine.
     *
     * @access  public
     * @param   string $username The username to be stored in the session
     * @param   string $fullname The user full name to be stored in the session
     * @param   string $email The email address to be stored in the session
     * @param   integer $autologin Flag to indicate whether this user should be automatically logged in or not
     * @return  void
     */
    function createLoginSession($username, $fullname, $email, $autologin = 0)
    {
		global $HTTP_SERVER_VARS;
		$ipaddress = $HTTP_SERVER_VARS['REMOTE_ADDR'];
        $time = time();
        $_SESSION["username"] = $username;
        $_SESSION["fullname"] = $fullname;
        $_SESSION["email"] = $email;
        $_SESSION["ipaddress"] = $ipaddress;
        $_SESSION["login_time"] = $time;
        $_SESSION["hash"] = md5($GLOBALS["private_key"] . md5($time) . $username);
		$_SESSION["autologin"] = $autologin;
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
        session_name($session_name);
		@session_start();		
		// Unset all of the session variables.
		$_SESSION = array();
		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (isset($_COOKIE[session_name()])) {
		   setcookie(session_name(), '', time()-42000, '/');
		}
		// Finally, destroy the session.
		session_destroy();
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
            $stmt = "SELECT usr_administrator FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "user WHERE usr_username='$username'";
            $info = $GLOBALS["db_api"]->dbh->getOne($stmt);
            if (PEAR::isError($info)) {
                Error_Handler::logError(array($info->getMessage(), $info->getDebugInfo()), __FILE__, __LINE__);
                return false;
            } elseif (count($info) == 1) {
                return false;
            } else {
                return true;
            }
        }
    }


    /**
     * Checks whether the provided password match against the username.
     *
     * @access  public
     * @param   string $email The email address to check for
     * @param   string $password The password of the user to check for
     * @return  boolean
     */
    function isCorrectPassword($username, $password)
    {
        if (APP_TEST) {
            return true;
        } else {
            global $HTTP_POST_VARS;
            if (Auth::userExists($HTTP_POST_VARS["username"])) {
                $userDetails = User::getDetails($username);
                if (($userDetails['usr_ldap_authentication'] == 1) && (LDAP_SWITCH == "ON")) {
                    return Auth::ldap_authenticate($username, $password);
                } else {
                    if ($userDetails['usr_password'] != md5($password) || (trim($password) == "")) {
                        return false;
                    } else {
                        return true;
                    }
                }
            } else {
                if (LDAP_SWITCH == "ON") { 
                    return Auth::ldap_authenticate($username, $password);
                } else {
                    return false;
                }
            }
        }
    }


    /**
     * Method to check whether an user is active or not.
     *
     * @access  public
     * @param   string $username The username to be checked
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
     * Gets the current user ID.
     *
     * @access  public
     * @return  integer The ID of the user
     */
    function getUserID()
    {
        if (empty($_SESSION['username'])) {
            return '';
        } else {
            return @User::getUserIDByUsername($_SESSION["username"]);
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
        if (empty($_SESSION) || empty($_SESSION['username'])) {
            return '';
        } else {
            return $_SESSION['username'];
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
        if (empty($_SESSION) || empty($_SESSION["fullname"])) {
            return '';
        } else {
            return $_SESSION["fullname"];
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
        if (empty($_SESSION) || empty($_SESSION["email"])) {
            return '';
        } else {
            return $_SESSION["email"];
        }
    }

    /**
     * Gets the LDAP groups the user belongs to. 
     *
     * @access  public
     * @param   string $username The username of the user (in ldap)
     * @param   string $password The password of the user (in ldap)
     * @return  array $usersgroups, plus saves them to the LDAP groups session variable
     */
    function GetUsersLDAPGroups($username, $password)  {
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
		$ldap_bind = @ldap_bind($ldap_conn, LDAP_PREFIX."\\".$username, $password);
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
		$_SESSION[APP_LDAP_GROUPS_SESSION] = $usersgroups;
		return $usersgroups;
    } //end of GetUserGroups function.


    /**
     * Checks if the user can authentication off the LDAP server. 
     *
     * @access  public
     * @param   string $p_user_id The username of the user (in ldap)
     * @param   string $p_password The password of the user (in ldap)
     * @return  boolean true if the user successfully binds to the LDAP server
     */
	function ldap_authenticate($p_user_id, $p_password) {
        if (APP_TEST) {
            return true; // switch this on and comment the rest out for debugging/development
        } else {
            $t_authenticated 		= false;
            $t_username             = $p_user_id;
            $t_ds                   = ldap_connect(LDAP_SERVER, LDAP_PORT);
# Attempt to bind with the DN and password
            $t_br = @ldap_bind( $t_ds, LDAP_PREFIX."\\".$t_username, $p_password );
            if ($t_br) {
                $t_authenticated = true;
            }
            @ldap_unbind( $t_ds );
            return $t_authenticated; 
        }
	}

    /**
     * Logs the user in with session variables for user groups etc. 
     *
     * @access  public
     * @param   string $username The username of the user (in ldap)
     * @param   string $password The password of the user (in ldap)
     * @return  boolean true if the user successfully binds to the LDAP server
     */
    function LoginAuthenticatedUser($username, $password) {	
        session_name(APP_SESSION);
        @session_start();
        if (!Auth::userExists($username)) { // If the user isn't a registered fez user, get their details elsewhere (The AD/LDAP) as they must have logged in with LDAP
            $_SESSION['isInAD'] = true;
            $_SESSION['isInDB'] = false;
            $userDetails = User::GetUserLDAPDetails($username, $password);
            $fullname = $userDetails['displayname'];
            $email = $userDetails['email'];
            Auth::GetUsersLDAPGroups($username, $password);
        } else { // if it is a registered Fez user then get their details from the fez user table
            $_SESSION['isInDB'] = true;
            $userDetails = User::getDetails($username);			
            $fullname = $userDetails['usr_full_name'];
            $email = $userDetails['usr_email'];
			$usr_id = User::getUserIDByUsername($username);
            User::updateLoginDetails($usr_id); //incremement login count and last login date
            if ($userDetails['usr_ldap_authentication'] == 1) {
	            $_SESSION['isInAD'] = true;			
                Auth::GetUsersLDAPGroups($userDetails['usr_username'], $password);
            }  else {
	            $_SESSION['isInAD'] = false;			
			}
            // get internal fez groups
			Auth::GetUsersInternalGroups($usr_id);
            
        }
        Auth::createLoginSession($username, $fullname, $email, $HTTP_POST_VARS["remember_login"]);
    }

    /**
     * Gets the internal Fez system groups the user belongs to. 
     *
     * @access  public
     * @param   string $usr_id The Fez internal user id of the user
     * @return  void Sets the internal groups session to the found internal groups
     */
	function GetUsersInternalGroups($usr_id) {
        session_name(APP_SESSION);
        @session_start();
		$internal_groups = Group::getGroupColList($usr_id);
		$_SESSION[APP_INTERNAL_GROUPS_SESSION] = $internal_groups;
	}

    /**
     * Is the user in the institutions AD/LDAP system?
     *
     * @access  public
     * @return  boolean true if in the AD/LDAP, false otherwise.
     */
    function isInAD()
    {
        return @$_SESSION['isInAD'];
    }

    /**
     * Is the user in the internal Fez system?
     *
     * @access  public
     * @return  boolean true if in the internal Fez system, false otherwise.
     */
    function isInDB()
    {
        return @$_SESSION['isInDB'];
    }

    /**
     * Checks and appends the security roles (authorisation groups) the user has over the object for the listing/search screens. 
     *
     * @access  public
     * @param   array $details The details returned from and index lookup
     * @return  array $details The details returned from and index lookup with appended security role checks
     */
    function ProcessListResults($details) {
		foreach ($details as $key => $row) {
			$xdis_array = Fedora_API::callGetDatastreamContentsField ($row['pid'], 'FezMD', array('xdis_id'));
            if (!empty($xdis_array)) {
                $xdis_id = $xdis_array['xdis_id'][0];
                $rowAuthGroups = Auth::getAuthorisationGroups($row['pid']);
                // get only the roles which are of relevance/use on the listing screen. This logic may be changed later.
                $details[$key]['isCommunityAdministrator'] = in_array('Community Administrator', $rowAuthGroups); //editor is only for the children. To edit the actual community record details you need to be a community admin
                $details[$key]['isEditor'] = in_array('Editor', $rowAuthGroups);
                $details[$key]['isViewer'] = in_array('Viewer', $rowAuthGroups);
                $details[$key]['isLister'] = in_array('Lister', $rowAuthGroups);
                //			$details[$key]['isApprover'] = in_array('Approver', $rowAuthGroups); // probably not necessary at the listing stage
            } 
		}
        return $details;
    }

    /**
     * Return the global default security roles
     *
     * @access  public
     * @return  array $defaultRoles
     */
    function getDefaultRoles() {
        global $defaultRoles;
        return $defaultRoles;
    }

    /**
     * Return the global default security role name of the given role id
     *
     * @access  public
     * @param integer $role_id
     * @return array $defaultRoles
     */
    function getDefaultRoleName($role_id) {
        global $defaultRoles;
        return $defaultRoles[$role_id];
    }
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Auth Class');
}
?>
