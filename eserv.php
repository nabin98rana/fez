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
//include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.fedora_direct_access.php");
//include_once(APP_INC_PATH . "class.record.php");
//include_once(APP_INC_PATH . "class.fedora_api.php");

$username = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($username);

$stream = @$_POST["stream"] ? $_POST["stream"] : $_GET["stream"];
$wrapper = @$_POST["wrapper"] ? $_POST["wrapper"] : $_GET["wrapper"];
$pid = @$_POST["pid"] ? $_POST["pid"] : $_GET["pid"];
$dsID = @$_POST["dsID"] ? $_POST["dsID"] : $_GET["dsID"];
$not_exists = false;
if ( (is_numeric(strpos($pid, ".."))) && (is_numeric(strpos($dsID, "..")))) {
	die;
} // to stop haxors snooping our confs

$acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");

if (!empty($pid) && !empty($dsID)) {
	$dissemination_dsID = "";
	if (is_numeric(strpos($dsID, "archival_"))) {
		$dsID = str_replace("archival_", "", $dsID);
		Auth::redirect(APP_BASE_URL."eserv/".$pid."/".$dsID);
	}
	$real_dsID = $dsID;
	$is_video = 0;
	$is_image = 0;
	list($data,$info) = Misc::processURL_info(APP_FEDORA_GET_URL."/".$pid."/".$dsID);
	if ($not_exists == false) {
		$ctype = $info['content_type'];
		if (is_numeric(strpos($ctype, "video"))) {
			$is_video = 1;
		} elseif (is_numeric(strpos($ctype, "image"))) {
			$is_image = 1;
		}
		if (($is_image == 1) && (!is_numeric(strpos($dsID, "web_"))) && (!is_numeric(strpos($dsID, "preview_"))) && (!is_numeric(strpos($dsID, "thumbnail_"))) ) {
			$acceptable_roles = array("Community_Admin", "Editor", "Creator", "Archival_Viewer");
			$dissemination_dsID = "web_".substr($dsID, 0, strrpos($dsID, ".") + 1)."jpg";
		} elseif (($is_video == 1) && (!is_numeric(strpos($dsID, "stream_")) && (!is_numeric(strpos($ctype, "flv"))))) {
			$acceptable_roles = array("Community_Admin", "Editor", "Creator", "Archival_Viewer");
			$dissemination_dsID = "stream_".substr($dsID, 0, strrpos($dsID, ".") + 1)."flv";
		} else {
			$acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");
		}
		if (($stream == 1 && $is_video == 1) && (is_numeric(strpos($ctype, "flv")))) {
			if (Auth::checkAuthorisation($pid, $dsID, $acceptable_roles, $_SERVER['REQUEST_URI']) == true) {
				$urldata = APP_FEDORA_GET_URL."/".$pid."/".$dsID;
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

	//				while ($seekat <= $size) {
					// temp thing to test buffering

				  	echo stream_get_contents($fh, $size, $seekat);

	//				  $seekat += $buffer;
	//				}
					fclose($fh);
				}
			}

		    exit;
		} elseif (($is_video == 1) && (is_numeric(strpos($ctype, "flv")))) {
			if (Auth::checkAuthorisation($pid, $dsID, $acceptable_roles, $_SERVER['REQUEST_URI']) == true) {
		        include_once(APP_INC_PATH . "class.template.php");
				$tpl = new Template_API();
				$tpl->setTemplate("flv.tpl.html");
				$username = Auth::getUsername();
				$tpl->assign("isUser", $username);
				if (Auth::userExists($username)) { // if the user is registered as a Fez user
					$tpl->assign("isFezUser", $username);
				}
				$isAdministrator = User::isUserAdministrator($username);
				$tpl->assign("isAdministrator", $isAdministrator);
				$tpl->assign("APP_BASE_URL", APP_BASE_URL);
				$tpl->assign("eserv_url", APP_BASE_URL."eserv.php");
				$tpl->assign("dsID", $dsID);
				$tpl->assign("wrapper", $wrapper);
				$tpl->assign("pid", $pid);
				$tpl->displayTemplate();
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
		if (Auth::checkAuthorisation($pid, $dsID, $acceptable_roles, $_SERVER['REQUEST_URI']) == true) {
			$urldata = APP_FEDORA_GET_URL."/".$pid."/".$real_dsID; // this should stop them dang haxors (forces the http on the front for starters)
			$urlpath = $urldata;
	//		list($data,$info) = Misc::processURL_info($urldata);
	        if (!empty($header)) {
	        	//echo $header; exit;
	            header($header);
	        } elseif (!empty($info['content_type'])) {
	            header("Content-type: {$info['content_type']}");
	        } else {
	            header("Content-type: text/xml");
	        }
	        header('Content-Disposition: filename="'.substr($urldata, (strrpos($urldata, '/')+1) ).'"');
			if (!empty($info['download_content_length'])) {
				header("Content-length: ".$info['download_content_length']);
			}
			header('Pragma: private');
			header('Cache-control: private, must-revalidate');
			list($data,$info) = Misc::processURL($urldata, true);
			exit;
		}
	}
}
include_once(APP_INC_PATH . "class.template.php");
$tpl = new Template_API();
$tpl->setTemplate("view.tpl.html");
$tpl->assign("pid", $pid);
$tpl->assign("not_exists", $not_exists);
$tpl->assign("show_not_allowed_msg", true);
$tpl->displayTemplate();



?>
