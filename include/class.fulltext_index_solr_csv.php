<?php

/**
 * Fulltext implementation for the Solr search engine.
 *
 * @author Rhys Palmer <r.palmer@library.uq.edu.au>
 * @version 1.1, February 2008
 *
 */

include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.fulltext_index.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");
include_once(APP_INC_PATH . "class.custom_view.php");
include_once(APP_INC_PATH . "Apache/Solr/Service.php");

class FulltextIndex_Solr_CSV extends FulltextIndex 
{

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
	public function processQueue() 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$countDocs = 0;

		$searchKeys = Search_Key::getList();
		$csvHeader = "";

		$authLister_t = $this->getFieldName(FulltextIndex::FIELD_NAME_AUTHLISTER, FulltextIndex::FIELD_TYPE_TEXT, false);
		$authCreator_t = $this->getFieldName(FulltextIndex::FIELD_NAME_AUTHCREATOR, FulltextIndex::FIELD_TYPE_TEXT, false);
		$authEditor_t = $this->getFieldName(FulltextIndex::FIELD_NAME_AUTHEDITOR, FulltextIndex::FIELD_TYPE_TEXT, false);

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
		// $viewKey = array(
		//             'sek_title'         =>  'views',
		//             'sek_title_db'      =>  'views',
		//             'sek_data_type'     =>  'int',
		//             'sek_relationship'  =>  0,
		//             'sek_simple_used'   =>  1,
		// );
		// $searchKeys[] = $viewKey;

		/*
		 * Custom search key (not a registered search key)
		 */
		// $dLKey = array(
		//             'sek_title'         =>  'file_downloads',
		//             'sek_title_db'      =>  'file_downloads',
		//             'sek_data_type'     =>  'int',
		//             'sek_relationship'  =>  0,
		//             'sek_simple_used'   =>  1,
		// );
		// $searchKeys[] = $dLKey;

		$roles = array(
            'Lister',
            'Creator',
            'Editor',
		);
		$roleIDs = Auth::convertTextRolesToIDS($roles);
		$roleIDs = array_flip($roleIDs);

		/*
		 * Determine which search keys we are going to be
		 * indexing into solr
		 */
		foreach ($searchKeys as $sekDetails) {

			/*
			 * Only index search keys which are searchable on fez
			 */
			// if(  $sekDetails["sek_simple_used"] == 1 ||
			// $sekDetails["sek_myfez_visible"] == 1 ||
			// $sekDetails["sek_adv_visible"] == 1 || (Custom_View::searchKeyUsedCview($sekDetails["sek_id"]) == 1))
			// {
				$fieldType = $this->mapType($sekDetails['sek_data_type']);
				$isMultiple = $sekDetails['sek_cardinality'] != 0;

				if ($sekDetails["sek_relationship"] > 0) {

					$mtColumns[] = array(
                        'name'  => $sekDetails["sek_title_db"],
                        'type'  =>  $fieldType,
						'cardinality' => $sekDetails['sek_cardinality']
						);
						//$sekDetails["sek_title_db"];
					$mtColumnsHeader[] = $this->getFieldName($sekDetails["sek_title_db"],$fieldType, $isMultiple);
					// Add year and decade to solr if its a date field so they can be faceted on
					if ($fieldType == FulltextIndex::FIELD_TYPE_DATE) {
						$mtColumnsHeader[] = $sekDetails["sek_title_db"]."_year_t";
						
						// $mtYearColumns[] = $sekDetails["sek_title_db"];
						// $mtYearColumnsHeader[] = $sekDetails["sek_title_db"]."_year_t";
					}
					
					// append an exact string for author/contributor
					if ($sekDetails['sek_title_db'] == 'author' || $sekDetails['sek_title_db'] == 'contributor') {
						$mtColumnsHeader[] = "{$sekDetails['sek_title_db']}_mt_exact";
					}

				} else {

					$singleColumns[] = array(
                        'name'  => "rek_".$sekDetails["sek_title_db"],
                        'type'  =>  $fieldType,
					);
					$singleColumnsHeader[] = $this->getFieldName($sekDetails["sek_title_db"],$fieldType, $isMultiple);
					if ($fieldType == FulltextIndex::FIELD_TYPE_DATE) {
						$singleColumnsHeader[] = $sekDetails["sek_title_db"]."_year_t";
						
						// $singleYearColumns[] = $sekDetails["sek_title_db"];
						// $singleYearColumnsHeader[] = $sekDetails["sek_title_db"]."_year_t";
					}
					
				}
//			}

		}
		
		$queue = FulltextQueue::singleton();
		$this->totalDocs = $queue->size();

		if( $this->bgp ) {
			$this->bgpDetails = $this->bgp->getDetails();
		}

		/*
		 * Loop through queue and index a number of records set in APP_SOLR_COMMIT_LIMIT config var at a time into solr
		 */
		while( ($chunk = $queue->popChunk($singleColumns)) != false ) {
				
			$csv       = array();
			$pids_arr  = array();
			$pids      = '';
			$spliting  = '';

			$csvHeader = 'id,'.implode(',', $singleColumnsHeader) . ',' . $authLister_t . ','. $authCreator_t . ',' . $authEditor_t . ','.implode(',', $mtColumnsHeader) . ",content\n";
				
			foreach ( $chunk as $row ) {
				 
				if(empty($row['rek_pid']))
				continue;
				 
				//$csv[$row['rek_pid']] = '"'.$row['rek_pid'] .'",'. preg_replace('/[^(\x20-\x7F)]*/','', $row['row']).  '"';
				$csv[$row['rek_pid']] = '"'.$row['rek_pid'] .'",'. $row['row'].  '"';   //20110527 preg-replace removed
				// $csv[$row['rek_pid']] = '"'.$row['rek_pid'] .'","'.$row['row'] .  '"';
				$pids_arr[] = $row['rek_pid'];
				 
			}
				
			$pids = "'".implode("','", $pids_arr)."'";			
				
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
			$rulesGroups = $this->getRuleGroupsChunk($pids, $roleIDs);
			foreach ($csv as $rek_pid => $rek_line) {
				 
				$rules = $rulesGroups[$rek_pid];
				if( !empty($rules) ) {
					 
					$lister_rules = '""';
					$creator_rules = '""';
					$editor_rules = '""';
					 
					if (!empty($rules[$roleIDs['Lister']])) {
						$lister_rules = $rules[$roleIDs['Lister']];
					}
					if (!empty($rules[$roleIDs['Creator']])) {
						$creator_rules = $rules[$roleIDs['Creator']];
					}
					if (!empty($rules[$roleIDs['Editor']])) {
						$editor_rules = $rules[$roleIDs['Editor']];
					}
				
					$csv[$rek_pid] .= ',' . $lister_rules .','.$creator_rules. ','. $editor_rules;
					// $csv[$rek_pid] .= ',"' . $lister_rules .'","'.$creator_rules. '","'. $editor_rules;
				} else {
					$csv[$rek_pid] .= ',"","",""';
				}

			}
				
			/*
			 * Add multi-valued search keys to the csv array
			 */
			foreach ( $mtColumns as $mtColumn ) {
			
				$col_name = "";
				if ($mtColumn['type'] == FulltextIndex::FIELD_TYPE_DATE ) {
				  if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { 
						$col_name = "(DATE_FORMAT(a1.rek_".$mtColumn["name"] .",'%Y-%m-%dT%H:%i:%sZ'))";
					} else {
						$col_name = "(DATE_FORMAT(a2.rek_".$mtColumn["name"] .",'%Y-%m-%dT%H:%i:%sZ'))";
					}
				} else {
				  if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { 
						$col_name = "a1.rek_".$mtColumn["name"];
					} else {
						$col_name = "a2.rek_".$mtColumn["name"];
					}
				}

				$orderByClause = '';
				if ($mtColumn['cardinality'] == 1) {
			  	if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { 
						$orderByClause = "ORDER BY a1.rek_{$mtColumn['name']}_order ASC";
					} else {
						$orderByClause = "ORDER BY a2.rek_{$mtColumn['name']}_order ASC";
					}
          $limitExtra = "";
				} else {
          $limitExtra = " LIMIT 1 OFFSET 0 "; //to catch any data inconsistences not caught earlier or by DDL unique keys
        }
				
				$stmt = "    SELECT a2.rek_".$mtColumn["name"]."_pid as pid, ";
				
						  if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { 
								$stmt .= " array_to_string(array(SELECT ".$col_name." FROM ".APP_TABLE_PREFIX."record_search_key_".$mtColumn["name"]." a1 WHERE a1.rek_".$mtColumn["name"]."_pid = a2.rek_".$mtColumn["name"]."_pid {$orderByClause}), '"."\t"."') AS value ";
							} else {
								$stmt .= " GROUP_CONCAT(".$col_name." {$orderByClause} SEPARATOR \"\t\") as value ";
							}					

							$stmt .= "
														
                            FROM ".APP_TABLE_PREFIX."record_search_key_".$mtColumn["name"]." a2 
                            WHERE a2.rek_".$mtColumn["name"]."_pid IN (". $pids . ")
                            GROUP BY a2.rek_".$mtColumn["name"]."_pid".$limitExtra;
				
				try {
					$resultSeks = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
				}
				catch(Exception $ex) {
					$log->err($ex);
				}			
				foreach ($resultSeks as $resultSek) {
          if ($resultSek['value'] != '-1') {
					  $tmpArr[$resultSek['pid']] = $resultSek['value'];
          }
				}
				 
				foreach ($csv as $rek_pid => $rek_line) {
			   
					if( !empty($tmpArr[$rek_pid]) ) {
						$val = str_replace('"', '""', $tmpArr[$rek_pid]);
						$csv[$rek_pid] .= ',"' . $val.'"';
					} else {
						$csv[$rek_pid] .= ',""';
					}
					$val = '';
				}

				// add an additional field for both author and contributor
				if ($mtColumn['name'] == 'author' || $mtColumn['name'] == 'contributor') {
					foreach ($csv as $rek_pid => $rek_line) {

						if( !empty($tmpArr[$rek_pid]) ) {
							$val = str_replace('"', '""', $tmpArr[$rek_pid]);
							$csv[$rek_pid] .= ',"' . $val .'"';
						} else {
							$csv[$rek_pid] .= ',""';
						}
						$val = '';
					}
				}

				$tmpArr = array();

				if ($mtColumn['type'] == FulltextIndex::FIELD_TYPE_DATE ) {
					$tmpArr = array();
				  if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { 
						$col_name = "(DATE_FORMAT(a1.rek_".$mtColumn["name"] .",'%Y'))";
					} else {
						$col_name = "(DATE_FORMAT(a2.rek_".$mtColumn["name"] .",'%Y'))";
					}

					$orderByClause = '';
					if ($mtColumn['cardinality'] == 1) {
						if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { 
							$orderByClause = "ORDER BY a1.rek_{$mtColumn['name']}_order ASC";
						} else {
							$orderByClause = "ORDER BY a2.rek_{$mtColumn['name']}_order ASC";
						}
            $limitExtra = "";
          } else {
            $limitExtra = " LIMIT 1 OFFSET 0 "; //to catch any data inconsistences not caught earlier or by DDL unique keys
          }


					$stmt = "    SELECT a2.rek_".$mtColumn["name"]."_pid as pid, ";

				  if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) { 
						$stmt .= " array_to_string(array(SELECT ".$col_name." FROM ".APP_TABLE_PREFIX."record_search_key_".$mtColumn["name"]." a1 WHERE a1.rek_".$mtColumn["name"]."_pid = a2.rek_".$mtColumn["name"]."_pid {$orderByClause}), '"."\t"."') AS value ";
					} else {
						$stmt .= " GROUP_CONCAT(".$col_name." {$orderByClause} SEPARATOR \"\t\") as value ";
					}					
					
					$stmt .= "          FROM ".APP_TABLE_PREFIX."record_search_key_".$mtColumn["name"]." a2
	                            WHERE a2.rek_".$mtColumn["name"]."_pid IN (". $pids . ")
	                            GROUP BY a2.rek_".$mtColumn["name"]."_pid".$limitExtra;

					try {
						$resultSeks = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
					}
					catch(Exception $ex) {
						$log->err($ex);
					}			
					foreach ($resultSeks as $resultSek) {
            if ($resultSek['value'] != '-1') {
						  $tmpArr[$resultSek['pid']] = $resultSek['value'];
            }
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
				}

				$tmpArr = array();
				
			}
			
			/*
			 * Add datasteam text to CSV array
			 */
			$content = $this->getCachedContent($pids);
			$content = preg_replace('/[^(\x20-\x7F)]*/','',$content);
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
			$postFields = array();
			foreach ($mtColumnsHeader as $mtHeader) {
				$postFields["f.".$mtHeader.".split"] = "true";
				$postFields["f.".$mtHeader.".separator"] = html_entity_decode("&#09;");
				// old get method
				//$spliting .= "&f.$mtHeader.split=true&f.$mtHeader.separator=%09";
			}
			$postFields["f.content.split"] = "true";
			$postFields["f.content.separator"] = html_entity_decode("&#09;");
			//old get method
//			$spliting .= "&f.content.split=true&f.content.separator=%09";

			$log->debug(array("processQueue: about to send"));
			$postFields["commit"] = "false";
			$url = "http://".APP_SOLR_HOST.":".APP_SOLR_PORT.APP_SOLR_PATH."update/csv";
			
			if (APP_SOLR_HOST == APP_HOSTNAME) {
				$postFields["stream.file"] = $tmpfname;
				//$url = "http://".APP_SOLR_HOST.":".APP_SOLR_PORT.APP_SOLR_PATH."update/csv";
				//old get method
				//$url = "http://".APP_SOLR_HOST.":".APP_SOLR_PORT.APP_SOLR_PATH."update/csv?commit=true&stream.file=".$tmpfname.$spliting;
			} else {
				$url_loc = "http://".APP_HOSTNAME.APP_RELATIVE_URL."solr_upload/".substr($tmpfname, (strrpos($tmpfname, "/")+1));
//				echo $url_loc."\n";
				//old get method
				//$url_of_stream = "http://".APP_SOLR_HOST.":".APP_SOLR_PORT.APP_SOLR_PATH."update/csv?commit=true&stream.url=".$url_loc.$spliting;
				$postFields["stream.url"] = $url_loc;
			}
			//$url = "http://localhost:8080/solr/update/csv?commit=true&stream.file=".$tmpfname.$spliting;

			if( $this->bgp ) {
				$this->bgp->setStatus("Sending CSV file ".$tmpfname." to Solr");
			}
				
//			$log->debug(array($url));

			
			/*
			 * Use cURL to tell solr it has a CSV file to process
			 */
			$raw_response = Misc::processURL($url, null, null, $postFields, null, 30);
			$uploaded = false;
			if($raw_response[1]["http_code"] != "200") {
				// Caught solr napping.. try again 1 more time	
				$log->err('No response from solr.. trying again: '.print_r($raw_response, true));			
				unset($raw_response);
				sleep(1);
				$raw_response = Misc::processURL($url, null, null, $postFields, null, 30);
				//if(! $raw_response[0]) {
				if($raw_response[1]["http_code"] != "200") {
					$info = array();
					$log->err('No response from solr.. after the second attempt: '.print_r($raw_response, true));			
					$log->debug(array($url));
				}
				else {
					$uploaded = true;
				}
			}	
			else {
				$uploaded = true;
			}
			
			// Dont delete csv if there is an error
			if($uploaded == true) {
				unlink($tmpfname);
			}
		

			//$this->postprocessIndex($ftq_pid, $ftq_op);
			//Logger::debug("processQueue: finished indexing mem_used=".memory_get_usage());

			$countDocs += APP_SOLR_COMMIT_LIMIT;
			if ($countDocs > $this->totalDocs) {
				$countDocs = $this->totalDocs;
			}

			if( $this->bgp ) {
				$this->bgp->setStatus("Finished Solr fulltext indexing for (".$countDocs."/".$this->totalDocs." Added)");
				$this->bgp->setProgress($countDocs);
				
				foreach ($pids_arr as $finishedPid) {
					$this->bgp->markPidAsFinished($finishedPid);
				}
				
			}

		}
		 
		if( $this->bgp ) {
			$this->bgp->setStatus("Processing any PIDS to delete from solr");
		}
		 
		$deletePids = $queue->popDeleteChunk();
        
		$this->connectToSolr();
		if( $deletePids ) {
				

				
			if( $this->bgp ) {
				$this->bgp->setStatus("Deleting " . count($deletePids) . " from Solr Index");
			}
			$this->solr->deleteByMultipleIds($deletePids);

			
			// MT: 20100319 commented out this as the function doesn't exist in the Solr Service class. 
			// $this->solr->triggerUpdate();
			
/*			foreach ( $deletePids as $row) {
				$this->removeByPid($pid);
				//$this->removeByPid($row['ftq_pid']);
			} */
		} 
		$this->solr->commit();
		return $countDocs;
	}


	/**
	 * Delete this pid in solr index.
	 *
	 * @param unknown_type $pid
	 */
	protected function removeByPid($pid)
	{
		$log = FezLog::get();
		// call parent cleanup
		parent::removeByPid($pid);

		$log->debug(array("removeByPid($pid) -> call apache solr with deleteById($pid)"));
		$this->solr->deleteById($pid);
		$this->solr->commit();

	}


	public function getFieldName($fezName, $datatype=FulltextIndex::FIELD_TYPE_TEXT,
	$multiple=false) 
	{			
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

	private function getRuleGroupsChunk($pids, $roles) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt =  'SELECT authi_pid, authi_role, authi_arg_id
                  FROM '.APP_TABLE_PREFIX.'auth_index2 
                  WHERE authi_pid IN ('.$pids.') 
                        AND authi_role IN ('.implode(',',$roles).')';

		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		$ret = array();
		foreach ($res as $row) {
			$ret[$row['authi_pid']][$row['authi_role']] = $row['authi_arg_id'];
		}

		return $ret;
	}

	private function preBuildCitations($pids) 
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$stmt = "SELECT rek_pid
                FROM ".APP_TABLE_PREFIX."record_search_key
                WHERE rek_pid IN (" . $pids . ") AND
                      (rek_citation IS NULL OR rek_citation = '')";
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
	  
		$rebuildCount = count($res);
		$rCounter = 0;
		foreach ($res as $pidData) {
			$rCounter++;
			$log->debug(array("processQueue: about to pre build citation ".$pidData['rek_pid']." (".$rCounter."/".$rebuildCount.")"));
			 
			Citation::updateCitationCache($pidData['rek_pid']);
		}
	}

	private function preCacheDatastreams($pids) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if( $this->bgp ) {
			$this->bgp->setStatus("Caching datastreams");
		}

		$stmt = "SELECT rek_file_attachment_name_pid as rek_pid
                FROM ".APP_TABLE_PREFIX."record_search_key_file_attachment_name 
                LEFT JOIN ".APP_TABLE_PREFIX."fulltext_cache ON rek_file_attachment_name_pid = ftc_pid AND rek_file_attachment_name = ftc_dsid
                WHERE ftc_dsid IS NULL AND rek_file_attachment_name_pid IN (" . $pids . ") AND
                      rek_file_attachment_name LIKE '%.pdf'";
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);		
		}

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
	public function getCachedContent($pids) 
	{
		$log = FezLog::get();
		$db = DB_API::get();
		$pids = str_replace('"', "'", $pids);
		// Remove newlines, page breaks and replace " with "" (which is how to escape for CSV files)
		$stmt = "SELECT ftc_pid as pid, REPLACE(REPLACE(REPLACE(ftc_content, '\"','\"\"'), '\n', ' '), '\t', ' ') as content ".
        		'FROM '.APP_TABLE_PREFIX.FulltextIndex::FULLTEXT_TABLE_NAME.
        		' WHERE ftc_pid IN ('.$pids.') AND ftc_is_text_usable = TRUE';
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);	
			$res = null;	
		}
		$ret = array();
		foreach ($res as $row) {
			 
			if( !empty($ret[$row['pid']]) ) {
				$ret[$row['pid']] .= "\t". $row['content'];
			} else {
				$ret[$row['pid']] = $row['content'];
			}
		}
	  
		return $ret;
	}

	protected function executeQuery($query, $options, $approved_roles, $sort_by, $start, $page_rows) 
	{

	}

	protected function updateFulltextIndex($pid, $fields, $fieldTypes) 
	{
		 
	}

	private function connectToSolr() 
	{
		$this->solrHost = APP_SOLR_HOST;
		$this->solrPort = APP_SOLR_PORT;
		$this->solrPath = APP_SOLR_PATH;
	  
		$this->solr = new Apache_Solr_Service($this->solrHost, $this->solrPort, $this->solrPath);
	}
}
