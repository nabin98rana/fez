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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH.'class.record_edit_form.php');
include_once(APP_INC_PATH.'class.fulltext_queue.php');
include_once(APP_INC_PATH.'class.fedora_api.php');

class DuplicatesReport {

	const MERGE_TYPE_ALL = 0;
	const MERGE_TYPE_HIDDEN = 1;
	const MERGE_TYPE_RM_ISI = 2;

	const RELEVANCE_ISI_LOC_MATCH = -1;

	const DUPS_THRESHOLD = 0.8;

	var $pid;
	var $bgp; // background process object for user feedback
	var $sets = array();
	var $xml_dom;
	var $wfl_id; // for recording history on any records that are modified.
	var $in_automerge = false;



	function __construct($pid=null)
	{
		$this->pid = $pid;
	}

	function setBGP(&$bgp)
	{
		$this->bgp = $bgp;
	}

	function getWorkflowId()
	{
		if (empty($this->wfl_id)) {
			$this->wfl_id = null;
		}
		return $this->wfl_id;
	}

	function setWorkflowId($wfl_id)
	{
		$this->wfl_id = $wfl_id;
	}


	function generate($pids)
	{
		$total_pids = count($pids);
		$progress = 0;
		$dupes_count = 0;
		$report_array = array();
		$bgp_details = $this->bgp->getDetails();
		$tz = Date_API::getPreferredTimezone($bgp_details["bgp_usr_id"]);
		for ($ii = 0; $ii < count($pids); $ii++) {
			$pid = $pids[$ii];
			$progress++;
			$utc_date = Date_API::getSimpleDateUTC();

			$time_per_object = Date_API::dateDiff("s", $bgp_details['bgp_started'], $utc_date);
			$time_per_object = round(($time_per_object / $progress), 2);

			$records_left = $total_pids - $progress;

			$date_new = new Date(strtotime($bgp_details['bgp_started']));
			$date_new->addSeconds($time_per_object*$records_left);

			$expected_finish = Date_API::getFormattedDate($date_new->getTime(), $tz);

			if (!empty($this->bgp)) {
				$this->bgp->setProgress($progress / $total_pids * 100);
				$this->bgp->setStatus("Processing " . $progress . " of " . $total_pids . ", "
				. $pid . ". "
				. $dupes_count . " dupes found so far. (Avg ".$time_per_object."s per Object, Expected Finish ".$expected_finish.")");
				$this->bgp->markPidAsFinished($pid); // marking as finished here as there are too many paths out of this loop
			}
			$record = new RecordObject($pid);
			if ($record->checkExists()) {
				// recurse into collections and communities by appending the children to
				// the end of the list.
				if ($record->isCollection()) {
					$pids = array_merge($pids, array_diff($record->getChildrenPids(), $pids));
					continue;
				}
				if ($record->isCommunity()) {
					$pids = array_merge($pids, array_diff($record->getChildrenPids(), $pids));
					continue;
				}
				$res = $this->findSimilarRecords($record);
				$dup_pids = array();

				if (count($res)) {
					foreach ($res as $dup_row) {
						$dup_pid = $dup_row['pid'];

						// avoid checking same pid twice or detecting self
						if ($dup_pid == $pid || in_array($dup_pid,$dup_pids)) {
							continue;
						}
						$dup_pids[] = $dup_pid;

						// skip previously processed records
						$history_res = History::searchOnPid($dup_pid,
						array('pre_detail' => '% Marked not duplicate of '.$pid));
						if (!empty($history_res)) {
							if (!empty($this->bgp)) {
								$this->bgp->setStatus("Skipping previously processed pid: " . $dup_pid);
							}
							continue;
						}

						$dup_rec = new RecordObject($dup_pid);
						if (!$dup_rec->checkExists()) {
							continue;
						}
						$dup_xdis_id = $dup_rec->getXmlDisplayId();
						if (empty($dup_xdis_id)) {
							// record is damaged
							continue;
						}

						if ($dup_row['relevance'] == self::RELEVANCE_ISI_LOC_MATCH) {
							$score = 1;
						} else {
							$score = $this->compareRecords($record, $dup_rec);
							echo "\n Comparing ".$pid." - ".$dup_pid.", Score = ".$score."\n";
							//                        echo "tokens: \n".print_r($tokens,true)."\n";
							//                        echo "dup_tokens: \n".print_r($dup_tokens,true)."\n";
						}
						if ($score > self::DUPS_THRESHOLD) {
							if (!isset($report_array[$pid])) {
								$report_array[$pid] = array(
                                	'pid' => $pid,
                                	'title' => $record->getTitle(),
                                    'rm_prn' => $this->getRM_PRN($record),
                                    'isi_loc' => $this->getISI_LOC($record)
								);
							}
							$report_array[$pid]['list'][$dup_pid]
							= array('pid' => $dup_pid,
                                                                                 'probability' => $score,
                                         'title' => $dup_rec->getTitle(),
                                         'rm_prn' => $this->getRM_PRN($dup_rec),
                                         'isi_loc' => $this->getISI_LOC($dup_rec));
							$dupes_count++;
						}
					}
				}
			}
		}
		$this->bgp->setStatus("Found ". $dupes_count . " dupes in " .  $total_pids . " items.");

		$report_array = $this->mergeSets($report_array);
		$xml = $this->generateXML($report_array);
		$this->saveReport($xml);
		//Error_Handler::debugStop();

	}

	function generateXML($report_array)
	{
		if (!is_array($report_array)) {
			return '';
		}
		$report_dom = new DOMDocument();
		$report_root = $report_dom->createElement('DuplicatesReport');
		$report_dom->appendChild($report_root);

		foreach ($report_array as $pid => $item) {
			$report_item = $report_dom->createElement('duplicatesReportItem');
			foreach ($item as $key => $value) {
				if ($key != 'list') {
					$report_item->setAttribute($key, $value);
				}
			}
			$report_root->appendChild($report_item);
			foreach ($item['list'] as $list_item_pid => $list_item) {
				$report_dup_list_item = $report_dom->createElement('duplicateItem');
				foreach ($list_item as $key => $value) {
					$report_dup_list_item->setAttribute($key, $value);
				}
				$report_item->appendChild($report_dup_list_item);
			}
		}
		return $report_dom->saveXML();
	}

	function saveReport($xml)
	{
		if (empty($xml)) {
			return;
		}
		$this->saveReportToFile($xml);
		if (!$this->in_automerge) {
			$this->addReportToFedoraObject($xml);
		}
	}

	function addReportToFedoraObject($xml)
	{
		return;
		if (empty($xml)) {
			return;
		}
		// add the report to the duplicates report record
		$report_pid = $this->pid;
		$dsIDName = 'DuplicatesReport';
		$state = 'A';
		$label = 'DuplicatesReport';
		if (Fedora_API::datastreamExists($report_pid, $dsIDName)) {
			Fedora_API::callModifyDatastreamByValue($report_pid, $dsIDName, $state, $label, $xml);
		} else {
			Fedora_API::getUploadLocation($report_pid, $dsIDName, $xml, $label, 'text/xml', 'X');
		}
	}

	function saveReportToFile($xml)
	{
		if (empty($xml)) {
			return;
		}
		// save the report to a file
		$filename = $this->getReportFilename();
		file_put_contents($filename, $xml);
	}

	function getReportFilename()
	{
		return APP_DUPLICATES_REPORTS_LOCATION . 'fez_duplicates_report_'.str_replace(':','_',$this->pid);
	}

	function getXML_DOM()
	{
		if (empty($this->xml_dom)) {
			$filename = $this->getReportFilename();
            if (empty($filename) || !file_exists($filename)) {
                return false;
            }
			$xml = file_get_contents($filename);
			//$xml = Fedora_API::callGetDatastreamContents($this->pid, 'DuplicatesReport', true);
			if (is_null($xml) || !is_string($xml) || empty($xml)) {
				$xml = Fedora_API::callGetDatastreamContents($this->pid, 'DuplicatesReport', true);
			}
			if (is_null($xml) || !is_string($xml) || empty($xml)) {
				return null;
			}
			$report_dom = DOMDocument::loadXML($xml);
			if (empty($report_dom)) {
				return null;
			}
			$this->xml_dom = $report_dom;
		}
		return $this->xml_dom;
	}

	function setXML_DOM($dom)
	{
		$this->xml_dom = $dom;
	}

	function mergeSets($report_array)
	{
		$rearranged_report = $this->rearrangeSets($report_array);
		$final_groups = $this->mergeRearrangedSets($rearranged_report);
		$final_groups = $this->recalcDupScores($final_groups);
		return $final_groups;
	}

	function rearrangeSets($report_array)
	{
		// Rearrange all sets so that the base record is the oldest in the grouping
		$rearranged_report = array();
		foreach ($report_array as $pid => $item) {
			$items = $item['list'];
			unset($item['list']);
			$items[$pid] = $item;
			// Seperate set into two lists so that rm_prn records will be preferred.
			// If there are no rm_prn records then the lowest pid will be the base
			$rm_list = array();
			$other_list = array();
			foreach ($items as $item) {
				if (!empty($item['rm_prn'])) {
					$rm_list[$item['pid']] = $item;
				} else {
					$other_list[$item['pid']] = $item;
				}
			}
			uksort($rm_list, array('Misc','comparePIDs'));
			uksort($other_list, array('Misc','comparePIDs'));
			$items = array_merge(array_values($rm_list), array_values($other_list)); // put any rm_prn items first
			$base = array_shift($items);
			$rearranged_report[] = array_merge($base, array('list' => $items));
		}
		return $rearranged_report;
	}

	function mergeRearrangedSets($rearranged_report)
	{
		// Merge any sets that share the same base record.
		$final_groups = array();
		foreach ($rearranged_report as $group) {
			if (isset($final_groups[$group['pid']])) {
				// merge these groups
				// key the arrays so that each pid is listed once in the destination
				$target_group_list = Misc::keyArray($final_groups[$group['pid']]['list'], 'pid');
				$group_list = Misc::keyArray($group['list'], 'pid');
				$new_list = array_merge($target_group_list, $group_list);
				$final_groups[$group['pid']]['list'] = $new_list;
			} else {
				$final_groups[$group['pid']] = $group;
			}
			// make sure we haven't somehow merged the base pid into the dupes list
			if (isset($final_groups[$group['pid']]['list'][$group['pid']])) {
				unset($final_groups[$group['pid']]['list'][$group['pid']]);
			}
		}
		return $final_groups;
	}

	function recalcDupScores($final_groups)
	{
		// recalculate links to the base record for each group
		foreach ($final_groups as $key => $group)
		{
			$base_record = new RecordObject($group['pid']);
			foreach ($group['list'] as $dup_pid => $dup_item) {
				if ($dup_item['isi_loc'] == $group['isi_loc']) {
					$final_groups[$key]['list'][$dup_pid]['probability'] = 1;
				} else {
					$dup_record = new RecordObject($dup_item['pid']);
					$final_groups[$key]['list'][$dup_pid]['probability']
					= $this->compareRecords($base_record, $dup_record);
				}
			}
		}
		return $final_groups;
	}

	function findSimilarRecords(&$record)
	{
		$pid = $record->pid;

		$isi_loc_res = Misc::keyArray($this->matchingISI_LOCQuery($pid), 'pid');

		$title = trim($record->getTitle());
		if (empty($title)) {
			$title_res =  array();
		} else {
			$title_res = Misc::keyArray($this->similarTitlesQuery($pid, $title), 'pid');
		}
		// the isi_loc matches will overwrite the title matches
		$res = array_merge($title_res, $isi_loc_res);
		return $res;
	}


	function matchingISI_LOCQuery($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$pidnum = substr($pid, strpos($pid, ':') + 1);
		$dbtp = APP_SQL_DBNAME . "." . APP_TABLE_PREFIX;

		// Do a isi_loc match on records that don't have the same pid as the candidate record
		$record = new RecordObject($pid);
		$isi_loc = $this->getISI_LOC($record);

		$stmt = "SELECT DISTINCT r2.rek_isi_loc_pid AS pid, "
		.self::RELEVANCE_ISI_LOC_MATCH." AS relevance " .
                "FROM  ".$dbtp."record_search_key_isi_loc AS r2 " .
                "    WHERE r2.rek_isi_loc=".$db->quote($isi_loc)." " .
                "    AND NOT(r2.rek_isi_loc_pid = ".$db->quote($pid).") ";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}

	/**
	 * @param string $pid - exclude this pid from the search
	 * @param string $title - search for this title
	 */
	function similarTitlesQuery($pid, $title)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$pidnum = substr($pid, strpos($pid, ':') + 1);
		$dbtp = APP_SQL_DBNAME . "." . APP_TABLE_PREFIX;
		// Do a fuzzy title match on records that don't have the same pid as the candidate record
		// and are type '3' (records not collections or communities)
		// and are not preprints..
		$pre_print_display_type = XSD_Display::getID("Preprint");
		$pp_where = "";
		$pp_solr_filter = "";
		if (is_numeric($pre_print_display_type)) {
			$pp_where = " AND rek_display_type != ".$pre_print_display_type;
			$pp_solr_filter = " AND !display_type_i:".$pre_print_display_type;
		}
		if (APP_SOLR_SWITCH == "ON" ) { //Solr - the preferable option if available
			//Query solr with an OR query on title for a similar fix
			// Solr search params
			$index = new FulltextIndex_Solr();
			$params = array();
			$start = 0;
			$page_rows = 15;
			$params['fl'] = 'pid_t,score';
			$params['fq'] = 'object_type_i:3 AND !pid_t:'.$index->solr->escape($pid).$pp_solr_filter;
			//Fez Solr schema.xml has the default search to be an AND based search, while dedupe similar titles query needs to be an OR based search
			// so it doesn't exclude similar titles
			$title =  preg_replace("/ +/", " ", $title);
			$title = $index->solr->escape($title);
			$title = $index->solr->escapeBooleans($title);
      //$title = preg_replace('/\s+/', '', $title);
      $title = preg_replace ("/[^a-zA-Z0-9 ]/", "", $title);
      $title = preg_replace('!\s+!', ' ', $title);
      $title = trim($title);
			$titleOr = implode(" OR ", explode(" ", $title));
			$titleOr = preg_replace("/( *OR *OR *)/", " OR ", $titleOr);
			$titleOr = preg_replace("/( *AND *AND *)/", " AND ", $titleOr);
      //make sure something is left to search for
      if (trim($titleOr) == '') {
        return array();
      }
			$queryString = "title_t:(".$titleOr.")";
			$response = $index->solr->search($queryString, $start, $page_rows, $params);
			$total_rows = $response->response->numFound;
			$res = array();
			if ($total_rows > 0) {
				$i = 0;
				foreach ($response->response->docs as $doc) {
					// resolve result
					$res[$i]['pid'] = $doc->pid_t;
					$res[$i]['relevance'] = $doc->score;
					$i++;
				}
			}
			return $res;
		} elseif (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) {
			$stmt = "SELECT distinct r2.rek_pid as pid, "
			.self::RELEVANCE_ISI_LOC_MATCH." as relevance " .
	                "FROM  ".$dbtp."record_search_key AS r2 " .
	                "  WHERE r2.rek_title LIKE ".$db->quote('%'.$title.'%').
	                "  AND NOT(rek_pid = ".$db->quote($pid).") AND rek_object_type = 3 " . $pp_where .
	                " LIMIT 5 OFFSET 0";
		} else {
			$stmt = "SELECT distinct r2.rek_pid as pid, " .
	                "  MATCH (r2.rek_title) AGAINST ('".$db->quote($title)."') AS relevance " .
	                "FROM  ".$dbtp."record_search_key AS r2 " .
	                "  WHERE MATCH (r2.rek_title) AGAINST (".$db->quote($title).") " .
	                "  AND NOT(rek_pid = ".$db->quote($pid).") AND rek_object_type = 3 " . $pp_where .
	                " ORDER BY relevance DESC " .
	                " LIMIT 5 OFFSET 0";
		}
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return array();
		}
		return $res;
	}

	function autoMergeOnISI_LOC()
	{
		//Error_Handler::debugStart();
		$this->in_automerge = true;
		// get the report
		$report_dom = $this->getXML_DOM();
		$xpath = new DOMXPath($report_dom);
		$items = $xpath->query('/DuplicatesReport/duplicatesReportItem');
		$wfl_id = $this->getWorkflowId();

		if (defined('APP_RQF_REALLY_AUTO_MERGE') && APP_RQF_REALLY_AUTO_MERGE === true) {
			$test_mode = false;
		} else {
			$test_mode = true;
			$this->bgp->setStatus("Running in test mode.  No changes to records will be saved");
		}

		$progress = 0;
		foreach ($items as $report_item) {
			$base_pid = $report_item->getAttribute('pid');
			if (!empty($this->bgp)) {
				$this->bgp->setProgress(++$progress / $items->length * 100);
				$this->bgp->setStatus("Processing ".$progress." of ".$items->length.". ".$base_pid);
			}
			$base_record = new RecordObject($base_pid);
			$dups_list = $xpath->query('duplicateItem', $report_item);
			foreach ($dups_list as $dup_item) {
				$dup_pid = $dup_item->getAttribute('pid');
				$dup_record = new RecordObject($dup_pid);
				// Check for a matching ISI_LOC
				// but can't auto merge records that aren't the same Document Type
				if ($base_record->getXmlDisplayId() == $dup_record->getXmlDisplayId()
				&& $this->compareISI_LOC($base_record, $dup_record)) {
					// yikes they match!
					if (!empty($this->bgp)) {
						$this->bgp->setStatus("Merging on matching isi_loc ".$dup_pid);
					}
					if ($this->recordIsResearchMaster($base_record)
					&& $this->recordIsUQCited($dup_record)) {
						$merge_res = $this->mergeRecords($base_record, $dup_record,
						self::MERGE_TYPE_RM_ISI, $test_mode);
					} else {
						$merge_res = $this->mergeRecords($base_record, $dup_record,
						self::MERGE_TYPE_ALL, $test_mode);
					}
					if (!PEAR::isError($merge_res)) {
						$this->setMergeResult($base_pid, $dup_pid, 'ok', false);
						if (!$test_mode) {
							// set some history on the object so we know why it was merged.
							History::addHistory($base_pid, $wfl_id, "", "", false,
					        	'', "Merged on LOC_ISI with ".$dup_pid);
							$this->markDuplicate($base_pid, $dup_pid, false);
							// cause the base record to be re-read for the next merge
							$base_record = new RecordObject($base_pid);
						}
					} else {
						if (!empty($this->bgp)) {
							$this->bgp->setStatus("Merge failed: ".$merge_res->getMessage());
						}
						$this->setMergeResult($base_pid, $dup_pid, $merge_res->getMessage());
					}
				}
			}
		}
		$this->in_automerge = false;
		$report_dom = $this->getXML_DOM();
		$this->saveReport($report_dom->saveXML());
		//Error_Handler::debugStop();
	}

	function autoMergeRecords(&$base_record, &$dup_record)
	{
		Error_Handler::debugStart();
		if ($base_record->getXmlDisplayId() != $dup_record->getXmlDisplayId()) {
			// can't automerge records of different document types
			$error = PEAR::raiseError("Can't automerge records of different document types");
			return $error;
		}

		if ($this->recordIsResearchMaster($base_record) && $this->recordIsUQCited($dup_record)) {
			$merge_res = $this->mergeRecords($base_record, $dup_record, self::MERGE_TYPE_RM_ISI);
		} else {
			$merge_res = $this->mergeRecords($base_record, $dup_record);
		}

		// Should we need to do this, for some reason the details don't update in these records
		// and it merges the old record details
		//if ($merge_res > 0) {
		//    $merge_res = $this->mergeRecords($base_record, $dup_record, self::MERGE_TYPE_HIDDEN);
		//}

		if (!PEAR::isError($merge_res)) {
			$wfl_id = $this->getWorkflowId();
			// set some history on the object so we know why it was merged.
			History::addHistory($base_record->pid, $wfl_id, "", "", false, '',
		    		"Merged with " . $dup_record->pid . " by " . Auth::getUserFullName(), null);
		}
		Error_Handler::debugStop();
		return $merge_res;
	}

	function recordIsResearchMaster(&$record)
	{
		$rm_prn = $this->getIdentifier($record,'rm_prn');
		if (!empty($rm_prn)) {
			return true;
		}
		return false;
	}

	function recordIsUQCited(&$record)
	{
		$rm_prn = $this->getIdentifier($record,'rm_prn');
		$isi_loc = $this->getIdentifier($record,'isi_loc');
		if (!empty($isi_loc) && empty($rm_prn)) {
			return true;
		}
		return false;
	}

	function mergeRecords(&$base_record, &$dup_record, $merge_type = self::MERGE_TYPE_ALL,
	$test_mode = false)
	{

		switch ($merge_type)
		{
			case self::MERGE_TYPE_HIDDEN:
				if ($base_record->getXmlDisplayId() == $dup_record->getXmlDisplayId()) {
					$base_det = $this->mergeDetailsHiddenSameDocType($base_record, $dup_record);
				} else {
					$base_det = $this->mergeDetailsHiddenDiffDocType($base_record, $dup_record);
				}
				break;
			case self::MERGE_TYPE_RM_ISI:
				$base_det = $this->mergeDetailsAll($base_record, $dup_record);
				if (!PEAR::isError($base_det)) {
					$base_det = $this->mergeNormaliseKeywords($base_record, $dup_record, $base_det);
				}
				if (!PEAR::isError($base_det)) {
					$base_det = $this->overrideRMDetails($base_record, $dup_record, $base_det);
				}
				if (!PEAR::isError($base_det)) {
					$base_det = $this->mergeAuthorsRM_UQCited($base_record, $dup_record, $base_det);
				}
				break;
			case self::MERGE_TYPE_ALL:
			default:
				$base_det = $this->mergeDetailsAll($base_record, $dup_record);
				if (!PEAR::isError($base_det)) {
					$base_det = $this->mergeNormaliseKeywords($base_record, $dup_record, $base_det);
				}
				if (!PEAR::isError($base_det)) {
					$base_det = $this->mergeAuthorsGeneral($base_record, $dup_record, $base_det);
				}
				break;
		}
		if (!PEAR::isError($base_det)) {
			$base_det = $this->removeDuplicateIdentifiers($base_record, $base_det);
		}
		if (!PEAR::isError($base_det)) {
			$base_det = $this->merge_R_Datastreams($base_record, $dup_record, $base_det);
		}
		if (!PEAR::isError($base_det)) {
			$base_det = $this->merge_M_Datastreams($base_record, $dup_record, $base_det);
		}

		// check for errors and don't merge if there was a problem
		if (PEAR::isError($base_det)) {
			return $base_det;
		}
		if (!$test_mode) {
			$params = array();
			$params['sta_id'] = $base_record->getPublishedStatus();

			// Just want to find the basic xsdmf_ids for the title, date and user and set them to something useful
			$params['xsd_display_fields'] = $base_det;
			$ref = new RecordEditForm();
			$ref->fixParams($params, $base_record);

			$base_record->fedoraInsertUpdate(array("FezACML"), array(""),$params);

			if ( APP_SOLR_INDEXER == "ON" ) {
				FulltextQueue::singleton()->add($base_record->pid);
				FulltextQueue::singleton()->commit();
				FulltextQueue::singleton()->triggerUpdate();
			}
		}
		return 1;
	}

	function mergeRecordsHiddenFields(&$base_record, &$dup_record)
	{
		return $this->mergeRecords($base_record, $dup_record, self::MERGE_TYPE_HIDDEN);
	}



	function mergeDetailsAll(&$base_record, &$dup_record)
	{
		// get the values for both records and copy over anything that isn't set in the base.
		$base_det = $base_record->getDetails();
		$dup_det = $dup_record->getDetails();
		foreach ($dup_det as $xsdmf_id => $dup_value) {
			$this->mergeDetailGeneric($base_det, $xsdmf_id, $dup_value);
		}
		return $base_det;
	}

	function mergeDetailGeneric(&$base_det, $xsdmf_id, $dup_value, $make_unique = true)
	{
		if (!isset($base_det[$xsdmf_id]) || empty($base_det[$xsdmf_id])) {
			$base_det[$xsdmf_id] = $dup_value;
		} elseif (!empty($dup_value) && !is_array($dup_value) && is_array($base_det[$xsdmf_id])) {
			if ($make_unique) {
				$base_det[$xsdmf_id] = array_values(array_unique(array_merge(
				$base_det[$xsdmf_id], array($dup_value))));
			} else {
				$base_det[$xsdmf_id] = array_values(array_merge($base_det[$xsdmf_id], array($dup_value)));
			}
		} elseif (!empty($dup_value) && !is_array($dup_value)
		&& !empty($base_det[$xsdmf_id]) && !is_array($base_det[$xsdmf_id])) {
			// check if this is supposed to be a multiple element
			$xsdmf = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
			if ($xsdmf['xsdmf_multiple'] == 1 || $xsdmf['xsdmf_html_input'] == 'multiple' || $xsdmf['xsdmf_html_input'] == 'dual_multiple') {
				if ($make_unique) {
					$base_det[$xsdmf_id] = array_values(array_unique(
					array($base_det[$xsdmf_id], $dup_value)));
				} else {
					$base_det[$xsdmf_id] = array_values(array($base_det[$xsdmf_id], $dup_value));
				}
			}
		} elseif (is_array($dup_value)) {
			if (is_array($base_det[$xsdmf_id])) {
				if ($make_unique) {
					$base_det[$xsdmf_id] = array_values(array_unique(array_merge(
					$base_det[$xsdmf_id], $dup_value)));
				} else {
					$base_det[$xsdmf_id] = array_values(array_merge($base_det[$xsdmf_id], $dup_value));
				}
			} else {
				if ($make_unique) {
					$base_det[$xsdmf_id] = array_values(array_unique(array_merge(
					array($base_det[$xsdmf_id]), $dup_value)));
				} else {
					$base_det[$xsdmf_id] = array_values(array_merge(
					array($base_det[$xsdmf_id]), $dup_value));
				}
			}
		}
	}


	function mergeDetailsHiddenSameDocType(&$base_record, &$dup_record, $base_det = null)
	{
		// get the values for both records and copy over anything that isn't set in the base.
		if (empty($base_det)) {
			$base_det = $base_record->getDetails();
		}
		$dup_det = $dup_record->getDetails();
		$base_record->getDisplay();
		$xsd_display_fields = Misc::keyArray($base_record->display->getMatchFieldsList(array("FezACML"),
		array("")), 'xsdmf_id');
		foreach ($dup_det as $xsdmf_id => $dup_value) {
			// skip everything except hidden fields
			if ($xsd_display_fields[$xsdmf_id]['xsdmf_html_input'] == 'hidden') {
				$this->mergeDetailGeneric($base_det, $xsdmf_id, $dup_value, false);
			}
		}
		return $base_det;
	}

	function mergeDetailsHiddenDiffDocType(&$base_record, &$dup_record)
	{
		// the records are different document types
		// not much we can do but will try to rescue any isi_loc or rm_prn

		// get the values for both records and copy over anything that isn't set in the base.
		$base_det = $base_record->getDetails();
		$dup_det = $dup_record->getDetails();

		// assumes the sub looping title is the identifier type in caps with no underscore
		$xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_ID_ByElementInSubElement(
											'!identifier','ISI LOC',
											'!identifier');
		if ($xsdmf_id < 0) {
			$id_xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!identifier');
			$type_xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!identifier!type');
			// make sure the base record slots for identifiers are arrays
			if (!isset($base_det[$id_xsdmf_id])) {
				$base_det[$id_xsdmf_id] = array();
			} elseif (!is_array($base_det[$id_xsdmf_id])) {
				$base_det[$id_xsdmf_id] = array($base_det[$id_xsdmf_id]);
			}
			if (!isset($base_det[$type_xsdmf_id])) {
				$base_det[$type_xsdmf_id] = array();
			} elseif (!is_array($base_det[$type_xsdmf_id])) {
				$base_det[$type_xsdmf_id] = array($base_det[$type_xsdmf_id]);
			}
			// copy over the identifiers from the dupe
			foreach (array('rm_prn', 'isi_loc', 'isbn','issn') as $id_type) {
				// don't merge if the identifier type is already in the base record
				if (in_array($id_type, $base_det[$type_xsdmf_id])) {
					continue;
				}
				// copy the identifier over to the base record
				$dup_id = $this->getIdentifier($dup_record, $id_type);
				if (!empty($dup_id)) {
					$base_det[$id_xsdmf_id][] = $dup_id;
					$base_det[$type_xsdmf_id][] = $id_type;
				}
			}
		} else {
			foreach (array('rm_prn', 'isi_loc', 'isbn','issn') as $id_type) {
				$sel_title = str_replace('_', ' ', strtoupper($id_type));
				$base_xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_ID_ByElementInSubElement(
											'!identifier',$sel_title,
											'!identifier');
				if (empty($base_det[$base_xsdmf_id])) {
					$dup_id = $this->getIdentifier($dup_record, $id_type);
					if (!empty($dup_id)) {
						$base_det[$base_xsdmf_id] = $dup_id;
					}
				}
			}
		}
		return $base_det;
	}

	function removeDuplicateIdentifiers(&$base_record, $base_det)
	{
		$error = 0;
		$xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_ID_ByElementInSubElement(
											'!identifier','ISI LOC',
											'!identifier');
		if ($xsdmf_id < 0) {
			$id_xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!identifier');
			$type_xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!identifier!type');
			if (!empty($base_det[$type_xsdmf_id]) && !empty($base_det[$id_xsdmf_id])) {
				// put the actual identifier as the key as the type can be non-unique
				if (!is_array($base_det[$type_xsdmf_id])) {
					$base_det[$type_xsdmf_id] = array($base_det[$type_xsdmf_id]);
				}
				if (!is_array($base_det[$id_xsdmf_id])) {
					$base_det[$id_xsdmf_id] = array($base_det[$id_xsdmf_id]);
				}
				if (count($base_det[$id_xsdmf_id]) == count($base_det[$type_xsdmf_id])) {
					$identifier_key_pairs = array_combine($base_det[$id_xsdmf_id], 	$base_det[$type_xsdmf_id]);
					$base_det[$id_xsdmf_id] = array_keys($identifier_key_pairs);
					$base_det[$type_xsdmf_id] = array_values($identifier_key_pairs);
				} else {
					$error = PEAR::raiseError("The identifiers would not merge");
				}
			}
		}
		if (empty($error)) {
			return $base_det;
		} else {
			return $error;
		}
	}



	function overrideRMDetails(&$base_record, &$dup_record, $base_det)
	{
		$error = 0;
		$dup_det = $dup_record->getDetails();
		// title, journal name, date, start_page, end_page, volume_number?
		$elements = array('!titleInfo!title', '!relatedItem!name!namePart', '!originInfo!dateIssued',
    						'!relatedItem!part!extent!start', '!relatedItem!part!extent!end');
		foreach ($elements as $element) {
			$xsdmf_id = $dup_record->display->xsd_html_match->getXSDMF_IDByXDIS_ID($element);
			$base_det[$xsdmf_id] = $dup_det[$xsdmf_id];
		}
		// Find the Volume number xsdmf id
		$xsdmf_id = $dup_record->display->xsd_html_match->getXSDMF_ID_ByElementInSubElement(
											'!relatedItem!part!detail','Volume',
											'!relatedItem!part!detail!number');
		if ($xsdmf_id > 0) {
			if (!empty($base_det[$xsdmf_id]) && !empty($dup_det[$xsdmf_id])
			&& $base_det[$xsdmf_id] != $dup_det[$xsdmf_id]) {
				$error = PEAR::raiseError("The base record has a different Volume value to the duplicate");
			} elseif (empty($base_det[$xsdmf_id])) {
				$base_det[$xsdmf_id] = $dup_det[$xsdmf_id];
			}
		} else {
			Error_Handler::logError("Expected Duplicate record to have a Volume "
			. "sublooping element on !part!detail!number", __FILE__,__LINE__);
			$error = PEAR::raiseError("Expected Duplicate record to have a Volume "
			. "sublooping element on !part!detail!number. "
			. "getXSDMF_ID_ByElementInSubElement error code: ".$xsdmf_id);
		}
		if (empty($error)) {
			return $base_det;
		} else {
			return $error;
		}
	}

	function mergeNormaliseKeywords(&$base_record, &$dup_record, $base_det)
	{
		$base_keywords = Misc::array_flatten($base_record->getFieldValueBySearchKey('Keywords'),'',true);
		$dup_keywords = Misc::array_flatten($dup_record->getFieldValueBySearchKey('Keywords'),'',true);

		$base_keywords = array_map(create_function('$a', 'return ucwords(strtolower($a));'), $base_keywords);
		$dup_keywords = array_map(create_function('$a', 'return ucwords(strtolower($a));'), $dup_keywords);

		$sek_id = Search_Key::getID('Keywords');
		$xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_IDBySEK($sek_id);
		if (is_numeric($xsdmf_id) && $xsdmf_id > 0) {
			$base_det[$xsdmf_id] = $base_keywords;
			$this->mergeDetailGeneric($base_det, $xsdmf_id, $dup_keywords);
		}
		return $base_det;
	}

	/**
	 * This function prefers to use the name from the dupe as the UQ Cited records apparently
	 * have better names.
	 */
	function mergeAuthorsRM_UQCited(&$base_record, &$dup_record, $base_det)
	{
		/* the rules are:
		 if the base author is blank (there are more authors on the dup than the base) then copy them across.
		 if the ids match, then use the UQ Cited Author Display name (dup)
		 if the author names have a good levenstein score and the base id is not set (0) then use the dup
		 if the author names have a bad score then return -1

		 */
		$error = 0;

		$base_authors = Misc::array_flatten($base_record->getFieldValueBySearchKey('Author'),'',true);
		$base_author_ids = Misc::array_flatten($base_record->getFieldValueBySearchKey('Author ID'),'',true);

		$dup_authors = Misc::array_flatten($dup_record->getFieldValueBySearchKey('Author'),'',true);
		$dup_author_ids = Misc::array_flatten($dup_record->getFieldValueBySearchKey('Author ID'),'',true);

		$res_authors = array();
		$res_author_ids = array();

		for ($ii = 0; $ii < count($dup_authors); $ii++) {
			if (empty($base_authors[$ii])) {
				$res_authors[] = $dup_authors[$ii];
				$res_author_ids[] = $dup_author_ids[$ii];
			} elseif (!empty($base_author_ids[$ii]) &&  $base_author_ids[$ii] == $dup_author_ids[$ii]) {
				$res_authors[] = $dup_authors[$ii];
				$res_author_ids[] = $dup_author_ids[$ii];
			} elseif (empty($base_author_ids[$ii])) {
				$lev = $this->calcAuthorLevenshtein($base_authors[$ii], $dup_authors[$ii]);
				$minlen = min(strlen($base_authors[$ii]), strlen($dup_authors[$ii]));
				if ($lev < $minlen / 2) {
					$res_authors[] = $dup_authors[$ii];
					$res_author_ids[] = $dup_author_ids[$ii];
				} else {
					$error = PEAR::raiseError("Author names '" . $base_authors[$ii] . "' and '"
					. $dup_authors[$ii]
					. "' are too different to be confident that they are the same person (diff:"
					. $lev . ")");
					break;
				}
			} elseif (empty($dup_author_ids[$ii])) {
				$lev = $this->calcAuthorLevenshtein($base_authors[$ii], $dup_authors[$ii]);
				$minlen = min(strlen($base_authors[$ii]), strlen($dup_authors[$ii]));
				if ($lev < $minlen / 2) {
					$res_authors[] = $dup_authors[$ii];
					$res_author_ids[] = $base_author_ids[$ii];
				} else {
					$error = PEAR::raiseError("Author names '" . $base_authors[$ii] . "' and '"
					. $dup_authors[$ii]
					. "' are too different to be confident that they are the same person (diff:"
					. $lev . ")");
					break;
				}
			} else {
				$error = PEAR::raiseError("Author names '".$base_authors[$ii]."' and '".$dup_authors[$ii]
				."' couldn't be merged");
				break;
			}
		}

		if (empty($error)) {
			// find the authors and ids xsdmf_ids and merge them in
			$sek_id = Search_Key::getID('Author');
			$xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_IDBySEK($sek_id);
			$base_det[$xsdmf_id] = $res_authors;
			$sek_id = Search_Key::getID('Author ID');
			$xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_IDBySEK($sek_id);
			$base_det[$xsdmf_id] = $res_author_ids;
			return $base_det;
		} else {
			return $error;
		}
	}

	/**
	 * This function will use the base record author names if there is one as we have no reason to
	 * think the dupe will be any better.
	 */
	function mergeAuthorsGeneral(&$base_record, &$dup_record, $base_det)
	{
		$error = 0;

		$base_authors = Misc::array_flatten($base_record->getFieldValueBySearchKey('Author'),'',true);
		$base_author_ids = Misc::array_flatten($base_record->getFieldValueBySearchKey('Author ID'),'',true);

		$dup_authors = Misc::array_flatten($dup_record->getFieldValueBySearchKey('Author'),'',true);
		$dup_author_ids = Misc::array_flatten($dup_record->getFieldValueBySearchKey('Author ID'),'',true);

		$res_authors = array();
		$res_author_ids = array();

		if (empty($dup_authors) && empty($base_authors)) {
			// nothing to do here
			return 	$base_det;
		}

		for ($ii = 0; $ii < count($dup_authors); $ii++) {
			if (empty($base_authors[$ii])) {
				$res_authors[] = $dup_authors[$ii];
				$res_author_ids[] = $dup_author_ids[$ii];
			} elseif (!empty($base_author_ids[$ii]) &&  $base_author_ids[$ii] == $dup_author_ids[$ii]) {
				$res_authors[] = $base_authors[$ii];
				$res_author_ids[] = $base_author_ids[$ii];
			} elseif (empty($base_author_ids[$ii])) {
				$lev = $this->calcAuthorLevenshtein($base_authors[$ii], $dup_authors[$ii]);
				$minlen = min(strlen($base_authors[$ii]), strlen($dup_authors[$ii]));
				if ($lev < $minlen / 2) {
					$res_authors[] = $base_authors[$ii];
					$res_author_ids[] = $dup_author_ids[$ii]; // maybe the dupe has an id
				} else {
					$error = PEAR::raiseError("Author names '" . $base_authors[$ii] . "' and '"
					. $dup_authors[$ii]
					. "' are too different to be confident that they are the same person (diff:"
					. $lev . ")");
					break;
				}
			} elseif (empty($dup_author_ids[$ii])) {
				$lev = $this->calcAuthorLevenshtein($base_authors[$ii], $dup_authors[$ii]);
				$minlen = min(strlen($base_authors[$ii]), strlen($dup_authors[$ii]));
				if ($lev < $minlen / 2) {
					$res_authors[] = $base_authors[$ii];
					$res_author_ids[] = $base_author_ids[$ii];
				} else {
					$error = PEAR::raiseError("Author names '" . $base_authors[$ii] . "' and '"
					. $dup_authors[$ii]
					. "' are too different to be confident that they are the same person (diff:"
					. $lev . ")");
					break;
				}
			} else {
				$error = PEAR::raiseError("Author names '".$base_authors[$ii]."' and '".$dup_authors[$ii]
				."' couldn't be merged");
				break;
			}
		}

		if (empty($error)) {
			// find the authors and ids xsdmf_ids and merge them in
			$sek_id = Search_Key::getID('Author');
			$xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_IDBySEK($sek_id);
			$base_det[$xsdmf_id] = $res_authors;
			$sek_id = Search_Key::getID('Author ID');
			$xsdmf_id = $base_record->display->xsd_html_match->getXSDMF_IDBySEK($sek_id);
			$base_det[$xsdmf_id] = $res_author_ids;
			return $base_det;
		} else {
			return $error;
		}
	}



	function calcAuthorLevenshtein($left, $right)
	{
		// remove spaces and punctuation from authorname
		$pattern = '/[\s,.]/';
		$left = preg_replace($pattern,'',$left);
		$right = preg_replace($pattern,'',$right);
		return levenshtein($left, $right);
	}

	function merge_R_Datastreams(&$base_record, &$dup_record, $base_det)
	{
		$error = 0;
		$datastreams = Fedora_API::callGetDatastreams($dup_record->pid);
		$datastreams = Misc::cleanDatastreamList($datastreams); //This may have issues $datastreams seems wrong format

		$links_xsdmf_id =  $dup_record->display->xsd_html_match->getXSDMF_ID_ByElementInSubElement(
									'!datastream','Link','!datastream!datastreamVersion!contentLocation');
		$link_labels_xsdmf_id =  $dup_record->display->xsd_html_match->getXSDMF_ID_ByElementInSubElement(
									'!datastream','Link','!datastream!datastreamVersion!LABEL');

		foreach ($datastreams as $ds_key => $ds) {
			if ($datastreams[$ds_key]['controlGroup'] == 'R') {
				$link = trim($datastreams[$ds_key]['location']);
				$link_label = trim($datastreams[$ds_key]['label']);
				// only raise an error if there is a link to be copied and the destination isn't right
				if (empty($links_xsdmf_id) || $links_xsdmf_id < 0
				|| empty($link_labels_xsdmf_id) || $link_labels_xsdmf_id < 0) {
					Error_Handler::logError("Couldn't merge the record link ".$link,__FILE__,__LINE__);
					$error = PEAR::raiseError("Couldn't merge the record link: bad xsdmf_id on base "
					. "document type");
					break;
				}
				$this->mergeDetailGeneric($base_det, $links_xsdmf_id, $link);
				$this->mergeDetailGeneric($base_det, $link_labels_xsdmf_id, $link_label);
			}
		}

		if (empty($error)) {
			return $base_det;
		} else {
			return $error;
		}
	}

	function merge_M_Datastreams(&$base_record, &$dup_record, $base_det)
	{
		$error = 0;
		$dup_datastreams = Fedora_API::callGetDatastreams($dup_record->pid);
		$base_datastreams = Fedora_API::callGetDatastreams($base_record->pid);
		$copied_ds = array();
		foreach ($dup_datastreams as $ds_key => $ds) {
			if ($ds['controlGroup'] == 'M') {
				$found_in_base = false;
				foreach ($base_datastreams as $base_ds_key => $base_ds) {
					if ($base_ds['controlGroup'] == 'M') {
						if ($base_ds['ID'] == $ds['ID']) {
							$found_in_base = true;
						}
					}
				}
				if (!$found_in_base) {
					// copy dup binary DS to base
					$value = Fedora_API::callGetDatastreamContents($dup_record->pid, $ds['ID'], true);
					Fedora_API::getUploadLocation($base_record->pid, $ds['ID'], $value, $ds['label'],
					$ds['MIMEType'], $ds['controlGroup'], null, $ds['versionable']);
					$copied_ds[] = $ds['ID'];
				}
			}
		}
		if (empty($error)) {
			return $base_det;
		} else {
			return $error;
		}
	}


	/**
	 * Look for <mods:identifier type="isi_loc"> on both records and if they're the same
	 * return true.
	 */
	function compareISI_LOC(&$record1, &$record2)
	{
		$res = false;
		$isi1 = trim($this->getISI_LOC($record1));
		if (!empty($isi1)) {
			$isi2 = trim($this->getISI_LOC($record2));
			if ($isi1 == $isi2) {
				$res = true;
			}
		}
		return $res;
	}

	function getISI_LOC(&$record)
	{
		return $this->getIdentifier($record, 'isi_loc');
	}

	function getRM_PRN(&$record)
	{
		return $this->getIdentifier($record, 'rm_prn');
	}

	function getIdentifierXSDMF_ID(&$record, $find_type)
	{
		$in_sublooping_element = true;
		$sel_title = str_replace('_', ' ', strtoupper($find_type));
		$xsdmf_id = $record->display->xsd_html_match->getXSDMF_ID_ByElementInSubElement(
											'!identifier',$sel_title,
											'!identifier');
		if ($xsdmf_id < 0) {
			$xsdmf_id = $record->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!identifier');
			$in_sublooping_element = false;
		}
		return array($xsdmf_id, $in_sublooping_element);
	}

	function getIdentifier(&$record, $find_type)
	{
		// get the mods:identifier.

		// assumes the sub looping title is the identifier type in caps with no underscore
		$sel_title = str_replace('_', ' ', strtoupper($find_type));
		$record->getDisplay();
		$record->display->getXSD_HTML_Match();
		$xsdmf_id = $record->display->xsd_html_match->getXSDMF_ID_ByElementInSubElement(
											'!identifier',$sel_title,
											'!identifier');
		if ($xsdmf_id < 0) {
			$res = '';
			$types = $record->getDetailsByXSDMF_element('!identifier!type');
			$identifiers = $record->getDetailsByXSDMF_element('!identifier');
			if (is_array($types)) {
				foreach ($types as $key => $type) {
					if ($type == $find_type) {
						$res = $identifiers[$key];
						break;
					}
				}
				// If there is only one identifier element: ...
			} elseif (!empty($types) && $types == $find_type) {
				$res = $identifiers;
			}
		} else {
			$res = $record->getDetailsByXSDMF_ID($xsdmf_id);
		}
		return $res;
	}


    function compareRecords(&$record1, &$record2)
    {

		$page_score = 1; //initialise with 1 for no change to the final score
		$start_page1 = Record::getSearchKeyIndexValue($record1->pid, "Start Page", false);
		$start_page2 = Record::getSearchKeyIndexValue($record2->pid, "Start Page", false);

		$end_page1 = Record::getSearchKeyIndexValue($record1->pid, "End Page", false);
		$end_page2 = Record::getSearchKeyIndexValue($record2->pid, "End Page", false);


		//If the stand and end pages match exactly put this in the dedupe report
		if ($start_page1 == $start_page2 && $end_page1 == $end_page2 && is_numeric($start_page1) && is_numeric($end_page1)) {
                        // MT: 2009-11-30 Commented out what looks like debug to me.
                        // echo "\n Matched on Page numbers\n";
			return 1;
		}

		//If either the start or end pages match exactly, double the final score, otherwise dont change final score
		if (($start_page1 == $start_page2 || $end_page1 == $end_page2) && is_numeric($start_page1) && is_numeric($end_page1)) {
                        // MT: 2009-11-30 Commented out what looks like debug to me.
                        // echo "\n Partially matched on Page numbers\n";
			$page_score = 1.5;
		}

		// If both are not book chapters then compare on isbn - if match then put in the dedupe report. Even if one is a wrong doc type is should be in the report because the doc type will need changing
        if (!is_numeric(strpos($record1->getDocumentType(), 'Book Chapter'))
        		&& !is_numeric(strpos($record2->getDocumentType(), 'Book Chapter'))) {

			$isbn1 = Record::getSearchKeyIndexValue($record1->pid, "ISBN", false);
			$isbn2 = Record::getSearchKeyIndexValue($record2->pid, "ISBN", false);

			if (sort($isbn1) == sort($isbn2) && count($isbn1) > 0 ) {
				return 1;
			}
		}

        $title_tokens1 = $this->tokenise(array(strtolower($record1->getTitle())));
        $title_tokens2 = $this->tokenise(array(strtolower($record2->getTitle())));
        $title_score = $this->calcOverlap($title_tokens1, $title_tokens2);

		$author_tokens1 = $this->tokenise($record1->getFieldValueBySearchKey('Author'));
		$author_tokens2 = $this->tokenise($record2->getFieldValueBySearchKey('Author'));
		$author_score = $this->calcOverlap($author_tokens1, $author_tokens2);
		$author_score = (($author_score+1)/2);

		// if this is a journal
		if (is_numeric(strpos($record1->getDocumentType(), 'Journal Article'))
		&& is_numeric(strpos($record2->getDocumentType(), 'Journal Article'))) {
			$journal_tokens1 = $this->tokenise($record1->getFieldValueBySearchKey('Journal Name'));
			//$journal_tokens1 = $this->tokenise($record1->getDetailsByXSDMF_element(
			//												'!relatedItem!name!namePart'));
			$journal_tokens2 = $this->tokenise($record2->getFieldValueBySearchKey('Journal Name'));
			//$journal_tokens2 = $this->tokenise($record2->getDetailsByXSDMF_element(
			//												'!relatedItem!name!namePart'));
			/*echo "\n journal tokens 1: ";
			 print_r($journal_tokens1);
			 echo "\n journal tokens 2: ";
			 print_r($journal_tokens2); */
			$journal_title_score = $this->calcOverlap($journal_tokens1, $journal_tokens2);
		} else {
			$journal_title_score = 1;
		}
		//			echo "\n (t)".$title_score."\n";
		// If the title is an exact match on the title words and the total number of words is at least 3 (greater than 2x2 = 4 total) in each
		if ($title_score == 1 && ( (count($title_tokens2) + count($title_tokens1)) > 4 )   ) {
			return 1;
		}
//echo "\n (t)".$title_score." - (a)".$author_score." - (j)".$journal_title_score."\n";
        return ($title_score * $author_score * $journal_title_score) * $page_score;
    }

	function calcOverlap($array1,$array2)
	{
		if ((count($array1) + count($array2)) == 0) {
			return 1;
		}
		return count(array_intersect($array1, $array2)) * 2  / (count($array1) + count($array2));
	}

    function tokenise($array_of_strings)
    {
        $array_of_strings = Misc::array_flatten($array_of_strings, '', true);

    	if (!is_array($array_of_strings)) {
        	$tokens = explode(' ',(preg_replace("/[^a-zA-Z0-9 ]/", "", $array_of_strings)));
        } else {
	        $tokens = explode(' ',(preg_replace("/[^a-zA-Z0-9 ]/", "", implode(' ', $array_of_strings))));
        }
        // get rid of anything three chars or less - initials and stuff
        $tokens = array_filter($tokens, array($this,'shortWordsFilter'));
        return array_values($tokens);

	}

	function shortWordsFilter($a)
	{
		return (strlen($a) > 3);
	}


  function authorTokenise($array_of_strings)
  {
      $array_of_strings = Misc::array_flatten($array_of_strings, '', true);

    if (!is_array($array_of_strings)) {
        $tokens = explode(' ',(preg_replace("/[^a-zA-Z0-9 ]/", "", $array_of_strings)));
      } else {
        $tokens = explode(' ',(preg_replace("/[^a-zA-Z0-9 ]/", "", implode(' ', $array_of_strings))));
      }
      // get rid of anything three chars or less - initials and stuff
      $tokens = array_filter($tokens, array($this,'authorShortWordsFilter'));
      return array_values($tokens);

}

function authorShortWordsFilter($a)
{
  return (strlen($a) >= 3 || (strlen($a) > 1 && $a != strtoupper($a)));
}


	function getListing($page, $page_size, $show_resolved = true)
	{
		$report_dom = $this->getXML_DOM();
		if (empty($report_dom)) {
			return -1;
		}
		$xpath = new DOMXPath($report_dom);
		$first_item = $page * $page_size;
		$last_item = $first_item + $page_size;
		$items = $xpath->query('/DuplicatesReport/duplicatesReportItem');
		$listing = array();
		$resolved_count = 0;
		$unresolved_count = 0;
		$isi_loc_match_count = 0;
		$found_count = 0;
		$merge_ok_count = 0;
		$left_pids = array();
		for ($ii = 0; $ii < $items->length; $ii++) {
			$itemNode = $items->item($ii);
			if (!empty($itemNode)) {


				$left_pid = '';
				$left_pid = $itemNode->getAttribute('pid');
				if ($left_pid != '') {
					if (!in_array($left_pids, $left_pids)) {
						array_push($left_pids, $left_pid);
					}
				}


				list($max_score,$resolved,$isi_match, $dups_count, $merge_result) = $this->getDupsStats($left_pid, $xpath, $itemNode, $ii);
				if ($resolved) {
					$resolved_count++;
				} else {
					$unresolved_count++;
				}
				if ($isi_match) {
					$isi_loc_match_count++;
				}
				if ($merge_result) {
					$merge_ok_count++;
				}
				if ($found_count >= $first_item && $found_count < $last_item
				&& (($resolved && $show_resolved) || !$resolved)) {
					$listing_item = array(
                    	'pid' => $itemNode->getAttribute('pid'),
	                    'title' => $itemNode->getAttribute('title'),
    	            	'probability' => $max_score,
            	    	'resolved' => $resolved,
                		'count' => $dups_count,
	                	'isi_loc_match' => $isi_match,
	                	'merge_result' => $merge_result
					);
					$listing[] = $listing_item;
				}
				if (($resolved && $show_resolved) || !$resolved) {
					$found_count++;
				}
			}
		}
		$pages = intval(floor(($found_count / $page_size) + 0.999999));
		$list_meta = compact('pages', 'resolved_count', 'unresolved_count','isi_loc_match_count',
        						'merge_ok_count');
		return compact('listing','list_meta');
	}

	function getDupsStats($left_pid, $xpath, $itemNode, $ii)
	{
		$max_score = 0;
		//        $dups_list = $xpath->query('duplicateItem', $itemNode);
//		$dups_list = $xpath->query("/DuplicatesReport/duplicatesReportItem[@pid='".$left_pid."']/duplicateItem");
		$dups_list = $xpath->query("/DuplicatesReport/duplicatesReportItem[".($ii+1)."]/duplicateItem");
		$resolved = true;
		$base_isi_loc = $itemNode->getAttribute('isi_loc');
		//$base_isi_loc = '';
		$isi_match = false;
		$merge_result = true;
		$dups_count = $dups_list->length;

		$right_pids = array();
		$right_pid = '';

		foreach ($dups_list as $dupNode) {

			$right_pid = '';
			$right_pid = $dupNode->getAttribute('pid');
			if ($right_pid != '') {
				if (!in_array($right_pid, $right_pids)) {
					array_push($right_pids, $right_pid);
				} else {
					continue;
				}
			}


			$score = $dupNode->getAttribute('probability');
			if ($score > $max_score) {
				$max_score = $score;
			}
			// if we find at least one blank duplicate attribute in the set of
			// dupes then this is not resolved yet.
			$dup_att = $dupNode->getAttribute('duplicate');
			if (empty($dup_att)) {
				$resolved = false;
			}
			if (!empty($base_isi_loc)) {
				$dup_isi_loc = $dupNode->getAttribute('isi_loc');
				if ($dup_isi_loc == $base_isi_loc) {
					$isi_match = true;
				}
			}
			$mres = $dupNode->getAttribute('mergeResult');
			if ($mres != 'ok') {
				$merge_result = false;
			}
		}
		return array($max_score,$resolved,$isi_match, $dups_count, $merge_result);
	}

	function getPrevItem($pid, $show_resolved = true)
	{
		return $this->getNextPrevItemCommon($pid, $show_resolved, 'previousSibling');
	}

	function getNextItem($pid, $show_resolved = true)
	{
		return $this->getNextPrevItemCommon($pid, $show_resolved, 'nextSibling');
	}

	function getNextPrevItemCommon($pid, $show_resolved, $axis)
	{
		$report_dom = $this->getXML_DOM();
		if (empty($report_dom)) {
			return -1;
		}
		$xpath = new DOMXPath($report_dom);
		$done = false;
		$res = 0;
		// loops through pids
		$nodelist = $xpath->query(
						'/DuplicatesReport/duplicatesReportItem[@pid=\''.$pid.'\']');
		if ($nodelist->length > 0) {
			$node = $nodelist->item(0);
            $ii = 0;
			for ($node = $node->$axis; !empty($node) && !$done; $node = $node->$axis) {
				if ($node->nodeType == XML_ELEMENT_NODE) {
					$pid = $node->getAttribute('pid');
					if (!$show_resolved) {
						list($max_score,$resolved,$isi_match,$merge_result) = $this->getDupsStats($pid, $xpath, $node, $ii);
					}
					if ($show_resolved || !$resolved) {
						$done = true;
						$res = $pid;
					}
                    $ii++;
				}
			}
		}
		return $res;
	}

	function getItemDetails($pid, $xml='')
	{
        if ($xml != '') {
            if (is_string($xml)) {
                $config = array(
                    'indent'        => true,
                    'input-xml'     => true,
                    'output-xml'    => true,
                    'wrap'          => 0
                );
                $tidy = new tidy;
                $tidy->parseString($xml, $config, 'utf8');
                $tidy->cleanRepair();
                $xml = $tidy;
//                $this->setXML_DOM(new DOMDocument($xml));
                $this->setXML_DOM(DOMDocument::loadXML($xml));
            } else {
                return array();
            }
        }
        if (!isset($pid) || $pid == '') {
            return array();
        }
		$report_dom = $this->getXML_DOM();
		if (empty($report_dom)) {
			return array();
		}
		$xpath = new DOMXPath($report_dom);
        $x = $report_dom->saveXML();
//		$items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$pid.'\']/duplicateItem');
        $query = '/DuplicatesReport/duplicatesReportItem[@pid=\''.$pid.'\']/duplicateItem';
		$items = $xpath->query($query);
		$listing = array();
		foreach ($items as $item) {
			$listing_item = array();
			$right_pid = '';
			foreach (array('pid','probability','duplicate','mergeResult') as $att) {
				$listing_item[$att] = $item->getAttribute($att);
				if ($att == 'pid') {
					$right_pid = $listing_item[$att];
				}
			}
			if ($right_pid != '' && !array_key_exists($right_pid, $listing)) { //if base pid appears more than once than try and merge them
				$listing[$right_pid] = $listing_item;
			}

			//            $listing[] = $listing_item;

		}
        $list_meta = array();
		$listing = array_values($listing);
		$res = array(
        	'listing' => $listing,
        	'list_meta' => $list_meta
		);
		return $res;
	}

	/**
	 * @param boolean $merge_hidden - when automergeing, this should be set to false as there is no need
	 * to merge the hidden fields.  When manually merging, this is set to true so that the hidden fields
	 * will be mertged.
	 */
	function markDuplicate($base_pid, $dup_pid, $merge_hidden = true)
	{
		$log = FezLog::get();

		$res = $this->setDuplicateXML($base_pid, $dup_pid, true);
		if ($res == 1) {
			$this->saveReport($this->xml_dom->saveXML());
			$rec = new RecordObject($dup_pid);
			if ($merge_hidden) {
				$base_rec = new RecordObject($base_pid);
				$this->mergeRecordsHiddenFields($base_rec, $rec);
			}


			// put object in limbo
			$rec->markAsDeleted();
			// set some history on the object
			$wfl_id = $this->getWorkflowId();
			$rec->tombstone($base_pid, $dup_pid);
			History::addHistory($dup_pid, $wfl_id, "", "", false, '', "Marked Duplicate of ".$base_pid);
			History::addHistory($base_pid, $wfl_id, "", "", true, '', "Resolved duplicate ".$dup_pid);

			if ( APP_SOLR_INDEXER == "ON" ) {
				FulltextQueue::singleton()->remove($dup_pid);
				FulltextQueue::singleton()->commit();
				FulltextQueue::singleton()->triggerUpdate();
			}
		} else {
			$log->err(array("Failed to set ".$dup_pid." as duplicate of ".$base_pid
			." in XML (report pid".$this->pid.")", __FILE__,__LINE__));
		}
	}

	function markNotDuplicate($base_pid, $dup_pid)
	{
		$log = FezLog::get();

		$res = $this->setDuplicateXML($base_pid, $dup_pid, false);
		if ($res == 1) {
			$this->saveReport($this->xml_dom->saveXML());
			// get object back from limbo
			$rec = new RecordObject($dup_pid);
			$rec->markAsActive();
			// set some history on the object
			$wfl_id = $this->getWorkflowId();
			History::addHistory($dup_pid, $wfl_id, "", "", false, '',
            											"Marked not duplicate of " . $base_pid);
			History::addHistory($base_pid, $wfl_id, "", "", true, '',
            												"Resolved non-duplicate " . $dup_pid);

		} else {
			$log->err(array("Failed to set ".$dup_pid." as non-duplicate of ".$base_pid
			." in XML (report pid".$this->pid.")", __FILE__,__LINE__));
		}
	}

	/**
	 * checks if there are files that require merging
	 *
	 * @return void
	 **/
	public function filesExistInDuplicatePid($dup_pid)
	{
		$log = FezLog::get();
		$files = self::getFilesForPid($dup_pid);
		return count($files) > 0;
	}

	/**
	 * gets the file for a specified pid
	 *
	 * @param string $pid
	 * @return array
	 **/
	public function getFilesForPid($pid)
	{
		$log = FezLog::get();

		$datastreams = Fedora_API::callGetDatastreams($pid);
		$datastreams = Misc::cleanDatastreamList($datastreams); //This may have issues $datastreams seems wrong format

		$files = array();
		foreach($datastreams as $ds) {
			if ($ds['controlGroup'] == 'M')
				$files[] = $ds['ID'];
		}

		return $files;

	}

	/**
	 * Copies a file datastream from one pid into another (with optional new name)
	 *
	 * @param string $originalPid
	 * @param string $originalFilename
	 * @param string $newPid
	 * @param string $newFilename (optional)
	 * @return void
	 **/
	public function copyFileDatastream($originalPid, $originalFilename, $newPid, $newFilename = null)
	{
		$log = FezLog::get();

		if ($newFilename == null)
			$newFilename = $originalFilename;

		// get the original details
		$datastream = Fedora_API::callGetDatastream($originalPid, $originalFilename);
		$value = Fedora_API::callGetDatastreamContents($originalPid, $originalFilename, true);

		// and upload into fedora
		Fedora_API::getUploadLocation($newPid, $newFilename, $value, $datastream['label'],$datastream['MIMEType'], $datastream['controlGroup'], null, $datastream['versionable']);

	}

	/**
	 * setDuplicateXML - sets the duplicate attribute on the dup_pid for the base_pid in the XML report.
	 * @param string $base_pid
	 * @param string $dup_pid
	 * @param string $xml - must be valid XML of DuplicatesReport
	 * @param boolean $is_duplicate - the duplicate attribute will be set to "true" or "false" based on this
	 * @return string modified XML string on success, null on failure.
	 */
	function setDuplicateXML($base_pid, $dup_pid, $is_duplicate = true)
	{
		$report_dom = $this->getXML_DOM();
		if (empty($report_dom)) {
			return -1;
		}
		if (empty($base_pid) || !is_string($base_pid)) {
			return -1;
		}
		if (empty($dup_pid) || !is_string($dup_pid)) {
			return -1;
		}
		$xpath = new DOMXPath($report_dom);
		$items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$base_pid.'\']/duplicateItem[@pid=\''.$dup_pid.'\']');
		if ($items->length > 0) {
			$item = $items->item(0);
			$item->setAttribute('duplicate',$is_duplicate?'true':'false');
		} else {
			// the Pids should exist otherwise it's an error
			return -1;
		}
		return 1;
	}

	/**
	 * @param boolean $save_now - Set this to true to cause the result to be saved to the fedora
	 * object, otherwise if there is going to be another call to change the XML, there is no need to
	 * write this.
	 */
	function setMergeResult($base_pid, $dup_pid, $mres, $save_now = true)
	{
		$log = FezLog::get();

		$res = $this->setMergeResultXML($base_pid, $dup_pid, $mres);
		if ($res == 1) {
			if ($save_now) {
				$this->saveReport($this->xml_dom->saveXML());
			}
		} else {
			$log->err(array("Failed to set merge result for dup "
			.$dup_pid." on base ".$base_pid
			." in XML (report pid".$this->pid.")", __FILE__,__LINE__));
		}
	}

	function setMergeResultXML($base_pid, $dup_pid, $mres)
	{
		$report_dom = $this->getXML_DOM();
		if (empty($report_dom)) {
			return -1;
		}
		if (empty($base_pid) || !is_string($base_pid)) {
			return -1;
		}
		if (empty($dup_pid) || !is_string($dup_pid)) {
			return -1;
		}
		$xpath = new DOMXPath($report_dom);
		$items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$base_pid.'\']'
		.'/duplicateItem[@pid=\''.$dup_pid.'\']');
		if ($items->length > 0) {
			$item = $items->item(0);
			$item->setAttribute('mergeResult',$mres);
		} else {
			// the Pids should exist otherwise it's an error
			return -1;
		}
		return 1;
	}

	function swapBase($base_pid,$dup_pid)
	{
		$log = FezLog::get();

		$res = $this->swapBaseXML($base_pid,$dup_pid);
		if ($res == 1) {
			$res = $this->recalcDupScoresXML($dup_pid);
		} else {
			$log->err(array("Failed to swap dup ".$dup_pid." for base ".$base_pid
			." in XML (report pid".$this->pid.")", __FILE__,__LINE__));
			return;
		}
		if ($res == 1) {
			$this->saveReport($this->xml_dom->saveXML());
		} else {
			$log->err(array("Failed to recalc dup scores when trying to swap dup "
			.$dup_pid." for base ".$base_pid
			." in XML (report pid".$this->pid.")", __FILE__,__LINE__));
		}
	}

	function swapBaseXML($base_pid,$dup_pid)
	{
		$report_dom = $this->getXML_DOM();
		if (empty($report_dom)) {
			return -1;
		}
		if (empty($base_pid) || !is_string($base_pid)) {
			return -1;
		}
		if (empty($dup_pid) || !is_string($dup_pid)) {
			return -1;
		}
		$xpath = new DOMXPath($report_dom);
		$items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$base_pid.'\']/duplicateItem[@pid=\''.$dup_pid.'\']');
		if ($items->length < 1) {
			// the Pids should exist otherwise it's an error
			return -1;
		}
		$dup_item = $items->item(0);
		$dup_item->setAttribute('pid',$base_pid);
		$items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$base_pid.'\']');
		if ($items->length < 1) {
			// the base Pid should exist otherwise it's an error
			return -1;
		}
		$base_item = $items->item(0);
		$base_item->setAttribute('pid',$dup_pid);

		return 1;
	}

	/**
	 * Finds a base record and updates the duplicate scores for the dup records.  Called if the base record is swapped.
	 */
	function recalcDupScoresXML($base_pid)
	{
		$report_dom = $this->getXML_DOM();
		if (empty($report_dom)) {
			return -1;
		}
		if (empty($base_pid) || !is_string($base_pid)) {
			return -1;
		}
		$xpath = new DOMXPath($report_dom);
		$items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$base_pid.'\']/duplicateItem');
		if ($items->length < 1) {
			// the Pids should exist otherwise it's an error
			return -1;
		}
		$base_record = new RecordObject($base_pid);
		foreach ($items as $dup_item) {
			$dup_record = new RecordObject($dup_item->getAttribute('pid'));
			$dup_item->setAttribute('probability',$this->compareRecords($base_record, $dup_record));
		}
		return 1;
	}

	/**
	 * @return Levenshtein distance normalized to 1 (where 1 is completely differrent and 0 is the same)
	 */
	function calcLevenshtein($left, $right)
	{
		// for numeric values or pids, just give 0 or 1 (0 for == and 1 for !=)
		if ((Misc::isPid($left) && Misc::isPid($right))
		|| (is_numeric($left) && is_numeric($right))) {
			if ($left == $right) {
				return 0;
			} else {
				return 1;
			}
		}
		$maxlen = max(strlen($right),strlen($left));
		if ($maxlen > 255) {
			$left_array = $this->tokenise($left);
			$right_array = $this->tokenise($right);
			$score = $this->calcOverlap($left_array,$right_array);
			return 1-$score;
		}
		if ($maxlen > 0 && $maxlen <= 255) {
			return levenshtein($left, $right) / max(strlen($right),strlen($left));
		} else {
			return 1;
		}
	}

	function generateLevenshteinScores($left_details, $right_details)
	{
		$distances = array();
		foreach ($right_details as $xsdmf_id => $det) {
			if (is_array($det)) {
				if (is_array($left_details[$xsdmf_id])) {
					$distances[$xsdmf_id] = array();
					foreach ($det as $idx => $value) {
						if (isset($left_details[$xsdmf_id][$idx])) {
							$distances[$xsdmf_id][$idx]
							= $this->calcLevenshtein($left_details[$xsdmf_id][$idx], $value);
						} else {
							$distances[$xsdmf_id][$idx] = 1;
						}
					}
				} else {
					if (!empty($left_details[$xsdmf_id])) {
						$distances[$xsdmf_id] = $this->calcLevenshtein($left_details[$xsdmf_id], $value);
					} else {
						$distances[$xsdmf_id] = 1;
					}
				}
			} else {
				if (is_array($left_details[$xsdmf_id])) {
					$distances[$xsdmf_id][0] = $this->calcLevenshtein($left_details[$xsdmf_id][0], $det);
				} else {
					if (!empty($left_details[$xsdmf_id])) {
						$distances[$xsdmf_id] = $this->calcLevenshtein($left_details[$xsdmf_id], $det);
					} else {
						$distances[$xsdmf_id][$idx] = 1;
					}
				}
			}
		}
		return $distances;
	}

	function convertLevColour($lev)
	{
		if ($lev == 0) {
			list($r,$g,$b) = Misc::HSV2RGB(80,64,255);
		} else {
			list($r,$g,$b) = Misc::HSV2RGB(60-$lev*60,64,255);
		}
		return "rgb(" . $r . ", " . $g . ", " . $b . ")";
	}

	function convertLevColours($distances)
	{
		$colours = array();
		foreach ($distances as $xsdmf_id => $dist) {
			if (is_array($dist)) {
				$colours[$xsdmf_id] = array();
				foreach ($dist as $idx => $dval) {
					$colours[$xsdmf_id][$idx] = $this->convertLevColour($dval);
				}
			} else {
				$colours[$xsdmf_id] = $this->convertLevColour($dist);
			}
		}
		return $colours;
	}

	/**
	 * Checks if the records have been resolved in a different duplicates report and
	 * updates the current report to reflect this.
	 * @return integer 1 if there was a change, 0 if no change and <0 on error
	 */
	function checkResolvedElsewhere($base_record, $dup_record)
	{
		$res = 0;
		$base_pid = $base_record->pid;
		$dup_pid = $dup_record->pid;


		$report_dom = $this->getXML_DOM();
		$xpath = new DOMXPath($report_dom);
		$items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$base_pid.'\']'
		. '/duplicateItem[@pid=\''.$dup_pid.'\']');
		$item = $items->item(0);
		$duplicate = $item->getAttribute('duplicate');
		// if the item has been resolved in this report then no need to check whether it's been
		// resolved eleswhere.
		if (!empty($duplicate)) {
			return $res;
		}

		// look for marked as non duplicate
		$history_res = History::searchOnPid($dup_pid, array('pre_detail' => '% Marked not duplicate of '
		. $base_pid));
		if (empty($history_res)) {
			$history_res = History::searchOnPid($dup_pid, array('pre_detail' => '% Resolved non-duplicate '
			. $base_pid));
		}
		if (!empty($history_res)) {
			$res = $this->setDuplicateXML($base_pid, $dup_pid, false);
			if ($res == 1) {
				$this->saveReport($this->xml_dom->saveXML());
			}
		} else {
			// look for marked as dupe
			$history_res = History::searchOnPid($dup_pid, array('pre_detail' => '% Marked Duplicate of '
			. $base_pid));
			if (empty($history_res)) {
				$history_res = History::searchOnPid($dup_pid, array('pre_detail' => '% Resolved duplicate '
				. $base_pid));
			}
			if (!empty($history_res)) {
				$res = $this->setDuplicateXML($base_pid, $dup_pid, true);
				if ($res == 1) {
					$this->saveReport($this->xml_dom->saveXML());
				}
			}
		}
		return $res;
	}

}
