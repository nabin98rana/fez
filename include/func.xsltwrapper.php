<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
// | Australian Partnership for Sustainable Repositories,                 |
// | eScholarship Project                                                 |
// |                                                                      |
// | Some of the Fez code was derived from Eventum (Copyright 2003, 2004  |
// | MySQL AB - http://dev.mysql.com/downloads/other/eventum/ - GPL)      |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//
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
