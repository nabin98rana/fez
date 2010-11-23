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
// | Authors: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+

define("TEST", false); // Limit to 50 records only if TRUE
define("WINDOW_START",				'2003-01-01 00:00:00');
define("WINDOW_END",				'2099-01-01 00:00:00');

class RCL
{
	function matchAll()
	{
		echo "======================================\n";
		echo "RCL Matching Utility\n";
		echo date('d/m/Y H:i:s') . "\n";
		
		$matches = array();
		$candidateConferences = RCL::getCandidateConferences();
		$rankedConferences = RCL::getRankedConferences();
		$rankedConferencesAcronyms = RCL::getRankedConferenceAcronyms();
		
		/* Print some information about the number of items found */
		echo "Number of candidate conferences: " . sizeof($candidateConferences) . "\n";
		echo "Number of ranked conferences: " . sizeof($rankedConferences) . "\n";
		echo "Number of conference acronyms: " . sizeof($rankedConferencesAcronyms) . "\n";
		
		/* Perform normalisation */
		$normalisedCandidateConferences = RCL::normaliseListOfTitles($candidateConferences);
		$normalisedRankedConferences = RCL::normaliseListOfTitles($rankedConferences);
		
		// We need to do this in the order of least likely to most likely matching, so that the later,
		// more-likely-correct matches get precedence over the new ones.
		
		/* Look for acronym matches */
		RCL::lookForMatchesByAcronym($candidateConferences, $rankedConferencesAcronyms, $matches);
		echo "Number of matches after acronym matching: " . sizeof($matches) . "\n";

		/* Crush matches */
		$crushedCandidateConferences = RCL::crushListOfTitles($normalisedCandidateConferences);
		$crushedRankedConferences = RCL::crushListOfTitles($normalisedRankedConferences);
		RCL::lookForMatchesByStringCrush($crushedCandidateConferences, $crushedRankedConferences, $matches);
		echo "Total number of matches after crushing: " . sizeof($matches) . "\n";

		/* Look for title matches (string normalisation and comparison) */
		RCL::lookForMatchesByStringComparison($normalisedCandidateConferences, $normalisedRankedConferences, $matches);
		echo "Number of normalised string matches: " . sizeof($matches) . "\n";

		/* Look for manual matches */
		//RCL::lookForManualMatches($normalisedCandidateJournals, $manualMatches, $matches); // -- this looks like it was never implemented?
		
		/* Subtract from any match results those PIDs that are either black-listed, or manually mapped */
		$matches = array_diff_key($matches, matching::getMatchingExceptions("C"));
		
		echo "Total number of matches: " . sizeof($matches) . "\n";

		RCL::runInserts($matches);

		return;
	}
	
	
	
	function matchOne($pid)
	{
		echo "Match a single PID here. Rar!";
		
		// LKDB - TODO!
		
		return;
	}
	
	
	
	function getCandidateConferences()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		echo "Running query to build candidate conference list ... ";
		$candidateConferences = array();
	
		$stmt = "	
			SELECT
				rek_pid AS record_pid,
				rek_conference_name AS conference_name
			FROM
				" . APP_TABLE_PREFIX . "record_search_key, " . APP_TABLE_PREFIX . "record_search_key_conference_name
			WHERE
				" . APP_TABLE_PREFIX . "record_search_key_conference_name.rek_conference_name_pid = " . APP_TABLE_PREFIX . "record_search_key.rek_pid
				AND rek_status = 2
				AND " . APP_TABLE_PREFIX . "record_search_key.rek_date > '" . WINDOW_START . "'
				AND " . APP_TABLE_PREFIX . "record_search_key.rek_date < '" . WINDOW_END . "'
			ORDER BY
				conference_name ASC;
		";
		
		if (TEST) {
			$stmt .= " LIMIT 50";
		}
		
		try {
			$result = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if (count($result) > 0) {
			foreach ($result as $key => $row) {
				$candidateConferences[$row['record_pid']] = $row['conference_name'];
			}
		}
		
		echo "done.\n";
		
		return $candidateConferences;
	}
	
	
	
	function getRankedConferences()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		echo "Running query to build ranked conferences list ... ";
		$rankedConferences = array();

		$stmt = "
			SELECT
				cnf_era_id AS eraid,
				cnf_conference_name AS title
			FROM
				" . APP_TABLE_PREFIX . "conference
			ORDER BY
				cnf_conference_name ASC;
		";
		
		try {
			$result = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if (count($result) > 0) {
			foreach ($result as $key => $row) {
				$rankedConferences[$row['eraid']] = $row['title'];
			}
		}
		
		echo "done.\n";
		
		return $rankedConferences;
	}
	
	
	
	function getRankedConferenceAcronyms()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		echo "Running query to build ranked conferences acronym list ... ";
		$rankedConferencesAcronyms = array();

		$stmt = "
			SELECT
				eraid,
				acronym
			FROM (
				SELECT
					cnf_era_id AS eraid,
					cnf_acronym AS acronym,
					COUNT(cnf_acronym) AS acronym_count
				FROM
					" . APP_TABLE_PREFIX . "conference
				WHERE
					cnf_acronym != ''
				GROUP BY
					cnf_acronym
				) Q1
			WHERE acronym_count < 2
			ORDER BY
				acronym ASC;
		";
		
		try {
			$result = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		if (count($result) > 0) {
			foreach ($result as $key => $row) {
				$rankedConferencesAcronyms[$row['eraid']] = $row['acronym'];
			}
		}
		
		echo "done.\n";
		
		return $rankedConferencesAcronyms;
	}
	
	
	
	function normaliseListOfTitles($titles)
	{
		foreach ($titles as &$title) {
			$title = RCL::normaliseTitle($title);
		}
		
		return $titles;
	}
	
	
	
	function normaliseTitle($title)
	{
		$title = strtolower($title);
		$title = RCL::strip_punctuation($title);
		$title = trim($title);

		return $title;
	}
	
	
	
	function strip_punctuation($text)
	{
	    $text = preg_replace("/[:]/", " ", $text); // Replace colons with spaces
	    $text = preg_replace("/[&]/", "and", $text); // Swap ampersands with the word "and"
	    $text = preg_replace("/[^a-z0-9\s]/", " ", $text); // force a-z, 0-9
	    if (strpos($text, "the ") !== false && strpos($text, "the ") == '0') {
	    	$text = substr_replace($text, "", 0, 4); // remove any leading "the ", if one is encountered
	    }
	    $text = preg_replace("/\s{2,}/", " ", $text); // strip any double / induced whitespace
	    
	    return $text;
	}
	
	
	
	function crushListOfTitles($titles)
	{
		foreach ($titles as &$title) {
			$title = RCL::crushTitle($title);
		}
		
		return $titles;
	}
	
	
	
	function crushTitle($title)
	{
		/*
		Take the first letter of each word, uppercase it.
		*/
		
		$nuStr = "";
		$parts = explode(" ", $title);
		foreach ($parts as $part) {
			$firstLetter = strtoupper(substr($part, 0, 1));
			$nuStr .= $firstLetter . " ";
		}
		
		return $nuStr;
	}
	

	
	function lookForMatchesByStringComparison($check, $against, &$matches)
	{
		echo "Running normalised string match ... ";
		
		/* Step through each source item */
		foreach ($check as $sourceKey => $sourceVal) {

			/* Attempt to match it against each target item */
			foreach ($against as $targetKey => $targetVal) {

				/* Test for exact string match */
				if ($sourceVal == $targetVal) {
					//echo "T";
					$matches[$sourceKey] = $targetKey;
				}				
			}
		}
		
		echo " done.\n";
		
		return;
	}
	
	
	
	function lookForMatchesByStringCrush($check, $against, &$matches)
	{
		echo "Running normalised string match ... ";
		
		/* Step through each source item */
		foreach ($check as $sourceKey => $sourceVal) {

			/* Attempt to match it against each target item */
			foreach ($against as $targetKey => $targetVal) {

				/* Test for exact string match */
				if ($sourceVal == $targetVal) {
					//echo "T";
					$matches[$sourceKey] = $targetKey;
				}				
			}
		}
		
		echo " done.\n";
		
		return;
	}
	
	
	
	function lookForMatchesByAcronym($check, $against, &$matches)
	{
		echo "Running acronym match ... ";
		
		/* Step through each source item */
		foreach ($check as $sourceKey => $sourceVal) {
			
			/* Attempt to match it against each target item */
			foreach ($against as $targetKey => $targetVal) {
				
				$targetVal = str_replace('/', '', $targetVal); // Get rid of slashes ... these cause us headaches.
				$regexp = '/^(?:.*[^A-Za-z0-9])*(' . $targetVal . ')(?:[^A-Za-z0-9].*)*$/'; // This will look for the acronym in isolation.
				
				if (preg_match($regexp, $sourceVal)) {
					/* Rule out any values we know we don't want to match on */
					if (
						$targetVal != "Complex"
						&& $targetVal != "Group"
						&& $targetVal != "Information Processing"
						&& $targetVal != "Interaction"
						&& $targetVal != "Coordination"
						&& $targetVal != "Complexity"
						&& $targetVal != "IV"
						&& $targetVal != "VI"
						&& $targetVal != "e-Science"
						&& $targetVal != "Sensor Networks"
						&& $targetVal != "Interact"
						&& $targetVal != "Middleware"
						&& $targetVal != "PRIMA"
						&& $targetVal != "Agile"
						&& $targetVal != "DNA"
						) {
							if (array_key_exists($sourceKey, $matches)) {
								//echo "~DOUBLE MATCH~"; // This is probably bad news.
							} else {
								$matches[$sourceKey] = $targetKey;
								//echo "A";
								//echo $sourceVal . " ?~~~? " . $targetVal . "\n\n";
							}
					}
				}				
			}
		}
		
		echo " done.\n";
		
		return;
	}
	
	
	
	function runInserts($matches)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		echo "\n\nRunning insertion queries on eSpace database ... ";
		
		foreach ($matches as $pid => $eraid) {
			
			$stmt = "INSERT INTO " . APP_TABLE_PREFIX . "matched_conferences (mtc_pid, mtc_eraid, mtc_status) VALUES ('" . $pid . "', '" . $eraid . "', 'A') ON DUPLICATE KEY UPDATE mtc_pid = mtc_pid;";
			
			try {
				$db->exec($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				die('There was a problem with the query ' . $stmt);
			}
			
			//echo $stmt . "\n";
		}
		
		echo "done.\n";
		
		return;
	}
	
}
