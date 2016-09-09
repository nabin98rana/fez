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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>        |
// +----------------------------------------------------------------------+
//
//

/**
 * Class designed to handle all business logic related to the resampling of video datastreams in the
 * system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.foxml.php");

/**
 * Video_Resample
 */
class Video_Resample
{


  public static function takeThumbnail($file)
  {
    $log = FezLog::get();
    $error = "";
    $file = escapeshellcmd($file);
    $file_dir = "";

    if (is_numeric(strpos($file, "/"))) {
      $file_dir = substr($file, 0, strrpos($file, "/"));
      $file = substr($file, strrpos($file, "/") + 1);

    }
    if (trim($file_dir) == "") {
      $file_dir = APP_TEMP_DIR;
    }
    if ((!(is_numeric(strpos($file, "&")))) && (!(is_numeric(strpos($file, "|"))))) { // check for command hax
      if (is_numeric(strrpos($file, '.'))) {
        $ffpmeg_file = APP_TEMP_DIR . 'thumbnail_' . Foxml::makeNCName(substr($file, 0, strrpos($file, '.'))) . '.jpg';
      } else {
        $ffpmeg_file = APP_TEMP_DIR . 'thumbnail_' . Foxml::makeNCName($file) . '.jpg';
      }
      if (is_file($ffpmeg_file)) { // if already exists, delete it
        unlink($ffpmeg_file);
      }

      $full_file = $file_dir . '/' . $file;
      if (is_numeric(strpos($full_file, " "))) {
        $newfile = Foxml::makeNCName($file);
        Misc::processURL($full_file, true, APP_TEMP_DIR . $newfile);
//        copy($full_file, APP_TEMP_DIR.$newfile);
        $full_file = APP_TEMP_DIR . $newfile;
      }
      if (!stristr(PHP_OS, 'win') || stristr(PHP_OS, 'darwin')) { // Not Windows Server
        $unix_extra = " 2>&1";
      } else {
        $unix_extra = '';
        $full_file = str_replace('/', '\\', $full_file);
      }
      $command = APP_FFMPEG_CMD . ' -i ' . "$full_file -ss " . APP_FFMPEG_DEFAULT_THUMB_POS . " -s " . APP_THUMBNAIL_WIDTH . "x" . APP_THUMBNAIL_HEIGHT . " -vframes 1 -y -f image2 $ffpmeg_file";
      if (!$file) $error .= "<b>ERROR:</b> no file specified<br>";
      if (!is_file($full_file)) {
        $error .= "<b>ERROR:</b> given file filename not found or bad filename given<br>";
      }
      if (!empty($error)) {
        $log->err($error);
      }
      $return_status = 0;
      $return_array = array();
      exec($command . $unix_extra, $return_array, $return_status);
      if ($return_status <> 0) {
        $log->err("FFMpeg Error: " . implode(",", $return_array) . ", return status = $return_status, for command $command \n");
      }
      if (!empty($newfile)) {
        unlink($full_file);
      }
      echo $ffpmeg_file;
    }

  }


  public static function convertToFlashAudio($file) {
    $file = escapeshellcmd($file);
    $file_dir = "";
    $error = "";
    $log = FezLog::get();

    if (is_numeric(strpos($file, "/"))) {
      $file_dir = substr($file, 0, strrpos($file, "/"));
      $file = substr($file, strrpos($file, "/")+1);

    }
    if (trim($file_dir) == "") { $file_dir = APP_TEMP_DIR; }
    if ((!(is_numeric(strpos($file, "&")))) && (!(is_numeric(strpos($file, "|"))))) { // check for command hax
      if (is_numeric(strrpos($file, '.'))) {
        $ffpmeg_file = APP_TEMP_DIR.'stream_'.Foxml::makeNCName(substr($file, 0, strrpos($file, '.'))).'.flv';
      } else {
        $ffpmeg_file = APP_TEMP_DIR.'stream_'.Foxml::makeNCName($file).'.flv';
      }
      if (is_file($ffpmeg_file)) { // if already exists, delete it
        unlink($ffpmeg_file);
      }
      $full_file = $file_dir.'/'.$file;
      if (is_numeric(strpos($full_file, " "))) {
        $newfile = Foxml::makeNCName($file);
//        copy($full_file, APP_TEMP_DIR.$newfile);
        Misc::processURL($full_file, true, APP_TEMP_DIR.$newfile);
        $full_file = APP_TEMP_DIR.$newfile;
      }
      if (!stristr(PHP_OS, 'win') || stristr(PHP_OS, 'darwin')) { // Not Windows Server
        $unix_extra = " 2>&1";
      } else {
        $unix_extra = '';
        $full_file = str_replace('/','\\',$full_file);
      }
      $command = APP_FFMPEG_CMD.' -i '.$full_file." -ar 44100 -ac 2 ".$ffpmeg_file;
      if(!$file) $error .= "<b>ERROR:</b> no file specified<br>";
      if(!is_file($full_file)) { $error .= "<b>ERROR:</b> given file filename not found or bad filename given<br>"; }
      if (!empty($error)) {
        $log->err($error);
      }
      $return_status = 0;
      $return_array = array();
      exec($command.$unix_extra , $return_array, $return_status);
      if ($return_status <> 0) {
        $log->err("FFMpeg Error: ".implode(",", $return_array).", return status = $return_status, for command $command \n");
      }
      if (!empty($newfile)) {
        unlink($full_file);
      }
    }
  }


  // @@@ CK - 10/12/2007
// FFMpeg video to flash video converter webservice
// - Takes url parameters to convert an video file to a flash video cached dissemination
  public static function convertToFlashVideo($file)
  {
    $log = FezLog::get();
    $error = "";

    $file = escapeshellcmd(urldecode($file));
    $file_dir = "";

    if (is_numeric(strpos($file, "/"))) {
      $file_dir = substr($file, 0, strrpos($file, "/"));
      $file = substr($file, strrpos($file, "/") + 1);

    }
    if (trim($file_dir) == "") {
      $file_dir = APP_TEMP_DIR;
    }
    if ((!(is_numeric(strpos($file, "&")))) && (!(is_numeric(strpos($file, "|"))))) { // check for command hax
      if (is_numeric(strrpos($file, '.'))) {
        $ffpmeg_file = APP_TEMP_DIR . 'stream_' . Foxml::makeNCName(substr($file, 0, strrpos($file, '.'))) . '.flv';
      } else {
        $ffpmeg_file = APP_TEMP_DIR . 'stream_' . Foxml::makeNCName($file) . '.flv';
      }
      if (is_file($ffpmeg_file)) { // if already exists, delete it
        unlink($ffpmeg_file);
      }
      $full_file = $file_dir . '/' . $file;
      if (is_numeric(strpos($full_file, " "))) {
        $newfile = Foxml::makeNCName($file);
        //        copy($full_file, APP_TEMP_DIR.$newfile);
        Misc::processURL($full_file, true, APP_TEMP_DIR . $newfile);
        $full_file = APP_TEMP_DIR . $newfile;
      }
      if (!stristr(PHP_OS, 'win') || stristr(PHP_OS, 'darwin')) { // Not Windows Server
        $unix_extra = " 2>&1";
      } else {
        $unix_extra = '';
        $full_file = str_replace('/', '\\', $full_file);
      }
      $command = APP_FFMPEG_CMD . ' -i ' . $full_file . " -s " . APP_FFMPEG_DEFAULT_WIDTH . "x" . APP_FFMPEG_DEFAULT_HEIGHT . " -ar 44100 -ac 2 " . $ffpmeg_file;
      if (!$file) $error .= "<b>ERROR:</b> no file specified<br>";
      if (!is_file($full_file)) {
        $error .= "<b>ERROR:</b> given file filename not found or bad filename given<br>";
      }
      if (!empty($error)) {
        $log->err($error);
      }

      $return_status = 0;
      $return_array = array();
      exec($command . $unix_extra, $return_array, $return_status);
      if ($return_status <> 0) {
        $log->err("FFMpeg Error: " . implode(",", $return_array) . ", return status = $return_status, for command $command \n");
      }

      // add metadata to ffmpeg file using yamdi (yamdi cannot overwrite the file so we need to specify another file)
      $metadataCommand = APP_FFMPEG_YAMDI_CMD . " -i {$ffpmeg_file} -o {$ffpmeg_file}.2";
      $return_status = 0;
      $return_array = array();
      exec($metadataCommand . $unix_extra, $return_array, $return_status);
      if ($return_status <> 0) {
        $log->err("FFMpeg Yamdi Error: " . implode(",", $return_array) . ", return status = $return_status, for command $metadataCommand \n");
      }

      // rename the file back to the original name (overwriting the original)
      if ($return_status == 0) {
        rename("{$ffpmeg_file}.2", $ffpmeg_file);
      }

      if (!empty($newfile)) {
        unlink($full_file);
      }
//    echo $ffpmeg_file;
    }
  }

}
