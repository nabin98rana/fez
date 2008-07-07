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
// |                                                                      |
// +----------------------------------------------------------------------+
//
//
include_once('config.inc.php');
include_once(APP_INC_PATH . "class.template.php");
//include_once(APP_INC_PATH . "class.oai.php");
include_once(APP_INC_PATH . "class.record.php");

$tpl = new Template_API();

$tpl_file = "orca.tpl.html";	
$tpl->setTemplate($tpl_file);

$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
$filter["searchKey".Search_Key::getID("Object Type")] = 2; // enforce collection level objects only
$pager_row = 0;
$rows = 1000000;
$sort_by = "searchKey".Search_Key::getID("Created Date");
$options = array();
$options["sort_order"] = 0;
//$sort_by = "Created Date";
$getSimple = false;
$citationCache = false;

$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter);

$list_info = $list["info"];
$list = $list["list"];

$tpl->assign("list", $list);

header("Content-type: text/xml");
//print_r($list); exit;
$tpl->displayTemplate();



?>
