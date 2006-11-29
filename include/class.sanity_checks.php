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
    	$tmp = new ConfigResult('_message', '','',$msg);
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
        $results = array_merge($results, SanityChecks::dirs());
        $results = array_merge($results, SanityChecks::jhove());
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
        if (count($results) == 1) {  // no messages other than the intro
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
        if (count($results) == 1) { // no messages other than the intro
        	// if all the other checks have passed, we should be able to run jhove on a file
                copy(APP_PATH."images/1rightarrow_16.gif", APP_TEMP_DIR."test.gif");
                Workflow::checkForPresMD(APP_TEMP_DIR."test.gif");
              	$result = SanityChecks::checkXML('Jhove Result',APP_TEMP_DIR."presmd_test.xml",
                    '/j:jhove/j:repInfo/j:mimeType[\'image/gif\']', 
                    array('j' => 'http://hul.harvard.edu/ois/xml/ns/jhove'));
                $results = array_merge($results, $result);
                @unlink(APP_TEMP_DIR."presmd_test.xml");
                @unlink(APP_JHOVE_TEMP_DIR."test.gif");
        }
        if (count($results) == 1) { // no messages other than the intro
        	$results[] = ConfigResult::messageOk('All JHove tests passed');
        }
        return $results;
    }	
    
    function logging()
    {
    	
    }
    
    function shib()
    {
    	
    }
    
    function ldap()
    {
    	
    } 
    
    function imageMagick()
    {
    	
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
            return array(new ConfigResult('File', $configDefine, $value, "Failed is_file"));
        }
        if ($exec) {
            if (!is_executable($value)) {
                return array(new ConfigResult('File', $configDefine, $value, "Failed is_executable"));
            }
        }
        if ($writeable) {
            if (!is_writable($value) ) {
                return array(new ConfigResult('File', $configDefine, $value, "Failed is_writable"));
            }
        }
        return array();
    }

    function checkXML($configDefine, $value, $xpath = '', $ns_array = array())
    {
        $results = SanityChecks::checkFile($configDefine,$value);
        $dom = DOMDocument::load($value);
        if (!$dom) {
            return array(new ConfigResult('XML', $configDefine, $value, "XML Parse failed"));
        }
        //print_r($dom->saveXML());
        if (!empty($xpath)) {
            $xp = new DOMXPath($dom);
            foreach ($ns_array as $prefix => $uri) {
                $xp->registerNamespace($prefix,$uri);
            }
            $res = $xp->query($xpath);
            if ($res->length < 1) {
                return array(new ConfigResult('XPath', $configDefine, $value, "XPath not found"));
            }
        }
        return array();
    }
    

 }
?>
