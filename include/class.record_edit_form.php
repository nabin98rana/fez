<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 20/06/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
 include_once(APP_INC_PATH . "class.community.php");
 
 class RecordEditForm 
 {
    var $details;
    var $xsd_display_fields;
    
     function setTemplateVars($tpl, $record)
     {
        $pid = $record->pid;
        $sta_id = $record->getPublishedStatus();
        if (!$sta_id) {
            $sta_id = Record::status_unpublished;
        }
        $tpl->assign('sta_id', $sta_id); 
        
        $parents = $record->getParents();
        $tpl->assign("pid", $pid);

        $xsd_display_fields = $record->display->getMatchFieldsList(array("FezACML"), array(""));  // XSD_DisplayObject
        $this->fixDisplayFields($xsd_display_fields, $record);
        $this->xsd_display_fields = $xsd_display_fields;
    
        $tpl->assign("xsd_display_fields", $xsd_display_fields);
    
        $tpl->assign("xdis_id", $record->getXmlDisplayId());
    
        $details = $record->getDetails();
        $this->fixDetails($details);
        $this->details = $details;
        
        
        $tpl->assign("parents", $parents);
        $title = $record->getTitle(); // RecordObject
        $tpl->assign("title", $title);
        if ($record->isCollection()) {
            $tpl->assign('record_type', 'Collection');
            $tpl->assign('parent_type', 'Community');
            $tpl->assign('view_href', APP_RELATIVE_URL."list.php?collection_pid=$pid");
        } elseif ($record->isCommunity()) {
            $tpl->assign('record_type', 'Community');
            $tpl->assign('view_href', APP_RELATIVE_URL."list.php?community_pid=$pid");
        } else {
            $tpl->assign('record_type', 'Record');
            $tpl->assign('parent_type', 'Collection');
            $tpl->assign('view_href', APP_RELATIVE_URL."view.php?pid=$pid");
        }
    //  print_r($details);
    //    print_r($datastreams);
        $tpl->assign("eserv_url", APP_BASE_URL."eserv.php?pid=".$pid."&dsID=");
        $tpl->assign("local_eserv_url", APP_RELATIVE_URL."eserv.php?pid=".$pid."&dsID=");
        $tpl->assign('triggers', count(WorkflowTrigger::getList($pid)));
        $tpl->assign("ds_get_path", APP_FEDORA_GET_URL."/".$pid."/");
        $tpl->assign("isEditor", 1);
        $tpl->assign("details", $details);
        $tpl->registerNajax( NAJAX_Client::register('SelectOrgStructure', 'edit_metadata.php')."\n"
                        .NAJAX_Client::register('Suggestor', 'edit_metadata.php'));
             
     }

	function setDynamicVar($name)
	{
		// setup smarty variables used in dynamic XSD value lookups
		switch ($name)
		{
			case '$community_list':
				global $community_list;
				if (empty($community_list)) {
	        		$community_list = Community::getAssocList();
        		}
	        	break;
	        case '$collection_list':
				global $collection_list;
				if (empty($collection_list)) {
		            $collection_list = Collection::getEditListAssoc();
	            }
	            break;
	        case '$xdis_collection_list':
				global $xdis_collection_list;
				if (empty($xdis_collection_list)) {
		        	$xdis_collection_list = XSD_Display::getAssocListCollectionDocTypes(); 
	        	}
	        	break;
	        case '$xdis_list':
				global $xdis_list;
				// @@@ CK - 24/8/05 added for collections to be able to select their child document types/xdisplays
				if (empty($xdis_list)) {
			        $xdis_list = XSD_Display::getAssocListDocTypes(); 
		        }
        	break;
		}
	}
     
    function fixDisplayFields(&$xsd_display_fields, $record)
    {
        $parents = $record->getParents();
        $parent_relationships = array();
        foreach ($parents as $parent) {
            $parent_record = new RecordObject($parent['pid']);
            $parent_xdis_id = $parent_record->getXmlDisplayIdUseIndex();
            $parent_relationship = XSD_Relationship::getColListByXDIS($parent_xdis_id);
            array_push($parent_relationship, $parent_xdis_id);
            $parent_relationships = Misc::array_merge_values($parent_relationships, $parent_relationship);
        }

        
        //@@@ CK - 26/4/2005 - fix the combo and multiple input box lookups - should probably move this into a function somewhere later
        foreach ($xsd_display_fields  as $dis_key => $dis_field) {
           
			if ($dis_field["xsdmf_enabled"] == 1) {
      		  if ($dis_field["xsdmf_html_input"] == 'org_selector') {
                    if ($dis_field["xsdmf_org_level"] != "") {
                        $xsd_display_fields[$dis_key]['field_options'] = Org_Structure::getAssocListByLevel($dis_field["xsdmf_org_level"]);
                    }
                }
                if ($dis_field["xsdmf_html_input"] == 'author_selector') {
                    if ($dis_field["xsdmf_use_parent_option_list"] == 1) {
                        // Loop through the parents - there is only one parent for entering metadata
                        if (in_array($dis_field["xsdmf_parent_option_xdis_id"], $parent_relationships)) {
                            foreach ($parents as $parent) {
                                $parent_record = new RecordObject($parent['pid']);
                                $parent_details = $parent_record->getDetails();
                                if (is_numeric(@$parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]])) {
                                    $authors_sub_list = Org_Structure::getAuthorsByOrgID($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]]);
                                    $xsd_display_fields[$dis_key]['field_options'] = $authors_sub_list;
                                }
                            }
                        }
                    }
                }
                if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'multiple') {
                    if (!empty($dis_field["xsdmf_smarty_variable"]) && $dis_field["xsdmf_smarty_variable"] != "none") {
                        $this->setDynamicVar($dis_field["xsdmf_smarty_variable"]);
                        eval("global ".$dis_field['xsdmf_smarty_variable']
                        	."; \$xsd_display_fields[\$dis_key]['field_options'] = " 
	                        . $dis_field["xsdmf_smarty_variable"] . ";");
                    }
                    if (!empty($dis_field["xsdmf_dynamic_selected_option"]) && $dis_field["xsdmf_dynamic_selected_option"] != "none") {
                        $this->setDynamicVar($dis_field["xsdmf_smarty_variable"]);
                        eval("global ".$dis_field['xsdmf_smarty_variable']
                        	."; \$xsd_display_fields[\$dis_key]['selected_option'] = " 
                        	. $dis_field["xsdmf_dynamic_selected_option"] . ";");
                    }
        
                    if ($dis_field["xsdmf_use_parent_option_list"] == 1) { // if the display field inherits this list from a parent then get those options
                        // Loop through the parents
                        if (in_array($dis_field["xsdmf_parent_option_xdis_id"], $parent_relationships)) {
                            $parent_details = $parent_record->getDetails(); // this only works for one parent for now.. need to loop over them again
                            if (is_array($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]])) {
                                $xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($dis_field["xsdmf_parent_option_child_xsdmf_id"]);
                                if ($xsdmf_details['xsdmf_smarty_variable'] != "" && $xsdmf_details['xsdmf_html_input'] == "multiple") {
                                    $temp_parent_options = array();
                                    $temp_parent_options_final = array();
			                        $this->setDynamicVar($xsdmf_details['xsdmf_smarty_variable']);
            			            eval("global ".$xsdmf_details['xsdmf_smarty_variable']
                                	    . "; \$temp_parent_options = "
                                    	. $xsdmf_details['xsdmf_smarty_variable'].";");
                                    $xsd_display_fields[$dis_key]['field_options'] = array();
                                    foreach ($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]] as $parent_smarty_option) {
                                        if (array_key_exists($parent_smarty_option, $temp_parent_options)) {
                                            $xsd_display_fields[$dis_key]['field_options'][$parent_smarty_option] = $temp_parent_options[$parent_smarty_option];
                                        }
                                    }
                                } else {
                                    $xsd_display_fields[$dis_key]['field_options'] = array();
                                    foreach ($parent_details[$dis_field["xsdmf_parent_option_child_xsdmf_id"]] as $parent_detail_text) {
                                        $xsd_display_fields[$dis_key]['field_options'][$parent_detail_text] = $parent_detail_text;
                                    }
                                }
                            }
                        }
                    }   
                }   
    /*          if (($dis_field["xsdmf_html_input"] == 'contvocab')) {
                    $xsd_display_fields[$dis_key]['field_options'] = $cvo_list['data'][$dis_field['xsdmf_cvo_id']];
                } */
                
            }
        }
    }
     
     function fixDetails(&$details)
     {
        $xsd_display_fields = $this->xsd_display_fields;
        foreach ($xsd_display_fields  as $dis_field) {
            if ($dis_field["xsdmf_enabled"] == 1) {
                if ($dis_field["xsdmf_html_input"] == 'text' || $dis_field["xsdmf_html_input"] == 'textarea' || $dis_field["xsdmf_html_input"] == 'hidden') {
                    if (is_array($details[$dis_field['xsdmf_id']])) {
                        foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
                            $details[$dis_field['xsdmf_id']][$ckey] = preg_replace('/\s\s+/', ' ', trim($cdata));
                        }
                    } else {
                        $details[$dis_field['xsdmf_id']] = preg_replace('/\s\s+/', ' ', trim($details[$dis_field['xsdmf_id']]));
                    }               
                }
                if ($dis_field["xsdmf_html_input"] == 'combo' || $dis_field["xsdmf_html_input"] == 'multiple' || $dis_field["xsdmf_html_input"] == 'contvocab' || $dis_field["xsdmf_html_input"] == 'contvocab_selector') {
                    if (@$details[$dis_field["xsdmf_id"]]) { // if a record detail matches a display field xsdmf entry
                        if (($dis_field["xsdmf_html_input"] == 'contvocab_selector') && ($dis_field['xsdmf_cvo_save_type'] != 1)) {         
                            $tempArray = $details[$dis_field["xsdmf_id"]];
                            if (is_array($tempArray)) {
                                $details[$dis_field["xsdmf_id"]] = array();
                                foreach ($tempArray as $cv_key => $cv_value) {
                                    $details[$dis_field["xsdmf_id"]][$cv_value] = Controlled_Vocab::getTitle($cv_value);
                                }
                            } elseif  (trim($details[$dis_field["xsdmf_id"]]) != "") {
                                $tempValue = $details[$dis_field["xsdmf_id"]];
                                $details[$dis_field["xsdmf_id"]] = array();
                                $details[$dis_field["xsdmf_id"]][$tempValue] = Controlled_Vocab::getTitle($tempValue);
                            }
                            
    
                        } elseif (is_array($dis_field["field_options"])) { // if the display field has a list of matching options
                            foreach ($dis_field["field_options"] as $field_key => $field_option) { // for all the matching options match the set the details array the template uses
                                if (is_array($details[$dis_field["xsdmf_id"]])) { // if there are multiple selected options (it will be an array)
                                    foreach ($details[$dis_field["xsdmf_id"]] as $detail_key => $detail_value) {
                                        if ($field_option == $detail_value) {
                                            $details[$dis_field["xsdmf_id"]][$detail_key] = $field_key;
                                        }
                                    }                   
                                } else {
                                    if ($field_option == $details[$dis_field["xsdmf_id"]]) {
                                        $details[$dis_field["xsdmf_id"]] = $field_key;
                                    }
                                }
                            }
                        }
                    }
    //          } elseif ($dis_field["xsdmf_html_input"] == 'author_selector') { // fix author id drop down combo if attached
    
                } elseif ($dis_field['xsdmf_html_input'] == "xsdmf_id_ref") {
                    $xsdmf_details_ref = XSD_HTML_Match::getDetailsByXSDMF_ID($dis_field['xsdmf_id_ref']);
                    $xsdmf_id_ref = $xsdmf_details_ref['xsdmf_id'];
                    if (($xsdmf_details_ref['xsdmf_html_input'] == 'contvocab') || ($xsdmf_details_ref['xsdmf_html_input'] == 'contvocab_selector')) {
                        if (!empty($details[$dis_field['xsdmf_id_ref']])) {
                            $details[$xsdmf_id_ref] = array(); //clear the existing data
                            if (is_array($details[$dis_field['xsdmf_id']])) {
                                foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
                                    if (!empty($cdata)) {
                                        $details[$xsdmf_id_ref][$cdata] = Controlled_Vocab::getTitle($cdata);
                                    }
        //                                      $details[$xsdmf_id_ref][$ckey] = "<a class='silent_link' href='".APP_BASE_URL."list.php?browse=subject&parent_id=".$cdata."'>".$controlled_vocabs[$cdata]."</a>";
                                }
                            } elseif (!empty($details[$dis_field['xsdmf_id']])) {
                                $details[$xsdmf_id_ref][$details[$dis_field['xsdmf_id']]] = Controlled_Vocab::getTitle($details[$dis_field['xsdmf_id']]);
                            }
                        }               
                    }                   
    
                
                } elseif (($dis_field["xsdmf_multiple"] == 1) && (!@is_array($details[$dis_field["xsdmf_id"]])) ){ // makes the 'is_multiple' tagged display fields into arrays if they are not already so smarty renders them correctly
                    $details[$dis_field["xsdmf_id"]] = array($details[$dis_field["xsdmf_id"]]);
                }
            }
			// handle attached fields on multiple things
        	if (is_numeric($dis_field["xsdmf_attached_xsdmf_id"]) && $dis_field["xsdmf_multiple"] == 1) {
        		if (!empty($details[$dis_field["xsdmf_attached_xsdmf_id"]]) 
        					&& !is_array($details[$dis_field["xsdmf_attached_xsdmf_id"]])) {
        			$details[$dis_field["xsdmf_attached_xsdmf_id"]] 
        					= array($details[$dis_field["xsdmf_attached_xsdmf_id"]]);
    			}
			}
        }
     }
     
     function setDatastreamEditingTemplateVars($tpl, $record)
     {
        $pid = $record->pid;
        $securityfields = Auth::getAllRoles();
        $datastreams = Fedora_API::callGetDatastreams($pid);
        $datastreams = Misc::cleanDatastreamList($datastreams);
    
        $datastream_workflows = WorkflowTrigger::getListByTrigger('-1', 5); //5 is for datastreams
        $linkCount = 0;
        $fileCount = 0;     

        $datastream_isMemberOf = array(0 => $pid);
        $parents = $record->getParents();
        foreach ($parents as $parent) {
            array_push($datastream_isMemberOf, $parent['pid']);
        }
        
        foreach ($datastreams as $ds_key => $ds) {
            if ($datastreams[$ds_key]['controlGroup'] == 'R') {
                $linkCount++;               
            }       
            if ($datastreams[$ds_key]['controlGroup'] == 'R' && $datastreams[$ds_key]['ID'] != 'DOI') {
                $datastreams[$ds_key]['location'] = trim($datastreams[$ds_key]['location']);
                // Check for APP_LINK_PREFIX and add if not already there
                if (APP_LINK_PREFIX != "") {
                    if (!is_numeric(strpos($datastreams[$ds_key]['location'], APP_LINK_PREFIX))) {
                        $datastreams[$ds_key]['location'] = APP_LINK_PREFIX.$datastreams[$ds_key]['location'];
                    }
                }
            } elseif ($datastreams[$ds_key]['controlGroup'] == 'M') {
                $FezACML_DS = array();
                $FezACML_DS = Record::getIndexDatastream($pid, $ds['ID'], 'FezACML');
                $return = array();
                $fileCount++;
                foreach ($FezACML_DS as $result) {
                    if (in_array($result['xsdsel_title'], $securityfields)  && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) )  {
                        if (!is_array($return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
                            $return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']] = array();
                        }
                        if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']])) {
                            array_push($return[$result['rmf_rec_pid']]['FezACML'][0][$result['xsdsel_title']][$result['xsdmf_element']], $result['rmf_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
                        }
                    }
                    if ($result['xsdmf_element'] == '!inherit_security') {
                        if (!is_array($return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'])) {
                            $return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'] = array();
                        }
                        if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'])) {
                            array_push($return[$result['rmf_rec_pid']]['FezACML'][0]['!inherit_security'], $result['rmf_'.$result['xsdmf_data_type']]);
                        }
                    }
                }
    
                $datastreams[$ds_key]['FezACML'] = @$return[$pid]['FezACML'];
                $datastreams[$ds_key]['workflows'] = $datastream_workflows;
                $parentsACMLs = array();
                if (count($FezACML_DS) == 0 || $datastreams[$ds_key]['FezACML'][0]['!inherit_security'][0] == "on") {
                    // if there is no FezACML set for this row yet, then is it will inherit from above, so show this for the form
                    if ($datastreams[$ds_key]['FezACML'][0]['!inherit_security'][0] == "on") {
                        $datastreams[$ds_key]['security'] = "include";
                        $parentsACMLs = $datastreams[$ds_key]['FezACML'];
                    } else {
                        $datastreams[$ds_key]['security'] = "inherit";
                        $parentsACMLs = array();
                    } 
                    Auth::getIndexParentACMLMemberList(&$parentsACMLs, $pid, $datastream_isMemberOf);
    //              print_r($parentsACMLs);
                    $datastreams[$ds_key]['FezACML'] = $parentsACMLs;           
                } else {
                    $datastreams[$ds_key]['security'] = "exclude";          
                }
            }
            
        } 
    
        $datastreams = Auth::getIndexAuthorisation($datastreams);
        $tpl->assign("linkCount", $linkCount);
        $tpl->assign("datastreams", $datastreams);
        $tpl->assign("fileCount", $fileCount);                  
         
     }
     
     function getRecordDetails()
     {
         return $this->details;
     }
    
    
    /**
     * makes sure that an array of params conforms to the expected 
     * formats as if it was submitted by a form.
     */ 
    function fixParams(&$params, $record)
    {
    	$record->getDisplay();
    	$xsd_display_fields = $record->display->getMatchFieldsList(array("FezACML"), array(""));  
        $this->fixDisplayFields($xsd_display_fields, $record);
        $this->xsd_display_fields = $xsd_display_fields;
        $this->fixDetails($params['xsd_display_fields']);
        foreach ($xsd_display_fields  as $dis_field) {
            if ($dis_field["xsdmf_enabled"] != 1) {
            	continue; // skip non-enabled items
    		}
    		// make sure multiple items are arrays even if they only have one item
            if ( ($dis_field["xsdmf_html_input"] == 'multiple' 
        	    		|| $dis_field["xsdmf_html_input"] == 'contvocab_selector') 
    	        	&& (!@is_array($params['xsd_display_fields'][$dis_field["xsdmf_id"]])) ){ 
	            $params['xsd_display_fields'][$dis_field["xsdmf_id"]] 
	            	= array($params['xsd_display_fields'][$dis_field["xsdmf_id"]]);
            }
            // the contvocab selector uses key value pairs but we only want the keys
            if ($dis_field["xsdmf_html_input"] == 'contvocab_selector') {
            	$params['xsd_display_fields'][$dis_field["xsdmf_id"]] 
	            	= array_keys($params['xsd_display_fields'][$dis_field["xsdmf_id"]]);
			}
			// handle attached fields
            if (isset($params['xsd_display_fields'][$dis_field["xsdmf_id"]])) {
            	if (is_numeric($dis_field["xsdmf_attached_xsdmf_id"])) {
            		if (is_array($params['xsd_display_fields'][$dis_field["xsdmf_attached_xsdmf_id"]])) {
	            		$ctr = 0;
	            		foreach ($params['xsd_display_fields'][$dis_field["xsdmf_attached_xsdmf_id"]] as $item) {
    	        			$att_key = 'xsd_display_fields_'.$dis_field["xsdmf_attached_xsdmf_id"].'_'.$ctr;
        	    			$params[$att_key] = $item;
            				$ctr++;
            			}
	        		} else {
    	        		$att_key = 'xsd_display_fields_'.$dis_field["xsdmf_attached_xsdmf_id"].'_0';
        	    		$params[$att_key] 
        	    			= $params['xsd_display_fields'][$dis_field["xsdmf_attached_xsdmf_id"]];
    	    		}
            	}
			}
			// convert dates
			if ($dis_field["xsdmf_html_input"] == 'date' 
				&& !empty($params['xsd_display_fields'][$dis_field["xsdmf_id"]])) {
				// need to break this into array of Year / Month / Day
				// We are expecting a YYYY-MM-DD format
				if (preg_match('/(\d{4})(-(\d{1,2})(-(\d{1,2}))?)?/', 		trim($params['xsd_display_fields'][$dis_field["xsdmf_id"]]), $matches)) {
					if (isset($matches[1])) {
						$params['xsd_display_fields'][$dis_field["xsdmf_id"]] = array('Year' => $matches[1]);
					}
					if (isset($matches[3])) {
						$params['xsd_display_fields'][$dis_field["xsdmf_id"]]['Month'] = $matches[3];
					}
					if (isset($matches[5])) {
						$params['xsd_display_fields'][$dis_field["xsdmf_id"]]['Day'] = $matches[5];
					}
				}
			}
        }
        // as a last pass, strip out any non enabled items. We do this in a seperate loop because the attached fields 
        // need to be read in the first loop and they are not usually enabled.
        foreach ($xsd_display_fields  as $dis_field) {
            if ($dis_field["xsdmf_enabled"] != 1 && isset($params['xsd_display_fields'][$dis_field["xsdmf_id"]])) {
				unset($params['xsd_display_fields'][$dis_field["xsdmf_id"]]);
			}
		}
		// strip out FezACML
		$fezacml_list = $record->display->getMatchFieldsList(array(), array("FezACML"));
		foreach ($fezacml_list as $item) {
			if (isset($params['xsd_display_fields'][$item['xsdmf_id']])) {
				unset($params['xsd_display_fields'][$item['xsdmf_id']]);
			}
		}
    }
 }
?>
