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

/**
 * Class designed to handle all business logic related to the resampling of image datastreams in the
 * system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");


/**
 * Image_Resample
 */
class Image_Resample
{
  public static function resample($pid, $dsID, $width, $height, $regen, $copyright_message = "", $watermark = false)
  {
    $real_dsID = $dsID;
    $urldata = APP_FEDORA_GET_URL . "/" . $pid . "/" . $real_dsID;
    $tempDumpFileName = Misc::getFileTmpPath($real_dsID);
    $sourceOAI = fopen($urldata, "r");
    $sourceOAIRead = '';
    while ($tmp = fread($sourceOAI, 4096)) {
      $sourceOAIRead .= $tmp;
    }
    $tempDump = fopen($tempDumpFileName, 'w');
    fwrite($tempDump, $sourceOAIRead);
    fclose($tempDump);
    $mimetype = Misc::mime_content_type($tempDumpFileName);
    Workflow::processIngestTrigger($pid, $real_dsID, $mimetype);
    if (is_file($tempDumpFileName)) { // now remove the file from temp
      $deleteCommand = APP_DELETE_CMD . " " . $tempDumpFileName;
      exec($deleteCommand);
    }
  }

  /**
   * @param $image
   * @param $quality
   * @param $width
   * @param $height
   * @param $copyright
   * @param $watermark
   * @param $ext
   * @param $outfile
   */
  public static function imageResize($image, $quality, $width, $height, $copyright, $watermark, $ext, $outfile)
  {

    $log = FezLog::get();

    $image_dir = "";
    if (is_numeric(strpos($image, "/"))) {
      $image_dir = substr($image, 0, strrpos($image, "/") + 1);
      $image = substr($image, strrpos($image, "/") + 1);
    }

    if (trim($image_dir) == "") {
      $image_dir = Misc::getFileTmpPath();
    }

// Strip existing extension, store in $temp_file.
    $ext_loc = strrpos($outfile, ".");
    if (is_numeric($ext_loc)) {
      $temp_file = substr($outfile, 0, $ext_loc);
    } else {
      $temp_file = $outfile;
    }
// Add desired extension.
    $temp_file .= ".$ext";
    $temp_file = str_replace(" ", "_", $temp_file);
    $temp_file = trim($temp_file);
    $error = '';
    if (!$image) $error .= "<b>ERROR:</b> no image specified<br>";
    if (empty($temp_file)) {
      $error .= "<b>ERROR:</b> outfile: '" . htmlspecialchars($outfile) . "' not a valid name<br>";
    }

// get image from an URL
    if (preg_match('/^https?:\/\//', $image_dir . $image)) {
      if (!is_file(Misc::getFileTmpPath($image))) {
        file_put_contents(Misc::getFileTmpPath($image), file_get_contents($image_dir . $image));
      }
      $image_dir = Misc::getFileTmpPath();
    }


    if (!is_file($image_dir . $image)) {
      $error .= "<b>ERROR:</b> given image filename not found or bad filename given<br>";
    }
    if (!is_numeric($width) && !is_numeric($height)) $error .= "<b>ERROR:</b> no numeric sizes specified<br>";
    if (!is_numeric($quality)) $quality = 100;
    if ($error) {
      $log->err($error);
      die;
    }

    $return_array = array();
    $return_status = 0;

    if (!stristr(PHP_OS, 'win') || stristr(PHP_OS, 'darwin')) { // Not Windows Server
      $unix_extra = " 2>&1";
    } else {
      $unix_extra = '';
    }
// Create the output file if it does not exist
    if ($watermark == "" && $copyright == "") {
      if (!is_file(Misc::getFileTmpPath($temp_file))) {
        if (extension_loaded('imagick')) {
          $im = new Imagick($image_dir . escapeshellcmd($image));
          $im->setImageColorspace(1); // 1 = rgb
          $existingQuality = $im->getCompressionQuality();
          if ($quality < $existingQuality) {
            $im->setCompressionQuality($quality);
          }
          $im->thumbnailImage($width, $height);
          $im->stripImage();
          $im->writeImage(Misc::getFileTmpPath(escapeshellcmd($temp_file)));
        } else {
          $command = APP_CONVERT_CMD . " -strip -quality " . escapeshellcmd($quality) . " -resize \"" . escapeshellcmd($width) . "x" . escapeshellcmd($height) . ">\" -colorspace rgb \"" . $image_dir . escapeshellcmd($image) . "\"[0] " . Misc::getFileTmpPath(escapeshellcmd($temp_file));
          exec($command . $unix_extra, $return_array, $return_status);
        }
      }
    } elseif ($watermark == "" && $copyright != "") {
      $command = APP_CONVERT_CMD . " -strip -quality " . escapeshellcmd($quality) . " -resize \"" . escapeshellcmd($width) . "x" . escapeshellcmd($height) . ">\" -colorspace rgb \"" . $image_dir . escapeshellcmd($image) . "\"[0] " . Misc::getFileTmpPath(escapeshellcmd($temp_file));
      exec($command . $unix_extra, $return_array, $return_status);
      $command = APP_CONVERT_CMD . ' ' . Misc::getFileTmpPath(escapeshellcmd($temp_file)) . ' -font Arial -pointsize 20 -draw "gravity center fill black text 0,12 \'Copyright' . $copyright . '\' fill white  text 1,11 \'Copyright' . $copyright . '\'" ' . Misc::getFileTmpPath(escapeshellcmd($temp_file)) . '';
      exec($command . $unix_extra, $return_array, $return_status);
    } elseif ($watermark != "" && $copyright == "") {
      $command = APP_CONVERT_CMD . " -strip -quality " . escapeshellcmd($quality) . " -resize \"" . escapeshellcmd($width) . "x" . escapeshellcmd($height) . ">\" -colorspace rgb \"" . $image_dir . escapeshellcmd($image) . "\"[0] " . Misc::getFileTmpPath(escapeshellcmd($temp_file));
      exec($command . $unix_extra, $return_array, $return_status);
      $command = APP_COMPOSITE_CMD . " -dissolve 15 -tile " . escapeshellcmd(APP_PATH) . "/images/" . APP_WATERMARK . " " . Misc::getFileTmpPath(escapeshellcmd($temp_file)) . " " . Misc::getFileTmpPath(escapeshellcmd($temp_file)) . "";
      exec($command . $unix_extra, $return_array, $return_status);
    } elseif ($watermark != "" && $copyright != "") {
      $command = APP_CONVERT_CMD . " -strip -quality " . escapeshellcmd($quality) . " -resize \"" . escapeshellcmd($width) . "x" . escapeshellcmd($height) . ">\" -colorspace rgb \"" . $image_dir . escapeshellcmd($image) . "\"[0] " . Misc::getFileTmpPath(escapeshellcmd($temp_file));
      exec($command . $unix_extra, $return_array, $return_status);
      $command = APP_CONVERT_CMD . ' ' . Misc::getFileTmpPath(escapeshellcmd($temp_file)) . ' -font Arial -pointsize 20 -draw "gravity center fill black text 0,12 \'Copyright' . $copyright . '\' fill white  text 1,11 \'Copyright' . $copyright . '\'" ' . Misc::getFileTmpPath(escapeshellcmd($temp_file)) . '';
      exec($command . $unix_extra, $return_array, $return_status);
      $command = APP_COMPOSITE_CMD . " -dissolve 15 -tile " . escapeshellcmd(APP_PATH) . "/images/" . APP_WATERMARK . " " . Misc::getFileTmpPath(escapeshellcmd($temp_file)) . " " . Misc::getFileTmpPath(escapeshellcmd($temp_file)) . "";
      exec($command . $unix_extra, $return_array, $return_status);
    }

//$log->err($command);
    if ($return_status <> 0) {
      $log->err(array('Message' => "Image Magick Error: " . implode(",", $return_array) . ", return status = $return_status, for command $command$unix_extra \n", 'File' => __FILE__, 'Line' => __LINE__));
    }
  }

}
