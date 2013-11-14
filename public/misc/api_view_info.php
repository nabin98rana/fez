<?php

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once APP_INC_PATH.'class.wok_service.php';
include_once(APP_INC_PATH . 'class.scopus_service.php');

$isUser = Auth::getUsername();
if (User::isUserAdministrator($isUser)) {


    $id = @$_REQUEST["id"];
    if ($id) {

        if (strlen($id) == 15) {
            $wok_ws = new WokService(FALSE);
            $result = $wok_ws->retrieveById($id);

        } else {
            $scopusService = new ScopusService(APP_SCOPUS_API_KEY);
            $result = $scopusService->getRecordByScopusId($id);
        }
        print_r($result);
    } else {
?>
        <form name="input" method="get">
            <h2>Raw output we recieve for Scopus or WOS via their API's we use when we import one record</h2>
            Do not include the prefixes 2-s2.0- or WOS:<br /> <br />
            Scopus/Wok ID: <input type="text" name="id">
            <input type="submit" value="Submit">
        </form>
        On the following page you can view source on the web page to see the result better formated.
<?php
    }
} else {
    echo "Please login as Admin";
}