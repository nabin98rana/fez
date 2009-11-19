<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007, 2008 The University of Queensland,   |
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
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.exiftool.php");
include_once(APP_INC_PATH . "class.fedora_direct_access.php");

$auth = new Auth();
$auth->checkForBasicAuthRequest('eserv');

$stream     = @$_REQUEST["stream"];
$wrapper    = @$_REQUEST["wrapper"];
$pid        = @$_REQUEST["pid"];
$dsID       = @$_REQUEST["dsID"];
$origami    = @$_REQUEST["oi"];

$SHOW_STATUS_PARM = @$_REQUEST["status"];
$SHOW_STATUS = @($SHOW_STATUS_PARM == "true") ? true : false; 
$ALLOW_SECURITY_REDIRECT = @$SHOW_STATUS ? false : true; 

$not_exists = false;
if ( (is_numeric(strpos($pid, ".."))) || (Misc::isPid($pid) != true) || (is_numeric(strpos($pid, "/"))) || (is_numeric(strpos($pid, "/"))) || (is_numeric(strpos($dsID, "..")))) {
	header("HTTP/1.0 404 Not Found");
	header("Status: 404 Not Found");
	$tpl = new Template_API();
    $tpl->setTemplate("404.tpl.html");
    $tpl->displayTemplate();
	exit;
} // to stop haxors snooping our confs

$acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");

if (!empty($pid) && !empty($dsID)) {

	if( Record::isDeleted($pid) ) {
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
		$tpl = new Template_API();
        $tpl->setTemplate("404.tpl.html");
        $tpl->displayTemplate();
        exit;
	}
	
    // Retrieve the selected version date from the request. 
    // This will be null unless a version date has been
    // selected by the user.
    $requestedVersionDate = $_REQUEST['version_date'];
    if( isset($requestedVersionDate) && $requestedVersionDate != NULL ){
	    $record = new RecordObject($pid);
    	if( !$record->canViewVersions()){
	        include_once(APP_INC_PATH . "class.template.php");
			$tpl = new Template_API();
			$tpl->setTemplate("view.tpl.html");
			$tpl->assign("show_not_allowed_msg", true);
			$tpl->displayTemplate();
			exit;
    	}
    	$requestedVersionDate = "/" . $requestedVersionDate; 
    } else {
		$requestedVersionDate = "";
    } 
		
    $dissemination_dsID = "";
	if (is_numeric(strpos($dsID, "archival_"))) {
		if( !$ALLOW_SECURITY_REDIRECT ){
			header("HTTP/1.0 403 Forbidden");
			exit;
		}
		$dsID = str_replace("archival_", "", $dsID);
		Auth::redirect(APP_BASE_URL."eserv/".$pid."/".$dsID);
	}
	
	$is_video = 0;
	$is_image = 0;
	$info = array();
	$exif_array = Exiftool::getDetails($pid, $dsID);
	if (!is_numeric($exif_array['exif_file_size']) || $requestedVersionDate != "") {
		$getURL = APP_FEDORA_GET_URL."/".$pid."/".$dsID.$requestedVersionDate;
		list($data,$info) = Misc::processURL_info($getURL);
	} else {
		$info['content_type'] = $exif_array['exif_mime_type'];
		$info['download_content_length'] = $exif_array['exif_file_size'];
	}
	if( $info['download_content_length'] == 0 )
		$not_exists = true;
	
	if ($not_exists == false) {
		$ctype = $info['content_type'];
		
		if ($ctype == "application/octet-stream") {
			if (substr($dsID, -4) == ".flv") {
				$ctype = "video/x-flv";
			}
		}
		
		if (is_numeric(strpos($ctype, "video"))) {
			$is_video = 1;
		} elseif (is_numeric(strpos($ctype, "image"))) {
			$is_image = 1;
		}
		
		if (($is_image == 1) && (!is_numeric(strpos($dsID, "web_"))) && (!is_numeric(strpos($dsID, "preview_"))) && (!is_numeric(strpos($dsID, "thumbnail_"))) ) {
			$acceptable_roles = array("Community_Admin", "Editor", "Creator", "Archival_Viewer");
			
			if($origami == true) {
			    $acceptable_roles[] = "Viewer";
			}
			
			$dissemination_dsID = "web_".substr($dsID, 0, strrpos($dsID, ".") + 1)."jpg";
		} elseif (($is_video == 1) && (!is_numeric(strpos($dsID, "stream_")) && (!is_numeric(strpos($ctype, "flv"))))) {
			$acceptable_roles = array("Community_Admin", "Editor", "Creator", "Archival_Viewer");
			$dissemination_dsID = "stream_".substr($dsID, 0, strrpos($dsID, ".") + 1)."flv";
		} else {
			$acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");
		}
		
		//Restrict datastreams/files to the Creator role+ via eserv if the object they are in is not published
		$status = Record::getSearchKeyIndexValue($pid, "Status", false);
		if ($status != Status::getID("Published")) {
			$acceptable_roles = array("Community_Admin", "Editor", "Creator");
		}

		if (Auth::checkAuthorisation($pid, $dsID, $acceptable_roles, $_SERVER['REQUEST_URI'], null, $ALLOW_SECURITY_REDIRECT) != true) {
	  		if( $SHOW_STATUS ){
				header("HTTP/1.0 403 Forbidden");
				exit;
			} else {
	      		include_once(APP_INC_PATH . "class.template.php");
				$tpl = new Template_API();
				$tpl->setTemplate("view.tpl.html");
				$tpl->assign("show_not_allowed_msg", true);
				$tpl->displayTemplate();
				exit;
	  		}
		}
		
		if (($stream == 1 && $is_video == 1) && (is_numeric(strpos($ctype, "flv")))) {
			
			$urldata = APP_FEDORA_GET_URL."/".$pid."/".$dsID.$requestedVersionDate;
			$file = $urldata;
			$seekat = $_GET["pos"];
	        $size = Misc::remote_filesize($urldata);
			# content headers
			header("Content-Type: video/x-flv");
			header("Content-Disposition: attachment; filename=\"" . $dsID . "\"");
	
		    if ($seekat != 0) {
	        	print("FLV");
	   			print(pack('C', 1 ));
	    		print(pack('C', 1 ));
	    		print(pack('N', 9 ));
	    		print(pack('N', 9 ));
		    }
			if (APP_FEDORA_APIA_DIRECT == "ON") {
	            $fda = new Fedora_Direct_Access();
				$dsVersionID = $fda->getMaxDatastreamVersion($pid, $dsID);
				$fda->getDatastreamManagedContentStream($pid, $dsID, $dsVersionID, $seekat);
			} else {
				$fh = fopen($file, "rb");
				$buffer = 512;
			  	echo stream_get_contents($fh, $size, $seekat);
				fclose($fh);
			}
			// Add view to statistics buffer
			Statistics::addBuffer($pid, $dsID);							
		    exit;
		    
		} elseif( $origami == true ) {
		    
	        include_once(APP_INC_PATH . "class.template.php");
	        include_once(APP_INC_PATH . "class.origami.php");
	        
			$tpl = new Template_API();
			$tpl->setTemplate("flviewer.tpl.html");           
	        
			$tpl->assign("url", Origami::getTitleLocation($pid, $dsID));
			$tpl->displayTemplate();
			// Add view to statistics buffer
			Statistics::addBuffer($pid, $dsID);
			exit;
		    
		} elseif (($is_video == 1) && (is_numeric(strpos($ctype, "flv")))) {
			
	        include_once(APP_INC_PATH . "class.template.php");
			$tpl = new Template_API();
			$tpl->setTemplate("flv.tpl.html");
			$tpl->assign("APP_BASE_URL", APP_BASE_URL);
			$tpl->assign("eserv_url", APP_BASE_URL."eserv.php");
			$tpl->assign("dsID", $dsID);
			if (is_numeric($exif_array['exif_image_height']) && is_numeric($exif_array['exif_image_width'])) { 
				$player_height = $exif_array['exif_image_height'];
				$player_width = $exif_array['exif_image_width'];
			} else {
				$player_height = 350;
				$player_width = 425;
			}
			$tpl->assign("player_height", $player_height);
			$tpl->assign("player_width",  $player_width);
			$tpl->assign("dsID", $dsID);
			$tpl->assign("preview_ds", str_replace(".flv", ".jpg", str_replace("stream_", "preview_", $ds_id)));
			$tpl->assign("wrapper", $wrapper);
			$tpl->assign("pid", $pid);
			$tpl->displayTemplate();
			exit;
		}
		
		// this should stop them dang haxors (forces the http on the front for starters)
		$urldata = APP_FEDORA_GET_URL."/".$pid."/".$dsID.$requestedVersionDate; 
		$urlpath = $urldata;
	    if (!empty($header)) {
	    	//echo $header; exit;
	        header($header);
	    } elseif (!empty($info['content_type'])) {
	        header("Content-type: {$info['content_type']}");
	    } else {
	        header("Content-type: text/html");
	    }
	    
	    // PDF? > 7MB? Firefox? Force download.
	    if (is_numeric(strpos($ctype, "pdf")) && $info['download_content_length'] > 7000000 && Misc::is_firefox()) {
	    	//header('Content-Type: application/download');
	    	header("Content-Type: application/force-download");
	    }
	    
	    header('Content-Disposition: filename="'.substr($urldata, (strrpos($urldata, '/')+1) ).'"');
		if (!empty($info['download_content_length'])) {
			header("Content-length: ".$info['download_content_length']);
		}
		header('Pragma: private');
		header('Cache-control: private, must-revalidate');
		
		/*
		 * Send file to user
		 */
		Misc::processURL($urldata, true);
		// Add view to statistics buffer
		Statistics::addBuffer($pid, $dsID);				
		exit;
	}
}

if( $SHOW_STATUS && ($pid == "" || $dsID == "" || $not_exists == true )){
	header("HTTP/1.0 404 Not Found");
	header("Status: 404 Not Found");
	$tpl = new Template_API();
    $tpl->setTemplate("404.tpl.html");
    $tpl->displayTemplate();
	exit;
} 

include_once(APP_INC_PATH . "class.template.php");
$tpl = new Template_API();
$tpl->setTemplate("view.tpl.html");
$tpl->assign("pid", $pid);
$tpl->assign("not_exists", $not_exists);
//$tpl->assign("show_not_allowed_msg", true);  // prefer non_exists message
$tpl->displayTemplate();
