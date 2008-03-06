<?php

/**
 * Convenience class for logging using the PEAR Log package. 
 *
 * @author Kai Jauslin <kai.jauslin@library.ethz.ch>
 * 
 */

include_once("Log.php");

class Logger {
	
	static function getLogger() {
		
		$opts = array ( 
            'append' => true, 
            'mode'   => 0640, 
            'lineFormat' => 
            '%{ident};%{timestamp};%{priority};%{message}', 
            'timeFormat' => '%H:%m:%s', 
            'eol' => "\r\n" 
        );
        
		$log = &Log::singleton("file", APP_LOGGING_DESTINATION);
		//$log->setMask($log->UPTO(APP_LOGGING_LEVEL));
		return $log;
	}
	
	static function debug($message) { 
		$log = Logger::getLogger();
		$pid = getmypid();
		$message = "[".$pid."] ". $message;
		$log->debug($message);
	}

	static function warn($message) { 
		$log = Logger::getLogger();
		$log->warning($message);
	}

	static function error($message) { 
		$log = Logger::getLogger();
		$log->err($message);
	}

	static function info($message) { 
		$log = Logger::getLogger();
		$log->info($message);
	}

	static function crit($message) { 
		$log = Logger::getLogger();
		$log->crit($message);
	}
	
	// returns result of print_r as string
	static function str_r($var) {
	    ob_start();
		print_r($var);
    	$result = ob_get_contents();
    	ob_end_clean(); 
    	return $result;
	}
}


?>