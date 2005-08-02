<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004 MySQL AB                                    |
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
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: s.new.php 1.14 03/07/11 05:04:05-00:00 jpm $
//
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
//include_once(APP_INC_PATH . "class.news.php");
//include_once(APP_INC_PATH . "class.category.php");
//include_once(APP_INC_PATH . "class.subcategory.php");
//include_once(APP_INC_PATH . "class.release.php");
include_once(APP_INC_PATH . "class.record.php");

include_once(APP_INC_PATH . "class.batchimport.php");

include_once(APP_INC_PATH . "class.misc.php");
//include_once(APP_INC_PATH . "class.resolution_location.php");
//include_once(APP_INC_PATH . "class.support.php");
//include_once(APP_INC_PATH . "class.custom_field.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.date.php");
//include_once(APP_INC_PATH . "class.library_staff_ad.php");
//include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");


$tpl = new Template_API();
$tpl->setTemplate("batchimport.tpl.html");

Auth::checkAuthentication(APP_SESSION);
//$user_id = Auth::getUserID();

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);
//$col_id = Auth::getCurrentCollection();
//$role_id = User::getRoleByUserCollection($user_id, $col_id);

//if ($role_id == User::getRoleID('standard user') || ($role_id == User::getRoleID('administrator')) || ($role_id == User::getRoleID('manager'))) {

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
/*if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
//	echo "XDIS_ID -> ".$xdis_id;
//	echo "redirecting";
	Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=insert_form".$extra_redirect, false);
}
$tpl->assign("xdis_id", $xdis_id);
*/
if (@$HTTP_POST_VARS["cat"] == "report") {

    $res = BatchImport::insert();
/*    if ($res != -1) {
        // show direct links to the issue page, issue listing page and 
        // email listing page
        $tpl->assign("new_issue_id", $res);
    } else {
        // need to show everything again
        $tpl->assign("error_msg", "1");
    }*/

	if (@$HTTP_POST_VARS['report_stays']) {
		// stay here
	} else { // otherwise redirect to list where this record would show
        sleep(1); // give fedora some time to update it's indexes or whatever it does.
//		Auth::redirect(APP_RELATIVE_URL . "list.php?new_pid=".$res.$extra_redirect, false);
	}


}

/*if (@$HTTP_GET_VARS["cat"] == "associate") {
    $res = Support::getListDetails($HTTP_GET_VARS["item"]);
    $tpl->assign("emails", $res);
    $tpl->assign("attached_emails", @implode(",", $HTTP_GET_VARS["item"]));
}*/

//$subcatQuery = array();
//$subcatQuery = Subcategory::getAssocListAll();
//print_r($subcatQuery);
$jtaskData = "";
$maxG = 0;
/*foreach($subcatQuery as $iquery ) {
//        for($i=0; $i<count($iquery);$i++) {
				$jtaskData .= "group[".$iquery[0]."][".$iquery[1]."]=new Option(\"".$iquery[1]."\",\"".$iquery[2]."\")\n";
//        }
//        $ind++;
	if ($iquery[0] > $maxG) {
		$maxG = $iquery[0];
	}
}*/
//$tpl->assign("maxG", ($maxG+1)); // +1 so for the 'please select'
//$tpl->assign("jtaskData", $jtaskData);
//$tpl->assign("library_staff", Library_Staff_AD::getAssocList());
//@@@ - CK - 24/8/32004 - Added default reminder date for the new reminder functionality
//$tpl->assign("default_reminder_date", mktime (0,0,0,date("m"),date("d")+7,  date("Y"))); 
//$tpl->assign("col_id", $col_id);
//$tpl->assign("user_id", $user_id);
//$tpl->assign("cats", Category::getAssocList($col_id));
//$tpl->assign("priorities", Misc::getPriorities());
//$tpl->assign("resolution_locations", Resolution_Location::getAssocList());
//$users = User::getActiveAssocListByCollection($col_id);
//$users = Collection::getUserAssocList($col_id, 'active');
//$tpl->assign("users", Collection::getUserAssocList($col_id, 'active'));
// CK - 20/1/2005 - filter out the generic askit usernames (which contain askit as a string).

//$tpl->assign("users", $users);
//$tpl->assign("users", User::getActiveAssocList());
//$tpl->assign("users", Collection::getUserAssocList($col_id, 'active'));
//$tpl->assign("releases", Release::getAssocList($col_id));

// @@@ 21/7/2004 CK - Added $custom_modified to sort library branches alphabetically, and any other custom fields as needed
/*$custom_modified = array();
$custom_modified = (Custom_Field::getListByCollection($col_id, 'report_form'));
asort($custom_modified[1]['field_options']);
$tpl->assign("custom_fields", $custom_modified);*/

//$custom_modified = array();
//$xsd_display_fields = (XSD_HTML_Match::getListByCollection($col_id, 'report_form'));
//$xdis_id = 5; // was 5
/*$xsd_display_fields = (XSD_HTML_Match::getListByDisplay($xdis_id));

//@@@ CK - 26/4/2005 - fix the combo and multiple input box lookups - should probably move this into a function somewhere later
foreach ($xsd_display_fields  as $dis_key => $dis_field) {
	if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'multiple') {
		if (!empty($dis_field["xsdmf_smarty_variable"]) && $dis_field["xsdmf_smarty_variable"] != "none") {
			eval("\$xsd_display_fields[\$dis_key]['field_options'] = " . $dis_field["xsdmf_smarty_variable"] . ";");
		}
		if (!empty($dis_field["xsdmf_dynamic_selected_option"]) && $dis_field["xsdmf_dynamic_selected_option"] != "none") {
			eval("\$xsd_display_fields[\$dis_key]['selected_option'] = " . $dis_field["xsdmf_dynamic_selected_option"] . ";");
		}
	}
}
*/

//open the current directory
$directory = opendir(APP_SAN_IMPORT_DIR);
while (false !== ($file = readdir($directory))) { 
	if (!is_numeric(strpos($file, "."))) {
		$filenames[$file] = $file;
	}
}
/*foreach ($filenames as $file)
{
	//echo "$file<br>";
} 
*/
$tpl->assign("filenames", $filenames);
//print_r($xsd_display_fields);
//asort($custom_modified[1]['field_options']);
$tpl->assign("xsd_display_fields", $xsd_display_fields);
$tpl->assign("xdis_id", $xdis_id);
$tpl->assign("form_title", "Batch Import Records");
$tpl->assign("form_submit_button", "Batch Import Records");

//print_r($HTTP_POST_VARS);

/*$teams = Collection::getAllExcept($col_id);
if ($col_id == 2) { // @@@ CK - 21/1/2005 - If askit then remove litlos from the escalation list in create issue - by their request.
	$teams = Misc::array_clean($teams, 'litlo', false);
}
*/
//$tpl->assign("collection_list", $teams);

$setup = Setup::load();
//$tpl->assign("allow_unassigned_issues", $setup["allow_unassigned_issues"]);


//$user_details = User::getDetails(Auth::getUserID());
//$primary_campus_id = $user_details['usr_primary_campus_id'];
//$tpl->assign("user_primary_campus_id", $primary_campus_id);
//$tpl->assign("news", News::getListByCollection($col_id));

//} else {
//	Auth::redirect(APP_RELATIVE_URL . "list.php", false);
//    $tpl->assign("show_not_allowed_msg", true);
//}


$tpl->displayTemplate();
?>
