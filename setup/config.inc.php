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
ini_set('allow_url_fopen', 0);
ini_set("display_errors", 1);
error_reporting(1);
error_reporting(E_FATAL);
set_time_limit(0);

// definitions of Organisation LDAP related variables. You may need to query your Org's LDAP expert for help on these settings.
@define("LDAP_SWITCH", "%{LDAP_SWITCH}%");  // Set to OFF or ON depending on whether you want to use LDAP authentication
@define("LDAP_ORGANISATION", "%{LDAP_ORGANISATION}%"); //eg o=The University of Fez, c=AU
@define("LDAP_ROOT_DN", "%{LDAP_ROOT_DN}%"); //eg DC=uq,DC=edu,DC=au
@define("LDAP_PREFIX", "%{LDAP_PREFIX}%");  //eg UQ
@define("LDAP_SERVER", "%{LDAP_SERVER}%"); // yourldapserver.yourdomain.edu
@define("LDAP_PORT", "%{LDAP_PORT}%"); // Usually 389

@define("EPRINTS_OAI", "http://eprint.uq.edu.au/perl/oai2?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai%3Aeprint.uq.edu.au%3A"); // ePrints OAI service provider for batch importing of ePrints records

// definitions of path related variables
@define("APP_SAN_IMPORT_DIR", ""); //eg /fez/incoming or c:\\fez\\incoming

@define("APP_PATH", '%{APP_PATH}%');  //eg /usr/local/apache/htdocs/fez/ or C:\\Program Files\\Apache Group\\Apache\\htdocs\\dev-fez\\
@define("APP_INC_PATH", APP_PATH . "include/");
@define("APP_PEAR_PATH", APP_INC_PATH . "pear/");
@define("APP_TPL_PATH", APP_PATH . "templates/");
@define("APP_SMARTY_PATH", APP_INC_PATH . "Smarty/");
@define("APP_THUMBS_PATH", APP_INC_PATH . "thumbs/");
@define("APP_JPGRAPH_PATH", APP_INC_PATH . "jpgraph/");

// Bill vs Linus
if ((stristr(PHP_OS, 'win')) && (!stristr(PHP_OS, 'darwin'))) { // Windows Server
	@define("APP_TEMP_DIR", 'c:/temp/'); 
	@define("APP_CONVERT_CMD", "convert");   // To convert image (part of ImageMagick)
	@define("APP_IDENTIFY_CMD", "identify"); // To get image information (part of ImageMagick)
	@define("APP_JHOVE_DIR", "c:/jhove");
	@define("APP_JHOVE_TEMP_DIR", 'c:\temp/'); // jhove needs windows style dir names when run on a win server
    ini_set("include_path", ".;" . APP_PEAR_PATH);
} else { //  Linux Server
	@define("APP_TEMP_DIR", "/tmp/"); 
	@define("APP_CONVERT_CMD", "/usr/bin/convert");   // To convert image (part of ImageMagick)
	//@define("APP_CONVERT_CMD", "/usr/X11R6/bin/convert");   // convert could be in here for some Linux distros
	@define("APP_IDENTIFY_CMD", "/usr/X11R6/bin/identify"); // To get image information (part of ImageMagick)
	@define("APP_JHOVE_DIR", "/usr/local/jhove");
	@define("APP_JHOVE_TEMP_DIR", APP_TEMP_DIR);
    ini_set("include_path", ".:" . APP_PEAR_PATH);
}
@define("APP_SETUP_PATH", APP_PATH);
@define("APP_SETUP_FILE", APP_SETUP_PATH . "setup.conf.php");

// FEDORA VARIABLES

//base fedora server domain - note SSL/HTTPS was only available from Fedora 2.1 onwards. Fedora 2.0 and previous only offered HTTP
@define("APP_FEDORA_SETUP", "%{APP_FEDORA_SETUP}%");
@define("APP_FEDORA_LOCATION", "%{APP_FEDORA_LOCATION}%"); // the location of your fedora server without the http or https protocol usually with port 8080
@define("APP_FEDORA_SSL_LOCATION", "%{APP_FEDORA_SSL_LOCATION}%"); // the location of your fedora ssl server without the http or https protocol usually with port 8443
@define("APP_FEDORA_APIA_PROTOCOL_TYPE", "%{APP_FEDORA_APIA_PROTOCOL_TYPE}%");
@define("APP_FEDORA_APIM_PROTOCOL_TYPE", "%{APP_FEDORA_APIM_PROTOCOL_TYPE}%");
if (APP_FEDORA_SETUP == 'sslall') {
	@define("APP_BASE_FEDORA_APIA_DOMAIN", APP_FEDORA_APIA_PROTOCOL_TYPE.APP_FEDORA_SSL_LOCATION); // the location of your fedora ssl server for apia
	@define("APP_BASE_FEDORA_APIM_DOMAIN", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_SSL_LOCATION); // the location of your fedora ssl server for apim
} else {
	if (APP_FEDORA_SETUP == 'sslapim') {
		@define("APP_BASE_FEDORA_APIM_DOMAIN", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_SSL_LOCATION); // the location of your fedora ssl server for apim	
		//upload url
		@define("APP_FEDORA_UPLOAD_URL", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_USERNAME.":".APP_FEDORA_PWD."@".APP_FEDORA_SSL_LOCATION."/management/upload");
	} else {
		@define("APP_BASE_FEDORA_APIM_DOMAIN", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_LOCATION); // the location of your fedora server for apim
		//upload url
		@define("APP_FEDORA_UPLOAD_URL", APP_FEDORA_APIM_PROTOCOL_TYPE.APP_FEDORA_USERNAME.":".APP_FEDORA_PWD."@".APP_FEDORA_LOCATION."/management/upload");
	}
	@define("APP_BASE_FEDORA_APIA_DOMAIN", APP_FEDORA_APIA_PROTOCOL_TYPE.APP_FEDORA_LOCATION); // the location of your fedora server for apia
}

// Setup reusable Fedora API variables
@define("APP_FEDORA_USERNAME", "%{APP_FEDORA_USERNAME}%"); 
@define("APP_FEDORA_PWD", "%{APP_FEDORA_PWD}%");

// Fedora SOAP Services API-A and API-M
@define("APP_FEDORA_ACCESS_API", APP_BASE_FEDORA_APIA_DOMAIN."/services/access"); // for Fedora 2.1
@define("APP_FEDORA_MANAGEMENT_API", APP_BASE_FEDORA_APIM_DOMAIN."/services/management"); // for Fedora 2.1
//@define("APP_FEDORA_MANAGEMENT_API", APP_BASE_FEDORA_APIA_DOMAIN."/management/soap"); // for Fedora 2.0
//@define("APP_FEDORA_MANAGEMENT_API", APP_BASE_FEDORA_APIM_DOMAIN."/access/soap"); // for Fedora 2.0

//fedora get datastream url
@define("APP_FEDORA_GET_URL", APP_BASE_FEDORA_APIA_DOMAIN."/get");

//fedora server search url
@define("APP_FEDORA_SEARCH_URL", APP_BASE_FEDORA_APIA_DOMAIN."/search");

//fedora server resource index search url
@define("APP_FEDORA_RISEARCH_URL", APP_BASE_FEDORA_APIA_DOMAIN."/risearch");

//oai url
@define("APP_FEDORA_OAI_URL", APP_BASE_FEDORA_APIA_DOMAIN."/oai");

// definitions of SQL variables
@define("APP_SQL_DBTYPE", "mysql");
@define("APP_SQL_DBHOST", "%{APP_SQL_DBHOST}%");
@define("APP_SQL_DBNAME", "%{APP_SQL_DBNAME}%");
@define("APP_SQL_DBUSER", "%{APP_SQL_DBUSER}%");
@define("APP_SQL_DBPASS", "%{APP_SQL_DBPASS}%");

@define("APP_DEFAULT_DB", APP_SQL_DBNAME);
@define("APP_TABLE_PREFIX", "%{APP_TABLE_PREFIX}%");

@define("APP_ERROR_LOG", APP_PATH . "error_handler.log");

@define("APP_NAME", "%{APP_NAME}%");
@define("APP_ORG_NAME", "%{APP_ORG_NAME}%");
@define("APP_SHORT_ORG_NAME", "%{APP_SHORT_ORG_NAME}%");
@define("APP_SHORT_NAME", APP_NAME);
@define("APP_URL", "http://www.library.uq.edu.au/escholarship/");
@define("APP_HOSTNAME", "%{APP_HOSTNAME}%");
@define("APP_SITE_NAME", APP_NAME);
@define("APP_RELATIVE_URL", "%{APP_RELATIVE_URL}%");

@define("APP_HTTPS", "%{APP_HTTPS}%"); // if you don't want Fez to redirect to SSL/HTTPS for login/password screens then turn this to OFF
@define("APP_BASE_URL", "%{PROTOCOL_TYPE}%" . APP_HOSTNAME . APP_RELATIVE_URL);
@define("APP_COOKIE", "fez");
@define("APP_COOKIE_EXPIRE", time() + (60 * 60 * 8));
@define("APP_LIST_COOKIE", 'fez_list');
@define("APP_LIST_COOKIE_EXPIRE", time() + (60 * 60 * 24 * 30 * 48));

@define("APP_SESSION", "fez");
@define("APP_INTERNAL_GROUPS_SESSION", APP_SESSION."_internal_groups");
@define("APP_LDAP_GROUPS_SESSION", APP_SESSION."_ldap_groups");

@define("APP_SESSION_EXPIRE", time() + (60 * 60 * 8));

@define("APP_VERSION", "1.1 BETA");

@define("APP_DEFAULT_PAGER_SIZE", 5);
@define("APP_DEFAULT_REFRESH_RATE", 5); // in minutes

@define("APP_SHADED_BAR", "gradient.gif");
@define("APP_CELL_COLOR", "#A7C1DF");
@define("APP_VALUE_COLOR", "#EFF6FF");
@define("APP_LIGHT_COLOR", "#EFF6FF");
@define("APP_MIDDLE_COLOR", "#CACACA");
@define("APP_DARK_COLOR", "#003399");
@define("APP_HEADING_COLOR", "#367FCC");
@define("APP_CYCLE_COLORS", "#DDDDDD,#CACACA");
@define("APP_INTERNAL_COLOR", APP_CELL_COLOR);

// define the user_id of system user
@define("APP_SYSTEM_USER_ID", 1);

@define("APP_BENCHMARK", true);
if (APP_BENCHMARK) {
    // always benchmark the scripts
    include_once("Benchmark/Timer.php");
    $bench = new Benchmark_Timer;
    $bench->start();
}

include_once(APP_INC_PATH . "class.misc.php");

if (isset($_GET)) {
    $HTTP_POST_VARS = $_POST;
    $HTTP_GET_VARS = $_GET;
    $HTTP_SERVER_VARS = $_SERVER;
    $HTTP_ENV_VARS = $_ENV;
    $HTTP_POST_FILES = $_FILES;
    // seems like PHP 4.1.0 didn't implement the $_SESSION auto-global...
    if (isset($_SESSION)) {
        $HTTP_SESSION_VARS = $_SESSION;
    }
//    $HTTP_SESSION_VARS = $_SESSION;
    $HTTP_COOKIE_VARS = $_COOKIE;
}

// fix magic_quote_gpc'ed values (i wish i knew who is the person behind this - i would shout their name from the tallest mountain)
$HTTP_GET_VARS =& Misc::dispelMagicQuotes($HTTP_GET_VARS);
$HTTP_POST_VARS =& Misc::dispelMagicQuotes($HTTP_POST_VARS);

// handle the language preferences now
@include_once(APP_INC_PATH . "class.language.php");
Language::setPreference();
?>
