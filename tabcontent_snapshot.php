<?php

/* This is very, VERY temporary. This is all reverse-engineered from the ETH Fex 
   demo site. We'll use their code to do this as soon as it's available. */

include_once("config.inc.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.cloud_tag.php");
include_once(APP_INC_PATH . "class.template.php");


$browseMode = @$_GET["browse"];
if ($browseMode == "topdownloads") {
	displayTopDownloads();
} elseif ($browseMode == "recentitems") {
	displayRecentItems();
} elseif ($browseMode == "tagcloud") {
	displayCloudTag();
}





function displayTopDownloads() {

	$tpl = new Template_API();
	$tpl->setTemplate("tab_top_downloads.html");

	$recentDownloads = Record::getRecentDLRecords();
	$list = Record::getDetailsLite($recentDownloads[0]);
	
	$tpl->assign("list", $list);
	$tpl->assign("downloads", $recentDownloads[1]);
	$tpl->displayTemplate();

}



function displayRecentItems() {

	$tpl = new Template_API();
	
	$tpl->setTemplate("tab_recent_items.html");
	$recentRecordsPIDs = Record::getRecentRecords();
	$list = Record::getDetailsLite($recentRecordsPIDs[0]);
	$tpl->assign("list", $list);
	$tpl->assign("eserv_url", APP_RELATIVE_URL."eserv/");
	$tpl->displayTemplate();

}



function displayCloudTag() {

	if (APP_CLOUD_TAG == "ON") {
		echo Cloud_Tag::buildCloudTag();
	} else {
		echo "This feature is unavailable.";
	}

}

?>