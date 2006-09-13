<?php

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
        $filename = APP_PATH."exports/{$this->bgp->bgp_id}.xml";
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
        $spreadsheet = &$this->spreadsheet;
        $exclude_list = array('FezACML','FezMD','RELS-EXT');
        $exclude_prefix = array('presmd','thumbnail','web','preview');
        $acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");

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

            $additional_notes = $record->getDetailsByXSDMF_element('!additional_notes');
            $spreadsheet->addValue($additional_notes,'Additional Notes');

            // Metadata
            foreach ($datastreams as $ds) {
                if ($ds['controlGroup'] == 'X' 
                        && !in_array($ds['ID'], $exclude_list)
                        && !in_array(substr($ds['ID'],0,strpos($ds['ID'],'_')), $exclude_prefix)
                        && Auth::checkAuthorisation($pid, $ds['ID'], $acceptable_roles, '', null, false)
                   ) {
                    $metaArray = Fedora_API::callGetDatastreamContents($pid, $ds['ID']);
                    // Special case lookup for author id
                    if (isset($metaArray['submitting_author']) && is_array($metaArray['submitting_author'])) {
                        foreach ($metaArray['submitting_author'] as $key => $sauth) {
                            if (is_numeric($sauth)) {
                                $auth_name = Author::getFullname($sauth);
                                $metaArray['submitting_author'][$key] = $auth_name;
                                $auth_id = Author::getOrgStaffId($sauth);
                                $metaArray['submitting_author_org_id'][$key] = $auth_id;
                            }
                        }
                    }
                    if (isset($metaArray['authorID']) && is_array($metaArray['authorID'])) {
                        foreach ($metaArray['authorID'] as $key => $sauth) {
                            if (is_numeric($sauth)) {
                                $auth_id = Author::getOrgStaffId($sauth);
                                if (empty($auth_id)) {
                                    $auth_id = 'unknown';
                                }
                                $metaArray['authorID'][$key] = $auth_id;
                            } else {
                                $metaArray['authorID'][$key] = 'unknown';
                            }
                        }
                    }
                    $spreadsheet->addArray($metaArray);
                } elseif ($ds['controlGroup'] == 'R' 
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
        }

        return $spreadsheet;

    }
}
