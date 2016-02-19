<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
require_once(__DIR__ . '/class.record.php');

/**
 * Class for images records batch import. Based on class.flint.php
 *
 */
class Bulk_Image_Import
{
  private $_importBgp;
  private $_log;

  public function __construct() {

  }


  /**
   * Handles the import of images from the SAN when the image import
   * collection was selected in a batch import workflow
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
      $this->_log->err('San image batch import - not running within a background process.');
      return false;
    }
    $this->_importBgp = $bgp;

    // Expect a valid import directory.
    if (! is_dir($directory)) {
      $error = 'San image batch import - the batch import directory "' . $directory . '" does not exist.';
      $this->_importBgp->setStatus($error);
      $this->_log->err($error);
      return false;
    }

    // Check an Image type (MODS 1.0) was selected
    $display = XSD_Display::getDetails($xdis_id);
    if (! ($display && $display['xdis_title'] === 'Image' && $display['xdis_version'] === 'MODS 1.0')) {
      $error = 'San image batch import - invalid document type was selected, expected Image/MODS 1.0';
      $this->_importBgp->setStatus($error);
      $this->_log->err($error);
      return false;
    }

    // Get a list of file names ending in *_ingest.csv.
    $files = Misc::getFileList($directory, true, true);
    $metadata_files = array();
    foreach ($files as $file) {
      if (Misc::endsWith(strtolower($file), '_ingest.csv')) {
        $metadata_files[] = $file;
      }
    }
    if (count($metadata_files) === 0) {
      $error = 'San image batch import - no metadata files found in directory "' . $directory . '".';
      $this->_importBgp->setStatus($error);
      $this->_log->err($error);
      return false;
    }

    // Loop through each metadata file and parse the data to import. Bail if there is a parse error.
    $importData = array();
    foreach ($metadata_files as $file) {
      $parsedData = $this->_parseBatchImportMetadataFile($file);
      if ($parsedData === false) {
        $error = 'San image batch import  - failed to parse metadata file "' . $file . '"';
        $this->_importBgp->setStatus($error);
        $this->_log->err($error);
        return false;
      } else if (count($parsedData) === 0) {
        $error = 'San image batch import  - no data to import';
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
        $headings = str_getcsv($line);
        $expectedHeadings = array(
            'Filename 1', 'Filename 2', 'Filename 3', 'Creator', 'Title', 'Place of publication', 'Publisher', 'Open Access Status',
            'Publication date', 'Year available', 'Contributor 1', 'Contributor 2', 'Series', 'Type', 'Format', 'Source',
            'Rights', 'License', 'Abstract/Summary', 'Formatted Abstract/Summary', 'Keywords', 'Geographic co-ordinates',
            'Advisory Statement', 'Acknowledgements', 'Additional Notes'
        );

        foreach ($expectedHeadings as $k) {
          if (! in_array($k, $headings)) {
            if ($k == 'Filename 3') {
              $expectedHeadings = array_diff($expectedHeadings, array('Filename 3'));  //Remove Filename 3 from headings
            } else {
              // Not all expected headings were found, bailing..
              $this->_log->err('San image batch import - while parsing metadata file, the file headings were found to be missing an expected value "' . $k . '".');
              return false;
            }
          }
        };
      }
      else {
        $values = array();
        $data = str_getcsv($line);
        if (count($data) !== count($headings)) {
          // Headings do not match with values, bailing..
          $this->_log->err('San image batch import - while parsing the metadata file, found a line '.  $data['0'] .' which does not match file headings.');
          return false;
        }
        for ($i = 0; $i < count($data); $i++) {
          $k = $headings[$i];
          $values[$k] = trim($data[$i]);
        }
        $values['ImportDirectory'] = dirname($file) . '/';
        if (! is_file($values['ImportDirectory'] . $values['Filename 1'])) {
          $this->_log->err('San image batch import - the jpg file ' . $values['Filename 1'] . ' was not found.');
          return false;
        }
        if (! is_file($values['ImportDirectory'] . $values['Filename 2'])) {
          $this->_log->err('San image batch import - the tif file ' . $values['Filename 2'] . '  was not found.');
          return false;
        }
        if (!empty($values['Filename 3']) && !is_file($values['ImportDirectory'] . $values['Filename 3'])) {
          $this->_log->err('San image batch import - the 2nd tif file ' . $values['Filename 3'] . '  was not found.');
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
    $params['sta_id'] = 2;
    $params['collection_pid'] = $collection_pid;

    $xdis_list = XSD_Relationship::getListByXDIS($xdis_id);
    array_push($xdis_list, array("0" => $xdis_id));
    $xdis_str = Misc::sql_array_to_string($xdis_list);

    // Title
    $recData['Title'] = rtrim($recData['Title'], '.');
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
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']][] = $recData['Creator'];
    }

    // Contributors
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Contributor'), $xdis_str);
    if ($xsdmf) {
      // Split input from file using comma
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = array(); // Clear any previous values
      $contributors[] = $recData['Contributor 1'];
      $contributors[] = $recData['Contributor 2'];
      foreach ($contributors as $c) {
        $params['xsd_display_fields'][$xsdmf['xsdmf_id']][] = $c;
      }
    }

    //////------------------------------------------------------------------------------------------------------------------------

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
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Series'];
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

    // Link
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Link'), $xdis_str);
    $xsdmfDesc = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Link Description'), $xdis_str);
    if ($xsdmf && $xsdmfDesc) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = array();
      $params['xsd_display_fields'][$xsdmfDesc['xsdmf_id']] = array();
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']][0] = 'https://www.library.uq.edu.au/fryer-library/indigenous-voices';
      $params['xsd_display_fields'][$xsdmfDesc['xsdmf_id']][0] = 'Browse Indigenous language resources from this collection online';
    }

    // Remove published date
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Date'), $xdis_str);
    if ($xsdmf && array_key_exists($xsdmf['xsdmf_id'], $params['xsd_display_fields'])) {
      unset($params['xsd_display_fields'][$xsdmf['xsdmf_id']]);
    }
    //////------------------------------------------------------------------------------------------------------------------------
    $record = new RecordObject();
    $pid = $record->fedoraInsertUpdate(array(), array(), $params);

    BatchImport::handleStandardFileImport(
        $pid,
        $recData['ImportDirectory'] . $recData['Filename 1'],
        $recData['Filename 1'],
        $xdis_id
    );
    BatchImport::handleStandardFileImport(
        $pid,
        $recData['ImportDirectory'] . $recData['Filename 2'],
        $recData['Filename 2'],
        $xdis_id
    );

    if (!empty($recData['Filename 3'])) {
      BatchImport::handleStandardFileImport(
          $pid,
          $recData['ImportDirectory'] . $recData['Filename 3'],
          $recData['Filename 3'],
          $xdis_id
      );
    }

    return $pid;
  }
}
