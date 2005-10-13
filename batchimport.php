<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.batchimport.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");


$tpl = new Template_API();
$tpl->setTemplate("batchimport.tpl.html");

Auth::checkAuthentication(APP_SESSION);
$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);

$xdis_id = @$HTTP_POST_VARS["xdis_id"] ? $HTTP_POST_VARS["xdis_id"] : @$HTTP_GET_VARS["xdis_id"];	
$collection_pid = @$HTTP_POST_VARS["collection_pid"] ? $HTTP_POST_VARS["collection_pid"] : @$HTTP_GET_VARS["collection_pid"];	
$community_pid = @$HTTP_POST_VARS["community_pid"] ? $HTTP_POST_VARS["community_pid"] : @$HTTP_GET_VARS["community_pid"];	

$tpl->assign("collection_pid", $collection_pid);
$tpl->assign("community_pid", $community_pid);

$extra_redirect = "";
if (!empty($collection_pid)) {
	$extra_redirect.="&collection_pid=".$collection_pid;
}
if (!empty($community_pid)) {
	$extra_redirect.="&community_pid=".$community_pid;
}
$community_list = Community::getAssocList();
$collection_list = Collection::getAssocList();
if (@$HTTP_POST_VARS["cat"] == "report") {

    $res = BatchImport::insert();

	if (@$HTTP_POST_VARS['report_stays']) {
		// stay here
	} else { // otherwise redirect to list where this record would show
        sleep(1); // give fedora some time to update it's indexes or whatever it does.
//		Auth::redirect(APP_RELATIVE_URL . "list.php?new_pid=".$res.$extra_redirect, false);
	}


}

$jtaskData = "";
$maxG = 0;

//open the current directory
$directory = opendir(APP_SAN_IMPORT_DIR);
while (false !== ($file = readdir($directory))) { 
	// just get the directories
	if (!is_numeric(strpos($file, "."))) {
		$filenames[$file] = $file;
	}
}

$tpl->assign("filenames", $filenames);

$tpl->assign("xsd_display_fields", $xsd_display_fields);
$tpl->assign("xdis_id", $xdis_id);
$tpl->assign("form_title", "Batch Import Records");
$tpl->assign("form_submit_button", "Batch Import Records");

$setup = Setup::load();

$tpl->displayTemplate();
?>
