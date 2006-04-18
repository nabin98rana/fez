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


include_once("config.inc.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "db_access.php");

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
		$message = sprintf(getLocalString('invalid_origin'), $_REQUEST[$redirectCookieName])."</p><p>\n<tt>";
					foreach ($IDProviders as $key => $value){
						$message .= $key."\n";
					}
		$message .= "</tt>\n";
		printError($message);
		exit;
	}
	
	return true;
}

/******************************************************************************/

function getHostName($string){
	
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


if (($_SERVER["SERVER_PORT"] != 443) && (APP_HTTPS == "ON")) {
   header ("HTTP 302 Redirect");
   header ("Location: https://".$_SERVER['HTTP_HOST'].APP_RELATIVE_URL."login.php"."?".$HTTP_SERVER_VARS['QUERY_STRING']);
}

$tpl = new Template_API();
$tpl->setTemplate("index.tpl.html");

if (Auth::hasValidSession(APP_SESSION)) {
    if ($_SESSION["autologin"]) {
        if (!empty($HTTP_GET_VARS["url"])) {
            $extra = '?url=' . $HTTP_GET_VARS["url"];
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
if (SHIB_SWITCH == "ON") {
	// Configuration
	$commonDomain = '.au'; // Must start with a .
	$RelyingParty = 'urn:mace:federation.org.au:testfed:level-1:'; // Substring of IdP ID
//	$languageFile = 'languages.php'; // Language file
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
		// First access without cookie or resoure hint, user has to choose
		else {
			
			// Show Header
			//printHeader();
			
			// Show drop down list
			//printWAYF();
			
			// Show footer
			//printFooter();
			//exit;
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
	
	// Show note
	elseif ($redirectIDP != '-'){
		// Show Header
		//printHeader();
		
		// Show drop down list
		//printNotice();
		
		// Show footer
		//printFooter();
		//exit;
	} 
	
	// Show settings
	else {
	
		// Show Header
		//printHeader();
		
		// Show drop down list
		//printSettings();
		
		// Show footer
		//printFooter();
		//exit;
	
	} 
	
//	$IDPList = Auth::getIDPList();
	$tpl->assign("SHIB_IDP_LIST", $IDPList['List']);
	if ($_REQUEST['getArguments']){
		$getArguments = $_REQUEST['getArguments'];
	} else {
//		$getArguments = $_SERVER['argv'][0]; 
		$target = "cookie";
		$time = "1142380709";
		$providerId = urlencode("urn:mace:federation.org.au:testfed:level-1:dev-repo.library.uq.edu.au");
		$shire = urlencode("https://dev-repo.library.uq.edu.au/Shibboleth.sso/SAML/POST");
		$getArguments = "target=$target&shire=$shire&providerId=$providerId";
//		$getArguments = "target=$target&shire=$shire&providerId=$providerId&time=$time";
	}

	
	$tpl->assign("getArguments", $getArguments);
} else {
	$tpl->assign("SHIB_IDP_LIST", array());
}
$tpl->assign("SHIB_HOME_IDP", SHIB_HOME_IDP);
$tpl->assign("SHIB_FEDERATION_NAME", SHIB_FEDERATION_NAME);
$tpl->displayTemplate();
?>
