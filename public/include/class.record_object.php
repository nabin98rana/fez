<?php
include_once(APP_INC_PATH . 'class.record_general.php');
include_once(APP_INC_PATH . 'class.fedora_api.php');
include_once(APP_INC_PATH . 'class.digitalobject.php');

/**
 * RecordObject
 * Manages the interface to the database and fedora for records.
 * Stores local copies of record properties to save multiple accesses to the database.
 */
class RecordObject extends RecordGeneral
{
  var $created_date;
  var $updated_date;
  var $depositor;
  var $assign_grp_id;
  var $assign_usr_id;
  var $file_downloads; //for statistics of file datastream downloads from eserv.php
  var $default_xdis_id = 5;
  var $status;


  function RecordObject($pid = null, $createdDT = null)
  {
    RecordGeneral::RecordGeneral($pid, $createdDT);
  }

  /**
   * Enter deleted pids and their children into
   * the tombstone table.
   * @param <string> $pid
   */
  function tombstone($pid, $rel_pid)
  {
    $db = DB_API::get();
    $dte = gmdate('Y-m-d H:i:s');

    $sql = "INSERT INTO " . APP_TABLE_PREFIX . "tombstone "
        . "(tom_pid_main, tom_pid_rel, tom_delete_ts) VALUES "
        . "(:mainpid, :relpid, '$dte')";

    $bindings = array(':mainpid' => $pid,
        ':relpid' => $rel_pid);

    $db->query($sql, $bindings);
  }

  /**
   * getXmlDisplayId
   * Retrieve the display id for this record
   */
  function getObjectAdminMD()
  {
    if (APP_FEDORA_BYPASS == 'ON') {
      $xdis_array['created_date'][0] = Record::getSearchKeyIndexValue($this->pid, "Created Date", false);
      $xdis_array['updated_date'][0] = Record::getSearchKeyIndexValue($this->pid, "Updated Date", false);
      $xdis_array['depositor'][0] = Record::getSearchKeyIndexValue($this->pid, "Depositor", false);
      $xdis_array['grp_id'][0] = Record::getSearchKeyIndexValue($this->pid, "Assigned Group ID", false);
      $xdis_array['usr_id'][0] = Record::getSearchKeyIndexValue($this->pid, "Assigned User ID", false);
      $xdis_array['sta_id'][0] = Record::getSearchKeyIndexValue($this->pid, "Status", false);

    } else {
      $xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
    }

    if (isset($xdis_array['created_date'][0])) {
      $this->created_date = $xdis_array['created_date'][0];
    } else {
      $this->created_date = NULL;
    }
    if (isset($xdis_array['updated_date'][0])) {
      $this->updated_date = $xdis_array['updated_date'][0];
    } else {
      $this->updated_date = NULL;
    }
    if (isset($xdis_array['depositor'][0])) {
      $this->depositor = $xdis_array['depositor'][0];
    } else {
      $this->depositor = NULL;
    }
    if (isset($xdis_array['grp_id'][0]) && !empty($xdis_array['grp_id'][0])) {
      $this->assign_grp_id = $xdis_array['grp_id'][0];
    } else {
      $this->assign_grp_id = NULL;
    }
    if (isset($xdis_array['usr_id'][0]) && !empty($xdis_array['usr_id'][0])) {
      if (!is_array($this->assign_usr_id)) {
        $this->assign_usr_id = array();
      }
      foreach ($xdis_array['usr_id'] as $assign_usr_id) {
        array_push($this->assign_usr_id, $assign_usr_id);
      }
    } else {
      $this->assign_usr_id = array();
    }
    if (isset($xdis_array['sta_id'][0])) {
      $this->status = $xdis_array['sta_id'][0];
    } else {
      $this->status = NULL;
    }
  }

  /**
   * updateAdminDatastream
   * Used to associate a display for this record
   *
   * @access  public
   * @param  integer $xdis_id The XSD Display ID of the object
   * @return  void
   */
  function updateAdminDatastream($xdis_id)
  {
    if (APP_FEDORA_BYPASS == 'ON') {
      // Update record search key
      $recordSearchKey = new Fez_Record_Searchkey($this->pid);
      $recordSearchKey->updateRecordDisplayType($xdis_id);
      return;
    }

    $xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
    $this->xdis_id = $xdis_id;
    $newXML = '<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">';
    $foundElement = false;
    foreach ($xdis_array as $xkey => $xdata) {
      foreach ($xdata as $xinstance) {
        if ($xkey == "xdis_id") {
          $foundElement = true;
          $newXML .= "<" . $xkey . ">" . $this->xdis_id . "</" . $xkey . ">";
        } elseif ($xinstance != "") {
          $newXML .= "<" . $xkey . ">" . $xinstance . "</" . $xkey . ">";
        }
      }
    }
    if ($foundElement != true) {
      $newXML .= "<xdis_id>" . $this->xdis_id . "</xdis_id>";
    }
    $newXML .= "</FezMD>";
    if ($newXML != "") {
      Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", "inherit");
      $this->setIndexMatchingFields();
    }
  }

  function prepareSearchKeyData($params)
  {
    $alphaParams = array();

    foreach ($params['xsd_display_fields'] as $dfk => $dfv) {
      $alphaKey = Search_Key::getDetailsByXSDMF_ID($dfk);
      $alphaParams[$alphaKey['sek_title']] = $dfv;
    }

    return $alphaParams;
  }

  /**
   * fedoraInsertUpdate
   * Process a submitted record insert or update form
   *
   * @access  public
   * @return  string - pid
   */
  function fedoraInsertUpdate($exclude_list = array(), $specify_list = array(), $params = array(), $tmpFilesArray = array())
  {
    if (!empty($params)) {
      // dirty double hack as this function and all the ones it calls assumes this is
      // to do with a form submission
      $_POST = $params;
    }

    //location_widget won't work with array ie name="xsd_display_fields[1234]" so need to convert it( ie xsd_display_fields_location_widget_1234) back to the xsd_display_fields array
    foreach ($_POST as $key => $value) {
      if (strstr($key, 'xsd_display_fields_location_widget')) {
        $pieces = explode('_', $key);
        $_POST['xsd_display_fields'][$pieces['5']] = $value;
        unset($_POST[$key]);
      }
    }

    $existingDatastreams = array();

    if (APP_FEDORA_BYPASS == 'ON') {
      if (!Zend_Registry::isRegistered('version')) {
        Zend_Registry::set('version', Date_API::getCurrentDateGMT());
      }

      $now = Zend_Registry::get('version');

      $xdisDisplayFields = $_POST['xsd_display_fields'];

      $last = "";
      $lastKey = null;
      //Now add all the xsdmf_ref_id values
      $xsdmf_list = XSD_HTML_Match::getListByDisplay($_POST['xdis_id'], $exclude_list, $specify_list);
      foreach ($xsdmf_list as $xsdmf) {
        if ($xsdmf['xsdmf_html_input'] == 'static' && $xsdmf['xsdmf_element'] == '!genre') {
            $xdisDisplayFields[$xsdmf['xsdmf_id']] = $xsdmf['xsdmf_static_text'];
        }
        if ($xsdmf['xsdmf_html_input'] == 'xsdmf_id_ref') {
          //now check if the posted data contains a reference to it
          if (array_key_exists($xsdmf['xsdmf_id_ref'], $xdisDisplayFields)
           && !empty($xdisDisplayFields[$xsdmf['xsdmf_id_ref']])) {
              //now see if we are to transfer the value or the id
            $xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf['xsdmf_id_ref']);
            if ($xsdmf_details['xsdmf_html_input'] == 'contvocab_selector') {
              if ($xsdmf['xsdmf_cvo_save_type'] == 1) {
                $xdisDisplayFields[$xsdmf['xsdmf_id']] = Controlled_Vocab::getTitle($xdisDisplayFields[$xsdmf['xsdmf_id_ref']]);
              } else {
                $xdisDisplayFields[$xsdmf['xsdmf_id']] = $xdisDisplayFields[$xsdmf['xsdmf_id_ref']];
              }
           } else {
              $xdisDisplayFields[$xsdmf['xsdmf_id']] = $xdisDisplayFields[$xsdmf['xsdmf_id_ref']];
           }
          }
        }
        if ($xsdmf['xsdmf_html_input'] == 'rich_text') {
          $value = $xdisDisplayFields[$xsdmf['xsdmf_id']];
          if ($value == '<br />') {
            $xdisDisplayFields[$xsdmf['xsdmf_id']] = '';
          }
        }
      }
      //Except the final one if it's empty since it is the empty field reserved for "new item" if a multiple field
      //Previous empty values may be needed to space out between values
      foreach ($_POST as $key => $value) {
        if ((strpos($key, 'xsd_display_fields') !== false) && ($key != 'xsd_display_fields')) {
          $xsdDisplayFieldsElementKeys = explode('_', $key);
          if (!empty($last) && $xsdDisplayFieldsElementKeys[3] != $last) {
            if (empty($lastValue)) {
              unset($xdisDisplayFields[$last][$lastKey]);
            }
          }
          $xdisDisplayFields[$xsdDisplayFieldsElementKeys[3]][$xsdDisplayFieldsElementKeys[4]] = $value;
          $lastValue = $value;
          $last = $xsdDisplayFieldsElementKeys[3];
          $lastKey = $xsdDisplayFieldsElementKeys[4];
        }
      }
      if (empty($lastValue)) {
        unset($xdisDisplayFields[$last][$lastKey]);
      }

      $xsd_display_fields = RecordGeneral::setDisplayFields($xdisDisplayFields);

      $xdis_list = XSD_Relationship::getListByXDIS($_POST['xdis_id']);
      array_push($xdis_list, array("0" => $_POST['xdis_id']));
      $xdis_str = Misc::sql_array_to_string($xdis_list);

      $xdis_details = XSD_Display::getDetails($_POST['xdis_id']);

      $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('Display Type'), $xdis_str);
      $xsd_display_fields[0]['display_type'] = array('xsdmf_id' => $xsdmf_id[0], 'xsdmf_value' => $_POST['xdis_id']);

      if (empty($this->pid)) {
        $depositor = Auth::getUserID();
      } else {
        $depositor = Record::getSearchKeyIndexValue($this->pid, "Depositor", false);
      }
      if (empty($xsd_display_fields[0]['genre'])) {
          $genre = Record::getSearchKeyIndexValue($this->pid, "Genre", false);
          $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('Genre'), $xdis_str);
          if (empty($genre)) {
              $genre = $xsdmf[$xsdmf_id]['xsdmf_static_text'];
          }
          $xsd_display_fields[0]['genre'] = array('xsdmf_id' => $xsdmf_id[0], 'xsdmf_value' => $genre);
      }

      $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('Depositor'), $xdis_str);
      $xsd_display_fields[0]['depositor'] = array('xsdmf_id' => $xsdmf_id[0], 'xsdmf_value' => $depositor);

      if (array_key_exists('collection_pid', $_POST) && !empty($_POST['collection_pid'])) {
        $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('isMemberOf'), $xdis_str);
        $xsd_display_fields[1]['ismemberof'] = array('xsdmf_id' => $xsdmf_id[0], 'xsdmf_value' => [$_POST['collection_pid']]);
      }

      $updatedDate = Date_API::getCurrentDateGMT();

      if (empty($this->pid)) {
        $createdDate = $updatedDate;
      } else {
        $createdDate = Record::getSearchKeyIndexValue($this->pid, "Created Date", false);
      }
      $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('Created Date'), $xdis_str);
      $xsd_display_fields[0]['created_date'] = array('xsdmf_id' => $xsdmf_id[0], 'xsdmf_value' => $createdDate);

      $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('Updated Date'), $xdis_str);
      $xsd_display_fields[0]['updated_date'] = array('xsdmf_id' => $xsdmf_id[0], 'xsdmf_value' => $updatedDate);

      $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('Status'), $xdis_str);
      $xsd_display_fields[0]['status'] = array('xsdmf_id' => $xsdmf_id[0], 'xsdmf_value' => $_POST['sta_id']);

      $xsdmf_id = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('Object Type'), $xdis_str);
      $xsd_display_fields[0]['object_type'] = array('xsdmf_id' => $xsdmf_id[0], 'xsdmf_value' => $xdis_details['xdis_object_type']);

      // empty checkboxes don't get posted, so you need to manually set the value to save 'off' or it will never save or change from 'on' values
      // and empty controlled vocabs don't get posted, so you need to manually remove the values
      foreach ($xsdmf_list as $xsdmf_key => $xsdmf_val) {
        if ($xsdmf_val['xsdmf_html_input'] == 'checkbox') {
          if (!empty($xsdmf_val['xsdmf_sek_id'])) {
            $sek_details = Search_Key::getBasicDetails($xsdmf_val['xsdmf_sek_id']);
            if (array_key_exists('xsd_display_fields', $_POST)
                && !array_key_exists($xsdmf_val['xsdmf_id'], $_POST['xsd_display_fields'])) {
              $xsd_display_fields[$sek_details['sek_relationship']][$sek_details['sek_title_db']] = array('xsdmf_id' => $xsdmf_val['xsdmf_id'], 'xsdmf_value' => 'off');
            }
          }
        }
        if ($xsdmf_val['xsdmf_html_input'] == 'xsdmf_id_ref') {
          if (!empty($xsdmf_val['xsdmf_id_ref'])) {
            $xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_val['xsdmf_id_ref']);
            if ($xsdmf_details['xsdmf_html_input'] == 'contvocab_selector') {
              $sek_details = Search_Key::getBasicDetails($xsdmf_val['xsdmf_sek_id']);
              if (array_key_exists('xsd_display_fields', $_POST)
                && !array_key_exists($xsdmf_details['xsdmf_id'], $_POST['xsd_display_fields'])
              ) {
                $xsd_display_fields[$sek_details['sek_relationship']][$sek_details['sek_title_db']] = array(
                  'xsdmf_id' => $xsdmf_details['xsdmf_id'],
                  'xsdmf_value' => ''
                );
              }
            }
          }
        }
      }

      $this->xdis_id = $_POST['xdis_id'];

      if (empty($this->pid)) {
        $this->pid = Fedora_API::getNextPID();
        $newPid = true;
      }

      $this->created_date = $createdDate;
      $this->updated_date = $updatedDate;
      $this->depositor = $depositor;
      $this->assign_usr_id = array(Auth::getUserID());

      $this->getXmlDisplayId();

      if (isset($_POST['uploader_files_uploaded'])) {
        $wfstatus = &WorkflowStatusStatic::getSession();
        if (! count($tmpFilesArray)) {
          $tmpFilesArray = Uploader::generateFilesArray($wfstatus->id, $_POST['uploader_files_uploaded']);
        }
        if (count($tmpFilesArray)) {

          $_FILES = $tmpFilesArray;

          $resourceData = $_FILES['xsd_display_fields']['new_file_location'];
          $resourceDataKeys = array_keys($resourceData);
          $numFiles = count($resourceData[$resourceDataKeys[0]]);
          $mimeData = $_FILES['xsd_display_fields']['type'];
          $mimeDataKeys = array_keys($mimeData);
          $fileNames = array();

          for ($i = 0; $i < $numFiles; $i++) {
            $resourceDataLocation = $resourceData[$resourceDataKeys[0]][$i];
            if (! is_file($resourceDataLocation)) {
              continue;
            }
            $mimeDataType = $mimeData[$mimeDataKeys[0]][$i];
            $fileInfo = new SplFileInfo($resourceDataLocation);
            $new_dsID = Foxml::makeNCName($fileInfo->getFilename());
            $label = "";
            if (@$_POST['description'][$i]) {
              $label = $_POST['description'][$i];
            }

            Fedora_API::getUploadLocationByLocalRef($this->pid, $new_dsID, $resourceDataLocation, $label, $mimeDataType,"M",null,true);
            array_push($fileNames, $new_dsID);
            $tmpFile = Misc::getFileTmpPath($new_dsID);
//            rename($resourceDataLocation, $tmpFile);

            Record::generatePresmd($this->pid, $new_dsID);
          }
        }
      }
      // Check for all the existing files in case there are thumbs/presmds etc not indexed
      $datastreams = Fedora_API::callGetDatastreams($this->pid);

      $fileAttachXsdmfId = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('File Attachment Name'), $xdis_str);
      if (is_array($fileAttachXsdmfId) && !empty($fileAttachXsdmfId[0])) {
        foreach ($datastreams as $ds_value) {
          if (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'M') {
            if (!is_array($xsd_display_fields[1]['file_attachment_name'])) {
              $xsd_display_fields[1]['file_attachment_name'] = array();
              if (!is_array($xsd_display_fields[1]['file_attachment_name']['xsdmf_value'])) {
                $xsd_display_fields[1]['file_attachment_name']['xsdmf_value'] = array();
              }
              $xsd_display_fields[1]['file_attachment_name']['xsdmf_id'] = $fileAttachXsdmfId;
            }
            array_push($xsd_display_fields[1]['file_attachment_name']['xsdmf_value'], $ds_value['ID']);
          }
        }
      }

      $fileDescXsdmfId = XSD_HTML_Match::getXSDMF_IDBySekIDXDIS_ID(Search_Key::getID('File Description'), $xdis_str);
      if (is_array($fileDescXsdmfId) && !empty($fileDescXsdmfId[0])) {
        foreach ($datastreams as $ds_value) {
          if (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'M') {
            if (!is_array($xsd_display_fields[1]['file_description'])) {
              $xsd_display_fields[1]['file_description'] = array();
              if (!is_array($xsd_display_fields[1]['file_description']['xsdmf_value'])) {
                $xsd_display_fields[1]['file_description']['xsdmf_value'] = array();
              }
              $xsd_display_fields[1]['file_description']['xsdmf_id'] = $fileDescXsdmfId;
            }
            array_push($xsd_display_fields[1]['file_description']['xsdmf_value'], $ds_value['label']);
          }
        }
      }

      Record::updateSearchKeys($this->pid, $xsd_display_fields, false, $now); //into the non-shadow tables
      Record::updateSearchKeys($this->pid, $xsd_display_fields, true, $now); //into the shadow tables

      // now process the ingest triggers now that file_attachment_name etc has been saved into the search keys
      if (count($tmpFilesArray) > 0) {
        for ($i = 0; $i < $numFiles; $i++) {
          $tmpFile = Misc::getFileTmpPath($fileNames[$i]);
          $resourceDataLocation = $resourceData[$resourceDataKeys[0]][$i];
          Workflow::processIngestTrigger($this->pid, $fileNames[$i], $mimeDataType);
          if (is_file($tmpFile)) {
            unlink($tmpFile);
          }
          if (is_file($resourceDataLocation)) {
            unlink($resourceDataLocation);
          }

        }
      }

      //Mark any files required for deletion.
      if (isset($_POST['removeFiles'])) {
        foreach ($_POST['removeFiles'] as $removeFile) {
          Fedora_API::deleteDatastream($this->pid, $removeFile);
        }
      }
      $xdis_title = XSD_Display::getMatchingFezACMLTitle($xdis_str);
      if (! empty($xdis_title) && !in_array('FezACML', $exclude_list)) {
        FezACML::editPidSecurity($this->pid, $xdis_title);
        /*
         * This pid has been updated, we want to delete any
         * cached files as well as cached files for custom views
         */
        if (APP_FILECACHE == "ON") {
          $cache = new fileCache($this->pid, 'pid='.$this->pid);
          $cache->poisonCache();
        }
      }

    } else {
      // If pid is null then we need to ingest the object as well
      // otherwise we are updating an existing object
      $ingestObject = false;

      if (empty($this->pid)) {
        $this->pid = Fedora_API::getNextPID();
        $ingestObject = true;
        $this->created_date = Date_API::getFedoraFormattedDateUTC();
        $this->updated_date = $this->created_date;
        $this->depositor = Auth::getUserID();
        $this->assign_usr_id = array(Auth::getUserID());
      } else {
        $existingDatastreams = Fedora_API::callGetDatastreams($this->pid);
        if (APP_VERSION_UPLOADS_AND_LINKS != "ON" && !in_array("FezACML", $specify_list)) {
          Misc::purgeExistingLinks($this->pid, $existingDatastreams);
        }
        $this->getObjectAdminMD();
        if (empty($this->created_date)) {
          $this->created_date = Date_API::getFedoraFormattedDateUTC();
        }
        $this->updated_date = Date_API::getFedoraFormattedDateUTC();
        $this->getXmlDisplayId();
      }
      $pid = $this->pid;

      if (empty($this->xdis_id)) {
        $this->xdis_id = $_POST["xdis_id"];
      }
      $xdis_id = $this->xdis_id;
      $this->getDisplay();
      $display = &$this->display;
      list($array_ptr, $xsd_element_prefix, $xsd_top_element_name, $xml_schema)
          = $display->getXsdAsReferencedArray();
      $xmlObj = '<?xml version="1.0"?>' . "\n";
      $xmlObj .= "<" . $xsd_element_prefix . $xsd_top_element_name . " ";
      $xmlObj .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid); // for the pid, fedora uri etc
      $xmlObj .= $xml_schema;
      $xmlObj .= ">\n";
      // @@@ CK - 6/5/2005 - Added xdis so xml building could search using the xml display ids
      $indexArray = array();

      $xmlObj = Foxml::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "", $indexArray, 0, $this->created_date, $this->updated_date, $this->depositor, $this->assign_usr_id, $this->assign_grp_id);

      $xmlObj .= "</" . $xsd_element_prefix . $xsd_top_element_name . ">";
      $datastreamTitles = $display->getDatastreamTitles($exclude_list, $specify_list);
      Record::insertXML($pid, compact('datastreamTitles', 'exclude_list', 'specify_list', 'xmlObj', 'indexArray', 'existingDatastreams', 'xdis_id'), $ingestObject);
      $this->clearDetails();  // force the details to be refreshed from fedora.
    }

    if (APP_FEDORA_BYPASS == 'ON') {
      AuthIndex::setIndexAuth($this->pid);
    }
    return $this->pid;
  }

  function forceInsertUpdate($edits)
  {
    $wfstatus = &WorkflowStatusStatic::getSession();
    $this->getDisplay();

    $matchFieldsList = $this->display->getMatchFieldsList(array("FezACML"), array());

    $xsdmf_to_use = array();
    $xsdmf_state = array();

    foreach ($matchFieldsList as $xsdmf) {

      if (($xsdmf['xsdmf_html_input'] != '' && $xsdmf['xsdmf_enabled'] == 1)) {

        if ($xsdmf['xsdmf_html_input'] != 'static') {
          $xsdmf_to_use[] = $xsdmf;

        } elseif ($xsdmf['xsdmf_html_input'] == 'static'
            && $xsdmf['xsdmf_show_in_view'] == 1
            && $xsdmf['xsdmf_static_text'] != ''
        ) {

          $xsdmf_to_use[] = $xsdmf;
        }

      } elseif ($xsdmf['xsdmf_title'] == 'state') {
        $xsdmf_state[] = $xsdmf;
      }

    }

    $sta_id = $this->getPublishedStatus();
    $details = $this->getDetails();
    $xdis_id = $this->getXmlDisplayId();
    $current_user_id = Auth::getUserID();
    $internal_notes = InternalNotes::readNote($this->pid);

    $fauxPost = array(
        'id' => $wfstatus->id,
        'workflow_button_1136' => 'Save Changes',
        'cat' => 'update_form',
        'xdis_id' => $xdis_id,
        'sta_id' => $sta_id,
        'userfullname' => Auth::getUserFullName(),
        'state' => $xsdmf_state[0]['xsdmf_static_text'],
        'user_id' => $current_user_id,
        'xsd_display_fields' => $details,
        'internal_notes' => $internal_notes
    );

    $fauxPost = array_replace_recursive($fauxPost, $edits);

    $this->fedoraInsertUpdate(array(), array(), $fauxPost);
    InternalNotes::recordNote($this->pid, $fauxPost['internal_notes']);
  }

  /**
   * @param $mimetype
   * @return array List
   */
  function getIngestTrigger($mimetype)
  {
    $this->getXmlDisplayId();
    $trigger = WorkflowTrigger::getIngestTrigger($this->pid, $this->xdis_id, $mimetype);
    if (!$trigger) {
      $this->getParents();
      if (is_array($this->record_parents)) {
        foreach ($this->record_parents as $ppid) {
          $trigger = WorkflowTrigger::getIngestTrigger($ppid, $this->xdis_id, $mimetype);
          if ($trigger) {
            break;
          }
        }
      }
      if (!$trigger) {
        // get defaults
        $trigger = WorkflowTrigger::getIngestTrigger(-1, $this->xdis_id, $mimetype);
      }
    }
    return $trigger;
  }

  function regenerateImages()
  {
    $pid = $this->pid;

    // get a list of datastreams from the object
    $ds = Fedora_API::callGetDatastreams($pid);

    // ingest the datastreams
    foreach ($ds as $dsKey => $dsTitle) {
      $dsIDName = $dsTitle['ID'];
      if ($dsTitle['controlGroup'] == 'M'
          && (is_numeric(strpos($dsTitle['MIMEType'], "image/")) || is_numeric(strpos($dsTitle['MIMEType'], "video/")) || is_numeric(strpos($dsTitle['MIMEType'], "audio/")))
          && !Misc::hasPrefix($dsIDName, 'preview_')
          && !Misc::hasPrefix($dsIDName, 'web_')
          && !Misc::hasPrefix($dsIDName, 'stream_')
          && !Misc::hasPrefix($dsIDName, 'thumbnail_')
      ) {
        $new_dsID = Foxml::makeNCName($dsIDName);

        // first extract the image and save temporary copy
        if (APP_FEDORA_BYPASS == 'ON') {

          $tmpPth = Misc::getFileTmpPath($dsIDName);
          $fhfs = fopen($tmpPth, 'ab');
          $fileContent = Fedora_API::callGetDatastreamDissemination($pid, $dsIDName);
          fwrite($fhfs, $fileContent['stream']);
          fclose($fhfs);


        } else {
          $urldata = APP_FEDORA_GET_URL . "/" . $pid . "/" . $dsIDName;
          $handle = fopen(Misc::getFileTmpPath($dsIDName), "w");
          Misc::processURL($urldata, false, $handle);
          fclose($handle);
          if ($new_dsID != $dsIDName) {
            // delete and re-ingest - need to do this because sometimes the object made it
            // into the repository even though it's dsID is illegal.
            Fedora_API::callPurgeDatastream($pid, $dsIDName);
          }
          $versionable = APP_VERSION_UPLOADS_AND_LINKS == "ON" ? 'true' : 'false';
          Fedora_API::getUploadLocationByLocalRef($pid, $new_dsID, Misc::getFileTmpPath($dsIDName), $dsIDName,
              $dsTitle['MIMEType'], "M", null, $versionable);

        }
        // preservation metadata
        $presmd_check = Workflow::checkForPresMD($new_dsID);

        if ($presmd_check != false) {
          // strip directory off the name
          $pres_dsID = basename($presmd_check);
          if (Fedora_API::datastreamExists($pid, $pres_dsID)) {
            Fedora_API::callPurgeDatastream($pid, $pres_dsID);
            Fedora_API::getUploadLocationByLocalRef($pid, $pres_dsID, $presmd_check, $presmd_check,
                "text/xml", "M");

          } else {
            Fedora_API::getUploadLocationByLocalRef($pid, $pres_dsID, $presmd_check, $presmd_check,
                "text/xml", "M");
          }
          if (is_file($presmd_check)) {
            $deleteCommand = APP_DELETE_CMD . " " . $presmd_check;
            exec($deleteCommand);
          }
        }
        Exiftool::saveExif($pid, $dsIDName);
        // process it's ingest workflows
        Workflow::processIngestTrigger($pid, $dsIDName, $dsTitle['MIMEType']);
        //clear the managed content file temporarily saved in the APP_TEMP_DIR
        if (is_file(Misc::getFileTmpPath($dsIDName))) {
          $deleteCommand = APP_DELETE_CMD . " " . Misc::getFileTmpPath($dsTitle['ID']);
          exec($deleteCommand);
        }
      }
    }
    if (APP_FEDORA_BYPASS != 'ON') {
      Record::setIndexMatchingFields($pid);
    }
  } // end of function
} // end of class
