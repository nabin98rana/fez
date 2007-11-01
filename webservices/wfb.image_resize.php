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
// @@@ CK - 28/7/05
// Image resize webservice
// - Takes url parameters to convert an image file in the Fez temp directory into a image file of the given format and size

include_once("../config.inc.php");
include_once(APP_INC_PATH. 'class.error_handler.php');

// Get image and size
$image = urldecode($_GET["image"]);
$width = $_GET["width"]; //maximum width
$height = $_GET["height"]; //maximum height
$copyright = $_GET["copyright"]; //the copyright message to add (if any)
$watermark = $_GET["watermark"]; //"true" if the image is to be watermarked
$ext = strtolower($_GET["ext"]); //the file type extension to convert the image to
$outfile= $_GET["outfile"]; 
$image_dir = "";
if (is_numeric(strpos($image, "/"))) {
	$image_dir = substr($image, 0, strrpos($image, "/")+1);
	$image = substr($image, strrpos($image, "/")+1);
}	

if (trim($image_dir) == "") { $image_dir = APP_TEMP_DIR; }

$temp_file = preg_replace('/\.\S*?$/', ".$ext", $outfile);
if (!strstr($temp_file,$ext)) {
    $temp_file .= ".$ext";
}
$temp_file = str_replace(" ", "_", $temp_file);
$error = '';
if(!$image) $error .= "<b>ERROR:</b> no image specified<br>";

// get image from an URL 
$is_url = false;
if (preg_match('/^https?:\/\//',$image_dir.$image)) {
  // make a temporary local copy
  $is_url = true;
  file_put_contents(APP_TEMP_DIR.$image, file_get_contents($image_dir.$image));
  $image_dir = APP_TEMP_DIR;
}


if(!is_file($image_dir.$image)) { $error .= "<b>ERROR:</b> given image filename not found or bad filename given<br>"; }
if(!is_numeric($width) && !is_numeric($height)) $error .= "<b>ERROR:</b> no sizes specified<br>";
if($error){ Error_Handler::logError($error, __FILE__,__LINE__); die; }

// Set the header type
if ($ext=="jpg" || $ext=="jpeg")
  $content_type="image/jpeg";
elseif ($ext=="gif")
  $content_type="image/gif";
elseif ($ext=="png")
  $content_type="image/png";
else{ echo "<b>ERROR:</b> unknown file type<br>"; die; }
$return_array = array();
$return_status = 0;

if (!stristr(PHP_OS, 'win') || stristr(PHP_OS, 'darwin')) { // Not Windows Server
    $unix_extra = " 2>&1";
} else {
    $unix_extra = '';
}
// Create the output file if it does not exist
if ($watermark == "" && $copyright == "") {
//	if(!is_file(APP_TEMP_DIR.$temp_file)) {
	if(!is_file(APP_TEMP_DIR.$temp_file)) {
		$command = APP_CONVERT_CMD." -resize \"".escapeshellcmd($width)."x".escapeshellcmd($height).">\" -colorspace rgb ".$image_dir.escapeshellcmd($image)." ".APP_TEMP_DIR.escapeshellcmd($temp_file);
		exec($command.$unix_extra, $return_array, $return_status);
//		$error_message = shell_exec($command.$unix_extra);		
	//	exec(escapeshellcmd($command));
	} 
} elseif ($watermark == "" && $copyright != "") {
	$command = APP_CONVERT_CMD." -resize \"".escapeshellcmd($width)."x".escapeshellcmd($height).">\" -colorspace rgb ".$image_dir.escapeshellcmd($image)." ".APP_TEMP_DIR.escapeshellcmd($temp_file);
	exec($command.$unix_extra, $return_array, $return_status);
	$command = APP_CONVERT_CMD.' '.APP_TEMP_DIR.escapeshellcmd($temp_file).' -font Arial -pointsize 20 -draw "gravity center fill black text 0,12 \'Copyright'.$copyright.'\' fill white  text 1,11 \'Copyright'.$copyright.'\'" '.APP_TEMP_DIR.escapeshellcmd($temp_file).'';
	exec($command.$unix_extra, $return_array, $return_status);
} elseif ($watermark != "" && $copyright == "") {
	$command = APP_CONVERT_CMD." -resize \"".escapeshellcmd($width)."x".escapeshellcmd($height).">\" -colorspace rgb ".$image_dir.escapeshellcmd($image)." ".APP_TEMP_DIR.escapeshellcmd($temp_file);
	exec($command.$unix_extra, $return_array, $return_status);
	$command = APP_COMPOSITE_CMD." -dissolve 15 -tile ".escapeshellcmd(APP_PATH)."/images/".APP_WATERMARK." ".APP_TEMP_DIR.escapeshellcmd($temp_file)." ".APP_TEMP_DIR.escapeshellcmd($temp_file)."";
	exec($command.$unix_extra, $return_array, $return_status);
} elseif ($watermark != "" && $copyright != "") {
	$command = APP_CONVERT_CMD." -resize \"".escapeshellcmd($width)."x".escapeshellcmd($height).">\" -colorspace rgb ".$image_dir.escapeshellcmd($image)." ".APP_TEMP_DIR.escapeshellcmd($temp_file);
	exec($command.$unix_extra, $return_array, $return_status);
	$command = APP_CONVERT_CMD.' '.APP_TEMP_DIR.escapeshellcmd($temp_file).' -font Arial -pointsize 20 -draw "gravity center fill black text 0,12 \'Copyright'.$copyright.'\' fill white  text 1,11 \'Copyright'.$copyright.'\'" '.APP_TEMP_DIR.escapeshellcmd($temp_file).'';
	exec($command.$unix_extra, $return_array, $return_status);
	$command = APP_COMPOSITE_CMD." -dissolve 15 -tile ".escapeshellcmd(APP_PATH)."/images/".APP_WATERMARK." ".APP_TEMP_DIR.escapeshellcmd($temp_file)." ".APP_TEMP_DIR.escapeshellcmd($temp_file)."";
	exec($command.$unix_extra, $return_array, $return_status);
}
//Error_Handler::logError("Image Magick Error: ".$error_message.", for command $command \n", __FILE__,__LINE__);
if ($return_status <> 0) {	
	Error_Handler::logError("Image Magick Error: ".implode(",", $return_array).", return status = $return_status, for command $command$unix_extra \n", __FILE__,__LINE__);
}

echo ' ';

?>
