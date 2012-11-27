<?php

/**
 * Base class inherited by all classses 
 * representing data imported from external 
 * sources to be processed.
 * @author Chris Maj <c.maj@library.uq.edu.au>
 * @since November 2012
 *
 */
abstract class RecordItem
{
    protected $_collections;
    protected $_abstract;
    protected $_ut = null;
    protected $_pubmedId = null;
    protected $_scopusId = null;
    protected $_wokCitationCount = null;
    protected $_scopusCitationCount = null;
    protected $_sourceAbbrev = null;
    protected $_title = null;
    protected $_journalTitle = null;
    protected $_journalTitleAbbreviation = null;
    protected $_date_issued = null;
    protected $_articleNos = array();
    protected $_totalPages = null;
    protected $_startPage = null;
    protected $_endPage = null;
    protected $_issueDate = null;
    protected $_issueNumber = null;
    protected $_volume = null;
    //protected $_docType = null;
    //protected $_docTypeCode = null;
    protected $_langageCode = null;
    protected $_issn = null;
    protected $_isbn = null;
    protected $_conferenceDate = null;
    protected $_conferenceTitle = null;
    protected $_confenceLocationCity = null;
    protected $_confenceLocationState = null;
    protected $_authors = array();
    protected $_author_ids = array();
    protected $_keywords = array();
    protected $_loaded = FALSE;
    protected $_publisher = null;
    protected $_doi = null;
    protected $_xdisId = null;
    protected $_xdisTitle = null;
    protected $_xdisSubtype = null;

    
    //more fields common to all child classes
    //with cooresponding liken*() methods
    
    public abstract function load($recordData);
    
    /**
     * Common xpath object used by all child classes
     * @param string $rawXML
     * @return DOMXPath
     */
    protected function getXPath($rawXML)
    {
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->loadXML($rawXML);
        
        $xpath = new DOMXPath($xmlDoc);
        /*$xpath->registerNamespace('prism');
        $xpath->registerNamespace('dc');*/
        
        return $xpath;
    }
    
    /**
     * Strip title and perform comparison
     */
    public function likenTitle()
    {
        //title comparison logic
        $str1 = str_replace(array('\t', '\r\n', '\n', ' '), ''
                                            , strtolower($this->title));
        return $str1;
    }
    
    /**
     * Compare DOI
     */
    public function likenDoi()
    {
        //doi comparison logic
    }
    
    //more liken methods
}