<?php

/**
 * Fulltext implementation for the Solr search engine.
 * 
 * @author Rhys Palmer <r.palmer@library.uq.edu.au>
 * @version 1.1, February 2008
 *
 */	

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.fulltext_index.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");
include_once(APP_INC_PATH . "Apache/Solr/Service.php");
	
class FulltextIndex_Solr_CSV extends FulltextIndex {
	
	private $solrHost;
	private $solrPort;
	private $solrPath;
	private $docsAdded = 0;
	private $docs;
	private $solr;
	private $csvData;
	private $csvHeader;
	private $createCSV = true;
	
	const FIELD_TYPE_INT = 0;
	const FIELD_TYPE_DATE = 1;
	const FIELD_TYPE_VARCHAR = 2;
	const FIELD_TYPE_TEXT = 3;
	
	/**
     * Processes the queue. Retrieves an item using the pop() function
     * of the queue and calls the index or remove methods.
     *
     */
    public function processQueue() {
        
        global $bench;
        
        $countDocs = 0;
        
        $searchKeys = Search_Key::getList();
		$csvHeader = "";
		
		$authLister_t = $this->getFieldName(FulltextIndex::FIELD_NAME_AUTH, FulltextIndex::FIELD_TYPE_TEXT, false);
		
		/*
         * Custom search key (not a registered search key)
         */
		$citationKey = array(
            'sek_title'         =>  'citation',
            'sek_title_db'      =>  'citation', 
            'sek_data_type'     =>  'text',
            'sek_relationship'  =>  0,
            'sek_simple_used'   =>  1,
        );
        $searchKeys[] = $citationKey;
        
        /*
         * Custom search key (not a registered search key)
         */
        $viewKey = array(
            'sek_title'         =>  'views',
            'sek_title_db'      =>  'views',
            'sek_data_type'     =>  'int',
            'sek_relationship'  =>  0,
            'sek_simple_used'   =>  1,
        );
        $searchKeys[] = $viewKey;
        
        /*
         * Custom search key (not a registered search key)
         */
        $dLKey = array(
            'sek_title'         =>  'file_downloads',
            'sek_title_db'      =>  'file_downloads',
            'sek_data_type'     =>  'int',
            'sek_relationship'  =>  0,
            'sek_simple_used'   =>  1,
        );
        $searchKeys[] = $dLKey;
        
        /*
         * Determine which search keys we are going to be 
         * indexing into solr
         */
		foreach ($searchKeys as $sekDetails) {
		    
		    /*
		     * Only index search keys which are searchable on fez
		     */
            if(  $sekDetails["sek_simple_used"] == 1 || 
	             $sekDetails["sek_myfez_visible"] == 1 || 
	             $sekDetails["sek_adv_visible"] == 1)
            {
                $fieldType = $this->mapType($sekDetails['sek_data_type']);
                
                if ($sekDetails["sek_relationship"] > 0) {
                
                    $mtColumns[] = $sekDetails["sek_title_db"];
                    $mtColumnsHeader[] = $this->getFieldName($sekDetails["sek_title_db"],$fieldType, true);
                
                } else {
                
                    $singleColumns[] = array(
                        'name'  => "rek_".$sekDetails["sek_title_db"],
                        'type'  =>  $fieldType,
                    );
                    $singleColumnsHeader[] = $this->getFieldName($sekDetails["sek_title_db"],$fieldType, false);
                }
            }
	             
		}
        
        
        $queue = FulltextQueue::singleton();
        $this->totalDocs = $queue->size();
        
        if( $this->bgp ) {
            $this->bgpDetails = $this->bgp->getDetails();
        }
        
        /*
         * Loop through queue and index 500 records at a time into solr
         */
    	while( ($chunk = $queue->popChunk($singleColumns)) != false ) {
    	    
    	    $csv       = array();
    	    $pids_arr  = array();
    	    $pids      = '';
    	    $spliting  = '';
    	    
    		$csvHeader = 'id,'.implode(',', $singleColumnsHeader) . ',' . $authLister_t . ','. implode(',', $mtColumnsHeader) . ",content\n";
			
			foreach ( $chunk as $row ) {
			    
			    $csv[$row['rek_pid']] = '"'.$row['rek_pid'] .'","'.$row['row'] .  '"';
			    $pids_arr[] = $row['rek_pid'];
			    
			}
			
			$pids = '"'.implode('","', $pids_arr).'"';
			
			/*
			 * Rebuild any empty citations so
			 * they are cached in solr
			 */
			$this->preBuildCitations($pids);
			
			/*
			 * Cache any datastreams that have text
			 * files
			 */
			$this->preCacheDatastreams($pids);
			
			/*
			 * Add the authlister rules to the csv array
			 */
			$rulesGroups = $this->getListerRuleGroupsChunk($pids);
			foreach ($csv as $rek_pid => $rek_line) {
			        
                if( !empty($rulesGroups[$rek_pid]) ) {
                    $csv[$rek_pid] .= ',"' . $rulesGroups[$rek_pid] .'"';
                } else {
                    $csv[$rek_pid] .= ',""';
                }
                    
		    }
			
		    /*
		     * Add multi-valued search keys to the csv array
		     */
			foreach ( $mtColumns as $mtColumn ) {
			    
			    $sql = "    SELECT rek_{$mtColumn}_pid as pid, GROUP_CONCAT(rek_{$mtColumn} SEPARATOR \"\t\") as value
                            FROM ".APP_TABLE_PREFIX."record_search_key_{$mtColumn} 
                            WHERE rek_{$mtColumn}_pid IN (". $pids . ")
                            GROUP BY rek_{$mtColumn}_pid";
			    
			    $resultSeks = $GLOBALS['db_api']->dbh->getAll($sql, DB_FETCHMODE_ASSOC);
			    
			    foreach ($resultSeks as $resultSek) {
                    $tmpArr[$resultSek['pid']] = $resultSek['value'];
			    }
			    
			    foreach ($csv as $rek_pid => $rek_line) {
			        
                    if( !empty($tmpArr[$rek_pid]) ) {
                        $val = str_replace('"', '""', $tmpArr[$rek_pid]);
                        $csv[$rek_pid] .= ',"' . $val .'"';
                    } else {
                        $csv[$rek_pid] .= ',""';
                    }
                    
                    $val = '';
			    }
			    
			    $tmpArr = array();
			}
			
			/*
			 * Add datasteam text to CSV array
			 */
			$content = $this->getCachedContent($pids);
			
			foreach ($csv as $rek_pid => $rek_line) {
			        
                if( !empty($content[$rek_pid]) ) {
                    $csv[$rek_pid] .= ',"' . $content[$rek_pid] .'"';
                } else {
                    $csv[$rek_pid] .= ',""';
                }
                    
		    }
		    
			$csv = implode("\n", $csv);
		    $tmpfname = tempnam(APP_PATH."solr_upload", "solrCsv");

            $handle = fopen($tmpfname, "w");
            fwrite($handle, $csvHeader);
            fwrite($handle, $csv);
            fclose($handle);
            
            // This is so solr has permissions to read the file
            chmod($tmpfname, 0755);
            
            foreach ($mtColumnsHeader as $mtHeader) {
                $spliting .= "&f.$mtHeader.split=true&f.$mtHeader.separator=%09";
            }
            
            $spliting .= "&f.content.split=true&f.content.separator=%09";
            
            Logger::debug("processQueue: about to send");
            if (APP_SOLR_HOST == APP_HOSTNAME) {
              $url = "http://".APP_SOLR_HOST.":".APP_SOLR_PORT.APP_SOLR_PATH."update/csv?commit=true&stream.file=".$tmpfname.$spliting;
            } else {
              $url_loc = "http://".APP_HOSTNAME.APP_RELATIVE_URL."solr_upload/".substr($tmpfname, (strrpos($tmpfname, "/")+1));
              echo $url_loc."\n";
              $url = "http://".APP_SOLR_HOST.":".APP_SOLR_PORT.APP_SOLR_PATH."update/csv?commit=true&stream.url=".$url_loc.$spliting;
            }
            //$url = "http://localhost:8080/solr/update/csv?commit=true&stream.file=".$tmpfname.$spliting;
		    
            if( $this->bgp ) {
                $this->bgp->setStatus("Sending CSV file to Solr");
			}
			
			Logger::debug($url);
            
            /*
             * Use cURL to tell solr it has a CSV file to process
             */
            $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
    
            $data = curl_exec ($ch);
            if ($data) {
                $info = curl_getinfo($ch);
                curl_close ($ch);
            } else {
                $info = array();
                Error_Handler::logError(curl_error($ch)." ".$url,__FILE__,__LINE__);
                curl_close ($ch);
            }
            
            unlink($tmpfname);
            
            if( $info['http_code'] != 200 ) {
                Error_Handler::logError($data,__FILE__,__LINE__);
            }
    		    		
    		//$this->postprocessIndex($ftq_pid, $ftq_op);
            //Logger::debug("processQueue: finished indexing mem_used=".memory_get_usage());
            
            $countDocs += 500;
            
            if( $this->bgp ) {
                $this->bgp->setStatus("Finished Solr fulltext indexing for (".$countDocs."/".$this->totalDocs." Added)");
			}
    		
    	}
    	
    	return $countDocs;
    }
	
    
    /**
     * Delete this pid in solr index.
     *
     * @param unknown_type $pid
     */
    protected function removeByPid($pid)
    {
    	// call parent cleanup
    	parent::removeByPid($pid);

    	Logger::debug("removeByPid($pid) -> call apache solr with deleteById($pid)");
    	$this->solr = $this->getSolr();
    	$this->solr->deleteById($pid);
    	$this->solr->commit();

    }
    
    
	public function getFieldName($fezName, $datatype=FulltextIndex::FIELD_TYPE_TEXT, 
		$multiple=false) {
			
    	$fezName .= '_';
    	if ($multiple) {
    		$fezName .= 'm';
    	}
    	
    	switch ($datatype) {
    		case FulltextIndex::FIELD_TYPE_TEXT: $fezName .= 't'; break;
    		case FulltextIndex::FIELD_TYPE_DATE: $fezName .= 'dt'; break;
    		case FulltextIndex::FIELD_TYPE_INT: $fezName .= 'i'; break;
    		case FulltextIndex::FIELD_TYPE_VARCHAR : $fezName .= 't'; break;
    		default:
    			$fezName .= 't';
    	}
	   
	   return $fezName;
    }

   
     private function getListerRuleGroupsChunk($pids) {

		$stmt =  'SELECT authi_pid, GROUP_CONCAT(authi_arg_id) as value
                  FROM '.APP_TABLE_PREFIX.'auth_index2_lister 
		          WHERE authi_pid IN ('.$pids.') 
		          GROUP BY authi_pid';
		
		$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);

		if (PEAR::isError($res)) {
	        Logger::error($res->getMessage());
	        return "";
	    }

		return $res;
    }
    
    
    private function preBuildCitations($pids) {
        
        $sql = "SELECT rek_pid 
                FROM ".APP_TABLE_PREFIX."record_search_key
                WHERE rek_pid IN (" . $pids . ") AND
                      rek_citation IS NULL OR rek_citation = ''";
        
        $res = $GLOBALS['db_api']->dbh->getAll($sql, DB_FETCHMODE_ASSOC);
        
		if (PEAR::isError($res)) {
	        Logger::error($res->getMessage());
	        return "";
	    }
	    
	    $rebuildCount = count($res);
        $rCounter = 0; 
	    foreach ($res as $pidData) {
            $rCounter++;
            Logger::debug("processQueue: about to pre build citation ".$pidData['rek_pid']." (".$rCounter."/".$rebuildCount.")");
	        
            Citation::updateCitationCache($pidData['rek_pid']);
	    }
    }
    
    private function preCacheDatastreams($pids) {
        
        if( $this->bgp ) {
            $this->bgp->setStatus("Caching datastreams");
        }
        
        $sql = "SELECT rek_file_attachment_name_pid as rek_pid
                FROM ".APP_TABLE_PREFIX."record_search_key_file_attachment_name 
                WHERE rek_file_attachment_name_pid IN (" . $pids . ") AND
                      rek_file_attachment_name LIKE '%.pdf'";
        
        $res = $GLOBALS['db_api']->dbh->getAll($sql, DB_FETCHMODE_ASSOC);
        
        foreach ($res as $pidData) {
            $record = new RecordGeneral($pidData['rek_pid']);
            $dslist = $record->getDatastreams();
            
            if( count($dslist) == 0 ) {
                if( $this->bgp ) {
                    $this->bgp->setStatus($pidData['rek_pid'] . " has no datastreams but it should");
                }
                continue;
            }
            
            foreach ($dslist as $dsitem) {
                $this->indexDS($record, $dsitem);
            }
        }
        
        if( $this->bgp ) {
            $this->bgp->setStatus("Finished Caching datastreams");
        }
    }
    
    /**
     * Retrieves the cached plaintext for a (pid,datastream) pair from the
     * fulltext cache.
     *
     * @param string $pid
     * @param string $dsID
     * @return plaintext of datastream, null on error
     */
    public function getCachedContent($pids) {
    	
        // Remove newlines, page breaks and replace " with "" (which is how to escape for CSV files)
    	$stmt = 'SELECT ftc_pid as pid, REPLACE(REPLACE(REPLACE(ftc_content, \'"\',\'""\'), "\n", ""), "\f", "") as content '.        		
        		'FROM '.APP_TABLE_PREFIX.FulltextIndex::FULLTEXT_TABLE_NAME.
        		' WHERE ftc_pid IN ('.$pids.') AND ftc_is_text_usable = 1';
        				      	
        $res = $GLOBALS['db_api']->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);		
        if (PEAR::isError($res)) {
	        Logger::error($res->getMessage());	        
	        $res = null;
	    }
	    
	    foreach ($res as $row) {
	        
	        if( !empty($ret[$row['pid']]) ) {
                $ret[$row['pid']] .= "\t". $row['content'];
	        } else {
	            $ret[$row['pid']] = $row['content'];
	        }
	    }
	    
        return $ret;
    }
    
    protected function executeQuery($query, $options, $approved_roles, $sort_by, $start, $page_rows) {
        
    }
    
    protected function updateFulltextIndex($pid, $fields, $fieldTypes) {
    	
    }
}


?>
