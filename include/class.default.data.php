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

class Default_Data
{

    /**
     * getConfDefaults
     *
     * This function sets up an array of configuration defaults. Basically, it can be used for
     * resetting the site to a default state (such as a clean install, for example), or if the
     * user b0rks their settings and just wants to go back to how things were initially.
     *
     * This function is roughly equivalent to the old config.inc.php-example file.
     */
    function getConfDefaults() {

        global $HTTP_POST_VARS;
        $defaultData = array();

        $defaultData['app_hostname']                        = $_SERVER['SERVER_NAME'];      // This should be OK.
        $defaultData['webserver_log_statistics']            = "OFF";
        if ((stristr(PHP_OS, 'win')) && (!stristr(PHP_OS, 'darwin'))) {
            // Windows
            $defaultData['webserver_log_dir']               = "C:/PROGRA~1/APACHE~1/Apache2.2/logs/";
            $defaultData['webserver_log_file']              = "access.log";
            $defaultData['app_geoip_path']                  = "C:/PROGRA~1/APACHE~1/Apache2.2/htdocs/geoip/";
            $defaultData['app_temp_dir']                    = "C:/temp/";
            $defaultData['app_convert_cmd']                 = "convert.exe";
            $defaultData['app_composite_cmd']               = "composite.exe";
            $defaultData['app_identify_cmd']                = "identify.exe";
            $defaultData['app_jhove_dir']                   = "C:/jhove/";
            $defaultData['app_dot_exec']                    = "C:/PROGRA~1/ATT/Graphviz/bin/dot.exe";
            $defaultData['app_php_exec']                    = "C:/php/php.exe";
            $defaultData['app_pdftotext_exec']              = "C:/utils/pdftotext.exe";
            $defaultData['app_san_import_dir']              = "C:/fez/incoming/";
        } else {
            // Unix
            $defaultData['webserver_log_dir']               = "/usr/local/apache/logs/";
            $defaultData['webserver_log_file']              = "access_log";
            $defaultData['app_geoip_path']                  = "/usr/local/share/geoip/";
            $defaultData['app_temp_dir']                    = "/tmp/";
            $defaultData['app_convert_cmd']                 = "/usr/bin/convert";
            $defaultData['app_composite_cmd']               = "/usr/bin/composite";
            $defaultData['app_identify_cmd']                = "/usr/bin/identify";
            $defaultData['app_jhove_dir']                   = "/usr/local/jhove/";
            $defaultData['app_dot_exec']                    = "/usr/local/bin/dot";
            $defaultData['app_php_exec']                    = "/usr/local/bin/php";
            $defaultData['app_pdftotext_exec']              = "/usr/bin/pdftotext";
            $defaultData['app_san_import_dir']              = "/fez/incoming/";
        }   // Can add some other defaults here for other common OS setups

        $defaultData['datamodel_version']                   = "2007111201";                 // Change this to last upgrade + 1
        $defaultData['shib_switch']                         = "OFF";
        $defaultData['shib_direct_login']                   = "OFF";
        $defaultData['shib_federation_name']                = "MAMS Testbed Federation";
        $defaultData['shib_survey']                         = "false";
        $defaultData['shib_federation']                     = "urn:mace:federation.org.au:testfed:level-1:";
        $defaultData['shib_home_sp']                        = $defaultData['shib_federation'] . $defaultData['app_hostname'];
        $defaultData['shib_home_idp']                       = $defaultData['shib_federation'] . "idp.yourinst.edu";
        $defaultData['shib_wayf_metadata_location']         = "/usr/local/shibboleth-sp/etc/shibboleth/level-1-metadata.xml";
        $defaultData['app_fedora_version']                  = "2.2";
        $defaultData['app_fedora_username']                 = "fedoraAdmin";
        $defaultData['app_fedora_pwd']                      = "fedoraAdmin";
        $defaultData['fedora_db_host']                      = "localhost";
        $defaultData['fedora_db_type']                      = "mysql";
        $defaultData['fedora_db_database_name']             = "";   // Empty default
        $defaultData['fedora_db_username']                  = "";   // Empty default
        $defaultData['fedora_db_passwd']                    = "";   // Empty default
        $defaultData['fedora_db_port']                      = "3306";
        $defaultData['app_shaded_bar']                      = "gradient.gif";           // DISCONTINUED
        $defaultData['app_cell_color']                      = "#abddf3";
        $defaultData['app_value_color']                     = "#e7f9ff";
        $defaultData['app_light_color']                     = "#e7f9ff";
        $defaultData['app_selected_color']                  = "#fdffd9";
        $defaultData['app_middle_color']                    = "#cecece";
        $defaultData['app_dark_color']                      = "#57a0af";
        $defaultData['app_heading_color']                   = "#46aeed";
        $defaultData['app_cycle_color_one']                 = "#ebebeb";
        $defaultData['app_cycle_color_two']                 = "#f7f7f7";
        $defaultData['app_internal_color']                  = "#a7c1df";
        $defaultData['app_adv_color_light']                 = "#fff0f0";
        $defaultData['app_adv_color_dark']                  = "#ffc1bf";
        $defaultData['app_fedora_setup']                    = "";   // Empty default
        $defaultData['app_fedora_location']                 = "";   // Empty default
        $defaultData['app_fedora_ssl_location']             = "";   // Empty default
        $defaultData['ldap_switch']                         = "OFF";
        $defaultData['ldap_organisation']                   = "";   // Empty default
        $defaultData['ldap_root_dn']                        = "";   // Empty default
        $defaultData['ldap_prefix']                         = "";   // Empty default
        $defaultData['ldap_server']                         = "";   // Empty default
        $defaultData['ldap_port']                           = "";   // Empty default
        $defaultData['eprints_oai']                         = "http://eprint.yourinst.edu/perl/oai2?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai%3Aeprint.yourinst.edu.au%3A";
        $defaultData['eprints_username']                    = "";   // Empty default
        $defaultData['eprints_passwd']                      = "";   // Empty default
        $defaultData['eprints_subject_authority']           = "";   // Empty default
        $defaultData['eprints_db_host']                     = "";   // Empty default
        $defaultData['eprints_db_type']                     = "";   // Empty default
        $defaultData['eprints_db_database_name']            = "";   // Empty default
        $defaultData['eprints_db_username']                 = "";   // Empty default
        $defaultData['eprints_db_passwd']                   = "";   // Empty default
        $defaultData['eprints_import_users']                = "ON";
        $defaultData['self_registration']                   = "OFF";
        $defaultData['app_name']                            = "New Fez Installation";
        $defaultData['app_admin_email']                     = "admin@yourdomain.com";
        $defaultData['app_org_name']                        = "University of Fez";
        $defaultData['app_short_org_name']                  = "UF";
        $defaultData['app_pid_namespace']                   = "";   // Empty default
        $defaultData['app_url']                             = "http://yourdomain.com/fez-location/";
        //$defaultData['app_relative_url']                    = "";                         // From setup form.
        $defaultData['app_image_preview_max_width']         = "500";
        $defaultData['app_image_preview_max_height']        = "1000";
        $defaultData['app_thumbnail_width']                 = "100";
        $defaultData['app_thumbnail_height']                = "200";
        $defaultData['app_image_web_max_width']             = "300";
        $defaultData['app_image_web_max_height']            = "600";
        $defaultData['app_https']                           = "OFF";
        $defaultData['app_disable_password_checking']       = "false";
        $defaultData['app_debug_level']                     = "2";
        $defaultData['app_display_error_level']             = "1";
        $defaultData['app_display_errors_user']             = "2";
        $defaultData['app_report_error_file']               = "true";
        $defaultData['app_error_log']                       = $HTTP_POST_VARS['app_path'] . "error_handler.log";
        $defaultData['app_system_user_id']                  = "1";
        $defaultData['app_email_system_from_address']       = "fez@yourdomain.com";
        $defaultData['app_email_smtp']                      = "mail.yourdomain.com";
        $defaultData['app_watermark']                       = "watermark.gif";
        $defaultData['app_default_user_timezone']           = "Australia/Brisbane";
        $defaultData['app_default_refresh_rate']            = "5";
        $defaultData['app_sql_cache']                       = "";   // Empty default
        $defaultData['app_default_pager_size']              = "50";
        $defaultData['app_version']                         = "2.0 RC1";
        $defaultData['app_cookie']                          = "fez";
        $defaultData['app_https_curl_check_cert']           = "OFF";
        $defaultData['batch_import_type']                   = "MODS 1.0";
        $defaultData['app_link_prefix']                     = "";   // Empty default
        $defaultData['app_cloud_tag']                       = "ON";
        $defaultData['app_fedora_apia_direct']              = "OFF";

        return $defaultData;

    }



    /**
     * buildColourConfigArray
     *
     * This function sets up an array of colour-related configuration variable names. We will use this for
     * when we need to build a "all colours reset" type function.
     */
    function buildColourConfigArray() {

        $colourConfs = array("app_cell_color", "app_value_color", "app_light_color", "app_selected_color", "app_middle_color", "app_dark_color", "app_heading_color", "app_cycle_color_one", "app_cycle_color_two", "app_internal_color", "app_adv_color_light", "app_adv_color_dark");
        return $colourConfs;

    }



    /**
     * associateDefaultColours
     *
     * Take the list of colour-related variables, and return their default values.
     */
    function associateDefaultColours($allConfigVars) {

        $colourDefaults = array();
        $colourVars = Default_Data::buildColourConfigArray();

        foreach ($colourVars as $colourVar) {
            $colourDefaults[$colourVar] = $allConfigVars[$colourVar];
        }

        return $colourDefaults;

    }

}
