<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "najax_classes.php");

class Lister
{
    function getList($params, $display=true) {
        $tpl = new Template_API();

        $tpl_idx = intval($_GET['tpl']);
        $tpls = array(
            0 => array('file' => 'list.tpl.html', 'title' => 'Default'),
            1 => array('file' => 'views/list/author_bulk_edit.tpl.html', 'title' => 'Edit Authors'),
            2 => array('file' => 'rss.tpl.html', 'title' => 'RSS Feed')
        );
    
        $tpl_file = $tpls[$tpl_idx]['file'];    
        $tpl->setTemplate($tpl_file);

        $tpl->assign('tpl_list', array_map(create_function('$a','return $a[\'title\'];'), $tpls));

        $username = Auth::getUsername();
        $tpl->assign("isUser", $username);
        $isAdministrator = User::isUserAdministrator($username);
        if (Auth::userExists($username)) { // if the user is registered as a Fez user
            $tpl->assign("isFezUser", $username);
        }
        $tpl->assign("isAdministrator", $isAdministrator);
        $pagerRow = Pager::getParam('pagerRow',$params);
        if (empty($pagerRow)) {
            $pagerRow = 0;
        }
        $rows = Pager::getParam('rows',$params);
        if (empty($rows)) {
            $rows = APP_DEFAULT_PAGER_SIZE;
        }
        $options = Pager::saveSearchParams($params);
        $tpl->assign("options", $options);
        $terms = Pager::getParam('terms',$params);
        $cat = Pager::getParam('cat',$params);
        $browse = Pager::getParam('browse',$params);
        $letter = Pager::getParam('letter',$params);
        $collection_pid = Pager::getParam('collection_pid',$params);
        $community_pid = Pager::getParam("community_pid",$params);
        $order_by = Pager::getParam('order_by',$params);
        $order_by_list = array();
        if (!empty($community_pid)) {
            $order_by_list = array(
                'Title' => 'Title',
                'Description' => 'Description'
            );
        } elseif (empty($community_pid) && empty($collection_pid) && empty($cat) && empty($browse)) {
            $order_by_list = array(
                'Title' => 'Title',
                'Description' => 'Description'
            );
        } else {
            foreach (Search_Key::getAssocListAdvanced() as $key => $value) {
                $order_by_list[$value] = $value;
            }
        }
        if (!empty($terms) || $cat == 'search') {
            $order_by_list['Relevance'] = "Search Relevance";
        }
        $tpl->assign('order_by_list', $order_by_list);
        $orderby_keys = array_keys($order_by_list);
        if (!in_array($order_by, $orderby_keys)) {
            $order_by = $orderby_keys[0];
        }
        $list_info = array();
        
		$bulk_workflows = WorkflowTrigger::getAssocListByTrigger("-1", 7); //get the bulk change workflows
//		print_r($bulk_workflows);
        $tpl->assign("bulk_workflows", $bulk_workflows);
        if (!empty($collection_pid)) {
            if (empty($order_by)) {
                $order_by = 'Title';
            }
            // list a collection
            // first check the user has view rights over the collection object
            $record = new RecordObject($collection_pid);
            $canView = $record->canView(true);

            $tpl->assign("isViewer", $canView);

            if ($canView) {

                $tpl->assign("xdis_id", Record::getRecordXDIS_ID());
                $collection_details = Collection::getDetails($collection_pid);
                $parents = Collection::getParents2($collection_pid);
                $tpl->assign("parents", $parents);
                $collection_xdis_id = Collection::getCollectionXDIS_ID();
//                $userPIDAuthGroups = Auth::getIndexAuthorisationGroups($collection_details);
                $userPIDAuthGroups = Auth::getAuthorisationGroups($collection_pid);
//				print_r($userPIDAuthGroups);
                $isCreator = (in_array("Creator", $userPIDAuthGroups) 
                        || in_array("Community Administrator", $userPIDAuthGroups) 
                        || in_array("Collection Administrator", $userPIDAuthGroups));
                $tpl->assign("isCreator", $isCreator);
                $isEditor = (in_array("Creator", $userPIDAuthGroups) 
                        || in_array("Community Administrator", $userPIDAuthGroups) 
                        || in_array("Editor", $userPIDAuthGroups) 
                        || in_array("Collection Administrator", $userPIDAuthGroups));
                $tpl->assign("isEditor", $isEditor);		
                
                $list = Collection::getListing($collection_pid, $pagerRow, $rows, $order_by);
                $list_info = $list["info"];
                $list = $list["list"];
                $tpl->assign("list_heading", "List of Records in ".$collection_details[0]['title'][0]." Collection");
                $tpl->assign("list_type", "collection_records_list");
                $tpl->assign("collection_pid", $collection_pid);
                $childXDisplayOptions = Collection::getChildXDisplayOptions($collection_pid);
                if (count($childXDisplayOptions) > 0) {
                    $tpl->assign("childXDisplayOptions", $childXDisplayOptions);
                } else {
                    $tpl->assign("childXDisplayOptions", 0);
                } 
            } else {
                $tpl->assign("show_not_allowed_msg", true);
            } 
        } elseif (!empty($community_pid)) {
            /*if (empty($order_by)) {
                $order_by = 'Title';
            }*/
			$order_by = 'Title';
            // list collections in a community
            // first check the user has view rights over the collection object
            $record = new RecordObject($community_pid);
            $canView = $record->canView(true);
            $tpl->assign("isViewer", $canView);
            if ($canView) {	
                $tpl->assign("community_pid", $community_pid);
                $xdis_id = Collection::getCollectionXDIS_ID();
                $community_xdis_id = Community::getCommunityXDIS_ID();
                $userPIDAuthGroups = Auth::getAuthorisationGroups($community_pid);
                $isCreator = (in_array("Creator", $userPIDAuthGroups));
                $tpl->assign("isCreator", $isCreator);
                $isEditor = (in_array("Creator", $userPIDAuthGroups) || in_array("Community Administrator", $userPIDAuthGroups) || in_array("Editor", $userPIDAuthGroups));
                $tpl->assign("isEditor", $isEditor);
                $tpl->assign("xdis_id", $xdis_id);	
                $community_details = Community::getDetails($community_pid);
                $list = Collection::getListing($community_pid, $pagerRow, $rows, $order_by);
                $list_info = $list["info"];
                $list = $list["list"];
                $tpl->assign("list_heading", "List of Collections in ".$community_details[0]['title'][0]." Community");
                $tpl->assign("list_type", "collection_list");
                $childXDisplayOptions = Community::getChildXDisplayOptions($community_pid);
                if (count($childXDisplayOptions) > 0) {
                    $tpl->assign("childXDisplayOptions", $childXDisplayOptions);
                } else {
                    $tpl->assign("childXDisplayOptions", 0);
                }
            } else {
                $tpl->assign("show_not_allowed_msg", true);
            }
        } elseif (!empty($terms)) {
            if (empty($order_by)) {
                $order_by = 'Relevance';
            }

            // search Fez
            $list = Collection::searchListing($terms, $pagerRow, $rows, $order_by);	
            $list_info = $list["info"];
            $list = $list["list"];
            $tpl->assign("list_heading", "Search Results ($terms)");
            $tpl->assign("list_type", "all_records_list");
        } elseif ($cat == "search") {
            if (empty($order_by)) {
                $order_by = 'Title';
            }

            // search 
            $list = Collection::advSearchListing($pagerRow, $rows, $order_by);	
            $list_info = @$list["info"];
            $terms = @$list['search_info'];
            $list = @$list["list"];
            $tpl->assign("list_heading", "Search Results ($terms)");
            $tpl->assign("list_type", "all_records_list");
        } elseif ($browse == "latest") {
            // browse by latest additions / created date desc
            // reget the order by thing so we can change the default
            if (empty($order_by)) {
                $order_by = 'Created Date';
            }
            $list = Collection::browseListing($pagerRow, $rows, "Created Date",$order_by);
            //print_r($list);
            $list_info = $list["info"];
            $list = $list["list"];
            $tpl->assign("browse_type", "browse_latest");
            $tpl->assign("list_heading", "Browse By Latest Additions");
            $tpl->assign("today", date("Y-m-d"));
            $tpl->assign("today_day_name", date("l"));
            $tpl->assign("yesterday", date("Y-m-d", time()-86400));
            $tpl->assign("last", "Last ");
            $tpl->assign("list_type", "all_records_list");
        } elseif ($browse == "year") {
            // browse by year
            $year = Pager::getParam('year',$params);
            if (empty($order_by)) {
            $order_by = 'Title';
            }
            if (is_numeric($year)) {	
                $list = Collection::browseListing($pagerRow, $rows, "Date", $order_by);
                $list_info = $list["info"];
                $list = $list["list"];
                $tpl->assign("browse_heading", "Browse By Year ".$year);
                $tpl->assign("list_heading", "List of Records");
            } else {
                $list = Collection::listByAttribute($pagerRow, $rows,"Date",$order_by);
                $list_info = $list["info"];
                $list = $list["list"];
                $tpl->assign("browse_heading", "Browse By Year");
            }
            $tpl->assign("browse_type", "browse_year");
        } elseif ($browse == "author") {
            // browse by author
            $author = Pager::getParam('author',$params);
            if (empty($order_by)) {
                $order_by = 'Title';
            }
            if (!empty($author)) {	
                $list = Collection::browseListing($pagerRow, $rows, "Author", $order_by);
                $list_info = $list["info"];
                $list = $list["list"];
                $tpl->assign("browse_heading", "Browse By Author - ".$author);
			    $tpl->assign("list_heading", "Browse By Author - ".$author);	
            } else {
                $list = Collection::listByAttribute($pagerRow, $rows, "Author", $order_by, $letter);
                $list_info = $list["info"];
                $list = $list["list"];
                $tpl->assign("browse_heading", "Browse By Author");
			    $tpl->assign("list_heading", "Browse By Author");					
            }
            $tpl->assign("browse_type", "browse_author");
            $tpl->assign("letter_list", Collection::getLetterList());
            $tpl->assign("alphabet_list", Misc::generateAlphabetArray());
        } elseif ($browse == "depositor") {
            // browse by depositor
            $depositor = Pager::getParam('depositor',$params);
			$depositor_fullname = User::getFullName($depositor);
            if (empty($order_by)) {
            $order_by = 'Title';
            }
            if (!empty($depositor)) {	
                $list = Collection::browseListing($pagerRow, $rows, "Depositor",$order_by);
                $list_info = $list["info"];
                $list = $list["list"];
                $tpl->assign("browse_heading", "Browse By Depositor - ".$depositor_fullname);
			    $tpl->assign("list_heading", "Browse By Depositor - ".$depositor_fullname);	
            } else {
                $list = Collection::listByAttribute($pagerRow, $rows, "Depositor",$order_by);
                $list_info = $list["info"];
                $list = $list["list"];		
                $tpl->assign("browse_heading", "Browse By Depositor");
			    $tpl->assign("list_heading", "Browse By Depositor");					
            }
            $tpl->assign("browse_type", "browse_depositor");			
        } elseif ($browse == "subject") {
            if (empty($order_by)) {
                $order_by = 'Title';
            }
            // browse by subject
            $parent_id = Pager::getParam('parent_id',$params);
            if (is_numeric($parent_id)) {	
                $subject_list = Controlled_Vocab::getList($parent_id);
                $treeIDs = Controlled_Vocab::getAllTreeIDs($parent_id);

                $subject_count = Collection::getCVCountSearch($treeIDs, $parent_id);
                $list = Collection::browseListing($pagerRow, $rows, "Subject",$order_by);	
                $list_info = $list["info"];
                $list = $list["list"];		
            } else {
                $subject_list = Controlled_Vocab::getList();	
            }
            $breadcrumb = Controlled_Vocab::getParentAssocListFullDisplay($parent_id);
            $breadcrumb = Misc::array_merge_preserve($breadcrumb, Controlled_Vocab::getAssocListByID($parent_id));
            $newcrumb = array();
            foreach ($breadcrumb as $key => $data) {
                array_push($newcrumb, array("cvo_id" => $key, "cvo_title" => $data));
            }
            $max_breadcrumb = (count($newcrumb) -1);
            $tpl->assign("max_subject_breadcrumb", $max_breadcrumb);
            $tpl->assign("subject_breadcrumb", $newcrumb);
            $tpl->assign("list_type", "all_records_list");
            $tpl->assign("parent_id", $parent_id);
            $tpl->assign("subject_list", $subject_list);
            $tpl->assign("subject_count", $subject_count);
            $tpl->assign("browse_heading", "Browse By Subject Classifications Records");
            $tpl->assign("list_heading", "List of Subject Classifications Records");
            $tpl->assign("browse_type", "browse_subject");
        } else {
            // list all communities
            if (empty($order_by)) {
                $order_by = 'Title';
            }
            $xdis_id = Community::getCommunityXDIS_ID();
            $tpl->assign("xdis_id", $xdis_id);	
            $list = Community::getList($pagerRow, $rows, $order_by);
            $list_info = $list["info"];
            $list = $list["list"];
            $tpl->assign("list_type", "community_list");
            $tpl->assign("list_heading", "List of Communities");
        }
        $tpl->assign('order_by_default', $order_by);
        $workflows_list = Misc::keyPairs(Workflow::getList(), 'wfl_id', 'wfl_title');
        $tpl->assign('workflows_list', $workflows_list);
        $tpl->assign("eserv_url", APP_BASE_URL."eserv.php");

        $tpl->assign("list", $list);
        $tpl->assign("list_info", $list_info);
        if (Auth::userExists($username)) {
            $prefs = Prefs::get(Auth::getUserID());
        }
        $tpl->registerNajax(NAJAX_Client::register('NajaxRecord', APP_RELATIVE_URL.'ajax.php')."\n"
            .NAJAX_Client::register('Suggestor', APP_RELATIVE_URL.'ajax.php')."\n");
        
        if ($display) {
            //print_r($tpl);
            $tpl->displayTemplate();
        } 
        return compact('list','list_info');
    }
}

?>
