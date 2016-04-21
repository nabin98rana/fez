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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>        |
// +----------------------------------------------------------------------+
//
//
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH.'/class.fedora_direct_access.php');
include_once(APP_INC_PATH . "Apache/Solr/Service.php");

/**
 * Class to manage all tasks related to the cache abstraction module.
 *
 * @version 1.0
 */

class IntegrityCheck
{
  /**
   * @var FezLog
   */
  private $log;

  /**
   * @var \Zend_Db_Adapter_Abstract
   */
  private $db;

  /**
   * @var string
   */
  private $prefix;

  /**
   * AWS constructor.
   */
  public function __construct() {
    $this->log = FezLog::get();
    $this->db = DB_API::get();
    $this->prefix = APP_TABLE_PREFIX;
  }

  /**
   * Main function, runs everything
   *
   * @param string $runType
   * @return void
   **/
  public function run($runType = "check") {

    // run checks
    if ($runType == 'check' || $runType == 'both') {
      $this->doFedoraFezIntegrityChecks();
      if (APP_SOLR_INDEXER == "ON") {
        $this->doFezSolrIntegrityChecks();
        $this->doSolrCitationChecks();
      }
      $this->doPidAuthChecks();
    }
    // run deletes
    if ($runType == 'fix' || $runType == 'both') {
      $this->doFedoraFezDelete();
      if (APP_SOLR_INDEXER == "ON") {
        $this->doFezSolrDeletes();
        $this->addSolrCitations();
        $this->addSolrUnspawned();
      }
      $this->doPidAuthDeletes();
    }
  }

  /**
   * checks to see if there are any pids marked as deleted in fedora that still exist in the record search key table
   *
   * @return void
   **/
  private function doFedoraFezIntegrityChecks() {
    $countInserted = 0;

    try {
      // get the fedora pids
      $fedoraDeletedPids = Fedora_Direct_Access::fetchAllFedoraPIDs('', 'D');

      $this->db->query("TRUNCATE TABLE {$this->prefix}integrity_index_ghosts");

      // for each pid, check if it exists in fez, and if so, put into the exceptions table
      // note, we're checking for items earlier than today

      if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) { //eg if postgresql etc
        $stmt = "SELECT * FROM {$this->prefix}record_search_key WHERE rek_pid = ? AND rek_created_date < (NOW() - INTERVAL '1 days')";
      } else {
        $stmt = "SELECT * FROM {$this->prefix}record_search_key WHERE rek_pid = ? AND rek_created_date < DATE_SUB(NOW(), INTERVAL 1 DAY)";
      }

      foreach($fedoraDeletedPids as $fedoraPid) {
        $result = $this->db->fetchOne($stmt, $fedoraPid['pid']);
        if ($result == $fedoraPid['pid']) {
          $this->db->insert("{$this->prefix}integrity_index_ghosts", array('pid'=>$result));
          $countInserted++;
        }
      }
      echo "\tFound {$countInserted} pids that are in marked as deleted in fedora and also in fez when they shouldn't be\n";
    } catch(Exception $ex) {
      echo "The following exception occurred in doFedoraFezIntegrityChecks: " . $ex->getMessage() . "\n";
      $this->log->err($ex);
    }
  }

  /**
   * Do the delete from fedora/solr
   *
   * @return void
   **/
  private function doFedoraFezDelete() {
    try {
      $sql = "SELECT pid FROM {$this->prefix}integrity_index_ghosts";
      $pids = $this->db->fetchCol($sql);
      foreach($pids as $pid) {
        Record::removeIndexRecord($pid);
      }
      echo "\t" . count($pids) . " deleted from fez (and possibly solr as well)\n";

    } catch (Exception $ex) {
      echo "The following exception occurred in doFedoraFezDelete: " . $ex->getMessage() . "\n";
      $this->log->err($ex);
    }
  }

  /**
   * checks to see if there are any fez pids which are not in solr and any solr pids that are not in fez
   *
   * @return void
   **/
  private function doFezSolrIntegrityChecks() {
    $countInsertedGhosts = 0;
    $countInsertedUnspawned = 0;

    try {
      // grab all fez pids
      $fezPidsQuery = "SELECT rek_pid FROM {$this->prefix}record_search_key";
      $fezPids = $this->db->fetchCol($fezPidsQuery);

      // find all items
      $solrQuery = 'id:[* TO *]';

      $response = $this->doSolrSearch($solrQuery);
      foreach ($response->response->docs as $doc) {
        $solrPids[] = $doc->id;
      }
      unset($response);

      // truncate the two result tables
      $this->db->query("TRUNCATE TABLE {$this->prefix}integrity_solr_ghosts");
      $this->db->query("TRUNCATE TABLE {$this->prefix}integrity_solr_unspawned");

      // compare arrays, finding pids that exist in one but not the other
      $pidsInFezNotInSolr = array_diff($fezPids, $solrPids);
      $pidsInSolrNotInFez = array_diff($solrPids, $fezPids);

      foreach($pidsInFezNotInSolr as $pid) {
        $this->db->insert("{$this->prefix}integrity_solr_unspawned", array('pid'=>$pid));
        $countInsertedUnspawned++;

      }
      foreach($pidsInSolrNotInFez as $pid) {
        $this->db->insert("{$this->prefix}integrity_solr_ghosts", array('pid'=>$pid));
        $countInsertedGhosts++;
      }

      echo "\tFound {$countInsertedGhosts} pids that are in solr but not in fez\n";
      echo "\tFound {$countInsertedUnspawned} pids that are in fez but not in solr\n";
    } catch(Exception $ex) {
      echo "The following exception occurred in doFezSolrIntegrityChecks: " . $ex->getMessage() . "\n";
      $this->log->err($ex);
    }
  }

  /**
   * Do the delete from fedora/solr
   *
   * @return void
   **/
  private function doFezSolrDeletes() {
    try {
      $sql = "SELECT pid FROM {$this->prefix}integrity_solr_ghosts";
      $pids = $this->db->fetchCol($sql);
      foreach($pids as $pid) {
        Record::removeIndexRecord($pid);
      }
      echo "\t" . count($pids) . " deleted from solr\n";

    } catch (Exception $ex) {
      echo "The following exception occurred in doFezSolrDeletes: " . $ex->getMessage() . "\n";
      $this->log->err($ex);
    }
  }

  /**
   * checks to make sure that all solr items have citations
   *
   * @return void
   **/
  private function doSolrCitationChecks() {
    $countInserted = 0;

    try {
      $this->db->query("TRUNCATE TABLE {$this->prefix}integrity_solr_unspawned_citations");

      // find where the citation_t field has no value
      $solrQuery = '-citation_t:[* TO *]';
      $response = $this->doSolrSearch($solrQuery);

      foreach ($response->response->docs as $doc) {
        $this->db->insert("{$this->prefix}integrity_solr_unspawned_citations", array('pid'=>$doc->id));
        $countInserted++;
      }
      echo "\tFound {$countInserted} pids that don't have citations in solr\n";
    } catch(Exception $ex) {
      echo "The following exception occurred in doSolrCitationChecks: " . $ex->getMessage() . "\n";
      $this->log->err($ex);
    }

  }

  /**
   * adds citations to solr for pids that didn't have them previously
   *
   * @return void
   **/
  private function addSolrUnspawned() {
    try {
      $sql = "SELECT pid FROM {$this->prefix}integrity_solr_unspawned";
      $pids = $this->db->fetchCol($sql);
      $queue = FulltextQueue::singleton();

      foreach($pids as $pid) {
        Citation::updateCitationCache($pid);
        $queue->add($pid);
      }
      $queue->commit();
      echo "\tAdded " . count($pids) . " missing pid in solr\n";

    } catch (Exception $ex) {
      echo "The following exception occurred in addSolrUnspawned: " . $ex->getMessage() . "\n";
      $this->log->err($ex);
    }
  }

  /**
   * adds citations to solr for pids that didn't have them previously
   *
   * @return void
   **/
  private function addSolrCitations() {
    try {
      $sql = "SELECT pid FROM {$this->prefix}integrity_solr_unspawned_citations";
      $pids = $this->db->fetchCol($sql);
      $queue = FulltextQueue::singleton();

      foreach($pids as $pid) {
        Citation::updateCitationCache($pid);
        $queue->add($pid);
      }
      $queue->commit();
      echo "\tUpdated " . count($pids) . " citations in solr\n";

    } catch (Exception $ex) {
      echo "The following exception occurred in addSolrCitations: " . $ex->getMessage() . "\n";
      $this->log->err($ex);
    }
  }


  /**
   * Does auth checks for pids
   *
   * @return void
   **/
  private function doPidAuthChecks() {
    try {
      $this->db->query("TRUNCATE TABLE {$this->prefix}integrity_pid_auth_ghosts");

      $sql = "SELECT authi_pid FROM {$this->prefix}auth_index2 LEFT JOIN {$this->prefix}record_search_key ON rek_pid = authi_pid WHERE rek_pid IS NULL";
      $pids = $this->db->fetchCol($sql);
      if (count($pids) > 0) {
        $pids = array_unique($pids);
        $countInserted = 0;
        foreach($pids as $pid) {
          $this->db->insert("{$this->prefix}integrity_pid_auth_ghosts", array('pid'=>$pid));
          $countInserted++;
        }
        echo "\tFound {$countInserted} auth rows for missing pids\n";
      } else {
        echo "\tNo missing pids auth indexes were found\n";
      }
    } catch(Exception $ex) {
      echo "The following exception occurred in doPidAuthChecks: " . $ex->getMessage() . "\n";
      $this->log->err($ex);
    }

  }

  /**
   * Does the delete of the auths for pids that don't exist any more
   *
   * @return void
   **/
  private function doPidAuthDeletes() {
    try {
      $sql = "SELECT pid FROM {$this->prefix}integrity_pid_auth_ghosts";
      $pids = $this->db->fetchCol($sql);
      if (count($pids) > 0) {
        $result = AuthIndex::clearIndexAuth($pids);
        if ($result) {
          echo "\t" . count($pids) . " pids auth index were deleted\n";
        } else {
          echo "\t*** There was an error in clearing out the pids auth index\n";
        }
      }
    } catch(Exception $ex) {
      echo "The following exception occurred in doPidAuthDeletes: " . $ex->getMessage() . "\n";
      $this->log->err($ex);
    }

  }

  // BUILD OUR OWN VERSION OF THE SOLR SEARCH SERVICE BECAUSE THE GENERAL VERSION HAS A 30 SECOND TIMEOUT
  // COPIED AND PASTED FROM Apache_Solr_Service AND MODIFIED AS NECESSARY
  private function doSolrSearch($query) {
    $usr_id = Auth::getUserID();
    if (defined(APP_SOLR_SLAVE_HOST) && defined(APP_SOLR_SLAVE_READ) && (APP_SOLR_SLAVE_READ == "ON") && !is_numeric($usr_id)) {
      $solrHost = APP_SOLR_SLAVE_HOST;
    } else {
      $solrHost = APP_SOLR_HOST;
    }
    $solrPort = APP_SOLR_PORT;
    $solrPath = APP_SOLR_PATH;

    $solr = new Apache_Solr_Service($solrHost, $solrPort, $solrPath);

    $params['fl'] = 'id';
    $params['wt'] = Apache_Solr_Service::SOLR_WRITER;
    $params['json.nl'] = $solr->getNamedListTreatment();
    $params['q'] = $query;
    $params['start'] = 0;
    $params['rows'] = 999999;
    $queryString = http_build_query($params, null, '&');

    // because http_build_query treats arrays differently than we want to, correct the query
    // string by changing foo[#]=bar (# being an actual number) parameter strings to just
    // multiple foo=bar strings. This regex should always work since '=' will be urlencoded
    // anywhere else the regex isn't expecting it
    $queryString = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);

    $url = "http://{$solrHost}:{$solrPort}{$solr->getPath()}select?{$queryString}";

    $curlResponse = new Apache_Solr_HttpTransport_Curl;
    $raw_response = $curlResponse->performGetRequest($url, 600); // We'll see if curl times are causing things to fail

    if($raw_response->getStatusCode() != 200) {
      $this->log->err('No response from solr.. trying again.');
      unset($raw_response);
      sleep(1);
      $raw_response = $curlResponse->performGetRequest($url, 600);
      if($raw_response->getStatusCode() != 200) {
        throw new Exception(print_r($raw_response[1], true));
      }
    }
    $response = new Apache_Solr_Response($raw_response, null, true);
    return $response;
  }
}