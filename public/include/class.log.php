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
// |          Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle application logging
 *
 * @version 1.0
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 */
class FezLog
{
	private $_logs = null;
	private $_stopwatch = null;
	private $_use_firebug = false;
	public $log_trace = false;
	private $_channel = null;
	private $_response= null;
  public $solr_query_time = array();
  public $solr_query_string = array();
	private $_logLevel = null;

	public function __construct($logs, $use_firebug = false, $log_trace = false)
	{
		$this->_logs = $logs;
		$this->_firebug = false;
		$this->log_trace = $log_trace;
		$this->_logLevel = intval(APP_LOG_LEVEL);

		if($use_firebug) {
			$this->_use_firebug = true;
			$request  = new Zend_Controller_Request_Http();
			$this->_response = new Zend_Controller_Response_Http();
			$this->_channel  = Zend_Wildfire_Channel_HttpHeaders::getInstance();
			$this->_channel->setRequest($request);
			$this->_channel->setResponse($this->_response);
			ob_start();
		}
		$this->_stopwatch = new StopWatch();
		$this->debug('Start');
		Zend_Registry::set('fezlog', $this);
	}


    //Pretty stacktrace
    private function generateStacktrace()
    {
        $e = new Exception();
        $trace = explode("\n", $e->getTraceAsString());
        // reverse array to make steps line up chronologically
        $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        $length = count($trace);
        $result = array();

        for ($i = 0; $i < $length; $i++)
        {
            $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
        }

        return "\t" . implode("\n\t", $result);
    }

	/**
	 *
	 * @return FezLog
	 */
	public static function get()
	{
		$log = Zend_Registry::get('fezlog');
		if($log->log_trace) {
			$trace = debug_backtrace();
            if (array_key_exists(1, $trace) && array_key_exists('class', $trace[1])) {
                $log->debug_method($trace[1]['class'].$trace[1]['type'].$trace[1]['function'], $end = false);
            }
		}
		return $log;
	}

	public function emerg($message)
	{
		foreach($this->_logs as $log)
			$log['log']->emerg($this->format_message($log['type'], $message));

		error_log($this->format_message('file', $message));
	}

	public function alert($message)
	{
		foreach($this->_logs as $log)
			$log['log']->alert($this->format_message($log['type'], $message));

		if ($this->_logLevel >= 1) {
			error_log($this->format_message('file', $message));
		}
	}

	public function crit($message)
	{
		foreach($this->_logs as $log)
			$log['log']->crit($this->format_message($log['type'], $message));

		if ($this->_logLevel >= 2) {
			error_log($this->format_message('file', $message));
		}
	}

	public function err($message)
	{

        if (is_array($message)) {
            $message[] = $this->generateStacktrace();

        } else {
            $message .= "\n" . $this->generateStacktrace() . "\n";
        }

		foreach($this->_logs as $log)
			$log['log']->err($this->format_message($log['type'], $message));

		if ($this->_logLevel >= 3) {
			error_log($this->format_message('file', $message));
		}
	}

	public function warn($message)
	{
		foreach($this->_logs as $log)
			$log['log']->warn($this->format_message($log['type'], $message));

		if ($this->_logLevel >= 4) {
			error_log($this->format_message('file', $message));
		}
	}

	public function notice($message)
	{
		foreach($this->_logs as $log)
			$log['log']->notice($this->format_message($log['type'], $message));

		if ($this->_logLevel >= 5) {
			error_log($this->format_message('file', $message));
		}
	}

	public function info($message)
	{
		foreach($this->_logs as $log)
			$log['log']->info($this->format_message($log['type'], $message));

		if ($this->_logLevel >= 6) {
			error_log($this->format_message('file', $message));
		}
	}

	public function debug($message)
	{
		foreach($this->_logs as $log)
			$log['log']->debug($this->format_message($log['type'], $message));

		if ($this->_logLevel >= 7) {
			error_log($this->format_message('file', $message));
		}
	}

	public function debug_method($name)
	{
        $function = array('class'=>'', 'type'=>'', 'function'=>'', 'file'=>'', 'line'=>'');
		$function = array_merge($function, $this->_getBacktraceElemFromFuncName($name));

  		if(! is_array($function['args']))
				$function['args'] = array();

		$args = array();
		foreach($function['args'] as $arg) {
			if (is_object($arg))
  				$args[] = var_export($arg, true);
  			else
  				$args[] = $arg;
		}

        $message = $function['class'] . $function['type'] .
                   $function['function'] . '(' . Misc::multi_implode(', ', $args) . ')' .
                   ' - ' .
                   $function['file'] . '(' . $function['line'] . ')';

		$this->debug($message);
	}

    public function close()
    {
    	$this->debug('End');

    	if($this->_use_firebug) {
    		$this->_channel->flush();
			$this->_response->sendHeaders();
    	}
    }

    public function getLogElapsedTime()
    {
    	return $this->_stopwatch->elapsed();
    }

  private function format_message($type, $message)
    {
    	$user_message = array();

    	if(! empty($_SESSION['username'])) {
    		$user_message = array(
    							'usr_username' => $_SESSION['username'],
    							'usr_full_name' => $_SESSION['fullname'],
    							'usr_email' => $_SESSION['email'],
    							'login_time' => $_SESSION['login_time']
    						);
    	}
    	else {
    		$user_message = array('Public user');
    	}

    	switch($type) {
    		case 'file':
    			if(is_object($message) && is_subclass_of($message, 'Exception')) {
    				return print_r($user_message, true) . print_r(array('Exception Message' => $message->getMessage()), true) . print_r($message->getTrace(), true);
                } elseif (APP_DEBUG_LEVEL == '3') { //if highest level of logging then include the full backtrace
    				return print_r($user_message, true) . print_r($message, true) . print_r(debug_backtrace(false), true);
                } else {
                    return print_r($user_message, true) . print_r($message, true);
                }
    		case 'firebug':
    			return $this->_stopwatch->elapsed().' '.print_r($message, true);
    		default:
    			return $this->_stopwatch->elapsed().' '.print_r($message, true);
    			//return array('Time'=>$this->_stopwatch->elapsed(), 'Message'=>$message);
    	}
    }

	private function _getBacktraceElemFromFuncName( $target, $subclass_ok = true )
	{
	    if( strpos( $target, "::" ) ) {
	        list( $class, $target ) = explode( "::", $target, 2 );
	        $type = "::";
	    }
	    else if( strpos( $target, "->" ) ) {
	        list( $class, $target ) = explode( "->", $target, 2 );
	        $type = "->";
	    }
	    else {
	        $type = NULL;
	        $class = NULL;
	    }
	    $class and $class = new ReflectionClass( $class );

	    foreach( debug_backtrace() as $obj ) {

	        if( $obj['function'] == $target ) {
	            if( $type and $obj['type'] == $type ) {
	                $_cl = new ReflectionClass( $obj['class'] );
	                if( $_cl->getName() == $class->getName() or ( $subclass_ok and $_cl->isSubclassOf( $class ) ) ) {
	                    return $obj;
	                }
	                unset( $_cl );
	            }
	            else if( !$type ) {
	                return $obj;
	            }
	        }
	    }
	    return NULL;
	}
}

/**
 * StopWatch Class
 *
 * @version 1.0
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 */
class StopWatch
{
    public $total;
    public $time;

    public function __construct()
    {
        $this->total = $this->time = microtime(true);
    }

    public function elapsed()
    {
    	$elapsed = microtime(true) - $this->total;
        return sprintf('%0.5f', (string)round($elapsed,5));
    }

    public function reset()
    {
        $this->total=$this->time=microtime(true);
    }
} 