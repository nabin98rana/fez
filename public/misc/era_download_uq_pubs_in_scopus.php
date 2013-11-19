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

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . 'class.scopus_service.php');
include_once(APP_INC_PATH . 'class.scopus_queue.php');
include_once(APP_INC_PATH . "class.record.php");

if ((php_sapi_name()==="cli") || (User::isUserSuperAdministrator($isUser))) {

//  $query = 'OG=(Univ Queensland)';
//  $depth = '4week';
//  $timeSpan = array();
//  $databaseID = "WOS";
  ini_set("display_errors", 1);
  error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
  $afids = array('60031004', '60087457');


  //Edition set to "" should default to all
  $editions = array();
  $sort = '';
  $first_rec = 1;
  $num_recs = WOK_BATCH_SIZE;
  $scopusService = new ScopusService();
  $sq = ScopusQueue::get();
  $i=0;
  $foundResults = true;
//  echo "\n";
  while($i < 5030 && $foundResults) {
    $foundResults = false;
    //get the last 30 days of recently added records
    $query = array('query' => '(af-id(' . $afids[0] . ') OR af-id(' . $afids[1] . ')) AND recent(30)',
      'count' => 30,
      'start' => $i,
      'view' => 'STANDARD'
    );
    $resp = $scopusService->search($query);
    //var_dump($resp);
    /*-------------------------------------------------------------*/
    $doc = new DOMDocument();
    $doc->loadXML($resp);
    $records = $doc->getElementsByTagName('identifier');
    foreach ($records as $record) {
      if (!$foundResults) {
        $foundResults = true;
      }
      $scopus_id = $record->nodeValue;
      echo $scopus_id."\n";
      $sq->add($scopus_id);
    }
    $i += 30;
//    echo "\nup to $i\n";
//    if ($i > 30) { exit; }
  }
}

