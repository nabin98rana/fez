<?php

require_once 'config.inc.php';
require_once 'include/class.scopus_service.php';
require_once 'include/class.scopus_record.php';
require_once 'include/class.record.php';

/*require_once '../../../../public/config.inc.php';
require_once '../../../../public/include/class.scopus_service.php';
require_once '../../../../public/include/class.scopus_record.php';
require_once '../../../../public/include/class.record.php';*/


/**
 * Test de-duping logic for Scopus data
 * @author uqcmaj
 * @since January 2013
 *
 */
class ScopusTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test pids to remove
     * @var array
     */
    protected $_savedPids = array();
    
    protected $_scopusIDCurrent = null;
    
    /**
     * Add pids to remove to the _savedPids array by scopus id.
     * Used as a clean sweep mechanism in case any test data
     * was left behind by any other test data removal mechanism.
     * @param string $scopusId
     */
    protected function getPIDsByScopusId($scopusId)
     {
         
        if($scopusId)
        {
            $pidSet = Record::getPIDsByScopusID($scopusId);
            
        }

        for($i=0;$i<count($pidSet);$i++)
        {
            if(!in_array($pidSet[$i]['rek_scopus_id_pid'], $this->_savedPids))
            {
                $this->_savedPids[] = $pidSet[$i]['rek_scopus_id_pid'];
            }
        }
     }
     
     /**
      * Remove anything in the _savedPids array and also
      * check for any others to remove matched by 
      * scopus id or passed in manually to be sure.
      * @param array $andThese
      * @param string $scopusId
      */
     protected function removeAllTestPIDs($andThese=null, $scopusId=null)
     {
         if(is_array($andThese))
         {
             foreach($andThese as $ePID)
             {
                 if(!in_array($ePID, $this->_savedPids))
                 {
                     $this->_savedPids[] = $ePID;
                 }
             }
         }
         
         if($scopusId)
         {
             $this->getPIDsByScopusId($scopusId);
         }
         
         foreach($this->_savedPids as $sPID)
         {
             $this->removeTestData($sPID);
         }
         
//          $this->_savedPids = array();
     }
    
    /**
     * Load required test data. Yes, there's a setUp method
     * in PHPUnit; no, it's not suitable for what's being done here.
     * @param string $file
     * @return string
     */
    protected function loadTestData($file)
    {
        $xml = file_get_contents($file);
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $records = $doc->getElementsByTagName('abstracts-retrieval-response');
        $rec = $records->item(0);
    
        $csr = new ScopusRecItem();
         
        $nameSpaces = array(
                   'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
                   'dc' => "http://purl.org/dc/elements/1.1/",
                   'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
        );
    
        $xmlDoc = new DOMDocument();
        $xmlDoc->appendChild($xmlDoc->importNode($rec, true));
          
        $csr->load($xmlDoc->saveXML(), $nameSpaces);
        $savedPid = $csr->save();

        //Prepare to clean up test data.
        $this->_savedPids[] = $savedPid;
        $this->_scopusIDCurrent = $csr->__get('_scopusId');
        $this->getPIDsByScopusId($csr->__get('_scopusId'));
         
        return $savedPid;
    }

    /**
     * Perform actual test data removal.
     * @param string $pid
     */
    protected function removeTestData($pid)
    {
        if(preg_match("/^[A-Z]{2}\:[0-9]+$/", trim($pid)))
         {
             Record::removeIndexRecord($pid);
         }
    }

    /**
     * Perform a liken test
     * @param string $xml
     * @return string
     */
    protected function saveUpdateAbstract($xml)
    {
         
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $records = $doc->getElementsByTagName('abstracts-retrieval-response');
        $rec = $records->item(0);
         
        $csr = new ScopusRecItem();
         
        $nameSpaces = array(
                     'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
                     'dc' => "http://purl.org/dc/elements/1.1/",
                     'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
        );
         
        $xmlDoc = new DOMDocument();
        $xmlDoc->appendChild($xmlDoc->importNode($rec, true));
     
        $csr->load($xmlDoc->saveXML(), $nameSpaces);
        
        //Prepare to clean up test data
        $this->getPIDsByScopusId($csr->__get('_scopusId'));
        
        return $csr->liken();
    }
     
    /**
     * Scopus Id matches
     * Title matches
     * Start page matches
     * End page matches
     * Volume matches
     * Pubmed Id is empty in the DL record
     *
     * This should update.
     */
    public function testSaveUpdateScopusIdTitleStartEndVolumeDOIMatches()
    {
        $testPid = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractScopusIdMatch.xml');
    
        $xml = file_get_contents(APP_PATH.'../tests/application/Unit/scopus/sampleAbstracts02.xml');
        $likened = $this->saveUpdateAbstract($xml);
         
        $this->removeAllTestPIDs(array($testPid));

        $this->assertEquals('UPDATE', $likened);
    }
     
    /**
     * Scopus Id matches
     * Title mismatches
     * Start matches
     * End is matches
     * Volume matches
     * Pubmed Id matches
     * DOI matches
     *
     * This should UPDATE though it will never do
     * the start/end/volume check.
     */
    public function testSaveUpdateAllButTitleMatches()
    {
        $testPid = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractAllButTitleMatchLocal.xml');
        
        $xml = file_get_contents(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractAllButTitleMatchDL.xml');
        $likened = $this->saveUpdateAbstract($xml);
         
        $this->removeAllTestPIDs(array($testPid));
        
        $this->assertEquals('UPDATE', $likened);
    }
    
    /**
     * Update functionality is passed data known to match a local record
     * 
     * Update method should return true
     */
    public function testPIDUpdate()
    {
        $xml = file_get_contents(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractUpdateTest.xml');
         
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $records = $doc->getElementsByTagName('abstracts-retrieval-response');
        $rec = $records->item(0);
    
        $csr = new ScopusRecItem();
    
        $nameSpaces = array(
                              'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
                              'dc' => "http://purl.org/dc/elements/1.1/",
                              'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
        );
    
        $xmlDoc = new DOMDocument();
        $xmlDoc->appendChild($xmlDoc->importNode($rec, true));
    
        $csr->load($xmlDoc->saveXML(), $nameSpaces);
        $testDataPid = $csr->save();
         
        $res = $csr->update($testDataPid);
         
        $this->removeAllTestPIDs($testDataPid, $csr->__get('_scopusId'));
        
        $this->assertEquals(true, $res);
    }
     
    /**
     * Scopus Id matches
     * Title matches
     * Start is empty in the DL record
     * End is empty in the DL record
     * Volume is empty in the DL record
     * Pubmed Id is empty in the DL record
     * 
     * This should update.
     */
     public function testSaveUpdateScopusIdTitleMatches()
     {
         $testPid = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractVolumePageEmptyLocal.xml');
        
         $xml = file_get_contents(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractVolumePageEmptyDL.xml');
         $likened = $this->saveUpdateAbstract($xml);
         
         $this->removeTestData($testPid);
         $this->removeAllTestPIDs(array($testPid));
        
         $this->assertEquals('UPDATE', $likened);
     }
     
     /**
      * Check that a Scopus import collection has been set
      */
     public function testScopusImportCollectionSet()
     {
         $this->assertRegExp("/^UQ\:\d+/", APP_SCOPUS_IMPORT_COLLECTION);
     }
     
     /**
      * Check to see that new PIDs are being saved to a collection
      */
     public function testSaveNewInScopusImportCollection()
     {
         //Set the Scopus import collection to a random collection for the purpose of this test if one is not set explicitly
         if(!defined('APP_SCOPUS_IMPORT_COLLECTION'))
         {
             $db = DB_API::get();
             $query = "SELECT rek_ismemberof FROM _fez_toxic4.fez_record_search_key_ismemberof where rek_ismemberof like 'UQ:%' LIMIT 5,1";
             $res = $db->fetchAll($query);
             define('APP_SCOPUS_IMPORT_COLLECTION', $res[0]['rek_ismemberof']);
         }
         
         $xml = file_get_contents(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractUpdateTest.xml');
          
         $doc = new DOMDocument();
         $doc->loadXML($xml);
         $records = $doc->getElementsByTagName('abstracts-retrieval-response');
         $rec = $records->item(0);
     
         $csr = new ScopusRecItem();
     
         $nameSpaces = array(
                   'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
                   'dc' => "http://purl.org/dc/elements/1.1/",
                   'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
         );
     
         $xmlDoc = new DOMDocument();
         $xmlDoc->appendChild($xmlDoc->importNode($rec, true));
     
         $csr->load($xmlDoc->saveXML(), $nameSpaces);
         $testDataPid = $csr->save(null, APP_SCOPUS_IMPORT_COLLECTION);
     
         $inTestCollection = Record::getPIDsByScopusID($csr->__get('_scopusId'), true);
         
         $this->assertEquals(false, empty($inTestCollection));
         $this->removeTestData($testDataPid);
         $this->removeAllTestPIDs($testDataPid, $csr->__get('_scopusId'));
     }
     
     /**
      * Perform a fuzzy title and IVP search using all IVP and DOI
      */
     public function testFuzzyMatchTitleAllIVP()
     {
         $testPID = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/fuzzyMatchingLocalTitleDoiSpEpVolIss.xml');
         
         //Faux downloaded record data
         $title = 'Localization of a brain sulfotransferase, SULT4A1-18, in the human, pig, gerbil, zombie and rat brain: An immunohistochemical study';
         $doi = '10.1080/09936846.2011.646069';
         $startPage = 1013;
         $volume = '45a';
         $endPage = 1029;
         $issue = 15;
         
         $searchRes = Record::getPidsByFuzzyTitle($title, $doi, $startPage, $volume, $endPage, $issue);
         $this->removeTestData($testPID);
         $this->removeAllTestPIDs(array($testPID), $this->_scopusIDCurrent);
         
         $this->assertEquals($title, $searchRes[0]["rek_title"]);
         $this->assertEquals($doi, $searchRes[0]["rek_doi"]);
         $this->assertEquals($startPage, $searchRes[0]["rek_start_page"]);
         $this->assertEquals($volume, $searchRes[0]["rek_volume_number"]);
         $this->assertEquals($endPage, $searchRes[0]["rek_end_page"]);
         $this->assertEquals($issue, $searchRes[0]["rek_issue_number"]);         
     }
     
     /**
      * Perform fussy title search with doi start page and volume
      */
     public function testFuzzyMatchTitleDOIStartpVol()
     {
         $testPID = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/fuzzyMatchingLocalTitleDoiSpVol.xml');
          
         //Faux downloaded record data
         $title = 'Localization of a brain sulfotransferase, SULT4A1-18, in the human, pig, gerbil, zombie and rat brain: An immunohistochemical study';
         $doi = '10.1080/09936846.2011.646069';
         $startPage = 1013;
         $volume = '45a';
         $endPage = 1029;
         $issue = 15;
          
         $searchRes = Record::getPidsByFuzzyTitle($title, $doi, $startPage, $volume, $endPage, $issue);
         $this->removeTestData($testPID);
         $this->removeAllTestPIDs(array($testPID), $this->_scopusIDCurrent);
          
         $this->assertEquals($title, $searchRes[0]["rek_title"]);
         $this->assertEquals($doi, $searchRes[0]["rek_doi"]);
         $this->assertEquals($startPage, $searchRes[0]["rek_start_page"]);
         $this->assertEquals($volume, $searchRes[0]["rek_volume_number"]);
         $this->assertFalse($endPage == $searchRes[0]["rek_end_page"]);
         $this->assertFalse($issue == $searchRes[0]["rek_issue_number"]);
     }
     
     /**
     * Perform fussy title search with doi
     */
     public function testFuzzyMatchTitleDOI()
     {
         $testPID = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/fuzzyMatchingLocalTitleDoi.xml');
     
         //Faux downloaded record data
         $title = 'Localization of a brain sulfotransferase, SULT4A1-18, in the human, pig, gerbil, zombie and rat brain: An immunohistochemical study';
         $doi = '10.1080/09936846.2011.646069';
         $startPage = 1013;
         $volume = '45a';
         $endPage = 1029;
         $issue = 15;
     
         $searchRes = Record::getPidsByFuzzyTitle($title, $doi, $startPage, $volume, $endPage, $issue);
         $this->removeTestData($testPID);
         $this->removeAllTestPIDs(array($testPID), $this->_scopusIDCurrent);
     
         $this->assertEquals($title, $searchRes[0]["rek_title"]);
         $this->assertEquals($doi, $searchRes[0]["rek_doi"]);
         $this->assertFalse($startPage == $searchRes[0]["rek_start_page"]);
         $this->assertFalse($volume == $searchRes[0]["rek_volume_number"]);
         $this->assertFalse($endPage == $searchRes[0]["rek_end_page"]);
         $this->assertFalse($issue == $searchRes[0]["rek_issue_number"]);
     }
}