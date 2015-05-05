<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
require_once(__DIR__ . '/class.record.php');

/**
 * Class that contains utilities for Flint.
 *
 */
class Flint
{
  private $_importBgp;
  private $_log;

  public function __construct() {

  }

  public static function returnFlintLanguages() {
    $log = FezLog::get();
    $db = DB_API::get();
    $stmt = "SELECT B.*, COUNT(rek_subject) AS record_count FROM " . APP_TABLE_PREFIX . "controlled_vocab_relationship
        LEFT JOIN " . APP_TABLE_PREFIX . "controlled_vocab AS A  ON cvo_id = cvr_parent_cvo_id
        LEFT JOIN " . APP_TABLE_PREFIX . "controlled_vocab AS B ON B.cvo_id = cvr_child_cvo_id
        LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_subject ON rek_subject = cvr_child_cvo_id
        LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_subject_pid = rek_pid AND rek_status = '2'
        WHERE A.cvo_title = 'AIATSIS codes' GROUP BY B.cvo_id";

    try {
      $res = $db->fetchAll($stmt);
    }
    catch (Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  public static function returnInterviewees() {
    $log = FezLog::get();
    $db = DB_API::get();
    $stmt = "SELECT rek_contributor, COUNT(rek_contributor) AS interviewee_count FROM " . APP_TABLE_PREFIX . "record_search_key_contributor
    LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_ismemberof ON rek_ismemberof_pid = rek_contributor_pid
    WHERE rek_ismemberof IN ('".APP_FLINT_COLLECTION."')
    GROUP BY rek_contributor";

    try {
      $res = $db->fetchAll($stmt);
    }
    catch (Exception $ex) {
      $log->err($ex);
      return false;
    }
    return $res;
  }

  /**
   * Handles the import of Flint records from the SAN when the Flint collection
   * was selected in a batch import workflow
   *
   * @param BackgroundProcess $bgp The background process which triggered this import
   * @param string $directory The SAN directory of files to import
   * @param int $xdis_id The type of document
   * @param string $collection_pid The collection being imported into
   * @param array $dsarray An array of template fields
   * @return bool True if the import succeeded, otherwise false
   */
  public function batchImport(&$bgp, $directory, $xdis_id, $collection_pid, $dsarray)
  {
    $this->_log = FezLog::get();

    // Expect to be run in the context of a background process.
    if (! $bgp || ! $bgp instanceof BackgroundProcess) {
      $this->_log->err('Flint batch import - not running within a background process.');
      return false;
    }
    $this->_importBgp = $bgp;

    // Expect a valid import directory.
    if (! is_dir($directory)) {
      $error = 'Flint batch import - the batch import directory "' . $directory . '" does not exist.';
      $this->_importBgp->setStatus($error);
      $this->_log->err($error);
      return false;
    }

    // Get a list of file names ending in *_metadata.txt.
    $files = Misc::getFileList($directory, true, true);
    $metadata_files = array();
    foreach ($files as $file) {
      if (Misc::endsWith(strtolower($file), '_metadata.txt')) {
        $metadata_files[] = $file;
      }
    }
    if (count($metadata_files) === 0) {
      $error = 'Flint batch import - no metadata files found in directory "' . $directory . '".';
      $this->_importBgp->setStatus($error);
      $this->_log->err($error);
      return false;
    }

    // Loop through each metadata file and parse the data to import. Bail if there is a parse error.
    $importData = array();
    foreach ($metadata_files as $file) {
      $parsedData = $this->_parseBatchImportMetadataFile($file);
      if ($parsedData === false) {
        $error = 'Flint batch import - failed to parse metadata file "' . $file . '"';
        $this->_importBgp->setStatus($error);
        $this->_log->err($error);
        return false;
      } else if (count($parsedData) === 0) {
        $error = 'Flint batch import - no data to import';
        $this->_importBgp->setStatus($error);
        $this->_log->err($error);
        return false;
      }
      $importData[] = $parsedData;
    }

    // Loop through parsed data and import.
    $count = 1;
    foreach ($importData as $data) {
      $this->_importBgp->setProgress(intval(($count / count($importData)) * 100));
      foreach ($data as $d) {
        $pid = $this->_importRecord($collection_pid, $xdis_id, $dsarray, $d);
        $this->_importBgp->setStatus('Created record: ' . $pid);
      }
      $count++;
    }

    return true;
  }

  /**
   * Parses a metadata file and returns the data in an array ready for ingesting.
   *
   * @param string $file The file to parse
   * @return array|bool An array of data for ingesting, unless a parse error occurred in which case false is returned
   */
  private function _parseBatchImportMetadataFile($file)
  {
    $importData = array();
    if (! is_file($file)) {
      return false;
    }
    $handle = fopen($file, 'r');
    if (! $handle) {
      return false;
    }

    $headings = array();
    while (($line = fgets($handle)) !== false) {
      if (count($headings) === 0) {
        // First line contains the column headings
        $headings = explode('|', trim($line));
        $expectedHeadings = array(
          'Title', 'Genre', 'ContributorIdentities', 'SourceField', 'Duration', 'RecordingDate',
          'RecordingPlace', 'GroupID', 'SnippetSoundFileWAV', 'SnippetSoundFileMP3'
        );
        foreach ($expectedHeadings as $k) {
          if (! in_array($k, $headings)) {
            // Not all expected headings were found, bailing..
            $this->_log->err('Flint batch import - while parsing metadata file, the file headings were found to be missing an expected value "' . $k . '".');
            return false;
          }
        };
      }
      else {
        $values = array();
        $data = explode('|', $line);
        if (count($data) !== count($headings)) {
          // Headings do not match with values, bailing..
          $this->_log->err('Flint batch import - while parsing the metadata file, found a line which does not match file headings.');
          return false;
        }
        for ($i = 0; $i < count($data); $i++) {
          $k = $headings[$i];
          $values[$k] = trim($data[$i]);
        }
        $values['ImportDirectory'] = dirname($file) . '/';
        $snippetFileParts = explode('.', $values['SnippetSoundFileWAV']);
        $transcriptFile = $values['ImportDirectory'] . $snippetFileParts[0] . '.txt';
        if (is_file($transcriptFile)) {
          $values['Transcript'] = nl2br(trim(Misc::getFileContents($transcriptFile)));
        } else {
          $this->_log->err('Flint batch import - the transcript file was not found.');
          return false;
        }
        if (! is_file($values['ImportDirectory'] . $values['SnippetSoundFileWAV'])) {
          $this->_log->err('Flint batch import - the WAV sound file was not found.');
          return false;
        }
        if (! is_file($values['ImportDirectory'] . $values['SnippetSoundFileMP3'])) {
          $this->_log->err('Flint batch import - the MP3 sound file was not found.');
          return false;
        }
        $importData[] = $values;
      }
    }
    fclose($handle);

    return $importData;
  }

  /**
   * Imports a record
   *
   * @param int $collection_pid The collection the record will be added to
   * @param int $xdis_id The type of document
   * @param array $dsarray An array of template fields
   * @param array $recData An array of data to import
   * @return string The PID of the imported record
   */
  private function _importRecord($collection_pid, $xdis_id, $dsarray, $recData)
  {
    $params = $dsarray['rawPost'];
    $params['xdis_id'] = $xdis_id;
    $params['sta_id'] = 1; // unpublished record
    $params['collection_pid'] = $collection_pid;

    $xdis_list = XSD_Relationship::getListByXDIS($xdis_id);
    array_push($xdis_list, array("0" => $xdis_id));
    $xdis_str = Misc::sql_array_to_string($xdis_list);

    // Title
    // Replace template placeholder title
    foreach ($params['xsd_display_fields'] as $k => $v) {
      if ($v == '__makeInsertTemplate_DCTitle__') {
        $params['xsd_display_fields'][$k] = $recData['Title'];
      }
    }
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Title'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Title'];
    }

    // Creator
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Author'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = array(); // Clear any previous values
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']][] = 'Flint, Elwyn Henry';
    }

    // Genre
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Alternate Genre'), $xdis_str);
    if ($xsdmf) {
      $cvo_id = Controlled_Vocab::getIDByTitleAndParentID($recData['Genre'], $xsdmf['xsdmf_cvo_id']);
      if ($cvo_id) {
        $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = array(); // Clear any previous values
        $params['xsd_display_fields'][$xsdmf['xsdmf_id']][] = $cvo_id;
      }
    }

    // ContributorIdentities
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Contributor'), $xdis_str);
    if ($xsdmf) {
      // Split input from file using comma
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = array(); // Clear any previous values
      $contributors = explode(',', $recData['ContributorIdentities']);
      foreach ($contributors as $c) {
        $params['xsd_display_fields'][$xsdmf['xsdmf_id']][] = trim($c);
      }
    }

    // SourceField
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Source'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['SourceField'];
    }

    // Duration
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Length'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Duration'];
    }

    // RecordingDate
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Date Recorded'), $xdis_str);
    if ($xsdmf) {
      $recDate = explode('/', $recData['RecordingDate']);
      $year = $recDate[2];
      if (strlen($year) === 2) {
        $year = '19' . $year;
      }
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']]['Year'] = $year;
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']]['Month'] = $recDate[1];
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']]['Day'] = $recDate[0];
    }

    // RecordingPlace
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Location'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['RecordingPlace'];
    }

    // Transcript
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Transcript'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Transcript'];
    }

    // Series
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Series'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = 'Elwyn Flint collection, UQFL173';
    }

    // Type
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Genre'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = 'audio/wav'; // Hardcoded
    }

    // Group
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Identifier'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = 'Group ' . $recData['GroupID'];
    }

    /* Language (AIATSIS code) - where is this coming from? It's not in the metadata file..
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Subject'), $xdis_str);
    if ($xsdmf) {
      $cvo_id = Controlled_Vocab::getIDByTitleAndParentID($recData['Language'], $xsdmf['xsdmf_cvo_id']);
      if ($cvo_id) {
        $params['xsd_display_fields'][$xsdmf['xsdmf_id']][] = $cvo_id;
      }
    }*/

    // Not sure whether these will be in the import
    /*
    $xsdmf_id = XSD_HTML_Match::getXSDMFIDByTitleXDIS_ID('Abstract/Summary', $xdis_id);
    if ($xsdmf_id) {
      $params['xsd_display_fields'][$xsdmf_id] = 'The Flint papers comprise written documents..';
    }

    $xsdmf_id = XSD_HTML_Match::getXSDMFIDByTitleXDIS_ID('Keyword', $xdis_id);
    if ($xsdmf_id) {
      $params['xsd_display_fields'][$xsdmf_id][] = 'Aboriginal Australians -- Languages';
      $params['xsd_display_fields'][$xsdmf_id][] = 'Queensland Speech Survey';
      $params['xsd_display_fields'][$xsdmf_id][] = 'Culture, stories, people';
    }

    $xsdmf_id = XSD_HTML_Match::getXSDMFIDByTitleXDIS_ID('Acknowledgements', $xdis_id);
    if ($xsdmf_id) {
      $params['xsd_display_fields'][$xsdmf_id] = 'This project supported by the Australia National Data Service [MODC 23]';
    }
    */

    $record = new RecordObject();
    $pid = $record->fedoraInsertUpdate(array(), array(), $params);

    BatchImport::handleStandardFileImport(
      $pid,
      $recData['ImportDirectory'] . $recData['SnippetSoundFileWAV'],
      $recData['SnippetSoundFileWAV'],
      $xdis_id
    );
    BatchImport::handleStandardFileImport(
      $pid,
      $recData['ImportDirectory'] . $recData['SnippetSoundFileMP3'],
      $recData['SnippetSoundFileMP3'],
      $xdis_id
    );

    return $pid;
  }
}
