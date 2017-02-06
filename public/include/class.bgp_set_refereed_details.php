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
include_once(APP_INC_PATH . "class.refereed.php");

class BackgroundProcess_Set_Refereed_Details extends BackgroundProcess
{
  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_set_refereed_details.php';
    $this->name = 'Set refereed details';
  }

  function run() {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    $this->setRefereedDetails();

    $this->setState(BGP_FINISHED);
  }

  function setRefereedDetails() {
    $db = DB_API::get();
    $log = FezLog::get();

    $refereedSource[0] = 'Ulrichs';
    $refereedSource[1] = 'Thomson Reuters';
    $refereedSource[2] = 'ERA Journal List 2012';
    $refereedSource[3] = 'ERA Journal List 2015';
    $refereedSource[4] = 'ERA Journal List 2010';
    $refereedSource[5] = 'Not yet assessed';

    $history[0] = 'set due to Ulrichs';
    $history[1] = 'set due to ISI Loc set';
    $history[2] = 'set due to Journal being in ERA 2012';
    $history[3] = 'set due to Journal being in ERA 2015';
    $history[4] = 'set due to Journal being in ERA 2010';
    $history[5] = 'set due to no other information or matches';


    $refereedSourceCV[0] = Controlled_Vocab::getID($refereedSource[0], 'Refereed Source');
    $refereedSourceCV[1] = Controlled_Vocab::getID($refereedSource[1], 'Refereed Source');
    $refereedSourceCV[2] = Controlled_Vocab::getID($refereedSource[2], 'Refereed Source');
    $refereedSourceCV[3] = Controlled_Vocab::getID($refereedSource[3], 'Refereed Source');
    $refereedSourceCV[4] = Controlled_Vocab::getID($refereedSource[4], 'Refereed Source');
    $refereedSourceCV[5] = Controlled_Vocab::getID($refereedSource[5], 'Refereed Source');


    $query[0] = "SELECT rek_pid AS pid FROM " . APP_TABLE_PREFIX . "record_search_key
                 INNER JOIN fez_record_search_key_issn ON rek_issn_pid = rek_pid
                 INNER JOIN " . APP_TABLE_PREFIX . "ulrichs ON ulr_issn = rek_issn
                 LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_refereed_source ON rek_refereed_source_pid = rek_pid
                 WHERE ulr_refereed = 'true'
                 AND ((rek_refereed_source != " .$refereedSourceCV[0]. ") OR rek_refereed_source IS NULL)
                 AND (rek_genre != 'thesis' AND rek_genre != 'database')
                 GROUP BY pid";

    $query[1] = "SELECT rek_pid AS pid FROM " . APP_TABLE_PREFIX . "record_search_key
                 INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_isi_loc ON rek_isi_loc_pid = rek_pid
                 LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_refereed_source ON rek_refereed_source_pid = rek_pid
                 WHERE ((rek_refereed_source != " .$refereedSourceCV[0]. " && rek_refereed_source != " .$refereedSourceCV[1]. ") OR rek_refereed_source IS NULL)
                 AND (rek_genre != 'thesis' AND rek_genre != 'database')";

    $query[2] = "SELECT rek_pid AS pid FROM " . APP_TABLE_PREFIX . "record_search_key INNER JOIN " . APP_TABLE_PREFIX . "matched_journals ON rek_pid = mtj_pid
                 LEFT JOIN " . APP_TABLE_PREFIX . "journal ON mtj_jnl_id = jnl_id
                 LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_refereed_source ON rek_refereed_source_pid = rek_pid
                 WHERE (jnl_era_year = 2012)
                 AND ((rek_refereed_source != " .$refereedSourceCV[0]. " && rek_refereed_source != " .$refereedSourceCV[1]. "
                   && rek_refereed_source != " .$refereedSourceCV[2]. ") OR rek_refereed_source IS NULL)
                 AND (rek_genre != 'thesis' AND rek_genre != 'database')";

    $query[3] = "SELECT rek_pid AS pid FROM " . APP_TABLE_PREFIX . "record_search_key INNER JOIN " . APP_TABLE_PREFIX . "matched_journals ON rek_pid = mtj_pid
                 LEFT JOIN " . APP_TABLE_PREFIX . "journal ON mtj_jnl_id = jnl_id
                 LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_refereed_source ON rek_refereed_source_pid = rek_pid
                 WHERE (jnl_era_year = 2015)
                 AND ((rek_refereed_source != " .$refereedSourceCV[0]. " && rek_refereed_source != " .$refereedSourceCV[1]. " && rek_refereed_source != " .$refereedSourceCV[2]. "
                   && rek_refereed_source != " .$refereedSourceCV[3]. ") OR rek_refereed_source IS NULL)
                 AND (rek_genre != 'thesis' AND rek_genre != 'database')";

    $query[4] = "SELECT rek_pid AS pid FROM " . APP_TABLE_PREFIX . "record_search_key INNER JOIN " . APP_TABLE_PREFIX . "matched_journals ON rek_pid = mtj_pid
                 LEFT JOIN " . APP_TABLE_PREFIX . "journal ON mtj_jnl_id = jnl_id
                 LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_refereed_source ON rek_refereed_source_pid = rek_pid
                 WHERE (jnl_era_year = 2010)
                 AND ((rek_refereed_source != " .$refereedSourceCV[0]. " && rek_refereed_source != " .$refereedSourceCV[1]. " && rek_refereed_source != " .$refereedSourceCV[2]. "
                   && rek_refereed_source != " .$refereedSourceCV[3]. "&& rek_refereed_source != " .$refereedSourceCV[4].") OR rek_refereed_source IS NULL)
                 AND (rek_genre != 'thesis' AND rek_genre != 'database')";

    $query[5] = "SELECT rek_pid AS pid FROM " . APP_TABLE_PREFIX . "record_search_key
                 LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_refereed_source
                 ON rek_refereed_source_pid = rek_pid
                 WHERE rek_refereed_source IS NULL
                 AND (rek_genre != 'thesis' AND rek_genre != 'database')
                 ";

    for ($i=0; $i<6; $i++) {

      try {
        $result = $db->fetchAll($query[$i]);
      } catch (Exception $ex) {
        $log->err('Message: ' . $ex->getMessage() . ', File: ' . __FILE__ . ', Line: ' . __LINE__);
        return;
      }

      // for each pid,
      foreach ($result as $pidDetails) {
        $pid = $pidDetails['pid'];

        //For the last option (Nothing found, we save as not yet assessed)
        if ($i < 6) {
          Refereed::saveIfHigher($pid, $refereedSource[$i], $history[$i]);
        }
        else {
          Refereed::save($pid, $refereedSource[$i], $history[$i]);
        }
      }
    }
  }
}
