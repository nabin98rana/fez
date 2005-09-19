<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Record Tracking System                                      |
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
// @(#) $Id: s.view.php 1.27 04/01/23 03:42:02-00:00 jpradomaia $
//
include_once("config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.record.php");
//include_once(APP_INC_PATH . "class.support.php");
//include_once(APP_INC_PATH . "class.notification.php");
//include_once(APP_INC_PATH . "class.attachment.php");
//include_once(APP_INC_PATH . "class.custom_field.php");
//include_once(APP_INC_PATH . "class.phone_support.php");
//include_once(APP_INC_PATH . "class.scm.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.collection.php");
//include_once(APP_INC_PATH . "class.draft.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "db_access.php");
//include_once(APP_INC_PATH . "class.mail_forward_list.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.fedora_api.php");


$tpl = new Template_API();
$tpl->setTemplate("view.tpl.html");

//Auth::checkAuthentication(APP_SESSION);

$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = Auth::isAdministrator();
$tpl->assign("isAdministrator", $isAdministrator);


//$role_id = User::getRoleByUser($usr_id)


$pid = @$HTTP_POST_VARS["pid"] ? $HTTP_POST_VARS["pid"] : $HTTP_GET_VARS["pid"];

$tpl->assign("fez_root_dir", APP_PATH);
$tpl->assign("eserv_url", APP_BASE_URL."eserv.php?pid=".$pid."&dsID=");
$tpl->assign("local_eserv_url", APP_RELATIVE_URL."eserv.php?pid=".$pid."&dsID=");


// @@@ CK - below three redundant and only for testing as current role gets set in class.template.php
//$role_id = User::getRoleByUserCollection($usr_id, $col_id);
//$tpl->assign("current_role", $role_id);
//echo $role_id;

	$tpl->assign("extra_title", "Record #$pid Details");
	if (!empty($pid)) {
		$tpl->assign("pid", $pid);
        $record = new RecordObject($pid);
        $xdis_id = $record->getXmlDisplayId();
	
		//echo "XDIS_ID -> ".$xdis_id;
		if (!is_numeric($xdis_id)) {
			$xdis_id = @$HTTP_POST_VARS["xdis_id"] ? $HTTP_POST_VARS["xdis_id"] : @$HTTP_GET_VARS["xdis_id"];	
			if (is_numeric($xdis_id)) { // must have come from select xdis so save xdis in the eSpace MD
                $record->updateAdminDatastream($xdis_id);
			}
		}
		if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
		//	echo "XDIS_ID -> ".$xdis_id;
		//	echo "redirecting";
            Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=view_form&pid=".$pid.$extra_redirect, false);
		}

        $tpl->assign("isViewer", $record->canView(true));
        if ($record->canView()) {
            $tpl->assign("isEditor", $record->canEdit(false));
		
            $display = new XSD_DisplayObject($xdis_id);
			$xsd_display_fields = $display->getMatchFieldsList();
			$tpl->assign("xsd_display_fields", $xsd_display_fields);

			$details = $record->getDetails();
			$controlled_vocabs = Controlled_Vocab::getAssocListAll();
			$tpl->assign("details_array", $details);
			foreach ($xsd_display_fields as $row) {
				if (($row['xsdmf_html_input'] == "contvocab") || ($row['xsdmf_html_input'] == "contvocab_selector")) {
					if (!empty($details[$row['xsdmf_id']])) {
						if (is_array($details[$row['xsdmf_id']])) {
							foreach ($details[$row['xsdmf_id']] as $ckey => $cdata) {
								$details[$row['xsdmf_id']][$ckey] = $controlled_vocabs[$cdata];
							}
						} else {
							$details[$row['xsdmf_id']] = $controlled_vocabs[$details[$row['xsdmf_id']]];
						}
					}
				}
			}
			foreach ($details as $dkey => $dvalue) { // turn any array values into a comma seperated string value
				if (is_array($dvalue)) {
					$details[$dkey] = implode("<br /> ", $dvalue);
				}
			}
		
			//$tpl->assign("collection_list", Collection::getAllExcept($col_id)); // OLD
			//@@@ CK 24/8/2004 - Fixed the escalation to show the teams that the collection is not currently set to
			if (isset($details['iss_col_id'])) {
				$tpl->assign("collection_list", Collection::getAllExceptSorted($details['iss_col_id']));
			}
		} else {
			$tpl->assign("show_not_allowed_msg", true);
		}
		//$tpl->assign("col_id", $col_id);
		// check if the requested record is a part of the 'current' collection // CK @@@ - 23/8/2004 removed the is current collection check
		// @@@ CK - 23/8/2004 OLD if ((empty($details)) || ($details['iss_col_id'] != $col_id)) {
		if (empty($details)) {
			$tpl->assign('details', '');
		} else {
			// @@@ - CK 2/9/2004 - If reporter is Admin User it means it came from an email so alter message accordingly
		//		$cf = Custom_Field::getListByRecord($col_id, $pid);
		//		print_r($cf);
		////	if ($details['reporter'] == 'Admin User') {
		//		$cf = Custom_Field::getListByRecord($col_id, $pid);
		////		$details['reporter'] = '<em>Created From Email - See originating emails or description below</em>';
		//		$details['reporter'] = $cf[
		//		print_r($cf);
		////	}
		
			$datastreams = Fedora_API::callGetDatastreams($pid);
			//print_r($datastreams);	
			$datastreams = Misc::cleanDatastreamList($datastreams);
			$tpl->assign("datastreams", $datastreams);
		
			$tpl->assign("ds_get_path", APP_FEDORA_GET_URL."/".$pid."/");
		
		
			$parents = Record::getParents($pid);
			$tpl->assign("parents", $parents);
		
			$tpl->assign("details", $details);

			$tpl->assign("controlled_vocabs", $controlled_vocabs);			
	
	}
} else {
	$tpl->assign("show_not_allowed_msg", true);
}
// @@@ CK - 20/10/2004 - Added list of email address to forward
//$tpl->assign("mail_forward_list", Mail_Forward_List::getAssocList());

//$tpl->displayTemplate();
// @@@ CK - 24/8/2004 - changed so template can get 

$tpl->displayTemplateRecord($pid);
?>
