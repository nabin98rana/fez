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

// Loops through all records in eSpace, and inserts the ScopusID by 
// searching the Scopus CitedBy Retrieve on DOI

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . 'class.worldcat.php');
include_once(APP_INC_PATH . "class.journal.php");

$max = 100; 	// Max number of primary key IDs to send with each service request call
$sleep = 0; 	// Number of seconds to wait for between successive service calls 

$issns = Journal::getJournalISSNsByYear(2010);
//print_r($issns);
//exit;
$total_issns = count($issns);
echo $total_issns."\n";
ob_flush();
//exit;
for($i=0; $i<(int)$total_issns; $i++) {
  $new_issns = array();
  $new_issns = WorldCat::getISSNs($issns[$i]['jni_issn']);
  Journal::insertISSNComplete($issns[$i]['jni_jnl_id'], $issns[$i]['jni_issn']);
  foreach($new_issns as $new_issn) {
    Journal::insertISSNComplete($issns[$i]['jni_jnl_id'], $new_issn);
  }
//		if ()
//		sleep($sleep); // Wait before using the service again

}
