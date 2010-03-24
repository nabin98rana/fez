<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005-2010 The University of Queensland,                |
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
// | Authors: Marko Tsoi <m.tsoi@library.uq.edu.au>                       |
// +----------------------------------------------------------------------+

include_once('../config.inc.php');
include_once(APP_INC_PATH.'/class.fedora_direct_access.php');
include_once(APP_INC_PATH . "Apache/Solr/Service.php");

echo "Script started: " . date("Y-m-d H:i:s") . "\n";
main();
echo "Script Finished: " . date("Y-m-d H:i:s") . "\n";

/**
 * Main function, runs everything
 *
 * @return void
 **/
function main() {
	doFedoraFezIntegrityChecks();
	doFezSolrIntegrityChecks();
	doSolrCitationChecks();
}

/**
 * checks to see if there are any pids marked as deleted in fedora that still exist in the record search key table
 *
 * @return void
 **/
function doFedoraFezIntegrityChecks() {
	$log = FezLog::get();
	$db = DB_API::get();
	$prefix = APP_TABLE_PREFIX;
	$countInserted = 0;

	// get the fedora pids
	$fedoraDeletedPids = Fedora_Direct_Access::fetchAllFedoraPIDs('', 'D');

	$db->query("TRUNCATE TABLE {$prefix}integrity_index_ghosts");
	
	// for each pid, check if it exists in fez, and if so, put into the exceptions table
	// note, we're checking for items earlier than today
	$stmt = "SELECT * FROM {$prefix}record_search_key WHERE rek_pid = ? AND rek_created_date < DATE_SUB(NOW(), INTERVAL 1 DAY)";
	foreach($fedoraDeletedPids as $fedoraPid) {
		$result = $db->fetchOne($stmt, $fedoraPid['pid']);
		if ($result == $fedoraPid['pid']) {
			$db->insert("{$prefix}integrity_index_ghosts", array('pid'=>$result));
			$countInserted++;
		}
	}
	echo "\tFound {$countInserted} pids that are in fedora but not in fez\n";
}

/**
 * checks to see if there are any fez pids which are not in solr and any solr pids that are not in fez
 *
 * @return void
 **/
function doFezSolrIntegrityChecks() {
	$log = FezLog::get();
	$db = DB_API::get();
	$prefix = APP_TABLE_PREFIX;
	$countInsertedGhosts = 0;
	$countInsertedUnspawned = 0;
	
	// grab all fez pids
	$fezPidsQuery = "SELECT rek_pid FROM {$prefix}record_search_key";
	$fezPids = $db->fetchCol($fezPidsQuery);

	// grab all solr pids
	$params['fl'] = 'id';
	// find all items
	$solrQuery = 'id:[* TO *]'; 
	
	// get a list of all pids that exist in solr
	$usr_id = Auth::getUserID();
	if (defined(APP_SOLR_SLAVE_HOST) && defined(APP_SOLR_SLAVE_READ) && (APP_SOLR_SLAVE_READ == "ON") && ($readOnly == true) && !is_numeric($usr_id)) {
		$solrHost = APP_SOLR_SLAVE_HOST;
	} else {
		$solrHost = APP_SOLR_HOST;
	}
	$solrPort = APP_SOLR_PORT;
	$solrPath = APP_SOLR_PATH;
  
	$solr = new Apache_Solr_Service($solrHost, $solrPort, $solrPath);
	
	$stmt = "SELECT * FROM {$prefix}record_search_key WHERE rek_pid = ?";
	$response = $solr->search($solrQuery, 0, 999999, $params);
	foreach ($response->response->docs as $doc) {
		$solrPids[] = $doc->id;
	}
	unset($response);

	// truncate the two result tables
	$db->query("TRUNCATE TABLE {$prefix}integrity_solr_ghosts");
	$db->query("TRUNCATE TABLE {$prefix}integrity_solr_unspawned");
	
	// compare arrays, finding pids that exist in one but not the other
	$pidsInFezNotInSolr = array_diff($fezPids, $solrPids);
	$pidsInSolrNotInFez = array_diff($solrPids, $fezPids);
	
	foreach($pidsInFezNotInSolr as $pid) {
		$db->insert("{$prefix}integrity_solr_unspawned", array('pid'=>$pid));
		$countInsertedUnspawned++;
		
	}
	foreach($pidsInSolrNotInFez as $pid) {
		$db->insert("{$prefix}integrity_solr_ghosts", array('pid'=>$pid));
		$countInsertedGhosts++;
	}
	
	echo "\tFound {$countInsertedGhosts} pids that are in solr but not in fez\n";
	echo "\tFound {$countInsertedUnspawned} pids that are in fez but not in solr\n";
	
}

/**
 * checks to make sure that all solr items have citations
 *
 * @return void
 **/
function doSolrCitationChecks() {
	$log = FezLog::get();
	$db = DB_API::get();
	$prefix = APP_TABLE_PREFIX;
	$countInserted = 0;

	$db->query("TRUNCATE TABLE {$prefix}integrity_solr_unspawned_citations");

	// we want just the pid
	$params['fl'] = 'id';
	// find where the citation_t field has no value
	$solrQuery = '-citation_t:[* TO *]'; 
	
	$usr_id = Auth::getUserID();
	if (defined(APP_SOLR_SLAVE_HOST) && defined(APP_SOLR_SLAVE_READ) && (APP_SOLR_SLAVE_READ == "ON") && ($readOnly == true) && !is_numeric($usr_id)) {
		$solrHost = APP_SOLR_SLAVE_HOST;
	} else {
		$solrHost = APP_SOLR_HOST;
	}
	$solrPort = APP_SOLR_PORT;
	$solrPath = APP_SOLR_PATH;
  
	$solr = new Apache_Solr_Service($solrHost, $solrPort, $solrPath);
	
	$response = $solr->search($solrQuery, 0, 999999, $params);
	foreach ($response->response->docs as $doc) {
		$db->insert("{$prefix}integrity_solr_unspawned_citations", array('pid'=>$doc->id));
		$countInserted++;
	}
	echo "\tFound {$countInserted} pids that don't have citations in solr\n";
	
}

