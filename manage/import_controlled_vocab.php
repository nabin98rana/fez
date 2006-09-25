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
include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "db_access.php");

$tpl = new Template_API();
$tpl->setTemplate("manage/index.tpl.html");

Auth::checkAuthentication(APP_SESSION);
$tpl->assign("extra_title", "Import Controlled Vocabulary");
$tpl->assign("type", "import_controlled_vocab");
$parent_id = @$HTTP_POST_VARS["parent_id"] ? $HTTP_POST_VARS["parent_id"] : @$HTTP_GET_VARS["parent_id"];	
//$parents = Controlled_Vocab::getParentAssocListFullDisplay($parent_id);
	$cvo_id = $parent_id;
	$max_breadcrumb = "";
	$newcrum = "";
	if (!empty($cvo_id)) {
		$breadcrumb = Controlled_Vocab::getParentAssocListFullDisplay($cvo_id);
		$breadcrumb = Misc::array_merge_preserve($breadcrumb, Controlled_Vocab::getAssocListByID($cvo_id));
	
		$newcrumb = array();
		foreach ($breadcrumb as $key => $data) {
			array_push($newcrumb, array("cvo_id" => $key, "cvo_title" => $data));
		}
		$max_breadcrumb = (count($newcrumb) -1);
	}
	$tpl->assign("max_subject_breadcrumb", $max_breadcrumb);
	$tpl->assign("subject_breadcrumb", $newcrumb);

$tpl->assign("parent_id", $parent_id);
$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);

if ($isAdministrator) {
  
    if (@$HTTP_POST_VARS["cat"] == "import") {
        $files = array();
        for ($i = 0; $i < count($HTTP_POST_FILES["cvi_xml_filename"]); $i++) {
            $filename = @$HTTP_POST_FILES["cvi_xml_filename"]["name"][$i];
            if (empty($filename)) {
                continue;
            }
            $blob = Misc::getFileContents($HTTP_POST_FILES["cvi_xml_filename"]["tmp_name"][$i]);
            $files[] = array(
                "filename"  =>  $filename,
                "type"      =>  $HTTP_POST_FILES['cvi_xml_filename']['type'][$i],
                "blob"      =>  $blob
            );
        }
		if (!empty($blob)) {
			$tpl->assign("result", Controlled_Vocab::import($parent_id, $blob));
		} else {
			$tpl->assign("result", -1);
		}
    }
//    $tpl->assign("parents", $parents); // for the parents about the very first one
	if (is_numeric($parent_id)) {
	    $tpl->assign("parent_title", Controlled_Vocab::getTitle($parent_id));
	} else {
		$tpl->assign("parent_title", "0");
	}
    $tpl->assign("list", Controlled_Vocab::getList($parent_id));
} else {
    $tpl->assign("show_not_allowed_msg", true);
}

$tpl->displayTemplate();
?>