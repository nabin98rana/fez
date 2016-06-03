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
// | Authors: Rhys Palmer <r.palmer@library.uq.edu.au>                    |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH . 'class.background_process.php');
include_once(APP_INC_PATH . 'class.altmetric.php');
include_once(APP_INC_PATH . "class.record.php");

class BackgroundProcess_Update_Altmetric_Info extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_update_altmetric_info.php';
    $this->name = 'Update Altmetric Info';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    $this->updateInfo();

    $this->setState(BGP_FINISHED);
  }


  function updateInfo() {
    $sleep = 1;

    $filter = array();
    $filter["searchKey".Search_Key::getID("Status")] = 2;      // published only
    $filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only
    $filter["manualFilter"] = "doi_t_s:[* TO *]";

    $page_rows = 100;
    $listing = Record::getListing(array(), array(9,10), 0, $page_rows, 'Created Date', false, false, $filter);
    $altmetric = new Altmetric();

    // Check not already running
    if (! $altmetric->isSafeToRun()) {
      return;
    }

    // In-memory cache of DOIs already processed
    $doiCache = array();
    $lastAltmetricCallTime = microtime(TRUE);
    for ($i = 0; $i < ((int)$listing['info']['total_pages']+1); $i++) {

      // Skip first loop - we have called getListing once already
      if ($i > 0) {
        $listing = Record::getListing(
          array(), array(9,10), $listing['info']['next_page'], $page_rows, 'Created Date', false, false, $filter
        );
      }

      if (is_array($listing['list'])) {
        for ($j=0; $j < count($listing['list']); $j++) {
          $record = $listing['list'][$j];
          $pid = $record['rek_pid'];
          $doi = $record['rek_doi'];
          if (! empty($doi)) {
            if (! in_array($doi, $doiCache)) {
              $currentTime = microtime(TRUE);
              if (($currentTime - $lastAltmetricCallTime) < 1000000) {
                usleep(round(1000000 - ($currentTime - $lastAltmetricCallTime))); //Don't call more than once per second
              }
              $altmetric->fetchInformation($doi);
              $lastAltmetricCallTime = microtime(TRUE);
              $doiCache[] = $doi;
            }
            Record::updateAltmetricScoreFromHistory($pid);
            if ( APP_SOLR_INDEXER == "ON" ) {
              FulltextQueue::singleton()->add($pid);
            }
          }
          flush();
          ob_flush();
        }

        if ( APP_SOLR_INDEXER == "ON" ) {
          FulltextQueue::singleton()->commit();
        }
      }
    }

    $altmetric->endRun();

  }
}
