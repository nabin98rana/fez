<?php
/*
 * Fez 
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 27/03/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
 class XML_Helper 
 {
     /**
      * getOrCreateElement - will only create the leaf element, if the parent doesn't exist then it
      * will fail.
      * @param object $doc
      * @param string $path path to parent of element
      * @param string $element 
      * @param array $ns array of namespaces as prefix/url keypairs
      * @return object or false on failure.
      */
     function getOrCreateElement($doc, $path, $element, $ns = array())
     {
        $xpath = new DOMXPath($doc);
        foreach ($ns as $prefix => $uri) {
            $xpath->registerNamespace($prefix,$uri);
        }
        $nodes = $xpath->query($path);
        if ($nodes->length < 1) {
            return false;
        }
        $parentNode = $nodes->item(0);
        $nodes = $xpath->query($element,$parentNode);
        if ($nodes->length < 1) {
            list($prefix, $element_stripped) = explode(':', $element);
            if (!empty($element_stripped)) {
                if (!isset($ns[$prefix])) {
                    Error_Handler::logError('Element has unknown prefix',__FILE__,__LINE__);
                    return false;
                }
                $node = $doc->createElementNS($ns[$prefix], $element_stripped);
            } else {
                $node = $doc->createElement($element);
            }
            $parentNode->appendChild($node);
            return $node;
        } else {
            return $nodes->item(0);
        }
     }
     
     function setElementNodeValue($doc, $path, $element, $value, $ns = array())
     {
         $node = XML_Helper::getOrCreateElement($doc, $path, $element, $ns);
         $node->nodeValue = $value;
     }
 }
?>
