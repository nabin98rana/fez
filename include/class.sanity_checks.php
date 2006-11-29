<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 29/11/2006
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 *
 */
 
 class ConfigResult
 {
 	var $type;
    var $config;
    var $value;
    var $message;
    var $passed;
    
    function __construct($type, $config, $value, $message, $passed=false) {
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

    
    function runAllChecks()
    {
    	$results = array(); // array of ConfigResult objects
        $results = array_merge($results, SanityChecks::checkHTTPConnect('APP_BASE_URL',APP_BASE_URL));
        $results = array_merge($results, SanityChecks::dirs());
        if (!SanityChecks::resultsClean($results)) {
            // no point continuing if the basics aren't met
            return $results;
        }
        $results = array_merge($results, SanityChecks::jhove());
        $results = array_merge($results, SanityChecks::shib());
        $results = array_merge($results, SanityChecks::ldap());
        $results = array_merge($results, SanityChecks::imageMagick());
        return $results;
    }   
     
    function dirs()
    {
    	$results = array(ConfigResult::message('Testing general directories'));
    	$results = array_merge($results, SanityChecks::checkDir('APP_TEMP_DIR', APP_TEMP_DIR, true));
        $results = array_merge($results, SanityChecks::checkDir('APP_SAN_IMPORT_DIR', APP_SAN_IMPORT_DIR));
        $results = array_merge($results, SanityChecks::checkDir('WEBSERVER_CONFIG_PATH', WEBSERVER_CONFIG_PATH));
        $results = array_merge($results, SanityChecks::checkDir('WEBSERVER_LOG_DIR', WEBSERVER_LOG_DIR));
        $results = array_merge($results, SanityChecks::checkFile('WEBSERVER_LOG_DIR.WEBSERVER_LOG_FILE', WEBSERVER_LOG_DIR . WEBSERVER_LOG_FILE));
        $results = array_merge($results, SanityChecks::checkDir('APP_PATH/templates_c', APP_PATH."templates_c", true));
        if (APP_REPORT_ERROR_FILE) {
        	$results = array_merge($results, SanityChecks::checkFile('APP_ERROR_LOG', APP_ERROR_LOG, true));
        }
        if (SanityChecks::resultsClean($results)) {  
            $results[] = ConfigResult::messageOk('Testing general directories');
        }
        return $results;
    }
    
    function jhove()
    {
        $results = array(ConfigResult::message('Testing JHove'));
        // check that the executable is where we think it is
        $results = array_merge($results, SanityChecks::checkDir("APP_JHOVE_DIR",APP_JHOVE_DIR));
        $results = array_merge($results, SanityChecks::checkDir("APP_JHOVE_TEMP_DIR", APP_JHOVE_TEMP_DIR, true));
        if ((stristr(PHP_OS, 'win')) && (!stristr(PHP_OS, 'darwin'))) { // Windows Server
            $results = array_merge($results, SanityChecks::checkFile('APP_JHOVE_DIR/jhove.bat',
                APP_JHOVE_DIR."/jhove.bat", false, true));
        } else {
        	$results = array_merge($results, SanityChecks::checkFile('APP_JHOVE_DIR/jhove',
                APP_JHOVE_DIR."/jhove", false, true));
        }
        if (SanityChecks::resultsClean($results)) {
        	// if all the other checks have passed, we should be able to run jhove on a file
                copy(APP_PATH."images/1rightarrow_16.gif", APP_TEMP_DIR."test.gif");
                Workflow::checkForPresMD(APP_TEMP_DIR."test.gif");
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
            $results = array_merge($results, SanityChecks::checkXML('Shibboleth','SHIB_WAYF_METADATA_LOCATION',
                SHIB_WAYF_METADATA_LOCATION,"//md:EntitiesDescriptor/md:EntityDescriptor",
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
    
    function imageMagick()
    {
    	$results = array(ConfigResult::message('Testing imageMagick'));
        $results = array_merge($results, SanityChecks::checkFile('APP_CONVERT_CMD', APP_CONVERT_CMD, false, true));
        $results = array_merge($results, SanityChecks::checkFile('APP_COMPOSITE_CMD', APP_COMPOSITE_CMD, false, true));
        $results = array_merge($results, SanityChecks::checkFile('APP_IDENTIFY_CMD', APP_IDENTIFY_CMD, false, true));
        if (strlen(APP_WATERMARK) > 0) {
        	$results = array_merge($results, SanityChecks::checkFile('APP_PATH/images/APP_WATERMARK', APP_PATH."images/".APP_WATERMARK));
        }
        if (SanityChecks::resultsClean($results)) { 
            copy(APP_PATH."images/1rightarrow_16.gif", APP_TEMP_DIR."test.gif");
            $getString = APP_BASE_URL."webservices/wfb.image_resize.php?image="
                        .urlencode("test.gif")."&height=20&width=20&ext=jpg&outfile="."thumbnail_test.jpg";
            Misc::ProcessURL($getString);
            $results = array_merge($results, SanityChecks::checkFile('Check Image Convert Result', APP_TEMP_DIR."thumbnail_test.jpg"));
            @unlink(APP_TEMP_DIR."thumbnail_test.jpg");
        }
        if (!SanityChecks::resultsClean($results)) {
        	$results[] = ConfigResult::message('Sometimes a problem with image magick on windows is that ' .
                    'the image magick command \'convert\' needs to be in the path. ');
        } 
        
        // check copyright
        if (SanityChecks::resultsClean($results)) { 
            copy(APP_PATH."images/1rightarrow_16.gif", APP_TEMP_DIR."test.gif");
            $getString = APP_BASE_URL."webservices/wfb.image_resize.php?image="
                        .urlencode("test.gif")."&height=20&width=20&ext=jpg&outfile="."thumbnail_test.jpg&copyright=hello";
            Misc::ProcessURL($getString);
            $results = array_merge($results, SanityChecks::checkFile('Run Image Convert', APP_TEMP_DIR."thumbnail_test.jpg"));
            @unlink(APP_TEMP_DIR."thumbnail_test.jpg");
        }
        // check watermark
        if (SanityChecks::resultsClean($results)) { 
            copy(APP_PATH."images/1rightarrow_16.gif", APP_TEMP_DIR."test.gif");
            $getString = APP_BASE_URL."webservices/wfb.image_resize.php?image="
                        .urlencode("test.gif")."&height=20&width=20&ext=jpg&outfile="."thumbnail_test.jpg&watermark=1";
            Misc::ProcessURL($getString);
            $results = array_merge($results, SanityChecks::checkFile('Run Image Convert', APP_TEMP_DIR."thumbnail_test.jpg"));
            @unlink(APP_TEMP_DIR."thumbnail_test.jpg");
        }
        // check copyright and watermark
        if (SanityChecks::resultsClean($results)) { 
            copy(APP_PATH."images/1rightarrow_16.gif", APP_TEMP_DIR."test.gif");
            $getString = APP_BASE_URL."webservices/wfb.image_resize.php?image="
                        .urlencode("test.gif")."&height=20&width=20&ext=jpg&outfile="."thumbnail_test.jpg&watermark=1&copyright=hello";
            Misc::ProcessURL($getString);
            $results = array_merge($results, SanityChecks::checkFile('Run Image Convert', APP_TEMP_DIR."thumbnail_test.jpg"));
            @unlink(APP_TEMP_DIR."thumbnail_test.jpg");
        }
        if (SanityChecks::resultsClean($results)) { 
            $results[] = ConfigResult::messageOk('All imageMagick tests passed');
        }
        return $results;
    }
    
    function backgroundProcess()
    {
    	
    }
    
    function dot()
    {
    	
    }
    
    function tidy()
    {
    	
    } 
    
    function fedora()
    {
    	
    } 
    
    function sql()
    {
    	
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
        if (!is_dir($value)) {
            return array(new ConfigResult('Directory', $configDefine, $value, "Failed is_dir"));
        }
        $dh = @opendir($value);
        if (!$dh) {
            return array(new ConfigResult('Directory', $configDefine, $value, 
                "Failed opendir (probably a permissions problem)"));
        }
        closedir($dh);
        if ($writable) {
            $tmpfname = tempnam($value, "FOO");
            $teststr = "This is a test";
            if (@file_put_contents($tmpfname, $teststr) < strlen($teststr)) {
                return array(new ConfigResult('Directory', $configDefine, $value, "Failed to write a file"));
            }
            unlink($tmpfname);
        }
        
        return array();
    }
    
    function checkFile($configDefine, $value, $writeable = false, $exec = false) 
    {
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
        return array();
    }

    function checkXML($configDefine, $value, $xpath = '', $ns_array = array(), $debug = false)
    {
        $results = SanityChecks::checkFile($configDefine,$value);
        if (!empty($results)) {
        	return $results;
        }
        $dom = DOMDocument::load($value);
        if (!$dom) {
            return array(new ConfigResult('XML', $configDefine, $value, "The file must be valid" .
                    " XML.  Perhaps the application that generated it didn't run correctly."));
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
       if (APP_HTTPS_CURL_CHECK_CERT == "OFF")  {
         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
         curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
       }
       $data = curl_exec ($ch);  
       $info = curl_getinfo($ch);
       if (curl_errno($ch) != 0) {
            $errstr = curl_error($ch);
            return array(new ConfigResult('ConnectHTTP', $configDefine, $value, "Error: $errstr. " .
                    "The webserver couldn't connect to this address.  Check that the address is correct. " .
                    "Perhaps it is blocked at a firewall."));
       }
       curl_close ($ch);
       if ($info['http_code'] != 200) { 
            return array(new ConfigResult('ConnectHTTP', $configDefine, $value, 
                    "The webserver couldn't connect to this address.  Check that the address is correct. " .
                    "Perhaps it is blocked at a firewall."));
       }      
       return array();
    }


 }
?>
