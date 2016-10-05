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
  public function searchAdvancedQuery($searchKey_join, $filter_join, $approved_roles, $start, $page_rows, $use_faceting = false, $use_highlighting = false, $facet_limit = APP_SOLR_FACET_LIMIT, $facet_mincount = APP_SOLR_FACET_MINCOUNT)
  {
    $log = FezLog::get();


    try {
      $query = $this->prepareAdvancedQuery($searchKey_join, $filter_join, $approved_roles);
      // Solr search params
      $params = array();
      $facets = array();
//      $use_highlighting = false;
      if ($use_highlighting) {
        // hit highlighting
        $params['hl'] = 'true';
        $params['hl.fl'] = 'content'; //'content_mt,alternative_title_mt,author_mt,keywords_mt';
        $params['hl.requireFieldMatch'] = 'false';
        $params['hl.snippets'] = 1;
        $params['hl.fragmenter'] = 'gap';
        $params['hl.fragsize'] = 100;
        $params['hl.mergeContiguous'] = "true";
        $params['hl.useFastVectorHighlighter'] = "true";
//        $params['hl.useFastVectorHighlighter'] = "false";
      }
      $lookupFacetsToUse = array();
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

      // filtering
      $params['fq'] = $query['filter'];
      $queryString = $query['query'];
      $solr_titles = Search_Key::getSolrTitles();
      $params['fl'] = implode(",", $solr_titles) . ',sherpa_colour_t,ain_detail_t,rj_tier_rank_t,rj_tier_title_t,rj_2015_rank_t,rj_2015_title_t,rc_2015_rank_t,rc_2015_title_t,rj_2010_rank_t,rj_2010_title_t,rj_2012_rank_t,rj_2012_title_t,rc_2010_rank_t,rc_2010_title_t,herdc_code_description_t,score,citation_t';


      $log->debug(array("Solr filter query: " . $params['fq']));
      $log->debug(array("Solr query string: " . $queryString));
//      $log->debug(array("Solr sort by: " . $params['sort']));

      $solrParams = $params;

      $params = [
          'index' => $this->esIndex,
          'type' => $this->esType,
          'body' => [
              'query' => [
                  'filtered' => [
                      'query' => [
                          'query_string' => [
                              'query' => $queryString
                          ]
                      ],
                      'filter' => [
                          'query_string' => [
                              'query' => $solrParams['fq']

                          ]
                      ]
                  ]
              ]
          ]
      ];

      // sorting
      if (!empty($searchKey_join[SK_SORT_ORDER])) {
        $sortOrder = explode(" ", $searchKey_join[SK_SORT_ORDER]);
        $sortOrder[0] = str_replace("_ms", "_mt_s", $sortOrder[0]);
        $params['body']['sort'] = [$sortOrder[0] => ["order" => $sortOrder[1]]];
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
    $snips = array();
    $total_rows = $results['hits']['total'];

    if ($total_rows > 0) {
      $i = 0;
      $sekdet = Search_Key::getList(false);
      $cache_db_names = array();
      foreach ($results['hits']['hits'] as $doc) {

        foreach ($doc['_source'] as $solrID => $field) {
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
              $docs[$i][$sek_id] = $field;
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
        'facets' => array(),
        'docs' => $docs,
        'snips' => array()
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
  //TODO: implement
  protected function updateFulltextIndex($pid, $fields, $fieldTypes)
  {
    $log = FezLog::get();

    try {
      $fields['id'] = $pid;
      $fields['type'] = $this->esType;

      $doc = $fields;

      $this->docs[] = $doc;
      $this->docsAdded++;

      if ($this->docsAdded % 250 == 0) {
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
        $log->debug(array("======= FulltextIndex::updateFulltextIndex committed mem_usage=" . memory_get_usage() . " ======="));

      } catch (Exception $e) {

        $log->err($e);

      }
    }
  }


}