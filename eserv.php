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

include_once("config.inc.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.fedora_api.php");

$tpl = new Template_API();
$tpl->setTemplate("view.tpl.html");


$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = User::isUserAdministrator($username);
if (Auth::userExists($username)) { // if the user is registered as a Fez user
	$tpl->assign("isFezUser", $username);
}
$tpl->assign("isAdministrator", $isAdministrator);


if ( (is_numeric(strpos($_GET['pid'], ".."))) && (is_numeric(strpos($_GET['dsID'], "..")))) { 
	die; 
} // to stop haxors snooping our confs

$pid = @$HTTP_POST_VARS["pid"] ? $HTTP_POST_VARS["pid"] : $HTTP_GET_VARS["pid"];
$dsID = @$HTTP_POST_VARS["dsID"] ? $HTTP_POST_VARS["dsID"] : $HTTP_GET_VARS["dsID"];
$tpl->assign("pid", $pid);
$tpl->assign("dsID", $dsID);
if (!empty($pid) && !empty($dsID)) {
	$xdis_array = Fedora_API::callGetDatastreamContentsField ($pid, 'FezMD', array('xdis_id'));
	$xdis_id = $xdis_array['xdis_id'][0];
	if (is_numeric($xdis_id)) {	
		$acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");
		if (Auth::checkAuthorisation($pid, $acceptable_roles, $HTTP_SERVER_VARS['PHP_SELF']."?".urlencode($HTTP_SERVER_VARS['QUERY_STRING'])) == true) {
			$urldata = APP_FEDORA_GET_URL."/".$pid."/".$dsID; // this should stop them dang haxors (forces the http on the front for starters)
			$urlpath = $urldata;
			if (!is_numeric(strpos($dsID, "thumbnail"))) {
				Record::incrementFileDownloads($pid); //increment FezMD.file_downloads counter
			}
			$file_extension = strtolower(substr( strrchr( $urldata, "." ), 1 ));
			switch( $file_extension ) {
			
			case 'pdf'  :
					Header("Content-type: application/pdf\n");
					break;
			
			case 'xls'  :
					Header("Content-type: application/vnd.ms-excel\n");
					break;
			
			case 'doc'  :
					Header("Content-type: application/msword\n");
					break;
			
			case 'ica'  :
					Header("Content-type: application/x-ica\n");
					break;
			
			case 'gif'  :
					Header("Content-type: image/gif\n");
					break;
			
			case 'bmp'  :
					Header("Content-type: image/bmp\n");
					break;
			
			case 'jpg'  :
					Header("Content-type: image/jpg\n");
					break;
			case 'jpeg'  :
					Header("Content-type: image/jpg\n");
					break;
			case 'ico'  :
					Header("Content-type: image/ico\n");
					break;
			
			case 'ppt'  :
					Header("Content-type: application/vnd.ms-powerpoint");
					break;
			case 'txt'  :
					Header("Content-type: text/plain");
					break;

			default		:
					Header("Content-type: text/xml");
					break;
			
			} // end switch field_extension
			
  	 		header('Content-Disposition: filename="'.substr($urldata, (strrpos($urldata, '/')+1) ).'"');
			$tempDumpFileName = APP_TEMP_DIR.'tmpdumpfile.txt';
			// Read the source OAI repository url or file
			
			$sourceOAI = fopen($urldata, "r");
			$sourceOAIRead = '';
			while ($tmp = fread($sourceOAI, 1024))
			{
			$sourceOAIRead .= $tmp;
			}
			
			$tempDump = fopen($tempDumpFileName, 'w');

			// Write the source xml to a temporary file to we can get the filesize (required for the content length header)
			fwrite($tempDump, $sourceOAIRead);
			
			fclose($tempDump); 
			
			$tempDump = "";
			$tempDump = fopen($tempDumpFileName, 'r');
			$sourceOAIRead = fread($tempDump, filesize($tempDumpFileName));
			header("Content-length: " . filesize($tempDumpFileName) . "\n");
			echo $sourceOAIRead;
			die;
		} else {
			$tpl->assign("show_not_allowed_msg", true);
			$tpl->displayTemplate();
		}
	} else {
		$tpl->assign("show_not_allowed_msg", true);
		$tpl->displayTemplate();
	}		
} else {
	$tpl->assign("show_not_allowed_msg", true);
	$tpl->displayTemplate();
}



?>

