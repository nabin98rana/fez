<?php
// @@@ CK - 28/7/05
// Image resize webservice
// - Takes url parameters to convert an image file in the eSpace /tmp directory into a image file of the given format and size

include_once("../config.inc.php");

// Get image and size
$image = $_GET["image"];
//$size = $_GET["size"]; //maximum width or height
$width = $_GET["width"]; //maximum width
$height = $_GET["height"]; //maximum height
$ext = $_GET["ext"]; //the file type extension to convert the image to

$image_dir = substr($image, 0, strrpos($image, "/"));
$image = substr($image, strrpos($image, "/")+1);

if (trim($image_dir) == "") { $image_dir = APP_TEMP_DIR; }

$temp_file = "thumbnail_".substr($image, 0, strrpos($image, ".")).".".$ext;
//echo $temp_file;
// Some error reporting
if(!$image) $error .= "<b>ERROR:</b> no image specified<br>";
if(!is_file(APP_TEMP_DIR.$image)) { $error .= "<b>ERROR:</b> given image filename not found or bad filename given<br>"; }
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
  $command = APP_CONVERT_CMD." -resize ".$width."x".$height." ".$image_dir."/".$image." ".APP_TEMP_DIR.$temp_file;
//	echo escapeshellcmd($command); echo "<br /><br />";
//  exec($command);
	exec(escapeshellcmd($command));
} 
// Output the image
//header("Content-type: ".$content_type);
//readfile(APP_TEMP_DIR.$temp_file);










?>