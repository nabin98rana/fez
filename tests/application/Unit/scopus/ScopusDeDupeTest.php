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
    
    /**
     * Current Scopus ID being processed.
     * @var string
     */
    protected $_scopusIDCurrent = null;
    
    /**
     * Set the Scopus record item (ScopusRecItem) to being in test
     * so that it only logs its actions without actually performimg them
     * @var boolean
     */
    protected $_recItemIntest = false;
    
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
        $csr->setInTest($this->_recItemIntest);
         
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
        $csr->setInTest($this->_recItemIntest);
         
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
        $this->_recItemIntest = true;
        $testPid = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractScopusIdMatch.xml');
    
        $xml = file_get_contents(APP_PATH.'../tests/application/Unit/scopus/sampleAbstracts02.xml');
        $likened = $this->saveUpdateAbstract($xml);
         
        $this->removeTestData($testPid);
        $this->removeAllTestPIDs(array($testPid), $this->_scopusIDCurrent);
        $this->_recItemIntest = false;

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
         
        $this->removeTestData($testPid);
        $this->removeAllTestPIDs(array($testPid), $this->_scopusIDCurrent);
        
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
         $this->removeAllTestPIDs(array($testPid), $this->_scopusIDCurrent);
        
         $this->assertEquals('UPDATE', $likened);
     }
     
     /**
     * Title is very similar but not identical (differences in punctuation marks)
     * Start matches
     * End matches
     * Volume empty in local record
     * DOI empty in local record
     * Issue empty in local record
     *
     * This should match the ST26 status.
     */
     public function testSaveUpdateFuzzyTitleMatch()
     {
         $this->_recItemIntest = true;
         $testPid = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractFuzzyDOIIVPMatchLocal.xml');
         
         $xml = file_get_contents(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractFuzzyDOIIVPMatchDL.xml');
         $likened = $this->saveUpdateAbstract($xml);
         
         $this->removeTestData($testPid);
         $this->removeAllTestPIDs(array($testPid), $this->_scopusIDCurrent);
         $this->_recItemIntest = false;
         
         $this->assertRegExp('/^ST26/', $likened[0]);
     }
     
     /**
     * Title is very similar but not identical (letters in the title differ)
     * Start matches
     * End matches
     * Volume matches
     * DOI matches
     * Issue matches
     *
     * This should UPDATE and will not enter into the higher STxx codes (ie above 09).
     */
     public function testSaveUpdateFuzzyTitleMismatch()
     {
         $this->_recItemIntest = true;
         $testPid = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractFuzzyDOIIVPTitleMismatchLocal.xml');
          
         $xml = file_get_contents(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractFuzzyDOIIVPTitleMismatchDL.xml');
         $likened = $this->saveUpdateAbstract($xml);
          
         $this->removeTestData($testPid);
         $this->removeAllTestPIDs(array($testPid), $this->_scopusIDCurrent);
         $this->_recItemIntest = false;
         
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
         $fields = array();
         $fields['_doi'] = '10.1080/09936846.2011.646069';
         $fields['_startPage'] = 1013;
         $fields['_issueVolume'] = '45a';
         $fields['_issueNumber'] = 15;
         $fields['_endPage'] = 1029;
         $fields['_title'] = 'Localization of a brain sulfotransferase, SULT4A1-18, in the human, pig, gerbil, zombie and rat brain: An immunohistochemical study';
         
         $searchRes = Record::getPidsByFuzzyTitle($fields);
         $this->removeTestData($testPID);
         $this->removeAllTestPIDs(array($testPID), $this->_scopusIDCurrent);
         
         $this->assertEquals($fields['_title'], $searchRes['data'][0]["rek_title"]);
         $this->assertEquals($fields['_doi'], $searchRes['data'][0]["rek_doi"]);
         $this->assertEquals($fields['_startPage'], $searchRes['data'][0]["rek_start_page"]);
         $this->assertEquals($fields['_issueVolume'], $searchRes['data'][0]["rek_volume_number"]);
         $this->assertEquals($fields['_endPage'], $searchRes['data'][0]["rek_end_page"]);
         $this->assertEquals($fields['_issueNumber'], $searchRes['data'][0]["rek_issue_number"]);         
     }
     
     /**
      * Perform fuzzy title search with doi start page and volume
      */
     public function testFuzzyMatchTitleDOIStartpVol()
     {
         $testPID = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/fuzzyMatchingLocalTitleDoiSpVol.xml');
          
         //Faux downloaded record data
         $fields = array();
         $fields['_doi'] = '10.1080/09936846.2011.646069';
         $fields['_startPage'] = 1013;
         $fields['_issueVolume'] = '45a';
         $fields['_issueNumber'] = 15;
         $fields['_endPage'] = 1029;
         $fields['_title'] = 'Localization of a brain sulfotransferase, SULT4A1-18, in the human, pig, gerbil, zombie and rat brain: An immunohistochemical study';
          
         $searchRes = Record::getPidsByFuzzyTitle($fields);
         $this->removeTestData($testPID);
         $this->removeAllTestPIDs(array($testPID), $this->_scopusIDCurrent);
          
         $this->assertEquals($fields['_title'], $searchRes['data'][0]["rek_title"]);
         $this->assertEquals($fields['_doi'], $searchRes['data'][0]["rek_doi"]);
         $this->assertEquals($fields['_startPage'], $searchRes['data'][0]["rek_start_page"]);
         $this->assertEquals($fields['_issueVolume'], $searchRes['data'][0]["rek_volume_number"]);
         $this->assertFalse($fields['_endPage'] == $searchRes['data'][0]["rek_end_page"]);
         $this->assertFalse($fields['_issueNumber'] == $searchRes['data'][0]["rek_issue_number"]);
     }
     
     /**
     * Perform fuzzy title search with doi
     */
     public function testFuzzyMatchTitleDOI()
     {
         $testPID = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/fuzzyMatchingLocalTitleDoi.xml');
     
         //Faux downloaded record data
         $fields = array();
         $fields['_doi'] = '10.1080/09936846.2011.646069';
         $fields['_startPage'] = 1013;
         $fields['_issueVolume'] = '45a';
         $fields['_issueNumber'] = 15;
         $fields['_endPage'] = 1029;
         $fields['_title'] = 'Localization of a brain sulfotransferase, SULT4A1-18, in the human, pig, gerbil, zombie and rat brain: An immunohistochemical study';
     
         $searchRes = Record::getPidsByFuzzyTitle($fields);
         $this->removeTestData($testPID);
         $this->removeAllTestPIDs(array($testPID), $this->_scopusIDCurrent);
     
         $this->assertEquals($fields['_title'], $searchRes['data'][0]["rek_title"]);
         $this->assertEquals($fields['_doi'], $searchRes['data'][0]["rek_doi"]);
         $this->assertFalse($fields['_startPage'] == $searchRes['data'][0]["rek_start_page"]);
         $this->assertFalse($fields['_issueVolume'] == $searchRes['data'][0]["rek_volume_number"]);
         $this->assertFalse($fields['_endPage'] == $searchRes['data'][0]["rek_end_page"]);
         $this->assertFalse($fields['_issueNumber'] == $searchRes['data'][0]["rek_issue_number"]);
     }
     
     /**
     * Fuzzy matching matches nothing
     */
     public function testFuzzyMatchNoMatch()
     {
         $testPID = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/fuzzyMatchingLocalTitleDoi.xml');
          
         //Faux downloaded record data
         $fields = array();
         $fields['_doi'] = '10.1080/09932246.2040.646779';
         $fields['_startPage'] = 5000;
         $fields['_issueVolume'] = '99a';
         $fields['_issueNumber'] = 234555;
         $fields['_endPage'] = 999999;
         $fields['_title'] = 'I am nothing';
          
         $searchRes = Record::getPidsByFuzzyTitle($fields);
         $this->removeTestData($testPID);
         $this->removeAllTestPIDs(array($testPID), $this->_scopusIDCurrent);
          
         $this->assertEquals(0, $searchRes['state']);
         $this->assertEquals(array(), $searchRes['data']);
     }
     
     /**
      * Test log message to search result mapping.
      * This test does no actual fuzzy title/Scopus ID/IVP matching
      */
     public function testFuzzyMatchResultReporting1()
     {
         $xml = file_get_contents(APP_PATH.'../tests/application/Unit/scopus/fuzzyMatchResultReporting01.xml');
         
         $results = array ('state' => 9,
            'data' => array (
                0 => array (
                    'rek_pid' => 'UQ:10048',
                    'rek_title' => 'Modelling Optical Micro-Machines',
                    'rek_doi' => '10.1016/j.biocon.2004.07.004',
                    'rek_scopus_id' => '2-s2.0-896587658765876',
                    'rek_start_page' => '163',
                    'rek_end_page' => '166',
                    'rek_volume_number' => NULL,
                    'rek_issue_number' => NULL,
                ),
                1 => array (
                    'rek_pid' => 'UQ:4158',
                    'rek_title' => 'Modelling Optical Micro-Machines',
                    'rek_doi' => NULL,
                    'rek_scopus_id' => NULL,
                    'rek_start_page' => '163',
                    'rek_end_page' => '166',
                    'rek_volume_number' => NULL,
                    'rek_issue_number' => NULL,
                ),
                2 => array (
                    'rek_pid' => 'UQ:4460',
                    'rek_title' => 'Modelling Optical Micro-Machines',
                    'rek_doi' => NULL,
                    'rek_scopus_id' => NULL,
                    'rek_start_page' => '163',
                    'rek_end_page' => '166',
                    'rek_volume_number' => NULL,
                    'rek_issue_number' => NULL,
                ),
            ),
         );
         
         $nameSpaces = array(
             'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
             'dc' => "http://purl.org/dc/elements/1.1/",
             'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
         );
         
         $csr = new ScopusRecItem();
         $csr->load($xml, $nameSpaces);
         $res = $csr->getFuzzySearchStatus($results);
         
         $this->assertEquals("ST27 - Matched on fuzzy title. Scopus ID in the downloaded record was 2-s2.0-84859635595. Scopus ID in the local record was 2-s2.0-896587658765876. Pid matched: UQ:10048", $res[0]);
         $this->assertEquals("ST26 - Matched on fuzzy title. Scopus ID in the downloaded record was 2-s2.0-84859635595. Scopus ID in the local record was null. Pid matched: UQ:4158", $res[1]);
     }
     
     /**
     * Test log message to search result mapping.
     * This test does no actual fuzzy title/Scopus ID/IVP matching
     */
     public function testFuzzyMatchResultReporting2()
     {
         $xml = file_get_contents(APP_PATH.'../tests/application/Unit/scopus/fuzzyMatchResultReporting01.xml');
          
         $results = array ('state' => 1,
                 'data' => array (
                 0 => array (
                         'rek_pid' => 'UQ:10048',
                         'rek_title' => 'Modelling Optical Micro-Machines',
                         'rek_doi' => '10.1016/j.biocon.2004.07.004',
                         'rek_scopus_id' => '2-s2.0-896587658765876',
                         'rek_start_page' => '163',
                         'rek_end_page' => '166',
                         'rek_volume_number' => NULL,
                         'rek_issue_number' => NULL,
                 ),
                 1 => array (
                         'rek_pid' => 'UQ:10023',
                         'rek_title' => 'Modelling Optical Micro-Machines',
                         'rek_doi' => '10.1016/j.biocon.2004.07.004',
                         'rek_scopus_id' => NULL,
                         'rek_start_page' => '163',
                         'rek_end_page' => '166',
                         'rek_volume_number' => NULL,
                         'rek_issue_number' => NULL,
         ),
             ),
         );
          
         $nameSpaces = array(
                  'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
                  'dc' => "http://purl.org/dc/elements/1.1/",
                  'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
         );
          
         $csr = new ScopusRecItem();
         $csr->load($xml, $nameSpaces);
         $res = $csr->getFuzzySearchStatus($results);
         
         $this->assertEquals("ST11 - Matched on fuzzy title, DOI, start page, end page, issue, volume. Scopus ID in the downloaded record was 2-s2.0-84859635595. Scopus ID in the local record was 2-s2.0-896587658765876. Pid matched: UQ:10048", $res[0]);
         $this->assertEquals("ST10 - Matched on fuzzy title, DOI, start page, end page, issue, volume. Scopus ID in the downloaded record was 2-s2.0-84859635595. Scopus ID in the local record was null. Pid matched: UQ:10023", $res[1]);
     }
}