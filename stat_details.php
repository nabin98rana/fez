<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
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

include_once("config.inc.php");

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.statistics.php");

$tpl = new Template_API();
$tpl->setTemplate("stat_details.tpl.html");

if (WEBSERVER_LOG_STATISTICS != 'ON') {
	echo "WEB SERVER STATS CURRENLTY UNAVAILABLE";
	exit;
}

$abstractViews = 0;
$downloads = 0;

$pid = @$_POST["pid"] ? $_POST["pid"] : $_GET["pid"];
$pid = (!empty($pid)) ? $pid : 'all';
$action = @$_REQUEST['action'];
$country = @$_REQUEST['country'];
$range = (@$_REQUEST['range'] == "4w") ? "4w" : "all";
$year = is_numeric(@$_REQUEST['year']) ? $_REQUEST['year'] : 'all';
$month = (@$_REQUEST['month'] >= 1 && @$_REQUEST['month'] <= 12) ? $_REQUEST['month'] : 'all';
$max_width = 500; // Max pixel width of barcharts
$max_count = 1;
if ($pid != 'all') {
	$record = new RecordObject($pid);
	$object_title = $record->getTitle();
	$tpl->assign("title", $object_title);
}

if ($range == "4w") {
	$dateString = "for past 4 weeks";
} elseif (is_numeric($month) && is_numeric($year)) {
	$dateString = "for ".Statistics::getMonthName($month)." ".$year;		
} elseif (is_numeric($year)) {
	$dateString = "for ".$year;
} else { //all time
	$dateString = "for all years";
}
if ($action == "show_detail") {
	$userAbstractViews = Statistics::getStatsByUserAbstractView($pid, $year, $month, $range);
	$userDownloads = Statistics::getStatsByUserDownloads($pid, $year, $month, $range);

	$allUsers = Statistics::mergeUsers($userAbstractViews, $userDownloads);
	for ($i=0;$i<count($allUsers);$i++) {
		$max_count = max($max_count, $allUsers[$i]['downloads'], $allUsers[$i]['abstracts']);
	}

	for ($i=0;$i<count($allUsers);$i++) {
		$allUsers[$i]['abstractViewsWidth'] = (int) ($allUsers[$i]['stl_country_abstracts']/$max_count * $max_width); 
		$allUsers[$i]['downloadsWidth'] = (int) ($allUsers[$i]['stl_country_downloads']/$max_count * $max_width); 
	}


	$abstractViews = Statistics::getStatsByAbstractView($pid, $year, $month, $range);
	$downloads = Statistics::getStatsByAllFileDownloads($pid, $year, $month, $range);
	$countryAll = Statistics::getStatsByCountryAbstractsDownloads($pid, $year, $month, $range);	
	$abstractViewsHistory = Statistics::getStatsByAbstractViewHistory($pid);
	$downloadsHistory = Statistics::getStatsByDownloadHistory($pid);
	$allHistory = Statistics::mergeDates($abstractViewsHistory, $downloadsHistory);

	$max_count = max($max_count, $abstractViews, $downloads, 1);
	for ($i=0;$i<count($countryAll);$i++) {
		$max_count = max($max_count, $countryAll[$i]['stl_country_downloads'], $countryAll[$i]['stl_country_abstracts']);
	}
	for ($i=0;$i<count($allHistory);$i++) {
		$max_count = max($max_count, $allHistory[$i]['abstracts'], $allHistory[$i]['downloads']);
	}
	$abstractViewsWidth = (int) ($abstractViews/$max_count * $max_width);
	$downloadsWidth = (int) ($downloads/$max_count * $max_width);
	for ($i=0;$i<count($countryAll);$i++) {
		$countryAll[$i]['abstractViewsWidth'] = (int) ($countryAll[$i]['stl_country_abstracts']/$max_count * $max_width); 
		$countryAll[$i]['downloadsWidth'] = (int) ($countryAll[$i]['stl_country_downloads']/$max_count * $max_width); 
		// select a flag
		$ccode = strtolower($countryAll[$i]['stl_country_code']);
		$c_flag = 'images/flags18x14/' . $ccode . '.png';
		if (file_exists(APP_PATH . $c_flag)) {
			$countryAll[$i]['flag'] = $c_flag;
		} else {
			$countryAll[$i]['flag'] = "images/flags18x14/" ."unknown.png";
		}
	}

} elseif ($action == "cumulative_usage") {
	$abstractViewsHistory = Statistics::getStatsByAbstractViewHistory($pid);
	$downloadsHistory = Statistics::getStatsByDownloadHistory($pid);
	$allHistory = Statistics::mergeDates($abstractViewsHistory, $downloadsHistory);
	for ($i=0;$i<count($allHistory);$i++) {
		$max_count = max($max_count, $allHistory[$i]['abstracts'], $allHistory[$i]['downloads']);
	}

} elseif ($action == "cumulative_usage_country") {
	$countryAll = Statistics::getStatsByCountryAbstractsDownloads($pid, $year, $month, $range);	
		// select a flag
	for ($i=0;$i<count($countryAll);$i++) {
		$ccode = strtolower($countryAll[$i]['stl_country_code']);
		$c_flag = 'images/flags18x14/' . $ccode . '.png';
		if (file_exists(APP_PATH . $c_flag)) {
			$countryAll[$i]['flag'] = $c_flag;
		} else {
			$countryAll[$i]['flag'] = "images/flags18x14/" ."unknown.png";
		}
		$max_count = max($max_count, $countryAll[$i]['stl_country_downloads'], $countryAll[$i]['stl_country_abstracts']);
	}
} elseif (($action == "cumulative_usage_country_specific") && ($country != "")) {
	
	$tpl->assign("country_name", $country);
	$countryAll = Statistics::getStatsByCountrySpecificAbstractsDownloads($pid, $year, $month, $range, $country);	
	for ($i=0;$i<count($countryAll);$i++) {
		$max_count = max($max_count, $countryAll[$i]['stl_country_downloads'], $countryAll[$i]['stl_country_abstracts']);
	}
}

for ($i=0;$i<count($countryAll);$i++) {	
	$countryAll[$i]['abstractViewsWidth'] = (int) ($countryAll[$i]['stl_country_abstracts']/$max_count * $max_width); 
	$countryAll[$i]['downloadsWidth'] = (int) ($countryAll[$i]['stl_country_downloads']/$max_count * $max_width); 
}

for ($i=0;$i<count($allUsers);$i++) {
	$allUsers[$i]['abstractViewsWidth'] = (int) ($allUsers[$i]['abstracts']/$max_count * $max_width); 
	$allUsers[$i]['downloadsWidth'] = (int) ($allUsers[$i]['downloads']/$max_count * $max_width); 
}



for ($i=0;$i<count($allHistory);$i++) {
	$allHistory[$i]['abstractViewsWidth'] = (int) ($allHistory[$i]['abstracts']/$max_count * $max_width); 
	$allHistory[$i]['downloadsWidth'] = (int) ($allHistory[$i]['downloads']/$max_count * $max_width); 
}

$tpl->assign("action", $action);
$tpl->assign("pid", $pid);
$tpl->assign("eserv_url", APP_BASE_URL."eserv/");
$tpl->assign("thisYear", date("Y"));
$tpl->assign("lastYear", date("Y")-1);
$tpl->assign("downloads", $downloads);
$tpl->assign("abstractViews", $abstractViews);
$tpl->assign("downloadsWidth", $downloadsWidth);
$tpl->assign("userDownloadsWidth", $userDownloadsWidth);
$tpl->assign("userAbstractViewsWidth", $userAbstractViewsWidth);
$tpl->assign("userAbstractViews", $userAbstractViews);
$tpl->assign("userDownloads", $userDownloads);
$tpl->assign("abstractViewsWidth", $abstractViewsWidth);

$tpl->assign("dateString", $dateString);
//$tpl->assign("list", $list);
$tpl->assign("listHistory", $allHistory);
$tpl->assign("listUsers", $allUsers);
$tpl->assign("firstLogged", Statistics::getEarliestUserView());
$tpl->assign("listCountry", $countryAll);
$tpl->assign("listCountryCount", count($countryAll));
//$tpl->assign("list", $list_history);
//$tpl->assign("list_info", $list_info);

$tpl->displayTemplate();
?>
