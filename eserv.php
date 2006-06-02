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

$username = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($username);

$pid = @$HTTP_POST_VARS["pid"] ? $HTTP_POST_VARS["pid"] : $HTTP_GET_VARS["pid"];
$dsID = @$HTTP_POST_VARS["dsID"] ? $HTTP_POST_VARS["dsID"] : $HTTP_GET_VARS["dsID"];

if ( (is_numeric(strpos($pid, ".."))) && (is_numeric(strpos($dsID, "..")))) { 
	die; 
} // to stop haxors snooping our confs
$is_image = 0;
if (!empty($pid) && !empty($dsID)) {
	$file_extension = strtolower(substr( strrchr( $dsID, "." ), 1 ));
	switch( $file_extension ) {
		
		case 'pdf'  :
				$header = "Content-type: application/pdf\n";
				break;
		
		case 'xls'  :
				$header = "Content-type: application/vnd.ms-excel\n";
				break;
		
		case 'doc'  :
				$header = "Content-type: application/msword\n";
				break;
		
		case 'ica'  :
				$header = "Content-type: application/x-ica\n";
				break;
		
		case 'gif'  :
				$is_image = 1;
				$header = "Content-type: image/gif\n";
				break;
		
		case 'tif'  :
				$is_image = 1;
				$header = "Content-type: image/tif\n";
				break;
		case 'tiff'  :
				$is_image = 1;
				$header = "Content-type: image/tiff\n";
				break;
		case 'bmp'  :
				$is_image = 1;
				$header = "Content-type: image/bmp\n";
				break;
		
		case 'jpg'  :
				$is_image = 1;
				$header = "Content-type: image/jpeg\n";
				break;
		case 'jpeg'  :
				$is_image = 1;
				$header = "Content-type: image/jpeg\n";
				break;
		case 'ico'  :
				$is_image = 1;
				$header = "Content-type: image/ico\n";
				break;
		
		case 'ppt'  :
				$header = "Content-type: application/vnd.ms-powerpoint";
				break;
		case 'txt'  :
				$header = "Content-type: text/plain";
				break;
		
		default		:
				$header = "Content-type: text/xml";
				break;
	
	} // end switch field_extension
	
	if (($is_image == 1) && (is_numeric(strpos($dsID, "archival_"))) ) { // if its trying to find the archival version then check
		$real_dsID = str_replace("archival_", "", $dsID);
		$acceptable_roles = array("Community_Admin", "Editor", "Creator", "Archival_Viewer");
	} elseif (($is_image == 1) && (!is_numeric(strpos($dsID, "web_"))) && (!is_numeric(strpos($dsID, "preview_"))) && (!is_numeric(strpos($dsID, "thumbnail_"))) ) {
		$real_dsID = "web_".substr($dsID, 0, strrpos($dsID, ".") + 1)."jpg";
		$acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");
		$header = "Content-type: image/jpeg\n";
	} else {
		$real_dsID = $dsID;
		$acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");
	}

	$xdis_array = Fedora_API::callGetDatastreamContentsField ($pid, 'FezMD', array('xdis_id'));
	$xdis_id = $xdis_array['xdis_id'][0];
	if (is_numeric($xdis_id)) {	
		if (Auth::checkAuthorisation($pid, $dsID, $acceptable_roles, $HTTP_SERVER_VARS['PHP_SELF']."?".urlencode($HTTP_SERVER_VARS['QUERY_STRING'])) == true) {
			$urldata = APP_FEDORA_GET_URL."/".$pid."/".$real_dsID; // this should stop them dang haxors (forces the http on the front for starters)
			$urlpath = $urldata;
			ob_start();
		    header($header);
  	 		header('Content-Disposition: filename="'.substr($urldata, (strrpos($urldata, '/')+1) ).'"');
		    header('Pragma: private');
		    header('Cache-control: private, must-revalidate'); 
			$tempDumpFileName = APP_TEMP_DIR.'tmpdumpfile.txt';
			// Read the source OAI repository url or file
			
/*			$sourceOAI = fopen($urldata, "r");
			$sourceOAIRead = '';
			while ($tmp = fread($sourceOAI, 4096))
			{
			$sourceOAIRead .= $tmp;
			}
			echo $sourceOAIRead;
			*/
			$data = Misc::processURL($urldata);

			echo $data;
			ob_end_flush(); // the incrementFileDownloads takes some (small) time so flush the file content out first
			if ((!is_numeric(strpos($dsID, "thumbnail_"))) && (!is_numeric(strpos($dsID, "web_"))) && (!is_numeric(strpos($dsID, "preview_"))) && (!is_numeric(strpos($dsID, "presmd_"))) && (!is_numeric(strpos($dsID, "FezACML_"))) ) {
				Record::incrementFileDownloads($pid); //increment FezMD.file_downloads counter
			}

/*			$tempDump = fopen($tempDumpFileName, 'w');

			// Write the source xml to a temporary file to we can get the filesize (required for the content length header)
			fwrite($tempDump, $sourceOAIRead);
			
			fclose($tempDump); 
			
			$tempDump = "";
			$tempDump = fopen($tempDumpFileName, 'r');
			$sourceOAIRead = fread($tempDump, filesize($tempDumpFileName));
			header("Content-length: " . filesize($tempDumpFileName) . "\n");
			echo $sourceOAIRead;
			die;*/
            exit;
		}
	}		
}
$tpl = new Template_API();
$tpl->setTemplate("view.tpl.html");
$tpl->assign("show_not_allowed_msg", true);
$tpl->displayTemplate();



?>
