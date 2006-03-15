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
			foreach ( $returns[$pid] as $fezACML_row) {					
				array_push($ACMLArray, $fezACML_row); //add it to the acml array and dont go any further up the hierarchy
			}
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
						" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1
						inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on (r1.rmf_xsdmf_id = x1.xsdmf_id)
					    left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key k1 on (k1.sek_title = 'isMemberOf' AND k1.sek_id = x1.xsdmf_sek_id)
						left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d1 on (x1.xsdmf_xdis_id = d1.xdis_id)
						inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd as xsd1 on (xsd1.xsd_id = d1.xdis_xsd_id and xsd1.xsd_title = 'FezACML')
						left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
					 WHERE
						r1.rmf_rec_pid in ('$parent_pid_string') and (r1.rmf_dsid IS NULL or r1.rmf_dsid = '')
						";

			$securityfields = Auth::getAllRoles();
			$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
			$return = array();	
			foreach ($res as $result) {
				if (!is_array(@$return[$result['rmf_rec_pid']])) {
					$return[$result['rmf_rec_pid']]['exists'] = array();
				}
				if (in_array($result['xsdsel_title'], $securityfields) && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) ) {
					if (!is_array(@$return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
						$return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']] = array();
					}
					if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
						array_push($return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']], $result['rmf_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
					}
				}
			}
			foreach ($return as $key => $record) {
				if (is_array(@$record['FezACML'])) {
					if (!is_array(@$returns[$pid])) {
						$returns[$pid] = array();
					}
					foreach ($record['FezACML'] as $fezACML_row) {					
						array_push($ACMLArray, $fezACML_row); //add it to the acml array and dont go any further up the hierarchy
						array_push($returns[$pid], $fezACML_row);
					} 
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
//		$pid = $pid['pid'];
        if (is_array(@$returns[$pid])) {		
//			$ACMLArray = $returns[$pid]; //add it to the acml array and dont go any further up the hierarchy
//			array_push($ACMLArray, $returns[$pid]); //add it to the acml array and dont go any further up the hierarchy			
			foreach ($returns[$pid] as $fezACML_row) {					
				array_push($ACMLArray, $fezACML_row); //add it to the acml array and dont go any further up the hierarchy
			}


        } else {								
			$stmt = "SELECT 
						* 
					 FROM
						" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1
						inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on (r1.rmf_xsdmf_id = x1.xsdmf_id)
						inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d1 on (d1.xdis_id = x1.xsdmf_xdis_id and r1.rmf_rec_pid ='".$pid."')
						left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd x2 on (x2.xsd_id = d1.xdis_xsd_id and x2.xsd_title = 'FezACML')
						left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
						left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key k1 on (k1.sek_title = 'isMemberOf' AND r1.rmf_xsdmf_id = x1.xsdmf_id AND k1.sek_id = x1.xsdmf_sek_id)
						WHERE (r1.rmf_dsid IS NULL or r1.rmf_dsid = '')";
	
//          global $defaultRoles;
//			$returnfields = $defaultRoles;
//			echo $stmt;
			$securityfields = Auth::getAllRoles();
			$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
			$return = array();
			$returns = array();			

			foreach ($res as $result) {
				if (!is_array(@$return[$result['rmf_rec_pid']])) {
					$return[$result['rmf_rec_pid']]['exists'] = array();
				}
				if (in_array($result['xsdsel_title'], $securityfields)  && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) )  {
					if (!is_array(@$return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
						$return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']] = array();
					}
					if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
						array_push($return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']], $result['rmf_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
					}
				}
				if ($result['xsdmf_element'] == '!inherit_security') {
					if (!is_array(@$return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'])) {
						$return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'] = array();
					}
					if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'])) {
						array_push($return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'], $result['rmf_'.$result['xsdmf_data_type']]);
					}
				}								
			}
	
			foreach ($return as $key => $record) {	
				if (is_array(@$record['FezACML'])) {
					if (!is_array(@$returns[$pid])) {
						$returns[$pid] = array();
					}
					foreach ($record['FezACML'] as $fezACML_row) {
						array_push($ACMLArray, $fezACML_row);
						array_push($returns[$pid], $fezACML_row);
					}
					//add it to the acml array and dont go further up the hierarchy only if inherity security is set
					$parentACMLs = array();
					foreach ($record['FezACML'] as $fezACML_row) {
						if (@$fezACML_row['!inherit_security'][0] == "on") {
							Auth::getIndexParentACMLs($parentACMLs, $key);
						}					
					}
					foreach ($parentACMLs as $pACML) {
						array_push($ACMLArray, $pACML);
						array_push($returns[$pid], $pACML);
					}
				} else {
					if (!is_array(@$returns[$pid])) {
						$returns[$pid] = array();
					}
					$parentACMLs = array();
					Auth::getIndexParentACMLs($parentACMLs, $key);
					foreach ($parentACMLs as $pACML) {
						array_push($ACMLArray, $pACML);
						array_push($returns[$pid], $pACML);
					}
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
			$inherit = false;
			$xdis_array = Fedora_API::callGetDatastreamContentsField($parent['pid'], 'FezMD', array('xdis_id'));
			$xdis_id = $xdis_array['xdis_id'][0];
			$parentACML = Record::getACML($parent['pid']);		
            if ($parentACML != false) {
                array_push($ACMLArray, $parentACML); // add and then check if need to inherit				
                $xpath = new DOMXPath($parentACML);
                $inheritSearch = $xpath->query('/FezACML/inherit_security');
                $found_inherit_on = false;
                $found_inherit_off = false;
                $found_inherit_blank = false;
                // There shouldn't be more than one inherit_security node, but if there is, then turning inherit off
                // overrides any that turn it on. 
                foreach ($inheritSearch as $inheritRow) {
                    if ($inheritRow->nodeValue == "on") { 
                        $found_inherit_on = true;
                    } elseif (empty($inheritRow->nodeValue)) {
                        $found_inherit_blank = true;
                    } else {
                        $found_inherit_off = true;
                    }
                }
                $inherit = !$found_inherit_off && ($found_inherit_on || $found_inherit_blank);

                if ($inherit == true) { // if need to inherit
                    $superParents = Record::getParents($parent['pid']);
                    if ($superParents != false) {
                        Auth::getParentACMLs(&$ACMLArray, $superParents);
                    }
                }
            } else { // if no ACML found then assume inherit
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
    function checkAuthorisation($pid, $dsID, $acceptable_roles, $failed_url, $userPIDAuthGroups=null, $redirect=true) {
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
            $userPIDAuthGroups = Auth::getAuthorisationGroups($pid, $dsID);
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
		$securityfields = Auth::getAllRoles();
		foreach ($indexArray as $indexKey => $indexRecord) {
			$userPIDAuthGroups = $NonRestrictedRoles;
			$cleanedArray = array();
			if (!is_array(@$indexRecord['FezACML'])) {
//				return false;
				// if it doesnt have its own acml record try and get rights from its parents
//				Auth::getIndexAuthorisationGroups();
				// 1. get the parents records with their fez acml's
				// 2. if at least one of them have an fez acml then use it otherwise get the parents parents

			} else {		
				foreach ($indexRecord['FezACML'] as $FezACML) { // can have multiple fez acmls if got from parents
					foreach ($FezACML as $role_name => $role) {						
						if (in_array($role_name, $userPIDAuthGroups) && in_array($role_name, $NonRestrictedRoles) && (@$cleanedArray[$role_name] != 1)) {
							$userPIDAuthGroups = Misc::array_clean($userPIDAuthGroups, $role_name, false, true);
							$cleanedArray[$role_name] = 1;
						}
						if (in_array($role_name, $securityfields) && $role_name != '0') {
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
				}		
			}
			$indexArray[$indexKey]['isCommunityAdministrator'] = (in_array('Community Administrator', $userPIDAuthGroups) || Auth::isAdministrator()); //editor is only for the children. To edit the actual community record details you need to be a community admin
			$indexArray[$indexKey]['isEditor'] = (in_array('Editor', $userPIDAuthGroups) || $indexArray[$indexKey]['isCommunityAdministrator'] == true);
			$indexArray[$indexKey]['isArchivalViewer'] = (in_array('Archival_Viewer', $userPIDAuthGroups) || ($indexArray[$indexKey]['isEditor'] == true));
			$indexArray[$indexKey]['isViewer'] = (in_array('Viewer', $userPIDAuthGroups) || ($indexArray[$indexKey]['isEditor'] == true));
			$indexArray[$indexKey]['isLister'] = (in_array('Lister', $userPIDAuthGroups) || ($indexArray[$indexKey]['isViewer'] == true));
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
     * @param   string $dsID (optional) The datastream ID 
     * @returns array $userPIDAuthGroups The authorisation groups (roles) the user belongs to against this object.
    */
	function getAuthorisationGroups($pid, $dsID="") {
        static $roles_cache;
		$inherit = false;
		if ($dsID != "") {
	        if (isset($roles_cache[$pid][$dsID])) {
				return $roles_cache[$pid][$dsID];
			}
		} else {
			if (isset($roles_cache[$pid])) {
				return $roles_cache[$pid];
			}
		}
        $userPIDAuthGroups = array();

        $acmlBase = false;

		if ($dsID != "") {
	        $acmlBase = Record::getACML($pid, $dsID);
		}

		// if no FezACML exists for a datastream then it must inherit from the pid object
        if ($acmlBase == false) {
	        $acmlBase = Record::getACML($pid);
		}

        $ACMLArray = array();
        if ($acmlBase == false) { // no FezACML was found for DS or PID object so go to parents straight away (inherit presumed)
            $parents = Record::getParents($pid);
            Auth::getParentACMLs(&$ACMLArray, $parents);
        } else { // otherwise found something so use that and check if need to inherit

            $ACMLArray[0] = $acmlBase;
			// If found an ACML then check if it inherits security
			$xpath = new DOMXPath($acmlBase);
			$inheritSearch = $xpath->query('/FezACML/inherit_security');
            $found_inherit_on = false;
            $found_inherit_off = false;
            $found_inherit_blank = false;
            // There shouldn't be more than one inherit_security node, but if there is, then turning inherit off
            // overrides any that turn it on. 
			foreach ($inheritSearch as $inheritRow) {
				if ($inheritRow->nodeValue == "on") { 
                    $found_inherit_on = true;
                } elseif (empty($inheritRow->nodeValue)) {
                    $found_inherit_blank = true;
				} else {
                    $found_inherit_off = true;
                }
			}
            $inherit = !$found_inherit_off && ($found_inherit_on || $found_inherit_blank);
			if ($inherit == true) { // if need to inherit, check if at dsID level or not first and then 

				if ($dsID == "") { // if already at PID level just get parent pids and add them
					$parents = Record::getParents($pid);
					Auth::getParentACMLs(&$ACMLArray, $parents);				
				} else { // otherwise get the pid object first and check whether to inherit
					$acmlBase = Record::getACML($pid);
					if ($acmlBase == false) { // if pid level doesnt exist go higher
						$parents = Record::getParents($pid);
						Auth::getParentACMLs(&$ACMLArray, $parents);
					} else { // otherwise found pid level so add to ACMLArray and check whether to inherit or not
				    	array_push($ACMLArray, $acmlBase);
						// If found an ACML then check if it inherits security
						$inherit = false;
						$xpath = new DOMXPath($acmlBase);
						$inheritSearch = $xpath->query('/FezACML/inherit_security');
						foreach ($inheritSearch as $inheritRow) {
							if ($inheritRow->nodeValue == "on") {
								$inherit = true;
							}
						}
						if ($inherit == true) {
							$parents = Record::getParents($pid);
							Auth::getParentACMLs(&$ACMLArray, $parents);				
						}
					}
				}
			}
        }
        // Usually everyone can list, view and view comments
        global $NonRestrictedRoles;
        $userPIDAuthGroups = $NonRestrictedRoles;
        // loop through the ACML docs found for the current pid or in the ancestry
		$cleanedArray = array();
        foreach ($ACMLArray as &$acml) {
	        // Usually everyone can list, view and view comments - these need to be reset for each ACML loop
			// because they are presumed ok first
		    $userPIDAuthGroups = Misc::array_merge_values($userPIDAuthGroups, $NonRestrictedRoles);
            // Use XPath to find all the roles that have groups set and loop through them
            $xpath = new DOMXPath($acml);
            $roleNodes = $xpath->query('/FezACML/rule/role');
            foreach ($roleNodes as $roleNode) {
                $role = $roleNode->getAttribute('name');
                //echo $acml->saveXML($roleNode);
                // Use XPath to get the sub groups that have values
                $groupNodes = $xpath->query('./*[string-length(normalize-space()) > 0]', $roleNode); /* */
                if ($groupNodes->length) {
//                    echo $role;
                    // if the role is in the ACML then it is restricted so remove it
                    if (in_array($role, $userPIDAuthGroups) && in_array($role, $NonRestrictedRoles) && (@$cleanedArray[$role] != true)) {
                        $userPIDAuthGroups = Misc::array_clean($userPIDAuthGroups, $role, false, true);
						$cleanedArray[$role] = true;
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
		if ($dsID != "") {
		    $roles_cache[$pid][$dsID] = $userPIDAuthGroups;
		} else {			
	        $roles_cache[$pid] = $userPIDAuthGroups;
		}
//		print_r($userPIDAuthGroups);
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
            } elseif (count($info) != 1) {
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
        if (APP_TEST === "true") {
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
        if (APP_TEST === "true") {
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
     * Retrieves an array of Shibboleth Federation IDPs for display in the Fez WAYF 
     *
     * @access  public
     * @return  array 
     */
    function getIDPList() {	
		if (is_file(SHIB_WAYF_METADATA_LOCATION) == true) {		
			$sourceXML = fopen(SHIB_WAYF_METADATA_LOCATION, "r");
			$sourceXMLRead = '';
			while ($tmp = fread($sourceXML, 4096)) {
				$sourceXMLRead .= $tmp;
			}
			$xmlDoc= new DomDocument();
			$xmlDoc->preserveWhiteSpace = false;
			$xmlDoc->loadXML($sourceXMLRead);
			$xpath = new DOMXPath($xmlDoc);
			$xpath->registerNamespace("md", "urn:oasis:names:tc:SAML:2.0:metadata");
			$xpath->registerNamespace("shib","urn:mace:shibboleth:metadata:1.0");
			$recordNodes = $xpath->query("//md:EntitiesDescriptor/md:EntityDescriptor");
			$IDPArray = array();
			foreach ($recordNodes as $recordNode) {
				$type_fields = $xpath->query("./md:IDPSSODescriptor", $recordNode);
				$foundIDP = false;
				foreach ($type_fields as $type_field) {
					$foundIDP = true;
				}				
				if ($foundIDP == true) {
					$entityID = "";
					$type_fields = $xpath->query("./@entityID[string-length(.) > 0]", $recordNode);
					foreach ($type_fields as $type_field) {
						if  ($entityID == "") {
							$entityID = $type_field->nodeValue;
						}
					}
					$OrganisationDisplayName = "";
					$type_fields = $xpath->query("./md:Organization/md:OrganizationDisplayName", $recordNode);
					foreach ($type_fields as $type_field) {
						if  ($OrganisationDisplayName == "") {
							$OrganisationDisplayName = $type_field->nodeValue;
						}
					}
					$SSO = "";
					$type_fields = $xpath->query("./md:IDPSSODescriptor/md:Extensions/shib:Scope", $recordNode);
					foreach ($type_fields as $type_field) {
						if  ($SSO == "") {
							$SSO = $type_field->nodeValue;
						}
					}

					if ($OrganisationDisplayName != "" && $entityID != "" && $SSO != "") {
						$IDPArray['list'][$entityID] = $OrganisationDisplayName;
						$IDPArray['SSO'][$entityID] = "https://$SSO/shibboleth-idp/SSO";
					}
				}			
			}
			return $IDPArray;
		} else {
			return array(); //if the file cannot be found return an empty array
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
    function LoginAuthenticatedUser($username, $password, $shib_login=false) {	
        session_name(APP_SESSION);
        @session_start();
        if (!Auth::userExists($username)) { // If the user isn't a registered fez user, get their details elsewhere (The AD/LDAP) as they must have logged in with LDAP
			if ($_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['Shib-Attributes'] != "") {
				$_SESSION['isInAD'] = true;
				$_SESSION['isInDB'] = false;
				$_SESSION['isInFederation'] = true;			
				if ($_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['displayName'] != "") {
					$fullname =	$_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['displayName'];
				} elseif ($_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'] != "") {
					$fullname =	$_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'];
				} elseif ($_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'] != "") {
					$fullname =	$_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'];
				} elseif ($_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-Nickname'] != "") {
					$fullname =	$_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-Nickname'];
				}
				if ($_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['Shib-Person-mail'] != "") {
					$email = $_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['Shib-Person-mail'];
				} else {				
					$email = "";
				}
			} else {
				$_SESSION['isInAD'] = true;
				$_SESSION['isInDB'] = false;
				$_SESSION['isInFederation'] = false;
				$userDetails = User::GetUserLDAPDetails($username, $password);
				$fullname = $userDetails['displayname'];
				$email = $userDetails['email'];
				Auth::GetUsersLDAPGroups($username, $password);
			}
        } else { // if it is a registered Fez user then get their details from the fez user table
            $_SESSION['isInDB'] = true;
			if ($_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['Shib-Attributes'] != "") {
				$_SESSION['isInFederation'] = true;
			} else {
				$_SESSION['isInFederation'] = false;
			}
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
     * Gets the Shibboleth attributes. 
     *
     * @access  public
     * @return  void Sets the internal shib attributes session to the found shib attributes
     */
	function GetShibAttributes() {
        session_name(APP_SESSION);
        @session_start();
	    $headers = apache_request_headers();
		//$shib_attributes = $headers['Shib-Attributes'];

		$_SESSION[APP_SHIB_ATTRIBUTES_SESSION] = $headers;
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
                $details[$key]['isCommunityAdministrator'] = (in_array('Community Administrator', $rowAuthGroups) || Auth::isAdministrator()); //editor is only for the children. To edit the actual community record details you need to be a community admin
                $details[$key]['isEditor'] = (in_array('Editor', $rowAuthGroups) || $details[$key]['isCommunityAdministrator'] == true);
                $details[$key]['isArchivalViewer'] = (in_array('Archival_Viewer', $rowAuthGroups) || $details[$key]['isEditor'] == true);
                $details[$key]['isViewer'] = (in_array('Viewer', $rowAuthGroups) || $details[$key]['isEditor'] == true);
                $details[$key]['isLister'] = (in_array('Lister', $rowAuthGroups) || $details[$key]['isViewer'] == true);
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
