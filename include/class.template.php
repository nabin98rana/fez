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
 * Class used to abstract the backend template system used by the site. This
 * is especially useful to be able to change template backends in the future
 * without having to rewrite all PHP based scripts.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */

require_once(APP_PEAR_PATH . "Net/UserAgent/Detect.php");
require_once(APP_SMARTY_PATH . "Smarty.class.php");
require_once(APP_INC_PATH . "class.collection.php");
require_once(APP_INC_PATH . "class.auth.php");
require_once(APP_INC_PATH . "class.user.php");
require_once(APP_INC_PATH . "class.setup.php");
class Template_API
{
    var $smarty;
    var $tpl_name = "";
    var $headerscript;

    /**
     * Constructor of the class
     *
     * @access public
     */
    function Template_API()
    {
        $this->smarty = new Smarty;
        $this->smarty->template_dir = APP_PATH . "templates/" . APP_CURRENT_LANG;
        $this->smarty->compile_dir = APP_PATH . "templates_c";
        $this->smarty->config_dir = '';
    }


    /**
     * Sets the internal template filename for the current PHP script
     *
     * @access public
     * @param  string $tpl_name The filename of the template
     */
    function setTemplate($tpl_name)
    {
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
        if (APP_BENCHMARK) {
            // stop the benchmarking
            $GLOBALS["bench"]->stop();
            $profiling = $GLOBALS["bench"]->getProfiling();
            // last minute check on the benchmarking results
            $this->assign(array("benchmark_total" => sprintf("%.4f", $profiling[count($profiling)-1]["total"]),
                                "benchmark_results" => base64_encode(serialize($profiling))));
        }

        $this->processTemplate();
        // finally display the parsed template
        $this->smarty->display($this->tpl_name);
    }

    /**
     * Prints the actual parsed template.
     *
     * @access public
     */
    function displayTemplateRecord($record_id)
    {
        if (APP_BENCHMARK) {
            // stop the benchmarking
            $GLOBALS["bench"]->stop();
            $profiling = $GLOBALS["bench"]->getProfiling();
            // last minute check on the benchmarking results
            $this->assign(array("benchmark_total" => sprintf("%.4f", $profiling[count($profiling)-1]["total"]),
                                "benchmark_results" => base64_encode(serialize($profiling))));
        }
        $this->processTemplateRecord($record_id);
        // finally display the parsed template
        $this->smarty->display($this->tpl_name);
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
        global $HTTP_SERVER_VARS;

        // determine the correct CSS file to use
        if (ereg('MSIE ([0-9].[0-9]{1,2})', $HTTP_SERVER_VARS["HTTP_USER_AGENT"], $log_version)) {
            $user_agent = 'ie';
        } else {
            $user_agent = 'other';
        }
        $this->assign("user_agent", $user_agent);
        // create the list of collections
        $username = Auth::getUsername();
        if ($username != '') {
            $this->assign("current_full_name", Auth::getUserFullName());
            $this->assign("current_email", Auth::getUserEmail());
        }
        $this->assign("app_path", APP_PATH);
        $this->assign("app_setup", Setup::load());
        $this->assign("app_setup_path", APP_SETUP_PATH);
        $this->assign("app_setup_file", APP_SETUP_FILE);
        $this->assign("ldap_switch", LDAP_SWITCH);
        $this->assign("application_version", APP_VERSION);
        $this->assign("application_title", APP_NAME);
        $this->assign("org_name", APP_ORG_NAME);
        $this->assign("org_short_name", APP_SHORT_ORG_NAME);
        $this->assign("app_base_url", APP_BASE_URL);
        $this->assign("rel_url", APP_RELATIVE_URL);
        $this->assign("lang", APP_CURRENT_LANG);
        $this->assign("SID", SID);

        // now for the browser detection stuff
        Net_UserAgent_Detect::detect();
        $this->assign("browser", Net_UserAgent_Detect::_getStaticProperty('browser'));
        $this->assign("os", Net_UserAgent_Detect::_getStaticProperty('os'));

        // this is only used by the textarea resize script
        $js_script_name = str_replace('/', '_', str_replace('.php', '', $HTTP_SERVER_VARS['PHP_SELF']));
        $this->assign("js_script_name", $js_script_name);

        $this->assign("total_queries", $GLOBALS['TOTAL_QUERIES']);
		$this->assign('headerscript', $this->headerscript);

        $this->assign(array(
            "shaded_bar"     => "background='".APP_RELATIVE_URL."images/".APP_SHADED_BAR."'",
            "heading_color"     => APP_HEADING_COLOR,
            "value_color"     => APP_VALUE_COLOR,
            "cell_color"     => APP_CELL_COLOR,
            "light_color"    => APP_LIGHT_COLOR,
            "selected_color"    => APP_SELECTED_COLOR,
            "middle_color"   => APP_MIDDLE_COLOR,
            "dark_color"     => APP_DARK_COLOR,
            "cycle"          => APP_CYCLE_COLORS,
            "internal_color" => APP_INTERNAL_COLOR
        ));
    }



    /**
     * Processes the template and assigns common variables automatically.
     * 
     * @access	private
     */
    function processTemplateRecord($record_id)
    {
        global $HTTP_SERVER_VARS;

        // determine the correct CSS file to use
        if (ereg('MSIE ([0-9].[0-9]{1,2})', $HTTP_SERVER_VARS["HTTP_USER_AGENT"], $log_version)) {
            $user_agent = 'ie';
        } else {
            $user_agent = 'other';
        }
        $this->assign("user_agent", $user_agent);
        // create the list of collections
        $username = Auth::getUsername();
        if ($username != '') {
            $this->assign("current_full_name", Auth::getUserFullName());
            $this->assign("current_email", Auth::getUserEmail());
        }
        $this->assign("app_path", APP_PATH);
        $this->assign("app_setup", Setup::load());
        $this->assign("app_setup_path", APP_SETUP_PATH);
        $this->assign("app_setup_file", APP_SETUP_FILE);
        $this->assign("ldap_switch", LDAP_SWITCH);
        $this->assign("application_version", APP_VERSION);
        $this->assign("application_title", APP_NAME);
        $this->assign("org_name", APP_ORG_NAME);
        $this->assign("org_short_name", APP_SHORT_ORG_NAME);		
        $this->assign("app_base_url", APP_BASE_URL);
        $this->assign("rel_url", APP_RELATIVE_URL);
        $this->assign("lang", APP_CURRENT_LANG);
        $this->assign("SID", SID);

        // now for the browser detection stuff

        Net_UserAgent_Detect::detect();
        $this->assign("browser", Net_UserAgent_Detect::_getStaticProperty('browser'));
        $this->assign("os", Net_UserAgent_Detect::_getStaticProperty('os'));


        // this is only used by the textarea resize script
        $js_script_name = str_replace('/', '_', str_replace('.php', '', $HTTP_SERVER_VARS['PHP_SELF']));
        $this->assign("js_script_name", $js_script_name);

        $this->assign("total_queries", $GLOBALS['TOTAL_QUERIES']);
		$this->assign('headerscript', $this->headerscript);
        $this->assign(array(
            "shaded_bar"     => "background='".APP_RELATIVE_URL."images/".APP_SHADED_BAR."'",
            "heading_color"     => APP_HEADING_COLOR,
            "cell_color"     => APP_CELL_COLOR,
            "value_color"     => APP_VALUE_COLOR,
            "light_color"    => APP_LIGHT_COLOR,
            "selected_color"    => APP_SELECTED_COLOR,
            "middle_color"   => APP_MIDDLE_COLOR,
            "dark_color"     => APP_DARK_COLOR,
            "cycle"          => APP_CYCLE_COLORS,
            "internal_color" => APP_INTERNAL_COLOR
        ));
    }
}


// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Template Class');
}
?>
