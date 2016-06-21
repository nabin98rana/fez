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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+

include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.eventum.php");
include_once(APP_INC_PATH . "class.mail.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.datastream.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.page.php");

/**
 * MyResearch
 * This is a static class that handles My Research functionality, such as record correction flagging.
 */
class MyResearch
{

    function addDatasetLink(&$tpl) {
      $wft_id = WorkflowTrigger::getWorkflowTriggerIDByTitle("Submission of Dataset");
      $dataset_xdis_id = XSD_Display::getXDIS_IDByTitle('Data Collection');
      $add_dataset_url = APP_RELATIVE_URL . "workflow/new.php?xdis_id=".$dataset_xdis_id."&pid=".APP_MY_RESEARCH_NEW_DATASETS_COLLECTION.'&cat=select_workflow&wft_id='.$wft_id;
      $tpl->assign("add_dataset_url", $add_dataset_url);
    }

    /**
     * Dispatch to the appropriate functionality for the requested page.
     */
    function dispatcher($type)
    {
        $tpl = new Template_API();
        $tpl->setTemplate("myresearch/index.tpl.html");
        MyResearch::addDatasetLink($tpl);

        $isUser = Auth::getUsername();
        $isUPO = User::isUserUPO($isUser);
        $filter = array();

        // Find out if the facets refine request had a proxy component, and set the acting user if necessary
        $proxy = @$_GET['proxy'];
        if ($isUPO && $proxy != '') {
            Auth::setActingUsername($proxy); // Change to a new acting user
        }

        Auth::checkAuthentication(APP_SESSION, $_SERVER['REQUEST_URI']);
        $username = Auth::getUsername();
        $actingUser = Auth::getActingUsername();
        $author_id = Author::getIDByUsername($actingUser);
        $actingUserArray = Author::getDetailsByUsername($actingUser);
        $actingUserArray['org_unit_description'] = MyResearch::getHRorgUnit($actingUser);

        $tpl->assign("type", $type);

        if (MyResearch::getHRorgUnit($username) == "" && !$isUPO) {
            $tpl->assign("non_hr", true); // This will cause a bail-out in template land
        }

        $tpl->assign("active_nav", "my_fez");
        $tpl->assign("jqueryUI", true);
        $tpl->assign('isApprover', $_SESSION['auth_is_approver']);

        //We allow admins to edit this page header, if it exits
        $page = Page::getPage('my-research-header');
        $zf = new Fez_Filter_RichTextHtmlpurifyWithLinks();
        $page = $zf->filter($page);
        $tpl->assign("headerContent", $page['content']);

        // Some text will be presented slightly differently to the user if they also have edited something.
        $tpl->assign("is_editor", Author::isAuthorAlsoAnEditor($author_id));

        // Determine what we're actually doing here.
        $action = Misc::GETorPOST('action');
        $list = true;

        if ($type == "possible") {
            if ($action == 'claim-add') {
                MyResearch::possiblePubsClaim();
            } elseif ($action == 'claim') {
                $recordDetails = Record::getDetailsLite(Misc::GETorPOST('claim-pid'));
                $tpl->assign("pid", $recordDetails[0]['rek_pid']);
                $tpl->assign('file_options', Datastream::$file_options);
                $tpl->assign('sherpa_romeo_link',SherpaRomeo::getJournalColourFromPidComment($recordDetails[0]['rek_pid']));
                $tpl->assign("citation", $recordDetails[0]['rek_citation']);
                $tpl->assign("herdc_message", MyResearch::herdcMessage($recordDetails[0]['rek_date']));
                $tpl->assign("qindex_meta", Record::getQindexMeta($recordDetails[0]['rek_pid']));
                $tpl->assign("wos_collection", Record::isInWOScollection($recordDetails[0]['rek_pid']));
                $list = false;
            } elseif ($action == 'hide') {
                MyResearch::hide(Misc::GETorPOST('hide-pid'));
            } elseif ($action == 'hide-bulk') {
                MyResearch::bulkHide();
            }
        } elseif ($type == "claimed") {
            if ($action == 'not-mine') {
                MyResearch::claimedPubsDisown(Misc::GETorPOST('pid'));
            } elseif ($action == 'not-mine-bulk') {
                MyResearch::handleBulkDisown();
            } elseif ($action == 'correction') {
                $recordDetails = Record::getDetailsLite(Misc::GETorPOST('pid'));
                //---------------------------
                $tpl->assign('file_options', Datastream::$file_options);
                $tpl->assign('sherpa_romeo_link',SherpaRomeo::getJournalColourFromPidComment($recordDetails[0]['rek_pid']));
                $tpl->assign('header_include_flash_uploader_files', 1);
                //---------------------------
                $tpl->assign("pid", $recordDetails[0]['rek_pid']);
                $tpl->assign("citation", $recordDetails[0]['rek_citation']);
                $tpl->assign("qindex_meta", Record::getQindexMeta($recordDetails[0]['rek_pid']));
                $tpl->assign("wos_collection", Record::isInWOScollection($recordDetails[0]['rek_pid']));
                $tpl->assign("direct_link", Misc::GETorPOST('direct_link'));
                $list = false;
            } elseif ($action == 'duplication-add') {
                MyResearch::claimedPubsDuplicate(Misc::GETorPOST('pid'));
            } elseif ($action == 'correction-add') {
                MyResearch::claimedPubsCorrect(Misc::GETorPOST('pid'));
            } elseif ($action == 'correction-add-files') {
                MyResearch::claimedPubsCorrectFileUpload(Misc::GETorPOST('pid'));
            }

        } elseif ($type == "claimed_data") {


        }

        if ($list) {

            $flagged_claimed = MyResearch::getClaimedFlaggedPubs($actingUser);
            $flagged_possible = MyResearch::getPossibleFlaggedPubs($actingUser);

            /*
                * These are the only $params(ie. $_GET) vars that can be passed to this page.
                * Strip out any that aren't in this list
                */
            $args = array(
                'browse'          => 'string',
                'author_id'       => 'numeric',
                'hide_closed'     => 'numeric',
                'collection_pid'  => 'string',
                'community_pid'   => 'string',
                'cat'             => 'string',
                'reset'           => 'numeric',
                'author'          => 'string',
                'tpl'             => 'numeric',
                'year'            => 'numeric',
                'rows'            => 'numeric',
                'pager_row'       => 'numeric',
                'sort'            => 'string',
                'sort_by'         => 'string',
                'search_keys'     => 'array',
                'order_by'        => 'string',
                'sort_order'      => 'string',
                'value'           => 'string',
                'operator'        => 'string',
                'custom_view_pid' => 'string',
                'form_name'       => 'string',
            );

            $params = $_GET;
            foreach (
                $args as $getName => $getType
            ) {
                if (Misc::sanity_check($params[$getName], $getType) !== false) {
                    $allowed[$getName] = $params[$getName];
                }
            }
            $params = $allowed;

            /*
                * These options are used in a dropdown box to allow the
                * user to sort a list
                */
            $sort_by_list = array(
                "searchKey0"                                              => "Search Relevance",
                "searchKey" . Search_Key::getID("Title")                  => 'Title',
                "searchKey" . Search_Key::getID("File Downloads")         => 'File Downloads',
                "searchKey" . Search_Key::getID("Date")                   => 'Published Date',
                "searchKey" . Search_Key::getID("Created Date")           => 'Created Date',
                "searchKey" . Search_Key::getID("Updated Date")           => 'Updated Date',
                "searchKey" . Search_Key::getID("Sequence")               => 'Sequence',
                "searchKey" . Search_Key::getID("Thomson Citation Count") => 'Thomson Citation Count',
                "searchKey" . Search_Key::getID("Scopus Citation Count")  => 'Scopus Citation Count'
            );

            if (defined('ALTMETRIC_API_ENABLED') && ALTMETRIC_API_ENABLED == 'true') {
                $sort_by_list["searchKey".Search_Key::getID("Altmetric Score")] = "Altmetric Score";
            }

            $options = array();
            $options = Pager::saveSearchParams($params);
            $sort_by = $options["sort_by"];
            $sort_order = $options["sort_order"];

            $sort_by_list['searchKey0'] = "Search Relevance";
            if (empty($params["sort_by"])) {
                if ($type == "possible") {
                    $sort_by = "searchKey0";
                } else {
                    $sort_by = "searchKey" . Search_Key::getID("Date");
                }
            }

            // if searching by Title, Abstract, Keywords and sort order not specifically set in the querystring
            // (from a manual sort order change) than make search revelance sort descending
            if (!is_numeric($params["sort_order"])
                && ($sort_by == "searchKey0" || $sort_by == "searchKey" . Search_Key::getID("Date"))
            ) {
                $options["sort_order"] = 1; // DESC relevance
            }


            // Default Sort
            if (!array_key_exists($sort_by, $sort_by_list)) {
                $sort_by = "searchKey" . Search_Key::getID("Created Date");
                $options["sort_order"] = 1;
            }


            $pager_row = $params['pager_row'];
            if (empty($pager_row)) {
                $pager_row = 0;
            }

            $rows = $params['rows'];
            if (empty($rows)) {
                if (!empty($_SESSION['rows'])) {
                    $rows = $_SESSION['rows'];
                } else {
                    $rows = APP_DEFAULT_PAGER_SIZE;
                }
            } else {
                $_SESSION['rows'] = $rows;
            }

            $order_dir = 'ASC';

            //$current_row = ($current_row/100);
            $citationCache = false;
            $getSimple = false;

            if ($type == "claimed") {
                // $filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
                $filter["searchKey" . Search_key::getID("Object Type")] = 3;
                $filter["searchKey" . Search_Key::getID("Author ID")] = $author_id;
                if (defined('APP_MY_RESEARCH_EXCLUDE_COLLECTIONS') && APP_MY_RESEARCH_EXCLUDE_COLLECTIONS != '') {
                  $filter["manualFilter"] = " !ismemberof_mt:('" . str_replace(':', '\:', implode("' OR '", explode(',', APP_MY_RESEARCH_EXCLUDE_COLLECTIONS))) . "')";
                }
            } elseif ($type == "claimed_data") {
              $filter["searchKey" . Search_key::getID("Object Type")] = 3;
              $filter["searchKey" . Search_key::getID("Display Type")] = XSD_Display::getXDIS_IDByTitle("Data Collection");
              $filter["searchKey" . Search_Key::getID("Author ID")] = $author_id;
              if (defined('APP_MY_RESEARCH_EXCLUDE_COLLECTIONS') && APP_MY_RESEARCH_EXCLUDE_COLLECTIONS != '') {
                $filter["manualFilter"] = " !ismemberof_mt:('" . str_replace(':', '\:', implode("' OR '", explode(',', APP_MY_RESEARCH_EXCLUDE_COLLECTIONS))) . "')";
              }

            } elseif ($type == "possible") {
                $lastname = Author::getLastName($author_id);
                $firstname = Author::getFirstname($author_id);
                $firstname = trim($firstname);
                if ($firstname != "") {
                    $firstname = " " . substr($firstname, 0, 1);
                }
                if (is_numeric($author_id)) {
                    $alternativeAuthorNames = Author::getAlternativeNames($author_id);
                    $alternatives = "";
                    if (count($alternativeAuthorNames) > 0) {
                        $alternatives = implode('"^3' . " OR author_mws:" . '"', $alternativeAuthorNames);
                    }
                    if ($alternatives != "") {
                        $alternatives = "OR author_mws:" . '"' . $alternatives . '"^3';
                    }
                }
                //echo $alternatives;

                //				$filter["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only // now been asked to show everything, and indicated the unpublished ones
                $filter["searchKey" . Search_key::getID("Object Type")] = 3;
                $filter["searchKey" . Search_Key::getID("Author")] = $lastname;

                //Due to the fact some author id are null and not 0 we need to count these too. Because of solr issues we need to add author_mws:[* TO *] to the it as well
                $filter["manualFilter"] = " ( (author_id_mi:0 AND !author_id_mi:" . $author_id." ) OR ( !author_id_mi:[\"\" TO *] AND author_mws:[* TO *] ) ) ";
                //$filter["manualFilter"] .= " AND (author_mws:".'"'.$lastname.'" OR author_mws:'.'"'.$lastname.$firstname.'"^4 '.$alternatives.')';

                if (defined('APP_MY_RESEARCH_EXCLUDE_COLLECTIONS') && APP_MY_RESEARCH_EXCLUDE_COLLECTIONS != '') {
                  $filter["manualFilter"] .= " AND !ismemberof_mt:('" . str_replace(':', '\:', implode("' OR '", explode(',', APP_MY_RESEARCH_EXCLUDE_COLLECTIONS))) . "')";
                }

                $filter["manualFilter"]
                    .= " AND (author_mws:" . '"' . $lastname . '" OR author_mt:' . $lastname . ' OR author_mws:' . '"'
                    . $lastname . $firstname . '"^4 ' . $alternatives . ')';

                if ($options['hide_closed'] == 0) {
                    $hidePids = MyResearch::getHiddenPidsByUsername($actingUser);

                    if (count($hidePids) > 0) {
                        $filter["manualFilter"]
                            .= " AND !pid_t:('" . str_replace(':', '\:', implode("' OR '", $hidePids)) . "')";
                    }
                }
                $options["manualFilter"] = $filter["manualFilter"];
            }

            $message = '';
            if (is_numeric($author_id)) {
                $return = Record::getListing(
                    $options, array(9, 10), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $filter, "AND",
                    true, false, false, 10, 1, true
                );
            } else {
                $message = "You are not registered in " . APP_NAME . " as an author. Please contact the <a href='"
                    . APP_BASE_URL . "contact'>" . APP_SHORT_ORG_NAME . " Manager</a> to resolve this.";
            }
            $facets = @$return['facets'];

            /*
                * We dont want to display facets that a user
                * has already searched by
                */
            if (isset($facets)) {
                foreach (
                    $facets as $sek_id => $facetData
                ) {
                    if (!empty($options['searchKey' . $sek_id])) {
                        unset($facets[$sek_id]);
                    }
                }
            }
            $tpl->assign("author_id_message", $message);
            $tpl->assign("facets", $facets);
            $tpl->assign("list", $return['list']);
            $tpl->assign("list_info", $return['info']);
            $tpl->assign("flagged_claimed", $flagged_claimed);
            $tpl->assign("flagged_possible", $flagged_possible);
            $tpl->assign("options", $options);

            if (Auth::isValidSession($_SESSION)) {
                $sort_by_list["searchKey" . Search_Key::getID("GS Citation Count")] = "Google Scholar Citation Count";
            }

            $tpl->assign('sort_by_list', $sort_by_list);

            if (count($params) > 0) {
                $exclude[] = 'rows';
                $tpl->assign('url_wo_rows', Misc::query_string_encode($params, $exclude));
                array_pop($exclude);

                $exclude[] = 'tpl';
                $tpl->assign('url_wo_tpl', Misc::query_string_encode($params, $exclude));
                array_pop($exclude);

                $exclude[] = 'sort';
                $exclude[] = 'sort_by';
                $tpl->assign('url_wo_sort', Misc::query_string_encode($params, $exclude));
            }
        }

        // Hack to get SCRIPT_URL without querystrings.
        // Usually we could get this info from $_SERVER['SCRIPT_URL'], but can't since
        // we are doing rewrite rules on a per-directory basis via .htaccess file
        $PAGE_URL = preg_replace('/(\?.*)/', '', $_SERVER['REQUEST_URI']);
        $tpl->assign('PAGE_URL', $PAGE_URL);
        $tpl->assign('list_type', $type);
        $terms = @$return['info']['search_info'];
        $tpl->assign('terms', $terms);
        $tpl->assign("list_heading", "My $type Research");

        $tpl->assign('rows', $rows);
        $tpl->assign('sort_order', $options["sort_order"]);
        $tpl->assign('sort_by_default', $sort_by);
        $tpl->assign("action", $action);
        $tpl->assign("acting_user", $actingUserArray);
        $tpl->assign("actual_user", $username);
        $tpl->displayTemplate();

        return;
    }

    /**********************************
     * POSSIBLE PUBLICATION FUNCTIONS *
     **********************************/

    /**
     * This function handles the various actions associated with claiming a possible publication.
     */
    function possiblePubsClaim()
    {
        $log = FezLog::get();

        // 1. Mark the publication claimed in the database
        $pid = @$_POST['pid'];
        $publishedDate = Record::getSearchKeyIndexValue($pid, "Date", true);
        $publishedDate = strftime("%Y", strtotime($publishedDate));
        $author = Auth::getActingUsername();
        $user = Auth::getUsername();
        $correction = @$_POST['correction'];
        $jobID = MyResearch::markPossiblePubAsMine($pid, $author, $user, $correction);

        // 2. Send an email to Eventum about it
        $sendEmail = true;
        $authorDetails = Author::getDetailsByUsername($author);
        $userDetails = User::getDetails($user);
        $authorID = $authorDetails['aut_id'];
        $authorName = $authorDetails['aut_display_name'];
        $userName = $userDetails['usr_full_name'];
        $userEmail = $userDetails['usr_email'];

        // Attempt to link author ID to author on the pub
        $record = new RecordObject($pid);
        $result = $record->matchAuthor($authorID, TRUE, TRUE, 1, FALSE);
        if ((is_array($result)) && $result[0] === true && $correction == '') {
            $sendEmail = false;
        }



        $body = "Record: http://" . APP_HOSTNAME . APP_RELATIVE_URL . "view/" . $pid . "\n\n";
        if ($author == $user) {
            $body .= $authorName . " (" . $authorID . ") has claimed to be an author of this publication.\n\n";
        } else {
            $body .= "User " . $userName . " has indicated that " . $authorName . " (" . $authorID
                . ") is an author of this publication.\n\n";
        }

        if ($correction != '') {
            $body .= "Additionally, the following correction information was supplied:\n\n" . $correction;
        }

        $listFiles = MyResearch::uploadFilesToPid($pid);

        $accessMessage = "(";
        if (is_numeric(strpos($listFiles, '(admin access)'))) {
          $accessMessage .= " admin";
        }
        if (is_numeric(strpos($listFiles, '(open access)'))) {
          $accessMessage = " open";
        }
        if (is_numeric(strpos($listFiles, '(NTRO access)'))) {
            $accessMessage = " NTRO";
        }
        $accessMessage .= ' access)';

        if (!empty($listFiles)) {
            $body .= "\n\n And the following files were attached:\n\n" . $listFiles;
            $subject = "My Research :: Claimed Publication with File Uploads ".$accessMessage." :: " . $jobID . " :: " . $pid . " :: " . $publishedDate . " :: " . $author;
        } else {
          $subject = "My Research :: Claimed Publication :: " . $jobID . " :: " . $pid . " :: " . $publishedDate . " :: " . $author;
        }

        // If this record is claimed and it is in the WoS/Scopus import collection, strip it from there and put it into the provisional HERDC collection as long as it is in the last 6 years
        $currentYear = date("Y");
        $wos_collection = trim(APP_WOS_COLLECTIONS, "'");
        $scopus_collection = APP_SCOPUS_IMPORT_COLLECTION;
        $isMemberOf = Record::getSearchKeyIndexValue($pid, "isMemberOf", false);
        $pubDate = Record::getSearchKeyIndexValue($pid, "Date", false);
        $pubYear = date("Y", strtotime($pubDate));
        if (is_numeric($pubYear) && is_numeric($currentYear)) {
            if (in_array($wos_collection, $isMemberOf) && (($currentYear - $pubYear) < 7)) {
                $res = $record->updateRELSEXT("rel:isMemberOf", APP_MY_RESEARCH_NEW_ITEMS_COLLECTION, false);
                if ($res < 1) {
                    $log->err( "Copy of '" . $pid . "' into the Provisional HERDC Collection " . APP_MY_RESEARCH_NEW_ITEMS_COLLECTION . " Failed" );
                }
                $res = $record->removeFromCollection($wos_collection);
                if (!$res) {
                    $log->err("ERROR Removing '" . $pid . "' from collection '" . $wos_collection . "'");
                }
            } else if (in_array($scopus_collection, $isMemberOf) && (($currentYear - $pubYear) < 7)) {
                $res = $record->updateRELSEXT("rel:isMemberOf", APP_MY_RESEARCH_NEW_ITEMS_COLLECTION, false);
                if ($res < 1) {
                    $log->err( "Copy of '" . $pid . "' into the Provisional HERDC Collection " . APP_MY_RESEARCH_NEW_ITEMS_COLLECTION . " Failed" );
                }
                $res = $record->removeFromCollection($scopus_collection);
                if (!$res) {
                    $log->err("ERROR Removing '" . $pid . "' from collection '" . $scopus_collection . "'");
                }
            }
        }
        // If this record is in the APP_HERDC_TRIAL_COLLECTION list and it has been claimed by a new author,
        // then change the eSpace followup flag to 'followup' and change the email to indicate this
        $herdc_trial_collection = explode(',', preg_replace("/[' ]/", '', APP_HERDC_TRIAL_COLLECTION));

        if (count(array_intersect($herdc_trial_collection, $isMemberOf)) > 0) {
            $record = new RecordObject($pid);
            $search_keys = array("Follow up Flags");
            $values = array(Controlled_Vocab::getID("Follow-up"));
            $record->addSearchKeyValueList($search_keys, $values, true);
            $subject = str_replace("Claimed Publication ::", "Claimed Publication :: Completed HERDC author change :: ", $subject);
            $sendEmail = true; //make sure the email is sent even if author id matching was automatic as this will need a followup
        }
        if ($sendEmail || !empty($listFiles)) {
            Eventum::lodgeJob($subject, $body, $userEmail);
        }

        return;
    }


    /**
     * This function is invoked when a user marks a publication as belonging to a particular author.
     */
    function markPossiblePubAsMine($pid, $author, $user, $correction = '')
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt
            = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_possible_flagged
				(
					mrp_pid,
					mrp_author_username,
					mrp_user_username,
					mrp_timestamp,
					mrp_type,
					mrp_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($author) . ",
					" . $db->quote($user) . ",
					" . $db->quote(Date_API::getCurrentDateGMT()) . ",
					" . $db->quote('C') . ",
					" . $db->quote($correction) . ");";
        try {
            $db->exec($stmt);
        }

        catch (Exception $ex) {
            $log->err($ex);
            return -1;
        }

        return $db->lastInsertId(APP_TABLE_PREFIX . "my_research_possible_flagged", "mrp_id");
    }


    /**
     * This function hides a possible publication from the user.
     */
    function hide($pid)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $author = Auth::getActingUsername();
        $user = Auth::getUsername();
        $correction = '';

        $stmt
            = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_possible_flagged
				(
					mrp_pid,
					mrp_author_username,
					mrp_user_username,
					mrp_timestamp,
					mrp_type,
					mrp_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($author) . ",
					" . $db->quote($user) . ",
					" . $db->quote(Date_API::getCurrentDateGMT()) . ",
					" . $db->quote('H') . ",
					" . $db->quote($correction) . ");";
        try {
            $db->exec($stmt);
        }

        catch (Exception $ex) {
            $log->err($ex);
            return -1;
        }

        return 1;
    }


    /**
     * Hide a whole bunch of PIDs at once.
     */
    function bulkHide()
    {
        $pids = explode(",", @$_POST['bulk-hide-pids']);

        foreach (
            $pids as $pid
        ) {
            MyResearch::hide($pid);
        }

        return;
    }


    public static function getHiddenPidsByUsername($username)
    {
        $log = FezLog::get();

        $res = array();
        $db = DB_API::get();

        $stmt = "SELECT mrp_pid FROM " . APP_TABLE_PREFIX . "my_research_possible_flagged
							WHERE mrp_type = 'H' and mrp_author_username = " . $db->quote($username) . "";

        try {
            $res = $db->fetchCol($stmt);
        }
        catch (Exception $ex) {
            $log->err($ex);
        }
        return $res;
    }


    /**
     * Get all flagged publications for a given user.
     */
    function getPossibleFlaggedPubs($username)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt
            = "SELECT
					mrp_pid,
					mrp_type,
					mrp_correction,
					mrp_user_username
				FROM
					" . APP_TABLE_PREFIX . "my_research_possible_flagged
				WHERE
					mrp_author_username = " . $db->quote($username) . "";
        try {
            $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return '';
        }

        // Reformat the results so that we can easily comapre them to the record index.
        $ret = array();
        foreach (
            $res as $row
        ) {
            $ret[$row['mrp_pid']]['type'] = $row['mrp_type'];
            $ret[$row['mrp_pid']]['correction'] = $row['mrp_correction'];
            $ret[$row['mrp_pid']]['user'] = $row['mrp_user_username'];
        }

        return $ret;
    }


    /*********************************
     * CLAIMED PUBLICATION FUNCTIONS *
     *********************************/

    /**
     * This function dispatches to the appropriate claimed publications functionality
     */
    function claimedPubsDispatcher()
    {
        MyResearch::dispatcher("claimed");
    }

    /**
     * Get all flagged claimed publications for a given user.
     */
    function getClaimedFlaggedPubs($username)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt
            = "SELECT
					mrc_pid,
					mrc_type,
					mrc_correction,
					mrc_user_username
				FROM
					" . APP_TABLE_PREFIX . "my_research_claimed_flagged
				WHERE
					mrc_author_username = " . $db->quote($username) . "";

        try {
            $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return '';
        }

        // Reformat the results so that we can easily comapre them to the record index.
        $ret = array();
        foreach (
            $res as $row
        ) {
            $ret[$row['mrc_pid']]['type'] = $row['mrc_type'];
            $ret[$row['mrc_pid']]['correction'] = $row['mrc_correction'];
            $ret[$row['mrc_pid']]['user'] = $row['mrc_user_username'];
        }

        return $ret;
    }


    /**
     * Get the list of PIDs we are disowning, pass off to the singular disown function.
     */
    function handleBulkDisown()
    {
        $pids = explode(",", @$_POST['bulk-not-mine-pids']);

        foreach (
            $pids as $pid
        ) {
            MyResearch::claimedPubsDisown($pid);
        }

        return;
    }


    /**
     * Fire relevant subroutines for disowning a publication.
     */
    function claimedPubsDisown($pid)
    {
        // 1. Mark the publication claimed in the database
        $author = Auth::getActingUsername();
        $user = Auth::getUsername();
        $jobID = MyResearch::markClaimedPubAsNotMine($pid, $author, $user);

        // 2. Send an email to Eventum about it
        $authorDetails = Author::getDetailsByUsername($author);
        $userDetails = User::getDetails($user);
        $authorID = $authorDetails['aut_id'];
        $authorName = $authorDetails['aut_display_name'];
        $userName = $userDetails['usr_full_name'];
        $userEmail = $userDetails['usr_email'];
        $publishedDate = Record::getSearchKeyIndexValue($pid, "Date", true);
        $publishedDate = strftime("%Y", strtotime($publishedDate));

        $subject = "My Research :: Disowned Publication :: " . $jobID . " :: " . $pid . " :: " . $publishedDate . " :: " . $author;

        $body = "Record: http://" . APP_HOSTNAME . APP_RELATIVE_URL . "view/" . $pid . "\n\n";
        if ($author == $user) {
            $body
                .= $authorName . " (" . $authorID . ") has indicated that they are not the author of this publication.";
        } else {
            $body .= "User " . $userName . " has indicated that " . $authorName . " (" . $authorID
                . ") is not the author of this publication.";
        }

        Eventum::lodgeJob($subject, $body, $userEmail);

        return;
    }


    /**
     * Fire relevant subroutines for correcting a claimed publication.
     */
    function claimedPubsCorrect($pid)
    {
        // 1. Mark the publication claimed in the database
        $author = Auth::getActingUsername();
        $user = Auth::getUsername();
        $correction = @$_POST['correction'];
        $jobID = MyResearch::markClaimedPubAsNeedingCorrection($pid, $author, $user, $correction);

        // 2. Send an email to Eventum about it
        $authorDetails = Author::getDetailsByUsername($author);
        $userDetails = User::getDetails($user);
        $authorID = $authorDetails['aut_id'];
        $authorName = $authorDetails['aut_display_name'];
        $userName = $userDetails['usr_full_name'];
        $userEmail = $userDetails['usr_email'];
        $publishedDate = Record::getSearchKeyIndexValue($pid, "Date", true);
        $publishedDate = strftime("%Y", strtotime($publishedDate));


        $subject = "My Research :: Correction Required :: " . $jobID . " :: " . $pid . " :: " . $publishedDate . " :: " . $author;
        $body = "Record: http://" . APP_HOSTNAME . APP_RELATIVE_URL . "view/" . $pid . "\n\n";
        if ($author == $user) {
            $body .= $authorName . " (" . $authorID . ") has supplied the following correction information:\n\n";
        } else {
            $body .= "User " . $userName . ", acting on behalf of " . $authorName
                . ", has supplied the following correction information:\n\n";
        }
        $body .= $correction;

        Eventum::lodgeJob($subject, $body, $userEmail);

        return;
    }

    /**
     * Fire relevant subroutines for correcting a claimed publication with uploads attached.
     */
    function claimedPubsCorrectFileUpload($pid)
    {
        $log = FezLog::get();
        // 1. Mark the publication claimed in the database
        $author = Auth::getActingUsername();
        $user = Auth::getUsername();
        $correction = @$_POST['correction_upload'];

        // 2. Send an email to Eventum about it
        $authorDetails = Author::getDetailsByUsername($author);
        $userDetails = User::getDetails($user);
        $authorID = $authorDetails['aut_id'];
        $authorName = $authorDetails['aut_display_name'];
        $userName = $userDetails['usr_full_name'];
        $userEmail = $userDetails['usr_email'];
        $publishedDate = Record::getSearchKeyIndexValue($pid, "Date", true);
        $publishedDate = strftime("%Y", strtotime($publishedDate));
        $listFiles = MyResearch::uploadFilesToPid($pid);

        $accessMessage = "(";
        if (is_numeric(strpos($listFiles, '(admin access)'))) {
            $accessMessage .= " admin";
        }
        if (is_numeric(strpos($listFiles, '(open access)'))) {
            $accessMessage = " open";
        }
        if (is_numeric(strpos($listFiles, '(NTRO access)'))) {
            $accessMessage = " NTRO";
        }
        $accessMessage .= ' access )';

        $subject = "My Research :: File Uploads ".$accessMessage." :: " . $jobID . " :: " . $pid . " :: " . $publishedDate . " :: " . $author;
        $body = "Record: http://" . APP_HOSTNAME . APP_RELATIVE_URL . "view/" . $pid . "\n\n";
        if ($author == $user) {
            $body .= $authorName . " (" . $authorID . ") has supplied the following file uploads information:\n\n";
        } else {
            $body .= "User " . $userName . ", acting on behalf of " . $authorName
                . ", has supplied the following file uploads information:\n\n";
        }
        $body .= 'Notes: '.$correction;
        $body .= "\n\nFiles uploaded: \n\n". $listFiles;
        Eventum::lodgeJob($subject, $body, $userEmail);

        return;
    }

    //Save uploads to Pid
    //Returns a string list of filenames
    function uploadFilesToPid($pid) {
        $log = FezLog::get();
        $listFiles = '';
        $historyComment = '';
        foreach($_FILES['file']['name'] as $key => $file) {
            if (!empty($file)) {
                if (move_uploaded_file($_FILES["file"]["tmp_name"][$key], APP_TEMP_DIR . $_FILES["file"]["name"][$key]) == false) {
                    $log->err("File not renamed $file (file already there?)<br/>\n", __FILE__, __LINE__);
                    return $listFiles.' Error on file uploads at '.date("Y-m-d H:i:s");
                }
                $listFiles .= $file .' ';
                $description = $_POST['description'][$key];
                //If (a HERDC file OR an embargo date is saved) set permissions(5) to admin and upo only(10) ( else open access, which is inherit in this case)
                if (($_POST['filePermissions'][$key] == 5) || !empty($_POST['embargo_date'][$key])) {
                  $fezACMLTemplateNum = 10;
                  $listFiles .= "(admin access)\n";
                } else if(($_POST['filePermissions'][$key] == 8))  {
                    $fezACMLTemplateNum = 11;
                    $listFiles .= "(NTRO access)\n";
                } else {
                  $fezACMLTemplateNum = NULL;
                  $listFiles .= "(open access)\n";
                }
                Datastream::addDatastreamToPid($pid, $file, $description, $fezACMLTemplateNum);
                Datastream::saveDatastreamSelectedPermissions($pid, $file, $_POST['filePermissions'][$key], $_POST['embargo_date'][$key]);
                $filePermsNumber = $_POST['filePermissions'][$key];
                $historyComment .= $file.' - "'.Datastream::$file_options[$filePermsNumber].'"; ';
            }
        }
        if(!empty($historyComment)) {
            History::addHistory($pid, null, '', '', true, 'User File Uploads', $historyComment);
        }
        return $listFiles;
    }

    /**
     * Fire relevant subroutines for a de-duplication request on a claimed publication.
     */
    function claimedPubsDuplicate($pid)
    {
        // 1. Mark the publication as a duplicate in the database
        $author = Auth::getActingUsername();
        $user = Auth::getUsername();
        $duplication = @$_POST['duplication'];
        $jobID = MyResearch::markClaimedPubAsDuplication($pid, $author, $user, $duplication);

        // 2. Send an email to Eventum about it
        $authorDetails = Author::getDetailsByUsername($author);
        $userDetails = User::getDetails($user);
        $authorID = $authorDetails['aut_id'];
        $authorName = $authorDetails['aut_display_name'];
        $userName = $userDetails['usr_full_name'];
        $userEmail = $userDetails['usr_email'];
        $publishedDate = Record::getSearchKeyIndexValue($pid, "Date", true);
        $publishedDate = strftime("%Y", strtotime($publishedDate));


        $subject = "My Research :: De-Duplication Required :: " . $jobID . " :: " . $pid . " :: " . $publishedDate . " :: " . $author;
        $body = "Record: http://" . APP_HOSTNAME . APP_RELATIVE_URL . "view/" . $pid . "\n\n";
        if ($author == $user) {
            $body .= $authorName . " (" . $authorID . ") has supplied the following de-duplication information:\n\n";
        } else {
            $body .= "User " . $userName . ", acting on behalf of " . $authorName
                . ", has supplied the following de-duplication information:\n\n";
        }
        $body .= $duplication;

        Eventum::lodgeJob($subject, $body, $userEmail);

        return;
    }

    /**
     * This function is invoked when a user marks a claimed publication as not being theirs.
     */
    function markClaimedPubAsNotMine($pid, $author, $user)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $correction = '';

        $stmt
            = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_claimed_flagged
				(
					mrc_pid,
					mrc_author_username,
					mrc_user_username,
					mrc_timestamp,
					mrc_type,
					mrc_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($author) . ",
					" . $db->quote($user) . ",
					" . $db->quote(Date_API::getCurrentDateGMT()) . ",
					" . $db->quote('D') . ",
					" . $db->quote($correction) . ");";
        try {
            $db->exec($stmt);
        }

        catch (Exception $ex) {
            $log->err($ex);
            return -1;
        }

        return $db->lastInsertId(APP_TABLE_PREFIX . "my_research_claimed_flagged", "mrc_id");
    }


    /**
     * This function is invoked when a user marks a claimed publication as not being theirs.
     */
    function markClaimedPubAsNeedingCorrection($pid, $author, $user, $correction)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt
            = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_claimed_flagged
				(
					mrc_pid,
					mrc_author_username,
					mrc_user_username,
					mrc_timestamp,
					mrc_type,
					mrc_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($author) . ",
					" . $db->quote($user) . ",
					" . $db->quote(Date_API::getCurrentDateGMT()) . ",
					" . $db->quote('C') . ",
					" . $db->quote($correction) . ");";
        try {
            $db->exec($stmt);
        }

        catch (Exception $ex) {
            $log->err($ex);
            return -1;
        }

        return $db->lastInsertId(APP_TABLE_PREFIX . "my_research_claimed_flagged", "mrc_id");
    }

    /**
     * This function is invoked when a user marks a claimed publication as being a duplicate of some other publication(s).
     */
    function markClaimedPubAsDuplication($pid, $author, $user, $duplication)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt
            = "INSERT INTO
					" . APP_TABLE_PREFIX . "my_research_claimed_flagged
				(
					mrc_pid,
					mrc_author_username,
					mrc_user_username,
					mrc_timestamp,
					mrc_type,
					mrc_correction
				) VALUES (
					" . $db->quote($pid) . ",
					" . $db->quote($author) . ",
					" . $db->quote($user) . ",
					" . $db->quote(Date_API::getCurrentDateGMT()) . ",
					" . $db->quote('U') . ",
					" . $db->quote($duplication) . ");";
        try {
            $db->exec($stmt);
        }

        catch (Exception $ex) {
            $log->err($ex);
            return -1;
        }

        return $db->lastInsertId(APP_TABLE_PREFIX . "my_research_claimed_flagged", "mrc_id");
    }

    /**
     * Determine whether we need to display a message concerning correction of HERDC codes.
     */
    function herdcMessage($year)
    {
        /*
          SC says: "The HERDC coding for a given year will not be finalised until about June/July
          the following year (so 2010 publications should have a finalised HERDC code by July 2011)."
          */
        $pubYear = date("Y", strtotime($year + '1 year'));
        $pubYear = (int)$pubYear;

        $currentMonth = date("m");
        $currentYear = date("Y");

        if ($pubYear >= $currentYear
            || ($pubYear == ($currentYear - 1) && $currentMonth < 7)
        ) {
            return true;
        }

        return false;
    }


    /*****************
     * UPO FUNCTIONS *
     *****************/

    /**
     * Shows a list of all authors within a given AOU.
     */
    function listAuthorsByAOU($orgID)
    {

        $log = FezLog::get();
        $db = DB_API::get();

        if (!is_numeric($orgID)) {
            return false;
        }

        $stmt
            = "
				SELECT
					aut_org_username AS username,
					aut_fname AS first_name,
					UCASE(aut_lname) AS last_name,";

        if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) {
            $stmt .= " array_to_string(array_accum(pos_title), ', ') AS pos_title ";
        } else {
            $stmt .= " GROUP_CONCAT(pos_title SEPARATOR ', ') AS pos_title ";
        }

        $stmt
            .= " FROM
					" . APP_TABLE_PREFIX . "author INNER JOIN
					hr_position_vw on aut_org_username = user_name
				WHERE
					aou = " . $db->quote($orgID) . "
					AND user_name != ''
				GROUP BY aut_org_username, aut_fname, aut_lname
				ORDER BY
					aut_lname ASC,
					aut_fname ASC;
		";

        try {
            $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return '';
        }

        return $res;
    }


    /**
     * Gets the default Org Unit for a particular user.
     */
    function getDefaultAOU($username)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt
            = "
				SELECT
					aou AS aou
				FROM
					hr_position_vw
				WHERE
					user_name = " . $db->quote($username) . "
				LIMIT 1
				";

        try {
            $res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return '';
        }

        return $res['aou'];
    }


    /**
     * Gets the org unit description for a given username.
     */
    public static function getHRorgUnit($username)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if ($username == '' || !isset($username)) {
            return '';
        }

        $stmt
            = "
				SELECT
					aurion_org_desc AS org_description
				FROM
					hr_position_vw,
					hr_org_unit_distinct_manual
				WHERE
					hr_position_vw.aou = hr_org_unit_distinct_manual.aurion_org_id
				AND
					user_name = " . $db->quote($username) . "
				LIMIT 1
				";

        try {
            $res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return '';
        }
        if (count($res) == 0) {
            return '';
        } else {
            return $res['org_description'];
        }
    }


    /**
     * Checks if the user is in one of the my research groups that should go to the classic my uq espace instead when this module is on.
     * RETURNS 0/1 as true/false
     */
    public static function isClassicUser($username)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if (!defined('APP_MY_RESEARCH_USE_CLASSIC_GROUPS') || APP_MY_RESEARCH_USE_CLASSIC_GROUPS == '') {
            return 0;
        }

        $stmt
            = "
				SELECT
					usr_id
				FROM
					" . APP_TABLE_PREFIX . "user INNER JOIN
					" . APP_TABLE_PREFIX . "group_user on gpu_usr_id = usr_id INNER JOIN
					" . APP_TABLE_PREFIX . "group on grp_id = gpu_grp_id
				WHERE
					grp_title IN (" . APP_MY_RESEARCH_USE_CLASSIC_GROUPS . ")
				AND
					usr_username = " . $db->quote($username) . "
				LIMIT 1
				";

        try {
            $res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return '';
        }
        if (is_numeric($res['usr_id'])) {
            return 1;
        } else {
            return 0;
        }
    }


    /**
     * Returns only users who match a particular UQ username.
     */
    function findAuthorsByUsername($username)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        if ($username == '') {
            return false;
        }

        $stmt
            = "
				SELECT
					aut_org_username AS username,
					aut_fname AS first_name,
					UCASE(aut_lname) AS last_name,";

        if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) {
            $stmt .= " array_to_string(array_accum(pos_title), ', ') AS pos_title ";
        } else {
            $stmt .= " GROUP_CONCAT(pos_title SEPARATOR ', ') AS pos_title ";
        }

        $stmt
            .= " FROM
					" . APP_TABLE_PREFIX . "author INNER JOIN
					hr_position_vw on aut_org_username = user_name
				WHERE ";
        if (is_numeric(strpos(APP_SQL_DBTYPE, "pgsql"))) {
            $stmt
                .= "
					(aut_org_username ILIKE " . $db->quote("%$username%") . "
					OR CONCAT_WS(' ', aut_fname, aut_lname) ILIKE " . $db->quote("%$username%") . ")
					AND user_name != ''";
        } else {
            $stmt
                .= "
					(aut_org_username LIKE " . $db->quote("%$username%") . "
					OR CONCAT_WS(' ', aut_fname, aut_lname) LIKE " . $db->quote("%$username%") . ")
					AND user_name != ''";
        }
        $stmt
            .= "
				GROUP BY aut_org_username, aut_fname, aut_lname
				ORDER BY
					aut_lname ASC,
					aut_fname ASC;
		";
        try {
            $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return '';
        }

        return $res;
    }


    /******************
     * MISC FUNCTIONS *
     ******************/

    /**
     * Closes a My Research job. This function is invoked from a shell script after scanning for
     * recently closed jobs in Eventum.
     */
    function closeJob($type, $jobID)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        // This is slightly counter-intuitive. Jobs for 'Claimed Publications' are actually sitting in the
        // 'Possible' flagged table, as they were lodged against possible publications. And vice-versa.
        if ($type == 'Claimed Publication') {
            $query
                = "
					DELETE
					FROM
						" . APP_TABLE_PREFIX . "my_research_possible_flagged
					WHERE
						mrp_id = " . $db->quote($jobID) . ";
					";
        } else {
            $query
                = "
					DELETE
					FROM
						" . APP_TABLE_PREFIX . "my_research_claimed_flagged
					WHERE
						mrc_id = " . $db->quote($jobID) . ";
					";
        }

        try {
            $db->query($query);
        }
        catch (Exception $ex) {
            $log->err($ex);
            return false;
        }

        echo "* Deleting job type '" . $type . "', ID: " . $jobID . "\n";

        return true;
    }

}
