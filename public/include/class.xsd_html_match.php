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
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle the business logic related to the XSD to HTML Matching in the system
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */
include_once (APP_INC_PATH . "class.error_handler.php");
include_once (APP_INC_PATH . "class.misc.php");
include_once (APP_INC_PATH . "class.xsd_relationship.php");
include_once (APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once (APP_INC_PATH . "class.record.php");
include_once (APP_INC_PATH . "class.user.php");
include_once (APP_INC_PATH . "class.auth.php");

class XSD_HTML_Match
{
	public static $xsdmf_columns = array (
		'xsdmf_id',
		'xsdmf_xdis_id',
		'xsdmf_xsdsel_id',
		'xsdmf_element',
		'xsdmf_title',
		'xsdmf_description',
		'xsdmf_long_description',
		'xsdmf_html_input',
		'xsdmf_multiple',
		'xsdmf_multiple_limit',
		'xsdmf_valueintag',
		'xsdmf_enabled',
		'xsdmf_order',
		'xsdmf_validation_type',
		'xsdmf_validation_maxlength',
        'xsdmf_validation_regex',
        'xsdmf_validation_message',
		'xsdmf_required',
		'xsdmf_static_text',
		'xsdmf_dynamic_text',
		'xsdmf_xdis_id_ref',
		'xsdmf_id_ref',
		'xsdmf_id_ref_save_type',
		'xsdmf_is_key',
		'xsdmf_key_match',
		'xsdmf_show_in_view',
		'xsdmf_invisible',
		'xsdmf_smarty_variable',
		'xsdmf_fez_variable',
		'xsdmf_enforced_prefix',
		'xsdmf_value_prefix',
		'xsdmf_selected_option',
		'xsdmf_dynamic_selected_option',
		'xsdmf_image_location',
		'xsdmf_parent_key_match',
		'xsdmf_data_type',
		'xsdmf_indexed',
		'xsdmf_sek_id',
		'xsdmf_cvo_id',
		'xsdmf_cvo_min_level',
		'xsdmf_cvo_save_type',
		'xsdmf_original_xsdmf_id',
		'xsdmf_attached_xsdmf_id',
		'xsdmf_cso_value',
		'xsdmf_citation_browse',
		'xsdmf_citation',
		'xsdmf_citation_bold',
		'xsdmf_citation_italics',
		'xsdmf_citation_order',
		'xsdmf_citation_brackets',
		'xsdmf_citation_prefix',
		'xsdmf_citation_suffix',
		'xsdmf_use_parent_option_list',
		'xsdmf_parent_option_xdis_id',
		'xsdmf_parent_option_child_xsdmf_id',
		'xsdmf_org_level',
		'xsdmf_use_org_to_fill',
		'xsdmf_org_fill_xdis_id',
		'xsdmf_org_fill_xsdmf_id',
		'xsdmf_asuggest_xdis_id',
		'xsdmf_asuggest_xsdmf_id',
		'xsdmf_date_type',
		'xsdmf_meta_header',
		'xsdmf_meta_header_name',
		'xsdmf_show_simple_create',
		'xsdmf_xpath'
		);


		function getXPATHTails($xpath, $xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
							xsdmf_id, xsdmf_xpath
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields as m1
						 WHERE m1.xsdmf_xpath like " . $db->quote($xpath.'%'). " AND m1.xsdmf_xdis_id = " . $db->quote($xdis_id, 'INTEGER');
			$stmt .= " ORDER BY xsdmf_id ASC";

			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}

			return $res;
		}

    function getXPATHByXSDMF_ID($xsdmf_id) {
      $log = FezLog::get();
      $db = DB_API::get();

      $stmt = "SELECT
              xsdmf_xpath
                     FROM
                        " . APP_TABLE_PREFIX . "xsd_display_matchfields as m1
             WHERE m1.xsdmf_id = " . $db->quote($xsdmf_id, 'INTEGER');

      try {
        $res = $db->fetchOne($stmt);
      }
      catch(Exception $ex) {
        $log->err($ex);
        return array();
      }

      return $res;

    }


		function getXPATHBySearchKeyTitleXDIS_ID($sek_title, $xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT m2.xsdmf_xpath
				 FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields m1
				 INNER JOIN  " . APP_TABLE_PREFIX . "xsd_relationship ON xsdrel_xsdmf_id = m1.xsdmf_id
				 INNER JOIN " . APP_TABLE_PREFIX . "xsd_display ON xdis_id = m1.xsdmf_xdis_id
				 INNER JOIN " . APP_TABLE_PREFIX . "xsd_display_matchfields m2 ON m2.xsdmf_xdis_id = xsdrel_xdis_id
				 INNER JOIN " . APP_TABLE_PREFIX . "search_key ON sek_id = m2.xsdmf_sek_id
 				 WHERE sek_title = ".$db->quote($sek_title)." AND m1.xsdmf_xdis_id = ".$db->quote($xdis_id, 'INTEGER')."
				 GROUP BY m2.xsdmf_xpath ";

			try {
				$res = $db->fetchOne($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if ($res == NULL) {
				return "";
			} else {
				return $res;
			}
		}

		function getXSDMFIDBySearchKeyTitleXDIS_ID($sek_title, $xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT m2.xsdmf_id
				 FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields m1
				 INNER JOIN  " . APP_TABLE_PREFIX . "xsd_relationship ON xsdrel_xsdmf_id = m1.xsdmf_id
				 INNER JOIN " . APP_TABLE_PREFIX . "xsd_display ON xdis_id = m1.xsdmf_xdis_id
				 INNER JOIN " . APP_TABLE_PREFIX . "xsd_display_matchfields m2 ON m2.xsdmf_xdis_id = xsdrel_xdis_id
				 INNER JOIN " . APP_TABLE_PREFIX . "search_key ON sek_id = m2.xsdmf_sek_id
 				 WHERE sek_title = ".$db->quote($sek_title)." AND m1.xsdmf_xdis_id = ".$db->quote($xdis_id, 'INTEGER')."
				 GROUP BY m2.xsdmf_xpath ";
			try {
				$res = $db->fetchOne($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if ($res == NULL) {
				return "";
			} else {
				return $res;
			}
		}

		function getDetailsByXPATH($pid, $xdis_id, $exclude_list=array(), $specify_list=array(), $createdDT = null)
		{
			$log = FezLog::get();

			if (count($exclude_list) > 0) {
				$xdis_list = XSD_Relationship::getColListByXDISMinimal($xdis_id, implode("','", $exclude_list));
			} elseif (count($specify_list) > 0) {
				$xdis_list = XSD_Relationship::getColListByXDISMinimal($xdis_id, "", implode("','", $specify_list));
			} else {
				$xdis_list = XSD_Relationship::getColListByXDISMinimal($xdis_id);
			}

			//array_push($xdis_list, $xdis_id);
			$xsdmf_array = array();
			foreach ($xdis_list as $xdis_id) {

				$xdis_details = XSD_Display::getAllDetails($xdis_id);

        $dsID = $xdis_details['xsd_title'];
        if ($dsID == 'OAI DC') {
          $dsID = 'DC';
        }

				if (Fedora_API::datastreamExists($pid, $dsID)) {
					$xsdmf_details = XSD_HTML_Match::getList($xdis_id);
//          $xsdmf_details = XSD_HTML_Match::getBasicListByDisplay($xdis_id);
					//print_r($xdis_details);
					//echo $xdis_details['xsd_title'];

//					$xmlString = Fedora_API::callGetDatastreamContents($pid, $dsID, true);
          $DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $dsID, $createdDT);
          $xmlString = $DSResultArray['stream'];
					//		print_r($xmlString);
					//exit;

					$doc = DOMDocument::loadXML($xmlString);
					$xpath = new DOMXPath($doc);

					//sometimes xpath doesnt like not having 'rel' specified
					// later found out it was because the RELS-EXT xml itself wasn't setup properly - not xpath's fault (was ANU DSpace objects not sent in properly)
					//				$xpath->registerNamespace("mods", "http://www.loc.gov/mods/v3");
					//				$xpath->registerNamespace("rel", "info:fedora/fedora-system:def/relations-external#");

					foreach ($xsdmf_details as $xsdmf) {
						if ($xsdmf['xsdmf_enabled'] == 1) {

              //Workaround for authors elements that don't yet have an ID attribute for historical reasons
              $name_id = false;
              $xsdmf_xpath = $xsdmf['xsdmf_xpath'];

              if (preg_match("/\/mods:mods\/mods:name\[mods:role\/mods:roleTerm = '.*'\]\/@ID/", $xsdmf_xpath)) {
                $name_id = true;
                $xsdmf_xpath = substr($xsdmf_xpath, 0, strrpos($xsdmf_xpath, "/@ID"));

              }

							$fieldNodeList = $xpath->query($xsdmf_xpath);
							//$fieldNodeList = $xpath->query("/mods:mods/mods:subject/@ID");
							//$fieldNodeList = $xpath->query("/mods:mods/mods:subject");
							//$fieldNodeList = $xpath->query("/mods/subject");
							if($fieldNodeList->length == 0) {
								//echo "nothing found\n";
							} else {
								foreach ($fieldNodeList as $fieldNode) {
//									echo $fieldNode->nodeValue."\n";
									/*if ($fieldNodeList->length > 1) {
									 $xsdmf_array[$xsdmf['xsdmf_id']][] = $fieldNode->nodeValue;
									 } else {*/
//									$log->debug("XPATH fieldnode value: ".$fieldNode)

                  $nodeValue = $fieldNode->nodeValue;

                  if ($name_id == true) {
                    $nodeValue = '0'; //preset the ID to 0 so it at least fills the array
                    $attribs = $fieldNode->attributes;
                    foreach ($attribs as $attr_name => $attr_value) {
                      if ($attr_name == "ID") {
                        $nodeValue = $attr_value->value;
                      }
                    }
                  }

									if ($xsdmf['xsdmf_value_prefix'] != "") { //strip off stuff like info:fedora/ in rels-ext ismemberof @resource values
										$nodeValue = str_replace($xsdmf['xsdmf_value_prefix'], "", $nodeValue);
									}
									if (((!empty($nodeValue) && $nodeValue != "") || $nodeValue === '0') && $nodeValue != '-1') {
										if (isset($xsdmf_array[$xsdmf['xsdmf_id']])) {
											if (!is_array($xsdmf_array[$xsdmf['xsdmf_id']])) {
												$temp_val =	$xsdmf_array[$xsdmf['xsdmf_id']];
												$xsdmf_array[$xsdmf['xsdmf_id']] = array();
												array_push($xsdmf_array[$xsdmf['xsdmf_id']], $temp_val);
											}
											array_push($xsdmf_array[$xsdmf['xsdmf_id']], $nodeValue);
										} else {
											$xsdmf_array[$xsdmf['xsdmf_id']] = $nodeValue;
										}
									}

									//}

								}
							}
						}
					}
				}
			}
			return($xsdmf_array);

		}


		function getDisplayType($pid)
		{
			$xmlString = Fedora_API::callGetDatastreamContents($pid, "FezMD", true);
			if(!$xmlString)
				return null;

			$doc = DOMDocument::loadXML($xmlString);
			$xpath = new DOMXPath($doc);
			$fieldNodeList = $xpath->query("/FezMD/xdis_id");
			if($fieldNodeList->length == 0) {
				return null;
			} else {
				foreach ($fieldNodeList as $fieldNode) {
					return $fieldNode->nodeValue;
				}
			}
		}


  function refreshXPATH($xsdmf_id = null, $traverse = true, $update_children=false)
  {
    $xpath = "";
    $parentRefreshs = array();
    $xpath_ns_fixes = array();
    $list = array();
    if ($xsdmf_id == null) {
      $list = XSD_HTML_Match::getListAll();
    } else {
      array_push($list, XSD_HTML_Match::getAllDetailsByXSDMF_ID($xsdmf_id));
    }

    foreach ($list as $lrow) {
      $xpath = str_replace("!", "/", $lrow['xsdmf_element']);
      $xpath_ns_search = "";
      $isAttribute = XSD_HTML_Match::isAttribute($lrow['xsdmf_element'], $lrow['xsdmf_xdis_id']);
      $xpath = "/".$lrow['xsd_top_element_name'] . $xpath;

      if ($isAttribute == true) {
        $xpath = substr($xpath, 0, strrpos($xpath, "/") + 1)."@".substr($xpath, strrpos($xpath, "/") + 1);
        if (($lrow["xsdmf_enforced_prefix"]) != "") {
          $xpath = substr($xpath, 0, strrpos($xpath, "/@") + 2).$lrow["xsdmf_enforced_prefix"].substr($xpath, strrpos($xpath, "/@") + 2);
        }
      } else {
        if (($lrow["xsdmf_enforced_prefix"]) != "") {
          $xpath_ns_search = $xpath;
          $xpath = substr($xpath, 0, strrpos($xpath, "/") + 1).$lrow["xsdmf_enforced_prefix"].substr($xpath, strrpos($xpath, "/") + 1);
        }
      }

      //			if ($lrow['xsdmf_xdis_id'] == 16) echo "before xpath = ".$xpath."\n";
      if (!empty($lrow["xsd_element_prefix"])) {

        $xpath = preg_replace("/\/([^\/:@]+[^:\/])((?=\/)|(?![\/:])$)/", "/".$lrow["xsd_element_prefix"].":$1", $xpath);
        $xpath_ns_search = preg_replace("/\/([^\/:@]+[^:\/])((?=\/)|(?![\/:])$)/", "/".$lrow["xsd_element_prefix"].":$1", $xpath_ns_search);
        //$xpath = preg_replace("/\/([^\/:]+[^:\/])((?=\/)|(?![\/:]))/", "/".$lrow["xsd_element_prefix"].":$1", $xpath);
        //$xpath = preg_replace("/\/(?!.*\:.*)(?!\/.*)$/", "/".$lrow["xsd_element_prefix"].":", $xpath);
      }
      //			if ($lrow['xsdmf_xdis_id'] == 16) echo "after xpath = ".$xpath."\n\n";


      //XSD_HTML_Match::isAttribute($lrow['xsdmf_id'], $lrow['xsdmf_xdis_id']);
      if (is_numeric($lrow['xsdmf_xsdsel_id'])) {
        if ($lrow['xsdsel_type'] == "attributeloop" || $lrow['xsdsel_type'] == "hardset") {
          /*[local-name()='mods' and namespace-uri()='http://www.loc.gov/mods/v3']/*[local-name()='abstract' and namespace-uri()='http://www.loc.gov/mods/v3'][1] */
          if (is_numeric($lrow['xsdsel_indicator_xsdmf_id']) && is_numeric($lrow['xsdsel_indicator_xdis_id']) && ($lrow['xsdsel_indicator_xdis_id'] >= 1)) {
            //					echo " HERE = ".$lrow['xsdsel_indicator_xsdmf_id']."<";
            $indicator_details = XSD_HTML_Match::getAllDetailsByXSDMF_ID($lrow['xsdsel_indicator_xsdmf_id']);
            //print_r($indicator_details);
            if (is_numeric($indicator_details['xsdmf_id'])) {
              $ind_xpath = str_replace("!", "/", $indicator_details['xsdmf_element']);
              $isAttribute = XSD_HTML_Match::isAttribute($indicator_details['xsdmf_element'], $indicator_details['xsdmf_xdis_id']);
              $ind_xpath = "/".$indicator_details['xsd_top_element_name'] . $ind_xpath;



              if ($isAttribute == true) {
                $ind_xpath = substr($ind_xpath, 0, strrpos($ind_xpath, "/") + 1)."@".substr($ind_xpath, strrpos($ind_xpath, "/") + 1);
                if (!empty($indicator_details["xsdmf_enforced_prefix"])) {
                  $ind_xpath = substr($ind_xpath, 0, strrpos($ind_xpath, "/@") + 2).$indicator_details["xsdmf_enforced_prefix"].substr($ind_xpath, strrpos($ind_xpath, "/@") + 2);
                }
              } else {
                if (!empty($indicator_details["xsdmf_enforced_prefix"])) {
                  $ind_xpath = substr($ind_xpath, 0, strrpos($ind_xpath, "/") + 1).$indicator_details["xsdmf_enforced_prefix"].substr($ind_xpath, strrpos($ind_xpath, "/") + 1);
                }
              }

              if (!empty($indicator_details["xsd_element_prefix"])) {
                //$ind_xpath = preg_replace("/\/([^\/:]+[^:\/])((?=\/)|(?![\/:]))/", "/".$indicator_details["xsd_element_prefix"].":$1", $ind_xpath);
                $ind_xpath = preg_replace("/\/([^\/:@]+[^:\/])((?=\/)|(?![\/:])$)/", "/".$indicator_details["xsd_element_prefix"].":$1", $ind_xpath);
                //$xpath = preg_replace("/\/(?!.*\:.*)(?!\/.*)$/", "/".$lrow["xsd_element_prefix"].":", $xpath);
              }



              //							echo "xpath = ".$xpath."\n";
              //							echo "indicator xpath = ".$ind_xpath."\n";

              $common_xpath = Misc::strlcs($xpath, $ind_xpath);
              $after_common_xpath = substr($common_xpath, strrpos($common_xpath, "/"));
              //							echo "AFTER C X = ".substr($after_common_xpath, -1)."\n";
              if (in_array(substr($after_common_xpath, -1), array(":", "@", "/"))) {
                $common_xpath = substr($common_xpath, 0 , strrpos($common_xpath, "/"));
              }

              //							echo "common substring = ".$common_xpath."\n";
              //$xpath_pre = substr($xpath, 0, strrpos($ind_xpath, "/"));
              $xpath_post = substr($xpath, strrpos($xpath, $common_xpath)+strlen($common_xpath));
              //echo "Xpost = ".$xpath_post."\n";
              $ind_xpath = substr($ind_xpath, (strrpos($ind_xpath, $common_xpath)+strlen($common_xpath)));
              $ind_xpath = ltrim($ind_xpath, "/");
              //echo "Ipost = ".$ind_xpath."\n";
              //$ind_xpath = $ind_xpath . " = '".$indicator_details['xsdsel_indicator_value']."'";

              if ($ind_xpath == "") {
                $ind_xpath = $after_common_xpath;
              }


              if ($indicator_details['xsdsel_indicator_value'] == '') {
                if (in_array(substr($after_common_xpath, -1), array(":", "@", "/"))) {
                  $xpath = $common_xpath."[not(".$ind_xpath. ") or ".$ind_xpath. " = '".$indicator_details['xsdsel_indicator_value']."']".$xpath_post;
                } else {
                  $xpath = $common_xpath.$xpath_post."[not(".$ind_xpath. ") or ".$ind_xpath. " = '".$indicator_details['xsdsel_indicator_value']."']";
                }
              } else {
                if (in_array(substr($after_common_xpath, -1), array(":", "@", "/"))) {
                  $xpath = $common_xpath."[".$ind_xpath. " = '".$indicator_details['xsdsel_indicator_value']."']".$xpath_post;
                } else {
                  $xpath = $common_xpath.$xpath_post."[".$ind_xpath. " = '".$indicator_details['xsdsel_indicator_value']."']";
                }
              }

              //$ind_xpath_insert_pos = strrpos($ind_xpath, $xpath_pre) + strlen($xpath_pre);
              //$ind_xpath_cut = substr($ind_xpath, $ind_xpath_insert_pos + 1);

              //$xpath = $xpath_pre."[".$ind_xpath_cut."]".substr($xpath, strrpos($xpath, "/"));


              //echo " - INDY xpath = ".$ind_xpath."\n";
              //echo " - FINAL xpath = ".$xpath."\n\n\n";
            }
          }
        }
      }
      if (!empty($lrow["xsdmf_enforced_prefix"]) && $xpath_ns_search != "") {
        $xpath_ns_fixes[$xpath] = array($xpath_ns_search, $lrow['xsdmf_xdis_id']);
      }

      // Check for any parents with different namespaces so they flow onto this child and it's children
      $splitElement = $lrow['xsdmf_element'];
      $splitElement = substr($splitElement, 0, strrpos($splitElement, "!"));
      $splitCount = substr_count($splitElement, "!");
      $elements = array($splitElement);
      while ($splitCount > 1) {
        $splitElement = substr($splitElement, 0, strrpos($splitElement, "!"));
        $splitCount = substr_count($splitElement, "!");
        array_push($elements, $splitElement);
      }

      $parentRefreshs = XSD_HTML_Match::getXSDMF_IDsByElements($elements, $lrow['xsdmf_xdis_id']);
      XSD_HTML_Match::updateXPathByXSDMF_ID($lrow['xsdmf_id'], $xpath);
    }

    if ($traverse == true) {
        foreach ($parentRefreshs as $parent_xsdmf_id) {
          XSD_HTML_Match::refreshXPATH($parent_xsdmf_id, false);
        }
    }

    foreach ($xpath_ns_fixes as $xpath_replace => $xpath_search) {
      $fixes = XSD_HTML_Match::getXPATHTails($xpath_search[0], $xpath_search[1]);
      foreach ($fixes as $fix_row) {
        $xpath_replace_save = str_replace($xpath_search[0], $xpath_replace, $fix_row['xsdmf_xpath']);
        XSD_HTML_Match::updateXPathByXSDMF_ID($fix_row['xsdmf_id'], $xpath_replace_save);

      }
    }

      //Also refresh any children eg if you changed sublooping element indicators then they will all need to refresh to get the @name='x'
      if ($update_children == true) {
          $children = XSD_HTML_Match::getXPATHTails($xpath, $lrow['xsdmf_xdis_id']);
          foreach ($children as $child) {
              if ($traverse == true && $child['xsdmf_id'] != $xsdmf_id) {
                  XSD_HTML_Match::refreshXPATH($child['xsdmf_id'], false);
              }
          }
      }
  }

		function isAttribute($xsdmf_element, $xdis_id)
		{
			//echo "NUUUM = ".$xsdmf_element."- ".$xdis_id;
			$xdis_details = XSD_Display::getDetails($xdis_id);
			$xsd_id = $xdis_details['xdis_xsd_id'];
			$xsd_array = XSD_HTML_Match::getXSDTypes($xsd_id);
			if (array_key_exists($xsdmf_element, $xsd_array)) {
				if ($xsd_array[$xsdmf_element] == 'attribute') {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		function getXSDTypes($xsd_id)
		{
			static $xsd_array;
			if (!is_array($xsd_array) || count($xsd_array) > 10) {
				$xsd_array = array();
			}

			if (!empty($xsd_array[$xsd_id])) {
				return $xsd_array[$xsd_id];
			}
			$top_element_name = Doc_Type_XSD::getDetails($xsd_id);
			$top_element_name = $top_element_name['xsd_top_element_name'];
			$xsd_str = array();
			$xsd_str = Doc_Type_XSD::getXSDSource($xsd_id);
			$xsd_str = $xsd_str[0]['xsd_file'];
			$xsd = new DomDocument();
			$xsd->loadXML($xsd_str);
			$array_ptr = array();
			$temp = array();
			Misc::dom_xsd_to_flat_array($xsd, $top_element_name, $array_ptr, "", "", $xsd);
			$xsd_array[$xsd_id] = $array_ptr;
			return $array_ptr;


		}


		/**
		 * Method used to remove a group of matching field options.
		 *
		 * @access  public
		 * @param   array $fld_id The list of matching field IDs
		 * @param   array $mfo_id The list of matching field option IDs
		 * @return  boolean
		 */
		function removeOptions($fld_id, $mfo_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (!is_array($fld_id)) {
				$fld_id = array (
				$fld_id
				);
			}
			if (!is_array($mfo_id)) {
				$mfo_id = array (
				$mfo_id
				);
			}
			$stmt = "DELETE FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_mf_option
		                 WHERE
		                    mfo_id IN (" . Misc::arrayToSQLBindStr($mfo_id) . ")";
			try {
				$db->query($stmt, $mfo_id);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return false;
			}
			return true;
		}

		/**
		 * Method used to add possible options into a given matching field.
		 *
		 * @access  public
		 * @param   integer $fld_id The matching field ID
		 * @param   array $options The list of options that need to be added
		 * @return  integer 1 if the insert worked, -1 otherwise
		 */
		function addOptions($fld_id, $options)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (!is_array($options)) {
				$options = array (
				$options
				);
			}
			foreach ($options as $option) {
				$stmt = "INSERT INTO
			                        " . APP_TABLE_PREFIX . "xsd_display_mf_option
			                     (
			                        mfo_fld_id,
			                        mfo_value
			                     ) VALUES (
			                        " . $db->quote($fld_id, 'INTEGER').",
			                        " . $db->quote($option) . "
			                     )";
				try {
					$db->exec($stmt);
				}
				catch(Exception $ex) {
					$log->err($ex);
					return -1;
				}
			}
			return 1;
		}

		/**
		 * Method used to update an existing matching field option value.
		 *
		 * @access  public
		 * @param   integer $mfo_id The matching field option ID
		 * @param   string $mfo_value The matching field option value
		 * @return  boolean
		 */
		function updateOption($mfo_id, $mfo_value)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_mf_option
		                 SET
		                    mfo_value=" . $db->quote($mfo_value) . "
		                 WHERE
		                    mfo_id=" . $db->quote($mfo_id, 'INTEGER');
			try {
				$db->exec($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return false;
			}
			return true;
		}

		/**
		 * Method used to get the list of matching fields associated with
		 * a given display id.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID
		 * @return  array The list of matching fields fields
		 */
		function getListByDisplaySpecify($xdis_id, $specify_titles = array ('FezACML'))
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    xsdmf_id,
		                    xsdmf_element,
		                    xsdmf_title,
		                    xsdmf_description,
		                    xsdmf_long_description,
		                    xsdmf_html_input,
		                    xsdmf_order,
		                    xsdmf_validation_type,
		                    xsdmf_validation_maxlength,
                        xsdmf_validation_regex,
                        xsdmf_validation_message,
		                    xsdmf_enabled,
		                    xsdmf_indexed,
		                    xsdmf_show_in_view,
		                    xsdmf_invisible,
		                    xsdmf_multiple,
		                    xsdmf_multiple_limit,
							xsdmf_static_text,
							xsdmf_dynamic_text,
							xsdmf_smarty_variable,
							xsdmf_dynamic_selected_option,
							xsdmf_selected_option,
							xsdmf_fez_variable,
							xsdmf_enforced_prefix,
							xsdmf_data_type,
							xsdmf_value_prefix,
		                    xsdmf_xdis_id_ref,
		                    xsdmf_id_ref,
		                    xsdmf_xdis_id_ref,
		                    xsdmf_id_ref_save_type,
							xsdmf_attached_xsdmf_id,
							xsdmf_cvo_id,
							xsdmf_cvo_min_level,
							xsdmf_cvo_save_type,
							cvo_hide,
							xsdsel_order
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "controlled_vocab on xsdmf_cvo_id = cvo_id left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdsel_id = xsdmf_xsdsel_id)
		                 WHERE
		                   xsdmf_xdis_id=".$db->quote($xdis_id, 'INTEGER')." AND xsdmf_enabled=1";

			// @@@ CK - Added order statement to custom fields displayed in a desired order
			$stmt .= " ORDER BY xsdsel_order, xsdmf_order ASC";
			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}

			// Add any reference displays
			$specify_ids = XSD_Display::getIDs($specify_titles);
			foreach ($res as $rkey => $record) {
				if ($record['xsdmf_html_input'] == 'xsd_ref') {
					$ref = XSD_Relationship::getListByXSDMF($record['xsdmf_id']);
					if (is_array($ref)) {
						foreach ($ref as $reference) {
							if (is_array($specify_ids) && (count($specify_ids) > 0)) {
								if (in_array($reference['xsdrel_xdis_id'], $specify_ids)) {
									$ref_display = XSD_HTML_Match::getListByDisplay($reference['xsdrel_xdis_id']);
									$res = array_merge($ref_display, $res);
								}
							} else {
								$ref_display = XSD_HTML_Match::getListByDisplay($reference['xsdrel_xdis_id']);
								$res = array_merge($ref_display, $res);
							}
						}
					}
				}
				//@@@ CK - 29/4/2005 - Added multiple_array as an element so smarty could look the html input elements
				if (($record['xsdmf_multiple'] == 1) && (is_numeric($record['xsdmf_multiple_limit']))) {
					$res[$rkey]['multiple_array'] = array ();
					for ($x = 1; $x < ($record['xsdmf_multiple_limit'] + 1); $x++) {
						array_push($res[$rkey]['multiple_array'], $x);
					}
				}
			}
			if (count($res) == 0) {
				return "";
			} else {
				//				echo "About to do ".strval(count($res) * 2)." queries on line ".__LINE__."\n";
				for ($i = 0; $i < count($res); $i++) {
					$res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["xsdmf_id"]);
					$res[$i]["field_options_value_only"] = XSD_HTML_Match::getOptionsValueOnly($res[$i]["xsdmf_id"]);
				}
				return $res;
			}
		}

		/**
		 * Method used to get an associative array of the xsdmf id and xsdmf element
		 *
		 * @access  public
		 * @return  array The list of xsd html matches
		 */
		function getAssocList($xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();
      if (is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
				$stmt = "SELECT distinct xsdmf_id, IFNULL(CONCAT('(', xsdmf_id, ') (', xsdsel_title, ') ', xsdmf_element), CONCAT('(', xsdmf_id, ') ', xsdmf_element)) as xsdmf_presentation ";
			} else {
					$stmt = "SELECT xsdmf_id, IFNULL(('(' || xsdmf_id || ') (' || xsdsel_title || ') ' || xsdmf_element), ('(' || xsdmf_id || ') ' || xsdmf_element)) as xsdmf_presentation ";
			}

			$stmt .= "
						 FROM
							" . APP_TABLE_PREFIX . "xsd_display_matchfields as m1 left join
							" . APP_TABLE_PREFIX . "xsd_loop_subelement as s1 on s1.xsdsel_id = m1.xsdmf_xsdsel_id
			 			 WHERE xsdmf_xdis_id = " . $db->quote($xdis_id, 'INTEGER') . "
						 ORDER BY xsdsel_title";

			try {
				$res = $db->fetchPairs($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			return $res;
		}

		/**
	  * Method used to get the list of matching fields associated with
	  * a given display id.
	  *
	  * @access  public
	  * @param   integer $xdis_id The XSD Display ID
	  * @param   array optional $exclude_list The list of datastream IDs to exclude, takes preference over the specify list
	  * @param   array optional $specify_list The list of datastream IDs to specify
	  * @return  array The list of matching fields fields
	  */
		function getBasicListByDisplay($xdis_id, $exclude_list = array (), $specify_list = array())
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$exclude_str = implode("', '", $exclude_list);
			$specify_str = implode("', '", $specify_list);

			if (in_array("FezACML for Datastreams", $specify_list)) {
				$FezACML_xdis_id = XSD_Display::getID('FezACML for Datastreams');
				$specify_str = "FezACML";
				$xsdrelall = array();
				array_push($xsdrelall, $FezACML_xdis_id);
			} else {
				$stmt = "SELECT distinct r2.xsdrel_xdis_id FROM " . APP_TABLE_PREFIX . "xsd_relationship r2 right join
								(SELECT m3.xsdmf_id FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields as m3 WHERE m3.xsdmf_xdis_id=" . $db->quote($xdis_id, 'INTEGER') . ")
								as rels on (r2.xsdrel_xsdmf_id = (rels.xsdmf_id))";
				try {
					$xsdrelall = $db->fetchCol($stmt);
				}
				catch(Exception $ex) {
					$log->err($ex);
					$xsdrelall = array();
				}
				array_push($xsdrelall, $xdis_id);
			}


			$stmt = "SELECT
		                    distinct xsdmf_id,
		                    xsdmf_element,
		                    xsdmf_title,
		                    xsdmf_description,
		                    xsdmf_long_description,
		                    xsdmf_html_input,
		                    xsdmf_order,
                            xsdmf_validation_type,
                            xsdmf_validation_maxlength,
                            xsdmf_validation_regex,
                            xsdmf_validation_message,
		                    xsdmf_enabled,
		                    xsdmf_show_in_view,
		                    xsdmf_invisible,
		                    xsdmf_multiple,
		                    xsdmf_multiple_limit,
							xsdmf_static_text,
							xsdmf_dynamic_text,
							xsdmf_smarty_variable,
							xsdmf_dynamic_selected_option,
							xsdmf_selected_option,
							xsdmf_fez_variable,
							xsdmf_enforced_prefix,
							xsdmf_is_key,
							xsdmf_required,
							xsdmf_data_type,
							xsdmf_key_match,
							xsdmf_parent_key_match,
							xsdmf_value_prefix,
							xsdmf_static_text,
							xsdmf_xsdsel_id,
							xsdmf_image_location,
		                    xsdmf_xdis_id_ref,
		                    xsdmf_id_ref,
		                    xsdmf_id_ref_save_type,
							xsdsel_order,
							xsdsel_title,
							xsdmf_attached_xsdmf_id,
							xsdmf_cvo_id,
							xsdmf_cvo_min_level,
							xsdmf_cvo_save_type,
							xsdmf_cso_value,
							xsdmf_citation_browse,
							xsdmf_citation,
							xsdmf_citation_bold,
							xsdmf_citation_italics,
							xsdmf_citation_brackets,
							xsdmf_citation_order,
							xsdmf_citation_prefix,
							xsdmf_citation_suffix,
							xsdmf_use_parent_option_list,
							xsdmf_parent_option_xdis_id,
							xsdmf_parent_option_child_xsdmf_id,
							xsdmf_asuggest_xdis_id,
							xsdmf_asuggest_xsdmf_id,
							xsdmf_org_level,
							xsdmf_use_org_to_fill,
							xsdmf_org_fill_xdis_id,
							xsdmf_org_fill_xsdmf_id,
							xsdmf_date_type,
							xsdmf_meta_header,
							xsdmf_meta_header_name,
							xsdmf_show_simple_create,
							xsdmf_xdis_id,
							xsdmf_xpath,
							xsdmf_sek_id,
							sek_title,
							sek_suggest_function,
							sek_comment_function,
							sek_lookup_function,
							sek_meta_header,
							sek_data_type,
							sek_cardinality,
							cvo_hide
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields as m1
							left join " . APP_TABLE_PREFIX . "controlled_vocab as cv1 on cvo_id = m1.xsdmf_cvo_id
							left join " . APP_TABLE_PREFIX . "search_key as sk1 on sk1.sek_id = m1.xsdmf_sek_id ";

			if ($specify_str != "") {
				$stmt .= "
								inner join
								(SELECT d1.xdis_id FROM " . APP_TABLE_PREFIX . "xsd_display d1, " . APP_TABLE_PREFIX . "xsd as xsd WHERE xsd.xsd_id=d1.xdis_xsd_id AND xsd.xsd_title in (" . $db->quote($specify_str) . ")) as displays on (m1.xsdmf_xdis_id in (displays.xdis_id) AND displays.xdis_id in (" . Misc::sql_array_to_string_simple($xsdrelall) . "))";
			}
			elseif ($exclude_str != "") {
				$stmt .= "
								inner join
								(SELECT d1.xdis_id FROM " . APP_TABLE_PREFIX . "xsd_display d1, " . APP_TABLE_PREFIX . "xsd as xsd WHERE xsd.xsd_id=d1.xdis_xsd_id AND xsd.xsd_title not in (" . $db->quote($exclude_str) . ")) as displays on (m1.xsdmf_xdis_id in (displays.xdis_id) AND displays.xdis_id in (" . Misc::sql_array_to_string_simple($xsdrelall) . "))";
			}
			//				if ($specify_str == "") {
			//				}
			$stmt .= "
							left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement as s1 on (s1.xsdsel_id = m1.xsdmf_xsdsel_id)
						WHERE m1.xsdmf_xdis_id in (" . Misc::sql_array_to_string_simple($xsdrelall) . ")";
			// @@@ CK - Added order statement to custom fields displayed in a desired order

			$stmt .= " ORDER BY xsdmf_order, xsdsel_order ASC";

			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}

			// Add any reference displays
			foreach ($res as $rkey => $record) {
				if (($record['xsdmf_multiple'] == 1) && (is_numeric($record['xsdmf_multiple_limit']))) {
					$res[$rkey]['multiple_array'] = array();
					for ($x = 1; $x < ($record['xsdmf_multiple_limit'] + 1); $x++) {
						array_push($res[$rkey]['multiple_array'], $x);
					}
				}
				//				if ($record['xsdmf_attached_xsdmf_id'] != "")
			}

			return $res;
		}

		/**
		 * Method used to get the list of matching fields that are not sublooping elements associated with
		 * a given display id.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID
		 * @return  array The list of matching fields fields
		 */
		function getNonSELChildListByDisplay($xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
							*
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields as m1
						 WHERE ISNULL(m1.xsdmf_xsdsel_id) AND m1.xsdmf_xdis_id = " . $db->quote($xdis_id, 'INTEGER');
			$stmt .= " ORDER BY xsdmf_id ASC";
			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}
			return $res;
		}

		/**
		 * Method used to get the list of matching fields that are sublooping elements associated with
		 * a given display id and sublooping element id.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID
		 * @param   integer $xsdsel_id The sublooping element ID
		 * @return  array The list of matching fields fields
		 */
		function getSELChildListByDisplay($xdis_id, $xsdsel_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
							*
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields as m1
						 WHERE (m1.xsdmf_xsdsel_id = ".$db->quote($xsdsel_id, 'INTEGER').") AND m1.xsdmf_xdis_id = " . $db->quote($xdis_id, 'INTEGER');
			$stmt .= " ORDER BY xsdmf_id ASC";
			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}
			return $res;
		}

		/**
		 * Method used to get the list of matching fields.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID
		 * @param   array optional $exclude_list The list of datastream IDs to exclude, takes preference over the specify list
		 * @param   array optional $specify_list The list of datastream IDs to specify
		 * @return  array The list of matching fields fields
		 */
		function getListByDisplay($xdis_id, $exclude_list = array(), $specify_list = array())
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$res = XSD_HTML_Match::getBasicListByDisplay($xdis_id, $exclude_list, $specify_list);

			if (count($res) == 0) {
				return array ();
			} else {
				/**
				 * ORIGINAL FEZ CODE
				 * KJ: optimized for performance - a single MySQL query was used for each matchfield
				 * (but most matchfields don't have any options at all). This was slowing down record
				 * display in view
				 */
				/**
				 for ($i = 0; $i < count($res); $i++) {
				 $res[$i]["field_options"] = XSD_HTML_Match::getOptions($res[$i]["xsdmf_id"]);
				 $res[$i]["field_options_value_only"] = XSD_HTML_Match::getOptionsValueOnly($res[$i]["xsdmf_id"]);
				 }
				 */

				/**
				 * OPTIMIZED CODE
				 * Use a single query for all matchfields and requery for values only if there are any options.
				 * mfo_value is already selected for a possible later performance optimization
				 */
				// one query to get all matchfield options
				$ids = array();
				for ($i = 0; $i < count($res); $i++) {
					array_push($ids, $res[$i]["xsdmf_id"]);
				}
				$stmt = "SELECT mfo_fld_id, mfo_id, mfo_value ".
					"FROM ".APP_TABLE_PREFIX."xsd_display_mf_option ".
					"WHERE mfo_fld_id IN (".Misc::arrayToSQLBindStr($ids).") ORDER BY	mfo_fld_id, mfo_value ASC";

				// last parameter of getAssoc $group=true: pushes values for the same key (mfo_fld_id)
				// into an array

				try {
					$mfoResult = $db->fetchAll($stmt, $ids);
				}
				catch(Exception $ex) {
					$log->err($ex);
					return array();
				}

				// Hacky bug fix
				$_mfoResult = array();
				for($i = 0; $i < count($mfoResult); $i++) {
					$_mfoResult[$mfoResult[$i]['mfo_fld_id']][] = array($mfoResult[$i]['mfo_id'],$mfoResult[$i]['mfo_value']);
				}
				unset($mfoResult);
				$mfoResult = $_mfoResult;


				// iterate over match field list: only get value(s) if there are any options at all
				for ($i = 0; $i < count($res); $i++) {
					$res[$i]["field_options"] = array();
					$res[$i]["field_options_value_only"] = array();


                    if (array_key_exists($res[$i]['xsdmf_id'], $mfoResult)) {
                        $mfoEntries = $mfoResult[$res[$i]['xsdmf_id']];

                        // check if this field has any options
                        if (count($mfoEntries) > 0) {
                            for ($n=0; $n<count($mfoEntries); $n++) {
                                $res[$i]["field_options"][$mfoEntries[$n][0]] = $mfoEntries[$n][1];
                            }
                            // this could be further optimized, but is just called in very few cases
                            $res[$i]["field_options_value_only"] = XSD_HTML_Match::getOptionsValueOnly($res[$i]["xsdmf_id"]);

                        }
                    }
				}

				return $res;
			}
		}

		/**
		 * Method used to get the matching field option value.
		 *
		 * @access  public
		 * @param   integer $fld_id The matching field ID
		 * @param   integer $value The matching field option ID
		 * @return  string The matching field option value
		 */
		function getOptionValue($fld_id, $value)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (empty ($value)) {
				return "";
			}
			$stmt = "SELECT
		                    mfo_value
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_mf_option
		                 WHERE
		                    mfo_fld_id=".$db->quote($fld_id, 'INTEGER')." AND
		                    mfo_id=".$db->quote($value, 'INTEGER');
			try {
				$res = $db->fetchOne($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return "";
			}

			if ($res == NULL) {
				return "";
			} else {
				return $res;
			}
		}

		/**
		 * Method used to get the matching field option value by ID.
		 *
		 * @access  public
		 * @param   integer $mfo_id The custom field ID
		 * @return  string The custom field option value
		 */
		function getOptionValueByMFO_ID($mfo_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (!is_numeric($mfo_id)) {
				return "";
			}
			$stmt = "SELECT
		                    mfo_value
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_mf_option
		                 WHERE
		                    mfo_id=".$db->quote($mfo_id, 'INTEGER');
			try {
				$res = $db->fetchOne($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if ($res == NULL) {
				return "";
			} else {
				return $res;
			}
		}

		/**
		 * Method used to remove a XSD matching field.
		 *
		 * @access  public
		 * @param   integer $xsdmf_id The XSD matching field ID
		 * @return  boolean
		 */
		function remove($xsdmf_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (!is_numeric($xsdmf_id))
			{
				return -1;
			}

			$stmt = "DELETE FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER');
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return -1;
			}
			return 1;
		}

		/**
		 * Method used to remove a XSD matching fields by their XSDMF IDs.
		 *
		 * @access  public
		 * @return  boolean
		 */
		function removeByXSDMF_IDs($xsdmf_ids = array ())
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (empty ($xsdmf_ids)) {
				$xsdmf_ids = & $_POST['items'];
			}
			if (@ strlen(Misc::arrayToSQL($xsdmf_ids)) < 1) {
				return false;
			}
			foreach ($xsdmf_ids as $xsdmf_id) {
				$att_list = XSD_HTML_Match::getChildren($xsdmf_id);
				if (!empty ($att_list)) {
					$att_ids = array_keys(Misc::keyArray($att_list, 'att_id'));
					$stmt = "delete from " . APP_TABLE_PREFIX . "xsd_display_attach " .
				"where att_id in (".Misc::arrayToSQLBindStr($att_ids).")";

					try {
						$db->query($stmt,$att_ids);
					}
					catch(Exception $ex) {
						$log->err($ex);
					}
				}
				$mfo_list = XSD_HTML_Match::getOptions($xsdmf_id);
				if (is_array($mfo_list) && !empty($mfo_list)) {
					//$mfo_ids = array_keys(Misc::keyArray($mfo_list, 'mfo_id'));
					$mfo_ids = array_keys($mfo_list);
					$stmt = "delete from " . APP_TABLE_PREFIX . "xsd_display_mf_option " .
				"where mfo_id in (".Misc::arrayToSQLBindStr($mfo_ids).")";
					try {
						$db->query($stmt,$mfo_ids);
					}
					catch(Exception $ex) {
						$log->err($ex);
					}
				}
				$subs = XSD_Loop_Subelement::getSimpleListByXSDMF($xsdmf_id);
				if (!empty ($subs)) {
					$xsdsel_ids = array_keys(Misc::keyArray($subs, 'xsdsel_id'));
					$stmt = "delete from " . APP_TABLE_PREFIX . "xsd_loop_subelement " .
				"where xsdsel_id in (".Misc::arrayToSQLBindStr($xsdsel_ids).")";
					try {
						$db->query($stmt,$xsdsel_ids);
					}
					catch(Exception $ex) {
						$log->err($ex);
					}
				}
				$rels = XSD_Relationship::getSimpleListByXSDMF($xsdmf_id);
				if (!empty ($rels)) {
					$xsdrel_ids = array_keys(Misc::keyArray($rels, 'xsdrel_id'));
					$stmt = "delete from " . APP_TABLE_PREFIX . "xsd_relationship " .
				"where xsdrel_id in (".Misc::arrayToSQLBindStr($xsdrel_ids).")";
					try {
						$db->query($stmt,$xsdrel_ids);
					}
					catch(Exception $ex) {
						$log->err($ex);
					}
				}
			}

			$stmt = "DELETE FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_id in (".Misc::arrayToSQLBindStr($xsdmf_ids).")";
			try {
				$db->query($stmt, $xsdmf_ids);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return -1;
			}
			return 1;
		}

		function removeByXDIS_ID($xdis_id)
		{
			$list = XSD_HTML_Match::getList($xdis_id);
			$xsdmf_ids = array_keys(Misc::keyArray($list, 'xsdmf_id'));
			return XSD_HTML_Match::removeByXSDMF_IDs($xsdmf_ids);
		}

		/**
		 * Method used to add a new XSD matching field to the system, from form post variables.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID
		 * @param   string  $xml_element The XML element
		 * @return  integer 1 if the insert worked, -1 otherwise
		 */
		function insert($xdis_id, $xml_element)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (@ $_POST["enabled"]) {
				$enabled = 'TRUE';
			} else {
				$enabled = 'FALSE';
			}
			if (@ $_POST["multiple"]) {
				$multiple = 'TRUE';
			} else {
				$multiple = 'FALSE';
			}
			if (@ $_POST["indexed"]) {
				$indexed = 'TRUE';
			} else {
				$indexed = 'FALSE';
			}
			if (@ $_POST["required"]) {
				$required = 'TRUE';
			} else {
				$required = 'FALSE';
			}
			if (@ $_POST["show_in_view"]) {
				$show_in_view = 'TRUE';
			} else {
				$show_in_view = 'FALSE';
			}
			if (@ $_POST["invisible"]) {
				$invisible = 'TRUE';
			} else {
				$invisible = 'FALSE';
			}
			if (@ $_POST["show_simple_create"]) {
				$show_simple_create = 'TRUE';
			} else {
				$show_simple_create = 'FALSE';
			}
			if (@ $_POST["valueintag"]) {
				$valueintag = 'TRUE';
			} else {
				$valueintag = 'FALSE';
			}
			if (@ $_POST["is_key"]) {
				$is_key = 'TRUE';
			} else {
				$is_key = 'FALSE';
			}
			if (@ $_POST["xsdmf_citation"]) {
				$xsdmf_citation = 'TRUE';
			} else {
				$xsdmf_citation = 'FALSE';
			}
			if (@ $_POST["xsdmf_citation_browse"]) {
				$xsdmf_citation_browse = 'TRUE';
			} else {
				$xsdmf_citation_browse = 'FALSE';
			}

			if (@ $_POST["xsdmf_citation_bold"]) {
				$xsdmf_citation_bold = 'TRUE';
			} else {
				$xsdmf_citation_bold = 'FALSE';
			}
			if (@ $_POST["xsdmf_citation_italics"]) {
				$xsdmf_citation_italics = 'TRUE';
			} else {
				$xsdmf_citation_italics = 'FALSE';
			}
			if (@ $_POST["xsdmf_citation_brackets"]) {
				$xsdmf_citation_brackets = 'TRUE';
			} else {
				$xsdmf_citation_brackets = 'FALSE';
			}

			if (@ $_POST["xsdmf_use_parent_option_list"]) {
				$xsdmf_use_parent_option_list = 'TRUE';
			} else {
				$xsdmf_use_parent_option_list = 'FALSE';
			}
			if (@ $_POST["xsdmf_use_org_to_fill"]) {
				$xsdmf_use_org_to_fill = 'TRUE';
			} else {
				$xsdmf_use_org_to_fill = 'FALSE';
			}
			if (@ $_POST["xsdmf_meta_header"]) {
				$xsdmf_meta_header = 'TRUE';
			} else {
				$xsdmf_meta_header = 'FALSE';
			}

			$stmt = "INSERT INTO
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 (
		                    xsdmf_xdis_id,
		                    xsdmf_element,
		                    xsdmf_title,
		                    xsdmf_description,
		                    xsdmf_long_description,
		                    xsdmf_html_input,
		                    xsdmf_order,
		                    xsdmf_validation_type,
		                    xsdmf_validation_maxlength,
                            xsdmf_validation_regex,
                            xsdmf_validation_message,
		                    xsdmf_enabled,
		                    xsdmf_indexed,
		                    xsdmf_required,
		                    xsdmf_multiple,
							xsdmf_meta_header_name,
							xsdmf_meta_header,
							xsdmf_citation_browse,
							xsdmf_citation,
							xsdmf_citation_bold,
							xsdmf_citation_italics,
							xsdmf_citation_brackets,";
			if (is_numeric($_POST["xsdmf_citation_order"])) {
				$stmt .= "xsdmf_citation_order,";
			}
			if ($_POST["xsdmf_citation_prefix"] != "") {
				$stmt .= "xsdmf_citation_prefix,";
			}
			if ($_POST["xsdmf_citation_suffix"] != "") {
				$stmt .= "xsdmf_citation_suffix,";
			}

			if ($_POST["multiple_limit"] != "") {
				$stmt .= "xsdmf_multiple_limit,";
			}
			if ($_POST["xsdmf_sek_id"] != "") {
				$stmt .= "xsdmf_sek_id,";
			}
			if ($_POST["xsdmf_org_level"] != "") {
				$stmt .= "xsdmf_org_level,";
			}
			if (is_numeric($_POST["xsdmf_org_fill_xdis_id"])) {
				$stmt .= "xsdmf_org_fill_xdis_id,";
			}
			if (is_numeric($_POST["xsdmf_org_fill_xsdmf_id"])) {
				$stmt .= "xsdmf_org_fill_xsdmf_id,";
			}
			if (is_numeric($_POST["xsdmf_parent_option_xdis_id"])) {
				$stmt .= "xsdmf_parent_option_xdis_id,";
			}
			if (is_numeric($_POST["xsdmf_parent_option_child_xsdmf_id"])) {
				$stmt .= "xsdmf_parent_option_child_xsdmf_id,";
			}
			if (is_numeric($_POST["xsdmf_asuggest_xdis_id"])) {
				$stmt .= "xsdmf_asuggest_xdis_id,";
			}
			if (is_numeric($_POST["xsdmf_asuggest_xsdmf_id"])) {
				$stmt .= "xsdmf_asuggest_xsdmf_id,";
			}
			if (is_numeric($_POST["xsdmf_cvo_min_level"])) {
				$stmt .= "xsdmf_cvo_min_level,";
			}
			if (is_numeric($_POST["xsdmf_cvo_save_type"])) {
				$stmt .= "xsdmf_cvo_save_type,";
			}

			$stmt .= "
							xsdmf_use_org_to_fill,
							xsdmf_use_parent_option_list,
		                    xsdmf_valueintag,
		                    xsdmf_is_key,
		                    xsdmf_data_type,
		                    xsdmf_parent_key_match,
		                    xsdmf_key_match,";
			if ($_POST["xsdmf_xdis_id_ref"] != "") {
				$stmt .= "xsdmf_xdis_id_ref,";
			}
			if ($_POST["xsdmf_id_ref"] != "") {
				$stmt .= "xsdmf_id_ref,";
			}
			if ($_POST["xsdmf_id_ref_save_type"] != "") {
				$stmt .= "xsdmf_id_ref_save_type,";
			}

			if ($_POST["smarty_variable"] != "") {
				$stmt .= "xsdmf_smarty_variable,";
			}
			if ($_POST["fez_variable"] != "") {
				$stmt .= "xsdmf_fez_variable,";
			}
			if ($_POST["dynamic_selected_option"] != "") {
				$stmt .= "xsdmf_dynamic_selected_option,";
			}
			if ($_POST["selected_option"] != "") {
				$stmt .= "xsdmf_selected_option,";
			}

			$stmt .= "xsdmf_cso_value,";

			$stmt .= "xsdmf_show_in_view,
							xsdmf_invisible,
							xsdmf_show_simple_create,
							xsdmf_enforced_prefix,
							xsdmf_value_prefix,
							xsdmf_image_location,
							xsdmf_static_text,
							xsdmf_dynamic_text,
							xsdmf_date_type,
							xsdmf_cvo_id";
			if (is_numeric($_POST["attached_xsdmf_id"])) {
				$stmt .= ", xsdmf_attached_xsdmf_id";
			}
			if (is_numeric($_POST["xsdsel_id"])) {
				$stmt .= ", xsdmf_xsdsel_id";
			}
			$stmt .= "
		                 ) VALUES (
		                    ".$db->quote($xdis_id, 'INTEGER').",
		                    ".$db->quote($xml_element).",
		                    " . $db->quote($_POST["title"]) . ",
		                    " . $db->quote($_POST["description"]) . ",
		                    " . $db->quote($_POST["long_description"]) . ",
		                    " . $db->quote($_POST["field_type"]) . ",
		                    " . $db->quote($_POST["order"]) . ",
		                    " . $db->quote($_POST["validation_types"]) . ",
		                    " . $db->quote($_POST["validation_maxlength"], 'INTEGER') . ",
		                    " . $db->quote($_POST["validation_regex"]) . ",
		                    " . $db->quote($_POST["validation_message"]) . ",
		                    " . $enabled . ",
		                    " . $indexed . ",
		                    " . $required . ",
		                    " . $multiple . ",
		                    " . $db->quote($_POST["xsdmf_meta_header_name"]) . ",
		                    " . $xsdmf_meta_header . ",
		                    " . $xsdmf_citation_browse . ",
		                    " . $xsdmf_citation . ",
		                    " . $xsdmf_citation_bold . ",
		                    " . $xsdmf_citation_italics . ",
		                    " . $xsdmf_citation_brackets . ", ";
			if (is_numeric($_POST["xsdmf_citation_order"])) {
				$stmt .= $db->quote($_POST["xsdmf_citation_order"], 'INTEGER') . ", ";
			}
			if ($_POST["xsdmf_citation_prefix"]) {
				$stmt .= $db->quote($_POST["xsdmf_citation_prefix"]) . ", ";
			}
			if ($_POST["xsdmf_citation_suffix"]) {
				$stmt .= $db->quote($_POST["xsdmf_citation_suffix"]) . ", ";
			}
			if ($_POST["multiple_limit"] != "") {
				$stmt .= $db->quote($_POST["multiple_limit"], 'INTEGER') . ",";
			}
			if ($_POST["xsdmf_sek_id"] != "") {
				$stmt .= $db->quote($_POST["xsdmf_sek_id"]) . ",";
			}
			if ($_POST["xsdmf_org_level"] != "") {
				$stmt .= "'" . $db->quote($_POST["xsdmf_org_level"]) . "',";
			}
			if (is_numeric($_POST["xsdmf_org_fill_xdis_id"])) {
				$stmt .= $db->quote($_POST["xsdmf_org_fill_xdis_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_org_fill_xsdmf_id"])) {
				$stmt .= $db->quote($_POST["xsdmf_org_fill_xsdmf_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_parent_option_xdis_id"])) {
				$stmt .= $db->quote($_POST["xsdmf_parent_option_xdis_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_parent_option_child_xsdmf_id"])) {
				$stmt .= $db->quote($_POST["xsdmf_parent_option_child_xsdmf_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_asuggest_xdis_id"])) {
				$stmt .= $db->quote($_POST["xsdmf_asuggest_xdis_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_asuggest_xsdmf_id"])) {
				$stmt .= $db->quote($_POST["xsdmf_asuggest_xsdmf_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_cvo_min_level"])) {
				$stmt .= $db->quote($_POST["xsdmf_cvo_min_level"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_cvo_save_type"])) {
				$stmt .= $db->quote($_POST["xsdmf_cvo_save_type"]) . ",";
			}
			$stmt .= 			$xsdmf_use_org_to_fill . ",
							" . $xsdmf_use_parent_option_list . ",
		                    " . $valueintag . ",
		                    " . $is_key . ",
		                    " . $db->quote($_POST["xsdmf_data_type"]) . ",
		                    " . $db->quote($_POST["parent_key_match"]) . ",
		                    " . $db->quote($_POST["key_match"]) . ",";

			if ($_POST["xsdmf_xdis_id_ref"] != "") {
				$stmt .= $db->quote($_POST["xsdmf_xdis_id_ref"], 'INTEGER') . ",";
			}
			if ($_POST["xsdmf_id_ref"] != "") {
				$stmt .= $db->quote($_POST["xsdmf_id_ref"], 'INTEGER') . ",";
			}
			if ($_POST["xsdmf_id_ref_save_type"] != "") {
				$stmt .= $db->quote($_POST["xsdmf_id_ref_save_type"]) . ",";
			}
			if ($_POST["smarty_variable"] != "") {
				$stmt .= $db->quote($_POST["smarty_variable"]) . ",";
			}
			if ($_POST["fez_variable"] != "") {
				$stmt .= $db->quote($_POST["fez_variable"]) . ",";
			}
			if ($_POST["dynamic_selected_option"] != "") {
				$stmt .= $db->quote($_POST["dynamic_selected_option"]) . ",";
			}
			if ($_POST["selected_option"] != "") {
				$stmt .= $db->quote($_POST["selected_option"]) . ",";
			}

			$stmt .= $db->quote($_POST["checkbox_selected_option"]) . ",";

			$stmt .= 			$show_in_view . ", " . $invisible . ", " . $show_simple_create . ",
		                    " . $db->quote($_POST["enforced_prefix"]) . ",
		                    " . $db->quote($_POST["value_prefix"]) . ",
		                    " . $db->quote($_POST["image_location"]) . ",
		                    " . $db->quote($_POST["static_text"]) . ",
		                    " . $db->quote($_POST["dynamic_text"]) . ",
		                    " . $db->quote($_POST["xsdmf_date_type"]) . ",
		                    " . $db->quote($_POST["xsdmf_cvo_id"], 'INTEGER');

			if (is_numeric($_POST["attached_xsdmf_id"])) {
				$stmt .= ", " . $db->quote($_POST["attached_xsdmf_id"], 'INTEGER');
			}

			if (is_numeric($_POST["xsdsel_id"])) {
				$stmt .= ", " . $db->quote($_POST["xsdsel_id"], 'INTEGER');
			}
			$stmt .= ")";

			try {
				$db->exec($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return -1;
			}

			$new_id = $db->lastInsertId(APP_TABLE_PREFIX . "xsd_display_matchfields", "xsdmf_id");
			XSD_HTML_Match::refreshXPATH($new_id);
			if (($_POST["field_type"] == 'combo') || ($_POST["field_type"] == 'multiple')) {
				foreach ($_POST["field_options"] as $option_value) {
					$params = XSD_HTML_Match::parseParameters($option_value);
					XSD_HTML_Match::addOptions($new_id, $params["value"]);
				}
			}
		}

		/**
		 * Method used to add a new XSD matching field to the system, from an array.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID
		 * @param   array  $insertArray The array of values to be entered.
		 * @return  integer 1 if the insert worked, -1 otherwise
		 */
		function insertFromArray($xdis_id, $insertArray)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$insertArray['xsdmf_xdis_id'] = $xdis_id;
			$stmt = "INSERT INTO
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 ( ";
			foreach (XSD_HTML_Match::$xsdmf_columns as $col_name) {
				if ($insertArray[$col_name] != '') {
					$stmt .= "  $col_name,\n";
				}
			}
			$stmt = rtrim($stmt,", \n"); // get rid of trailing comma
			$stmt .= " ) VALUES ( ";
			foreach (XSD_HTML_Match::$xsdmf_columns as $col_name) {
				if ($insertArray[$col_name] != '') {
					$value = $insertArray[$col_name];
					$stmt .= "  ".$db->quote($value).",\n";
				}
			}
			$stmt = rtrim($stmt,", \n"); // get rid of trailing comma
			$stmt .= " )";
			try {
				$db->exec($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return -1;
			}

			$xsdmf_id = $db->lastInsertId(APP_TABLE_PREFIX . "xsd_display_matchfields", "xsdmf_id");

			XSD_HTML_Match::refreshXPATH($xsdmf_id);
			return $xsdmf_id;
		}

		/**
		 * Method used to add a new XSD matching field to the system for a specific XSD sublooping element, from an array.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID
		 * @param   string  $xsdsel_id The XSD sublooping element
		 * @param   array  $insertArray The array of values to be entered.
		 * @return  integer 1 if the insert worked, -1 otherwise
		 */
		function insertFromArraySEL($xdis_id, $xsdsel_id, $insertArray)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$inserts = "xsdmf_xdis_id,
					xsdmf_element,
					xsdmf_title,
					xsdmf_description,
					xsdmf_html_input,
					xsdmf_validation_type,
					xsdmf_validation_maxlength,
                   xsdmf_validation_regex,
                   xsdmf_validation_message,
					xsdmf_parent_key_match,
					xsdmf_key_match,
					";
			$values = $xdis_id . ",
					" . $db->quote($insertArray["xsdmf_element"]) . ",
					" . $db->quote($insertArray["xsdmf_title"]) . ",
					" . $db->quote($insertArray["xsdmf_description"]) . ",
					" . $db->quote($insertArray["xsdmf_html_input"]) . ",
					" . $db->quote($insertArray["xsdmf_validation_type"]) . ",
					" . $db->quote($insertArray["xsdmf_validation_maxlength"]) . ",
					" . $db->quote($insertArray["xsdmf_validation_regex"]) . ",
					" . $db->quote($insertArray["xsdmf_validation_message"]) . ",
                    " . $db->quote($insertArray["xsdmf_parent_key_match"]) . ",
                    " . $db->quote($insertArray["xsdmf_key_match"]) . ",
					";

			if (!empty ($insertArray["xsdmf_order"])) {
				$inserts .= " xsdmf_order, ";
				$values  .= $db->quote($insertArray["xsdmf_order"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_enabled"])) {
				$inserts .= " xsdmf_enabled, ";
				$values  .= $db->quote($insertArray["xsdmf_enabled"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_indexed"])) {
				$inserts .= " xsdmf_indexed, ";
				$values  .= $db->quote($insertArray["xsdmf_indexed"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_required"])) {
				$inserts .= " xsdmf_required, ";
				$values  .= $db->quote($insertArray["xsdmf_required"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_multiple"])) {
				$inserts .= " xsdmf_multiple, ";
				$values  .= $db->quote($insertArray["xsdmf_multiple"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_multiple_limit"])) {
				$inserts .= " xsdmf_multiple_limit, ";
				$values  .= $db->quote($insertArray["xsdmf_multiple_limit"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_sek_id"])) {
				$inserts .= " xsdmf_sek_id, ";
				$values  .= $db->quote($insertArray["xsdmf_sek_id"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_valueintag"])) {
				$inserts .= " xsdmf_valueintag, ";
				$values  .= $db->quote($insertArray["xsdmf_valueintag"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_is_key"])) {
				$inserts .= " xsdmf_is_key, ";
				$values  .= $db->quote($insertArray["xsdmf_is_key"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_meta_header_name"])) {
				$inserts .= " xsdmf_meta_header_name, ";
				$values  .= $db->quote($insertArray["xsdmf_meta_header_name"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_meta_header"])) {
				$inserts .= " xsdmf_meta_header, ";
				$values  .= $db->quote($insertArray["xsdmf_meta_header"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_citation_browse"])) {
				$inserts .= " xsdmf_citation_browse, ";
				$values  .= $db->quote($insertArray["xsdmf_citation_browse"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_citation"])) {
				$inserts .= " xsdmf_citation, ";
				$values  .= $db->quote($insertArray["xsdmf_citation"]). ", ";
			}
			if (!empty ($insertArray["xsdmf_citation_bold"])) {
				$inserts .= " xsdmf_citation_bold, ";
				$values  .= $db->quote($insertArray["xsdmf_citation_bold"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_citation_italics"])) {
				$inserts .= " xsdmf_citation_italics, ";
				$values  .= $db->quote($insertArray["xsdmf_citation_italics"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_citation_brackets"])) {
				$inserts .= " xsdmf_citation_brackets, ";
				$values  .= $db->quote($insertArray["xsdmf_citation_brackets"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_citation_order"])) {
				$inserts .= " xsdmf_citation_order, ";
				$values  .= $db->quote($insertArray["xsdmf_citation_order"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_citation_prefix"])) {
				$inserts .= " xsdmf_citation_prefix, ";
				$values  .= $db->quote($insertArray["xsdmf_citation_prefix"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_citation_suffix"])) {
				$inserts .= " xsdmf_citation_suffix, ";
				$values  .= $db->quote($insertArray["xsdmf_citation_suffix"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_xdis_id_ref"])) {
				$inserts .= " xsdmf_xdis_id_ref, ";
				$values  .= $db->quote($insertArray["xsdmf_xdis_id_ref"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_id_ref"])) {
				$inserts .= " xsdmf_id_ref, ";
				$values  .= $db->quote($insertArray["xsdmf_id_ref"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_id_ref_save_type"])) {
				$inserts .= " xsdmf_id_ref_save_type, ";
				$values  .= $db->quote($insertArray["xsdmf_id_ref_save_type"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_smarty_variable"])) {
				$inserts .= " xsdmf_smarty_variable, ";
				$values  .= $db->quote($insertArray["xsdmf_smarty_variable"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_fez_variable"])) {
				$inserts .= " xsdmf_fez_variable, ";
				$values  .= $db->quote($insertArray["xsdmf_fez_variable"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_dynamic_selected_option"])) {
				$inserts .= " xsdmf_dynamic_selected_option, ";
				$values  .= $db->quote($insertArray["xsdmf_dynamic_selected_option"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_selected_option"])) {
				$inserts .= " xsdmf_selected_option, ";
				$values  .= $db->quote($insertArray["xsdmf_selected_option"]) . ", ";
			}
			if (is_numeric($insertArray["xsdmf_show_in_view"])) {
				$inserts .= " xsdmf_show_in_view, ";
				$values  .= $db->quote($insertArray["xsdmf_show_in_view"]) . ", ";
			}
			if (is_numeric($insertArray["xsdmf_invisible"])) {
				$inserts .= " xsdmf_invisible, ";
				$values  .= $db->quote($insertArray["xsdmf_invisible"]) . ", ";
			}
			if (is_numeric($insertArray["xsdmf_show_simple_create"])) {
				$inserts .= " xsdmf_show_simple_create, ";
				$values  .= $db->quote($insertArray["xsdmf_show_simple_create"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_use_parent_option_list"])) {
				$inserts .= " xsdmf_use_parent_option_list, ";
				$values  .= $db->quote($insertArray["xsdmf_use_parent_option_list"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_parent_option_xdis_id"])) {
				$inserts .= " xsdmf_parent_option_xdis_id, ";
				$values  .= $db->quote($insertArray["xsdmf_parent_option_xdis_id"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_parent_option_xsdmf_id"])) {
				$inserts .= " xsdmf_parent_option_xsdmf_id, ";
				$values  .= $db->quote($insertArray["xsdmf_parent_option_xsdmf_id"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_asuggest_xdis_id"])) {
				$inserts .= " xsdmf_asuggest_xdis_id, ";
				$values  .= $db->quote($insertArray["xsdmf_asuggest_xdis_id"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_asuggest_xsdmf_id"])) {
				$inserts .= " xsdmf_asuggest_xsdmf_id, ";
				$values  .= $db->quote($insertArray["xsdmf_asuggest_xsdmf_id"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_cvo_save_type"])) {
				$inserts .= " xsdmf_cvo_save_type, ";
				$values  .= $db->quote($insertArray["xsdmf_cvo_save_type"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_cvo_min_level"])) {
				$inserts .= " xsdmf_cvo_min_level, ";
				$values  .= $db->quote($insertArray["xsdmf_cvo_min_level"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_cvo_id"])) {
				$inserts .= " xsdmf_cvo_id, ";
				$values  .= $db->quote($insertArray["xsdmf_cvo_id"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_org_level"])) {
				$inserts .= " xsdmf_org_level, ";
				$values  .= $db->quote($insertArray["xsdmf_org_level"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_use_org_to_fill"])) {
				$inserts .= " xsdmf_use_org_to_fill, ";
				$values  .= $db->quote($insertArray["xsdmf_use_org_to_fill"]) . ", ";
			}
			if (!empty ($insertArray["xsdmf_org_fill_xdis_id"])) {
				$inserts .= " xsdmf_org_fill_xdis_id, ";
				$values  .= $db->quote($insertArray["xsdmf_org_fill_xdis_id"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_org_fill_xsdmf_id"])) {
				$inserts .= " xsdmf_org_fill_xsdmf_id, ";
				$values  .= $db->quote($insertArray["xsdmf_org_fill_xsdmf_id"], 'INTEGER') . ", ";
			}
			if (!empty ($insertArray["xsdmf_attached_xsdmf_id"])) {
				$inserts .= " xsdmf_attached_xsdmf_id, ";
				$values  .= $db->quote($insertArray["xsdmf_attached_xsdmf_id"], 'INTEGER') . ", ";
			}
			if (is_numeric($xsdsel_id)) {
				$inserts .= " xsdmf_xsdsel_id, ";
				$values  .= $db->quote($xsdsel_id, 'INTEGER') . ", ";

			}
			$inserts .= "xsdmf_enforced_prefix,
					xsdmf_value_prefix,
					xsdmf_image_location,
					xsdmf_static_text,
					xsdmf_dynamic_text,
					xsdmf_original_xsdmf_id";
			$values .= $db->quote($insertArray["xsdmf_enforced_prefix"]) . ",
		            " . $db->quote($insertArray["xsdmf_value_prefix"]) . ",
		            " . $db->quote($insertArray["xsdmf_image_location"]) . ",
		            " . $db->quote($insertArray["xsdmf_static_text"]) . ",
		            " . $db->quote($insertArray["xsdmf_dynamic_text"]) . ",
					" . $db->quote($insertArray["xsdmf_id"], 'INTEGER');

			$stmt = "INSERT INTO " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 (" . $inserts . ") VALUES (" . $values . ")";

			try {
				$db->exec($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return -1;
			}

			$xsdmf_id = $db->lastInsertId(APP_TABLE_PREFIX . "xsd_display_matchfields", "xsdmf_id");
			XSD_HTML_Match::refreshXPATH($xsdmf_id);
			return $xsdmf_id;
		}

		/**
		 * Method used to update a custom field in the system.
		 *
		 * @access  public
		 * @return  integer 1 if the insert worked, -1 otherwise
		 */
		//	function update($xdis_id, $xml_element)
		function update($xsdmf_id, $update_children = false)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (!is_numeric($xsdmf_id)) {
				return false;
			}

			if (@ $_POST["enabled"]) {
				$enabled = 'TRUE';
			} else {
				$enabled = 'FALSE';
			}

			if (@ $_POST["multiple"]) {
				$multiple = 'TRUE';
			} else {
				$multiple = 'FALSE';
			}
			if (@ $_POST["required"]) {
				$required = 'TRUE';
			} else {
				$required = 'FALSE';
			}
			if (@ $_POST["indexed"]) {
				$indexed = 'TRUE';
			} else {
				$indexed = 'FALSE';
			}

			if (@ $_POST["valueintag"]) {
				$valueintag = 'TRUE';
			} else {
				$valueintag = 'FALSE';
			}

			if (@ $_POST["show_in_view"]) {
				$show_in_view = 'TRUE';
			} else {
				$show_in_view = 'FALSE';
			}
			if (@ $_POST["invisible"]) {
				$invisible = 'TRUE';
			} else {
				$invisible = 'FALSE';
			}
			if (@ $_POST["show_simple_create"]) {
				$show_simple_create = 'TRUE';
			} else {
				$show_simple_create = 'FALSE';
			}
			if (@ $_POST["is_key"]) {
				$is_key = 'TRUE';
			} else {
				$is_key = 'FALSE';
			}
			if (@ $_POST["xsdmf_meta_header"]) {
				$xsdmf_meta_header = 'TRUE';
			} else {
				$xsdmf_meta_header = 'FALSE';
			}

			if (@ $_POST["xsdmf_citation_browse"]) {
				$xsdmf_citation_browse = 'TRUE';
			} else {
				$xsdmf_citation_browse = 'FALSE';
			}
			if (@ $_POST["xsdmf_citation"]) {
				$xsdmf_citation = 'TRUE';
			} else {
				$xsdmf_citation = 'FALSE';
			}
			if (@ $_POST["xsdmf_citation_bold"]) {
				$xsdmf_citation_bold = 'TRUE';
			} else {
				$xsdmf_citation_bold = 'FALSE';
			}
			if (@ $_POST["xsdmf_citation_italics"]) {
				$xsdmf_citation_italics = 'TRUE';
			} else {
				$xsdmf_citation_italics = 'FALSE';
			}
			if (@ $_POST["xsdmf_citation_brackets"]) {
				$xsdmf_citation_brackets = 'TRUE';
			} else {
				$xsdmf_citation_brackets = 'FALSE';
			}
			if (@ $_POST["xsdmf_use_parent_option_list"]) {
				$xsdmf_use_parent_option_list = 'TRUE';
			} else {
				$xsdmf_use_parent_option_list = 'FALSE';
			}
			if (@ $_POST["xsdmf_use_org_to_fill"]) {
				$xsdmf_use_org_to_fill = 'TRUE';
			} else {
				$xsdmf_use_org_to_fill = 'FALSE';
			}
			/*		if (is_numeric($_POST["xsdsel_id"])) {
			 $extra_where = " AND xsdmf_xsdsel_id = " . $_POST["xsdsel_id"];
			 } else {
			 $extra_where = " AND xsdmf_xsdsel_id IS NULL";
			 }
			 */
			$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET
		                    xsdmf_title = " . $db->quote($_POST["title"]) . ",
		                    xsdmf_description = " . $db->quote($_POST["description"]) . ",
		                    xsdmf_long_description = " . $db->quote($_POST["long_description"]) . ",
		                    xsdmf_html_input = " . $db->quote($_POST["field_type"]) . ",
		                    xsdmf_validation_type = " . $db->quote($_POST["validation_types"]) . ",
		                    xsdmf_validation_maxlength = " . $db->quote($_POST["validation_maxlength"], 'INTEGER') . ",
		                    xsdmf_validation_regex = " . $db->quote($_POST["validation_regex"]) . ",
		                    xsdmf_validation_message = " . $db->quote($_POST["validation_message"]) . ",
		                    xsdmf_order = " . $db->quote($_POST["order"], 'INTEGER') . ",
		                    xsdmf_date_type = " . $db->quote($_POST["xsdmf_date_type"], 'INTEGER') . ",
		                    xsdmf_cvo_id = " . $db->quote($_POST["xsdmf_cvo_id"], 'INTEGER') . ",
		                    xsdmf_use_org_to_fill = " . $xsdmf_use_org_to_fill . ",
		                    xsdmf_use_parent_option_list = " . $xsdmf_use_parent_option_list . ",
		                    xsdmf_required = " . $required . ",
		                    xsdmf_indexed = " . $indexed . ",
		                    xsdmf_enabled = " . $enabled . ",
		                    xsdmf_meta_header_name = " . $db->quote($_POST["xsdmf_meta_header_name"]) . ",
		                    xsdmf_meta_header = " . $xsdmf_meta_header . ",
		                    xsdmf_citation_browse = " . $xsdmf_citation_browse . ",
		                    xsdmf_citation = " . $xsdmf_citation . ",
		                    xsdmf_citation_bold = " . $xsdmf_citation_bold . ",
		                    xsdmf_citation_italics = " . $xsdmf_citation_italics . ",
		                    xsdmf_citation_brackets = " . $xsdmf_citation_brackets . ",
		                    xsdmf_citation_prefix = " . $db->quote($_POST["xsdmf_citation_prefix"]) . ",
		                    xsdmf_citation_suffix = " . $db->quote($_POST["xsdmf_citation_suffix"]) . ",
		                    xsdmf_multiple = " . $multiple . ",";
			if ($_POST["multiple_limit"] != "") {
				$stmt .= " xsdmf_multiple_limit = " . $db->quote($_POST["multiple_limit"], 'INTEGER') . ",";
			}
			if (!empty($_POST["xsdmf_sek_id"])) {
				$stmt .= " xsdmf_sek_id = " . $db->quote($_POST["xsdmf_sek_id"]) . ",";
			} else {
				$stmt .= " xsdmf_sek_id = NULL,";
			}
			if ($_POST["xsdmf_org_level"] != "") {
				$stmt .= " xsdmf_org_level = " . $db->quote($_POST["xsdmf_org_level"]) . ",";
			}
			if (is_numeric($_POST["xsdmf_org_fill_xdis_id"])) {
				$stmt .= " xsdmf_org_fill_xdis_id = " . $db->quote($_POST["xsdmf_org_fill_xdis_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_org_fill_xsdmf_id"])) {
				$stmt .= " xsdmf_org_fill_xsdmf_id = " . $db->quote($_POST["xsdmf_org_fill_xsdmf_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_parent_option_xdis_id"])) {
				$stmt .= " xsdmf_parent_option_xdis_id = " . $db->quote($_POST["xsdmf_parent_option_xdis_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_parent_option_child_xsdmf_id"])) {
				$stmt .= " xsdmf_parent_option_child_xsdmf_id = " . $db->quote($_POST["xsdmf_parent_option_child_xsdmf_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_asuggest_xdis_id"])) {
				$stmt .= " xsdmf_asuggest_xdis_id = " . $db->quote($_POST["xsdmf_asuggest_xdis_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_asuggest_xsdmf_id"])) {
				$stmt .= " xsdmf_asuggest_xsdmf_id = " . $db->quote($_POST["xsdmf_asuggest_xsdmf_id"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_cvo_min_level"])) {
				$stmt .= " xsdmf_cvo_min_level = " . $db->quote($_POST["xsdmf_cvo_min_level"], 'INTEGER') . ",";
			}
			if (is_numeric($_POST["xsdmf_cvo_save_type"])) {
				$stmt .= " xsdmf_cvo_save_type = " . $db->quote($_POST["xsdmf_cvo_save_type"]) . ",";
			}
			if (is_numeric($_POST["xsdmf_citation_order"])) {
				$stmt .= " xsdmf_citation_order = " . $db->quote($_POST["xsdmf_citation_order"], 'INTEGER') . ",";
			}
			if ($_POST["smarty_variable"] != "") {
				$stmt .= " xsdmf_smarty_variable = " . $db->quote($_POST["smarty_variable"]) . ",";
			}
			if ($_POST["fez_variable"] != "") {
				$stmt .= " xsdmf_fez_variable = " . $db->quote($_POST["fez_variable"]) . ",";
			}
			if ($_POST["dynamic_selected_option"] != "") {
				$stmt .= " xsdmf_dynamic_selected_option = " . $db->quote($_POST["dynamic_selected_option"]) . ",";
			}
			if (!empty ($_POST["selected_option"])) {
				$stmt .= " xsdmf_selected_option = " . $db->quote($_POST["selected_option"]) . ",";
			}
			$stmt .= "
		                    xsdmf_valueintag = " . $valueintag . ",
		                    xsdmf_is_key = " . $is_key . ",
		                    xsdmf_show_in_view = " . $show_in_view . ",
		                    xsdmf_invisible = " . $invisible . ",
		                    xsdmf_show_simple_create = " . $show_simple_create . ",
		                    xsdmf_key_match = " . $db->quote($_POST["key_match"]) . ",
		                    xsdmf_parent_key_match = " . $db->quote($_POST["parent_key_match"]) . ",
		                    xsdmf_data_type = " . $db->quote($_POST["xsdmf_data_type"]) . ",";
			if (is_numeric($_POST["attached_xsdmf_id"])) {
				$stmt .= "   xsdmf_attached_xsdmf_id = " . $db->quote($_POST["attached_xsdmf_id"], 'INTEGER') . ",";
			}
			elseif (trim($_POST["attached_xsdmf_id"]) == "") {
				$stmt .= "   xsdmf_attached_xsdmf_id = NULL,";
			}
			if (is_numeric($_POST["xsdmf_xdis_id_ref"])) {
				$stmt .= " xsdmf_xdis_id_ref = " . $db->quote($_POST["xsdmf_xdis_id_ref"], 'INTEGER') . ",";
			}
			elseif (trim($_POST["xsdmf_xdis_id_ref"]) == "") {
				$stmt .= "   xsdmf_xdis_id_ref = NULL,";
			}
			if (is_numeric($_POST["xsdmf_id_ref"])) {
				$stmt .= " xsdmf_id_ref = " . $db->quote($_POST["xsdmf_id_ref"], 'INTEGER') . ",";
			}
			elseif (trim($_POST["xsdmf_id_ref"]) == "") {
				$stmt .= "   xsdmf_id_ref = NULL,";
			}

			if (is_numeric($_POST["xsdmf_id_ref_save_type"])) {
				$stmt .= " xsdmf_id_ref_save_type = " . $db->quote($_POST["xsdmf_id_ref_save_type"]) . ",";
			}
			elseif (trim($_POST["xsdmf_id_ref_save_type"]) == "") {
				$stmt .= "   xsdmf_id_ref_save_type = NULL,";
			}

			$stmt .= "
		                    xsdmf_enforced_prefix = " . $db->quote($_POST["enforced_prefix"]) . ",
		                    xsdmf_value_prefix = " . $db->quote($_POST["value_prefix"]) . ",
		                    xsdmf_image_location = " . $db->quote($_POST["image_location"]) . ",
		                    xsdmf_dynamic_text = " . $db->quote($_POST["dynamic_text"]) . ",
		                    xsdmf_static_text = " . $db->quote($_POST["static_text"]);
			//		$stmt .= " WHERE xsdmf_xdis_id = $xdis_id AND xsdmf_element = '" . $xml_element . "'" . $extra_where;
			$stmt .= " WHERE xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER');
			try {
				$db->exec($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return -1;
			}
			XSD_HTML_Match::refreshXPATH($_POST['xsdmf_id'], true, $update_children);
			// update the custom field options, if any
			if (($_POST["field_type"] == "combo") || ($_POST["field_type"] == "multiple")) {
				$stmt = "SELECT mfo_id
                         FROM
                            " . APP_TABLE_PREFIX . "xsd_display_mf_option
                         WHERE
                            mfo_fld_id=" . $db->quote($_POST['xsdmf_id'], 'INTEGER');
				try {
					$current_options = $db->fetchCol($stmt);
				}
				catch(Exception $ex) {
					$log->err($ex);
				}
				$updated_options = array ();

				foreach ($_POST["field_options"] as $option_value) {
					$params = XSD_HTML_Match::parseParameters($option_value);
					if ($params["type"] == 'new') {
						XSD_HTML_Match::addOptions($_POST["xsdmf_id"], $params["value"]);
					} else {
						$updated_options[] = $params["id"];
						// check if the user is trying to update the value of this option
						if ($params["value"] != XSD_HTML_Match::getOptionValue($_POST["xsdmf_id"], $params["id"])) {
							XSD_HTML_Match::updateOption($params["id"], $params["value"]);
						}
					}
				}
			}

			// get the diff between the current options and the ones posted by the form
			// and then remove the options not found in the form submissions
			if (in_array($_POST["field_type"], array (
				'combo',
				'multiple'
				))) {
					$params = XSD_HTML_Match::parseParameters($option_value);
					$diff_ids = @ array_diff($current_options, $updated_options);
					if (@ count($diff_ids) > 0) {
						XSD_HTML_Match::removeOptions($_POST['xsdmf_id'], array_values($diff_ids));
					}
				}
		}

		function updateFromArray($xsdmf_id, $params)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "UPDATE " . APP_TABLE_PREFIX . "xsd_display_matchfields " .
                "SET ";
			foreach (XSD_HTML_Match::$xsdmf_columns as $col_name) {
				if ($col_name == 'xsdmf_id') {
					// don't set the id
					continue;
				}
				$value = @$params[$col_name];
				if (strstr($col_name, '_id') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_multiple') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_required') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_order') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_validation_maxlength') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_is_key') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_show_in_view') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_invisible') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_show_simple_create') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_cvo_min_level') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_enabled') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_citation_order') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} elseif (strstr($col_name, 'xsdmf_valueintag') && $value == '') {
					$stmt .= " ".$col_name."=null,\n";
				} else {
					$stmt .= " ".$col_name."=".$db->quote($value).",\n";
				}
			}
			$stmt = rtrim($stmt,", \n"); // get rid of trailing comma
			$stmt .= " WHERE xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER');
			try {
				$res = $db->exec($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return -1;
			}
			XSD_HTML_Match::refreshXPATH($xsdmf_id);
			return 1;
		}

		/**
		 * Method used to try and get a XSDMF ID from an xsdmf_element
		 *
		 * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   string $xsdmf_xdis_id The XSD display to search for
		 * @return  array The XSDMF ID, or false if not found or more than one was found.
		 */
		function getXSDMF_IDByElement($xsdmf_element, $xsdmf_xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_element = ".$db->quote($xsdmf_element).
		                    " and xsdmf_xdis_id = ".$db->quote($xsdmf_xdis_id, 'INTEGER')." and xsdmf_xsdsel_id IS NULL";

			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
      //			$log->debug("omg whats the story for $xsdmf_element $xdis_id ?! ".$res[0]['xsdmf_id']." - ".print_r($res, true).$stmt);
			if (count($res) != 1) {
//				$log->debug("omg count not one! - ".print_r($res, true));
				return false;
			} else {

				return $res[0]['xsdmf_id'];
			}
    }

      function getXSDMF_IDsByElements($xsdmf_elements, $xsdmf_xdis_id)
      {
        $log = FezLog::get();
        $db = DB_API::get();

        $elements = "('".implode($xsdmf_elements, "','")."')";

        $stmt = "SELECT
                          xsdmf_id
                       FROM
                          " . APP_TABLE_PREFIX . "xsd_display_matchfields
                       WHERE
                          xsdmf_element IN ".$elements.
                          " and xsdmf_xdis_id = ".$db->quote($xsdmf_xdis_id, 'INTEGER')." ORDER BY LENGTH(xsdmf_element) DESC ";

        try {
          $res = $db->fetchCol($stmt);
        }
        catch(Exception $ex) {
          $log->err($ex);
          return '';
        }
				return $res;
		}

		/**
		 * Method used to update an idref to a new value for an xsdmf id record
		 *
		 * @access  public
		 * @param   string $xsdmf_id the XSD MF ID to update
		 * @param   string $new_xsdmf_id_ref The new id ref to replace the old one with
		 * @return  "" if failed, 1 if success.
		 */
		function updateXSDMF_ID_REF($xsdmf_id, $new_xsdmf_id_ref, $new_id_ref_xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET xsdmf_id_ref = ".$db->quote($new_xsdmf_id_ref, 'INTEGER').",
		                     xsdmf_xdis_id_ref = ".$db->quote($new_id_ref_xdis_id, 'INTEGER')."
		                 WHERE
		                    xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER');
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			return 1;
		}

		function updateXPathByXSDMF_ID($xsdmf_id, $xpath)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET xsdmf_xpath = ".$db->quote($xpath)."
		                 WHERE
		                    xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER');
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			return 1;
		}


		/**
		 * Method used to update an author suggest xdis id and xsdmf id to a new value for an xsdmf id record (mainly for cloning)
		 *
		 * @access  public
		 * @param   string $xsdmf_id the XSD MF ID to update
		 * @param   string $new_xsdmf_id_ref The new id ref to replace the old one with
		 * @return  "" if failed, 1 if success.
		 */
		function updateAuthorSuggestTarget($xsdmf_id, $new_xsdmf_id, $new_xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET xsdmf_asuggest_xsdmf_id = ".$db->quote($new_xsdmf_id, 'INTEGER').",
		                     xsdmf_asuggest_xdis_id = ".$db->quote($new_xdis_id, 'INTEGER')."
		                 WHERE
		                    xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER');
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			return 1;
		}

		/**
		 * Method used to update an organisation struction fill target xdis id and xsdmf id to a new value for an xsdmf id record (mainly for cloning)
		 *
		 * @access  public
		 * @param   string $xsdmf_id the XSD MF ID to update
		 * @param   string $new_xsdmf_id The new id to replace the old one with
		 * @return  "" if failed, 1 if success.
		 */
		function updateOrgFillTarget($xsdmf_id, $new_xsdmf_id, $new_xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET xsdmf_org_fill_xsdmf_id = ".$db->quote($new_xsdmf_id, 'INTEGER').",
		                     xsdmf_org_fill_xdis_id = ".$db->quote($new_xdis_id, 'INTEGER')."
		                 WHERE
		                    xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER');
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			return 1;
		}

		/**
		 * Method used to update parent option fill target xdis id and xsdmf id to a new value for an xsdmf id record (mainly for cloning)
		 *
		 * @access  public
		 * @param   string $xsdmf_id the XSD MF ID to update
		 * @param   string $new_xsdmf_id The new id to replace the old one with
		 * @return  "" if failed, 1 if success.
		 */
		function updateParentOptionTarget($xsdmf_id, $new_xsdmf_id, $new_xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET xsdmf_parent_option_child_xsdmf_id = ".$db->quote($new_xsdmf_id, 'INTEGER').",
		                     xsdmf_parent_option_xdis_id = ".$db->quote($new_xdis_id, 'INTEGER')."
		                 WHERE
		                    xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER');
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			return 1;
		}

		/**
		 * Method used to update attached xsdmf id targe and xsdmf id to a new value for an xsdmf id record (mainly for cloning)
		 *
		 * @access  public
		 * @param   string $xsdmf_id the XSD MF ID to update
		 * @param   string $new_xsdmf_id The new id to replace the old one with
		 * @return  "" if failed, 1 if success.
		 */
		function updateAttachedTarget($xsdmf_id, $new_xsdmf_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();


			$stmt = "UPDATE
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 SET xsdmf_attached_xsdmf_id = ".$db->quote($new_xsdmf_id, 'INTEGER')."
		                 WHERE
		                    xsdmf_id = ".$db->quote($new_xdis_id, 'INTEGER');
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			return 1;
		}

		/**
		 * Method used to try and get a XSDMF ID from an xsdmf_element
		 *
		 * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   string $xdis_str The string comma separated list of xdis_id's to search through
		 * @return  array The XSDMF ID, or false if not found or more than one was found.
		 */
		function getXSDMF_IDByXDIS_ID($xsdmf_element, $xdis_str)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                     xsdmf_element = ".$db->quote($xsdmf_element)." and xsdmf_xdis_id in (".$xdis_str.") and (xsdmf_is_key = FALSE || xsdmf_is_key is null)";

			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}

			if (count($res) != 1) {
				return false;
			} else {
				return $res[0]['xsdmf_id'];
			}
		}


    function getXSDMF_IDByXDIS_IDLowestNamespaceDiff($xsdmf_element, $xdis_str, $xsdmf_sel_id="")
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT
                        xsdmf_id
                     FROM
                        " . APP_TABLE_PREFIX . "xsd_display_matchfields
                     WHERE
                         xsdmf_element LIKE ".$db->quote($xsdmf_element."%")." and xsdmf_xdis_id in (".$xdis_str.") ";

        if (!is_numeric($xsdmf_sel_id)) {
            $stmt .= " AND xsdmf_sel_id = ".$xsdmf_sel_id;
        }
        $stmt .= " ORDER BY LENGTH(xsdmf_element) ASC";

        try {
            $res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }

        if (count($res) != 1) {
            return false;
        } else {
            return $res[0]['xsdmf_id'];
        }
    }


		/**
	  * getXSDMF_IDByKeyXDIS_ID
	  * look for key match on the attribute value - this is where the matchfield needs the
	  * attribute to be set to a certain value to match.
		 *
	  * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   string $element_value
		 * @param   string $xdis_str The string comma separated list of xdis_id's to search through
	  * @return  array The XSDMF ID, or false if not found or more than one was found.
	  */
		function getXSDMF_IDByKeyXDIS_ID($xsdmf_element, $element_value, $xdis_str)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    (".$db->quote($xsdmf_element)." = xsdmf_element) and xsdmf_xdis_id in (".$xdis_str.") and xsdmf_is_key = TRUE and (".$db->quote($element_value)." = xsdmf_key_match)";
			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0]['xsdmf_id'];
			}
		}

		/**
	  * getXSDMF_IDByParentKeyXDIS_ID
	  * look for key match on the attribute value - this is where the matchfield needs the
	  * attribute to be set to a certain value to match, by parent key.
		 *
	  * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   string $parent_key The parent key to use in the search
		 * @param   string $xdis_str The string comma separated list of xdis_id's to search through
	  * @return  array The XSDMF ID, or false if not found or more than one was found.
	  */
		function getXSDMF_IDByParentKeyXDIS_ID($xsdmf_element, $parent_key, $xdis_str)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    ".$db->quote($xsdmf_element)." = xsdmf_element and xsdmf_xdis_id in (".$xdis_str.") and xsdmf_parent_key_match = ".$db->quote($parent_key);

			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0]['xsdmf_id'];
			}
		}

		/**
	  * getXSDMF_IDByParentKeyXDIS_ID
	  * look for key match on the attribute value - this is where the matchfield needs the
	  * attribute to be set to a certain value to match, by parent key.
		 *
	  * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   string $key_value The key value to search by.
		 * @param   string $xdis_str The string comma separated list of xdis_id's to search through
		 * @param   integer $xsdsel_id The xsdsel ID to search by.
	  * @return  array The XSDMF ID, or false if not found or more than one was found.
	  */
		function getXSDMF_IDByKeyXDIS_IDSEL_ID($xsdmf_element, $key_value, $xdis_str, $xsdsel_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (!is_array($xsdsel_ids)) {
				return false;
			}
			$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    (".$db->quote($xsdmf_element)." = xsdmf_element) and xsdmf_xdis_id in (".$xdis_str.") and xsdmf_is_key = TRUE and (".$db->quote($key_value)." = xsdmf_key_match) and xsdmf_xsdsel_id = ".$db->quote($xsdsel_id);
			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0]['xsdmf_id'];
			}
		}

		/**
	  * look for key match on the attribute value - this is where the matchfield needs the
	  * attribute to be set to a certain value to match, by element SEL ID.
		 *
	  * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   integer $xsdsel_id The xsdsel ID to search by.
		 * @param   string $xdis_str The string comma separated list of xdis_id's to search through
	  * @return  array The XSDMF ID, or false if not found or more than one was found.
	  */
		function getXSDMF_IDByElementSEL_ID($xsdmf_element, $xsdsel_id, $xdis_str)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_element = ".$db->quote($xsdmf_element)." and xsdmf_xdis_id in (".$xdis_str.") and xsdmf_xsdsel_id=" . $db->quote($xsdsel_id, 'INTEGER');

			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0]['xsdmf_id'];
			}
		}

        function getOneDetailsBySEL_XSDMF_ID($xsdmf_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields INNER JOIN
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement ON xsdsel_indicator_xsdmf_id = xsdmf_id
		                 WHERE
		                    xsdsel_xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER');

			try {
				$res = $db->fetchRow($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return false;
			}
            return $res;
		}

		function getXSDMF_IDByElementSEL_Title($xsdmf_element, $xsdsel_title, $xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

      $xdis_list = XSD_Relationship::getListByXDIS($xdis_id);
      array_push($xdis_list, array("0" => $xdis_id));
      $xdis_str = Misc::sql_array_to_string($xdis_list);


			$stmt = "SELECT
		                    xsdmf_id
		                FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields
						INNER JOIN " . APP_TABLE_PREFIX . "xsd_loop_subelement on xsdmf_xsdsel_id = xsdsel_id
		                 WHERE
		           xsdmf_element = ".$db->quote($xsdmf_element)." and xsdmf_xdis_id IN (".$xdis_str.") and xsdsel_title=" . $db->quote($xsdsel_title);
//		                    xsdmf_element = ".$db->quote($xsdmf_element)." and xsdmf_xdis_id = ".$xdis_id." and xsdsel_title=" . $db->quote($xsdsel_title);

			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0]['xsdmf_id'];
			}
		}

		/**
	  * getXSDMF_IDByParentKeyXDIS_ID
	  * look for key match on the attribute value - this is where the matchfield needs the
	  * attribute to be set to a certain value to match, by parent key.
		 *
	  * @access  public
		 * @param   string $xsdmf_element The xsdmf element to search for
		 * @param   array $xsdsel_id The list of xsdsel IDs to search by.
		 * @param   string $xdis_str The string comma separated list of xdis_id's to search through
	  * @return  array The XSDMF ID, or false if not found or more than one was found.
	  */
		function getXSDMF_IDByElementSEL_IDArray($xsdmf_element, $xsdsel_ids, $xsdmf_xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (!is_array($xsdsel_ids)) {
				return false;
			}
			$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_element = ".$db->quote($xsdmf_element)." and xsdmf_xdis_id = ".$db->quote($xsdmf_xdis_id,'INTEGER')." and xsdmf_xsdsel_id in (" . implode("," . $xsdsel_id) . ")";
			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0]['xsdmf_id'];
			}
		}

		/**
	  * getXSDMF_IDByOriginalXSDMF_ID
	  * Returns the new xsdmf_id of an entry by its previous (original from a clone) xsdmf_id. Mainly used by the XSD Display clone function.
		 *
	  * @access  public
		 * @param   integer $original_xsdmf_id The xsdmf id element to search for
		 * @param   integer $xdis_id The xdis_id display ID to search in
	  * @return  integer The new XSDMF ID, or false if not found or more than one was found.
	  */
		function getXSDMF_IDByOriginalXSDMF_ID($original_xsdmf_id, $xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (!is_numeric($original_xsdmf_id)) {
				return false;
			}
			$stmt = "SELECT
		                    xsdmf_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_original_xsdmf_id = " . $db->quote($original_xsdmf_id, 'INTEGER') . " AND xsdmf_xdis_id = " . $db->quote($xdis_id, 'INTEGER');
			try {
				$res = $db->fetchAll($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if (count($res) != 1) {
				return false;
			} else {
				return $res[0]['xsdmf_id'];
			}
		}


		/**
	  * getXSDMF_IDsBySekTitle
	  * Returns a list of XSDMF_IDs matching a sek title
	  *
	  * @access  public
	  * @param   string $sek_title
	  * @return  array $res The list of xsdmf_ids
	  */
		function getXSDMF_IDsBySekTitle($sek_title, $nocache = false)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			static $returns;
			if (!$sek_title) {
				return array();
			}
			if (!empty($returns[$sek_title]) && !$nocache) {
				return $returns[$sek_title];
			} else {
				$stmt = "SELECT
	                   xsdmf_id
	                FROM
	                   " . APP_TABLE_PREFIX . "xsd_display_matchfields x1
					INNER JOIN " . APP_TABLE_PREFIX . "search_key AS s1
	                ON
	                   x1.xsdmf_sek_id = s1.sek_id and s1.sek_title = ".$db->quote($sek_title);
				try {
					$res = $db->fetchCol($stmt);
				}
				catch(Exception $ex) {
					$log->err($ex);
					return '';
				}
				if ($GLOBALS['app_cache']) {
					if (!is_array($returns) || count($returns) > 10) { //make sure the static memory var doesnt grow too large and cause a fatal out of memory error
						$returns = array();
					}
					$returns[$sek_title] = $res;
				}
				return $res;
			}
		}

		/**
		 * getXSDMF_IDsBySekID
		 * Returns a list of XSDMF_IDs matching a sek ID
		 *
		 * @access  public
		 * @param   integer $sek_id
		 * @return  array $res The list of xsdmf_ids
		 */
		function getXSDMF_IDsBySekID($sek_id, $nocache = false)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			static $returns;
			if (empty($sek_id)) {
				return array();
			}
			if (!empty($returns[$sek_id]) && !$nocache) {
				return $returns[$sek_id];
			} else {
				$stmt = "SELECT
	                   xsdmf_id
	                FROM
	                   " . APP_TABLE_PREFIX . "xsd_display_matchfields x1
					WHERE
	                   x1.xsdmf_sek_id = ".$db->quote($sek_id);
				try {
					$res = $db->fetchCol($stmt);
				}
				catch(Exception $ex) {
					$log->err($ex);
					return '';
				}

				if ($GLOBALS['app_cache']) {
					if (!is_array($returns) || count($returns) > 10) { //make sure the static memory var doesnt grow too large and cause a fatal out of memory error
						$returns = array();
					}
					$returns[$sek_id] = $res;
				}
				return $res;
			}
		}

		function getXSDMF_IDBySekIDXDIS_ID($sek_id, $xdis_str="", $nocache = false)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (empty($sek_id)) {
				return array();
			}

			$stmt = "SELECT
	                   xsdmf_id
	                FROM
	                   " . APP_TABLE_PREFIX . "xsd_display_matchfields x1
					WHERE
	                   x1.xsdmf_sek_id = ".$db->quote($sek_id) . " AND x1.xsdmf_xdis_id in (".$xdis_str.")";
			try {
				$res = $db->fetchCol($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if ($GLOBALS['app_cache']) {
				if (!is_array($returns) || count($returns) > 10) { //make sure the static memory var doesnt grow too large and cause a fatal out of memory error
					$returns = array();
				}
			}
			return $res;
		}



		/**
		 * Method used to get the list of XSD HTML Matching fields available in the
		 * system.
		 *
		 * @access  public
		 * @return  array The list of custom fields
		 */
		function getListAssoc()
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    xsdmf_id, xsdmf_element
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 ORDER BY
		                    xsdmf_element ASC";
			try {
				$res = $db->fetchPairs($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			return $res;
		}

		function getList($xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE xsdmf_xdis_id=".$db->quote($xdis_id)."
		                 ORDER BY
		                    xsdmf_element ASC";
			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}

			return $res;
		}

		function getListAll()
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id) left join
							" . APP_TABLE_PREFIX . "xsd_display on (xsdmf_xdis_id = xdis_id) left join
							" . APP_TABLE_PREFIX . "xsd on (xdis_xsd_id = xsd_id) ";
			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}

			return $res;
		}


		/**
		 * Method used to get the details of a specific XSD HTML Matching Field.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID
		 * @param   string $xml_element The XML element to search by.
		 * @return  array The field details
		 */
		function getDetails($xdis_id, $xml_element) {
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id)
		                 WHERE
							 xsdmf_element=".$db->quote($xml_element)." AND (xsdmf_xsdsel_id IS NULL) AND xsdmf_xdis_id=" . $db->quote($xdis_id, 'INTEGER');
			try {
				$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if (is_array($res)) {
				$options = XSD_HTML_Match::getOptions($res['xsdmf_id']);
				foreach ($options as $mfo_id => $mfo_value) {
					$res["field_options"]["existing:" . $mfo_id . ":" . $mfo_value] = $mfo_value;
				}
			}
			return $res;
		}

  		/**
		 * Method used to get the details of a specific XSD HTML Matching Field, by XSDMF ID
		 *
		 * @access  public
		 * @param   array $xsdmf_ids
		 * @return  array The details
		 */
		function getSearchKeysByXSDMF_IDS($xsdmf_ids)
		{
			$log = FezLog::get();
			$db = DB_API::get();

      if (!is_array($xsdmf_ids)) {
        return false;
      }

      $xsdmf_str = Misc::array_to_sql_string($xsdmf_ids);

			$stmt = "SELECT
		                   xsdmf_id, sek_title
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields INNER JOIN
		                    " . APP_TABLE_PREFIX . "search_key on xsdmf_sek_id = sek_id

		                 WHERE
		                    xsdmf_id IN (" . $xsdmf_str .")";
			try {
				$res = $db->fetchPairs($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			return $res;
		}

		/**
		 * Method used to get the details of a specific XSD HTML Matching Field, by XSDMF ID
		 *
		 * @access  public
		 * @param   integer $xsdmf_id
		 * @return  array The details
		 */
		function getDetailsByXSDMF_ID($xsdmf_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id)
		                 WHERE
		                    xsdmf_id=" . $db->quote($xsdmf_id, 'INTEGER');
			try {
				$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if (is_array($res)) {
				$options = XSD_HTML_Match::getOptions($res['xsdmf_id']);
				foreach ($options as $mfo_id => $mfo_value) {
					$res["field_options"]["existing:" . $mfo_id . ":" . $mfo_value] = $mfo_value;
				}
			}
			return $res;
		}

		function getAllDetailsByXSDMF_ID($xsdmf_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();
			$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id) left join
							" . APP_TABLE_PREFIX . "xsd_display on (xsdmf_xdis_id = xdis_id) left join
							" . APP_TABLE_PREFIX . "xsd on (xdis_xsd_id = xsd_id)
		                 WHERE
		                    xsdmf_id=" . $db->quote($xsdmf_id, 'INTEGER');
			try {
				$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if (is_array($res)) {
				$options = XSD_HTML_Match::getOptions($res['xsdmf_id']);
				foreach ($options as $mfo_id => $mfo_value) {
					$res["field_options"]["existing:" . $mfo_id . ":" . $mfo_value] = $mfo_value;
				}
			}
			return $res;
		}


		/**
		 * Method used to get the XSD Display ID with a given XSDMFID
		 *
		 * @access  public
		 * @param   integer $xdis_title The XSD title to search by.
		 * @return  array $res The xdis_id
		 */
		function getXDIS_IDByXSDMF_ID($xsdmf_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                   xsdmf_xdis_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER');
			try {
				$res = $db->fetchOne($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			return $res;
		}


                /**
                 * Method used to get the XSD Display ID with a given XSDMFID
                 *
                 * @access  public
                 * @param   integer $xdis_title The XSD title to search by.
                 * @return  array $res The xdis_id
                 */
                function getSmartyVarByXSDMF_ID($xsdmf_id)
                {
                        $log = FezLog::get();
                        $db = DB_API::get();

                        $stmt = "SELECT
                                   xsdmf_smarty_variable
                                 FROM
                                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
                                 WHERE
                                    xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER');
                        try {
                                $res = $db->fetchOne($stmt);
                        }
                        catch(Exception $ex) {
                                $log->err($ex);
                                return '';
                        }
                        return $res;
                }

		/**
		 * Method used to get the details of a specific XSD HTML Matching Field, by xml element and sublooping id.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID
		 * @param   string $xml_element The XML element to search by.
		 * @param   integer $xsdsel_id The sublooping element to search by
		 * @return  array The custom field details
		 */
		function getDetailsSubelement($xdis_id, $xml_element, $xsdsel_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id)
		                 WHERE
		                    xsdmf_element=".$db->quote($xml_element)." AND xsdmf_xsdsel_id = ".$db->quote($xsdsel_id, 'INTEGER')." AND xsdmf_xdis_id=" . $db->quote($xdis_id, 'INTEGER');

			try {
				$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}

			if (is_array($res)) {
				$options = XSD_HTML_Match::getOptions($res['xsdmf_id']);
				foreach ($options as $mfo_id => $mfo_value) {
					$res["field_options"]["existing:" . $mfo_id . ":" . $mfo_value] = $mfo_value;
				}
			}
			return $res;
		}

		/**
		 * Method used to get the XSD_ID parent of a given XSD Display.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID to search by.
		 * @return  array The XSD ID
		 */
		function getXSD_ID($xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    xdis_xsd_id
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display
		                 WHERE
		                    xdis_id=".$db->quote($xdis_id, 'INTEGER');
			try {
				$res = $db->fetchOne($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			return $res;
		}

		/**
		 * Method used to check if the XSD matching field is attached to other fields, therefore gets handled differently in FOXML::array_to_xml_instance
		 *
		 * @access  public
		 * @param   integer $xsdmf_id The XSD matching field ID to search for
		 * @return  boolean true or false
		 */
		function isAttachedXSDMF($xsdmf_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT count(*) as
		                    attach_count
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_attached_xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER')." and xsdmf_id not in
							(select distinct ifnull(xsdmf_attached_xsdmf_id, 0)
		        			   from " . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER').");";
			try {
				$res = $db->fetchOne($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}
			if (is_numeric($res) && ($res > 0)) {
				return true;
			} else {
				return false;
			}
		}

		function getChildren($xsdmf_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT *
		                 FROM " . APP_TABLE_PREFIX . "xsd_display_attach
		                 WHERE att_parent_xsdmf_id = ".$db->quote($xsdmf_id, 'INTEGER');

			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}
			return $res;
		}

		function setChild($xsdmf_id, $att_child_xsdmf_id, $att_order)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "INSERT INTO " . APP_TABLE_PREFIX . "xsd_display_attach" .
					"(att_parent_xsdmf_id, att_child_xsdmf_id, att_order)" .
					"VALUES" .
					"(".$db->quote($xsdmf_id).", ".$db->quote($att_child_xsdmf_id).", ".$db->quote($att_order).")";
			try {
				$db->query($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return -1;
			}
			return $db->lastInsertId(APP_TABLE_PREFIX . "xsd_display_attach", "att_id");
		}

		/**
		 * Method used to get the list of matching field options associated
		 * with a given matching field ID.
		 *
		 * @access  public
		 * @param   integer $fld_id The matching field ID
		 * @param   array $fld_id If the fld_id is an array of fld_ids, then the fucntion will return a list
		 *                        of matching options for all the fields.
		 * @return  array The list of matching field options as array(mfo_id => mfo_value),
		 *                 or if an array was passed, array(fld_id => array(mfo_id, mfo_value))
		 */
		function getOptions($fld_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			static $mfo_returns;
			if (!empty ($mfo_returns[$fld_id])) { // check if this has already been found and set to a static variable
				return $mfo_returns[$fld_id];
			} else {

				if (is_array($fld_id)) {
					$stmt = "SELECT
										mfo_id,
										mfo_value
									FROM
										" . APP_TABLE_PREFIX . "xsd_display_mf_option
									WHERE
										mfo_fld_id IN (".Misc::arrayToSQLBindStr($fld_id).")
									ORDER BY
										mfo_value ASC";
					try {
						$res = $db->fetchPairs($stmt, $fld_id, Zend_DB::FETCH_BOTH);
					}
					catch(Exception $ex) {
						$log->err($ex);
						return array();
					}
				} else {

					$stmt = "SELECT
									mfo_id,
									mfo_value
									FROM
										" . APP_TABLE_PREFIX . "xsd_display_mf_option
										WHERE
										mfo_fld_id=?
										ORDER BY
										mfo_value ASC";
					try {
						$res = $db->fetchPairs($stmt, $fld_id, Zend_DB::FETCH_BOTH);
					}
					catch(Exception $ex) {
						$log->err($ex);
						if ($GLOBALS['app_cache']) {
							if (!is_array($mfo_returns) || count($mfo_returns) > 10) { //make sure the static memory var doesnt grow too large and cause a fatal out of memory error
								$mfo_returns = array();
							}
							$mfo_returns[$fld_id] = $res;
						}
						return array();
					}
				}
				return $res;
			}
		}

		/**
		 * Method used to get the of matching field option value with a given field id
		 *
		 * @access  public
		 * @param   integer $fld_id The matching field ID
		 * @return  array The list of matching field options as array(mfo_id => mfo_value),
		 *                 or if an array was passed, array(fld_id => array(mfo_id, mfo_value))
		 */
		function getOptionsValueOnly($fld_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    mfo_value, mfo_value
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_mf_option
		                 WHERE
		                    mfo_fld_id=".$db->quote($fld_id, 'INTEGER')."
		                 ORDER BY
		                    mfo_value ASC";
			try {
				$res = $db->fetchPairs($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return '';
			}

			$res2 = array ();
			foreach ($res as $key => $value) {
				$res2[utf8_encode($key)] = $value;
			}

			return $res2;
		}

		/**
		 * Method used to parse the special format used in the combo boxes
		 * in the administration section of the system, in order to be
		 * used as a way to flag the system for whether the matching field
		 * option is a new one or one that should be updated.
		 *
		 * @access  private
		 * @param   string $value The matching field option format string
		 * @return  array Parameters used by the update/insert methods
		 */
		function parseParameters($value)
		{
			if (substr($value, 0, 4) == 'new:') {
				return array (
				"type" => "new",
				"value" => substr($value,
				4
				));
			} else {
				$value = substr($value, strlen("existing:"));
				return array (
				"type" => "existing",
				"id" => substr($value,
				0,
				strpos($value,
				":"
				)), "value" => substr($value, strpos($value, ":") + 1));
			}
		}

		/**
		 * Method used to get list of XSDMF elements belonging to a XSD Display
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID to search by.
		 * @return  array The list of XSDMF elements
		 */
		function getElementMatchList($xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    xsdmf_element
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields
		                 WHERE
		                    xsdmf_xdis_id=".$db->quote($xdis_id);
			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}
			$ret = array ();
			foreach ($res as $record) {
				$ret[] = $record['xsdmf_element'];
			}
			return $ret;
		}

		/**
		 * Method used to get list of XSDMF elements belonging to a XSD Display plus some extra display information
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID to search by.
		 * @return  array The list of XSDMF elements
		 */
		function getElementMatchListDetails($xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$stmt = "SELECT
		                    xsdmf_id, xsdmf_element, xsdmf_title, xsdmf_id_ref, xsdmf_html_input, xsdmf_enabled, xsdmf_invisible, xsdmf_show_simple_create, xsdmf_order, xsdmf_dynamic_text, xsdmf_static_text, xsdmf_xsdsel_id, xsdsel_title
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields left join
		                    " . APP_TABLE_PREFIX . "xsd_loop_subelement on (xsdmf_xsdsel_id = xsdsel_id)
		                 WHERE
		                    xsdmf_xdis_id=".$db->quote($xdis_id, 'INTEGER');
			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}

			$ret = array();
			foreach ($res as $record) {
				$ret[$record['xsdmf_element']][] = $record;
			}
			return $ret;
		}

		/**
		 * Method used to get list of XSDMF elements belonging to a XSD Display, but not found in the XSD Source itself.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID to search by.
		 * @return  array The list of orphaned XSDMF elements (for deletion usually).
		 */
		function getElementOrphanList($xdis_id, $xsd_array)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$xsd_list = Misc::array_flatten($xsd_array);
			$xsd_list = implode("', '", $xsd_list);
			$sel_list = XSD_HTML_Match::getSubloopingElementsByXDIS_ID($xdis_id);
			$sel_list = implode(",", $sel_list);
			$stmt = "SELECT
		                    *
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields x1 left join
							" . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
		                 WHERE
		                    x1.xsdmf_xdis_id=".$db->quote($xdis_id,'INTEGER')." and (x1.xsdmf_element not in ('".$xsd_list."') or x1.xsdmf_xsdsel_id not in (
                                ".$sel_list."
								))
		                    ";
			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}
			return $res;
		}

		function getSubloopingElementsByXDIS_ID($xdis_id)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (!is_numeric($xdis_id)) {
				return array(0);
			}

			$stmt = " SELECT distinct(s2.xsdsel_id) FROM
                                     " . APP_TABLE_PREFIX . "xsd_loop_subelement s2 left join
                                     " . APP_TABLE_PREFIX . "xsd_display_matchfields x2 on (x2.xsdmf_xsdsel_id = s2.xsdsel_id)
                                  WHERE x2.xsdmf_xdis_id = ".$db->quote($xdis_id,'INTEGER');

			try {
				$res = $db->fetchCol($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array(0);
			}
			return $res;
		}

		/**
		 * Method used to get the count of XSDMF elements belonging to a XSD Display, but not found in the XSD Source itself.
		 *
		 * @access  public
		 * @param   integer $xdis_id The XSD Display ID to search by.
		 * @return  integer The count of orphaned XSDMF elements (for deletion usually).
		 */
		function getElementOrphanCount($xdis_id, $xsd_array)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			$xsd_list = Misc::array_flatten($xsd_array);
			$xsd_list = implode("', '", $xsd_list);
			$sel_list = XSD_HTML_Match::getSubloopingElementsByXDIS_ID($xdis_id);
			$sel_list = implode(",", $sel_list);
			$stmt = "SELECT
		                    count(*) as orphan_count
		                 FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields x1 left join
							" . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
		                 WHERE
		                    x1.xsdmf_xdis_id=".$db->quote($xdis_id, 'INTEGER');
			if ($xsd_list !== "" || $sel_list !== "") {
				$stmt .= " and (";
				if ($xsd_list !== "") {
					$stmt .= "x1.xsdmf_element not in ('".$xsd_list."') ";
				}
				if ($sel_list !== "") {
					if ($xsd_list !== "") {
						$stmt .= " or ";
					}
					$stmt .= "x1.xsdmf_xsdsel_id not in (".$sel_list.")";
				}
				$stmt .= ")";
			}
			try {
				$res = $db->fetchOne($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}
			return $res;
		}

		function exportMatchFields(& $xdis, $xdis_id)
		{
			$list = XSD_HTML_Match::getList($xdis_id);
			foreach ($list as $item) {
				$xmatch = $xdis->ownerDocument->createElement('matchfield');
				foreach (XSD_HTML_Match::$xsdmf_columns as $field) {
					$xmatch->setAttribute($field, $item[$field]);
				}
				$att_list = XSD_HTML_Match::getChildren($item['xsdmf_id']);
				if (!empty ($att_list)) {
					foreach ($att_list as $att) {
						$xatt = $xdis->ownerDocument->createElement('attach');
						$xatt->setAttribute('att_id', $att['att_id']);
						$xatt->setAttribute('att_child_xsdmf_id', $att['att_child_xsdmf_id']);
						$xatt->setAttribute('att_order', $att['att_order']);
						$xmatch->appendChild($xatt);
					}
				}
				$mfo_list = XSD_HTML_Match::getOptions($item['xsdmf_id']);
				if (is_array($mfo_list)) {
					foreach ($mfo_list as $mfo_key => $mfo_value) {
						if (!empty ($mfo_value)) {
							$xmfo = $xdis->ownerDocument->createElement('option');
							$xmfo->setAttribute('mfo_id', $mfo_key);
							$xmfo->setAttribute('mfo_value', $mfo_value);
							$xmatch->appendChild($xmfo);
						}
					}
				}
				$subs = XSD_Loop_Subelement::getSimpleListByXSDMF($item['xsdmf_id']);
				if (!empty ($subs)) {
					foreach ($subs as $sub) {
						$xsub = $xdis->ownerDocument->createElement('loop_subelement');
						$xsub->setAttribute('xsdsel_id', $sub['xsdsel_id']);
						$xsub->setAttribute('xsdsel_title', $sub['xsdsel_title']);
						$xsub->setAttribute('xsdsel_type', $sub['xsdsel_type']);
						$xsub->setAttribute('xsdsel_order', $sub['xsdsel_order']);
						$xsub->setAttribute('xsdsel_attribute_loop_xdis_id', $sub['xsdsel_attribute_loop_xdis_id']);
						$xsub->setAttribute('xsdsel_attribute_loop_xsdmf_id', $sub['xsdsel_attribute_loop_xsdmf_id']);
						$xsub->setAttribute('xsdsel_indicator_xdis_id', $sub['xsdsel_indicator_xdis_id']);
						$xsub->setAttribute('xsdsel_indicator_xsdmf_id', $sub['xsdsel_indicator_xsdmf_id']);
						$xsub->setAttribute('xsdsel_indicator_value', $sub['xsdsel_indicator_value']);
						$xmatch->appendChild($xsub);
					}
				}
				$rels = XSD_Relationship::getSimpleListByXSDMF($item['xsdmf_id']);
				if (!empty ($rels)) {
					foreach ($rels as $rel) {
						$xrel = $xdis->ownerDocument->createElement('relationship');
						$xrel->setAttribute('xsdrel_id', $rel['xsdrel_id']);
						$xrel->setAttribute('xsdrel_xdis_id', $rel['xsdrel_xdis_id']);
						$xrel->setAttribute('xsdrel_order', $rel['xsdrel_order']);
						$xmatch->appendChild($xrel);
					}
				}
				$xdis->appendChild($xmatch);
			}
		}
		function importMatchFields($xdis, $xdis_id, & $maps)
		{
			$xpath = new DOMXPath($xdis->ownerDocument);
			$xmatches = $xpath->query('matchfield', $xdis);
			foreach ($xmatches as $xmatch) {
				$params = array ();
				foreach (XSD_HTML_Match::$xsdmf_columns as $field) {
					$params[$field] = $xmatch->getAttribute($field);
				}
				$params['xsdmf_xdis_id'] = $xdis_id;
				$xsdmf_id = XSD_HTML_Match::insertFromArray($xdis_id, $params);
				$maps['xsdmf_map'][$xmatch->getAttribute('xsdmf_id')] = $xsdmf_id;
				$xpath = new DOMXPath($xmatch->ownerDocument);
				$xatts = $xpath->query('attach', $xmatch);
				foreach ($xatts as $xatt) {
					XSD_HTML_Match::setChild($xsdmf_id, $xatt->getAttribute('att_child_xsdmf_id'), $xatt->getAttribute('att_order'));
				}
				$xopts = $xpath->query('option', $xmatch);
				$opts = array ();
				foreach ($xopts as $xopt) {
					$opts[] = $xopt->getAttribute('mfo_value');
				}
				XSD_HTML_Match::addOptions($xsdmf_id, $opts);
				XSD_Loop_Subelement::importSubelements($xmatch, $xsdmf_id, $maps);
				XSD_Relationship::importRels($xmatch, $xsdmf_id, $maps);
			}
		}

		/**
		 * This is the second pass of the import which looks for inserted records which reference other records
		 * These references need to be updated to point to the ids used in the DB instead of in the XML file
		 */
		function remapImport(& $maps, & $bgp)
		{
			$log = FezLog::get();
			$db = DB_API::get();

			if (empty ($maps['xsdmf_map'])) {
				return;
			}
			// find all the stuff that references the new displays
			$xsdmf_ids = array_values(@ $maps['xsdmf_map']);
			$xsdmf_ids_str = Misc::arrayToSQL($xsdmf_ids);

			$bgp->setStatus("Remapping XSDMF ids");
			foreach ($maps['xsdmf_map'] as $xsdmf_id) {
				$stmt = "SELECT * FROM ". APP_SQL_DBNAME . "." . APP_TABLE_PREFIX ."xsd_display_matchfields " .
                    "WHERE xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER');
				try {
					$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
					Misc::arraySearchReplace($res,
					array('xsdmf_xdis_id_ref','xsdmf_parent_option_xdis_id','xsdmf_asuggest_xdis_id'),
					$maps['xdis_map']);
					Misc::arraySearchReplace($res,
					array('xsdmf_original_xsdmf_id',
                    'xsdmf_attached_xsdmf_id',
                    'xsdmf_parent_option_child_xsdmf_id',
                    'xsdmf_org_fill_xsdmf_id',
                    'xsdmf_asuggest_xsdmf_id',
                    'xsdmf_id_ref'),
					$maps['xsdmf_map']);
					Misc::arraySearchReplace($res,
					array('xsdmf_xsdsel_id'),
					$maps['xsdsel_map']);
					XSD_HTML_Match::updateFromArray($xsdmf_id,$res);
				}
				catch(Exception $ex) {
					$log->err($ex);
				}
				// remap attachments
				$stmt = "SELECT * FROM ". APP_SQL_DBNAME . "." . APP_TABLE_PREFIX ."xsd_display_attach " .
                    "WHERE att_parent_xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER');

				try {
					$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
					if (!empty($res)) {
						Misc::arraySearchReplace($res,
						array('att_child_xsdmf_id'),
						$maps['xsdmf_map']);
						$stmt = "UPDATE ". APP_SQL_DBNAME . "." . APP_TABLE_PREFIX ."xsd_display_attach " .
                            "SET " ;
						foreach ($res as $key => $value) {
							$stmt .= " ".$key." = '".$value."', ";
						}
						$stmt = rtrim($stmt, ', ');
						$stmt .= " WHERE att_parent_xsdmf_id=".$db->quote($xsdmf_id, 'INTEGER');

						try {
							$res = $db->exec($stmt);
						}
						catch(Exception $ex) {
							$log->err($ex);
						}
					}
				}
				catch(Exception $ex) {
					$log->err($ex);
					$res = array();
				}
			}

			// remap the sublooping elements
			$bgp->setStatus("Remapping Sub Looping Elements");
			XSD_Loop_Subelement::remapImport($maps);
			//remap the relationships
			$bgp->setStatus("Remapping XSD Relationships");
			XSD_Relationship::remapImport($maps);
			$bgp->setStatus("Remapping Citation templates");
			Citation::remapImport($maps);

		}

		function escapeXPath($xpath) {
			$element_text = str_replace('/', '!', $xpath);
			// get rid of root element
			$element_text = preg_replace('/![^!]+/', '', $element_text, 1);
			$element_text = preg_replace('/@/', '', $element_text);
			// get rid of prefixes (unless it's dc)
			if (!strstr($element_text, '!dc:')) {
				$element_text = preg_replace('/![^:]+:/', '!', $element_text);
			}
			return $element_text;
		}


} // end class XSD_HTML_Match

/**
 * XSD_HTML_MatchObject
 * Object for managing display fields matching against XML datastreams.
 */
class XSD_HTML_MatchObject {
	var $xdis_str;
	//	var $xdis_array = array(); // doesnt appear to be used in here anymore - CK

	/**
	 * XSD_HTML_MatchObject
	 * Instantiate object with a list of displays that relate to the main display being matched
	 */
	function XSD_HTML_MatchObject($xdis_str)
	{
		$this->xdis_str = $xdis_str;
	}

	/**
	 * getMatchCols
	 * Retrieve the matchfields records that relate to the current display and store them locally.  This
	 * method keeps a local copy of the results to save making multiple queries for the same information.
	 */
	function getMatchCols()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		// Check for a global var
		if (!empty($GLOBALS['match_cols'][$this->xdis_str])) {
			return $GLOBALS['match_cols'][$this->xdis_str];
		}

		// stop this global array growing too large.
		if (!array_key_exists('match_cols', $GLOBALS) || count($GLOBALS['match_cols']) > 10) {
			$GLOBALS['match_cols'] = array();
		}
		// do query to get all the match cols for this display set
		$stmt = "SELECT
			                   m1.*,
							   s1.xsdsel_title,
                               s1.xsdsel_indicator_xsdmf_id,
                               s1.xsdsel_indicator_value,
                               x1.xsd_element_prefix,
                               m2.xsdmf_element as indicator_element,
			   				   m2.xsdmf_xsdsel_id as indicator_xsdsel_id
			                FROM
			                    " . APP_TABLE_PREFIX . "xsd_display_matchfields m1 left join
								" . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (m1.xsdmf_id = s1.xsdsel_xsdmf_id) left join
								" . APP_TABLE_PREFIX . "xsd_display d1 on (m1.xsdmf_xdis_id = d1.xdis_id)  left join
								" . APP_TABLE_PREFIX . "xsd x1 on (d1.xdis_xsd_id = x1.xsd_id) left join
								" . APP_TABLE_PREFIX . "xsd_display_matchfields m2 on (m2.xsdmf_id = s1.xsdsel_indicator_xsdmf_id)
			                WHERE
			                    m1.xsdmf_xdis_id in (".$this->xdis_str.")";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			$GLOBALS['match_cols'][$this->xdis_str] = $res;
		}
		catch(Exception $ex) {
			$log->err($ex);
			$GLOBALS['match_cols'][$this->xdis_str] = array ();
		}
		return $GLOBALS['match_cols'][$this->xdis_str];
	}


	/**
	 * getXSDMF_IDByParentKeyXDIS_ID
	 * Find a match for an element that has a parent key element with the matched value.
	 */
	function getXSDMF_IDByParentKeyXDIS_ID($xsdmf_element, $parent_key)
	{
		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
			if (($xsdmf['xsdmf_element'] == $xsdmf_element)
			&& ($xsdmf['xsdmf_parent_key_match'] == $parent_key) && !empty ($xsdmf['xsdmf_id'])) {
				return $xsdmf['xsdmf_id'];
			}
		}
		return null;
	}

	/**
	 * getXSDMF_IDByXDIS_ID
	 * Find a match for the given element and option title
	 */
	function getXSDMF_IDByXDIS_ID($xsdmf_element, $xsdmf_title="")
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
//			echo $xsdmf['xsdmf_element'];
			if (($xsdmf['xsdmf_element'] == $xsdmf_element) && $xsdmf['xsdmf_is_key'] == FALSE && is_numeric($xsdmf['xsdmf_id'])) {
				if ($xsdmf_title != "") {
					if ($xsdmf['xsdmf_title'] == $xsdmf_title) {
						return $xsdmf['xsdmf_id'];
					}
				} else {
					return $xsdmf['xsdmf_id']; // just returns the first one if there are many and xsdmf_title is not used
				}
			}
		}
		return null;
	}

	/**
	 * getXSDMFByElement
	 * Find a match for the given element
	 * @param string $xsdmf_element - xml element to search for (escaped with '!')
	 * @param string $xdis_ids - Comma delimited list. Specify sub XSD disply mappings to use.
	 *                          If null will default to all for the current display.
	 */
	function getXSDMFByElement($xsdmf_element, $xdis_ids=null)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (empty($xdis_ids)) {
			$xdis_str = $this->xdis_str;
		} else {
			$xdis_str = $xdis_ids;
		}

		$stmt = "SELECT
		                   m1.xsdmf_element,
		                   m1.xsdmf_id,
		                   m1.xsdmf_is_key,
		                   m1.xsdmf_key_match,
		                   m1.xsdmf_parent_key_match,
		                   m1.xsdmf_xsdsel_id,
		                   m1.xsdmf_value_prefix,
						   m1.xsdmf_html_input,
						   s1.xsdsel_title,
						   s1.xsdsel_indicator_xsdmf_id,
						   s1.xsdsel_indicator_value,
						   x1.xsd_element_prefix,
						   m2.xsdmf_element as indicator_element,
		   				   m2.xsdmf_xsdsel_id as indicator_xsdsel_id
		                FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields m1 left join
							" . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (m1.xsdmf_id = s1.xsdsel_xsdmf_id) left join
							" . APP_TABLE_PREFIX . "xsd_display d1 on (m1.xsdmf_xdis_id = d1.xdis_id)  left join
							" . APP_TABLE_PREFIX . "xsd x1 on (d1.xdis_xsd_id = x1.xsd_id) left join
							" . APP_TABLE_PREFIX . "xsd_display_matchfields m2 on (m2.xsdmf_id = s1.xsdsel_indicator_xsdmf_id)
		                WHERE
		                    m1.xsdmf_xdis_id in (".$xdis_str.") and m1.xsdmf_element = ".$db->quote($xsdmf_element);

		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}

	/**
	 * getXSDMF_IDByKeyXDIS_ID
	 * Find a match field for an element that matches a key on the element value
	 *
	 * @access  public
	 * @param   string $xsdmf_element
	 * @param   string $element_value
	 * @return  integer The xsdmf_id
	 */
	function getXSDMF_IDByKeyXDIS_ID($xsdmf_element, $element_value)
	{
		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
			if (($xsdmf['xsdmf_element'] == $xsdmf_element) && ($xsdmf['xsdmf_key_match'] == $element_value) && $xsdmf['xsdmf_is_key'] && !empty ($xsdmf['xsdmf_id'])) {
				return $xsdmf['xsdmf_id'];
			}
		}
		return null;
	}

	/**
	 * getXSDMF_IDByKeyXDIS_ID
	 * Find a match field for an element that matches a subelement loop on the element value
	 *
	 * @access  public
	 * @param   string $xsdmf_element
	 * @param   string $element_value
	 * @return  integer The xsdmf_id
	 */
	function getXSDMF_IDBySELXDIS_ID($xsdmf_element, $xsdsel_id)
	{
		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
			if (($xsdmf['xsdmf_element'] == $xsdmf_element) && ($xsdmf['xsdmf_xsdsel_id'] == $xsdsel_id) && !empty ($xsdmf['xsdmf_id'])) {
				return $xsdmf['xsdmf_id'];
			}
		}
		return null;
	}

	function getXSDMF_IDBySEK($sek_id)
	{
		foreach ($this->getMatchCols() as $xsdmf ) {
			if ($xsdmf['xsdmf_sek_id'] == $sek_id) {
				return $xsdmf['xsdmf_id'];
			}
		}
	}
	/**
	 * getDetailsByXSDMF_ID
	 * Retrieve the details of a match field
	 *
	 * @access  public
	 * @param   integer $xsdmf_id
	 * @return  array The details
	 */
	function getDetailsByXSDMF_ID($xsdmf_id)
	{
		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
			if ($xsdmf['xsdmf_id'] == $xsdmf_id) {
				return $xsdmf;
			}
		}
		return null;
	}
	function getDetailsByElement($xsdmf_element)
	{
		$mc = $this->getMatchCols();
		foreach ($mc as $xsdmf) {
			if ($xsdmf['xsdmf_element'] == $xsdmf_element && !$xsdmf['xsdmf_is_key'] && !empty ($xsdmf['xsdmf_id'])) {
				return $xsdmf;
			}
		}
		return null;
	}

	/**
	 * Finds an xsdmf_id by element name within a sub-looping element.
	 * @param string $loop_base_element - the element that is the base of the sub looping mapping
	 * @param string $loop_name - title of the sub looping element
	 * @param string $element - element to find within the sub looping element
	 * @return integer The xsdmf_id or a negative number if there was an error or the item isn't mapped
	 */
	function getXSDMF_ID_ByElementInSubElement($loop_base_element, $loop_name, $element)
	{
		// use the static function because it excludes members of sublooping elements - so will get the base
		// sublooping element even if there are mappings on the base
		$sub_xsdmf_id = $this->getSELBaseXSDMF_IDByElement($loop_base_element);
		if (!empty($sub_xsdmf_id)) {
			$subs = XSD_Loop_Subelement::getSimpleListByXSDMF($sub_xsdmf_id);
		}
		if (!empty($subs)) {
			foreach ($subs as $sub) {
				if ($sub['xsdsel_title'] == $loop_name) {
					$sub_id = $sub['xsdsel_id'];
				}
			}
		}
		if (!empty($sub_id)) {
			$xsdmf_id = $this->getXSDMF_IDBySELXDIS_ID($element, $sub_id);
			return $xsdmf_id;
		} else {
			return -1;
		}
	}

	function getSELBaseXSDMF_IDByElement($loop_base_element, $xdis_ids = '')
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (empty($xdis_ids)) {
			$xdis_str = $this->xdis_str;
		} else {
			$xdis_str = $xdis_ids;
		}

		$stmt = "SELECT
		                   m1.xsdmf_id
		                FROM
		                    " . APP_TABLE_PREFIX . "xsd_display_matchfields m1
		                WHERE
		                    m1.xsdmf_xdis_id in (".$xdis_str.")
							AND m1.xsdmf_element = ".$db->quote($loop_base_element)."
							AND m1.xsdmf_xsdsel_id IS NULL
							";

		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return $res;
	}
}
