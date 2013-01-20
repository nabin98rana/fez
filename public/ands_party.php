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
// | Authors: Bernadette Houghton <bhoughton@deakin.edu.au>,       |
// |                                                                      |
// +----------------------------------------------------------------------+
//
//
include_once('config.inc.php');
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.db_api.php");

//initialise and set template file
$tpl = new Template_API();
$tpl_file = "ands_party.tpl.html";
$tpl->setTemplate($tpl_file);

$data_collection_xdis_id = XSD_Display::getXDIS_IDByTitle("Data Collection");


//establish connection to host and database
$log = FezLog::get();
$db = DB_API::get();

// prepare SQL statement to grab author details from fez_author
$stmt = "SELECT aut_id,
				aut_lname,
				aut_mname,
				aut_fname,
				aut_title,
				aut_org_username,
				aut_homepage_link,
				aut_scopus_id,
				aut_people_australia_id,
				aut_researcher_id,
				aut_description,
				aut_update_date
			FROM " . APP_TABLE_PREFIX . "author as t1
			LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id as t2 on t2.rek_author_id = t1.aut_id
			LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key as t3 on t3.rek_pid = t2.rek_author_id_pid
			WHERE t3.rek_status = 2 AND t3.rek_display_type= " . $data_collection_xdis_id . "

			UNION

		SELECT aut_id,
				aut_lname,
				aut_mname,
				aut_fname,
				aut_title,
				aut_org_username,
				aut_homepage_link,
				aut_scopus_id,
				aut_people_australia_id,
				aut_researcher_id,
				aut_description,
				aut_update_date
		FROM " . APP_TABLE_PREFIX . "author as t1
		LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key_contributor_id as t6 on t6.rek_contributor_id = t1.aut_id
		LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key as t7 on t7.rek_pid = t6.rek_contributor_id_pid
		WHERE t7.rek_status = 2 AND t7.rek_display_type=" . $data_collection_xdis_id . "

		ORDER BY aut_id";

try {
		$result = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
}
catch(Exception $ex) {
	$log->err($ex);
  print_r($ex);
	return array();
}
// Prevent bad researcher ID data from being displayed
foreach ($result as $rk => $rdata) {
  if (is_numeric(strpos($rdata['aut_researcher_id'], 'ERR'))) {
    $result[$rk]['aut_researcher_id'] = '';
  }
}

$list = $result;

//grab FOR codes for all authorIDs that have a Data Collection record
/* $stmt2 = "SELECT DISTINCT rek_author_id AS aut_id, rek_fields_of_research
			FROM " . APP_TABLE_PREFIX . "record_search_key_fields_of_research, " . APP_TABLE_PREFIX . "record_search_key_author_id
			WHERE rek_fields_of_research_pid = rek_author_id_pid AND
				rek_author_id > 0 AND
				rek_fields_of_research_pid IN(SELECT DISTINCT rek_pid
				FROM " . APP_TABLE_PREFIX . "record_search_key, " . APP_TABLE_PREFIX . "record_search_key_author_id
				WHERE rek_author_id_pid = rek_pid
				AND rek_display_type= " . $data_collection_xdis_id . ")
			ORDER BY rek_author_id, rek_fields_of_research"; */



$stmt2 = "SELECT rek_author_id AS aut_id, rek_fields_of_research
            FROM " . APP_TABLE_PREFIX . "record_search_key_fields_of_research
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key_author_id ON rek_fields_of_research_pid = rek_author_id_pid AND rek_author_id > 0
            INNER JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = rek_fields_of_research_pid AND rek_display_type = " . $data_collection_xdis_id . "
            GROUP BY aut_id, rek_fields_of_research
            ORDER BY rek_author_id, rek_fields_of_research";


try {
		$result2 = $db->fetchAll($stmt2, array(), Zend_Db::FETCH_ASSOC);
}
catch(Exception $ex) {
	$log->err($ex);
	return array();
}

$sekDetails = Search_Key::getDetailsByTitle("Fields of Research");

$list2 = $result2;

//merge $list2 into $list
$k = 0;
for ($i=0;$i<count($list);$i++) {
$new = 0; //initialise for each new author
	for ($j=0;$j<count($list2);$j++) {
		if ($list[$i]['aut_id'] == $list2[$j]['aut_id']) {
			if ($new == $list2[$j]['aut_id']) {
				$k++;

				$list[$i]['rek_fields_of_research'][$k] = trim($list2[$j]['rek_fields_of_research']);
        if ($sekDetails['sek_lookup_function'] != '' && $list[$i]['rek_fields_of_research'][$k] != '') {
          $lookupFunction = $sekDetails['sek_lookup_function'];
          $temp = '';
          eval("\$temp = ".$lookupFunction."(".$list[$i]['rek_fields_of_research'][$k].");");
          if ($temp != '') {
            $list[$i]['rek_fields_of_research'][$k] = $temp;
          }
        }
				$new = 0;
			}
			else {
				$list[$i]['rek_fields_of_research'][0] = trim($list2[$j]['rek_fields_of_research']);
        if ($sekDetails['sek_lookup_function'] != '' && $list[$i]['rek_fields_of_research'][0] != '') {
          $lookupFunction = $sekDetails['sek_lookup_function'];
          $temp = '';
          eval("\$temp = ".$lookupFunction."(".$list[$i]['rek_fields_of_research'][0].");");
          if ($temp != '') {
            $list[$i]['rek_fields_of_research'][0] = $temp;
          }
        }
        $k = 0;
				$new = $list[$i]['aut_id'];
			}
		}
	}
}

$tpl->assign("list", $list);
$tpl->assign("app_hostname", APP_HOSTNAME);

header("Content-type: text/xml");
$tpl->displayTemplate();
