<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
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
class Jhove_Helper
{
   function extractFileSize($xmlObj) {
   	    $xmlDoc= new DomDocument();
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->loadXML($xmlObj);
		$fileSize = "";
        $xpath = new DOMXPath($xmlDoc);
        $xpath->registerNamespace('a', 'http://hul.harvard.edu/ois/xml/ns/jhove');
        $recordNodes = $xpath->query('//a:jhove/a:repInfo/a:size');
		foreach ($recordNodes as $file_field) {
			if ($fileSize == "") {
				$fileSize = $file_field->nodeValue;
	        }
	    }
		return $fileSize;
   }
   
   
   function extractSpatialMetrics($xmlObj) {
        
        $width = 0;
        $height = 0;
       
        $xml = new SimpleXMLElement($xmlObj);
        $xml->registerXPathNamespace('mix', 'http://www.loc.gov/mix/');
        
        foreach ($xml->xpath('//mix:ImageWidth') as $imgWidth) 
        {
            $width = (int)$imgWidth[0];
        }
        
        foreach ($xml->xpath('//mix:ImageLength') as $imgLength) 
        {
            $height = (int)$imgLength[0];
        }
		
		return array($width, $height);
   }
    
}

?>
