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

    try {
//      $doc = new Apache_Solr_Document();

      // set solr id to object pid of
      //$doc->id = $pid;
      $doc = [
        'id' => $pid,


      ];
      $doc['body'] = $fields;

//      foreach ($fields as $key => $value) {
//        if (is_array($value) && $fieldTypes) {
//          foreach ($value as $v) {
//            // too much utf8_encode for fields already encoded...
//            if ($v != "") {
//              $doc->setMultiValue($key, $v); // TODO: utf8_encode needed??
//            }
//          }
//        } else {
//          if (!empty($value)) {
//            $doc->$key = $value;
//          }
//        }
//      }

      $this->docs[] = $doc;
      $this->docsAdded++;

//      if ($this->docsAdded % 250 == 0) {
//        $this->solr->addDocuments($this->docs);
//        $this->solr->commit();
//
//        unset($this->docs);
//        $log->debug(array("======= FulltextIndex::updateFulltextIndex committed mem_usage=" . memory_get_usage() . " ======="));
//      }

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

//        $this->solr->addDocuments($this->docs);
//        $this->solr->commit();
        $params = ['body' => []];
        foreach($this->docs as $doc) {
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