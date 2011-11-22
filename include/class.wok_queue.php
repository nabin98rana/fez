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
include_once(APP_INC_PATH . "class.eventum.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.wok_service.php");
include_once(APP_INC_PATH . "class.wos_record.php");
include_once(APP_INC_PATH . "class.org_structure.php");
include_once(APP_INC_PATH . "class.matching_conferences.php");
include_once(APP_INC_PATH . "class.mail.php");

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
      if (!$res) {   
        parent::add($ut);
        if ($aut_id) {
          if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
            $sql = "INSERT INTO ".$this->_dbtp."queue_aut (".$this->_dbap."id,".$this->_dbap."aut_id) VALUES (?,?)";
          } else {
            $sql = "REPLACE INTO ".$this->_dbtp."queue_aut (".$this->_dbap."id,".$this->_dbap."aut_id) VALUES (?,?)";
          }
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
      if ($this->_bgp) {
        $this->_bgp->setStatus("WoK queue sendToWok searching for: \n".implode(", ",$uts));
      }
//      echo "WoK queue sendToWok searching for: \n".print_r($uts,true);
      $result = $wok_ws->retrieveById($uts);
      if ($result) {
        $doc = new DOMDocument();
        $doc->loadXML($result);
//        if (!empty($this->_bgp)) {
//          $this->_bgp->setStatus("WoK response is: \n".$result."\n");
//        }
        $recs = $doc->getElementsByTagName("REC");
        $wos_collection = trim(APP_WOS_COLLECTIONS, "'");

        foreach ($recs as $rec_elem) {
          $rec = new WosRecItem($rec_elem);
          $aut_ids = $this->getAutIds($rec->ut);
          $rec->author_ids = $aut_ids;
          if (!defined('APP_WOS_COLLECTIONS') || trim(APP_WOS_COLLECTIONS) == "") {
            $rec->collections = array(RID_DL_COLLECTION);
          } else {
              if ($aut_ids) {
                $rec->collections = array(RID_DL_COLLECTION);
              } else {
                $rec->collections = array($wos_collection);
              }
          }

          if (array_key_exists($rec->ut, $existing_uts)) {
              // If this came through with an author id it means it came via RID so put the pid in the RID collection, otherwise put it in the WOS import collection
              $isMemberOf = Record::getSearchKeyIndexValue($existing_uts[$rec->ut], "isMemberOf", false);
              if (!in_array(RID_DL_COLLECTION, $rec->collections)) { // if not wok updated via a rid download don't put it into the wos collection
                $rec->collections = array();
              }
              if (in_array(RID_DL_COLLECTION, $isMemberOf)) { // already in rid collection so don't try and add it again
                $rec->collections = array();
              }
              $updateOK = true;
              // If isn't currently in the WOS or RID collections, skip updating this UT unless the title matches quite well
              if (!in_array(RID_DL_COLLECTION, $isMemberOf) && !in_array($wos_collection, $isMemberOf)) {
                  //check the title is close before updating
                  $title = Record::getSearchKeyIndexValue($existing_uts[$rec->ut], "Title", false);
                  $stripA = RCL::normaliseTitle($title);
                  $stripB = RCL::normaliseTitle($rec->itemTitle);
                  
                  if ($stripA != $stripB) {
                      $updateOK = false;
                  } else {
                      // This record was found outside the wos and rid collections, so don't put it into them - it's good where it is now
                      $rec->collections = array();
                      $this->_bgp->setStatus('FOUND matching UT outside RID/WoS collections matching titles ok so RUNNING updating existing PID: '.$existing_uts[$rec->ut]." for UT: ".
                         $rec->ut." Title match was: (Ours: \n".$stripA." - Theirs: \n".$stripB.")\nOriginal Ours: \n". $title." \nOriginal Theirs: \n".$stripB);
                  }
              }

              if ($updateOK == true) {
                  //check to not copy a pid into the rid collection when a wos or rid download updates it, if one of the author ids already on it is in the same org unit as the new author id
                  if (count($aut_ids) > 0) {
                      $aut_org_ids =  Org_Structure::getAuthorOrgListByAutID($aut_ids[0]);
                      $pid_aut_ids = Record::getSearchKeyIndexValue($existing_uts[$rec->ut], "Author ID", false);
                      if (count($pid_aut_ids) > 0) {
                          $all_org_ids = array();
                          foreach($pid_aut_ids as $pid_aut_id) {
                              $org_ids = Org_Structure::getAuthorOrgListByAutID($pid_aut_id);
                              $all_org_ids = array_merge($org_ids, $all_org_ids);
                          }
                          $all_org_ids = array_unique($all_org_ids);
                          foreach ($aut_org_ids as $aut_org_id) {
                              if (in_array($aut_org_id, $all_org_ids)) { //if one of the pid authors is already in the same org unit as the new author, don't copy into a new collection
                                  if ($this->_bgp) {
                                    $this->_bgp->setStatus('DISCOVERED one of the existing pid '.$existing_uts[$rec->ut]." - Already has Org Unit ".$aut_org_id." set so not going to put into RID collection");
                                  }
                                  $rec->collections = array();
                              }
                          }
                      }
                  }
                  if ($rec->update($existing_uts[$rec->ut])) {
                    if ($this->_bgp) {
                      $this->_bgp->setStatus('Updated existing PID: '.$existing_uts[$rec->ut]." for UT: ".$rec->ut);
                    }
      //              echo 'Updated existing PID: '.$existing_uts[$rec->ut]." for UT: ".$rec->ut;
                    $processed[$rec->ut] = $existing_uts[$rec->ut];
                  }
               } else {
                  if ($this->_bgp) {
                    $this->_bgp->setStatus('Skipped updating existing PID: '.$existing_uts[$rec->ut]." for UT: ".
                       $rec->ut." because title didn't match well enough: (Ours: \n".$stripA."\nTheirs: \n".$stripB.")");
                  }
               }

          } else {
            $pid = $rec->save();
            if ($pid) {
              if ($this->_bgp) {
                $this->_bgp->setStatus('Created new PID: '.$pid." for UT: ".$rec->ut);
              }
              $processed[$rec->ut] = $pid;
            }
          }
        }
      }
    } else {
        if ($this->_bgp) {
          $this->_bgp->setStatus('Aborted because WoKService not ready');
        }
        $log->err("Aborted because WoKService not ready");
    }
    // Match authors where we know the aut_id
    foreach ($processed as $ut => $pid) {
      $aut_ids = $this->getAutIds($ut);
      if ($aut_ids) {
        if ($this->_bgp) {
          $this->_bgp->setStatus('Matched authors on PID: '.$pid);
        }
        $record = new RecordObject($pid);
        $record->setBGP($this->_bgp);
        foreach ($aut_ids as $author_id) {
          $record->matchAuthor($author_id, TRUE, TRUE); // TODO: enable this when required
            // If this record is in the APP_HERDC_TRIAL_COLLECTION and it has been claimed by a new author,
            // then change the eSpace followup flag to 'followup' and change the email to indicate this
          $isMemberOf = Record::getSearchKeyIndexValue($pid, "isMemberOf", false);
          $herdc_trial_collection = trim(APP_HERDC_TRIAL_COLLECTION, "'");
          if (in_array($herdc_trial_collection, $isMemberOf)) {
            $search_keys = array("Follow up Flags");
            $values = array(Controlled_Vocab::getID("Follow-up"));
            $record->addSearchKeyValueList($search_keys, $values, true);
            $autDetails = Author::getDetails($author_id);
            $subject = "ResearcherID Completed HERDC author change :: ".$pid." :: ".$autDetails['aut_org_username'];
            $body = "Automatically assigned this pid ".$pid." to the HERDC TRIAL COLLECTION ".APP_HERDC_TRIAL_COLLECTION." for RID download of author ".
                $autDetails['aut_display_name']." with username ".$autDetails['aut_org_username'];
            $userEmail = "";
            Eventum::lodgeJob($subject, $body, "");
          }

        }
        $record->setIndexMatchingFields();
      }
      $this->deleteAutIds($ut);
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

    return $aut_ids;
  }


  private function deleteAutIds($ut) {

    $log = FezLog::get();
    $db = DB_API::get();


    // Delete rows
    $sql = "DELETE FROM ".$this->_dbtp."queue_aut WHERE ".$this->_dbap."id=?";
    try {
      $db->query($sql, $ut);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return null;
    }
    return true;
  }

}
