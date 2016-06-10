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

include_once(APP_INC_PATH . "class.foxml.php");

class Jhove_Helper
{
  var $xmlDoc;
  var $xpath;

  function __construct($xmlObj)
  {
    $this->xmlDoc = new DomDocument();
    $this->xmlDoc->preserveWhiteSpace = false;
    $this->xmlDoc->loadXML($xmlObj);

    $this->xpath = new DOMXPath($this->xmlDoc);
  }


  function extractFileSize()
  {
    $this->xpath->registerNamespace('a', 'http://hul.harvard.edu/ois/xml/ns/jhove');
    $recordNodes = $this->xpath->query('//a:jhove/a:repInfo/a:size');
    foreach ($recordNodes as $file_field) {
      if (!isset($fileSize)) {
        $fileSize = $file_field->nodeValue;
      }
    }

    return $fileSize;
  }


  function extractSpatialMetrics()
  {
    $width = 0;
    $height = 0;

    $this->xpath->registerNamespace('mix', 'http://www.loc.gov/mix/');
    $recordNodes = $this->xpath->query('//mix:ImageWidth');
    foreach ($recordNodes as $file_field) {
      if ($width == "") {
        $width = $file_field->nodeValue;
        break;
      }
    }

    $recordNodes = $this->xpath->query('//mix:ImageLength');
    foreach ($recordNodes as $file_field) {
      if ($height == "") {
        $height = $file_field->nodeValue;
        break;
      }
    }

    return array($width, $height);
  }


  public static function processFile($file)
  {
    $file = escapeshellcmd($file);
    $file_dir = "";
    $error = "";
    $log = FezLog::get();

    if (is_numeric(strpos($file, "/"))) {
      $file_dir = substr($file, 0, strrpos($file, "/"));
      $file = substr($file, strrpos($file, "/") + 1);

    }
    if (trim($file_dir) == "") {
      $file_dir = APP_JHOVE_TEMP_DIR;
    }
    if ((!(is_numeric(strpos($file, "&")))) && (!(is_numeric(strpos($file, "|"))))) { // check for command hax
      if (is_numeric(strrpos($file, '.'))) {
        $presmd_file = APP_JHOVE_TEMP_DIR . 'presmd_' . Foxml::makeNCName(substr($file, 0, strrpos($file, '.'))) . '.xml';
      } else {
        $presmd_file = APP_JHOVE_TEMP_DIR . 'presmd_' . Foxml::makeNCName($file) . '.xml';
      }
      if (is_file($presmd_file)) { // if already exists, delete it
        unlink($presmd_file);
      }
      $full_file = $file_dir . '/' . $file;
      if (is_numeric(strpos($full_file, " "))) {
        $newfile = Foxml::makeNCName($file);

        copy($full_file, APP_TEMP_DIR . $newfile);
        $full_file = APP_TEMP_DIR . $newfile;
      }
      if (!stristr(PHP_OS, 'win') || stristr(PHP_OS, 'darwin')) { // Not Windows Server
        $unix_extra = " 2>&1";
      } else {
        $unix_extra = '';
        $full_file = str_replace('/', '\\', $full_file);
      }
      $command = APP_JHOVE_DIR . 'jhove -h xml -o ' . "$presmd_file $full_file";
      if (!$file) $error .= "<b>ERROR:</b> no file specified<br>";
      if (!is_file($full_file)) {
        $error .= "<b>ERROR:</b>$command :given file filename not found or bad filename given<br>";
      }
      if (!empty($error)) {
        $log->err($error);
      }
      $return_status = 0;
      $return_array = array();
      exec($command . $unix_extra, $return_array, $return_status);
      if ($return_status <> 0) {
        $log->err("JHOVE Error: " . implode(",", $return_array) . ", return status = $return_status, for command $command \n");
      }
      if (!empty($newfile)) {
        unlink($full_file);
      }
    }
  }

}
