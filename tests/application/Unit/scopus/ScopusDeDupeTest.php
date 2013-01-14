<?php

require_once '../../../../public/config.inc.php';
require_once '../../../../public/include/class.scopus_service.php';
require_once '../../../../public/include/class.scopus_record.php';
require_once '../../../../public/include/class.record.php';

class ScopusTest extends PHPUnit_Framework_TestCase
{
    
    public function loadTestData($file)
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
        return $csr->save();
    }
     
    public function removeTestData($pid)
    {
        Record::removeIndexRecord($pid);
    }
     
    public function saveUpdateAbstract($xml)
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
         
        $this->removeTestData($testPid);

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
        
        $this->assertEquals('UPDATE', $likened);
    }
     
    /**
     * Scopus Id matches
     * Title matches
     * Start is empty in the DL record
     * End is empty in the DL record
     * Volume is empty in the DL record
     * Pubmed Id is empty in the DL record
     * 
     * This should error out because of the start/end page and volume mismatch.
     */
     public function testSaveUpdateScopusIdTitleMatches()
     {
         $testPid = $this->loadTestData(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractVolumePageEmptyLocal.xml');
        
         $xml = file_get_contents(APP_PATH.'../tests/application/Unit/scopus/sampleAbstractVolumePageEmptyDL.xml');
         $likened = $this->saveUpdateAbstract($xml);
         
         $this->removeTestData($testPid);
        
         $this->assertEquals(false, $likened);
     }
}