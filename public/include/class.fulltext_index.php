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
// | Authors: Kai Jauslin <kai.jauslin@library.ethz.ch>                   |
// +----------------------------------------------------------------------+

/**
 * This class provides the interface and base functions for fulltext
 * indexing.
 *
 * For the indexing engine subclasses, implement at least
 * <ul>
 * <li>updateFulltextIndex
 * <li>executeQuery
 * </ul>
 *
 * @author Kai Jauslin <kai.jauslin@library.ethz.ch>
 * @version 1.1, February 2008
 *
 */
include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.bgp_fulltext_index.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");
include_once(APP_INC_PATH . "class.fulltext_tools.php");
include_once(APP_INC_PATH . "class.fulltext_index_solr.php");
include_once(APP_INC_PATH . "class.fulltext_index_elasticsearch.php");
include_once(APP_INC_PATH . "class.fulltext_index_solr_csv.php");
include_once(APP_INC_PATH . "class.citation.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "Apache/Solr/Service.php");

abstract class FulltextIndex {
	const FIELD_TYPE_INT = 0;
	const FIELD_TYPE_DATE = 1;
	const FIELD_TYPE_VARCHAR = 2;
	const FIELD_TYPE_TEXT = 3;

	const FULLTEXT_TABLE_NAME = "fulltext_cache";

	const FIELD_MOD_MULTI = '_multivalue';
	const FIELD_NAME_AUTHLISTER = '_authlister';
	const FIELD_NAME_AUTHCREATOR = '_authcreator';
	const FIELD_NAME_AUTHEDITOR = '_autheditor';
	const FIELD_NAME_FULLTEXT = 'content';
const USE_LOCKING = true;
	const LOCK_NAME_FULLTEXT_INDEX = 'indexer';

	const ACTION_INSERT = 'I';
	const ACTION_DELETE = 'D';

	protected $bgp;
	protected $pid_count = 0;
	protected $countDocs = 0;
	protected $totalDocs = 0;
	protected $bgpDetails;
	protected $searchKeyData;

	// how often the index optimizer is called
	const COMMIT_COUNT = APP_SOLR_COMMIT_LIMIT; // Now gets this variablee from a config var set in the admin gui


  public static function get($readOnly = false)
  {
    if (APP_ES_SWITCH == "ON") {
      return new FulltextIndex_ElasticSearch($readOnly);
    } elseif (APP_SOLR_SWITCH == "ON") {
      return new FulltextIndex_Solr_CSV($readOnly);
    } else {
      throw new Exception("No fulltext search index configured.");
      return false;
    }
  }

    /**
	 * Links this instance to a corresponding background process.
	 *
	 * @param BackgroundProcess_Fulltext_Index $bgp
	 */
	public function setBGP(BackgroundProcess_Fulltext_Index &$bgp)
	{
		$this->bgp = &$bgp;
	}

	/**
	 * Releases lock held by this thread.
	 *
	 */
	private function releaseLock()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "DELETE FROM ".APP_TABLE_PREFIX."fulltext_locks WHERE ftl_name='";
		$stmt .= FulltextQueue::LOCK_NAME_FULLTEXT_INDEX."'";
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			$log->err(array("FulltextIndex::releaseLock failed",$res));
		}
	}


	/**
	 * Updates the queue lock to reflect the current process id.
	 * The lock can be retaken if the process with this id does
	 * not exist anymore.
	 *
	 */
	private function updateLock()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$my_pid = FulltextQueue::getProcessInfo();


		$db->beginTransaction();

		$stmt = "SELECT ftl_value, ftl_pid FROM ".APP_TABLE_PREFIX."fulltext_locks ".
					"WHERE ftl_name=".$db->quote(self::LOCK_NAME_FULLTEXT_INDEX);

		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);

			$process_id = $res['ftl_pid'];
			$lockValue = $res['ftl_value'];
			$acquireLock = true;
			$log->debug("FulltextIndex REALLY::triggerUpdate got lockValue=".$lockValue.", pid=".$process_id." with ".$stmt." and ".print_r($res, true));

			if ($process_id != "-1" && !empty($process_id)) {
				//If we are in AWS land, get the task / process id from the bgp
				if ($process_id == 'load_new_task') {
					if( $this->bgp ) {
						$this->bgpDetails = $this->bgp->getDetails();
					}
					$my_pid = $this->bgpDetails['bgp_task_arn'];
				}

				// check if current locks process id is still running or if this is an invalid lock
				$psinfo = FulltextQueue::getProcessInfo($process_id);
				$log->warn("checking for lock on  lock ".$process_id);
				// TODO: unix, windows, ...
				$log->warn(array("psinfo",$psinfo));

				if (!empty($psinfo)) {
					// override existing lock
					$acquireLock = false;
					$log->debug("overriding existing lock ".$psinfo);
				}
			}

			// worst case: a background process is started, but the queue already
			// empty at this point (very fast indexer)
			if ($acquireLock) {
				// if we're using AWS then the task id is already set to the

				$stmt =  "UPDATE ".APP_TABLE_PREFIX."fulltext_locks SET ftl_pid=".$db->quote($my_pid);
				$stmt .= " WHERE ftl_name='".FulltextQueue::LOCK_NAME_FULLTEXT_INDEX."'";

				$db->exec($stmt);
				$db->commit();
			} else {
        $db->commit();
				return false;
			}
		}
		catch(Exception $ex) {
			$db->rollBack();

			$log->err($ex." stmt: ".$stmt);
			return false;
		}
		return true;
	}

	/**
	 * This function is called when the queue triggers an index update
	 * and the update process is called. It will process it as long as there are more
	 * items in the queue. If this process got started, it has the necessary lock
	 * and is the only one.
	 *
	 */
	public function startBGP()
	{
		$log = FezLog::get();

		// mark lock with pid
		if (FulltextQueue::USE_LOCKING) {
			if (!$this->updateLock()) {
        $this->bgp->setStatus("Lock already acquired by another process, aborting");
        $this->bgp->setState(2);
				return false;
			}
		}

		$this->bgp->setStatus("Fulltext index update started");

		$this->countDocs = 0;

		$log->debug(array("startBGP: call processQueue mem_used=".memory_get_usage()));
		$this->processQueue();

		if (FulltextQueue::USE_LOCKING) {
			$this->releaseLock();
		}
    $this->bgp->setState(2);

	}


	/**
	 * This function is called AFTER an object has been added or removed from
	 * the index. It can be used for periodical index optimization (default
	 * behaviour).
	 *
	 * @param unknown_type $pid
	 * @param unknown_type $op
	 */
	protected function postProcessIndex($pid, $op)
	{

		if (($this->countDocs % self::COMMIT_COUNT) == 0) {
			$this->optimizeIndex();
		}

	}

	/**
	 * Optimizes the index. Can be implemented in subclass, if needed.
	 * Default behaviour: do nothing.
	 *
	 */
	protected function optimizeIndex()
	{
		return;
	}


	/**
	 * Processes the queue. Retrieves an item using the pop() function
	 * of the queue and calls the index or remove methods.
	 *
	 */
	public function processQueue()
	{

		$queue = FulltextQueue::singleton();
		$this->totalDocs = $queue->size();
		if( $this->bgp ) {
			$this->bgpDetails = $this->bgp->getDetails();
		}

		do {
			$empty = false;
			$result = $queue->pop();

			if (is_array($result)) {
				extract($result, EXTR_REFS);

				if ($ftq_op == FulltextQueue::ACTION_DELETE) {
					$this->removeByPid($ftq_pid);
				} else {
//				  $sekData = try to get sek data frmo cache else from buidl it manually
          $sekData = array();
					$this->indexRecord($ftq_pid, $sekData);
				}

				$this->countDocs++;

				$utc_date = Date_API::getSimpleDateUTC();
				$time_elapsed = Date_API::dateDiff("s", $this->bgpDetails['bgp_started'], $utc_date);
				$date_now = new Date(strtotime($bgp_details['bgp_started']));

				if ($this->countDocs > 0) {
					$time_per_object = round(($time_elapsed / $this->countDocs), 2);

					$date_now->addSeconds($time_per_object * ($this->totalDocs - $this->countDocs));
					$tz = Date_API::getPreferredTimezone($this->bgpDetails["bgp_usr_id"]);
					$expected_finish = Date_API::getFormattedDate($date_now->getTime(), $tz);
				}

				$this->bgp->markPidAsFinished($ftq_pid);
				$this->bgp->setStatus("Finished Solr fulltext indexing for ($ftq_pid) (".$this->countDocs."/".$this->totalDocs." Added) (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");

			} else {
				$empty = true;
			}

			unset($result);
			unset($ftq_op);
			unset($ftq_pid);
			unset($ftq_key);

		} while (!$empty);

		$this->forceCommit();

		return $countDocs;
	}


	/**
	 * Returns the rule groups a user can have for listing
	 * this object.
	 *
	 * @param unknown_type $pid
	 * @return unknown
	 */
	private function getListerRuleGroups($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt =  "SELECT * FROM ".APP_TABLE_PREFIX."auth_index2_lister ";
		$stmt .= "WHERE authi_pid=".$db->quote($pid);

		try {
			$res = $db->fetchAssoc($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		if (count($res[$pid]) > 1) {
			$ruleGroups = implode(" ", $res[$pid]);
		} else {
			$ruleGroups = $res[$pid];
		}

		return $ruleGroups;
	}

	/**
	 * Maps a field to match the search engine syntax. For example
	 * date/time formats. Default: date processing to Java format.
	 *
	 */
	protected function mapFieldValue($title, $datatype, $value)
	{
		if (is_null($value)) {
			return "";
		}
		if ($datatype == FulltextIndex::FIELD_TYPE_DATE) {
			// update date format
			$value = Date_API::getFedoraFormattedDate($value);
		} elseif ($datatype == FulltextIndex::FIELD_TYPE_INT) {
		  if (is_array($value)) {
		    $newValue = array();
        foreach ($value as $val) {
          array_push($newValue, (int)$val);
        }
        $value = $newValue;
      } else {
        $value = (int)$value;
      }
    }

		return $value;
	}

  /**
   * Caches the pid into
   * will recurse into collection or communities.
   *
   * @param array $pids
   * @param array $sekData
   */
  public function cacheRecords($pids = array(), $sekData = array())
  {
    //if no data is passed, go and get it yourself for caching
    if (count($sekData) == 0) {
      $options = array();
      $filter = array();
      $filter["searchKey" . Search_Key::getID("Pid")]['override_op'] = 'OR';
      foreach ($pids as $starredPid) {
        $filter["searchKey" . Search_Key::getID("Pid")][] = $starredPid;
      }

      $current_row = 0;
      $max = "ALL";
      $order_by = "Title";

      $sekData = Record::getListing($options, array(9, 10), $current_row, $max, $order_by, false, false, $filter, 'AND', false, false, false, APP_SOLR_FACET_LIMIT, APP_SOLR_FACET_MINCOUNT, false, $createdDT, true);

    }
    if (!is_array($sekData['list'])) {
      return;
    }

    foreach ($pids as $pid) {
      $record = new RecordObject($pid);
      $dslist = $record->getDatastreams();

      foreach ($dslist as $dsitem) {
        if ($dsitem['controlGroup'] == 'M') {
          $this->indexDS($record, $dsitem);
        }
      }
    }
    $roles = array(
        'Lister',
        'Creator',
        'Editor',
    );
    $roleIDs = Auth::convertTextRolesToIDS($roles);
    $roleIDs = array_flip($roleIDs);
    $pidsString = "'" . implode("','", $pids) . "'";
    $rulesGroups = $this->getRuleGroupsChunk($pidsString, $roleIDs);
    // get the list of search keys because its easier to find and add solr dynamic field mapping that reverse lookup
    // the sekDetails later (less sql calls)

    $searchKeys = Search_Key::getList(false);

    /*
    * Custom search key (not a registered search key)
    */
    $citationKey = array(
        'sek_title' => 'citation',
        'sek_title_db' => 'citation',
        'sek_data_type' => 'text',
        'sek_relationship' => 0,
    );
    $docfields = array();
    $searchKeys[] = $citationKey;

    foreach ($sekData['list'] as $sekKey => $sekRow) {
      $pid = $sekRow['rek_pid'];
      foreach ($searchKeys as $sekDetails) {
        $title = $sekDetails["sek_title"];
        $index_title = $sekDetails["sek_title_db"];
        if ($title == 'File Attachment Content') {
          continue;
        }


        if (!array_key_exists('rek_' . $index_title, $sekRow)
            || $sekRow['rek_' . $index_title] == null
            || (is_array($sekRow['rek_' . $index_title]) && count($sekRow['rek_' . $index_title]) == 0) ) {
          continue;
        }

        //add lookups? - dont seem to come in yet - although we should get them now which is great from getlisting
        $fieldValue = $sekRow['rek_' . $index_title];

        // We want solr to cache all citations
        if ($fieldValue == "" && $title == 'citation') {
          $fieldValue = Citation::updateCitationCache($pid);
        }

        // consolidate field types
        $fieldType = $this->mapType($sekDetails['sek_data_type']);

        // search-engine specific mapping of field content (date!)
        $fieldValue = $this->mapFieldValue($title, $fieldType, $fieldValue);

        if ($fieldValue !== "") {
          // mark multi-valued search keys
          $isMultiValued = false;
          if ($sekDetails["sek_cardinality"] == 1) {
            $isMultiValued = true;
          }

          // search-engine specific mapping of field name
          $index_title = $this->getFieldName($index_title, $fieldType, $isMultiValued);

          if (APP_ES_SWITCH == "ON") {
            //Add date year copy
            if ($fieldType == FulltextIndex::FIELD_TYPE_DATE) {
              $ftName = str_replace("_dt", "_year_t", $index_title);
              $docfields[$ftName] = Date_API::getFedoraFormattedYear($fieldValue);
            }

            //Add facet fields, exact matching fields, sort fields

            if ($fieldType == FulltextIndex::FIELD_TYPE_TEXT) {
              // text blob search keys are not really suitable for sorting or faceting, so don't bother, only do varchar
              if ($sekDetails['sek_data_type'] == 'varchar') {
                $docfields[$index_title . "_s"] = $this->alphaOnlySortFormat($fieldValue);
                $ftName = str_replace("_mt", "_mft", $index_title);
                $ftName = preg_replace("/(.*)_t$/", '$1_t_ft', $ftName);
                $docfields[$ftName] = $fieldValue;
                $ftName = str_replace("_mt", "_mt_exact", $index_title);
                $docfields[$ftName] = $fieldValue;
              }
            }
          }

          $docfields[$index_title] = $fieldValue;

          //Add any lookups
          if (!empty($sekDetails['sek_lookup_function'])) {
            $docfields[$index_title . "_lookup"] = $sekRow["rek_".$sekDetails['sek_title_db']. "_lookup"];
            $docfields[$index_title . "_lookup_exact"] = $sekRow["rek_".$sekDetails['sek_title_db']. "_lookup"];
          }
          unset($fieldValue);
          unset($fieldType);
        }
      }

      // add fulltext for each datastream (fulltext is supposed to be in the special cache)
      $index_title = $this->getFieldName(self::FIELD_NAME_FULLTEXT, self::FIELD_TYPE_TEXT, true);
      $docfields[$index_title] = array();


      $ftResult = $this->getCachedContent($pid, false);
      if (!empty($ftResult) && !empty($ftResult[$pid])) {
        $docfields[$index_title] = $ftResult[$pid];
      }
      unset($ftResult);

      //now add the lister, creator and editor auth indexes


      $rules = $rulesGroups[$pid];
      if (!empty($rules)) {
        $lister_rules = '';
        $creator_rules = '';
        $editor_rules = '';

        if (!empty($rules[$roleIDs['Lister']])) {
          $lister_rules = $rules[$roleIDs['Lister']];
          $auth_title = $this->getFieldName(FulltextIndex::FIELD_NAME_AUTHLISTER, FulltextIndex::FIELD_TYPE_TEXT, false);
          $docfields[$auth_title] = $lister_rules;
        }
        if (!empty($rules[$roleIDs['Creator']])) {
          $creator_rules = $rules[$roleIDs['Creator']];
          $auth_title = $this->getFieldName(FulltextIndex::FIELD_NAME_AUTHCREATOR, FulltextIndex::FIELD_TYPE_TEXT, false);
          $docfields[$auth_title] = $creator_rules;
        }
        if (!empty($rules[$roleIDs['Editor']])) {
          $editor_rules = $rules[$roleIDs['Editor']];
          $auth_title = $this->getFieldName(FulltextIndex::FIELD_NAME_AUTHEDITOR, FulltextIndex::FIELD_TYPE_TEXT, false);
          $docfields[$auth_title] = $editor_rules;
        }
      }

      //now save it to the cache.
      $content = json_encode($docfields);
      // strip out any bad binary data from files
      $content = preg_replace('/[^(\x20-\x7F)]*/', '', $content);
      FulltextIndex::updateFulltextCache($pid, "", $content, 0);
      $returnContent[$pid] = $docfields;
      unset($docfields);
    }
    return $returnContent;
  }


  private function alphaOnlySortFormat($value) {
    return preg_replace("/([^a-zA-Z0-9])/", "", strtolower($value));
  }


	/**
	 * Inserts or updates records in the fulltext index. This function
	 * will recurse into collection or communities.
	 *
	 * @param string $pid
	 */
	public function indexRecord($pid)
	{

		if ($this->bgp) {
			$this->bgp->setHeartbeat();
			$this->bgp->setProgress(++$this->pid_count);
		}

    $cache = $this->getMultipleCachedContent(array($pid), true);
    if ($cache[$pid] == '') {
      $cache[$pid] = $this->cacheRecords(array($pid));
    }
    $this->updateFulltextIndex($pid, $cache[$pid]);

		if ($this->bgp) {
			$this->bgp->setStatus("Finished Solr fulltext indexing for ($pid)");
		}
	}


  /**
   * Inserts or updates records in the fulltext index for multiple pids at once for performance
   *
   * @param array $pids
   * @param object $queue - the current fulltext queue object
   */

  public function indexRecords($pids, $queue = null)
  {
    $log = FezLog::get();
    $cachedResults = array();
    if ($this->bgp) {
      $this->bgp->setHeartbeat();
      $this->bgp->setProgress($this->pid_count + count($pids));
    }

    $cache = $this->getMultipleCachedContent($pids, true);
    $pidsNoCache = array();
    foreach ($pids as $pid) {
      if (!array_key_exists($pid, $cache)) {
        array_push($pidsNoCache, $pid);
        $log->debug("FTI: no content for ".$pid." adding to to-be-cached list");
      } else {
        $this->updateFulltextIndex($pid, $cache[$pid]);
        //lower the rams
        unset($cache[$pid]);
      }
    }
    // free the big rams
    unset($cache);
    if (count($pidsNoCache) > 0) {
      $cachedResults = $this->cacheRecords($pidsNoCache);
    }

    $addedToQueue = false;
    foreach ($cachedResults as $cachePid => $cacheContent) {
      $queue->memorySize += strlen(serialize($cacheContent));
      if (($queue->memorySize / 1000000 < APP_SOLR_CSV_MAX_SIZE)) {
        $this->updateFulltextIndex($cachePid, $cacheContent);
      } else {
        //put it back on the queue because it didnt fit this time
        FulltextQueue::singleton()->add($cachePid);
        $log->debug("FTI: adding ".$pid." back onto queue because we are over mem limit already");
        $addedToQueue = true;
      }
      //free more rams
      unset($cachedResults[$cachePid]);
    }
    if ($addedToQueue == true) {
      FulltextQueue::singleton()->commit();
    }

    unset($cachedResults);
    if ($this->bgp) {
      $this->bgp->setStatus("Finished Solr fulltext indexing for ".count($pids)." pids");
    }
  }



	/**
	 * Indexes the content of a datastream. Taken over from previous fulltext implementation.
	 *
	 * @param array $dsitem - a ds listing item as returned from getDatastreams
	 */
	protected function indexDS($rec, $dsitem)
	{
		// determine the type of datastream
		switch ($dsitem['controlGroup']) {
			case 'X':
				break;
			case 'M':
				// managed means that we have a copy here
				$this->indexManaged($rec, $dsitem);
				break;
			case 'R':
				// index the remote object
				// leave this alone for now - the remote object could be html or doc or who knows what
				// there might also be ads on the target page and all sorts of things that we don't want to index
				break;
			default:
				// don't index it if it's unknown
				break;
		}
	}

	/**
	 * Indexes a managed datastream and does the plaintext extraction.
	 *
	 * @param unknown_type $rec
	 * @param unknown_type $dsitem
	 */
	protected function indexManaged($rec, $dsitem)
	{
		// check if the fulltext index can do anything with this stream
		$can_index = Fulltext_Tools::checkMimeType($dsitem['MIMEType']);
		if (!$can_index) {
			return;
		}

		// test for cached content
		$pid = $rec->getPid();
		$res = $this->checkCachedContent($pid, $dsitem['ID']);

		if (!empty($res) && $res['cnt'] > 0) {
			return;
		}

		// very slow...
		// TODO: have to find a solution for very large files...
		$filename = Misc::getFileTmpPath("fulltext_".rand()."_".$dsitem['ID']);


    if (defined('AWS_ENABLED') && AWS_ENABLED == 'true' && APP_FEDORA_BYPASS == 'ON') {
      $aws = AWS::get();
      $dataPath = Fedora_API::getDataPath($pid);
      $aws->saveFileContent($dataPath, $dsitem['ID'], $filename);
    } else {
      $filehandle = fopen($filename, "w");
      $rec->getDatastreamContents($dsitem['ID'], $filehandle);
      fclose($filehandle);
    }


		$textfilename = Fulltext_Tools::convertFile($dsitem['MIMEType'], $filename);
		unlink($filename);

		if (!empty($textfilename) && is_file($textfilename)) {
			$plaintext = file_get_contents($textfilename);
			unlink($textfilename);

			// index the plaintext
			if (!empty($plaintext)) {
				$this->indexPlaintext($rec, $dsitem['ID'], $plaintext);
				unset($plaintext);
			}
		}
	}

	/**
	 * Updates the fulltext index with a new or existing document. This function
	 * has to be implemented by child classes.
	 *
	 * @param string $pid
	 * @param array $fields
	 */
	protected abstract function updateFulltextIndex($pid, $fields);


	/**
	 * Prepares the plaintext and inserts it into the database fulltext cache.
	 * Note: the database table should be setup with media/large text fields.
	 *
	 * @param unknown_type $rec
	 * @param unknown_type $dsID
	 * @param unknown_type $plaintext
	 */
	private function indexPlaintext(&$rec, $dsID, &$plaintext)
	{
		$log = FezLog::get();

		$pid = $rec->getPid();
		$log->debug(array("FulltextIndex::indexPlaintext preparing fulltext for ".$pid));

		$isTextUsable = true;

		/*
		 * Some PDF's are obfuscated so we are performing a check
		 * to see if the text we extracted is actually human readable text
		 *
		 * The hueristic is that the first 1000 characters should contain
		 * 5 dictionary words
		 */
		if(function_exists('pspell_check')) {

			$pspell_link = pspell_new(APP_DEFAULT_LANG);

			$chunkToTest  = explode(' ', $plaintext, 1000);
			$numDictWords = 0;

			foreach ($chunkToTest as $word) {

				// 1 character words are valid
				// according to pspell
				if (strlen($word) <= 1)
				continue;

				if (pspell_check($pspell_link, $word)) {

					$numDictWords++;
					if( $numDictWords >= 5 ) {
						break;
					}

				}
			}

			if( $numDictWords < 5 ) {
				$isTextUsable = 0;
			}

		}
		//clean out most things
    $plaintext = preg_replace("/[^a-zA-Z0-9 ,.-]/", "", $plaintext);
    //trim it to 31000 chars max
    $plaintext = mb_strimwidth($plaintext, 0, 31000, "...");

		// insert or replace current entry
		$this->updateFulltextCache($pid, $dsID, $plaintext, $isTextUsable);
	}


	/**
	 * Completely removes this PID from the fulltext index (Solr + MySQL cache).
	 * This function has to be overwritten in subclasses. Make sure to call the
	 * parent class for caching clean-up.
	 *
	 * @param string $pid
	 */
	protected function removeByPid($pid)
	{
		$log = FezLog::get();

		$log->debug(array("removeByPid($pid)"));
		$this->deleteFulltextCache($pid, '', true);

	}


	/**
	 * Builds a fulltext query from the specified search options. This function
	 * can/should be overwritten in inherited classes to implement a search engine
	 * specific syntax. Default implementation uses the Lucene/Solr syntax.
	 *
	 * @param unknown_type $options
	 */
  protected function prepareQuery($params, $options, $rulegroups, $approved_roles, $sort_by, $start, $page_rows)
  {
    $query = '';
    $filterQuery = '';
    $i = 0;
    if ($params['words']) {
      foreach ($params['words'] as $key => $value) {
        if ($value['wf'] != 'ALL') {
          $sek_details = Search_Key::getBasicDetailsByTitle($value['wf']);

          if ($sek_details['sek_relationship'] > 0) {
            $isMulti = true;
          } else {
            $isMulti = false;
          }
          $wf = FulltextIndex::getFieldName($value['wf'], self::FIELD_TYPE_TEXT, $isMulti);
          $query .= $wf . ":(";
        } else {
          $query .= '(';
        }
        $query .= $value['w']; // need to do some escaping here?
        $query .= ')';

        $i++;
        if ($i < count($params['words'])) {
          $query .= ' ' . $value['op'] . ' ';
        }
      }
    }

    if ($params['direct']) {
      foreach ($params['direct'] as $key => $value) {
        if (strlen(trim($query)) > 0) {
          $query .= ' AND ';
        }
        $query .= '(' . $value . ')';
      }
    }

    $queryString = $query;
    $filterQuery = "(status_i:2)";
    if (!empty($rulegroups)) {
      $filterQuery .= " AND (_authlister_t:(" . $rulegroups . "))";
    }


    return array(
        'query' => $queryString,
        'filter' => $filterQuery
    );
  }

  protected function prepareAdvancedQuery($searchKey_join, $filter_join, $roles)
  {

    $filterQuery = "";

    if ($searchKey_join[2] == "") {
      $searchQuery = "*:*";
    } else {
      $searchQuery = $searchKey_join[2];
    }

    $approved_roles = array();
    if (!Auth::isAdministrator()) {
      $rulegroups = $this->prepareRuleGroups();
      $usr_id = Auth::getUserID();
      if (is_array($rulegroups)) {
        $rulegroups = implode(" OR ", $rulegroups);
      } else {
        $rulegroups = false;
      }

      foreach ($roles as $role) {
        if (!is_numeric($role)) {
          $approved_roles[] = $role;
        } else {
          $roleID = Auth::getRoleTitleByID($role);
          if ($roleID != false) {
            $approved_roles[] = $roleID;
          }
        }
      }
      if (is_numeric($usr_id)) {
        if (in_array('Creator', $approved_roles)) {
          $creatorGroups = Auth::getUserRoleAuthRuleGroupsInUse($usr_id, "Creator");
          if (is_array($creatorGroups)) {
            $creatorGroups = implode(" OR ", $creatorGroups);
            $filterQueryParts[] = "(_authcreator_t:(" . $creatorGroups . "))";
          } else {
            $filterQueryParts[] = "(_authcreator_t:(" . $rulegroups . "))";
          }
        }
        if (in_array('Editor', $approved_roles)) {
          $editorGroups = Auth::getUserRoleAuthRuleGroupsInUse($usr_id, "Editor");
          if (!empty($editorGroups)) {
            if (is_array($editorGroups)) {
              $editorGroups = implode(" OR ", $editorGroups);
              $filterQueryParts[] = "(_autheditor_t:(" . $editorGroups . "))";
            } else {
              $filterQueryParts[] = "(_autheditor_t:(" . $rulegroups . "))";
            }
          }
        }
        if (in_array('Lister', $approved_roles)) {
          $listerGroups = Auth::getUserListerAuthRuleGroupsInUse($usr_id);
          if (!empty($listerGroups)) {
            $listerGroups = implode(" OR ", $listerGroups);
            $filterQueryParts[] = "(_authlister_t:(" . $listerGroups . "))";
          }
        }
      } else {
        if (!empty($rulegroups)) {
          $filterQueryParts[] = "(_authlister_t:(" . $rulegroups . "))";
        }
      }
      if (is_array($filterQueryParts)) {
        $filterQuery = implode(" OR ", $filterQueryParts);
      } else {
        $filterQuery = "";
      }
    }

    if ($filter_join[2] != "") {
      if ($filterQuery != "") {
        $filterQuery .= " AND ";
      }
      $filterQuery .= $filter_join[2];
    }

    return array('query' => $searchQuery, 'filter' => $filterQuery);
  }


	/**
	 * Executes the prepared query in the subclass. This is an abstract class
	 * that has to be used for the specific implementation.
	 *
	 * @param unknown_type $query
	 */
	protected abstract function executeQuery($query, $options, $approved_roles, $sort_by, $start, $page_rows);


	public function prepareRuleGroups()
	{
		// gets user rule groups for this user
		$userID = Auth::getUserID();
		if (empty($userID)) {
			// get public lister rulegroups
			$userRuleGroups = Collection::getPublicAuthIndexGroups();
		} else {
			$userRuleGroups = Auth::getUserAuthRuleGroupsInUse($userID);
		}
		return $userRuleGroups;
	}

	//TODO: see why this was different from the above
	// from solr index class - oddly different!
//  public function prepareRuleGroups()
//  {
//    // gets user rule groups for this user
//    $userID = Auth::getUserID();
//    if (empty($userID)) {
//      // get public lister rulegroups
//      $userRuleGroups = Collection::getPublicAuthIndexGroups();
//    } else {
//      $userRuleGroups = Collection::getPublicAuthIndexGroups();
//    }
//    return $userRuleGroups;
//  }

	public function searchAdvancedQuery($searchKey_join, $approved_roles, $start, $page_rows) {}

	/**
	 * Issues a search request to the fulltext search engine. This is the main
	 * function to call for search. It includes dealing with sorting, authorization,
	 * paging and hit highlighting. Usually, this function is not overwritten
	 * in subclasses since it already calls the appropriate functions in the subclasses.
	 *
	 * @param $params search parameters
	 * @param unknown_type $options paging options
	 * @param unknown_type $approved_roles
	 * @param unknown_type $sort_by
	 * @param unknown_type $start
	 * @param unknown_type $page_rows
	 * @return unknown
	 */
	public function search($params, $options, $approved_roles, $sort_by, $start, $page_rows)
	{
		$log = FezLog::get();

		$userRuleGroups = $this->prepareRuleGroups($approved_roles);
		$ruleGroupStmt = implode(" OR ", $userRuleGroups);
		$log->debug(array("FulltextIndex::search userid='".$userID."', rule groups='$ruleGroupStmt'"));

		if (!empty($sort_by)) {

			$sek_id = str_replace("searchKey", "", $sort_by);
			if( $sek_id == 0 ) {
				$sort_by = 'score';
			} else {
				$sek_data = Search_Key::getDetails($sek_id);
				$sort_name = FulltextIndex::getFieldName($sek_data['sek_title']);
				$sort_by = $this->getFieldName($sort_name, $this->mapType($sek_data['sek_data_type']), false, true);
			}

		}

		// prepare fulltext query string (including auth filters)
		$query = $this->prepareQuery($params, $options, $ruleGroupStmt, $approved_roles, $sort_by, $start, $page_rows);

		// send query to search engine
		$log->debug(array("FulltextIndex::search query ",$query));
		$log->debug(array("FulltextIndex::search sort_by='".$sort_by."'"));
		$qresult = $this->executeQuery($query, $options, $approved_roles, $sort_by, $start, $page_rows);

		$total_rows = $qresult['total_rows'];
		$log->debug(array("FulltextIndex::search found ".$total_rows." items"));

		$result = array();
		$result['list'] = $qresult['docs'];
		$result['total_rows'] = $total_rows;

		return $result;
	}


	/**
	 * This function exists for historical reasons (e.g. workflow fulltext index)
	 * and can be called to insert/update an object in the fulltext index.
	 *
	 * @param string $pid
	 */
	public static function indexPid($pid)
	{
		FulltextQueue::singleton()->add($pid);
	}

	/**
	 * Removes the single datastream of an object from the fulltext index.
	 * The caller has to ensure that the datastream is also deleted in
	 * Fedora - otherwise nothing will happen.
	 *
	 * This function is public and can be called from anywhere (like indexPid).
	 *
	 * @param string $pid
	 * @param string $dsID
	 */
	public function removeByDS($pid, $dsID)
	{
		// Re-index object. Since the datastream is not in Fedora
		// anymore, the cache will not be rebuilt
		FulltextQueue::singleton()->add($pid);

	}

	/**
	 * Maps the Fez search key type to the search engine specific type.
	 *
	 * @param unknown_type $fezName
	 */
	public function mapType($sek_data_type)
	{
		switch ($sek_data_type) {
			case "varchar":
				$datatype = FulltextIndex::FIELD_TYPE_TEXT; break; // _s?
			case "int":
				$datatype = FulltextIndex::FIELD_TYPE_INT; break;
			case "text":
				$datatype = FulltextIndex::FIELD_TYPE_TEXT; break;
			case "date":
				$datatype = FulltextIndex::FIELD_TYPE_DATE; break;
			default:
				$datatype = FulltextIndex::FIELD_TYPE_TEXT; break;
		}

		return $datatype;
	}


	/**
	 * Retrieves the cached plaintext for a (pid,datastream) pair from the
	 * fulltext cache.
	 *
	 * @param string $pid
	 * @param string $dsID
	 * @return plaintext of datastream, null on error
	 */
//	protected function getCachedContent($pid, $dsID)
//	{
//		$log = FezLog::get();
//		$db = DB_API::get();
//    if (defined("APP_SQL_CACHE_DBHOST")) {
//      $db = DB_API::get('db_cache');
//    } else {
//      $db = DB_API::get();
//    }
//
//      $stmt = "SELECT ftc_pid as pid, ftc_dsid as dsid, ftc_content as content ".
//        		"FROM ".APP_TABLE_PREFIX.FulltextIndex::FULLTEXT_TABLE_NAME." ".
//        		"WHERE ftc_pid=".$db->quote($pid)." ".
//        		"AND ftc_dsid=".$db->quote($dsID)." " .
//        		"AND ftc_is_text_usable = 1";
//
//		try {
//			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
//		}
//		catch(Exception $ex) {
//			$log->err($ex);
//			$res = null;
//		}
//
//        //This assumes this function is run without anyone logged in. IE background process
//        foreach ($res as $key => $value) {
//            $userPIDAuthGroups = Auth::getAuthorisationGroups($value['pid'], $value['dsid']);
//            if (!in_array('Lister', $userPIDAuthGroups)) {
//                unset($res[$key]);
//            }
//        }
//		return $res;
//	}

	protected function checkCachedContent($pid, $dsID)
	{
		$log = FezLog::get();
    if (defined("APP_SQL_CACHE_DBHOST")) {
      $db = DB_API::get('db_cache');
    } else {
      $db = DB_API::get();
    }

		$stmt = "SELECT count(ftc_pid) as cnt ".
        		"FROM ".APP_TABLE_PREFIX.FulltextIndex::FULLTEXT_TABLE_NAME." ".
        		"WHERE ftc_pid=".$db->quote($pid)." ".
        		"AND ftc_dsid=".$db->quote($dsID);

		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			$res = null;
		}
		return $res;

	}

	/**
	 * Removes the specified datastream(s) from the MySQL fulltext cache. If
	 * datastream id is left blank, the whole object is removed from the
	 * fulltext index.
	 *
	 * @param string $pid
	 * @param string $dsID
	 */
	public function deleteFulltextCache($pid, $dsID='', $deleteAll = false)
	{
		$log = FezLog::get();
    if (defined("APP_SQL_CACHE_DBHOST")) {
      $db = DB_API::get('db_cache');
    } else {
      $db = DB_API::get();
    }


    $stmt = "DELETE FROM ".APP_TABLE_PREFIX.FulltextIndex::FULLTEXT_TABLE_NAME." ".
				"WHERE ".
	        	"ftc_pid=".$db->quote($pid);

		if ($deleteAll !== true) {
			$stmt .= " AND".
	        		 " ftc_dsid=".$db->quote($dsID);
		}
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
	}

	/**
	 * Updates the fulltext cache. Inserts new and replaces existing entries.
	 *
	 * @param string $pid
	 * @param string $dsID
   * @param string $fulltext
   * @param integer $is_text_usable
   * @return boolean
	 */
	protected function updateFulltextCache($pid, $dsID, &$fulltext, $is_text_usable = 1)
	{
		$log = FezLog::get();
    if (defined("APP_SQL_CACHE_DBHOST")) {
      $db = DB_API::get('db_cache');
    } else {
      $db = DB_API::get();
    }

		// prepare ids

		// prepare text for SQL
		$fulltext = utf8_encode($fulltext);

		// start a new transaction
		$db->beginTransaction();
		$this->deleteFulltextCache($pid, $dsID);
		// REPLACE: MySQL specific syntax
		// can be replaced with IF EXISTS INSERT or DELETE/INSERT for other databases
		// or use transactional integrity - if using multiple indexing processes
		$stmt = "INSERT INTO ".APP_TABLE_PREFIX.FulltextIndex::FULLTEXT_TABLE_NAME." ";

    $values = array($pid,$dsID,$is_text_usable);
    if(! empty($fulltext)) {
      $stmt .= "(ftc_pid, ftc_dsid, ftc_is_text_usable, ftc_content) VALUES (?,?,?,?)";
      $values[] = str_replace("\t", ' ', (str_replace("\n", ' ', (str_replace('"', '""', $fulltext)))));
    } else {
      $stmt .= "(ftc_pid, ftc_dsid, ftc_is_text_usable) VALUES (?,?,?)";
    }

		try {
			$db->query($stmt, $values);
			$db->commit();
		}
		catch(Exception $ex) {
			$db->rollBack();
			$log->err($ex);
			return false;
		}

	}


  /**
   * Internally maps the name of a Fez search key to the search engine's
   * internal syntax. This function is usually overwritten in subclasses
   * The default behaviour is to replace spaces with underscores.
   *
   * @param string $fezName
   * @param int $datatype
   * @param string $multiple
   * @return string name of field in search engine
   */
  public function getFieldName($fezName, $datatype = FulltextIndex::FIELD_TYPE_TEXT,
                               $multiple = false)
  {
    $fezName .= '_';
    if ($multiple) {
      $fezName .= 'm';
    }

    switch ($datatype) {
      case FulltextIndex::FIELD_TYPE_TEXT:
        $fezName .= 't';
        break;
      case FulltextIndex::FIELD_TYPE_DATE:
        $fezName .= 'dt';
        break;
      case FulltextIndex::FIELD_TYPE_INT:
        $fezName .= 'i';
        break;
      case FulltextIndex::FIELD_TYPE_VARCHAR :
        $fezName .= 't';
        break;
      default:
        $fezName .= 't';
    }

    return $fezName;
  }

  /**
   * @param string $pids
   * @param array $roles
   * @return array|string
   */
  public function getRuleGroupsChunk($pids, $roles)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = 'SELECT authi_pid, authi_role, authi_arg_id
                  FROM ' . APP_TABLE_PREFIX . 'auth_index2
                  WHERE authi_pid IN (' . $pids . ')
                        AND authi_role IN (' . implode(',', $roles) . ')';

    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    } catch (Exception $ex) {
      $log->err($ex);
      return '';
    }
    $ret = array();
    foreach ($res as $row) {
      $ret[$row['authi_pid']][$row['authi_role']] = $row['authi_arg_id'];
    }

    return $ret;
  }

  public function preBuildCitations($pids)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT rek_pid
                FROM " . APP_TABLE_PREFIX . "record_search_key
                WHERE rek_pid IN (" . $pids . ") AND
                      (rek_citation IS NULL OR rek_citation = '')";

    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    } catch (Exception $ex) {
      $log->err($ex);
      return '';
    }

    $rebuildCount = count($res);
    $rCounter = 0;
    foreach ($res as $pidData) {
      $rCounter++;
      $log->debug(array("processQueue: about to pre build citation " . $pidData['rek_pid'] . " (" . $rCounter . "/" . $rebuildCount . ")"));

      Citation::updateCitationCache($pidData['rek_pid']);
    }
  }

  public function preCacheDatastreams($pids)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if ($this->bgp) {
      $this->bgp->setStatus("Caching datastreams");
    }
    $res = array();
    if (defined("APP_SQL_CACHE_DBHOST")) {
      $db_cache = DB_API::get('db_cache');

      $stmt = "SELECT rek_file_attachment_name_pid as rek_pid, rek_file_attachment_name
                FROM " . APP_TABLE_PREFIX . "record_search_key_file_attachment_name
                WHERE rek_file_attachment_name_pid IN (" . $pids . ") AND
                rek_file_attachment_name LIKE '%.pdf'";

      try {
        $potentials = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
      } catch (Exception $ex) {
        $log->err($ex);
      }

      if (count($potentials) > 0) {
        $pdfPidsSet = array();

        foreach ($potentials as $pt) {
          $pdfPidsSet[] = $pt['rek_pid'];
        }

        $pdfPids = implode("','", $pdfPidsSet);

        $stmt = "SELECT ftc_pid as rek_pid, ftc_dsid
                FROM " . APP_TABLE_PREFIX . "fulltext_cache
                WHERE ftc_pid IN ('" . $pdfPids . "')";
        try {
          $res = $db_cache->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
        } catch (Exception $ex) {
          $log->err($ex);
        }
        $missing = array();
        $found = array();
        foreach ($potentials as $pt) {
          foreach ($res as $hit) {
            if ($hit['rek_pid'] == $pt['rek_pid'] && $hit['ftc_dsid'] == $pt['rek_file_attachment_name']) {
              $found[] = $hit['rek_pid'];
            }
          }
        }
        foreach ($potentials as $pt) {
          if (!in_array($pt['rek_pid'], $found)) {
            $missing[]['rek_pid'] = $pt['rek_pid'];
          }
        }
        $res = $missing;
      }
    } else {
      $stmt = "SELECT rek_file_attachment_name_pid as rek_pid
                FROM " . APP_TABLE_PREFIX . "record_search_key_file_attachment_name
                LEFT JOIN " . APP_TABLE_PREFIX . "fulltext_cache ON rek_file_attachment_name_pid = ftc_pid AND rek_file_attachment_name = ftc_dsid
                WHERE ftc_dsid IS NULL AND rek_file_attachment_name_pid IN (" . $pids . ") AND
                      rek_file_attachment_name LIKE '%.pdf'";

      try {
        $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
      } catch (Exception $ex) {
        $log->err($ex);
      }

    }


    foreach ($res as $pidData) {
      $record = new RecordObject($pidData['rek_pid']);
      $dslist = $record->getDatastreams();

      if (count($dslist) == 0) {
        if ($this->bgp) {
          $this->bgp->setStatus($pidData['rek_pid'] . " has no datastreams but it should");
        }
        continue;
      }

      foreach ($dslist as $dsitem) {
        $this->indexDS($record, $dsitem);
      }
    }

    if ($this->bgp) {
      $this->bgp->setStatus("Finished Caching datastreams");
    }
  }

  /**
   * Retrieves the cached plaintext for a (pid,datastream) pair from the
   * fulltext cache.
   *
   * @param string $pids
   * @param string $dsID
   * @return plaintext of datastream, null on error
   */
  public function getCachedContent($pids, $noDatastream = false)
  {
    $log = FezLog::get();
    if (defined("APP_SQL_CACHE_DBHOST")) {
      $db = DB_API::get('db_cache');
    } else {
      $db = DB_API::get();
    }

    $pids = str_replace('"', "'", $pids);
    // Remove newlines, page breaks and replace " with "" (which is how to escape for CSV files)
    //$stmt = "SELECT ftc_pid as pid, REPLACE(REPLACE(REPLACE(ftc_content, '\"','\"\"'), '\n', ' '), '\t', ' ') as content, ftc_dsid as dsid ".
    $stmt = "SELECT ftc_pid as pid, ftc_content as content, ftc_dsid as dsid " .
        'FROM ' . APP_TABLE_PREFIX . FulltextIndex::FULLTEXT_TABLE_NAME .
        ' WHERE ftc_pid IN ('.Misc::arrayToSQLBindStr($pids).")";

    if ($noDatastream) {
      $stmt .= " AND ftc_dsid = ''";
    } else {
      $stmt .= " AND ftc_is_text_usable = 1";
    }

    try {
      $res = $db->fetchAll($stmt, $pids, Zend_Db::FETCH_ASSOC);
    } catch (Exception $ex) {
      $log->err($ex.' FULL SQL: '.$stmt);
      $res = null;
    }

    //This assumes this function is run without anyone logged in. IE background process
    foreach ($res as $key => $value) {
      $userPIDAuthGroups = Auth::getAuthorisationGroups($value['pid'], $value['dsid']);
      if (!in_array('Lister', $userPIDAuthGroups)) {
        unset($res[$key]);
      }
    }

    $ret = array();
    foreach ($res as $row) {
      if (!empty($ret[$row['pid']])) {
        $ret[$row['pid']] .= "\t" . $row['content'];
      } else {
        $ret[$row['pid']] = $row['content'];
      }
    }

    return $ret;
  }


  /**
   * Retrieves the cached plaintext for a (pid,datastream) pair from the
   * fulltext cache.
   *
   * @param string $pid
   * @param string $dsID
   * @return plaintext of datastream, null on error
   */
  public function getMultipleCachedContent($pids, $noDatastream = false)
  {
    $log = FezLog::get();
    if (defined("APP_SQL_CACHE_DBHOST")) {
      $db = DB_API::get('db_cache');
    } else {
      $db = DB_API::get();
    }

    $pids = str_replace('"', "'", $pids);
    // Remove newlines, page breaks and replace " with "" (which is how to escape for CSV files)
    //$stmt = "SELECT ftc_pid as pid, REPLACE(REPLACE(REPLACE(ftc_content, '\"','\"\"'), '\n', ' '), '\t', ' ') as content, ftc_dsid as dsid ".
    $stmt = "SELECT ftc_pid as pid, ftc_content as content " .
        'FROM ' . APP_TABLE_PREFIX . FulltextIndex::FULLTEXT_TABLE_NAME .
        ' WHERE ftc_pid IN ('.Misc::arrayToSQLBindStr($pids).")";

    if ($noDatastream) {
      $stmt .= " AND ftc_dsid = ''";
    } else {
      $stmt .= " AND ftc_is_text_usable = 1";
    }

    try {
      $res = $db->fetchAssoc($stmt, $pids);
    } catch (Exception $ex) {
      $log->err($ex);
      $res = null;
    }

    //This assumes this function is run without anyone logged in. IE background process
    foreach ($res as $key => $value) {
      if ($noDatastream) {
        $res[$key] = (array)json_decode(str_replace('""', '"', $value['content']));
      } else {
        $res[$key] = mb_strimwidth($value['content'], 0, 31000, "...");
      }
    }

    return $res;
  }


  public function escape($value)
  {
    //list taken from http://lucene.apache.org/java/docs/queryparsersyntax.html#Escaping%20Special%20Characters
    $pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\/|\[|]|\^|"|~|\*|\?|:|\\\)/';
    $replace = '\\\$1';

    return preg_replace($pattern, $replace, $value);
  }

  /**
   * Escape a value meant to be contained in a phrase for special query characters
   *
   * @param string $value
   * @return string
   */
  public function escapePhrase($value)
  {
    $pattern = '/("|\\\)/';
    $replace = '\\\$1';

    return preg_replace($pattern, $replace, $value);
  }

  /**
   * Escape a boolean value
   *
   * @param string $value
   * @return string
   */
  public function escapeBooleans($value)
  {
    $value = strtolower($value);
    //list taken from http://lucene.apache.org/java/docs/queryparsersyntax.html#Escaping%20Special%20Characters
    $pattern = '/ (and|or) /';
    $replace = ' \\\$1 ';
    return preg_replace($pattern, $replace, $value);
  }

}
