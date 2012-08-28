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

if (!is_file("config.inc.php")) {
    header("Location: setup/");
    exit;
}
include_once("config.inc.php");
if (!defined('APP_INC_PATH')) {
    header("Location: setup/");
    exit;
}

include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.citation.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.news.php");
include_once(APP_INC_PATH . "class.lister.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.my_research.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "najax/najax.php");
include_once(APP_INC_PATH . "najax_objects/class.suggestor.php");

// Redirect if sent from an alias or IP as shibboleth and sessions won't like it otherwise
if ((array_key_exists('HTTP_HOST', $_SERVER) && $_SERVER['HTTP_HOST'] != APP_HOSTNAME) && (!is_numeric(APP_CUSTOM_VIEW_ID))) {
       header ("HTTP 302 Redirect");
       header ("Location: http://".APP_HOSTNAME);
}

NAJAX_Server::allowClasses('Suggestor');
if (NAJAX_Server::runServer()) {
	exit;
}
if (@$_SESSION['IDP_LOGIN_FLAG'] == 1) {
	Auth::GetShibAttributes();
	$_SESSION['IDP_LOGIN_FLAG'] = 0;
}

$masquerade = @$_POST["masquerade"];
if ((@$_SESSION[APP_SHIB_ATTRIBUTES_SESSION]['Shib-EP-TargetedID'] != "" || @$_SERVER['Shib-Session-ID'] != "") && $masquerade == '') {

// Uncomment this to see a debug output of all the shibboleth attributes in the session
	// echo "<pre>"; 
	// print_r($_SESSION[APP_SHIB_ATTRIBUTES_SESSION]);
	// echo "</pre>";  

	if (Auth::LoginAuthenticatedUser("", "", true) > 0) {
    	Auth::redirect(APP_RELATIVE_URL . "login.php?err=22");
	}
	if (!empty($_SESSION["url"])) { 
		$url = $_SESSION["url"];
		$realUrl = urldecode($url);
		$_SESSION["url"] = "";
		$username = Auth::getUsername();
		Zend_Session::writeClose(); // write the session data out before doing a redirect
		if (!empty($realUrl) && $realUrl != APP_RELATIVE_URL && $realUrl != "/index.php?err=6") {		
			Auth::redirect($realUrl);
		} else {
			if (APP_MY_RESEARCH_MODULE == 'ON' && MyResearch::getHRorgUnit($username) != "") {
				Auth::redirect(APP_BASE_URL."/my_fez.php"); // even though its the same page redirect so if they refresh it doesnt have the post vars
			} else {
				Auth::redirect(APP_BASE_URL); // even though its the same page redirect so if they refresh it doesnt have the post vars
				$extra = '';
			}
		}
		exit;
	}
} elseif (count($_POST) > 0) {
	$userVal = new Fez_Validate_Username();
	$noemptyVal = new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::ALL);
	if (!$userVal->isValid($_POST['username'])) {
		Auth::redirect(APP_RELATIVE_URL . "login.php?err=1");
	}
	if (!$noemptyVal->isValid($_POST["passwd"])) {
		Auth::redirect(APP_RELATIVE_URL . "login.php?err=2&username=" . $_POST["username"]);
	}
	// check if the password matches
	// This method can also check via LDAP
	if ($masquerade != '' && User::isUserSuperAdministrator($_POST["username"])) {
		Auth::redirect(APP_RELATIVE_URL . "login.php?err=30&username=" . $_POST["username"]);	
	}
	
	if (!Auth::isCorrectPassword($_POST["username"], $_POST["passwd"])) {
        Auth::redirect(APP_RELATIVE_URL . "login.php?err=3&username=" . $_POST["username"].'&url='.base64_encode($_POST["url"]));
	}
	
    $loginres = Auth::LoginAuthenticatedUser($_POST["username"], $_POST["passwd"], false, $masquerade);
    
    if ($loginres > 0) {
        Auth::redirect(APP_RELATIVE_URL . "login.php?err={$loginres}&username=" . $_POST["username"]);	
    }
	$username = Auth::getUsername();
	Zend_Session::writeClose(); // write the session data out before doing a redirect
	$realUrl = urldecode($_POST["url"]);
	if (!empty($_POST["url"]) && $realUrl != APP_RELATIVE_URL && $realUrl != "/index.php?err=6") {
		Auth::redirect(urldecode($_POST["url"])); 
	} else {
		if (APP_MY_RESEARCH_MODULE == 'ON' && MyResearch::isClassicUser($username) == 1) {
			Auth::redirect(APP_BASE_URL."/my_fez_traditional.php");
		} elseif (APP_MY_RESEARCH_MODULE == 'ON' && MyResearch::getHRorgUnit($username) != "") {
			Auth::redirect(APP_BASE_URL."/my_fez.php"); // even though its the same page redirect so if they refresh it doesnt have the post vars
		} else {
			Auth::redirect(APP_BASE_URL); // even though its the same page redirect so if they refresh it doesnt have the post vars
			$extra = '';
		}
	}
}

$aliasResult = Lister::checkAliasController();

// Don't need to proceed with the front page if we found an alias piped to index.php eg for my pubs aliases like fez/Christiaan
if ($aliasResult == false) {
    
    $tpl = new Template_API();
    //$tpl->setTemplate("maintenance.tpl.html");
    $front_page = "";
    $username = Auth::getUsername();

    if (Auth::userExists($username)) { // if the user is registered as a Fez user
        $prefs = Prefs::get(Auth::getUserID());
        if (array_key_exists('front_page', $prefs)) {
            $front_page = $prefs['front_page'];
        }
    } else {
        $front_page = Pager::getParam("front_page");
    }

    if ($front_page == "" || $front_page == "front_page") {
        $front_page = "front_page.tpl.html";
    } elseif ($front_page == "simple_front_page") {
        $front_page = "simple_front_page.tpl.html";
    } elseif ($front_page == "very_simple_front_page") {
        $front_page = "very_simple_front_page.tpl.html";
    }

    $tpl->assign('fedora_connectivity', $fedoraConnectivity);
    $tpl->setTemplate($front_page);

    //check for custom view search keys
    if (is_numeric(APP_CUSTOM_VIEW_ID)) {
        include_once(APP_INC_PATH . "class.custom_view.php");
        $search_keys = Custom_View::getSekList(APP_CUSTOM_VIEW_ID);
        $tpl->assign("search_keys", $search_keys);
    }

    $recCount = Record::getNumPublishedRecords();
    $recCount = number_format($recCount, 0, ".", " ");
    $recCount = str_replace(" ", html_entity_decode(",&nbsp;", ENT_COMPAT, "UTF-8"), $recCount);
    $tpl->assign("record_count", $recCount);

    $news = News::getList(5, User::isUserAdministrator($username) || User::isUserUPO($username));       // Maximum of 5 news posts for front page.
    $news_count = count($news);
    $tpl->assign("news", $news);
    $tpl->assign("isHomePage", "true");
    $tpl->assign("news_count", $news_count);

    $tpl->assign("autosuggest", 1);
    /* $tpl->headerscript .= "window.oTextbox_front_search
        = new AutoSuggestControl(document.search_frm, 'front_search', document.getElementById('front_search'), document.getElementById('front_search'),
                new StateSuggestions('Collection','suggest',false,
                    'class.collection.php'));
                    document.getElementById('front_search').focus();";

    $tpl->registerNajax(NAJAX_Client::register('Suggestor', 'index.php')); */


    $tpl->assign("active_nav", "home");
    $tpl->displayTemplate();
}
//echo ($GLOBALS['bench']->getOutput());
