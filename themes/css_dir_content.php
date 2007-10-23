<?php

include_once('../config.inc.php');

header('Content-type: application/xml');
print("<?xml version='1.0'?>\n");
print("<dir:directory xmlns:dir=\"http://apache.org/cocoon/directory/2.0\">\n");

$dir = APP_PATH.$_REQUEST['path'];

if (strstr($dir, '..')) {
	Error_Handler::logError("Doesn't support relative paths", __FILE__,__LINE__);
	print ("Doesn't support relative paths\n");
	exit;
}

if (is_dir($dir)) {
	if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (strstr($file, ".css")) {
        		print("<dir:file name=\"$file\" />\n");
    		}
        }
        closedir($dh);
    }
}

print("</dir:directory>\n");
?>