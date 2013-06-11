<?php
/**
 * This class implements a persistent queue that tracks objects to be added
 * or removed from the fulltext index. It is implemented with the singleton
 * pattern and will only commit the results once when the request ends (on
 * object destruction) or when an explicit commit is called.
 *
 * <p>Usage:</p>
 * <ul>
 * <li>FulltextQueue::singleton()->add(pid)
 * <li>FulltextQueue::singleton()->remove(pid)
 * </ul>
 *
 * After commiting the object(s) to the database, this class will trigger
 * a background process to process the queue. It is up to this background
 * process to deal with concurrency issues that come from multiple processes.
 *
 *
 * <em>Note:</em>
 * The issues of concurrency have been dealt with in this class at first. A
 * full application-level locking had been implemented. The problem with that
 * approach was that a crashing background process would not release the locks.
 * In contrast, database locks can be kept only in the same thread, but will
 * be released if the thread terminates unexpectedly.
 *
 *
 * @author Kai Jauslin <kai.jauslin@library.ethz.ch>
 * @version 1.1, February 2008
 *
 */

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.bgp_fulltext_index.php");
include_once(APP_INC_PATH . "class.logger.php");
include_once(APP_INC_PATH . "class.sherpa_romeo.php");

class FulltextQueue
{
	// disable this to allow multiple indexer processes
	const USE_LOCKING = true;
	const LOCK_NAME_FULLTEXT_INDEX = 'indexer';

	const ACTION_INSERT = 'I';
	const ACTION_DELETE = 'D';

	// in-memory array
	private $pids;

	// singleton instance (per request/thread) of this class
	private static $instance = null;


	// constructor is private: use getInstance for access
	private function __construct()
	{
		$this->pids = array();
	}

	public function __destruct()
	{

	}

	/**
	 * Returns the singleton queue instance.
	 *
	 * @return instance of class FulltextQueue
	 */
	public static function singleton()
	{
		$db = DB_API::get();
		$log = FezLog::get();

		if (!is_object(self::$instance)) {
			self::$instance = new FulltextQueue();
			$log->debug('self::instance not an object, storing reference to database handler');
			// keep reference to database handler - this is needed
			// for destruction time!
			self::$instance->db_api = $db;
		}

		return self::$instance;
	}


	/**
	 * Adds a document for indexing to the queue. If updating a document,
	 * use the insert action (FulltextQueue::ACTION_INSERT). To delete
	 * a document from the index, use FulltextQueue::ACTION_DELETE. The
	 * results of this functions are committed to the database when a
	 * commit() is called, or the thread ends. When doing conflicting calls
	 * with remove, the last one called is used.
	 *
	 * @param String $pid pid of the object in the fez index
	 */
	public function add($pid)
	{
		$log = FezLog::get();

		//if not in queue and record exists
		if (!array_key_exists($pid, $this->pids) && (Record::getIfRecordExists($pid))) {
			$this->pids[$pid] = FulltextQueue::ACTION_INSERT;
			$log->debug("FulltextQueue::add($pid)");
		}
	}

	/**
	 * @see description for add()
	 */
	public function remove($pid)
	{
		if (!$this->pids[$pid]) {
			$this->pids[$pid] = FulltextQueue::ACTION_DELETE;
		}
	}

	/**
	 * Starts the background process which will process the queue.
	 *
	 */
	private static function createUpdateProcess()
	{
		$bgp = new BackgroundProcess_Fulltext_Index();
		$bgp->register(serialize(array()), APP_SYSTEM_USER_ID); // todo: maybe take something other than admin
	}

	public static function getProcessInfo($pid='')
	{
		if (empty($pid)) {
			return array(
			     'pid'   =>  getmypid(),
			);
		} else {
			// Windows
			if ((stristr(PHP_OS, 'win')) && (!stristr(PHP_OS, 'darwin'))) {
				//exec("tasklist /FI \"PID eq " . $pid . "\"", $output); // This will work in W7 is psinfo not installed
				exec("pslist.exe /accepteula " . $pid, $output);
				if (count($output) < 4) {
					return false;
				}
				return $output;
			} else {
				exec("ps " . $pid, $output);
				if (count($output) < 2) {
					return false;
				}
				return $output;
			}
		}
	}

  public function isFinishedProcessing() {

    $log = FezLog::get();
    $db = DB_API::get();

    //First see if there is anything left in it
    $size = FulltextQueue::size();

    $stmt = "SELECT ftl_value, ftl_pid FROM ".APP_TABLE_PREFIX."fulltext_locks ".
      "WHERE ftl_name=".$db->quote(self::LOCK_NAME_FULLTEXT_INDEX);


    $res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);

    $process_id = $res['ftl_pid'];
    $lockValue = $res['ftl_value'];
    $acquireLock = true;
    $log->debug("FulltextIndex::triggerUpdate got lockValue=".$lockValue.", pid=".$process_id." with ".$stmt." and ".print_r($res, true));

    //If the background process hasn't kicked off yet report back as false because we can't ps check for it yet
    if ($process_id == -2) {
      return false;
    }

    if ($lockValue > 0 && !empty($process_id) && is_numeric($process_id)) {

      // check if process is still running or if this is an invalid lock
      $psinfo = self::getProcessInfo($process_id);

      // TODO: unix, windows, ...
      $log->debug(array("psinfo", $psinfo));

      if (!empty($psinfo)) {
        // override existing lock
        $acquireLock = false;
        $log->debug("overriding existing lock ".$psinfo);
      }
    } elseif ($size > 0) { // if there is no running process and there is stuff in the queue, trigger an update
      $acquireLock = false;
      FulltextQueue::triggerUpdate();
    }
    return $acquireLock;

  }

	public function	triggerUpdate()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (!self::USE_LOCKING) {
			$log->debug("not using locking - starting background process directly");
			$this->createUpdateProcess();
			return;
		}

		/**
		 * CHECK PROCESS MUTEX
		 * 1. Check lock state
		 * 2. If locked, check if process is running
		 * 3. If process if running: commit/end transaction
		 * 4. If process is not running: clean lock
		 * 5. Acquire lock
		 * 6. Run process
		 *
		 */

		// Start a transaction explicitly.
		$db->beginTransaction();

		$stmt = "SELECT ftl_value, ftl_pid FROM ".APP_TABLE_PREFIX."fulltext_locks ".
					"WHERE ftl_name=".$db->quote(self::LOCK_NAME_FULLTEXT_INDEX);

		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);

			$process_id = $res['ftl_pid'];
			$lockValue = $res['ftl_value'];
			$acquireLock = true;
			$log->debug("FulltextIndex::triggerUpdate got lockValue=".$lockValue.", pid=".$process_id." with ".$stmt." and ".print_r($res, true));

			if ($lockValue > 0 && !empty($process_id) && is_numeric($process_id)) {

				// check if process is still running or if this is an invalid lock
				$psinfo = self::getProcessInfo($process_id);

				// TODO: unix, windows, ...
				$log->debug(array("psinfo", $psinfo));

				if (!empty($psinfo)) {
					// override existing lock
					$acquireLock = false;
					$log->debug("overriding existing lock ".$psinfo);
				}
			}

			// worst case: a background process is started, but the queue already
			// empty at this point (very fast indexer)
			if ($acquireLock) {
				// acquire lock
				$log->debug("FulltextIndex::triggerUpdate acquire lock");

				// delete (postgresql) / use INSERT instead of REPLACE below

					$sql = "DELETE FROM ".APP_TABLE_PREFIX."fulltext_locks WHERE ftl_name='";
					$sql .= self::LOCK_NAME_FULLTEXT_INDEX."'";
					$db->query($sql);

				$invalidProcessId = -2;
				$stmt  = "INSERT INTO ".APP_TABLE_PREFIX."fulltext_locks (ftl_name,ftl_value,ftl_pid) ";
				$stmt .= " VALUES ('".self::LOCK_NAME_FULLTEXT_INDEX."', 1, $invalidProcessId) ";

				$ok = true;
				$db->query($stmt);
				// If all succeed, commit the transaction and all changes
			    // are committed at once.
			    $db->commit();

			} else {
				$ok = false;
				$log->debug("FulltextIndex::triggerUpdate lock already taken by another process");
				$db->rollBack();
			}
		} catch(Exception $ex) {
			$db->rollBack();
			$log->err($ex);
			$ok = false;
		}

			if (! $ok) {
				// setting lock failed because another process was faster
				//Logger::debug("FulltextQueue::triggerUpdate - lock value has been taken");
				$log->debug("FulltextQueue::triggerUpdate - lock value has been taken");

			} else {

				// create new background update process
				//Logger::debug("FulltextQueue::triggerUpdate create new background process!");
				$log->debug("FulltextQueue::triggerUpdate create new background process!");
				self::createUpdateProcess();
			}
	}

	/**
	 * Inserts all cached document PIDs into the queue and triggers
	 * the index update process.
	 *
	 * @return unknown
	 */
	public function commit()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		//Logger::debug("FulltextQueue::commit() commit results to database");
		$log->debug("FulltextQueue::commit() commit results to database");

		if (!$this->pids || count($this->pids) == 0) {
			$log->debug("FulltextQueue::commit() Nothing found to commit (pidcount=0)");
			return;
		}
		//Logger::debug(Logger::str_r($this->pids));
		foreach ($this->pids as $pid => $action) {
			//Logger::debug("FulltextQueue::commit() queing ". Misc::escapeString($pid).", ".Misc::escapeString($action));
      if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {

        $db->beginTransaction();

        $sql = "DELETE FROM ".APP_TABLE_PREFIX."fulltext_queue WHERE ftq_pid=";
        $sql .= $db->quote($pid)." AND ftq_op = ".$db->quote($action);
        $db->query($sql);

        $stmt = "INSERT INTO ".APP_TABLE_PREFIX."fulltext_queue (ftq_pid,ftq_op) VALUES (".
        $db->quote($pid).", ".$db->quote($action).")";
      } else {
        $stmt = "REPLACE INTO ".APP_TABLE_PREFIX."fulltext_queue (ftq_pid,ftq_op) VALUES (".
        $db->quote($pid).", ".$db->quote($action).")";
      }
			try {
				$db->query($stmt);
        if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
				  $db->commit();
        }
			}
			catch(Exception $ex) {
        if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
				  $db->rollBack();
        }
				$log->err($ex);
				return false;
			}
			unset($this->pids[$pid]);
		}

		// reset cached pids
		$this->pids = array();
		$this->triggerUpdate();
		return true;
	}


	/**
	 * Cleans up the queue and removes redundant objects (I,I / D,D / D,I / I,D).
	 * Not implemented yet.
	 *
	 */
	function cleanup()
	{
		return true;
	}

	/**
	 * Removes front element from queue in an atomar operation. The transaction
	 * might be an overkill, but who knows if the queue will be used for
	 * other operations at a later time (e.g. multiple indexer processes...)
	 *
	 * @return row (pid, action) of front pid
	 * @return null, if queue is empty or if there is an error
	 *
	 */
	public function pop()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		// start transaction
		//$GLOBALS['db_api']->dbh->autoCommit(false);

		// fetch first row
		$stmt  = "SELECT * FROM ".APP_TABLE_PREFIX."fulltext_queue ";
		$stmt .= "ORDER BY ftq_key ASC "; //maybe this needs to be commented out like RP did because of hte below? doubt it surely.. CK
		$stmt = $db->limit($stmt, 1, 0);

		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return null;
		}

		if (count($res) == 0) {
			$log->debug("FulltextQueue::pop() Queue is empty.");
			return null;
		}

		// delete row
		$stmt =  "DELETE FROM ".APP_TABLE_PREFIX."fulltext_queue ";
		$stmt .= "WHERE ftq_key=".$db->quote($res['ftq_key'], 'INTEGER');

		try {
			$db->query($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return null;
		}

		$log->debug("FulltextQueue::pop() success! ".Logger::str_r($res));
		return $res;
	}

	public function size()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt  = "SELECT count(*) FROM ".APP_TABLE_PREFIX."fulltext_queue ";

		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return 0;
		}
		return $res;
	}

	public function popChunk($singleColumns)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		global $bench;

		$pids = array();

		// fetch first row
		if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { //pgsql
			$stmt  = "SELECT ftq_key, sk.rek_pid, ( '\"' ";
		} else {
			$stmt  = "SELECT ftq_key, sk.rek_pid, CONCAT('\"', CONCAT_WS('\",\"' ";
		}

		foreach ($singleColumns as $column) {


			if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { //pgsql
				if($column['type'] == FulltextIndex::FIELD_TYPE_DATE ) {
					$stmt .= " || IFNULL(DATE_FORMAT(sk.".$column['name'] .",'%Y-%m-%dT%H:%i:%sZ'),'') || '\",\"' ";
					// If a date, also send the year to solr right next to its full value for faceting etc
					$stmt .= " || IFNULL(DATE_FORMAT(sk.".$column['name'] .",'%Y'),'') || '\",\"' ";
				} else {
					$stmt .= " || IFNULL(REPLACE(TEXT(sk.".$column['name'] ."), TEXT '\"', TEXT '\"\"'),'') || '\",\"' ";
				}

			} else {
				if($column['type'] == FulltextIndex::FIELD_TYPE_DATE ) {
					$stmt .= ",IFNULL(DATE_FORMAT(sk.".$column['name'] .",'%Y-%m-%dT%H:%i:%sZ'),'') ";
					// If a date, also send the year to solr right next to its full value for faceting etc
					$stmt .= ",IFNULL(DATE_FORMAT(sk.".$column['name'] .",'%Y'),'') ";
				} else {
					$stmt .= ",IFNULL(REPLACE(sk.".$column['name'] .",'\"','\"\"'),'') ";
				}
			}
		}
		if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { //pgsql
			$stmt = rtrim($stmt, " || '\",\"' ");
			$stmt = str_replace("SELECT ftq_key, sk.rek_pid, ( ,", "SELECT ftq_key, sk.rek_pid, ( ", $stmt);
		} else {
			$stmt .= ")";
		}


		// MT 20100317 - modified order by clause from pid ASC to key DESC so that we have a last-in-first-out order on the queue
		$stmt .= ") as row, sk.* FROM ".APP_TABLE_PREFIX."fulltext_queue
			             INNER JOIN ".APP_TABLE_PREFIX."record_search_key as sk ON rek_pid = ftq_pid
		             WHERE ftq_op = '".FulltextQueue::ACTION_INSERT."'
		             ORDER BY ftq_key DESC
		             LIMIT ".APP_SOLR_COMMIT_LIMIT;
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}

		if (count($res) == 0) {
            //Sometimes the queue gets deleted pids in it as inserts so we'll delete them out
            if (APP_SOLR_COMMIT_LIMIT > 0) {
                $stmt = "DELETE ".APP_TABLE_PREFIX."fulltext_queue
                        FROM ".APP_TABLE_PREFIX."fulltext_queue
                        LEFT JOIN ".APP_TABLE_PREFIX."record_search_key ON rek_pid = ftq_pid
                        WHERE ftq_op = '".FulltextQueue::ACTION_INSERT."'
                        AND rek_pid IS NULL";
                try {
                    $db->query($stmt);
                }
                catch(Exception $ex) {
                    $log->err($ex);
                }
            }

			$log->debug("FulltextQueue::pop() Queue is empty.");
			return false;
		}
    // get the 1-1 core table lookups
    Record::getExtendedPidInfo($res);
    // get the era/herdc extra info
    if (APP_MY_RESEARCH_MODULE == 'ON') {
      $res = Record::getResearchDetailsbyPIDS($res, false);
    }
    // get the internal notes
    InternalNotes::readNotes($res);
//    Record::identifyThumbnails($res);


    foreach ( $res as $rkey => $row ) {
      $pids[] = $row['rek_pid'];
			$keys[] = $row['ftq_key'];
      // Add a closing quote to the citation column
      $res[$rkey]['row'] .= '"';
      // Add the lookups
      foreach ($singleColumns as $column) {
        if ($column['type'] == FulltextIndex::FIELD_TYPE_INT && !empty($column['sek_lookup_function']) ) {
          if( !empty($res[$rkey][$column['name']]) ) {
            $res[$rkey]['row'] .= ',"' . $res[$rkey][$column['name'].'_lookup'] .'"';
            // save it again for the exact too (fast because cached)
            $res[$rkey]['row'] .= ',"' . $res[$rkey][$column['name'].'_lookup'] .'"';
          } else {
            $res[$rkey]['row'] .= ',""';
            $res[$rkey]['row'] .= ',""';
          }
        }
      }

      //Also add sherpa romeo colour
      $sherpa_details = SherpaRomeo::getJournalColourFromPid($row['pid']);
      $res[$rkey]['row'] .= ',"' .$sherpa_details['colour'] .'"';

      //Also add the internal notes
      $res[$rkey]['row'] .= ',"' .$row['ain_detail'] .'"';
      // Add the ranked journal and conference codes
      if (array_key_exists('rj_2010_rank', $row)) {
        $res[$rkey]['row'] .= ',"' .$row['rj_2010_rank'] .'"';
      } else {
        $res[$rkey]['row'] .= ',""';
      }
      if (array_key_exists('rj_2010_title', $row)) {
        $res[$rkey]['row'] .= ',"' .$row['rj_2010_title'] .'"';
      } else {
        $res[$rkey]['row'] .= ',""';
      }
      if (array_key_exists('rj_2012_rank', $row)) {
        $res[$rkey]['row'] .= ',"' .$row['rj_2012_rank'] .'"';
      } else {
        $res[$rkey]['row'] .= ',""';
      }
      if (array_key_exists('rj_2012_title', $row)) {
        $res[$rkey]['row'] .= ',"' .$row['rj_2012_title'] .'"';
      } else {
        $res[$rkey]['row'] .= ',""';
      }
      if (array_key_exists('rc_2010_rank', $row)) {
        $res[$rkey]['row'] .= ',"' .$row['rc_2010_rank'] .'"';
      } else {
        $res[$rkey]['row'] .= ',""';
      }
      if (array_key_exists('rc_2010_title', $row)) {
        $res[$rkey]['row'] .= ',"' .$row['rc_2010_title'] .'"';
      } else {
        $res[$rkey]['row'] .= ',""';
      }
      if (array_key_exists('herdc_code_description', $row)) {
        $res[$rkey]['row'] .= ',"' .$row['herdc_code_description'] .'"';
      } else {
        $res[$rkey]['row'] .= ',""';
      }
    }






        //We will test if the amount of cached content is to large for solr to handle if so we will remove content
        //But we will always do at least one else the queue will get stuck
        if (APP_SOLR_INDEX_DATASTREAMS == 'ON') {
            $size = 0;
            foreach($res as $elementKey => $elementValue) {
                if (array_key_exists('rek_pid', $elementValue)) {
                  $size += strlen(serialize(FulltextIndex_Solr_CSV::getCachedContent("'".$elementValue['rek_pid']."'")));
                }
                if (($size/1000000 > APP_SOLR_CSV_MAX_SIZE) && ($elementKey > 0)){
                    unset($res[$elementKey]);
                    unset($keys[$elementKey]);
                }
            }
        }

		// delete chunk from queue
		$stmt =  "DELETE FROM ".APP_TABLE_PREFIX."fulltext_queue ";
		$stmt .= "WHERE ftq_key IN (".Misc::arrayToSQLBindStr($keys).")";

		try {
			$db->query($stmt, $keys);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}

		return $res;
	}

	function popDeleteChunk()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "  SELECT *
		              FROM ".APP_TABLE_PREFIX."fulltext_queue
		              WHERE ftq_op = ".$db->quote(FulltextQueue::ACTION_DELETE);

		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}

		if (count($res) == 0) {
			$log->debug("FulltextQueue::popDeleteChunk() Delete Queue is empty.".print_r($res));
			return false;
		}
		$keys = array();
		$pids = array();
		foreach ( $res as $row ) {
			$keys[] = $row['ftq_key'];
			$pids[] = $row['ftq_pid'];
		}

		// delete chunk from queue
		$stmt =  "DELETE FROM ".APP_TABLE_PREFIX."fulltext_queue ";
		$stmt .= "WHERE ftq_key IN (".Misc::arrayToSQLBindStr($keys).")";
		try {
			$db->query($stmt, $keys);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}

		return $pids;
	}

	/**
	 * Gets a count of the number of times this pid has been queued in the full text queue
	 *
	 * @return int
	 **/
	public static function getDetailsForPid($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		$prefix = APP_TABLE_PREFIX;

		$q = "SELECT ftq_key AS ftqId, ftq_pid AS pid, ftq_op AS operation FROM {$prefix}fulltext_queue WHERE ftq_pid = ?";
		$details = $db->fetchAll($q, $pid);
		return $details;
	}
}
