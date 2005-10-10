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
// @@@ CK - 28/7/05
// Image resize webservice
// - Takes url parameters to convert an image file in the eSpace /tmp directory into a image file of the given format and size

include_once("../config.inc.php");

// Get image and size
$image = urldecode($_GET["image"]);
//$size = $_GET["size"]; //maximum width or height
$width = $_GET["width"]; //maximum width
$height = $_GET["height"]; //maximum height
$ext = strtolower($_GET["ext"]); //the file type extension to convert the image to
//echo $image;
$image_dir = "";
if (is_numeric(strpos($image, "/"))) {
	$image_dir = substr($image, 0, strrpos($image, "/"));
	$image = substr($image, strrpos($image, "/")+1);
}	

if (trim($image_dir) == "") { $image_dir = APP_TEMP_DIR; }

$temp_file = "thumbnail_".substr($image, 0, strrpos($image, ".")).".".$ext;
$temp_file = str_replace(" ", "_", $temp_file);
//echo $temp_file;
// Some error reporting
$error = '';
if(!$image) $error .= "<b>ERROR:</b> no image specified<br>";
if(!is_file($image_dir."/".$image)) { $error .= "<b>ERROR:</b> given image filename not found or bad filename given<br>"; }
if(!is_numeric($width) && !is_numeric($height)) $error .= "<b>ERROR:</b> no sizes specified<br>";
if($error){ echo $error; die; }

// Set the header type
if ($ext=="jpg" || $ext=="jpeg")
  $content_type="image/jpeg";
elseif ($ext=="gif")
  $content_type="image/gif";
elseif ($ext=="png")
  $content_type="image/png";
else{ echo "<b>ERROR:</b> unknown file type<br>"; die; }

// Create the output file if it does not exist
if(!is_file(APP_TEMP_DIR.$temp_file)) {
  $command = APP_CONVERT_CMD." -resize ".$width."x".$height." '".$image_dir."/".$image."' ".APP_TEMP_DIR.$temp_file;
//	echo escapeshellcmd($command); echo "<br /><br />";
//  exec($command);
	exec(escapeshellcmd($command));
} 
// Output the image
//header("Content-type: ".$content_type);
//readfile(APP_TEMP_DIR.$temp_file);










?>
