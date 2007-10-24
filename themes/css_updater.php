<?php

include_once('../config.inc.php');
$username = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($username);
if (!$isAdministrator) {
	echo "not allowed";
	Error_Handler::logError("not allowed",__FILE__,__LINE__);
	exit;
}

/*
	var xmlContent = "<update>";
            xmlContent += "<data id=\"themeName\">" + document.getElementById("themeName").firstChild.nodeValue + "</data>";
            xmlContent += "<data id=\"cssFile\">" + document.getElementById("cssFile").firstChild.nodeValue + "</data>";
            xmlContent += "<data id=\"cssContent\">" + css + "</data>";
            xmlContent += "</update>";
            
            */
            
$request = file_get_contents('php://input');

//Error_Handler::logError($request);


$dom = new DOMDocument;
$dom->loadXML($request);
$xpath = new DOMXPath($dom);

$themeNameNodes = $xpath->query('/update/data[@id=\'themeName\']');
$themeNameNode = $themeNameNodes->item(0);
$theme_name = $themeNameNode->nodeValue;

$cssFileNodes = $xpath->query('/update/data[@id=\'cssFile\']');
$cssFileNode = $cssFileNodes->item(0);
$css_file = $cssFileNode->nodeValue;

$cssContentNodes = $xpath->query('/update/data[@id=\'cssContent\']');
$cssContentNode = $cssContentNodes->item(0);
$css_content = $cssContentNode->nodeValue;

//Error_Handler::logError(APP_PATH.'themes/'.$theme_name.'/css/'.$css_file);

file_put_contents(APP_PATH.'themes/'.$theme_name.'/css/'.$css_file, $css_content);
       
?>