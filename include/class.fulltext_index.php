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
include_once(APP_INC_PATH . "class.fulltext_index_solr_csv.php");
include_once(APP_INC_PATH . "class.citation.php");
include_once(APP_INC_PATH . "Apache/Solr/Service.php");
//include_once(APP_INC_PATH . "class.memory.php");

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

	/**
	 * Links this instance to a corresponding background process.
	 *
	 * @param BackgroundProcess_Fulltext_Index $bgp
	 */
	public function setBGP(&$bgp) 
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
		
		$my_process = FulltextQueue::getProcessInfo();
		$my_pid = $my_process['pid'];

		if (!is_numeric($my_pid)) {
			$my_pid = 'null';
		}

		$db->beginTransaction();

		$stmt = "SELECT ftl_value, ftl_pid FROM ".APP_TABLE_PREFIX."fulltext_locks ".
					"WHERE ftl_name=".$db->quote(self::LOCK_NAME_FULLTEXT_INDEX);
		
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		
			$process_id = $res['ftl_pid'];
			$lockValue = $res['ftl_value'];
			$acquireLock = true;
			$log->debug("FulltextIndex REALLY::triggerUpdate got lockValue=".$lockValue.", pid=".$process_id." with ".$stmt." and ".print_r($res, true));
		
			if ($lockValue != -1 && !empty($process_id) && is_numeric($process_id)) {
			 
				// check if process is still running or if this is an invalid lock
				$psinfo = FulltextQueue::getProcessInfo($process_id);
				$log->debug("checking for lock on  lock ".$process_id);
				// TODO: unix, windows, ...
				$log->debug(array("psinfo",$psinfo));

				if (!empty($psinfo)) {
					// override existing lock
					$acquireLock = false;
					$log->debug("overriding existing lock ".$psinfo);
				}
			}
					
			// worst case: a background process is started, but the queue already
			// empty at this point (very fast indexer)
			if ($acquireLock) {


				$stmt =  "UPDATE ".APP_TABLE_PREFIX."fulltext_locks SET ftl_pid=".$db->quote($my_pid);
				$stmt .= " WHERE ftl_name='".FulltextQueue::LOCK_NAME_FULLTEXT_INDEX."'";
		
				$db->query($stmt);
				$db->commit();
			} else {
				return false;
			}
		}
		catch(Exception $ex) {
			$db->rollBack();
			
			$log->err($ex);
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

			Logger::debug($this->countDocs . " / " . self::COMMIT_COUNT);
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

//		$countDocs = 0;
		$queue = FulltextQueue::singleton();
		$this->totalDocs = $queue->size();
		//$this->memory_man = new memory_man();
		if( $this->bgp ) {
			$this->bgpDetails = $this->bgp->getDetails();
		}

		do {
			$empty = false;

			//Logger::debug("processQueue: start indexing mem_used=".memory_get_usage());
			$result = $queue->pop();

			if (is_array($result)) {
				extract($result, EXTR_REFS);
				 
				if ($ftq_op == FulltextQueue::ACTION_DELETE) {
					//Logger::debug("FulltextIndex::processQueue - calling removeByPid for $ftq_pid");
					$this->removeByPid($ftq_pid);
				} else {
					//Logger::debug("FulltextIndex::processQueue - calling indexRecord for $ftq_pid");
					$this->indexRecord($ftq_pid);
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
		if (empty($value)) {
			return;
		}
		if ($datatype == FulltextIndex::FIELD_TYPE_DATE) {
			// update date format
			$date = new Date($strValue);
			//$strValue = $date->format('%Y%m%d T 00:00:00Z');
			$value = Date_API::getFedoraFormattedDateUTC($value);
		}

		return $value;
	}


	/**
	 * Inserts or updates records in the fulltext index. This function
	 * will recurse into collection or communities.
	 *
	 * @param unknown_type $pid
	 * @param unknown_type $regen
	 * @param unknown_type $topcall
	 */
	public function indexRecord($pid, $regen=false, $topcall=true)
	{
		$this->regen = $regen;

		if ($this->bgp) {
			$this->bgp->setHeartbeat();
			$this->bgp->setProgress(++$this->pid_count);
		}

		$record = new RecordGeneral($pid);
		$dslist = $record->getDatastreams();

		foreach ($dslist as $dsitem) {
			$this->indexDS($record, $dsitem);
		}

		//
		// get record metadata from Fez search index
		//

		// use all search keys (large list), because e.g. status is not in advanced search
		$searchKeys = Search_Key::getList(false);
		$docfields = array();
		$fieldTypes = array();

		/*
		 * Custom search key (not a registered search key)
		 */
		$citationKey = array(
            'sek_title'         =>  'citation',
            'sek_title_db'      =>  'rek_citation', 
            'sek_data_type'     =>  'text',
            'sek_relationship'  =>  0,
		);

		$searchKeys[] = $citationKey;

		foreach ($searchKeys as $sekDetails) {
			$title = $sekDetails["sek_title"];
			if ($title == 'File Attachment Content') {
				continue;
			}

			// TODO: lookups are disabled for the moment
			// they are a problem because data type does not match,
			// e.g. "Display Type" (integer, core 1:1) -> lookup returns string
			// but for full-text searching subjects this would be nice to have
			$fieldValue = Record::getSearchKeyIndexValue($pid, $title, false, $sekDetails);
			 
			// We want solr to cache all citations
			if($fieldValue == "" && $title == 'citation') {
				$fieldValue = Citation::updateCitationCache($pid);
			}

			// consolidate field types
			$fieldType = $this->mapType($sekDetails['sek_data_type']);

			// search-engine specific mapping of field content (date!)
			$fieldValue = $this->mapFieldValue($title, $fieldType, $fieldValue);
			 
			if( $fieldValue != "" ) {
				// mark multi-valued search keys
				$isMultiValued = false;
				if ($sekDetails["sek_relationship"] == 1) {
					$isMultiValued = true;
					$fieldTypes[$title.FulltextIndex::FIELD_MOD_MULTI] = true;
				}
				 
				// search-engine specific mapping of field name
				$title = $this->getFieldName($title, $fieldType, $isMultiValued);
				$docfields[$title] = $fieldValue;
				$fieldTypes[$title] = $fieldType;
				unset($fieldValue);
				unset($fieldType);
			}
		}

		unset($searchKeys);

		// add fulltext for each datastream (fulltext is supposed to be in the special cache)
		$title = $this->getFieldName(self::FIELD_NAME_FULLTEXT, self::FIELD_TYPE_TEXT, true);
		$docfields[$title] = array();
		$fieldTypes[$title] = self::FIELD_TYPE_TEXT;
		$fieldTypes[$title.FulltextIndex::FIELD_MOD_MULTI] = true;

		foreach ($dslist as $dsitem) {
			$dsid = $dsitem['ID'];
			$ftResult = $this->getCachedContent($pid, $dsid);
			if (!empty($ftResult) && !empty($ftResult['content'])) {
				$docfields[$title][$dsid] = $ftResult['content'];
			}
			unset($ftResult);
		}

		//
		// add lister security index to document - kind of special
		// maybe this needs more abstraction for new search engines
		// _authindex solr: tokenized, indexed and stored _t
		//
		$auth_title = $this->getFieldName(FulltextIndex::FIELD_NAME_AUTH, FulltextIndex::FIELD_TYPE_TEXT, false);
		$docfields[$auth_title] = $this->getListerRuleGroups($pid);
		$fieldTypes[$auth_title] = FulltextIndex::FIELD_TYPE_TEXT;
		 
		//
		// now we have everything in $docfields >> do update
		//
		$this->updateFulltextIndex($pid, $docfields, $fieldTypes);

		if ($this->bgp) {
			$this->bgp->setStatus("Finished Solr fulltext indexing for ".$record->getFieldValueBySearchKey("Title")." ($pid)");
		}

		unset($record);
		unset($dslist);
		unset($docfields);
		unset($fieldTypes);
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
		//Logger::debug("---------------> cached content res is: ".$res['pid']);

		if (!empty($res) && $res['cnt'] > 0) {
			//Logger::debug("- use cached content for $pid/".$dsitem['ID']);
			return;
		}

		// very slow...
		// TODO: have to find a solution for very large files...
		$filename = APP_TEMP_DIR."fulltext_".rand()."_".$dsitem['ID'];

		$filehandle = fopen($filename, "w");
		$rec->getDatastreamContents($dsitem['ID'], $filehandle);
		fclose($filehandle);

		$textfilename = Fulltext_Tools::convertFile($dsitem['MIMEType'], $filename);
		unlink($filename);

		if (!empty($textfilename) && is_file($textfilename)) {
			//Logger::debug("- got converted text in file ".$textfilename);
			$plaintext = file_get_contents($textfilename);
			unlink($textfilename);

			// index the plaintext
			if (!empty($plaintext)) {
				//Logger::debug("calling indexPlaintext for datastream ".$dsitem['ID']);
				$this->indexPlaintext($rec, $dsitem['ID'], $plaintext);
				unset($plaintext);
			}
		}
	}

	/**
	 * Updates the fulltext index with a new or existing document. This function
	 * has to be implemented by child classes.
	 *
	 * @param unknown_type $pid
	 * @param unknown_type $fields
	 */
	protected abstract function updateFulltextIndex($pid, $fields, $fieldTypes);


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
		$this->deleteFulltextCache($pid);

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
		return $options["q"];
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
/*			$ses = Auth::getSession();
			$userRuleGroups = $ses['auth_index_user_rule_groups']; */
			$userRuleGroups = Auth::getUserAuthRuleGroupsInUse($userID);
		}
		return $userRuleGroups;
	}

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
		$snips = $qresult['snips'];

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
		//Logger::debug("FulltextIndex::removeByDS");
		// delete fulltext cache for this datastream
		$this->deleteFulltextCache($pid, $dsID);

		// Re-index object. Since the datastream is not in Fedora
		// anymore, the cache will not be rebuilt
		FulltextQueue::singleton()->add($pid);

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
	public function getFieldName($fezName, $datatype=FulltextIndex::FIELD_TYPE_TEXT,
	$multiple=false) 
	{
			
		return strtolower(preg_replace('/\s/', '_', $fezName));
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
	protected function getCachedContent($pid, $dsID) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		//$GLOBALS['db_api']->dbh->autoCommit(true);
		
		$stmt = "SELECT ftc_pid as pid, ftc_dsid as dsid, ftc_content as content ".
        		"FROM ".APP_TABLE_PREFIX.FulltextIndex::FULLTEXT_TABLE_NAME." ".
        		"WHERE ftc_pid=".$db->quote($pid)." ".
        		"AND ftc_dsid=".$db->quote($dsID)." " .
        		"AND ftc_is_text_usable = 1";
		
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			$res = null;
		}
		return $res;
	}

	protected function checkCachedContent($pid, $dsID) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
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
	protected function deleteFulltextCache($pid, $dsID='') 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM ".APP_TABLE_PREFIX.FulltextIndex::FULLTEXT_TABLE_NAME." ".
				"WHERE ".
	        	"ftc_pid=".$db->quote($pid);

		if ($dsID > '') {
			$stmt .= " AND".
	        		 " ftc_dsid=".$db->quote($dsID);
		}
		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
		//Logger::debug("deleted existing content for ($pid, $dsID)");
	}

	/**
	 * Updates the fulltext cache. Inserts new and replaces existing entries.
	 *
	 * @param unknown_type $pid
	 * @param unknown_type $dsID
	 */
	protected function updateFulltextCache($pid, $dsID, &$fulltext, $is_text_usable = 1) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		//Logger::debug("FulltextIndex::indexPlaintext inserting fulltext for ($pid,$dsID) into database");
		
		// prepare ids

		// prepare text for SQL
		$fulltext = utf8_encode($fulltext);
		
		// start a new transaction
		//$GLOBALS['db_api']->dbh->autoCommit(false);
		//$this->deleteFulltextCache($pid, $dsID);

		$db->beginTransaction();
		$this->deleteFulltextCache($pid, $dsID);
		// REPLACE: MySQL specific syntax
		// can be replaced with IF EXISTS INSERT or DELETE/INSERT for other databases
		// or use transactional integrity - if using multiple indexing processes		
		$stmt = "INSERT INTO ".APP_TABLE_PREFIX.FulltextIndex::FULLTEXT_TABLE_NAME." ";
        	
        $values = array($pid,$dsID,$is_text_usable);        
        if(! empty($fulltext)) {
        	$stmt .= "(ftc_pid, ftc_dsid, ftc_is_text_usable, ftc_content) VALUES (?,?,?,?)";
        	$values[] = $fulltext;
        }
        else
			$stmt .= "(ftc_pid, ftc_dsid, ftc_is_text_usable) VALUES (?,?,?)";
        
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

}
