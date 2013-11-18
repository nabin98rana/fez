<?
//This is an example script of how you could create a dedupe report on a bulk scopus xml file

ini_set("display_errors", 1);

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . 'class.scopus_service.php');
include_once(APP_INC_PATH . 'class.scopus_record.php');
include_once(APP_INC_PATH . 'class.record.php');
include_once(APP_INC_PATH . 'class.user.php');

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

if ((php_sapi_name()==="cli") || (User::isUserSuperAdministrator($isUser))) {

  $process_dir = '/espace_san/dev/';

  echo "Starting...\n\n";

  $xr = new XMLReader();

  $xr->open($process_dir.'scopusLiveData5.xml');

  $nameSpaces = array(
    'd' => 'http://www.elsevier.com/xml/svapi/abstract/dtd',
    'ce' => 'http://www.elsevier.com/xml/ani/common',
    'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
    'dc' => "http://purl.org/dc/elements/1.1/",
    'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
  );

  $stmt = "TRUNCATE " . APP_TABLE_PREFIX . "scopus_import_stats ";
  try {
    $db->exec($stmt);
  } catch (Exception $ex) {
    $log->err($ex->getMessage());
    exit;
  }

  while($xr->read() && $xr->name !== 'abstracts-retrieval-response');

  $ct = 0;


  $time_started = Date_API::getSimpleDateUTC();
  $record_count = 32564;
  //$ri = new ScopusRecItem();

  //$xpath = $ri->getXPath(file_get_contents($process_dir.'scopusLiveData5.xml'));

  //$records_found = $xpath->query('/abstracts-retrieval-response');
  //echo "found count ".$records_found; exit;

  while($xr->name === 'abstracts-retrieval-response')
  {
  //  echo "\n";
  //  var_dump($ct);
    //file_put_contents($process_dir.'scopuscount.txt', $ct);
    //echo "\n";
  //  ob_flush();

  //  if (true == true) {
  //  if($ct >= 31601) { //$ct is an example of a record that it fails on.
    $csr = new ScopusRecItem();
    $csr->setInTest(true); //Make sure that $csr->setStatsFile() has a sane value before using this.
    $csr->setLikenAction(true); //Make sure that $csr->setStatsFile() has a sane value before using this.
    //var_dump($xr->name);
    $rec = $xr->expand();

    $xmlDoc = new DOMDocument();
    $xmlDoc->appendChild($xmlDoc->importNode($rec, true));

    $csr->load($xmlDoc->saveXML(), $nameSpaces);
    //var_dump($csr->__get('_scopusId'));
    //$csr->setStatsFile('/var/www/scopusimptest/scopusDownloaded.s3db');

  //    {
    $csr->liken();
  //    }
    unset($csr);
  //  }
    $nx = $xr->next('abstracts-retrieval-response');

    $ct++;
  //  if($ct > 31601) {
    $utc_date = Date_API::getSimpleDateUTC();
    $record_counter = $ct;
    $records_left = $record_count - $record_counter;
    $time_per_object = Date_API::dateDiff("s", $time_started, $utc_date);
    $time_per_object = round(($time_per_object / $record_counter), 2);

    $exp_finish_time = new Date($utc_date);
    $exp_finish_time->addSeconds($time_per_object * $records_left);
    $exp_finish_formatted = Date_API::getFormattedDate($exp_finish_time->getTime());

    $progress = intval(100 * $record_counter / $record_count);
    if ($record_counter % 10 == 0 || $record_counter == $record_count) {
      $msg =    "\n(" . $record_counter . "/" . $record_count . ") ".
        "(Avg " . $time_per_object . "s per Object. " .
        "Expected Finish " . $exp_finish_formatted . ")\n";
      echo $msg;
      ob_flush();
    }
  //  } else {
  ////    $ct++;
  //    var_dump($ct);
  //    ob_flush();
  //
  //  }

    /*
     // can be uncommented to do a speed run just to get the count of a very large file
     $nx = $xr->next('abstracts-retrieval-response');
    $ct++;
    var_dump($ct);
    ob_flush();*/


  }
} else {
  echo "Access Denied.";
}