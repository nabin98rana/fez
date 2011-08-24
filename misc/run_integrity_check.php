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

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once(APP_INC_PATH.'/class.fedora_direct_access.php');
include_once(APP_INC_PATH . "Apache/Solr/Service.php");


// get the command line options (show warning message if nothing passed in)
if( $argc != 2 ) {
	displayUsage();
    exit(-1);
}

$runType = strtolower($argv[1]);
if (!in_array($runType, array('check','fix','both'))) {
	echo "\nERROR: Invalid mode '{$runType}'\n";
	displayUsage();
	exit(-2);
}

echo "Script started: " . date("Y-m-d H:i:s") . "\n";
main($runType);
echo "Script Finished: " . date("Y-m-d H:i:s") . "\n";

/**
 * Main function, runs everything
 *
 * @return void
 **/
function main($runType = "check") {
	
	// run checks
	if ($runType == 'check' || $runType == 'both') {
		doFedoraFezIntegrityChecks();
		if (APP_SOLR_INDEXER == "ON") {
			doFezSolrIntegrityChecks();
			doSolrCitationChecks();
		}
		doPidAuthChecks();
	}
	// run deletes
	if ($runType == 'fix' || $runType == 'both') {
		doFedoraFezDelete();
		if (APP_SOLR_INDEXER == "ON") {
			doFezSolrDeletes();
			addSolrCitations();
			addSolrUnspawned();
		}
		doPidAuthDeletes();
	}
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

	try {
		// get the fedora pids
		$fedoraDeletedPids = Fedora_Direct_Access::fetchAllFedoraPIDs('', 'D');

		$db->query("TRUNCATE TABLE {$prefix}integrity_index_ghosts");
	
		// for each pid, check if it exists in fez, and if so, put into the exceptions table
		// note, we're checking for items earlier than today
		
		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) { //eg if postgresql etc
			$stmt = "SELECT * FROM {$prefix}record_search_key WHERE rek_pid = ? AND rek_created_date < (NOW() - INTERVAL '1 days')";
		} else {
			$stmt = "SELECT * FROM {$prefix}record_search_key WHERE rek_pid = ? AND rek_created_date < DATE_SUB(NOW(), INTERVAL 1 DAY)";			
		}
		
		foreach($fedoraDeletedPids as $fedoraPid) {
			$result = $db->fetchOne($stmt, $fedoraPid['pid']);
			if ($result == $fedoraPid['pid']) {
				$db->insert("{$prefix}integrity_index_ghosts", array('pid'=>$result));
				$countInserted++;
			}
		}
		echo "\tFound {$countInserted} pids that are in marked as deleted in fedora and also in fez when they shouldn't be\n";
	} catch(Exception $ex) {
		echo "The following exception occurred: " . $ex->getMessage() . "\n";
		$log->err($ex);
		return false;
	}
}

/**
 * Do the delete from fedora/solr 
 *
 * @return void
 **/
function doFedoraFezDelete() {
	$log = FezLog::get();
	$db = DB_API::get();
	$prefix = APP_TABLE_PREFIX;
	
	try {
		$sql = "SELECT pid FROM {$prefix}integrity_index_ghosts";
		$pids = $db->fetchCol($sql);
		foreach($pids as $pid) {
			Record::removeIndexRecord($pid);
		}
		echo "\t" . count($pids) . " deleted from fez (and possibly solr as well)\n";
		
	} catch (Exception $ex) {
		echo "The following exception occurred: " . $ex->getMessage() . "\n";
		$log->err($ex);
		return false;
	}
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
	
	try {
		// grab all fez pids
		$fezPidsQuery = "SELECT rek_pid FROM {$prefix}record_search_key";
		$fezPids = $db->fetchCol($fezPidsQuery);

		// find all items
		$solrQuery = 'id:[* TO *]'; 
	
		$response = doSolrSearch($solrQuery);
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
	} catch(Exception $ex) {
		echo "The following exception occurred: " . $ex->getMessage() . "\n";
		$log->err($ex);
		return false;
	}	
}

/**
 * Do the delete from fedora/solr 
 *
 * @return void
 **/
function doFezSolrDeletes() {
	$log = FezLog::get();
	$db = DB_API::get();
	$prefix = APP_TABLE_PREFIX;
	
	try {
		$sql = "SELECT pid FROM {$prefix}integrity_solr_ghosts";
		$pids = $db->fetchCol($sql);
		foreach($pids as $pid) {
			Record::removeIndexRecord($pid);
		}
		echo "\t" . count($pids) . " deleted from solr\n";
		
	} catch (Exception $ex) {
		echo "The following exception occurred: " . $ex->getMessage() . "\n";
		$log->err($ex);
		return false;
	}
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

	try {
		$db->query("TRUNCATE TABLE {$prefix}integrity_solr_unspawned_citations");

		// find where the citation_t field has no value
		$solrQuery = '-citation_t:[* TO *]'; 
		$response = doSolrSearch($solrQuery);
	
		foreach ($response->response->docs as $doc) {
			$db->insert("{$prefix}integrity_solr_unspawned_citations", array('pid'=>$doc->id));
			$countInserted++;
		}
		echo "\tFound {$countInserted} pids that don't have citations in solr\n";
	} catch(Exception $ex) {
		echo "The following exception occurred: " . $ex->getMessage() . "\n";
		$log->err($ex);
		return false;
	}	
	
}

/**
 * adds citations to solr for pids that didn't have them previously
 *
 * @return void
 **/
function addSolrUnspawned() {

	$log = FezLog::get();
	$db = DB_API::get();
	$prefix = APP_TABLE_PREFIX;

	try {
		$sql = "SELECT pid FROM {$prefix}integrity_solr_unspawned";
		$pids = $db->fetchCol($sql);
		$queue = FulltextQueue::singleton();
		
		foreach($pids as $pid) {
			Citation::updateCitationCache($pid);
			$queue->add($pid);
		}
		$queue->commit();
		echo "\tAdded " . count($pids) . " missing pid in solr\n";
		
	} catch (Exception $ex) {
		echo "The following exception occurred: " . $ex->getMessage() . "\n";
		$log->err($ex);
		return false;
	}
}

/**
 * adds citations to solr for pids that didn't have them previously
 *
 * @return void
 **/
function addSolrCitations() {

	$log = FezLog::get();
	$db = DB_API::get();
	$prefix = APP_TABLE_PREFIX;

	try {
		$sql = "SELECT pid FROM {$prefix}integrity_solr_unspawned_citations";
		$pids = $db->fetchCol($sql);
		$queue = FulltextQueue::singleton();
		
		foreach($pids as $pid) {
			Citation::updateCitationCache($pid);
			$queue->add($pid);
		}
		$queue->commit();
		echo "\tUpdated " . count($pids) . " citations in solr\n";
		
	} catch (Exception $ex) {
		echo "The following exception occurred: " . $ex->getMessage() . "\n";
		$log->err($ex);
		return false;
	}
}


/**
 * Does auth checks for pids
 *
 * @return void
 **/
function doPidAuthChecks() {
	
	$db = DB_API::get();
	$log = FezLog::get();
	$prefix = APP_TABLE_PREFIX;
	
	try {
		$db->query("TRUNCATE TABLE {$prefix}integrity_pid_auth_ghosts");
		
		$sql = "SELECT authi_pid FROM {$prefix}auth_index2 LEFT JOIN {$prefix}record_search_key ON rek_pid = authi_pid WHERE rek_pid IS NULL";
		$pids = $db->fetchCol($sql);
		if (count($pids) > 0) {
			$pids = array_unique($pids);
			$countInserted = 0;
			foreach($pids as $pid) {
				$db->insert("{$prefix}integrity_pid_auth_ghosts", array('pid'=>$pid));
				$countInserted++;
			}
			echo "\tFound {$countInserted} auth rows for missing pids\n";
		} else {
			echo "\tNo missing pids auth indexes were found\n";
		}
	} catch(Exception $ex) {
		echo "The following exception occurred: " . $ex->getMessage() . "\n";
		$log->err($ex);
		return false;
	}	

}

/**
 * Does the delete of the auths for pids that don't exist any more
 *
 * @return void
 **/
function doPidAuthDeletes() {
	$db = DB_API::get();
	$log = FezLog::get();
	$prefix = APP_TABLE_PREFIX;
	
	try {
		$sql = "SELECT pid FROM {$prefix}integrity_pid_auth_ghosts";
		$pids = $db->fetchCol($sql);
		if (count($pids) > 0) {
			$result = AuthIndex::clearIndexAuth($pids);
			if ($result) {
				echo "\t" . count($pids) . " pids auth index were deleted\n";
			} else {
				echo "\t*** There was an error in clearing out the pids auth index\n";
			}
		}
	} catch(Exception $ex) {
		echo "The following exception occurred: " . $ex->getMessage() . "\n";
		$log->err($ex);
		return false;
	}	
	
}

// BUILD OUR OWN VERSION OF THE SOLR SEARCH SERVICE BECAUSE THE GENERAL VERSION HAS A 30 SECOND TIMEOUT
// COPIED AND PASTED FROM Apache_Solr_Service AND MODIFIED AS NECESSARY
function doSolrSearch($query) {
	
	$usr_id = Auth::getUserID();
	if (defined(APP_SOLR_SLAVE_HOST) && defined(APP_SOLR_SLAVE_READ) && (APP_SOLR_SLAVE_READ == "ON") && ($readOnly == true) && !is_numeric($usr_id)) {
		$solrHost = APP_SOLR_SLAVE_HOST;
	} else {
		$solrHost = APP_SOLR_HOST;
	}
	$solrPort = APP_SOLR_PORT;
	$solrPath = APP_SOLR_PATH;
  
	$solr = new Apache_Solr_Service($solrHost, $solrPort, $solrPath);
	
	$params['fl'] = 'id';
	$params['version'] = Apache_Solr_Service::SOLR_VERSION;
	$params['wt'] = Apache_Solr_Service::SOLR_WRITER;
	$params['json.nl'] = $solr->getNamedListTreatment();
	$params['q'] = $query;
	$params['start'] = 0;
	$params['rows'] = 999999;
	$queryString = http_build_query($params, null, '&');

	// because http_build_query treats arrays differently than we want to, correct the query
	// string by changing foo[#]=bar (# being an actual number) parameter strings to just
	// multiple foo=bar strings. This regex should always work since '=' will be urlencoded
	// anywhere else the regex isn't expecting it
	$queryString = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);
	
	$url = "http://{$solrHost}:{$solrPort}{$solr->getPath()}select?{$queryString}";
	
	$raw_response = Misc::processURL($url, null, null, null, null, 600);
	
	if(! $raw_response[0]) {
		$log->err('No response from solr.. trying again.');			
		unset($raw_response);
		sleep(1);
		$raw_response = Misc::processURL($url, null, null, null, null, 600);
		if(! $raw_response[0]) {
			throw new Exception(print_r($raw_response[1], true));
		}			
	}
	$response = new Apache_Solr_Response($raw_response[0], null, true, true);

	return $response;
}

/**
 * helper function to display the usage of this script
 *
 * @return void
 **/
function displayUsage() {
	$prefix = APP_TABLE_PREFIX;
	$scriptName = basename(__FILE__);
	echo "\nUsage: php {$scriptName} [check|fix|both]\n";
	echo " - check = Run the checks, output into the {$prefix}integrity_* tables\n";
	echo " - fix = Fix the problems based on a previous run of this script with the 'check' option\n";
	echo " - both = Run the checks, then the deletes\n";
	echo "\n";
}
