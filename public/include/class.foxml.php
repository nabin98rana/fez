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

include_once(APP_INC_PATH . "class.controlled_vocab.php");
/**
 * Handles the manipulation of foxml
 */
class Foxml
{

	/**
	 * Method used when converting from a posted form of array variables back into an XML object for HTML text form elements.
	 *
	 * @access  public
	 * @param   string $attrib_value Passed by reference
	 * @param   array $indexArray Passed by reference, The array of xsdmf_ids and values being built up to go into the Fez Index
	 * @param   string $pid The persistent identifier
	 * @param   integer $parent_sel_id The parent elements sublooping element ID
	 * @param   integer $xdis_id The current XSD Display ID
	 * @param   array $xsdmf_details The current XSD matching field details
	 * @param   array $xsdmf_details_ref The current XSD matching field details for XSD References
	 * @param   integer $attrib_loop_index The current index of an attribute loop, if inside an attribute loop.
	 * @param   string $element_prefix eg OAI_DC:, FOXML: etc
	 * @param   string $i The current element name
	 * @return  void ($attrib_value and $indexArray passed by reference)
	 */
	function handleTextInstance(&$attrib_value, &$indexArray, $pid, $parent_sel_id, $xdis_id,
	$xsdmf_id, $xsdmf_details, $xsdmf_details_ref, $attrib_loop_index, $element_prefix, $i, &$xmlObj, $tagIndent)
	{
		$full_attached_attribute = "";
		// The attrib value is escaped for XML generically at the end of this function.  Use the following flag
		// if there is some special case for the escaping
		$escaped_attrib_value = false;
		// echo "attrib_value = $attrib_value and XSDMF = $xsdmf_id AND ID REF = ".$xsdmf_details_ref['xsdmf_id']." AND attrib loop ID = ".$attrib_loop_index."<br />\n";
		if ($xsdmf_details['xsdmf_html_input'] == 'xsdmf_id_ref') {
			// value is a reference that we have to look up
			if (is_numeric($attrib_loop_index)
			&& (is_array(@$_POST['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']]))) {
				// the value is one of many in an attribute loop
				$attrib_value = Misc::addPrefix(
				$_POST['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']][$attrib_loop_index],
				$xsdmf_details['xsdmf_value_prefix']);
				array_push($indexArray, array($pid, $xsdmf_details_ref['xsdmf_indexed'], $xsdmf_id,
				$xdis_id, $parent_sel_id, $xsdmf_details_ref['xsdmf_data_type'], $attrib_value));
			} else {
				// lookup the value
				@$attrib_value = Misc::addPrefix($_POST['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']],
				$xsdmf_details_ref['xsdmf_value_prefix']);
				if ($attrib_value == "&nbsp;" || $attrib_value == "<br />") {
					$attrib_value = "";
				}
				$attrib_value = str_replace('<br type="_moz" />', '', $attrib_value);
				array_push($indexArray, array($pid, $xsdmf_details_ref['xsdmf_indexed'], $xsdmf_id,
				$xdis_id, $parent_sel_id, $xsdmf_details_ref['xsdmf_data_type'], $attrib_value));
			}
		} else {
			if (is_numeric($attrib_loop_index) && (@is_array($_POST['xsd_display_fields'][$xsdmf_id]))
			&& ($xsdmf_details['xsdmf_multiple'] == 1) && ($xsdmf_details['xsdsel_type'] == 'attributeloop')) {
				// multiple attribute loop
				// attached matching fields
				$full_attached_attribute = "";
				if (is_numeric($xsdmf_details['xsdmf_attached_xsdmf_id'])) {
					//						$post_idx = 'xsd_display_fields_'.$xsdmf_details['xsdmf_attached_xsdmf_id']
					//							.'_'.$loop_count;
					$post_idx = 'xsd_display_fields_'.$xsdmf_details['xsdmf_attached_xsdmf_id']
					.'_'.($attrib_loop_index);

					$attached_value = @$_POST[$post_idx];

					if ( (is_numeric($attached_value) && ($attached_value != -1))
					|| (!is_numeric($attached_value) && !empty($attached_value)) ) {

						$xsdmf_details_attached = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_details['xsdmf_attached_xsdmf_id']);
						$attached_xsd_element = substr($xsdmf_details_attached['xsdmf_element'],
						(strrpos($xsdmf_details_attached['xsdmf_element'], "!") + 1));


						$prefix_xsd_element = substr($xsdmf_details_attached['xsdmf_element'],
						0, (strrpos($xsdmf_details_attached['xsdmf_element'], "!")));
						$full_attached_attribute = ' '.$attached_xsd_element.'="'.$attached_value.'"';
					}
				}
				$xsd_element_only = substr($prefix_xsd_element, strrpos($prefix_xsd_element, "!"));
				if ($xsdmf_details['xsdmf_element'] != $prefix_xsd_element) { // If the attribute to add is not connected to the current element, search for last instance of the element and manually insert the attribute there
					if (trim($xsd_element_only != "") && trim($full_attached_attribute) != "") {
						FOXML::addAttributeToParent($xmlObj, $xsd_element_only, $full_attached_attribute);
					}
				} else { //otherwise it adds onto the current element

				}
				$attrib_value = Misc::addPrefix($_POST['xsd_display_fields'][$xsdmf_id][$attrib_loop_index],
				$xsdmf_details['xsdmf_value_prefix']);
				array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id,
				$xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
					
				/*					$multiple_element = $_POST['xsd_display_fields'][$xsdmf_id][$attrib_loop_index];
					if (!empty($multiple_element)) {
					if ($attrib_value == "") {
					$attrib_value = Misc::addPrefix($multiple_element,
					$xsdmf_details['xsdmf_value_prefix']);
					array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'],
					$xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'],
					Misc::addPrefix($multiple_element,$xsdmf_details['xsdmf_value_prefix'])));
					} else {
					// add that attribute!
					if (!is_numeric(strpos($i, ":"))) {
					$attrib_value .= $element_prefix.$i.$full_attached_attribute;
					} else {
					$attrib_value .= $i.$full_attached_attribute;
					}
					array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'],
					$xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'],
					Misc::addPrefix($multiple_element, $xsdmf_details['xsdmf_value_prefix'])));
					}
					} */

					
			} elseif ($xsdmf_details['xsdmf_multiple'] != 1) {
				// simple single instance - just get the value
				//                $attrib_value = Misc::addPrefix(@$_POST['xsd_display_fields'][$xsdmf_id],
				//                        $xsdmf_details['xsdmf_value_prefix']);
				$attrib_value = @$_POST['xsd_display_fields'][$xsdmf_id];
				if ($attrib_value == "&nbsp;" || $attrib_value == "<br />") {
					$attrib_value = "";
				}
				$attrib_value = str_replace('<br type="_moz" />', '', $attrib_value);
				array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'],
				$xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
			} elseif ($xsdmf_details['xsdmf_multiple'] == 1) {
				// multiple input fields
				if (@is_array($_POST['xsd_display_fields'][$xsdmf_id])) {
					$loop_count = 0;
					foreach ($_POST['xsd_display_fields'][$xsdmf_id] as $multiple_element) {
						// attached matching fields
						$full_attached_attribute = "";
						if (is_numeric($xsdmf_details['xsdmf_attached_xsdmf_id'])) {
							$post_idx = 'xsd_display_fields_'.$xsdmf_details['xsdmf_attached_xsdmf_id']
							.'_'.$loop_count;
							$attached_value = @$_POST[$post_idx];
							// if it's numeric, then a -1 means we should drop it
							// if it's not numeric but not empty then let it through
							if ( (is_numeric($attached_value) && ($attached_value != -1))
							|| (!is_numeric($attached_value) && !empty($attached_value)) ) {
								$xsdmf_details_attached = XSD_HTML_Match::getDetailsByXSDMF_ID(
								$xsdmf_details['xsdmf_attached_xsdmf_id']);
								$attached_xsd_element = substr($xsdmf_details_attached['xsdmf_element'],
								(strrpos($xsdmf_details_attached['xsdmf_element'], "!") + 1));
								$prefix_xsd_element = substr($xsdmf_details_attached['xsdmf_element'],
								0, (strrpos($xsdmf_details_attached['xsdmf_element'], "!")));

								$full_attached_attribute = ' '.$attached_xsd_element.'="'.$attached_value.'"';
							}
						}
						if ($xsdmf_details['xsdmf_element'] != $prefix_xsd_element) { // if the attribute doesn't belong on this element then clear it
							$full_attached_attribute = "";
						}
						if (!empty($multiple_element)) {
							if ($attrib_value == "") {
								$attrib_value = Misc::addPrefix($multiple_element,
								$xsdmf_details['xsdmf_value_prefix']);
								array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'],
								$xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'],
								Misc::addPrefix($multiple_element,$xsdmf_details['xsdmf_value_prefix'])));
							} else {
								// Give a tag to each value, eg DC language - english & french need own language tags
								// close the previous
								if (!is_numeric(strpos($i, ":"))) {
									$attrib_value .= "\n".$tagIndent."</".$element_prefix.$i.">\n";
								} else {
									$attrib_value .= "\n".$tagIndent."</".$i.">\n";
								}
								//open a new tag
								if (!is_numeric(strpos($i, ":"))) {
									$attrib_value .= "\n".$tagIndent."<".$element_prefix.$i.$full_attached_attribute;
								} else {
									$attrib_value .= "\n".$tagIndent."<".$i.$full_attached_attribute;
								}
								//finish the new open tag
								if ($xsdmf_details['xsdmf_valueintag'] == 1) {
									$attrib_value .= ">\n";
								} else {
									$attrib_value .= "/>\n";
								}
								$attrib_value .= htmlspecialchars(Misc::addPrefix($multiple_element,
								$xsdmf_details['xsdmf_value_prefix']));
								$escaped_attrib_value = true;
								array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'],
								$xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'],
								Misc::addPrefix($multiple_element, $xsdmf_details['xsdmf_value_prefix'])));
							}
						}
						$loop_count++;
					}
				}
			}
		}
		if (!$escaped_attrib_value) {
			$attrib_value = htmlspecialchars($attrib_value);
			$escaped_attrib_value = true;
		}
	}

	/**
	 * Method used when converting from a posted form of array variables back into an XML object for static form elements.
	 *
	 * @access  public
	 * @param   string $attrib_value Passed by reference
	 * @param   array $indexArray Passed by reference, The array of xsdmf_ids and values being built up to go into the Fez Index
	 * @param   string $pid The persistent identifier
	 * @param   integer $parent_sel_id The parent elements sublooping element ID
	 * @param   integer $xdis_id The current XSD Display ID
	 * @param   array $xsdmf_details The current XSD matching field details
	 * @param   array $xsdmf_details_ref The current XSD matching field details for XSD References
	 * @param   integer $attrib_loop_index The current index of an attribute loop, if inside an attribute loop.
	 * @param   string $element_prefix eg OAI_DC:, FOXML: etc
	 * @param   string $i The current element name
	 * @param   string $created_date
	 * @param   string $updated_date
	 * @param   integer $file_downloads
	 * @param   integer $top_xdis_id
	 * @return  string $xmlObj The xml object, plus the indexArray is passed back by reference
	 */
	function handleStaticInstance(&$attrib_value, &$indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details,  $attrib_loop_index, $element_prefix, $i, $created_date, $updated_date, $depositor, $file_downloads, $top_xdis_id, $assign_usr_id=null, $assign_grp_id=null) 
	{
		if ($xsdmf_details['xsdmf_fez_variable'] == "pid") {
			$attrib_value = $pid;
		} elseif ($xsdmf_details['xsdmf_fez_variable'] == "created_date") {
			$attrib_value = $created_date;
		} elseif ($xsdmf_details['xsdmf_fez_variable'] == "updated_date") {
			$attrib_value = $updated_date;
		} elseif ($xsdmf_details['xsdmf_fez_variable'] == "usr_id") {
			$attrib_value = $depositor;
		} elseif ($xsdmf_details['xsdmf_fez_variable'] == "file_downloads") {
			$attrib_value = $file_downloads;
		} elseif ($xsdmf_details['xsdmf_fez_variable'] == "xdis_id") {
			$attrib_value = $top_xdis_id;
		} elseif ($xsdmf_details['xsdmf_fez_variable'] == "assigned_usr_id") {
			$attrib_value = $assign_usr_id[0];
		} elseif ($xsdmf_details['xsdmf_fez_variable'] == "assigned_grp_id") {
			$attrib_value = $assign_grp_id;
		} elseif ($xsdmf_details['xsdmf_smarty_variable'] != "") {
			$return = Misc::getPostedDate($xsdmf_details['xsdmf_attached_xsdmf_id']);
			$dateType = $return['dateType'];
			eval("\$attrib_value = ".$xsdmf_details['xsdmf_smarty_variable'].";");
			$attrib_value = Misc::addPrefix($attrib_value,$xsdmf_details['xsdmf_value_prefix']);
		} else {
			if (is_numeric($xsdmf_details['xsdsel_attribute_loop_xsdmf_id'])) {
				$loop_attribute_xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_details['xsdsel_attribute_loop_xsdmf_id']);
				if (($xsdmf_details['xsdmf_element'] == '!datastream!ID') && (($xsdmf_details['xsdsel_title'] == 'File_Attachment') || ($xsdmf_details['xsdsel_title'] == 'Link')) && ($loop_attribute_xsdmf_details['xsdmf_multiple'] == 1)) {
					$extra = $attrib_loop_index;
				} else {
					$extra = "";
				}
			} else {
				$extra = "";
			}
			$attrib_value = $xsdmf_details['xsdmf_static_text'].$extra;
		}
		$attrib_value = Misc::addPrefix($attrib_value,$xsdmf_details['xsdmf_value_prefix']);
		array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
	}

	/**
	 * Method used when converting from a posted form of array variables back into an XML object for HTML multipe form elements.
	 *
	 * @access  public
	 * @param   string $attrib_value Passed by reference
	 * @param   array $indexArray Passed by reference, The array of xsdmf_ids and values being built up to go into the Fez Index
	 * @param   string $pid The persistent identifier
	 * @param   integer $parent_sel_id The parent elements sublooping element ID
	 * @param   integer $xdis_id The current XSD Display ID
	 * @param   integer $xdis_id The current XSD Matching Field ID
	 * @param   array $xsdmf_details The current XSD matching field details
	 * @param   array $xsdmf_details_ref The current XSD matching field details for XSD References
	 * @param   integer $attrib_loop_index The current index of an attribute loop, if inside an attribute loop.
	 * @param   string $element_prefix eg OAI_DC:, FOXML: etc
	 * @param   string $i The current element name
	 * @param   string $xmlObj The current xmlObj string as it currently stands
	 * @return  void ($attrib_value and $indexArray passed by reference)
	 */
	function handleMultipleInstance(&$attrib_value, &$indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $xsdmf_details_ref = array(), $attrib_loop_index, $element_prefix, $i, $xmlObj, $tagIndent)
	{
		//		if ($xsdmf_details['xsdmf_html_input'] == 'author_selector') {

		//		} else {
		$loop_count = 0;
		if (is_array(@$_POST['xsd_display_fields'][$xsdmf_id])) {

			//				echo "attrib_value = $attrib_value and XSDMF = $xsdmf_id AND ID REF = ".$xsdmf_details_ref['xsdmf_id']." AND attrib loop ID = ".$attrib_loop_index."<br />\n";
			if ($xsdmf_details['xsdsel_type'] == 'attributeloop' && is_numeric($attrib_loop_index) && (@is_array($_POST['xsd_display_fields'][$xsdmf_id]))
			) {
				//							echo "made it in for $xsdmf_id\n";
				/*					if (!is_numeric(strpos($i, ":"))) {
				 $attrib_value .= "</".$element_prefix.$i.">\n";
					} else {
					$attrib_value .= "</".$i.">\n";
					} */
				if ((($xsdmf_details['xsdmf_cvo_save_type'] == 1) && empty($xsdmf_details_ref)) || (($xsdmf_details_ref['xsdmf_cvo_save_type'] == 1) && !empty($xsdmf_details_ref))) {
					$cv_title = Controlled_Vocab::getTitle($_POST['xsd_display_fields'][$xsdmf_id][$attrib_loop_index]);
					$attrib_value = $cv_title;
					$attrib_value = Misc::addPrefix($attrib_value,$xsdmf_details['xsdmf_value_prefix']);
					array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
				} else {
					$attrib_value = $_POST['xsd_display_fields'][$xsdmf_id][$attrib_loop_index];
					$attrib_value = Misc::addPrefix($attrib_value,$xsdmf_details['xsdmf_value_prefix']);
					array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
					//						echo $xmlObj."\n\n";
				}
			} else {
				foreach ($_POST['xsd_display_fields'][$xsdmf_id] as $multiple_element) {
					if (!empty($multiple_element)) {
						if ($attrib_value == "") {
							if ($xsdmf_details['xsdmf_smarty_variable'] == ""
							&& $xsdmf_details['xsdmf_html_input'] != 'contvocab_selector') {
								$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
								$attrib_value = Misc::addPrefix($attrib_value, $xsdmf_details['xsdmf_value_prefix']);
								array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
							} elseif  ($xsdmf_details['xsdmf_html_input'] == 'contvocab_selector') {
								if ((($xsdmf_details['xsdmf_cvo_save_type'] == 1) && empty($xsdmf_details_ref)) || (($xsdmf_details_ref['xsdmf_cvo_save_type'] == 1) && !empty($xsdmf_details_ref))) {
									$cv_title = Controlled_Vocab::getTitle($multiple_element);
									$attrib_value = $cv_title;
									$attrib_value = Misc::addPrefix($attrib_value,
									$xsdmf_details['xsdmf_value_prefix']);
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
								} else {
									$attrib_value = $multiple_element;
									$attrib_value = Misc::addPrefix($attrib_value,
									$xsdmf_details['xsdmf_value_prefix']);
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
								}
							} else {
								//							echo $xsdmf_id." - $multiple_element <br />\n";
								$attrib_value = $multiple_element;
								$attrib_value = Misc::addPrefix($attrib_value,
								$xsdmf_details['xsdmf_value_prefix']);
								array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
							}
						} else {
							// Give a tag to each value, eg DC language - english & french need own language tags
							// close the previous

							if (!is_numeric(strpos($i, ":"))) {
								$attrib_value .= "\n".$tagIndent."</".$element_prefix.$i.">\n";
							} else {
								$attrib_value .= "\n".$tagIndent."</".$i.">\n";
							}
							//open a new tag
							if (!is_numeric(strpos($i, ":"))) {
								$attrib_value .= "\n".$tagIndent."<".$element_prefix.$i;
							} else {
								$attrib_value .= "\n".$tagIndent."<".$i;
							}
							//finish the new open tag
							if ($xsdmf_details['xsdmf_valueintag'] == 1) {
								$attrib_value .= ">\n";
							} else {
								$attrib_value .= "/>\n";
							}
							if ($xsdmf_details['xsdmf_smarty_variable'] == ""
							&& $xsdmf_details['xsdmf_html_input'] != 'contvocab_selector') {
									
								$new_attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
								$new_attrib_value = Misc::addPrefix($new_attrib_value,
								$xsdmf_details['xsdmf_value_prefix']);
								$attrib_value .= $new_attrib_value;
								array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $new_attrib_value));
							} else {
								$multiple_element = Misc::addPrefix($multiple_element,
								$xsdmf_details['xsdmf_value_prefix']);
								$attrib_value .= $multiple_element;
								array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
							}
								
						}
					}
				} // end of foreach loop
			}
		}
		//		}
	}

	// Go back up the existing XML object string to the last found $parent_element and add the given attribute
	function addAttributeToParent(&$xmlObj, $parent_element, $full_attached_attibute) 
	{
		$parent_element = trim(str_replace("!", "", $parent_element));
		//		$parentLocation = strrpos($xmlObj, $parent_element.">"); // find the start of the last parent element
		$parentLocation = strrpos($xmlObj, "<".$parent_element); // find the start of the last parent element
		if (!is_numeric($parentLocation)) { // if you can't find the element with no attributes, see if one exists with an attribute (a space after the element name)
			$parentLocation = strrpos($xmlObj, $parent_element." "); // find the start of the last parent element
		}
		$parentEndTag = strpos($xmlObj, ">", $parentLocation); // find the end of the parent element eg the next close tag bracket after the location
		$endContent = substr($xmlObj, $parentEndTag); // save everything after the point of insertion for adding back on later
		$xmlObj = substr($xmlObj, 0, $parentEndTag);  // prune the xml string so you can add the attribute
		$xmlObj .= " ".$full_attached_attibute." ".$endContent; // Add the attribute and the endContent and your done.

	}


	/**
	 * Method used when converting from a posted form of array variables back into an XML object for HTML text form elements.
	 *
	 * Developer Note: This is a recursive function passing variables by reference.
	 *
	 * @access  public
	 * @param   string $attrib_value Passed by reference
	 * @param   array $a The XSD Schema array to loop through
	 * @param   string $xmlObj The XML object being built - now passed by reference
	 * @param   string $element_prefix eg OAI_DC:, FOXML: etc
	 * @param   string $sought_node_type eg attributes
	 * @param   string $tagIndent How much to indent the text for the XML, possibly redundant as we are using Tidy to make the XML nice after this function finishes
	 * @param   integer $parent_sel_id The parent elements sublooping element ID
	 * @param   integer $xdis_id The current XSD Display ID
	 * @param   string $pid The persistent identifier
	 * @param   integer $top_xdis_id
	 * @param   integer $attrib_loop_index The current index of an attribute loop, if inside an attribute loop.
	 * @param   array $indexArray Passed by reference, The array of xsdmf_ids and values being built up to go into the Fez Index
	 * @param   integer $file_downloads
	 * @param   string $created_date
	 * @param   string $updated_date
	 * @return  string $xmlObj The xml object, plus the indexArray is passed back by reference
	 */
	function array_to_xml_instance($a, &$xmlObj="", $element_prefix, $sought_node_type="", $tagIndent="", $parent_sel_id="", $xdis_id, $pid, $top_xdis_id, $attrib_loop_index="", &$indexArray=array(), $file_downloads=0, $created_date, $updated_date, $depositor, $assign_usr_id=null, $assign_grp_id=null)
	{
		//        $tagIndent .= "    ";
		$tagIndent = "";
		// *** LOOP THROUGH THE XSD ARRAY
		foreach ($a as $i => $j) {
			if (is_array($j)) {
				// *** LOOPING THROUGH THE XML ATTRIBUTES
				if ($sought_node_type == 'attributes') {
					if ((!empty($j['fez_nodetype'])) && (!empty($j['fez_hyperlink']))) {
						if ($j['fez_nodetype'] == 'attribute') {
							if (is_numeric($parent_sel_id)) {

								$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_ID(urldecode($j['fez_hyperlink']), $parent_sel_id, $xdis_id);
							} else {
								$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement(urldecode($j['fez_hyperlink']), $xdis_id);
							}
								
							$attrib_value = "";
							if (is_numeric($xsdmf_id)) { // only add the attribute if there is an xsdmf set against it
								$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
								if ($xsdmf_details['xsdmf_enforced_prefix']) {
									$element_prefix = $xsdmf_details['xsdmf_enforced_prefix'];
								}
								if ($xsdmf_details['xsdmf_html_input'] == 'date') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
									$dateReturn = Misc::getPostedDate($xsdmf_id);
									$attrib_value = $dateReturn['value'];
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
								} elseif ($xsdmf_details['xsdmf_html_input'] == 'combo') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
									if ($xsdmf_details['xsdmf_smarty_variable'] == "" && $xsdmf_details['xsdmf_use_parent_option_list'] == 0) {
										$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($_POST['xsd_display_fields'][$xsdmf_id]);
									} elseif ($xsdmf_details['xsdmf_use_parent_option_list'] == 1) {
										$attrib_value = $_POST['xsd_display_fields'][$xsdmf_id];
									} else {
										if ($_POST['cat'] != 'update_security' && $xsdmf_details['xsdmf_cso_value'] == 'checked') { // special exception for non-security forms creation of fezacml
											$attrib_value = "on";
										} else {
											$attrib_value = "off";
										}
									}
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($_POST['xsd_display_fields'][$xsdmf_id])));
								} elseif ($xsdmf_details['xsdmf_html_input'] == 'checkbox') {
									$checkbox_value = Misc::checkBox($_POST['xsd_display_fields'][$xsdmf_id]);
									if ($checkbox_value == 1) {
										$attrib_value = "on";
									} else {
										$attrib_value = "off";
									}
								} elseif ($xsdmf_details['xsdmf_html_input'] == 'contvocab' || $xsdmf_details['xsdmf_html_input'] == 'contvocab_selector') {
									Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, array(), $attrib_loop_index, $element_prefix, $i, $xmlObj, $tagIndent);
								} elseif ($xsdmf_details['xsdmf_html_input'] == 'multiple' || $xsdmf_details['xsdmf_html_input'] == 'dual_multiple' || $xsdmf_details['xsdmf_html_input'] == 'customvocab_suggest') {
									Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, array(),$attrib_loop_index, $element_prefix, $i, $xmlObj, $tagIndent);
								} elseif ($xsdmf_details['xsdmf_html_input'] == 'xsdmf_id_ref') { // this assumes the xsdmf_id_ref will only refer to an xsdmf_id which is a text/textarea/combo/multiple, will have to modify if we need more
									$xsdmf_details_ref = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_details['xsdmf_id_ref']);
									if ($xsdmf_details_ref['xsdmf_html_input'] == 'date') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
										$dateReturn = Misc::getPostedDate($xsdmf_details['xsdmf_id_ref']);
										$attrib_value = $dateReturn['value'];
										array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details_ref['xsdmf_data_type'], $attrib_value));
									} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'combo') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
										if ($xsdmf_details_ref['xsdmf_smarty_variable'] == "" && $xsdmf_details_ref['xsdmf_use_parent_option_list'] == 0) {
											$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($_POST['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']]);
										} elseif ($xsdmf_details_ref['xsdmf_use_parent_option_list'] == 1) {
											$attrib_value = $_POST['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']];
										} else {
											$attrib_value = $_POST['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']];
										}
										//                                        $attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($_POST['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']]);
										array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($_POST['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']])));
									} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'checkbox') {
										$checkbox_value = Misc::checkBox($_POST['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']]);
										if ($checkbox_value == 1) {
											$attrib_value = "on";
										} else {
											if ($_POST['cat'] != 'update_security' && $xsdmf_details_ref['xsdmf_cso_value'] == 'checked') { // special exception for non-security forms creation of fezacml
												$attrib_value = "on";
											} else {
												$attrib_value = "off";
											}
										}
									} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'contvocab' || $xsdmf_details_ref['xsdmf_html_input'] == 'contvocab_selector') {
										Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_details_ref['xsdmf_id'], $xsdmf_details_ref, $xsdmf_details, $attrib_loop_index, $xsdmf_details_ref['xsdmf_element_prefix'], $i, $xmlObj, $tagIndent);
									} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'multiple'  || $xsdmf_details_ref['xsdmf_html_input'] == 'dual_multiple' || $xsdmf_details_ref['xsdmf_html_input'] == 'customvocab_suggest') {
										Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_details_ref['xsdmf_id'], $xsdmf_details_ref,  $xsdmf_details, $attrib_loop_index, $xsdmf_details_ref['xsdmf_element_prefix'], $i, $xmlObj, $tagIndent);
									} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'text' || $xsdmf_details_ref['xsdmf_html_input'] == 'rich_text' || $xsdmf_details_ref['xsdmf_html_input'] == 'textarea'  || $xsdmf_details_ref['xsdmf_html_input'] == 'hidden') {
										Foxml::handleTextInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $xsdmf_details_ref, $attrib_loop_index, $element_prefix, $i, $xmlObj, $tagIndent);
									if ($xsdmf_details_ref['xsdmf_element'] = '!objectProperties!property!VALUE') { //Due to a limitation in Fedora, Object labels cannot be greater than 255 chars or they will cause an ingest exception
										$attrib_value = substr($attrib_value, 0, 255);                                        }
									}
								} elseif ($xsdmf_details['xsdmf_html_input'] == 'static') {
									Foxml::handleStaticInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i, $created_date, $updated_date, $depositor, $file_downloads, $top_xdis_id, $assign_usr_id, $assign_grp_id);
								} elseif ($xsdmf_details['xsdmf_html_input'] == 'dynamic') {
									//                                 eval("\$attrib_value = \$xsdmf_details['xsdmf_dynamic_text'];");
									$attrib_value = Misc::addPrefix($_POST[$xsdmf_details['xsdmf_dynamic_text']], $xsdmf_details['xsdmf_value_prefix']);
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
								} elseif ($xsdmf_details['xsdmf_html_input'] == 'text' || $xsdmf_details['xsdmf_html_input'] == 'rich_text' || $xsdmf_details['xsdmf_html_input'] == 'textarea' || $xsdmf_details['xsdmf_html_input'] == 'hidden') {
									Foxml::handleTextInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $xsdmf_details_ref, $attrib_loop_index, $element_prefix, $i, $xmlObj, $tagIndent);
									//                                } elseif ($xsdmf_details['xsdmf_html_input'] == 'text') {
									//                                    $attrib_value = $xsdmf_details['xsdmf_value_prefix'] . @$_POST['xsd_display_fields'][$xsdmf_id];
									//                                    array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . @$_POST['xsd_display_fields'][$xsdmf_id]));
									//								Foxml::handleStaticInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i, $created_date, $updated_date, $file_downloads, $top_xdis_id);
								}
								//                                if (XSD_HTML_Match::isAttachedXSDMF($xsdmf_details['xsdmf_id']) != true) { // attached matching  //uncommented this if statement because its not what we want - CK 18/8/06
								if (trim($attrib_value) != "") {
									if ($xsdmf_details['xsdmf_enforced_prefix']) {
										$xmlObj .= ' '.$xsdmf_details['xsdmf_enforced_prefix'].$i.'="'. $attrib_value.'"';
									} else {
										$xmlObj .= ' '.$i.'="' . $attrib_value.'"';
									}
								}
							}
						}
					}
					// *** NOT AN ATTRIBUTE, SO LOOP THROUGH XML ELEMENTS
				} elseif (!empty($j['fez_hyperlink'])) {
					if (!isset($j['fez_nodetype']) || $j['fez_nodetype'] != 'attribute') {
						list($xmlObj, $xsdmf_id, $xsdmf_details, $element_prefix) = Foxml::outputElementValue($i, $j, $xmlObj, $element_prefix, $sought_node_type, $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads, $created_date, $updated_date, $depositor, $assign_usr_id, $assign_grp_id);
							
						if (is_numeric($xsdmf_id)) {
								
							// *** IF IT IS A LOOPING SUBELEMENT THEN GO RECURSIVE
							if ($xsdmf_details['xsdmf_html_input'] == 'xsd_loop_subelement') {
								$sel = XSD_Loop_Subelement::getListByXSDMF($xsdmf_id);
								if (count($sel) > 0) { //if there are xsd sublooping elements attached to it then prepare their headers and go recursive!
									foreach($sel as $sel_record) {
										if ($sel_record['xsdsel_type'] == 'attributeloop') {
											if (is_numeric($sel_record['xsdsel_attribute_loop_xsdmf_id']) && ($sel_record['xsdsel_attribute_loop_xsdmf_id'] != 0)) {
												$attrib_loop_details = XSD_HTML_Match::getDetailsByXSDMF_ID($sel_record['xsdsel_attribute_loop_xsdmf_id']);
												if ($attrib_loop_details['xsdmf_html_input'] == "file_input") {
													$attrib_loop_child = @$_FILES['xsd_display_fields']["name"][$sel_record['xsdsel_attribute_loop_xsdmf_id']];
												} else {
													$attrib_loop_child = @$_POST['xsd_display_fields'][$sel_record['xsdsel_attribute_loop_xsdmf_id']];
												}
											} else {
												$attrib_loop_child = "";
											}
											if (is_array($attrib_loop_child)) {
												$attrib_loop_count = count($attrib_loop_child);
											} else {
												$attrib_loop_count = 1;
											}
										} else {
											$attrib_loop_details = array();
											$attrib_loop_count = 1;
											$attrib_loop_child = "";
										}

										for ($x=0;$x<$attrib_loop_count;$x++) { // if this sel id is a loop of attributes then it will loop through each, otherwise it will just go through once
											if (
											($sel_record['xsdsel_type'] == 'hardset')
											|| ((@$attrib_loop_details['xsdmf_html_input'] != "file_input") && (@$attrib_loop_details['xsdmf_html_input'] != "text"))
											|| (is_array($attrib_loop_child) && ($attrib_loop_details['xsdmf_html_input'] == "file_input") && ($_FILES['xsd_display_fields']["name"][$sel_record['xsdsel_attribute_loop_xsdmf_id']][$x] != ""))
											|| (!is_array($attrib_loop_child) && ($attrib_loop_details['xsdmf_html_input'] == "file_input") && (!empty($_FILES['xsd_display_fields']["name"][$sel_record['xsdsel_attribute_loop_xsdmf_id']])))
											|| (is_array($attrib_loop_child) && ($attrib_loop_details['xsdmf_html_input'] == "text") && (!empty($_POST['xsd_display_fields'][$sel_record['xsdsel_attribute_loop_xsdmf_id']][$x])))
											|| (!is_array($attrib_loop_child) && ($attrib_loop_details['xsdmf_html_input'] == "text") && (!empty($_POST['xsd_display_fields'][$sel_record['xsdsel_attribute_loop_xsdmf_id']])))
											) {
												list($xmlObj, $sub_xsdmf_id, $sub_xsdmf_details, $sub_element_prefix) = Foxml::outputElementValue($i, $j, $xmlObj, $element_prefix, $sought_node_type, $tagIndent, $sel_record['xsdsel_id'], $xdis_id, $pid, $top_xdis_id, $x, $indexArray, $file_downloads, $created_date, $updated_date, $depositor, $assign_usr_id, $assign_grp_id);
												 
												if (!is_numeric($sub_xsdmf_id)) {
													// There are no mappings for the base sublooping element.
													// Need to just make a container element and check the attributes
													if (!is_numeric(strpos($i, ":"))) {
														$xmlObj .= "\n".$tagIndent."<".$element_prefix.$i;
													} else {
														$xmlObj .= "\n".$tagIndent."<".$i;
													}
													Foxml::array_to_xml_instance($j, $xmlObj, $element_prefix, "attributes", $tagIndent, $sel_record['xsdsel_id'], $xdis_id, $pid, $top_xdis_id, $x, $indexArray, $file_downloads, $created_date, $updated_date, $depositor, $assign_usr_id, $assign_grp_id);
													if ($xsdmf_details['xsdmf_valueintag'] == 1) {
														$xmlObj .= ">\n";
													} else {
														$xmlObj .= "/>\n";
													}
												}

												//get the elements and recurse further
												Foxml::array_to_xml_instance($j, $xmlObj, $element_prefix, "", $tagIndent, $sel_record['xsdsel_id'], $xdis_id, $pid, $top_xdis_id, $x, $indexArray, $file_downloads, $created_date, $updated_date, $depositor, $assign_usr_id, $assign_grp_id);

												if (!is_numeric(strpos($i, ":"))) {
													$xmlObj .= "\n".$tagIndent."</".$element_prefix.$i.">\n";
												} else {
													$xmlObj .= "\n".$tagIndent."</".$i.">\n";
												}
											} // end check for empty file posts
										} // end attrib for loop
									}
								}
							}
							// *** IF THERE ARE XSD RELATIONSHIPS ATTACHED TO IT THEN PREPARE THE HEADERS AND GO RECURSIVE
							$rel = XSD_Relationship::getListByXSDMF($xsdmf_id);
							if (count($rel) > 0) { //
								foreach($rel as $rel_record) {
									$tagIndent .= "";
									$xsd_id = XSD_Display::getParentXSDID($rel_record['xdis_id']);
									$xsd_str = Doc_Type_XSD::getXSDSource($xsd_id);
									$xsd_str = $xsd_str[0]['xsd_file'];
									$xsd_details = Doc_Type_XSD::getDetails($xsd_id);
									$xsd = new DomDocument();
									$xsd->loadXML($xsd_str);
									$xsd_element_prefix = $xsd_details['xsd_element_prefix'];
									$xsd_top_element_name = $xsd_details['xsd_top_element_name'];
									$xsd_extra_ns_prefixes = explode(",", $xsd_details['xsd_extra_ns_prefixes']); // get an array of the extra namespace prefixes
									$xml_schema = Misc::getSchemaAttributes($xsd, $xsd_top_element_name, $xsd_element_prefix, $xsd_extra_ns_prefixes);
									if ($xsd_element_prefix != "") {
										$xsd_element_prefix .= ":";
									}
									$array_ptr = array();
									Misc::dom_xsd_to_referenced_array($xsd, $xsd_top_element_name, $array_ptr, "", "", $xsd);
									$xmlObj .= "\n".$tagIndent."<".$xsd_element_prefix.$xsd_top_element_name." ";
									$xmlObj .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid);
									if (trim($xml_schema) != "") {
										$xmlObj .= $xml_schema;
									}
									Foxml::array_to_xml_instance($array_ptr, $xmlObj, $element_prefix,
										"attributes", $tagIndent, $sel_record['xsdrel_xdis_id'], $xdis_id, 
									$pid,$top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads,
									$created_date, $updated_date, $depositor, $assign_usr_id,
									$assign_grp_id);
									$xmlObj .= ">\n";
									Foxml::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", $tagIndent, "", $rel_record['xsdrel_xdis_id'], $pid, $top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads, $created_date, $updated_date, $depositor, $assign_usr_id, $assign_grp_id);
									$xmlObj .= "\n".$tagIndent."</".$xsd_element_prefix.$xsd_top_element_name.">\n";
								}
							}
							Foxml::array_to_xml_instance($j, $xmlObj, $element_prefix, "", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads, $created_date, $updated_date, $depositor, $assign_usr_id, $assign_grp_id);
							$xmlObj .= $tagIndent;
							if ($xsdmf_details['xsdmf_html_input'] != 'xsd_loop_subelement') { // subloop element attributes get treated differently
								if ($xsdmf_details['xsdmf_valueintag'] == 1) {
									if (!is_numeric(strpos($i, ":"))) {
										$xmlObj .= "\n".$tagIndent."</".$element_prefix.$i.">\n";
									} else {
										$xmlObj .= "\n".$tagIndent."</".$i.">\n";
									}
								}
							}
						}
					} else {
						Foxml::array_to_xml_instance($j, $xmlObj, $element_prefix, "", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads, $created_date, $updated_date, $depositor, $assign_usr_id, $assign_grp_id);
					} // new if is numeric
				} else {
					Foxml::array_to_xml_instance($j, $xmlObj, $element_prefix, "", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads, $created_date, $updated_date, $depositor, $assign_usr_id, $assign_grp_id);
				}
			}
		}

		return $xmlObj;
	}

	function outputElementValue($i, $j, &$xmlObj, $element_prefix, $sought_node_type, $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, $attrib_loop_index, &$indexArray, $file_downloads, $created_date, $updated_date, $depositor, $assign_usr_id, $assign_grp_id)
	{
		
		$xsdmf_details = array();
		if (is_numeric($parent_sel_id)) {
			$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_ID(urldecode($j['fez_hyperlink']), $parent_sel_id, $xdis_id);
		} else {
			$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement(urldecode($j['fez_hyperlink']), $xdis_id);
		}
		
		if (is_numeric($xsdmf_id)) { // if the xsdmf_id exists - then this is the only time we want to add to the xml instance object for non attributes

			$xmlObj .= $tagIndent;
			$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
				
			if ($xsdmf_details['xsdmf_enforced_prefix']) {
				$element_prefix = $xsdmf_details['xsdmf_enforced_prefix'];
			}
			// attached matching fields
			$full_attached_attribute = "";
			if (is_numeric($xsdmf_details['xsdmf_attached_xsdmf_id'])) {
				$loop_count = 0;
				$attached_value = @$_POST['xsd_display_fields_'.$xsdmf_details['xsdmf_attached_xsdmf_id'].'_'.$loop_count];
				if ( (is_numeric($attached_value) && ($attached_value != -1))
				|| (!is_numeric($attached_value) && !empty($attached_value)) ) {

					$xsdmf_details_attached = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_details['xsdmf_attached_xsdmf_id']);
					$attached_xsd_element = substr($xsdmf_details_attached['xsdmf_element'], (strrpos($xsdmf_details_attached['xsdmf_element'], "!") + 1));
					$prefix_xsd_element = substr($xsdmf_details_attached['xsdmf_element'], 0, (strrpos($xsdmf_details_attached['xsdmf_element'], "!")));
					if ($xsdmf_details['xsdmf_element'] != $prefix_xsd_element) { // if the attribute doesn't belong on this element then clear it
						$full_attached_attribute = "";
					} else {
						$full_attached_attribute = ' '.$attached_xsd_element.'="'.$attached_value.'"';
					}
				}
			}

			if ($xsdmf_details['xsdmf_html_input'] != 'xsd_loop_subelement') { // subloop element attributes get treated differently
				if (!is_numeric(strpos($i, ":"))) {
					$xmlObj .= "\n".$tagIndent."<".$element_prefix.$i.$full_attached_attribute;
				} else {
					$xmlObj .= "\n".$tagIndent."<".$i.$full_attached_attribute;
				}
				Foxml::array_to_xml_instance($j, $xmlObj, $element_prefix, "attributes", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads, $created_date, $updated_date, $depositor, $assign_usr_id, $assign_grp_id);
				if ($xsdmf_details['xsdmf_valueintag'] == 1) {
					$xmlObj .= ">\n";
				} else {
					$xmlObj .= "/>\n";
				}
			}
			$attrib_value = "";
			if ($xsdmf_details['xsdmf_html_input'] == 'date') {
				$dateReturn = Misc::getPostedDate($xsdmf_id);
				$attrib_value = $dateReturn['value'];
				array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
			} elseif ($xsdmf_details['xsdmf_html_input'] == 'combo') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
				if ($xsdmf_details['xsdmf_smarty_variable'] == "" && $xsdmf_details['xsdmf_use_parent_option_list'] == 0) {
					$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID(@$_POST['xsd_display_fields'][$xsdmf_id]);
				} elseif ($xsdmf_details['xsdmf_use_parent_option_list'] == 1) {
					$attrib_value = $_POST['xsd_display_fields'][$xsdmf_id];
				} else {
					$attrib_value = $_POST['xsd_display_fields'][$xsdmf_id];
				}
				array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
			} elseif ($xsdmf_details['xsdmf_html_input'] == 'checkbox') {
				$checkbox_value = Misc::checkBox($_POST['xsd_display_fields'][$xsdmf_id]);
				if ($checkbox_value == 1) {
					$attrib_value = "on";
				} else {
					if ($_POST['cat'] != 'update_security' && $xsdmf_details['xsdmf_cso_value'] == 'checked') { // special exception for non-security forms creation of fezacml
						$attrib_value = "on";
					} else {
						$attrib_value = "off";
					}
				}
			} elseif ($xsdmf_details['xsdmf_html_input'] == 'contvocab' || $xsdmf_details['xsdmf_html_input'] == 'contvocab_selector') {
				Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, array(), $attrib_loop_index, $element_prefix, $i, $xmlObj, $tagIndent);
			} elseif (($xsdmf_details['xsdmf_html_input'] == 'multiple' || $xsdmf_details['xsdmf_html_input'] == 'dual_multiple' || $xsdmf_details['xsdmf_html_input'] == 'customvocab_suggest') && isset($_POST['xsd_display_fields'][$xsdmf_id])) {
				Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, array(), $attrib_loop_index, $element_prefix, $i, $xmlObj, $tagIndent);
			} elseif ($xsdmf_details['xsdmf_html_input'] == 'xsdmf_id_ref') { // this assumes the xsdmf_id_ref will only refer to an xsdmf_id which is a text/textarea/combo/multiple, will have to modify if we need more
				$xsdmf_details_ref = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_details['xsdmf_id_ref']);
				if ($xsdmf_details_ref['xsdmf_html_input'] == 'date') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
					$dateReturn = Misc::getPostedDate($xsdmf_details['xsdmf_id_ref']);
					$attrib_value = $dateReturn['value'];
					array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details_ref['xsdmf_data_type'], $attrib_value));
				} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'combo') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
					if ($xsdmf_details_ref['xsdmf_smarty_variable'] == "" && $xsdmf_details_ref['xsdmf_use_parent_option_list'] == 0) {
						$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($_POST['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']]);
					} elseif ($xsdmf_details['xsdmf_use_parent_option_list'] == 1) {
						/*										$parent_option_xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_details['xsdmf_parent_option_xsdmf_id']);
						 if ($parent_option_xsdmf_details['xsdmf_html_input'] == 'text') {
						 $attrib_value = $_POST['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']];
						 } */
						$attrib_value = $_POST['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']];
					}
					array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details_ref['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($_POST['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']])));
				} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'checkbox') {
					$checkbox_value = Misc::checkBox($_POST['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']]);
					if ($checkbox_value == 1) {
						$attrib_value = "on";
					} else {
						if ($_POST['cat'] != 'update_security' && $xsdmf_details_ref['xsdmf_cso_value'] == 'checked') { // special exception for non-security forms creation of fezacml
							$attrib_value = "on";
						} else {
							if ($_POST['cat'] != 'update_security' && $xsdmf_details['xsdmf_cso_value'] == 'checked') { // special exception for non-security forms creation of fezacml
								$attrib_value = "on";
							} else {
								$attrib_value = "off";
							}
						}									}
				} elseif ($xsdmf_details['xsdmf_html_input'] == 'contvocab' || $xsdmf_details['xsdmf_html_input'] == 'contvocab_selector') {
					Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_details_ref['xsdmf_id'], $xsdmf_details_ref, $xsdmf_details, $attrib_loop_index, $xsdmf_details_ref['element_prefix'], $i, $xmlObj, $tagIndent);
				} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'multiple' || $xsdmf_details_ref['xsdmf_html_input'] == 'dual_multiple' || $xsdmf_details_ref['xsdmf_html_input'] == 'customvocab_suggest') {
					Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_details_ref['xsdmf_id'], $xsdmf_details_ref, $xsdmf_details, $attrib_loop_index, $xsdmf_details_ref['element_prefix'], $i, $xmlObj, $tagIndent);
				} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'file_input' || $xsdmf_details_ref['xsdmf_html_input'] == 'file_selector') {
					// check for file upload error
					if (is_numeric($attrib_loop_index)
					&& is_array($_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']])
					&& $_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']][$attrib_loop_index] > 0) {
						$fu_name = $_FILES["xsd_display_fields"]["name"][$xsdmf_details['xsdmf_id']][$attrib_loop_index];
						$fu_error = $_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']][$attrib_loop_index];
						Session::setMessage("File upload failed, file: ".$fu_name.", Error: ".Misc::fileUploadErr($fu_error));
					} elseif (!is_numeric($attrib_loop_index)
					&& !is_array($_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']])
					&& $_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']] > 0) {
						$fu_name = $_FILES["xsd_display_fields"]["name"][$xsdmf_details['xsdmf_id']];
						$fu_error = $_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']];
						Session::setMessage("File upload failed, file: ".$fu_name.", Error: ".Misc::fileUploadErr($fu_error));
					} else {
						if (is_numeric($attrib_loop_index) && is_array($_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']])) {
							//      $attrib_value = (fread(fopen($_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']][$attrib_loop_index], "r"), $_FILES["xsd_display_fields"]["size"][$xsdmf_details['xsdmf_id']][$attrib_loop_index]));
						} else {
							//      $attrib_value = (fread(fopen($_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']], "r"), $_FILES["xsd_display_fields"]["size"][$xsdmf_details['xsdmf_id']]));
						}
					}
				} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'text' || $xsdmf_details_ref['xsdmf_html_input'] == 'rich_text' || $xsdmf_details_ref['xsdmf_html_input'] == 'textarea' || $xsdmf_details_ref['xsdmf_html_input'] == 'hidden') {
					//                                    $xsdmf_details_ref = array();
					Foxml::handleTextInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $xsdmf_details_ref, $attrib_loop_index, $element_prefix, $i, $xmlObj, $tagIndent);
					if ($xsdmf_details_ref['xsdmf_element'] = '!objectProperties!property!VALUE') { //Due to a limitation in Fedora, Object labels cannot be greater than 255 chars or they will cause an ingest exception
						$attrib_value = substr($attrib_value, 0, 255);
					}
				}
			} elseif ($xsdmf_details['xsdmf_html_input'] == 'static') {
				Foxml::handleStaticInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i, $created_date, $updated_date, $depositor, $file_downloads, $top_xdis_id, $assign_usr_id, $assign_grp_id);
			} elseif ($xsdmf_details['xsdmf_html_input'] == 'dynamic') {
				//                            eval("\$attrib_value = \$xsdmf_details['xsdmf_dynamic_text'];");
				$attrib_value = Misc::addPrefix($_POST[$xsdmf_details['xsdmf_dynamic_text']], $xsdmf_details['xsdmf_value_prefix']);
				array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
			} elseif (($xsdmf_details['xsdmf_html_input'] == 'file_input' || $xsdmf_details['xsdmf_html_input'] == 'file_selector') && !empty($_FILES["xsd_display_fields"]["name"][$xsdmf_details['xsdmf_id']])) {
				if (is_numeric($attrib_loop_index)
				&& is_array($_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']])
				&& $_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']][$attrib_loop_index] > 0) {
					$fu_name = $_FILES["xsd_display_fields"]["name"][$xsdmf_details['xsdmf_id']][$attrib_loop_index];
					$fu_error = $_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']][$attrib_loop_index];
					Session::setMessage("File upload failed, file: ".$fu_name.", Error: ".Misc::fileUploadErr($fu_error));
				} elseif (!is_numeric($attrib_loop_index)
				&& !is_array($_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']])
				&& $_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']] > 0) {
					$fu_name = $_FILES["xsd_display_fields"]["name"][$xsdmf_details['xsdmf_id']];
					$fu_error = $_FILES["xsd_display_fields"]["error"][$xsdmf_details['xsdmf_id']];
					Session::setMessage("File upload failed, file: ".$fu_name.", Error: ".Misc::fileUploadErr($fu_error));
				} else {
					if (is_numeric($attrib_loop_index) && is_array($_FILES["xsd_display_fields"]["name"][$xsdmf_details['xsdmf_id']])) {
						if (!empty($_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']][$attrib_loop_index])) {
							//                                              $attrib_value = (fread(fopen($_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']][$attrib_loop_index], "r"), $_FILES["xsd_display_fields"]["size"][$xsdmf_details['xsdmf_id']][$attrib_loop_index]));
						}
					} else {
						//                                            $attrib_value = (fread(fopen($_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']], "r"), $_FILES["xsd_display_fields"]["size"][$xsdmf_details['xsdmf_id']]));
					}
				}

			} elseif ($xsdmf_details['xsdmf_html_input'] == 'text' || $xsdmf_details['xsdmf_html_input'] == 'rich_text' || $xsdmf_details['xsdmf_html_input'] == 'textarea' || $xsdmf_details['xsdmf_html_input'] == 'hidden') {
				//                                $xsdmf_details_ref = array();
				Foxml::handleTextInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $xsdmf_details_ref, $attrib_loop_index, $element_prefix, $i, $xmlObj, $tagIndent);
			} else { // not necessary in this side
				if ($xsdmf_details['xsdmf_multiple'] == 1) {
					Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_details['xsdmf_id'], $xsdmf_details, $xsdmf_details, $attrib_loop_index, $xsdmf_details['element_prefix'], $i, $xmlObj, $tagIndent);
				} else {
					$attrib_value = Misc::addPrefix(@$_POST['xsd_display_fields'][$xsdmf_id], $xsdmf_details['xsdmf_value_prefix']);
					array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], @$_POST['xsd_display_fields'][$xsdmf_id]));
				}
			}
			if (trim($attrib_value) != "") {
				$xmlObj .= $attrib_value; // The actual value to store inside the element tags, if one exists
			}
		}

		return array($xmlObj, $xsdmf_id, $xsdmf_details, $element_prefix);
	}


	function setDCTitle($title, $xmlObj)
	{
		$xmlObj = preg_replace('/<dc:title>.*?<\/dc:title>/s', "<dc:title>".$title."</dc:title>", $xmlObj);
		$xmlObj = preg_replace('/<dc:title\/>/', "<dc:title>".$title."</dc:title>", $xmlObj);
		return $xmlObj;
	}


	/**
	 * Method used to generate a FOXML template for an imported file.
	 *
	 * @access  public
	 * @param   string $pid The current persistent identifier
	 * @param   string $parent_pid The pid of the collection this will belong to.
	 * @param   string $filename The filename of the file being imported, including directory path
	 * @param   string $short_name The filename of the file being imported, without the directory path (basic filename)
	 * @param   string $xdis_id The XSD Display ID the object will have.
	 * @param   string $ret_id The object type ID the object will have.
	 * @param   string $sta_id The initial status ID the object will have.
	 * @param   string $depositor The fez user id of the depositor
	 * @return  string $xmlObj The xml object
	 */
	function GenerateSingleFOXMLTemplate($pid, $parent_pid, $label, $dctitle, $xdis_id, $ret_id, $sta_id, $depositor="") 
	{

		$created_date = Date_API::getFedoraFormattedDateUTC();
		$updated_date = $created_date;
		$depositor = Auth::getUserID();
		$xmlObj = '<?xml version="1.0" ?>
            <foxml:digitalObject PID="'.$pid.'"
            fedoraxsi:schemaLocation="info:fedora/fedora-system:def/foxml# http://www.fedora.info/definitions/1/0/foxml1-0.xsd" xmlns:fedoraxsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:foxml="info:fedora/fedora-system:def/foxml#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
            <foxml:objectProperties>
            <foxml:property NAME="http://www.w3.org/1999/02/22-rdf-syntax-ns#type" VALUE="FedoraObject"/>
            <foxml:property NAME="info:fedora/fedora-system:def/model#state" VALUE="Active"/>
            <foxml:property NAME="info:fedora/fedora-system:def/model#label" VALUE="'.$label.'"/>
            </foxml:objectProperties>
            <foxml:datastream ID="DC" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
            <foxml:datastreamVersion MIMETYPE="text/xml" ID="DC1.0" LABEL="Dublin Core Record">
            <foxml:xmlContent>
            <oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
            <dc:title>'.$dctitle.'</dc:title>
            <dc:creator/>
            <dc:subject/>
            <dc:description/>
            <dc:publisher/>
            <dc:contributor/>
            <dc:date/>
            <dc:type/>
            <dc:source/>
            <dc:language/>
            <dc:relation/>
            <dc:coverage/>
            <dc:rights/>
            </oai_dc:dc>
            </foxml:xmlContent>			
            </foxml:datastreamVersion>
            </foxml:datastream>
            <foxml:datastream ID="RELS-EXT" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
            <foxml:datastreamVersion MIMETYPE="text/xml" ID="RELS-EXT.0" LABEL="Relationships to other objects">
            <foxml:xmlContent>
            <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
            xmlns:rel="info:fedora/fedora-system:def/relations-external#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
            <rdf:description rdf:about="info:fedora/'.$pid.'">
            <rel:isMemberOf rdf:resource="info:fedora/'.$parent_pid.'"/>
            </rdf:description>
            </rdf:RDF>
            </foxml:xmlContent>
            </foxml:datastreamVersion>
            </foxml:datastream>
            <foxml:datastream ID="FezMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
            <foxml:datastreamVersion MIMETYPE="text/xml" ID="Fez1.0" LABEL="Fez extension metadata">
            <foxml:xmlContent>
            <FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
            <xdis_id>'.$xdis_id.'</xdis_id>
            <sta_id>'.$sta_id.'</sta_id>
            <ret_id>'.$ret_id.'</ret_id>
            <created_date>'.$created_date.'</created_date>					  
            <updated_date>'.$updated_date.'</updated_date>
            <depositor>'.$depositor.'</depositor>            
            </FezMD>
            </foxml:xmlContent>
            </foxml:datastreamVersion>
            </foxml:datastream>
            </foxml:digitalObject>
            ';
		return $xmlObj;
	}

	// NCName ::= (Letter | '_') (NCNameChar)
	// NCNameChar ::= Letter | Digit | '.' | '-' | '_' | CombiningChar | Extender
	function makeNCName($str) {
		$str = preg_replace('/[^\w\n\-\.]/', "_", $str);
		if (!preg_match('/^[a-zA-Z_]/', $str)) {
			// add an n to the front to make it a valid NCName
			$str= "n$str";
		}
		return $str;
	}
}
