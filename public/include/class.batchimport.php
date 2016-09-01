<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//

/**
 * Class designed to handle all business logic related to the batch importing of records in the
 * system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.eprints.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.background_process.php");
include_once(APP_INC_PATH . "class.foxml.php");
include_once(APP_INC_PATH . "class.error_handler.php");


/**
 * Batch Import
 */
class BatchImport
{
  const AWS_SAN_IMPORT_PREFIX = 'san_import';

  var $pid;
  var $externalDatastreams;
  var $bgp; // background process object for keeping track of status since batchimport runs in background

  function setBackgroundObject($bgp)
  {
    $this->bgp = $bgp;
  }

  function getEprintsDate($year, $month)
  {
    $eprintsDate = "";
    $eprintMonth = array("jan" => "01",
      "feb" => "02",
      "mar" => "03",
      "apr" => "04",
      "may" => "05",
      "jun" => "06",
      "jul" => "07",
      "aug" => "08",
      "sep" => "09",
      "oct" => "10",
      "nov" => "11",
      "dec" => "12");

    if ($year != "" && $month != "") {
      $eprintsDate = $year . "-" . $eprintMonth[$month] . "-01";

    } elseif ($year != "") {
      $eprintsDate = $year . "-01-01";
    }
    return $eprintsDate;
  }

  function getEprintsPages($pages)
  {
    $pageArray = array(0 => "", 1 => "");
    if (is_numeric(strpos($pages, "-"))) {
      $pageArray = preg_split("/-/", $pages);
    }
    return $pageArray;
  }

  function getEprintsKeywords($keywords)
  {
    $keywords = str_replace(",", ";", $keywords);
    $keywordArray = preg_split("/;/", $keywords);
    return $keywordArray;
  }

  function createMODSSubject($subjectArray, $document_type, $key)
  {
    $xmlDocumentType = "";
    if (is_array(@$subjectArray)) {
      foreach (@$subjectArray as $subject) {
        if (is_array($subject)) {
          $subject = $subject['subjects'];
        }
        if ($subject != "") {
          $cv_title = Controlled_Vocab::getTitleByExternalID($subject);
        }
        if ($cv_title != "" && EPRINTS_SUBJECT_AUTHORITY != "") {
          $xmlDocumentType .= '
                        <mods:subject ID="' . htmlspecialchars($subject) . '" authority="' . EPRINTS_SUBJECT_AUTHORITY . '"><mods:topic>' . $cv_title . '</mods:topic></mods:subject>
                        ';
        } elseif ($cv_title != "") {
          $xmlDocumentType .= '
                        <mods:subject ID="' . htmlspecialchars($subject) . '"><mods:topic>' . $cv_title . '</mods:topic></mods:subject>
                        ';
        } else {
          $xmlDocumentType .= '
                        <mods:subject><mods:topic>' . htmlspecialchars($subject) . '</mods:topic></mods:subject>
                        ';
        }
      }
    }
    return $xmlDocumentType;
  }

  function createMODSName($nameArrayExtra, $key, $name_term)
  {
    $xmlDocumentType = "";
    if (is_array(@$nameArrayExtra[$key])) {
      foreach ($nameArrayExtra[$key] as $name) {
        if (array_key_exists("id", (array)$name)) {
          $xmlDocumentType .= '<mods:name ID="' . $name["id"] . '" authority="The University of Queensland">';
        } else {
          $xmlDocumentType .= '<mods:name>';
        }
        $xmlDocumentType .= '<mods:namePart type="personal">' . htmlspecialchars($name["fullname"]) . '</mods:namePart>
							<mods:role>
							  <mods:roleTerm type="text">' . $name_term . '</mods:roleTerm>
							</mods:role>
						  </mods:name>';
      }
    }
    return $xmlDocumentType;
  }

  /**
   * Method used to ingest a FOXML object xml string into fedora.
   *
   * Developer Note: This was separated into a seperate function as it will be made more complicated in future.
   *
   * @access  public
   * @param   string $xmlObj The string read from the eprints export_xml xml file
   * @return  void
   */
  function handleFOXMLImport($xmlObj)
  {
    // xml is already in fedora object xml format so just add it
    Fedora_API::callIngestObject($xmlObj);

  }

  /**
   * Method used to import a METS xml file.
   *
   * Developer Note: This function works, but probably needs more work to make more user friendly and more testing and work done with METS files for import in general.
   *
   * @access  public
   * @param   string $pid The current persistent identifier
   * @param   string $xmlObj The string read from the eprints export_xml xml file
   * @param   string $xmlBegin The already started xml string for ingestion (the FOXML headers already generated).
   * @param   string $xdis_id The XSD Display ID
   * @return  void
   */
  function handleMETSImport($pid, $xmlObj, $xmlBegin, $xdis_id)
  {
    $externalDatastreams = array();
    // check for oai_dc, if so add it
    $oai_dc = $this->getOAI_DC($xmlObj);
    if ($oai_dc != false) {
      $this->getExternalDatastreams($oai_dc, $externalDatastreams);
      foreach ($externalDatastreams as $ds) {
        if (is_numeric(strpos($ds, "/"))) {
          $short_ds = substr($ds, strrpos($ds, "/") + 1); // take out any nasty slashes from the ds name itself
        }
        $short_ds = str_replace(" ", "_", $short_ds);
        $mimetype = Misc::get_content_type($ds);
        $xmlBegin .= '
                    <foxml:datastream ID="' . $short_ds . '" CONTROL_GROUP="M" STATE="A">
                    <foxml:datastreamVersion ID="' . $short_ds . '.0" MIMETYPE="' . $mimetype . '" LABEL="' . $short_ds . '">
                    <foxml:contentLocation REF="' . $ds . '" TYPE="URL"/>
                    </foxml:datastreamVersion>
                    </foxml:datastream>';
      }

      $oai_dc = $this->stripTag($oai_dc, "<dc:format>");
      $oai_dc = $this->stripTag($oai_dc, "<dc:identifier>");
      $xmlBegin .= '
                <foxml:datastream ID="DC" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                <foxml:datastreamVersion MIMETYPE="text/xml" ID="DC1.0" LABEL="Dublin Core Record">
                <foxml:xmlContent>
                ' . $oai_dc . '
                </foxml:xmlContent>
                </foxml:datastreamVersion>
                </foxml:datastream>';
    } else {
      $xmlBegin .= '
                <foxml:datastream ID="DC" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
                <foxml:datastreamVersion MIMETYPE="text/xml" ID="DC1.0" LABEL="Dublin Core Record">
                <foxml:xmlContent>
                <oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                <dc:title>' . $short_name . '</dc:title>
                <dc:creator/>
                <dc:subject/>
                <dc:description/>
                <dc:publisher/>
                <dc:contributor/>
                <dc:date/>
                <dc:type/>
                <dc:source/>
                <dc:language/>
                <dc:relation/>
                <dc:coverage/>
                <dc:rights/>
                </oai_dc:dc>
                </foxml:xmlContent>
                </foxml:datastreamVersion>
                </foxml:datastream>';
    }

    $xmlBegin .= '
            </foxml:digitalObject>
            ';

    Fedora_API::callIngestObject($xmlBegin);
    foreach ($externalDatastreams as $ds) {
      // add directory to the name
      $presmd_check = Workflow::checkForPresMD($ds); // we are not indexing presMD so just upload the presmd if found
      if ($presmd_check != false) {
        $pres_dsID = basename($presmd_check);
        //				$presmd_check = APP_TEMP_DIR.$presmd_check;
        Fedora_API::getUploadLocationByLocalRef($pid, $pres_dsID, $presmd_check, $presmd_check, "text/xml", "M");
      }
      if (is_numeric(strpos($ds, "/"))) {
        $ds = substr($ds, strrpos($ds, "/") + 1); // take out any nasty slashes from the ds name itself
      }
      $ds = str_replace(" ", "_", $ds);
      //Record::insertIndexMatchingField($pid, 122, 'varchar', $ds); // add the thumbnail to the Fez index
      $mimetype = Misc::get_content_type($ds);
      Workflow::processIngestTrigger($pid, $ds, $mimetype);
    }
    if (APP_FEDORA_BYPASS != 'ON') {
      Record::setIndexMatchingFields($pid);
    }

    return $xmlBegin;
  }

  /**
   * Method used to import a standard file as a datastream to a new FOXML object.
   *
   * Developer Note: This function works, but probably needs more work to make more user friendly and more testing.
   *
   * @access  public
   * @param   string $pid The current persistent identifier
   * @param   string $full_name The full directory path inclusive filename
   * @param   string $short_name The basic filename without the directory path
   * @param   int    $xdis_id The XSD Display ID the object will have.
   * @param   bool   $is_temp_file
   * @param   int    $qat_id The datastream quick auth template rule to apply
   * @return  void
   */
  public static function handleStandardFileImport(
    $pid, $full_name, $short_name, $xdis_id = 0, $is_temp_file = false, $qat_id = -1
  )
  {
    $dsIDName = $short_name;
    $ncName = Foxml::makeNCName($dsIDName);

    if ($is_temp_file) {
      $temp_store = $full_name;
    }
    else {
      $temp_store = APP_TEMP_DIR . $ncName;
      self::getFileContent($full_name, $temp_store);
    }

    $mimetype = Misc::mime_content_type($temp_store);
    if ($mimetype == 'text/xml') {
      $controlgroup = 'X';
    } else {
      $controlgroup = 'M';
    }

    if (Fedora_API::datastreamExists($pid, $ncName)) {
      Fedora_API::callPurgeDatastream($pid, $ncName);
    }

    if (APP_VERSION_UPLOADS_AND_LINKS == "ON") {
      $versionable = "true";
    } else {
      $versionable = "false";
    }

    $did = Fedora_API::getUploadLocationByLocalRef(
      $pid, $ncName, $temp_store, "", $mimetype, $controlgroup, null, $versionable
    );
    Record::generatePresmd($pid, $ncName);
    // Now check for post upload workflow events like thumbnail resizing of images and add them as datastreams if required
    Workflow::processIngestTrigger($pid, $ncName, $mimetype);
    if (is_file($temp_store) && !$is_temp_file) {
      unlink($temp_store);
    }
    if (APP_FEDORA_BYPASS != 'ON') {
      Record::setIndexMatchingFields($pid);
    }

    if (is_numeric($qat_id) && $qat_id != "-1" && $qat_id != -1) {
      if (APP_FEDORA_BYPASS == 'ON') {
        FezACML::updateDatastreamQuickRule($pid, $qat_id, $did);

      } else {
        $xmlObj = FezACML::getQuickTemplateValue($qat_id);
        if ($xmlObj != FALSE) {
          $FezACML_dsID = FezACML::getFezACMLDSName($ncName);
          if (Fedora_API::datastreamExists($pid, $FezACML_dsID)) {
            Fedora_API::callModifyDatastreamByValue($pid, $FezACML_dsID, "A", "FezACML security for datastream - " . $ncName,
              $xmlObj, "text/xml", "true");
          } else {
            Fedora_API::getUploadLocation($pid, $FezACML_dsID, $xmlObj, "FezACML security for datastream - " . $ncName,
              "text/xml", "X", NULL, "true");
          }
        }
      }
    }
  }

  function saveEprintPID($eprint_id, $pid)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "INSERT INTO
				" . APP_TABLE_PREFIX . "eprints_import_pids
			 (
				epr_eprints_id,
				epr_fez_pid,
				epr_date_added
			 ) VALUES (
				" . $db->quote($eprint_id, 'INTEGER') . ",
				" . $db->quote($pid) . ",
				NOW()
			 )";
    try {
      $db->exec($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return -1;
    }
    return 1;
  }

  /**
   * The main method for batch importing. It opens up each file in the specified directory,
   * scans for content type and imports accordingly. It can also handle custom imports based on
   * a selected collection.
   *
   * @access  public
   * @param   array $dsarray
   * @return  void
   */
  function insert($directory, $xdis_id, $collection_pid, $dsarray = null)
  {
    $importClass = $this->getCollectionSpecificImport($xdis_id, $collection_pid);
    $files = $this->getFileList($directory);

    if ($importClass) {
      // Check is .csv file exists
      $metadata_files = array();
      if (count($metadata_files) === 0) {  // Lets grab .csv if _ingest.csv not found
        foreach ($files as $file) {
          if (Misc::endsWith(strtolower($file), '.csv')) {
            $metadata_files[] = $file;
          }
        }
      }
      if (count($metadata_files) != 0) {
        $this->handleCollectionSpecificImport($importClass, $files, $xdis_id, $collection_pid, $dsarray);
      } else {
        $importClass = false; // no csv file!
      }
    }

    if (!$importClass) {
      $ret_id = 3; // standard record type id
      $sta_id = Status::getID("In Creation"); // standard status type id
      $parent_pid = $collection_pid;

      $counter = 0;
      foreach ($files as $file) {
        $counter++;
        $handled_as_xml = FALSE;
        $pid = Fedora_API::getNextPID();
        $short_name = Misc::shortFilename($file);

        if (Misc::endsWith(strtolower($file), '.csv')) {
          $xmlObj = self::getFileContent($file);
          if (is_numeric(strpos($xmlObj, "foxml:digitalObject"))) {
            $this->handleFOXMLImport($xmlObj);
            if (APP_FEDORA_BYPASS != 'ON') {
              Record::setIndexMatchingFields($pid);
            }
            $handled_as_xml = TRUE;
          } else if (is_numeric(strpos($xmlObj, "METS:mets"))) {
            $xmlBegin = $this->ConvertMETSToFOXML($pid, $xmlObj, $collection_pid, $short_name, $xdis_id, $ret_id, $sta_id);
            $this->handleMETSImport($pid, $xmlObj, $xmlBegin, $xdis_id);
            $handled_as_xml = TRUE;
          }
        }
        if (!$handled_as_xml) {
          if ($this->bgp) {
            $this->bgp->setProgress(intval($counter * 100 / count($files)));
            $this->bgp->setStatus($pid . " - " . $short_name);
          }
          // Create the Record in Fedora
          if (empty($dsarray)) {
            // Use default metadata
            $xmlObj = Foxml::GenerateSingleFOXMLTemplate(
              $pid, $parent_pid, $file, $short_name, $xdis_id, $ret_id, $sta_id
            );
            // Insert the generated foxml object
            Fedora_API::callIngestObject($xmlObj);
          } else {
            // Use metadata from a user template
            Record::insertFromTemplate($pid, $xdis_id, $short_name, $dsarray);
          }
          // Add the binary batch import file.
          $this->handleStandardFileImport($pid, $file, $short_name, $xdis_id);
          Record::setIndexMatchingFields($pid);

          if ($this->bgp) {
            $this->bgp->setStatus('Imported ' . $counter . ' files');
          }
        }

      }
    }

    if ($this->bgp) {
      $this->bgp->setProgress(100);
    }
  }

  /**
   * Checks to see if the selected collection PID has a specific import associated with it by checking
   * the keywords on $collection_pid
   *
   * @param $xdis_id
   * @param $collection_pid
   * @return object|bool The import class otherwise false indicating this collection does not have a specific import associated with it
   */
  function getCollectionSpecificImport($xdis_id, $collection_pid)
  {
    $record = new RecordObject($collection_pid);
    $record->getDisplay();
    $details = $record->getDetails();
    $xdis_list = XSD_Relationship::getListByXDIS($record->xdis_id);
    array_push($xdis_list, array("0" => $record->xdis_id));
    $xdis_str = Misc::sql_array_to_string($xdis_list);
    $xsdmf = XSD_HTML_Match::getDetailsBySekIDXDIS_ID(Search_Key::getID('Keywords'), $xdis_str);

    if ($xsdmf && array_key_exists($xsdmf['xsdmf_id'], $details)) {
      $keywords = $details[$xsdmf['xsdmf_id']];
      if (!is_array($keywords)) {
        // Force into an array
        $keywords = array($keywords);
      }
      $search = 'BATCH_IMPORT:';
      foreach ($keywords as $k) {
        if (stripos($k, $search) === 0) {
          $parts = explode(':', $k);
          if (count($parts) === 2) {
            $file = strtolower($parts[1]);
            if (
              preg_match('/^([a-z0-9_]*)$/', $file) &&
              is_file(APP_INC_PATH . 'class.' . $file . '.php')
            ) {
              include_once(APP_INC_PATH . 'class.' . $file . '.php');
              $class = ucfirst($file);
              $import = new $class();
              if (method_exists($import, 'batchImport')) {
                return $import;
              }
            }
          }
        }
      }
    }
    return false;
  }

  /**
   * Handles a custom import based on a selected collection.
   *
   * @access  public
   * @param   object $importClass
   * @param   array $files
   * @param   int $xdis_id
   * @param   string $collection_pid
   * @param   array $dsarray
   * @return  void
   */
  function handleCollectionSpecificImport($importClass, $files, $xdis_id, $collection_pid, $dsarray)
  {
    $importClass->batchImport($this->bgp, $files, $xdis_id, $collection_pid, $dsarray);
    if ($this->bgp) {
      $this->bgp->setProgress(100);
    }
  }

  /**
   * Method used to extra the OAI dublin core metadata from an xml string.
   *
   * @access  public
   * @return  string $xmlObj The xml object
   * @return  string $xmlCut The OAI DC extracted, or boolean false if not found
   */
  function getOAI_DC($xmlObj)
  {
    $tagOpen = '<oai_dc:dc';
    $tagClose = "</oai_dc:dc>";
    $IDPos = stripos($xmlObj, $tagOpen); // stripos is a php5 function
    if (is_numeric($IDPos)) {
      $searchScopeEnd = strpos($xmlObj, $tagClose, $IDPos);
      if (is_numeric($searchScopeEnd)) {
        $startCut = ($IDPos);
        $xmlCut = substr($xmlObj, $startCut, ($searchScopeEnd - $startCut + strlen($tagClose)));
        return $xmlCut;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  /**
   * Method used to remove a specific tag from an xml string.
   *
   * @access  public
   * @return  string $xmlObj The xml object
   * @return  string $tag The tag to be removed
   * @return  string $xmlObj The xml string without the tag
   */
  function stripTag($xmlObj, $tag)
  {
    $tagClose = '\<\/' . substr($tag, 1);
    $xmlObj = preg_replace("/(\\" . $tag . ")(.*?)(" . $tagClose . ")/", "", $xmlObj);
    return $xmlObj;
  }

  /**
   * Method get the external datastreams from an ePrints object.
   *
   * @access  public
   * @return  string $xmlObj The xml object
   * @return  array $externalDatastreams
   * @return  array $externalDatastreams by reference with all the ePrints files (pdfs)
   */
  function getExternalDatastreams($xmlObj, &$externalDatastreams)
  {
    // get all the URLs - especially from ePrints exported METS objects
    // checks DC:Format for http:// urls and returns the urls as an array
    // finds the first one if can and then
    $tagOpen = '<dc:format>';
    $tagClose = "</dc:format>";
    $httpFind = "http://";

    $IDPos = stripos($xmlObj, $tagOpen); // stripos is a php5 function
    if (is_numeric($IDPos)) {
      $searchScopeEnd = strpos($xmlObj, $tagClose, $IDPos);
      if (is_numeric($searchScopeEnd)) {
        $startCut = ($IDPos + strlen($tagOpen));
        $xmlCut = substr($xmlObj, $startCut, ($searchScopeEnd - $startCut));
        $httpPos = strpos($xmlCut, $httpFind);
        if (is_numeric($httpPos)) { // found a url
          $url = substr($xmlCut, $httpPos);
          array_push($externalDatastreams, $url);
        }
        //Remove the used tag from the xml
        $xmlObj = str_replace($tagOpen . $xmlCut . $tagClose, "", $xmlObj);
        // find any others
        $this->getExternalDatastreams($xmlObj, $externalDatastreams);
      }
    }
  }

  /**
   * Method used to convert a METS xml record into a FOXML record.
   *
   * @access  public
   * @param   string $pid The current persistent identifier
   * @param   string $xmlImport the string of the METS xml file
   * @param   string $collection_pid The pid of the collection this will belong to.
   * @param   string $short_name The filename of the file being imported, without the directory path (basic filename)
   * @param   string $xdis_id The XSD Display ID the object will have.
   * @param   string $ret_id The object type ID the object will have.
   * @param   string $sta_id The initial status ID the object will have.
   * @return  string $xmlObj The xml object
   */
  function ConvertMETSToFOXML($pid, $xmlImport, $collection_pid, $short_name, $xdis_id, $ret_id, $sta_id)
  {
    $xmlObj = '<?xml version="1.0" ?>
            <foxml:digitalObject PID="' . $pid . '"
            fedoraxsi:schemaLocation="info:fedora/fedora-system:def/foxml# http://www.fedora.info/definitions/1/0/foxml1-0.xsd" xmlns:fedoraxsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:foxml="info:fedora/fedora-system:def/foxml#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
            <foxml:objectProperties>
            <foxml:property NAME="http://www.w3.org/1999/02/22-rdf-syntax-ns#type" VALUE="FedoraObject"/>
            <foxml:property NAME="info:fedora/fedora-system:def/model#state" VALUE="Active"/>
            <foxml:property NAME="info:fedora/fedora-system:def/model#label" VALUE="Batch Import ' . $short_name . '"/>
            </foxml:objectProperties>
            <foxml:datastream ID="RELS-EXT" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
            <foxml:datastreamVersion MIMETYPE="text/xml" ID="RELS-EXT.0" LABEL="Relationships to other objects">
            <foxml:xmlContent>
            <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
            xmlns:rel="info:fedora/fedora-system:def/relations-external#" xmlns:xsi="http://www.w3.org/2001/XMLSchema">
            <rdf:description rdf:about="info:fedora/' . $pid . '">
            <rel:isMemberOf rdf:resource="info:fedora/' . $collection_pid . '"/>
            </rdf:description>
            </rdf:RDF>
            </foxml:xmlContent>
            </foxml:datastreamVersion>
            </foxml:datastream>
            <foxml:datastream ID="FezMD" VERSIONABLE="true" CONTROL_GROUP="X" STATE="A">
            <foxml:datastreamVersion MIMETYPE="text/xml" ID="Fez1.0" LABEL="Fez extension metadata">
            <foxml:xmlContent>
            <FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">
            <xdis_id>' . $xdis_id . '</xdis_id>
            <sta_id>' . $sta_id . '</sta_id>
            <ret_id>' . $ret_id . '</ret_id>
            </FezMD>
            </foxml:xmlContent>
            </foxml:datastreamVersion>
            </foxml:datastream>
            ';
    return $xmlObj;
  }

  /**
   ** Takes what should be added to the array, it creates the key if not exists
   ** @param array reference
   ** @param string key
   ** @param string value to be added
   **
   ** @return void
   */
  function addToArray(&$array, $key, $value)
  {
    if ($array[$key]) {
      $array[$key] = array_merge((array)$array[$key], (array)$value);
    } else {
      $array[$key] = $value;
    }
  }

  /**
   * Method used to get a list of dir names in the import dir
   *
   * @param   string $subdir
   * @return  mixed The array of dir names or false on error
   */
  public static function getImportDirs($subdir = '')
  {
    $log = FezLog::get();
    $subdir = str_replace('/', '', $subdir);

    if (defined('AWS_S3_ENABLED') && AWS_S3_ENABLED == 'true') {
      return self::getImportDirsFromS3($subdir);
    }

    $dirs = array();

    $parent = empty($subdir) ? APP_SAN_IMPORT_DIR : APP_SAN_IMPORT_DIR . '/' . $subdir;
    if ($handle = opendir($parent)) {
      while (FALSE !== ($dir = readdir($handle))) {
        if (!(!is_dir($parent . $dir) || $dir == '.' || $dir == '..')) {
          $dirs[$dir] = $dir;
        }
      }
      closedir($handle);
    } else {
      $log->err('Unable to open directory');
    }

    return $dirs;
  }

  /**
   * Method used to get a list of file names in the import dir
   *
   * @param   string $subdir
   * @return  mixed The array of file names or false on error
   */
  public static function getImportFiles($subdir = '')
  {
    $log = FezLog::get();
    $subdir = str_replace('/', '', $subdir);

    if (defined('AWS_S3_ENABLED') && AWS_S3_ENABLED == 'true') {
      return self::getFileListFromS3($subdir);
    }

    $dirs = array();

    $parent = empty($subdir) ? APP_SAN_IMPORT_DIR : APP_SAN_IMPORT_DIR . '/' . $subdir;
    if ($handle = opendir($parent)) {
      while (FALSE !== ($dir = readdir($handle))) {
        if (!(!is_dir($parent . $dir) || $dir == '.' || $dir == '..')) {
          $dirs[$dir] = $dir;
        }
      }
      closedir($handle);
    } else {
      $log->err('Unable to open directory');
    }

    return $dirs;
  }

  /**
   * Method used to get a list of file names from an S3 bucket
   *
   * @param   string $subdir
   * @return  mixed The array of file names
   */
  private static function getImportDirsFromS3($subdir = '')
  {
    $aws = new AWS(AWS_S3_SAN_IMPORT_BUCKET);

    $return = [];
    $prefix = $aws->createPath((empty($subdir) ? self::AWS_SAN_IMPORT_PREFIX : self::AWS_SAN_IMPORT_PREFIX . '/' . $subdir), '');
    $dirs = $aws->listObjectsInBucket($prefix);

    foreach ($dirs as $dir) {
      // Don't skip the files, check all the things
      if ($dir['Size'] !== 0) {
        $pattern = str_replace('/', '\/', $prefix);
        preg_match('/^' . $pattern . '\/([^\/]+)\//', $dir['Key'], $matches);
        if (count($matches) === 2) {
          $d = trim($matches[1], '/');
          if (!empty($d) && !array_key_exists($d, $return)) {
            $return[$d] = $d;
          }
        }
      }
    }

    return $return;
  }

  /**
   * @param String $directory
   * @return array
   */
  private function getFileList($directory)
  {
    if (defined('AWS_S3_ENABLED') && AWS_S3_ENABLED == 'true') {
      return self::getFileListFromS3($directory);
    }
    return Misc::getFileList(APP_SAN_IMPORT_DIR. '/'. $directory, true, true);
  }

  /**
   * @param String $directory
   * @return array
   */
  private static function getFileListFromS3($directory)
  {
    $aws = new AWS(AWS_S3_SAN_IMPORT_BUCKET);

    $return = [];
    $files = $aws->listObjectsInBucket(self::AWS_SAN_IMPORT_PREFIX . '/' . $directory);

    foreach ($files as $file) {
      // Skip the folders
      if ($file['Size'] !== 0) {
        $return[$file['Key']] = $file['Key'];
      }
    }
    return $return;
  }

  /**
   * @param string $file
   * @param string $saveAs
   * @return string
   */
  public static function getFileContent($file, $saveAs = '')
  {
    $log = FezLog::get();
    if (defined('AWS_ENABLED') && AWS_ENABLED == 'true') {
      $aws = new AWS(AWS_S3_SAN_IMPORT_BUCKET);

      $params = [];
      if (! empty($saveAs)) {
        $params['SaveAs'] = $saveAs;
      }

      return $aws->getFileContent($file, '', $params);
    }

    if (! empty($saveAs)) {
      copy($file, $saveAs);
      return "";
    } else {
      return file_get_contents($file);
    }
  }
}
