<?php

// One off script to find and remove all invalid doi links
// Invalid doi links are characterised by pids that have: 
// - a 'link' datastream and 
// - no 'link_##' datastream

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';

echo "Script started: " . date('Y-m-d H:i:s') . "\n";

// get listing of all pids in fedora
$fedoraPids = Fedora_Direct_Access::fetchAllFedoraPIDs('');
echo "Checking " . count($fedoraPids) . " fedora pids\n";

// for each pid,
foreach ($fedoraPids as $pidDetails) {
	$pid = $pidDetails['pid'];
	
	$linkDSExists = false;
	$linkUnderscoreDSExists = false;
	$linkDOIExists = false;

	// check the datastreams (lite)
	$datastreams = Fedora_API::callGetDatastreams($pid);
	
	// get some prelim data, so we don't have to do the full datastream check if we don't need to
	foreach($datastreams as $ds) {
		if ($ds['ID'] == 'DOI' && $ds['state'] == 'A') {
			$linkDOIExists = true;
			print_r($ds);
			die();
		} elseif ($ds['ID'] == 'link' && $ds['state'] == 'A') {
			$linkDSExists = true;
		} elseif (!$linkUnderscoreDSExists && strpos($ds['ID'], 'link_') === 0 && $ds['state'] == 'A') {
			$linkUnderscoreDSExists = true;
		}
	}

	if ($linkDOIExists) {
		$linkDSString = $linkDSExists ? 'a link datastream' : '';
		$linkUnderscoreString = $linkUnderscoreDSExists ? 'a link_* datastream' : '';
		echo "{$pid} has a DOI datastream {$linkDSString} {$linkUnderscoreString}\n";
	}

	// // we're only interested in pids that have a 'link' datastream
	// if ($linkDSExists) {
	// 	// if the 'link' datastream exists, but no 'link_*' datastream exists, then just rename link to link_1 datastream
	// 	if (!$linkUnderscoreDSExists) {
	// 		echo "Renaming 'link' to 'link_1' as it's the only link on {$pid}\n";
	// 		// Record::renameDatastream($pid, 'link', 'link_1');
	// 	}  else {
	// 		// get the full set of datastream information
	// 		$datastreams = Fedora_API::callGetDatastreams($pid);
	// 
	// 		$linkUrlExistsInLinkUnderscoreUrls = false;
	// 		// $linkNumbers = array();
	// 		$linkUrl = '';
	// 		$linkUrlsInUnderscores = array();
	// 		
	// 		foreach($datastreams as $ds) {
	// 			if ($ds['ID'] == 'link') {
	// 				$linkUrl = $ds['location'];
	// 			} elseif (strpos($ds['ID'], 'link_') === 0) {
	// 				$linkUrlsInUnderscores[substr($ds['ID'], strlen('link_'))] = $ds['location'];
	// 			}
	// 		}
	// 
	// 		// the lite datastreams can bring back old datastreams that have been removed, but listDatastreams doesn't.
	// 		if ($linkUrl) {
	// 			$linkUrlExistsInLinkUnderscoreUrls = in_array($linkUrl, $linkUrlsInUnderscores);
	// 
	// 			// if the 'link' datastream doesn't match any of the 'link_*' datastreams, then rename the 'link' datastream to the max 'link_' datastream + 1
	// 			if (!$linkUrlExistsInLinkUnderscoreUrls) {
	// 				$linkNumbers = array_keys($linkUrlsInUnderscores);
	// 				sort($linkNumbers, SORT_NUMERIC);
	// 				$number = $linkNumbers[count($linkNumbers)-1] + 1;
	// 				echo "Renaming 'link' datastream to 'link_{$number}' datastream on {$pid}\n";
	// 				// Record::renameDatastream($pid, 'link', "link_{$number}");
	// 			} else {
	// 				// if the url already exists, remove the 'link' datastream
	// 				echo "Purging 'link' datastream from {$pid}\n";
	// 				// Fedora_API::callPurgeDatastream ($pid, 'link', NULL, NULL, $logMessage="Purged invalid DOI LINK datastream from Fez"); 
	// 			}
	// 		}
	// 	}
	// }
}

echo "Script finished: " . date('Y-m-d H:i:s') . "\n";
