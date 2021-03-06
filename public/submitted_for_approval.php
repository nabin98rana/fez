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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.palmer@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+


include_once("config.inc.php");
include_once(APP_INC_PATH . "class.db_api.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");

include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.collection.php");

Auth::checkAuthentication(APP_SESSION);

$tpl = new Template_API();
$tpl->assign("yui_autosuggest", '1');
$tpl->setTemplate("my_fez.tpl.html");
$options = Pager::saveSearchParams();

// for the my fez unpublished items page need to set the default to any non-published items 
if (!array_key_exists("searchKeycore_9", $options)) {
    $options["searchKeycore_9"] = Status::getID("Submitted for Approval"); 
    $options["noOrder"] = 0;
}

// set up the $sort_by var if necessary (makes rest of page work for sorting)
if (isset($_REQUEST['sort_by'])) {
	$sort_by = $_REQUEST['sort_by'];
}

if (empty($sort_by) || ($sort_by == "searchKey0" && empty($options['searchKey0']))) {
    $sort_by = "searchKey".Search_Key::getID("Title");
}

$collection_assoc_list = array();
$collection_assoc_list = Collection::getEditListAssoc();

$search_keys = Search_Key::getMyFezSearchList();
$sk_is_memberof = Search_Key::getID("isMemberOf");
$sk_status = Search_Key::getID("Status");

foreach ($search_keys as $skey => $svalue) {
    if ($svalue["sek_id"] == $sk_is_memberof) {
        $search_keys[$skey]["field_options"] = $collection_assoc_list;
    }
    
    if ($svalue["sek_smarty_variable"] == 'User::getAssocList()') {
        $search_keys[$skey]["field_options"] = array(
              "-1" => "un-assigned", 
              "-2" => "myself", 
              "-3" => "myself and un-assigned"
          ) + $search_keys[$skey]["field_options"];
    }
    
    if ($svalue["sek_html_input"] != 'multiple' && $svalue["sek_smarty_variable"] != 'Status::getUnpublishedAssocList()') {
        $search_keys[$skey]["field_options"] = array("" => "any") + $search_keys[$skey]["field_options"];       
    }   
    
    if ($svalue["sek_id"]  == $sk_status) {
        $search_keys[$skey]["field_options"] = array(
              "-4" => "any Unpublished"
          ) + $search_keys[$skey]["field_options"]; //get all status's
        
        if (!array_key_exists($options["searchKeycore_9"], $search_keys[$skey]["field_options"])) {
            $options["searchKeycore_9"] = "-4";
        }
    }
}


$pager_row  = $_GET['pager_row'];
$rows       = $_GET['rows'];

if (empty($pager_row))  $pager_row = 0;
if (empty($rows))       $rows = APP_DEFAULT_PAGER_SIZE;

$urlDataOrderBy = array(
    'cat'           =>  $_GET['cat'],
    'search_keys'   =>  $_GET['search_keys'],
    'rows'          =>  $rows,
    'pager_row'     =>  $pager_row,
);
$urlnoSort = Misc::query_string_encode($urlDataOrderBy);

$urlData = array(
    'cat'           =>  $_GET['cat'],
    'search_keys'   =>  $_GET['search_keys'],
    'sort_by'       =>  $_GET['sort_by'],
    'sort_order'    =>  $_GET['sort_order'],
);
$urlnoOrder = Misc::query_string_encode($urlData);

$bulk_workflows = WorkflowTrigger::getAssocListByTrigger("-1", 7); //get the bulk change workflows
$bulk_search_workflows = WorkflowTrigger::getAssocListByTrigger("-1", WorkflowTrigger::getTriggerId('Bulk Change Search')); 

 
// if search button was just pressed and all fields search 
// has something in it then sort by relevance enforced
if (Misc::GETorPost("search_button") == "Search" && trim($options["searchKey0"]) != "") {
    $options["sort_by"] = "searchKey0";
}

if ($options["searchKey0"] != "" && (Misc::GETorPost("sort_by") == "" || $options["sort_by"] == "searchKey0")) {
    $options["sort_order"] = 1;
} elseif (!is_numeric($options["sort_order"])) {
    $options["sort_order"] = 0; 
}


$assigned_items = Record::getListing($options, array("Editor"), $pager_row, $rows, $sort_by);
Record::getParentsByPids($assigned_items['list']);

$tpl->assign("bulk_workflows",          $bulk_workflows);
$tpl->assign("bulk_search_workflows",   $bulk_search_workflows);

$tpl->assign("page_url_order",          $_SERVER['PHP_SELF'].'?'.$urlnoSort);
$tpl->assign("page_url",                $_SERVER['PHP_SELF'].'?'.$urlnoOrder);

$tpl->assign('myFezView',               "SFA");
$tpl->assign('extra_title',             "Submitted for Approval");
$tpl->assign('search_keys',             $search_keys);
$tpl->assign("options",                 $options);
$tpl->assign("status_list",             Status::getAssocList());
$tpl->assign('my_assigned_items_list',  $assigned_items['list']);
$tpl->assign('items_info',              $assigned_items['info']);
$tpl->assign('isApprover',              Auth::isUserApprover(Auth::getUserID()));

$tpl->displayTemplate();