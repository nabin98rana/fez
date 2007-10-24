<?php

include_once('../config.inc.php');



$rel_dir = substr($_REQUEST['path'], 0, strrpos($_REQUEST['path'], '?'));
$dir = APP_PATH.$rel_dir;

header('Content-type: application/xml');

$xml = "<?xml version='1.0'?>\n";
$xml .= "<files path=\"".$rel_dir."\">\n";


if (strstr($dir, '..')) {
	Error_Handler::logError("Doesn't support relative paths", __FILE__,__LINE__);
	print ("Doesn't support relative paths\n");
	exit;
}

if (is_dir($dir)) {
	if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (stristr($file, ".jpg") 
            	|| stristr($file, ".jpeg") 
            	|| stristr($file, ".png") 
            	|| stristr($file, ".gif") 
	        ) {
        		$xml .= "<file type=\"img\">images/$file</file>\n";
    		}
        }
        closedir($dh);
    }
}

$xml .= "</files>\n";

print($xml);
//Error_Handler::logError($xml,__FILE__,__LINE__);
?>