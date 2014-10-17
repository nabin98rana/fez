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

include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.jhove.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.statistics.php");
include_once(APP_INC_PATH . "class.citation.php");
include_once(APP_INC_PATH . "class.org_structure.php");
include_once(APP_INC_PATH . "class.internal_notes.php");
include_once(APP_INC_PATH . "class.record_view.php");
include_once(APP_INC_PATH . "class.user_comments.php");
include_once(APP_INC_PATH . "class.origami.php");
include_once(APP_PEAR_PATH . "Date.php");
include_once(APP_INC_PATH . "class.bookreaderimplementation.php");
include_once(APP_INC_PATH . "class.author_affiliations.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.links.php");
include_once(APP_INC_PATH . 'class.sherpa_romeo.php');
include_once(APP_INC_PATH . 'class.api.php');

// Viewing through the API. Make the autentication check for username
if (APP_API) {
    Auth::checkAuthentication(APP_SESSION, $failed_url = NULL, $is_popup = false);
}

$username = Auth::getUsername();
$isAdministrator = Auth::isAdministrator();
$isSuperAdministrator = User::isUserSuperAdministrator($username);
$isUPO = User::isUserUPO($username);

$tpl->assign("isSuperAdministrator", $isSuperAdministrator);

if ($isAdministrator || $isUPO) {

    //get ranked journals for 2010/12 for spyglass
    $rjl_spyglass = '';
    $rjinfo = Record::getRankedJournalInfo($pid);
    $rcinfo = Record::getRankedConferenceInfo($pid);
    if (is_array($rjinfo) || is_array($rcinfo)) {
        if (array_key_exists('rj_2010_rank', $rjinfo) && $rjinfo['rj_2010_rank'] == '') {
            $rjinfo['rj_2010_rank'] = "N/R";
        }
        if (array_key_exists('rj_2015_title', $rjinfo)) {
            $rjl_spyglass .= "(ERA 2015: ".$rjinfo['rj_2015_title'].")</br>";
        }
        if (array_key_exists('rc_2015_title', $rcinfo)) {
            $rjl_spyglass .= "(ERA 2015: ".$rcinfo['rc_2015_title'].")</br>";
        }
        if (array_key_exists('rj_2012_title', $rjinfo)) {
            $rjl_spyglass .= "(ERA 2012: ".$rjinfo['rj_2012_title'].")</br>";
        }
        if (array_key_exists('rc_2010_title', $rcinfo)) {
            $rjl_spyglass .= "(ERA 2010: ".$rcinfo['rc_2010_title'].", ranked ".$rcinfo['rc_2010_rank'].")</br>";
        }
        if (array_key_exists('rj_2010_rank', $rjinfo)) {
            $rjl_spyglass .= "(ERA 2010: ".$rjinfo['rj_2010_title'].", ranked ".$rjinfo['rj_2010_rank'].")";
        }
    }

  if (APP_FEDORA_SETUP == 'sslall' || APP_FEDORA_SETUP == 'sslapim') {
    $get_url = APP_FEDORA_APIM_PROTOCOL_TYPE . APP_FEDORA_SSL_LOCATION . "/get" . "/" . $pid;
  } else {
    $get_url = APP_FEDORA_APIM_PROTOCOL_TYPE . APP_FEDORA_LOCATION . "/get" . "/" . $pid;
  }
  $tpl->assign("fedora_get_view", 1);

  $affilliations = AuthorAffiliations::getListAll($pid);
  $tpl->assign('affilliations', $affilliations);
  $tpl->assign("internal_notes", InternalNotes::readNote($pid));
  $tpl->assign("rjl_spyglass", $rjl_spyglass);
} else {
  $tpl->assign("fedora_get_view", 0);
}

$spyglasshref = ($isSuperAdministrator) ? $get_url : '#';
$spyglassclick = ($isSuperAdministrator) ? "javascript:window.open('$get_url'); return false;" : "";

$tpl->assign('spyglasshref', $spyglasshref);
$tpl->assign('spyglassclick', $spyglassclick);

$tpl->assign("fez_root_dir", APP_PATH);
$tpl->assign("eserv_url", APP_BASE_URL . "view/" . $pid . "/");
$tpl->assign("local_eserv_url", APP_BASE_URL . "view/" . $pid . "/");


$debug = @$_REQUEST['debug'];
if ($debug == 1) {
  $tpl->assign("debug", "1");
} else {
  $tpl->assign("debug", "0");
}


if (!empty($pid)) {

  // Retrieve the selected version date from the request.
  // This will be null unless a version date has been
  // selected by the user.
  $requestedVersionDate = (isset($_REQUEST['version_date'])
    && $_REQUEST['version_date'] > 0)
    ? $_REQUEST['version_date'] : null;

  $record = new RecordObject($pid, $requestedVersionDate);
}

if (!empty($pid) && $record->checkExists()) {

  $canViewVersions = $record->canViewVersions(false);
  $useVersions = false;
  if ($requestedVersionDate != null && !$canViewVersions) {
    // user not allowed to see other versions,
    // so revert back to latest version
    $requestedVersionDate = null;
    $record = new RecordObject($pid);
  } else {
    $useVersions = true;
  }
  $title = Record::getSearchKeyIndexValue($pid, "title", false);
  if ($title !== false) {
    $tpl->assign("extra_title", $title);
  } else {
    $tpl->assign("extra_title", "Record #" . $pid . " Details");
  }


  $tpl->assign("pid", $pid);
  $deleted = false;
  if (@$show_tombstone) {

    // check if this record has been deleted
    if ($record->isDeleted() && ($requestedVersionDate == null || !$canViewVersions)) {
      $tpl->assign('show_tombstone', true);
      $tpl->assign('deleted', true);
      $deleted = true;
      $history_res = History::searchOnPid($pid,
        array('pre_detail' => '%Marked Duplicate of %'));
      if (!empty($history_res)) {
        preg_match('/Marked Duplicate of (\S+)/', $history_res[0]['pre_detail'], $matches);
        $tpl->assign('duplicate_pid', $matches[1]);
        $tpl->assign('duplicate_premis', $history_res[0]);

      }
    }
  }

  if (APP_FEDORA_BYPASS == 'ON') {
    $xdis_id = ($deleted) ? Record::getSearchKeyIndexValueShadow($pid, 'Display Type') : Record::getSearchKeyIndexValue($pid, 'Display Type');
    $xdis_key = array_keys($xdis_id);
    $xdis_id = $xdis_key[0];
  } else {
    $xdis_id = $record->getXmlDisplayId($useVersions);
  }
  $tpl->assign("xdis_id", $xdis_id);
  $xdis_title = XSD_Display::getTitle($xdis_id);
  $tpl->assign("xdis_title", $xdis_title);
  if (!is_numeric($xdis_id)) {
    $xdis_id = @$_REQUEST["xdis_id"];
    if (is_numeric($xdis_id)) { // must have come from select xdis so save xdis in the Fez MD
      $record->updateAdminDatastream($xdis_id);
    }
  }

  if (APP_SOLR_SWITCH == 'ON' && $username) {
    $details = FulltextQueue::getDetailsForPid($pid);
    if (count($details) > 0) {
      Session::setMessage('This record is currently in the Solr queue - changes may not appear for a few moments.');
    }
  }

  if (!is_numeric($xdis_id)) { // if still can't find the xdisplay id then ask for it
    if (APP_API) {
        $arr = API::makeResponse(
          400,
          "Incorrect display id.",
          array('pid' => $pid)
        );
        API::reply(400, $arr, APP_API);
        exit;
    }
    Auth::redirect(APP_RELATIVE_URL . "select_xdis.php?return=view_form&pid=" . $pid . $extra_redirect, false);
  }

  $custom_view_pid = (isset($_GET['custom_view_pid'])) ? $_GET['custom_view_pid'] : null;

  if (!empty($custom_view_pid)) {
    $parents = Record::getParentsAll($pid);
    $found = false;
    foreach ($parents as $parent) {
      if ($custom_view_pid == $parent['rek_pid']) {
        $found = true;
      }
    }
    if (!$found) {
      Auth::redirect("http://" . APP_HOSTNAME . APP_RELATIVE_URL . "view/" . $pid, false);
    }
  }

  $canEdit = false;
  $canView = true;

  $canEdit = $record->canEdit(false);
  if ($canEdit == true) {
    $tpl->assign("internal_notes", InternalNotes::readNote($pid));
    $canView = true;
  } else {
    $canView = $record->canView();
  }

  $tpl->assign("isViewer", $canView);
  if ($canView) {
    list($prev, $next) = RecordView::getNextPrevNavigation($pid);

    if (APP_API) {
        $tpl->setTemplate("view.tpl.xml");
    } else {
        $tpl->setTemplate('header.tpl.html');
        $header = $tpl->getTemplateContents();
        echo $header;
        ob_flush();
        flush();
        $tpl->setTemplate("view.tpl.html");
    }

    $ret_id = 3;
    $strict = false;
    $workflows = array_merge($record->getWorkflowsByTriggerAndRET_IDAndXDIS_ID('Update', $ret_id, $xdis_id, $strict),
      $record->getWorkflowsByTriggerAndRET_IDAndXDIS_ID('Export', $ret_id, $xdis_id, $strict),
      $record->getWorkflowsByTriggerAndRET_IDAndXDIS_ID('Delete', $ret_id, $xdis_id, $strict));

    // check which workflows can be triggered
    $workflows1 = array();
    if (is_array($workflows)) {
      foreach ($workflows as $trigger) {
        if (WorkflowTrigger::showInList($trigger['wft_options'])
          && Workflow::canTrigger($trigger['wft_wfl_id'], $pid)
        ) {
          $workflows1[] = $trigger;
        }
      }
      $workflows = $workflows1;
    }

    $tpl->assign("workflows", $workflows);
    $tpl->assign("isEditor", $canEdit);
    $tpl->assign("canViewVersions", $canViewVersions);

    if ($requestedVersionDate != null) {
      $tpl->assign("viewingPreviousVersion", true);
      $tpl->assign("versionDate", $requestedVersionDate);
      $tpl->assign("versionDatePretty", Date_API::getFormattedDate($requestedVersionDate));
    } else {
      $tpl->assign("viewingPreviousVersion", false);
    }

    $record->getDisplay();
    $xsd_display_fields = $record->display->getMatchFieldsList(array("FezACML"), array()); // XSD_DisplayObject

    $tpl->assign("sta_id", $record->getPublishedStatus());
    $tpl->assign("xsd_display_fields", $xsd_display_fields);
    $details = $record->getDetails();

    $parents = Record::getParentsDetails($pid);

    // do citation before mucking about with the details array
    $citation_html = Citation::renderCitation($xdis_id, $details, $xsd_display_fields);
    $tpl->assign('citation', $citation_html);

    $displayOrderForm = false;
    $parent_relationships = array();
    foreach ($parents as $parent) {
      $parent_rel = XSD_Relationship::getColListByXDIS($parent['rek_display_type']);
      $parent_relationships[$parent['rek_pid']] = array();
      foreach ($parent_rel as $prel) {
        array_push($parent_relationships[$parent['rek_pid']], $prel);
      }
      array_push($parent_relationships[$parent['rek_pid']], $parent['rek_display_type']);
      //Check if the order form should be displayed
        $displayOrderForm =  $displayOrderForm || in_array($parent['rek_pid'], explode(',',THESIS_COLLECTIONS_ORDERABLE));

    }
    $displayOrderForm = (empty($username) && $displayOrderForm);
    // Now generate the META Tag headers
    // lets add the dublin core schema
    $meta_head = '<link rel="schema.DC" href="http://purl.org/DC/elements/1.0/" />' . "\n";
    // and the identifier
    $meta_head .= '<meta name="DC.Identifier" scheme="URI" content="' . APP_BASE_URL . "view/{$pid}\" />\n";
    // grab the metadata fields o ut of the search keys
    foreach ($xsd_display_fields as $dis_key => $dis_field) {
      if (isset($dis_field['sek_meta_header']) && $dis_field['sek_meta_header']) {
        // split the metadata field out into the various types
        $metaDataFields[$dis_field['xsdmf_id']] = array('fieldnames' => explode('|', $dis_field['sek_meta_header']), 'type' => $dis_field['sek_data_type'], 'lookup' => $dis_field['sek_lookup_function'], 'can_output_multiple' => $dis_field['sek_cardinality'] == 0 ? false : true);
      }
    }

    $combineDataFields = array('citation_authors', 'citation_keywords');
    $fieldsOutputAlready = array();

    // for every metadata field
    foreach ($metaDataFields as $xsdmfId => $metaDataDetails) {
      // if there are details
      if (isset($details[$xsdmfId]) && !empty($details[$xsdmfId])) {
        // foreach metadata field name
        foreach ($metaDataDetails['fieldnames'] as $fieldName) {
          // make sure we're only outputting multiple values for fields that are supposed to output multiple values
          if (!$metaDataDetails['can_output_multiple'] && in_array($fieldName, $fieldsOutputAlready)) {
            $log->info("Field {$fieldName} is not a multiple field, but has been asked to be output multiple times. Ignoring the second output attempt");
            continue;
          }
          // do special processing for fields that should be joined in one field, separated with a semicolon
          if (in_array($fieldName, $combineDataFields)) {
            $fieldsCombinedData = array();
            if (is_array($details[$xsdmfId])) {
              foreach ($details[$xsdmfId] as $fieldData) {
                if (trim($fieldData) != '') {
                  $fieldsCombinedData[] = $fieldData;
                }
              }
            } else {
              $fieldsCombinedData[] = $details[$xsdmfId];
            }
            if (count($fieldsCombinedData) > 0) {
              // use the lookup function if one is specified
              if ($metaDataDetails['lookup'] != '') {
                foreach ($fieldsCombinedData as $index => $data) {
                  eval("\$fieldsCombinedData[\$index] = {$metaDataDetails['lookup']}('{$data}');");
                }
              }
              $fieldsOutputAlready[$fieldName] = $fieldName;
              $meta_head .= "<meta name=\"{$fieldName}\" content=\"" . htmlspecialchars(trim(implode('; ', $fieldsCombinedData)), ENT_QUOTES) . "\" />\n";
            }
          } elseif ($fieldName == 'citation_pdf_url') {
            // do special processing for pdf file links
            if (is_array($details[$xsdmfId])) {
              foreach ($details[$xsdmfId] as $filename) {
                if (trim($filename) != '') {
                  $pathinfo = pathinfo($filename);
                  if (strtolower($pathinfo['extension']) == 'pdf') {
                    $fieldsOutputAlready[$fieldName] = $fieldName;
                    $meta_head .= "<meta name=\"{$fieldName}\" content=\"" . htmlspecialchars(trim(APP_BASE_URL . "view/{$pid}/{$filename}"), ENT_QUOTES) . "\" />\n";
                  }
                }
              }
            }
          } else {
            // otherwise, process the field normally
            if (is_array($details[$xsdmfId])) {
              foreach ($details[$xsdmfId] as $data) {
                if (trim($data) != '') {
                  if ($metaDataDetails['type'] == 'date') {
                    if (is_numeric($data) && strlen($data) == 4) {
                      // Don't pad years - this makes google scholar angry
                    } else {
                      if ($fieldName == 'citation_date') {
                        $data = date('Y/m/d', strtotime($data)); // google wants this format for the date
                      } else {
                        $data = date('Y-m-d', strtotime($data)); // everyone else
                      }
                    }
                  }
                  // use the lookup function if one is specified
                  if ($metaDataDetails['lookup'] != '') {
                    eval("\$data = {$metaDataDetails['lookup']}('{$data}');");
                  }
                  $fieldsOutputAlready[$fieldName] = $fieldName;
                  $meta_head .= "<meta name=\"{$fieldName}\" content=\"" . htmlspecialchars(trim($data), ENT_QUOTES) . "\" />\n";
                }
              }
            } else {
              if ($metaDataDetails['type'] == 'date') {
                if (trim($data) != '') {
                  if (is_numeric($details[$xsdmfId]) && strlen($details[$xsdmfId]) == 4) {
                    // Don't pad years - this makes google scholar angry
                    $data = $details[$xsdmfId];
                  } else {
                    if ($fieldName == 'citation_date') {
                      $data = date('Y/m/d', strtotime($details[$xsdmfId])); // google wants this format for the date
                    } else {
                      $data = date('Y-m-d', strtotime($details[$xsdmfId])); // everyone else
                    }
                  }
                }
              } else {
                $data = $details[$xsdmfId];
              }
              // use the lookup function if one is specified
              if ($metaDataDetails['lookup'] != '') {
                eval("\$data = {$metaDataDetails['lookup']}('{$data}');");
              }
              $fieldsOutputAlready[$fieldName] = $fieldName;
              $meta_head .= "<meta name=\"{$fieldName}\" content=\"" . htmlspecialchars(trim($data), ENT_QUOTES) . "\" />\n";
            }
          }
        }
      }
    }
    $depositor_org = '';
    $depositor_org_id = '';
    // get the created / updated and depositor info
    foreach ($xsd_display_fields as $dis_key => $dis_field) {

      if ($dis_field['xsdmf_enabled'] == 1) {
        if ($dis_field['xsdmf_element'] == "!created_date") {
          if (!empty($details[$dis_field['xsdmf_id']])) {
            if (is_array($details[$dis_field['xsdmf_id']])) {
              foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
                $created_date = Date_API::getFormattedDate($cdata, FALSE, TRUE);
              }
            } else {
              $created_date = Date_API::getFormattedDate($details[$dis_field['xsdmf_id']], FALSE, TRUE);
            }
          }
        }
        if ($dis_field['xsdmf_element'] == "!depositor") {
          if (!empty($details[$dis_field['xsdmf_id']])) {
            if (is_array($details[$dis_field['xsdmf_id']])) {
              foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
                $depositor_id = $cdata;
                $depositor = User::getFullName($cdata);
              }
            } else {
              $depositor_id = $details[$dis_field['xsdmf_id']];
              $depositor = User::getFullName($details[$dis_field['xsdmf_id']]);
            }
          }
        }
        if ($dis_field['xsdmf_element'] == "!depositor_affiliation") {
          if (!empty($details[$dis_field['xsdmf_id']])) {
            if (is_array($details[$dis_field['xsdmf_id']])) {
              foreach ($details[$dis_field['xsdmf_id']] as $ckey => $cdata) {
                $depositor_org_id = $cdata;
                $depositor_org = Org_Structure::getTitle($cdata);
              }
            } else {
              $depositor_org_id = $details[$dis_field['xsdmf_id']];
              $depositor_org = Org_Structure::getTitle($details[$dis_field['xsdmf_id']]);
            }
          }
        }
      }
    }

    $tpl->assign('meta_head', $meta_head);
    $record_view = new RecordView($record); // record viewer object
    $details = $record_view->getDetails();
  } else {
    if (APP_API) {
      API::reply(401, API::makeResponse(401, 'Not authorised'), APP_API);
      exit;
    } else {
      header("HTTP/1.0 403 Forbidden");
      $tpl->setTemplate('header.tpl.html');
      $header = $tpl->getTemplateContents();
      echo $header;
      ob_flush();
      flush();

      $tpl->setTemplate("view.tpl.html");
      $tpl->assign("show_not_allowed_msg", true);
      $savePage = false;
    }
  }

  //Direct link to solr for super admins
  if ((APP_SOLR_SWITCH == "ON") && ($isSuperAdministrator)) {
    $tpl->assign("link_to_solr", "http://" . APP_SOLR_HOST . ":" . APP_SOLR_PORT . "" . APP_SOLR_PATH . "select/?q=pid_t:%22" . $pid . "%22");
  }

  // If we have a Journal Article of a Conference Paper, we want to display the sub-type information.
  if ($xdis_title == 'Journal Article') {
    $sub_type = Record::getSearchKeyIndexValue($pid, "Subtype", false);
  } elseif ($xdis_title == 'Conference Paper') {
    $sub_type = Record::getSearchKeyIndexValue($pid, "Genre Type", false);
  } else {
    $sub_type = false;
  }
  $tpl->assign("sub_type", $sub_type);

  if (empty($details)) {
    $tpl->assign('details', '');
  } else {
    $linkCount = 0;
    $fileCount = 0;

    if (APP_FEDORA_BYPASS == 'ON') {
      //This retrieves the datastreams
      //Metadata is retrieved by XSD_DisplayObject::getXSDMF_Values
      $dob = new DigitalObject();
      $params = array('pid' => $pid);

      if ($requestedVersionDate) {
        $params['rev'] = $requestedVersionDate;
      }

      $datastreams = $dob->getDatastreams($params);
    } else {
      $datastreams = Fedora_API::callGetDatastreams($pid, $requestedVersionDate, 'A');
    }

    // Extact and generate list of timestamps for the datastreams of the record
    generateTimestamps($pid, $datastreams, $requestedVersionDate, $tpl);

    //Make this method call pull from the db.
    if ($requestedVersionDate != null && APP_FEDORA_BYPASS != 'ON') {
      $datastreams = Misc::addDeletedDatastreams($datastreams, $pid, $requestedVersionDate);
    }

    $datastreamsAll = $datastreams;
    $datastreams = Misc::cleanDatastreamListLite($datastreams, $pid);

    $doi = Record::getSearchKeyIndexValue($pid, 'DOI');
    $tpl->assign(altmetricDOI, $doi);
    //if fedora bypass is on need to get from mysql else it datastreams as down below
    if (APP_FEDORA_BYPASS == 'ON') {
      $links = Links::getLinks($pid);
      foreach ($links as &$link) {
        $linkCount++;
        $link['rek_link'] = trim($link['rek_link']);
        if (APP_LINK_PREFIX != "") {
          if (!is_numeric(strpos($link['rek_link'], APP_LINK_PREFIX))) {
            $link['prefix_location'] = APP_LINK_PREFIX . $link['rek_link'];
            $link['rek_link'] = str_replace(APP_LINK_PREFIX, "", $link['rek_link']);
          } else {
            $link['prefix_location'] = "";
          }
        } else {
          $link['prefix_location'] = "";
        }
        if (strtoupper('http://dx.doi.org/' . $doi) == strtoupper($link['rek_link'])) {
          $doiInLinks = true;
        }
      }

      if (!$doiInLinks && !empty($doi)) {
          $linkCount++;
          $newLink['rek_link'] = 'http://dx.doi.org/' . $doi;
          $newLink['rek_link_description'] = 'Full text from publisher';
        if (APP_LINK_PREFIX != "") {
            $newLink['prefix_location'] = APP_LINK_PREFIX . $newLink['rek_link'];
        }
            array_unshift($links, $newLink);
      }



      $dob = new DSResource();
      $streams = $dob->listStreams($pid);
      foreach ($streams as &$stream) {
        $stream['downloads'] = Statistics::getStatsByDatastream($pid, $stream['filename']);
        $stream['base64ID'] = base64_encode($stream['filename']);
        $fileCount++;
      }


    }

    if ($datastreams) {

      $links = array();

      foreach ($datastreams as $ds_key => $ds) {

        if ($datastreams[$ds_key]['controlGroup'] == 'R') {
          $linkCount++;
        }

        if (($datastreams[$ds_key]['controlGroup'] == 'R') && ($datastreams[$ds_key]['ID'] != 'DOI') && (APP_FEDORA_BYPASS != 'ON')) {
          $links[$linkCount - 1]['rek_link'] = trim($datastreams[$ds_key]['location']);
          $links[$linkCount - 1]['rek_link_description'] = $datastreams[$ds_key]['label'];
          $links[$linkCount - 1]['rek_link_description'] = $datastreams[$ds_key]['label'];

          if (strtoupper('http://dx.doi.org/' . $doi) == strtoupper($links[$linkCount - 1]['rek_link'])) {
            $doiInLinks = true;
          }

          // Check for APP_LINK_PREFIX and add if not already there add it to a special ezyproxy link for it
          if (APP_LINK_PREFIX != "") {
            if (!is_numeric(strpos($links[$linkCount - 1]['rek_link'], APP_LINK_PREFIX))) {
              $links[$linkCount - 1]['prefix_location'] = APP_LINK_PREFIX . $links[$linkCount - 1]['rek_link'];
              $links[$linkCount - 1]['rek_link'] = str_replace(APP_LINK_PREFIX, "", $links[$linkCount - 1]['rek_link']);
            } else {
              $links[$linkCount - 1]['prefix_location'] = "";
            }
          } else {
            $links[$linkCount - 1]['prefix_location'] = "";
          }

        } elseif ($datastreams[$ds_key]['controlGroup'] == 'M') {

          $fileCount++;
          $datastreams[$ds_key]['exif'] = Exiftool::getDetails($pid, $datastreams[$ds_key]['ID']);

          if (APP_EXIFTOOL_SWITCH != "ON" || !is_numeric($datastreams[$ds_key]['exif']['exif_file_size'])) { //if Exiftool isn't on then get the datastream info from JHOVE (which is a lot slower than EXIFTOOL)
            if (is_numeric(strrpos($datastreams[$ds_key]['ID'], "."))) {
              $Jhove_DS_ID = "presmd_" . substr($datastreams[$ds_key]['ID'], 0, strrpos($datastreams[$ds_key]['ID'], ".")) . ".xml";
            } else {
              $Jhove_DS_ID = "presmd_" . $datastreams[$ds_key]['ID'] . ".xml";
            }

            foreach ($datastreamsAll as $dsa) {

              if ($dsa['ID'] == $Jhove_DS_ID) {
                $Jhove_XML = Fedora_API::callGetDatastreamDissemination($pid, $Jhove_DS_ID);

                if (!empty($Jhove_XML['stream'])) {
                  $jhoveHelp = new Jhove_Helper($Jhove_XML['stream']);

                  $fileSize = $jhoveHelp->extractFileSize();
                  $datastreams[$ds_key]['archival_size'] = Misc::size_hum_read($fileSize);
                  $datastreams[$ds_key]['archival_size_raw'] = $fileSize;

                  $spatialMetrics = $jhoveHelp->extractSpatialMetrics();

                  if (is_numeric($spatialMetrics[0]) && $spatialMetrics[0] > 0) {
                    $tpl->assign("img_width", $spatialMetrics[0]);
                  }

                  if (is_numeric($spatialMetrics[1]) && $spatialMetrics[1] > 0) {
                    $tpl->assign("img_height", $spatialMetrics[1]);
                  }

                  unset($jhoveHelp);
                  unset($Jhove_XML);
                }
              }
            }
          } else {
            $datastreams[$ds_key]['MIMEType'] = $datastreams[$ds_key]['exif']['exif_mime_type'];
            $datastreams[$ds_key]['archival_size'] = $datastreams[$ds_key]['exif']['exif_file_size_human'];
            $datastreams[$ds_key]['archival_size_raw'] = $datastreams[$ds_key]['exif']['exif_file_size'];
            $tpl->assign("img_height", $datastreams[$ds_key]['exif']['exif_image_height']);
            $tpl->assign("img_width", $datastreams[$ds_key]['exif']['exif_image_width']);
          }
          $origami_switch = "OFF";
          if (APP_ORIGAMI_SWITCH == "ON" && ($datastreams[$ds_key]['MIMEType'] == 'image/jpeg' ||
            $datastreams[$ds_key]['MIMEType'] == 'image/tiff' ||
            $datastreams[$ds_key]['MIMEType'] == 'image/tif' ||
            $datastreams[$ds_key]['MIMEType'] == 'image/jpg')
          ) {
            $origami_path = Origami::getTitleHome() . Origami::getTitleLocation($pid, $datastreams[$ds_key]['ID']);
            if (is_dir($origami_path)) {
              $origami_switch = "ON";
            }
          }
          $datastreams[$ds_key]['origami_switch'] = $origami_switch;

          $datastreams[$ds_key]['FezACML'] = Auth::getAuthorisationGroups($pid, $datastreams[$ds_key]['ID']);
          $datastreams[$ds_key]['downloads'] = Statistics::getStatsByDatastream($pid, $ds['ID']);
          $datastreams[$ds_key]['base64ID'] = base64_encode($ds['ID']);

          if (APP_FEDORA_DISPLAY_CHECKSUMS == "ON") {
            $datastreams[$ds_key]['checksumType'] = $ds['checksumType'];
            $datastreams[$ds_key]['checksum'] = $ds['checksum'];
            $tpl->assign("display_checksums", "ON");
          }

          Auth::getAuthorisation($datastreams[$ds_key]);
        }

        if ($datastreams[$ds_key]['controlGroup'] == 'R' && $datastreams[$ds_key]['ID'] == 'DOI') {
          $links[$linkCount - 1]['rek_link'] = trim($datastreams[$ds_key]['location']);
          $tpl->assign('doi', $datastreams[$ds_key]);
        }
      }

      if (!$doiInLinks && !empty($doi)) {
        $linkCount++;
        $newLink['rek_link'] = 'http://dx.doi.org/' . $doi;
        $newLink['rek_link_description'] = 'Full text from publisher';
        if (APP_LINK_PREFIX != "") {
            $newLink['prefix_location'] = APP_LINK_PREFIX . $newLink['rek_link'];
        }
        array_unshift($links, $newLink);
      }
    }

    $tpl->assign("datastreams", $datastreams);
    $tpl->assign("ds_get_path", APP_FEDORA_GET_URL . "/" . $pid . "/");
    $tpl->assign("parents", $parents);

    if (count($parents) > 1) {
      $tpl->assign("parent_heading", "Collections:");
    } else {
      $tpl->assign("parent_heading", "Collection:");
    }

    //what does this record succeed?
    $hasVersions = 0;
    $derivations = Record::getParentsAll($pid, 'isDerivationOf', true);

    if (count($derivations) == 0) {
      $derivations[0]['rek_title'] = Record::getSearchKeyIndexValue($pid, "Title");
      $derivations[0]['rek_pid'] = $pid;
    } else {
      $hasVersions = 1;
    }

    //are there any other records that also succeed this parent
    foreach ($derivations as $devkey => $dev) { // gone all the way up, now go back down getting ALL the children as we ride the spiral
      $child_devs = Record::getChildrenAll($derivations[$devkey]['rek_pid'], "isDerivationOf", false);
      if (count($child_devs) != 0) {
        $hasVersions = 1;
      }
      $derivations[$devkey]['children'] = $child_devs;
    }

    $derivationTree = "";
    if ($hasVersions == 1) {
      Record::generateDerivationTree($pid, $derivations, $derivationTree);
      Record::wrapDerivationTree($derivationTree);
    }

    //what does this record isDatasetOf?
    $hasDatasets = 0;
    $datasets = Record::getParentsAll($pid, 'isDatasetOf', true);

    if (count($datasets) == 0) {
      $datasets[0]['rek_title'] = Record::getSearchKeyIndexValue($pid, "Title");
      $datasets[0]['rek_pid'] = $pid;
    } else {
      $hasDatasets = 1;
    }
    //are there any other records that also succeed this parent
    foreach ($datasets as $devkey => $dev) { // gone all the way up, now go back down getting ALL the children as we ride the spiral
      $child_devs = Record::getChildrenAll($datasets[$devkey]['rek_pid'], "isDatasetOf", false);
      if (count($child_devs) != 0) {
        $hasDatasets = 1;
      }
      $datasets[$devkey]['children'] = $child_devs;
    }

    $datasetTree = "";
    $datasetArray = array();
    if ($hasDatasets == 1) {
      Record::generateDerivationTree($pid, $datasets, $datasetTree, $datasetArray, true);
      Record::wrapDerivationTree($datasetTree);
    }

    // Link to request open access - shown on a Thesis if locked down
    if ($xdis_title == 'Thesis' && !$isAdministrator) {
      $displayReqOpenAccess = false;
        foreach ($datastreams as $datastream) {
            if ($datastream['controlGroup'] == 'M') {
                  $displayReqOpenAccess = true;
                $publicPerms = Auth::getAuthPublic($pid, $datastream['ID']);
                if ($publicPerms['viewer']) {
                    $displayReqOpenAccess = false;
                    break;
              }
          }
      }
    }

    $retracted = Record::getSearchKeyIndexValue($pid, "Retracted");
    $tpl->assign("retracted", $retracted);
    if(APP_ADDTHIS_SWITCH == 'ON')
    {
      $tpl->assign("addthis", APP_ADDTHIS_ID);
    }
    $tpl->assign("displayOrderForm", $displayOrderForm);
    $tpl->assign("displayReqOpenAccess", $displayReqOpenAccess);
    $tpl->assign("origami", APP_ORIGAMI_SWITCH);
    $tpl->assign("linkCount", $linkCount);
    $tpl->assign("links", $links);
    $tpl->assign("hasVersions", $hasVersions);
    $tpl->assign("hasDatasets", $hasDatasets);
    $tpl->assign("fileCount", $fileCount);
    $tpl->assign("derivationTree", $derivationTree);
    $tpl->assign("datasetTree", $datasetTree);
    $tpl->assign("created_date", $created_date);
    $tpl->assign("depositor", $depositor);
    $tpl->assign("depositor_id", $depositor_id);
    $tpl->assign("depositor_org", $depositor_org);
    $tpl->assign("depositor_org_id", $depositor_org_id);
    $tpl->assign("details", $details);
    $tpl->assign("APP_SHORT_ORG_NAME", APP_SHORT_ORG_NAME);
    $tpl->assign('title', $record->getTitle());
    $tpl->assign('fedora_bypass', APP_FEDORA_BYPASS == 'ON');
    $tpl->assign("streams", $streams);
    $tpl->assign("statsAbstract", Statistics::getStatsByAbstractView($pid));
    $tpl->assign("statsFiles", Statistics::getStatsByAllFileDownloads($pid));

    // citations from thomson and scopus
    $countThomsonCitations = Record::getSearchKeyIndexValue($pid, "Thomson Citation Count", false);
    $countScopusCitations = Record::getSearchKeyIndexValue($pid, "Scopus Citation Count", false);
    $countGoogleCitations = Record::getSearchKeyIndexValue($pid, "GS Citation Count", false);
    $tpl->assign("citationsThomson", $countThomsonCitations);
    $tpl->assign("citationsScopus", $countScopusCitations);
    $tpl->assign("citationsGoogle", $countGoogleCitations);
    if ($countGoogleCitations > 0) {
      $googleCitationsLink = Record::getSearchKeyIndexValue($pid, "GS Cited By Link", false);
      $tpl->assign("citationsGoogleLink", $googleCitationsLink);
    }

    // Thomson and Scopus IDs. Grab 1st ID only
    $ThomsonCitationsID = Record::getSearchKeyIndexValue($pid, "ISI LOC", false);
    $ScopusCitationsID = Record::getSearchKeyIndexValue($pid, "Scopus ID", false);
    $tpl->assign("ThomsonID", $ThomsonCitationsID);
    $tpl->assign("ScopusID", $ScopusCitationsID);
    $app_link_prefix = (defined('APP_LINK_PREFIX')) ? APP_LINK_PREFIX : '';
    $tpl->assign("app_link_prefix", $app_link_prefix);

    // Add view to statistics buffer
    Statistics::addBuffer($pid);


    $tpl->assign(compact('prev', 'next'));

    // determine if there are workflows currently working on this pid and let the user know if there are
    $outstandingStatus = '';
    if ($isAdministrator) {
      $outstandingStatus = Misc::generateOutstandingStatusString($pid);
    }
    $tpl->assign('outstandingStatus', $outstandingStatus);
    $pageCounts = array();
    //Find the pdf stream
    if ($datastreams) {
      foreach ($datastreams as $ds) {
        if ($ds['MIMEType'] == 'application/pdf') {
          //Check that it has been converted to images
          //and let the template know.
          $resource = explode('.pdf', $ds['ID']);
          $pidFs = str_replace(':', '_', $pid);
          $resourcePath = BR_IMG_DIR . $pidFs . '/' . $resource[0];
          $bri = new bookReaderImplementation($resourcePath);
          $pageCounts[$resource[0] . '.pdf'] = $bri->countPages(); //Page count check for the template
        }
      }
    }
    $tpl->assign('pageCounts', $pageCounts);

    // Generate OpenURL and assign link resolver button to template
    $openurl = misc::OpenURL2($pid);
    $tpl->assign('openurl', $openurl);
    $resolver_button = APP_LINK_RESOLVER_BUTTON_URL;
    $resolver_base_url = APP_LINK_RESOLVER_BASE_URL;
    $tpl->assign('resolver_button', $resolver_button);
    $tpl->assign('resolver_base_url', $resolver_base_url);

// Get fields to be displayed on Spyglass hover.
// @usage: view_inverse_metadata.tpl.html
    if ($isAdministrator || $isUPO) {
        $spyglassFields = RecordGeneral::getSpyglassHoverFields($xsd_display_fields, $details);
        $tpl->assign('spyglassFields', $spyglassFields);
    }
    // display user comments, if any
    $uc = new UserComments($pid);

// Users must be logged in to submit comments
    if (!empty($username)) {
      $tpl->assign('addusercomment', true);
    }
    $tpl->assign('displayusercomments', true);
    $tpl->assign('usercomments', $uc->comments);

  }
} else {
  if (APP_API) {
    $arr = API::makeResponse(
      404,
      "Resource not found.",
      array('pid' => $pid)
    );
    API::reply(404, $arr, APP_API);
    exit;
  } else {
    header("Status: 404 Not Found");
    $tpl->setTemplate('header.tpl.html');
    $header = $tpl->getTemplateContents();
    echo $header;
    ob_flush();
    flush();

    $tpl->setTemplate("view.tpl.html");
    $tpl->assign('not_exists', true);
    $savePage = false;
  }
}


/**
 * Sets a list on the Smarty template containing unique datastream timestamps.
 *
 * <p>
 * As atomic Fez operations result in non-atomic Fedora operations (for example, updating all datstreams
 * results in different version timestamps for each), a fuzzy search is performed to identify timestamps
 * that are likely to belong to the same atomic operation.
 * </p>
 *
 * <p>
 * The list of dates is keyed under 'created_dates_list' on the Smarty template and each entry is an array
 * containing the following data:
 * </p>
 *
 * <ul>
 * <li><strong>fedoraDate</strong> datestamp retrieved from fedora</li>
 * <li><strong>displayDate</strong> formatted datestamp for display</li>
 * <li><strong>filtered</strong> true if the date is determined as being a component of a compound Fez
 *  operation and is therefore deemed redundant, false otherwise</li>
 * <li><strong>selected</strong> true if the current date corresponds to the version of the record being
 * viewed</li>
 * </ul>
 *
 * @param $pid PID of the Fedora record to retrieve timestamps for
 * @param $datastreams list of datastreams for record
 * @param $requestedVersionDate date of the version being displayed
 * @param $tpl Smarty template
 */
function generateTimestamps($pid, $datastreams, $requestedVersionDate, $tpl)
{

  $createdDates = array();

  if (APP_FEDORA_BYPASS == 'ON') {
    $rec = new Fez_Record_SearchkeyShadow($pid);
    $createdDates = $rec->returnVersionDates();

    /*foreach($versions as $version)
    {
        $createdDates[] = $version['createDate'];
    }*/
  } else {
    // Retrieve all versions of all datastreams
    foreach ($datastreams as $datastream) {
      //probably only need to check the dates of the FezMD datastream. This should reduce calls to Fedora and improve performance - CK added 17/7/2009.
        //Re-added mods since they also need checking
		if ($datastream['ID'] == 'FezMD' || $datastream['ID'] == 'MODS') {
        $parms = array('pid' => $pid, 'dsID' => $datastream['ID']);

        $datastreamVersions = Fedora_API::openSoapCall('getDatastreamHistory', $parms);

        // Extract created dates from datastream versions
        foreach ($datastreamVersions as $key => $var) {

          // If a datastream contains multiple versions, Fedora bundles them in an array, however doesn't
          // do if a datastream only has a single version.

          // If the datastream is an array, retrieve value keyed under createDate
          if (is_array($var) && array_key_exists('createDate', $var)) {
            $createdDates[] = $var['createDate'];
          } // If the datastream isn't an array, retrieve the createDate value
          else if ($key === 'createDate') {
            $createdDates[] = $var;
          }
        }
      }
    }
  }

  // Remove duplicate datestamps from array
  $createdDates = array_unique($createdDates);

  // Sort datestamps using the custom fedoraDateSorter function
  usort($createdDates, "fedoraDateSorter");

  $originalCreatedDates = $createdDates;

  // Iterate through amalgamated list of datestamps, removing those that are deemed to have been created
  // too closely-together to have been a result of a user edit.
  //
  // Once a 'phantom' version has been found, iterate through the list again until all datestamps are
  // suitably far apart.
  do {
    $phantomVersionFound = false;
    for ($i = 1; $i < sizeof($createdDates); $i++) {

      // If the time between the current datestamp and the previous datestamp is too low, remove the previous
      // entry and scan the list from the start
      if (strtotime($createdDates[$i]) - strtotime($createdDates[$i - 1]) < APP_VERSION_TIME_INTERVAL) {
        array_splice($createdDates, $i - 1, 1);
        $phantomVersionFound = true;
        break;
      }

      if ($phantomVersionFound) break;
    }
  } while ($phantomVersionFound);


  // Iterate through initial list of datastream version created dates and create a list of dates for display
  // purposes, containing human-readable dates, the original Fedora date, whether the date has been filtered
  // or whether the date corresponds to the currently selected date.
  $createdDatesForDisplay = array();
  $timezone = Date_API::getPreferredTimezone();

  foreach ($originalCreatedDates as $createdDate) {

    // Determine whether the date has been filtered out from the list or not
    $filtered = in_array($createdDate, $createdDates) ? false : true;

    // format as RFC 2822 formatted date for readibility
    $displayDate = Date_API::getFormattedDate($createdDate, $timezone);

    // Create the date display entry
    $createdDatesForDisplay[] = array("fedoraDate" => $createdDate, "displayDate" => $displayDate, "filtered" => $filtered, "selected" => $createdDate == $requestedVersionDate);
  }

  // set the last date (ie, current version) to null to force latest revision to be displayed
  $createdDatesForDisplay[sizeof($createdDatesForDisplay) - 1]['fedoraDate'] = null;

  // If a version date hasn't been selected, flag the last (ie, current revision) as selected
  if ($requestedVersionDate == null) {
    $createdDatesForDisplay[sizeof($createdDatesForDisplay) - 1]['selected'] = true;
  }

  // Put date lists on the template
  $tpl->assign('created_dates_list', $createdDatesForDisplay);

  // Retrieve the full/filtered option from the request and repopulate it on the template
  $versionViewType = false;
  if (array_key_exists('version_view_type', $_REQUEST)) {
    $versionViewType = $_REQUEST['version_view_type'];
  }
  $tpl->assign("version_view_type", $versionViewType);
}

/**
 * Custom date sorter for Fedora dates, used by PHP's usort()
 *
 * <p>
 * Note: This function uses strtotime() directly on the dates, which appears to work but which may be flawed - I'm not
 * familiar with Fedora's date format or whether it's custom or a standard format.
 * </p>
 */
function fedoraDateSorter($a, $b)
{
  $unixTimestamp1 = strtotime($a);
  $unixTimestamp2 = strtotime($b);

  if ($unixTimestamp1 == $unixTimestamp2) return 0;
  return ($unixTimestamp1 < $unixTimestamp2) ? -1 : 1;
}
