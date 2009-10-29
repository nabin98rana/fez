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
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

class RecordView {

	private $record;

	function __construct($record)
	{
		$this->record = $record;
	}

	function getDetails()
	{
		$details = $this->record->getDetails();
		$xsd_display_fields = $this->getDisplayFields();
		foreach ($xsd_display_fields as $dis_key => $dis_field) {
			if (($dis_field['xsdmf_enabled'] == 1)) { // CK - took out check for is in view form, as not much is in view form now
				if ((($dis_field['xsdmf_html_input'] == "contvocab") || ($dis_field['xsdmf_html_input'] == "contvocab_selector")) && ($dis_field['xsdmf_cvo_save_type'] != 1)) {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = Controlled_Vocab::getTitle($cdata);
								$details[$dis_field['xsdmf_id']][$ckey] = "<a class='silent_link' href='".APP_BASE_URL."list/subject/".$cdata."/'>".Controlled_Vocab::getTitle($cdata)."</a>";
							}
						} else {
							$details[$dis_field['xsdmf_id']] = "<a class='silent_link' href='".APP_BASE_URL."list/subject/".$details[$dis_field['xsdmf_id']]."/'>".Controlled_Vocab::getTitle($details[$dis_field['xsdmf_id']])."</a>";
						}
					}
				}
				if ($dis_field['xsdmf_html_input'] == "xsdmf_id_ref") {

					$xsdmf_details_ref = XSD_HTML_Match::getDetailsByXSDMF_ID($dis_field['xsdmf_id_ref']);
					$xsdmf_id_ref = $xsdmf_details_ref['xsdmf_id'];
					if (($xsdmf_details_ref['xsdmf_html_input'] == 'contvocab') ||($xsdmf_details_ref['xsdmf_html_input'] == 'contvocab_selector')) {
						if (!empty($details[$dis_field['xsdmf_id']])) {
							$details[$xsdmf_id_ref] = array(); //clear the existing data
							if (is_array($details[$dis_field['xsdmf_id']])) {
								foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
									$details[$xsdmf_id_ref][$cdata] = Controlled_Vocab::getTitle($cdata);
									$details[$xsdmf_id_ref][$cdata] = "<a class='silent_link' href='".APP_BASE_URL."list/subject/".$cdata."/'>".Controlled_Vocab::getTitle($cdata)."</a>";
								}
							} else {
								$details[$xsdmf_id_ref] = "<a class='silent_link' href='".APP_BASE_URL."list/subject/".$details[$dis_field['xsdmf_id']]."/'>".Controlled_Vocab::getTitle($details[$dis_field['xsdmf_id']])."</a>";
							}
						}
					}
				}
				if ($dis_field['xsdmf_data_type'] == "date") {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = $details[$cdata];
							}
						} else {
							//$tempDate = new Date($details[$dis_field['xsdmf_id']]);
							//						$tempDate->format
							if (@$details[$dis_field['xsdmf_attached_xsdmf_id']] == 1) {
								$details[$dis_field['xsdmf_id']] = substr($details[$dis_field['xsdmf_id']], 0, 4);
							} elseif (@$details[$dis_field['xsdmf_attached_xsdmf_id']] == 2) {
								$details[$dis_field['xsdmf_id']] = substr($details[$dis_field['xsdmf_id']], 0, 7);
							}
						}
					}
				}
				if ($dis_field["xsdmf_html_input"] == 'org_selector' || $dis_field["xsdmf_html_input"] == 'depositor_org') {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$org_det = Org_Structure::getDetails($cdata);
								$details[$dis_field['xsdmf_id']][$ckey] = $org_det['org_title'];
							}
						} else {
							$org_det = Org_Structure::getDetails($details[$dis_field['xsdmf_id']]);
							$details[$dis_field['xsdmf_id']] = $org_det['org_title'];
						}
					}
				}
				if ($dis_field['xsdmf_html_input'] == "author_selector") {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = Author::getFullname($cdata);
							}
						} else {
							$details[$dis_field['xsdmf_id']] =  Author::getFullname($details[$dis_field['xsdmf_id']]);
						}
					}
				}
				if ($dis_field['sek_title'] == "Author") {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$temp_xsdmf_id = $dis_field['xsdmf_attached_xsdmf_id'];
								if ( is_array($details[$temp_xsdmf_id]) &&  (is_numeric($details[$temp_xsdmf_id][$ckey])) && ($details[$temp_xsdmf_id][$ckey] != 0)) {
									//if ( array_key_exists($temp_xsdmf_id, $details) ) {
									$details[$dis_field['xsdmf_id']][$ckey] = "<a title='Browse by Author ID for ".$details[$dis_field['xsdmf_id']][$ckey]."' class='author_id_link' href='".APP_BASE_URL."list/author_id/".urlencode($details[$temp_xsdmf_id][$ckey])."/'>".$details[$dis_field['xsdmf_id']][$ckey]."</a>";
								} else {
									$details[$dis_field['xsdmf_id']][$ckey] = "<a title='Browse by Author Name for ".$details[$dis_field['xsdmf_id']][$ckey]."' class='silent_link' href=".'"'.APP_BASE_URL."list/author/".urlencode($details[$dis_field['xsdmf_id']][$ckey])."/".'"'.">".$details[$dis_field['xsdmf_id']][$ckey]."</a>";

								}
							}
						} else {
							$temp_xsdmf_id = $dis_field['xsdmf_attached_xsdmf_id'];
							if ((is_numeric($details[$temp_xsdmf_id])) && ($details[$temp_xsdmf_id] != 0)) {
								$details[$dis_field['xsdmf_id']] = "<a title='Browse by Author ID for ".$details[$dis_field['xsdmf_id']]."' class='author_id_link' href='".APP_BASE_URL."list/author_id/".urlencode($details[$temp_xsdmf_id])."/'>".$details[$dis_field['xsdmf_id']]."</a>";
							} else {
								$details[$dis_field['xsdmf_id']] = "<a title='Browse by Author Name for ".$details[$dis_field['xsdmf_id']]."' class='silent_link' href=".'"'.APP_BASE_URL."list/author/".urlencode($details[$dis_field['xsdmf_id']])."/".'"'.">".$details[$dis_field['xsdmf_id']]."</a>";
							}
						}
					}
				}
				if ($dis_field['sek_title'] == "Description") {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = (trim($details[$dis_field['xsdmf_id']][$ckey]));
							}
						} else {
							$details[$dis_field['xsdmf_id']] = (trim($details[$dis_field['xsdmf_id']]));
						}
					}
				}
				if ($dis_field['sek_title'] == "Keywords") {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = "<a class='silent_link' href=".'"'.APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B0%5D=".urlencode($details[$dis_field['xsdmf_id']][$ckey]).'"'.">".$details[$dis_field['xsdmf_id']][$ckey]."</a>";
							}
						} else {
							$details[$dis_field['xsdmf_id']] = "<a class='silent_link' href=".'"'.APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B0%5D=".urlencode($details[$dis_field['xsdmf_id']]).'"'.">".$details[$dis_field['xsdmf_id']]."</a>";
						}
					}
				}
				if ($dis_field['sek_title'] == "Subject" && (($dis_field['xsdmf_html_input'] != "contvocab_selector")) ) {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = "<a class='silent_link' href='".APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B0%5D=".$details[$dis_field['xsdmf_id']][$ckey]."'>".$details[$dis_field['xsdmf_id']][$ckey]."</a>";
							}
						} else {
							$details[$dis_field['xsdmf_id']] = "<a class='silent_link' href='".APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B0%5D=".$details[$dis_field['xsdmf_id']]."'>".$details[$dis_field['xsdmf_id']]."</a>";
						}
					}
				}
				if ($dis_field["xsdmf_use_parent_option_list"] == 1 && is_array($parent_relationships)) {
					// if the display field inherits this list from a parent then get those options
					// Loop through the parents
					foreach ($parent_relationships as $pkey => $prel) {
						if (in_array($dis_field["xsdmf_parent_option_xdis_id"], $prel)) {
							$parent_record = new RecordObject($pkey);
							$parent_details = $parent_record->getDetails();
							if (is_array($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]])) {
								$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($dis_field["xsdmf_parent_option_child_xsdmf_id"]);
								if ($xsdmf_details['xsdmf_smarty_variable'] != "" && ($xsdmf_details['xsdmf_html_input'] == "multiple" || $xsdmf_details['xsdmf_html_input'] == "dual_multiple")) {
									$temp_parent_options = array();
									$temp_parent_options_final = array();
									eval("\$temp_parent_options = ". $xsdmf_details['xsdmf_smarty_variable'].";");
									$xsd_display_fields[$dis_key]['field_options'] = array();
									foreach ($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]] as $parent_smarty_option) {
										if (array_key_exists($details[$dis_field['xsdmf_id']], $temp_parent_options)) {
											$details[$dis_field['xsdmf_id']] = $temp_parent_options[$details[$dis_field['xsdmf_id']]];
										}
									}
								}
							}
						}
					}
				}
			}
		}
		foreach ($details as $dkey => $dvalue) { // turn any array values into a comma seperated string value
			if (is_array($dvalue)) {
				$details[$dkey] = implode("<br /> ", $dvalue);
			}
		}

		return $details;
	}

	function getDisplayFields()
	{
		$this->record->getDisplay();
		$this->xsd_display_fields = $this->record->display->getMatchFieldsList(array("FezACML"), array());
		return $this->xsd_display_fields;
	}

}
