<?php

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once APP_INC_PATH.'class.wok_service.php';
include_once(APP_INC_PATH . 'class.scopus_service.php');
include_once(APP_INC_PATH . 'class.ulrichs.php');

$isUser = Auth::getUsername();
if (User::isUserAdministrator($isUser)) {

    $id = @$_REQUEST["id"];
    $id_sherpa = @$_REQUEST["id_sherpa"];
    $id_ulrichs = @$_REQUEST["id_ulrichs"];
    $pid_solr = @$_REQUEST["pid_solr"];
    $links_amr_pid = @$_REQUEST["links_amr_pid"];

    if (!empty($id_sherpa)) {
        $sr = new SherpaRomeo();
        $result = $sr::loadXMLData($id_sherpa);
        header("Content-type: text/xml; charset=utf-8");
        print_r($result);
    } else if ($id) {
        $id = str_ireplace('WOS:', '', $id);
        $id = str_ireplace('2-s2.0-', '', $id);
        if (strlen($id) == 15) {
            $wok_ws = new WokService(FALSE);
            $result = $wok_ws->retrieveById($id);

        } else {
            $scopusService = new ScopusService(APP_SCOPUS_API_KEY);
            $result = $scopusService->getRecordByScopusId($id);
        }
        header("Content-type: text/xml; charset=utf-8");
        print_r($result);
    } else if ($id_ulrichs) {
        $ulrichs = new Ulrichs();
        $result = $ulrichs::getXMLFromUlrichs('issn:'.$id_ulrichs);
        header("Content-type: text/xml; charset=utf-8");
        print_r($result);
    } else if ($pid_solr) {
        header("Content-type: text/xml; charset=utf-8");
        print file_get_contents("http://" . APP_SOLR_HOST . ":" . APP_SOLR_PORT . "" . APP_SOLR_PATH . 'select/?q=pid_t:"'.$pid_solr.'"');
    } else if ($links_amr_pid) {
        $laq = LinksAmrQueue::get();
        $pids = array();
        $pids[0] = '$links_amr_pid';
        $response = $laq->sendToLinksAmr($links_amr_pid, true);
        if ($response) {
            header("Content-type: text/xml; charset=utf-8");
            echo $response->saveXML();
        } else {
            echo "No response. It may already be assigned a ISI Loc, in the Temporary Duplicates collection or you may be missing ";
            echo "enough info for the data to be submitted .You need -  DOI or (title vol issue page) or (title vol issue an) or (first_author issn vol issue page) or (first_author issn vol issue an). (an - article number, mostly unused)";
        }
    }else {
?>
        <form name="input" method="get">
            <h2>Raw output we receive for Scopus or WOS via their API's we use when we import one record</h2>
            <br />
            Scopus/Wok ID: <input type="text" name="id">
            <input type="submit" value="Submit">
        </form>
        <br />
        <form name="sherpa" method="get">
            <h2>Raw output we receive for Sherpa/Romeo via their API's</h2>
            <br />
            Sherpa/Romeo - Journal ISSN: <input type="text" name="id_sherpa">
            <input type="submit" value="Submit Sherpa ISSN">
        </form>
        <br />
        <form name="ulrichs" method="get">
            <h2>Raw output we receive for Ulrichs via their API's</h2>
            <br />
            Ulrichs - ISSN: <input type="text" name="id_ulrichs">
            <input type="submit" value="Submit Ulrichs Title">
        </form>
        <br />
        <form name="solr" method="get">
            <h2>Raw output we receive for solr via our API</h2>
            <br />
            PID: <input type="text" name="pid_solr">
            <input type="submit" value="Submit PID">
        </form>
        <br />
        <form name="links_amr" method="get">
            <h2>Raw output we receive for links AMR via their API (It will match a WOS ID)</h2>
            <br />
            PID: <input type="text" name="links_amr_pid">
            <input type="submit" value="Submit PID">
        </form>
<?php
    }
} else {
    echo "Please login as Admin";
}