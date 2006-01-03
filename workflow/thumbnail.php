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
$filename_ext = strtolower(substr($filename, (strrpos($filename, ".") + 1)));
$getString = APP_RELATIVE_URL."webservices/wfb.thumbnail.php?image="
.urlencode($filename)."&height=50&width=50&ext=jpg";
$http_req = new HTTP_Request($getString, array("http" => "1.0"));
$http_req->setMethod("GET");
$http_req->sendRequest();
$xml = $http_req->getResponseBody();
if (is_numeric(strpos($filename, "/"))) {
    $thumbnail = APP_TEMP_DIR."thumbnail_".str_replace(" ", "_", 
            substr(substr($filename, 0, strrpos($filename, ".")), strrpos($filename, "/")+1)).".jpg";
} else {
    $thumbnail = APP_TEMP_DIR."thumbnail_".str_replace(" ", "_", substr($filename, 0, strrpos($filename, "."))).".jpg";
}

if ($thumbnail) {
    Fedora_API::getUploadLocationByLocalRef($pid, $thumbnail, $thumbnail, $thumbnail, 'image/jpeg', 'M');
    if (is_numeric(strpos($thumbnail, "/"))) {
        $thumbnail = substr($thumbnail, strrpos($thumbnail, "/")+1); // take out any nasty slashes from the ds name itself
    }
    $thumbnail = str_replace(" ", "_", $thumbnail);
//    Record::insertIndexMatchingField($pid, 122, "varchar", $thumbnail); // add the thumbnail to the fez index
	Record::setIndexMatchingFields($xdis_id, $pid);

}




?>
