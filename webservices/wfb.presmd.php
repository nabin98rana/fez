<?php
// @@@ CK - 28/7/05
// Image resize webservice
// - Takes url parameters to convert an image file in the eSpace /tmp directory into a image file of the given format and size

include_once("../config.inc.php");
$file = urldecode($_GET['file']);
$file_dir = "";	

if (is_numeric(strpos($file, "/"))) {
	$file_dir = substr($file, 0, strrpos($file, "/"));
	$file = substr($file, strrpos($file, "/")+1);

}
//$file = str_replace(" ", "_", $file);

if (trim($file_dir) == "") { $file_dir = APP_TEMP_DIR; }
if ((!(is_numeric(strpos($file, "&")))) && (!(is_numeric(strpos($file, "|"))))) { // check for command hax
	$APP_JHOVE_DIR = "/usr/local/jhove";

	$APP_JHOVE_CMD = $APP_JHOVE_DIR.'/jhove -h xml -o '.APP_TEMP_DIR.'presmd_'.str_replace(' ', '_', substr($file, 0, strrpos($file, '.'))).'.xml';
	$APP_JHOVE_CMD = escapeshellcmd($APP_JHOVE_CMD);
	if (is_numeric(strpos($file, " "))) {
		$APP_JHOVE_CMD .= ' \"'.$file_dir.'/'.$file.'\"';
	} else {
		$APP_JHOVE_CMD .= ' '.$file_dir.'/'.$file;	
	}

//	$APP_JHOVE_CMD = $APP_JHOVE_DIR."/jhove -h xml -o ".APP_TEMP_DIR."presmd_".substr($file, 0, strrpos($file, ".")).".xml ".APP_TEMP_DIR.$file;
	//$temp_file = "thumbnail_".substr($image, 0, strrpos($image, ".")).".".$ext;
	//$temp_file = "thumbnail_".substr($image, 0, strrpos($image, ".")).".".$ext;
	//echo $temp_file;
	// Some error reporting
	if(!$file) $error .= "<b>ERROR:</b> no file specified<br>";
	if(!is_file($file_dir.$file)) { $error .= "<b>ERROR:</b> given file filename not found or bad filename given<br>"; }
	//if(!is_numeric($width) && !is_numeric($height)) $error .= "<b>ERROR:</b> no sizes specified<br>";
	//if($error){ echo $error; die; }
	
	// Create the output file if it does not exist
	//if(!is_file(APP_TEMP_DIR.$temp_file)) {
	$command = $APP_JHOVE_CMD;
//	echo("command = ".$command);
	exec($command);
//	exec(escapeshellcmd($command));
} 
//} 
// Output the file
//header("Content-type: ".$content_type);
//readfile(APP_TEMP_DIR.$temp_file);










?>