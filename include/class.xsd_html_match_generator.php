<?php

include_once(APP_INC_PATH.'class.org_structure.php');

 class XSD_HTML_Match_Generator
 {
    function generateEditWidget($pid, $escaped_path, $label) {
        $html_result = '<div class="xsdmf_edit_widget">';
        // get xsdmf_id
        $record_obj = new RecordGeneral($pid);
        $xsdmf_cols = $record_obj->getXSDMFDetailsByElement($escaped_path);
        if (empty($xsdmf_cols)) {
            $html_result .= $label." not found";
        } else {
            $values = $record_obj->getDetailsByXSDMF_element($escaped_path);
    //        $html_result .= "<a href=\"#\" class=\"form_note\">?<span class=\"form_note\">" 
    //            . print_r($xsdmf_cols, true) 
    //            . print_r($values, true)
    //            . "</span></a>";
            if (!$record_obj->canEdit()) {
            	$html_result .= "You are not authorised to edit this record.";
            } else {
                if (!is_array($values)) {
                	$values = array($values);
                }
                if ($xsdmf_cols['xsdmf_multiple'] == 1) {
                	$rows = $xsdmf_cols['xsdmf_multiple_limit'];
                } else {
                	$rows = 1;
                }
                $safe_pid = str_replace(':','_',$pid);
                for ($vidx = 0; $vidx < $rows; $vidx++) {
                    $value = @$values[$vidx];
                    if ($vidx < 1 || isset($values[$vidx-1])) {
                        $hide_div = '';
                    } else {
                        $hide_div = ' style="display:none" ';
                    }
                    $html_result .= "<div id=\"xsdmf_editor_div_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\"" .
                            " class=\"xsdmf_editor_div\" $hide_div>" .
                            "<div class=\"xsdmf_editor_label\">$label</div>" .
                            "<form name=\"wfl_form_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\"" .
                            "   class=\"xsdmf_editor_input_form\" >" ;
                    $func_name = XSD_HTML_Match_Generator::getFuncName($xsdmf_cols['xsdmf_html_input']);
                    $html_result .= call_user_func(array('XSD_HTML_Match_Generator',$func_name), $pid, $xsdmf_cols, $vidx, $value, $record_obj);
                    $html_result .= '</form>';
                    // handle the side by side pairs thing
                    if (!empty($xsdmf_cols['xsdmf_attached_xsdmf_id'])) {
                        $xsdmf_id1 = $xsdmf_cols['xsdmf_attached_xsdmf_id'];
                        $xsdmf_cols1 = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id1);
                        $values1 = $record_obj->getDetailsByXSDMF_ID($xsdmf_id1);
                        $func_name = XSD_HTML_Match_Generator::getFuncName($xsdmf_cols1['xsdmf_html_input']);
                        $html_result .= "<form name=\"wfl_form_".$xsdmf_cols1['xsdmf_id']."_".$safe_pid."_".$vidx."\"" .
                                " class=\"xsdmf_editor_input_form\" >" ;
                        $html_result .= call_user_func(array('XSD_HTML_Match_Generator',$func_name), $pid, $xsdmf_cols1, $vidx, $values1[$vidx], $record_obj);
                        $html_result .= '</form>';
                    }
                    $html_result .= '</div>';
                }
            }
        }
        $html_result .= '</div>';
        return $html_result;
    }
    
    function getFuncName($xsdmf_html_input)
    {
    	switch($xsdmf_html_input)
                {
                    case 'author_selector':
                        $func_name = 'authorSelector';
                        break;
                    case 'author_suggestor':
                        $func_name = 'authorSuggestor';
                        break;
                    default:
                        $func_name = 'textBox';
                        break;
                }
                return $func_name;
    }
    function textBox($pid, $xsdmf_cols, $vidx, $value, &$record_obj)
    {
    	$onkeyup = '';
        if ($xsdmf_cols['xsdmf_multiple']) {
    		if ($vidx < $xsdmf_cols['xsdmf_multiple_limit'] - 1) {
    		      $nextIdx = $vidx + 1;
                  $onkeyup = " onkeyup=\"unhideXSDMF_Editor('".$pid."','".$xsdmf_cols['xsdmf_id']."','".$nextIdx."');\" ";	
    		} 
    	}
        $safe_pid = str_replace(':','_',$pid);
        return " 

<div class=\"xsdmf_editor_input_div\">
<input name=\"xsdmf_editor_input_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\" 
  id=\"xsdmf_editor_input_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\" value=\"".$value."\" ".$onkeyup."/>
<input type=\"button\" value=\"Submit\" id=\"xsdmf_editor_submit_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\" 
onclick=\"handleXSDMF_Editor('".$pid."','".$xsdmf_cols['xsdmf_id']."','".$vidx."');\"/>
<span class=\"updating\" style=\"display:none;\" id=\"xsdmf_editor_mess_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\"></span>
</div>

";
    }
    
    function authorSelector($pid, $xsdmf_cols, $vidx, $value, &$record_obj)
    {
        $parents = $record_obj->getParents();
        $authors_sub_list = array();
        foreach ($parents as $parent) {
            $parent_pid = $parent;
            if (!empty($parent_pid) && $parent_pid != "-1") {
              $parent_record = new RecordObject($parent_pid);
              $parent_xdis_id = $parent_record->getXmlDisplayId();
              $parent_relationships = XSD_Relationship::getColListByXDIS($parent_xdis_id);
              array_push($parent_relationships, $parent_xdis_id);
              if ($xsdmf_cols["xsdmf_use_parent_option_list"] == 1) {
                    if (in_array($xsdmf_cols["xsdmf_parent_option_xdis_id"], $parent_relationships)) {
                      $parent_details = $parent_record->getDetails();
                        if (is_numeric($parent_details[$xsdmf_cols["xsdmf_parent_option_child_xsdmf_id"]])) {
                            $authors_sub_list = Org_Structure::getAuthorsByOrgID($parent_details[$xsdmf_cols["xsdmf_parent_option_child_xsdmf_id"]]);
                            break;
                        }
                    }
              }
            }
        }
    
        $option_list_html = '';
        foreach ($authors_sub_list as $key => $option_value) {
            if ($key == $value) {
            	$checked = ' selected="on" ';
            } else {
                $checked = '';	
            }
            $option_list_html .= "<option value=\"".$key."\" ".$checked.">".$option_value."</option>\n" ;
        } 
        $onkeyup = '';
        if ($xsdmf_cols['xsdmf_multiple']) {
            if ($vidx < $xsdmf_cols['xsdmf_multiple_limit'] - 1) {
                  $nextIdx = $vidx + 1;
                  $onkeyup = " unhideXSDMF_Editor('".$pid."','".$xsdmf_cols['xsdmf_id']."','".$nextIdx."'); "; 
            } 
        }
        $safe_pid = str_replace(':','_',$pid);
        return "

<div class=\"xsdmf_editor_input_div\">
<select name=\"xsdmf_editor_input_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\" 
id=\"xsdmf_editor_input_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\">
 ".$option_list_html."
</select> 
<input type=\"button\" value=\"Submit\" id=\"xsdmf_editor_submit_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\" 
onclick=\"handleXSDMF_Editor('".$pid."','".$xsdmf_cols['xsdmf_id']."','".$vidx."');".$onkeyup."\"/>
<span class=\"updating\" style=\"display:none;\" id=\"xsdmf_editor_mess_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\"></span>
</div>

";
    	
    }

    function authorSuggestor($pid, $xsdmf_cols, $vidx, $value, &$record_obj)
    {
        $details = $record_obj->getDetails();
        $auth_name = '';
        if (!empty($value)) {
            if (isset($details[$xsdmf_cols['xsdmf_asuggest_xsdmf_id']])) {
            	if (is_array($details[$xsdmf_cols['xsdmf_asuggest_xsdmf_id']])) {
            		$auth_name = $details[$xsdmf_cols['xsdmf_asuggest_xsdmf_id']][$vidx];
            	} else {
            		$auth_name = $details[$xsdmf_cols['xsdmf_asuggest_xsdmf_id']];
            	}
            }
            $html_option = "
<option value=\"".$value."\" selected>".$auth_name." (".$value.")</option>
";
        } else {
        	$html_option = '';
        }
        $onkeyup = '';
        if ($xsdmf_cols['xsdmf_multiple']) {
            if ($vidx < $xsdmf_cols['xsdmf_multiple_limit'] - 1) {
                  $nextIdx = $vidx + 1;
                  $onkeyup = " && unhideXSDMF_Editor('".$pid."','".$xsdmf_cols['xsdmf_id']."','".$nextIdx."') "; 
            } 
        }
        $safe_pid = str_replace(':','_',$pid);
        return "

<div class=\"xsdmf_editor_input_div\">
<script type=\"text/javascript\">
function register_suggest_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."() {
    window.oTextbox_xsdmf_editor_input_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."_lookup
    = new AutoSuggestControl(document.wfl_form_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx.", 
        'xsdmf_editor_input_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."', 
        document.getElementById('xsdmf_editor_input_".$xsdmf_cols['xsdmf_asuggest_xsdmf_id']."_".$safe_pid."_".$vidx."'), 
        document.getElementById('xsdmf_editor_input_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."_lookup'),
        new StateSuggestions('Author','suggest',false,'class.author.php'));
}
</script>
<div class=\"register_suggest\" name=\"register_suggest\">register_suggest_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."();</div>
<select id=\"xsdmf_editor_input_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\" 
    name=\"xsdmf_editor_input_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\">    
<option value=\"0\">(none)</option>
".$html_option."
</select>
<!-- Google suggest style selection -->
<input type=\"text\" name=\"xsdmf_editor_input_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."_lookup\" 
    id=\"xsdmf_editor_input_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."_lookup\" size=\"30\" autocomplete=\"off\"  />
<input type=\"button\" value=\"Submit\" id=\"xsdmf_editor_submit_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx."\" 
    onclick=\"handleXSDMF_Editor('".$pid."','".$xsdmf_cols['xsdmf_id']."','".$vidx."')".$onkeyup.";\"/>
<span class=\"updating\" style=\"display:none;\" id=\"xsdmf_editor_mess_".$xsdmf_cols['xsdmf_id']."_".$safe_pid."_".$vidx." \"></span>
</div>
";

    }
    
 }
?>
