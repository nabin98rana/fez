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
 * Class to manage all tasks related to error conditions of the site, such as
 * logging facilities or alert notifications to the site administrators.
 *
 * @version 1.0
 * @author Jo�o Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.setup.php");


class Error_Handler
{
 
    static $app_errors = array();
    static $debug_on = false;
    
    /**
     * Logs the specified error
     *
     * @access public
     * @param  string $error_code The error code
     * @param  string $error_msg The error message
     * @param  string $script The script name where the error happened
     * @param  int $line The line number where the error happened
     */
    function logError($error_msg = "", $script = "", $line = "")
    {
       
         $txt = print_r($error_msg, true);
         $error = array(
             'txt' => explode("\n",$txt),
             'script' => $file = str_replace(APP_PATH,'',$script),
             'line' => $line
         );
         $error_display = $error;
		 if ((APP_DEBUG_LEVEL == 2) || (APP_DEBUG_LEVEL == 3))  { //debug level 2 or 3
            $backtrace = debug_backtrace();
            $output_processed = array();
            $output_processed_display = array();            
           foreach ($backtrace as $bt) {
               $args = '';
			   if (APP_DEBUG_LEVEL == 3) {
	               foreach ($bt['args'] as $a) {
	                   if (!empty($args)) {
	                       $args .= ', ';
	                   }
	                   switch (gettype($a)) {
	                   case 'integer':
	                   case 'double':
	                       $args .= $a;
	                       break;
	                   case 'string':
	                       $a = htmlspecialchars(substr($a, 0, 64)).((strlen($a) > 64) ? '...' : '');
	                       $args .= "\"$a\"";
	                       break;
	                   case 'array':
	                       $args .= 'Array('.count($a).')';
	                       break;
	                   case 'object':
	                       $args .= 'Object('.get_class($a).')';
	                       break;
	                   case 'resource':
	                       $args .= 'Resource('.strstr($a, '#').')';
	                       break;
	                   case 'boolean':
	                       $args .= $a ? 'True' : 'False';
	                       break;
	                   case 'NULL':
	                       $args .= 'Null';
	                       break;
	                   default:
	                       $args .= 'Unknown';
	                   }
	               }
			   }
               $output_processed_item = array();
               $output_processed_item_display = array();               
               $file = str_replace(APP_PATH,'',$bt['file']);
               $output_processed_item['file'] = $file.":".$bt['line'];
               $output_processed_item['call'] = $bt['class'].$bt['type'].$bt['function'].($args);               
               $output_processed_item_display['file'] = $file.":".$bt['line'];
               if ((APP_DISPLAY_ERROR_LEVEL == 3) && (APP_DEBUG_LEVEL == 3)){
	               $output_processed_item_display['call'] = $bt['class'].$bt['type'].$bt['function'].($args);
               } elseif (APP_DISPLAY_ERROR_LEVEL == 2) {
               	   $output_processed_item_display['call'] = $bt['class'].$bt['type'].$bt['function'];
               }
               
               $output_processed[] = $output_processed_item;
			   $output_processed_display[] = $output_processed_item_display;               
           }
	       $error['backtrace'] = $output_processed;            
           
           if ((APP_DISPLAY_ERROR_LEVEL == 2) || (APP_DISPLAY_ERROR_LEVEL == 3)) {
	           $error_display['backtrace'] = $output_processed_display;            
	           Error_Handler::$app_errors[] = $error_display; 
           } elseif (APP_DISPLAY_ERROR_LEVEL == 1) {
           	   Error_Handler::$app_errors[] = $error_display; 
           }
            // echo "<div class=\"app_error\">$txt $script $line</div>";
			
            
        } else {
        	$error = $error_msg;
        	if ((APP_DISPLAY_ERROR_LEVEL == 1) || (APP_DISPLAY_ERROR_LEVEL == 2) || (APP_DISPLAY_ERROR_LEVEL == 3)) {
        		 Error_Handler::$app_errors[] = $error_display; 
        	}
        }
        if (APP_REPORT_ERROR_FILE) {
            Error_Handler::_logToFile($error, $script, $line);
        }
        $setup = Setup::load();
        if (@$setup['email_error']['status'] == 'enabled') {
            Error_Handler::_notify($error, $script, $line);
        }
    }


    /**
     * Notifies site administrators of the error condition
     *
     * @access public
     * @param  string $error_msg The error message
     * @param  string $script The script name where the error happened
     * @param  int $line The line number where the error happened
     */
    function _notify($error_msg = "unknown", $script = "unknown", $line = "unknown")
    {
        global $HTTP_SERVER_VARS;

        $setup = Setup::load();
        $notify_list = trim($setup['email_error']['addresses']);
        if (empty($notify_list)) {
            return false;
        }
        $notify_list = str_replace(';', ',', $notify_list);
        $notify_list = explode(',', $notify_list);

        $subject = APP_SITE_NAME . " - Error found! - " . date("m/d/Y H:i:s");
        $msg = "Hello,\n\n";
        $msg .= "An error was found at " . date("m/d/Y H:i:s") . " (" . time() . ") on line '" . $line . "' of script " . "'$script'.\n\n";
        $msg .= "The error message passed to us was:\n\n";
        $msg .= print_r($error_msg,true)."\n";
        $msg .= "That happened on page '" . $HTTP_SERVER_VARS["PHP_SELF"] . "' from IP Address '" . getenv("REMOTE_ADDR") . "' coming from the page (referrer) '" . getenv("HTTP_REFERER") . "'.\n\n";
        $msg .= "Sincerely yours,\nAutomated Error_Handler Class";
        foreach ($notify_list as $notify_email) {
     //       $mail = new Mail_API;
     //       $mail->setTextBody($msg);
     //       $mail->send($setup['smtp']['from'], $notify_email, $subject);
        }
    }


    /**
     * Logs the error condition to a specific file
     *
     * @access public
     * @param  string $error_msg The error message
     * @param  string $script The script name where the error happened
     * @param  int $line The line number where the error happened
     */
    function _logToFile($error_msg = "unknown", $script = "unknown", $line = "unknown")
    {
        global $HTTP_SERVER_VARS;
        $msg = "[" . date("D M d H:i:s Y") . "] " . print_r($error_msg,true) ."\n";
//		echo $msg;
//		echo APP_ERROR_LOG;
        $fp = @fopen(APP_ERROR_LOG, "a");
        @fwrite($fp, $msg);
        @fclose($fp);
    }


	function simpleBacktrace($backtrace = array()) {
		$sbt = "";
		$errorCounter = 0;
		foreach ($backtrace as $bt) {
			$errorCounter++;
			$sbt .= "\n<br/> #".$errorCounter." ".$bt['function']." called at [".$bt['file'].":".$bt['line']."]";			
		}
		return $sbt."\n";
	}
    
    /**
     * @param string $initials - Your initials.  e.g. MSS =>  /tmp/fez_debug_mss.txt
     * @param array $var - the variable to be debugged - use compact to create it, e.g. compact('thing');
     */
    function debug($initials, $vars)
    {
        if (!self::$debug_on) {
            return;
        }
        file_put_contents('/tmp/fez_debug_'.strtolower($initials).'.txt',date('r').' -> ', FILE_APPEND);
        $index = 0;
        foreach ($vars as $key => $var ) {
            if ($index++ > 0) {
                file_put_contents('/tmp/fez_debug_'.strtolower($initials).'.txt', ', ', FILE_APPEND);
            }
            file_put_contents('/tmp/fez_debug_'.strtolower($initials).'.txt', 
                $key.':'.print_r($var,true), FILE_APPEND);
        }
        file_put_contents('/tmp/fez_debug_'.strtolower($initials).'.txt', "\n", FILE_APPEND);
    }
    
    function debugStart()
    {
        self::$debug_on = true;
    }
    
    function debugStop()
    {
        self::$debug_on = false;
    }
}
// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Error_Handler Class');
}
?>
