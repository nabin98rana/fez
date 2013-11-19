<?php
/**
 * This class is used for persistent queues that tracks objects to be added
 * or removed from the enrichment queue. It is implemented with the singleton
 * pattern and will only commit the results when an explicit commit is called.
 *
 * Subclasses exist to implement specific queuing requirements.
 *
 * This class was derived from the FulltextQueue class.
 *
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 * @version 1.0, February 2011
 */

abstract class Queue
{
  const ACTION_ADD = 'A';
  const ACTION_REMOVE = 'R';

  // in-memory array
  protected $_ids;
  // Database table prefix for the locks and queue
  protected $_dbtp;
  // Locks table column prefix
  protected $_dblp;
  // Queue table column prefix
  protected $_dbqp;
  // The name of the lock
  protected $_lock;
  // If TRUE only allow a single Links AMR queue processor
  protected $_use_locking;

  public function __construct()
  {
    $this->_ids = array();
    $this->_use_locking = TRUE;
  }

  /**
   * Abstracted function to process the queue
   */
  abstract protected function process();

  /**
   * Adds a document to the queue. The results of this functions are committed
   * to the database when a commit() is called, or the thread ends.
   *
   * @param String $id ID of the object to add to the queue
   */
  public function add($id)
  {
    $log = FezLog::get();
    if (!array_key_exists($id,$this->_ids) || !$this->_ids[$id]) {
      $this->_ids[$id] = self::ACTION_ADD;
      $log->debug("Added $id to queue");

    }
    // Register the commit shutdown
    if (! $this->_commit_shutdown_registered) {
      register_shutdown_function(array($this,"commit"));
      $this->_commit_shutdown_registered = TRUE;
    }
  }

  /**
   * Removes a document from the queue.
   *
   * @param String $id ID of the object to remove from the queue
   */
  public function remove($id)
  {
    $log = FezLog::get();

    if (array_key_exists($id, $this->_ids)) {
      $this->_ids[$id] = self::ACTION_REMOVE;
      $log->debug("Removed $id from queue");
    }
  }

  /**
   * Attempts to trigger an update by acquiring a lock and starting
   * the update process
   */
  public function triggerUpdate()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (!$this->_use_locking) {
      $log->debug("not using locking - starting background process directly");
      $this->process();
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
     */
    // Start a transaction explicitly.
    $db->beginTransaction();

    $sql = "SELECT ".$this->_dblp."value, ".$this->_dblp."pid FROM ".$this->_dbtp."locks ".
           "WHERE ".$this->_dblp."name=?";

    try {
      $res = $db->fetchRow($sql, $this->_lock, Zend_Db::FETCH_ASSOC);

      $pid = $res[$this->_dblp.'pid'];
      $lock_value = $res[$this->_dblp.'value'];
      $acquire_lock = true;
      $log->debug(
          "Queue::triggerUpdate got lockValue=".$lock_value.", pid=".$pid.
          " with ".$sql." and ".print_r($res, true)
      );

      if ($lock_value > 0 && !empty($pid) && is_numeric($pid)) {
        // check if process is still running or if this is an invalid lock
        $psinfo = $this->getProcessInfo($pid);
        $log->debug(array("psinfo",$psinfo));

        if (!empty($psinfo)) {
          // override existing lock
          $acquire_lock = false;
          $log->debug("overriding existing lock ".$psinfo);
        }
      }

      // worst case: a background process is started, but the queue already
      // empty at this point (very fast indexer)
      if ($acquire_lock) {
        // acquired lock
        $sql = "DELETE FROM ".$this->_dbtp."locks WHERE ".$this->_dblp."name=?";
        $db->query($sql, $this->_lock);

        $invalid_pid = -2;
        $sql = "INSERT INTO ".$this->_dbtp."locks (".$this->_dblp."name,".$this->_dblp."value,".$this->_dblp."pid) ".
               "VALUES (?,?,?)";
        $db->query($sql, array($this->_lock, 1, $invalid_pid));
        // If all succeed, commit the transaction and all changes
        // are committed at once.
        $db->commit();
        $ok = true;
      } else {
        $db->rollBack();
        $ok = false;
      }
    } catch(Exception $ex) {
      $db->rollBack();
      $log->err($ex);
      $ok = false;
    }

    if (! $ok) {
      // setting lock failed because another process was faster
      $log->debug("Queue::triggerUpdate - lock value has been taken");

    } else {
      // create new background update process
      $log->debug("Queue::triggerUpdate - created new background process!");
      $this->process();
    }
  }

  /**
   * Inserts all cached IDs into the queue and triggers
   * the enrichment process.
   *
   * @return mixed
   */
  public function commit()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (!$this->_ids || count($this->_ids) == 0) {
      if ($this->size() == 0) {
        return;
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
    $this->triggerUpdate();
    return true;
  }

  /**
   * Cleans up the queue and removes redundant objects (I,I / D,D / D,I / I,D).
   * Not implemented yet.
   *
   * @return true
   */
  public function cleanup()
  {
    return true;
  }

  /**
   * Removes front element from queue in an atomar operation. The transaction
   * might be an overkill, but who knows if the queue will be used for
   * other operations at a later time (e.g. multiple indexer processes...)
   *
   * @return row (id, action) of front id
   * @return null, if queue is empty or if there is an error
   *
   */
  public function pop()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    // fetch first row
    $sql = "SELECT * FROM ".$this->_dbtp."queue ".
           "ORDER BY ".$this->_dbqp."key ASC";
    $db->limit($sql, 1, 0);

    try {
      $res = $db->fetchRow($sql, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return null;
    }

    if (count($res) == 0) {
      $log->debug("Queue is empty.");
      return null;
    }

    // delete row
    $sql = "DELETE FROM ".$this->_dbtp."queue WHERE ".$this->_dbqp."key=?";
    try {
      $db->query($sql, $res[$this->_dbqp.'key']);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return null;
    }

    $log->debug("Queue pop success!");
    return $res;
  }

  /**
   * Size of the queue
   *
   * @return unknown
   */
  public function size()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $sql  = "SELECT count(*) FROM ".$this->_dbtp."queue ";

    try {
      $res = $db->fetchOne($sql);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return 0;
    }
    return $res;
  }

  /**
   * Releases lock held by this thread.
   *
   */
  protected function releaseLock()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $sql = "DELETE FROM ".$this->_dbtp."locks WHERE ".$this->_dblp."name=?";
    try {
      $db->query($sql, $this->_lock);
    }
    catch(Exception $ex) {
      $log->err($ex);
      $log->err(array("Queue releaseLock failed", $res));
    }
  }

  /**
   * Updates the queue lock to reflect the current process id.
   * The lock can be retaken if the process with this id does
   * not exist anymore.
   *
   */
  protected function updateLock()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $my_process = $this->getProcessInfo();
    $my_pid = $my_process['pid'];

    if (!is_numeric($my_pid)) {
      $my_pid = 'null';
    }

    $db->beginTransaction();

    $stmt = "SELECT ".$this->_dblp."value, ".$this->_dblp."pid FROM ".$this->_dbtp."locks ".
            "WHERE ".$this->_dblp."name=?";

    try {
      $res = $db->fetchRow($stmt, $this->_lock, Zend_Db::FETCH_ASSOC);

      $pid = $res[$this->_dblp.'pid'];
      $lock_value = $res[$this->_dblp.'value'];
      $acquire_lock = true;
      $log->debug(
          "Queue got lockValue=".$lock_value.", pid=".$pid." with ".$stmt." and ".print_r($res, true)
      );

      if ($lock_value != -1 && (!empty($pid)) && is_numeric($pid)) {
        // check if process is still running or if this is an invalid lock
        $psinfo = $this->getProcessInfo($pid);
        $log->debug("checking for lock on  lock ".$pid);
        $log->debug(array("psinfo",$psinfo));
        if (!empty($psinfo)) {
          // override existing lock
          $acquire_lock = false;
          $log->debug("overriding existing lock ".$psinfo);
        }
      }

      // worst case: a background process is started, but the queue already
      // empty at this point
      if ($acquire_lock) {
        $sql =  "UPDATE ".$this->_dbtp."locks SET ".$this->_dblp."pid=? ".
                "WHERE ".$this->_dblp."name=?";
        $db->query($sql, array($my_pid, $this->_lock));
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
   * Get the current process ID
   *
   * @param string $pid (optional) Search for this process ID
   *
   * @return mixed
   */
  private function getProcessInfo($pid='')
  {
    $log = FezLog::get();

    if (empty($pid)) {
      return array(
        'pid' => getmypid(),
      );
    } else {
      exec("ps ".$pid, $output);
      if (count($output) < 2) {
        return false;
      }
      return $output;
    }
  }
}
