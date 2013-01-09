<?php

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

/**
 * This class manages a PID record search keys. It allows simpler call for any CRUD processes on record search key.
 * It automatically evaluates the needs to update shadow table for each sk records update,
 * which eliminates the calling function to call shadow update separately.
 *
 * This class only supports Fedora-less Fez system.
 * If you are using Fedora, you are looking at the wrong file, Ctrl+w is your friend.
 *
 * @version 1.0, April 2012
 * @author Elvi Shu <e.shu at library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */
class Fez_Record_Searchkey
{

    protected $_log = null;
    protected $_db = null;
    protected $_pid = null;
    protected $_version = null;
    protected $_shadow = null;

    /**
     * Class constructor.
     * Assign Database and Fezlog Object to local properties.
     * Assign PID and shadow version.
     *
     * @param string $pid
     */
    public function __construct($pid = null)
    {
        $this->_log = FezLog::get();
        $this->_db = DB_API::get();

        $this->_setPid($pid);

        $this->_setVersion();

        $this->_setShadow();
    }

    /**
     * Instantiate Fez_Record_SearchkeyShadow object
     */
    protected function _setShadow()
    {
        $this->_shadow = new Fez_Record_SearchkeyShadow($this->_pid);

    }

    /**
     * Set the version timestamp to be used on Shadow table(s) operations.
     * Utilise the version registered on Zend Register from earlier process.
     */
    protected function _setVersion()
    {
        if (!Zend_Registry::isRegistered('version')) {
            Zend_Registry::set('version', Date_API::getCurrentDateGMT());
        }
        $this->_version = Zend_Registry::get('version');
    }

    /**
     * Set the PID for this process.
     * @param string $pid
     */
    protected function _setPid($pid = null)
    {
        if (!empty($pid)){
            $this->_pid = $pid;
        } else if (empty($this->_pid)){
            $this->_pid = Fedora_API::getNextPID();
        }
    }


    /**
     * Returns PID
     * @return string
     */
    public function getPid()
    {
        return $this->_pid;
    }


    /**
     * Returns the version used on this PID Search Key process.
     * @return datetime
     */
    public function getVersion()
    {
        return $this->_version;
    }


    /**
     * Updates requested record search key of a PID.
     *
     * @param string $pid
     * @param array $sekData An array of search keys title & value pairs.
     * The format value for $sekData = array(
     *                                       [0] => Array of 1-to-1 search keys
     *                                       [1] => Array of 1-to-Many search keys
     *                                 )
     * @return boolean
     */
    public function insertRecord($sekData = array(), $historyMsg = false, $citationData = array())
    {
        // Set PID
        $this->_setPid();

        // Save 1-to-1 search key
        $oneToOne = $this->_insertOneToOneRecord($sekData[0]);

        // Save 1-to-many search key
        $oneToMany = $this->_insertOneToManyRecord($sekData[1]);

        // Returns false when both updates failed.
        if (!$oneToOne && !$oneToMany['oneSuccess']){
            return false;
        }

        if ($historyMsg){
            $this->_updateHistory($historyMsg);
        }
        $this->_insertRecordCitation($citationData);
        $this->_updateSolrIndex();
        $this->_updateLinksAMR();

        return true;
    }


    /**
     * Updates requested record search key of a PID.
     *
     * @param string $pid
     * @param array $sekData An array of search keys title & value pairs.
     * The format value for $sekData = array(
     *                                       [0] => Array of 1-to-1 search keys
     *                                       [1] => Array of 1-to-Many search keys
     *                                 )
     * @return boolean
     */
    public function updateRecord($pid = null, $sekData = array(), $historyMsg = false)
    {

        // Set PID
        $this->_setPid($pid);

        // Save 1-to-1 search key
        $oneToOne = $this->_updateOneToOneRecord($sekData[0]);

        // Save 1-to-many search key
        $oneToMany = $this->_updateOneToManyRecord($sekData[1]);

        // Returns false when both updates failed.
        if (!$oneToOne && !$oneToMany['oneSuccess']){
            return false;
        }

        if ($historyMsg){
            $this->_updateHistory($historyMsg);
        }
        $this->_updateRecordCitation();
        $this->_updateSolrIndex();
        $this->_cleanCache();
        $this->_updateLinksAMR();
        return true;
    }


    /**
     * Builds array of Search Keys Data, which used for inserting/updating a PID record search keys.
     *
     * @param array $sekTitles
     * @param array $values
     */
    public function buildSearchKeyDataByDisplayType($sekData = array(), $displayType = 0)
    {
        // $searchKeyData[0] = 1-to-1 search keys, $searchKeyData[1] = 1-to-many search keys
        $searchKeyData = array(0 => array(), 1 => array());

        foreach ($sekData as $title => $value) {
            $xsdmfId = XSD_HTML_Match::getXSDMFIDBySearchKeyTitleXDIS_ID($title, $displayType);

            // This search key has no relation to request Display Type, continue to the next search key.
            if (empty($xsdmfId)){
                continue;
            }

            $sekDetails = Search_Key::getDetailsByTitle($title);
            $relationship = $sekDetails['sek_relationship'];
            $sekTitleDb = $sekDetails['sek_title_db'];

            $searchKeyData[$relationship][$sekTitleDb]['xsdmf_id']    = $xsdmfId;
            $searchKeyData[$relationship][$sekTitleDb]['xsdmf_value'] = $value;
        }
        return $searchKeyData;
    }

    /**
     * Builds array of Search Keys Data, which used for inserting/updating a PID record search keys.
     *
     * @param array $xsdmfId
     * @param array $values
     */
    public function buildSearchKeyDataByXSDMFID($sekData = array(), $displayType = 0)
    {
        // $searchKeyData[0] = 1-to-1 search keys, $searchKeyData[1] = 1-to-many search keys
        $searchKeyData = array(0 => array(), 1 => array());

        foreach ($sekData as $xsdmfId => $value) {
            if (empty($xsdmfId)){
                continue;
            }
            $xsdDetails = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmfId);
            $sekDetails = Search_Key::getDetails($xsdDetails['xsdmf_sek_id']);
            $relationship = $sekDetails['sek_relationship'];
            $sekTitleDb = $sekDetails['sek_title_db'];

            if(!empty($sekTitleDb)) {
                $searchKeyData[$relationship][$sekTitleDb]['xsdmf_id']    = $xsdmfId;
                $searchKeyData[$relationship][$sekTitleDb]['xsdmf_value'] = $value;
            }
        }
        return $searchKeyData;
    }

    public function cloneRecord($pid, $new_xdis_id = null, $is_succession = false, $clone_attached_datastreams=false, $collection_pid=null)
    {
        //$new_pid = Fedora_API::getNextPID();
        $record = new RecordObject($pid);
        $record->getDisplay();
        $details = $record->getDetails();
        $sekData = Fez_Record_Searchkey::buildSearchKeyDataByXSDMFID($details);

        $xdis_list = XSD_Relationship::getListByXDIS($new_xdis_id);
        array_push($xdis_list, array("0" => $new_xdis_id));
        $xdis_str = Misc::sql_array_to_string($xdis_list);


        if ($is_succession) {
          $sekData[1]['isderivationof']['xsdmf_value'] = $pid;
          $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('isDerivationOf'), $xdis_str);
          $sekData[1]['isderivationof']['xsdmf_id'] = $xsdmf_id[0];
        }
        if (!empty($collection_pid)) {
          $sekData[1]['ismemberof']['xsdmf_value'] = array($collection_pid);
          $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('isMemberOf'), $xdis_str);
          $sekData[1]['ismemberof']['xsdmf_id'] = $xsdmf_id[0];
        }

        if (!empty($new_xdis_id)) {
            $sekData[0]['display_type']['xsdmf_value'] = $new_xdis_id;
            $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('Display Type'), $xdis_str);
            $sekData[0]['display_type']['xsdmf_id'] = $xsdmf_id[0];
        }

        if (!empty($clone_attached_datastreams)) {
            $sekData[1]['file_attachment_name']['xsdmf_value'] = null;
            $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('File Attachment Name'), $xdis_str);
            $sekData[1]['file_attachment_name']['xsdmf_id'] = $xsdmf_id[0];;
        }
        $recordSearchKey = new Fez_Record_Searchkey();
        $result = $recordSearchKey->insertRecord($sekData);

        $new_pid = $recordSearchKey->_pid;



      // Copy over the security
      $perms = AuthNoFedora::getNonInheritedSecurityPermissions($pid);


      foreach ($perms as $perm) {
        AuthNoFedora::addRoleSecurityPermissions($new_pid, $perm['authii_role'], $perm['argr_arg_id'], '0');
      }
      AuthNoFedora::recalculatePermissions($new_pid);

      if (!empty($clone_attached_datastreams)) {
          $datastreams = Fedora_API::callGetDatastreams($pid);

          foreach ($datastreams as $ds_value) {
            if (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'M'
              && $clone_attached_datastreams) {
//              $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
//              Fedora_API::getUploadLocation(
//                $new_pid, $ds_value['ID'], $value, $ds_value['label'],
//                $ds_value['MIMEType'], $ds_value['controlGroup'], null, $ds_value['versionable']
//              );
              $new_did = Fedora_API::getUploadLocationByLocalRef($new_pid, $ds_value['ID'], $ds_value['full_path'], $ds_value['label'],
                $ds_value['MIMEType'], $ds_value['controlGroup'], null, $ds_value['versionable']);
              $perms = AuthNoFedoraDatastreams::getNonInheritedSecurityPermissions($ds_value['id']);
              foreach ($perms as $perm) {
                AuthNoFedoraDatastreams::addRoleSecurityPermissions($new_did, $perm['authdii_role'], $perm['argr_arg_id'], '0');
              }
              AuthNoFedoraDatastreams::recalculatePermissions($new_did);

            } elseif (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'R'
              && $clone_attached_datastreams) {
              Fedora_API::callAddDatastream(
                $new_pid, $ds_value['ID'], $ds_value['location'], $ds_value['label'],
                $ds_value['state'], $ds_value['MIMEType'], $ds_value['controlGroup'], $ds_value['versionable']
              );
            }
          }
        }




        return $new_pid;
    }
    /**
     * Inserts 1-to-1 record search keys with value specified by the $data parameter &
     * automatically create current version data to Shadow table.
     *
     * It loops through each of the 1-to-1 search keys from $data parameter, and
     * for each search key, it executes the following database update:
     *      a. Insert new details to Main sek table
     *      b. Insert this version to Shadow table
     * Once db->commit() is successful for the above queries, continue with next search key.
     *
     * @param array $data An array of 1-to-1 search key name & value pairs
     * @return boolean True when all queries has been successfully executed.
     */
    protected function _insertOneToOneRecord($data = array())
    {

        if (!is_array($data) || sizeof($data) <= 0){
            return false;
        }

        // Insert new record to main table
        $stmtInsertNew = $this->_buildOneToOneInsertQuery($data);

        try {
            $this->_db->exec($stmtInsertNew);
        } catch (Exception $ex) {
            $this->_log->err($ex);
            return false;
        }

        // Copy to Shadow
        if (!$this->_shadow->copyRecordSearchKeyToShadow()){
            return false;
        }

        return true;
    }


    /**
     * Inserts 1-to-many search keys with value specified by the $data parameter.
     *
     * It loops through each of the 1-to-many search keys on the parameters,
     * for each search key, execute the following database update:
     *      a. Insert new details to Main sek table
     *      b. Insert this version to Shadow table
     * Once db->commit() is successful for the above queries, continue with next search key.
     *
     * @param array $data An array of 1-to-many search key values
     * @return boolean True when all queries has been successfully executed.
     */
    protected function _insertOneToManyRecord($data = array())
    {
        $result = array('oneSuccess' => false);

        if (!is_array($data) || sizeof($data) <=0 ) {
            return $result;
        }

        foreach ($data as $sekTitle => $value) {
            $result[$sekTitle] = false;

            // Verify sk value
            if (!$this->_verifyOneToManyData($value, $sekTitle)){
                continue;
            }

            // Insert new record to main sek table
            $stmtInsertNew = $this->_buildOneToManyInsertQuery($sekTitle, $value);
            try {
                $this->_db->exec($stmtInsertNew);
            } catch (Exception $ex) {
                $this->_log->err($ex);
                // Ignore the rest of processing if Insert query failed.
                continue;
            }

            $hasDelta = $this->_shadow->hasDelta($sekTitle);
            if ($hasDelta) {
              $this->_shadow->copySearchKeyToShadow($sekTitle);
            }

//            if (!$this->_shadow->copySearchKeyToShadow($sekTitle)){
//                continue;
//            }

            // Everything should be running fine upto this point, flag oneSuccess to True.
            $result['oneSuccess'] = true;
            $result[$sekTitle]    = true;
        }

        return $result;
    }


    /**
     * @todo: Add related workflow, bgp bulk, and other relevant info on $historyDetailExtra
     *
     * @param string $msg
     * @return boolean
     */
    protected function _updateHistory($message)
    {
        $extraMsg = null;

        // addHistory($pid, $wfl_id=null, $outcome="", $outcomeDetail="", $refreshDatastream=false, $historyDetail="", $historyDetailExtra=null, $event_date = false)
        History::addHistory($this->_pid, null, "", "", true, $message, $extraMsg);

        return true;
    }


    /**
     * Adds PID on the Links AMR service queuefor updating 'ISI Loc' search key.
     * Only sends to the queue when PID doesn't already have an ISI Loc.
     *
     * @return boolean
     */
    protected function _updateLinksAMR()
    {
        if (APP_AUTO_LINKSAMR_UPLOAD != "ON") {
            return true;
        }

        $isi_loc = Record::getSearchKeyIndexValue($this->_pid, "ISI Loc", false);
        if (empty($isi_loc)) {
            LinksAmrQueue::get()->add($this->_pid);
        }
        return true;
    }


    /**
     * Cleans the cache file related to current PID.
     *
     * @return boolean
     */
    protected function _cleanCache()
    {
        if (APP_FILECACHE != "ON" ) {
            return true;
        }

        $cache = new fileCache($this->_pid, 'pid=' . $this->_pid);
        $cache->poisonCache();
        return true;
    }


    /**
     * Update SOLR index caches.
     *
     * @return boolean
     */
    protected function _updateSolrIndex()
    {
        if( APP_SOLR_INDEXER != "ON" ) {
            return true;
        }

        $this->_log->debug("Fez_Record_Searchkey->update() adding " . $this->_pid . " to SOLR Queue");
        FulltextQueue::singleton()->add($this->_pid);
        FulltextQueue::singleton()->commit();
        return true;
    }


    /**
     * Updates citation caches.
     * @return boolean
     */
    protected function _updateRecordCitation()
    {
        if (!defined('PROVISIONAL_CODE_UPDATE_FROM_SCRIPT') || PROVISIONAL_CODE_UPDATE_FROM_SCRIPT === false) {
            Record::applyProvisionalCode($this->_pid);
        }

        Citation::updateCitationCache($this->_pid);
        // We don't need to update the following. Each citation will get updated when related script is executed (mostly via cron job)
//        Statistics::updateSummaryStatsOnPid($this->_pid);
//        Google_Scholar::updateCitationCache($this->_pid);
//        Record::updateThomsonCitationCountFromHistory($this->_pid);
//        Record::updateScopusCitationCountFromHistory($this->_pid);

        return true;
    }


    /**
     * Inserts citations for newly created PID.
     *
     * @return boolean
     */
    protected function _insertRecordCitation($citationData = array())
    {
        if (!defined('PROVISIONAL_CODE_UPDATE_FROM_SCRIPT') || PROVISIONAL_CODE_UPDATE_FROM_SCRIPT === false) {
            Record::applyProvisionalCode($this->_pid);
        }

        // Renders citation cache (used on SOLR search)
        Citation::updateCitationCache($this->_pid);

        if (array_key_exists('googleScholar', $citationData) && !empty($citationData['googleScholar'])){
            Record::updateGoogleScholarCitationCount($this->_pid,
                                                      $citationData['googleScholar']['count'],
                                                      $citationData['googleScholar']['link']);
            Google_Scholar::insertGoogleScholarCitationCount($this->_pid,
                                                      $citationData['googleScholar']['count'],
                                                      $citationData['googleScholar']['link']);
        }

        if (array_key_exists('scopus', $citationData) && !empty($citationData['scopus'])){
            $eid = Record::getSearchKeyIndexValue($this->_pid, "Scopus ID", false);
            Record::updateScopusCitationCount($this->_pid, $citationData['scopus'], $eid);
        }

        if (array_key_exists('thomson', $citationData) && !empty($citationData['thomson'])){
            $isiloc = Record::getSearchKeyIndexValue($this->_pid, "ISI LOC", false);
            Record::updateThomsonCitationCount($this->_pid, $citationData['thomson'], $isiloc);
        }

        return true;
    }


    /**
     * Updates 1-to-1 record search keys with value specified by the $data parameter &
     * automatically backup previous data to Shadow table.
     * It handles all/partial search keys update on the core sk table.
     * If the PID already has 1-to-1 sk record, it replaces the existing record with value specified on $data parameter.
     *
     * It loops through each of the 1-to-many search keys from $data parameter, and
     * for each search key, it executes the following database update:
     *      a. Backup current search key (sk) to Shadow table
     *      b. Delete current sk details
     *      c. Insert new sk details
     * Once db->commit() is successful for the above queries, continue with next search key.
     *
     * @param array $data An array of 1-to-1 search key name & value pairs
     * @return boolean True when all queries has been successfully executed.
     */
    protected function _updateOneToOneRecord($data = array())
    {

        if (!is_array($data) || sizeof($data) <= 0){
            return false;
        }

        $table = APP_TABLE_PREFIX . "record_search_key";
        $tableShadow = $table . "__shadow";


        // Query to backup old record: copy the data from sk main table to shadow table
        $stmtBackupToShadow = "INSERT INTO " . $tableShadow .
                "  SELECT *, " . $this->_db->quote($this->_version, 'DATE') .
                "  FROM " . $table .
                "  WHERE rek_pid = " . $this->_db->quote($this->_pid, 'STRING');

        // Query to remove old record on main table.
        $stmtDeleteOld = "DELETE FROM " . $table .
                " WHERE rek_pid = " . $this->_db->quote($this->_pid, 'STRING');

        // Query to insert new record to main table
        $stmtInsertNew = $this->_buildOneToOneUpdateQuery($data);

        // Begin DB transaction explicitly. We want to be able to rollback if any of these queries failed.
        $this->_db->beginTransaction();
        try {
            $this->_db->exec($stmtBackupToShadow);
            $this->_db->exec($stmtDeleteOld);
            $this->_db->exec($stmtInsertNew);
            $this->_db->commit();

            return true;
        }
        catch (Exception $ex) {
            $this->_db->rollBack();
            $this->_log->err($ex);
        }
        return false;
    }


    /**
     * Updates 1-to-many search keys with value specified by the $data parameter.
     *
     * It loops through each of the 1-to-many search keys on the parameters,
     * for each search key, execute the following database update:
     *      a. Backup current search key (sk) to shadow table
     *      b. Delete current sk details
     *      c. Insert new sk details
     * Once db->commit() is successful for the above queries, continue with next search key.
     *
     * @param array $data An array of 1-to-many search key values
     * @return boolean True when all queries has been successfully executed.
     */
    protected function _updateOneToManyRecord($data = array())
    {
        $result = array('oneSuccess' => false);

        if (!is_array($data) || sizeof($data) <=0 ) {
            return $result;
        }

        foreach ($data as $sekTitle => $value) {
            $table = APP_TABLE_PREFIX . "record_search_key_" . $sekTitle;
            $tableShadow = $table . "__shadow";
            $pidColumn = "rek_" . $sekTitle . "_pid";

            // Varify sk value
            if (!$this->_verifyOneToManyData($value, $sekTitle)){
                continue;
            }
            // Check that the data to be inserted is not exactly the same as the new data. If it is exactly the same don't save it and just rely on deltas.
            $recordSearchKeyShadow = new Fez_Record_SearchkeyShadow($this->_pid);
            $hasDelta = $recordSearchKeyShadow->hasDelta($sekTitle);


            // Query to backup old record to shadow table
            $stmtBackupToShadow = "INSERT INTO " . $tableShadow .
                    "  SELECT *, " . $this->_db->quote($this->_version, 'DATE') .
                    "  FROM " . $table .
                    "  WHERE ". $pidColumn ." = " . $this->_db->quote($this->_pid, 'STRING');

            // Query to remove old record on main table.
            $stmtDeleteOld = "DELETE FROM " . $table .
                    "  WHERE ". $pidColumn ." = " . $this->_db->quote($this->_pid, 'STRING');

            // Query to insert new record to main table
            $stmtInsertNew = $this->_buildOneToManyInsertQuery($sekTitle, $value);

            // Begin DB transaction explicitly. We want to be able to rollback if any of these queries failed.
            $this->_db->beginTransaction();
            try {
                if ($hasDelta) {
                  $this->_db->exec($stmtBackupToShadow);
                }
                $this->_db->exec($stmtDeleteOld);
                $this->_db->exec($stmtInsertNew);
                $this->_db->commit();

                $result['oneSuccess'] = true;
                $result[$sekTitle] = true;
            }
            catch (Exception $ex) {
                $this->_db->rollBack();
                $this->_log->err($ex);

                $result[$sekTitle] = false;
            }
        }

        return $result;
    }


    /**
     * Verifies the proposed record sk against the following:
     * - If the values is array, make sure it is not empty array or empty array values.
     * - If the value is singular (not array), make sure it is not empty or NULL.
     * - If the search key has 1-to-1 cardinality, the value should be singular (not array)
     *
     * @param type $value
     * @return boolean
     */
    protected function _verifyOneToManyData($value = array(), $sekTitle = "")
    {
        if (is_array($value['xsdmf_value'])) {
            $sekValTest = strtoupper(implode('', $value['xsdmf_value']));
        } else {
            $sekValTest = $value['xsdmf_value'];
        }

        if (empty($value['xsdmf_value']) || is_null($value['xsdmf_value']) || ($sekValTest == "NULL")) {
            return false;
        }

        // Added this notEmpty check to look for empty arrays.  Stops fez from writing empty keyword
        // values to fez_record_search_key_keywords table.  -  heaphey
        $notEmpty = 1;  // start assuming that value is not empty
        if (is_array($sek_value['xsdmf_value'])) {
            $stringvalue = implode("", $sek_value['xsdmf_value']);
            if (strlen($stringvalue) == 0) {
                $notEmpty = 0;  // this value is an array and it is empty
            }
        }

        // only write values to tables if the value is not empty
        if (!$notEmpty) {
            return false;
        }

        $xsdDetails = XSD_HTML_Match::getDetailsByXSDMF_ID($value['xsdmf_id']);
        $searchKeyDetails = Search_Key::getDetails($xsdDetails['xsdmf_sek_id']);

        // do final check for cardinality before trying to insert/update an array of values in one to many tables
        if (is_array($value['xsdmf_value']) && $searchKeyDetails['sek_cardinality'] == 0) {
            $this->_log->err(
                    "The cardinality of this value is 1-1 but it is in the 1-M data and contains multiple " .
                    "values. We cannot insert/update pid {$this->_pid} for the {$sekTitle} table with data: " .
                    var_export($value, true)
            );
            return false;
        }

        return true;
    }


    /**
     * Builds INSERT query for a PID's 1-to-1 search keys.
     * Handles all/partial search keys update on the core sk table.
     * If the PID already has 1-to-1 sk record, it replaces the existing record with value specified on $data parameter.
     *
     * @param array $data Search keys data to be inserted.
     * @return string Query statement
     */
    protected function _buildOneToOneUpdateQuery($data = array())
    {
        $table = APP_TABLE_PREFIX . "record_search_key";
        $current = null;
        $stmt = "";

        $stmtFields = array();
        $stmtValues = array();

        if (!empty($this->_pid)) {
            $stmtSelect = "SELECT * FROM " . $table .
                          " WHERE rek_pid = " . $this->_db->quote($this->_pid);
            $current = $this->_db->fetchRow($stmtSelect);
        }

        // Set the query fields & values
        if (is_array($current)) {
            foreach ($current as $field => $value) {
                $stmtFields[] = $field;

                if ($field == "rek_updated_date" || $field == "rek_created_date") {
                    $stmtValues[] = $this->_db->quote($this->_version);
                    continue;
                }

                $datafield = str_replace("rek_", "", $field);

                if (!isset($data[$datafield])) {
                    $stmtValues[] = $this->_db->quote($value);
                    continue;
                }

                if ($data[$datafield] == 'NULL') {
                    $stmtValues[] = $data[$datafield];
                } else {
                    // Hack for Year?
                    if (is_array($data[$datafield]["xsdmf_value"]) && array_key_exists('Year', $data[$datafield]["xsdmf_value"])) {
                        $data[$datafield]["xsdmf_value"] = $data[$datafield]["xsdmf_value"]['Year'];
                    }
                    $stmtValues[] = $this->_db->quote($data[$datafield]["xsdmf_value"]);
                }
            }
        } else {
            foreach ($data as $field => $valueArray) {
                $fieldname = "rek_" . $field;

                $stmtFields[] = $fieldname;
                $stmtValues[] = $this->_db->quote($valueArray['xsdmf_value']);
                $stmtFields[] = $fieldname . "_xsdmf_id";
                $stmtValues[] = $valueArray['xsdmf_id'];
            }
        }

        // Build the query statement
        $stmt = "INSERT INTO " . $table .
                " (" . implode(",", $stmtFields) . ")" .
                " VALUES (" . implode(",", $stmtValues) . ");";

        return $stmt;
    }


    /**
     * Builds INSERT query for a NEW PID's 1-to-1 search keys.
     *
     * @param array $data Search keys data to be inserted.
     * @return string Query statement
     */
    protected function _buildOneToOneInsertQuery($data = array())
    {
        $table = APP_TABLE_PREFIX . "record_search_key";

        $current = null;
        $stmt = "";

        $stmtFields = array();
        $stmtValues = array();

        // Set PID
        $stmtFields[] = 'rek_pid';
        $stmtValues[] = $this->_db->quote($this->_pid);

        // Set the query fields & values
        foreach ($data as $field => $valueArray) {
            $fieldname = "rek_" . $field;

            $stmtFields[] = $fieldname;
            $stmtValues[] = $this->_db->quote($valueArray['xsdmf_value']);
            $stmtFields[] = $fieldname . "_xsdmf_id";
            $stmtValues[] = $valueArray['xsdmf_id'];
        }

        // Build the query statement
        $stmt = "INSERT INTO " . $table .
                " (" . implode(",", $stmtFields) . ")" .
                " VALUES (" . implode(",", $stmtValues) . ");";

        return $stmt;
    }


    /**
     * Builds INSERT query for a PID's 1-to-many search keys.
     *
     * @param string $sekTitle
     * @param array $value
     * @return string Query statement
     */
    protected function _buildOneToManyInsertQuery($sekTitle = "", $value = array())
    {
        $stmtFields = array();
        $stmtValues = array();
        $currentData = null;

        $table = APP_TABLE_PREFIX . "record_search_key_" . $sekTitle;
        $pidColumn = "rek_" . $sekTitle . "_pid";
        $cardinalityColumn = "rek_" . $sekTitle . "_order";


        $xsdDetails = XSD_HTML_Match::getDetailsByXSDMF_ID($value['xsdmf_id']);
        $searchKeyDetails = Search_Key::getDetails($xsdDetails['xsdmf_sek_id']);
        $sekCardinality = $searchKeyDetails['sek_cardinality'];

        // Set the query fields
        $stmtFields = array($pidColumn,
                        "rek_" . $sekTitle . "_xsdmf_id",
                        "rek_" . $sekTitle);
        if ($sekCardinality == 1) {
            $stmtFields[] = $cardinalityColumn;
        }

        // Set the query values
        $cardinalityVal = 1;
        if (is_array($value['xsdmf_value'])) {
            foreach ($value['xsdmf_value'] as $val) {
                $values = array($this->_db->quote($this->_pid),
                                $this->_db->quote($value['xsdmf_id'], 'INTEGER'),
                                $this->_db->quote($val));
                if ($sekCardinality == 1){
                    $values[] = $cardinalityVal;
                }

                $stmtValues[] = "(" . implode(",", $values) . ")";
                $cardinalityVal++;
            }

        } else {
            $values = array($this->_db->quote($this->_pid),
                            $this->_db->quote($value['xsdmf_id'], 'INTEGER'),
                            $this->_db->quote($value['xsdmf_value']));

            if ($sekCardinality == 1){
                $values[] = $cardinalityVal;
            }
            $stmtValues[] = "(" . implode(",", $values) . ")";
        }

        // Build the query statement
        $stmt = "INSERT INTO " . $table .
                " (" . implode(",", $stmtFields) . ")" .
                " VALUES " . implode(",", $stmtValues). ";";

        return $stmt;
    }

}
