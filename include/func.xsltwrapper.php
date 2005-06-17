<?php
//if (PHP_VERSION >= 5) {
   // Emulate the old xslt library functions
   function xslt_create() {
       return new XsltProcessor();
   }

   function xslt_process($xsltproc,
                         $xml_arg,
                         $xsl_arg,
                         $xslcontainer = null,
                         $args = null,
                         $params = null) {


       // Start with preparing the arguments
       $xml_arg = str_replace('arg:', '', $xml_arg);
       //$xsl_arg = str_replace('arg:', '', $xsl_arg); //original
       $xsl_arg = file_get_contents($xsl_arg);

       // Create instances of the DomDocument class
       $xml = new DomDocument;
       $xsl = new DomDocument;

       // Load the xml document and the xsl template
       $xml->loadXML($args[$xml_arg]);
       //$xsl->loadXML($args[$xsl_arg]);
       $xsl->loadXML($xsl_arg);

       // Load the xsl template
       $xsltproc->importStyleSheet($xsl);

       // Set parameters when defined
       if ($params) {
           foreach ($params as $param => $value) {
               $xsltproc->setParameter("", $param, $value);
           }
       }
       // Start the transformation
       $processed = $xsltproc->transformToXML($xml);

       // Put the result in a file when specified
       if ($xslcontainer) {
           return @file_put_contents($xslcontainer, $processed);
       } else {
           return $processed;
       }

   }

   function xslt_free($xsltproc) {
       unset($xsltproc);
   }
//}
?>
