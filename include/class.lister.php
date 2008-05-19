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
        
        /*
         * These are the only $_GET vars that can be passed to this page.
         * Strip out any that aren't in this list
         */
        $args = array(
            'browse'        =>  'string',
            'author_id'     =>  'numeric',
            'collection_pid'=>  'string',
            'community_pid' =>  'string',
            'cat'           =>  'string',
            'author'        =>  'string',
            'tpl'           =>  'numeric',
            'year'          =>  'numeric',
            'rows'          =>  'numeric',
            'pager_row'     =>  'numeric',
            'sort'          =>  'string',
            'sort_by'       =>  'string',
            'search_keys'   =>  'array',
            'order_by'      =>  'string',
            'value'         =>  'string',
        );
        foreach ($args as $getName => $getType) {
            
            if( Misc::sanity_check($_GET[$getName], $getType) ) {
                $GET_VARS[$getName] = $_GET[$getName];
            }
            
        }
        
        $_GET = $GET_VARS;
        
        $tpl = new Template_API();
		if (is_numeric($_GET['tpl'])) {
        	$tpl_idx = intval($_GET['tpl']);
		} else {
			$tpl_idx = 0;
		}
        $tpls = array(
            0 => array('file' => 'list.tpl.html', 'title' => 'Default'),
            2 => array('file' => 'rss.tpl.html', 'title' => 'RSS Feed'),
            3 => array('file' => 'xml_feed.tpl.html', 'title' => 'XML Feed'),
            4 => array('file' => 'citation_only_list.tpl.html', 'title' => 'Citations Only'),
            5 => array('file' => 'simple_list.tpl.html', 'title' => 'Classic Simple View')
        );
		// disabled edit authors view until it can be looked again in the future
        // 1 => array('file' => 'views/list/author_bulk_edit.tpl.html', 'title' => 'Edit Authors'),
    
		if ($tpl_idx != 0 && $tpl_index != 4) {
			$citationCache = false;
		} else {
			$citationCache = true;
		}
		$getSimple = false;
		if ($tpl_idx == 2 || $tpl_idx == 3) {
			$header = "Content-type: application/xml\n";
			header($header);
		}
		
        $tpl_file = $tpls[$tpl_idx]['file'];    
        $tpl->setTemplate($tpl_file);
        
        if (Auth::userExists($username)) { // if the user is registered as a Fez user
            $tpl->assign("isFezUser", $username);
        }
        
        $pager_row = $_REQUEST['pager_row'];
        if (empty($pager_row)) {
            $pager_row = 0;
        }
        
        
        $rows = $_REQUEST['rows'];
        if (empty($rows)) {
            
            if(!empty($_SESSION['rows'])) {
                $rows = $_SESSION['rows'];
            } else {
                $rows = APP_DEFAULT_PAGER_SIZE;
            }
            
        } else {
            $_SESSION['rows'] = $rows;
        }
        
        switch ($tpl_idx) {
        	case 2:
//        		$rows = "ALL"; //If an RSS feed show all the rows
//				$pager_row = 0;
        		break;
        	default:
        		break;
        }
        
        $cookie_key = Pager::getParam('form_name',$params);
        $options = Pager::saveSearchParams($params, $cookie_key);
	    
	    $getFunction = 'getListing';
        if( APP_SOLR_SWITCH == "ON" )
        {
            $getFunction = 'getSearchListing';
        }

        $options['tpl_idx'] = $tpl_idx;     
        $tpl->assign("options", $options);
        
        $terms          = $_REQUEST['terms'];
        $cat            = $_REQUEST['cat'];
        $browse         = $_REQUEST['browse'];
        $letter         = $_REQUEST['letter'];
        $collection_pid = $_REQUEST['collection_pid'];
        $community_pid  = $_REQUEST['community_pid'];
        
		if (!empty($collection_pid)) {
			$pid = $collection_pid;
			$browse_mode = "collection";
		} elseif (!empty($community_pid)) {
			$pid = $community_pid;
			$browse_mode = "community";
		} else {
			$browse_mode = "list";
		}
		$tpl->assign("pid", $pid);
		$tpl->assign("browse_mode", $browse_mode);
		$sort_by = $options["sort_by"];
		$operator = $options["operator"];

		/*
		 * These options are used in a dropdown box to allow the 
		 * user to sort a list
		 */
        $sort_by_list = array(
            "searchKey".Search_Key::getID("Title") => 'Title',
            "searchKey".Search_Key::getID("Description") => 'Description',
            "searchKey".Search_Key::getID("File Downloads") => 'File Downloads',
            "searchKey".Search_Key::getID("Date") => 'Date',
            "searchKey".Search_Key::getID("Sequence") => 'Sequence'
        );
        
        if (($cat == 'search' || $cat == 'all_fields' || $cat == 'quick_filter') && $options["searchKey0"] != "") {        	
            $sort_by_list['searchKey0'] = "Search Relevance";           
            if ((Misc::GETorPOST("sort_by")) == "") {
            	$sort_by = "searchKey0";            	
            }
            
            // if searching by Title, Abstract, Keywords and sort order not specifically set in the querystring 
            // (from a manual sort order change) than make search revelance sort descending
            if (!is_numeric(Misc::GETorPOST("sort_order")) && ($sort_by == "searchKey0")) {
            	$options["sort_order"] = 1; // DESC relevance
        	}
        }
        
		// Default Sort
        if (!array_key_exists($sort_by, $sort_by_list)) {
        	$sort_by = "searchKey".Search_Key::getID("Title");
        }
        
        $list_info = array();
        
        //get the bulk change workflows
		$bulk_workflows = WorkflowTrigger::getAssocListByTrigger("-1", WorkflowTrigger::getTriggerId('Bulk Change'));
		$bulk_search_workflows = WorkflowTrigger::getAssocListByTrigger("-1", WorkflowTrigger::getTriggerId('Bulk Change Search')); 
		
        $tpl->assign("bulk_workflows",          $bulk_workflows);
        $tpl->assign("bulk_search_workflows",   $bulk_search_workflows);
        
        if (!empty($collection_pid)) {
            
            // list a collection
            // first check the user has view rights over the collection object
            $record = new RecordObject($collection_pid);
            $canList = $record->canList(true);

            $tpl->assign("isLister", $canList);

            if ($canList) {
                
                $tpl->assign("xdis_id", Record::getSearchKeyIndexValue($collection_pid, "Display Type"));
                $parents = Record::getParentsDetails($collection_pid);

                $tpl->assign("parents", $parents);
                $collection_xdis_id = Collection::getCollectionXDIS_ID();
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
                $title = Record::getSearchKeyIndexValue($collection_pid, "Title");
                $display_type = Record::getSearchKeyIndexValue($collection_pid, "Display Type");
				$display_type = array_values($display_type);
				$citation = Record::getCitationIndex($collection_pid);
                $tpl->assign("list_heading", "List of Records in ".$title." ".$display_type[0]);
                $tpl->assign("list_heading_citation", "List of Records in ".$citation." ".$display_type[0]);
                $tpl->assign("list_type", "collection_records_list");

                $tpl->assign("collection_pid", $collection_pid);
                $childXDisplayOptions = Record::getSearchKeyIndexValue($collection_pid, "XSD Display Option");
                
                if (count($childXDisplayOptions) > 0) {
                    $tpl->assign("childXDisplayOptions", $childXDisplayOptions);
                } else {
                    $tpl->assign("childXDisplayOptions", 0);
                } 
                
                unset($_GET['collection_pid']);
                
                $tpl->assign('url', Misc::query_string_encode($_GET));
                
            } else {
                $tpl->assign("show_not_allowed_msg", true);
            }
            
        } elseif (!empty($community_pid)) {
            
            include_once(APP_INC_PATH . "class.custom_view.php");
            
			$sort_by = "searchKey".Search_Key::getID("Title");
			
            // list collections in a community
            // first check the user has view rights over the collection object
            $record = new RecordObject($community_pid);
            $canView = $record->canView(true);
            $tpl->assign("isViewer", $canView);
            if ($canView) {	
                
                /* 
                 * Custom View
                 */
                $customView = Custom_View::getCommCview($community_pid);
                
                if($customView) {
                    $path       = $customView['cview_folder'];
                    $header     = APP_PATH. $path . $customView['cview_header_tpl'];
                    $content    = APP_PATH. $path . $customView['cview_content_tpl'];
                    $footer     = APP_PATH. $path . $customView['cview_footer_tpl'];
                    $css        = APP_PATH. $path . $customView['cview_css'];
                    
                    if( is_file($header) )
                        $tpl->assign('cv_header',   $header);
                    
                    if( is_file($content) )
                        $tpl->assign('cv_content',  $content);
                        
                    if( is_file($footer) )
                        $tpl->assign('cv_footer',   $footer);
                        
                    if( is_file($css) )
                        $tpl->assign('cv_css',      $css);
                }
                
                $tpl->assign("community_pid", $community_pid);
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
                
                $list_info = $list["info"];
                $list = $list["list"];
                $title = Record::getSearchKeyIndexValue($community_pid, "Title");
                $display_type = Record::getSearchKeyIndexValue($community_pid, "Display Type");
				$display_type = array_values($display_type);
				$citation = Record::getCitationIndex($community_pid);
				
                $tpl->assign("list_heading", "List of Collections in ".$title." ".$display_type[0]);		
                $tpl->assign("list_heading_citation", "List of Collections in ".$citation." ".$display_type[0]);
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
            
            /*
             * Remove these sort options when viewing a list of community
             */
        	unset($sort_by_list["searchKey".Search_Key::getID("File Downloads")]);
        	unset($sort_by_list["searchKey".Search_Key::getID("Sequence")]);
        	unset($sort_by_list["searchKey".Search_Key::getID("Description")]);
        	unset($sort_by_list["searchKey".Search_Key::getID("Date")]);
        	
        	/*
        	 * Remove 'citation' and 'classic' display option when viewing a list of subjects
        	 */
        	unset($tpls[4]);
        	unset($tpls[5]);
            
        } elseif ($browse == "latest") {
            
			$options = array();
			$options["sort_order"] = "1";
			$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
			
			$sort_by = "searchKey".Search_Key::getID("Created Date");
			
			$list = Record::getListing($options, $approved_roles=array("Lister"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);
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
            $year = Lister::getValue('year');
            if (is_numeric($year)) {
                
				$options = Search_Key::stripSearchKeys($options);
				$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
				$options["searchKey".Search_Key::getID("Date")] = array();
				$options["searchKey".Search_Key::getID("Date")]["filter_type"] = "between";
				$options["searchKey".Search_Key::getID("Date")]["filter_enabled"] = 1;
				$options["searchKey".Search_Key::getID("Date")]["start_date"] = $year."-01-01";
				$options["searchKey".Search_Key::getID("Date")]["end_date"] = $year."-12-31";			
				$list = Record::getListing($options, $approved_roles=array("Lister"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("year", $year);
                $tpl->assign("browse_heading", "Browse By Year ".$year);
                $tpl->assign("list_heading", "List of Records");
            } else {
                $list = Collection::listByAttribute($pager_row, $rows, "Date", $sort_by);
                $list_info = $list["info"];
                $list = $list["list"];
                $tpl->assign("browse_heading", "Browse By Year");
                
                /*
                 * Remove these sort options when viewing a list of subjects
                 */
            	unset($sort_by_list["searchKey".Search_Key::getID("File Downloads")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Sequence")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Description")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Date")]);
            	
            	/*
            	 * Remove 'citation' and 'classic' display option when viewing a list of subjects
            	 */
            	unset($tpls[4]);
            	unset($tpls[5]);
            }
            $tpl->assign("browse_type", "browse_year");
            
        } elseif (($browse == "author") || ($browse == "author_id")) {
            
            // browse by author
            if( $browse == "author") {
                
                if( strlen(Lister::getValue('author')) == 1 ) {
                    $letter = Lister::getValue('author');
                } else {
                    $author = Lister::getValue('author');
                }
            }
                
            if( $browse == "author_id" )
                $author_id = Lister::getValue('author_id');
            
            if (!empty($author_id)) {            	
            	$options = Search_Key::stripSearchKeys($options);                                                           
            	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
				$options["searchKey".Search_Key::getID("Author ID")] = $author_id; //
				$author = Author::getFullname($author_id); 
            	$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);
            	
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("author_id", $author_id);
                $tpl->assign("browse_heading", "Browse By Author ID - ".$author);
                $tpl->assign("list_heading", "Browse By Author ID - ".$author);
            } elseif (!empty($author)) {	
	        	$options = Search_Key::stripSearchKeys($options);                                                           
            	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
				$options["searchKey".Search_Key::getID("Author")] = $author; //
            	$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("author", $author);
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
            	
            	/*
                 * Remove these sort options when viewing a list of authors
                 */
            	unset($sort_by_list["searchKey".Search_Key::getID("File Downloads")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Sequence")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Description")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Date")]);
            	
            	/*
            	 * Remove 'citation' display option when viewing a list of authors
            	 */
            	unset($tpls[4]);
            }
            $tpl->assign("browse_type", "browse_author");
            $tpl->assign("alphabet_list", Misc::generateAlphabetArray());
        
        } elseif ($browse == "depositor") {
            
            // browse by depositor
            $depositor = Lister::getValue('depositor');
			$depositor_fullname = User::getFullName($depositor);
			
			if (!empty($depositor)) {				
				$options = Search_Key::stripSearchKeys($options);
            	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
				$options["searchKey".Search_Key::getID("Depositor")] = $depositor; // 
            	$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("depositor", $depositor);
                $tpl->assign("browse_heading", "Browse By Depositor - ".$depositor_fullname);
			    $tpl->assign("list_heading", "Browse By Depositor - ".$depositor_fullname);	
            } else {
                $list = Collection::listByAttribute($pager_row, $rows, "Depositor",$sort_by);
                $list_info = $list["info"];
                $list = $list["list"];
				
                $tpl->assign("browse_heading", "Browse By Depositor");
			    $tpl->assign("list_heading", "Browse By Depositor");
			    
			    /*
                 * Remove these sort options when viewing a list of Depositors
                 */
            	unset($sort_by_list["searchKey".Search_Key::getID("File Downloads")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Sequence")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Description")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Date")]);
            	
            	/*
            	 * Remove 'citation' display option when viewing a list of depositors
            	 */
            	unset($tpls[4]);
            }
            $tpl->assign("browse_type", "browse_depositor");
            
        } elseif ($browse == "subject") {
        	
            // browse by subject
            $parent_id = Lister::getValue('parent_id');
            
            if (is_numeric($parent_id)) {	
                $subject_list = Controlled_Vocab::getList($parent_id);
                $treeIDs = Controlled_Vocab::getAllTreeIDs($parent_id);
                $subject_count = Collection::getCVCountSearch($treeIDs, $parent_id);
                
				$options = Search_Key::stripSearchKeys($options);                                                           
            	$options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
				$options["searchKey".Search_Key::getID("Subject")] = $parent_id; // 
            	$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);	
                
                $list_info = $list["info"];
                $list = $list["list"];
                
                $tpl->assign('parent_id', $parent_id);
				
            } else {
                $subject_list = Controlled_Vocab::getList();
                
                /*
                 * Remove these sort options when viewing a list of subjects
                 */
            	unset($sort_by_list["searchKey".Search_Key::getID("File Downloads")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Sequence")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Description")]);
            	unset($sort_by_list["searchKey".Search_Key::getID("Date")]);
            	
            	/*
            	 * Remove 'citation' display option when viewing a list of subjects
            	 */
            	unset($tpls[4]);
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
            
        } elseif ($cat == "quick_filter") { // Advanced Search

        	include_once(APP_INC_PATH . "class.spell.php");
        	include_once(APP_INC_PATH . "class.language.php");
        	
        	if (empty($sort_by)) {      
        		if ($options["searchKey0"] == "") {
        			$sort_by = "searchKey".Search_Key::getID("Title");	
        		} else {        			
        			$sort_by = "searchKey0"; // Search Relevance
					$options["sort_dir"] = 1;
        		}
        	}
        	
        	// search Fez
        	
			// enforce certain search parameters			
			// enforce published records only
			$options["searchKey".Search_Key::getID("Status")] = array(
			     'override_op'   =>  'AND',
			     'value'         =>  2,
			);
			
			//$list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache, $operator);
			$list = Record::$getFunction($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);
			
			$spell = new spellcheck(APP_DEFAULT_LANG);
			
			if( $spell )
			{
			    $spell_suggest = $spell->query_suggest($_REQUEST['search_keys'][0]);
			    
			    // Did pspell return any suggestions?
			    if( $spell_suggest )
			    {
			        // Replace search_key[0]={search} with search_key[0]={suggestion}
			        // search key 0 will be 'Title, Abstract, Keywords'
                    $tpl->assign("spell_suggest", $spell_suggest);
                    
                    $exclude[] = 'search_keys';
                    $tpl->assign('spell_suggest_url', Misc::query_string_encode($_GET,$exclude) . '&search_keys[0]='.$spell_suggest);
                    array_pop($exclude);
			    } 
			}
			
        	$list_info = @$list["info"];
        	$terms = @$list_info['search_info'];
        	$list = @$list["list"];
			
        	// KJ@ETH
        	$tpl->assign("major_function", "search");
			$tpl->assign("q", htmlspecialchars($_REQUEST['search_keys'][0]));
        	
        	$tpl->assign("list_heading", htmlspecialchars("Search Results ($terms)"));        	 
        	$tpl->assign("list_type", "all_records_list");
        } else {
                       
            $xdis_id = Community::getCommunityXDIS_ID();
            $tpl->assign("xdis_id", $xdis_id);
            $options = Search_Key::stripSearchKeys($options);                                                           
            $options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
            $options["searchKey".Search_Key::getID("Object Type")] = 1; // enforce communities only
            $list = Record::getListing($options, array("Lister", "Viewer"), $pager_row, $rows, $sort_by, $getSimple, $citationCache);
            $list_info = $list["info"];
            $list = $list["list"];
            
            $tpl->assign("list_type", "community_list");
            $tpl->assign("list_heading", "List of Communities");
            
            /*
             * Remove these sort options when viewing a list of communities
             */
        	unset($sort_by_list["searchKey".Search_Key::getID("File Downloads")]);
        	unset($sort_by_list["searchKey".Search_Key::getID("Sequence")]);
        	unset($sort_by_list["searchKey".Search_Key::getID("Description")]);
        	unset($sort_by_list["searchKey".Search_Key::getID("Date")]);
        	
        	/*
        	 * Remove 'citation' and 'classic' display option when viewing a list of communities
        	 */
        	unset($tpls[4]);
        	unset($tpls[5]);
        }
        
        $tpl->assign('rows', $rows);
        $tpl->assign('tpl_list', array_map(create_function('$a','return $a[\'title\'];'), $tpls));
        $tpl->assign('browse', $browse);
        $tpl->assign('sort_by_list', $sort_by_list);
		$tpl->assign("cycle_colours", "#FFFFFF," . APP_CYCLE_COLOR_TWO);
        $tpl->assign('sort_by_default', $sort_by);
        $tpl->assign("eserv_url", APP_BASE_URL."eserv/");
        $tpl->assign('sort_order', $options["sort_order"]);
        $tpl->assign("list", $list);
        $tpl->assign("list_info", $list_info);
        
        // Hack to get SCRIPT_URL without querystrings.
        // Usually we could get this info from $_SERVER['SCRIPT_URL'], but can't since 
        // we are doing rewrite rules on a per-directory basis via .htaccess file
        $PAGE_URL = preg_replace('/(\?.*)/','',$_SERVER['REQUEST_URI']);
        
        // When generating the URL's for sorting etc do not include 
        // these variables as they will already be in the PAGE_URL
        $exclude = array(
            'pager_row',
            'browse',
            'value',
            'community_pid',
        );
        
        if(count($_GET) > 0) {
            
            $exclude[] = 'rows';
            $tpl->assign('url_wo_rows', Misc::query_string_encode($_GET,$exclude));
            array_pop($exclude);
            
            $exclude[] = 'tpl';
            $tpl->assign('url_wo_tpl',  Misc::query_string_encode($_GET,$exclude));
            array_pop($exclude);
            
            $exclude[] = 'sort';
            $exclude[] = 'sort_by';
            $tpl->assign('url_wo_sort', Misc::query_string_encode($_GET,$exclude));
        }

        $tpl->assign('PAGE_URL', $PAGE_URL);
		$tpl->assign("template_mode", $tpl_idx);
		$tpl->assign("active_nav", "list");
        
        $tpl->registerNajax(NAJAX_Client::register('NajaxRecord', APP_RELATIVE_URL.'ajax.php')."\n"
            .NAJAX_Client::register('Suggestor', APP_RELATIVE_URL.'ajax.php')."\n");
            
        if ($display) {
            $tpl->displayTemplate();
        } 
        return compact('list','list_info');
    }
    
    
    function getValue($varName) {
        
        if(isset($_GET[$varName])) {
            
            return $_GET[$varName];
            
        } elseif(isset($_GET['value'])) {
            
            return $_GET['value'];
            
        }
        
        return false;
    }
}




?>
