<?php

include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.lister.php");

$url = $_SERVER['QUERY_STRING'];

// Check to see what output is needed - either table cells or unordered list (default)
strrpos($url,"&form=");
$form = substr($url,strrpos($url,"&form=")+6);

// get the list of items to display
$list = Lister::getList($_GET, false);

// format nicely, adding base url and escaping any single quotes (for document.write output)
foreach($list['list'] as $index => $listItem) {
	//$citation = str_replace('href="', 'href="'.APP_BASE_URL, $listItem['rek_citation']);
	$citation = str_replace('href="', 'href="http://'.APP_HOSTNAME, $listItem['rek_citation']);   //works if fez not in root directory - heaphey
	$citation = str_replace("'", "\'", $citation);
	$citation = str_replace("\r", " ", str_replace("\n", " ", $citation));
	$list['list'][$index]['rek_citation'] = $citation;
}

// we're outputting javascript, let the browser know
header("Content-type: text/javascript; charset=UTF-8");

// and output the details
$tpl = new Template_API();
$tpl->setTemplate("display_dynamic_list.tpl.html");
$tpl->assign('list', $list['list']);
$tpl->assign('form', $form);
$tpl->displayTemplate();

FezLog::get()->close();
