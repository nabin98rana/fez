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
    protected $title;
    
    protected $doi;
    
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