<?php

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.workflow_status.php");

function pid2csv($pid)
{
    $csv = '';

    $record = new RecordGeneral($pid);


    $datastreams = $record->getDatastreams();
    $csv .= "\"PID:{$pid}\"\n";
    // Metadata
    foreach ($datastreams as $ds) {
        if ($ds['controlGroup'] == 'X') {
            $metaArray = Fedora_API::callGetDatastreamContents($pid, $ds['ID']);
            $csv .= "\"Metadata:{$ds['ID']}\"\n";
            $csv .= Misc::arrayToCSV($metaArray);
        }
    }
    $csv .= "\n\n";

    $children = $record->getChildrenPids();
    if ($children) {
        foreach ($children as $child) {
            $csv .= pid2csv($child);
        }
    }
    
    return $csv;

}

$pid = $this->pid;
$csv = pid2csv($pid);


header('Content-type: text/csv');
header("Content-Disposition: attachment; filename=\"export.csv\"");
header('Pragma: private');
header('Cache-control: private, must-revalidate'); 
echo $csv;

exit;

?>
