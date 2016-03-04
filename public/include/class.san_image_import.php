<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
require_once(__DIR__ . '/class.record.php');

/**
 * Class for images records batch import. Based on class.flint.php
 *
 */
class San_image_import
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
    if (count($metadata_files) === 0) {  // Lets grab .csv if _ingest.csv not found
      foreach ($files as $file) {
        if (Misc::endsWith(strtolower($file), '.csv')) {
          $metadata_files[] = $file;
        }
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
    while (($line = fgetcsv($handle)) !== false) {
      if (array_filter($line)) {  // ignore empty lines
        if (count($headings) === 0) {
          // First line contains the column headings
          $headings = $line;
          $expectedHeadings = array(
              'Filename 1', 'Filename 2', 'Filename 3', 'Creator', 'Title', 'Place of publication', 'Publisher', 'Open Access Status',
              'Publication date', 'Year available', 'Contributor(s)', 'Series', 'Type', 'Format', 'Source',
              'Rights', 'License', 'Abstract/Summary', 'Formatted Abstract/Summary', 'Keywords', 'Geographic co-ordinates',
              'Advisory Statement', 'Acknowledgements', 'Additional Notes'
          );

          foreach ($expectedHeadings as $k) {
            if (!in_array($k, $headings)) {
              if ($k == 'Filename 3') {
                $expectedHeadings = array_diff($expectedHeadings, array('Filename 3'));  //Remove Filename 3 from headings
              } else {
                // Not all expected headings were found, bailing..
                $this->_log->err('San image batch import - while parsing metadata file, the file headings were found to be missing an expected value "' . $k . '".');
                return false;
              }
            }
          };
        } else {
          $values = array();
          $data = $line;
          if (count($data) !== count($headings)) {
            // Headings do not match with values, bailing..
            $this->_log->err('San image batch import - while parsing the metadata file, found a line ' . $data['0'] . ' which does not match file headings ' . count($data) . ' vs ' . count($headings));
            return false;
          }
          for ($i = 0; $i < count($data); $i++) {
            $k = $headings[$i];
            $values[$k] = trim($data[$i]);
          }
          $values['ImportDirectory'] = dirname($file) . '/';
          if (!is_file($values['ImportDirectory'] . $values['Filename 1'])) {
            $this->_log->err('San image batch import - the jpg file ' . $values['Filename 1'] . ' was not found.');
            return false;
          }
          if (!is_file($values['ImportDirectory'] . $values['Filename 2'])) {
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

    // Contributor(s)
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Contributor'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = array(); // Clear any previous values
      $contributors = explode('|', $recData['Contributor(s)']);
      foreach( $contributors as $contributor ) {
        $params['xsd_display_fields'][$xsdmf['xsdmf_id']][] = trim($contributor);
      }
    }

    // SourceField
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Source'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Source'];
    }

    // Series
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Series'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Series'];
    }

    // Geographic area
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Geographic Area'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Geographic co-ordinates'];
    }

    // Advisory Statement
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Advisory Statement'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Advisory Statement'];
    }

    // Acknowledgements
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Acknowledgements'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Acknowledgements'];
    }

    // Additional Notes
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Notes'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Additional Notes'];
    }

    // Type
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Genre'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Type'];
    }

    // Rights
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Rights'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Rights'];
    }

    // Year available
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Date Available'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']]['Year'] = $recData['Year available'];
    }

    // Publisher
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Publisher'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Publisher'];
    }

    // Place of Publication
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Place of Publication'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Place of publication'];
    }

    // Formatted Abstract/Summary
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Formatted Abstract'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Formatted Abstract/Summary'];
    }


    // Abstract/Summary
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Description'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Abstract/Summary'];
    }

    // Open Access Status
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('OA Status'), $xdis_str);
    if ($xsdmf) {
      $refDetails =  XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf['xsdmf_id']);
      $cvo_id = Controlled_Vocab::getIDByTitleAndParentID($recData['Open Access Status'], $refDetails['xsdmf_cvo_id']);
      if ($cvo_id) {
        $params['xsd_display_fields'][$refDetails['xsdmf_id']][] = $cvo_id;
      }
    }

    // License
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('License'), $xdis_str);
    if ($xsdmf) {
      $refDetails =  XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf['xsdmf_id']);
      $cvo_id = Controlled_Vocab::getIDByTitleAndParentID($recData['License'], $refDetails['xsdmf_cvo_id']);
      if ($cvo_id) {
        $params['xsd_display_fields'][$refDetails['xsdmf_id']][] = $cvo_id;
      }
    }

    // Keywords
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Keywords'), $xdis_str);
    if ($xsdmf) {
      $keywords = explode('|', $recData['Keywords']);
      foreach( $keywords as $keyword ) {
        $params['xsd_display_fields'][$xsdmf['xsdmf_id']][] = trim($keyword);
      }
    }

    // Original Format
      $params['xsd_display_fields']['8567'] = $recData['Format'];  //Has no searchkey

    // Remove published date
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Date'), $xdis_str);
    if ($xsdmf && array_key_exists($xsdmf['xsdmf_id'], $params['xsd_display_fields'])) {
      unset($params['xsd_display_fields'][$xsdmf['xsdmf_id']]);
    }

    // Publication date
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Date'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']]['Year'] = $recData['Publication date'];
    }

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
