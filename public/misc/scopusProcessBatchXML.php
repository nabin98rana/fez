<?
ini_set("display_errors", 1);

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . 'class.scopus_service.php');
include_once(APP_INC_PATH . 'class.scopus_record.php');
include_once(APP_INC_PATH . 'class.record.php');

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

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
while($xr->name === 'abstracts-retrieval-response')
{
  echo "\n";
  var_dump($ct);
  file_put_contents($process_dir.'scopuscount.txt', $ct);
  //echo "\n";
  ob_flush();

  if (true == true) {
//  if($ct == 259) { //$ct is an example of a record that it fails on.
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
  }
  $nx = $xr->next('abstracts-retrieval-response');
  //var_dump($nx);


  $ct++;
}