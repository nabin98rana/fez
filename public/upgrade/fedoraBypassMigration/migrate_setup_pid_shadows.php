<?php
/**
 * The purpose of this script is to
 * set up shadow tables with version information for all pids inc deleted
 *
 * This is a one-off migration script as part of Fedora-less project.
 *
 * Fedora Bypass must be off!
 *
 * Data is currently not checked to see if it's pre existing
 *
 */
include_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'config.inc.php';

error_reporting(1);
set_time_limit(0);

$fedoraPids = Fedora_Direct_Access::fetchAllFedoraPIDs('','');

//Function to be used by uasort to sort array in reverse
function cmpVersionDates($a, $b)
{
    if ($a[createDate] == $b[createDate]) {
        return 0;
    }
    return ($a[createDate] < $b[createDate]) ? -1 : 1;
}
$fedoraPids = array('UQ:13497');
foreach ($fedoraPids as $pid) {
    //$time_start = microtime(true);
    $parms = array('pid' => $pid, 'dsID' => 'MODS');
    $datastreamVersions = Fedora_API::openSoapCall('GetDatastreamHistory', $parms);

    uasort($datastreamVersions, 'cmpVersionDates');

    //Check Multiple versions
    $previousVersionXml = null;

    //If only one place in array so foreach works correctly
    if (!is_array($datastreamVersions[0])) {
        $datastreamVersions = array($datastreamVersions);
    }

    echo 'Running';
    $previousDatastreamVersionData =  null;
    $xdis_id = XSD_HTML_Match::getDisplayType($pid);

    foreach($datastreamVersions as $datastreamVersion) {
        //Don't use solr index for data  $skipIndex = true
        //$datastreamVersion[createDate] = '2012-03-08T04:01:52.209Z';

        $xsdDispObj = new XSD_DisplayObject($xdis_id);
        $datastreamVersionData = $xsdDispObj->getXSDMF_Values($pid, $datastreamVersion[createDate], true);
        //echo microtime(true) - $time_start. '<br />';

        saveData($datastreamVersionData, $previousDatastreamVersionData, $pid, $datastreamVersion[createDate]);
        $previousDatastreamVersionData =  $datastreamVersionData;
    }
}

function saveData($datastreamVersionData, $previousDatastreamVersionData, $pid, $asOfDateTime) {
    if ($previousDatastreamVersionData) {

        //Current the whole rek_search_key is saved if anything is different
        $rekSearchKeySame = true;
        $rekSearchKeyDetails = array();
        $diffArray = array();
        foreach($datastreamVersionData as $xdsmf_id => $searchKeys) {
            $xsdDetails = XSD_HTML_Match::getDetailsByXSDMF_ID($xdsmf_id);
            $searchKeyDetails = Search_Key::getDetails($xsdDetails['xsdmf_sek_id']);
            if ($searchKeyDetails['sek_relationship'] == '0') {
                $rekSearchKeyDetails[$xdsmf_id] = $datastreamVersionData[$xdsmf_id];
            }
            if($datastreamVersionData[$xdsmf_id] != $previousDatastreamVersionData[$xdsmf_id]) {
                if ($searchKeyDetails['sek_relationship'] == '0') {
                    $rekSearchKeySame = false;
                } else {
                    $diffArray[$xdsmf_id] = $datastreamVersionData[$xdsmf_id];
                }
            }
        }

        if (!$rekSearchKeySame) {
            $diffArray = $rekSearchKeyDetails + $diffArray;
        }
        $datastreamVersionData = $diffArray;
    }

    foreach ($datastreamVersionData as $xsdmf_id => $searchKeys) {
        $xsdDetails = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
        $sekDetails = Search_Key::getDetails($xsdDetails['xsdmf_sek_id']);
        if (is_numeric($sekDetails['sek_relationship'])){
            $searchKeyData[$sekDetails['sek_relationship']][$sekDetails['sek_title_db']] = array(
                "xsdmf_id" => $xsdmf_id,
                "xsdmf_value" => $searchKeys,
            );
        }
    }
    Record::updateSearchKeys($pid, $searchKeyData, true, $asOfDateTime );
    echo "";

}
