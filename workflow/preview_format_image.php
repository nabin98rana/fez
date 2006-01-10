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
$pid = $this->pid;
$xdis_id = $this->xdis_id;
$dsInfo = $this->dsInfo;
$dsIDName = $dsInfo['ID'];
$filename=$dsIDName;
$file_name_prefix = "preview_";
$filename_ext = strtolower(substr($filename, (strrpos($filename, ".") + 1)));
$getString = APP_RELATIVE_URL."webservices/wfb.image_resize.php?image="
.urlencode($filename)."&height=700&width=400&ext=jpg&prefix=".$file_name_prefix."&copyright=copyright_test&watermark=true";
$http_req = new HTTP_Request($getString, array("http" => "1.0"));
$http_req->setMethod("GET");
$http_req->sendRequest();
$xml = $http_req->getResponseBody();
if (is_numeric(strpos($filename, "/"))) {
    $new_file = $file_name_prefix.str_replace(" ", "_", 
            substr(substr($filename, 0, strrpos($filename, ".")), strrpos($filename, "/")+1)).".jpg";
} else {
    $new_file = $file_name_prefix.str_replace(" ", "_", substr($filename, 0, strrpos($filename, "."))).".jpg";
}

if ($new_file) {
	if (Fedora_API::datastreamExists($pid, $new_file)) {
	    Fedora_API::callPurgeDatastream($pid, $new_file);
	}
	$new_file = APP_TEMP_DIR.$new_file;
	Fedora_API::getUploadLocationByLocalRef($pid, $new_file, $new_file, $new_file, 'image/jpeg', 'M');
	if (is_file($new_file)) {
		$deleteCommand = APP_DELETE_CMD." ".$new_file;
		exec($deleteCommand);
	}
	//Record::setIndexMatchingFields($xdis_id, $pid); // add the thumbnail to the fez index

}




?>
