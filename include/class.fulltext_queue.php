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

	include_once(APP_PATH . "config.inc.php");			
	include_once(APP_INC_PATH . "class.bgp_fulltext_index.php");
	include_once(APP_INC_PATH . "class.logger.php");
	
	
	class FulltextQueue {
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
		private function __construct($indexDirectory) {
			$this->pids = array();
		}		
		
		/**
		 * Returns the singleton queue instance. 
		 *
		 * @return instance of class FulltextQueue
		 */
		public static function singleton() {	
				
			if (!is_object(self::$instance)) {				
				self::$instance = new FulltextQueue($indexPath);
				
				// keep reference to database handler - this is needed 
				// for destruction time!
				self::$instance->db_api = $GLOBALS["db_api"];
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
		public function add($pid) {			
			if (!$this->pids[$pid]) {
				$this->pids[$pid] = FulltextQueue::ACTION_INSERT;
				Logger::debug("FulltextQueue::add($pid)");
			}
		}
		
		/**
		 * @see description for add()
		 */
		public function remove($pid) {								
			if (!$this->pids[$pid]) {
				$this->pids[$pid] = FulltextQueue::ACTION_DELETE;
			}
		}

		/**
		 * Starts the background process which will process the queue.
		 *
		 */
		private static function createUpdateProcess() {			
			Logger::debug("FulltextQueue::createUpdateProcess");
			$bgp = new BackgroundProcess_Fulltext_Index();        		
			//$bgp->register(serialize(compact('pid','regen')), Auth::getUserID());
			$bgp->register(serialize(array()), APP_SYSTEM_USER_ID); // todo: maybe take something other than admin
			//Logger::debug("FulltextQueue::createUpdateProcess bgp registered");
		}
			
		
		/*
		public function removeLock() {
			$GLOBALS["db_api"]->dbh->autoCommit(false);
			$sql = "DELETE FROM ".APP_TABLE_PREFIX."fulltext_locks WHERE lockName='".self::LOCK_FULLTEXT_INDEX."'";
			$GLOBALS["db_api"]->dbh->query($sql);
			
			if ($GLOBALS["db_api"]->dbh->commit() != DB_OK) {
				// setting lock failed because another process was faster
				Logger::error("FulltextQueue::removeLock - could not delete lock");
			} 
		}
		*/
		
        public static function getProcessInfo($pid='') {
			if (empty($pid)) {
			    return array(
			     'pid'   =>  getmypid(),
			    );
			} else {
				exec("ps ".$pid, $output);
				
                if(count($output) < 2){
                    return false;
                }
                
                return $output;
			}
        }
		
		public function	triggerUpdate() {			
			if (!self::USE_LOCKING) {
				Logger::debug("not using logging - starting background process directly");
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
			
			// start transaction
			$GLOBALS["db_api"]->dbh->autoCommit(false);
						
			//Logger::debug("FulltextIndex::triggerUpdate");
			
			$sql = "SELECT ftl_value, ftl_pid FROM ".APP_TABLE_PREFIX."fulltext_locks ".
					"WHERE ftl_name='".self::LOCK_NAME_FULLTEXT_INDEX."'";
			$res = $GLOBALS["db_api"]->dbh->getRow($sql, DB_FETCHMODE_ASSOC);
			$process_id = $res['ftl_pid'];
			$lockValue = $res['ftl_value'];
			$acquireLock = true;
			//Logger::debug("FulltextIndex::triggerUpdate got lockValue=".$lockValue.", pid=".$process_id);
			if ($lockValue > 0 && !empty($process_id) && is_numeric($process_id)) {
			    
				// check if process is still running or if this is an invalid lock
				$psinfo = self::getProcessInfo($process_id);
				
				// TODO: unix, windows, ...
				Logger::debug("psinfo=".Logger::str_r($psinfo));
				
				if (!empty($psinfo)) {
					// override existing lock
					$acquireLock = false;
				}				
			}
			
            // worst case: a background process is started, but the queue already
            // empty at this point (very fast indexer)
            if ($acquireLock) {  
				// acquire lock
				Logger::debug("FulltextIndex::triggerUpdate acquire lock");				
				
				// delete (postgresql) / use INSERT instead of REPLACE below
				/*
				$sql = "DELETE FROM ".APP_TABLE_PREFIX."fulltext_locks WHERE ftl_name='";
				$sql .= self::LOCK_NAME_FULLTEXT_INDEX."'";
				Logger::debug($sql);
				$GLOBALS["db_api"]->dbh->query($sql);
				*/				
				$invalidProcessId = -1;				
				$sql  = "REPLACE INTO ".APP_TABLE_PREFIX."fulltext_locks (ftl_name,ftl_value,ftl_pid) ";
				$sql .= " VALUES ('".self::LOCK_NAME_FULLTEXT_INDEX."', 1, $invalidProcessId) ";				
				//Logger::debug($sql);
				$GLOBALS["db_api"]->dbh->query($sql);
			
				if ($GLOBALS["db_api"]->dbh->commit() != DB_OK) {
					// setting lock failed because another process was faster
					//Logger::debug("FulltextQueue::triggerUpdate - lock value has been taken");
				} else {
					
					// create new background update process
					//Logger::debug("FulltextQueue::triggerUpdate create new background process!");
					
					// $update_bgp = new FulltextIndex_Update()
					self::createUpdateProcess();
				}
			} else {
				Logger::debug("FulltextIndex::triggerUpdate lock already taken by another process");
			}
		}
		
		/**
		 * Inserts all cached document PIDs into the queue and triggers
		 * the index update process.
		 *
		 * @return unknown
		 */
		public function commit() {
			//Logger::debug("FulltextQueue::commit() commit results to database");	
			
			if (!$this->pids || count($this->pids) == 0) {
				Logger::debug("FulltextQueue::commit() Nothing found to commit (pidcount=0)");
				return;
			}
			//Logger::debug(Logger::str_r($this->pids));
			
			$pidList = array();
			$actionList = array();
			
			//$psql = "INSERT INTO ".APP_TABLE_PREFIX."fulltext_queue (ftq_pid,ftq_op) VALUES (?,?)";
			//$pstmt = $GLOBALS["db_api"]->dbh->prepare($psql);
						    
			foreach ($this->pids as $pid => $action) {	
				//Logger::debug("FulltextQueue::commit() queing ". Misc::escapeString($pid).", ".Misc::escapeString($action));
								
				$sql = "REPLACE INTO ".APP_TABLE_PREFIX."fulltext_queue (ftq_pid,ftq_op) VALUES ('".
						Misc::escapeString($pid)."', '".Misc::escapeString($action)."')";
						
				$res = $GLOBALS["db_api"]->dbh->query($sql);				
				if (PEAR::isError($res)) {
					Logger::error("FulltextQueue::commit() cannot insert PID $pid");
            		return false;
        		}
        		unset($this->pids[$pid]);
			}
			
			// reset cached pids
			$this->pids = array();
			
			// run update process
			$this->triggerUpdate();
			
			return true;
		}
		
		/**
		 * Cleans up the queue and removes redundant objects (I,I / D,D / D,I / I,D).
		 * Not implemented yet.
		 * 
		 */
		function cleanup() {
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
		public function pop() {
			// start transaction
			$GLOBALS['db_api']->dbh->autoCommit(false);			
			
			// fetch first row
			$sql  = "SELECT * FROM ".APP_TABLE_PREFIX."fulltext_queue ";
			//$sql .= "ORDER BY ftq_key ASC ";			
			$sql = $GLOBALS['db_api']->dbh->modifyLimitQuery($sql, 0, 1);  
			$sql .= " FOR UPDATE "; 	
			  
			//Logger::debug($sql);
			$result = $GLOBALS['db_api']->dbh->getRow($sql, DB_FETCHMODE_ASSOC);
			if (PEAR::isError($result)) {
				Logger::error("FulltextQueue::pop() can't read queue - ".Logger::str_r($result));
			}
			if (count($result) == 0) {
				//Logger::debug("FulltextQueue::pop() Queue is empty.");
				return null;
			}			
			
			// delete row
			$sql =  "DELETE FROM ".APP_TABLE_PREFIX."fulltext_queue ";
			$sql .= "WHERE ftq_key=".$result['ftq_key'];
			//Logger::debug($sql);
			$GLOBALS['db_api']->dbh->query($sql);
			
			$status = $GLOBALS['db_api']->dbh->commit();
			
			if ($status != DB_OK) {
				Logger::error("FulltextQueue::pop ".Logger::str_r($result));
				return null;
			}
			
			//Logger::debug("FulltextQueue::pop() success! ".Logger::str_r($result));
			return $result;
		}
		
		public function size() {
		    
		    $sql  = "SELECT count(*) FROM ".APP_TABLE_PREFIX."fulltext_queue ";
		    $result = $GLOBALS['db_api']->dbh->getOne($sql);
		    
		    if (PEAR::isError($result)) {
                return 0;
	        }
		    
		    return $result;
		}
		
		public function popChunk($singleColumns) {
		    
		    global $bench;
		    
			$pids = array();
			
			// fetch first row
			$sql  = "SELECT ftq_key, sk.rek_pid, CONCAT_WS('\",\"' ";
			 
			foreach ($singleColumns as $column) {
			    
			    if($column['type'] == FulltextIndex::FIELD_TYPE_DATE ) {
			        $sql .= ",IFNULL(DATE_FORMAT(sk.".$column['name'] .",'%Y-%m-%dT%H:%i:%sZ'),'') ";
			    } else {
                    $sql .= ",IFNULL(REPLACE(sk.".$column['name'] .",'\"','\"\"'),'') ";
			    }
			}
			
			$sql .= ") as row FROM ".APP_TABLE_PREFIX."fulltext_queue 
			             LEFT JOIN ".APP_TABLE_PREFIX."record_search_key as sk ON rek_pid = ftq_pid LIMIT 500";
			
			$result = $GLOBALS['db_api']->dbh->getAll($sql, DB_FETCHMODE_ASSOC);
			if (PEAR::isError($result)) {
				Logger::error("FulltextQueue::pop() can't read queue - ".Logger::str_r($result));
			}
			
			if (count($result) == 0) {
				Logger::debug("FulltextQueue::pop() Queue is empty.");
				return false;
			}
						
			foreach ( $result as $row ) {
			    $keys[] = $row['ftq_key'];
			}
			
			$keys = '"'.implode('","', $keys).'"';
			
			// delete chunk from queue
			$sql =  "DELETE FROM ".APP_TABLE_PREFIX."fulltext_queue ";
			$sql .= "WHERE ftq_key IN (".$keys.")";
			
			//Logger::debug($sql);
			$GLOBALS['db_api']->dbh->query($sql);

			return $result;
		}
		
		
		function __destruct() {	
			// this is needed because globals might already been cleaned-up
			$GLOBALS["db_api"] = self::$instance->db_api;
			
			if (count($this->pids) > 0) {		
				$this->commit();
			}
		}
        
	}
	

?>
