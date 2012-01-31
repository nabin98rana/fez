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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle access to the WorldCat webservices
 *
 * @version 1.0
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 * 
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . 'nusoap.php');
include_once(APP_INC_PATH . "class.misc.php");

class WorldCat
{
// Production Addresses

	const REST = '';
  const SUFFIX = '?method=getForms&format=xml';


  function getISSNs($issn) {
    $url = "http://xissn.worldcat.org/webservices/xid/issn/".$issn."?method=getForms&format=xml";
    $xml = Misc::processURL($url);

//    $xmldoc= new DomDocument();
//    $xmldoc->preserveWhiteSpace = false;
//    $xmldoc->loadXML($xml);
    $res = array();
    //print_r($xml);
    //ob_flush();
   // exit;
    $response = simplexml_load_string($xml[0]);
//echo $xml;
//    echo "\n";
    ob_flush();
    if ($response) {
      foreach($response->group->issn as $new_issn) {
//        echo $new_issn."\n";
        array_push($res, $new_issn);
      }
    }
//    exit;
    return $res;
  }

}
