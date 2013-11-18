<?php

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once APP_INC_PATH.'class.wok_service.php';
include_once(APP_INC_PATH . 'class.scopus_service.php');

$isUser = Auth::getUsername();
if (User::isUserAdministrator($isUser)) {

    $id = @$_REQUEST["id"];
    if ($id) {
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
    } else {
?>
        <form name="input" method="get">
            <h2>Raw output we recieve for Scopus or WOS via their API's we use when we import one record</h2>
            <br />
            Scopus/Wok ID: <input type="text" name="id">
            <input type="submit" value="Submit">
        </form>
<?php
    }
} else {
    echo "Please login as Admin";
}