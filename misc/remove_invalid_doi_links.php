<?php

// One off script to find and remove all invalid doi links
// Invalid doi links are characterised by pids that have: 
// - a 'link' datastream OR a 'DOI' datastream


include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';

echo "Script started: " . date('Y-m-d H:i:s') . "\n";
main();
echo "Script finished: " . date('Y-m-d H:i:s') . "\n";

function main() {
	
	$log = FezLog::get();
	
	// get listing of all pids in fedora
	$fedoraPids = Fedora_Direct_Access::fetchAllFedoraPIDs('');
	$log->debug("Checking " . count($fedoraPids) . " fedora pids");

	

	// for each pid,
	foreach ($fedoraPids as $pidDetails) {
		$pid = $pidDetails['pid'];
	
		$linkDSExists = false;
		$linkUnderscoreDSExists = false;
		$linkDOIExists = false;
		$linkUrl = '';
		$linkUrlsInUnderscores = array();
		$linkUrlInDOI = '';
		$DOIDetails = null;
		$newLinkDatastreamNumber = 0;
		$linkUrlNumbers = array();

		// check the datastreams
		$datastreams = Fedora_API::callGetDatastreams($pid);
	
		// get some prelim data
		foreach($datastreams as $ds) {
			if ($ds['ID'] == 'link' && $ds['state'] == 'A') {
				$linkDSExists = true;
				$linkUrl = cleanLocationUrl($ds['location']);
			} elseif (strpos($ds['ID'], 'link_') === 0) {
				// we're mostly interested in the active links
				if ($ds['state'] == 'A') {
					$linkUnderscoreDSExists = true;
					$linkUrlsInUnderscores[] = cleanLocationUrl($ds['location']);
				}
				// but we want all the link numbers so that we can name this link uniquely (if necessary)
				$linkUrlNumbers[] = substr($ds['ID'], strlen('link_'));
			} elseif ($ds['ID'] == 'DOI' && $ds['state'] == 'A') {
				$linkDOIExists = true;
				$linkUrlInDOI = cleanLocationUrl($ds['location']);
				$DOIDetails = $ds;
			}
		}


		// we're only interested in pids that have either a link datastream or a DOI datastream
		if (!$linkDSExists && !$linkDOIExists) {
			continue;
		}

		$newLinkDatastreamNumber = max($linkUrlNumbers)+1;

		// for pids that have a link datastream
		if ($linkDSExists) {
			// if the 'link' datastream exists, but no 'link_*' datastream exists, then just rename link to link_1 datastream
			if (!$linkUnderscoreDSExists) {
				$log->debug("Renaming 'link' to 'link_1' as it's the only link on {$pid}");
				Record::renameDatastream($pid, 'link', 'link_1', 'DOI: renamed datastream as part of removing old DOI links');
			}  else {
			
				// if the 'link' datastream doesn't match any of the 'link_*' datastreams, then rename the 'link' datastream to the max 'link_' datastream + 1
				if (!in_array($linkUrl, $linkUrlsInUnderscores)) {
					$log->debug("Renaming 'link' datastream to 'link_{$newLinkDatastreamNumber}' datastream on {$pid}");
					Record::renameDatastream($pid, 'link', "link_{$newLinkDatastreamNumber}");
					$linkUrlsInUnderscores[] = $linkUrl; // add it for the DOI check below
					$newLinkDatastreamNumber++;
				} else {
					// if the url already exists, remove the 'link' datastream
					$log->debug("Purging 'link' datastream from {$pid}");
					Fedora_API::callPurgeDatastream ($pid, 'link', NULL, NULL, $logMessage="DOI: Purged invalid 'LINK' datastream as part of removing old DOI links"); 
				}
			}
		}
	
		// deal with doi datastreams
		if ($linkDOIExists) {
			// check if the doi link already exists
			if (!in_array($linkUrlInDOI, $linkUrlsInUnderscores)) {
				// if not, add a link_* datastream with all the same details as the DOI datastream
				$log->debug("Renaming 'DOI' datastream to 'link_{$newLinkDatastreamNumber}' datastream on {$pid}");
				Record::renameDatastream($pid, 'DOI', "link_{$newLinkDatastreamNumber}", "DOI: renamed datastream as part of removing old DOI links");
			}
		}
	}
}

// cleans up the location url (by removing ezproxy prefix and trimming the result)
function cleanLocationUrl($url) {
	$url = trim($url);
	$httpPosition = strrpos($url, "http://");
	if ($httpPosition !== 0) {
		// we have two (or more) http's here, just grab the last one
		$url = substr($url, $httpPosition);
	}
	return $url;
}

