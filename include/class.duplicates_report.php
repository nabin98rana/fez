<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 15/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */

include_once(APP_INC_PATH.'class.record_edit_form.php'); 
 
class DuplicatesReport {

	const MERGE_TYPE_ALL = 0;
	const MERGE_TYPE_HIDDEN = 1;
	const MERGE_TYPE_RM_ISI = 2;
	
	const RELEVANCE_ISI_LOC_MATCH = -1;

    var $pid;
    var $bgp; // background process object for user feedback
    var $sets = array();
    var $xml_dom;
    var $wfl_id; // for recording history on any records that are modified.


    
    function __construct($pid=null) {
        $this->pid = $pid;    
    }
    
    function setBGP(&$bgp) {
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
    
    
    function generate($pids) {
        //Error_Handler::debugStart();
        $total_pids = count($pids);
        $progress = 0;
        $report_array = array();
        for ($ii = 0; $ii < count($pids); $ii++) {
            $pid = $pids[$ii];
            if (!empty($this->bgp)) {
                $this->bgp->setProgress(++$progress / $total_pids * 100);
                $this->bgp->setStatus("Processing ".$progress." of ".$total_pids.". ".$pid);
            }
            $record = new RecordGeneral($pid);
            if ($record->checkExists()) {
                // recurse into collections and communities by appending the children to 
                // the end of the list.
                if ($record->isCollection()) {
                	$pids = array_merge($pids, $record->getChildrenPids());
                	continue;
                }
                if ($record->isCommunity()) {
                	$pids = array_merge($pids, $record->getChildrenPids());
                	continue;
                }
                $res = $this->findSimilarRecords($record);

                if (count($res)) {
                    foreach ($res as $dup_row) {
                		$dup_pid = $dup_row['pid'];
                        $dup_rec = new RecordGeneral($dup_pid);
                        
                        if ($dup_row['relevance'] == self::RELEVANCE_ISI_LOC_MATCH) {
                    		$score = 1;
                    	} else {
                    	    $score = $this->compareRecords($record, $dup_rec);
                        	//echo "tokens: \n".print_r($tokens,true)."\n";
                        	//echo "dup_tokens: \n".print_r($dup_tokens,true)."\n";
                		}
                        if ($score > 0.5) {
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
                        }
                    }
                }
            }
        }
        $report_array = $this->mergeSets($report_array);
        $xml = $this->generateXML($report_array);
        $this->addReportToFedoraObject($xml);
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
    
    function addReportToFedoraObject($xml)
    {
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
    
    function getXML_DOM()
    {
        if (empty($this->xml_dom)) {
            $xml = Fedora_API::callGetDatastreamContents($this->pid, 'DuplicatesReport', true);
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
                $target_group_list = $final_groups[$group['pid']]['list'];
                $group_list = $group['list'];
                $new_list = array_merge($target_group_list, $group_list);
                $final_groups[$group['pid']]['list'] = $new_list;
            } else {
                $final_groups[$group['pid']] = $group;
            }
        }
        return $final_groups;
    }

    function recalcDupScores($final_groups)
    { 
        // recalculate links to the base record for each group
        foreach ($final_groups as $key => $group)
        {
            $base_record = new RecordGeneral($group['pid']);
            foreach ($group['list'] as $dup_pid => $dup_item) {
                if ($dup_item['isi_loc'] == $group['isi_loc']) {
            		$final_groups[$key]['list'][$dup_pid]['probability'] = 1;
            	} else {
	                $dup_record = new RecordGeneral($dup_item['pid']);
    	            $final_groups[$key]['list'][$dup_pid]['probability'] 
    	            	= $this->compareRecords($base_record, $dup_record);
	            }
            }
        }
        return $final_groups;
    }
    
    function findSimilarRecords($record)
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
        $pidnum = substr($pid, strpos($pid, ':') + 1);
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        
        // Do a isi_loc match on records that don't have the same pid as the candidate record
        // and are type '3' (records not collections or communities)
		$record = new RecordGeneral($pid);
		$isi_loc = $this->getISI_LOC($record); 

        $stmt = "SELECT distinct r2.rmf_rec_pid as pid, ".self::RELEVANCE_ISI_LOC_MATCH." as relevance " .
                "FROM  ".$dbtp."record_matching_field AS r2 " .
                "INNER JOIN ".$dbtp."xsd_display_matchfields AS x2 " .
                " ON r2.rmf_xsdmf_id=x2.xsdmf_id AND x2.xsdmf_element='!identifier' " .
                "    AND r2.rmf_varchar='$isi_loc' " .
                "    AND NOT (r2.rmf_rec_pid_num = ".$pidnum." AND r2.rmf_rec_pid = '".$pid."') " .
                "INNER JOIN ".$dbtp."record_matching_field AS r3 " .
				" ON r3.rmf_rec_pid_num=r2.rmf_rec_pid_num AND r3.rmf_rec_pid=r2.rmf_rec_pid " .
                "INNER JOIN ".$dbtp."xsd_display_matchfields AS x3 " .
                " ON r3.rmf_xsdmf_id=x3.xsdmf_id AND x3.xsdmf_element='!identifier!type' " .
                "    AND r3.rmf_varchar='isi_loc' " ;
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }

        return $res;
    }
    
    /**
     * @param $pid string - exclude this pid from the search
     * @param $title string - search for this title
     */
    function similarTitlesQuery($pid, $title)
    {
        $pidnum = substr($pid, strpos($pid, ':') + 1);
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        // Do a fuzzy title match on records that don't have the same pid as the candidate record
        // and are type '3' (records not collections or communities)
        $stmt = "SELECT distinct r2.rmf_rec_pid as pid, " .
                "  match (r2.rmf_varchar) against ('".Misc::escapeString($title)."') as relevance " .
                "FROM  ".$dbtp."record_matching_field AS r2 " .
                "INNER JOIN ".$dbtp."xsd_display_matchfields AS x2 " .
                "  ON r2.rmf_xsdmf_id = x2.xsdmf_id " .
                "  AND match (r2.rmf_varchar) against ('".$title."') " .
                "  AND NOT (rmf_rec_pid_num = ".$pidnum." AND rmf_rec_pid = '".$pid."') " .
                "INNER JOIN ".$dbtp."search_key AS s2 " .
                "  ON s2.sek_id = x2.xsdmf_sek_id " .
                "  AND s2.sek_title = 'Title' " .
                "INNER JOIN ".$dbtp."record_matching_field AS r3 " .
                "  ON r2.rmf_rec_pid_num=r3.rmf_rec_pid_num " .
                "  AND r2.rmf_rec_pid = r3.rmf_rec_pid " .
                "  AND r3.rmf_int = 3 " .
                "INNER JOIN ".$dbtp."xsd_display_matchfields AS x3 " .
                "  ON r3.rmf_xsdmf_id = x3.xsdmf_id " .
                "  AND x3.xsdmf_element = '!ret_id' " .
                "ORDER BY relevance DESC " .
                "LIMIT 0,10";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        return $res;
    }
    
    function autoMergeOnISI_LOC()
    {
        Error_Handler::debugStart();

        // get the report
        $report_dom = $this->getXML_DOM();
        $xpath = new DOMXPath($report_dom);
        $items = $xpath->query('/DuplicatesReport/duplicatesReportItem');
        $wfl_id = $this->getWorkflowId();

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
                    $base_rm_prn = $report_item->getAttribute('rm_prn');
                    $dup_isi_loc = $dup_item->getAttribute('isi_loc');
                    if (!empty($base_rm_prn) && !empty($dup_isi_loc)) {
	                    $merge_res = $this->mergeRecords($base_record, $dup_record, self::MERGE_TYPE_RM_ISI);
    	            } else {
        	            $merge_res = $this->mergeRecords($base_record, $dup_record);
            	    }
            	    if ($merge_res > 0) {
				        // set some history on the object so we know why it was merged.
				        History::addHistory($base_pid, $wfl_id, "", "", false, 
				        	"Merged on LOC_ISI with ".$dup_pid, null);
            	        $this->markDuplicate($base_pid, $dup_pid);
            	    }
                }
            }
        }
        Error_Handler::debugStop();
    }
    
    function mergeRecords($base_record, $dup_record, $merge_type = self::MERGE_TYPE_ALL)
    {
        
		switch ($merge_type)
		{
			case self::MERGE_TYPE_ALL:
		        $base_det = $this->mergeDetailsAll($base_record, $dup_record);
			break;
			case self::MERGE_TYPE_HIDDEN:
				if ($base_record->getXmlDisplayId() == $dup_record->getXmlDisplayId()) {
			        $base_det = $this->mergeDetailsHiddenSameDocType($base_record, $dup_record);
		        } else {
			        $base_det = $this->mergeDetailsHiddenDiffDocType($base_record, $dup_record);
		        }
			break;
			case self::MERGE_TYPE_RM_ISI:
		        $base_det = $this->mergeDetailsAll($base_record, $dup_record);
		        if (is_array($base_det)) {
		        	$base_det = $this->overrideRMDetails($base_record, $dup_record, $base_det);
	        	}
		        if (is_array($base_det)) {
			        $base_det = $this->mergeAuthorsRM_UQCited($base_record, $dup_record, $base_det);
		        }
			break;
		}
		
		// check for errors and don't merge if there was a problem
		if (!is_array($base_det)) {
			return $base_det;
		}
        
        $params = array();
        $params['sta_id'] = $base_record->getPublishedStatus();

        // Just want to find the basic xsdmf_ids for the title, date and user and set them to something useful
        $params['xsd_display_fields'] = $base_det; 
		$ref = new RecordEditForm();
		$ref->fixParams(&$params, $base_record);

        $base_record->fedoraInsertUpdate(array("FezACML"), array(""),$params);
        return 1;
    }


    function mergeDetailsAll($base_record, $dup_record)
    {
        // get the values for both records and copy over anything that isn't set in the base.
        $base_det = $base_record->getDetails();
        $dup_det = $dup_record->getDetails();
        foreach ($dup_det as $xsdmf_id => $dup_value) {
            if (!isset($base_det[$xsdmf_id]) || empty($base_det[$xsdmf_id])) {
                $base_det[$xsdmf_id] = $dup_value;
            } elseif (is_array($dup_value)) {
				if (is_array($base_det[$xsdmf_id])) {
                	$base_det[$xsdmf_id] = array_unique(array_merge($base_det[$xsdmf_id], $dup_value));
				} else {
            		$base_det[$xsdmf_id] = array_unique(array_merge(array($base_det[$xsdmf_id]), $dup_value));
        		}
            }
        }
        return $base_det;
    }
    
    function mergeDetailsHiddenSameDocType($base_record, $dup_record)
    {
        // get the values for both records and copy over anything that isn't set in the base.
        $base_det = $base_record->getDetails();
        $dup_det = $dup_record->getDetails();
        $base_record->getDisplay();
        $xsd_display_fields = Misc::keyArray($base_record->display->getMatchFieldsList(array("FezACML"), 
        								array("")), 'xsdmf_id');  
    	foreach ($dup_det as $xsdmf_id => $dup_value) {
        	// skip everything except hidden fields
            if ($xsd_display_fields[$xsdmf_id]['xsdmf_html_input'] != 'hidden') {
	        	continue;
    	    }
            if (!isset($base_det[$xsdmf_id]) || empty($base_det[$xsdmf_id])) {
	            $base_det[$xsdmf_id] = $dup_value;
    	    } elseif (is_array($dup_value)) {
				if (is_array($base_det[$xsdmf_id])) {
	            	$base_det[$xsdmf_id] = array_unique(array_merge($base_det[$xsdmf_id], $dup_value));
				} else {
        			$base_det[$xsdmf_id] = array_unique(array_merge(array($base_det[$xsdmf_id]), 
        										$dup_value));
        		}
	        }
    	}
        return $base_det;
    }

    function mergeDetailsHiddenDiffDocType($base_record, $dup_record)
    {
        // get the values for both records and copy over anything that isn't set in the base.
        $base_det = $base_record->getDetails();
        $dup_det = $dup_record->getDetails();

		// the records are different document types
		// not much we can do but will try to rescue any isi_loc or rm_prn
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
		foreach (array('rm_prn', 'isi_loc') as $id_type) {
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

        return $base_det;
    }



    function mergeRecordsHiddenFields($base_record, $dup_record)
    {
		return $this->mergeRecords($base_record, $dup_record, self::MERGE_TYPE_HIDDEN);
    }
    
    function overrideRMDetails($base_record, $dup_record, $base_det)
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
		$sub_xsdmf_id = $dup_record->display->xsd_html_match->getXSDMF_IDByXDIS_ID(
							'!relatedItem!part!detail');
		$subs = XSD_Loop_Subelement::getSimpleListByXSDMF($sub_xsdmf_id);
		if (!empty($subs)) {
			foreach ($subs as $sub) {
				if ($sub['xsdsel_title'] == 'Volume') {
					$sub_id = $sub['xsdsel_id'];
				}
			}
		}
		if (!empty($sub_id)) {
			$xsdmf_id = $dup_record->display->xsd_html_match->getXSDMF_IDBySELXDIS_ID(
								'!part!detail!number', $sub_id);
			if (!empty($base_det[$xsdmf_id]) && $base_det[$xsdmf_id] != $dup_det[$xsdmf_id]) {
				$error = -1;
			} elseif (empty($base_det[$xsdmf_id])) {
				$base_det[$xsdmf_id] = $dup_det[$xsdmf_id];
			}
		} else {
			$error = -2;
		}
		if (empty($error)) {
    		return $base_det;
		} else {
			return $error;
		}
    }
    
    function mergeAuthorsRM_UQCited($base_record, $dup_record, $base_det)
    {
    	/* the rules are:
    	   if the base author is blank (there are more authors on the dup than the base) then copy them across.
    	   if the ids match, then use the UQ Cited Author Display name (dup)
    	   if the author names have a good levenstein score and the base id is not set (0) then use the dup
    	   if the author names have a bad score then return -1
    	   	
    	*/
    	$error = 0;
    	
    	$base_authors = $base_record->getFieldValueBySearchKey('Author');
    	$base_author_ids = $base_record->getFieldValueBySearchKey('Author ID');
    	
    	$dup_authors = $dup_record->getFieldValueBySearchKey('Author');
    	$dup_author_ids = $dup_record->getFieldValueBySearchKey('Author ID');
    	
    	$res_authors = array();
    	$res_author_ids = array();
    	
    	Error_Handler::debug('MSS', compact('base_authors','base_author_ids','dup_authors', 'dup_author_ids'));
    	
		for ($ii = 0; $ii < count($dup_authors); $ii++) {
			if (!isset($base_authors[$ii])) {
				$res_authors[] = $dup_authors[$ii];
				$res_author_ids[] = $dup_author_ids[$ii];
			} elseif ($base_author_ids[$ii] == $dup_author_ids[$ii]) {
				$res_authors[] = $dup_authors[$ii];
				$res_author_ids[] = $dup_author_ids[$ii];
			} elseif (empty($base_author_ids[$ii]) 
				&& levenshtein($base_authors[$ii], $dup_authors[$ii]) < strlen($dup_authors[$ii]) / 2) {
				$res_authors[] = $dup_authors[$ii];
				$res_author_ids[] = $dup_author_ids[$ii];
				$lev = levenshtein($base_authors[$ii], $dup_authors[$ii]);
				Error_Handler::debug('MSS', compact('lev'));
			} else {
				$error = -1;
				break;
			}
		}
		Error_Handler::debug('MSS', compact('res_authors','res_author_ids','error'));
		
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
     * Look for <mods:identifier type="isi_loc"> on both records and if they're the same
     * return true.
     */
    function compareISI_LOC($record1, $record2)
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
    
    function getISI_LOC($record) 
    {
    	return $this->getIdentifier($record, 'isi_loc');
    }

    function getRM_PRN($record) 
    {
    	return $this->getIdentifier($record, 'rm_prn');
    }
    
    function getIdentifier($record, $find_type)
    {
        // get the mods:identifier.
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
        return $res;
    }

    
    function compareRecords($record1, $record2) 
    {
        /**
        // Can't compare records of different content models as the XSDMFs will all be different
        * actually I changed my mind.  We are going to show them but not merge them
        if ($record1->getXmlDisplayId() != $record2->getXmlDisplayId()) {
            return 0;
        }
        */
        
        $title_tokens1 = $this->tokenise(array($record1->getTitle()));
        $title_tokens2 = $this->tokenise(array($record2->getTitle()));
        $title_score = $this->calcOverlap($title_tokens1, $title_tokens2);

        $author_tokens1 = $this->tokenise($record1->getFieldValueBySearchKey('Author'));
        $author_tokens2 = $this->tokenise($record2->getFieldValueBySearchKey('Author'));
        $author_score = $this->calcOverlap($author_tokens1, $author_tokens2);
        return $title_score * $author_score;
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
        $tokens = explode(' ',implode(' ', $array_of_strings));
        // get rid of anything three chars or less - initials and stuff
        $tokens = array_filter($tokens, array($this,'shortWordsFilter'));
        return array_values($tokens);
        
    }
    
    function shortWordsFilter($a)
    {
        return strlen($a) > 3;
    } 

    function getListing($page, $page_size)
    {
        $report_dom = $this->getXML_DOM();
        if (empty($report_dom)) {
            return -1;
        }
        $xpath = new DOMXPath($report_dom);
        $first_item = $page * $page_size;
        $last_item = $first_item + $page_size;
        $items = $xpath->query('/DuplicatesReport/duplicatesReportItem');
        $pages = intval(floor(($items->length / $page_size) + 0.999999));
        $listing = array();
        for ($ii = $first_item; $ii < $last_item && $ii < $items->length; $ii++) {
            $itemNode = $items->item($ii);
            if (!empty($itemNode)) {
                $listing_item = array(
                    'pid' => $itemNode->getAttribute('pid'),
                    'title' => $itemNode->getAttribute('title'),
                );
                $max_score = 0;
                $dups_list = $xpath->query('duplicateItem', $itemNode);
                $resolved = true;
                foreach ($dups_list as $dupNode) {
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
                }
                $listing_item['probability'] = $max_score;
                $listing_item['resolved'] = $resolved;
                $listing_item['count'] = $dups_list->length;
                $listing[] = $listing_item;
            }
        }
        $list_meta = compact('pages');
        return compact('listing','list_meta');
    }
    
    function getItemDetails($pid)
    {
        $xml = Fedora_API::callGetDatastreamContents($this->pid, 'DuplicatesReport', true);
        return $this->getItemDetailsFromXML($pid,$xml);
    }
    
    function getItemDetailsFromXML($pid,$xml)
    {
        if (is_null($xml) || !is_string($xml) || empty($xml)) {
            return array();
        }
        $report_dom = DOMDocument::loadXML($xml);
        if (empty($report_dom)) {
            return array();
        }
        $xpath = new DOMXPath($report_dom);
        $items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$pid.'\']/duplicateItem');
        $listing = array();
        foreach ($items as $item) {
            $listing_item = array();
            foreach (array('pid','probability','duplicate') as $att) {
                $listing_item[$att] = $item->getAttribute($att);
            }
            $listing[] = $listing_item;
        }
        return $listing;
    }
    
    function markDuplicate($base_pid, $dup_pid)
    {
        $res = $this->setDuplicateXML($base_pid, $dup_pid, true);
        if ($res == 1) {
            $this->addReportToFedoraObject($this->xml_dom->saveXML());
            // put object in limbo
            $rec = new RecordObject($dup_pid);
            $base_rec = new RecordObject($base_pid);
            $this->mergeRecordsHiddenFields($base_rec, $rec);
            $rec->markAsDeleted();
            // set some history on the object
            $wfl_id = $this->getWorkflowId();
            History::addHistory($dup_pid, $wfl_id, "", "", false, "Marked Duplicate of ".$base_pid, null);
            History::addHistory($base_pid, $wfl_id, "", "", true, "Resolved duplicate ".$dup_pid, null);
        } else {
            Error_Handler::logError("Failed to set ".$dup_pid." as duplicate of ".$base_pid
                ." in XML (report pid".$this->pid.")", __FILE__,__LINE__);
        }
    }

    function markNotDuplicate($base_pid, $dup_pid)
    {
        $res = $this->setDuplicateXML($base_pid, $dup_pid, false);
        if ($res == 1) {
            $this->addReportToFedoraObject($this->xml_dom->saveXML());
            // get object back from limbo
            $rec = new RecordObject($dup_pid);
            $rec->markAsActive();
            // set some history on the object
            $wfl_id = $this->getWorkflowId();
            History::addHistory($dup_pid, $wfl_id, "", "", false, "Marked not duplicate of".$base_pid, null);
            History::addHistory($base_pid, $wfl_id, "", "", true, "Resolved non-duplicate ".$dup_pid, null);
            
        } else {
            Error_Handler::logError("Failed to set ".$dup_pid." as non-duplicate of ".$base_pid
                ." in XML (report pid".$this->pid.")", __FILE__,__LINE__);
        }
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
    
    function swapBase($base_pid,$dup_pid)
    {
        $res = $this->swapBaseXML($base_pid,$dup_pid);
        if ($res == 1) {
            $res = $this->recalcDupScoresXML($dup_pid);
        } else {
            Error_Handler::logError("Failed to swap dup ".$dup_pid." for base ".$base_pid
                ." in XML (report pid".$this->pid.")", __FILE__,__LINE__);
            return;
        }
        if ($res == 1) {
            $this->addReportToFedoraObject($this->xml_dom->saveXML());
        } else {
            Error_Handler::logError("Failed to recalc dup scores when trying to swap dup "
                .$dup_pid." for base ".$base_pid
                ." in XML (report pid".$this->pid.")", __FILE__,__LINE__);
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
        $base_record = new RecordGeneral($base_pid);
        foreach ($items as $dup_item) {
            $dup_record = new RecordGeneral($dup_item->getAttribute('pid'));
            $dup_item->setAttribute('probability',$this->compareRecords($base_record, $dup_record));
        }
        return 1;
    }
    
}



?>
