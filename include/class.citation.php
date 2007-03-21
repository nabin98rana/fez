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
 
    function getDetails($xdis_id, $type='APA') {
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
    
    function save($xdis_id, $template, $type='APA') 
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
    
    function renderCitation($xdis_id, $details, $xsd_display_fields, $type='APA')
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $det = Citation::getDetails($xdis_id, $type);
        $result = $det['cit_template'];
        if (empty($result)) {
            return '';
        }
        return Citation::renderCitationTemplate($result, $details, $xsd_display_fields, $type);
    }
    
    function renderCitationTemplate($template, $details, $xsd_display_fields, $type='APA')
    {
        preg_match_all('/\{(.*?)\}/',$template,$matches,PREG_PATTERN_ORDER);
        $xsdmf_list = Misc::keyArray($xsd_display_fields, 'xsdmf_id');
        foreach ($matches[1] as $key => $match) {
            list($xsdmf_id,$prefix,$suffix,$option) = explode('|',$match);
            $value = Citation::formatValue($details[$xsdmf_id], $xsdmf_list[$xsdmf_id], $option, $type);
            if (!empty($value)) {
                $value = $prefix.$value.$suffix;
            }
            //Error_Handler::logError($match);
            $template = str_replace('{'.$match.'}', $value, $template);
        } 
        return $template;
    }
     
    function formatValue($value, $xsdmf, $option = '', $type='APA')
    {
        if (is_array($value)) {
            // recurse for each item of the array
            $list = '';
            $cnt = count($value);
            for ($ii = 0; $ii < $cnt; $ii++) {
                if ($ii > 0) {
                    if ($ii >= $cnt - 1) {
                        $list .= ' and ';
                    } else {
                        $list .= ', ';
                    }
                }
                $list .= Citation::formatValue($value[$ii],$xsdmf, $option, $type);
            }
            $value = $list;
        } elseif ($xsdmf['xsdmf_data_type'] == 'date') {
            switch($option) {
                case 'ymd':
                    $value = strftime("%Y, %B %d", strtotime($value));
                break;
                case 'ym':
                    $value = strftime("%Y, %B", strtotime($value));
                break;
                case 'my':
                    $value = strftime("%B %Y", strtotime($value));
                break;
                default:
                    $value = strftime("%Y", strtotime($value));
                break;
            } 
        // need to for an attached field for the suggestor thing to work or we need some other way
        // of handling commas in authors names.
        } elseif ($xsdmf['xsdmf_html_input'] == 'author_selector') {
           $value = Citation::formatAuthor(Author::getFullname($value), $type);
            // special case hack for editors name fix
        } elseif ($xsdmf['sek_title'] == "Author"
                    || strpos($xsdmf['xsdmf_title'], 'Editor') !== false) {
            $value = Citation::formatAuthor($value, $type);
        } 
        return $value;
    }
    
    function formatAuthor($value, $type='APA') 
    {
        if (empty($value)) {
            return '';
        }
        // First convert to display names style - Title FName MName/Init LName
        $parts = explode(',', $value, 2);
        if (count($parts) > 1) {
            $value = $parts[1].' '.$parts[0];     
        }
        $value = str_replace('.', '. ', $value);
        
        switch($type)
        {
            case 'APA':
                $parts = explode(' ', $value);
                $parts = array_filter($parts, create_function('$a', 'return !empty($a);'));
                $lname = array_pop($parts);
                $inits = array_map(create_function('$a', 'return substr(trim($a), 0, 1);'), $parts);
                $value = $lname.', '.implode('. ',$inits).'.';
            break;
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
        return Citation::save($xdis_id, trim($citation_html), 'APA'); 
    }
     
 }
?>
