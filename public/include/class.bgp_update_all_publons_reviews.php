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
include_once(APP_INC_PATH . 'class.publons.php');
include_once(APP_INC_PATH . 'class.api_researchers.php');

class BackgroundProcess_Update_All_Publons_Reviews extends BackgroundProcess
{
  /*
   * Limit is one per sixty seconds
   *
   * Value is in micro seconds
   */
  const RATE_LIMIT = 800000;


  function __construct()
  {
    parent::__construct();
    $this->include = 'class.bgp_update_all_publons_reviews.php';
    $this->name = 'Update all Publons reviews';
  }


  function run()
  {
    $this->setState(BGP_RUNNING);
    extract(unserialize($this->inputs));

    $this->updateReviews();

    $this->setState(BGP_FINISHED);
  }


  function updateReviews()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT aut_orcid_id FROM " . APP_TABLE_PREFIX
      . "author WHERE aut_orcid_id IS NOT NULL AND aut_orcid_id != ''";

    try {
      $res = $db->fetchCol($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return false;
    }

    foreach ($res as $orcid) {
      $authorId = ApiResearchers::authorIdFromOrcid($orcid);
      $this->setHeartbeat();
      $user = Publons::getUser($orcid);

      if ($user) {
        Publons::savePublonsId($authorId, '1');  //Currently we don't want to store the publons id

        $results = Publons::getUserData($orcid);

        foreach ($results['results'] as $paper) {
          Publons::savePublonsReview($authorId, $paper);
        }
      } else {
        $log->info("Error receiving publons details for " . $orcid);
      }
      usleep(self::RATE_LIMIT);
    }
    $this->setHeartbeat();
    echo "Updating publons tiered list\n";
    $stmt = "UPDATE " . APP_TABLE_PREFIX . "publons_journals
                INNER JOIN " . APP_TABLE_PREFIX . "journal_uq_tiered_issns ON jni_issn = psj_journal_issn
                INNER JOIN " . APP_TABLE_PREFIX . "journal_uq_tiered ON jnl_id = jni_jnl_id
                SET " . APP_TABLE_PREFIX . "publons_journals.psj_journal_tier = " . APP_TABLE_PREFIX . "journal_uq_tiered.jnl_rank
                WHERE psj_journal_tier IS NULL OR psj_journal_tier = ''";

    try {
      $db->exec($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return false;
    }

    #We will also check electronic issn's just in case
    $stmt = "UPDATE " . APP_TABLE_PREFIX . "publons_journals
                INNER JOIN " . APP_TABLE_PREFIX . "journal_uq_tiered_issns ON jni_issn = psj_journal_eissn
                INNER JOIN " . APP_TABLE_PREFIX . "journal_uq_tiered ON jnl_id = jni_jnl_id
                SET " . APP_TABLE_PREFIX . "publons_journals.psj_journal_tier = " . APP_TABLE_PREFIX . "journal_uq_tiered.jnl_rank
                WHERE psj_journal_tier IS NULL OR psj_journal_tier = ''";

    try {
      $db->exec($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return false;
    }
  }
}
