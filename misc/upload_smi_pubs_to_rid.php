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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.researcherid.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . 'class.esti_search_service.php');

$max = 9999;
$sleep = 1;

$log = FezLog::get();
$db = DB_API::get();
  
$stmt = "
        SELECT rek_pid FROM 
          fez_record_search_key r1 INNER JOIN
          fez_record_search_key_author_id ON rek_pid = rek_author_id_pid INNER JOIN
          fez_record_search_key_isi_loc ON rek_pid = rek_isi_loc_pid INNER JOIN
          fez_author ON aut_id = rek_author_id INNER JOIN
          __temp_smi_staff ON smi_wami_key = aut_org_staff_id LEFT JOIN
          fez_xsd_display ON xdis_id = rek_display_type 
        WHERE
          xdis_title = 'Journal Article' AND rek_date > '1999-12-31' AND rek_isi_loc != ''
        GROUP BY
          r1.rek_pid";
try {
  $res = $db->fetchAll($stmt);
}
catch(Exception $ex) {
  $log->err($ex);
  echo 'Nein!!';
  exit;
}
$manualPidFilter = ' (pid_t:(';
$count = count($res);
for($i=0; $i<$count; $i++) {
  $pid = $res[$i]['rek_pid'];
  $pid = preg_replace('/^UQ\:/', 'UQ\\:', $pid);
  $manualPidFilter .= $pid;
  if( $i < ($count-1)) {
    $manualPidFilter .= ' OR ';
  }
}
$manualPidFilter .= ')) ';

$filter = array();
$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
$filter["searchKey".Search_Key::getID("Object Type")] = 3; // records only
$filter["manualFilter"] = $manualPidFilter; 

$listing = Record::getListing(array(), array(9,10), 0, $max, 'Created Date', false, false, $filter);

$list = array();
foreach($listing['list'] as $rek) {
  $rek['rek_ref_type'] = 17;
  $list['list'][] = $rek;
}
$aut_id = 86103;
publicationsUpload($list, $aut_id);


function publicationsUpload($list, $id)
{
  $log = FezLog::get();
  $db = DB_API::get();
 
  if (count($list['list']) > 0) {
    $tpl = new Template_API();
    $tpl_file = "researcher_publications_upload.tpl.html";
    $tpl->setTemplate($tpl_file);
    $tpl->assign("list", $list['list']);
    $tpl->assign("app_admin_email", APP_ADMIN_EMAIL);
    $tpl->assign("org_name", APP_ORG_NAME);
    $tpl->assign("aut_org_username", Author::getOrgUsername($id));
    $request_data = $tpl->getTemplateContents();
    
    $xml_request_data = new DOMDocument();
    $xml_request_data->loadXML($request_data);

    // Validate against schema
    if (! $xml_request_data->schemaValidate(RID_UL_SERVICE_PUBLICATIONS_XSD)) {
      // Not valid
      $log->err('XML request data does not validate against schema.');
      print 'XML request data does not validate against schema.';
      return false;
    } else {
      $tpl = new Template_API();
      $tpl_file = "researcher_upload_request.tpl.html";
      $tpl->setTemplate($tpl_file);
      $tpl->assign("type", 'Publication');
      $tpl->assign("username", RID_UL_SERVICE_USERNAME);
      $tpl->assign("password", RID_UL_SERVICE_PASSWORD);
      $tpl->assign("request_data", $request_data);
      $request = $tpl->getTemplateContents();
      
      //$request = file_get_contents('publications_upload.xml');

      $xml_api_data_request = new DOMDocument();
      $xml_api_data_request->loadXML($request);

      //header ("content-type: text/xml");
      //echo $xml_api_data_request->saveXML();      
      //exit;
      
      
      // Do the service request
      $response_document = new DOMDocument();
      $response_document = ResearcherID::doServiceRequest($xml_api_data_request->saveXML());
      
      if (! $response_document) {
        print 'Nein!!';
        return false;
      } else {
        header ("content-type: text/xml");
        echo $response_document->saveXML();
        return true;
      }
    }
  } else {
    $log->err('No publications to upload for author '. $id);
    print 'No publications to upload for author '. $id;
    return false;
  }
}
