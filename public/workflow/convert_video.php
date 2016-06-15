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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//

include_once(APP_INC_PATH . "class.video_resample.php");

$log = FezLog::get();
$pid = $this->pid;
$xdis_id = $this->xdis_id;
$dsInfo = $this->dsInfo;
$dsIDName = $dsInfo['ID'];
$filename = $dsIDName;

// Added a check to see if the file is coming from a batch import location, therefore don't try to check if it is in the temp directory - which is only really for form uploads
if ((is_numeric(strpos($filename, "/"))) || (is_numeric(strpos($filename, "\\")))) {
  $filepath = $filename;
} else {
  $filepath = APP_TEMP_DIR . $filename;
}

if (!file_exists($filepath)) {
  $log->err("No base file " . $filepath . "<br/>\n");
} else {

  if (empty($file_name_prefix)) {
    $file_name_prefix = "stream_";
  }
  if (empty($height)) {
    $height = 50;
  }
  if (empty($width)) {
    $width = 50;
  }


  if (is_numeric(strpos($filename, "/"))) {
    $new_file = $file_name_prefix . Foxml::makeNCName(substr($filename, strrpos($filename, "/") + 1));
  } else {
    $new_file = $file_name_prefix . Foxml::makeNCName($filename);
  }
  if (is_numeric(strpos($new_file, "."))) {
    $new_file = substr($new_file, 0, strrpos($new_file, ".")) . ".flv";
  } else {
    $new_file .= ".flv";
  }
  Video_Resample::convertToFlashVideo($filename);

  if (!empty($new_file)) {
    if (Fedora_API::datastreamExists($pid, $new_file)) {
      Fedora_API::callPurgeDatastream($pid, $new_file);
    }
    $delete_file = APP_TEMP_DIR . $new_file;
    $new_file = APP_TEMP_DIR . $new_file;
    if (file_exists($new_file)) {
      Fedora_API::getUploadLocationByLocalRef($pid, $new_file, $new_file, $new_file, 'video/x-flv', 'M');
      if (is_file($new_file)) {
        $deleteCommand = APP_DELETE_CMD . " " . $delete_file;
        exec($deleteCommand);
      }
    } else {
      $log->err("File not created $new_file using url $getString <br/>\n");
    }
  }
}

?>
