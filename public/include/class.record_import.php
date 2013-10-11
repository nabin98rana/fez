<?php

/**
 * Base class inherited by all classses
 * representing data imported from external
 * sources to be processed.
 * @author Chris Maj <c.maj@library.uq.edu.au>
 * @since November 2012
 *
 */
abstract class RecordImport
{
    /**
     * Fields pertaining to the record
     */
    protected $_importAPI;
    protected $_collections=array();
    protected $_abstract;
    protected $_isiLoc = null;
    protected $_pubmedId = null;
    protected $_scopusId = null;
    protected $_embaseId = null;
    protected $_wokId = null;
    protected $_wokCitationCount = null;
    protected $_scopusCitationCount = null;
    protected $_title = null;
    protected $_journalTitle = null;
    protected $_journalTitleAbbreviation = null;
    protected $_totalPages = null;
    protected $_startPage = null;
    protected $_endPage = null;
    protected $_issueDate = null;
    protected $_issueNumber = null;
    protected $_issueVolume = null;
    protected $_wokDocTypeCode = null;
    public   $_scopusDocType = null;
    public   $_scopusSrcType = null;
    public   $_scopusDocTypeCode = null;
    protected $_pubmedDocTypeCode = null;
    protected $_embaseDocTypeCode = null;
    protected $_scopusAggregationType = null;
    //protected $_docTypeCode = null;
    protected $_languageCode = null;
    protected $_issn = null;
    protected $_isbn = null;
    protected $_conferenceDates = null;
    protected $_conferenceTitle = null;
    protected $_conferenceProceedingsTitle = null;
    protected $_seriesTitle = null;
    protected $_confenceLocationCity = null;
    protected $_confenceLocationState = null;
    protected $_authors = array();
    protected $_author_ids = array();
    protected $_keywords = array();
    protected $_subjects = array();
    protected $_loaded = FALSE;
    protected $_publisher = null;
    protected $_doi = null;
    protected $_xdisId = null;
    protected $_xdisTitle = null;
    protected $_xdisSubtype = null;

    //Levenstein distance fuzzy matching vars:
    protected $percentageMatch = null;
    protected $downloadedTitle = null;
    protected $localTitle = null;


    protected $fuzzySearchStatusMessages = array(
//      1 => 'ST10 - Matched on fuzzy title, DOI, start page, end page, issue, volume. ID in the downloaded record was %s. ID in the local record was null.',
//      101 => 'ST11 - Matched on fuzzy title, DOI, start page, end page, issue, volume. ID in the downloaded record was %s. ID in the local record was %s.',
//      2 => 'ST12 - Matched on fuzzy title, DOI, start page, end page, volume and issue. ID in the downloaded record was %s. ID in the local record was null',
//      102 => 'ST13 - Matched on fuzzy title, DOI, start page, end page, volume and issue. ID in the downloaded record was %s. ID in the local record was %s',
//      3 => 'ST14 - Matched on fuzzy title, start page, end page, volume and issue. ID in the downloaded record was %s. ID in the local record was null',
//      103 => 'ST15 - Matched on fuzzy title, start page, end page, volume and issue. ID in the downloaded record was %s. ID in the local record was %s',
      1 => 'ST10 - Matched on fuzzy title, start page, end page, volume and issue. ID in the downloaded record was %s. ID in the local record was missing',
      102 => 'ST11 - Matched on fuzzy title, start page, end page, volume and issue. ID in the downloaded record was %s. ID in the local record was %s',

//      4 => 'ST16 - Matched on fuzzy title, DOI, start page and volume. ID in the downloaded record was %s. ID in the local record was null',
//      104 => 'ST17 - Matched on fuzzy title, DOI, start page and volume. ID in the downloaded record was %s. ID in the local record was %s',
//      5 => 'ST18 - Matched on fuzzy title, DOI, start page issue. ID in the downloaded record was %s. ID in the local record was null',
//      105 => 'ST19 - Matched on fuzzy title, DOI, start page issue. ID in the downloaded record was %s. ID in the local record was %s',
//      6 => 'ST20 - Matched on fuzzy title, start page volume and issue. ID in the downloaded record was %s. ID in the local record was null',
//      106 => 'ST21 - Matched on fuzzy title, start page volume and issue. ID in the downloaded record was %s. ID in the local record was %s',
      2 => 'ST12 - Matched on fuzzy title, start page volume and issue. ID in the downloaded record was %s. ID in the local record was missing',
      102 => 'ST13 - Matched on fuzzy title, start page volume and issue. ID in the downloaded record was %s. ID in the local record was %s',

//      7 => 'ST22 - Matched on fuzzy title, DOI and start page. ID in the downloaded record was %s. ID in the local record was null.',
//      107 => 'ST23 - Matched on fuzzy title, DOI and start page. ID in the downloaded record was %s. ID in the local record was %s.',
//      8 => 'ST24 - Matched on fuzzy title, DOI. ID in the downloaded record was %s. ID in the local record was null.',
//      108 => 'ST25 - Matched on fuzzy title, DOI. ID in the downloaded record was %s. ID in the local record was %s.',
//      9 => 'ST26 - Matched on fuzzy title. ID in the downloaded record was %s. ID in the local record was null.',
//      109 => 'ST27 - Matched on fuzzy title. ID in the downloaded record was %s. ID in the local record was %s.',
      9 => 'ST14 - Matched on fuzzy title. ID in the downloaded record was %s. ID in the local record was missing.',
      109 => 'ST15 - Matched on fuzzy title. ID in the downloaded record was %s. ID in the local record was %s.',

//      10 => 'ST28 - Matched on DOI, start page, end page, issue, volume. ID in the downloaded record was %s. ID in the local record was null.',
//      110 => 'ST29 - Matched on DOI, start page, end page, issue, volume. ID in the downloaded record was %s. ID in the local record was %s.',
    );


  /**
     * The PID of the collection to save
     * newly inserted records into.
     * @var string
     */
    protected $_insertCollection = null;

    /**
     * The prefix used to name the primary ID and related fields.
     * @var string
     */
    protected $_primaryIdPrefix = null;

    /**
     * The path to the SQLite DB for logging purposes.
     * Used on when $_inTest is true
     * @var string
     */
    protected $_statsFile = null;
    ///var/www/scopusimptest/scopusDownloaded.s3db

    /**
     * Switch for setting the object to test mode.
     * @var boolean
     */
    protected $_inTest = false;

    /**
     * Switch for setting the object to action mode where it will save as it dedupes, otherwise just returns message and dupe status
     * @var boolean
     */
    protected $_likenAction= false;

    /**
     * Namespaces to use with the XPath object
     * @var array
     */
    protected $_namespaces = array();

    /**
     * An array of doc type codes to exempt from insertion,
     * update or any deduping
     * @var array
     */
    protected $_doctypeExceptions = array();

    /**
     * We will try to do a comparison on all these
     * ids when doing de-duping if they are set.
     * This is set with a default value of '_doi'
     * as a fallback only. DO NOT add/remove
     * comparison id types here! Set them in
     * your child class.
     * @var array
     */
    protected $_comparisonIdTypes = array('_doi');

    /**
     * Load data from an XML document into the object's fields
     * @param string $recordData
     * @param array $nameSpaces
     */
    public abstract function load($recordData, $nameSpaces=null);

  /**
   * Map a log message to a second stage dedupe status code (ie ST10+)
   * @param array $searchData
   */
    public abstract function getFuzzySearchStatus($searchData);

    /**
     * Check to see if a record already resides in a import collection
     * based on a primary id
     */
//    protected abstract function checkImportCollections();

    /**
     * Set the test state of the object.
     * True causes the output of the de-duping logic
     * to be sent to $this->_statsFile and for
     * updating and saving functionality to be disabled.
     * @param boolean $state
     */
    public function setInTest($state)
    {
        $this->_inTest = $state;
        return $this->_inTest;
    }

  /**
   * Set the action state of the object.
   * True causes the de-duping logic
   * to save records, otherwise just returns the history and status code
   * @param boolean $state
   */
  public function setLikenAction($state)
  {
    $this->_likenAction = $state;
    return $this->_likenAction;
  }


  /**
     * Return the values of all fields
     * in the object.
     * @return array
     */
    public function getFields()
    {
        $mirrorMirror = new ReflectionClass($this);
        $fields = $mirrorMirror->getProperties();
        $allFields = array();

        foreach($fields as $fkey => $field)
        {
            $fn = $field->name;
            $allFields[$fn] = $this->$fn;
        }

        return $allFields;
    }

    /**
     * Set a path to the stats file for testing purposes.
     * Use in conjunction with setInTest() method.
     * @param string $statsFile
     */
    public function setStatsFile($statsFile)
    {
        $this->_statsFile = $statsFile;
        return $this->_statsFile;
    }

    /**
     * Common xpath object used by all child classes
     * @param string $rawXML
     * @return DOMXPath
     */
    protected function getXPath($rawXML)
    {
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->loadXML($rawXML);

        $xpath = new DOMXPath($xmlDoc);

        if ($this->_namespaces) {
            foreach ($this->_namespaces as $name => $uri)
            {
                $xpath->registerNamespace($name, $uri);
            }

            $rootNameSpace = $xmlDoc->lookupNamespaceUri($xmlDoc->namespaceURI);
            $xpath->registerNamespace('default', $rootNameSpace);
        }

        return $xpath;
    }





    /**
     * Compare titles longer than 10 chars
     * @param string $authorativePid
     */
    protected function fuzzyTitleMatch($authorativePid)
    {
        //Fuzzy title matching. Title must be at least 10 chars long and
        //have a match of better than 80%
        $titleIsFuzzyMatched = false;

        if($authorativePid && strlen($this->_title) > 10)
        {
            $rec = new Record();
            $title = $rec->getTitleFromIndex($authorativePid);

            $this->percentageMatch = 0;

            $this->downloadedTitle = RCL::normaliseTitle($this->_title);
            $this->localTitle = RCL::normaliseTitle($title);
            similar_text($this->downloadedTitle, $this->localTitle, $this->percentageMatch);

            if($this->percentageMatch > 80)
            {
                $titleIsFuzzyMatched = true;
            }
        }

        return $titleIsFuzzyMatched;
    }

    /**
     * Store stats in SQLite DB when running in test mode.
     * $this->_statsFile should be set to the path of the SQLite DB.
     * @param string $scopusId
     * @param string $operation
     * @param string $docType
     * @param string $agType
     * @return boolean
     */
    protected function inTestSave($contribId, $operation, $docType=null, $agType=null)
    {
        /*
         * CREATE TABLE [records] (
         * [contrib_id] TEXT  PRIMARY KEY NOT NULL,
         * [operation] VARCHAR(8) DEFAULT 'NULL' NULL,
         * [count] INTEGER DEFAULT '0' NOT NULL,
         * [doc_type] VARCHAR(10) DEFAULT 'NULL' NULL,
         * [ag_type] VARCHAR(100) DEFAULT 'NULL' NULL,
         * [title] TEXT DEFAULT 'NULL' NULL
		 * )
         */
        if(!is_file($this->_statsFile))
        {
            return false;
        }
        $db = new PDO('sqlite:'.$this->_statsFile);
        $query = "INSERT OR IGNORE INTO records (contrib_id, operation, doc_type, ag_type) "
        ."VALUES ('" . $contribId . "', '" . $operation . "', '" . $docType . "', '" . $agType . "')";
        $db->query($query);
    }

    public function isLoaded() {
      return $this->_loaded;
    }

    /**
     * Perform de-duping on incoming records
     */
  public function liken()
  {
    //Order of ids in the _comparisonIdTypes matters.
    //Eg for ScopusRecItem _scopusId should be first,
    //for PubmedRecItem _pubmedId should be first, etc.
    $primaryId = $this->_comparisonIdTypes[0];

    $docTypeCode = "_{$this->_primaryIdPrefix}DocTypeCode";
    $aggregationType = "_{$this->_primaryIdPrefix}AggregationType";

    // likely used for testing specifics only
    if (in_array($this->$docTypeCode, $this->_doctypeExceptions)) {
      return false;
    }



    //If the Scopus ID matches something that is already in the Scopus
    //import collection, we need not go any further.
    if ($this->_inTest) {
      if (is_file($this->_statsFile)) {
        $db = new PDO('sqlite:' . $this->_statsFile);

        $query = "SELECT * FROM records WHERE contrib_id = '" . $this->$primaryId . "' LIMIT 1";
        $res = $db->query($query);
        $rows = $res->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($rows)) {
          $query = "UPDATE records SET count = count+1 WHERE contrib_id = '" . $this->$primaryId . "'";
          $db->query($query);

          return;
        }
      }
    }
//TODO: don't restrict restrict search to just the scopus import collection, search the entire espace for pids with that scopus id
//    } else {
//      //$inImportColl = Record::getPIDsByScopusID($this->_scopusId, true);
//      $inImportColl = $this->checkImportCollections();
//      if (!empty($inImportColl)) {
//        return;
//      }
//    }

    //set an idcollection array for pids returned by id type
    $idCollection = array();
    $mirrorMirror = new ReflectionClass($this);
    $associations = array();

    //See what returns a PID
    foreach ($this->_comparisonIdTypes as $id) {
      //Check that a method exists for retrieving
      //a local record by that id type.
      $retrieverName = 'getPIDsBy' . $id;
      $retriever = $mirrorMirror->getMethod($retrieverName);

      if ($retriever) {
        //Run the method and capture the pid(s)
        $pids = $this->$retrieverName();

        $pidCount = count($pids);

        //if there is only one pid returned
        if ($pidCount == 1) {
          $associations[$id]['status'] = 'MATCHED';
          $associations[$id]['matchedPid'] = $pids[0];
          // Add the history message if this flows all the way to ST09
          $histMsg = 'ST09 - Found '.$associations[$id]['matchedPid'].' based on match on '.$id;
        } elseif (!$this->$id) {
          $associations[$id]['status'] = 'EMPTY';
        } elseif ($pidCount > 1) {
          $histMsg = "ST01 - "
            . $this->$primaryId . " matches more than one record ("
            . implode(', ', $pids) . ") based on $retrieverName";


          if (!$this->_likenAction) {
            return array('ST01', $histMsg);
          } elseif (!$this->_inTest) {
            $this->save($histMsg, $this->_insertCollection);
          } else {
            $this->_log->err($histMsg);
            $this->inTestSave($this->$primaryId, 'ST01',
              $this->$docTypeCode, $this->$aggregationType);
          }
          return false;
        } else {
          $associations[$id]['status'] = 'UNMATCHED';
        }
      }
    }

    $authorativePid = false;
    $pidCollection = array();
    $areMatched = array();
    foreach ($associations as $id => $association) {
      if (array_key_exists('matchedPid', $association)) {
        if (!is_null($association['matchedPid'])) {
          $pidCollection[] = $association['matchedPid'];
        }
      }

      if ($association['status'] == 'MATCHED') {
        $areMatched[] = $id;
      }
    }

    //See that different ids return the same PID
    $ctUniq = count(array_unique($pidCollection));

    //If we have a single PID, weed out any remaining fields that did not match and log them.
    //A pid that matched on Scopus ID is considered most reliable, DOI is next most reliable and so on
    //so the order of $this->_comparisonIdTypes matters.
    if ($ctUniq == 1) {
      for ($i = 0; $i < count($this->_comparisonIdTypes); $i++) {
        $cit = $this->_comparisonIdTypes[$i];
        if (in_array($cit, $areMatched)) {
          $idMismatches = $this->getMismatchedFields(array_keys($associations), $associations[$cit]['matchedPid'], array($cit));

          if (!$idMismatches) {
            $authorativePid = $associations[$cit]['matchedPid'];
          } else {
            $histMsg = "ST02 - Mismatch error. Scopus Id "
              . $this->$cit . " matches ".$associations[$cit]['matchedPid']." but the following do not: "
              . var_export($idMismatches, true);


            if (!$this->_likenAction) {
              return array('ST02', $histMsg);
            } elseif (!$this->_inTest) {
              $this->save($histMsg, $this->_insertCollection);
            } else {
              $this->_log->err($histMsg);
              $this->inTestSave($this->$primaryId, 'ST02', $this->$docTypeCode, $this->$aggregationType);
            }
            return false;
          }
          break; //Stop processing any further id types
        }
      }

      //Fuzzy title matching. Title must be at least 10 chars long and
      //have a match of better than 80%

      $titleIsFuzzyMatched = $this->fuzzyTitleMatch($authorativePid);

      if ((($associations['_title']['status'] == 'UNMATCHED') && $titleIsFuzzyMatched)) {
        $associations['_title']['status'] = 'MATCHED';
      }

      //If we have either an exact title match (unlikely) or an acceptable
      //fuzzy title match, proceed to volume and page matching
      if ($associations['_title']['status'] == 'MATCHED') {
        $localStartPage = Record::getSearchKeyIndexValue($authorativePid, 'Start Page', false);
        if (!empty($this->_startPage) && !empty($localStartPage)) {
          if ($this->_startPage == $localStartPage) {
            $associations['_startPage']['status'] = 'MATCHED';
          } else {
            $histMsg = "ST03 - Start page mismatch for '" . $this->_title
              . " - Scopus ID: " . $this->$primaryId
              . "'. Local start page is: " . $localStartPage
              . " . Downloaded start page is: " . $this->_startPage;


            if (!$this->_likenAction) {
              return array('ST03', $histMsg);
            } elseif (!$this->_inTest) {
              $this->save($histMsg, $this->_insertCollection);
            } else {
              $this->_log->err($histMsg);
              $this->inTestSave($this->$primaryId, 'ST03', $this->$docTypeCode, $this->$aggregationType);
            }

            return false;
          }
        }

        $localEndPage = Record::getSearchKeyIndexValue($authorativePid, 'End Page', false);
        if (!empty($this->_endPage) && !empty($localEndPage)) {
          if ($this->_endPage == $localEndPage) {
            $associations['_endPage']['status'] = 'MATCHED';
          } else {
            $histMsg = "ST04 - End page mismatch for '" . $this->_title
              . " - Scopus ID " . $this->$primaryId
              . "'. Local end page is: " . $localEndPage
              . " . Downloaded end page is: " . $this->_endPage;


            if (!$this->_likenAction) {
              return array('ST04', $histMsg);
            } elseif (!$this->_inTest) {
              $this->save($histMsg, $this->_insertCollection);
            } else {
              $this->_log->err($histMsg);
              $this->inTestSave($this->$primaryId, 'ST04', $this->$docTypeCode, $this->$aggregationType);
            }

            return false;
          }
        }

        $localVolume = Record::getSearchKeyIndexValue($authorativePid, 'Volume Number', false);
        if (!empty($this->_issueVolume) && !empty($localVolume)) {
          if ($this->_issueVolume == $localVolume) {
            $associations['_volume']['status'] = 'MATCHED';
          } else {

            $histMsg = "ST05 - Volume mismatch for '" . $this->_title
              . " - Scopus ID " . $this->$primaryId
              . "'. Local end page is: " . $localVolume
              . " . Downloaded end page is: " . $this->_issueVolume;



            if (!$this->_likenAction) {
              return array('ST05', $histMsg);
            } elseif (!$this->_inTest) {
              $this->save($histMsg, $this->_insertCollection);
            } else {
              $this->_log->err($histMsg);
              $this->inTestSave($this->$primaryId, 'ST05', $this->$docTypeCode, $this->$aggregationType);
            }

            return false;
          }
        }
      } elseif ($associations['_title']['status'] != 'UNMATCHED') {
        $associations['_title']['status'] = 'UNCERTAIN';

        $histMsg = "ST06 - Scopus ID: " . $this->$primaryId
          . " Downloaded title: '" . $this->downloadedTitle
          . "' FAILED TO MATCH the local title: '" . $this->localTitle
          . "' with a match of only " . $this->percentageMatch . "%";

        if (!$this->_likenAction) {
          return array('ST06', $histMsg);
        } elseif (!$this->_inTest) {
          $this->save($histMsg, $this->_insertCollection);
        } else {
          $this->_log->err($histMsg);
          $this->inTestSave($this->$primaryId, 'ST06', $this->$docTypeCode, $this->$aggregationType);
        }
      }
    } elseif (empty($pidCollection)) {
      //Begin last ditch attempts to match on fuzzy title, DOI and IVP only
      $fuzzyMatchResult = Record::getPidsByFuzzyTitle($this->getFields());

      if ($fuzzyMatchResult['state']) {
        $fuzzyMatchState = $this->getFuzzySearchStatus($fuzzyMatchResult);

        $stCode = preg_replace("/(^ST\d{2})*./", "$1", $fuzzyMatchState[0]);
        $histMsg = $fuzzyMatchState[0];
        if (!$this->_likenAction) {
          return array($stCode, $histMsg);
        } elseif (!$this->_inTest) {
          $this->save($fuzzyMatchState[0], $this->_insertCollection);
        } else {
          //ST10-2x status
          $this->inTestSave($this->$primaryId, $stCode, $this->$docTypeCode, $this->$aggregationType);
          return $fuzzyMatchState;
        }
        return 'POSSIBLE MATCH';
      }

      $histMsg = "ST07 - No matches, saving a new PID for Scopus ID: "
        . $this->$primaryId . "'" . $this->_title;

      if (!$this->_likenAction) {
        return array('ST07', $histMsg);
      } elseif (!$this->_inTest) {
        $this->save($histMsg, $this->_insertCollection);
      } else {
        $this->inTestSave($this->$primaryId, 'ST07', $this->$docTypeCode, $this->$aggregationType);
      }

      return "SAVE";

    } else {
      $histMsg = "ST08 - Different ids in the same downloaded record are matching up with different pids for Scopus ID: "
        . $this->$primaryId . " '" . $this->_title . "'."
        . var_export($associations, true);



      if (!$this->_likenAction) {
        return array('ST08', $histMsg);
      } elseif (!$this->_inTest) {
        $this->save(null, $this->_insertCollection);
      } else {
        $this->_log->err($histMsg);
        $this->inTestSave($this->$primaryId, 'ST08', $this->$docTypeCode, $this->$aggregationType);
      }

      return false;
    }

    if ($authorativePid) {
      if (!$this->_likenAction) {
        return array('ST09', $histMsg);
      } elseif (!$this->_inTest) {
        //ST09 - updating
        $this->update($authorativePid);
      } else {
        /*file_put_contents($this->_statsFile, "ST09 - Updating: ".$authorativePid.". Scopus ID: "
            . $this->_scopusId . "\n\n", FILE_APPEND);*/
        $this->inTestSave($this->$primaryId, 'ST09', $this->$docTypeCode, $this->$aggregationType);
      }
      return "UPDATE";
    }
  }

    /**
     * Compare values of local fields with the values of fields
     * in a downloaded record and return mismatches.
     * @param array $idTypes
     * @param array $exceptions
     */
    protected function getMismatchedFields($otherIds, $pid, $exceptions=array())
    {
        //We definately don't wanna do title matching here
        if(!in_array('_title', $exceptions))
        {
            $exceptions[] = '_title';
        }

        //Diff otherIdValues keys with exceptions to determine which ones to check
        $idsToProcess = array_diff($otherIds, $exceptions);
        $mismatches = array();

        //Iterate through id types and check against local record for the given pid
        foreach($idsToProcess as $idToProcess)
        {
            $skIndex = strtolower(preg_replace("/[A-Z]/","_$0", ltrim($idToProcess, "_")));

            $localIdValue = Record::getSearchKeyIndexValue($pid, $skIndex , false);
            $dlValue = $this->$idToProcess;

            if(($localIdValue && $dlValue) && ($localIdValue != $dlValue))
            {
                $mismatches[$idToProcess] = array('localValue' => $localIdValue, 'dlValue' => $dlValue);
            }
        }

        //return mismatches if any otherwise return false.
        return (count($mismatches) > 0) ? $mismatches : false;
    }

    /**
     * Fetch an array of pids by Doi
     * @param mixed $id
     * @return array
     */
    protected function getPIDsBy_doi()
    {
        $pids = array();

        if($this->_doi)
        {
            $pidSet = Record::getPIDsByDoi($this->_doi);
        }

        for($i=0;$i<count($pidSet);$i++)
        {
            $pids[] = $pidSet[$i]['rek_doi_pid'];
        }

        return $pids;
    }

  /**
   * Fetch an array of pids by IsiLoc
   * @param mixed $id
   * @return array
   */
  protected function getPIDsBy_isiloc()
  {
    $pids = array();
    if($this->_isiLoc)
    {
      $pidSet = Record::getPIDsByIsiLoc($this->_isiLoc);

    }

    for($i=0;$i<count($pidSet);$i++)
    {
      $pids[] = $pidSet[$i]['rek_isiloc_pid'];
    }
    return $pids;
  }

    /**
    * Fetch an array of pids by ScopusId
    * @param mixed $id
    * @return array
    */
    protected function getPIDsBy_scopusId()
    {
        $pids = array();
        if($this->_scopusId)
        {
            $pidSet = Record::getPIDsByScopusID($this->_scopusId);

        }

        for($i=0;$i<count($pidSet);$i++)
        {
            $pids[] = $pidSet[$i]['rek_scopus_id_pid'];
        }
        return $pids;
    }

    /**
    * Fetch an array of pids by PubmedId
    * @param mixed $id
    * @return array
    */
    protected function getPIDsBy_pubmedId()
    {
        $pids = array();

        if($this->_pubmedId)
        {
            $pidSet = Record::getPIDsByPubmedId($this->_pubmedId);
        }

        for($i=0;$i<count($pidSet);$i++)
        {
        $pids[] = $pidSet[$i]['rek_pubmed_id_pid'];
                }
        return $pids;
    }

    /**
    * Fetch an array of pids by title
    * @param mixed $id
    * @return array
    */
    protected function getPIDsBy_title()
    {
        $pids = array();

        if($this->_title)
        {
            $pidSet = Record::getPIDsByTitle($this->_title);
        }

        for($i=0;$i<count($pidSet);$i++)
        {
        $pids[] = $pidSet[$i]['rek_pid'];
                }
        return $pids;
    }

    /**
    * Fetch an array of pids by WokId
    * @param mixed $id
    * @return array
    */
    protected function getPIDsBy_wokId()
    {
        //woteva

        //return array of pids
    }

    /**
     * Saves record items to Record Search Key
     *
     * @return string $pid
     */
    protected function _saveFedoraBypass($history = null)
    {
        $log = FezLog::get();

        if (!$this->_loaded) {
            $log->err($this->_importAPI.' record must be loaded before saving');
            return FALSE;
        }

        // Instantiate Record Sek class
        $recordSearchKey = new Fez_Record_Searchkey();

        if (empty($history)) {
            // History message
            $history = 'Imported from '.$this->_importAPI;
        }

        // Citation Data
        if (is_numeric($this->_wokCitationCount)) {
            $citationData['thomson'] = $this->_wokCitationCount;
        }
        if (is_numeric($this->_scopusCitationCount)) {
            $citationData['scopus'] = $this->_scopusCitationCount;
        }


        // Search key Data
        $sekData = $this->_getSekData($recordSearchKey);
        $sekData = $recordSearchKey->buildSearchKeyDataByDisplayType($sekData, $this->_xdisId);

        // Save Record
        $result = $recordSearchKey->insertRecord($sekData, $history, $citationData);

        if (!$result) {
            return false;
        }

        //assume solr need updating for new lister permissions
        if (APP_SOLR_INDEXER == "ON") {
            FulltextQueue::singleton()->add($recordSearchKey->getPid());
            FulltextQueue::singleton()->commit();
        }

        return $recordSearchKey->getPid();
    }

    /**
     * Stores to a new record in Fez
     */
    public function save($history = null, $collection=null)
    {
        $pid = null;

        if (APP_FEDORA_BYPASS == 'ON') {

            // save WOS data to Record Search Keys
            $pid = $this->_saveFedoraBypass($history);

        } else {


            $log = FezLog::get();

            if (!$this->_loaded) {
                $log->err($this->_importAPI.' record must be loaded before saving');
                return FALSE;
            }

            if (empty($history)){
                $history = 'Imported from '.$this->_importAPI;
            }
            $mods = array();
            $mods['titleInfo']['title'] = $this->_title;
            if (count($this->_authors) > 0) {
                $mods['name'][0]['id'] = '0';
                $mods['name'][0]['authority'] = APP_ORG_NAME;
                $mods['name'][0]['namePart_personal'] = $this->_authors[0];
                $mods['name'][0]['role']['roleTerm_text'] = 'author';
                if (count($this->_authors) > 1) {
                    for ($i = 1; $i < count($this->_authors); $i++) {
                        $mods['name'][$i]['id'] = '0';
                        $mods['name'][$i]['authority'] = APP_ORG_NAME;
                        $mods['name'][$i]['namePart_personal'] = $this->_authors[$i];
                        $mods['name'][$i]['role']['roleTerm_text'] = 'author';
                    }
                }
            }
            if (count($this->_keywords) > 0) {
                for ($i = 0; $i < count($this->_keywords); $i++) {
                    $mods['subject'][$i]['authority'] = 'keyword';
                    $mods['subject'][$i]['topic'] = $this->_keywords[$i];
                }
            }
            $countKeywords = count($this->_keywords);
            if (count($this->_subjects) > 0) {
              for ($i = 0; $i < count($this->_subjects); $i++) {
                $y = $i + $countKeywords;
                $mods['subject'][$y]['authority'] = 'asrc';
                $mods['subject'][$y]['topic'] =  Controlled_Vocab::getTitle($this->_subjects[$i]);
                $mods['subject'][$y]['id'] =  $this->_subjects[$i];
              }
            }
            $mods['abstract'] = $this->_abstract;
            $mods['identifier_isi_loc'] = $this->_isiLoc;
            $mods['identifier_isbn'] = $this->_isbn;
            $mods['identifier_issn'] = $this->_issn;
            $mods['identifier_doi'] = $this->_doi;
            $mods['identifier_scopus_doc_type'] = $this->_scopusDocTypeCode;
            $mods['identifier_wok_doc_type'] = $this->_wokDocTypeCode;
            $mods['identifier_scopus'] = $this->_scopusId;
            $mods['identifier_pubmed'] = $this->_pubmedId;
            $mods['identifier_embase'] = $this->_embaseId;
            $mods['language'] = $this->_languageCode;
            $mods['genre'] = $this->_xdisTitle;
            $mods['genre_type'] = $this->_xdisSubtype;
            $mods['relatedItem']['part']['detail_issue']['number'] = $this->_issueNumber;
            $mods['relatedItem']['part']['detail_volume']['number'] = $this->_issueVolume;
            $mods['relatedItem']['part']['extent_page']['start'] = $this->_startPage;
            $mods['relatedItem']['part']['extent_page']['end'] = $this->_endPage;
            $mods['relatedItem']['part']['extent_page']['total'] = $this->_totalPages;
            if ($this->_xdisTitle == 'Conference Paper') {
                $mods['originInfo']['dateIssued'] = $this->_issueDate;
                $mods['relatedItem']['titleInfo']['title'] = $this->_conferenceProceedingsTitle;
                $mods['relatedItem']['name'][0]['namePart_type'] = 'conference';
                $mods['relatedItem']['name'][0]['namePart'] = $this->_conferenceTitle;
                //Only save scopus (and wos) journal name (prism:publicationName) if it has an ISSN otherwise its a proceedings, but don't put journal name there if its a cp from a p
                if ($this->_scopusDocTypeCode == 'cp' && $this->_scopusSrcType == 'p') {
                  $mods['relatedItem']['name'][1]['namePart_type'] = 'Series'; //conf paper doctype using capital S Series
                  $mods['relatedItem']['name'][1]['namePart'] = $this->_seriesTitle;
                } elseif ($this->_scopusDocTypeCode == 'cp' && $this->_scopusSrcType == 'k') {
                  $mods['relatedItem']['titleInfo']['subTitle'] =  $this->_seriesTitle;
                  $mods['relatedItem']['name'][1]['namePart_type'] = 'Series'; //conf paper doctype using capital S Series
                  $mods['relatedItem']['name'][1]['namePart'] = $this->_seriesTitle;
                } elseif (strlen($this->_issn) == 9 && ($this->_scopusDocTypeCode == 'cp' && $this->_scopusSrcType != 'p')) {
                  $mods['relatedItem']['titleInfo']['subTitle'] =  $this->_journalTitle;
                }
                if (!empty($this->_confenceLocationCity) || !empty($this->_confenceLocationState)) {
                    $mods['relatedItem']['originInfo']['place']['placeTerm'] = $this->_confenceLocationCity . ' ' . $this->_confenceLocationState;
                }
                $mods['relatedItem']['originInfo']['dateOther'] = $this->_conferenceDates;
            } else if ($this->_xdisTitle == 'Journal Article') {
                $mods['relatedItem']['originInfo']['dateIssued'] = $this->_issueDate;
                $mods['relatedItem']['name'][0]['namePart_type'] = 'journal';
                $mods['relatedItem']['name'][0]['namePart'] = $this->_journalTitle;
                $mods['relatedItem']['originInfo']['publisher'] = $this->_publisher;
            } else if ($this->_xdisTitle == 'Book') {
              $mods['originInfo']['dateIssued'] = $this->_issueDate;
              $mods['originInfo']['publisher'] = $this->_publisher;
            } else if ($this->_xdisTitle == 'Book Chapter') {
              // don't add book title if it comes from a book series
              if ($this->_scopusSrcType != 'k') {
                $mods['relatedItem']['titleInfo']['title'] = $this->_journalTitle;
              }
              $mods['relatedItem']['originInfo']['dateIssued'] = $this->_issueDate;
              $mods['relatedItem']['originInfo']['publisher'] = $this->_publisher;
              // series only seems to come when its from a book in a book (bk in a k)
              if ($this->_seriesTitle != '') {
                $mods['relatedItem']['name'][0]['namePart_type'] = 'series';
                $mods['relatedItem']['name'][0]['namePart'] = $this->_seriesTitle;

              }
            }
            // Links currently blank since only getting first DOI or link
            $links = array();

            $rec = new Record();
            $collection = ($collection) ? $collection : $this->_collections[0];
            $pid = $rec->insertFromArray($mods, $collection, "MODS 1.0", $history, 0, $links, array());

            if (is_numeric($this->_wokCitationCount)) {
                Record::updateThomsonCitationCount($pid, $this->_wokCitationCount, $this->_isiLoc);
            }
            if (is_numeric($this->_scopusCitationCount)) {
                Record::updateScopusCitationCount($pid, $this->_scopusCitationCount, $this->_scopusId);
            }
        }
        return $pid;
    }

    /**
     * Update an existing record with additional bib data from WoK
     */
    public function update($pid)
    {
        $log = FezLog::get();
        // TODO: update an existing record

        if (!$pid) {
            $log->err('Need pid to update');
            return FALSE;
        }

        if (! $this->_loaded) {
            $log->err($this->_importAPI.' record must be loaded before saving');
            return FALSE;
        }

        $searchKeyTargets = array(
            "Date" => $this->_issueDate,
            "ISSN" => $this->_issn,
            "ISBN" => $this->_isbn,
            "DOI" => $this->_doi,
            "Volume Number" => $this->_issueVolume,
            "Start Page" => $this->_startPage,
            "End Page" => $this->_endPage,
            "Total Pages" => $this->_totalPages,
            "Issue Number" => $this->_issueNumber,
            "Language" => $this->_languageCode,
            "Conference Dates" => $this->_conferenceDates,
            "Conference Name" => $this->_conferenceTitle,
            "Journal Name" => $this->_journalTitle,
            "WoK Doc Type" => $this->_wokDocTypeCode,
            "Scopus Doc Type" => $this->_scopusDocTypeCode,
            "Pubmed Id" => $this->_pubmedId,
            "Embase Id" => $this->_embaseId,
            "Scopus Id" => $this->_scopusId,
            "ISI LOC" => $this->_isiLoc,
            "Publisher" => $this->_publisher,
        );

        if (!empty($this->_confenceLocationCity) || !empty($this->_confenceLocationState)) {
            $searchKeyTargets['Conference Location'] = $this->_confenceLocationCity . ' ' . $this->_confenceLocationState;
        }
        /// exception for conf papers that the subtype goes into genre type
        if ($this->_xdisTitle == "Conference Paper") {
            $searchKeyTargets["Genre Type"] = $this->_xdisSubtype;
        } else {
            $searchKeyTargets["Subtype"] = $this->_xdisSubtype;
        }

        $search_keys = array();
        $values = array();

        foreach ($searchKeyTargets as $skey => $svalue) {
            if (!empty($svalue)) {
                $existingValue =  Record::getSearchKeyIndexValue($pid, $skey, false);
                if (empty($existingValue)) {
                    $search_keys[] = $skey;
                    $values[] = $svalue;
                }
            }
        }

        $history = 'Filled empty metadata fields ('.implode(", ", $search_keys).') using '. $this->_importAPI;
        $record = new RecordObject($pid);
        $record->addSearchKeyValueList(
            $search_keys, $values, true, $history
        );

        if (is_numeric($this->_wokCitationCount)) {
            Record::updateThomsonCitationCount($pid, $this->_wokCitationCount, $this->_isiLoc);
        }
        if (is_numeric($this->_scopusCitationCount)) {
            Record::updateScopusCitationCount($pid, $this->_scopusCitationCount, $this->_scopusId);
        }
        return TRUE;
    }

    /**
     * Returns an array of Search key's title & value pair, built from WOS record items.
     *
     * @param array $dTMap
     * @param Fez_Record_Searchkey $recordSearchKey
     * @return array
     */
    protected function _getSekData($recordSearchKey)
    {
        // Build Search key data
        $sekData = array();

        $sekData['Display Type']    = $this->_xdisId;
        $sekData['Genre']           = $this->_xdisTitle;
        $sekData['Genre Type']      = $this->_xdisSubtype;
        $sekData['Title']           = $this->_title;
        $sekData['Author']          = $this->_authors;
        $sekData['ISI LOC']         = $this->_isiLoc;
        $sekData['Scopus ID']         = $this->_scopusId;
        $sekData['Embase ID']         = $this->_embaseId;
        $sekData['Pubmed ID']         = $this->_pubmedId;
        $sekData['Keywords']        = $this->_keywords;
        $sekData['ISBN']            = $this->_isbn;
        $sekData['ISSN']            = $this->_issn;
        $sekData['DOI']            = $this->_doi;
        $sekData['Publisher']       = $this->_publisher;
        $sekData['Scopus Doc Type'] = $this->_scopusDocType;

        /// exception for conf papers that the subtype goes into genre type
        if ($this->_xdisTitle == "Conference Paper") {
            $sekData["Genre Type"] = $this->_xdisSubtype;
        } else {
            $sekData["Subtype"] = $this->_xdisSubtype;
        }

        //Commented out due to copyright reasons
        //$sekData['Description']     = $this->_abstract;

        $sekData['Issue Number']    = $this->_issueNumber;
        $sekData['Volume Number']   = $this->_issueVolume;
        $sekData['Start Page']      = $this->_startPage;
        $sekData['End Page']        = $this->_endPage;
        $sekData['Total Pages']     = $this->_totalPages;

        $sekData['Date']            = Misc::MySQLDate(array("Year" => date("Y", strtotime($this->_issueDate)), "Month" => date("m", strtotime($this->_issueDate))));

        $sekData['Language']        = $this->_languageCode;
        $sekData['Status']          = Status::getID("Published");
        $sekData['Object Type']     = Object_Type::getID("Record");
        $sekData['Depositor']       = Auth::getUserID();
        $sekData['isMemberOf']      = $this->_insertCollection;
        $sekData['Created Date']    = $recordSearchKey->getVersion();
        $sekData['Updated Date']    = $recordSearchKey->getVersion();

        // Custom search keys based on Document Type
        if ($this->_xdis_title == 'Conference Paper') {
            $sekData['Proceedings Title'] = $this->_title;
            $sekData['Conference Name']   = $this->_conferenceTitle;
            $sekData['Conference Dates']  = $this->_conferenceDates;
            if (!empty($this->_confenceLocationCity) || !empty($this->_confenceLocationState)) {
                $sekData['Conference Location']  = $this->_confenceLocationCity . ' ' . $this->_confenceLocationState;
            }
        } else if ($this->_xdis_title == 'Journal Article') {
            $sekData['Journal Name'] = $this->_journalTitle;
        }

        return $sekData;
    }

    public function returnPidWithSameDoi()
    {
        if (empty($this->_doi)) {
            return '';
        }
        $log = FezLog::get();
        $db = DB_API::get();
        $stmt = "SELECT rek_doi_pid FROM " . APP_TABLE_PREFIX . "record_search_key_doi
                WHERE rek_doi = ".$db->quote($this->_doi);
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }
        return $res;
    }

    public function findPidWithBestTitleMatch()
    {
        if (empty($this->_title)) {
            return '';
        }

        $dupes = DuplicatesReport::similarTitlesQuery('dummy', trim($this->_title));
        return $dupes[0]['pid'];
    }

    //Returns score 1-4 on matches
    //Returns -1 on any that have values and don't match
    public function matchOnPageInfo($pid)
    {
        $matches = 0;

        $startPage = Record::getSearchKeyIndexValue($pid, "Start Page", false);
        $endPage = Record::getSearchKeyIndexValue($pid, "End Page", false);
        $totalPages = Record::getSearchKeyIndexValue($pid, "Total Pages", false);
        $issueVolume = Record::getSearchKeyIndexValue($pid, "Volume Number", false);
        $issueNumber = Record::getSearchKeyIndexValue($pid, "Issue Number", false);

        //We'll go through each variable and if both populated check if they match, if any fail we consider the match failed

        if (!empty($this->_startPage)) {
            if ($this->_startPage == $startPage) {
                $matches++;
            } else if (!empty($startPage)) {
                return -1;
            }
        }
        if (!empty($this->_endPage)) {
            if ($this->_endPage == $endPage) {
                $matches++;
            } else if (!empty($endPage)) {
                return -2;
            }
        }
        if (!empty($this->_totalPages)) {
            if ($this->_totalPages == $totalPages) {
                $matches++;
            } else if (!empty($totalPages)) {
                return -3;
            }
        }
        if (!empty($this->_issueNumber)) {
            if ($this->_issueNumber == $issueNumber) {
                $matches++;
            } else if (!empty($issueNumber)) {
                return -4;
            }
        }
        if (!empty($this->_issueVolume)) {
            if ($this->_issueVolume == $issueVolume) {
                $matches++;
            } else if (!empty($issueVolume)) {
                return -5;
            }
        }

        return $matches;
    }

    public function comparePidTitle($pid) {
        $pidTitle =  Record::getSearchKeyIndexValue($pid, "Title", false);
        $stripedPidTitle = RCL::normaliseTitle($pidTitle);
        $stripedEmbaseTitle = RCL::normaliseTitle($this->_title);

        similar_text($stripedPidTitle, $stripedEmbaseTitle, $percent);
        return $percent;
    }
}