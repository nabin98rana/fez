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
      $this->esHosts = array(APP_ES_HOST);
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
        'index' => $this->esIndex,
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

    // Uncomment if you need to delete the existing index first.
    //    $params = ['index' => $this->esIndex];
    //    $dresponse = $this->esClient->indices($this->esIndex)->delete($params);

    $file = __DIR__ ."/../../.docker/development/elasticsearch/elasticsearch_schema.json";
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