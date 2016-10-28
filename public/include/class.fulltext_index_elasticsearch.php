<?php

/**
 * Fulltext implementation for the ElasticSearch search engine.
 *
 * @author Christiaan Kortekaas <ck@uq.edu.au>
 * @version 0.1, September 2016
 *
 */

include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.fulltext_index.php");

use Elasticsearch\ClientBuilder;

require_once(__DIR__ . '/autoload.php'); // since ES is pulled in via composer, this is how to include it

class FulltextIndex_ElasticSearch extends FulltextIndex
{

  private $esHost;
  private $esPort;
  private $esPath;
  private $esType;
  private $docsAdded = 0;
  private $docs;
  private $esClient;

  //TODO: try it
  function __construct($readOnly = false)
  {
    $isAdministrator = Auth::isAdministrator();
    if (defined('APP_ES_SLAVE_HOST') && defined('APP_ES_SLAVE_READ') && (APP_ES_SLAVE_READ == "ON") && ($readOnly == true) && $isAdministrator != true) {
      $this->esHosts = array(APP_ES_SLAVE_HOST);
    } else {
      $this->esHosts = array(APP_ES_HOST);
    }
    $this->esIndex = APP_ES_INDEX_NAME;
    $this->esType = 'fez_record';

    $this->esClient = ClientBuilder::create()->setHosts($this->esHosts)->build();
  }



  /**
   * Delete this pid in ES index.
   *
   * @param string $pid
   */
  //TODO: try it
  protected function removeByPid($pid)
  {
    $log = FezLog::get();

    // call parent cleanup
    parent::removeByPid($pid);

    $log->debug(array("removeByPid($pid) -> call ES with deleteById($pid)"));
    $params = [
        'index' => $this->esIndex,
        'id' => $pid
    ];
    $this->esClient->delete($params);
  }

  /**
   * Delete an array of pids from the index
   * @param array $pids
   */
  protected function bulkDelete($pids) {
    foreach ($pids as $pid) {
      $params['body'][] = array(
          'delete' => array(
              '_index' => $this->esIndex,
              '_type' => $this->esType,
              '_id' => $pid
          )
      );
      $this->esClient->bulk($params);
    }
  }


  /**
   * Create an ES index based on the schema mapping json
   *
   * @return boolean
   */
  //TODO: try it
  public function setupIndex()
  {

    // Uncomment if you need to delete the existing index first.
    //    $params = ['index' => $this->esIndex];
    //    $dresponse = $this->esClient->indices($this->esIndex)->delete($params);

    $file = __DIR__ . "/../../.docker/development/elasticsearch/elasticsearch_schema.json";
    $mapping = file_get_contents($file);
    $mapping = json_decode($mapping, true);
    $params = [
        'index' => $this->esIndex,
        'body' => $mapping
    ];
    // Create the index with mappings and settings now
    return $response = $this->esClient->indices()->create($params);
  }

  //TODO: implement
  protected function executeQuery($query, $options, $approved_roles, $sort_by, $start, $page_rows)
  {
    $log = FezLog::get();
    return array();
  }

  //TODO: implement
  public function searchAdvancedQuery($searchKey_join, $filter_join, $approved_roles, $start, $page_rows, $use_faceting = false, $use_highlighting = false, $facet_limit = APP_ES_FACET_LIMIT, $facet_mincount = APP_ES_FACET_MINCOUNT)
  {
    $log = FezLog::get();


    try {
      $query = $this->prepareAdvancedQuery($searchKey_join, $filter_join, $approved_roles);
      // Solr search params
      $params = array();
      $facets = array();


      if ($use_faceting) {
        $sekIDs = Search_Key::getFacetList();

        if (count($sekIDs) > 0) {

          $params['facet'] = 'true';
          $params['facet.limit'] = $facet_limit;
          $params['facet.mincount'] = $facet_mincount;

          foreach ($sekIDs as $sek) {
            $sek_title_db = $sek['sek_title_db'];
            if ($sek['sek_data_type'] == "date") {
              $facetsToUse[] = $sek_title_db . "_year_t";
            } else {
              $solr_suffix = Record::getSolrSuffix($sek, 0, 1);
              // filter tag exclude the zeros from author ids
              if ($sek['sek_title'] == 'Author ID') {
                $params['f.' . $sek['sek_title_solr'] . '.facet.limit'] = '6';
                $params['f.' . $sek['sek_title_solr'] . '_lookup_exact' . '.facet.limit'] = '6';
              }
              $facetsToUse[] = $sek_title_db . $solr_suffix;


              // Also add the lookup if it exists, and join them afterwards,
              // so lookups don't have to be done, but id links still work
              if (!empty($sek['sek_lookup_function'])) {
                $facetsToUse[] = $sek['sek_title_solr'] . '_lookup_exact';
                // keep a reference of the added facet lookups for retrieval later
                $lookupFacetsToUse[$sek_title_db . $solr_suffix] = $sek['sek_title_solr'] . '_lookup_exact';
              }
            }

          }

          $params['facet.field'] = $facetsToUse;

//          $params['fq{!tag=zero_author_id_exact}'] = "author_id_mi_lookup_exact:'0'";
//          $params['facet.query'] = '('.$query['query'] . " AND (!author_id_mi_lookup_exact:'0' AND !author_id_mi:0))";
        }
      }
      $facets = array();
      foreach ($facetsToUse as $facet) {
        $facets["facets"][$facet] = ["terms" => ["field" => $facet, "size" => (int)$facet_limit, "min_doc_count" => (int)$facet_mincount]];
      }
      // filtering
      $params['fq'] = $query['filter'];
      $queryString = $query['query'];
      //split integer out because those fields go into docvalue_fields not stored_fields in ES, see https://www.elastic.co/guide/en/elasticsearch/reference/master/doc-values.html
      $integerFields = Search_Key::getSolrTitles(true, true, 'intOnly');
      $solr_titles = Search_Key::getSolrTitles(true, true, 'notInt');
      $docValueFields = array();
      foreach ($integerFields as $value) {
        if (is_numeric(strpos($value, "_lookup"))) {
          array_push($solr_titles, $value);
        } else {
          array_push($docValueFields, $value);
        }
      }
      $params['fl'] = implode(",", $solr_titles) . ',sherpa_colour_t,ain_detail_t,rj_tier_rank_t,rj_tier_title_t,rj_2015_rank_t,rj_2015_title_t,rc_2015_rank_t,rc_2015_title_t,rj_2010_rank_t,rj_2010_title_t,rj_2012_rank_t,rj_2012_title_t,rc_2010_rank_t,rc_2010_title_t,herdc_code_description_t,_score,citation_t';


      $log->debug(array("Solr filter query: " . $params['fq']));
      $log->debug(array("Solr query string: " . $queryString));
//      $log->debug(array("Solr sort by: " . $params['sort']));
      $fields = explode(",", $params['fl']);
      $solrParams = $params;

      $params = [
          'index' => $this->esIndex,
          'type' => $this->esType,
          'body' => [
              'docvalue_fields' => $docValueFields,
              'stored_fields' => $fields,
              'from' => $start,
              'size' => $page_rows,
              'query' => [
                  'bool' => [
                      'must' => [
                          'query_string' => [
                              'default_operator' => 'AND',
                              'query' => $queryString
                          ]
                      ],
                      'filter' => [
                          'query_string' => [
                              'default_operator' => 'AND',
                              'query' => $solrParams['fq']

                          ]
                      ]
                  ]
              ]
          ]
      ];

      // aggs
      if (is_array($facets['facets']) && count($facets['facets']) > 0) {
        $params['body']['aggregations'] = $facets['facets'];
      }
      // sorting
      $searchKey_join[SK_SORT_ORDER] = trim($searchKey_join[SK_SORT_ORDER]);
      if (!empty($searchKey_join[SK_SORT_ORDER])) {
        $sortOrder = explode(" ", $searchKey_join[SK_SORT_ORDER]);
        $sortOrder[0] = str_replace("_ms", "_mt_s", $sortOrder[0]);
        $sortOrder[0] = str_replace("score", "_score", $sortOrder[0]);
        $params['body']['sort'] = [$sortOrder[0] => ["order" => $sortOrder[1]]];
      }

      if ($use_highlighting) {
        // hit highlighting
        $params['body']['highlight']['fields']['content_mt'] = [
          'fragment_size' => 100,
          'number_of_fragments' => 1,
          'highlight_query' => [
              'bool' => [
                  'should' => [
                      'match_phrase' => [
                          'content_mt' => [
                              'query' => $queryString,
                              'phrase_slop' => 1,
                              'boost' => 10.0
                          ]
                      ]
                  ]
              ]
          ]
        ];
        $params['body']['highlight']['require_field_match'] = false;
      }

      $testJson = json_encode($params);

      $results = $this->esClient->search($params);
    } catch (Exception $e) {

      //
      // catches any Solr service exceptions (malformed syntax etc.)
      //

      // TODO add fine grained control, user message error handling
      $log->err($e);

      // report nothing found on error
      $docs = array();
      $total_rows = 0;

    }

    array_push($log->solr_query_time, $results['took']);
    array_push($log->solr_query_string, '');


    $snips = array();
    $total_rows = $results['hits']['total'];
    $facets = $this->extractFacets($results['aggregations'], $facetsToUse, $lookupFacetsToUse);

    // Solr hit highlighting
    foreach ($results['hits']['hits'] as $hit) {
      $pid = $hit['_id'];
      if (array_key_exists('highlight', $hit)) {
        foreach ($hit['highlight']['content_mt'] as $part) {
          $part = trim(str_ireplace(chr(12), ' | ', $part));
          $snips[$pid] .= $part;
        }
      }
    }

    if ($total_rows > 0) {
      $i = 0;
      $sekdet = Search_Key::getList(false);
      $cache_db_names = array();
      foreach ($results['hits']['hits'] as $doc) {

        foreach ($doc['fields'] as $solrID => $field) {
          if (($sek_id = Search_Key::getDBnamefromSolrID($solrID))) {
            if (array_key_exists($sek_id, $cache_db_names)) {
              $sek_rel = $cache_db_names[$sek_id];
            } else {
              $sek_rel = Search_Key::getCardinalityByDBName($sek_id);
              $cache_db_names[$sek_id] = $sek_rel;
            }
            if ($sek_rel == '1' && !is_array($field)) {
              if (!array_key_exists($sek_id, $docs[$i])) {
                $docs[$i][$sek_id] = array();
              }
              $docs[$i][$sek_id][] = $field;
            } else {
              $docs[$i][$sek_id] = $field[0];
            }
            // check for herdc code desc
          } elseif (in_array($solrID, array('sherpa_colour_t', 'ain_detail_t', 'rj_tier_rank_t', 'rj_tier_title_t', 'rj_2015_rank_t', 'rj_2015_title_t', 'rj_2010_rank_t', 'rj_2010_title_t', 'rj_2012_rank_t', 'rj_2012_title_t', 'rc_2015_rank_t', 'rc_2015_title_t', 'rc_2010_rank_t', 'rc_2010_title_t', 'herdc_code_description_t'))) {

            $sek_id = substr($solrID, 0, -2);
            if (is_array($field)) {
              if (!array_key_exists($sek_id, $docs[$i])) {
                $docs[$i][$sek_id] = array();
              }
              $docs[$i][$sek_id] = $field;
            } else {
              $docs[$i][$sek_id] = $field;
            }
            // check for lookups and other values and add them too
          } elseif (is_numeric(strpos($solrID, '_lookup'))) {

            $sek_id = str_replace('_mi_lookup', '_lookup', $solrID);
            $sek_id = str_replace('_i_lookup', '_lookup', $solrID);
            $sek_id = "rek_" . $sek_id;
            if (is_array($field)) {
              if (!array_key_exists($sek_id, $docs[$i])) {
                $docs[$i][$sek_id] = array();
              }
              $docs[$i][$sek_id] = $field;
            } else {
              $docs[$i][$sek_id] = $field;
            }

          }

        }

        // resolve result
        $docs[$i]['Relevance'] = $doc['_score'];
        $docs[$i]['rek_views'] = $doc['_source']['views'];
        $i++;
      }
    }

    return array(
        'total_rows' => $total_rows,
        'facets' => $facets,
        'docs' => $docs,
        'snips' => $snips
    );

  }

  //TODO: implement
  public function suggestQuery($query, $approved_roles, $start, $page_rows)
  {

  }

  /**
   * Updates the ES fulltext index with a new or existing document.
   *
   * @param string $pid
   * @param array $fields
   */
  protected function updateFulltextIndex($pid, $fields)
  {
    $log = FezLog::get();

    try {
      $fields['id'] = $pid;
      $fields['type'] = $this->esType;

      $doc = $fields;

      $this->docs[] = $doc;
      $this->docsAdded++;

      if ($this->docsAdded % APP_SOLR_COMMIT_LIMIT == 0) {
        $this->forceCommit();
        unset($this->docs);
        $log->debug(array("======= FulltextIndex::updateFulltextIndex committed mem_usage=" . memory_get_usage() . " ======="));

      }
    } catch (Exception $e) {

      // catches communication errors etc.
      //
      $log->err(array("Could not add document $pid to ES index. Adding $pid to end of queue."));
      $log->err(array("Exception message was: " . $e->getMessage()));
      $log->debug(array("$pid added to queue (again)."));

    }


  }

  /**
   * Extract the facets from a search response into the array format required by Fez
   *
   * @param array $aggs
   * @return array
   */
  private function extractFacets($aggs, $facetsToUse, $lookupFacetsToUse)
  {
    $facets = array();
    if (is_array($aggs)) {
      /*
       * We have to loop through every search key because
       * we can create a solr id from a fez search key but
       * not the other way around.
       */
      $sekdet = Search_Key::getList(false);
      foreach ($sekdet as $sval) {
        if ($sval['sek_data_type'] == "date") {
          $solr_name = $sval['sek_title_db'] . "_year_t";
        } else {
          $solr_suffix = Record::getSolrSuffix($sval, 0, 1);
          $solr_name = $sval['sek_title_db'] . $solr_suffix;
        }
        if (array_key_exists($solr_name, $aggs) && in_array($solr_name, $facetsToUse)) {

          /*
           * Convert (if possible) values into text representation
           * Depositor id=1 becomes 'Administrator'
           */
          $tmpArr = array();
          $facetIndex = 0;

          $allDifferent = true;
          $previousNum = 0;
          foreach ($aggs[$solr_name]['buckets'] as $bucket) {
            // $valueCheck => $numInFacetCheck
            if ($bucket['doc_count'] == $previousNum) {
              $allDifferent = false;
            }
            $previousNum = $bucket['doc_count'];
          }

          foreach ($aggs[$solr_name]['buckets'] as $bucket) {
            //$value => $numInFacet
            $valueFound = '';
            // don't add the lookup values, just the ids
            if (!is_numeric(strpos($solr_name, '_lookup'))) {

              $id = $bucket['key'];
              if (!empty($sval['sek_lookup_function'])) {
//                        $solr_name_cut = preg_replace('/(.*)({_t_s|_mt|_t|_t_s|_dt|_ms|_s|_t_ws|_t_ft|_f|_mws|_ft|_mft|_mtl|_l|_mi|_i|_b|_mdt|_mt_exact}$)/', '$1', $solr_name);
                // Try and get the lookup names from inside the facet returned values themselves

                $value_lookup = '';
                if ($allDifferent == true && array_key_exists($solr_name, $lookupFacetsToUse)) {
                  $facetIndex2 = 0;
                  foreach ($aggs[$lookupFacetsToUse[$solr_name]]['buckets'] as $value2) {
                    if ($facetIndex == $facetIndex2) {
                      $value_lookup = $value2['key'];
                    }
                    $facetIndex2++;
                  }
                }

                // if couldn't find it in the solr array, get it manually. Don't lookup 0 value author id things.
                if ($allDifferent != true && $value_lookup == '' && $value != '0') {
                  eval("\$valueFound = " . $sval["sek_lookup_function"] . "('" . $bucket['key'] . "');");
                } else {
                  $valueFound = $value_lookup;
                }
              } else {
                $valueFound = $bucket['key'];
              }

              $tmpArr[$id] = array(
                  'value' => $valueFound,
                  'num' => $bucket['doc_count'],
              );
            }
            $facetIndex++;
          }
          if (count($tmpArr) > 0) {
            $facets[$sval['sek_id']] = array(
                'sek_title' => $sval['sek_title'],
                'sek_alt_title' => $sval['sek_alt_title'],
                'values' => $tmpArr,
            );
            unset($tmpArr);
          }
        }
      }
    }
    return $facets;
  }

  protected function forceCommit()
  {
    $log = FezLog::get();

    if (!empty($this->docs)) {

      try {
        $params = ['body' => []];
        foreach ($this->docs as $doc) {
          $params['body'][] = [
              'index' => [
                  '_index' => $this->esIndex,
                  '_type' => $this->esType,
                  '_id' => $doc['id']
              ]
          ];
          $params['body'][] = $doc;
        }

        $this->esClient->bulk($params);

        unset($this->docs);
        $memoryUsage = Misc::bytesToSize(memory_get_usage());
        $log->debug(array("======= FulltextIndex::updateFulltextIndex committed mem_usage=" . $memoryUsage . " ======="));
//        echo "======= FulltextIndex::updateFulltextIndex committed mem_usage=" . $memoryUsage . " =======\n";

      } catch (Exception $e) {

        $log->err($e);

      }
    }
  }


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
    /*
     * Custom search key (not a registered search key)
     */
    $citationKey = array(
        'sek_title' => 'citation',
        'sek_title_db' => 'citation',
        'sek_data_type' => 'text',
        'sek_relationship' => 0,
        'sek_simple_used' => 1,
    );
    $searchKeys[] = $citationKey;
    $roles = array(
        'Lister',
        'Creator',
        'Editor',
    );

    $queue = FulltextQueue::singleton();
    $this->totalDocs = $queue->size();

    if ($this->bgp) {
      $this->bgpDetails = $this->bgp->getDetails();
    }

    // Loop through queue and index a number of records set in APP_SOLR_COMMIT_LIMIT config var at a time into solr
    while (($chunk = $queue->popChunkCache()) != false) {

      $pids_arr = array();
      // first cache anything not already cached
      foreach ($chunk as $row) {
        if (empty($row['ftq_pid']))
          continue;
        $pids_arr[] = $row['ftq_pid'];
      }
      $this->indexRecords($pids_arr, $queue);

      $countDocs += count($chunk);
      if ($countDocs > $this->totalDocs) {
        $countDocs = $this->totalDocs;
      }

      if ($this->bgp) {
        $this->bgp->setStatus("Finished Solr fulltext indexing for (" . $countDocs . "/" . $this->totalDocs . " Added)");
        $this->bgp->setProgress($countDocs);

        foreach ($pids_arr as $finishedPid) {
          $this->bgp->markPidAsFinished($finishedPid);
        }
      }
    }

    if ($this->bgp) {
      $this->bgp->setStatus("Processing any PIDS to delete from solr");
    }
    $deletePids = $queue->popDeleteChunk();

    if ($deletePids) {
      if ($this->bgp) {
        $this->bgp->setStatus("Deleting " . count($deletePids) . " from Solr Index");
      }
      $this->bulkDelete($deletePids);
    }
    $this->forceCommit();
    return $countDocs;
  }

}