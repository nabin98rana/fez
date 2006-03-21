<?php

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.workflow_status.php");
include_once(APP_INC_PATH . "class.csv_array.php");

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
        // Metadata
        foreach ($datastreams as $ds) {
            if ($ds['controlGroup'] == 'X' 
                    && !in_array($ds['ID'], $exclude_list)
                    && !in_array(substr($ds['ID'],0,strpos($ds['ID'],'_')), $exclude_prefix)
                    && Auth::checkAuthorisation($pid, $ds['ID'], $acceptable_roles, null, false)
               ) {

                $metaArray = Fedora_API::callGetDatastreamContents($pid, $ds['ID']);
                $csv->addArray($metaArray);
            }
            if ($ds['controlGroup'] == 'R' 
                    && !in_array($ds['ID'], $exclude_list)
                    && !in_array(substr($ds['ID'],0,strpos($ds['ID'],'_')), $exclude_prefix)
               ) {
                $csv->addValue($ds['label'], 'Link Label');
                $csv->addValue($ds['location'], 'Link Location');
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
