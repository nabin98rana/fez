<?php

include_once(APP_INC_PATH . "class.csv_array.php");
include_once(APP_INC_PATH . "class.author.php");


class ExportCSV {

    var $csv;
    var $bgp; // background process object for keeping track of status when running in background
    var $record_count;
    
    function export($pid)
    {
        $this->csv = new CSV_Array();
        $this->record_count = 0;
        $this->pid2csv($pid);
        $csvstr = $this->csv->toCSV();

        header('Content-type: text/csv');
        header("Content-Disposition: attachment; filename=\"export.csv\"");
        header('Pragma: private');
        header('Cache-control: private, must-revalidate'); 
        echo $csvstr;

        exit;
    }

    /**
      * Only runs in background
      */
    function export2File($pid)
    {
        $this->csv = new CSV_Array();
        $this->record_count = 0;
        $this->pid2csv($pid);
        $csvstr = $this->csv->toCSV();
        $filename = APP_PATH."exports/{$this->bgp->bgp_id}.csv";
        file_put_contents($filename, $csvstr);
        $headers = "Content-type: text/csv\n"
            ."Content-Disposition: attachment; filename=\"export.csv\"\n"
            ."Pragma: private\n"
            ."Cache-control: private, must-revalidate";

        $this->bgp->setExportFilename($filename, $headers);
    }

    function setBackgroundObject($bgp)
    {
        $this->bgp = $bgp;
    }


    function pid2csv($pid)
    {
        $csv = &$this->csv;
        $exclude_list = array('FezACML','FezMD','RELS-EXT');
        $exclude_prefix = array('presmd','thumbnail','web','preview');
        $acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");

        $record = new RecordGeneral($pid);
        if ($record->checkExists() && $record->canView(false)) {
            $datastreams = $record->getDatastreams();
            $csv->addRow();
            $csv->addValue($pid,'PID');
            $parents = $record->getParents();
            foreach ($parents as $parent) {
                foreach ($parent['title'] as $title) {
                    $csv->addValue($title,'Parent Record');
                }
            }
            $status = $record->getPublishedStatus(true);
            $csv->addValue($status,'Status');
            $doctype = $record->getDocumentType();
            $csv->addValue($doctype,'Document Type');

            $additional_notes = $record->getDetailsByXSDMF_element('!additional_notes');
            $csv->addValue($additional_notes,'Additional Notes');

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
                    $csv->addArray($metaArray);
                } elseif ($ds['controlGroup'] == 'R' 
                        && !in_array(substr($ds['ID'],0,strpos($ds['ID'],'_')), $exclude_prefix)
                        && Auth::checkAuthorisation($pid, $ds['ID'], $acceptable_roles, '', null, false)
                        ) {
                    $csv->addValue($ds['label'], 'Link Label');
                    $csv->addValue($ds['location'], 'Link Location');
                } elseif ($ds['controlGroup'] == 'M' 
                        && !in_array(substr($ds['ID'],0,strpos($ds['ID'],'_')), $exclude_prefix)
                        && Auth::checkAuthorisation($pid, $ds['ID'], $acceptable_roles, '', null, false)) {

                    $csv->addValue($ds['ID'], 'File Datastream ID');
                    $csv->addValue($ds['label'], 'File Label');
                    $csv->addValue($ds['MIMEType'], 'File MIME Type');
                    $csv->addValue($ds['size'], 'File Size');
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
                    $this->pid2csv($child);
                }
            }
        }

        return $csv;

    }
}
