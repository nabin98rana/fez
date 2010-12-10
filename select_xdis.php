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
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.db_api.php");

$tpl = new Template_API();
$tpl->setTemplate("select_xdis.tpl.html");


    // check if the list of active collections consists of just 
    // one collection, and redirect the user to the main page of the 
    // application on that case
$collection_doc_types = XSD_Display::getAssocList();
$tpl->assign("collection_doc_types", $collection_doc_types);

$pid = @$_POST["pid"] ? $_POST["pid"] : @$_GET["pid"];
$return = @$_POST["return"] ? $_POST["return"] : @$_GET["return"];

$collection_pid = @$_POST["collection_pid"] ? $_POST["collection_pid"] : @$_GET["collection_pid"];	
$community_pid = @$_POST["community_pid"] ? $_POST["community_pid"] : @$_GET["community_pid"];	

$tpl->assign("collection_pid", $collection_pid);
$tpl->assign("community_pid", $community_pid);

$tpl->assign("pid", $pid);
$tpl->assign("return", $return);
if (@$_POST["cat"] == "select") {
	//redirect to the create/update/view form for the pid
	$xdis_id = $_POST['collection_doc_type'];

	$extra_redirect = "";
	if (!empty($collection_pid)) {
	
		$extra_redirect="&collection_pid=".$collection_pid;
	}
	if (!empty($community_pid)) {
	
		$extra_redirect="&community_pid=".$community_pid;
	}
	
	if ((is_numeric($xdis_id)) && ($return == 'update_form') && (!empty($pid))) {
		Auth::redirect(APP_RELATIVE_URL . "workflow/update.php?xdis_id=".$xdis_id."&pid=".$pid, false);
	} elseif ((is_numeric($xdis_id)) && ($return == 'view_form')) {
		Auth::redirect(APP_RELATIVE_URL . "view/".$pid."?xdis_id=".$xdis_id, false);
	} elseif ((is_numeric($xdis_id)) && ($return == 'insert_form')) {
		Auth::redirect(APP_RELATIVE_URL . "workflow/new.php?xdis_id=".$xdis_id.$extra_redirect, false);
	} else { // don't know where they wanted to go so redirect to the list page
		Auth::redirect(APP_RELATIVE_URL . "list.php", false);
	}

}

$tpl->displayTemplate();