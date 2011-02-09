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
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include/simplesaml/lib/_autoload.php');
include_once("config.inc.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.db_api.php");

/******************************************************************************/

function generateCookieArray($value){

	// Decodes and splits cookie value
	$CookieArray = split(' ', $value);
	$CookieArray = array_map('base64_decode', $CookieArray);
	
	return $CookieArray;
}

/******************************************************************************/

function generateCookieValue($CookieArray){

	// Merges cookie content and encodes it
	$CookieArray = array_map('base64_encode', $CookieArray);
	$value = implode(' ', $CookieArray);
	return $value;
}

/******************************************************************************/

function appendCookieValue($value, $CookieArray){
	
	array_push($CookieArray, $value);
	$CookieArray = array_reverse($CookieArray);
	$CookieArray = array_unique($CookieArray);
	$CookieArray = array_reverse($CookieArray);
	
	return $CookieArray;
}
/******************************************************************************/

function checkIDP($IDP, $IDProviders){
	if (!$IDProviders[$IDP]){
		$message = sprintf('Invalid IDP origin', $_REQUEST[$redirectCookieName])."</p><p>\n<tt>";
		foreach ($IDProviders as $key => $value){
            $message .= $key."\n";
		}
		$message .= "</tt>\n";
		Error_Handler::logError($message, __FILE__, __LINE__);
		return false;
	}
	
	return true;
}

/******************************************************************************/

function getLoginHostName($string){
	
	if (preg_match('/([a-zA-Z0-9\-\.]+\.[a-zA-Z0-9\-\.]{2,6})/', $string, $matches))
		return $matches[0];
	else
		return '';
}

/******************************************************************************/

function parseSSO($string, $IDProviders, $redirectCookieName){
	
	// Remove redirect statement
	$IDPurl = eregi_replace($redirectCookieName, '', $string);
	
	// Remove slashes
	$IDPurl = ereg_replace('/', '', $IDPurl);
	
	// Do we still have something
	if ($IDPurl != ''){
		
		// Find a matching IdP SSO, must be matching the IdP urn 
		// or at least the last part of the urn
		foreach ($IDProviders as $key => $value){
			if (preg_match('/'.$IDPurl.'$/', $key)){
				return $key;
			}
		}
	}
	
	return '-';
}

/******************************************************************************/

if ((($_SERVER["SERVER_PORT"] != 443) && (APP_HTTPS == "ON")) || ($_SERVER['HTTP_HOST'] != APP_HOSTNAME))  {
   header ("HTTP 302 Redirect");
   if (APP_HTTPS == "ON") {
       header ("Location: https://".APP_HOSTNAME.APP_RELATIVE_URL."login.php"."?".$_SERVER['QUERY_STRING']);
   } else {
	   header ("Location: http://".APP_HOSTNAME.APP_RELATIVE_URL."login.php"."?".$_SERVER['QUERY_STRING']);
   }
}

$tpl = new Template_API();
$tpl->setTemplate("index.tpl.html");
$_GET['url'] = base64_decode($_GET['url']);
if (Auth::hasValidSession(APP_SESSION)) {
    if ($_SESSION["autologin"]) {    	
        if (!empty($_GET["url"])) {
            $extra = '?url=' . $_GET["url"];
        } else {
            $extra = '';
        }
        Auth::redirect(APP_RELATIVE_URL . "list.php" . $extra);
    } else {
        $tpl->assign("username", $session["username"]);
    }
} else {

}
$tpl->assign("SHIB_SWITCH", SHIB_SWITCH);
if (SHIB_SWITCH == "ON" && SHIB_VERSION == "1") {
	
	// set the url session for shib logins so redirects 
	// to index.php (front page) then redirecto to the original url
	if (!empty($_GET["url"])) {
		$_SESSION["url"] = $_GET["url"];			
	}
	
	// Configuration
	$commonDomain = '.'; // Must start with a .
	$redirectCookieName = 'redirect';
	$SAMLDomainCookieName = '_saml_idp';
	$IDPList = Auth::getIDPList();
	$IDProviders = $IDPList['SSO'];

	// First lets see if we can guess the user's IDP
	// IDP determined by redirect cookie
	if ($_COOKIE[$redirectCookieName] && checkIDP($_COOKIE[$redirectCookieName], $IDProviders)){
		$redirectIDP = $_COOKIE[$redirectCookieName];
	} else {
		$redirectIDP = '-';
	}
	
	// IDP determined by resource hint
	if ($_SERVER['PATH_INFO'] 
		&& ($tmp = parseSSO($_SERVER['PATH_INFO'], $IDProviders, $redirectCookieName))
		&& $tmp != '-'
		&& checkIDP($tmp, $IDProviders)
		){
		$hintedIDP = $tmp;
	} else {
		$hintedIDP = '-';
	}

	// IDP determined by SAML IDP cookie = previously used IDPs
	if ($_COOKIE[$SAMLDomainCookieName]){
		$IDPArray = generateCookieArray($_COOKIE[$SAMLDomainCookieName]);
		if (count($IDPArray) > 0){
			$previousIDP = end($IDPArray);
		} else {
			$previousIDP = '-';
		}
	} else {
		$IDPArray = array();
		$previousIDP = '-';
	}
	
	
	// Determine selected IDP
	if ($hintedIDP != '-'){
		$selectedIDP = $hintedIDP;
	} elseif ($redirectIDP != '-'){
		$selectedIDP = $redirectIDP;
	} elseif ($previousIDP != '-'){
		$selectedIDP = $previousIDP;
	} else {
		$selectedIDP = '-';
	}
	
	// Delete permanent cookie
	if ($_REQUEST['clear_permanent_cookie']){
		setcookie ($redirectCookieName, '', time() - 3600, '/', $commonDomain, true);
		
		if ($_REQUEST['getArguments']){
			header('Location: ?'.$_REQUEST['getArguments']);
		} else {
			header('Location: '.$_SERVER['PHP_SELF']);
		}
		exit;
	} elseif ($_REQUEST['set_permanent_cookie'] && checkIDP($_REQUEST['set_permanent_cookie'], $IDProviders)){
		setcookie ($redirectCookieName, $_REQUEST['set_permanent_cookie'], time() + (1000*24*3600), '/', $commonDomain, true);
		
		header('Location: '.$_SERVER['PHP_SELF']);
	}
	
	// Coming from a resource with proper GET arguments or from WAYF
	if ( ($_REQUEST['shire'] && $_REQUEST['target']) || $_REQUEST['getArguments']) {
		
		// User returned selection
		if($_REQUEST['origin'] && $_REQUEST['origin'] != '-' && checkIDP($_REQUEST['origin'], $IDProviders)) {
			// Set Cookie to remember the selection
			// Expiration in 1000 days
			// Add origin as most recent to idp cookie
			$IDPArray = appendCookieValue($_REQUEST['origin'], $IDPArray);
			
			setcookie ($SAMLDomainCookieName, generateCookieValue($IDPArray) , time() + (1000*24*3600), '/', $commonDomain, true);
			
			// Set cookie permanent or for session for automatic redirection
			if ($_REQUEST['permanent_redirect']){
				setcookie ($redirectCookieName, $_REQUEST['origin'], time() + (1000*24*3600), '/', $commonDomain, true);
				
				// Go to settings page
				header(
					'Location: ?redirectArguments='.urlencode($_REQUEST['getArguments'])
					);
				exit;
			} else {
				
				// Do we have to set the refresh cookie?
				if ($_REQUEST[$redirectCookieName]){
					setcookie ($redirectCookieName, $_REQUEST['origin'], null, '/', $commonDomain, true);
				}
				
				// Go to Identity Provider
				$_SESSION['IDP_LOGIN_FLAG'] = 1; // set the login flag to that fez will know the next time (only) it goes to index.php it has to get the shib attribs
				Auth::setHomeIDPCookie($_REQUEST['origin']); // set the origin cookie
				header(
					'Location: '.$IDProviders[$_REQUEST['origin']]['SSO'].
					'?'.$_REQUEST['getArguments']
					);
				exit;
			}
		}
		
		// Redirect if cookie is set
		elseif ( $redirectIDP != '-'){
			header(
				'Location: '.$IDProviders[$_COOKIE[$redirectCookieName]]['SSO'].
				'?'.$_SERVER['argv'][0]
				);
			exit;
		}
		
		// Redirect if resource gives a hint and forces a refresh
		elseif ($hintedIDP != '-' && eregi($redirectCookieName, $_SERVER['PATH_INFO'])){
			header(
				'Location: '.$IDProviders[$hintedIDP]['SSO'].
				'?'.$_SERVER['argv'][0]
				);
			exit;
		}
	}
	
	elseif((!$_REQUEST['shire'] && $_REQUEST['target']) || ($_REQUEST['shire'] && !$_REQUEST['target'])){
		
		$invalidstring = urldecode($_SERVER['QUERY_STRING']);
		$invalidstring = eregi_replace('&',"&\n",$invalidstring);
		if ($invalidstring == '')
			$invalidstring = 'no_arguments';
		$message = 'arguments_missing' . '<pre><code>'.$invalidstring.'</code></pre></p>
			<p>'. 'valid_request_description';
		echo $message;
		exit;
	}
	
	$tpl->assign("SHIB_IDP_LIST", $IDPList['List']);
} else {
	if (SHIB_VERSION == "2" && SHIB_SWITCH == "ON") { // so easy with shib 2.. all the above taken care of by the embedded wayf
		// set the url session for shib logins so redirects 
		// to index.php (front page) then redirecto to the original url
		if (!empty($_GET["url"])) { 		
			$_SESSION["url"] = $_GET["url"];			
		}
		// Set  refresh rate of the login page to 3 mins/ 180 secs so that the shib 2.x time doesnt go beyond the 5 min limit of the IDPs
    	$tpl->assign('refresh_rate', 180);
    	$tpl->assign('refresh_page', str_replace("/", "", $_SERVER['REQUEST_URI']));
		$_SESSION['IDP_LOGIN_FLAG'] = 1; // set the login flag to that fez will know the next time (only) it goes to index.php it has to get the shib attribs
	}
	$tpl->assign("SHIB_IDP_LIST", array());

	if (SHIB_VERSION == "3" && SHIB_SWITCH == "ON") { // so easy with simple saml.. all the above taken care of by the embedded wayf from Simple SAML PHP
		if (!empty($_GET["url"])) { 		
			$_SESSION["url"] = $_GET["url"];			
		}
		$auth = new SimpleSAML_Auth_Simple('default-sp');
		$_SESSION['IDP_LOGIN_FLAG'] = 1;
		if ($_GET['default-idp'] == "true") {
			$auth->login(array('saml:SP','saml:idp' => SHIB_HOME_IDP, 'ReturnTo' => "https://".APP_HOSTNAME));
			exit;
		}
		$SSPUrl = $auth->getLoginURL("https://".APP_HOSTNAME);
		$tpl->assign("SSP_URL", $SSPUrl);
		$SSPDirectUrl = $_SERVER['PHP_SELF']."?default-idp=true";
		$tpl->assign("SSP_DIRECT_URL", $SSPDirectUrl);		
	}
}
$shib_home_idp = Auth::getHomeIDPCookie();
if ($shib_home_idp == "") {
	$shib_home_idp = SHIB_HOME_IDP;
}
$tpl->assign("APP_HOSTNAME", APP_HOSTNAME);
$tpl->assign("SHIB_HOME_SP", SHIB_HOME_SP);
$tpl->assign("SHIB_VERSION", SHIB_VERSION);
$tpl->assign("SHIB_NONJS_URL", SHIB_NONJS_URL);
$tpl->assign("SHIB_WAYF_JS", SHIB_WAYF_JS);
$tpl->assign("SHIB_WAYF_URL", SHIB_WAYF_URL);
$tpl->assign("SHIB_HOME_IDP", $shib_home_idp);
$tpl->assign("SHIB_FEDERATION_NAME", SHIB_FEDERATION_NAME);
$tpl->assign("active_nav", "login");
$tpl->assign("app_short_org_name", APP_SHORT_ORG_NAME);

$tpl->displayTemplate();
