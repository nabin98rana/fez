<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005-2010 The University of Queensland,                |
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
// | Authors: Marko Tsoi <m.tsoi@library.uq.edu.au>                       |
// +----------------------------------------------------------------------+

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH.'/class.integrity_check.php');

// get the command line options (show warning message if nothing passed in)
if( $argc != 2 ) {
	displayUsage();
    exit(-1);
}

$runType = strtolower($argv[1]);
if (!in_array($runType, array('check','fix','both'))) {
	echo "\nERROR: Invalid mode '{$runType}'\n";
	displayUsage();
	exit(-2);
}

echo "Script started: " . date("Y-m-d H:i:s") . "\n";
main($runType);
echo "Script Finished: " . date("Y-m-d H:i:s") . "\n";

/**
 * Main function, runs everything
 *
 * @param string $runType
 * @return void
 **/
function main($runType = "check") {
	// run checks
	$check = new IntegrityCheck();
	$check->run($runType);
}

/**
 * helper function to display the usage of this script
 *
 * @return void
 **/
function displayUsage() {
	$prefix = APP_TABLE_PREFIX;
	$scriptName = basename(__FILE__);
	echo "\nUsage: php {$scriptName} [check|fix|both]\n";
	echo " - check = Run the checks, output into the {$prefix}integrity_* tables\n";
	echo " - fix = Fix the problems based on a previous run of this script with the 'check' option\n";
	echo " - both = Run the checks, then the deletes\n";
	echo "\n";
}
