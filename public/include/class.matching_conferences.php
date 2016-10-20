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

include_once(APP_INC_PATH . "class.filecache.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");

define("TEST", false); // Limit to 50 records only if TRUE
define("TEST_WHERE_MC",				""); // Adds this condition to the where statement for eg testing single pids
define("WINDOW_START_MC",				'2008-01-01 00:00:00');
define("WINDOW_END_MC",				'2099-01-01 00:00:00');

class RCL
{
  var $unMatched = "1";
  var $runType = "0";
  var $dupeList = "";

	function matchAll()
	{
		echo "======================================\n";
		echo "RCL Matching Utility\n";
		echo date('d/m/Y H:i:s') . "\n";

		$matches = array();
		$candidateConferences = RCL::getCandidateConferences(2015);
		$rankedConferences = RCL::getRankedConferences();
		//$rankedConferencesAcronyms = RCL::getRankedConferenceAcronyms();

        //We'll assume if and matching is done on a pid, it'll need to be done by hand for other years.
        $matchingExceptions = array(); //matching::getMatchingExceptions("C");
		/* Print some information about the number of items found */
		echo "Number of candidate conferences: " . sizeof($candidateConferences) . "\n";
		echo "Number of ranked conferences: " . sizeof($rankedConferences) . "\n";
		//echo "Number of conference acronyms: " . sizeof($rankedConferencesAcronyms) . "\n";

		/* Perform normalisation */
		$normalisedCandidateConferences = RCL::normaliseListOfTitles($candidateConferences);
		$normalisedRankedConferences = RCL::normaliseListOfTitles($rankedConferences);

		// We need to do this in the order of least likely to most likely matching, so that the later,
		// more-likely-correct matches get precedence over the new ones.

		/* Look for acronym matches */
		//RCL::lookForMatchesByAcronym($candidateConferences, $rankedConferencesAcronyms, $matches);
		//echo "Number of matches after acronym matching: " . sizeof($matches) . "\n";

		/* Crush matches */
		$crushedCandidateConferences = RCL::crushListOfTitles($normalisedCandidateConferences);
		$crushedRankedConferences = RCL::crushListOfTitles($normalisedRankedConferences);
		RCL::lookForMatchesByStringCrush($crushedCandidateConferences, $crushedRankedConferences, $matches);
		echo "Total number of matches after crushing: " . sizeof($matches) . "\n";

		/* Look for title matches (string normalisation and comparison) */
		RCL::lookForMatchesByStringComparison($normalisedCandidateConferences, $normalisedRankedConferences, $matches);
		echo "Number of normalised string matches: " . sizeof($matches) . "\n";

        /* Look for similar matches (string normalisation and comparison on %) */
        RCL::findBestApproxMatch($normalisedCandidateConferences, $normalisedRankedConferences, $matches);
        echo "Number of similar string matches: " . sizeof($matches) . "\n";

		/* Look for manual matches */
		//RCL::lookForManualMatches($normalisedCandidateJournals, $manualMatches, $matches); // -- this looks like it was never implemented?

		/* Subtract from any match results those PIDs that are either black-listed, or manually mapped */
        $okMatches = array();
        /* Subtract from any match results those PIDs that are either black-listed, or manually mapped */
        foreach ($matches as $match) {
          if (!in_array($match['pid'], $matchingExceptions)) {
            $okMatches[] = $match;
          }
        }
        $matches = $okMatches;
        echo "Total number of OK matches: " . sizeof($matches) . "\n";
        ob_flush();

		echo "Total number of matches: " . sizeof($matches) . "\n";

        echo "Dupe list:\n\n" . $this->dupeList . "\n";
        // Email the dupes list to the espace admin email address
        if ($this->dupeList != '') {
            $mail = new Mail_API;
            $mail->setTextBody(stripslashes($this->dupeList));
            $subject = '[' . APP_NAME . '] - Duplicate Journal Matches found, please resolve manually using manual matching';
            $from = APP_EMAIL_SYSTEM_FROM_ADDRESS;
            $to = 'a.brown@library.uq.edu.au'; //APP_ADMIN_EMAIL;
            $mail->send($from, $to, $subject, false);
        }

		//RCL::runInserts($matches);

		return;
	}



	function matchOne($pid)
	{
		echo "Match a single PID here. Rar!";

		// LKDB - TODO!

		return;
	}

	function getCandidateConferences($year = null)
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
			WHERE ".TEST_WHERE_MC."
				" . APP_TABLE_PREFIX . "record_search_key_conference_name.rek_conference_name_pid = " . APP_TABLE_PREFIX . "record_search_key.rek_pid
				AND rek_status = 2
				AND rek_date >= '" . WINDOW_START_MC . "'
				AND rek_date < '" . WINDOW_END_MC . "'
				AND rek_subtype = 'Fully published paper'
			ORDER BY
				conference_name ASC;
		";
    if ($this->unMatched == true) {

      $stmt = "
      SELECT
      rek_pid AS record_pid,
			rek_conference_name AS conference_name
      FROM " . APP_TABLE_PREFIX . "record_search_key INNER JOIN
      " . APP_TABLE_PREFIX . "record_search_key_conference_name ON rek_pid = rek_conference_name_pid INNER JOIN
      " . APP_TABLE_PREFIX . "xsd_display ON rek_display_type = xdis_id
      LEFT JOIN " . APP_TABLE_PREFIX . "matched_conferences ON rek_pid = mtc_pid
      WHERE rek_status = 2  AND ".TEST_WHERE_MC."
      rek_date >= '" . WINDOW_START_MC . "'
			AND rek_date < '" . WINDOW_END_MC . "'
      AND xdis_title IN ('Conference Paper', 'Conference Item', 'Journal Article', 'RQF 2006 Journal Article', 'RQF 2006 Conference Paper', 'RQF 2007 Journal Article', 'RQF 2007 Conference Paper', 'Online Journal Article')
      AND rek_subtype = 'Fully published paper'
      GROUP BY rek_pid, rek_conference_name
      HAVING COUNT(mtc_pid) < 2
      ORDER BY rek_conference_name
      ";
    }

    if ($year == 2015) {
        $stmt = "SELECT rek_pid AS record_pid,
			rek_conference_name AS conference_name FROM fez_record_search_key_herdc_code
        INNER JOIN fez_controlled_vocab AS A
        ON cvo_id = rek_herdc_code
        LEFT JOIN fez_record_search_key_herdc_status
        ON rek_herdc_code_pid = rek_herdc_status_pid
        LEFT JOIN fez_controlled_vocab AS B
        ON rek_herdc_status = B.cvo_id
        LEFT JOIN fez_record_search_key
        ON rek_herdc_code_pid = rek_pid
        LEFT JOIN fez_matched_conferences
        ON mtc_pid = rek_herdc_code_pid AND (mtc_cnf_id > 1952 OR mtc_cnf_id = 0)
        LEFT JOIN fez_conference
        ON cnf_id = mtc_cnf_id
        LEFT JOIN fez_record_search_key_conference_name
        ON rek_conference_name_pid = rek_herdc_code_pid
        WHERE rek_date > '2008'
        AND (mtc_cnf_id IS NULL)
        AND (      (A.cvo_title = 'E1'  AND B.cvo_title = 'Confirmed Code')
            OR (A.cvo_title = 'E1' AND B.cvo_title = 'Provisional Code' AND rek_subtype = 'Fully published paper' )
            OR (rek_genre = 'Conference Paper' AND B.cvo_title = 'Confirmed Code' AND (A.cvo_title = 'B1' OR A.cvo_title = 'C1'))
            OR (rek_genre = 'Conference Paper' AND rek_subtype = 'Fully published paper' AND (A.cvo_title = 'B1' OR A.cvo_title = 'C1') AND (B.cvo_title = 'Provisional Code' OR B.cvo_title IS NULL)))
        LIMIT 1000000;";
    }

    ob_flush();
		if (TEST) {
			$stmt .= " LIMIT 1";
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
				cnf_id,
				cnf_conference_name AS title,
				cnf_era_year
			FROM
				" . APP_TABLE_PREFIX . "conference
			ORDER BY
				cnf_conference_name ASC;
		";

		try {
            $rankedConferences = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
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
				cnf_id,
				acronym,
				cnf_era_year
			FROM (
				SELECT
					cnf_id,
					cnf_acronym AS acronym,
					cnf_era_year,
					COUNT(cnf_acronym) AS acronym_count
				FROM
					" . APP_TABLE_PREFIX . "conference
				WHERE
					cnf_acronym != ''
				GROUP BY
					cnf_acronym, cnf_era_year
				) Q1
			WHERE acronym_count < 3
			ORDER BY
				acronym ASC;
		";

		try {
            $rankedConferencesAcronyms = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		/*if (count($result) > 0) {
			foreach ($result as $key => $row) {
				$rankedConferencesAcronyms[$row['cnf_id']] = $row['acronym'];
			}
		}*/

		echo "done.\n";

		return $rankedConferencesAcronyms;
	}



	function normaliseListOfTitles($titles)
	{
		foreach ($titles as &$title) {
            if (is_string($title)) {
                $title = RJL::normaliseTitle($title);
            } elseif (isset($title['title']) && is_string($title['title'])) {
                $title['title'] = RJL::normaliseTitle($title['title']);
            }
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
            if (is_string($title)) {
			$title = RCL::crushTitle($title);
            } elseif (isset($title['title']) && is_string($title['title'])) {
                $title['title'] = RCL::crushTitle($title['title']);
            }
		}

		return $titles;
	}



	function crushTitle($title)
	{
		//Take the first letter of each word, uppercase it.
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
		$existsAlready = false;
		/* Step through each source item */
		foreach ($check as $sourceKey => $sourceVal) {

			/* Attempt to match it against each target item */
			foreach ($against as $target) {

				/* Test for exact string match */
				if ($sourceVal == $target['title']) {
                    foreach ($matches as $match) {
                        if ($match['pid'] == $sourceKey && $match['matching_year'] == $target['cnf_era_year']) {
                            $existsAlready = true;
                            break;
                        }
                    }
                    if ($existsAlready && $match['matching_id'] != $target['cnf_id'] ) {
                        $this->dupeList .= "Double Match: String Comparison: ".$sourceKey." on existing conf id ". $match['matching_id']." with conf id ".$target['cnf_id']. " - " .$target['title']." Year: ".$target['cnf_era_year']. "\n";
                    } else {
                        $matches[] = array('pid' => $sourceKey, 'matching_id' => $target['cnf_id'], 'matching_year' => $target['cnf_era_year']);
                    }
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
			foreach ($against as $target) {

				/* Test for exact string match */
                if ($sourceVal == $target['title']) {
                    $existsAlready = false;
                    foreach ($matches as $match) {
                        if ($match['pid'] == $sourceKey && $match['matching_year'] == $target['cnf_era_year']) {
                            $existsAlready = true;
                            break;
                        }
                    }
                    if ($existsAlready && $match['matching_id'] != $target['cnf_id'] ) {
                        $this->dupeList .= "Double Match: String Crush stage: ".$sourceKey." on proposed conf id ". $match['matching_id']." with conf id ".$target['cnf_id']. " - " .$target['title']." Year: ".$target['cnf_era_year']. "\n";
                    } else {
                        $matches[] = array('pid' => $sourceKey, 'matching_id' => $target['cnf_id'], 'matching_year' => $target['cnf_era_year']);
                    }
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
			foreach ($against as $target) {

                $target['acronym'] = str_replace('/', '', $target['acronym']); // Get rid of slashes ... these cause us headaches.
                //First we quickly see if it might be a match
                  if ((strpos($sourceVal, $target['acronym']) !== false) &&  !empty($target['acronym'])) {

                      //Now we check that the acronym is in isolation.
                      $regexp1 = '/[^A-Za-z0-9](' . $target['acronym'] . ')[^A-Za-z0-9]/';
                      $regexp2 = '/^(' . $target['acronym'] . ')[^A-Za-z0-9]/';
                      $regexp3 = '/[^A-Za-z0-9](' . $target['acronym'] . ')$/';
                      $regexp4 = '/^(' . $target['acronym'] . ')$/';
                      if (preg_match($regexp1, $sourceVal) || preg_match($regexp2, $sourceVal) || preg_match($regexp3, $sourceVal) || preg_match($regexp4, $sourceVal)) {
                        /* Rule out any values we know we don't want to match on */
                        if (
                            $target['acronym'] != "Complex"
                            && $target['acronym'] != "Group"
                            && $target['acronym'] != "Information Processing"
                            && $target['acronym'] != "Interaction"
                            && $target['acronym'] != "Coordination"
                            && $target['acronym'] != "Complexity"
                            && $target['acronym'] != "IV"
                            && $target['acronym'] != "VI"
                            && $target['acronym'] != "e-Science"
                            && $target['acronym'] != "Sensor Networks"
                            && $target['acronym'] != "Interact"
                            && $target['acronym'] != "Middleware"
                            && $target['acronym'] != "PRIMA"
                            && $target['acronym'] != "Agile"
                            && $target['acronym'] != "DNA"
                            ) {
                                $existsAlready = false;
                                foreach ($matches as $match) {
                                    if ($match['pid'] == $sourceKey && $match['matching_year'] == $target['cnf_era_year']) {
                                        $existsAlready = true;
                                        break;
                                    }
                                }
                                if ($existsAlready && $match['matching_id'] != $target['cnf_id'] ) {
                                    $this->dupeList .= "Double Match: acronym stage: ".$sourceKey." on proposed conf id ". $match['matching_id']." with conf id ".$target['cnf_id']. " - " .$sourceVal." vs ".$target['acronym']." Year: ".$target['cnf_era_year']. "\n";
                                } else {
                                    $matches[] = array('pid' => $sourceKey, 'matching_id' => $target['cnf_id'], 'matching_year' => $target['cnf_era_year']);
                                }
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

		echo "Running insertion queries on eSpace database ... ";

		foreach ($matches as $match) {

            //If matched by year already we won't remove it to keep things stable (Unless we are redoing all)
            $exists = RCL::getConferenceIDsByPIDYear($match['pid'], $match['matching_year']);
            if (($this->unMatched != true) || empty($exists)) {
                RCL::removeMatchByPID($match['pid']);
                $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "matched_conferences (mtc_pid, mtc_cnf_id, mtc_status) VALUES ('" . $match['pid'] . "', '" . $match['matching_id'] . "', 'A') ON DUPLICATE KEY UPDATE mtc_pid = mtc_pid, mtc_cnf_id = mtc_cnf_id;";

                try {
                    $db->exec($stmt);
                }
                catch(Exception $ex) {
                    $log->err($ex);
                    die('There was a problem with the query ' . $stmt);
                }
                  if ( APP_SOLR_INDEXER == "ON" || APP_ES_INDEXER == "ON" ) {
                    FulltextQueue::singleton()->add($match['pid']);
                  }

		    }
        }

		echo "done.\n";

		return;
	}


    function removeMatchByPID($pid, $year = '%')
   	{
   		$log = FezLog::get();
   		$db = DB_API::get();

        $existingIDs = RCL::getConferenceIDsByPIDYear($pid, $year);
        if (count($existingIDs) == 0) {
            return true;
        }

   		$stmt = "DELETE FROM
                       " . APP_TABLE_PREFIX . "matched_conferences
                    WHERE
                       mtc_pid = ? AND mtc_cnf_id IN ('" . implode("','", $existingIDs) . "')";
   		try {
   			$db->query($stmt, $pid);
   		}
   		catch(Exception $ex) {
   			$log->err($ex);
   			return false;
   		}
   		return true;
   	}

    function getConferenceIDsByPIDYear($pid, $year = '%')
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "
  			SELECT
  				cnf_id
  			FROM
  				" . APP_TABLE_PREFIX . "conference INNER JOIN
  				" . APP_TABLE_PREFIX . "matched_conferences ON cnf_id = mtc_cnf_id
  			WHERE cnf_era_year LIKE '" . $year . "' AND mtc_pid = '" . $pid . "'
  		";

        try {
            $result = $db->fetchCol($stmt);
        } catch (Exception $ex) {
            $log->err($ex);
            return array();
        }

        return $result;
    }

    function findBestApproxMatch($check, $against, &$matches) {
            echo "Running similarity string match ... ";
            foreach($against as &$conference) {
                $normalised = $conference['title'];
                $normalised = preg_replace('/[0-9]+th/', '', $normalised);
                $normalised = str_replace('1st', '', $normalised);
                $normalised = str_replace('2nd', '', $normalised);
                $conference['conference_name_normalised'] = preg_replace('/[.()!?\\ 0-9]+/', '', $normalised);
            }
            /* Step through each source item */
            foreach ($check as $sourceKey => $sourceVal) {

                $normalised = $sourceVal;
                $normalised = preg_replace('/[0-9]+th/', '', $normalised);
                $normalised = str_replace('1st', '', $normalised);
                $normalised = str_replace('2nd', '', $normalised);
                $normalised = preg_replace('/[.()!?\\ 0-9]+/', '', $normalised);

                $similarityBest = 0;
                foreach($against as $conference) {
                    similar_text($normalised, $conference['conference_name_normalised'], $similarity);
                        if ($similarity > $similarityBest) {
                            $similarityBest = $similarity;
                            $best = $conference;
                        }
                }
                if ($similarityBest > 0) {   //SIMILARITY_THRESHOLD  - won't use
                    $existsAlready = false;
                    foreach ($matches as $match) {
                        if ($match['pid'] == $sourceKey && $match['matching_year'] == $best['cnf_era_year']) {
                            $existsAlready = true;
                            break;
                        }
                    }
                    if ($existsAlready && $match['matching_id'] != $best['cnf_id'] ) {
                        $this->dupeList .= "Double Match: Similarity string match: ".$sourceKey." on proposed conf id ". $match['matching_id']." with conf id ".$best['cnf_id']. " - " .$best['title']." Year: ".$best['cnf_era_year']. "\n";
                    } else {
                        $matches[] = array('pid' => $sourceKey, 'matching_id' => $best['cnf_id'], 'matching_year' => $best['cnf_era_year']);
                        echo '"'.$best['title'].'","'.$sourceVal.'","'.$sourceKey.'","'.$similarityBest.'"'."\n";
                        //echo '"'.$best['rek_herdc_code_pid'].'","'.$best['cvo_title'].'","'.$best['confirmed'].'","'.$best['rek_title'].'","'.$best['rek_genre'].'","'.$best['rek_subtype'].'","'.$best['mtc_status'].'","'.$best['rek_conference_name'].'","'.$best['cnf_conference_name'].'"'."\n";
                    }
                }
            }

            echo " done.\n";

            return;
    }


  }
