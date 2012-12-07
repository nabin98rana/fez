<?php
/**
 * This class implements a persistent queue that tracks objects to be added
 * or removed from the enrichment queue. It is implemented with the singleton
 * pattern and will only commit the results once when the request ends or when
 * an explicit commit is called.
 *
 * <p>Usage:</p>
 * <ul>
 * <li>LinksAmrQueue::get()->add(pid)
 * <li>LinksAmrQueue::get()->remove(pid)
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
include_once(APP_INC_PATH . "class.bgp_links_amr.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.links_amr_service.php");

class LinksAmrQueue extends Queue
{
  protected $_bgp;
  protected $_bgp_details;
  // Max number of pids to send to Links AMR in each service call
  protected $_batch_size;
  // If we've registered the commit shutdown function
  protected $_commit_shutdown_registered;
  // Time to wait (in seconds) between successive Links AMR service calls
  protected $_time_between_calls;

  /**
   * Returns the singleton queue instance.
   *
   * @return instance of class LinksAmrQueue
   */
  public static function get()
  {
    $log = FezLog::get();

    try {
      $instance = Zend_Registry::get('LinksAmrQueue');
    }
    catch(Exception $ex) {
      // Create a new instance
      $instance = new LinksAmrQueue();
      $instance->_dbtp = APP_TABLE_PREFIX . 'linksamr_';
      $instance->_dblp = 'lnl_';
      $instance->_dbqp = 'lnq_';
      $instance->_lock = 'linksamr';
      $instance->_use_locking = TRUE;
      $instance->_batch_size = 50;
      $instance->_time_between_calls = 30;
      $instance->_commit_shutdown_registered = FALSE;
      Zend_Registry::set('LinksAmrQueue', $instance);
    }
    return $instance;
  }

  /**
   * Overridden add function. Checks if in DB queue in addition
   * to default check of whether in in-memory queue. Also registers
   * the commit automatically on shutdown.
   *
   * @param String $pid pid of the object in the fez index
   * @param bool   $in_memory Use in-memory array to store PIDs before committing to DB
   *                          TRUE use in memory array, else FALSE to send to DB immediately
   */
  public function add($pid, $in_memory = TRUE)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $details = Record::getDetailsLite($pid);
    if (! (is_array($details) && count($details) == 1)) {
      // Not found
      return;
    }
    // These are the only types we are interested in discovering using Links AMR
    if (
        ! ($details[0]['rek_display_type'] == XSD_Display::getXDIS_IDByTitleVersion('Journal Article', 'MODS 1.0') ||
           $details[0]['rek_display_type'] == XSD_Display::getXDIS_IDByTitleVersion('Conference Paper', 'MODS 1.0'))
    ) {
      return;
    }
    // Check not in DB queue instead of just this thread
    $sql = "SELECT ".$this->_dbqp."key FROM ".$this->_dbtp."queue ".
           "WHERE ".$this->_dbqp."id=?";
    try {
      $res = $db->fetchOne($sql, $pid, Zend_Db::FETCH_ASSOC);
      if (! $res) {
        parent::add($pid);
        if (! $in_memory) {
          // Don't use the in memory array, commit immediately
          $this->commit();
        }
      }
      // Register the commit shutdown
      if ((! $this->_commit_shutdown_registered) && $in_memory) {
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
   * @param string $id ID of the object to remove from the queue
   */
  public function remove($id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    // Remove from in-memory array
    if (array_key_exists($id, $this->_ids)) {
      $this->_ids[$id] = self::ACTION_REMOVE;
    }
    // Remove from DB
    try {
      $sql = "DELETE FROM ".$this->_dbtp."queue WHERE ".$this->_dbqp."id=?";
      $db->query($sql, $id);
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

    $bgp = new BackgroundProcess_LinksAmr();
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
    if ($this->size() < $this->_batch_size) {
      return;
    }

    // Mark lock with pid
    if ($this->_use_locking) {
      if (!$this->updateLock()) {
        return false;
      }
    }

    $this->_bgp->setStatus("Links AMR queue processing started");
    $started = time();
    $count_docs = 0;
    $pids = array();
    do {
      $empty = FALSE;
      $result = $this->pop();

      if (is_array($result)) {
        extract($result, EXTR_REFS);

        $q_op = $this->_dbqp.'op';
        $q_pid = $this->_dbqp.'id';

        if ($$q_op == self::ACTION_ADD) {
          $pids[] = $$q_pid;
          $count_docs++;
        }

        if ($count_docs % $this->_batch_size == 0) {
          // Batch process pids
          $this->_bgp->setStatus("sending these pids to links amr: \n".print_r($pids, true)."\n");
          $this->sendToLinksAmr($pids);
          $pids = array(); // reset
          // Give Links AMR a bit of a rest before moving on to the next batch.
          // Also helps when the service may be unresponsive.
          $this->_bgp->setStatus("Sleeping for : ".$this->_time_between_calls." seconds as per Links AMR throttling requirements");
          sleep($this->_time_between_calls);
        }
      } else {
        $empty = TRUE;
      }
      unset($result);
      unset($$q_op);
      unset($$q_pid);
    } while (!$empty);

    if (count($pids) > 0) {
      // Process remainder of pids
      $this->sendToLinksAmr($pids);
      $pids = array(); // reset
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
   * Steps through each of the phases for sending this batch of records to the Links AMR service
   *
   * @param array $pids the array of PIDS to send
   */
  function sendToLinksAmr($pids)
  {
    $log = FezLog::get();
    $details = Record::getDetailsLite($pids);
    Record::getSearchKeysByPIDS($details, true);

    $maps = array();
    $doi_prefix = 'http://dx.doi.org/';

    // Build the map array we'll be send to the service
    foreach ($details as $record) {
      if (isset($record['rek_isi_loc'])) {
        // Skip if we already know the UT
        if (isset($this->_bgp)) {
          $this->_bgp->setStatus("Already know the ISI Loc ".$record['rek_isi_loc']." for PID ".$record['rek_pid']);
        }
        continue;
      }
      // Skip if it is in the temporary dupes collection (will be deleted deduped manually later)
      if (defined('APP_TEMPORARY_DUPLICATES_COLLECTION') && APP_TEMPORARY_DUPLICATES_COLLECTION != '') {
        $collections = str_replace(':', '\:', APP_TEMPORARY_DUPLICATES_COLLECTION);
        $isMemberOf = Record::getSearchKeyIndexValue($record['rek_pid'], "isMemberOf", false);
        if (in_array(APP_TEMPORARY_DUPLICATES_COLLECTION, $isMemberOf)) { // if not wok updated via a rid download don't put it into the wos collection
          if (isset($this->_bgp)) {
            $this->_bgp->setStatus("PID ".$record['rek_pid']." is in the Temporary Duplicates collection so ignoring it for Links AMR processing");
          }
          continue;
        }
      }

        // Data elements for identifying articles
      $map = array(
        'pid' => null,
        'doi' => null,
        'ut' => null,
        'issn' => null,
        'atitle' => null,
        'stitle' => null,
        'vol' => null,
        'issue' => null,
        'year' => null,
        'spage' => null,
        'first_author' => null,
        'an' => null
      );

      $map['pid'] = $record['rek_pid'];
      if ($record['rek_link'] && is_array($record['rek_link'])) {
        foreach ($record['rek_link'] as $link) {
          if (stripos($link, $doi_prefix) !== FALSE) {
            // Send record twice since we have an identifier
            // A match using this method takes precedence over a
            // bib data match
            $doi = str_replace($doi_prefix, '', $link);
            $imap = array();
            $imap['pid'] = $record['rek_pid'].':identifier';
            $imap['doi'] = $doi;
            $maps[] = $imap;
          }
        }
      }

      // Remove subtitle from journal title if one exists
      $title = $record['rek_title'];
      if (strpos($title, '-') !== FALSE) {
        $title = explode('-', $title);
        $title = trim($title[0]);
      }
      $map['atitle'] = $title;
      if (isset($record['rek_issn'])) {
        $map['issn'] = $record['rek_issn'];
      } else {
        $map['issn'] = '';
      }

      $map['year'] = date('Y', strtotime($record['rek_date']));
      // Don't send journal title if you have an ISSN // Actually this is a really bad idea to not send journal title because TR need it
//      if (!empty($record['rek_issn'])) {
//        $map['stitle'] = null;
//      } else {
        $map['stitle'] = $record['rek_journal_name'];
//      }

      if (isset($record['rek_volume_number']) && is_numeric($record['rek_volume_number'])) {
        $map['vol'] = $record['rek_volume_number'];
      } else {
        $map['vol'] = null;
      }
      if (isset($record['rek_issue_number']) && is_numeric($record['rek_issue_number'])) {
        $map['issue'] = $record['rek_issue_number'];
      } else {
        $map['issue'] = null;
      }
      if (isset($record['rek_start_page']) && is_numeric($record['rek_start_page'])) {
        $map['spage'] = $record['rek_start_page'];
      } else {
        $map['spage'] = null;
      }

      $map['an'] = null; // We don't store this yet
      // Only the first author
      if ($record['rek_author'] && is_array($record['rek_author'])) {
        $map['first_author'] = $record['rek_author'][0];
      }

      // Check we have the minimum required data and bib data fits one of the combinations
      // required for unique identification
      if (
          ($map['stitle'] && $map['vol'] && $map['issue'] && $map['spage']) ||
          ($map['stitle'] && $map['vol'] && $map['issue'] && $map['an']) ||
          ($map['first_author'] && $map['issn'] && $map['vol'] && $map['issue'] && $map['spage']) ||
          ($map['first_author'] && $map['issn'] && $map['vol'] && $map['issue'] && $map['an'])
      ) {
        $maps[] = $map;
      } else {
          if (isset($this->_bgp)) {
            $this->_bgp->setStatus("Not enough bib data to do a safe match for PID ".$record['rek_pid'].": \n".print_r($map, true));
          }
      }
    }

    if (count($maps) == 0) {
      // Nothing to send
      return;
    }

    // Do the upload to Links AMR
    try {
      $response = LinksAmrService::retrieve($maps);
    } catch(Exception $ex) {
      $response = FALSE;
//      print_r($ex->getMessage()); exit;
      $log->err($ex);
    }

    // Unresponsive service, re-queue these PIDs once we've finished processing the queue
    if (!$response) {
      // Add/remove pids from the retry queue
      if (isset($this->_bgp)) {
        $this->_bgp->setStatus("Unresponsive service so adding ".count($pids)." back on the queue");
      }
      foreach ($pids as $pid) {
        $this->add($pid, FALSE);
      }
      return;
    }

    $xpath = new DOMXPath($response);
    $xpath->registerNamespace('lamr', 'http://www.isinet.com/xrpc41');
    $query = "/lamr:response/lamr:fn[@name='LinksAMR.retrieve'][@rc='OK']/lamr:map/lamr:map";
    $node_list = $xpath->query($query);

    $pid_updates = array();
    if (!is_null($node_list)) {
      foreach ($node_list as $element) {
        $pid = $element->getAttribute('name');
        $_query = $query . "[@name='$pid']/lamr:map[@name='".LinksAmrService::COLLECTION."']/lamr:val[@name='ut']";
        $_node_list = $xpath->query($_query);
        if (!is_null($_node_list)) {
          if ($_node_list->length > 0) {
            $ut = $_node_list->item(0)->nodeValue;
            if (strpos($pid, ':identifier') !== FALSE) {
              $pid = str_replace(':identifier', '', $pid);
              // UTs found using identifiers take precedence
              $pid_updates[$pid] = $ut;
            } else if (! array_key_exists($pid, $pid_updates)) {
              $pid_updates[$pid] = $ut;
            }
          }
        }
      }
    }
    foreach ($pid_updates as $pid => $ut) {

      //First check that we don't already have that ISI Loc on another pid, if so then email the helpdesk system and abort for this pid
      $filter = array();

      // Get records ..
      $filter["searchKey".Search_Key::getID("Object Type")] = 3;
      if (defined('APP_TEMPORARY_DUPLICATES_COLLECTION') && APP_TEMPORARY_DUPLICATES_COLLECTION != '') {
        $collections = str_replace(':', '\:', APP_TEMPORARY_DUPLICATES_COLLECTION);
        $filter["manualFilter"] = " ismemberof_mt:(".$collections.") AND isi_loc_t:(".$ut.") AND ";
      } else {
        $filter["manualFilter"] = " isi_loc_t:(".$ut.") AND ";
      }

      $listing = Record::getListing(array(), array(9,10), 0, 50, 'Created Date', false, false, $filter);
      // If found some records, then send this in an email to the helpdesk system
      if ($listing['info']['total_rows'] != 0) {
        if (APP_EVENTUM_SEND_EMAILS == 'ON') {
          $to = APP_EVENTUM_NEW_JOB_EMAIL_ADDRESS;

          $tplEmail = new Template_API();
          $tplEmail->setTemplate('workflow/emails/links_amr_dupe_ut.tpl.txt');
          $tplEmail->assign('list', $listing['list']);
          $email_txt = $tplEmail->getTemplateContents();

          $mail = new Mail_API;
          $mail->setTextBody(stripslashes($email_txt));
          $date = Record::getSearchKeyIndexValue($pid, "Date");
          $year = date('Y', strtotime($date));
          $subject = '['.APP_NAME.'] - Links AMR found ISI Loc '.$ut.' for '.$pid.' ('.$year.') that already is set to other pid(s)';
          $from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
          $mail->send($from, $to, $subject, false);
        }
      } else {
        // Update record with new UT
        if ($ut != '000084278100002') { // this UT is a known bug in Links AMR where it's "Untitled" so links amr often returns it as a match when it's really not
          $record = new RecordObject($pid);
          $search_keys = array("ISI Loc");
          $values = array($ut);
          if (isset($this->_bgp)) {
            $this->_bgp->setStatus("Adding Links AMR found UT Loc ".$ut." to PID ".$pid);
          }
          $record->addSearchKeyValueList(
            $search_keys, $values, true
          );
        }
      }
    }
  }
}
