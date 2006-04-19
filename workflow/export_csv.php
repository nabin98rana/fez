<?php

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.workflow_status.php");
include_once(APP_INC_PATH . "class.csv_array.php");
include_once(APP_INC_PATH . "class.author.php");

function pid2csv($pid, &$csv)
{
    $exclude_list = array('FezACML','FezMD','RELS-EXT');
    $exclude_prefix = array('presmd','thumbnail','web','preview');
    $acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");

    $record = new RecordGeneral($pid);
    if ($record->checkExists() && $record->canView()) {
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

        $children = $record->getChildrenPids();
        if ($children) {
            foreach ($children as $child) {
                pid2csv($child, $csv);
            }
        }
    }
    
    return $csv;

}


$pid = $this->pid;
$csv_array = new CSV_Array();
pid2csv($pid, $csv_array);
$csv = $csv_array->toCSV();

header('Content-type: text/csv');
header("Content-Disposition: attachment; filename=\"export.csv\"");
header('Pragma: private');
header('Cache-control: private, must-revalidate'); 
echo $csv;

exit;

?>
