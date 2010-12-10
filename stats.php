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

include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.citation.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.statistics.php");
include_once(APP_INC_PATH . "class.user.php");

$tpl = new Template_API();
$tpl->setTemplate("stats.tpl.html");

if (WEBSERVER_LOG_STATISTICS != 'ON') {
	echo "WEB SERVER STATS CURRENLTY UNAVAILABLE";
	exit;
}
$range = (@$_REQUEST['range'] == "4w") ? "4w" : "all";
$year = is_numeric(@$_REQUEST['year']) ? $_REQUEST['year'] : 'all';
$month = (@$_REQUEST['month'] >= 1 && @$_REQUEST['month'] <= 12) ? $_REQUEST['month'] : 'all';

$browse = @$_REQUEST['browse'];
if ($browse == "top50authors") {
	
	$list = Statistics::getTop50AuthorsSummary(); 
	
	$tpl->assign("browse_heading", "Top 50 ".APP_NAME." Authors");
	$tpl->assign("extra_title", "Top 50 ".APP_NAME." Authors");
	$tpl->assign("browse_type", "browse_top50authors");
}  elseif ($browse == "top50papers") {

	$list = Statistics::getTop50PapersSummary();
	
	$tpl->assign("browse_heading", "Top 50 Downloaded Papers");
	$tpl->assign("extra_title", "Top 50 Downloaded Papers");
	$tpl->assign("browse_type", "browse_top50papers");
}  elseif ($browse == "show_detail_date") {
	if ($range == "4w") {

		$list = Statistics::get4WeekStatistics();

		$dateString = "for past 4 weeks";
	} elseif (is_numeric($month) && is_numeric($year)) {

		$list = Statistics::getYearMonthSummary($year, $month);
		$dateString = "for ".Statistics::getMonthName($month)." ".$year;
	} elseif (is_numeric($year)) {

		$list = Statistics::getYearSummary($year);
		$dateString = "for ".$year;
	} else { //all time

		$list = Statistics::getYearSummary();
		$dateString = "for all years";
	}
	$tpl->assign("browse_heading", "Document downloads ".$dateString);
	$tpl->assign("browse_type", "browse_show_detail_date");
	$tpl->assign("extra_title", "Document downloads ".$dateString);
}
$tpl->assign("thisYear", date("Y"));
$tpl->assign("lastYear", date("Y")-1);

$tpl->assign("eserv_url", APP_BASE_URL."eserv/");
$tpl->assign("list", $list);
$tpl->assign("list_info", $list_info);

$tpl->displayTemplate();