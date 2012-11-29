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
    /**
     * Fields pertaining to the record
     */
    protected $_collections;
    protected $_abstract;
    protected $_ut = null;
    protected $_pubmedId = null;
    protected $_scopusId = null;
    protected $_wokId = null;
    protected $_wokCitationCount = null;
    protected $_scopusCitationCount = null;
    protected $_sourceAbbrev = null;
    protected $_title = null;
    protected $_date_issued = null;
    protected $_articleNos = array();
    protected $_totalPages = null;
    protected $_startPage = null;
    protected $_endPage = null;
    protected $_issueDate = null;
    protected $_issueNumber = null;
    protected $_volume = null;
    protected $_docType = null;
    protected $_docSubType = null;
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
    protected $_xdis_id = null;
    protected $_xdis_title = null;
    protected $_xdis_subtype = null;
    
    /**
     * Namespaces to use with the XPath object
     * @var array
     */
    protected $_namespaces = array();

    
    public abstract function load($recordData, $nameSpaces=null);
    
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
        
        if($this->nameSpaces)
        {
            foreach($nameSpaces as $name => $uri)
            {
                $xpath->registerNamespace($name, $uri);
            }
        }
        
        return $xpath;
    }
    
    /**
     * Wrapper for calling other liken methods
     * and deciding if something is the same or not.
     */
    public abstract function liken();
}