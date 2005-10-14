<?php
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

?>
