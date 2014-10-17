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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//





/**
 * Class to handle the business logic related to the Fez OAI provider service.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.search_key.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.statistics.php");
include_once(APP_INC_PATH . "class.fulltext_index.php");

class OAI
{


	/**
	 * Method used to get the list of records publicly available in the
	 * system.
	 *
	 * @access  public
	 * @param   string $set oai set collection (optional).
	 * @param   integer $current_row The point in the returned results to start from.
	 * @param   integer $max The maximum number of records to return
	 * @return  array The list of records
	 */
	function ListRecords($set, $identifier="", $current_row = 0, $max = 100, $order_by = 'Created Date', $from="", $until="", $setType, $filter=array())
	{
		$options = array();
		if ($max == "ALL") {
			$max = 9999999;
		}
		$current_row = ($current_row/100);
		$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		$filter["searchKey".Search_key::getID("Object Type")]=3; //exclude communities and collections bh 24/11/2008
		if (!empty($identifier)) {
			$filter["searchKey".Search_Key::getID("Pid")] = $identifier;
		} elseif (!empty($set)) {
			if ($setType == "isMemberOf") {
				$filter["searchKey".Search_Key::getID("isMemberOf")] = $set;
			} else {
				$filter["searchKey".Search_Key::getID("Subject")] = $set;
			}
		}
		//bh 24/11/2008 changed "Date" to "Updated Date" in below block to ensure harvesting operates on this rather than publication date
		if ($from != "" && $until != "") {
			$filter["searchKey".Search_Key::getID("Updated Date")] = array();
			$filter["searchKey".Search_Key::getID("Updated Date")]["filter_type"] = "between";
			$filter["searchKey".Search_Key::getID("Updated Date")]["filter_enabled"] = 1;
			$filter["searchKey".Search_Key::getID("Updated Date")]["start_date"] = $from;
			$filter["searchKey".Search_Key::getID("Updated Date")]["end_date"] = $until;
		} elseif (!empty($from) && empty($until)) {
			$filter["searchKey".Search_Key::getID("Updated Date")] = array();
			$filter["searchKey".Search_Key::getID("Updated Date")]["filter_type"] = "greater";
			$filter["searchKey".Search_Key::getID("Updated Date")]["filter_enabled"] = 1;
			$filter["searchKey".Search_Key::getID("Updated Date")]["start_date"] = $from;
		} elseif (!empty($until) && empty($from)) {
			$filter["searchKey".Search_Key::getID("Updated Date")] = array();
			$filter["searchKey".Search_Key::getID("Updated Date")]["filter_type"] = "less";
			$filter["searchKey".Search_Key::getID("Updated Date")]["filter_enabled"] = 1;
			$filter["searchKey".Search_Key::getID("Updated Date")]["start_date"] = $until;
		}

		$return = Record::getListing($options, array(9,10), $current_row, $max, $order_by, false, false, $filter);
		$return['list'] = Record::getParentTitlesByPIDS($return['list']);
        $usr_id = Auth::getUserID();
        if (!is_numeric($usr_id)) {
          Record::getSearchKeysByPIDS($return['list'], true);
        }
		if (is_array($return['list'])) {
			foreach ($return['list'] as $rkey => $res) {
				$fans = array();
				if (is_array($res['rek_file_attachment_name'])) {
					foreach($res['rek_file_attachment_name'] as $fan) {
						if (Misc::isAllowedDatastream($fan, $res['rek_pid'])) {
							array_push($fans, $fan);
						}
					}
				}

				if( !empty($res['rek_created_date']) )	{
					$return['list'][$rkey]['rek_created_date'] = Date_API::getFedoraFormattedDateUTC(strtotime($res['rek_created_date']));
				}
				if( !empty($res['rek_updated_date']) )	{
					$return['list'][$rkey]['rek_updated_date'] = Date_API::getFedoraFormattedDateUTC(strtotime($res['rek_updated_date']));
				}

				$return['list'][$rkey]['rek_file_attachment_name'] = $fans;
			}
		}
		return $return;

	}

	function makeReturnList($res, $statsFlag = 0)
	{
		$return = array();

		foreach ($res as $result) {
			if ($result['sek_title'] == 'isMemberOf') {
				$return[$result['rek_pid']]['isMemberOf'][] = $result['rek_varchar'];
			}
			if (($result['sek_title'] == 'Created Date' || $result['sek_title'] == 'Updated Date') && !(empty($result['rek_date']))) {
				if (!empty($result['rek_date'])) {
					$result['rek_date'] = Date_API::getFedoraFormattedDate($result['rek_date']);
				}
			}
			if  ($result['sek_title'] == 'Subject' && ((($result['xsdmf_html_input'] == "contvocab") || ($result['xsdmf_html_input'] == "contvocab_selector")) && ($result['xsdmf_cvo_save_type'] != 1))) {
				$return[$result['rek_pid']]['subject_id'][] = $result['rek_'.$result['xsdmf_data_type']];
				$result['rek_'.$result['xsdmf_data_type']] = Controlled_Vocab::getTitle($result['rek_'.$result['xsdmf_data_type']]);
			}
			if (($result['xsdmf_enabled'] == 1) && ($result['xsdmf_meta_header'] == 1) && (trim($result['xsdmf_meta_header_name']) != "")) {
				$value = "";
				$oai_name = str_replace(".", ":", strtolower($result['xsdmf_meta_header_name']));
				if ($return[$result['rek_pid']]['oai_dc'] == "") {
					$return[$result['rek_pid']]['oai_dc'] = '<dc:identifier>'.APP_BASE_URL.'view/'.$result['rek_pid'].'</dc:identifier>'."\n";
				}
				if ($result['xsdmf_data_type'] == "date") {
					if (!empty($result['rmf_date'])) {
						$value = Date_API::getFedoraFormattedDate($result['rek_'.$result['xsdmf_data_type']]);
					}
				} else {
					$value = htmlspecialchars($result['rek_'.$result['xsdmf_data_type']]);
				}
				$return[$result['rek_pid']]['oai_dc'] .=  '<'.$oai_name.'>'.$value.'</'.$oai_name.'>'."\n";
			}
			if (@$result['sek_title'] == 'isMemberOf') {
				if (!is_array(@$return[$result['rek_pid']]['isMemberOf'])) {
					$return[$result['rek_pid']]['isMemberOf'] = array();
				}
				if (!in_array($result['rek_varchar'], $return[$result['rek_pid']]['isMemberOf'])) {
					array_push($return[$result['rek_pid']]['isMemberOf'], $result['rek_varchar']);
				}
			}
			// get the document type
			if (!empty($result['xdis_title'])) {
				if (!is_array(@$return[$result['rek_pid']]['xdis_title'])) {
					$return[$result['rek_pid']]['xdis_title'] = array();
				}
				if (!in_array($result['xdis_title'],$return[$result['rek_pid']]['xdis_title'])) {
					array_push($return[$result['rek_pid']]['xdis_title'], $result['xdis_title']);
				}
			}
			if (is_numeric(@$result['sek_id'])) {
				$return[$result['rek_pid']]['pid'] = $result['rek_pid'];
				$search_var = strtolower(str_replace(" ", "_", $result['sek_title']));
				if (@!is_array($return[$result['rek_pid']][$search_var])) {
					$return[$result['rek_pid']][$search_var] = array();
				}
				if (!in_array($result['rek_'.$result['xsdmf_data_type']],
				$return[$result['rek_pid']][$search_var])) {
					array_push($return[$result['rek_pid']][$search_var],
					$result['rek_'.$result['xsdmf_data_type']]);
				}
			}
			// get thumbnails
			if ($result['xsdmf_element'] == "!datastream!ID") {
				if (is_numeric(strpos($result['rek_varchar'], "thumbnail_"))) {
					if (!is_array(@$return[$result['rek_pid']]['thumbnails'])) {
						$return[$result['rek_pid']]['thumbnails'] = array();
					}
					array_push($return[$result['rek_pid']]['thumbnails'], $result['rek_varchar']);
				} else {
					if ($result['sek_title'] == 'File Attachment Name') {
						if (!is_array(@$return[$result['rek_pid']]['File_Attachment'])) {
							$return[$result['rek_pid']]['File_Attachment'] = array();
						}
						if ((!is_numeric(strpos($result['rek_'.$result['xsdmf_data_type']], "thumbnail_"))) && (!is_numeric(strpos($result['rek_'.$result['xsdmf_data_type']], "web_"))) && (!is_numeric(strpos($result['rek_'.$result['xsdmf_data_type']], "preview_"))) && (!is_numeric(strpos($result['rek_'.$result['xsdmf_data_type']], "presmd_"))) && (!is_numeric(strpos($result['rek_'.$result['xsdmf_data_type']], "FezACML_"))) )   {
							if (!in_array($result['rek_'.$result['xsdmf_data_type']], $return[$result['rek_pid']]['File_Attachment'])) {
								array_push($return[$result['rek_pid']]['File_Attachment'], $result['rek_'.$result['xsdmf_data_type']]);
								$return[$result['rek_pid']]['oai_dc'] .=  '<dc:format>'.APP_BASE_URL.'view/'.$result['rek_pid'].'/'.$result['rek_'.$result['xsdmf_data_type']].'</dc:format>'."\n";
							}
						}
					}
					if (!is_array(@$return[$result['rek_pid']]['datastreams'])) {
						$return[$result['rek_pid']]['datastreams'] = array();
					}
					array_push($return[$result['rek_pid']]['datastreams'], $result['rek_varchar']);
				}
			}

		}
		return $return;
	}

	/**
	 * Method used to get the list of collections available in the
	 * system.
	 *
	 * @access  public
	 * @param   string $community_pid The parent community to get the collections from, if not set then all collection will be returned.
	 * @param   integer $start The point in the returned results to start from.
	 * @param   integer $max The maximum number of records to return
	 * @return  array The list of collections
	 */
	function ListSets($start = 0, $max = 100, $order_by="Created Date")
	{
		$list = Controlled_Vocab::getChildListAll($start, $max);
		foreach ($list as $lid => $lvalue) {
			$list[$lid]["cvo_title"] = htmlspecialchars($list[$lid]["cvo_title"]);
		}
		return array("list" => $list, "list_info" => array());
	}


}
