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
 * Class used to abstract the backend template system used by the site. This
 * is especially useful to be able to change template backends in the future
 * without having to rewrite all PHP based scripts.
 *
 * @version 1.0
 * @author Joo Prado Maia <jpm@mysql.com>
 */

require_once(APP_PEAR_PATH . "Net/UserAgent/Detect.php");
require_once(APP_SMARTY_PATH . "Smarty.class.php");
require_once(APP_INC_PATH . "class.collection.php");
require_once(APP_INC_PATH . "class.auth.php");
require_once(APP_INC_PATH . "class.author.php");
require_once(APP_INC_PATH . "class.user.php");
require_once(APP_INC_PATH . "class.masquerade.php");
require_once(APP_INC_PATH . "class.my_research.php");
require_once(APP_INC_PATH . "class.setup.php");
require_once(APP_INC_PATH . "najax/najax.php");
include_once(APP_INC_PATH . "najax_objects/class.session.php");
include_once(APP_INC_PATH . 'najax_objects/class.background_process_list.php');

class Template_API
{
	var $smarty;
	var $tpl_name = "";
	var $headerscript;
	var $najax_register;

	/**
	 * Constructor of the class
	 *
	 * @access public
	 */
	function Template_API($tpl_name=null)
	{
		if (!defined('APP_CURRENT_LANG')) {
			DEFINE("APP_CURRENT_LANG", "en");
		}

		if ((!defined('APP_TEMPLATE_COMPILE_PATH')) || APP_TEMPLATE_COMPILE_PATH == '') {
			$compile_path = APP_PATH . "templates_c";
		}
		else {
			$compile_path = APP_TEMPLATE_COMPILE_PATH;
		}

		$this->smarty = new Smarty;
		$this->smarty->template_dir = APP_PATH . "templates/" . APP_CURRENT_LANG;
		$this->smarty->compile_dir = $compile_path;
		$this->smarty->config_dir = '';
        $this->smarty->custom_view_dir = '';
		$custom_view_pid = (isset($_GET['custom_view_pid'])) ? $_GET['custom_view_pid'] : null;
		if (!empty($custom_view_pid)) {
			$customView = Custom_View::getCommCview($custom_view_pid);
			if($customView) {
				$this->smarty->custom_view_dir = $customView['cview_id'];
			}
		}
		if (!empty($tpl_name)) {
			$this->setTemplate($tpl_name);
		}
    $this->smarty->default_modifiers = array('escape:"html"');
	}


	/**
	 * Sets the internal template filename for the current PHP script
	 *
	 * @access public
	 * @param  string $tpl_name The filename of the template
	 */
	function setTemplate($tpl_name)
	{
		$_curr_path = $this->smarty->template_dir;
        if (isset($this->smarty->custom_view_dir)) {
            $_fullpath = $_curr_path . "/". $this->smarty->custom_view_dir. "/" .  $tpl_name;
        } else {
            $_fullpath = $_curr_path . "/".  $tpl_name;
        }

		if (file_exists($_fullpath) && is_file($_fullpath)) {
			$tpl_name = $_fullpath;
		}

		$this->tpl_name = $tpl_name;
	}


	/**
	 * Assigns variables to specific placeholders on the target template
	 *
	 * @access public
	 * @param  string $var_name Placeholder on the template
	 * @param  string $value Value to be assigned to this placeholder
	 */
	function assign($var_name, $value = "")
	{
		if (!is_array($var_name)) {
			$this->smarty->assign($var_name, $value);
		} else {
			$this->smarty->assign($var_name);
		}
	}


	/**
	 * Assigns variables to specific placeholders on the target template
	 *
	 * @access public
	 * @param  array $array Array with the PLACEHOLDER=>VALUE pairs to be assigned
	 */
	function bulkAssign($array)
	{
		while (list($key, $value) = each($array)) {
			$this->smarty->assign($key, $value);
		}
	}


    /**
     * Prints the actual parsed template.
     *
     * @access public
     */
    function displayTemplate()
    {
        // If we're making an API call, at the last second lets just make sure we're returning xml, if not 400.
        if (APP_API && strpos($this->tpl_name, '.tpl.xml') == false) {
            API::reply(400, API::makeResponse(
                400,
                "Your browser sent a request that this server could not understand.  " .
                "There is no xml version for '" . $this->tpl_name . "'. "), APP_API
            );
            exit;
        }
        if (APP_API_JSON) {
            $xml = $tpl->getTemplateContents();
            $xml = simplexml_load_string($xml);
            echo json_encode($xml);
        } else {
            $this->processTemplate();
            $this->smarty->display($this->tpl_name);
            FezLog::get()->close();
        }
    }

	/**
	 * Prints the actual parsed template.
	 *
	 * @access public
	 */
	function displayTemplateRecord($record_id)
	{
		$this->displayTemplate();
	}


    /**
     * load a filter of specified type and name
     *
     * @param string $type filter type
     * @param string $name filter name
     */
    function load_filter($type, $name) {
		$this->smarty->load_filter($type, $name);
	}

	/**
	 * Returns the contents of the parsed template
	 *
	 * @access public
	 * @return string The contents of the parsed template
	 */
	function getTemplateContents()
	{
		$this->processTemplate();
		return $this->smarty->fetch($this->tpl_name);
	}


	/**
	 * Processes the template and assigns common variables automatically.
	 *
	 * @access	private
	 */
	function processTemplate()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		// determine the correct CSS file to use
		if (array_key_exists('HTTP_USER_AGENT', $_SERVER) && preg_match('/MSIE ([0-9].[0-9]{1,2})/', $_SERVER["HTTP_USER_AGENT"], $log_version)) {
			$user_agent = 'ie';
		} else {
			$user_agent = 'other';
		}
		$this->assign("user_agent", $user_agent);
		// create the list of collections
		$username = Auth::getUsername();
	    if ($username != '') {
			$authorDetails = Author::getDetailsByUsername($username);
			if (is_numeric($authorDetails['aut_id'])) {
				$isAuthor = 1;
				if ($authorDetails['aut_mypub_url'] == '') {
					$myPubURL = $authorDetails['aut_org_username'];
				} else {
					$myPubURL = $authorDetails['aut_mypub_url'];
				}
			} else {
				$isAuthor = 0;
				$myPubURL = "";
			}
			if (defined('APP_MY_RESEARCH_MODULE') && APP_MY_RESEARCH_MODULE == 'ON') {
				$useClassic = MyResearch::isClassicUser($username);
			} else {
				$useClassic = 1;
			}
      		$this->assign("useClassic", $useClassic);
            $this->assign("isUser", $username);
			$this->assign("myPubURL", $myPubURL);
			$this->assign("isAuthor", $isAuthor);
            $this->assign("current_full_name", Auth::getUserFullName());
            $this->assign("current_email", Auth::getUserEmail());
            $this->assign("current_user_id", Auth::getUserID());
            $this->registerNajax( NAJAX_Client::register('NajaxBackgroundProcessList', APP_RELATIVE_URL.'najax_services/generic.php'));
            $this->assign("app_bg_poll_int", APP_BG_POLL_INT);
        }

		$isAdministrator = Auth::isAdministrator();
		$this->assign("isAdministrator", $isAdministrator);
        $isUPO = User::isUserUPO($username);
        $this->assign("isUPO", $isUPO);
		$canMasquerade = Masquerade::canUserMasquerade($username);
		$this->assign("canMasquerade", $canMasquerade);
        $prefs = Prefs::get(Auth::getUserID());
        if (isset($prefs['editor_condensed_view'])) {
            $this->assign("editorCondensedView", $prefs['editor_condensed_view']);
        }


		$custom_view_pid = (isset($_GET['custom_view_pid'])) ? $_GET['custom_view_pid'] : null;
		if (!empty($custom_view_pid)) {
			$customView = Custom_View::getCommCview($custom_view_pid);
			if ($customView) {
				$cv_title = Record::getSearchKeyIndexValue($custom_view_pid, "Title", false);
				$this->assign('cv_id',   $customView['cview_id']);
				$this->assign('cv_pid',   $custom_view_pid);
			}
		}
		else
		{
		    $customView = null;
		}
        $uri_encoded = '';
        $request_uri = '';
        if (array_key_exists('REQUEST_URI', $_SERVER)) {
            $request_uri = $_SERVER['REQUEST_URI'];
            $uri_encoded = base64_encode($_SERVER['REQUEST_URI']);
        }

        if (preg_match('/\/manage\/.*/', $request_uri)) {
			$this->assign('admin_area', true);
		}

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            // SSL connection
            $this->assign('is_ssl', true);
        } else {
            $this->assign('is_ssl', false);
        }

		$this->assign("start_date", date('Y-m-d', mktime(0,0,0,1,1,date('Y'))));
		$this->assign("end_date", date('Y-m-d', mktime(0,0,0,12,31,date('Y'))));

		$this->assign("app_path", APP_PATH);
		$this->assign("app_setup", Setup::load());
		$this->assign("app_setup_path", APP_SETUP_PATH);
		$this->assign("app_setup_file", APP_SETUP_FILE);
		$this->assign('app_analytics_switch', APP_ANALYTICS_SWITCH);
		$this->assign('app_analytics_id', APP_ANALYTICS_ID);
        $this->assign('app_piwik_switch', APP_PIWIK_SWITCH);
        $this->assign('app_piwik_id', APP_PIWIK_ID);
        $this->assign('app_piwik_location', APP_PIWIK_LOCATION);

		$this->assign("ldap_switch", LDAP_SWITCH);
		$this->assign("application_version", APP_VERSION);
        $ipPool = Auth::getBasicAuthIPs();
        $isBasicAuthIP = false;
        if (defined('APP_BASIC_AUTH_IP') && (in_array($_SERVER['REMOTE_ADDR'], $ipPool))) {
            $isBasicAuthIP = true;
        }
        $this->assign("is_basic_auth_ip", $isBasicAuthIP);
		if (is_array($customView)) {
			$this->assign("application_title", $cv_title);
		} else {
			$this->assign("application_title", APP_NAME);

		}
		$this->assign("app_name", APP_NAME);
		$this->assign("org_name", APP_ORG_NAME);
		$this->assign("org_short_name", APP_SHORT_ORG_NAME);
		$this->assign("app_base_url", APP_BASE_URL);
		$this->assign("app_url", APP_URL);
		$this->assign("rel_url", APP_RELATIVE_URL);
		$this->assign("uri_encoded", $uri_encoded);
		$this->assign("lang", APP_CURRENT_LANG);
		$this->assign("app_earliest_input_year", APP_EARLIEST_INPUT_YEAR);
		$this->assign("SELF_REGISTRATION", SELF_REGISTRATION);
		$this->assign("WEBSERVER_LOG_STATISTICS", WEBSERVER_LOG_STATISTICS);
        if (defined('APP_HERDC_SUPPORT')) {
            $this->assign("APP_HERDC_SUPPORT", APP_HERDC_SUPPORT);
        } else {
            $this->assign("APP_HERDC_SUPPORT", "OFF");
        }
		$this->assign("SID", SID);
		$this->assign("SHIB_SWITCH", SHIB_SWITCH);
		$this->assign("SHIB_DIRECT_LOGIN", SHIB_DIRECT_LOGIN);
        if (defined('APP_USE_GOOGLE_CITATION_COUNTS')) {
            $this->assign("useGoogleCitationCounts", APP_USE_GOOGLE_CITATION_COUNTS);
        } else {
            $this->assign("useGoogleCitationCounts", 'OFF');
        }
		if($customView && is_array($customView)) {
			$this->assign("APP_HOSTNAME", $customView['cvcom_hostname']);
		} else {
			$this->assign("APP_HOSTNAME", APP_HOSTNAME);
		}
		$this->assign("APP_CLOUD_TAG", APP_CLOUD_TAG);
		$this->assign("SHIB_HOME_SP", SHIB_HOME_SP);
		$this->assign("SHIB_HOME_IDP", SHIB_HOME_IDP);
		$this->assign("SHIB_FEDERATION_NAME", SHIB_FEDERATION_NAME);
		$this->assign("APP_INTERNAL_NOTES", APP_INTERNAL_NOTES);
		$this->assign("APP_MY_RESEARCH_MODULE", APP_MY_RESEARCH_MODULE);
		$this->assign("APP_MY_RESEARCH_NEW_ITEMS_COLLECTION", APP_MY_RESEARCH_NEW_ITEMS_COLLECTION);
		$this->assign("APP_MATCHING_TOOLS", APP_MATCHING_TOOLS);
        if (defined(APP_SCOPUS_PARTNER_ID)) {
		    $this->assign("APP_SCOPUS_PARTNER_ID", APP_SCOPUS_PARTNER_ID);
        }
        if (defined ("WOK_USERNAME")) {
		    $this->assign("WOK_USERNAME", WOK_USERNAME);
        }

		if (count(Error_Handler::$app_errors) > 0) {
			if ((APP_DISPLAY_ERRORS_USER == 1) && ($isAdministrator)) {
				$this->assign('app_errors', Error_Handler::$app_errors);
				$this->assign('has_app_errors', true);
			} elseif (APP_DISPLAY_ERRORS_USER == 2)  {
				$this->assign('app_errors', Error_Handler::$app_errors);
				$this->assign('has_app_errors', true);
			}
		}

		if (@$_REQUEST['getArguments']){
			$getArguments = $_REQUEST['getArguments'];
		} else {
			$target = "cookie";
			$time = "1142380709";
			$providerId = urlencode(SHIB_HOME_SP);
			$shire = urlencode("https://".APP_HOSTNAME."/Shibboleth.sso/SAML/POST");
			$getArguments = "target=$target&shire=$shire&providerId=$providerId";
		}
		$this->assign("getArguments", $getArguments);

		// now for the browser detection stuff
		Net_UserAgent_Detect::detect();
		$this->assign("browser", Net_UserAgent_Detect::_getStaticProperty('browser'));
		$this->assign("os", Net_UserAgent_Detect::_getStaticProperty('os'));

		// this is only used by the textarea resize script
		$js_script_name = str_replace('/', '_', str_replace('.php', '', $_SERVER['PHP_SELF']));
		$this->assign("js_script_name", $js_script_name);

		$this->assign(array(
		//"shaded_bar"     => "background='".APP_RELATIVE_URL."images/".APP_SHADED_BAR."'",
            "heading_color"  => "#" . APP_HEADING_COLOR,
            "value_color"    => "#" . APP_VALUE_COLOR,
            "cell_color"     => "#" . APP_CELL_COLOR,
            "light_color"    => "#" . APP_LIGHT_COLOR,
            "selected_color" => "#" . APP_SELECTED_COLOR,
            "middle_color"   => "#" . APP_MIDDLE_COLOR,
            "dark_color"     => "#" . APP_DARK_COLOR,
            "cycle"          => APP_CYCLE_COLORS,
            "internal_color" => "#" . APP_INTERNAL_COLOR,
			"highlight_color" => "#" . APP_HIGHLIGHT_COLOR
		));
		$this->assign('phpini_upload_max_filesize', Misc::convertSize(ini_get('upload_max_filesize')));
    if ($username) {
      // don't show ajax flash message if one of the basic auth / ARC IPs
      if (!defined('APP_BASIC_AUTH_IP') || (!in_array($_SERVER['REMOTE_ADDR'], $ipPool))) {
          $this->registerNajax(NAJAX_Client::register('Session', APP_RELATIVE_URL.'ajax.php'));
          $this->onload("getFlashMessage();");
      }
      $this->assign('najax_header', NAJAX_Utilities::header(APP_RELATIVE_URL.'include/najax'));
      $this->assign('najax_register', $this->najax_register);
     }
		$this->assign('headerscript', $this->headerscript);
		$this->assign('benchmark_total', $log->getLogElapsedTime());
    $this->assign('solr_query_time', $log->solr_query_time);
    // xml is easier to debug/read solr results in so switch it to xml before display
    $this->assign('solr_query_string', preg_replace('/wt=json/', 'wt=xml', $log->solr_query_string));

		$profiler = $db->getProfiler();
		if($profiler) {
			$this->assign("total_queries", $profiler->getTotalNumQueries());
			$this->assign("total_elapsed_secs", sprintf('%0.5f', (string)round($profiler->getTotalElapsedSecs(),5)));
		}

		$this->assign('generated_time', date('Y-m-d H:i:s'));

	}

	function registerNajax($najax)
	{
		$this->najax_register .= "\n$najax\n";
	}

	function onload($onload)
	{
		$this->headerscript .= "\n$onload\n";
	}

	/**
	 * setAuthVars
	 * Set template variables for the headers of the fez pages to display the right menus for an adminsitrator etc...
	 */
	function setAuthVars()
	{
		$username = Auth::getUsername();
		$this->assign("isUser", $username);
		$isAdministrator = User::isUserAdministrator($username);
		$isSuperAdministrator = User::isUserSuperAdministrator($username);
		if (Auth::userExists($username)) { // if the user is registered as a Fez user
			$this->assign("isFezUser", $username);
		}
		$this->assign("isAdministrator", $isAdministrator);
		$this->assign("isSuperAdministrator", $isSuperAdministrator);
	}

}
