<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007, 2009 The University of Queensland,   |
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

/**
 * Deals with the files uploaded from the flash uploader
 *
 */

include_once('config.inc.php');
include_once(APP_INC_PATH . "class.uploader.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.api.php");

switch ($_SERVER['REQUEST_METHOD']) {
    case 'PUT':
    case 'POST':
        break;
    default:
        if (APP_API) {
            $arr = API::makeResponse('FAIL', "Method not allowed.");
            API::reply(405, $arr, APP_API);
        }
        exit;
}

$workflowId = $_REQUEST['workflowId'];
$fileNumber = $_REQUEST['fileNumber'];
$fileNumber = sprintf('%03d', $fileNumber); // left pad with zeros so we can sort without worrying about numeric vs alpha sorting
$uploadDir = Uploader::getUploadedFilePath($workflowId);

// Create the directory (and any missing directories) for this workflow
if (!is_dir($uploadDir))
{
	$directory_path = "";
	$directories = explode("/",$uploadDir);

	foreach($directories as $directory)
	{
		$directory_path .= $directory."/";
		if (!is_dir($directory_path))
		{
			mkdir($directory_path);
			chmod($directory_path, 0777);
		}
	}
}

// and move the files
foreach ($_FILES as $fieldName => $file) {
	$returnValue = move_uploaded_file($file['tmp_name'], $uploadDir."/{$fileNumber}." . strip_tags(basename($file['name'])));
}
if ($returnValue) {
    if (APP_API) {
        $arr = API::makeResponse('202', "Uploaded.");
        API::reply(202, $arr, APP_API); //202 is accepted
    } else {
        echo " "; // need to echo at least something so the flash uploader recognises a file upload has taken place
    }
} else {
    if (APP_API) {
        $arr = API::makeResponse('400', "Method not allowed.");
        API::reply(400, $arr, APP_API);
    }
}
