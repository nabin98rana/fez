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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//

include_once('config.inc.php');
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.researcherid.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");

Auth::checkAuthentication(APP_SESSION);
$user = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($user);
$isSuperAdministrator = User::isUserSuperAdministrator($user);

if(! $isAdministrator || ! $isSuperAdministrator) {
	exit;
}

$server = new Zend_Json_Server();
$server->setClass('ResearcherIDProxy');

if ('GET' == $_SERVER['REQUEST_METHOD']) {
    
	$server->setTarget($_SERVER["SCRIPT_NAME"])
           ->setEnvelope(Zend_Json_Server_Smd::ENV_JSONRPC_2);
    $smd = $server->getServiceMap();
    $smd->setDojoCompatible(true);
    header('Content-Type: application/json');
    echo $smd;
    return;
}
$server->handle();

class ResearcherIDProxy
{
    /**
     * Creates a ResearcherID account for an author
     *
     * @param  string $aut_id ID of the author to create a ResearcherID account for
     * @return string
     */
    public function register($aut_id)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
		if(ResearcherID::profileUpload(array($aut_id))) {
			Author::setResearcherIdByAutId($aut_id, '-1');
			return 'true'; 
		}
		else {
			return 'false';
		}
    }
    
    /**
     * Downloads publications for a researcher using the ResearcherID Batch Download Service
     * @param string $researcher_id The ResearcherID to downlaod publications for 
     * @return string
     */
    public function download($researcher_id) 
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
		if(ResearcherID::downloadRequest(array($researcher_id), 'researcherIDs', 'researcherID')) {
			return 'true'; 
		}
		else {
			return 'false';
		}
    }
}
