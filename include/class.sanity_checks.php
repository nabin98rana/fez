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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH.'class.bgp_test.php');
include_once(APP_INC_PATH.'class.search_key.php');
include_once(APP_INC_PATH.'class.graphviz.php');
include_once(APP_INC_PATH.'class.fedora_direct_access.php');
include_once(APP_INC_PATH.'class.handle_requestor.php');

class ConfigResult
{
	var $type;
	var $config;
	var $value;
	var $message;
	var $passed;

	function __construct($type, $config, $value, $message, $passed=false) 
	{
		$this->type = $type;
		$this->config = $config;
		$this->value = $value;
		$this->message = $message;
		$this->passed = $passed;
	}

	function message($msg)
	{
		$tmp = new ConfigResult('_message', '','',$msg, true);
		return $tmp;
	}

	function messageOk($msg)
	{
		$tmp = new ConfigResult('Pass', '','',$msg, true);
		return $tmp;
	}
}

class SanityChecks
{

	function runSpecificCheck($field, $value)
	{
		if ($field == "app_convert_cmd") {
			return SanityChecks::check_app_convert_cmd($value);
		} else {
			return "Just ran a check on " . $field . ", value " . $value;
		}

	}
	
	function runAllChecks()
	{
		$results = array(); // array of ConfigResult objects
		$results = array_merge($results, SanityChecks::extensions());
		$results = array_merge($results, SanityChecks::checkHTTPConnect('APP_BASE_URL',APP_BASE_URL));
		$results = array_merge($results, SanityChecks::dirs());
		if (!SanityChecks::resultsClean($results)) {
			// no point continuing if the basics aren't met
			$results[] = ConfigResult::message('Aborting remaining tests');
			return $results;
		}
		$results = array_merge($results, SanityChecks::jhove());
		$results = array_merge($results, SanityChecks::shib());
		$results = array_merge($results, SanityChecks::ldap());
		$results = array_merge($results, SanityChecks::exiftool());
		$results = array_merge($results, SanityChecks::imageMagick());
		$results = array_merge($results, SanityChecks::FFMPEG());
		$results = array_merge($results, SanityChecks::backgroundProcess());
		$results = array_merge($results, SanityChecks::dot());
		$results = array_merge($results, SanityChecks::tidy());
		$results = array_merge($results, SanityChecks::fedora());
		$results = array_merge($results, SanityChecks::fedoraDirect());
		$results = array_merge($results, SanityChecks::sql());
		$results = array_merge($results, SanityChecks::pdftotext());
		$results = array_merge($results, SanityChecks::stats());
		$results = array_merge($results, SanityChecks::checkSearchKeys());
		$results = array_merge($results, SanityChecks::handle());
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('All tests Passed');
		}
		return $results;
	}

	function extensions()
	{
		$results = array(ConfigResult::message('Testing for PHP extensions'));
		ob_start();
		phpinfo();
		$contents = ob_get_contents();
		ob_end_clean();
		if (!preg_match("/mod_rewrite/U", $contents)) {
			$results[] = new ConfigResult('Apache modules','mod_rewrite', '',"The mod_rewrite module needs to be enabled in your Apache configuration file (httpd.conf) in order for Fez to work properly. To enable with Apache 2.2 you would run 'a2enmod rewrite'. You will need to restart Apache after enabling this extension.");
		}
		if (!preg_match("/GD Support.*<\/td><td.*>enabled/U", $contents)) {
			$results[] = new ConfigResult('PHP extensions', 'GD Support', '', "The GD extension needs to be enabled in your PHP.INI (for windows) or configured during source compile (Linux) file in order for Fez to work properly. You will need to restart Apache after enabling this extension.");
		}
		if (!preg_match("/Tidy support.*<\/th><th.*>enabled/U", $contents)) {
			$results[] = new ConfigResult('PHP extensions','Tidy support', '',"The Tidy extension needs to be enabled in your PHP.INI (for windows) or configured during source compile (Linux) file in order for Fez to work properly. You will need to restart Apache after enabling this extension.");
		}
		if (!preg_match("/[cC]URL support.*<\/td><td.*>enabled/i", $contents)) {
			$results[] = new ConfigResult('PHP extensions','CURL support', '',"The CURL extension needs to be enabled in your PHP.INI (for windows) or configured during source compile (Linux) file in order for Fez to work properly. You will need to restart Apache after enabling this extension.");
		}
		if (!preg_match("/DOM\/XML.*<\/td><td.*>enabled/U", $contents)) {
			$results[] = new ConfigResult('PHP extensions','DOM XML', '',"The DOM extension needs to be enabled in your PHP.INI (for windows) or configured during source compile (Linux) file in order for Fez to work properly. You will need to restart Apache after enabling this extension.");
		}
		if (LDAP_SWITCH == "ON") {
			if (!preg_match("/LDAP Support.*<\/td><td.*>enabled/U", $contents)) {
				$results[] = new ConfigResult('PHP extensions','LDAP Support', '',"The LDAP Support extension needs to be enabled in your PHP.INI (for windows) or configured during source compile (Linux) file in order for Fez to work properly. You will need to restart Apache after enabling this extension.");
			}
		}

		// check for MySQL support
		if (!function_exists('mysql_query')) {
			$results[] = new ConfigResult('PHP extensions','mysql_query', '',"The MySQL extension needs to be enabled in your PHP.INI (for windows) or configured during source compile (Linux) file in order for Fez to work properly. You will need to restart Apache after enabling this extension.");
		}

		// check for the file_uploads php.ini directive
		if (ini_get('file_uploads') != "1") {
			$results[] = new ConfigResult('PHP extensions','file_uploads', '',"The 'file_uploads' directive needs to be enabled in your PHP.INI file in order for Fez to work properly. You will need to restart Apache after enabling this extension.");
		}
		if (ini_get('allow_call_time_pass_reference') != "1") {
			$results[] = new ConfigResult('PHP extensions','allow_call_time_pass_reference', '',
                'allow_call_time_pass_reference',"The 'allow_call_time_pass_reference' directive needs to be enabled in your PHP.INI file in order for Fez to work properly. You will need to restart Apache after enabling this extension.");
		}
		$mem = Misc::convertSize(ini_get('memory_limit'));
		if ($mem > 0 && $mem < 33554432) {
			$results[] = new ConfigResult('PHP extensions', 'memory_limit',$mem, "The 'memory_limit' directive " .
                    "should be set higher than 32M in your PHP.INI file in order for Fez to work properly. " .
                    "This depends somewhat on the size of files that Fez should be handling. You will need to restart Apache after enabling this extension.");
		}
		$mem = Misc::convertSize(ini_get('upload_max_filesize'));
		if ($mem > 0 && $mem < 10485760) {
			$results[] = new ConfigResult('php.ini', 'upload_max_filesize',$mem, "The 'upload_max_filesize' directive " .
                    "should be set higher than 10M in your PHP.INI file in order for Fez to work properly. " .
                    "This depends somewhat on the size of files that Fez should be handling. You will need to restart Apache after enabling this extension.");
		}
		$post_max_size = Misc::convertSize(ini_get('post_max_size'));
		if ($post_max_size > 0 && $post_max_size < $mem) {
			$results[] = new ConfigResult('php.ini', 'post_max_size',$post_max_size, "The post_max_size setting must be " .
                    "equal or greater than the upload_max_filesize parameter. You will need to restart Apache after enabling this extension.");
		}

		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('Testing PHP extensions');
		}
		return $results;
	}

	function dirs()
	{
		$results = array(ConfigResult::message('Testing general directories'));
		$results = array_merge($results, SanityChecks::checkDir('APP_TEMP_DIR', APP_TEMP_DIR, true));
		$results = array_merge($results, SanityChecks::checkDir('APP_SAN_IMPORT_DIR', APP_SAN_IMPORT_DIR));
		$results = array_merge($results, SanityChecks::checkDir('APP_PATH/templates_c', APP_PATH."templates_c/", true));
		if (APP_REPORT_ERROR_FILE == "true") {
			$results = array_merge($results, SanityChecks::checkFile('APP_ERROR_LOG', APP_ERROR_LOG, true));
		}
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('Testing general directories');
		}
		return $results;
	}

	function stats() 
	{
		if (WEBSERVER_LOG_STATISTICS == "ON") {
			$results = array(ConfigResult::message('Testing Stats Setup'));
			$results = array_merge($results, SanityChecks::checkDir('APP_GEOIP_PATH', APP_GEOIP_PATH));
			$results = array_merge($results, SanityChecks::checkDir('WEBSERVER_LOG_DIR', WEBSERVER_LOG_DIR));
			$results = array_merge($results, SanityChecks::checkFile('WEBSERVER_LOG_DIR.WEBSERVER_LOG_FILE', WEBSERVER_LOG_DIR . WEBSERVER_LOG_FILE, false, false, true));
			if (SanityChecks::resultsClean($results)) {
				$logf = WEBSERVER_LOG_DIR . WEBSERVER_LOG_FILE;
				$archive_name = APP_HOSTNAME;
				$handle = fopen($logf, "r");
				$found_match = false;
				while (!feof($handle)) {
					$buffer = fgets($handle, 4096);
					if (preg_match("/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}) - - \[(.*?)\] \"GET ".preg_quote(APP_RELATIVE_URL,'/')."\/?\S* HTTP\/1..\" 200 .*/i",$buffer,$matches)) {
						$found_match = true;
						break;
					}
				}
				fclose($handle);
				if (!$found_match) {
					$results[] = new ConfigResult('Stats', '', '', 'The apache logfile didn\'t match the expected format. ' .
						'The format should be \'combined\'.  See \'Download Statistics setup\' in the Fez Wiki');
				}
			}
			return $results;
		} else {
			return array();
		}
	}


	function jhove()
	{
		$results = array(ConfigResult::message('Testing JHove'));
		// check that the executable is where we think it is
		$results = array_merge($results, SanityChecks::checkDir("APP_JHOVE_DIR",APP_JHOVE_DIR));
		$results = array_merge($results, SanityChecks::checkDir("APP_JHOVE_TEMP_DIR", APP_JHOVE_TEMP_DIR, true));
		if ((stristr(PHP_OS, 'win')) && (!stristr(PHP_OS, 'darwin'))) { // Windows Server
			$results = array_merge($results, SanityChecks::checkFile('APP_JHOVE_DIR/jhove.bat',
			APP_JHOVE_DIR."jhove.bat", false, false));
		} else {
			$results = array_merge($results, SanityChecks::checkFile('APP_JHOVE_DIR/jhove',
			APP_JHOVE_DIR."jhove", false, true));
		}
		if (SanityChecks::resultsClean($results)) {
			// if all the other checks have passed, we should be able to run jhove on a file
			copy(APP_PATH."images/1leftarrow.png", APP_TEMP_DIR."test.gif");
			Workflow::checkForPresMD("test.gif");
			$result = SanityChecks::checkXML('Jhove Result',APP_TEMP_DIR."presmd_test.xml",
                    '/j:jhove/j:repInfo/j:mimeType[\'image/gif\']',
			array('j' => 'http://hul.harvard.edu/ois/xml/ns/jhove'));
			$results = array_merge($results, $result);
			if (!empty($result)) {
				$results[] = ConfigResult::message('Common problems with jhove are that the environment variables are not '.
                            'set correctly.  Check that the jhove script has been edited as per the ' .
                            'installation mannual, the last line must be changed ' .
                            '# FOR LINUX: ${JAVA} -classpath $CP Jhove -c ${JHOVE_HOME}/conf/jhove.conf $ARGS '.
                            '# FOR WINDOWS: %JAVA% -classpath %CP% Jhove -c %JHOVE_HOME%/conf/jhove.conf %ARGS%');
			}
			@unlink(APP_TEMP_DIR."presmd_test.xml");
			@unlink(APP_JHOVE_TEMP_DIR."test.gif");
		}
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('All JHove tests passed');
		}
		return $results;
	}

	function shib()
	{
		if (SHIB_SWITCH == "ON") {
			$results = array(ConfigResult::message('Testing Shibboleth'));
			$results = array_merge($results, SanityChecks::checkXML('Shibboleth',SHIB_WAYF_METADATA_LOCATION,"//md:EntitiesDescriptor/md:EntityDescriptor",
			array("md" => "urn:oasis:names:tc:SAML:2.0:metadata",
                          "shib" => "urn:mace:shibboleth:metadata:1.0")));
			if (SanityChecks::resultsClean($results)) {
				$results[] = ConfigResult::messageOk('All Shibboleth tests passed');
			}
			return $results;
		} else {
			return array();
		}
	}

	function ldap()
	{
		if (LDAP_SWITCH == "ON") {
			$results = array(ConfigResult::message('Testing LDAP'));
			$results = array_merge($results, SanityChecks::checkConnect('LDAP_SERVER:LDAP_PORT',
			LDAP_SERVER.':'.LDAP_PORT));
			$ld = @ldap_connect(LDAP_SERVER, LDAP_PORT);
			if (!$ld) {
				$results[] = new ConfigResult('LDAP Connect', 'LDAP',LDAP_SERVER.':'.LDAP_PORT,
                    'Connect failed '.ldap_error($ld).'('.ldap_errno($ld).')');
			}
			$ldb = @ldap_bind($ld);
			if (!$ldb) {
				$results[] = new ConfigResult('LDAP Connect', 'LDAP', LDAP_SERVER.':'.LDAP_PORT,
                    'Connect failed '.ldap_error($ld).'('.ldap_errno($ld).')');
			}
			if (SanityChecks::resultsClean($results)) {
				$results[] = ConfigResult::messageOk('All LDAP tests passed');
			}
			return $results;
		} else {
			return array();
		}
	}

	function FFMPEG()
	{
		$results = array(ConfigResult::message('Testing FFMPEG Video Encoder'));
		$results = array_merge($results, SanityChecks::checkFile('APP_FFMPEG_CMD', APP_FFMPEG_CMD, false, true));
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('All FFMPEG tests passed');
		}
		return $results;
	}

	function imageMagick()
	{
		$results = array(ConfigResult::message('Testing imageMagick'));

		// On windows, the image magick command seem to only work through the path environment variable.
		// We have a lot of trouble trying to run them using the full path so we don't specifiy it in the
		// config file and instead just have the exe name.
		if (!stristr(PHP_OS, 'win') ||  stristr(PHP_OS, 'darwin')) { // Not Windows Server
			$results = array_merge($results, SanityChecks::checkFile('APP_CONVERT_CMD', APP_CONVERT_CMD, false, true));
			$results = array_merge($results, SanityChecks::checkFile('APP_COMPOSITE_CMD', APP_COMPOSITE_CMD,
			false, true));
			$results = array_merge($results, SanityChecks::checkFile('APP_IDENTIFY_CMD', APP_IDENTIFY_CMD,
			false, true));
		}
		if (strlen(APP_WATERMARK) > 0) {
			$results = array_merge($results, SanityChecks::checkFile('APP_PATH/images/APP_WATERMARK', APP_PATH."images/".APP_WATERMARK));
		}
		if (SanityChecks::resultsClean($results)) {
			copy(APP_PATH."images/1leftarrow.png", APP_TEMP_DIR."test.gif");
			$getString = APP_BASE_URL."webservices/wfb.image_resize.php?image="
			.urlencode("test.gif")."&height=20&width=20&ext=jpg&outfile="."thumbnail_test.jpg";
			Misc::ProcessURL($getString);
			$results = array_merge($results, SanityChecks::checkFile('Check Image Convert Result', APP_TEMP_DIR."thumbnail_test.jpg"));
			@unlink(APP_TEMP_DIR."thumbnail_test.jpg");
		}
		if (!SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::message('Sometimes a problem with image magick on windows is that ' .
                    'the image magick command \'convert.exe\' needs to be in the path and the config file just ' .
                    'needs to have \'convert.exe\' and not the full path. ');
		}

		// check copyright
		if (SanityChecks::resultsClean($results)) {
			copy(APP_PATH."images/1leftarrow.png", APP_TEMP_DIR."test.gif");
			$getString = APP_BASE_URL."webservices/wfb.image_resize.php?image="
			.urlencode("test.gif")."&height=20&width=20&ext=jpg&outfile="."thumbnail_test.jpg&copyright=hello";
			Misc::ProcessURL($getString);
			$results = array_merge($results, SanityChecks::checkFile('Run Image Convert with copyright', APP_TEMP_DIR."thumbnail_test.jpg"));
			@unlink(APP_TEMP_DIR."thumbnail_test.jpg");
		}
		// check watermark
		if (SanityChecks::resultsClean($results)) {
			copy(APP_PATH."images/1leftarrow.png", APP_TEMP_DIR."test.gif");
			$getString = APP_BASE_URL."webservices/wfb.image_resize.php?image="
			.urlencode("test.gif")."&height=20&width=20&ext=jpg&outfile="."thumbnail_test.jpg&watermark=1";
			Misc::ProcessURL($getString);
			$results = array_merge($results, SanityChecks::checkFile('Run Image Convert with watermark', APP_TEMP_DIR."thumbnail_test.jpg"));
			@unlink(APP_TEMP_DIR."thumbnail_test.jpg");
		}
		// check copyright and watermark
		if (SanityChecks::resultsClean($results)) {
			copy(APP_PATH."images/1leftarrow.png", APP_TEMP_DIR."test.gif");
			$getString = APP_BASE_URL."webservices/wfb.image_resize.php?image="
			.urlencode("test.gif")."&height=20&width=20&ext=jpg&outfile="
			."thumbnail_test.jpg&watermark=1&copyright=hello";
			Misc::ProcessURL($getString);
			$results = array_merge($results, SanityChecks::checkFile('Run Image Convert with watermark and copyright', APP_TEMP_DIR."thumbnail_test.jpg"));
			@unlink(APP_TEMP_DIR."thumbnail_test.jpg");
		}
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('All imageMagick tests passed');
		}
		return $results;
	}

	function backgroundProcess()
	{
		$results = array(ConfigResult::message('Testing backgroundProcess'));
		$results = array_merge($results, SanityChecks::checkFile('APP_PHP_EXEC', APP_PHP_EXEC, false, true));
		if (SanityChecks::resultsClean($results)) {
			// run a test bgp
			$bgp = new BackgroundProcess_Test();
			$id = $bgp->register(serialize(array('test'=>'Hello')),APP_SYSTEM_USER_ID);
			sleep(10);
			$bgp = new BackgroundProcess($id);
			$det = $bgp->getDetails();
			if ($det['bgp_status_message'] != "I got Hello") {
				$results[] = new ConfigResult('backgroundProcess', "Run Background Process", $id,
                        "The background process doesn't seem to have run.  On windows this can be a " .
                        "problem with the version of apache or php - try different versions.");
			}
		}
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('All backgroundProcess tests passed');
		}
		return $results;
	}

	function dot()
	{
		$results = array(ConfigResult::message('Testing graphviz dot'));
		$results = array_merge($results, SanityChecks::checkFile('APP_DOT_EXEC', APP_DOT_EXEC, false, true));
		$dot = 'digraph States { graph [fontpath="/usr/share/fonts/default/Type1/"]; rankdir=LR; node [color=lightblue, style=filled, fontname=n019003l, fontsize=10];454 [label="Review Metadata " URL="http://dev-repo.library.uq.edu.au/uqmsmi14/fez_devel/manage/workflow_states.php?cat=edit&wfl_id=114&wfs_id=454" ]; 453 [label="Preview " URL="http://dev-repo.library.uq.edu.au/uqmsmi14/fez_devel/manage/workflow_states.php?cat=edit&wfl_id=114&wfs_id=453" ]; 451 [label="Publish (end|auto)" URL="http://dev-repo.library.uq.edu.au/uqmsmi14/fez_devel/manage/workflow_states.php?cat=edit&wfl_id=114&wfs_id=451" style=bold color="lightgoldenrod1" ]; 452 [label="Submit for Approval (end|auto)" URL="http://dev-repo.library.uq.edu.au/uqmsmi14/fez_devel/manage/workflow_states.php?cat=edit&wfl_id=114&wfs_id=452" style=bold color="lightgoldenrod1" ]; 449 [label="Select Collection (start)" URL="http://dev-repo.library.uq.edu.au/uqmsmi14/fez_devel/manage/workflow_states.php?cat=edit&wfl_id=114&wfs_id=449" shape=box ]; 450 [label="Enter Metadata " URL="http://dev-repo.library.uq.edu.au/uqmsmi14/fez_devel/manage/workflow_states.php?cat=edit&wfl_id=114&wfs_id=450" ]; "450" -> "453"; "454" -> "452"; "454" -> "451"; "454" -> "453"; "453" -> "451"; "449" -> "450"; "450" -> "451"; "453" -> "452"; "450" -> "452"; "453" -> "454"; };';
		if (SanityChecks::resultsClean($results)) {
			ob_start();
			Graphviz::getPNG($dot);
			$png = bin2hex(ob_get_contents());
			ob_end_clean ();
			$pngsig = "89504E470D0A1A0A";
			if (strcasecmp(substr($png,0,strlen($pngsig)),$pngsig) != 0) {
				$results[] = new ConfigResult('Graphviz', "Run Dot", '',
                        "The generation of a PNG using Graphviz DOT failed.  This is not critical as DOT is only used for " .
                        "showing workflow patterns in the admin forms.");
			}
		}
		if (SanityChecks::resultsClean($results)) {
			$cmapx = Graphviz::getCMAPX($dot);
			$mapsig = '<map id="States" name="States">';
			if (strcasecmp(substr($cmapx,0,strlen($mapsig)),$mapsig) != 0) {
				$results[] = new ConfigResult('Graphviz', "Run Dot cmapx", '',
                        "The generation of an image map using dot didn't work.");
			}
		}
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('All graphviz dot tests passed');
		}
		return $results;
	}

	function tidy()
	{
		$results = array(ConfigResult::message('Testing Tidy'));
		$teststr = "<this><is></this>";
		$config = array(
            'add-xml-decl' => true,
              'indent' => true,
              'input-xml' => true,
              'output-xml' => true,
              'wrap' => 200);
		$tidy = new Tidy;
		if (!$tidy) {
			$results[] = new ConfigResult('Tidy', 'new Tidy', '', 'Tidy object creation failed.');
		} else {
			$tidy->parseString($teststr, $config, 'utf8');
			$tidy->cleanRepair();
			$xml = "$tidy";
			$results = array_merge($results, SanityChecks::checkXMLStr('Tidy Result',$xml, '/this/is'));
		}
		if (!SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::message('Some common problems with tidy are that the actual' .
                    'c libraries aren\'t installed properly. ' .
                    'For linux make sure libtidy, libtidy-dev and tidy packages are installed before compiling php');
		}
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('All Tidy tests passed');
		}
		return $results;
	}

	function fedora()
	{
		$results = array(ConfigResult::message('Testing Fedora'));
		$results = array_merge($results, SanityChecks::checkHTTPConnect('APP_BASE_FEDORA_APIA_DOMAIN',APP_BASE_FEDORA_APIA_DOMAIN.'/describe'));
		$results = array_merge($results, SanityChecks::checkHTTPConnect('APP_BASE_FEDORA_APIM_DOMAIN',APP_BASE_FEDORA_APIM_DOMAIN));
		$results = array_merge($results, SanityChecks::checkHTTPConnect('APP_FEDORA_ACCESS_API',APP_FEDORA_ACCESS_API));
		$results = array_merge($results, SanityChecks::checkHTTPConnect('APP_FEDORA_MANAGEMENT_API',APP_FEDORA_MANAGEMENT_API));
		$security_advice = 'Check that you supplied the correct password in ' .
                    'the Fez config.  Ensure APP_FEDORA_SETUP is correct.  Set fedora.fcfg option <param name="ENFORCE-MODE" value="permit-all-requests"/> ' .
                    'to allow requests from remote hosts (or taylor to suit your security requirements - default ' .
                    'seems to let through localhost only)';
		if (!SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::message('If the fedora server responded with a 401 code, then maybe ' .
                    'the security settings aren\'t right. '.$security_advice  );
		} else {
			// check get next pid is ok
			$results = array_merge($results, SanityChecks::checkHTTPConnect(
                'GetNextPid',
			APP_BASE_FEDORA_APIM_DOMAIN."/management/getNextPID?xml=true"));
			$getString = APP_BASE_FEDORA_APIM_DOMAIN."/management/getNextPID?xml=true";
			$ch = curl_init($getString);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			if (APP_HTTPS_CURL_CHECK_CERT == "OFF")  {
				curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			}
			$xml = curl_exec($ch);
			$dom = @DomDocument::loadXML($xml);
			if (!$dom) {
				//Error_Handler::logError($xml,__FILE__,__LINE__);
				$results[] = new ConfigResult('Fedora','Get Next PID', '', "Fedora didn't return valid XML.");
			}
			curl_close ($ch);
			if (!SanityChecks::resultsClean($results)) {
				$results[] = ConfigResult::message('Make sure that whatever PID prefix namespace you choose is also in ' .
                    'the "retainPIDS" set of namespaces. Eg if your PID namespace is "UQ" make sure "UQ" is in ' .
                    'the list of retainPIDS in fedora.fcfg. If this is not set then Fez will not be able to create ' .
                    'objects in Fedora.  '.$security_advice);
			}
		}
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('All Fedora tests passed');
		}
		return $results;
	}

	function fedoraDirect()
	{
		$log = FezLog::get();
		$db = DB_API::get('fedora_db');
		
		// I'm not very happy about this. We should be actually testing the credentials. Re-write this entire class at some point.
		$results = array(ConfigResult::message('Testing Fedora Direct Access'));
		$server = FEDORA_DB_HOST;
		$port = FEDORA_DB_PORT;
		$results = array_merge($results, SanityChecks::checkConnect('FEDORA_DB_HOST', $server . ':' . $port));
		if (SanityChecks::resultsClean($results)) {
			if (! $db->getConnection()) {
				$results[] = new ConfigResult('Fedora Direct','FEDORA_DB_DATABASE_NAME',FEDORA_DB_DATABASE_NAME,"Failed to connect to DB. " .
                        "Check that the specified database user has permissions on the Fedora database. " .
                        "Check that the database password is correct. " .
                        "Check that database name is set correctly. DB Error");
			} else {
				$stmt = "SELECT * FROM policy ";
				$db->limit($stmt, 1, 0);
				try {
					$db->query($stmt);
				}
				catch(Exception $ex) {					
					$results[] = new ConfigResult('Fedora Direct','FEDORA_DB_DATABASE_NAME',FEDORA_DB_DATABASE_NAME,"Failed to query DB. " .
                        "Check that the specified database user has permissions on the Fedora database. " .
                        "Check that the database password is correct. " .
                        "Check that database name is set correctly. DB Error: " .
                        $ex->getMessage());
				}
			}
		}
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('All Fedora Direct Access tests passed');
		}
		return $results;
	}

	function sql()
	{
		$results = array(ConfigResult::message('Testing SQL'));
		list($server, $port) = explode(':', APP_SQL_DBHOST);
		if (empty($port)) {
			$port = '3306';
		}
		$results = array_merge($results, SanityChecks::checkConnect('APP_SQL_DBHOST',$server.':'.$port));
		if (SanityChecks::resultsClean($results)) {
			$stmt = "use ".APP_SQL_DBNAME;
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {			
				$results[] = new ConfigResult('SQL','APP_SQL_DBNAME',APP_SQL_DBNAME,"Failed to use DB. " .
                        "Check that the configured APP_SQL_DBUSER has permissions on the DB in SQL. " .
                        "Check that APP_SQL_DBPASS is correct. " .
                        "Check that APP_SQL_DBNAME is set correctly. DB Error: " .
                        $ex->getMessage());
			}
		}
		if (SanityChecks::resultsClean($results)) {
			$stmt = "SELECT * FROM ".APP_SQL_DBNAME . "." . APP_TABLE_PREFIX . "xsd_loop_subelement limit 1";
			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$results[] = new ConfigResult('SQL','APP_TABLE_PREFIX',APP_TABLE_PREFIX,"Failed to query " .
                        "one of the Fez tables.  Check that APP_TABLE_PREFIX is correct. DB Error: " .
                        $ex->getMessage());
			}
		}
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('All SQL tests passed');
		}
		return $results;
	}

	function pdftotext()
	{
		$results = array(ConfigResult::message('Testing PDFtoText'));
		$results = array_merge($results, SanityChecks::checkFile('APP_PDFTOTEXT_EXEC',APP_PDFTOTEXT_EXEC, false, true));
		if (SanityChecks::resultsClean($results)) {
			$results[] = ConfigResult::messageOk('All PDFtoText tests passed');
		}
		return $results;
	}

	function exiftool()
	{
		$results = array(ConfigResult::message('Testing ExifTool'));
		if (APP_EXIFTOOL_SWITCH != "ON") {
			$results[] = ConfigResult::messageOk('Bypassing check for ExifTool because exiftool config switch is not ON');
		} else {
			$results = array_merge($results, SanityChecks::checkFile('APP_EXIFTOOL_CMD',APP_EXIFTOOL_CMD, false, true));
			if (SanityChecks::resultsClean($results)) {
				$results[] = ConfigResult::messageOk('All ExifTool tests passed');
			}
		}
		return $results;
	}


	function resultsClean($results)
	{
		foreach ($results as $res) {
			if (!$res->passed) {
				return false;
			}
		}
		return true;
	}

	function checkDir($configDefine, $value, $writable = false)
	{
		if (!Misc::endsWith($value, '/')) {
			return array(new ConfigResult('Directory', $configDefine, $value, "The directory name should have a trailing slash."));
		}
		if (!is_dir($value)) {
			return array(new ConfigResult('Directory', $configDefine, $value, "This is not a directory, check that it isn't a file."));
		}
		$dh = @opendir($value);
		if (!$dh) {
			return array(new ConfigResult('Directory', $configDefine, $value,
                "Failed opendir (probably a permissions problem)"));
		}
		closedir($dh);
		if ($writable) {
			$value2 = rtrim($value,'/');
			$tmpfname = "{$value2}/sanity_check_tmpfile";
			$teststr = "This is a test";
			if (@file_put_contents($tmpfname, $teststr) < strlen($teststr)) {
				return array(new ConfigResult('Directory', $configDefine, $value, "Failed to write a file"));
			}
			unlink($tmpfname);
		}

		return array();
	}

	function checkFile($configDefine, $value, $writeable = false, $exec = false, $readable = false)
	{
		if ((stristr(PHP_OS, 'win')) && (!stristr(PHP_OS, 'darwin'))) { // Windows Server
			$value = str_replace('/','\\',$value);
			$value = trim($value,'"');
		}
		if (!is_file($value)) {
			return array(new ConfigResult('File', $configDefine, $value, "This file doesn't exist, check the path" .
                    " and the permissions so that webserver user can read the file (the webserver must have 'rx' " .
                    "permission on any parent directories as well as 'r' permission on the file)"));
		}
		if ($exec) {
			if (!is_executable($value)) {
				return array(new ConfigResult('File', $configDefine, $value, "This file isn't executable by the web " .
                        "server.  The webserver user should have 'rx' permissions on this file."));
			}
		}
		if ($writeable) {
			if (!is_writable($value) ) {
				return array(new ConfigResult('File', $configDefine, $value, "The web server user doesn't" .
                        " have write permissions on this file."));
			}
		}
		if ($readable) {
			if (!is_readable($value) ) {
				return array(new ConfigResult('File', $configDefine, $value, "The web server user doesn't" .
                        " have read permissions on this file."));
			}
		}
		return array();
	}

	function checkXML($configDefine, $value, $xpath = '', $ns_array = array(), $debug = false)
	{
		$results = SanityChecks::checkFile($configDefine,$value);
		if (!empty($results)) {
			return $results;
		}
		$xml = file_get_contents($value);
		return SanityChecks::checkXMLStr($configDefine, $xml, $xpath, $ns_array, $debug);
	}

	function checkXMLStr($configDefine, $value, $xpath = '', $ns_array = array(), $debug = false)
	{
		$dom = DOMDocument::loadXML($value);
		if (!$dom) {
			return array(new ConfigResult('XML', $configDefine, $value, "The xml must be valid. " .
                    "Perhaps the application that generated it didn't run correctly.  " .
                    "NOTE: When compiling PHP we have noticed that DOMXPATH does not work with some versions " .
                    "of LIBXML2. We have successfully tested using DOMXPATH with LIBXML2 version 2.6.16 " .
                    "(on Linux Centos) and 2.6.23 (latest version of LIBXML on Redhat Fedora 4), but it causes " .
                    "a failure in PHP with version 2.6.19 (on Linux Red Hat Fedora 4). We have only tested these " .
                    "three versions so if you are having problems with XML, try different versions."));
		}
		if ($debug) {
			echo "<pre>".print_r($dom->saveXML(),true)."</pre>";
		}
		if (!empty($xpath)) {
			$xp = new DOMXPath($dom);
			foreach ($ns_array as $prefix => $uri) {
				$xp->registerNamespace($prefix,$uri);
			}
			$res = $xp->query($xpath);
			if ($res->length < 1) {
				return array(new ConfigResult('XPath', $configDefine, $value, "The XML file" .
                        " doesn't have the required XML elements in it.  The application that " .
                        "generated it may not be working correctly."));
			}
		}
		return array();
	}

	function checkConnect($configDefine,$value)
	{
		list($server, $port) = explode(':', $value);
		$errno = '';
		$errstr = '';
		$fp = @fsockopen($server, $port, $errno, $errstr, 10);
		if (!$fp) {
			return array(new ConfigResult('Connect', $configDefine, $value, "Error: $errstr ($errno)." .
                    "The webserver couldn't connect to this address.  Check that the address is correct." .
                    "Perhaps it is blocked at a firewall."));
		}
		$teststr = "test";
		if (@fwrite($fp, $teststr) < strlen($teststr)) {
			return array(new ConfigResult('Connect', $configDefine, $value, "The webserver couldn't connect to " .
                    "this address.   Check that the address is correct." .
                    " Perhaps it is blocked at a firewall."));
		}
		fclose($fp);
		return array();
	}

	function checkHTTPConnect($configDefine,$value)
	{
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $value);
		curl_setopt ($ch, CURLOPT_NOBODY, 1);
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		if (APP_HTTPS_CURL_CHECK_CERT == "OFF")  {
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		$data = curl_exec ($ch);
		$info = curl_getinfo($ch);
		if (curl_errno($ch) != 0) {
			$errstr = curl_error($ch);
			return array(new ConfigResult('ConnectHTTP', $configDefine, $value, "Error: ".$errstr.". " .
                    "The webserver couldn't connect to this address.  Check that the address is correct. " .
                    "Perhaps it is blocked at a firewall.  Also check that CURL is correctly installed."));
		}
		curl_close ($ch);
		if ($info['http_code'] != 200) {
			return array(new ConfigResult('ConnectHTTP', $configDefine, $value,
                    "HTTP Result ".$info['http_code']." code. ".
                    "The webserver couldn't connect to this address.  Check that the address is correct. " .
                    "Check that any authorisation needed is correct. " .
                    "Perhaps it is blocked at a firewall."));
		}
		return array();
	}

	function checkSearchKeys()
	{
		$results = array(ConfigResult::message('Testing Search keys'));
		$searchKeys = Search_Key::getList();

		foreach ($searchKeys as $skey) {

			if($skey['key_table_exists'] == 0) {
				$results = array_merge($results, array(new ConfigResult('', $configDefine, $value, "One of the search keys doesn't " .
                    "have a corresponding database column or table. Please check search keys in Admin panel")));
				return $results;
			}
		}

		$results[] = ConfigResult::messageOk('All Search keys tests passed');
		return $results;
	}



	function handle()
	{
		$results = array(ConfigResult::message('Testing Handle/JAHDL'));
		if ( APP_HANDLE == "ON" ) {
			if ( HANDLE_NAMING_AUTHORITY_PREFIX > 0 ) {
				$handleNamespace = HANDLE_NAMING_AUTHORITY_PREFIX . HANDLE_NA_PREFIX_DERIVATIVE ;
				$createHandleRequestor = new HandleRequestor( $handleNamespace );
				if ( ! $createHandleRequestor->isJAHDLPresent() ) {
					$results[] = new ConfigResult('JAHDL: ', 'app_jahdl_dir', APP_JAHDL_DIR, '.  (Problem with JAHDL setup.)');
					return $results;
				}
				$createHandleRequestor->addCreateHandleRequest("feztestpid", "http://" . APP_HOSTNAME . APP_RELATIVE_URL . "view/" . APP_PID_NAMESPACE . ":feztestpid");
				if (! $createHandleRequestor->processHandleRequests() ) {
					$results[] = new ConfigResult('Handle: ', 'processHandleRequests', "false", '.  (Problem processing Create Handle request.)');
				}

				//If this test was really rigorous it would test to see if the handle now resolves to the URL however if it didn't
				//work error messages would be reported anyway.

				//Need to use a new handle because the same handle is now being deleted.
				$deleteHandleRequestor = new HandleRequestor( $handleNamespace );
				$deleteHandleRequestor->addDeleteHandleRequest("feztestpid");
				if (! $deleteHandleRequestor->processHandleRequests() ) {
					$results[] = new ConfigResult('Handle: ', 'processHandleRequests', "false", '.  (Problem processing Delete Handle request.)');
				}

			} else {
				$results[] = new ConfigResult('Handle: ', 'handle_naming_authority_prefix', 'null', '.  (Fez config handle_naming_authority_prefix is probably not set.)');
				return $results;
			}

			if (SanityChecks::resultsClean($results)) {
				$results[] = ConfigResult::messageOk('All Handle/JAHDL tests passed');
			}
			return $results;
		} else {
			return array();
		}
	}



	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// NEW STUFF HERE
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/* This is a prototype function for how we'll be doing sanity checking in the future. */
	function check_app_convert_cmd($value)
	{
		// This is where all sanity checks specifically relating to app_convert_cmd will appear.
		// Repeat for every other input / setting we may wish to examine.
		return "EVERYTHING LOOKS GOOOOOD!";     // LKDB
	}

}
