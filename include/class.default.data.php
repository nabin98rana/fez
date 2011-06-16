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
	function getConfDefaults()
	{
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
			$defaultData['app_py_exec']                     = "C:/python/python.exe";
			$defaultData['app_pdftotext_exec']              = "C:/utils/pdftotext.exe";
			$defaultData['app_san_import_dir']              = "C:/fez/incoming/";
			$defaultData['app_ffmpeg_cmd']					= "C:/ffmpeg/ffmpeg.exe";
			$defaultData['app_duplicates_reports_location']	= "C:/temp/fez_duplicates_reports/";
			$defaultData['app_exiftool_cmd']                = "C:/exiftool/exiftool.exe";
			$defaultData['app_jahdl_dir']                   = "C:/PROGRA~1/jahdl/";
			$defaultData['handle_admpriv_key_file']         = "C:/path_to_file/admpriv.bin";
			$defaultData['app_log_location']                = "C:/temp/fez-error.log";
            $defaultData['br_img_dir']                      = "C:/temp/pidimages/";
            $defaultData['ghostscript_pth']                 = "C:/utils/gs9.02/bin/gswin32.exe";
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
			$defaultData['app_py_exec']                     = "/usr/bin/python";
			$defaultData['app_pdftotext_exec']              = "/usr/bin/pdftotext";
			$defaultData['app_san_import_dir']              = "/fez/incoming/";
			$defaultData['app_ffmpeg_cmd']					= "/usr/bin/ffmpeg";
			$defaultData['app_duplicates_reports_location']	= "/usr/local/fez_duplicates_reports/";
			$defaultData['app_exiftool_cmd']                = "/usr/bin/exiftool";
			$defaultData['app_jahdl_dir']                   = "/usr/local/jahdl/";
			$defaultData['handle_admpriv_key_file']         = "/usr/local/handle/data/admpriv.bin";
			$defaultData['app_log_location']                = "/var/log/fez/fez-error.log";
            $defaultData['br_img_dir']                      = "/var/www/fez/pidimages/";
            $defaultData['ghostscript_pth']                 = "/usr/bin/gs";

			// Sensible Debian/Ubuntu defaults
			if (is_readable('/etc/debian_version')) {
			    $defaultData['webserver_log_dir']           = "/var/log/apache2/";
			    $defaultData['webserver_log_file']          = "access.log";
			    $defaultData['app_geoip_path']              = "/usr/share/GeoIP/";
			    $defaultData['app_dot_exec']                = "/usr/bin/dot";
			    $defaultData['app_php_exec']                = "/usr/bin/php";
			}

		}   // Can add some other defaults here for other common OS setups

		$defaultData['datamodel_version']                   = "2008102701";                 // Change this to last upgrade + 1
		$defaultData['shib_switch']                         = "OFF";
		$defaultData['shib_direct_login']                   = "OFF";
		$defaultData['shib_federation_name']                = "Australian Access Federation";
		$defaultData['shib_survey']                         = "false";
		$defaultData['shib_federation']                     = "urn:mace:federation.org.au:testfed:level-1:";
		$defaultData['shib_home_sp']                        = $defaultData['shib_federation'] . $defaultData['app_hostname'];
		$defaultData['shib_home_idp']                       = $defaultData['shib_federation'] . "idp.yourinst.edu";
		$defaultData['shib_wayf_metadata_location']         = "/etc/shibboleth/level-1-metadata.xml";
		$defaultData['shib_wayf_url']                       = "https://ds.test.aaf.edu.au/discovery/DS";
		$defaultData['shib_version']                        = "2";
		$defaultData['shib_wayf_js']                        = "https://ds.test.aaf.edu.au/discovery/DS/embedded-wayf.js";
		$defaultData['shib_nonjs_url']                      = "/Shibboleth.sso/DS?target=https://manager.aaf.edu.au/rr/";
		$defaultData['shib_cache_attribs']                  = "OFF";
		$defaultData['app_fedora_version']                  = "2.2";
		$defaultData['app_fedora_username']                 = "fedoraAdmin";
		$defaultData['app_fedora_pwd']                      = "fedoraAdmin";
		$defaultData['fedora_db_host']                      = "localhost";
		$defaultData['fedora_db_type']                      = "pdo_mysql";
		$defaultData['fedora_db_database_name']             = "";   // Empty default
		$defaultData['fedora_db_username']                  = "";   // Empty default
		$defaultData['fedora_db_passwd']                    = "";   // Empty default
		$defaultData['fedora_db_port']                      = "3306";
		$defaultData['app_shaded_bar']                      = "gradient.gif";           // DISCONTINUED
		$defaultData['app_cell_color']                      = "E5E5E5";
		$defaultData['app_value_color']                     = "F6F6F6";
		$defaultData['app_adv_color_light']                 = "FFF0F0";
		$defaultData['app_adv_color_dark']                  = "FFC1BF";		
		$defaultData['app_light_color']                     = "F6F6F6";
		$defaultData['app_selected_color']                  = "FDFFD9";
		$defaultData['app_middle_color']                    = "E5E5E5";
		$defaultData['app_dark_color']                      = "5E217A";
		$defaultData['app_heading_color']                   = "732A95";
		$defaultData['app_cycle_color_one']                 = "EBEBEB";
		$defaultData['app_cycle_color_two']                 = "F7F7F7";
		$defaultData['app_internal_color']                  = "D9D9D9";
		$defaultData['app_highlight_color']                 = "FFE0D0";
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
		$defaultData['app_exiftool_switch']                 = "ON";
		$defaultData['app_image_preview_quality']           = "80";
		$defaultData['app_image_preview_max_width']         = "500";
		$defaultData['app_image_preview_max_height']        = "1000";
		$defaultData['app_thumbnail_quality']               = "70";
		$defaultData['app_thumbnail_width']                 = "150";
		$defaultData['app_thumbnail_height']                = "150";
		$defaultData['app_image_web_quality']               = "100";
		$defaultData['app_image_web_max_width']             = "1280";
		$defaultData['app_image_web_max_height']            = "1024";
		$defaultData['app_https']                           = "OFF";
		$defaultData['app_disable_password_checking']       = "false";
		$defaultData['app_debug_level']                     = "2";
		$defaultData['app_display_error_level']             = "1";
		$defaultData['app_display_errors_user']             = "2";
		$defaultData['app_report_error_file']               = "true";
		$defaultData['app_error_log']                       = $_POST['app_path'] . "error_handler.log";
		$defaultData['app_system_user_id']                  = "1";
		$defaultData['app_email_system_from_address']       = "fez@yourdomain.com";
		$defaultData['app_email_smtp']                      = "mail.yourdomain.com";
		$defaultData['app_watermark']                       = "watermark.gif";
		$defaultData['app_default_user_timezone']           = "Australia/Brisbane";
		$defaultData['app_default_refresh_rate']            = "5";
		$defaultData['app_sql_cache']                       = "";   // Empty default
		$defaultData['app_default_pager_size']              = "50";
		$defaultData['app_version']                         = "2.1 RC3";
		$defaultData['app_cookie']                          = "fez";
		$defaultData['app_https_curl_check_cert']           = "OFF";
		$defaultData['batch_import_type']                   = "MODS 1.0";
		$defaultData['app_network_interfaces']              = "\"eth0\"";  
		$defaultData['app_link_prefix']                     = "";   // Empty default
		$defaultData['app_cloud_tag']                       = "ON";
		$defaultData['app_fedora_apia_direct']              = "OFF";
		$defaultData['app_analytics_id']              		= "";
		$defaultData['app_analytics_switch']              	= "OFF";
		$defaultData['app_author_suggest_mode']             = "1";
		$defaultData['app_herdc_support']                   = "OFF";
		$defaultData['app_solr_indexer']              		= "OFF";
		$defaultData['app_solr_switch']              		= "OFF";
		$defaultData['app_solr_host']              	        = "";
		$defaultData['app_solr_port']                       = "";
		$defaultData['app_solr_path']                       = "";
		$defaultData['app_earliest_input_year']				= "1900";
		$defaultData['app_origami_switch']				    = "OFF";
		$defaultData['app_origami_path']				    = "";
		$defaultData['app_version_uploads_and_links']		= "OFF";
		$defaultData['app_version_time_interval']	        = "30";
		$defaultData['app_filecache_dir']                   = "";
		$defaultData['app_filecache']                       = "OFF";
		$defaultData['app_handle']                          = "OFF";
		$defaultData['handle_admpriv_key_passphrase']       = "none";
		$defaultData['handle_naming_authority_prefix']      = "";
		$defaultData['handle_na_prefix_derivative']         = "";
		$defaultData['handle_resolver_url']                 = "http://resolver.net.au/hdl/";
		$defaultData['show_record_link_as_handle']          = "";
		$defaultData['app_solr_commit_limit']               = "100";
		$defaultData['app_solr_facet_limit']                = "5";
		$defaultData['app_solr_facet_mincount']             = "2";
		$defaultData['app_disable_password_ip']             = "";
		$defaultData['app_ffmpeg_default_width']            = "320";
		$defaultData['app_ffmpeg_default_height']           = "240";
		$defaultData['app_ffmpeg_default_thumb_pos']        = "00:00:05";
		$defaultData['app_fedora_display_checksums']        = "OFF";
		$defaultData['app_mysql_innodb_flag']        		= "OFF";
		$defaultData['app_xpath_switch']    	    		= "OFF";
		$defaultData['app_xsdmf_index_switch']	    		= "OFF";
		$defaultData['app_enable_flash_uploader_switch']	= "ON";		
		$defaultData['app_logging_enabled']                 = "false";
		$defaultData['app_log_level']                       = "3";
		$defaultData['rid_enabled']                         = "false";		
		$defaultData['rid_dl_service_username']             = "your_username_here";
		$defaultData['rid_dl_service_password']             = "your_password_here";
		$defaultData['rid_dl_service_url']                  = "http://rid-dl-request.isiknowledge.com/esti/xrpc";
		$defaultData['rid_dl_service_request_xsd']          = "/path/to/download-request.xsd";
		$defaultData['rid_dl_service_response_xsd']         = "/path/to/download-response.xsd";
		$defaultData['rid_ul_service_username']             = "your_username_here";
		$defaultData['rid_ul_service_password']             = "your_password_here";
		$defaultData['rid_ul_service_url']                  = "https://wok-ws.isiknowledge.com/esti/xrpc";
		$defaultData['rid_ul_service_profiles_xsd']         = "/path/to/Researcher-Bulk-Profiles-schema.xsd";
		$defaultData['rid_ul_service_publications_xsd']     = "/path/to/Researcher-Bulk-Publications-schema.xsd";
		$defaultData['rid_ul_service_routed_email_path']    = "/path/to/upload/emails";
		$defaultData['rid_ul_service_email_append_note']    = "";				
		$defaultData['app_google_map_key']					= "";
		$defaultData['app_use_google_map_switch']			= "OFF";
		$defaultData['app_use_article_title_search']		= "OFF";		
		$defaultData['app_article_add_to_collection']		= "";
		$defaultData['app_article_search_wos_address']		= "";
		$defaultData['app_template_compile_path']			= "";
		$defaultData['app_internal_notes']					= "ON";
		$defaultData['app_session_timeout']					= "10800";
		$defaultData['app_user_group_cache_expiry']			= "3";
		$defaultData['app_main_page_record_count']			= "OFF";
		$defaultData['app_my_research_module']				= "OFF";
		$defaultData['app_my_research_new_items_collection']= "";
		$defaultData['app_my_research_upo_group']			= "Unit Publication Officers";
		$defaultData['app_my_research_use_classic_groups']			= "";
		$defaultData['app_eventum_send_emails']				= "OFF";
		$defaultData['app_eventum_new_job_email_address']	= "";
		$defaultData['app_eventum_database_host']			= "";
		$defaultData['app_eventum_database_name']			= "";
		$defaultData['app_eventum_database_user']			= "";
		$defaultData['app_eventum_database_pass']			= "";
		$defaultData['app_wos_collections']					= "";
		$defaultData['app_matching_tools']					= "OFF";
		$defaultData['app_record_locking']					= "OFF";
		$defaultData['app_auto_linksamr_upload']    		= "OFF";
		$defaultData['app_wheel_group']						= "Masqueraders";
		$defaultData['wok_ws_base_url']                     = "http://search.isiknowledge.com/esti/wokmws/ws/";
		$defaultData['wok_database_id']                     = "WOS";
		$defaultData['wok_cookie_name']                     = "SID";
		$defaultData['wok_username']                        = "";
		$defaultData['wok_password']                        = "";
		$defaultData['wok_batch_size']                      = "50";
		$defaultData['wok_seconds_between_calls']           = "300";
		$defaultData['app_bg_poll_int']                     = "20";
		
		return $defaultData;
	}



	/**
	 * buildColourConfigArray
	 *
	 * This function sets up an array of colour-related configuration variable names. We will use this for
	 * when we need to build a "all colours reset" type function.
	 */
	function buildColourConfigArray()
	{
		$colourConfs = array("app_cell_color", "app_value_color", "app_light_color", "app_selected_color", "app_middle_color", "app_dark_color", "app_heading_color", "app_cycle_color_one", "app_cycle_color_two", "app_internal_color", "app_adv_color_light", "app_adv_color_dark", "app_highlight_color");
		return $colourConfs;
	}



	/**
	 * associateDefaultColours
	 *
	 * Take the list of colour-related variables, and return their default values.
	 */
	function associateDefaultColours($allConfigVars)
	{
		$colourDefaults = array();
		$colourVars = Default_Data::buildColourConfigArray();

		foreach ($colourVars as $colourVar) {
			$colourDefaults[$colourVar] = $allConfigVars[$colourVar];
		}

		return $colourDefaults;
	}

}
