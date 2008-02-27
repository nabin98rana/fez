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
// |          Lachlun Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH . "class.spreadsheet_array.php");
include_once(APP_INC_PATH . "class.author.php");


/**
* !!! NOTE: This class originally exported Spreadsheet but now it uses the toXMLSS method instead because Spreadsheet doesn't
* handle UTF-8 consistantly.
*/
class ExportSpreadsheet {

    var $spreadsheet;
    var $bgp; // background process object for keeping track of status when running in background
    var $record_count;
    
    function export($pid)
    {
        $this->spreadsheet = new Spreadsheet_Array();
        $this->record_count = 0;
        $this->pid2spreadsheet($pid);
        $spreadsheetstr = $this->spreadsheet->toXMLSS();

        header('Content-type: text/xml');
        header("Content-Disposition: attachment; filename=\"export.xml\"");
        header('Pragma: private');
        header('Cache-control: private, must-revalidate'); 
        echo $spreadsheetstr;

        exit;
    }

    /**
      * Only runs in background
      */
    function export2File($pid)
    {
        $this->spreadsheet = new Spreadsheet_Array();
        $this->record_count = 0;
        $this->pid2spreadsheet($pid);
        $spreadsheetstr = $this->spreadsheet->toXMLSS();
        $filename = APP_PATH."exports/".$this->bgp->bgp_id.".xml";
        file_put_contents($filename, $spreadsheetstr);
        $headers = "Content-type: text/xml\n"
            ."Content-Disposition: attachment; filename=\"export.xml\"\n"
            ."Pragma: private\n"
            ."Cache-control: private, must-revalidate";

        $this->bgp->setExportFilename($filename, $headers);
    }

    function setBackgroundObject($bgp)
    {
        $this->bgp = $bgp;
    }


    function pid2spreadsheet($pid)
    {
        $exclude_list = array('FezACML','FezMD','RELS-EXT','PremisEvent');
        $exclude_prefix = array('presmd','thumbnail','web','preview');
        $acceptable_roles = array("Viewer", "Community_Administrator", "Editor", "Creator", "Annotator");
        $spreadsheet = &$this->spreadsheet;

        $record = new RecordGeneral($pid);
        if ($record->checkExists() && $record->canView(false)) {
            $datastreams = $record->getDatastreams();
            $spreadsheet->addRow();
            $spreadsheet->addValue($pid,'PID');
            $parents = $record->getParents();
            foreach ($parents as $parent) {
                foreach ($parent['title'] as $title) {
                    $spreadsheet->addValue($title,'Parent Record');
                }
            }
            $status = $record->getPublishedStatus(true);
            $spreadsheet->addValue($status,'Status');
            $doctype = $record->getDocumentType();
            $spreadsheet->addValue($doctype,'Document Type');
            // get the metadata columns
            $details = $record->getDetails();
            foreach ($details as $xsdmf_id => $value) {
                if (!is_array($value)) {
                    $value = array($value);
                }
                $xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
                $xsd_id = XSD_Display::getParentXSDID($xsdmf_details['xsdmf_xdis_id']);
                $datastream = Doc_Type_XSD::getTitle($xsd_id);
                if (($xsdmf_details['xsdmf_element'] == '!submitting_author') 
                        || ($xsdmf_details['xsdmf_html_input'] == 'author_suggestor')) {
                    $metaArray = array();
                    foreach ($value as $key => $sauth) {
                        if (is_numeric($sauth)) {
                             $auth_name = Author::getFullname($sauth);
                             $auth_id = Author::getOrgStaffId($sauth);
                        } else {
                             $auth_name = null;
                             $auth_id = null;
                        }
                        if (!empty($auth_id) && is_numeric($auth_id)) {
                             $metaArray[$xsdmf_details['xsdmf_title'].' Aurion ID'][$key] = $auth_id;
                        } else {
                            $metaArray[$xsdmf_details['xsdmf_title'].' Aurion ID'][$key] = 'unknown';
                        }
                        if (!empty($auth_name)) {
                            $metaArray[$xsdmf_details['xsdmf_title'].' Aurion Name'][$key] = $auth_name;
                        } else {
                            $metaArray[$xsdmf_details['xsdmf_title'].' Aurion Name'][$key] = 'unknown';
                        }
                    }
                    $spreadsheet->addArray($metaArray);
                } else {
                    Error_Handler::logError($datastream);
                    if (in_array($datastream,$exclude_list) || empty($xsdmf_details['xsdmf_show_in_view'])) {
                        Error_Handler::logError('skipping');
                        continue;
                    }
                    foreach ($value as $value_item) {
                        $spreadsheet->addValue($value_item,$xsdmf_details['xsdmf_title']);
                    }
                }
            }
            // get info about the attached binary files
            // get info about links
            foreach ($datastreams as $ds) {
                if ($ds['controlGroup'] == 'R' 
                        && !in_array(substr($ds['ID'],0,strpos($ds['ID'],'_')), $exclude_prefix)
                        && Auth::checkAuthorisation($pid, $ds['ID'], $acceptable_roles, '', null, false)
                        ) {
                    $spreadsheet->addValue($ds['label'], 'Link Label');
                    $spreadsheet->addValue($ds['location'], 'Link Location');
                } elseif ($ds['controlGroup'] == 'M' 
                        && !in_array(substr($ds['ID'],0,strpos($ds['ID'],'_')), $exclude_prefix)
                        && Auth::checkAuthorisation($pid, $ds['ID'], $acceptable_roles, '', null, false)) {
                    $spreadsheet->addValue($ds['ID'], 'File Datastream ID');
                    $spreadsheet->addValue($ds['label'], 'File Label');
                    $spreadsheet->addValue($ds['MIMEType'], 'File MIME Type');
                    $spreadsheet->addValue($ds['size'], 'File Size');
                }
            }
            $this->record_count++;
            if ($this->bgp) {
                $this->bgp->setProgress($this->record_count); 
                $this->bgp->setStatus($record->getTitle()); 
            }
            $children = $record->getChildrenPids();
            if ($children) {
                foreach ($children as $child) {
                    $this->pid2spreadsheet($child);
                }
            } 
        } else {
            if ($this->bgp) {
                $this->bgp->setStatus("Access Denied or record doesn't exist (".$pid.")");
            }
        }
    }
    
}
?>
