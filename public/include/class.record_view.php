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
								if ($dis_field['sek_title'] == "Subject" || $dis_field['sek_title'] == "Fields of Research" || $dis_field['sek_title'] == "SEO Code") {
									$details[$dis_field['xsdmf_id']][$ckey] = "<a class='silent_link' href='".APP_BASE_URL."list/subject/".$cdata."/'>".Controlled_Vocab::getTitle($cdata)."</a>";
								} else {
									$details[$dis_field['xsdmf_id']][$ckey] = "<a class='silent_link' href=".'"'.APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".urlencode($details[$dis_field['xsdmf_id']][$ckey]).'"'.">".$cdata."</a>";
								}
							}
						} else {
							if ($dis_field['sek_title'] == "Subject" || $dis_field['sek_title'] == "Fields of Research" || $dis_field['sek_title'] == "SEO Code") {
								$details[$dis_field['xsdmf_id']] = "<a class='silent_link' href='".APP_BASE_URL."list/subject/".$details[$dis_field['xsdmf_id']]."/'>".Controlled_Vocab::getTitle($details[$dis_field['xsdmf_id']])."</a>";
							} else {
								$details[$dis_field['xsdmf_id']] = "<a class='silent_link' href=".'"'.APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".urlencode($details[$dis_field['xsdmf_id']]).'"'.">".Controlled_Vocab::getTitle($details[$dis_field['xsdmf_id']])."</a>";
							}
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
				if ($dis_field['xsdmf_data_type'] == 'date' || $dis_field['xsdmf_html_input'] == 'date') {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = $details[$cdata];
							}
						} else {
                            $month = substr($details[$dis_field['xsdmf_id']], 5, 2);
                            $day = substr($details[$dis_field['xsdmf_id']], 8, 2);

							if ($dis_field['xsdmf_date_type'] == 1) {
								$details[$dis_field['xsdmf_id']] = substr($details[$dis_field['xsdmf_id']], 0, 4);
							} elseif ($dis_field['xsdmf_date_type'] == 0) {
                                if ($month != '00' && $day != '00') {
                                    $details[$dis_field['xsdmf_id']] = substr($details[$dis_field['xsdmf_id']], 0, 10);
                                } elseif ($month != '00') {
                                    $details[$dis_field['xsdmf_id']] = substr($details[$dis_field['xsdmf_id']], 0, 7);
                                } else {
                                    $details[$dis_field['xsdmf_id']] = substr($details[$dis_field['xsdmf_id']], 0, 4);
                                }

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
        if ($dis_field['xsdmf_html_input'] == "rich_text") {
          $details[$dis_field['xsdmf_id']] = strip_tags($details[$dis_field['xsdmf_id']], '<p><br><b><i><u><strong><sub><sup><em>');
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

                if ($dis_field['sek_title'] == "DOI") {
                    if (!empty($details[$xsd_display_fields[$dis_key]['xsdmf_id']])) {
                    $details[$xsd_display_fields[$dis_key]['xsdmf_id']] =
                                "<a href='http://dx.doi.org/".htmlspecialchars($details[$xsd_display_fields[$dis_key]['xsdmf_id']])."'>".htmlspecialchars($details[$xsd_display_fields[$dis_key]['xsdmf_id']])."</a>";
                    }
                }
				if ($dis_field['sek_title'] == "Author") {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$temp_xsdmf_id = $dis_field['xsdmf_attached_xsdmf_id'];
								if ( is_array($details[$temp_xsdmf_id]) &&  (is_numeric($details[$temp_xsdmf_id][$ckey])) && ($details[$temp_xsdmf_id][$ckey] != 0)) {
									$details[$dis_field['xsdmf_id']][$ckey] = "<a title='Browse by Author ID for ".$details[$dis_field['xsdmf_id']][$ckey]."' class='author_id_link' href='".APP_BASE_URL."list/author_id/".urlencode($details[$temp_xsdmf_id][$ckey])."/'>".$details[$dis_field['xsdmf_id']][$ckey]."</a>";
								} else {
									$details[$dis_field['xsdmf_id']][$ckey] = "<a title='Browse by Author Name for ".$details[$dis_field['xsdmf_id']][$ckey]."' class='silent_link' href=".'"'.APP_BASE_URL."list/author/".urlencode($details[$dis_field['xsdmf_id']][$ckey])."/".'"'.">".htmlspecialchars($details[$dis_field['xsdmf_id']][$ckey])."</a>";

								}
							}
						} else {
							$temp_xsdmf_id = $dis_field['xsdmf_attached_xsdmf_id'];
							if ((is_numeric($details[$temp_xsdmf_id])) && ($details[$temp_xsdmf_id] != 0)) {
								$details[$dis_field['xsdmf_id']] = "<a title='Browse by Author ID for ".$details[$dis_field['xsdmf_id']]."' class='author_id_link' href='".APP_BASE_URL."list/author_id/".urlencode($details[$temp_xsdmf_id])."/'>".$details[$dis_field['xsdmf_id']]."</a>";
							} else {
								$details[$dis_field['xsdmf_id']] = "<a title='Browse by Author Name for ".$details[$dis_field['xsdmf_id']]."' class='silent_link' href=".'"'.APP_BASE_URL."list/author/".urlencode($details[$dis_field['xsdmf_id']])."/".'"'.">".htmlspecialchars($details[$dis_field['xsdmf_id']])."</a>";
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
								$details[$dis_field['xsdmf_id']][$ckey] = "<a class='silent_link' href=".'"'.APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B0%5D=".urlencode($details[$dis_field['xsdmf_id']][$ckey]).'"'.">".htmlspecialchars($details[$dis_field['xsdmf_id']][$ckey])."</a>";
							}
						} else {
							$details[$dis_field['xsdmf_id']] = "<a class='silent_link' href=".'"'.APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B0%5D=".urlencode($details[$dis_field['xsdmf_id']]).'"'.">".htmlspecialchars($details[$dis_field['xsdmf_id']])."</a>";
						}
					}
				}
				if ($dis_field['sek_title'] == "Journal Name" || $dis_field['sek_title'] == "Proceedings Title") {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						//Check for Ranked Journal Rank
						$rjl = "";
						if (APP_MY_RESEARCH_MODULE == 'ON') {
							$rjinfo = Record::getRankedJournalInfo($this->record->pid);
                            if (is_array($rjinfo)) {
                                if (array_key_exists('rj_2010_rank', $rjinfo) && $rjinfo['rj_2010_rank'] == '') {
                                    $rjinfo['rj_2010_rank'] = "N/R";
                                }
                                if (array_key_exists('rj_2012_title', $rjinfo)) {
                                    $rjl .= "&nbsp; (<a href='#' title='ERA 2012 Listed Journal: ".$rjinfo['rj_2012_title']."'>ERA 2012 Listed</a>)";
                                }
                                if (array_key_exists('rj_2010_rank', $rjinfo)) {
                                    $rjl .= "&nbsp;&nbsp;&nbsp; (<a href='#' title='ERA 2010 Ranked Journal: ".$rjinfo['rj_2010_title'].", ranked ".$rjinfo['rj_2010_rank']."'>ERA 2010 Rank ".$rjinfo['rj_2010_rank']."</a>)";
                                }
                            }
                        }
                        $sRdetails = SherpaRomeo::getJournalColourFromPid($this->record->pid);
                        if (is_array($sRdetails) && array_key_exists('colour', $sRdetails)) {
                            $rjl .= "&nbsp;&nbsp;&nbsp;".SherpaRomeo::convertSherpaRomeoToLink($sRdetails);
                        }
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = "<a class='silent_link' href=".'"'.APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".urlencode($details[$dis_field['xsdmf_id']][$ckey]).'"'.">".htmlspecialchars($details[$dis_field['xsdmf_id']][$ckey])."</a>".$rjl;
							}
						} else {
							$details[$dis_field['xsdmf_id']] = "<a class='silent_link' href=".'"'.APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".urlencode($details[$dis_field['xsdmf_id']]).'"'.">".htmlspecialchars($details[$dis_field['xsdmf_id']])."</a>".$rjl;
						}
					}
				}

				if ($dis_field['sek_title'] == "Conference Name") {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						//Check for Ranked Conference Rank
						$rcl = "";
						if (APP_MY_RESEARCH_MODULE == 'ON') {
							$rcinfo = Record::getRankedConferenceInfo($this->record->pid);
                            if (is_array($rcinfo)) {
                                if (array_key_exists('rc_2010_rank', $rcinfo) && $rcinfo['rc_2010_rank'] == '') {
                                    $rcinfo['rc_2010_rank'] = "N/R";
                                }
                                if (array_key_exists('rc_2012_title', $rcinfo)) {
                                    $rcl .= "&nbsp; (<a href='#' title='ERA 2012 Listed Conference: ".$rcinfo['rc_2012_title']."'>ERA 2012 Listed</a>)";
                                }
                                if (array_key_exists('rc_2010_rank', $rcinfo)) {
                                    $rcl .= "&nbsp;&nbsp;&nbsp; (<a href='#' title='ERA 2010 Ranked Conference: ".$rcinfo['rc_2010_title'].", ranked ".$rcinfo['rc_2010_rank']."'>ERA 2010 Rank ".$rcinfo['rc_2010_rank']."</a>)";
                                }
                            }
						}
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = "<a class='silent_link' href=".'"'.APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".urlencode($details[$dis_field['xsdmf_id']][$ckey]).'"'.">".htmlspecialchars($details[$dis_field['xsdmf_id']][$ckey])."</a>".$rcl;
							}
						} else {
							$details[$dis_field['xsdmf_id']] = "<a class='silent_link' href=".'"'.APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".urlencode($details[$dis_field['xsdmf_id']]).'"'.">".htmlspecialchars($details[$dis_field['xsdmf_id']])."</a>".$rcl;
						}
					}
				}

				if (($dis_field['sek_title'] == "Subject"  || $dis_field['sek_title'] == "Fields of Research" || $dis_field['sek_title'] == "SEO Code") && (($dis_field['xsdmf_html_input'] != "contvocab_selector")) ) {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = "<a class='silent_link' href='".APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".htmlspecialchars($details[$dis_field['xsdmf_id']][$ckey])."'>".htmlspecialchars($details[$dis_field['xsdmf_id']][$ckey])."</a>";
							}
						} else {
							$details[$dis_field['xsdmf_id']] = "<a class='silent_link' href='".APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".htmlspecialchars($details[$dis_field['xsdmf_id']])."'>".htmlspecialchars($details[$dis_field['xsdmf_id']])."</a>";
						}
					}
				}

			   if ($dis_field['sek_title'] == "Faculty") {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = "<a class='silent_link' href='".APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".htmlspecialchars($details[$dis_field['xsdmf_id']][$ckey])."'>".htmlspecialchars($details[$dis_field['xsdmf_id']][$ckey])."</a>";
							}
						} else {
							$details[$dis_field['xsdmf_id']] = "<a class='silent_link' href='".APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".htmlspecialchars($details[$dis_field['xsdmf_id']])."'>".htmlspecialchars($details[$dis_field['xsdmf_id']])."</a>";
						}
					}
				}

				if ($dis_field['sek_title'] == "Org Unit Name") {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = "<a class='silent_link' href='".APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".htmlspecialchars($details[$dis_field['xsdmf_id']][$ckey])."'>".htmlspecialchars($details[$dis_field['xsdmf_id']][$ckey])."</a>";
							}
						} else {
							$details[$dis_field['xsdmf_id']] = "<a class='silent_link' href='".APP_RELATIVE_URL."list/?cat=quick_filter&amp;search_keys%5B".$dis_field['xsdmf_sek_id']."%5D=".htmlspecialchars($details[$dis_field['xsdmf_id']])."'>".htmlspecialchars($details[$dis_field['xsdmf_id']])."</a>";
						}
					}
				}


				if ($dis_field['sek_title'] == "Language") {
					if (!empty($details[$dis_field['xsdmf_id']])) {
						if (is_array($details[$dis_field['xsdmf_id']])) {
							foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
								$details[$dis_field['xsdmf_id']][$ckey] = Language::getTitle($cdata);
							}
						} else {
							$details[$dis_field['xsdmf_id']] = Language::getTitle($details[$dis_field['xsdmf_id']]);
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

                // Load the details for Combo input type with Lookup function
                if ($dis_field['xsdmf_html_input'] == 'combo' && !empty($dis_field['sek_lookup_function'])) {
                    // Let's make sure the lookup's class & method exist
                    $obj = explode("::", $dis_field['sek_lookup_function']);
                    if (array_key_exists($dis_field['xsdmf_id'], $details) && isset($obj[0]) && isset($obj[1]) && method_exists($obj[0], $obj[1])) {
                        $result = eval("return " . $dis_field['sek_lookup_function'] . "('" . $details[$dis_field['xsdmf_id']] . "');");
                        if ( !empty($details[$dis_field['xsdmf_id']]) || !empty($result) ) {
                            $details[$dis_field['xsdmf_id']] = $details[$dis_field['xsdmf_id']] . " - " . $result;
                        } else {
                            $details[$dis_field['xsdmf_id']] = '';
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

    /**
     * Find next page given search listing order
     */
    function getNextPage($currentPid)
    {
        $params = $_SESSION['list_params'];
        $last_page = $_SESSION['last_page'];
        $view_page = $_SESSION['view_page'];
        if ($view_page < $last_page) {
            $params['pager_row'] = $view_page + 1;
            $params['form_name'] = $_SESSION['script_name'];
            $res = Lister::getList($params, false);
            $res['list_params'] = $params;

    		foreach ($res['list'] as $record) {
    		    $pids[] = $record['rek_pid'];
    		}

    		array_unshift($pids, $currentPid);

    		$_SESSION['list'] = $pids;
    		$_SESSION['list_params'] = $params;
    		$_SESSION['last_page'] = $res['list_info']['last_page'];
    		$_SESSION['view_page'] = $res['list_info']['current_page'];

            return $res['list'][0];
        }
        return array();
    }

    function getPrevPage($currentPid)
    {
        $params = $_SESSION['list_params'];
        $view_page = $_SESSION['view_page'];
        if ($view_page > 0) {
            $params['pager_row'] = $view_page - 1;
            $params['form_name'] = $_SESSION['script_name'];
            $res = Lister::getList($params, false);
            $res['list_params'] = $params;

            foreach ($res['list'] as $record) {
                $pids[] = $record['rek_pid'];
            }

            $_SESSION['list'] = $pids;
            $_SESSION['list_params'] = $params;
            $_SESSION['last_page'] = $res['list_info']['last_page'];
            $_SESSION['view_page'] = $res['list_info']['current_page'];

            // The current pid will be the last element on the array
            // so use -2 to get the previous pid
            return $res['list'][count($res['list'])-2];
        }
        return array();
    }

    function getNextPrevNavigation($pid)
    {
        if (!array_key_exists('list', $_SESSION)) {
            return false;
        }
        // Get the current listing
        $list = $_SESSION['list'];

        // find current position in list
        $list_idx = null;
		if (is_array($list)) {
			foreach ($list as $key => $item) {
				if ($item == $pid) {
					$list_idx = $key;
					break;
				}
			}
		}

        $prev = null;  // the next item in the list
        $next = null;  // the previous item in the list
        if (!is_null($list_idx)) {

        	if ($list_idx > 0) {
                $prev_pid = $list[$list_idx-1];
                $prev_pid_data = Record::getDetailsLite($prev_pid);
                $prev_pid_data = $prev_pid_data[0];
                $prev = array(
                    'rek_pid'   =>  $prev_pid,
                    'rek_title' =>  $prev_pid_data['rek_title'],
                );
            } else {
                $prev = recordView::getPrevPage($pid);
            }

            if ($list_idx < count($list)-1) {
                $next_pid = $list[$list_idx+1];
                $next_pid_data = Record::getDetailsLite($next_pid);
                $next_pid_data = $next_pid_data[0];
                $next = array(
                    'rek_pid'   =>  $next_pid,
                    'rek_title' =>  $next_pid_data['rek_title'],
                );
            } else {
                $next = recordView::getNextPage($pid);
            }
        }
    return array($prev, $next);
    }

}