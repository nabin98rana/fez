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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle the business logic related to the Fez OAI provider service.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.search_key.php");
include_once(APP_INC_PATH . "class.controlled_vocab.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.statistics.php");
include_once(APP_INC_PATH . "class.fulltext_index.php");

class OAI
{

    /**
     * Method used to get the list of records publicly available in the 
     * system.
     *
     * @access  public
     * @param   string $set oai set collection (optional). 	 	 
     * @param   integer $current_row The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return	 
     * @return  array The list of records 
     */
    function ListRecords($set, $identifier="", $current_row = 0, $max = 25, $order_by = 'Title', $from="", $until="", $setType)
    {
		$from = str_replace("T", " ", $from);
		$from = str_replace("Z", " ", $from);
		$until = str_replace("Z", " ", $until);		
		$until = str_replace("Z", " ", $until);		
        $order_dir = 'ASC';
		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row * $max;

        // this query broken into pieces to try and get some speed.

        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
//        $order_by = 'Title';
        $sekdet = Search_Key::getDetailsByTitle($order_by);		
        $data_type = $sekdet['xsdmf_data_type'];
		if (empty($data_type)) {
			$data_type = "varchar";			
		}
        $restrict_community = '';

		$authArray = Collection::getAuthIndexStmt(array("Viewer"));
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];

		$subjectList = XSD_HTML_Match::getXSDMF_IDsBySekTitle('Subject');
		$isMemberOfList = XSD_HTML_Match::getXSDMF_IDsBySekTitle('isMemberOf');
		$createdDateList = XSD_HTML_Match::getXSDMF_IDsBySekTitle('Created Date');		
		$statusList = XSD_HTML_Match::getXSDMF_IDsBySekTitle('Status');		
		$displayTypeList = XSD_HTML_Match::getXSDMF_IDsBySekTitle('Display Type');				
		$order_byList = XSD_HTML_Match::getXSDMF_IDsBySekTitle($order_by);				
		$sql_filter = array();
		$sql_filter['where'] = "";
		$sql_filter['elsewhere'] = "";
        if (!empty($identifier)) {
        	$sql_filter['where'][] = "r2.rmf_rec_pid = '".Misc::escapeString($identifier)."'";
        } elseif (!empty($set)) {
        	if ($setType == "isMemberOf") {
	        	$sql_filter['where'][] = "r2.rmf_varchar = '".Misc::escapeString($set)."'";
				$sql_filter['where'][] = "r2.rmf_xsdmf_id in (".implode(",", $isMemberOfList).")";
        	} else { //cont vocab
	        	$sql_filter['where'][] = "r2.rmf_varchar = '".Misc::escapeString($set)."'";
				$sql_filter['where'][] = "r2.rmf_xsdmf_id in (".implode(",", $subjectList).")";        		
        	}
		} else {
			$elsewhere = "";
//			return array();
//			$memberOfStmt = "";
		}

        $bodyStmtPart1 = "FROM  {$dbtp}record_matching_field AS r2
                    INNER JOIN {$dbtp}record_matching_field AS r3
                      ON r3.rmf_rec_pid_num = r2.rmf_rec_pid_num and r3.rmf_rec_pid = r2.rmf_rec_pid and r3.rmf_xsdmf_id in 
					 (".implode(",", $statusList).")
                      and r3.rmf_int=2 $joinStmt


                    $authStmt				

                    ";
        $bodyStmt = $bodyStmtPart1;
        
        if ($from != "" && $until != "") {
			$bodyStmt .=
                   " INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r6 
                    on r6.rmf_rec_pid_num = r2.rmf_rec_pid_num and r6.rmf_rec_pid = r2.rmf_rec_pid and r6.rmf_xsdmf_id in (".implode(",", $createdDateList).") 
                    and (r6.rmf_date >= '".$from."' and r6.rmf_date <= '".$until."')";
        	
        } elseif (!empty($from) && empty($until)) {
			$bodyStmt .=
                   " INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r6 
                    on r6.rmf_rec_pid_num = r2.rmf_rec_pid_num and r6.rmf_rec_pid = r2.rmf_rec_pid and r6.rmf_xsdmf_id in (".implode(",", $createdDateList).") 
                    and (r6.rmf_date >= '".$from."')";
        	
        } elseif (!empty($until) && empty($from)) {        	
			$bodyStmt .=
                   " INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r6 
                    on r6.rmf_rec_pid_num = r2.rmf_rec_pid_num and r6.rmf_rec_pid = r2.rmf_rec_pid and r6.rmf_xsdmf_id in (".implode(",", $createdDateList).") 
                    and (r6.rmf_date <= '".$until."')";
        	
        }
        

		if ($order_by != "") {
			
			$bodyStmt .=
                   " LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r5 
                    on r5.rmf_rec_pid_num = r2.rmf_rec_pid_num and r5.rmf_rec_pid = r2.rmf_rec_pid and r5.rmf_xsdmf_id in (".implode(",", $order_byList).")";
		}                    
	    $bodyStmt .=                   
					 ( ($sql_filter['where']) != "" ? "WHERE ".implode("\r\nAND ", $sql_filter['where']) : $elsewhere) . "
					group by r2.rmf_rec_pid_num
             ";
		


        $stmt = "
                    SELECT ".APP_SQL_CACHE." SQL_CALC_FOUND_ROWS r2.rmf_rec_pid
                    $bodyStmt
					order by ";
        if ($order_by != "") {
			$stmt .= " r5.rmf_$data_type $order_dir, r2.rmf_rec_pid_num ASC";
        } else {
			$stmt .= " r2.rmf_rec_pid_num ASC";        	
        }
	    $stmt .= "		
                    LIMIT $start, $max					                   
            ";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$total_rows = $GLOBALS["db_api"]->dbh->getOne('SELECT FOUND_ROWS()');
		$return = array ();
		$return['count'] = $count[0];
		foreach ($res as $result) {
			$return['list'][] = $result['rmf_rec_pid'];

		}		
		if (!isset($return['list'])) {
			return array();
		}

        $stmtWrap = "SELECT ".APP_SQL_CACHE."  r1.*, x1.*, s1.*, k1.*, d1.* 
            FROM {$dbtp}record_matching_field AS r1
            INNER JOIN {$dbtp}xsd_display_matchfields AS x1
            ON r1.rmf_xsdmf_id = x1.xsdmf_id
            LEFT JOIN {$dbtp}xsd_loop_subelement s1 
            ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
            LEFT JOIN {$dbtp}search_key k1 
            ON (k1.sek_id = x1.xsdmf_sek_id)
            LEFT JOIN {$dbtp}xsd_display d1  
            ON (d1.xdis_id = r1.rmf_int and k1.sek_title = 'Display Type')
			WHERE r1.rmf_rec_pid in ('".implode("','", $return['list'])."')
			ORDER BY r1.rmf_rec_pid_num ASC, r1.rmf_id ASC
            ";                    
                                        
		$res = $GLOBALS["db_api"]->dbh->getAll($stmtWrap, DB_FETCHMODE_ASSOC);
	
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
       		$return = array();
			$return = OAI::makeReturnList($res);

//	        $return = Collection::makeSecurityReturnList($return);
			$hidden_rows = 0;
//			$return = Auth::getIndexAuthorisationGroups($return);
//			$return = Misc::cleanListResults($return);  
	//		$total_rows = count($return);
			if (($start + $max) < $total_rows) {
		        $total_rows_limit = $start + $max;
			} else {
			   $total_rows_limit = $total_rows;
			}
	
			$total_pages = ceil($total_rows / $max);
	        $last_page = $total_pages - 1;
	//		$return = Misc::limitListResults($return, $start, ($start + $max));		
			// add the available workflow trigger buttons
	
	    		// Now generate the META Tag headers
//			print_r($res); exit;
//			$oai_dc = array();
			$return = array_values($return);
//			print_r($return); exit;
            return array(
                "list" => $return,
                "info" => array(
                    "current_page"  => $current_row,
                    "start_offset"  => $start,
                    "end_offset"    => $total_rows_limit,
                    "total_rows"    => $total_rows,
                    "total_pages"   => $total_pages,
                    "previous_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page,
                    "hidden_rows"     => 0
                )
            );
        }
    }

    
    
    function makeReturnList($res, $statsFlag = 0) {
		$securityfields = Auth::getAllRoles();
        $return = array();
		foreach ($res as $result) {
			if ($result['sek_title'] == 'isMemberOf') {
                $return[$result['rmf_rec_pid']]['isMemberOf'][] = $result['rmf_varchar'];
			}			
			if (($result['sek_title'] == 'Created Date' || $result['sek_title'] == 'Updated Date') && !(empty($result['rmf_date']))) {
				if (!empty($result['rmf_date'])) {
					$result['rmf_date'] = Date_API::getFedoraFormattedDate($result['rmf_date']);
				}
            }
            if  ($result['sek_title'] == 'Subject' && ((($result['xsdmf_html_input'] == "contvocab") || ($result['xsdmf_html_input'] == "contvocab_selector")) && ($result['xsdmf_cvo_save_type'] != 1))) {            
				$return[$result['rmf_rec_pid']]['subject_id'][] = $result['rmf_'.$result['xsdmf_data_type']];
            	$result['rmf_'.$result['xsdmf_data_type']] = Controlled_Vocab::getTitle($result['rmf_'.$result['xsdmf_data_type']]);
            }
            if (($result['xsdmf_enabled'] == 1) && ($result['xsdmf_meta_header'] == 1) && (trim($result['xsdmf_meta_header_name']) != "")) {			
            	$value = "";
				$oai_name = str_replace(".", ":", strtolower($result['xsdmf_meta_header_name']));
				if ($return[$result['rmf_rec_pid']]['oai_dc'] == "") {
					$return[$result['rmf_rec_pid']]['oai_dc'] = '<dc:identifier>'.APP_BASE_URL.'view.php?pid='.$result['rmf_rec_pid'].'</dc:identifier>'."\n";
				}
				if ($result['xsdmf_data_type'] == "date") {
					if (!empty($value)) {
						$value = Date_API::getFedoraFormattedDate($result['rmf_'.$result['xsdmf_data_type']]);
					}
				} else {
					$value = htmlspecialchars($result['rmf_'.$result['xsdmf_data_type']]);
				}
            	$return[$result['rmf_rec_pid']]['oai_dc'] .=  '<'.$oai_name.'>'.$value.'</'.$oai_name.'>'."\n";;
            }						
			if (@$result['sek_title'] == 'isMemberOf') {
				if (!is_array(@$return[$result['rmf_rec_pid']]['isMemberOf'])) {
					$return[$result['rmf_rec_pid']]['isMemberOf'] = array();
				}
				if (!in_array($result['rmf_varchar'], $return[$result['rmf_rec_pid']]['isMemberOf'])) {
					array_push($return[$result['rmf_rec_pid']]['isMemberOf'], $result['rmf_varchar']);
				}
			}			
			// get the document type
            if (!empty($result['xdis_title'])) {
                if (!is_array(@$return[$result['rmf_rec_pid']]['xdis_title'])) {
                    $return[$result['rmf_rec_pid']]['xdis_title'] = array();
                }
                if (!in_array($result['xdis_title'],$return[$result['rmf_rec_pid']]['xdis_title'])) {
                    array_push($return[$result['rmf_rec_pid']]['xdis_title'], $result['xdis_title']);
                }
            }
			if (is_numeric(@$result['sek_id'])) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$search_var = strtolower(str_replace(" ", "_", $result['sek_title']));
				if (@!is_array($return[$result['rmf_rec_pid']][$search_var])) {
					$return[$result['rmf_rec_pid']][$search_var] = array();
				}
				if (!in_array($result['rmf_'.$result['xsdmf_data_type']], 
                            $return[$result['rmf_rec_pid']][$search_var])) {
					array_push($return[$result['rmf_rec_pid']][$search_var], 
                            $result['rmf_'.$result['xsdmf_data_type']]);
//					sort($return[$result['rmf_rec_pid']][$search_var]);

				}
			} 
			// get thumbnails
			if ($result['xsdmf_element'] == "!datastream!ID") {
				if (is_numeric(strpos($result['rmf_varchar'], "thumbnail_"))) {
					if (!is_array(@$return[$result['rmf_rec_pid']]['thumbnails'])) {
						$return[$result['rmf_rec_pid']]['thumbnails'] = array();
					}
					array_push($return[$result['rmf_rec_pid']]['thumbnails'], $result['rmf_varchar']);
				} else {
					if (!is_array(@$return[$result['rmf_rec_pid']]['datastreams'])) {
						$return[$result['rmf_rec_pid']]['datastreams'] = array();
					}
					array_push($return[$result['rmf_rec_pid']]['datastreams'], $result['rmf_varchar']);
				}
			}

		}
//		$return = array_values($return);
		return $return;
    }    

    /**
     * Method used to get the list of collections available in the 
     * system.
     *
     * @access  public
     * @param   string $community_pid The parent community to get the collections from, if not set then all collection will be returned. 	 	 
     * @param   integer $current_row The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return
     * @return  array The list of collections
     */
    function ListSets($current_row = 0, $max = 100, $order_by="Created Date")
    {
    	$list = Controlled_Vocab::getChildListAll($current_row, $max);
    	foreach ($list as $lid => $lvalue) {
    		$list[$lid]["cvo_title"] = htmlspecialchars($list[$lid]["cvo_title"]);
    	}
		return array("list" => $list, "list_info" => array());
		// Below commented out code was for list of all collections, now just going with controlled vocabs as sets
/*    	
        $start = $current_row * $max;
//        $sekdet = Search_Key::getDetailsByTitle($order_by);
//        $data_type = $sekdet['xsdmf_data_type'];

        // Should we restrict the list to a community.
        if ($community_pid) {
            $community_join = "	inner join (
	 						SELECT ".APP_SQL_CACHE."  distinct r3.rmf_rec_pid 
							FROM  
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r3,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x3,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s3
							WHERE x3.xsdmf_sek_id = s3.sek_id AND s3.sek_title = 'isMemberOf' AND x3.xsdmf_id = r3.rmf_xsdmf_id 
							  AND match(r3.rmf_varchar) against ('\"$community_pid\"' in boolean mode)
							) as com1 on com1.rmf_rec_pid = r1.rmf_rec_pid ";
        } else {
            // list all collections 
            $community_join = "";
        }
        $stmt = "SELECT ".APP_SQL_CACHE." 
            * 
            FROM
            " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1
            inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 
            ON r1.rmf_xsdmf_id = x1.xsdmf_id 
           $community_join 
			inner join (
                    SELECT ".APP_SQL_CACHE."  distinct r2.rmf_rec_pid 
                    FROM  
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2				  
                    WHERE r2.rmf_xsdmf_id = x2.xsdmf_id 
                    AND x2.xsdmf_sek_id = s2.sek_id 
                    AND s2.sek_title = 'Object Type' 
                    AND r2.rmf_int = 2 
                    ) as o1 on o1.rmf_rec_pid = r1.rmf_rec_pid
			inner join (
                    SELECT ".APP_SQL_CACHE."  distinct rmf_rec_pid FROM 
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field AS rmf
                    INNER JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields AS xdm
                    ON rmf.rmf_xsdmf_id = xdm.xsdmf_id
                    WHERE rmf.rmf_int = 2
                    AND xdm.xsdmf_element='!sta_id'
                    ) as sta1 on sta1.rmf_rec_pid = r1.rmf_rec_pid					
             left JOIN (
                        SELECT ".APP_SQL_CACHE."  distinct r2.rmf_rec_pid as sort_pid

                        FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                        on r2.rmf_xsdmf_id = x2.xsdmf_id 
                        inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
                        on s2.sek_id = x2.xsdmf_sek_id
                        where s2.sek_title = '$order_by'
                        ) as d3
                on r1.rmf_rec_pid = d3.sort_pid
                left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 
            on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
            left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key k1 
            on (k1.sek_id = x1.xsdmf_sek_id)
            order by r1.rmf_rec_pid_num ASC
            ";
//		echo $stmt;
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		$return = array();
		$return = OAI::makeReturnList($res);
		$return = array_values($return);
		$total_rows = count($return);
		if (($start + $max) < $total_rows) {
	        $total_rows_limit = $start + $max;
		} else {
		   $total_rows_limit = $total_rows;
		}
		$total_pages = ceil($total_rows / $max);
        $last_page = $total_pages - 1;
//		$return = Misc::limitListResults($return, $start, ($start + $max));
		// add the available workflow trigger buttons
		
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return array(
                "list" => $return,
                "info" => array(
                    "current_page"  => $current_row,
                    "start_offset"  => $start,
                    "end_offset"    => $start + ($total_rows_limit),
                    "total_rows"    => $total_rows,
                    "total_pages"   => $total_pages,
                    "previous_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
                    "next_page"     => ($current_row == $last_page) ? "-1" : ($current_row + 1),
                    "last_page"     => $last_page,
                    "hidden_rows"     => $hidden_rows - $total_rows
                )
            );
        }
		*/
    }    
    
	
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included OAI Class');
}
?>
