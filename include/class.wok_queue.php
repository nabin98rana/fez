<?php
/**
 * This class implements a persistent queue that tracks objects to be added
 * or removed from the WoK queue. It is implemented with the singleton
 * pattern and will only commit the results once when the request ends or when 
 * an explicit commit is called.
 * 
 * <p>Usage:</p>
 * <ul>
 * <li>WokQueue::get()->add(ut)
 * <li>WokAmrQueue::get()->remove(ut)
 * </ul>
 *
 * After commiting the object(s) to the database, this class will trigger
 * a background process to process the queue. It is up to this background
 * process to deal with concurrency issues that come from multiple processes.
 *
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 * @version 1.0, February 2011
 */

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.queue.php");
include_once(APP_INC_PATH . "class.bgp_wok.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.wok_service.php");
include_once(APP_INC_PATH . "class.wos_record.php");

class WokQueue extends Queue
{
  protected $_bgp;
  protected $_bgp_details;
  // Max number of pids to send to WoK in each service call
  protected $_batch_size;
  // If we've registered the commit shutdown function
  protected $_commit_shutdown_registered;
  // Time to wait (in seconds) between successive WoK service calls
  protected $_time_between_calls;
  // Author queue table column prefix
  protected $_dbap;
  
  /**
   * Returns the singleton queue instance.
   *
   * @return instance of class WoK
   */
  public static function get() 
  {
    $log = FezLog::get();
    
    try {
      $instance = Zend_Registry::get('Wok');
    }
    catch(Exception $ex) {
      // Create a new instance
      $instance = new WokQueue();
      $instance->_dbtp = APP_TABLE_PREFIX . 'wok_';
      $instance->_dblp = 'wkl_';
      $instance->_dbqp = 'wkq_';
      $instance->_dbap = 'wka_';
      $instance->_lock = 'wok';
      $instance->_use_locking = TRUE;
      $instance->_batch_size = WOK_BATCH_SIZE;
      $instance->_time_between_calls = WOK_SECONDS_BETWEEN_CALLS;
      $instance->_commit_shutdown_registered = FALSE;
      Zend_Registry::set('Wok', $instance);
    }
    return $instance;
  }

  /**
   * Overridden add function. Checks if in DB queue in addition
   * to default check of whether in in-memory queue. Also registers
   * the commit automatically on shutdown.
   *
   * @param String $ut The UT
   * @param int    $aut_id The author ID if we know one exists on this record     
   */
  public function add($ut, $aut_id = FALSE) 
  {
    $log = FezLog::get();
    $db = DB_API::get();
      
    // Check not in DB queue instead of just this thread
    $sql = "SELECT ".$this->_dbqp."key FROM ".$this->_dbtp."queue ".
           "WHERE ".$this->_dbqp."id=?";    
    try {
      $res = $db->fetchOne($sql, $ut, Zend_Db::FETCH_ASSOC);
      if (! $res) {   
        parent::add($ut);
        if ($aut_id) {
          $sql = "INSERT INTO ".$this->_dbtp."queue_aut (".$this->_dbap."id,".$this->_dbap."aut_id) VALUES (?,?)";
          $db->query($sql, array($ut, $aut_id));
        }
      }
      // Register the commit shutdown
      if (! $this->_commit_shutdown_registered) {
        register_shutdown_function(array($this,"commit"));
        $this->_commit_shutdown_registered = TRUE;
      }
    }
    catch(Exception $ex) {
      $log->err($ex);
      return;
    }
  }
  
  /**
   * Overridden remove function: Removes a document from the queue, 
   * both in memory and in the DB.
   *
   * @param string $ut THe UT to remove from the queue
   */
  public function remove($ut) 
  {
    $log = FezLog::get();
    $db = DB_API::get();

    // Remove from in-memory array
    if (array_key_exists($ut, $this->_ids)) {
      $this->_ids[$ut] = self::ACTION_REMOVE;
    }
    // Remove from DB
    try {
      $sql = "DELETE FROM ".$this->_dbtp."queue WHERE ".$this->_dbqp."id=?";
      $db->query($sql, $ut);
    }
    catch(Exception $ex) {
      ($ex);
      return false;
    }
  }
  
  /**
   * Processes the queue.
   */
  protected function process() 
  {
    $log = FezLog::get();
        
    $bgp = new BackgroundProcess_Wok();
    // TODO: maybe take something other than admin
    $bgp->register(serialize(array()), APP_SYSTEM_USER_ID);
  }
  
  /**
   * Links this instance to a corresponding background process started above
   *
   * @param BackgroundProcess_LinksAmr $bgp
   */
  public function setBGP(&$bgp) 
  {
    $this->_bgp = &$bgp;
  }

   public function commit()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (!$this->_ids || count($this->_ids) == 0) {
      if ($this->size() == 0) {
        return false;
      }
    }

    foreach ($this->_ids as $id => $action) {
      try {
        $db->beginTransaction();
        $sql = "DELETE FROM ".$this->_dbtp."queue WHERE ".$this->_dbqp."id=? ".
               "AND ".$this->_dbqp."op=?";
        $db->query($sql, array($id, $action));
        $sql = "INSERT INTO ".$this->_dbtp."queue (".$this->_dbqp."id,".$this->_dbqp."op) VALUES (?,?)";
        $db->query($sql, array($id, $action));
        $db->commit();
      }
      catch(Exception $ex) {
        $db->rollBack();
        $log->err($ex);
        return false;
      }
      unset($this->_ids[$id]);
    }

    // reset cached object ids
    $this->_ids = array();
//    $this->triggerUpdate(); // now dont want to trigger an update, will run it on cron every minute to triggerupdate and flush what has been added
    return true;
  }


  /**
   * Processes the queue in the background. Retrieves an item using the pop() 
   * function of the queue and calls the index or remove methods.   
   */
  public function bgProcess() 
  {
    $log = FezLog::get();
    
    // Don't process the queue until we have reached the batch size
    // This is so we at least attempt to play nicely with the 
    // Links AMR service.
    // Turn this off now.
/*    if ($this->size() < $this->_batch_size) {
      return;
    } */
    
    // Mark lock with pid
    if ($this->_use_locking) {
      if (!$this->updateLock()) {
        return false;
      }
    }
    
    $this->_bgp->setStatus("WoK queue processing started");
    $started = time();
    $count_docs = 0;
    $uts = array();
    do {
      $empty = FALSE;
      $result = $this->pop();

      if (is_array($result)) {
        extract($result, EXTR_REFS);
        
        $q_op = $this->_dbqp.'op';
        $q_ut = $this->_dbqp.'id';

        if ($$q_op == self::ACTION_ADD) {
          $uts[] = $$q_ut;
          $count_docs++;
        }
        $this->_bgp->setStatus("WoK queue popped ".$q_ut." for operation ".$q_op.". Count is now ".$count_docs);

        if ($count_docs % $this->_batch_size == 0) {
          // Batch process UTs
          $this->_bgp->setStatus("WoK queue sending now because count_docs ".$count_docs." mod ".$this->_batch_size." = 0, with: \n".print_r($uts,true));
          $this->sendToWok($uts);          
          $uts = array(); // reset
          // Sleep before next batch to avoid triggering the service throttling.
          sleep($this->_time_between_calls);
        }
      } else {
        $empty = TRUE;
      }
      unset($result);
      unset($$q_op);
      unset($$q_ut);
    } while (!$empty);
    
    if (count($uts) > 0) {
      // Process remainder of UTs
      $this->_bgp->setStatus("WoK queue sending remainder with: \n".print_r($uts,true));
      $this->sendToWok($uts);
      $uts = array(); // reset
      sleep($this->_time_between_calls); // same as above
    }
    
    if ($this->_bgp) {
      $plural = $count_docs > 1 ? 's' : '';
      $this->_bgp->setStatus(
          "Processed $count_docs record$plural in ". Date_API::getFormattedDateDiff(time(), $started)
      );
    }
    if ($this->_use_locking) {
      $this->releaseLock();
    }    
    return $count_docs;
  }

  /**
   * Send the list of UTs to the WoK service
   *
   * @param array $uts the array of UTs to send
   */
   function sendToWok($uts)
  {
    $log = FezLog::get();
    // Find out which already exist in the repository. For these we'll be adding
    // additional bib data
    $existing_uts = array();
    foreach ($uts as $ut) {
      $pid = Record::getPIDByIsiLoc($ut);
      if ($pid) {
        $existing_uts[$ut] = $pid;
      }
    }
    // Update/create new records using WoK data
    $processed = array();
    $wok_ws = new WokService(FALSE);
    if ($wok_ws->ready === TRUE) {
      $this->_bgp->setStatus("WoK queue sendToWok searching for: \n".print_r($uts,true));
      $result = $wok_ws->retrieveById($uts);
      if ($result) {
        $doc = new DOMDocument();
        $doc->loadXML($result);
        $this->_bgp->setStatus("WoK response is: \n".$result."\n");
        $recs = $doc->getElementsByTagName("REC");
        foreach ($recs as $rec_elem) {
          $rec = new WosRecItem($rec_elem);
          if (array_key_exists($rec->ut, $existing_uts)) {
            if ($rec->update()) {
              $this->_bgp->setStatus('Updated existing PID: '.$existing_uts[$rec->ut]." for UT: ".$rec->ut);
              $processed[$rec->ut] = $existing_uts[$rec->ut];
            }
          } else {
            $pid = $rec->save();
            if ($pid) {
              $this->_bgp->setStatus('Created new PID: '.$pid." for UT: ".$rec->ut);
              $processed[$rec->ut] = $pid;
            }
          }
        }
      }
    } else {
        $this->_bgp->setStatus('Aborted because WoKService not ready');
        $log->err("Aborted because WoKService not ready");
    }
    // Match authors where we know the aut_id
    foreach ($processed as $ut => $pid) {
      $aut_ids = $this->getAutIds($ut);
      if ($aut_ids) {
        $this->_bgp->setStatus('Matched authors on PID: '.$pid);
        $record = new RecordObject($pid);
        foreach ($aut_ids as $author_id) {
          $record->matchAuthor($author_id, TRUE, TRUE); // TODO: enable this when required
        }
        $record->setIndexMatchingFields();
      }
    }
  }
  
  /**
   * Gets the author ids for this UT 
   *
   * @return row (id, action) of front id
   * @return null, if queue is empty or if there is an error
   *
   */
  private function getAutIds($ut) 
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $aut_ids = array();    
    $sql = "SELECT ".$this->_dbap."aut_id FROM ".$this->_dbtp."queue_aut WHERE ".$this->_dbap."id=?";
    
    try {
      $res = $db->fetchAll($sql, $ut, Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return null;
    }

    if (count($res) == 0) {
      return null;
    }
    
    foreach ($res as $r) {
      $aut_ids[] = $r[$this->_dbap.'aut_id'];
    }
      
    // Delete rows
    $sql = "DELETE FROM ".$this->_dbtp."queue_aut WHERE ".$this->_dbap."id=?"; 
    try {
      $db->query($sql, $ut);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return null;
    }
    
    return $aut_ids;
  }
}
