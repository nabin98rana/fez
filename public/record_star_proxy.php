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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//

include_once('config.inc.php');
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.favourites.php");

Auth::checkAuthentication(APP_SESSION);
$user = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($user);
$isSuperAdministrator = User::isUserSuperAdministrator($user);

if (!$user) {
	exit;
}

$server = new Zend_Json_Server();
$server->setClass('RecordStarProxy');

if ('GET' == $_SERVER['REQUEST_METHOD']) {    
	$server->setTarget($_SERVER["SCRIPT_NAME"])->setEnvelope(Zend_Json_Server_Smd::ENV_JSONRPC_2);
	$smd = $server->getServiceMap();
	$smd->setDojoCompatible(true);
	header('Content-Type: application/json');
	echo $smd;
	
	return;
}

$server->handle();

class RecordStarProxy
{
	/**
	 * Stars the specified PID for the current user
	 *
	 * @param  string $pid PID of the record we wish to star
	 *
	 * @return string
	 */
	public function star($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (Favourites::star($pid)) {
			return 'true'; 
		} else {
			return 'false';
		}
	}
	
	
	
	/**
	 * Un-stars the specified PID for the current user
	 *
	 * @param  string $pid PID of the record we wish to un-star
	 *
	 * @return string
	 */
	public function unstar($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (Favourites::unstar($pid)) {
			return 'true'; 
		} else {
			return 'false';
		}
	}

    /**
   	 * Stars the specified search for the current user
   	 *
   	 * @param  string $searchLocation of the search we wish to star
   	 * @return string
   	 */
   	public function starSearch($searchLocation, $description) //, $alias, $email
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

   		if (Favourites::starSearch($searchLocation, $description)) {
   			return 'true';
   		} else {
   			return 'false';
   		}
   	}



   	/**
   	 * Un-stars the specified searchLocation for the current user
   	 *
   	 * @param  string $searchLocation of the record we wish to un-star
   	 *
   	 * @return string
   	 */
   	public function unstarSearch($searchLocation)
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

   		if (Favourites::unstarSearch($searchLocation)) {
   			return 'true';
   		} else {
   			return 'false';
   		}
   	}
}
