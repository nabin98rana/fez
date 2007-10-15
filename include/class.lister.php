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
include_once(APP_INC_PATH . "class.search_key.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.citation.php");
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
            2 => array('file' => 'rss.tpl.html', 'title' => 'RSS Feed'),
            3 => array('file' => 'xml_feed.tpl.html', 'title' => 'XML Feed'),
            4 => array('file' => 'citation_only_list.tpl.html', 'title' => 'Citations Only'),
            5 => array('file' => 'simple_list.tpl.html', 'title' => 'Classic Simple View')
        );
    
		if ($tpl_idx != 0 && $tpl_index != 4) {
			$citationCache = false;
		} else {
			$citationCache = true;
		}
		$getSimple = false;
		if ($tpl_idx == 4) {
//			$getSimple = true;
		}
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
        if (Auth::canEdit() == 1) {
        	$tpl->assign("user_can_edit", 1);
        }
        $pager_row = Pager::getParam('pager_row',$params);
        if (empty($pager_row)) {
            $pager_row = 0;
        }
        $rows = Pager::getParam('rows',$params);
        if (empty($rows)) {
            $rows = APP_DEFAULT_PAGER_SIZE;
        }
        
        switch ($tpl_idx) {
        	case 2:
        		$rows = "ALL"; //If an RSS feed show all the rows
				$pager_row = 0;
        		break;
        	default:
        		break;
        }
        $options = Pager::saveSearchParams($params);           
        $search_keys = Search_Key::getQuickSearchList();
	    foreach ($search_keys as $skey => $svalue) {
			if (!in_array($svalue["sek_html_input"], array('multiple','allcontvocab','contvocab')) && $svalue["sek_smarty_variable"] != 'Status::getUnpublishedAssocList()') {
				$search_keys[$skey]["field_options"] = array("" => "any") + $search_keys[$skey]["field_options"];		
			} elseif (in_array($svalue["sek_html_input"], array('contvocab')) && is_array($options["searchKey".$svalue['sek_id']])) {
				$cv_temp = $search_keys[$skey]["field_options"]; 
				$search_keys[$skey]["field_options"] = array();
				$temp_value = "";
				foreach ($options["searchKey".$svalue['sek_id']] as $option) {		
					eval("\$temp_value = ".$search_keys[$skey]["sek_lookup_function"]."(".$option.");");		
					$search_keys[$skey]["field_options"][$option] = $temp_value;
				}
				
			} elseif (in_array($svalue["sek_html_input"], array('allcontvocab')) && is_array($options["searchKey".$svalue['sek_id']])) {
				$cv_temp = $search_keys[$skey]["field_options"]; 
				$search_keys[$skey]["field_options"] = array();
				$temp_value = "";
				foreach ($options["searchKey".$svalue['sek_id']] as $option) {		
					eval("\$temp_value = ".$search_keys[$skey]["sek_lookup_function"]."(".$option.");");		
					$search_keys[$skey]["field_options"][$option] = $temp_value;
				}
			}
	    }
        $tpl->assign("search_keys", $search_keys);

        $options['tpl_idx'] = $tpl_idx;     
        $tpl->assign("options", $options);
        $terms = Pager::getParam('terms',$params);
        $cat = Misc::GETorPOST('cat');
        $browse = Pager::getParam('browse',$params);
        $letter = Pager::getParam('letter',$params);
        $collection_pid = Pager::getParam('collection_pid',$params);
        $community_pid = Pager::getParam("community_pid",$params);
        //$sort_by = Pager::getParam('sort_by',$params);
		$sort_by = $options["sort_by"];
//		$sort_order = $options["sort_order"];
		$sort_by_list = array();

        $sort_by_list = array();
        if (!empty($community_pid)) {
            $sort_by_list = array(
                "searchKey".Search_Key::getID("Title") => 'Title',
                "searchKey".Search_Key::getID("Description") => 'Description',
                "searchKey".Search_Key::getID("Date") => 'Date'
            );
        } elseif (empty($community_pid) && empty($collection_pid) && empty($browse)) {
            $sort_by_list = array(
            	"searchKey".Search_Key::getID("Title") => 'Title',
           		"searchKey".Search_Key::getID("Description") => 'Description',
        		"searchKey".Search_Key::getID("File Downloads") => 'File Downloads',
				"searchKey".Search_Key::getID("Date") => 'Date'
            );
        } else {
        	$sort_by_list = array(
        	"searchKey".Search_Key::getID("Title") => 'Title',
        	"searchKey".Search_Key::getID("Description") => 'Description',
        	"searchKey".Search_Key::getID("File Downloads") => 'File Downloads',
        	"searchKey".Search_Key::getID("Date") => 'Date'
			);
        }
       //print_r($options);
       //print_r($sort_by_list);
        if (($cat == 'search' || $cat == 'all_fields' || $cat == 'quick_filter') && $options["searchKey0"] != "") {        	
            $sort_by_list['searchKey0'] = "Search Relevance";           
            if ((Misc::GETorPOST("sort_by")) == "") {
            	$sort_by = "searchKey0";            	
            }
            if (!is_numeric(Misc::GETorPOST("sort_order")) && ($sort_by == "searchKey0")) { // if searching by all fields and sort order not specifically set in the querystring (from a manual sort order change) than make search revelance sort descending
            	$options["sort_order"] = 1; // DESC relevance
        	}
        }
        
        $tpl->assign('sort_by_list', $sort_by_list);
        //print_r($sort_by_list);
		$sort_by_keys = $sort_by_list;
        //$sort_by_keys = array_keys($sort_by_list);
        if (!array_key_exists($sort_by, $sort_by_keys)) {
        	$sort_by = "searchKey".Search_Key::getID("Title");
        }
        
        //print_r($sort_by);
        $list_info = array();
        
        //get the bulk change workflows
		$bulk_workflows = WorkflowTrigger::getAssocListByTrigger("-1", 
			        							WorkflowTrigger::getTriggerId('Bulk Change')); 
//		print_r($bulk_workflows);
        $tpl->assign("bulk_workflows", $bulk_workflows);

		$bulk_search_workflows = WorkflowTrigger::getAssocListByTrigger("-1", 
			        							WorkflowTrigger::getTriggerId('Bulk Change Search')); 
//		print_r($bulk_search_workflows);
        $tpl->assign("bulk_search_workflows", $bulk_search_workflows);


        if (!empty($collection_pid)) {
            if (empty($sort_by)) {
                $sort_by = "searchKey".Search_Key::getID("Title");
            }
            // list a collection
            // first check the user has view rights over the collection object
            $record = new RecordObject($collection_pid);
            $canView = $record->canView(true);

            $tpl->assign("isViewer", $canView);

            if ($canView) {

                $tpl->assign("xdis_id", Record::getSearchKeyIndexValue($collection_pid, "Display Type"));
                //$collection_details = Collection::getDetails($collection_pid);
                $parents = Record::getParentsDetails($collection_pid);
                $tpl->assign("parents", $parents);
                $collection_xdis_id = Collection::getCollectionXDIS_ID();
//                $userPIDAuthGroups = Auth::getIndexAuthorisationGroups($collection_details);
                $userPIDAuthGroups = AuthIndex::getIndexAuthRoles($collection_pid);
                $isCreator = @$userPIDAuthGroups['isCreator'] == 1;
                $tpl->assign("isCreator", $isCreator);
                $isEditor = @$userPIDAuthGroups['isEditor'] == 1;
                $tpl->assign("isEditor", $isEditor);
                $options = Search_Key::stripSearchKeys($options);                                                           
                $options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
			    $options["searchKey".Search_Key::getID("isMemberOf")] = $collection_pid; // 

                $list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);	
                
                
                //$list = Collection::getListing($collection_pid, $pager_row, $rows, $sort_by);
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("list_heading", "List of Records in ".$collection_details[0]['title'][0]." Collection");
                $tpl->assign("list_type", "collection_records_list");

                $tpl->assign("collection_pid", $collection_pid);
                $childXDisplayOptions = Record::getSearchKeyIndexValue($collection_pid, "XSD Display Option");
                //$childXDisplayOptions = Collection::getChildXDisplayOptions($collection_pid);
                if (count($childXDisplayOptions) > 0) {
                    $tpl->assign("childXDisplayOptions", $childXDisplayOptions);
                } else {
                    $tpl->assign("childXDisplayOptions", 0);
                } 
            } else {
                $tpl->assign("show_not_allowed_msg", true);
            } 
        } elseif (!empty($community_pid)) {
            /*if (empty($sort_by)) {
                $sort_by = "searchKey".Search_Key::getID("Title");
            }*/
			$sort_by = "searchKey".Search_Key::getID("Title");
            // list collections in a community
            // first check the user has view rights over the collection object
            $record = new RecordObject($community_pid);
            $canView = $record->canView(true);
            $tpl->assign("isViewer", $canView);
            if ($canView) {	
                $tpl->assign("community_pid", $community_pid);
                //$xdis_id = Collection::getCollectionXDIS_ID();
                //$community_xdis_id = Community::getCommunityXDIS_ID();
                $userPIDAuthGroups = AuthIndex::getIndexAuthRoles($community_pid);
                $isCreator = @$userPIDAuthGroups['isCreator'] == 1;
                $tpl->assign("isCreator", $isCreator);
                $isEditor = @$userPIDAuthGroups['isEditor'] == 1;
                $tpl->assign("isEditor", $isEditor);
                $tpl->assign("xdis_id", $xdis_id);	
                //$community_details = Community::getDetails($community_pid);
                $community_title = Record::getSearchKeyIndexValue($community_pid, "Title");
                
                $options = Search_Key::stripSearchKeys($options);                                                           
            	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
				$options["searchKey".Search_Key::getID("isMemberOf")] = $community_pid; // 
            	$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);	
                
                
                //$list = Collection::getListing($community_pid, $pager_row, $rows, $sort_by);
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("list_heading", "List of Collections in ".$community_title." Community");
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
/*        } elseif ($cat == "all_fields") {
        	//print_r($options); echo $sort_by;
        	if (empty($sort_by)) {
        		if ($options["search_keys0"] == "") {
        			$sort_by = "searchKey".Search_Key::getID("Title");
        		} else {
        			$sort_by = "searchKey0"; // Search Relevance
        			$options["sort_dir"] = 1;
        		}
        	}

        	// search Fez
        	$options = Pager::saveSearchParams();
        	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
        	$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by);

        	$list_info = @$list["info"];
        	$terms = @$list_info['search_info'];
        	$list = @$list["list"];
        	$tpl->assign("list_heading", "Search Results ($terms)");
        	$tpl->assign("list_type", "all_records_list"); */
/*        } elseif ($cat == "search") {
            if (empty($sort_by)) {
                $sort_by = "searchKey".Search_Key::getID("Title");
            }
            $search_keys = Search_Key::getQuickSearchList();
            // search 
            $list = Collection::advSearchListing($pager_row, $rows, $sort_by);	
            $list_info = @$list["info"];
            $terms = @$list_info['search_info'];
            $list = @$list["list"];
            $tpl->assign("list_heading", "Search Results ($terms)");
            $tpl->assign("list_type", "all_records_list");*/
        } elseif ($browse == "latest") {
            // browse by latest additions / created date desc
            // reget the order by thing so we can change the default
            if (empty($sort_by)) {
                $sort_by = 'Created Date';
            }
			$options = array();
			$options["sort_order"] = "1";
			$sort_by = "searchKey".Search_Key::getID("Created Date");
			$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
			$list = Record::getListing($options, $approved_roles=array("Lister"), $pager_rows,$rows, $sort_by, $getSimple, $citationCache);
//            $list = Collection::browseListing($pager_row, $rows, "Created Date", $sort_by);
            //print_r($list);
            $list_info = $list["info"];
            $list = $list["list"];
			
            $search_keys = Search_Key::getQuickSearchList();
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
            $search_keys = Search_Key::getQuickSearchList();
            if (empty($sort_by)) {
           		$sort_by = "searchKey".Search_Key::getID("Title");
            }
            if (is_numeric($year)) {	
//              $list = Collection::browseListing($pager_row, $rows, "Date", $sort_by);//
				$options = array();
//				$sort_by = "searchKey".Search_Key::getID("Date");
				$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
				$options["searchKey".Search_Key::getID("Date")] = array();
				$options["searchKey".Search_Key::getID("Date")]["filter_type"] = "between";
				$options["searchKey".Search_Key::getID("Date")]["filter_enabled"] = 1;
				$options["searchKey".Search_Key::getID("Date")]["start_date"] = $year."-01-01";
				$options["searchKey".Search_Key::getID("Date")]["end_date"] = $year."-12-31";			
				$list = Record::getListing($options, $approved_roles=array("Lister"), $pager_rows,$rows, $sort_by, $getSimple, $citationCache);
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("browse_heading", "Browse By Year ".$year);
                $tpl->assign("list_heading", "List of Records");
            } else {
                $list = Collection::listByAttribute($pager_row, $rows, "Date", $sort_by);
                $list_info = $list["info"];
                $list = $list["list"];
                $tpl->assign("browse_heading", "Browse By Year");
            }
            $tpl->assign("browse_type", "browse_year");
        } elseif (($browse == "author") || ($browse == "author_id")) {
            // browse by author
            $author = Pager::getParam('author',$params);
            $author_id = Pager::getParam('author_id',$params);
            if (Misc::GETorPOST("sort_by") == "") {            	
                $sort_by = "searchKey".Search_Key::getID("Title");
            }
            if (!empty($author_id)) {            	
            	$options = Search_Key::stripSearchKeys($options);                                                           
            	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
				$options["searchKey".Search_Key::getID("Author ID")] = $author_id; //
				$author = Author::getFullname($author_id); 
            	$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by);
            	//print_r($list);	                        	
                //$list = Collection::browseListing($pager_row, $rows, "Author ID", $sort_by);
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("browse_heading", "Browse By Author ID - ".$author);
                $tpl->assign("list_heading", "Browse By Author ID - ".$author);
            } elseif (!empty($author)) {	
	        	$options = Search_Key::stripSearchKeys($options);                                                           
            	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
				$options["searchKey".Search_Key::getID("Author")] = $author; //
            	$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);    
//                $list = Collection::browseListing($pager_row, $rows, "Author", $sort_by);
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("browse_heading", "Browse By Author Name - ".$author);
			    $tpl->assign("list_heading", "Browse By Author Name - ".$author);	                
            } else {
            	if ($browse == "author_id") {
	                $list = Collection::listByAuthor($pager_row, $rows, $sort_by, $letter);
	                $list_info = $list["info"];
	                $list = $list["list"];
					
	                $tpl->assign("browse_heading", "Browse By ".APP_NAME." Author ID");
				    $tpl->assign("list_heading", "Browse By ".APP_NAME." Author ID");
            	} else {
	                $list = Collection::listByAttribute($pager_row, $rows, "Author", $sort_by, $letter);
	                $list_info = $list["info"];
	                $list = $list["list"];
					
	                $tpl->assign("browse_heading", "Browse By Author Name");
				    $tpl->assign("list_heading", "Browse By Author Name");            		
            	}
            }
            $tpl->assign("browse_type", "browse_author");
            $tpl->assign("alphabet_list", Misc::generateAlphabetArray());
        } elseif ($browse == "depositor") {
            // browse by depositor
            $depositor = Pager::getParam('depositor',$params);
			$depositor_fullname = User::getFullName($depositor);
			if (Misc::GETorPOST("sort_by") == "") {
				$sort_by = "searchKey".Search_Key::getID("Title");
			}
			if (!empty($depositor)) {				
				$options = Search_Key::stripSearchKeys($options);                                                           
            	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
				$options["searchKey".Search_Key::getID("Depositor")] = $depositor; // 
            	$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);	
                //$list = Collection::browseListing($pager_row, $rows, "Depositor",$sort_by);
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("browse_heading", "Browse By Depositor - ".$depositor_fullname);
			    $tpl->assign("list_heading", "Browse By Depositor - ".$depositor_fullname);	
            } else {
                $list = Collection::listByAttribute($pager_row, $rows, "Depositor",$sort_by);
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("browse_heading", "Browse By Depositor");
			    $tpl->assign("list_heading", "Browse By Depositor");					
            }
            $tpl->assign("browse_type", "browse_depositor");			
        } elseif ($browse == "subject") {
        	if (Misc::GETorPOST("sort_by") == "") {
        		$sort_by = "searchKey".Search_Key::getID("Title");
        	}
            // browse by subject
            $parent_id = Pager::getParam('parent_id',$params);
            if (is_numeric($parent_id)) {	
                $subject_list = Controlled_Vocab::getList($parent_id);
                $treeIDs = Controlled_Vocab::getAllTreeIDs($parent_id);
                $subject_count = Collection::getCVCountSearch($treeIDs, $parent_id);
				$options = Search_Key::stripSearchKeys($options);                                                           
            	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
				$options["searchKey".Search_Key::getID("Subject")] = $parent_id; // 
            	$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);	
                
//                $list = Collection::browseListing($pager_row, $rows, "Subject",$sort_by);	
                $list_info = $list["info"];
                $list = $list["list"];		
				
            } else {
                $subject_list = Controlled_Vocab::getList();	
            }
            //exit;
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
        } elseif ($cat == "quick_filter") {
        	if (empty($sort_by)) {      
        		if ($options["searchKey0"] == "") {
        			$sort_by = "searchKey".Search_Key::getID("Title");	
        		} else {        			
        			$sort_by = "searchKey0"; // Search Relevance
					$options["sort_dir"] = 1;
        		}
        	}        	
        	// search Fez			
			//$options = Pager::saveSearchParams(); // already done up above
			// enforce certain search parameters			
			$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only

			$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);        	

        	$list_info = @$list["info"];
        	$terms = @$list_info['search_info'];
        	$list = @$list["list"];
			
        	$tpl->assign("list_heading", "Search Results ($terms)");        	 
        	$tpl->assign("list_type", "all_records_list");
        } else {
            // list all communities
            if (empty($sort_by)) {
                $sort_by = "searchKey".Search_Key::getID("Title");
            }            
            $xdis_id = Community::getCommunityXDIS_ID();
            $tpl->assign("xdis_id", $xdis_id);
            $options = Search_Key::stripSearchKeys($options);                                                           
            $options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
			$options["searchKey".Search_Key::getID("Object Type")] = 1; // enforce communities only
            $list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);	
            //$list = Community::getList($pager_row, $rows, $sort_by);
            $list_info = $list["info"];
            $list = $list["list"];
			
            $tpl->assign("list_type", "community_list");
            $tpl->assign("list_heading", "List of Communities");
        }        
        
        $tpl->assign('sort_by_default', $sort_by);        
        $workflows_list = Misc::keyPairs(Workflow::getList(), 'wfl_id', 'wfl_title');
        $tpl->assign('workflows_list', $workflows_list);
        $tpl->assign("eserv_url", APP_BASE_URL."eserv.php");
        $tpl->assign('sort_order', $options["sort_order"]);
//		if ($tpl_idx == 0 || $tpl_idx == 4) {
//			$list = Citation::renderIndexCitations($list);
//		}
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
