<?php

/**
 * Fulltext implementation for the Solr search engine.
 * 
 * @author Kai Jauslin <kai.jauslin@library.ethz.ch>
 * @version 1.1, February 2008
 *
 */	

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.fulltext_index.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");
include_once(APP_INC_PATH . "Apache/Solr/Service.php");
	
class FulltextIndex_Solr extends FulltextIndex {
	
	private $solrHost;
	private $solrPort;
	private $solrPath;
	private $docsAdded = 0;
	private $docs;
	private $solr;
	
	function __construct() {
	    $this->solrHost = APP_SOLR_HOST;
	    $this->solrPort = APP_SOLR_PORT;
	    $this->solrPath = APP_SOLR_PATH;
	    
	    $this->solr = new Apache_Solr_Service($this->solrHost, $this->solrPort, $this->solrPath);
	}
	
	/**
	 * Returns an instance of the php solr service class.
	 *
	 * @return Apache_Solr_Service
	 */
	private function getSolr() {		
		$solr = &new Apache_Solr_Service($this->solrHost, $this->solrPort, $this->solrPath);
		return $solr;
	}	
	
	/**
     * Updates the Solr fulltext index with a new or existing document. 
     *
     * @param string $pid
     * @param array $fields
     */
    protected function updateFulltextIndex($pid, $fields, $fieldTypes) {
    	try {
	    	//$solr = $this->getSolr();
	    	//Logger::debug("Solr Ping = ".$solr->ping());

	    	//Logger::debug("FulltextIndex::updateFulltextIndex start mem_usage=".memory_get_usage());
	    	
	    	$doc = new Apache_Solr_Document();
	    	
	    	// set solr id to object pid of
	    	$doc->id = $pid;

		    foreach ($fields as $key => $value) {
		    	//$fieldName = $this->getFieldName($key, $fieldTypes[$key]);

		    	if (is_array($value) && $fieldTypes) {
		    		foreach ($value as $v) {
		    			//Logger::debug($key."=".$v);
		    			// too much utf8_encode for fields already encoded...
		    			if($v != "") {
	    			       $doc->setMultiValue($key, $v); // TODO: utf8_encode needed??
		    			}
		    		}
		    	} else {
		    		if (!empty($value)) {
    	    			$doc->$key = $value;
		    		}
		    	}
		    }

		    $this->docs[] = $doc;
		    //$solr->addDocument($doc);
		    $this->docsAdded++;
		    
		    if( $this->docsAdded % 250 == 0 ) {
                $this->solr->addDocuments($this->docs);
                $this->solr->commit();
                
                unset($this->docs);
                Logger::debug("======= FulltextIndex::updateFulltextIndex committed mem_usage=".memory_get_usage(). " =======");
		    }

    	} catch (Exception $e) {

    		// catches communication errors etc.
    		//
    		Logger::error("Could not add document $pid to Solr index. Adding $pid to end of queue.");
    		Logger::error("Exception message was: ".$e->getMessage());

    		//FulltextQueue::singleton()->add($pid);
    		Logger::debug("$pid added to queue (again).");

    	}
    	
    	//Logger::debug("FulltextIndex::updateFulltextIndex finished mem_usage=".memory_get_usage());
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
    	$this->solr->deleteById($pid);
    	$this->solr->commit();

    }

    protected function prepareQuery($params, $options, $rulegroups, $approved_roles, $sort_by, $start, $page_rows) {    	    	
    	$query = '';
    	$i = 0;
    	if ($params['words']) {
	    	foreach ($params['words'] as $key=>$value) {
	    		if ($value['wf'] != 'ALL') {    			
	    			$sek_details = Search_Key::getBasicDetailsByTitle($value['wf']);
	    			
	    			if ($sek_details['sek_relationship'] > 0) {
	    				$isMulti = true;
	    			} else {
	    				$isMulti = false;
	    			}
	    			$wf = $this->getFieldName($value['wf'], self::FIELD_TYPE_TEXT, $isMulti);
	    			$query .= $wf.":(";
	    		} else {
	    			$query .= '(';
	    		}
	    		$query .= $value['w']; // need to do some escaping here?
	    		$query .= ')';
	    		
				$i++;    	
				if ($i < count($params['words'])) {
					$query .= ' '.$value['op'].' ';
				}
	    	}
    	}
    	
    	if ($params['direct']) {
	    	foreach ($params['direct'] as $key=>$value) {
	    		if (strlen(trim($query)) > 0) {
	    			$query .= ' AND ';
	    		}
	    		$query .= '('.$value.')';
	    	}
    	}
    	
		$queryString = $query;
		$filterQuery = "(_authlister_t:(" .$rulegroups . ")) AND (status_i:2)";
		
		//var_dump($query);
//		echo $queryString;
        return array(
		  'query' => $queryString, 
          'filter'=> $filterQuery
        );	
    }

    public function suggestQuery($query, $approved_roles, $start, $page_rows) {
		try {
	    	$queryString = "title_t:".$query['query'].'';
			$params['fl'] = 'title_t,author_mt';
			$params['fl'] = 'title_t,author_mt';
			echo "qs = ".$queryString;
		
			$response = $this->solr->search($queryString, $start, $page_rows, $params);
			$total_rows = $response->response->numFound;
			
			$docs = array();
			$snips = array();
			if ($total_rows > 0) {
				$i = 0;
				foreach ($response->response->docs as $doc) {
					// resolve result
					$docs[$doc->title_t] = 33;
				}       
	         }
		
		
		

		} catch (Exception $e) {

			//
			// catches any Solr service exceptions (malformed syntax etc.)
			//

			// TODO add fine grained control, user message error handling
			Logger::error("Error on searching: ".$e->getMessage());

			// report nothing found on error
			$docs = array();
			$total_rows = 0;	

    	}

    	return array(
    	   'total_rows' => $total_rows, 
    	   'docs'       => $docs, 
    	   'snips'      => $snips
	   );
	}



    protected function prepareAdvancedQuery($searchKey_join, $filter_join, $roles) {

        $filterQuery = "";
        $searchQuery = "";
        
        if ($searchKey_join[2] == "") {
            $searchQuery = "*:*";
        } else {
            $searchQuery = $searchKey_join[2];
        }
        $approved_roles = array();
        if (!Auth::isAdministrator()) {
            $rulegroups = $this->prepareRuleGroups();
            $rulegroups = implode(" OR ", $rulegroups);
			foreach ($roles as $role) {
				if (!is_numeric($role)) {
					$approved_roles[] = $role;
				} else {
					$roleID = Auth::getRoleTitleByID($role);
					if ($roleID != false) {
						$approved_roles[] = $roleID;
					}
				}
			}
            if(in_array('Creator', $approved_roles)) {
                $filterQueryParts[] = "(_authcreator_t:(" . $rulegroups . "))";
            } 
            if(in_array('Editor', $approved_roles)) {
                $filterQueryParts[] = "(_autheditor_t:(" . $rulegroups . "))";
            } 
            if(in_array('Lister', $approved_roles)) {
                $filterQueryParts[] = "(_authlister_t:(" . $rulegroups . "))";
            }

            if (is_array($filterQueryParts)) {
            	$filterQuery = implode(" OR ", $filterQueryParts);
			} else {
				$filterQuery = "";
			}
        }
        
        if($filter_join[2] != "") {
            if($filterQuery != "") {
                $filterQuery .= " AND ";
            }
            $filterQuery .= $filter_join[2];
        }
        
        return array('query' => $searchQuery, 'filter' => $filterQuery);
    }


	public function searchAdvancedQuery($searchKey_join, $filter_join, $approved_roles, $start, $page_rows) {


		try {

			//$solr = $this->getSolr();
			//Logger::debug("solr ping ".$solr->ping());
			$query = $this->prepareAdvancedQuery($searchKey_join, $filter_join, $approved_roles);
			// Solr search params
			$params = array();

			// hit highlighting
			$params['hl'] = 'true';			
			$params['hl.fl'] = 'content'; //'content_mt,alternative_title_mt,author_mt,keywords_mt';
			$params['hl.requireFieldMatch'] = 'false';			
			$params['hl.snippets'] = 3;
			$params['hl.fragmenter'] = 'gap';
			$params['hl.fragsize'] = 150;
			$params['hl.mergeContiguous'] = "true";
			
			$sekIDs = Search_Key::getFacetList();
			
			if(count($sekIDs) > 0) { 
				
				$params['facet'] = 'true';
				$params['facet.limit'] = '5';
				$params['facet.mincount'] = '1';
				
				foreach ($sekIDs as $sek) {
				    $sek_title_db = Search_Key::makeSQLTableName($sek['sek_title']);
				    $solr_suffix = Record::getSolrSuffix($sek,1,0);
				    $facetsToUse[] = $sek_title_db.$solr_suffix;
				}
			
				$params['facet.field'] = $facetsToUse;
			}

			// filtering
			$params['fq'] = $query['filter'];
			$queryString = $query['query'];
			$params['fl'] = '*,score';

			// sorting
			if (empty($searchKey_join[SK_SORT_ORDER])) {
				$params['sort'] = "";
			} else {
				$params['sort'] = $searchKey_join[SK_SORT_ORDER];
			}

			Logger::debug("Solr filter query: ".$params['fq']);
			Logger::debug("Solr query string: ".$queryString);
			Logger::debug("Solr sort by: ".$params['sort']);

			
			$response = $this->solr->search($queryString, $start, $page_rows, $params);
			$total_rows = $response->response->numFound;
			$docs = array();
			$snips = array();
			
			if ($total_rows > 0) {		
				$i = 0;
				
				foreach ($response->response->docs as $doc) {
				    
				    foreach ( $doc as $solrID => $field ) {
				        if(($sek_id = Search_Key::getDBnamefromSolrID($solrID))) {
				            $docs[$i][$sek_id] = $field;
				        }
				    }
				    
					// resolve result
					$docs[$i]['Relevance'] = $doc->score;
					$docs[$i]['rek_views'] = $doc->views_i;
					
//					foreach ($sekdet as $skey => $sval) {
//						$solr_suffix = Record::getSolrSuffix($sval);
//						$solr_name = $sval['sek_title_db'].$solr_suffix;
//						if ($sval['sek_relationship'] == 1) {
//							if (is_array($doc->$solr_name)) {
//								$docs[$i]["rek_".$sval['sek_title_db']] = $doc->$solr_name;
//							} else {
//								$docs[$i]["rek_".$sval['sek_title_db']] = array();
//								if ($doc->$solr_name != "") {
//									$docs[$i]["rek_".$sval['sek_title_db']][0] = $doc->$solr_name;
//								}
//							}
//						} else {
//							$docs[$i]["rek_".$sval['sek_title_db']] = $doc->$solr_name;							
//						}
//					}
					$i++;
				}
				
                if(is_object($response->facet_counts)) {
                    
                    $sekdet = Search_Key::getList(false);
                    
    				foreach ($response->facet_counts as $facetType => $facetData) {
    					
    				    if($facetType == 'facet_fields') {
    				        
    				        /*
    				         * We have to loop through every search key because
    				         * we can create a solr id from a fez search key but 
    				         * not the other way around.
    				         */
    				        foreach ($sekdet as $sval) {
    				            
                                $solr_suffix = Record::getSolrSuffix($sval,1,0);
                                $solr_name = $sval['sek_title_db'].$solr_suffix;
                                
                                if(isset($facetData->$solr_name)) {
                                    
                                    /*
                                     * Convert (if possible) values into text representation
                                     * Depositor id=1 becomes 'Adminstrator'
                                     */
                                    foreach ($facetData->$solr_name as $value => $numInFacet) {
                                        
                                    	$id = $value;
                                        if($sval['sek_lookup_function']) {
                                            eval("\$value = ".$sval["sek_lookup_function"]."(".$value.");");
                                        }
                                        
                                        $tmpArr[$id] = array(
                                            'value' =>  $value,
                                            'num'   =>  $numInFacet,
                                        );
                                    }
                                    
                                    if($tmpArr) {
                                        $facets[$sval['sek_id']] = array(
                                            'sek_title'     =>  $sval['sek_title'],
                                            'values'        =>  $tmpArr,
                                        );
                                        unset($tmpArr);
                                    }
                                    
                                    
                                }
                            }
    				        
    				    } elseif($facetType == 'facet_dates') {
    				        // Nothing for now
    				    }
    				}
    				
                }
                
                

				// Solr hit highlighting				
	            foreach ($response->highlighting as $pid => $snippet) {

	            	if (isset($snippet->content)) {   
	            		echo 'here';            	       	
		            	foreach ($snippet->content as $part) {
		            		$part = trim(str_ireplace(chr(12), ' | ', $part));		            		
		            		$snips[$pid] .= $part;
		            	}	
	            	} 
//	            	if (isset($snippet->keywords_mt)) {
//	            		foreach ($snippet->content_mt as $part) {
//		            		$part = trim(str_ireplace(chr(12), ' | ', $part));		            		
//		            		$snips[$pid] .= $part;
//		            	}
//	            	}
	            }

	         }	

		} catch (Exception $e) {

			//
			// catches any Solr service exceptions (malformed syntax etc.)
			//

			// TODO add fine grained control, user message error handling
			Logger::error("Error on searching: ".$e->getMessage());

			// report nothing found on error
			$docs = array();
			$total_rows = 0;	

    	}

    	return array(
    	   'total_rows' => $total_rows,
    	   'facets'     => $facets,
    	   'docs'       => $docs, 
    	   'snips'      => $snips
	   );		
		
	}

    
    protected function executeQuery($query, $options, $approved_roles, $sort_by, $start, $page_rows) {
    	
		try {
			
			//$solr = $this->getSolr();
			//Logger::debug("solr ping ".$solr->ping());

			// Solr search params
			$params = array();
			
			// hit highlighting
			$params['hl'] = 'true';			
			$params['hl.fl'] = 'content_mt'; //'content_mt,alternative_title_mt,author_mt,keywords_mt';
			$params['hl.requireFieldMatch'] = 'false';			
			$params['hl.snippets'] = 3;
			//$params['hl.formatter'] = 'gap';
			//$params['hl.formatter'] = 'simple';
			$params['hl.fragmenter'] = 'gap';
			$params['hl.fragsize'] = 150;
			$params['hl.mergeContiguous'] = "true";
			
			// filtering
			$params['fq'] = $query['filter'];			
			$queryString = $query['query'];
			$params['fl'] = '*,score';
			
			//$sort_by = 'score';
			
			// sorting
			if (!empty($sort_by)) {
				$params['sort'] = $sort_by;
				if ($options['sort_order'] == 1) {
					$params['sort'] .= ' desc';
				} else {
					$params['sort'] .= ' asc';
				}
				//var_dump($params['sort']);
			}
			
			
			Logger::debug("Solr filter query: ".$params['fq']);
			Logger::debug("Solr query string: $queryString");
			Logger::debug("Solr sort by: ".$params['sort']);
			
			$response = $this->solr->search($queryString, $start, $page_rows, $params);
			$total_rows = $response->response->numFound;
			
			$docs = array();
			$snips = array();
			if ($total_rows > 0) {		
				$i = 0;
				foreach ($response->response->docs as $doc) {
					// resolve result
					$docs[$i]['Relevance'] = $doc->score;
					$docs[$i]['rek_citation'] = $doc->citation_t;
					foreach ($sekdet as $skey => $sval) {
						$solr_suffix = Record::getSolrSuffix($sval);
						$solr_name = $sval['sek_title_db'].$solr_suffix;
						$docs[$i]["rek_".$sval['sek_title_db']] = $doc->$solr_name;

					}
					$i++;
				}
				
				// Solr hit highlighting				
	            foreach ($response->highlighting as $pid => $snippet) {	            	
	            	if (isset($snippet->content_mt)) {    	            	       	
		            	foreach ($snippet->content_mt as $part) {
		            		$part = trim(str_ireplace(chr(12), ' | ', $part));		            		
		            		$snips[$pid] .= $part;
		            	}	
	            	} 
	            	if (isset($snippet->keywords_mt)) {
	            		foreach ($snippet->content_mt as $part) {
		            		$part = trim(str_ireplace(chr(12), ' | ', $part));		            		
		            		$snips[$pid] .= $part;
		            	}
	            	}
	            } 	
       
	         }	
	         
		} catch (Exception $e) {
		
			//
			// catches any Solr service exceptions (malformed syntax etc.)
			//

			// TODO add fine grained control, user message error handling
			Logger::error("Error on searching: ".$e->getMessage());

			// report nothing found on error
			$docs = array();
			$total_rows = 0;	
					
    	}

    	return array(
    	   'total_rows' => $total_rows, 
    	   'docs'       => $docs, 
    	   'snips'      => $snips
	   );
    }
    
    
	public function getFieldName($fezName, $datatype=FulltextIndex::FIELD_TYPE_TEXT, 
		$multiple=false, $is_sort=false) {
			
    	$fezName .= '_';
    	if ($multiple) {
    		$fezName .= 'm';
    	}
    	
    	switch ($datatype) {
    		case FulltextIndex::FIELD_TYPE_TEXT: 
                $fezName .= 't'; 
                if($is_sort) {
                    $fezName .= '_s';
                }
                break;
                
    		case FulltextIndex::FIELD_TYPE_DATE: 
                $fezName .= 'dt';
                break;
                
    		case FulltextIndex::FIELD_TYPE_INT: 
                $fezName .= 'i'; 
                break;
                
    		case FulltextIndex::FIELD_TYPE_VARCHAR : 
                $fezName .= 't'; 
                if($is_sort) {
                    $fezName .= '_s';
                }
                break;
                
    		default:
    			$fezName .= 't';
    			if($is_sort) {
                    $fezName .= '_s';
                }
    	}
    	
    	return $fezName;
    }

    
    protected function optimizeIndex() {
    	try {
    		
	    	//$solr = $this->getSolr();
	    	$this->solr->optimize(false, false);
	    		    	
    	} catch (Exception $e) {
    		// it may happen, that solr is busy - in this case skip indexing
    		Logger::warn("Solr indexing: error on optimize index - ".Logger::str_r($e));
    	}
    }
    
    protected function forceCommit() {
        if(!empty($this->docs)) {
            
            try {
                
                $this->solr->addDocuments($this->docs);
                $this->solr->commit();
                
                unset($this->docs);
                Logger::debug("======= FulltextIndex::updateFulltextIndex committed mem_usage=".memory_get_usage(). " =======");
                
            } catch (Exception $e) {
                
                Logger::error("Error on searching: ".$e->getMessage());
                
            }
        }
    }
}


?>
