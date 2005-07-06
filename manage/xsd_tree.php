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
// @(#) $Id: s.doc_type_xsds.php 1.2 03/07/14 04:55:26-00:00 jpm $
//

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
//include_once(APP_INC_PATH . "class.xsd_xsl_transform.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();

$tpl->setTemplate("manage/xsd_tree.tpl.html");

Auth::checkAuthentication(APP_SESSION);

$tpl->assign("type", "xsd_tree");

$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);

if ($isAdministrator) {

//echo $_POST['xsd_source'];
//$role_id = User::getRoleByUserCollection(Auth::getUserID(), $col_id);
$xdis_id = @$HTTP_POST_VARS["xdis_id"] ? $HTTP_POST_VARS["xdis_id"] : @$HTTP_GET_VARS["xdis_id"];
// TEST
//$xdis_id = 3;
$xsd_id = XSD_HTML_Match::getXSD_ID($xdis_id);

$top_element_name = Doc_Type_XSD::getDetails($xsd_id);
//print_r($top_element_name);
$top_element_name = $top_element_name['xsd_top_element_name'];


/*    if (@$HTTP_POST_VARS["cat"] == "update") {
        $tpl->assign("result", XSD_XSL_Transform::update($xsl_id));
    }
*/

	$xsd_str = array();
	$xsd_str = Doc_Type_XSD::getXSDSource($xsd_id);

	$xsd_str = $xsd_str[0]['xsd_file'];

    $xsd = new DomDocument();
if ($xsd->loadXML($xsd_str) === true) {
//  echo "It loaded ok (true)!!";
}
$array_ptr = array();
//Misc::dom_xsd_to_simple_array($xsd, &$array_ptr);
//print_r($array_ptr);
$temp = array();
//$temp = (Misc::array_to_dtree($array_ptr, $xdis_id));

Misc::dom_xsd_to_referenced_array($xsd, $top_element_name, &$array_ptr, "", "", $xsd);
//print_r($array_ptr);
$element_match_list = XSD_HTML_Match::getElementMatchList($xdis_id);
$temp = (Misc::array_to_dtree($array_ptr, $xdis_id, $element_match_list));
//var_dump($array_ptr);

$tpl->assign("xsd_tree", $temp[1]);
   
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();

?>
