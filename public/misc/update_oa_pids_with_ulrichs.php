<?php
// Script to update pids with Ulrichs information
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . "class.fulltext_queue.php");

echo "Script started: " . date('Y-m-d H:i:s') . "\n";

$isUser = Auth::getUsername();
if (!empty($isUser)) {
    echo "Please logout before running";  //The test for open access needs this to be run by a non logged in user.
}

$query = "SELECT rek_issn_pid AS pid, rek_doi AS doi  FROM " . APP_TABLE_PREFIX . "record_search_key_issn
INNER JOIN " . APP_TABLE_PREFIX . "ulrichs
ON ulr_issn = rek_issn
LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_oa_status
ON rek_oa_status_pid = rek_issn_pid
LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_doi
ON rek_issn_pid = rek_doi_pid
WHERE ulr_open_access = 'true' AND rek_oa_status IS NULL
GROUP BY rek_issn_pid";

$db = DB_API::get();
$log = FezLog::get();

try {
    $result = $db->fetchAll($query);
} catch (Exception $ex) {
    $log = FezLog::get();
    $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
    return;
}

echo "Checking " . count($result) . " pids\n";
flush();
ob_flush();



// for each pid,
foreach ($result as $pidDetails) {
    $pid = $pidDetails['pid'];
    $doi = $pidDetails['doi'];
    $record = new RecordObject($pid);
    $open = hasDatastreamOpen($pid);

    $history = "";
    if($open) {
        $history = 'Ulrichs not added added, based on an open access attachment being present - OA Status = File (Publisher version)';
        $record->addSearchKeyValueList(array("OA Status"), array('453695'), true, $history);
    } else if (!empty($doi)) {
        $history = 'Ulrichs info added - OA Status = DOI, OA Embargo Days = 0';
        $record->addSearchKeyValueList(array("OA Status"), array('453693'), true, $history);
        $record->addSearchKeyValueList(array("OA Embargo Days"), array('0'), true, $history);
    } else {
        $history = 'Ulrichs info added - OA Status = Link (no DOI) based on no doi present, OA Embargo Days = 0';
        $record->addSearchKeyValueList(array("OA Status"), array('453694'), true, $history);
        $record->addSearchKeyValueList(array("OA Embargo Days"), array('0'), true, $history);
    }

    echo $pid.' '.$history."\n";
    flush();
    ob_flush();
}

echo "Script finished: " . date('Y-m-d H:i:s') . "\n";

function hasDatastreamOpen($pid)
{
    $status = Status::getID("Published");
    if ($status == Record::getSearchKeyIndexValue($pid, "Status", false)) {
        $datastreams = Fedora_API::callGetDatastreams($pid);
        foreach ($datastreams as $datastream) {
            if ($datastream['controlGroup'] == "M"
                && (!Misc::hasPrefix($datastream['ID'], 'preview_')
                    && !Misc::hasPrefix($datastream['ID'], 'web_')
                    && !Misc::hasPrefix($datastream['ID'], 'thumbnail_')
                    && !Misc::hasPrefix($datastream['ID'], 'stream_')
                    && !Misc::hasPrefix($datastream['ID'], 'presmd_'))
            ) {
                $userPIDAuthGroups = Auth::getAuthorisationGroups($pid, $datastream['ID']);
                if (in_array('Viewer', $userPIDAuthGroups)) {
                    return true;
                }
            }
        }
    }
    return false;
}
