<?php

//This is the final script used to download the live scopus data on 1/3/2013
//A second run done with this script on 07/03/2013 with the 60087457 afid

ini_set("display_errors", 1);

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'/public/config.inc.php';
include_once(APP_INC_PATH . 'class.scopus_service.php');
include_once(APP_INC_PATH . 'class.user.php');

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
if ((php_sapi_name()==="cli") || (User::isUserSuperAdministrator($isUser))) {
$dbFile = dirname(__FILE__).'/scopusData.s3db';
//$outputFile = dirname(__FILE__).'/scopusLiveData_181113.xml';
$outputFile = '/espace_san/dev/scopusLiveData_181113.xml';

//echo $dbFile; exit;
$db = new PDO('sqlite:'.$dbFile);

// clear the table out first
$query = "delete from scopusids";

$db->query($query);

$scopusService = new ScopusService(APP_SCOPUS_API_KEY);

$afids = array('60031004', '60087457');  // change these to your universities scopus affiliation ids
$rc = 0;
$time_started = Date_API::getSimpleDateUTC();
$record_count = 32564; // this is a guestimate..

  foreach($afids as $afid)
  {
    foreach(array('NOT doctype(ar)', 'doctype(ar)') as $dt)
    {
      foreach (range(2007, 2013) as $year)
      {
        $i=0;
        while($i < 5030)
        {
          $query = array('query' => 'af-id(' . $afid . ') AND pubyear IS ' . $year . ' AND ' . $dt,
            'count' => 30,
            'start' => $i,
            'view' => 'STANDARD'
          );

          $resp = $scopusService->search($query);
          //var_dump($resp);
          /*-------------------------------------------------------------*/
          $doc = new DOMDocument();
          $doc->loadXML($resp);
          $records = $doc->getElementsByTagName('identifier');



  //        echo "\nRecords: ";
          foreach ($records as $record) {
  //          echo $rc++; echo ",";
            $rc++;
            $scopusId = $record->nodeValue;

            $query = "SELECT * FROM scopusids WHERE scopus_id = '" . $scopusId . "' LIMIT 1";
            $res = $db->query($query);
            $rows = $res->fetchAll(PDO::FETCH_ASSOC);

            if(empty($rows))
            {
              //var_dump($scopusId);
              $matches = array();
              preg_match("/^SCOPUS_ID\:(\d+)$/", $scopusId, $matches);
              $scopusIdExtracted = (array_key_exists(1, $matches)) ? $matches[1] : null;
              //var_dump($scopusIdExtracted);
              $iscop = new ScopusService(APP_SCOPUS_API_KEY);
              $rec = $iscop->getRecordByScopusId($scopusIdExtracted);

              file_put_contents($outputFile, $rec, FILE_APPEND);

              $query = "INSERT OR IGNORE INTO scopusids (scopus_id) VALUES ('" . $scopusId . "')";
              $db->query($query);
            }
            else
            {
              //var_dump($rows);
              $query = "UPDATE scopusids SET count = count+1 WHERE scopus_id = '" . $scopusId . "'";
              $db->query($query);
            }
          }
          /*-------------------------------------------------------------*/
          //Null results returned
          /*if (strlen($resp) < 1900)
          {
              print_r($query);
              break;
          }*/
          $i += 30;

          $record_counter = $rc;
          $utc_date = Date_API::getSimpleDateUTC();
          //$record_counter = $ct;
          $records_left = $record_count - $record_counter;
          $time_per_object = Date_API::dateDiff("s", $time_started, $utc_date);
          $time_per_object = round(($time_per_object / $record_counter), 2);

          $exp_finish_time = new Date($utc_date);
          $exp_finish_time->addSeconds($time_per_object * $records_left);
          $exp_finish_formatted = Date_API::getFormattedDate($exp_finish_time->getTime());

          $progress = intval(100 * $record_counter / $record_count);
  //        if ($record_counter % 10 == 0 || $record_counter == $record_count) {
            $msg =    "\n(" . $record_counter . "/" . $record_count . ") ".
              "(Avg " . $time_per_object . "s per Object. " .
              "Expected Finish " . $exp_finish_formatted . ")\n";
            echo $msg;
            ob_flush();
  //        }


        }
      }
    }
  }
} else {
  echo "Access Denied.";
}