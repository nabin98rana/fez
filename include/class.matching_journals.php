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

define("TEST",   		 			false); // limit to 250 records only if TRUE
define("SIMILARITY_THRESHOLD",		80);    // These similarity functions aren't currently invoked
define("WINDOW_START",				'2003-01-01 00:00:00');

class RJL
{
	function matchAll()
	{
		echo "======================================\n";
		echo "RJL Matching Utility\n";
		echo date('d/m/Y H:i:s') . "\n";
		
		$matchesI = array(); // ISSN matches
		$matchesT = array(); // Journal title matches
		$matchesC = array(); // Conference title matches
		$matchesS = array(); // Similar title matches
		$matchesM = array(); // Manual matches
		$matchesF = array(); // Forced matches
		$matches = array();  // All matches
		
		$candidateJournals = RJL::getCandidateJournals();
		$candidateISSNs = RJL::getCandidateISSNs();
		$candidateConferences = RJL::getCandidateConfs();		
		$rankedJournals = RJL::getRankedJournals();
		$rankedJournalISSNs = RJL::getISSNsRJL();
		$manualMatches = RJL::getManualMatches();
		RJL::getForcedMatches($matchesF);
		
		/* Perform normalisation */
		$normalisedCandidateJournals = RJL::normaliseListOfTitles($candidateJournals);
		$normalisedCandidateISSNs = RJL::normaliseListOfISSNs($candidateISSNs);
		$normalisedCandidateConferences = RJL::normaliseListOfTitles($candidateConferences);
		$normalisedRankedJournals = RJL::normaliseListOfTitles($rankedJournals);
		$normalisedRankedJournalISSNs = $rankedJournalISSNs;

		/* See how many unique records we're really talking about here */
		$master = array_merge($candidateJournals, $candidateISSNs, $candidateConferences);
		$master = RJL::keyMasterList($master);

		/* Print some information about the number of items found */
		echo "Number of candidate journal titles: " . sizeof($candidateJournals) . "\n";
		echo "Number of candidate ISSNs: " . sizeof($normalisedCandidateISSNs) . "\n";
		echo "Number of candidate conferences: " . sizeof($normalisedCandidateConferences) . "\n";
		echo "Total number of candidate records: " . sizeof($master) . "\n";
		echo "Number of ranked journals: " . sizeof($rankedJournals) . "\n";
		echo "Number of ranked ISSNs: " . sizeof($normalisedRankedJournalISSNs) . "\n";

		/* Look for ISSN matches */
		RJL::lookForMatchesByISSN($normalisedCandidateISSNs, $normalisedRankedJournalISSNs, $matchesI);
		echo "Number of ISSN matches: " . sizeof($matchesI) . "\n";

		/* Look for title matches (string normalisation and comparison) */
		RJL::lookForMatchesByStringComparison($normalisedCandidateJournals, $normalisedRankedJournals, $matchesT, "T");
		echo "Number of normalised string matches (journal): " . sizeof($matchesT) . "\n";

		/* Look for conference matches (string normalisation and comparison) */
		RJL::lookForMatchesByStringComparison($normalisedCandidateConferences, $normalisedRankedJournals, $matchesC, "C");
		echo "Number of normalised string matches (conference): " . sizeof($matchesC) . "\n";

		/* Look for similar title matches (uses normalised strings for comparison) */
		/*RJL::lookForMatchesBySimilarStrings($normalisedCandidateJournals, $normalisedRankedJournals, $matchesS);
		echo "Number of similar string matches: " . sizeof($matchesS) . "\n";
		*/
		
		/* Look for manual matches */
		RJL::lookForManualMatches($normalisedCandidateJournals, $manualMatches, $matchesM);
		echo "Number of manual matches: " . sizeof($matchesM) . "\n";
		
		/* Assemble list of all matches */
		$matches = array_merge($matchesT, $matchesI, $matchesM, $matchesC, $matchesS, $matchesF);
		echo "Total number of matches: " . sizeof($matches) . "\n";

		/* Subtract matches from list before printing unmatched */
		/*
		$unmatched = $normalisedCandidateJournals;
		RJL::subtractMatchesFromCandidates(&$unmatched, $matchesI);
		RJL::subtractMatchesFromCandidates(&$unmatched, $matchesS);
		RJL::subtractMatchesFromCandidates(&$unmatched, $matchesT);
		RJL::subtractMatchesFromCandidates(&$unmatched, $matchesM);

		echo "Number of ISSN matches: " . sizeof($matchesI) . "\n";
		echo "Number of journal title matches: " . sizeof($matchesT) . "\n";
		echo "Number of conference title matches: " . sizeof($matchesC) . "\n";
		echo "Number of manual matches: " . sizeof($matchesM) . "\n";
		echo "Total number of matches: " . sizeof($matches) . "\n";
		
		// PRINT UNMATCHED JOURNALS (SPECIAL CASE)
		// Remove the title matches from the original candidate journal list
		$nonMatchingJournals = array_diff($coreJournals, RJL::keyMasterList($matchesT));
		echo "Number of non-matching journals: " . sizeof($nonMatchingJournals) . "\n";
		$nonMatchingJournals = array_diff($nonMatchingJournals, RJL::keyMasterList($matchesI)); // Also remove the ISSN matches
		echo "Number of non-matching journals after ISSN subtraction: " . sizeof($nonMatchingJournals) . "\n";
		$nonMatchingJournals = array_diff($nonMatchingJournals, RJL::keyMasterList($matchesM)); // Next remove the manual matches
		echo "Number of non-matching journals after manual match subtraction: " . sizeof($nonMatchingJournals) . "\n";
		
		// PRINT UNMATCHED CONFERENCES (SPECIAL CASE)
		// Remove the title matches from the original candidate conference list
		$nonMatchingConferences = array_diff($coreConferences, RJL::keyMasterList($matchesC));
		echo "Number of non-matching conferences: " . sizeof($nonMatchingConferences) . "\n";
		$nonMatchingConferences = array_diff($nonMatchingConferences, RJL::keyMasterList($matchesC)); // Also remove the ISSN matches
		echo "Number of non-matching conferences after ISSN subtraction: " . sizeof($nonMatchingConferences) . "\n";
		$nonMatchingConferences = array_diff($nonMatchingConferences, RJL::keyMasterList($matchesC)); // Next remove the manual matches
		echo "Number of non-matching conferences after manual match subtraction: " . sizeof($nonMatchingConferences) . "\n";
		*/
			
		/* Insert all the found matches */
		RJL::runInserts($matches);
		
		return;
	}
	
	
	
	function matchOne($pid)
	{
		echo "Match a single PID here. Rar!";
		
		// LKDB - TODO!
		
		return;
	}
	
	
	
	function getCandidateJournals()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		echo "Running query to build candidate journals list ... ";
		$candidateJournals = array();
	
		$stmt = "	
			SELECT
				rek_pid AS record_pid,
				rek_journal_name AS journal_title
			FROM
				" . APP_TABLE_PREFIX . "record_search_key, " . APP_TABLE_PREFIX . "record_search_key_journal_name, " . APP_TABLE_PREFIX . "xsd_display
			WHERE
				" . APP_TABLE_PREFIX . "record_search_key_journal_name.rek_journal_name_pid = " . APP_TABLE_PREFIX . "record_search_key.rek_pid
				AND rek_display_type = xdis_id
				AND " . APP_TABLE_PREFIX . "record_search_key.rek_date >= '" . WINDOW_START . "'
				AND xdis_title IN ('Conference Paper', 'Conference Item', 'Journal Article', 'RQF 2006 Journal Article', 'RQF 2006 Conference Paper', 'RQF 2007 Journal Article', 'RQF 2007 Conference Paper', 'Online Journal Article')
			ORDER BY
				journal_title ASC
		";
		
		if (TEST) {
			$stmt .= " LIMIT 250;";
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
				$candidateJournals[$row['record_pid']] = $row['journal_title'];
			}
		}
		
		echo "done.\n";
		
		return $candidateJournals;
	}
	
	
	
	function getCandidateISSNs()
	{
		$log = FezLog::get();
		$db = DB_API::get();
	
		echo "Running query to build candidate ISSNs list ... ";
		$candidateISSNs = array();
	
		$stmt = "
			SELECT
				rek_pid AS record_pid,
				rek_issn AS issn
			FROM
				" . APP_TABLE_PREFIX . "record_search_key, " . APP_TABLE_PREFIX . "record_search_key_issn, " . APP_TABLE_PREFIX . "xsd_display
			WHERE
				" . APP_TABLE_PREFIX . "record_search_key_issn.rek_issn_pid = " . APP_TABLE_PREFIX . "record_search_key.rek_pid
				AND rek_display_type = xdis_id
				AND " . APP_TABLE_PREFIX . "record_search_key.rek_date >= '" . WINDOW_START . "'
				AND xdis_title IN ('Conference Paper', 'Conference Item', 'Journal Article', 'RQF 2006 Journal Article', 'RQF 2006 Conference Paper', 'RQF 2007 Journal Article', 'RQF 2007 Conference Paper', 'Online Journal Article')
			ORDER BY
				issn ASC
		";
		
		if (TEST) {
			$stmt .= " LIMIT 250;";
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
				$candidateISSNs[$row['record_pid']] = $row['issn'];
			}
		}
		
		echo "done.\n";
		
		return $candidateISSNs;
	}
	
	
	
	function getCandidateConfs()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		echo "Running query to build candidate conference list ... ";
		$candidateConferences = array();

		$stmt = "	
			SELECT
				rek_pid AS record_pid,
				rek_proceedings_title AS conference_name
			FROM
				" . APP_TABLE_PREFIX . "record_search_key, " . APP_TABLE_PREFIX . "record_search_key_proceedings_title, " . APP_TABLE_PREFIX . "xsd_display
			WHERE
				" . APP_TABLE_PREFIX . "record_search_key_proceedings_title.rek_proceedings_title_pid = " . APP_TABLE_PREFIX . "record_search_key.rek_pid
				AND rek_display_type = xdis_id
				AND " . APP_TABLE_PREFIX . "record_search_key.rek_date >= '" . WINDOW_START . "'
				AND xdis_title IN ('Conference Paper', 'Conference Item', 'Journal Article', 'RQF 2006 Journal Article', 'RQF 2006 Conference Paper', 'RQF 2007 Journal Article', 'RQF 2007 Conference Paper')
			ORDER BY
				conference_name ASC
		";
		
		if (TEST) {
			$stmt .= " LIMIT 250;";
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
	
	
	
	function getRankedJournals()
	{
		$log = FezLog::get();
		$db = DB_API::get();
				
		echo "Running query to build ranked journals list ... ";
		$rankedJournals = array();

		$stmt = "
			SELECT
				eraid,
				title
			FROM
				__era_journals
			ORDER BY
				title ASC;
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
		    	$rankedJournals[$row['eraid']] = $row['title'];
		    }
		}
		
		echo "done.\n";
		
		return $rankedJournals;
	}
	
	
	
	function getManualMatches()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		echo "Retrieving list of manual matches ... ";
		$manualMatches = array();

		$stmt = "
			SELECT
				journal_title,
				eraid
			FROM
				__era_manual_journal_matches
			ORDER BY
				journal_title ASC;
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
		    	$manualMatches[$row['eraid']] = $row['journal_title'];
		    }
		}
		
		echo "done.\n";
		
		return $manualMatches;
	}
	
	
	
	function getISSNsRJL()
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		echo "Running query to build ranked journal ISSN list ... ";
		$rankedJournalISSNs = array();

		$stmt = "
			SELECT
				issn,
				__era_journals.eraid
			FROM
				__era_journals,
				__era_journal_issns
			WHERE
				__era_journals.eraid = __era_journal_issns.eraid
			ORDER BY
				issn ASC,
				number ASC;
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
		    	$issn = RJL::normaliseISSN($row['issn']);
		    	$rankedJournalISSNs[$issn] = $row['eraid'];
		    }
		}
		
		echo "done.\n";
		
		return $rankedJournalISSNs;
	}
	

	
	function normaliseListOfTitles($titles)
	{
		foreach ($titles as &$title) {
			$title = RJL::normaliseTitle($title);
		}
		
		return $titles;
	}
	
	
	
	function normaliseListOfISSNs($issns)
	{
		foreach ($issns as &$issn) {
			$issn = RJL::normaliseISSN($issn);
		}
		
		return $issns;
	}
	
	
	
	function normaliseTitle($title)
	{
		$title = strtolower($title);
		$title = RJL::strip_punctuation($title);
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
	
	
	
	function normaliseISSN($issn)
	{
		$issn = preg_replace("/[^0-9\-X]/", "", $issn);
		
		return $issn;		
	}
	
	
	
	function lookForMatchesByISSN($check, $against, &$matches)
	{
		echo "Running ISSN match ... ";
		
		/* Step through each source item */
		foreach ($check as $sourceKey => $sourceVal) {
			
			/* Reset match position and value */
			$earliestMatch = '';
			$earliestMatchPosition = 999999;
			
			/* Attempt to match it against each target item */
			foreach ($against as $targetKey => $targetVal) {
				/* Look for the target strng inside the source */
				
				if (substr_count($sourceVal, $targetKey) > 0) {
					$pos = strpos($sourceVal, $targetKey);

					/* Store the earliest occuring match */					
					if ($pos < $earliestMatchPosition) {
						$earliestMatch = $targetVal;
						$earliestMatchPosition = $pos;
					}
					
					//echo "I";
				}
			}

			if ($earliestMatchPosition < 999999) {
				$matches[$sourceKey] = $earliestMatch;
			}
		}
		
		echo " done.\n";
		
		return;
	}
	
	
	
	function lookForMatchesByStringComparison($check, $against, &$matches, $type)
	{
		echo "Running normalised string match ... ";
		
		$exceptions = RJL::getTitleMatchExceptionList(); // Retrieve list of exceptions.
		
		/* Step through each source item */
		foreach ($check as $sourceKey => $sourceVal) {

			/* Attempt to match it against each target item */
			foreach ($against as $targetKey => $targetVal) {
				/* Test for exact string match */
				if ($sourceVal == $targetVal) {
					
					/* Make sure this PID isn't on the exceptions list. */
					if (!in_array($sourceKey, $exceptions)) {
						//echo $type;
						$matches[$sourceKey] = $targetKey;	
					}										
					
				}
			}
		}
		
		echo " done.\n";
		
		return;
	}



	function lookForMatchesBySimilarStrings($check, $against, &$matches)
	{
		echo "Running similar strings match ... ";
		
		/* Step through each source item */
		foreach ($check as $sourceKey => $sourceVal) {
			foreach ($against as $targetKey => $targetVal) {
				similar_text($sourceVal, $targetVal, $similarity);
				if ($similarity > SIMILARITY_THRESHOLD && $similarity != 100) {
					//echo $sourceVal . " :: " . $targetVal . "\n"; // LKDB
					//echo "Similarity = " . $similarity . "%\n\n"; // LKDB
					
					//echo "S";
					$matches[$sourceKey] = $targetKey;
				}
			}
		}
		
		echo " done.\n";
		
		return;
	}
	
	
	
	function subtractMatchesFromCandidates(&$candidates, $matches)
	{
		echo "Removing matches from journal pool ... ";
		
		foreach ($matches as $matchKey => $matchVal){
			unset($candidates[$matchKey]);
		}
		
		echo " done.\n";
		
		return;
	}
	
	
	
	function lookForManualMatches($check, $manualMatches, &$matches)
	{
		echo "Checking un-matched journals for manual matches... ";
		
		foreach ($check as $sourceKey => $sourceVal) {
			/* Attempt to match it against each target item */
			foreach ($manualMatches as $targetKey => $targetVal) {
				/* Test for exact string match */
				if ($sourceVal == $targetVal) {
					//echo "M";
					$matches[$sourceKey] = $targetKey;
				}				
			}
		}
		
		echo " done!\n";
		
		return;
	}
	
	
	
	function keyMasterList($toClean)
	{
		$clean = array();		
		foreach ($toClean as $key => $val) {
			array_push($clean, $key);
		}
		
		return $clean;
	}
	
	
	
	function getTitleMatchExceptionList()
	{
		$exceptions = array('UQ:133037');
		return $exceptions;
	}
	
	
	
	function getForcedMatches(&$matches)
	{
		$matches['UQ:72013'] = 9773;
		
		return;
	}
	
	
	
	function runInserts($matches)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		echo "Running insertion queries on eSpace database ... ";
		
		foreach ($matches as $pid => $eraid) {
			$stmt = "INSERT INTO " . APP_TABLE_PREFIX . "matched_journals (mtj_pid, mtj_eraid, mtj_status) VALUES ('" . $pid . "', '" . $eraid . "', 'A') ON DUPLICATE KEY UPDATE mtj_eraid = '" . $eraid . "';";
			
			try {
				$db->exec($stmt);
			}
			catch(Exception $ex) {
				$log->err($ex);
				die('There was a problem with the query ' . $stmt);
			}
			
			//echo $stmt . "\n"; // This will tell us what's actually going to be run.
		}
		
		echo "done.\n";
		
		return;
	}

}
