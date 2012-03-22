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
 * Class to handle authentication and authorisation issues.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.masquerade.php");

global $NonRestrictedRoles;
$NonRestrictedRoles = array("Viewer","Lister","Comment_Viewer");
global $NonRestrictedRoleIDs;
$NonRestrictedRoleIDs = array(10,9,5);
global $defaultRoles;
$defaultRoles = array("Editor", "Creator", "Lister", "Viewer", "Approver", "Community Administrator", "Annotator", "Comment_Viewer", "Commentor");
global $defaultRoleIDs;
$defaultRoleIDs = array(8, 7, 5, 9, 2, 6, 1, 5, 4);

global $auth_isBGP;
global $auth_bgp_session;

$auth_isBGP = false;
$auth_bgp_session = array();

class Auth
{
	/**
	 * Method used to get the current listing related cookie information for the users shibboleth home idp
	 *
	 * @access  public
	 * @return  array The Record listing information
	 */
	function getHomeIDPCookie() 
	{
		return @unserialize(base64_decode($_COOKIE[APP_SHIB_HOME_IDP_COOKIE]));
	}


	function setHomeIDPCookie($home_idp) 
	{
		$encoded = base64_encode(serialize($home_idp));
		@setcookie(APP_SHIB_HOME_IDP_COOKIE, $encoded, APP_SHIB_HOME_IDP_COOKIE_EXPIRE);
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
		$log = FezLog::get();
		$db = DB_API::get();
		
		
		global $auth_isBGP, $auth_bgp_session;

		if ($auth_isBGP) {
			$ses =& $auth_bgp_session;
		} else {
			session_name(APP_SESSION);
			@session_start();
			$ses =& $_SESSION;
			if (empty($failed_url)) {
				$failed_url = APP_RELATIVE_URL . "login.php?err=5";
			} else {
				$failed_url = base64_encode($failed_url);
				$failed_url = APP_RELATIVE_URL . "login.php?err=21&url=".$failed_url;
			}

			//			echo $failed_url; exit;
			if (!Auth::isValidSession($_SESSION)) {
				Auth::removeSession($session_name);
				Auth::redirect($failed_url, $is_popup);
			}

		}
		Auth::checkRuleGroups();
		// if the current session is still valid, then renew the expiration
		Auth::createLoginSession($ses['username'], $ses['fullname'], $ses['email'], $ses['distinguishedname'], $ses['autologin'], $ses['acting_username']);
	}


	/**
	 * Method used to get the list of FezACML roles using in any XSD Display.
	 *
	 * @access  public
	 * @return  array The list of FezACML roles
	 */
	function getAllRoleIDs() 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		

		$stmt = "SELECT aro_id, aro_role FROM ". APP_TABLE_PREFIX . "auth_roles ";

		try {
			$res = $db->fetchAll($stmt, array());
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}

		$result = array();
		foreach ($res as $key => $data) {
			$result[$data['aro_role']] = $data['aro_id'];
		}
		return $result;
	}

	/**
	 * Method used to get the list of FezACML roles using in any XSD Display.
	 *
	 * @access  public
	 * @return  array The list of FezACML roles
	 */
	function getAssocRoleIDs() 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		

		$stmt = "SELECT aro_id, aro_role FROM ". APP_TABLE_PREFIX . "auth_roles where aro_id != 0";
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}

	function convertTextRolesToIDS($aro_roles = array()) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		

		if (is_array($aro_roles)) {
			if (count($aro_roles) > 0) {
				$stmt = "SELECT aro_id, aro_role
		                  FROM ". APP_TABLE_PREFIX . "auth_roles 
		                  WHERE aro_role in (".Misc::arrayToSQLBindStr($aro_roles).") 
		                        AND aro_id != 0";
				try {
					$res = $db->fetchPairs($stmt, $aro_roles);
				}
				catch(Exception $ex) {
					$log->err($ex);
					return array();
				}
				return $res;
			} else {
				return array();
			}
		} else {
			return array();
		}
	}

	function getRoleIDByTitle($title) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		

		$stmt = "SELECT aro_id FROM " . APP_TABLE_PREFIX . "auth_roles where aro_role = ?";
		try {
			$res = $db->fetchOne($stmt, $title);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}

	function getRoleTitleByID($aro_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		

		if (!is_numeric($aro_id)) {
			return false;
		}
		$stmt = "SELECT aro_role FROM ". APP_TABLE_PREFIX . "auth_roles where aro_id = ".$db->quote($aro_id, 'INTEGER');
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}

	/**
	 * Method used to get the list of FezACML roles using in any XSD Display.
	 *
	 * @access  public
	 * @return  array The list of FezACML roles
	 */
	function getAllRoles() 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT distinct xsdsel_title
			from " . APP_TABLE_PREFIX . "xsd_loop_subelement s1
			inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on x1.xsdmf_id = xsdsel_xsdmf_id
			inner join " . APP_TABLE_PREFIX . "xsd_display d1 on d1.xdis_id = x1.xsdmf_xdis_id
			inner join " . APP_TABLE_PREFIX . "xsd x2 on x2.xsd_id = xdis_xsd_id and x2.xsd_title = 'FezACML'";
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
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
	function getIndexParentACMLs(&$array, $pid) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$ACMLArray = &$array;
		static $returns;
		if (!empty($returns[$pid])) { // check if this has already been found and set to a static variable
			foreach ( $returns[$pid] as $fezACML_row) {
				array_push($ACMLArray, $fezACML_row); //add it to the acml array and dont go any further up the hierarchy
			}
		} else {
			$pre_stmt =  "SELECT r2.rek_ismemberof
							FROM  " . APP_TABLE_PREFIX . "record_search_key_ismemberof r2
							WHERE r2.rek_ismemberof_pid = '".$pid."')";

			$res = array();
			try {
				$res = $db->fetchCol($pre_stmt, array($pid));
			}
			catch(Exception $ex) {
				$log->err($ex);
			}

			$stmt = "SELECT
						* 
					 FROM
						" . APP_TABLE_PREFIX . "record_matching_field r1
						inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on (r1.rek_xsdmf_id = x1.xsdmf_id)
					    left join " . APP_TABLE_PREFIX . "search_key k1 on (k1.sek_title = 'isMemberOf' AND k1.sek_id = x1.xsdmf_sek_id)
						left join " . APP_TABLE_PREFIX . "xsd_display d1 on (x1.xsdmf_xdis_id = d1.xdis_id)
						inner join " . APP_TABLE_PREFIX . "xsd as xsd1 on (xsd1.xsd_id = d1.xdis_xsd_id and xsd1.xsd_title = 'FezACML')
						left join " . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
					 WHERE
						r1.rek_pid in (".Misc::arrayToSQLBindStr($res).") and (r1.rek_dsid IS NULL or r1.rek_dsid = '')
						"; 

			$securityfields = Auth::getAllRoles();

			try {
				$res = $db->fetchAll($stmt, $res);
			}
			catch(Exception $ex) {
				$log->err($ex);
			}
			$return = array();
			foreach ($res as $result) {
				if (!is_array(@$return[$result['rek_pid']])) {
					$return[$result['rek_pid']]['exists'] = array();
				}
				if (in_array($result['xsdsel_title'], $securityfields) && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) ) {
					if (!is_array(@$return[$result['rek_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
						$return[$result['rek_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']] = array();
					}
					if (!in_array($result['rek_'.$result['xsdmf_data_type']], $return[$result['rek_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
						array_push($return[$result['rek_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']], $result['rek_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
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
	function getIndexParentACMLMemberList(&$array, $pid, $parents) 
	{
		$log = FezLog::get();

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
	function getIndexParentACMLMember(&$array, $pid) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$ACMLArray = &$array;
		static $returns;
		if (is_array($pid)) {
			$pid = $pid['pid'];
		}

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
						" . APP_TABLE_PREFIX . "record_matching_field r1
						inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on (r1.rek_xsdmf_id = x1.xsdmf_id)
						inner join " . APP_TABLE_PREFIX . "xsd_display d1 on (d1.xdis_id = x1.xsdmf_xdis_id and r1.rek_pid_num=".$db->quote(Misc::numPID($pid), 'INTEGER')." and r1.rek_pid =".$db->quote($pid).")
						left join " . APP_TABLE_PREFIX . "xsd x2 on (x2.xsd_id = d1.xdis_xsd_id and x2.xsd_title = 'FezACML')
						left join " . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
						left join " . APP_TABLE_PREFIX . "search_key k1 on (k1.sek_title = 'isMemberOf' AND r1.rek_xsdmf_id = x1.xsdmf_id AND k1.sek_id = x1.xsdmf_sek_id)
						WHERE (r1.rek_dsid IS NULL or r1.rek_dsid = '')";

			$securityfields = Auth::getAllRoles();
			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
			}

			$return = array();
			$returns = array();

			foreach ($res as $result) {
				if (!is_array(@$return[$result['rek_pid']])) {
					$return[$result['rek_pid']]['exists'] = array();
				}
				if (in_array($result['xsdsel_title'], $securityfields)  && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) )  {
					if (!is_array(@$return[$result['rek_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
						$return[$result['rek_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']] = array();
					}
					if (!in_array($result['rek_'.$result['xsdmf_data_type']], $return[$result['rek_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
						array_push($return[$result['rek_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']], $result['rek_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
					}
				}
				if ($result['xsdmf_element'] == '!inherit_security') {
					if (!is_array(@$return[$result['rek_pid']]['FezACML'][0]['!inherit_security'])) {
						$return[$result['rek_pid']]['FezACML'][0]['!inherit_security'] = array();
					}
					if (!in_array($result['rek_'.$result['xsdmf_data_type']], $return[$result['rek_pid']]['FezACML'][0]['!inherit_security'])) {
						array_push($return[$result['rek_pid']]['FezACML'][0]['!inherit_security'], $result['rek_'.$result['xsdmf_data_type']]);
					}
				}
			}
			foreach ($return as $key => $record) {
				if (is_array(@$record['FezACML'])) {
					if (!is_array(@$returns[$pid]) || count(@$returns[$pid]) > 10) {
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
					if (!is_array(@$returns[$pid]) || count(@$returns[$pid]) > 10) {
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
	function getParentACMLs(&$array, $parents) 
	{
		$log = FezLog::get();

		if (!is_array($parents)) {
			return false;
		}

		$ACMLArray = &$array;
		foreach ($parents as $parent) {
			$inherit = false;
			$parentACML = Record::getACML($parent);

			if ($parentACML != false) {
					
				array_push($ACMLArray, $parentACML);

				// Check if it inherits security
				$xpath = new DOMXPath($parentACML);
				$anyRuleSearch = $xpath->query('/FezACML/rule/role/*[string-length(normalize-space()) > 0]');
				if ($anyRuleSearch->length == 0) {

					$inherit = true;

				} else {
					$inheritSearch = $xpath->query('/FezACML[inherit_security="on" or inherit_security=""]');

					if( $inheritSearch->length > 0 ) {
						$inherit = true;
					}
				}

				if ($inherit == true) { // if need to inherit
					$superParents = Record::getParents($parent);
					if ($superParents != false) {
						Auth::getParentACMLs(&$ACMLArray, $superParents);
					}
				}
			} else { // if no ACML found then assume inherit
				$superParents = Record::getParents($parent);
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
		$log = FezLog::get();
			
		global $auth_isBGP, $auth_bgp_session;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			session_name(APP_SESSION);
			@session_start();
			$session =& $_SESSION;
		}

		$answer = false;
		if (Auth::isValidSession($session)) {		
			if (!isset($session['isAdministrator'])) {				
				$session['isAdministrator'] = User::isUserAdministrator(Auth::getUsername());
			}
			$answer = $session['isAdministrator']?true:false;
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
	function checkAuthorisation($pid, $dsID, $acceptable_roles, $failed_url, $userPIDAuthGroups=null, $redirect=true) 
	{
		$log = FezLog::get();

		global $auth_isBGP, $auth_bgp_session;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			session_name(APP_SESSION);
			@session_start();
			$session =& $_SESSION;
		}

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
		if (is_array($userPIDAuthGroups)) {
			foreach ($acceptable_roles as $role) {
				if (in_array($role, $userPIDAuthGroups)) {
					$auth_ok = true;
				}
			}
		}
		if ($auth_ok != true) {

			// Perhaps the user hasn't logged in
			if (!Auth::isValidSession($session)) {

# this is wrong as it only works for one IP address and not the pool
# The rest of the system should be audited for other references to Basic Auth that don't work for more than one IP as well
#				if (defined('APP_BASIC_AUTH_IP') && ($_SERVER['REMOTE_ADDR'] == APP_BASIC_AUTH_IP)) {
                $ipPool = array();
                if (defined('APP_BASIC_AUTH_IP')) {
                    $ipPool = Auth::getBasicAuthIPs();
                }

                # Check pool of Basic Auth IP addresses
				if (defined('APP_BASIC_AUTH_IP') && (in_array($_SERVER['REMOTE_ADDR'], $ipPool))) {
					if ((($_SERVER["SERVER_PORT"] != 443) && (APP_HTTPS == "ON"))) { //should be ssl when using basic auth
						header ("Location: https://".APP_HOSTNAME.APP_RELATIVE_URL."view/".$_GET['pid']);
						exit;        		
					}
					if (!isset($_SERVER['PHP_AUTH_USER'])) {
					    header('WWW-Authenticate: Basic realm="'.APP_HOSTNAME.'"');
					    header('HTTP/1.0 401 Unauthorized');
					    echo 'You must login to access this service';
					    exit;
					} else {
						// Check for basic authentication (over ssl) to bypass authorisation and login the user coming directly to eserv.php (and bypass usual login)
						if (!Auth::isValidSession($session)) { // if user not already logged in
							//print_r($_SERVER); exit;
				        	if (isset($_SERVER['PHP_AUTH_USER'])) { // check to see if there is some basic auth login..
								$username = $_SERVER['PHP_AUTH_USER'];
								$pw = $_SERVER['PHP_AUTH_PW'];
				        		if (Auth::isCorrectPassword($username, $pw)) {
				        			Auth::LoginAuthenticatedUser($username, $pw, false);
									header ("Location: https://".APP_HOSTNAME.APP_RELATIVE_URL."view/".$_GET['pid']);
									exit;
				        		} else {
				        			header('WWW-Authenticate: Basic realm="'.APP_HOSTNAME.'"');
				    				header('HTTP/1.0 401 Unauthorized');
				    				exit;
				        		}
				        	}
						}
					}
				} else {
					if ($redirect != false) {
						$failed_url = base64_encode($failed_url);
						Auth::redirect(APP_RELATIVE_URL . "login.php?err=21&url=".$failed_url, $is_popup);
					}
				}				
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	function getAuthorisation(&$indexArray) 
	{
		$log = FezLog::get();

		$userPIDAuthGroups = $indexArray['FezACML'];
		$editor_matches = array_intersect(explode(',',APP_EDITOR_ROLES), $userPIDAuthGroups);
		$creator_matches = array_intersect(explode(',',APP_CREATOR_ROLES), $userPIDAuthGroups);
		$approver_matches = array_intersect(explode(',',APP_APPROVER_ROLES), $userPIDAuthGroups);

		$indexArray['isCommunityAdministrator'] = (in_array('Community Administrator', $userPIDAuthGroups) || Auth::isAdministrator()); //editor is only for the children. To edit the actual community record details you need to be a community admin
		$indexArray['isApprover'] = (!empty($approver_matches) || $indexArray['isCommunityAdministrator'] == true);
		$indexArray['isEditor'] = (!empty($editor_matches) || $indexArray['isCommunityAdministrator'] == true);
		$indexArray['isCreator'] = (!empty($creator_matches) || $indexArray['isCommunityAdministrator'] == true);
		$indexArray['isArchivalViewer'] = (in_array('Archival_Viewer', $userPIDAuthGroups) || ($indexArray['isEditor'] == true));
		$indexArray['isViewer'] = (in_array('Viewer', $userPIDAuthGroups) || ($indexArray['isEditor'] == true));
		$indexArray['isLister'] = (in_array('Lister', $userPIDAuthGroups) || ($indexArray['isViewer'] == true));

		return $indexArray;
	}

	function getIndexAuthCascade($indexArray) 
	{
		$log = FezLog::get();

		$isAdministrator = Auth::isAdministrator();
		foreach ($indexArray as $indexKey => $indexRecord) {

			if (array_key_exists('authi_role', $indexRecord)) {
				$editor_matches = array_intersect(explode(',',APP_EDITOR_ROLE_IDS), $indexRecord["authi_role"]);
				$creator_matches = array_intersect(explode(',',APP_CREATOR_ROLE_IDS), $indexRecord["authi_role"]);
				$approver_matches = array_intersect(explode(',',APP_APPROVER_ROLE_IDS), $indexRecord["authi_role"]);
				$userPIDAuthGroups = $indexRecord["authi_role"];
			} else {
				$editor_matches = array();
				$userPIDAuthGroups = array();
			}

			$indexArray[$indexKey]['isCommunityAdministrator'] = (in_array(6, $userPIDAuthGroups) || $isAdministrator); //editor is only for the children. To edit the actual community record details you need to be a community admin
			$indexArray[$indexKey]['isEditor'] = (!empty($editor_matches) || $indexArray[$indexKey]['isCommunityAdministrator'] == true);
			$indexArray[$indexKey]['isCreator'] = (!empty($creator_matches) || $indexArray[$indexKey]['isCommunityAdministrator'] == true);
			$indexArray[$indexKey]['isApprover'] = (!empty($approver_matches) || $indexArray[$indexKey]['isCommunityAdministrator'] == true);
			$indexArray[$indexKey]['isArchivalViewer'] = (in_array(3, $userPIDAuthGroups) || ($indexArray[$indexKey]['isEditor'] == true));
			$indexArray[$indexKey]['isViewer'] = (in_array(10, $userPIDAuthGroups) || ($indexArray[$indexKey]['isEditor'] == true));
			$indexArray[$indexKey]['isLister'] = (in_array(9, $userPIDAuthGroups) || ($indexArray[$indexKey]['isViewer'] == true));
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
	function getAuthorisationGroups($pid, $dsID="") 
	{
		$log = FezLog::get();

		global $auth_isBGP, $auth_bgp_session;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			session_name(APP_SESSION);
			@session_start();
			$session =& $_SESSION;
		}
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
		// Usually everyone can list, view and view comments
		global $NonRestrictedRoles;
		$userPIDAuthGroups = $NonRestrictedRoles;
		$usingDS = false;
		$acmlBase = false;
        $overridetmp = array();

		if ($dsID != "") {
			$usingDS = true;
			$acmlBase = Record::getACML($pid, $dsID);
		}

		// if no FezACML exists for a datastream then it must inherit from the pid object
		if ($acmlBase == false) {
			$usingDS = false;
			$acmlBase = Record::getACML($pid);
		}
		$ACMLArray = array();

		// no FezACML was found for DS or PID object
		// so go to parents straight away (inherit presumed)
		if ($acmlBase == false) {
			$parents = Record::getParents($pid);
			Auth::getParentACMLs(&$ACMLArray, $parents);
		} else { // otherwise found something so use that and check if need to inherit

			$ACMLArray[0] = $acmlBase;

			// Check if it inherits security
			$xpath = new DOMXPath($acmlBase);
			$anyRuleSearch = $xpath->query('/FezACML/rule/role/*[string-length(normalize-space()) > 0]');
			if ($anyRuleSearch->length == 0) {

				$inherit = true;

			} else {
				$inheritSearch = $xpath->query('/FezACML[inherit_security="on" or inherit_security=""]');

				if( $inheritSearch->length > 0 ) {
					$inherit = true;
				}
			}

			if ($inherit == true) { // if need to inherit, check if at dsID level or not first and then

        if ($dsID != '' && $acmlBase != false) {
          $userPIDAuthGroups["security"] = "include";
        } else {
          $userPIDAuthGroups["security"] = "inherit";
        }


				// if already at PID level just get parent pids and add them
				if (($dsID == "") || ($usingDS == false)) {
					$parents = Record::getParents($pid);
					Auth::getParentACMLs(&$ACMLArray, $parents);
				} else { // otherwise get the pid object first and check whether to inherit
					$acmlBase = Record::getACML($pid);
					if ($acmlBase == false) { // if pid level doesnt exist go higher
						$parents = Record::getParents($pid);
						Auth::getParentACMLs(&$ACMLArray, $parents);
					} else { // otherwise found pid level so add to ACMLArray and check whether to inherit or not
            $userPIDAuthGroups["security"] = "include";
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
			} else {
				$userPIDAuthGroups["security"] = "exclude";
			}
		}

		// loop through the ACML docs found for the current pid or in the ancestry
		$cleanedArray = array();
		$overrideAuth = array();
		$datastreamQuickAuth = false;
		foreach ($ACMLArray as &$acml) {
			// Usually everyone can list, view and view comments - these need to be reset for each ACML loop
			// because they are presumed ok first
			//$userPIDAuthGroups = Misc::array_merge_values($userPIDAuthGroups, $NonRestrictedRoles);
			// Use XPath to find all the roles that have groups set and loop through them
			$xpath = new DOMXPath($acml);
			$roleNodes = $xpath->query('/FezACML/rule/role');
			$inheritSearch = $xpath->query('/FezACML[inherit_security="on"]');
			$inherit = false;
			if( $inheritSearch->length > 0 ) {
				$inherit = true;
			}

			$datastreamSearch = $xpath->query('/FezACML/datastream_quickauth_template[.>0]');
			if( $datastreamSearch->length > 0 ) {
				foreach ($datastreamSearch as $dsSearchNode) {
					if ($datastreamQuickAuth == false) {
						$datastreamQuickAuth = $dsSearchNode->nodeValue;
					}
				}
			}
            
			foreach ($roleNodes as $roleNode) {
				$role = $roleNode->getAttribute('name');
				// Use XPath to get the sub groups that have values
				$groupNodes = $xpath->query('./*[string-length(normalize-space()) > 0]', $roleNode);

				/*
				 * Empty rules override non-empty rules. Example:
				 * If a pid belongs to 2 collections, 1 collection has lister restricted to fez users
				 * and 1 collection has no restriction for lister, we want no restrictions for lister
				 * for this pid.
				 */
				if($groupNodes->length == 0 && ($role == "Viewer" || $role == "Lister") && $inherit == false) {
					$overridetmp[$role] = true;
				}

				foreach ($groupNodes as $groupNode) {
					$group_type = $groupNode->nodeName;
					$group_values = explode(',', $groupNode->nodeValue);
					foreach ($group_values as $group_value) {

						$group_value = trim($group_value, ' ');
							
						// if the role is in the ACML with a non 'off' value
						// and not empty value then it is restricted so remove it
						if ($group_value != "off" && $group_value != "" && in_array($role, $userPIDAuthGroups) && in_array($role, $NonRestrictedRoles) && (@$cleanedArray[$role] != true)) {
							$userPIDAuthGroups = Misc::array_clean($userPIDAuthGroups, $role, false, true);
							$cleanedArray[$role] = true;
							$overridetmp[$role] = false;

						} elseif(($group_value == "" || $group_value == "off")
						&& ($role == "Viewer" || $role == "Lister")) {

							if(array_key_exists($role, $overridetmp) && $overridetmp[$role] !== false) {
								$overridetmp[$role] = true;
							}

						} elseif( $group_value != "off" && $group_value != "" ) {
							$overridetmp[$role] = false;
						}
							
						// @@@ CK - if the role has already been
						// found then don't check for it again
						if (!in_array($role, $userPIDAuthGroups)) {
							switch ($group_type) {
								case 'AD_Group':
									if (@in_array($group_value, $session[APP_LDAP_GROUPS_SESSION])) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'in_AD':
									if (($group_value == 'on') && Auth::isValidSession($session)
									&& Auth::isInAD()) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'in_Fez':
									if (($group_value == 'on') && Auth::isValidSession($session)
									&& Auth::isInDB()) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'AD_User':
									if (Auth::isValidSession($session)
									&& $group_value == Auth::getUsername()) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'AD_DistinguishedName':
									if (is_numeric(strpos(@$session['distinguishedname'], $group_value))) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'eduPersonTargetedID':
									if (is_numeric(strpos(@$session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'], $group_value))) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'eduPersonAffiliation':
									if (is_numeric(strpos(@$session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-UnscopedAffiliation'], $group_value))) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'eduPersonScopedAffiliation':
									if (is_numeric(strpos(@$session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-ScopedAffiliation'], $group_value))) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'eduPersonPrimaryAffiliation':
									if (is_numeric(strpos(@$session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrimaryAffiliation'], $group_value))) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'eduPersonPrincipalName':
									if (is_numeric(strpos(@$session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'], $group_value))) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'eduPersonOrgUnitDN':
									if (is_numeric(strpos(@$session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-OrgUnitDN'], $group_value))) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'eduPersonOrgDN':
									if (is_numeric(strpos(@$session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-OrgDN'], $group_value))) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								case 'eduPersonPrimaryOrgUnitDN':
									if (is_numeric(strpos(@$session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrimaryOrgUnitDN'], $group_value))) {
										array_push($userPIDAuthGroups, $role);
									}
									break;

								case 'Fez_Group':
									if (@in_array($group_value, $session[APP_INTERNAL_GROUPS_SESSION])) {
										array_push($userPIDAuthGroups, $role);
									}
									break;

								case 'Fez_User':
									if (Auth::isValidSession($session) && $group_value == Auth::getUserID()) {
										array_push($userPIDAuthGroups, $role);
									}
									break;
								default:
									break;
							}
						}
					}
				}

				// If all groups rules were empty $overridetmp for this role will be true
				// Therefore we want this rule to be enabled for this user
				if(array_key_exists($role, $overridetmp) && $overridetmp[$role] == true && $inherit == false) {
					$overrideAuth[$role] = true;
				}

				$overridetmp = array();
			}
		}



		if (in_array('Community_Administrator', $userPIDAuthGroups) && !in_array('Editor', $userPIDAuthGroups)) {
			array_push($userPIDAuthGroups, "Editor");
		}
		if (in_array('Community_Administrator', $userPIDAuthGroups) && !in_array('Creator', $userPIDAuthGroups)) {
			array_push($userPIDAuthGroups, "Creator");
		}
		if (in_array('Community_Administrator', $userPIDAuthGroups) && !in_array('Approver', $userPIDAuthGroups)) {
			array_push($userPIDAuthGroups, "Approver");
		}
		if (in_array('Editor', $userPIDAuthGroups) && !in_array('Archival_Viewer', $userPIDAuthGroups)) {
			array_push($userPIDAuthGroups, "Archival_Viewer");
		}
		if ((in_array('Editor', $userPIDAuthGroups) && !in_array('Viewer', $userPIDAuthGroups)) || (array_key_exists('Viewer', $overrideAuth) && $overrideAuth['Viewer'] == true)) {
			array_push($userPIDAuthGroups, "Viewer");
		}
		if ((in_array('Viewer', $userPIDAuthGroups) && !in_array('Lister', $userPIDAuthGroups)) || (array_key_exists('Lister', $overrideAuth) && $overrideAuth['Lister'] == true)) {
			array_push($userPIDAuthGroups, "Lister");
		}
		if ($datastreamQuickAuth != false) {
			$userPIDAuthGroups["datastreamQuickAuth"] = $datastreamQuickAuth;
		} else {
			$userPIDAuthGroups["datastreamQuickAuth"] = false;
		}

		/*
		 * Special Auth Case (This isn't set via the interface)
		 * If a user has creator rights, the pid isn't 'submitted for approval'
		 * and the user is assigned to this pid, then they can edit it
		 */
		if(!in_array("Editor", $userPIDAuthGroups)) {
			if(in_array("Creator", $userPIDAuthGroups)) {
				$status = Record::getSearchKeyIndexValue($pid, "Status", false);
				$assigned_user_ids = Record::getSearchKeyIndexValue($pid, "Assigned User ID", false);

				if(in_array(Auth::getUserID(), $assigned_user_ids) && $status != Status::getID("Submitted for Approval") && $status != Status::getID("Published")) {
					array_push($userPIDAuthGroups, "Editor");
				}
			}
		}

		/*
		 * Special Auth Case (This isn't set via the interface)
		 * If a user has approver rights, the pid isn't 'published'
		 * then they can delete it (get community admin rights)
		 */
		if(!in_array("Community_Administrator", $userPIDAuthGroups)) {
			if(in_array("Approver", $userPIDAuthGroups)) {
				$status = Record::getSearchKeyIndexValue($pid, "Status", false);
				if($status != Status::getID("Published")) {
					array_push($userPIDAuthGroups, "Community_Administrator");
				}
			}
		}

		if ($GLOBALS['app_cache']) {
			if (!is_array($roles_cache) || count($roles_cache) > 10) { //make sure the static memory var doesnt grow too large and cause a fatal out of memory error
				$roles_cache = array();
			}
			if ($dsID != "") {
				$roles_cache[$pid][$dsID] = $userPIDAuthGroups;
			} else {
				$roles_cache[$pid] = $userPIDAuthGroups;
			}
		}

		return $userPIDAuthGroups;
	}

	/**
	 * getAuth
	 * This method gets the roles (or authorisation groups) the user has, based on the given ACMLs using the Fez Fedora connection.
	 * It performs some of the lookups using XPATH searches. This is called when the user is working directly with the object
	 * eg view, update, edit etc.
	 *
	 * @access  public
	 * @param   string $pid The persistent identifier of the object
	 * @param   string $dsID (optional) The datastream ID
	 * @returns array $userPIDAuthGroups The authorisation groups (roles) the user belongs to against this object.
	 */
	function getAuth($pid, $dsID="") 
	{
		$log = FezLog::get();
			
		static $roles_cache;
			
		if ($dsID != "") {
			if (isset($roles_cache[$pid][$dsID])) {
				return $roles_cache[$pid][$dsID];
			}
		} else {
			if (isset($roles_cache[$pid])) {
				return $roles_cache[$pid];
			}
		}
			
		$auth_groups = array();
		$ACMLArray = array();
			
		$usingDS = false;
		$acmlBase = false;
		$inherit = false;
			
		if ($dsID != "") {
			$usingDS = true;
			$acmlBase = Record::getACML($pid, $dsID);
		}
			
		// if no FezACML exists for a datastream then it must inherit from the pid object
		if ($acmlBase == false) {
			$usingDS = false;
			$acmlBase = Record::getACML($pid);
		}
			
		/*
		 * No FezACML was found for DS or PID object
		 * so go to parents straight away (inherit presumed)
		 */
		if ($acmlBase == false) {
			$parents = Record::getParents($pid);
			Auth::getParentACMLs(&$ACMLArray, $parents);

			/*
			 * otherwise found something so use that and check if need to inherit
			 */
		} else {

			$ACMLArray[0] = $acmlBase;

			// Check if it inherits security
			$xpath = new DOMXPath($acmlBase);
			$anyRuleSearch = $xpath->query('/FezACML/rule/role/*[string-length(normalize-space()) > 0]');
			if ($anyRuleSearch->length == 0) {

				$inherit = true;
					
			} else {

				$inheritSearch = $xpath->query('/FezACML[inherit_security="on" or inherit_security=""]');
				if( $inheritSearch->length > 0 ) {
					$inherit = true;
				}

			}

			/*
			 * If need to inherit, check if at dsID level or not first and then
			 */
			if ($inherit == true) {
					
				/*
				 * If already at PID level just get parent pids and add them
				 */
				if (($dsID == "") || ($usingDS == false)) {
					$parents = Record::getParents($pid);
					Auth::getParentACMLs(&$ACMLArray, $parents);

					/*
					 * Otherwise get the pid object first and check whether to inherit
					 */
				} else {

					$acmlBase = Record::getACML($pid);

					// if pid level doesnt exist go higher
					if ($acmlBase == false) {
						$parents = Record::getParents($pid);
						Auth::getParentACMLs(&$ACMLArray, $parents);

						/*
						 * Otherwise found pid level so add to ACMLArray and
						 * check whether to inherit or not
						 */
					} else {

						array_push($ACMLArray, $acmlBase);
							
						// If found an ACML then check if it inherits security
						$xpath = new DOMXPath($acmlBase);
						$inheritSearch = $xpath->query('/FezACML[inherit_security="on" or inherit_security=""]');
							
						if( $inheritSearch->length > 0 ) {
							$parents = Record::getParents($pid);
							Auth::getParentACMLs(&$ACMLArray, $parents);
						}
							
					}
				}
					
			}
		}
			
			
		// loop through the ACML docs found for the current pid or in the ancestry
		foreach ($ACMLArray as &$acml) {

			// Use XPath to find all the roles that have groups set and loop through them
			$xpath = new DOMXPath($acml);
			$roleNodes = $xpath->query('/FezACML/rule/role');

			$inherit = false;
			$inheritSearch = $xpath->query('/FezACML[inherit_security="on" or inherit_security=""]');
			if( $inheritSearch->length > 0 ) {
				$inherit = true;
			}

			foreach ($roleNodes as $roleNode) {
				$role = $roleNode->getAttribute('name');
					
				// Use XPath to get the sub groups that have values
				// Note: off can be considered as empty
				$groupNodes = $xpath->query('./*[string-length(normalize-space()) > 0 and text() != "off"]', $roleNode);
				if ($groupNodes->length == 0) {

					/*
					 * If this is a top level rule (not inherited) and lister and
					 * viewer is empty then we want public listing for this pid no
					 * matter what other security this pid has for lister and viewer
					 */
					if(($role == 'Lister' || $role == 'Viewer') && $inherit == false) {

						$rule_array = array(
                                "rule"    => "override", 
                                "value"   => "true"
                                );
                                Auth::addRuleArray(&$auth_groups, $role, $rule_array);

					}

					continue;
				}
					
				foreach ($groupNodes as $groupNode) {
					$group_type = $groupNode->nodeName;
					$group_values = explode(',', $groupNode->nodeValue);
					foreach ($group_values as $group_value) {

						$group_value = trim($group_value, ' ');
						$rule_array = array(
		                        "rule"    => "!rule!role!".$group_type, 
		                        "value"   => $group_value
						);
						Auth::addRuleArray(&$auth_groups, $role, $rule_array);
							
					}
				}
			}
		}
			
		if ($GLOBALS['app_cache']) {
			if (!is_array($roles_cache) || count($roles_cache) > 10) { //make sure the static memory var doesnt grow too large and cause a fatal out of memory error
				$roles_cache = array();
			}
			if ($dsID != "") {
				$roles_cache[$pid][$dsID] = $auth_groups;
			} else {
				$roles_cache[$pid] = $auth_groups;
			}
		}

		return $auth_groups;
	}

	function addRuleArray(&$auth_groups, $role, $ruleArray = array()) 
	{
		if (!is_array($auth_groups[$role])) {
			$auth_groups[$role] = array();
		}
		array_push($auth_groups[$role], $ruleArray);

	}

	/**
	 * Find all the possible rights that this user has to any records in the system.  For example
	 * if they have admin rights on one record then this will return that role.  Use this function to check if the
	 * user should be allowed to start a workflow which requires a particular role on the objects it selects.
	 * NOTE: This assumes that the user is logged in as the auth_rule_group_users table is only updated when
	 * a user is logged in.
	 * @return array of strings - each string is a role name that this user has on at least one pid int he system
	 */
	function getAllIndexAuthorisationGroups($user_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT distinct aro_role as authi_role
    	         FROM " . APP_TABLE_PREFIX . "auth_rule_group_users " .
                "INNER JOIN " . APP_TABLE_PREFIX . "auth_rule_group_rules " .
                        "ON argu_usr_id=".$db->quote($user_id, 'INTEGER')." AND argr_arg_id=argu_arg_id " .
                "INNER JOIN " . APP_TABLE_PREFIX . "auth_index2 " .
                        "ON authi_arg_id=argr_arg_id " .
                "INNER JOIN " . APP_TABLE_PREFIX . "auth_roles " .
                        "ON authi_role=aro_id ";
			
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}

	function isUserApprover($user_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT * " .
                "FROM " . APP_TABLE_PREFIX . "auth_rule_group_users " .
                "INNER JOIN " . APP_TABLE_PREFIX . "auth_rule_group_rules " .
                        "ON argu_usr_id = ".$db->quote($user_id, 'INTEGER')." AND argr_arg_id = argu_arg_id " .
                "INNER JOIN " . APP_TABLE_PREFIX . "auth_index2 " .
                        "ON authi_arg_id = argr_arg_id " .
                "INNER JOIN " . APP_TABLE_PREFIX . "auth_roles " .
                        "ON (authi_role = " . $db->quote(Auth::getRoleIDByTitle("Approver")) .
    	                " OR authi_role = " . $db->quote(Auth::getRoleIDByTitle("Community_Administrator")) . ") ".
    	        "LIMIT 1";

		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		if(count($res) > 0) {
			return true;
		} else {
			return false;
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
		if (@!in_array($session_name, array_keys($_SESSION))) {
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
		global $auth_isBGP, $auth_bgp_session;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			session_name(APP_SESSION);
			@session_start();
			$session =& $_SESSION;
		}

		return Auth::isValidSession($session);
	}



	/**
	 * Method used to check whether a session is valid or not.
	 *
	 * @access  public
	 * @param   array $session The unserialized contents of the session
	 * @return  boolean
	 */
	function isValidSession(&$session) 
	{

		if ((empty($session["username"])) || (empty($session["hash"]))
		|| ($session["hash"] != md5($GLOBALS["private_key"] . md5($session["login_time"])
		. $session["username"]))
		) {
//		|| ($session['ipaddress'] != @$_SERVER['REMOTE_ADDR'])) {
			return false;
		} else {
			if ($session['ipaddress'] != @$_SERVER['REMOTE_ADDR']) {
				$log = FezLog::get();
				$log->debug("IP Session hijacking possibly detected. Session IP is ".$session['ipaddress']." and remote addr is ".@$_SERVER['REMOTE_ADDR'].". Session details are ".print_r($session, true));
			}
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
	 * @param   string $distinguishedname The user distinguishedname to be stored in the session
	 * @param   integer $autologin Flag to indicate whether this user should be automatically logged in or not
	 * @return  void
	 */
	function createLoginSession($username, $fullname,  $email, $distinguishedname, $autologin = 0, $actingUsername = '') 
	{			
		global $auth_bgp_session, $auth_isBGP;

		if ($auth_isBGP) {
			$ses =& $auth_bgp_session;
		} else {
			$ses =& $_SESSION;
		}
		
		if ($actingUsername == '') {
			$actingUsername = $username;
		}
		
		$ipaddress = @$_SERVER['REMOTE_ADDR'];
		$time = time();
		$ses["username"] = $username;
		$ses["acting_username"] = $actingUsername;
		$ses["fullname"] = $fullname;
		$ses["distinguishedname"] = $distinguishedname;
		$ses["email"] = $email;
		$ses["ipaddress"] = $ipaddress;
		$ses["login_time"] = $time;
		$ses["hash"] = md5($GLOBALS["private_key"] . md5($time) . $username);
		$ses["autologin"] = $autologin;
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
			$html = '<script type="text/javascript">
                     <!--
                     window.opener.location.href = "' . $new_url . '";
                     window.close();
                     //-->
                     </script>';
			echo $html;
			exit;
		} else {
			header("Refresh: 0; URL=".$new_url);
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
		@session_destroy();
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
		$log = FezLog::get();
		$db = DB_API::get();

		if (empty($username)) {
			return false;
		} else {
			$stmt = "SELECT usr_id
                    FROM " . APP_TABLE_PREFIX . "user 
                    WHERE usr_username=?";
			try {
				$res = $db->fetchOne($stmt, array($username));
			}
			catch(Exception $ex) {
				$log->err($ex);
				return false;
			}
			if (!is_numeric($res)) {
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
		$log = FezLog::get();

		if ((APP_DISABLE_PASSWORD_CHECKING == "true" && $_SERVER['REMOTE_ADDR'] == APP_DISABLE_PASSWORD_IP) || 
			(Masquerade::canUserMasquerade(Auth::getUsername()) && Auth::userExists($_POST["username"]))) {
			return true;
		} else {
			if (empty($username)) {
				$username = $_POST["username"];
			}
			if (Auth::userExists($username)) {
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
		global $auth_bgp_session, $auth_isBGP;
		static $usr_id;
		$log = FezLog::get();

		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			$session =& $_SESSION;
		}

		if (is_numeric($usr_id)) {
                   //     $log->err("returning static user id of ".$usr_id);
			return $usr_id;
		}

		if (empty($session['username'])) {
			return '';
		} else {
			$usr_id =  @User::getUserIDByUsername($session["username"]);
                 //       $log->err("returning gotten user id of ".$usr_id." from session username of ".$session['username']);
			return $usr_id;
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
		global $auth_bgp_session, $auth_isBGP;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			session_name(APP_SESSION);
			@session_start();
			$session =& $_SESSION;
		}
		if (empty($session) || empty($session['username'])) {
			return '';
		} else {
			return $session['username'];
		}
	}
	
	/**
	 * Gets the current acting username.
	 *
	 * @access  public
	 * @return  string The username of the user
	 */
	function getActingUsername() 
	{
		global $auth_bgp_session, $auth_isBGP;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			session_name(APP_SESSION);
			@session_start();
			$session =& $_SESSION;
		}
		if (empty($session) || empty($session['acting_username'])) {
			return '';
		} else {
			return $session['acting_username'];
		}
	}
	
	/**
	 * Update the current acting username.
	 *
	 * @access  public
	 */
	function setActingUsername($username) 
	{
		if ($username == '') {
			return;
		}
		
		Auth::setSession('acting_username', $username);
		
		return;
	}
	
	/**
	 * Gets the current user ID.
	 *
	 * @access  public
	 * @return  integer The ID of the user
	 */
	function getUserFullName() 
	{
		global $auth_bgp_session, $auth_isBGP;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			session_name(APP_SESSION);
			@session_start();
			$session =& $_SESSION;
		}
		if (empty($session) || empty($session["fullname"])) {
			return '';
		} else {
			return $session["fullname"];
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
		global $auth_bgp_session, $auth_isBGP;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			$session =& $_SESSION;
		}
		if (empty($session) || empty($session["email"])) {
			return '';
		} else {
			return $session["email"];
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
	function GetUsersLDAPGroups($username, $password)  
	{
		$log = FezLog::get();
		
		global $auth_bgp_session, $auth_isBGP;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			$session =& $_SESSION;
		}
		$memberships = array();
		$success = null;
		$useringroupcount = null;
		$useringroupcount = 0;
		$ldap_conn = null;
		$ldap_result = null;
		$ldap_info = null;
		$ldap_infoadmin = null;
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
					list($CNitem, $rest) = preg_split("/,/", $item);
					list($tag, $group) = preg_split("/=/", $CNitem);
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
		$session[APP_LDAP_GROUPS_SESSION] = $usersgroups;
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
	function ldap_authenticate($p_user_id, $p_password) 
	{
		$log = FezLog::get();
		
		if ((APP_DISABLE_PASSWORD_CHECKING == "true") && ($_SERVER['REMOTE_ADDR'] == APP_DISABLE_PASSWORD_IP)) {
			return true; // switch this on and comment the rest out for debugging/development
		} else {
			$t_authenticated 		= false;
			$t_username             = $p_user_id;
			$t_ds                   = ldap_connect(LDAP_SERVER, LDAP_PORT);
			# Attempt to bind with the DN and password
			if(LDAP_PREFIX) {
				$t_br = @ldap_bind( $t_ds, LDAP_PREFIX."\\".$t_username, $p_password );
			} else {
				$t_br = @ldap_bind( $t_ds, $t_username ."@".LDAP_SERVER, $p_password );
			}
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
	function getIDPList() 
	{
		$log = FezLog::get();
		
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
					$type_fields = $xpath->query("./md:IDPSSODescriptor/md:SingleSignOnService/@Location", $recordNode);
					foreach ($type_fields as $type_field) {
						if  ($SSO == "") {
							$SSO = $type_field->nodeValue;
						}
					}
					if ($OrganisationDisplayName != "" && $entityID != "" && $SSO != "" && is_numeric(strpos($entityID,SHIB_FEDERATION))) {
						$IDPArray['List'][$entityID] = $OrganisationDisplayName;
						$IDPArray['SSO'][$entityID]['SSO'] = $SSO;
						//						$IDPArray['SSO'][$entityID]['SSO'] = "https://$SSO/shibboleth-idp/SSO";
						$IDPArray['SSO'][$entityID]['Name'] = $OrganisationDisplayName;
					}
				}
			}
			//			print_r($IDPArray);
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
	function LoginAuthenticatedUser($username, $password, $shib_login = false, $masquerade = false)
    {
        $log = FezLog::get();

        // Flag on whether to load LDAP User Details. True by default. 
        // Value will be changed depending on the Disable Password Checking setting and Masquerading
        $getLDAPDetails = true;

        global $auth_bgp_session, $auth_isBGP;
        if ($auth_isBGP) {
            $session =& $auth_bgp_session;
        } else {
            $session =& $_SESSION;
        }
        $alreadyLoggedIn = false;
        if (!empty($session["login_time"]) && $masquerade == false) {
            $alreadyLoggedIn = true;
            return 0;
        } else {
            $alreadyLoggedIn = false;
        }

        if ($masquerade) {
            $masqueradingUsername = $session['username'];
            $session = null;
            Masquerade::setMasquerader($session, $masqueradingUsername);
        }

        if ($shib_login == true && (@$session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'] == "" && $session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'] == "")) {
            return 24;
        }

        if ($shib_login == true) {
            // Get the username from eduPerson Targeted ID. If empty then they are (really) anonymous
            if ($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'] != "") {
                $username = $session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'];

                // if user has a principal name already in fez add their shibboleth username,
                // but otherwise their username is their epTid
                if ($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'] != "") {
                    $principal_prefix = substr($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'], 0, strpos($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'], "@"));

                    if ($principal_prefix != '') {
                        if (Auth::userExists($username)) {
                            User::updateUsername($principal_prefix, $username);
                        }

                        $username = $principal_prefix;
                        // this is mainly to cater for having login available for both shib and ldap/ad
                        if (Auth::userExists($principal_prefix)) {
                            User::updateShibUsername($principal_prefix, $session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID']);
                        }
                    }
                }
            } elseif ($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'] != "") { // if no eptid then try using EP principalname - this should be rare
                $principal_prefix = substr($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'], 0, strpos($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'], "@"));
                if (Auth::userExists($principal_prefix)) {
                    $username = $principal_prefix;
                } else {
                    $username = $session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'];
                }
            } else {
                // if trying to login via shib and can't find a username in the IDP
                // attribs then return false to make redirect to login page with message
                return 23;
            }
        }

        // If the user isn't a registered fez user, get their details elsewhere (The AD/LDAP)
        // as they must have logged in with LDAP or Shibboleth

        if (!Auth::userExists($username)) {
            if ($shib_login == true) {
                $session['isInAD'] = false;
                $session['isInDB'] = false;
                $session['isInFederation'] = true;

                if ($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-Person-commonName'] != "") {
                    $fullname = $session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-Person-commonName'];
                } elseif ($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'] != "") {
                    $fullname = $session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'];
                } elseif ($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'] != "") {
                    $fullname = $session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'];
                } elseif ($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-Nickname'] != "") {
                    $fullname = $session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-Nickname'];
                } else {
                    $fullname = "Anonymous User";
                }
                if ($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-Person-mail'] != "") {
                    $email = $session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-Person-mail'];
                } else {
                    $email = "";
                }

                if ($session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'] != "") {
                    $shib_username = $session[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'];
                } else {
                    $shib_username = $username;
                }

                $distinguishedname = "";
                // Create the user in Fez
                User::insertFromShibLogin($username, $fullname, $email, $shib_username);
                $usr_id = User::getUserIDByUsername($username);
                User::updateShibAttribs($usr_id);
            } else {
                $session['isInAD'] = true;
                $session['isInDB'] = false;
                $session['isInFederation'] = false;

                $userDetails = User::GetUserLDAPDetails($username, $password);
                
                // Login failed, get out of here.
                if ($userDetails === false){
                    return 32;
                }

                $fullname = $userDetails['displayname'];
                $email = $userDetails['email'];
                $distinguishedname = $userDetails['distinguishedname'];
                Auth::GetUsersLDAPGroups($username, $password);
                // Create the user in Fez
                User::insertFromLDAPLogin();
                $usr_id = User::getUserIDByUsername($username);
                
                // @TOFIX: Investigate why GetUserLDAPDetails() method is called the 2nd time in this else condition.
                $userDetails = User::GetUserLDAPDetails($username, $password);
                //Overwrite shib attributes wiht those from ldap/ad
                User::updateShibAttribs($usr_id);
            }
            $usr_id = User::getUserIDByUsername($username);
            
        // User Exists
        } else { // if it is a registered Fez user then get their details from the fez user table
            $session['isInDB'] = true;
            $userDetails = User::getDetails($username);
            $usr_id = User::getUserIDByUsername($username);
            if (!Auth::isActiveUser($username)) {
                return 7;
            }
            if ($shib_login == true) {
                $session['isInFederation'] = true;
            } else {
                $session['isInFederation'] = false;
                if ($userDetails['usr_ldap_authentication'] == 1) {
                    if (!$auth_isBGP) {
                        
                        // Escape loading LDAP User details when one of these conditions is met, as the LDAP server won't bind without valid password.
                        // - Disable Password Checking is on & available for this IP
                        //   APP_DISABLE_PASSWORD_CHECKING (String). Value: "true" or "false"
                        // - User is masquerading as another user
                        if ( (APP_DISABLE_PASSWORD_CHECKING == "true" && $_SERVER['REMOTE_ADDR'] == APP_DISABLE_PASSWORD_IP) || ($masquerade) ) {
                            $getLDAPDetails = false;
                        }
                            
                        if ($getLDAPDetails) { 
                            Auth::GetUsersLDAPGroups($userDetails['usr_username'], $password);
                            $userDetails = User::GetUserLDAPDetails($username, $password);
                            $distinguishedname = @$userDetails['distinguishedname'];
                            $userDetails = User::getDetails($username);
                            //Overwrite shib attributes wiht those from ldap/ad
                            User::updateShibAttribs($usr_id);
                        }
                    } else {
                        $distinguishedname = '';
                    }
                    $session['isInAD'] = true;
                } else {
                    $distinguishedname = '';
                    $session['isInAD'] = false;
                }
            }
            $fullname = $userDetails['usr_full_name'];
            $email = $userDetails['usr_email'];
            if ($alreadyLoggedIn !== true) {
                User::updateLoginDetails($usr_id); //incremement login count and last login date
                if ($shib_login == true) {
                    User::updateShibLoginDetails($usr_id); //incremement login count for shib logins for this user

                    // Save attribs incase we need them when shib server goes down
                    // Added config var check for this
                    if (SHIB_CACHE_ATTRIBS != 'OFF') {
                        User::updateShibAttribs($usr_id);
                    }
                } else {
                    User::loadShibAttribs($usr_id);
                }
            }

            // get internal fez groups
            Auth::GetUsersInternalGroups($usr_id);
        }

        Auth::createLoginSession($username, $fullname, $email, $distinguishedname, @$_POST["remember_login"]);
        // pre process authorisation rules matches for this user
        Auth::setAuthRulesUsers();
        return 0;
    }

	/**
	 * Gets the internal Fez system groups the user belongs to.
	 *
	 * @access  public
	 * @param   string $usr_id The Fez internal user id of the user
	 * @return  void Sets the internal groups session to the found internal groups
	 */
	function GetUsersInternalGroups($usr_id) 
	{
		$log = FezLog::get();
		
		global $auth_bgp_session, $auth_isBGP;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			$session =& $_SESSION;
		}
		$internal_groups = Group::getGroupColList($usr_id);
		$session[APP_INTERNAL_GROUPS_SESSION] = $internal_groups;
	}

	/**
	 * Gets the Shibboleth attributes.
	 *
	 * @access  public
	 * @return  void Sets the internal shib attributes session to the found shib attributes
	 */
	function GetShibAttributes() 
	{
		$headers = array(); 
		session_name(APP_SESSION);
		@session_start();
		if (SHIB_VERSION == "2") {
			$headers = apache_request_headers();
		} elseif (SHIB_VERSION == "1") { //Shib 2 puts things in $_SERVER, not in the apache request headers..
			//Shib 2.x also calls things different ids, so here is a mapping.
			$shibboleth2_ids = array(
				"eppn" => "Shib-EP-PrincipalName", 
				"targeted-id" => "Shib-EP-TargetedID", 
				"affiliation" => "Shib-EP-ScopedAffiliation", 
				"unscoped-affiliation" => "Shib-EP-UnscopedAffiliation", 
				"entitlement" => "Shib-EP-Entitlement", 
				"assurance" => "Shib-EP-Assurance", 
				"library-number" => "Shib-EP-LibraryNumber", 
				"student-number" => "Shib-EP-StudentNumber", 
				"primary-orgunit-dn" => "Shib-EP-PrimaryOrgUnitDN", 
				"org-dn" => "Shib-EP-OrgUnitDN", 
				"cn" => "Shib-Person-commonName", 
				"mail" => "Shib-Person-mail", 
				"primary-affilation"  => "Shib-EP-PrimaryAffiliation");
			foreach($shibboleth2_ids as $key => $value) {
				if ($_SERVER[$key] != "") {
					$headers[$value] = $_SERVER[$key];
				}
			}
		} elseif (SHIB_VERSION == "3") { //SIMPLESAML puts things in the session, not in the $_SERVER or apache request headers.
			//Shib 2.x also calls things different ids, so here is a mapping.
			$auth = new SimpleSAML_Auth_Simple('default-sp');
			$attrs = $auth->getAttributes();
			
			//shoudl probably just make a oid2fez.php rather than do this here.
			$shibboleth2_ids = array(
				"eduPersonPrincipalName" => "Shib-EP-PrincipalName",
				"eduPersonTargetedID" => "Shib-EP-TargetedID",
				"eduPersonScopedAffiliation" => "Shib-EP-ScopedAffiliation",
				"eduPersonAffiliation" => "Shib-EP-UnscopedAffiliation", 
				"entitlement" => "Shib-EP-Entitlement",
				"assurance" => "Shib-EP-Assurance", 
				"urn:oid:1.3.6.1.4.1.5158.100.1" => "Shib-EP-LibraryNumber", 
				"student-number" => "Shib-EP-StudentNumber", 
				"eduPersonPrimaryOrgUnitDN" => "Shib-EP-PrimaryOrgUnitDN", 
				"eduPersonOrgUnitDN" => "Shib-EP-OrgUnitDN", 
				"cn" => "Shib-Person-commonName", 
				"mail" => "Shib-Person-mail", 
				"eduPersonPrimaryAffiliation"  => "Shib-EP-PrimaryAffiliation");
			foreach($shibboleth2_ids as $key => $value) {
				if ($attrs[$key][0] != "") {
					$headers[$value] = $attrs[$key][0];
				}
			}
		}
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
		global $auth_bgp_session, $auth_isBGP;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			$session =& $_SESSION;
		}
		return @$session['isInAD'];
	}

	/**
	 * Is the user in the internal Fez system?
	 *
	 * @access  public
	 * @return  boolean true if in the internal Fez system, false otherwise.
	 */
	function isInDB() {
		global $auth_bgp_session, $auth_isBGP;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			$session =& $_SESSION;
		}
		return @$session['isInDB'];
	}

	/**
	 * Is the user in the Shibboleth system?
	 *
	 * @access  public
	 * @return  boolean true if in the Shibboleth system, false otherwise.
	 */
	function isInFederation() 
	{
		global $auth_bgp_session, $auth_isBGP;
		if ($auth_isBGP) {
			$session =& $auth_bgp_session;
		} else {
			$session =& $_SESSION;
		}
		return @$session['isInFederation'];
	}

	/**
	 * Return the global default security roles
	 *
	 * @access  public
	 * @return  array $defaultRoles
	 */
	function getDefaultRoles() 
	{
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
	function getDefaultRoleName($role_id) 
	{
		global $defaultRoles;
		return $defaultRoles[$role_id];
	}

	function getUserAuthRuleGroups($usr_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp = APP_TABLE_PREFIX;
		$stmt = "SELECT argu_arg_id
		         FROM ".$dbtp."auth_rule_group_users 
		         WHERE argu_usr_id = ".$db->quote($usr_id, 'INTEGER');
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return $res;
	}


	//Same as above but only returns groups that are set against pids
	function getUserAuthRuleGroupsInUse($usr_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp = APP_TABLE_PREFIX;
		$stmt = "SELECT argu_arg_id
		         FROM ".$dbtp."auth_rule_group_users
		         INNER JOIN ".$dbtp."auth_index2 ON authi_arg_id = argu_arg_id
		         WHERE argu_usr_id = ".$db->quote($usr_id, 'INTEGER')."
				GROUP BY argu_arg_id";
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return $res;
	}

	//Same as above but only returns groups that are set against pids for a specific role
	function getUserRoleAuthRuleGroupsInUse($usr_id, $role) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp = APP_TABLE_PREFIX;
		$stmt = "SELECT argu_arg_id
		         FROM ".$dbtp."auth_rule_group_users
		         INNER JOIN ".$dbtp."auth_index2 ON authi_arg_id = argu_arg_id
				INNER JOIN  ".$dbtp."auth_roles ON authi_role = aro_id and aro_role = ".$db->quote($role)."
		         WHERE argu_usr_id = ".$db->quote($usr_id, 'INTEGER')."
				GROUP BY argu_arg_id";

		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}

		return $res;
	}




	//Same as above but only returns groups that are set against pids for the lister role only using the lister only table
	function getUserListerAuthRuleGroupsInUse($usr_id) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp = APP_TABLE_PREFIX;
		$stmt = "SELECT argu_arg_id
		         FROM ".$dbtp."auth_rule_group_users
		         INNER JOIN ".$dbtp."auth_index2_lister ON authi_arg_id = argu_arg_id
		         WHERE argu_usr_id = ".$db->quote($usr_id, 'INTEGER')."
				GROUP BY argu_arg_id";
		try {
			$res = $db->fetchCol($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}

		return $res;
	}



	function setAuthRulesUsers() 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		global $auth_isBGP;

		if (!$auth_isBGP) {
			$ses = &Auth::getSession();
				
				
				
			//$fez_groups_sql = Misc::arrayToSQL(@$ses[APP_INTERNAL_GROUPS_SESSION]);
			//$ldap_groups_sql = Misc::arrayToSQL(@$ses[APP_LDAP_GROUPS_SESSION]);
				
			$fez_groups = @$ses[APP_INTERNAL_GROUPS_SESSION];
			$ldap_groups = @$ses[APP_LDAP_GROUPS_SESSION];

			$dbtp =  APP_TABLE_PREFIX;
			$usr_id = Auth::getUserID();
			

//                        $log->err("user id for set auth rules is ".$usr_id);

			// clear the rule matches for this user
			$stmt = "DELETE FROM ".$dbtp."auth_rule_group_users WHERE argu_usr_id=".$db->quote($usr_id, 'INTEGER');
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
			}
// remove the below join so the query would run fast again. The join was meant to improve performance by limiting the set but doesnt really.
//                INNER JOIN ".$dbtp."auth_index2 ON argr_arg_id=authi_arg_id
				
			// test and insert matching rules for this user
			$authStmt = "
                INSERT INTO ".$dbtp."auth_rule_group_users (argu_arg_id, argu_usr_id)
                SELECT distinct argr_arg_id, ".$db->quote($usr_id, 'INTEGER')." 
                FROM ".$dbtp."auth_rule_group_rules
                INNER JOIN ".$dbtp."auth_rules ON argr_ar_id=ar_id

                AND 
                (
                    (ar_rule='public_list' AND ar_value='1') 
                OR  (ar_rule = '!rule!role!Fez_User' AND ar_value='".$usr_id."') 
                OR (ar_rule = '!rule!role!AD_User' AND ar_value=".$db->quote(Auth::getUsername()).") ";
			$bindParams = array();
				
			if (count($fez_groups) > 0) {
				$authStmt .="
                    OR (ar_rule = '!rule!role!Fez_Group' AND ar_value IN (".Misc::arrayToSQLBindStr($fez_groups).") ) ";
				$bindParams = array_merge($bindParams, $fez_groups);
			}
			if (count($ldap_groups) > 0) {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!AD_Group' AND ar_value IN (".Misc::arrayToSQLBindStr($ldap_groups).") ) ";
				$bindParams = array_merge($bindParams, $ldap_groups);
			}
			if (!empty($ses['distinguishedname'])) {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!AD_DistinguishedName' 
                            AND INSTR(".$db->quote($ses['distinguishedname']).", ar_value) > 0
                       ) ";
			}

			if (!empty($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'])) {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!eduPersonTargetedID' 
                            AND INSTR(".$db->quote($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID']).", ar_value) > 0
                       ) ";
			}
			if (!empty($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-UnscopedAffiliation'])) {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!eduPersonAffiliation' 
                            AND INSTR(".$db->quote($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-UnscopedAffiliation']).", 
                                ar_value) > 0
                       ) ";
			}
			if (!empty($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-ScopedAffiliation'])) {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!eduPersonScopedAffiliation' 
                            AND INSTR(".$db->quote($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-ScopedAffiliation']).", 
                                ar_value) > 0
                       ) ";
			}
			if (!empty($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrimaryAffiliation'])) {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!eduPersonPrimaryAffiliation' 
                            AND INSTR(".$db->quote($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrimaryAffiliation']).", ar_value) > 0
                       ) ";
			}
			if (!empty($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName'])) {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!eduPersonPrincipalName' 
                            AND INSTR(".$db->quote($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrincipalName']).", ar_value) > 0
                       ) ";
			}
			if (!empty($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-OrgDN'])) {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!eduPersonOrgDN' 
                            AND INSTR(".$db->quote($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-OrgDN']).", ar_value) > 0
                       ) ";
			}
			if (!empty($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-OrgUnitDN'])) {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!eduPersonOrgUnitDN' 
                            AND INSTR(".$db->quote($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-OrgUnitDN']).", ar_value) > 0
                       ) ";
			}
			if (!empty($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrimaryOrgUnitDN'])) {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!eduPersonPrimaryOrgUnitDN' 
                            AND INSTR(".$db->quote($ses[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-PrimaryOrgUnitDN']).", ar_value) > 0
                       ) ";
			}

			if (Auth::isInAD())  {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!in_AD' AND ar_value = 'on')";
			}
			if (Auth::isInDB()) {
				$authStmt .= "
                    OR (ar_rule = '!rule!role!in_Fez' AND ar_value = 'on')";
			}

			$authStmt .= ")";

			try {
				$db->query($authStmt, $bindParams);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return -1;
			}
				
			Auth::setSession('auth_index_user_rule_groups', Auth::getUserAuthRuleGroups($usr_id));
			Auth::setSession('auth_index_highest_rule_group', AuthIndex::highestRuleGroup());
			Auth::setSession('auth_is_approver', Auth::isUserApprover($usr_id));
		}
		Auth::setSession('can_edit', null);
		Auth::setSession('can_create', null);
		return 1;
	}

	/**
	 * Check caching of auth stuff to see if it needs to be invalidated.  If a new rule group has been set then
	 * it probably needs to be invalidated.
	 */
	function checkRuleGroups()
	{
		global $auth_isBGP, $auth_bgp_session;

		if (!$auth_isBGP) {
			$ses = &Auth::getSession();
			if (AuthIndex::highestRuleGroup() > $ses['auth_index_highest_rule_group']) {
				//Error_Handler::logError(AuthIndex::highestRuleGroup()." > ".$ses['auth_index_highest_rule_group'],__FILE__,__LINE__);;
				Auth::setAuthRulesUsers();
			}
		}
	}

	/**
	 * Get a reference to the session - not sure if you are running as background process or in apache so
	 * it grabs a global var and treats it as a session otherwise.
	 * NOTE:  There seems to be a bug that means that the session is not updated if you just set a key in the
	 * reference to the $_SESSION returned from the function.  So use Auth::setSession to make it do the right thing.
	 */
	function getSession()
	{
		global $auth_isBGP, $auth_bgp_session;

		if ($auth_isBGP) {
			$ses =& $auth_bgp_session;
		} else {
			session_name(APP_SESSION);
			@session_start();
			$ses =& $_SESSION;
		}
		return $ses;
	}

	/**
	 * Determines if we are background process and sets the right SESSION global for the occasion.
	 */
	function setSession($key, $value)
	{
		global $auth_isBGP, $auth_bgp_session;

		if ($auth_isBGP) {
			$auth_bgp_session[$key] = $value;
		} else {
			session_name(APP_SESSION);
			@session_start();
			$_SESSION[$key] = $value;
		}
	}
	
	/**
	 * Splits APP_BASIC_AUTH_IP value into an array of individual IPs.
	 */
	function getBasicAuthIPs()
	{
        if (!defined('APP_BASIC_AUTH_IP')) {
            return '';
        }
		$ips = explode(';', APP_BASIC_AUTH_IP);
		foreach ($ips as &$ip) {
			$ip = trim($ip);
		}
		return $ips;
	}
	
	
	/**
	 * Determine if we need to redirect the user to the Baic Authentication URL,
	 * and pass them on to their requested document.
	 */
	function checkForBasicAuthRequest($mode = 'view')
	{
		$ipPool = Auth::getBasicAuthIPs();
		if ((defined('APP_BASIC_AUTH_IP') && (in_array($_SERVER['REMOTE_ADDR'], $ipPool))) && !isset($_SERVER['PHP_AUTH_USER'])) {
			if ($mode == 'view') {
				header ("Location: https://" . APP_HOSTNAME.APP_RELATIVE_URL . "basicview.php?pid=" . $_GET['pid']);
			} elseif ($mode == 'eserv') {
				header ("Location: https://" . APP_HOSTNAME.APP_RELATIVE_URL . "basiceserv.php?pid=" . $_GET['pid'] . "&dsid=" . $_GET['dsID']);
			}
		}
	}
	
	
	
	/**
	 * Logs the current user out of Fez.
	 */
	function logout()
	{
		////////////////////////////////////////////////////////////////////////////////
		// IMPORTANT! everytime you destroy a cookie and you are using 
		// save_session_handler (database storage for sessions for 
		// instance) then you need to reset the save_session_hanlder
		// See the unresolved php bug for details http://bugs.php.net/bug.php?id=32330
		////////////////////////////////////////////////////////////////////////////////
		foreach($_SESSION as $k => $v) {
			unset($_SESSION[$k]);
		}
		
		if (SHIB_VERSION != "3" && SHIB_SWITCH == "ON") {
			if (isset($_COOKIE['_saml_idp'])) {
				setcookie(session_name(), '', time()-42000, '/');
			}
			foreach($_COOKIE as $k => $v) {
				if (is_numeric(strpos($k, "_shibsession_"))) {
					setcookie($k, '', time()-42000, '/');
				}
			}
		}
		
		Zend_Session::destroy();
		
		return;
	}

}
