<?php

include_once("config.inc.php");

$url = $_SERVER['QUERY_STRING'];

// Check to see what output is needed - either table cells or unordered list (default)
strrpos($url,"&form=");
$form = substr($url,strrpos($url,"&form=")+6);

// fetch the xmlfeed for this author
$xmlfeed = file_get_contents($url);

//xsl transform file
$xslfile='citation.xsl';

// Load the XML source
$xml = new DOMDocument;
$xmlfeed = utf8_encode($xmlfeed);  // convert everything to utf8 to eliminate problems

// load XML from a string
$xml->loadXML($xmlfeed);
//$xml->load($xmlfile);

$xsl = new DOMDocument;  // stylesheet to create citations 
$xsl->load($xslfile);
		
// Configure the transformer
$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl); // attach the xsl rules

$proc->setParameter('', 'form', $form);

$proc->setParameter('', 'baseURL',APP_BASE_URL);
	
echo $proc->transformToXML($xml);

?>
