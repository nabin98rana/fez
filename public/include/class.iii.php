<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
require_once(__DIR__ . '/class.record.php');

/**
 * Class for III Catalogue records batch import.
 *
 */
class Iii
{
  private $_importBgp;
  private $_log;

  public function __construct() {

  }

  /**
   * Handles the import of catalogue records from the SAN when the catalogue import
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
      $this->_log->err('III batch import - not running within a background process.');
      return false;
    }
    $this->_importBgp = $bgp;

    // Expect a valid import directory.
    if (! is_dir($directory)) {
      $error = 'III batch import - the batch import directory "' . $directory . '" does not exist.';
      $this->_importBgp->setStatus($error);
      $this->_log->err($error);
      return false;
    }

    // Check an Thesis type (MODS 1.0) was selected
    $display = XSD_Display::getDetails($xdis_id);
    if (! ($display && $display['xdis_title'] === 'Thesis' && $display['xdis_version'] === 'MODS 1.0')) {
      $error = 'III batch import - invalid document type was selected, expected Thesis/MODS 1.0';
      $this->_importBgp->setStatus($error);
      $this->_log->err($error);
      return false;
    }

    // Get a list of thesis in the import directory
    $files = array();
    foreach (glob($directory . "/*.pdf") as $file) {
      $files[] = $file;
    }
    if (count($files) === 0) {
      $error = 'III batch import - no thesis files found in directory "' . $directory . '".';
      $this->_importBgp->setStatus($error);
      $this->_log->err($error);
      return false;
    }

    $data = $this->_getDataFromIii($files);
    $count = 1;
    foreach ($data as $callNo => $d) {
      $this->_importBgp->setProgress(intval(($count / count($data)) * 100));
      $pid = $this->_importRecord($collection_pid, $xdis_id, $dsarray, $d);
      if ($pid) {
        $this->_importBgp->setStatus('Created record: ' . $pid);
      }
      $count++;
    }

    return true;
  }

  /**
   * Using the files determine a Call No. and retrieve data about the thesis from the catalogue
   *
   * @param array $files The files to retrieve
   * @return array An array of data for ingesting
   */
  private function _getDataFromIii($files)
  {
    $importData = array();

    foreach ($files as $file) {
      $data = array();
      $data['Error'] = '';
      $data['ImportDirectory'] = dirname($file) . '/';
      $data['ThesisFilenames'] = array(basename($file));
      $data['CallNo'] = str_ireplace('.pdf', '', $data['ThesisFilenames'][0]);
      $data['Institution'] = 'The University of Queensland';
      $data['ErrorFile'] = $data['ImportDirectory'] . $data['CallNo'] . '.error.txt';
      if (stripos($data['CallNo'], '_V') !== FALSE) {
        // Multiple volumes
        $data['CallNo'] = explode('_', $data['CallNo'])[0];
        if (array_key_exists($data['CallNo'], $importData)) {
          // Just add the extra file
          $importData[$data['CallNo']]['ThesisFilenames'][] = $data['ThesisFilenames'][0];
          continue;
        }
      }

      $searchUrl = 'http://library.uq.edu.au/search~S7/?searchtype=m&searcharg=' . $data['CallNo'];
      $record = Misc::getFileURL($searchUrl);
      preg_match('#id="recordnum" href="/record=b(\d+)#i', $record, $matches);
      if (! $matches) {
        // Possibly multiple results, try to find the exact result for this CallNo
        preg_match('#href="(.*)">' . $data['CallNo'] . '</a>#i', $record, $m);
        if (! $m) {
          $data['Error'] .= 'Failed to search on the call no. URL was: ' . $searchUrl;
          $this->_logErrorToFile($data);
          continue;
        }
        $record = Misc::getFileURL('http://library.uq.edu.au' . $m[1]);
        preg_match('#id="recordnum" href="/record=b(\d+)#i', $record, $matches);
        if (! $matches) {
          $data['Error'] .= 'Failed to search on the call no. URL was: ' . $searchUrl;
          $this->_logErrorToFile($data);
          continue;
        }
      }
      $data['BibNo'] = 'b' . $matches[1];
      $recordUrl = 'http://library.uq.edu.au/xrecord=' . $data['BibNo'];
      $recordXml = Misc::getFileURL($recordUrl);
      $xml = @simplexml_load_string($recordXml);
      if (!$xml) {
        $data['Error'] .= 'Failed to load the record XML in the catalogue at URL: ' . $recordUrl;
        $this->_logErrorToFile($data);
        continue;
      }
      $notFound = (string) @$xml->xpath('/IIIRECORD/NULLRECORD')[0]->MESSAGE[0];
      if ($notFound) {
        $data['Error'] .= 'The record was not found at URL: ' . $recordUrl;
        $this->_logErrorToFile($data);
        continue;
      }

      $author = (string) @$xml->xpath('/IIIRECORD/VARFLD/MARCINFO[MARCTAG="100"]/../MARCSUBFLD[SUBFIELDINDICATOR="a"]')[0]->SUBFIELDDATA[0];
      if ($author) {
        $data['Author'] = rtrim($author, '. ');
      } else {
        $data['Error'] .= 'Author missing in record';
      }

      $title = '';
      $titleA = (string) @$xml->xpath('/IIIRECORD/VARFLD/HEADER[TAG="TITLE"]/../MARCSUBFLD[SUBFIELDINDICATOR="a"]')[0]->SUBFIELDDATA[0];
      if ($titleA) {
        $title .= $titleA;
      }
      $titleB = (string) @$xml->xpath('/IIIRECORD/VARFLD/HEADER[TAG="TITLE"]/../MARCSUBFLD[SUBFIELDINDICATOR="b"]')[0]->SUBFIELDDATA[0];
      if ($titleB) {
        $title .= ' ' . $titleB;
      }
      if ($title) {
        $data['Title'] = rtrim($title, '/ ');
      } else {
        $data['Error'] .= 'Title missing in record';
      }

      $school = (string) @$xml->xpath('/IIIRECORD/VARFLD/MARCINFO[MARCTAG="610"]/../MARCSUBFLD[SUBFIELDINDICATOR="b"]')[0]->SUBFIELDDATA[0];
      if ($school) {
        $data['School'] = rtrim($school, '. ');
      } else {
        $data['Error'] .= 'School missing in record';
      }

      $subjects = @$xml->xpath('/IIIRECORD/VARFLD/HEADER[TAG="SUBJECT"]/..');
      $data['Subjects'] = array();
      if ($subjects) {
        $s = array();
        foreach ($subjects as $subject) {
          $skip = FALSE;
          $subjectText = '';
          foreach ($subject->MARCSUBFLD as $sub) {
            $text = rtrim((string) $sub->SUBFIELDDATA[0], '. ');
            if (stripos('University of Queensland', $text) !== FALSE) {
              $skip = TRUE;
            }
            else {
              if ($subjectText) {
                $subjectText .= ' - ';
              }
              $subjectText .= $text;
            }
          }
          if (!$skip) {
            $s[] = $subjectText;
          }
        }
        $data['Subjects'] = $s;
      }

      $pubDate = (string) @$xml->xpath('/IIIRECORD/VARFLD/MARCINFO[MARCTAG="260"]/../MARCSUBFLD[SUBFIELDINDICATOR="c"]')[0]->SUBFIELDDATA[0];
      if ($pubDate) {
        $data['PublicationDate'] = rtrim($pubDate, '. ');
        $data['CollectionYear'] = $data['PublicationDate'];
      } else {
        $data['Error'] .= 'Collection year missing in record';
      }

      $thesisType = (string) @$xml->xpath('/IIIRECORD/VARFLD/MARCINFO[MARCTAG="610"]/../MARCSUBFLD[SUBFIELDINDICATOR="x"]')[1]->SUBFIELDDATA[0];
      if ($thesisType) {
        $data['ThesisType'] = rtrim($thesisType, '. ');
      } else {
        $data['Error'] .= 'Thesis type missing in record';
      }

      $pages = (string) @$xml->xpath('/IIIRECORD/VARFLD/MARCINFO[MARCTAG="300"]/../MARCSUBFLD[SUBFIELDINDICATOR="a"]')[0]->SUBFIELDDATA[0];
      if ($pages) {
        $pages = preg_split('/[^\d]/', $pages);
        $data['TotalPages'] = $pages[0];
      } else {
        $data['Error'] .= 'Total pages missing in record';
      }

      $notes = (string) @$xml->xpath('/IIIRECORD/VARFLD/MARCINFO[MARCTAG="246"]/../MARCSUBFLD')[0]->SUBFIELDDATA[0];
      if ($notes) {
        $data['AdditionalNotes'] = rtrim($notes, '. ');
      }

      if (empty($data['Error'])) {
        $importData[$data['CallNo']] = $data;
      } else {
        $this->_logErrorToFile($data);
      }
    }

    return $importData;
  }

  /**
   * Writes import errors to a file
   *
   * @param Array $data The data to log (which contains the error message in the array key 'Error')
   */
  private function _logErrorToFile($data)
  {
    $message = $data['Error'] . "\n\n" . print_r($data, true);
    file_put_contents($data['ErrorFile'], $message);
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

    // Author
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Author'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = array(); // Clear any previous values
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']][] = $recData['Author'];
    }

    // School, Centre or Institute
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Org Unit Name'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['School'];
    }

    // Institution
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Org Name'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Institution'];
    }

    // Thesis Type
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Genre Type'), $xdis_str);
    $addedThesisType = false;
    if ($xsdmf) {
      $options = XSD_HTML_Match::getOptions($xsdmf['xsdmf_id']);
      foreach ($options as $id => $mf) {
        if (stripos($mf, $recData['ThesisType']) === 0) {
          $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $id;
          $addedThesisType = true;
        }
      }
    }
    if (! $addedThesisType) {
      $recData['Error'] .= 'Unable to add a thesis type.';
    }

    // Total Pages
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Total Pages'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['TotalPages'];
    }

    // Collection Year
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Collection Year'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']]['Year'] = $recData['CollectionYear'];
    }

    // Keywords
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Keywords'), $xdis_str);
    if ($xsdmf) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = array(); // Clear any previous values
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['Subjects'];
    }

    // Publication date
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Date'), $xdis_str);
    if ($xsdmf && array_key_exists($xsdmf['xsdmf_id'], $params['xsd_display_fields'])) {
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = array(); // Clear any previous values
      $params['xsd_display_fields'][$xsdmf['xsdmf_id']]['Year'] = $recData['PublicationDate'];
    }

    // Additional Notes
    if (array_key_exists('AdditionalNotes', $recData)) {
      $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Notes'), $xdis_str);
      if ($xsdmf) {
        $params['xsd_display_fields'][$xsdmf['xsdmf_id']] = $recData['AdditionalNotes'];
      }
    }

    // Remove subjects
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Supervisor'), $xdis_str);
    if ($xsdmf && array_key_exists($xsdmf['xsdmf_id'], $params['xsd_display_fields'])) {
      unset($params['xsd_display_fields'][$xsdmf['xsdmf_id']]);
    }

    // Remove supervisor
    unset($params['xsd_display_fields'][6834]);

    if (! empty($recData['Error'])) {
      $this->_logErrorToFile($recData);
      return false;
    }

    $record = new RecordObject();
    $pid = $record->fedoraInsertUpdate(array(), array(), $params);

    $count = 0;
    foreach ($recData['ThesisFilenames'] as $file) {
      BatchImport::handleStandardFileImport(
        $pid,
        $recData['ImportDirectory'] . $file,
        $file,
        $xdis_id
      );
      if ($count === 0) {
        // Set file description on the first file
        Record::updateDatastreamLabel($pid, $file, "Thesis (fulltext)");
      }
      $count++;
    }

    return $pid;
  }
}
