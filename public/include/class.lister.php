<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
// | Australian Partnership for Sustainable Repositories,                 |
// | eScholarship Project                                                 |
// |                                                                      |
// | Some of the Fez code was derived from Eventum (Copyright 2003, 2004  |
// | MySQL AB - http://dev.mysql.com/downloads/other/eventum/ - GPL)      |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle the business logic related to the administration
 * of collections in the system.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 * @author Lachlan Kuhn <l.kuhn@library.uq.edu.au>
 */
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.search_key.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.citation.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.custom_view.php");
include_once(APP_INC_PATH . "class.favourites.php");
include_once(APP_INC_PATH . "najax_classes.php");
include_once(APP_INC_PATH . "class.api.php");
include_once(APP_INC_PATH . "class.publons.php");

class Lister
{
  public static function getList($params, $display = true)
  {
    $log = FezLog::get();

    //These are the only $params(ie. $_GET) vars that can be passed to this page.
    //Strip out any that aren't in this list
    $args = array(
        'browse' => 'string',
        'author_id' => 'numeric',
        'collection_pid' => 'string',
        'community_pid' => 'string',
        'cat' => 'string',
        'author' => 'string',
        'tpl' => 'numeric',
        'year' => 'numeric',
        'rows' => 'numeric',
        'pager_row' => 'numeric',
        'sort' => 'string',
        'letter' => 'string',
        'sort_by' => 'string',
        'search_keys' => 'array',
        'order_by' => 'string',
        'sort_order' => 'string',
        'value' => 'string',
        'operator' => 'string',
        'custom_view_pid' => 'string',
        'form_name' => 'string',
        'format' => 'string'
    );
    $zf = new Fez_Filter_RichTextHtmlpurify();
    foreach ($args as $getName => $getType) {
      if (array_key_exists($getName, $params)) {
        if (Misc::sanity_check($params[$getName], $getType) !== false) {
          $allowed[$getName] = $params[$getName];
        }
      } else {
        $allowed[$getName] = '';
      }
    }
    $params = $allowed;

    if ($params['search_keys']) //In case someone launches a search with no params
    {
      foreach ($params['search_keys'] as $paramk => $paramv) {
        if (!is_array($paramv)) {
          $params['search_keys'][$paramk] = trim($paramv);
        }

      }
    }

    $custom_view_pid = $params['custom_view_pid'];
    $facets = array();
    $snips = array();
    $tpl = new Template_API();
    if (is_numeric($params['tpl'])) {
      $tpl_idx = intval($params['tpl']);
    } else {
      $tpl_idx = 0;
    }

    //if the template is 11 we'll treat it as XML then json convert it
    if (APP_API == 'xml') {
      $tpl_idx = 3;
    } elseif (APP_API == 'json' || $tpl_idx == 11) {
      $tpl_idx = 3;
      $jsonIt = true;
    }
    $tpls = array(
        0 => array('file' => 'list.tpl.html', 'title' => 'Default'),
        2 => array('file' => 'rss.tpl.html', 'title' => 'RSS Feed'),
        3 => array('file' => 'xml_feed.tpl.xml', 'title' => 'XML Feed'),
        1 => array('file' => 'excel.tpl.html', 'title' => 'Excel File'),
        4 => array('file' => 'citation_only_list.tpl.html', 'title' => 'Citations Only'),
        5 => array('file' => 'simple_list.tpl.html', 'title' => 'Classic Simple View'),
        6 => array('file' => 'gallery_list.tpl.html', 'title' => 'Image Gallery View'),
        7 => array('file' => 'endnote.tpl.html', 'title' => 'Export for Endnote'), //added for endnote - heaphey
        8 => array('file' => 'js.tpl.html', 'title' => 'HTML Code'), //added for js - heaphey
        9 => array('file' => 'msword.tpl.html', 'title' => 'Word File'), //added for word out - heaphey
        11 => array('file' => 'xml_feed.tpl.xml', 'title' => 'JSON') //This will convert XML to json before displaying
    );

    if (!empty($custom_view_pid)) {
      if (!is_numeric($params['tpl'])) {
        $cvcom_details = Custom_View::getCommCview($custom_view_pid);
        foreach ($tpls as $tplkey => $tplval) {
          if ($cvcom_details['cvcom_default_template'] == $tplval['file']) {
            $tpl_idx = $tplkey;
          }
        }
      }
      $child_collections = Record::getCollectionChildrenAll($custom_view_pid);
      $filter["searchKey" . Search_Key::getID("isMemberOf")]['override_op'] = 'OR';
      $filter["searchKey" . Search_Key::getID("isMemberOf")][] = $custom_view_pid;
      foreach ($child_collections as $rek_row) {
        $filter["searchKey" . Search_Key::getID("isMemberOf")][] = $rek_row['rek_pid'];
      }
    }

    $username = Auth::getUsername();
    $isAdministrator = User::isUserAdministrator($username);
    $isUPO = User::isUserUPO($username);

    if ($isAdministrator == true || $isUPO) {
      $tpl->assign("jqueryUI", true);
    }


    if (($tpl_idx != 0 && $tpl_idx != 4) || $isAdministrator || $isUPO) {
      $citationCache = false;
    } else {
      $citationCache = true;
    }
    $getSimple = false;
    if ($tpl_idx == 2 || ($tpl_idx == 3 && !$jsonIt)) {
      header("Content-type: application/xml");
    } elseif ($tpl_idx == 1) {
      header("Content-type: application/vnd.ms-excel");
      header("Content-Disposition: attachment; filename=export.xls");
      header("Content-Description: PHP Generated XLS Data");
    } elseif ($tpl_idx == 7) {  //heaphey - added for endnote
      header("Content-type: application/vnd.endnote");
      header("Content-Disposition: attachment; filename=endnote.enw");
      header("Content-Description: PHP Generated Endnote Data");
    } elseif ($tpl_idx == 9) {  //heaphey - added for word
      header("Content-type: application/vnd.ms-word");
      header("Content-Disposition: attachment; filename=word.doc");
      header("Content-Description: PHP Generated Word Data");
    } else if ($jsonIt) {
      header('Content-Type: application/json');
    }

    // for the html code output, we want to output the search params, so clean them up first
    if ($tpl_idx == 8) {
      $excludeForHtmlOutput = array(
          'pager_row',
          'rows'
      );
      $dynamicParams = Misc::query_string_encode($params, $excludeForHtmlOutput);
      $tpl->assign('dynamicParams', $dynamicParams);
    }

    $pager_row = $params['pager_row'];
    if (empty($pager_row) || $pager_row < 0) {
      $pager_row = 0;
    }

    $session =& Auth::getSession();

    $rows = $params['rows'];
    if (empty($rows)) {
      if (!empty($session['rows'])) {
        $rows = $session['rows'];
      } else {
        $rows = APP_DEFAULT_PAGER_SIZE;
      }
    } else {
      if ($rows < 0) {
        $rows = APP_DEFAULT_PAGER_SIZE;
      }
      $session['rows'] = $rows;
    }

    $cookie_key = Pager::getParam('form_name', $params);
    $options = Pager::saveSearchParams($params, $cookie_key);

    if ($tpl_idx == 0 || $tpl_idx == 4 || $tpl_idx == 5 || $tpl_idx == 6) {
      $tpl->setTemplate('header.tpl.html');
    } else if ($tpl_idx != 2 && $tpl_idx != 3 && $tpl_idx != 1) {
      // prevent escaping when not using html templates
      $tpl->smarty->default_modifiers = array();
    }

    $getFunction = 'getListing';
    if ((APP_SOLR_SWITCH == 'ON' || APP_ES_SWITCH == 'ON')) {
      $getFunction = 'getSearchListing';
    }

    $options['tpl_idx'] = $tpl_idx;
    $tpl->assign("options", $options);

    $cat = $params['cat'];
    $browse = $params['browse'];
    $collection_pid = $params['collection_pid'];
    $community_pid = $params['community_pid'];

    if (!empty($collection_pid)) {
      $pid = $collection_pid;
      $browse_mode = "collection";
    } elseif (!empty($community_pid)) {
      $pid = $community_pid;
      $browse_mode = "community";
    } else {
      $pid = '';
      $browse_mode = "list";
    }
    $tpl->assign("pid", $pid);
    $tpl->assign("browse_mode", $browse_mode);
    $sort_by = $options["sort_by"];
    $operator = $options["operator"];

    if (empty($operator)) {
      $operator = "AND";
    }

    /*
     * These options are used in a dropdown box to allow the
     * user to sort a list
     */
    $sort_by_list = array(
        "searchKey" . Search_Key::getID("Title") => 'Title',
        "searchKey" . Search_Key::getID("File Downloads") => 'File Downloads',
        "searchKey" . Search_Key::getID("Date") => 'Date',
        "searchKey" . Search_Key::getID("Created Date") => 'Created Date',
        "searchKey" . Search_Key::getID("Updated Date") => 'Updated Date',
        "searchKey" . Search_Key::getID("Sequence") => 'Sequence',
        "searchKey" . Search_Key::getID("Thomson Citation Count") => 'Thomson Citation Count',
        "searchKey" . Search_Key::getID("Scopus Citation Count") => 'Scopus Citation Count'
    );

    if (defined('ALTMETRIC_API_ENABLED') && ALTMETRIC_API_ENABLED == 'true') {
      $sort_by_list["searchKey" . Search_Key::getID("Altmetric Score")] = "Altmetric Score";
    }

    if (Auth::isValidSession($session)) {
      $sort_by_list["searchKey" . Search_Key::getID("GS Citation Count")] = "Google Scholar Citation Count";
    }


    if (($cat == 'search' || $cat == 'all_fields' || $cat == 'quick_filter')) {
      $sort_by_list['searchKey0'] = "Search Relevance";
      if (($params["sort_by"]) == "") {
        $sort_by = "searchKey0";
      }

      // if searching by Title, Abstract, Keywords and sort order not specifically set in the querystring
      // (from a manual sort order change) than make search revelance sort descending
      if (!is_numeric($params["sort_order"]) && ($sort_by == "searchKey0")) {
        $options["sort_order"] = 1; // DESC relevance
      }
    }

    // Default Sort
    if (!array_key_exists($sort_by, $sort_by_list)) {
      $sort_by = "searchKey" . Search_Key::getID("Title");
    }

    $list_info = array();

    //get the bulk change workflows
    if ($username) {
      $bulk_workflows = WorkflowTrigger::getAssocListByTrigger("-1", WorkflowTrigger::getTriggerId('Bulk Change'));
      $bulk_search_workflows = WorkflowTrigger::getAssocListByTrigger("-1", WorkflowTrigger::getTriggerId('Bulk Change Search'));
      $tpl->assign("bulk_workflows", $bulk_workflows);
      $tpl->assign("bulk_search_workflows", $bulk_search_workflows);
    }
    // if it is
    if (!empty($custom_view_pid) && empty($collection_pid) && empty($community_pid) && ($browse != "latest") && ($browse != "year") && (($browse != "author") && ($browse != "author_id")) && ($browse != "depositor") && ($browse != "subject") && ($cat != "quick_filter")) {
      $community_pid = $custom_view_pid;
    }

    if (!empty($collection_pid)) {

      $title = Record::getSearchKeyIndexValue($collection_pid, "Title");
      $citation = Record::getCitationIndex($collection_pid);
      $tpl->assign("list_heading", htmlspecialchars($title));
      $tpl->assign("list_heading_citation", "List of Records in " . $citation);
      $log->debug('List a collection');

      // list a collection
      // first check the user has view rights over the collection object
      $record = new RecordObject($collection_pid);
      if ($record->checkExists() && !($record->isDeleted())) {

        $canList = $record->canList(true);

        $tpl->assign("isLister", $canList);

        if ($canList) {
          $tpl->assign("list_type", "collection_records_list");

          $tpl->assign("xdis_id", Record::getSearchKeyIndexValue($collection_pid, "Display Type"));
          $parents = Record::getParentsDetails($collection_pid);

          $tpl->assign("parents", $parents);
          $userPIDAuthGroups = AuthIndex::getIndexAuthRoles($collection_pid);
          $isCreator = @$userPIDAuthGroups['isCreator'] == 1;
          $tpl->assign("isCreator", $isCreator);
          $isEditor = @$userPIDAuthGroups['isEditor'] == 1;
          $tpl->assign("isEditor", $isEditor);
          $options = Search_Key::stripSearchKeys($options);

          $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
          $filter["searchKey" . Search_Key::getID("isMemberOf")] = $collection_pid;
          $operator = 'AND';
          $use_faceting = TRUE;
          $list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter, $operator, $use_faceting);
          $list_info = $list["info"];
          $facets = @$list['facets'];
          $snips = @$list['snips'];
          $list = $list["list"];

          $tpl->assign("collection_pid", $collection_pid);
          $childXDisplayOptions = XSD_Display::getValidXSDDisplay($collection_pid);

          if (count($childXDisplayOptions) > 0) {
            $tpl->assign("childXDisplayOptions", $childXDisplayOptions);
          } else {
            $tpl->assign("childXDisplayOptions", 0);
          }

          // Build a list of workflow entry uri's for the available
          // display types that can be created for this collection:

          $tpl->assign("create_actions", false);
          if (APP_API) {
            $actions = array();
            foreach ($childXDisplayOptions as $xdis_id => $name) {
              $url = "/workflow/new.php?collection_pid=$pid&xdis_id=$xdis_id&xdis_id_top=$xdis_id&wft_id=346";
              $actions[] = array(
                  'url' => $url,
                  'name' => "Create $name",
                  'xdis_id' => $xdis_id,
              );
            }
            $tpl->assign("create_actions", $actions);
          }

          unset($params['collection_pid']);

          $tpl->assign('url', Misc::query_string_encode($params));

        } else {
          $tpl->assign("show_not_allowed_msg", true);
        }
      } else {
        if (APP_API) {
          API::reply(404, API::makeResponse(404, 'Not found'), APP_API);
          exit;
        } else {
          header("Status: 404 Not Found");
          $tpl->assign('not_exists', true);
        }
      }


    } elseif (!empty($community_pid)) {

      $title = Record::getSearchKeyIndexValue($community_pid, "Title");
      $citation = Record::getCitationIndex($community_pid);

      $tpl->assign("list_heading", "List of Collections in " . htmlspecialchars($title));
      $tpl->assign("list_heading_citation", "List of Collections in " . $citation);

      $log->debug('List collections in a community');

      $sort_by = "searchKey" . Search_Key::getID("Title");

      // list collections in a community
      // first check the user has view rights over the collection object
      $record = new RecordObject($community_pid);
      if ($record->checkExists() && !($record->isDeleted())) {

        $canView = $record->canView(true);
        $tpl->assign("isViewer", $canView);
        if ($canView) {
          $tpl->assign("community_pid", $community_pid);
          $userPIDAuthGroups = AuthIndex::getIndexAuthRoles($community_pid);
          $isCreator = @$userPIDAuthGroups['isCreator'] == 1;
          $tpl->assign("isCreator", $isCreator);
          $isEditor = @$userPIDAuthGroups['isEditor'] == 1;
          $tpl->assign("isEditor", $isEditor);
          $options = Search_Key::stripSearchKeys($options);

          $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
          $filter["searchKey" . Search_Key::getID("isMemberOf")] = $community_pid; //
          $list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter);

          $list_info = $list["info"];
          $facets = @$list['facets'];
          $snips = @$list['snips'];
          $list = $list["list"];

          $tpl->assign("list_type", "collection_list");

          $childXDisplayOptions = Record::getSearchKeyIndexValue($community_pid, "XSD Display Option");
          if (count($childXDisplayOptions) > 0) {
            $tpl->assign("childXDisplayOptions", $childXDisplayOptions);
          } else {
            $tpl->assign("childXDisplayOptions", 0);
          }
        } else {
          $tpl->assign("show_not_allowed_msg", true);
        }
      } else {
        if (APP_API) {
          API::reply(404, API::makeResponse(404, 'Not found'), APP_API);
          exit;
        } else {
          header("Status: 404 Not Found");
          $tpl->assign('not_exists', true);
        }
      }


      //Remove these sort options when viewing a list of community
      unset($sort_by_list["searchKey" . Search_Key::getID("File Downloads")]);
      unset($sort_by_list["searchKey" . Search_Key::getID("Sequence")]);
      unset($sort_by_list["searchKey" . Search_Key::getID("Date")]);
    } elseif ($browse == "favourites") {
      Auth::checkAuthentication(APP_SESSION, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
      $tpl->assign("list_heading", "Starred Records");

      $filter = array();
      $filter["searchKey" . Search_key::getID("Object Type")] = 3;
      $starredPids = Favourites::getStarred();

      /* Only the starred records */
      if (count($starredPids) > 0) {
        if ((APP_SOLR_SWITCH == 'ON' || APP_ES_SWITCH == 'ON')) {
          $filter["manualFilter"] = "(pid_t:('" . str_replace(':', '\:', implode("' OR '", $starredPids)) . "'))";
        } else {
          $filter["searchKey" . Search_Key::getID("Pid")]['override_op'] = 'OR';
          foreach ($starredPids as $starredPid) {
            $filter["searchKey" . Search_Key::getID("Pid")][] = $starredPid;
          }
        }
      } else {
        if ((APP_SOLR_SWITCH == 'ON' || APP_ES_SWITCH == 'ON')) {
          $filter["manualFilter"] = "(pid_t:('INVALID_PID'))";
        } else {
          $filter["searchKey" . Search_Key::getID("Pid")][] = 'INVALID_PID';
        }
      }

      //If favourites then search options should not be used to restrict the results only order so set searchKey parameter to empty
      $options['searchKey0'] = '';

      $list = Record::$getFunction($options, $approved_roles = array("Lister"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter);
      $list_info = $list["info"];
      $list = $list["list"];
      $tpl->assign("browse_type", "browse_favourites");

      $tpl->assign("list_type", "all_records_list");
      $tpl->assign("active_nav", "favourites");

    } elseif ($browse == "latest") {
      $tpl->assign("list_heading", "Browse By Latest Additions");

      $log->debug('Latest');

      $sort_by_list["searchKey" . Search_Key::getID("Created Date")] = 'Created Date';

      // Remove these sort options when viewing the latest records
      unset($sort_by_list["searchKey" . Search_Key::getID("Title")]);
      unset($sort_by_list["searchKey" . Search_Key::getID("File Downloads")]);
      unset($sort_by_list["searchKey" . Search_Key::getID("Sequence")]);
      unset($sort_by_list["searchKey" . Search_Key::getID("Date")]);

      $options = array();

      $options["sort_order"] = 1;
      $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only

      $sort_by = "searchKey" . Search_Key::getID("Created Date");

      $list = Record::$getFunction($options, $approved_roles = array("Lister"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter);
      $list_info = $list["info"];
      $list = $list["list"];

      $tpl->assign("browse_type", "browse_latest");

      $tpl->assign("today", date("Y-m-d"));
      $tpl->assign("today_day_name", date("l"));
      $tpl->assign("yesterday", date("Y-m-d", time() - 86400));
      $tpl->assign("last", "Last ");
      $tpl->assign("list_type", "all_records_list");

    } elseif ($browse == "year") {
      $tpl->assign("list_heading", "List of Records");

      $log->debug('Browse by year');
      // browse by year
      $year = Lister::getValue($params, 'year');
      if (is_numeric($year)) {

        $options = Search_Key::stripSearchKeys($options);

        $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
        $filter["searchKey" . Search_Key::getID("Date")] = array();
        $filter["searchKey" . Search_Key::getID("Date")]["filter_type"] = "between";
        $filter["searchKey" . Search_Key::getID("Date")]["filter_enabled"] = 1;
        $filter["searchKey" . Search_Key::getID("Date")]["start_date"] = $year . "-01-01";
        $filter["searchKey" . Search_Key::getID("Date")]["end_date"] = $year . "-12-31";
        $list = Record::getListing($options, $approved_roles = array("Lister"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter);
        $list_info = $list["info"];
        $list = $list["list"];

        $tpl->assign("year", $year);
        $tpl->assign("browse_heading", "Browse By Year " . htmlspecialchars($year));
      } else {
        $list = Collection::listByAttribute($pager_row, $rows, "Date", $sort_by);
        $list_info = $list["info"];
        $list = $list["list"];
        $tpl->assign("browse_heading", "Browse By Year");

        // Remove these sort options when viewing a list of subjects
        unset($sort_by_list["searchKey" . Search_Key::getID("File Downloads")]);
        unset($sort_by_list["searchKey" . Search_Key::getID("Sequence")]);
        unset($sort_by_list["searchKey" . Search_Key::getID("Date")]);

        // Remove 'citation' and 'classic' display option when viewing a list of subjects
        unset($tpls[4]);
        unset($tpls[5]);
      }
      $tpl->assign("browse_type", "browse_year");

    } elseif (($browse == "author") || ($browse == "author_id") || ($browse == 'author_refine')) {
      $log->debug('Browse by Author/Contributor/Editor');
      // browse by author
      if ($browse == "author") {
        if (strlen(Lister::getValue($params, 'author')) != 1) {
          $author = Lister::getValue($params, 'author');
        }
      }

      if ($browse == 'author_refine') {
        $author_refine = Lister::getValue($params, 'author_refine');
      }

      if ($browse == "author_id")
        $author_id = Lister::getValue($params, 'author_id');

      if (!empty($author_id)) {
        $options = Search_Key::stripSearchKeys($options);

        $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
        $filter["manualFilter"] = " (author_id_mi:" . $author_id . " OR contributor_id_mi:" . $author_id . ") ";
        $author = Author::getFullname($author_id);
        $list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter);

        $alternativeAuthorNamesList = Author::getAlternativeNamesList($author_id);

        $namesList = array();
        if (count($alternativeAuthorNamesList) > 1) {
          foreach ($alternativeAuthorNamesList as $name => $paperCount) {
            $namesList[] = '<a href="' . APP_RELATIVE_URL . 'list/author_refine/' . urlencode($name) . '">' . $name . '</a> (' . $paperCount . ')';
          }
        }

        $tpl->assign('alternativeAuthorNamesList', $namesList);

        $list_info = $list["info"];
        $list = $list["list"];

        $tpl->assign("author_id", $author_id);
        $tpl->assign("browse_heading", "Browse By Author/Contributor/Editor ID - " . htmlspecialchars($author));
        $tpl->assign("list_heading", "Browse By Author/Contributor/Editor ID - " . htmlspecialchars($author));
      } elseif (!empty($author)) {
        $options = Search_Key::stripSearchKeys($options);
        $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
        $filter["manualFilter"] = " ((author_mt:" . $author . ") OR (contributor_mt:" . $author . ")) ";
        $list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter);
        $list_info = $list["info"];
        $list = $list["list"];

        $tpl->assign("author", $author);
        $tpl->assign("browse_heading", "Browse By Author/Contributor/Editor Name - " . htmlspecialchars($author));
        $tpl->assign("list_heading", "Browse By Author/Contributor/Editor Name - " . htmlspecialchars($author));
      } elseif (!empty($author_refine)) {
        $options = Search_Key::stripSearchKeys($options);
        $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
        $filter["manualFilter"] = " (author_mt_exact:(\"" . str_replace("+", " ", $author_refine) . "\") OR contributor_mt_exact:(\"" . str_replace("+", " ", $author_refine) . "\")) ";


        $list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter, 'AND', false, false, true); // do an exact match

        $list_info = $list["info"];
        $list = $list["list"];

        $tpl->assign("author_refine", $author_refine);
        $tpl->assign("browse_heading", "Refine By Author/Contributor/Editor Name - \"{$author_refine}\"");
        $tpl->assign("list_heading", "Refine By Author/Contributor/Editor Name - \"{$author_refine}\"");
      } else {

        if ($browse == "author_id") {
          $list = array();
          $list_info = $list["info"];
          $list = $list["list"];

          $tpl->assign("browse_heading", "Browse By " . APP_NAME . " Author/Contributor/Editor ID");
          $tpl->assign("list_heading", "Browse By " . APP_NAME . " Author/Contributor/Editor ID");
        } else {
          $list = array();
          $list_info = $list["info"];
          $list = $list["list"];

          $tpl->assign("browse_heading", "Browse By Author/Contributor/Editor Name");
          $tpl->assign("list_heading", "Browse By Author/Contributor/Editor Name");
        }

        // Remove these sort options when viewing a list of authors
        unset($sort_by_list["searchKey" . Search_Key::getID("File Downloads")]);
        unset($sort_by_list["searchKey" . Search_Key::getID("Sequence")]);
        unset($sort_by_list["searchKey" . Search_Key::getID("Date")]);

        // Remove 'citation' display option when viewing a list of authors
        unset($tpls[4]);
      }
      $tpl->assign("browse_type", "browse_author");
      $tpl->assign("alphabet_list", Misc::generateAlphabetArray());

    } elseif ($browse == "depositor") {
      $tpl->assign("list_heading", "Browse By Depositor");

      $log->debug('Browse by depositor');
      // browse by depositor
      $depositor = Lister::getValue($params, 'depositor');
      $depositor_fullname = User::getFullName($depositor);

      if (!empty($depositor)) {
        $options = Search_Key::stripSearchKeys($options);
        $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
        $filter["searchKey" . Search_Key::getID("Depositor")] = $depositor; //
        $list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter);
        $list_info = $list["info"];
        $list = $list["list"];

        $tpl->assign("depositor", $depositor);
        $tpl->assign("browse_heading", "Browse By Depositor - " . htmlspecialchars($depositor_fullname));
        $tpl->assign("list_heading", "Browse By Depositor - " . htmlspecialchars($depositor_fullname));
      } else {
        $list = Collection::listByAttribute($pager_row, $rows, "Depositor", $sort_by);
        $list_info = $list["info"];
        $list = $list["list"];

        $tpl->assign("browse_heading", "Browse By Depositor");


        // Remove these sort options when viewing a list of Depositors
        unset($sort_by_list["searchKey" . Search_Key::getID("File Downloads")]);
        unset($sort_by_list["searchKey" . Search_Key::getID("Sequence")]);
        unset($sort_by_list["searchKey" . Search_Key::getID("Date")]);

        // Remove 'citation' display option when viewing a list of depositors
        unset($tpls[4]);
      }
      $tpl->assign("browse_type", "browse_depositor");

    } elseif ($browse == "mypubs") {
      $author_id = $params['author_id'];
      $authorDetails = Author::getDetails($author_id);
      //prefer the staff username if possible
      if (!empty($authorDetails['aut_org_username'])) {
        $authorUsername = $authorDetails['aut_org_username'];
      } else {
        $authorUsername = $authorDetails['aut_student_username'];
      }
      $tpl->assign("author_username", $authorUsername);
      $authorDetails["aut_publons_id"] = Publons::returnOrcidIfHasPublons($author_id);
      $tpl->assign("list_heading", "Publications by " . htmlspecialchars($authorDetails["aut_display_name"]));

      $log->debug('Browse MyPubs');

      $current_row = 0;
      $max = 9999999;
      $operator = "AND";
      $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
      $filter["searchKey" . Search_key::getID("Object Type")] = 3; //exclude communities and collections
      $filter["manualFilter"] = " (author_id_mi:" . $authorDetails["aut_id"] . " OR contributor_id_mi:" . $authorDetails["aut_id"] . ") "; // enforce display type X only

      if ($tpl_idx == 0) {
        $use_faceting = true;
        $use_highlighting = false;
        $simple = true;
        $citationCache = true;
      } else {
        $use_faceting = false;
        $use_highlighting = false;
        $simple = false;
        $citationCache = false;
      }
      $xdis_version = "MODS 1.0";


      $list = Record::getListing($options, array(9, 10), $current_row, $max, $sort_by, $simple, $citationCache, $filter, $operator, $use_faceting, $use_highlighting);
      $list_info = $list["info"];
      $facets = @$list['facets'];
      $list = $list["list"];

      $otherDisplayTypes = array();
      $otherDisplaySubTypes = array();
      $simple = false; //need the extra details for the actual results
      $citationCache = false; //need the extra details for the actual results

      if ($tpl_idx == 0) {
        $options = array();
        $sort_by = "searchKey" . Search_Key::getID("Date");
        $options["sort_order"] = 1; // DESC date
        $options["sort_by"] = $sort_by; // DESC date
        $use_faceting = false;

        $book_xdis_id = XSD_Display::getXDIS_IDByTitleVersion("Book", $xdis_version);
        if (is_numeric($book_xdis_id)) {
          array_push($otherDisplayTypes, $book_xdis_id);
          $filter["searchKey" . Search_Key::getID("Display Type")] = $book_xdis_id; // enforce display type X only
          $bookList = Record::getListing($options, array(9, 10), $current_row, $max, $sort_by, $simple, $citationCache, $filter, $operator, $use_faceting, $use_highlighting);
          $bookListInfo = $bookList["info"];
          $bookList = $bookList["list"];
        } else {
          $bookListInfo = array();
          $bookList = array();
        }

        $bc_xdis_id = XSD_Display::getXDIS_IDByTitleVersion("Book Chapter", $xdis_version);
        if (is_numeric($bc_xdis_id)) {
          array_push($otherDisplayTypes, $bc_xdis_id);
          $filter["searchKey" . Search_Key::getID("Display Type")] = $bc_xdis_id; // enforce display type X only
          $bcList = Record::getListing($options, array(9, 10), $current_row, $max, $sort_by, $simple, $citationCache, $filter, $operator, $use_faceting, $use_highlighting);
          $bcListInfo = $bcList["info"];
          $bcList = $bcList["list"];
        } else {
          $bcListInfo = array();
          $bcList = array();
        }

        $ja_xdis_id = XSD_Display::getXDIS_IDByTitleVersion("Journal Article", $xdis_version);
        if (is_numeric($ja_xdis_id)) {

          // Comment out: Do not exclude Journal Articles from Other Listing, because we need the erratum records
//                    array_push($otherDisplayTypes, $ja_xdis_id);
          // Grab erratum subtype under Journal Article
          $otherDisplaySubTypes[] = "(display_type_i :" . $ja_xdis_id . " AND !subtype_t : \"Correction/erratum\")";

          $filterJA = $filter;
          $filterJA["searchKey" . Search_Key::getID("Display Type")] = $ja_xdis_id; // enforce display type X only
          // Filter OUT Correction/erratum records
          $filterJA["manualFilter"] .= " AND -(subtype_t:\"Correction/erratum\")";

          $jaList = Record::getListing($options, array(9, 10), $current_row, $max, $sort_by, $simple, $citationCache, $filterJA, $operator, $use_faceting, $use_highlighting);
          $jaListInfo = $jaList["info"];
          $jaList = $jaList["list"];
        } else {
          $jaListInfo = array();
          $jaList = array();
        }

        $cp_xdis_id = XSD_Display::getXDIS_IDByTitleVersion("Conference Paper", $xdis_version);
        if (is_numeric($cp_xdis_id)) {
          array_push($otherDisplayTypes, $cp_xdis_id);
          $filter["searchKey" . Search_Key::getID("Display Type")] = $cp_xdis_id; // enforce display type X only
          $cpList = Record::getListing($options, array(9, 10), $current_row, $max, $sort_by, $simple, $citationCache, $filter, $operator, $use_faceting, $use_highlighting);
          $cpListInfo = $cpList["info"];
          $cpList = $cpList["list"];
        } else {
          $cpListInfo = array();
          $cpList = array();
        }

        $ci_xdis_id = XSD_Display::getXDIS_IDByTitleVersion("Conference Item", $xdis_version);
        if (is_numeric($ci_xdis_id)) {
          array_push($otherDisplayTypes, $ci_xdis_id);
          $filter["searchKey" . Search_Key::getID("Display Type")] = $ci_xdis_id; // enforce display type X only
          $ciList = Record::getListing($options, array(9, 10), $current_row, $max, $sort_by, $simple, $citationCache, $filter, $operator, $use_faceting, $use_highlighting);
          $ciListInfo = $ciList["info"];
          $ciList = $ciList["list"];
        } else {
          $ciListInfo = array();
          $ciList = array();
        }

        // Other displays
        // Records that meet the following criteria:
        // - Records that are not under Display Types loaded above, except Journal Article
        // - Records that are Journal Article with subtype Correction/erratum
        unset($filter["searchKey" . Search_Key::getID("Display Type")]);
        $filterOther = $filter;

        // Note the NOT operator "!".
        // The purpose is to get SOLR to return records that do not meet the search terms
        $filterOther["manualFilter"] .= " !(";

        // Enforce certain Display Types only
        $filterOther["manualFilter"] .= "display_type_i:(" . implode(" OR ", $otherDisplayTypes) . ")";

        // Include Journal Article with subtype Correction/erratum records
        $filterOther["manualFilter"] .= " OR " . implode($otherDisplaySubTypes);

        $filterOther["manualFilter"] .= ")";

        $otherList = Record::getListing($options, array(9, 10), $current_row, $max, $sort_by, $simple, $citationCache, $filterOther, $operator, $use_faceting, $use_highlighting);
        $otherListInfo = $otherList["info"];
        $otherList = $otherList["list"];

        $masterList = array(array($bookList, $bookListInfo, "Books"),
            array($bcList, $bcListInfo, "Book Chapters"),
            array($jaList, $jaListInfo, "Journal Articles"),
            array($cpList, $cpListInfo, "Conference Papers"),
            array($ciList, $ciListInfo, "Conference Items"),
            array($otherList, $otherListInfo, "All Others")
        );
      } else {
        $masterList = array();
      }

      if ($tpl_idx == 0) {
        $tpl_file = "list.tpl.html";
        $tpl->assign("cv_content", "list_mypubs.tpl.html");
        $tpl->setTemplate($tpl_file);
      } else {
        $tpl_file = $tpls[$tpl_idx]['file'];
        $tpl->assign("template_mode", $tpl_idx);
        $tpl->setTemplate($tpl_file);
      }

      $tpl->assign("masterList", $masterList);

      $tpl->assign("researcherID", $authorDetails["aut_researcher_id"]);
      $tpl->assign("aut_people_australia_id", $authorDetails['aut_people_australia_id']);
      $tpl->assign("aut_scopus_id", $authorDetails['aut_scopus_id']);
      $tpl->assign("aut_orcid_id", $authorDetails['aut_orcid_id']);
      $tpl->assign("aut_publons_id", $authorDetails['aut_publons_id']);
      $tpl->assign("aut_google_scholar_id", $authorDetails['aut_google_scholar_id']);
      $tpl->assign("username", $username); // default to logged in user

      $tpl->assign("list_type", "mypubs_list");

      //all
      $tpl->assign("list", $list);
      $tpl->assign("list_info", $list_info);
      $tpl->assign('facets', $facets);
      $tpl->assign("author_id", $author_id);
      $tpl->assign("authorDetails", $authorDetails);
      $tpl->assign("active_nav", "mypubs");

    } elseif ($browse == "subject") {
      $tpl->assign("list_heading", "List of Subject Classifications Records");

      $log->debug('Browse by subject');
      // browse by subject
      $parent_id = Lister::getValue($params, 'parent_id');

      if (is_numeric($parent_id)) {
        $subject_list = Controlled_Vocab::getList($parent_id);
        $treeIDs = Controlled_Vocab::getAllTreeIDs($parent_id);
        $subject_count = Collection::getCVCountSearch($treeIDs, $parent_id);

        $options = Search_Key::stripSearchKeys($options);
        $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
        $cvParents = Controlled_Vocab::getParentListFullDisplay($parent_id);
        if (trim($cvParents[0]['cvo_title']) == 'Fields of Research') {
          $filter["searchKey" . Search_Key::getID("Fields of Research")] = $parent_id;
        } elseif (trim($cvParents[0]['cvo_title']) == 'Socio-Economic Objective (2008)') {
          $filter["searchKey" . Search_Key::getID("SEO Code")] = $parent_id;
        } else {
          $filter["searchKey" . Search_Key::getID("Subject")] = $parent_id;
        }

        $list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter);

        $list_info = $list["info"];
        $list = $list["list"];

        $tpl->assign('parent_id', $parent_id);

      } else {
        $subject_list = Controlled_Vocab::getList();

        // Remove these sort options when viewing a list of subjects
        unset($sort_by_list["searchKey" . Search_Key::getID("File Downloads")]);
        unset($sort_by_list["searchKey" . Search_Key::getID("Sequence")]);
        unset($sort_by_list["searchKey" . Search_Key::getID("Date")]);

        // Remove 'citation' display option when viewing a list of subjects
        unset($tpls[4]);
      }

      $breadcrumb = Controlled_Vocab::getParentAssocListFullDisplay($parent_id);
      $breadcrumb = Misc::array_merge_preserve($breadcrumb, Controlled_Vocab::getAssocListByID($parent_id));
      $newcrumb = array();
      foreach ($breadcrumb as $key => $data) {
        array_push($newcrumb, array("cvo_id" => $key, "cvo_title" => $data));
      }
      $max_breadcrumb = (count($newcrumb) - 1);

      $tpl->assign("max_subject_breadcrumb", $max_breadcrumb);
      $tpl->assign("subject_breadcrumb", $newcrumb);
      $tpl->assign("list_type", "all_records_list");
      $tpl->assign("parent_id", $parent_id);
      $tpl->assign("subject_list", $subject_list);
      $tpl->assign("subject_count", $subject_count);
      $tpl->assign("browse_heading", "Browse By Subject Classifications Records");

      $tpl->assign("browse_type", "browse_subject");

    } elseif ($cat == "quick_filter") { // Advanced Search
      $log->debug('Advanced search');
      $searchKey_join = Record::buildSearchKeyFilterSolr($options, $sort_by, $operator, false);

      include_once(APP_INC_PATH . "class.spell.php");
      include_once(APP_INC_PATH . "class.language.php");

      if (empty($sort_by)) {
        if ($options["searchKey0"] == "") {
          $sort_by = "searchKey" . Search_Key::getID("Title");
        } else {
          $sort_by = "searchKey0"; // Search Relevance
          $options["sort_dir"] = 1;
        }
      }

      // search Fez

      // enforce certain search parameters
      // enforce published records only
      $options["searchKey" . Search_Key::getID("Status")] = array(
          'override_op' => 'AND',
          'value' => 2,
      );
      $filter["searchKey" . Search_Key::getID("Status")] = array(
          'override_op' => 'AND',
          'value' => 2,
      );

      // Turn these on for advanced search
      $use_faceting = true;
      $use_highlighting = true;

      $list = Record::$getFunction($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter, $operator, $use_faceting, $use_highlighting);

      $spell = new spellcheck(APP_DEFAULT_LANG);

      if ($spell && array_key_exists('search_keys', $params) && is_array($params['search_keys'])) {
        $spell_suggest = $spell->query_suggest($params['search_keys'][0]);

        // Did pspell return any suggestions?
        if ($spell_suggest) {
          // Replace search_key[0]={search} with search_key[0]={suggestion}
          // search key 0 will be 'Title, Abstract, Keywords'
          $tpl->assign("spell_suggest", $spell_suggest);

          $exclude[] = 'search_keys';
          $tpl->assign('spell_suggest_url', Misc::query_string_encode($params, $exclude) . '&search_keys[0]=' . urlencode($spell_suggest));
          array_pop($exclude);
        }
      }
      $list_info = @$list["info"];
      $terms = @$list_info['search_info'];
      $facets = @$list['facets'];
      $snips = @$list['snips'];
      $list = @$list["list"];
      $tpl->assign("list_heading", "Search Results ($terms)");
      // KJ@ETH
      $tpl->assign("major_function", "search");
      $q = "";
      if (array_key_exists('search_keys', $params) && is_array($params['search_keys'])) {
        $q = htmlspecialchars($params['search_keys'][0]);
      }
      $tpl->assign("q", $q);

      $tpl->assign("list_type", "all_records_list");
      $tpl->assign("previousSearch", $params['search_keys'][0]);
    } else {
      $tpl->assign("list_type", "community_list");
      $tpl->assign("list_heading", "List of Communities");

      $log->debug('Communities');
      $xdis_id = Community::getCommunityXDIS_ID();
      $tpl->assign("xdis_id", $xdis_id);
      $options = Search_Key::stripSearchKeys($options);
      $filter["searchKey" . Search_Key::getID("Status")] = 2; // enforce published records only
      $filter["searchKey" . Search_Key::getID("Object Type")] = 1; // enforce communities only
      $list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter);
      $list_info = $list["info"];
      $list = $list["list"];


      // Remove these sort options when viewing a list of communities
      unset($sort_by_list["searchKey" . Search_Key::getID("File Downloads")]);
      unset($sort_by_list["searchKey" . Search_Key::getID("Sequence")]);
      unset($sort_by_list["searchKey" . Search_Key::getID("Date")]);


      // Remove 'citation' and 'classic' display option when viewing a list of communities
      unset($tpls[4]);
      unset($tpls[5]);
    }

    // We dont want to display facets that a user has already searched by
    if (isset($facets)) {
      foreach ($facets as $sek_id => $facetData) {
        if (!empty($options['searchKey' . $sek_id])) {
          unset($facets[$sek_id]);
        }
      }
    }

    if ($tpl_idx == 1) {
      // Add the Research Details to the array
      $list = Record::getResearchDetailsbyPIDS($list);
    }

    // Star muxing time
    $stars = Favourites::getStarred();
    if (is_array($list)) {
      foreach ($list as &$record) {
        foreach ($stars as $star) {
          if ($record['rek_pid'] == $star) {
            $record['starred'] = true;
          }
        }
      }
    }

    $tpl->assign('facets', $facets);

    $snips = $zf->filter($snips);
    $tpl->assign('snips', $snips);
    $tpl->assign('rows', $rows);
    $tpl->assign('tpl_list', array_map(create_function('$a', 'return $a[\'title\'];'), $tpls));
    $tpl->assign('browse', $browse);
    $tpl->assign('sort_by_list', $sort_by_list);
    $tpl->assign("cycle_colours", "#FFFFFF," . "#" . APP_CYCLE_COLOR_TWO);
    $tpl->assign('sort_by_default', $sort_by);
    $tpl->assign("eserv_url", APP_BASE_URL . "view/");
    $tpl->assign('sort_order', $options["sort_order"]);
    $tpl->assign("list", $list);
    $tpl->assign("list_info", $list_info);

    $url = (!empty($_SERVER['HTTPS'])) ? "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $tpl->assign("search_favourited", Favourites::isStarredSearch($url));

    // Hack to get SCRIPT_URL without querystrings.
    // Usually we could get this info from $_SERVER['SCRIPT_URL'], but can't since
    // we are doing rewrite rules on a per-directory basis via .htaccess file
    $PAGE_URL = preg_replace('/(\?.*)/', '', $_SERVER['REQUEST_URI']);

    // When generating the URL's for sorting etc do not include
    // these variables as they will already be in the PAGE_URL
    $exclude = array(
      /*'pager_row', */ /* This breaks CSV / Excel exports. I'm removing it -- LK */
        'browse',
        'value',
        'community_pid',
    );

    if (count($params) > 0) {

      $exclude[] = 'rows';
      $exclude[] = 'pager_row';
      $tpl->assign('url_wo_rows', Misc::query_string_encode($params, $exclude));
      array_pop($exclude);

      $exclude[] = 'tpl';
      $tpl->assign('url_wo_tpl', Misc::query_string_encode($params, $exclude));
      array_pop($exclude);

      $exclude[] = 'sort';
      $exclude[] = 'sort_by';
      $tpl->assign('url_wo_sort', Misc::query_string_encode($params, $exclude));
    }

    $tpl->assign('PAGE_URL', $PAGE_URL);

    if ($tpl->smarty->getTemplateVars('active_nav') == '') {
      $tpl->assign("active_nav", "list");
    }
    if ($username) {
      $tpl->registerNajax(NAJAX_Client::register('NajaxRecord', APP_RELATIVE_URL . 'ajax.php') . "\n"
          . NAJAX_Client::register('Suggestor', APP_RELATIVE_URL . 'ajax.php') . "\n");
    }
    // If most results have thumbnails and there is no template set in the querystring than force the image gallery template
    if (!is_numeric($params['tpl']) && $tpl_idx != 3) {
      if (array_key_exists('thumb_ratio', $list_info) && is_numeric($list_info['thumb_ratio'])) {
        if ($list_info['thumb_ratio'] > 0.5) {
          $tpl_idx = 6;
        }
      }
    }
    $tpl_file = $tpls[$tpl_idx]['file'];
    $tpl->setTemplate($tpl_file);
    $tpl->assign("template_mode", $tpl_idx);
    $tpl->assign("use_json", true);

    if (APP_API) {
      $tpl->assign("rows", $rows);
      $tpl->assign("pager_row", $pager_row);
    }

    if ($display) {
      if ($jsonIt || APP_API_JSON) {
        $xml = $tpl->getTemplateContents();
        $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        echo json_encode($xml);
      } else {
        $tpl->displayTemplate();
      }
    }

    return compact('list', 'list_info');
  }


  function getValue($params, $varName)
  {

    if (isset($params[$varName]) && !empty($params[$varName])) {
      return $params[$varName];
    } elseif (isset($params['value']) && !empty($params['value'])) {
      return $params['value'];
    }

    return false;
  }


  public static function checkAliasController()
  {

    $uri = strtolower($_SERVER['REQUEST_URI']);
    $uri = str_replace(" ", "_", $uri);
    $uri = preg_replace('/(.*)\?(.*)/', "$1", $uri);
    $uri = preg_replace("/[^a-z0-9_]/", "", $uri);

    if (empty($uri)) {
      return false;
    }

    //check if it is an author username
    $authorDetails = Author::getDetailsByUsername($uri);
    $params = $_GET;

    if (count($authorDetails) != 0 && is_numeric($authorDetails['aut_id'])) {
      $params['browse'] = 'mypubs';
      $params['author_id'] = $authorDetails['aut_id'];
      Lister::getList($params, true);
      return true;
    }

    //Lets see if it's a saved search and also merge with get parameters (Since user might reorder saved search)
    $searchAlias = Favourites::getSearchAliasURL($uri);
    if ($searchAlias) {
      //Puts the saved get values into a array
      parse_str(parse_url($searchAlias, PHP_URL_QUERY), $savedParams);
      $mergedSearchParams = array_merge($savedParams, $params);
      Lister::getList($mergedSearchParams, true);
      return true;
    }
    return false;
  }
}
