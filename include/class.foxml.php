<?php

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
    function handleTextInstance(&$attrib_value, &$indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $xsdmf_details_ref, $attrib_loop_index, $element_prefix, $i) {
        global $HTTP_POST_VARS;
        if ($xsdmf_details['xsdmf_html_input'] == 'xsdmf_id_ref') { 
            if (is_numeric($attrib_loop_index) && (is_array($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']]))) {
                $attrib_value = $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']][$attrib_loop_index];
                array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']][$attrib_loop_index]));						
            } else {
                $attrib_value = $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']];
                array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']]));
            }
        } else {
            if (is_numeric($attrib_loop_index) && (@is_array($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id])) && ($xsdmf_details['xsdmf_multiple'] != 1)) {

                $attrib_value = $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id][$attrib_loop_index];
                array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id][$attrib_loop_index]));						
            } elseif ($xsdmf_details['xsdmf_multiple'] != 1) {
                $attrib_value = $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id];
                array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));						
            } elseif ($xsdmf_details['xsdmf_multiple'] == 1) {
                if (@is_array($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id])) {
                    foreach ($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id] as $multiple_element) {
                        if (!empty($multiple_element)) {
                            if ($attrib_value == "") {
                                $attrib_value = $multiple_element;
                                array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
                            } else {
                                // Give a tag to each value, eg DC language - english & french need own language tags
                                // close the previous
                                if (!is_numeric(strpos($i, ":"))) {
                                    $attrib_value .= "</".$element_prefix.$i.">\n";
                                } else {
                                    $attrib_value .= "</".$i.">\n";
                                }
                                //open a new tag
                                if (!is_numeric(strpos($i, ":"))) {
                                    $attrib_value .= "<".$element_prefix.$i;
                                } else {
                                    $attrib_value .= "<".$i;
                                } 
                                //finish the new open tag
                                if ($xsdmf_details['xsdmf_valueintag'] == 1) {
                                    $attrib_value .= ">\n";
                                } else {
                                    $attrib_value .= "/>\n";
                                }
                                $attrib_value .= $multiple_element;
                                array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
                            }
                        }
                    }
                }
            }
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
    function handleStaticInstance(&$attrib_value, &$indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details,  $attrib_loop_index, $element_prefix, $i, $created_date, $updated_date, $file_downloads, $top_xdis_id) {
        if ($xsdmf_details['xsdmf_fez_variable'] == "pid") {
            $attrib_value = $pid;
            array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $pid));
        } elseif ($xsdmf_details['xsdmf_fez_variable'] == "created_date") {
            $attrib_value = $created_date;
            array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $created_date));
        } elseif ($xsdmf_details['xsdmf_fez_variable'] == "updated_date") {
            $attrib_value = $updated_date;
            array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $updated_date));
        } elseif ($xsdmf_details['xsdmf_fez_variable'] == "file_downloads") {
            $attrib_value = $file_downloads;
            array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $file_downloads));
        } elseif ($xsdmf_details['xsdmf_fez_variable'] == "xdis_id") {
            $attrib_value = $top_xdis_id;
            array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $top_xdis_id));
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
            array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_static_text'].$extra));						
        }
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
     * @param   array $xsdmf_details The current XSD matching field details
     * @param   array $xsdmf_details_ref The current XSD matching field details for XSD References
     * @param   integer $attrib_loop_index The current index of an attribute loop, if inside an attribute loop.
     * @param   string $element_prefix eg OAI_DC:, FOXML: etc
     * @param   string $i The current element name 	 
     * @return  void ($attrib_value and $indexArray passed by reference)
     */
    function handleMultipleInstance(&$attrib_value, &$indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i) {
        global $HTTP_POST_VARS;
        foreach ($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id] as $multiple_element) {
            if ($attrib_value == "") {
                if ($xsdmf_details['xsdmf_smarty_variable'] == "" 
                        && $xsdmf_details['xsdmf_html_input'] != 'contvocab_selector') {
                    $attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
                    array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
                } else {
                    $attrib_value = $multiple_element;
                    array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
                }
            } else {
                // Give a tag to each value, eg DC language - english & french need own language tags
                // close the previous
                if (!is_numeric(strpos($i, ":"))) {
                    $attrib_value .= "</".$element_prefix.$i.">\n";
                } else {
                    $attrib_value .= "</".$i.">\n";
                }
                //open a new tag
                if (!is_numeric(strpos($i, ":"))) {
                    $attrib_value .= "<".$element_prefix.$i;
                } else {
                    $attrib_value .= "<".$i;
                } 
                //finish the new open tag
                if ($xsdmf_details['xsdmf_valueintag'] == 1) {
                    $attrib_value .= ">\n";
                } else {
                    $attrib_value .= "/>\n";
                }
                if ($xsdmf_details['xsdmf_smarty_variable'] == "") {
                    $attrib_value .= XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
                } else {
                    $attrib_value .= $multiple_element;
                    array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
                }	
            }
        } // end of foreach loop
    }

    /**
     * Method used when converting from a posted form of array variables back into an XML object for HTML text form elements.
     * 
     * Developer Note: This is a recursive function passing variables by reference.
     * 	 
     * @access  public
     * @param   string $attrib_value Passed by reference
     * @param   array $a The XSD Schema array to loop through
     * @param   string $xmlObj The XML object being built
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
    function array_to_xml_instance($a, $xmlObj="", $element_prefix, $sought_node_type="", $tagIndent="", $parent_sel_id="", $xdis_id, $pid, $top_xdis_id, $attrib_loop_index="", &$indexArray=array(), $file_downloads=0, $created_date, $updated_date) {
        global $HTTP_POST_VARS, $HTTP_POST_FILES; 
        $tagIndent .= "    ";
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
                                    $attrib_value = Misc::getPostedDate($xsdmf_id);
                                    array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
                                } elseif ($xsdmf_details['xsdmf_html_input'] == 'combo') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
                                    $attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]);
                                    array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id])));
                                } elseif ($xsdmf_details['xsdmf_html_input'] == 'contvocab' || $xsdmf_details['xsdmf_html_input'] == 'contvocab_selector') {
                                    Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i);
                                } elseif ($xsdmf_details['xsdmf_html_input'] == 'multiple') {
                                    Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i);
                                } elseif ($xsdmf_details['xsdmf_html_input'] == 'xsdmf_id_ref') { // this assumes the xsdmf_id_ref will only refer to an xsdmf_id which is a text/textarea/combo/multiple, will have to modify if we need more
                                    $xsdmf_details_ref = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_details['xsdmf_id_ref']);
                                    if ($xsdmf_details['xsdmf_html_input'] == 'date') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple							
                                        $attrib_value = Misc::getPostedDate($xsdmf_id);
                                        array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
                                    } elseif ($xsdmf_details['xsdmf_html_input'] == 'combo') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
                                        $attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']]);
                                        array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']])));									
                                    } elseif ($xsdmf_details['xsdmf_html_input'] == 'contvocab' || $xsdmf_details['xsdmf_html_input'] == 'contvocab_selector') {
                                        Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i);
                                    } elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'multiple') {
                                        Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i);
                                    } elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'text' || $xsdmf_details_ref['xsdmf_html_input'] == 'textarea') {
                                        Foxml::handleTextInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $xsdmf_details_ref, $attrib_loop_index, $element_prefix, $i);
                                    }
                                } elseif ($xsdmf_details['xsdmf_html_input'] == 'static') {
                                    Foxml::handleStaticInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i, $created_date, $updated_date, $file_downloads, $top_xdis_id);
                                } elseif ($xsdmf_details['xsdmf_html_input'] == 'dynamic') {
                                    $attrib_value = $HTTP_POST_VARS[$xsdmf_details['xsdmf_dynamic_text']];
                                    array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
                                } elseif ($xsdmf_details['xsdmf_html_input'] == 'text' || $xsdmf_details['xsdmf_html_input'] == 'textarea') {
                                    Foxml::handleTextInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $xsdmf_details_ref, $attrib_loop_index, $element_prefix, $i);
                                } else {
                                    $attrib_value = $xsdmf_details['xsdmf_value_prefix'] . @$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id];
                                    array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . @$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]));

                                    //								Foxml::handleStaticInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i, $created_date, $updated_date, $file_downloads, $top_xdis_id);
                                }
                                if ($xsdmf_details['xsdmf_enforced_prefix']) {
                                    $xmlObj .= ' '.$xsdmf_details['xsdmf_enforced_prefix'].$i.'="'.$xsdmf_details['xsdmf_value_prefix'] . $attrib_value.'"';
                                } else {
                                    $xmlObj .= ' '.$i.'="'.$xsdmf_details['xsdmf_value_prefix'] . $attrib_value.'"';
                                }
                            }
                        }
                    }
                    // *** NOT AN ATTRIBUTE, SO LOOP THROUGH XML ELEMENTS
                } elseif (!empty($j['fez_hyperlink'])) {
                    if (!isset($j['fez_nodetype']) || $j['fez_nodetype'] != 'attribute') {
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
                            if ($xsdmf_details['xsdmf_html_input'] != 'xsd_loop_subelement') { // subloop element attributes get treated differently
                                if (!is_numeric(strpos($i, ":"))) {
                                    $xmlObj .= "<".$element_prefix.$i;
                                } else {
                                    $xmlObj .= "<".$i;
                                } 
                                $xmlObj .= Foxml::array_to_xml_instance($j, "", $element_prefix, "attributes", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads, $created_date, $updated_date);
                                if ($xsdmf_details['xsdmf_valueintag'] == 1) {
                                    $xmlObj .= ">\n";
                                } else {
                                    $xmlObj .= "/>\n";
                                }
                            }	
                            $attrib_value = "";
                            if ($xsdmf_details['xsdmf_html_input'] == 'date') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple							
                                $attrib_value = Misc::getPostedDate($xsdmf_id);
                                array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
                            } elseif ($xsdmf_details['xsdmf_html_input'] == 'combo') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
                                $attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]);
                                array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));									
                            } elseif ($xsdmf_details['xsdmf_html_input'] == 'contvocab' || $xsdmf_details['xsdmf_html_input'] == 'contvocab_selector') {							
                                Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i);
                            } elseif ($xsdmf_details['xsdmf_html_input'] == 'multiple' && isset($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id])) {
                                Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i);
                            } elseif ($xsdmf_details['xsdmf_html_input'] == 'xsdmf_id_ref') { // this assumes the xsdmf_id_ref will only refer to an xsdmf_id which is a text/textarea/combo/multiple, will have to modify if we need more
                                $xsdmf_details_ref = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_details['xsdmf_id_ref']);
                                if ($xsdmf_details['xsdmf_html_input'] == 'date') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple							
                                    $attrib_value = Misc::getPostedDate($xsdmf_id);
                                    array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
                                } elseif ($xsdmf_details['xsdmf_html_input'] == 'combo') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
                                    $attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']]);
                                    array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']])));
                                } elseif ($xsdmf_details['xsdmf_html_input'] == 'contvocab' || $xsdmf_details['xsdmf_html_input'] == 'contvocab_selector') {
                                    Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i);
                                } elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'multiple') {
                                    Foxml::handleMultipleInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i);
                                } elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'file_input' || $xsdmf_details_ref['xsdmf_html_input'] == 'file_selector') {
                                    if (is_numeric($attrib_loop_index) && is_array($HTTP_POST_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']])) {
                                        $attrib_value = (fread(fopen($HTTP_POST_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']][$attrib_loop_index], "r"), $HTTP_POST_FILES["xsd_display_fields"]["size"][$xsdmf_details['xsdmf_id']][$attrib_loop_index]));
                                    } else {
                                        $attrib_value = (fread(fopen($HTTP_POST_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']], "r"), $HTTP_POST_FILES["xsd_display_fields"]["size"][$xsdmf_details['xsdmf_id']]));													
                                    }
                                } elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'text' || $xsdmf_details_ref['xsdmf_html_input'] == 'textarea') {
                                    $xsdmf_details_ref = array();
                                    Foxml::handleTextInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $xsdmf_details_ref, $attrib_loop_index, $element_prefix, $i);
                                }
                            } elseif ($xsdmf_details['xsdmf_html_input'] == 'static') {
                                Foxml::handleStaticInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $attrib_loop_index, $element_prefix, $i, $created_date, $updated_date, $file_downloads, $top_xdis_id);
                            } elseif ($xsdmf_details['xsdmf_html_input'] == 'dynamic') {
                                $attrib_value = $HTTP_POST_VARS[$xsdmf_details['xsdmf_dynamic_text']];
                                array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $attrib_value));
                            } elseif (($xsdmf_details['xsdmf_html_input'] == 'file_input' || $xsdmf_details['xsdmf_html_input'] == 'file_selector') && !empty($HTTP_POST_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']])) {
                                if (is_numeric($attrib_loop_index) && is_array($HTTP_POST_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']])) {
                                    if (!empty($HTTP_POST_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']][$attrib_loop_index])) {
                                        $attrib_value = (fread(fopen($HTTP_POST_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']][$attrib_loop_index], "r"), $HTTP_POST_FILES["xsd_display_fields"]["size"][$xsdmf_details['xsdmf_id']][$attrib_loop_index]));
                                    }										
                                } else {
                                    $attrib_value = (fread(fopen($HTTP_POST_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']], "r"), $HTTP_POST_FILES["xsd_display_fields"]["size"][$xsdmf_details['xsdmf_id']]));													
                                }
                                // put a full text indexer here for pdfs and word docs
                            } elseif ($xsdmf_details['xsdmf_html_input'] == 'text' || $xsdmf_details['xsdmf_html_input'] == 'textarea') {			
                                $xsdmf_details_ref = array();
                                Foxml::handleTextInstance($attrib_value, $indexArray, $pid, $parent_sel_id, $xdis_id, $xsdmf_id, $xsdmf_details, $xsdmf_details_ref, $attrib_loop_index, $element_prefix, $i);
                            } else { // not necessary in this side
                                $attrib_value = $xsdmf_details['xsdmf_value_prefix'] . @$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id];
                                array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . @$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]));
                            } 
                            $xmlObj .= $attrib_value; // The actual value to store inside the element tags, if one exists
                            // *** IF IT IS A LOOPING SUBELEMENT THEN GO RECURSIVE
                            if ($xsdmf_details['xsdmf_html_input'] == 'xsd_loop_subelement') {
                                $sel = XSD_Loop_Subelement::getListByXSDMF($xsdmf_id);
                                if (count($sel) > 0) { //if there are xsd sublooping elements attached to it then prepare their headers and go recursive!
                                    foreach($sel as $sel_record) {
                                        if (is_numeric($sel_record['xsdsel_attribute_loop_xsdmf_id']) && ($sel_record['xsdsel_attribute_loop_xsdmf_id'] != 0)) {
                                            $attrib_loop_details = XSD_HTML_Match::getDetailsByXSDMF_ID($sel_record['xsdsel_attribute_loop_xsdmf_id']);
                                            if ($attrib_loop_details['xsdmf_html_input'] == "file_input") {
                                                $attrib_loop_child = $HTTP_POST_FILES['xsd_display_fields']["tmp_name"][$sel_record['xsdsel_attribute_loop_xsdmf_id']];
                                            } else {
                                                $attrib_loop_child = $HTTP_POST_VARS['xsd_display_fields'][$sel_record['xsdsel_attribute_loop_xsdmf_id']];
                                            }
                                            if (is_array($attrib_loop_child)) {
                                                $attrib_loop_count = count($attrib_loop_child);
                                            } else {
                                                $attrib_loop_count = 1;
                                            }
                                        } else {
                                            $attrib_loop_count = 1;
                                        }
                                        for ($x=0;$x<$attrib_loop_count;$x++) { // if this sel id is a loop of attributes then it will loop through each, otherwise it will just go through once
                                            if (((@$attrib_loop_details['xsdmf_html_input'] != "file_input") && (@$attrib_loop_details['xsdmf_html_input'] != "text"))
                                                    || (is_array($attrib_loop_child) && ($attrib_loop_details['xsdmf_html_input'] == "file_input") && ($HTTP_POST_FILES['xsd_display_fields']["tmp_name"][$sel_record['xsdsel_attribute_loop_xsdmf_id']][$x] != ""))
                                                    || (!is_array($attrib_loop_child) && ($attrib_loop_details['xsdmf_html_input'] == "file_input") && ($HTTP_POST_FILES['xsd_display_fields']["tmp_name"][$sel_record['xsdsel_attribute_loop_xsdmf_id']] != ""))																				
                                                    || (is_array($attrib_loop_child) && ($attrib_loop_details['xsdmf_html_input'] == "text") && ($HTTP_POST_VARS['xsd_display_fields'][$sel_record['xsdsel_attribute_loop_xsdmf_id']][$x] != ""))
                                                    || (!is_array($attrib_loop_child) && ($attrib_loop_details['xsdmf_html_input'] == "text") && ($HTTP_POST_VARS['xsd_display_fields'][$sel_record['xsdsel_attribute_loop_xsdmf_id']] != ""))																				

                                               ) {
                                                if (!is_numeric(strpos($i, ":"))) {
                                                    $xmlObj .= "<".$element_prefix.$i;
                                                } else {
                                                    $xmlObj .= "<".$i;
                                                } 
                                                $xmlObj .= Foxml::array_to_xml_instance($j, "", $element_prefix, "attributes", $tagIndent, $sel_record['xsdsel_id'], $xdis_id, $pid, $top_xdis_id, $x, $indexArray, $file_downloads, $created_date, $updated_date);
                                                if ($xsdmf_details['xsdmf_valueintag'] == 1) {
                                                    $xmlObj .= ">\n";
                                                } else {
                                                    $xmlObj .= "/>\n";
                                                }			
                                                $xmlObj .= Foxml::array_to_xml_instance($j, "", $element_prefix, "", $tagIndent, $sel_record['xsdsel_id'], $xdis_id, $pid, $top_xdis_id, $x, $indexArray, $file_downloads, $created_date, $updated_date);
                                                if ($xsdmf_details['xsdmf_valueintag'] == 1) {
                                                    if (!is_numeric(strpos($i, ":"))) {
                                                        $xmlObj .= "</".$element_prefix.$i.">\n";
                                                    } else {
                                                        $xmlObj .= "</".$i.">\n";
                                                    }
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
                                    $tagIndent .= "    ";
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
                                    $xmlObj .= $tagIndent."<".$xsd_element_prefix.$xsd_top_element_name." ";
                                    $xmlObj .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid);
                                    $xmlObj .= $xml_schema;
                                    $xmlObj .= ">\n";
                                    $xmlObj .= Foxml::array_to_xml_instance($array_ptr, "", $xsd_element_prefix, "", $tagIndent, "", $rel_record['xsdrel_xdis_id'], $pid, $top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads, $created_date, $updated_date);
                                    $xmlObj .= $tagIndent."</".$xsd_element_prefix.$xsd_top_element_name.">\n";
                                }
                            }
                            $xmlObj .= Foxml::array_to_xml_instance($j, "", $element_prefix, "", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads, $created_date, $updated_date);
                            $xmlObj .= $tagIndent;
                            if ($xsdmf_details['xsdmf_html_input'] != 'xsd_loop_subelement') { // subloop element attributes get treated differently
                                if ($xsdmf_details['xsdmf_valueintag'] == 1) {
                                    if (!is_numeric(strpos($i, ":"))) {
                                        $xmlObj .= "</".$element_prefix.$i.">\n";
                                    } else {
                                        $xmlObj .= "</".$i.">\n";
                                    }
                                }
                            }
                        }
                    } else {
                        $xmlObj .= Foxml::array_to_xml_instance($j, "", $element_prefix, "", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads, $created_date, $updated_date);
                    } // new if is numeric
                } else {
                    $xmlObj = Foxml::array_to_xml_instance($j, $xmlObj, $element_prefix, "", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, $attrib_loop_index, $indexArray, $file_downloads, $created_date, $updated_date);
                }
            }
        }	
        return $xmlObj;
    }   


    function setDCTitle($title, $xmlObj)
    {
        $xmlObj = preg_replace('/<dc:title>.*?<\/dc:title>/s', "<dc:title>$title</dc:title>", $xmlObj);
        $xmlObj = preg_replace('/<dc:title\/>/', "<dc:title>$title</dc:title>", $xmlObj);
        return $xmlObj;
    }
}

?>
