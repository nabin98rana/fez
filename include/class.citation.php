<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 19/03/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
 class Citation
 {
 
    function getDetails($xdis_id, $type='MLA') {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $stmt = "SELECT * FROM {$dbtp}citation WHERE cit_xdis_id='$xdis_id' AND cit_type='$type' ";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            return $res;
        }
    }
    
    function save($xdis_id, $template, $type='MLA') 
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $det = Citation::getDetails($xdis_id, $type);
        $template = Misc::escapeString($template);
        if (empty($det)) {
            $stmt = "INSERT INTO {$dbtp}citation (cit_xdis_id, cit_template, cit_type) " .
                    "VALUES ('$xdis_id','$template','$type')";
        } else {
            $stmt = "UPDATE {$dbtp}citation SET " .
                    "cit_xdis_id='$xdis_id'," .
                    "cit_template='$template'," .
                    "cit_type='$type' " .
                    "WHERE cit_id='{$det['cit_id']}' ";
        }
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        }
        return true;
    }
    
    function renderCitation($xdis_id, $details, $xsd_display_fields, $type='MLA')
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $det = Citation::getDetails($xdis_id, $type);
        $result = $det['cit_template'];
        if (empty($result)) {
            return '';
        }
        return Citation::renderCitationTemplate($result, $details, $xsd_display_fields, $type);
    }
    
    function renderCitationTemplate($template, $details, $xsd_display_fields, $type='MLA')
    {
        preg_match_all('/\{(.*?)\}/',$template,$matches,PREG_PATTERN_ORDER);
        $xsdmf_list = Misc::keyArray($xsd_display_fields, 'xsdmf_id');
        foreach ($matches[1] as $key => $match) {
            list($xsdmf_id,$prefix,$suffix) = explode('|',$match);
            $value = Citation::formatValue($details[$xsdmf_id], $xsdmf_list[$xsdmf_id]);
            if (!empty($value)) {
                $value = $prefix.$value.$suffix;
            }
            //Error_Handler::logError($match);
            $template = str_replace('{'.$match.'}', $value, $template);
        } 
        return $template;
    }
     
    function formatValue($value, $xsdmf)
    {
        if (is_array($value)) {
            $list = '';
            $cnt = count($value);
            for ($ii = 0; $ii < $cnt; $ii++) {
                if ($ii > 0) {
                    if ($ii >= $cnt - 2) {
                        $list .= ' and ';
                    } elseif ($ii < $cnt - 2) {
                        $list .= ', ';
                    }
                }
                $list .= Citation::formatValue($value[$ii],$xsdmf);
            }
            $value = $list;
        } elseif ($xsdmf['xsdmf_data_type'] == 'date') {
            if ($xsdmf['xsdmf_html_input'] == 'date') {
                if ($xsdmf['xsdmf_date_type'] == 1) {
                    $value = strftime("%Y", strtotime($value)); 
                }
            } else {
                if ($xsdmf['xsdmf_attached_xsdmf_id'] == 0) {
                    $value = strftime("%A, %B %e, %Y", strtotime($value)); 
                }
            }
        }
        return $value;
    }
    
    function convert($xdis_id) 
    {
        $xsd_display_fields = XSD_HTML_Match::getListByDisplay($xdis_id, array('FezACML'));
        $citation = array();
        // Now generate the Citation View
        // First get the citation fields in the correct order
        foreach ($xsd_display_fields as $dis_key => $dis_field) {
            if (($dis_field['xsdmf_enabled'] == 1) && ($dis_field['xsdmf_citation'] == 1) && (is_numeric($dis_field['xsdmf_citation_order']))) {
                $citation[$dis_field['xsdmf_citation_order']] = $dis_field;
            }
        }
        ksort($citation);
        $citation_html = "";
        foreach($citation as $cit_key => $cit_field) {
            if ($cit_field['xsdmf_citation_bold'] == 1) {
                $citation_html .= "<b>";
            }
            if ($cit_field['xsdmf_citation_italics'] == 1) {
                $citation_html .= "<i>";
            }
            if ($cit_field['xsdmf_citation_brackets'] == 1) {
                $citation_html .= " (";
            }
            $citation_html .= '{'.$cit_field['xsdmf_id'];                        
            if (trim($cit_field['xsdmf_citation_prefix']) != "") {
                $citation_html .= '|'.$cit_field['xsdmf_citation_prefix'];
            }
            if (trim($cit_field['xsdmf_citation_suffix']) != "") {
                if (trim($cit_field['xsdmf_citation_prefix']) == "") {
                    $citation_html .= '|';
                }
                $citation_html .= '|'.$cit_field['xsdmf_citation_suffix'];
            }
            $citation_html .= '} ';
            if ($cit_field['xsdmf_citation_bold'] == 1) {
                $citation_html .= "</b>";
            }
            if ($cit_field['xsdmf_citation_italics'] == 1) {
                $citation_html .= "</i>";
            }
            if ($cit_field['xsdmf_citation_brackets'] == 1) {
                $citation_html .= ")";
            }
        }
        $citation_html = str_replace(' ,', ', ', $citation_html);
        $citation_html = str_replace(' .', '. ', $citation_html);
        $citation_html = preg_replace('/(,|\.),\S/', ', ', $citation_html);
        return Citation::save($xdis_id, trim($citation_html), 'MLA'); 
    }
     
 }
?>
