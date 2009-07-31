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

//include_once(APP_INC_PATH . "class.misc.php");
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
    	$log = FezLog::get();
    	$log->err(array('Message' => $error_msg, 'File' => $script, 'Line' => $line));    	
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
    	$log = FezLog::get();
    	$log->debug(array('Message' => $vars, 'Initials' => $initials));
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
