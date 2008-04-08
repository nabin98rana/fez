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

	$username = Auth::getUsername();
	$tpl->assign("isUser", $username);
	$isAdministrator = User::isUserAdministrator($username);
	if (Auth::userExists($username)) { // if the user is registered as a Fez user
		$tpl->assign("isFezUser", $username);
	}
	$tpl->assign("isAdministrator", $isAdministrator);

	$rows = 5; // Number to display
	$pager_row = 0;	
	$sort_by = "File Downloads";
	$options = array();                                                          
	$options["sort_order"] = 1; // sort desc
	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
	$options["searchKey".Search_Key::getID("Object Type")] = 3; // enforce records only
	$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, false, true);
	$list_info = $list["info"];
	$list = $list["list"];
	$list = Citation::renderIndexCitations($list);
	$tpl->assign("list", $list);
	$tpl->displayTemplate();

}



function displayRecentItems() {

	$tpl = new Template_API();

	$username = Auth::getUsername();
	$tpl->assign("isUser", $username);
	$isAdministrator = User::isUserAdministrator($username);
	if (Auth::userExists($username)) { // if the user is registered as a Fez user
		$tpl->assign("isFezUser", $username);
	}
	$tpl->assign("isAdministrator", $isAdministrator);

	$tpl->setTemplate("tab_recent_items.html");
	$recentRecordsPIDs = Record::getRecentRecords();
	$list['list'] = Record::getDetailsLite($recentRecordsPIDs[0]);
	$list = $list["list"];
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