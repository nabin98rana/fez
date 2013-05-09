<?php
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
// | Authors: Elvi Shu <e.shu@library.uq.edu.au>                          |
// +----------------------------------------------------------------------+

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.researcherid.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.pager.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

// Load jQuery library
$tpl->assign("jquery", true);

$tpl->assign("type", "rid_profile_uploads");
$tpl->assign("active_nav", "admin");

// Assign user priviledges
Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
$isUser = Auth::getUsername();
$isAdministrator = User::isUserAdministrator($isUser);
$isSuperAdministrator = User::isUserSuperAdministrator($isUser);
$tpl->assign("isUser", $isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

// If user has no priviledge to access the page, 
// display message and return, no further process is required from here.
if (!$isAdministrator) {
    $tpl->assign("show_not_allowed_msg", true);
    $tpl->displayTemplate();
    return;
}

// Values for sort_by drop down
$sort_by_list = array(
                    "rpu_email_filename"  => "Email Filename",
                    "rpu_email_file_date" => "Email File Date",
                    "rpu_created_date"    => "Record Created Date",
                    "rpu_aut_org_username"=> "Related Author", 
                    "rpu_response_status" => "Response Status" 
                );
$tpl->assign('sort_by_list', $sort_by_list);

// Values for search keys drop down
$search_keys_list = array(
                        ""                    => "",
                        "rpu_email_filename"  => "Email Filename",
                        "rpu_aut_org_username"=> "Related Author"
                    );
$tpl->assign('search_keys_list', $search_keys_list);


// Save params to cookies
$cookies = Pager::saveSearchParams($params);


/** 
 * Get parameters value for loading RID Jobs list.
 * Assign the params to Smarty template at the same time.
 */
// Current page
$currentPage = getCurrentPage();

// Maximum number of rows per page
$rowsPerPage = getRowsPerPage($tpl);

// Sort params
$sortParams  = getSortParams($tpl);

// Search params
$searchParams = getSearchParams($tpl);

// Load RID Upload responses list
$list = ResearcherID::getProfileUploads($currentPage, $rowsPerPage, $sortParams, $searchParams);


$tpl->assign('list', $list['list']);
$tpl->assign('list_info', $list['list_info']);

$tpl->displayTemplate();




/**
 * The following are Local Helpers functions
 */

/**
 * Get the Sorting parameters
 * If there is no sorting parameters specified, the default sorting is set to 'rpu_email_file_date DESC'.
 * 
 * Assign sort parameter to Smarty template object.
 * 
 * @param  Template_API Object $tpl - Smarty template object.
 * @return array Sort by and sort order parameter.
 */
function getSortParams($tpl)
{
    $sort = array();
    $sort['by']    = Pager::getParam('sort_by',$params);
    $sort['order'] = Pager::getParam('sort_order',$params);
    if (empty($sort['by'])){
        $sort['by'] = "rpu_email_file_date";
    }
    if (empty($sort['order'])){
        $sort['order'] = "DESC";
    }
    
    $tpl->assign("sort_by", $sort['by']);
    $tpl->assign("sort_order", $sort['order']);
    
    return $sort;
}

/**
 * Get the value of Search parameters
 * Both key / value are required in order to perform records search.
 * 
 * Assign search parameter to Smarty template object.
 * 
 * @param  Template_API $tpl Smarty template object.
 * @return array Sort by and sort order value.
 */
function getSearchParams($tpl)
{
    $search = array();
    $searchkey = Pager::getParam('search_key',$params);
    $searchval = Pager::getParam('search_val',$params);
    if (!empty($searchkey) && !empty($searchval)){
        $search['key'] = $searchkey;
        $search['val'] = $searchval;
        $tpl->assign("search_key", $search['key']);
        $tpl->assign("search_val", $search['val']);
    }

    return $search;
}

/**
 * Get currently active page number.
 * 
 * @return int Current page number.
 */
function getCurrentPage()
{
    $page = Pager::getParam('pagerRow',$params);
    if (empty($page)) {
        $page = 0;
    }
    return $page;
}

/**
 * Get the maximum number of rows per page.
 * Assign value to Smarty template object.
 * 
 * @param  Template_API $tpl Smarty template object.
 * @return int Rows per page.
 */
function getRowsPerPage($tpl)
{
    $rows = Pager::getParam('rows',$params);
    if (empty($rows)) {
        $rows = APP_DEFAULT_PAGER_SIZE;
    }
    $tpl->assign("rows", $rows);
    return $rows;
}