<?php

/**
 * Fulltext implementation for the Solr search engine.
 *
 * @author Kai Jauslin <kai.jauslin@library.ethz.ch>
 * @version 1.1, February 2008
 *
 */

include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.fulltext_index.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");
include_once(APP_INC_PATH . "Apache/Solr/Service.php");

class FulltextIndex_Solr extends FulltextIndex
{

  private $solrHost;
  private $solrPort;
  private $solrPath;
  private $docsAdded = 0;
  private $docs;
  public $solr;

  function __construct($readOnly = false)
  {
    $isAdministrator = Auth::isAdministrator();
    if (defined('APP_SOLR_SLAVE_HOST') && defined('APP_SOLR_SLAVE_READ') && (APP_SOLR_SLAVE_READ == "ON") && ($readOnly == true) && $isAdministrator != true) {
      $this->solrHost = APP_SOLR_SLAVE_HOST;
    } else {
      $this->solrHost = APP_SOLR_HOST;
    }
    $this->solrPort = APP_SOLR_PORT;
    $this->solrPath = APP_SOLR_PATH;

    $this->solr = new Apache_Solr_Service($this->solrHost, $this->solrPort, $this->solrPath);
  }

  /**
   * Returns an instance of the php solr service class.
   *
   * @return Apache_Solr_Service
   */
  private function getSolr()
  {
    $solr = new Apache_Solr_Service($this->solrHost, $this->solrPort, $this->solrPath);
    return $solr;
  }

  /**
   * Updates the Solr fulltext index with a new or existing document.
   *
   * @param string $pid
   * @param array $fields
   */
  protected function updateFulltextIndex($pid, $fields, $fieldTypes)
  {
    $log = FezLog::get();

    try {
      $doc = new Apache_Solr_Document();

      // set solr id to object pid of
      $doc->id = $pid;

      foreach ($fields as $key => $value) {
        if (is_array($value) && $fieldTypes) {
          foreach ($value as $v) {
            // too much utf8_encode for fields already encoded...
            if ($v != "") {
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
      $this->docsAdded++;

      if ($this->docsAdded % 250 == 0) {
        $this->solr->addDocuments($this->docs);
        $this->solr->commit();

        unset($this->docs);
        $log->debug(array("======= FulltextIndex::updateFulltextIndex committed mem_usage=" . memory_get_usage() . " ======="));
      }

    } catch (Exception $e) {

      // catches communication errors etc.
      //
      $log->err(array("Could not add document $pid to Solr index. Adding $pid to end of queue."));
      $log->err(array("Exception message was: " . $e->getMessage()));
      $log->debug(array("$pid added to queue (again)."));

    }
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

  protected function prepareQuery($params, $options, $rulegroups, $approved_roles, $sort_by, $start, $page_rows)
  {
    $query = '';
    $i = 0;
    if ($params['words']) {
      foreach ($params['words'] as $key => $value) {
        if ($value['wf'] != 'ALL') {
          $sek_details = Search_Key::getBasicDetailsByTitle($value['wf']);

          if ($sek_details['sek_relationship'] > 0) {
            $isMulti = true;
          } else {
            $isMulti = false;
          }
          $wf = $this->getFieldName($value['wf'], self::FIELD_TYPE_TEXT, $isMulti);
          $query .= $wf . ":(";
        } else {
          $query .= '(';
        }
        $query .= $value['w']; // need to do some escaping here?
        $query .= ')';

        $i++;
        if ($i < count($params['words'])) {
          $query .= ' ' . $value['op'] . ' ';
        }
      }
    }

    if ($params['direct']) {
      foreach ($params['direct'] as $key => $value) {
        if (strlen(trim($query)) > 0) {
          $query .= ' AND ';
        }
        $query .= '(' . $value . ')';
      }
    }

    $queryString = $query;
    $filterQuery = "(_authlister_t:(" . $rulegroups . ")) AND (status_i:2)";

    return array(
      'query' => $queryString,
      'filter' => $filterQuery
    );
  }

  public function suggestQuery($query, $approved_roles, $start, $page_rows)
  {
    $log = FezLog::get();

    try {
      $queryString = "title_t:" . $query['query'] . '';
      $params['fl'] = 'title_t,author_mt';
      $params['fl'] = 'title_t,author_mt';
      echo "qs = " . $queryString;

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
      $log->err($e);

      // report nothing found on error
      $docs = array();
      $total_rows = 0;

    }

    return array(
      'total_rows' => $total_rows,
      'docs' => $docs,
      'snips' => $snips
    );
  }

  public function prepareRuleGroups()
  {
    // gets user rule groups for this user
    $userID = Auth::getUserID();
    if (empty($userID)) {
      // get public lister rulegroups
      $userRuleGroups = Collection::getPublicAuthIndexGroups();
    } else {
      $userRuleGroups = Collection::getPublicAuthIndexGroups();
    }
    return $userRuleGroups;
  }


  protected function prepareAdvancedQuery($searchKey_join, $filter_join, $roles)
  {

    $filterQuery = "";

    if ($searchKey_join[2] == "") {
      $searchQuery = "*:*";
    } else {
      $searchQuery = $searchKey_join[2];
    }

    $approved_roles = array();
    if (!Auth::isAdministrator()) {
      $rulegroups = $this->prepareRuleGroups();
      $usr_id = Auth::getUserID();
      if (is_array($rulegroups)) {
        $rulegroups = implode(" OR ", $rulegroups);
      } else {
        $rulegroups = false;
      }

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
      if (is_numeric($usr_id)) {
        if (in_array('Creator', $approved_roles)) {
          $creatorGroups = Auth::getUserRoleAuthRuleGroupsInUse($usr_id, "Creator");
          if (is_array($creatorGroups)) {
            $creatorGroups = implode(" OR ", $creatorGroups);
            $filterQueryParts[] = "(_authcreator_t:(" . $creatorGroups . "))";
          } else {
            $filterQueryParts[] = "(_authcreator_t:(" . $rulegroups . "))";
          }
        }
        if (in_array('Editor', $approved_roles)) {
          $editorGroups = Auth::getUserRoleAuthRuleGroupsInUse($usr_id, "Editor");
          if (!empty($editorGroups)) {
            if (is_array($editorGroups)) {
              $editorGroups = implode(" OR ", $editorGroups);
              $filterQueryParts[] = "(_autheditor_t:(" . $editorGroups . "))";
            } else {
              $filterQueryParts[] = "(_autheditor_t:(" . $rulegroups . "))";
            }
          }
        }
        if (in_array('Lister', $approved_roles)) {
          $listerGroups = Auth::getUserListerAuthRuleGroupsInUse($usr_id);
          if (!empty($listerGroups)) {
            $listerGroups = implode(" OR ", $listerGroups);
            $filterQueryParts[] = "(_authlister_t:(" . $listerGroups . "))";
          }
        }
      } else {
        $filterQueryParts[] = "(_authlister_t:(" . $rulegroups . "))";
      }
      if (is_array($filterQueryParts)) {
        $filterQuery = implode(" OR ", $filterQueryParts);
      } else {
        $filterQuery = "";
      }
    }

    if ($filter_join[2] != "") {
      if ($filterQuery != "") {
        $filterQuery .= " AND ";
      }
      $filterQuery .= $filter_join[2];
    }

    return array('query' => $searchQuery, 'filter' => $filterQuery);
  }


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
                $params['f.'.$sek['sek_title_solr'].'.facet.limit'] = '6';
                $params['f.'.$sek['sek_title_solr'].'_lookup_exact'.'.facet.limit'] = '6';
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
      $params['fl'] = implode(",", $solr_titles) . ',sherpa_colour_t,ain_detail_t,rj_2014_rank_t,rj_2014_title_t,rj_2010_rank_t,rj_2010_title_t,rj_2012_rank_t,rj_2012_title_t,rc_2010_rank_t,rc_2010_title_t,herdc_code_description_t,score,citation_t';

      // sorting
      if (empty($searchKey_join[SK_SORT_ORDER])) {
        $params['sort'] = "";
      } else {
        $params['sort'] = $searchKey_join[SK_SORT_ORDER];
      }

      $log->debug(array("Solr filter query: " . $params['fq']));
      $log->debug(array("Solr query string: " . $queryString));
      $log->debug(array("Solr sort by: " . $params['sort']));

      $response = $this->solr->search($queryString, $start, $page_rows, $params);
      $total_rows = $response->response->numFound;
      $docs = array();
      $snips = array();

      if ($total_rows > 0) {
        $i = 0;
        $sekdet = Search_Key::getList(false);
        $cache_db_names = array();
        foreach ($response->response->docs as $doc) {

          foreach ($doc as $solrID => $field) {
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
            } elseif (in_array($solrID, array('sherpa_colour_t', 'ain_detail_t', 'rj_2014_rank_t','rj_2014_title_t', 'rj_2010_rank_t', 'rj_2010_title_t', 'rj_2012_rank_t', 'rj_2012_title_t', 'rc_2010_rank_t', 'rc_2010_title_t', 'herdc_code_description_t'))) {

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
          $docs[$i]['Relevance'] = $doc->score;
          $docs[$i]['rek_views'] = $doc->views_i;
          $i++;
        }

        if (is_object($response->facet_counts)) {


          foreach ($response->facet_counts as $facetType => $facetData) {

            if ($facetType == 'facet_fields') {

              /*
               * We have to loop through every search key because
               * we can create a solr id from a fez search key but
               * not the other way around.
               */
              foreach ($sekdet as $sval) {


                if ($sval['sek_data_type'] == "date") {
                  $solr_name = $sval['sek_title_db'] . "_year_t";
                } else {
                  $solr_suffix = Record::getSolrSuffix($sval, 0, 1);
                  $solr_name = $sval['sek_title_db'] . $solr_suffix;
                }


                if (isset($facetData->$solr_name)) {

                  /*
                   * Convert (if possible) values into text representation
                   * Depositor id=1 becomes 'Administrator'
                   */
                  $tmpArr = array();
                  $facetIndex = 0;

                  $allDifferent = true;
                  $previousNum = 0;
                  foreach ($facetData->$solr_name as $valueCheck => $numInFacetCheck) {
                    if ($numInFacetCheck == $previousNum) {
                      $allDifferent = false;
                    }
                    $previousNum = $numInFacetCheck;
                  }

                  foreach ($facetData->$solr_name as $value => $numInFacet) {
                    $valueFound = '';
                    // don't add the lookup values, just the ids
                    if (!is_numeric(strpos($solr_name, '_lookup'))) {

                      $id = $value;
                      if (!empty($sval['sek_lookup_function'])) {
//                        $solr_name_cut = preg_replace('/(.*)({_t_s|_mt|_t|_t_s|_dt|_ms|_s|_t_ws|_t_ft|_f|_mws|_ft|_mft|_mtl|_l|_mi|_i|_b|_mdt|_mt_exact}$)/', '$1', $solr_name);
                        // Try and get the lookup names from inside the facet returned values themselves

                        $value_lookup = '';
                        if ($allDifferent == true && array_key_exists($solr_name, $lookupFacetsToUse)) {
                          $facetIndex2 = 0;
                          foreach ($facetData->$lookupFacetsToUse[$solr_name] as $value2 => $numInFacet2) {
                            if ($facetIndex == $facetIndex2) {
                              $value_lookup = $value2;
                            }
                            $facetIndex2++;
                          }
                        }

                        // if couldn't find it in the solr array, get it manually. Don't lookup 0 value author id things.
                        if ($allDifferent != true && $value_lookup == '' && $value != '0') {
                          eval("\$valueFound = " . $sval["sek_lookup_function"] . "('" . $value . "');");
                        } else {
                          $valueFound = $value_lookup;
                        }
                      } else {
                        $valueFound = $value;
                      }

                      $tmpArr[$id] = array(
                        'value' => $valueFound,
                        'num' => $numInFacet,
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

            } elseif ($facetType == 'facet_dates') {
              // Nothing for now
            }
          }

        }


        // Solr hit highlighting
        //if(is_object($response->facet_counts)) {
        if (is_object($response->highlighting)) {
          foreach ($response->highlighting as $pid => $snippet) {
            if (isset($snippet->content)) {
              foreach ($snippet->content as $part) {
                $part = trim(str_ireplace(chr(12), ' | ', $part));
                if (!array_key_exists($pid, $snips)) {
                  $snips[$pid] = '';
                }
                $snips[$pid] .= $part;
              }
            }
          }
        }

      }

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
    return array(
      'total_rows' => $total_rows,
      'facets' => $facets,
      'docs' => $docs,
      'snips' => $snips
    );

  }


  protected function executeQuery($query, $options, $approved_roles, $sort_by, $start, $page_rows)
  {
    $log = FezLog::get();

    try {
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

      // filtering
      $params['fq'] = $query['filter'];
      $queryString = $query['query'];

      $solr_titles = Search_Key::getSolrTitles();
      $params['fl'] = implode(",", $solr_titles) . ',score,citation_t';

      // sorting
      if (!empty($sort_by)) {
        $params['sort'] = $sort_by;
        if ($options['sort_order'] == 1) {
          $params['sort'] .= ' desc';
        } else {
          $params['sort'] .= ' asc';
        }
      }


      $log->debug(array("Solr filter query: " . $params['fq']));
      $log->debug(array("Solr query string: $queryString"));
      $log->debug(array("Solr sort by: " . $params['sort']));

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
            $solr_name = $sval['sek_title_db'] . $solr_suffix;
            if ($sval["sek_relationship"] == 1 && !is_array($doc->$solr_name)) {
              if (!is_array($docs[$i]["rek_" . $sval['sek_title_db']])) {
                $docs[$i]["rek_" . $sval['sek_title_db']] = array();
              }
              $docs[$i]["rek_" . $sval['sek_title_db']][] = $doc->$solr_name;
            } else {
              $docs[$i]["rek_" . $sval['sek_title_db']] = $doc->$solr_name;
            }
          }
          $i++;
        }

        // Solr hit highlighting
        foreach ($response->highlighting as $pid => $snippet) {
          if (isset($snippet->content)) {
            foreach ($snippet->content as $part) {
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
      $log->err($e);

      // report nothing found on error
      $docs = array();
      $total_rows = 0;

    }

    return array(
      'total_rows' => $total_rows,
      'docs' => $docs,
      'snips' => $snips
    );
  }


  public function getFieldName($fezName, $datatype = FulltextIndex::FIELD_TYPE_TEXT,
                               $multiple = false, $is_sort = false)
  {

    $fezName .= '_';
    if ($multiple) {
      $fezName .= 'm';
    }

    switch ($datatype) {
      case FulltextIndex::FIELD_TYPE_TEXT:
        $fezName .= 't';
        if ($is_sort) {
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
        if ($is_sort) {
          $fezName .= '_s';
        }
        break;

      default:
        $fezName .= 't';
        if ($is_sort) {
          $fezName .= '_s';
        }
    }

    return $fezName;
  }


  protected function optimizeIndex()
  {
    $log = FezLog::get();

    try {
      $this->solr->optimize(false, false);

    } catch (Exception $e) {
      // it may happen, that solr is busy - in this case skip indexing
      $log->warn(array("Solr indexing: error on optimize index - " . $e->getMessage()));
    }
  }

  protected function forceCommit()
  {
    $log = FezLog::get();

    if (!empty($this->docs)) {

      try {

        $this->solr->addDocuments($this->docs);
        $this->solr->commit();

        unset($this->docs);
        $log->debug(array("======= FulltextIndex::updateFulltextIndex committed mem_usage=" . memory_get_usage() . " ======="));

      } catch (Exception $e) {

        $log->err($e);

      }
    }
  }
}
