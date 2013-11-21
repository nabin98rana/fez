<?php

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once APP_INC_PATH . 'class.wok_service.php';
include_once(APP_INC_PATH . 'class.scopus_service.php');

$isUser = Auth::getUsername();
if (User::isUserAdministrator($isUser)) {

  $wok_query = @$_REQUEST["wok_query"];
  $scopus_query = @$_REQUEST["scopus_query"];

  if ($wok_query || $scopus_query) {
//        $query = str_ireplace('WOS:', '', $query);
//        $query = str_ireplace('2-s2.0-', '', $query);
    if (strlen($wok_query) == 15) {
      $wok_ws = new WokService(FALSE);
//      $result = $wok_ws->retrieveById($query);
      // Escape characters used for wrapping title on search query
      $title = $this->_escapeSearchTitle(trim($title));

      // Title query param
      $query = 'TI=("' . $wok_query . '")';
      $first_rec = 1;
      $num_recs = 3;
      $timeSpan = '';


      if (defined('WOK_PASSWORD') && WOK_PASSWORD != '') {

        $databaseID = "WOS";
        //$editions = array("collection" => $databaseID, "edition" => $edition);
        $editions = array();
        $wok_ws = new WokService(FALSE);
        $response = $wok_ws->search($databaseID, $query, $editions, $timeSpan, $depth, "en", $num_recs);
        if (is_soap_fault($response)) {
          return '<span style="color:#fff;" id="ctWos">- ERROR</span>
                    <ol><li>Error: ' . $response->getMessage() . '</li></ol>';
        }
        $records = new DomDocument();
        $records->loadXML($response->return->records);
        $recordsNodes = $records->getElementsByTagName('REC');
        if ($records) {
          foreach ($recordsNodes as $record) {
            $xmlRecords = new WosRecItem();
            $xmlRecords->load($record);
            $matches[] = $xmlRecords->returnDataEnterForm();

          }
        }
      }


      $matchCount = count($matches);
      print_r($matches);
    } else {
      $scopusService = new ScopusService(APP_SCOPUS_API_KEY);
//    $result = $scopusService->getRecordByScopusId($query);
      $query = array('query' => "(doctype(ar)+OR+doctype(cp)+OR+doctype(bk)+OR+doctype(ch)+OR+(doctype(re)+AND+srctype(j)))+title(\""
        . urlencode(trim($scopus_query)) . "\")",
        'count' => $num_recs,
        'start' => 0,
        'view' => 'STANDARD',
      );


      $xml = $scopusService->getNextRecordSet($query);
      print_r($xml);
    }
    header("Content-type: text/xml; charset=utf-8");
    print_r($result);
  } else {
    ?>
    <form name="input" method="get">
      <h2>Raw output we recieve for Scopus or WOS via their API's we use when we
        search by title</h2>
      <br/>
      Wok: <input type="text" name="scopus_query">
      Scopus: <input type="text" name="wok_query">
      <input type="submit" value="Submit">
    </form>
  <?php
  }
} else {
  echo "Please login as Admin";
}