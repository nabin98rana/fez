<?php

include_once("../config.inc.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.workflow_status.php");

function pid2csv($pid)
{
    $csv = '';
    $exclude_list = array('FezACML','FezMD','RELS-EXT');
    $exclude_prefix = array('presmd','thumbnail','web','preview');

    $record = new RecordGeneral($pid);
    if ($record->canView()) {
        $datastreams = $record->getDatastreams();
        $csv .= "\"PID:{$pid}\"\n";
        // Metadata
        foreach ($datastreams as $ds) {
            if ($ds['controlGroup'] == 'X' 
                    && !in_array($ds['ID'], $exclude_list)
                    && !in_array(substr($ds['ID'],0,strpos($ds['ID'],'_')), $exclude_prefix)
               ) {
                $metaArray = Fedora_API::callGetDatastreamContents($pid, $ds['ID']);
                $csv .= "\"Metadata:{$ds['ID']}\"\n";
                $csv .= Misc::arrayToCSV($metaArray);
            }
            if ($ds['controlGroup'] == 'R' 
                    && !in_array($ds['ID'], $exclude_list)
                    && !in_array(substr($ds['ID'],0,strpos($ds['ID'],'_')), $exclude_prefix)
               ) {
                $csv .= "\"{$ds['label']}\",\"{$ds['location']}\"\n";
            }
        }
        $csv .= "\n\n";

        $children = $record->getChildrenPids();
        if ($children) {
            foreach ($children as $child) {
                $csv .= pid2csv($child);
            }
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
