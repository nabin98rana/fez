<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 15/05/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
 
class DuplicatesReport {
    var $pid;
    var $bgp; // background process object for user feedback
    var $sets = array();
   
    function __construct($pid=null) {
        $this->pid = $pid;    
    }
    
    function setBGP(&$bgp) {
        $this->bgp = $bgp;
    }
    
    function generate($pids) {
        $total_pids = count($pids);
        $progress = 0;
        
        $report_array = array();
        foreach ($pids as $pid) {
            if (!empty($this->bgp)) {
                $this->bgp->setProgress(++$progress / $total_pids * 100);
                $this->bgp->setStatus("Processing ".$progress." of ".$total_pids.". ".$pid);
            }
            $record = new RecordGeneral($pid);
            if ($record->checkExists()) {
                $res = $this->findSimilarPidsFirstPass($record);
                if (count($res)) {
                    foreach ($res as $dup_row) {
                        $dup_rec = new RecordGeneral($dup_row['pid']);
                        $score = $this->compareRecords($record, $dup_rec);
                        //echo "tokens: \n".print_r($tokens,true)."\n";
                        //echo "dup_tokens: \n".print_r($dup_tokens,true)."\n";
                        if ($score > 0.5) {
                            if (!isset($report_array[$pid])) {
                                $report_array[$pid] = array();
                                $report_array[$pid]['title'] = $record->getTitle();
                            }
                            $report_array[$pid]['list'][$dup_row['pid']] = array('pid' => $dup_row['pid'], 
                                                                                 'probability' => $score, 
                                                                                 'title' => $dup_rec->getTitle());
                        }
                    }
                }
            }
        }
        $report_array = $this->mergeSets($report_array);
        $xml = $this->generateXML($report_array);
        $this->addReportToFedoraObject($xml);
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
            $report_item->setAttribute('pid', $pid);
            $report_item->setAttribute('title', $item['title']);
            $report_root->appendChild($report_item);
            foreach ($item['list'] as $list_item_pid => $list_item) {
                $report_dup_list_item = $report_dom->createElement('duplicateItem');
                $report_dup_list_item->setAttribute('pid', $list_item_pid);
                $report_dup_list_item->setAttribute('probability', $list_item['probability']);
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
            $items = array_merge($item['list'], array(array('pid'=>$pid,'title'=>$item['title'])));
            $items = Misc::keyArray($items, 'pid');         
            uksort($items, array('Misc','comparePIDs'));
            $base = array_shift($items);
            $rearranged_report[] = array(
                    'pid' => $base['pid'],
                    'list' => $items, 
                    'title' => $base['title']
                );
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
                $dup_record = new RecordGeneral($dup_item['pid']);
                $final_groups[$key]['list'][$dup_pid]['probability'] = $this->compareRecords($base_record, $dup_record);
            }
        }
        return $final_groups;
    }
    
    function findSimilarPidsFirstPass($record)
    {
        $pid = $record->pid;
        $title = $record->getTitle(); // first we'll look for records with similar titles
        return $this->similarPidsQuery($pid, $title);
    }
    
    /**
     * @param $pid string - exclude this pid from the search
     * @param $title string - search for this title
     */
    function similarPidsQuery($pid, $title)
    {
        $pidnum = substr($pid, strpos($pid, ':') + 1);
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $stmt = "SELECT distinct r2.rmf_rec_pid as pid, " .
                "  match (r2.rmf_varchar) against ('".$title."') as Relevance " .
                "FROM  ".$dbtp."record_matching_field AS r2 " .
                "INNER JOIN ".$dbtp."xsd_display_matchfields AS x2 " .
                "  ON r2.rmf_xsdmf_id = x2.xsdmf_id " .
                "  AND match (r2.rmf_varchar) against ('".$title."' IN BOOLEAN MODE) " .
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
                "ORDER BY Relevance DESC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        return $res;
    }
    
    function compareRecords($record1, $record2) 
    {
        // Can't compare records of different content models as the XSDMFs will all be different
        if ($record1->getXmlDisplayId() != $record2->getXmlDisplayId()) {
            return 0;
        }
        
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
        $xml = Fedora_API::callGetDatastreamContents($this->pid, 'DuplicatesReport', true);
        return $this->getListingFromXML($page,$page_size, $xml);
    }
     
    function getListingFromXML($page,$page_size, $xml)
    {
        if (is_null($xml) || !is_string($xml) || empty($xml)) {
            return array();
        }
        $report_dom = DOMDocument::loadXML($xml);
        if (empty($report_dom)) {
            return array();
        }
        $xpath = new DOMXPath($report_dom);
        $first_item = $page * $page_size;
        $last_item = $first_item + $page_size;
        $items = $xpath->query('/DuplicatesReport/duplicatesReportItem');
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
        return $listing;
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
        $xml = Fedora_API::callGetDatastreamContents($this->pid, 'DuplicatesReport', true);
        $xml = $this->setDuplicateXML($base_pid, $dup_pid, $xml, true);
        if (!empty($xml)) {
            $this->addReportToFedoraObject($xml);
            // put object in limbo
            $rec = new RecordObject($dup_pid);
            $rec->markAsDeleted();
        } else {
            Error_Handler::logError("Failed to set ".$dup_pid." as duplicate of ".$base_pid
                ." in XML (report pid".$this->pid.")", __FILE__,__LINE__);
        }
    }

    function markNotDuplicate($base_pid, $dup_pid)
    {
        $xml = Fedora_API::callGetDatastreamContents($this->pid, 'DuplicatesReport', true);
        $xml = $this->setDuplicateXML($base_pid, $dup_pid, $xml, false);
        if (!empty($xml)) {
            $this->addReportToFedoraObject($xml);
            // get object back from limbo
            $rec = new RecordObject($dup_pid);
            $rec->markAsActive();
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
    function setDuplicateXML($base_pid, $dup_pid, $xml, $is_duplicate = true)
    {
        if (is_null($xml) || !is_string($xml) || empty($xml)) {
            return null;
        }
        if (empty($base_pid) || !is_string($base_pid)) {
            return null;
        }
        if (empty($dup_pid) || !is_string($dup_pid)) {
            return null;
        }
        $report_dom = DOMDocument::loadXML($xml);
        if (empty($report_dom)) {
            return null;
        }
        $xpath = new DOMXPath($report_dom);
        $items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$base_pid.'\']/duplicateItem[@pid=\''.$dup_pid.'\']');
        if ($items->length > 0) {
            $item = $items->item(0);
            $item->setAttribute('duplicate',$is_duplicate?'true':'false');
        } else {
            // the Pids should exist otherwise it's an error
            return null;
        }
        return $report_dom->saveXML();
    }
    
    function swapBase($base_pid,$dup_pid)
    {
        $xml = Fedora_API::callGetDatastreamContents($this->pid, 'DuplicatesReport', true);
        $xml = $this->swapBaseXML($base_pid,$dup_pid,$xml);
        if (!empty($xml)) {
            $xml = $this->recalcDupScoresXML($dup_pid,$xml);
        } else {
            Error_Handler::logError("Failed to swap dup ".$dup_pid." for base ".$base_pid
                ." in XML (report pid".$this->pid.")", __FILE__,__LINE__);
            return;
        }
        if (!empty($xml)) {
            $this->addReportToFedoraObject($xml);
        } else {
            Error_Handler::logError("Failed to recalc dup scores when trying to swap dup "
                .$dup_pid." for base ".$base_pid
                ." in XML (report pid".$this->pid.")", __FILE__,__LINE__);
        }
    }
    
    function swapBaseXML($base_pid,$dup_pid,$xml)
    {
        if (is_null($xml) || !is_string($xml) || empty($xml)) {
            return null;
        }
        if (empty($base_pid) || !is_string($base_pid)) {
            return null;
        }
        if (empty($dup_pid) || !is_string($dup_pid)) {
            return null;
        }
        $report_dom = DOMDocument::loadXML($xml);
        if (empty($report_dom)) {
            return null;
        }
        $xpath = new DOMXPath($report_dom);
        $items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$base_pid.'\']/duplicateItem[@pid=\''.$dup_pid.'\']');
        if ($items->length < 1) {
            // the Pids should exist otherwise it's an error
            return null;
        }
        $dup_item = $items->item(0);
        $dup_item->setAttribute('pid',$base_pid);
        $items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$base_pid.'\']');
        if ($items->length < 1) {
            // the base Pid should exist otherwise it's an error
            return null;
        }
        $base_item = $items->item(0);
        $base_item->setAttribute('pid',$dup_pid);
        
        return $report_dom->saveXML();
    }
    
    /**
     * Finds a base record and updates the duplicate scores for the dup records.  Called if the base record is swapped.
     */
    function recalcDupScoresXML($base_pid,$xml)
    { 
        if (is_null($xml) || !is_string($xml) || empty($xml)) {
            return null;
        }
        if (empty($base_pid) || !is_string($base_pid)) {
            return null;
        }
        $report_dom = DOMDocument::loadXML($xml);
        if (empty($report_dom)) {
            return null;
        }
        $xpath = new DOMXPath($report_dom);
        $items = $xpath->query('/DuplicatesReport/duplicatesReportItem[@pid=\''.$base_pid.'\']/duplicateItem');
        if ($items->length < 1) {
            // the Pids should exist otherwise it's an error
            return null;
        }
        $base_record = new RecordGeneral($base_pid);
        foreach ($items as $dup_item) {
            $dup_record = new RecordGeneral($dup_item->getAttribute('pid'));
            $dup_item->setAttribute('probability',$this->compareRecords($base_record, $dup_record));
        }
        return $report_dom->saveXML();
    }
    
}



?>
