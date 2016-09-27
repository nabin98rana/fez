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
      $this->esHosts = APP_ES_HOST;
    }
    $this->esIndex = APP_ES_INDEX_NAME;

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
        'index' => 'my_index',
        'type' => 'my_type',
        'id' => $pid
    ];
    $this->esClient->delete($params);
  }


  /**
   * Create an ES index based on the schema mapping json
   *
   * @return array
   */
  //TODO: try it
  public function setupIndex() {
    $params = [
        'index' => 'fez',
        'body' => [
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0
            ]
        ]
    ];

    // Create the index with mappings and settings now
    $response = $this->esClient->indices()->create($params);
    if ($response) {
      $mapping = file_get_contents(__DIR__ ."../.docker/development/elasticsearch/elasticsearch_schema.json");
      $mapping = json_decode($mapping, true);
      return $this->esClient->indices('fez')->putMapping($mapping);
    } else {
      return false;
    }
  }

  //TODO: implement
  protected function executeQuery($query, $options, $approved_roles, $sort_by, $start, $page_rows)
  {
    $log = FezLog::get();
    return array();
  }

  //TODO: implement
  public function searchAdvancedQuery($searchKey_join, $filter_join, $approved_roles, $start, $page_rows, $use_faceting = false, $use_highlighting = false, $facet_limit = APP_SOLR_FACET_LIMIT, $facet_mincount = APP_SOLR_FACET_MINCOUNT) {
    $log = FezLog::get();
  }

  //TODO: implement
  public function suggestQuery($query, $approved_roles, $start, $page_rows) {

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
  }


}