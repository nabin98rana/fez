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
$username = Auth::getUsername();
$tpl->assign("isUser", $username);
$isAdministrator = Auth::isAdministrator();
$tpl->assign("isAdministrator", $isAdministrator);


$tpl->assign("fez_root_dir", APP_PATH);
$tpl->assign("eserv_url", APP_BASE_URL."eserv.php?pid=".$pid."&dsID=");
$tpl->assign("local_eserv_url", APP_RELATIVE_URL."eserv.php?pid=".$pid."&dsID=");
$tpl->assign("extra_title", "Record #$pid Details");
if (!empty($pid)) {
	$tpl->assign("pid", $pid);
	$record = new RecordObject($pid);
	$xdis_id = $record->getXmlDisplayId();
	$xdis_title = XSD_Display::getTitle($xdis_id);	
    $tpl->assign("xdis_title", $xdis_title);
	if (!is_numeric($xdis_id)) {
		$xdis_id = @$HTTP_POST_VARS["xdis_id"] ? $HTTP_POST_VARS["xdis_id"] : @$HTTP_GET_VARS["xdis_id"];	
		if (is_numeric($xdis_id)) { // must have come from select xdis so save xdis in the Fez MD
			$record->updateAdminDatastream($xdis_id);
		}
	}
	if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
		Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=view_form&pid=".$pid.$extra_redirect, false);
	}
	$tpl->assign("isViewer", $record->canView(true));
	if ($record->canView()) {
		$tpl->assign("isEditor", $record->canEdit(false));
		$tpl->assign("sta_id", $record->getPublishedStatus());
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
	} else {
		$tpl->assign("show_not_allowed_msg", true);
	}
	if (empty($details)) {
		$tpl->assign('details', '');
	} else {
		$datastreams = Fedora_API::callGetDatastreams($pid);
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
//print_r($GLOBALS['bench']->getProfiling());
?>
