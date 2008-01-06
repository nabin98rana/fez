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
    function ListRecords($set, $identifier="", $current_row = 0, $max = 100, $order_by = 'Created Date', $from="", $until="", $setType)
    {
		$from = str_replace("T", " ", $from);
		$from = str_replace("Z", " ", $from);
		$until = str_replace("Z", " ", $until);		
		$until = str_replace("Z", " ", $until);		
        $order_dir = 'ASC';
		$options = array();
		if ($max == "ALL") {
            $max = 9999999;
        }
        $current_row = ($current_row/100);

        if (!empty($identifier)) {
			$options["searchKey".Search_Key::getID("Pid")] = $identifier;
        } elseif (!empty($set)) {
			if ($setType == "isMemberOf") {
				$options["searchKey".Search_Key::getID("isMemberOf")] = $set;
			} else {
				$options["searchKey".Search_Key::getID("Subject")] = $set;
			}
		}
        if ($from != "" && $until != "") {
			$options["searchKey".Search_Key::getID("Date")] = array();
			$options["searchKey".Search_Key::getID("Date")]["filter_type"] = "between";
			$options["searchKey".Search_Key::getID("Date")]["filter_enabled"] = 1;
			$options["searchKey".Search_Key::getID("Date")]["start_date"] = $from;
			$options["searchKey".Search_Key::getID("Date")]["end_date"] = $until;
        } elseif (!empty($from) && empty($until)) {
			$options["searchKey".Search_Key::getID("Date")] = array();
			$options["searchKey".Search_Key::getID("Date")]["filter_type"] = "greater";
			$options["searchKey".Search_Key::getID("Date")]["filter_enabled"] = 1;
			$options["searchKey".Search_Key::getID("Date")]["start_date"] = $from;        	
        } elseif (!empty($until) && empty($from)) {
			$options["searchKey".Search_Key::getID("Date")] = array();
			$options["searchKey".Search_Key::getID("Date")]["filter_type"] = "less";
			$options["searchKey".Search_Key::getID("Date")]["filter_enabled"] = 1;
			$options["searchKey".Search_Key::getID("Date")]["start_date"] = $until;
        }
 		$return = Record::getListing($options, $approved_roles=array(9,10), $current_row, $max, $order_by);

		if (is_array($return['list'])) {
			foreach ($return['list'] as $rkey => $res) {
				$fans = array();
				if (is_array($res['rek_file_attachment_name'])) {
					foreach($res['rek_file_attachment_name'] as $fan) {
						if (Misc::isAllowedDatastream($fan)) {
							array_push($fans, $fan);
						}
					}
				}
				
				if( !empty($res['rek_created_date']) )
				{
				    $return['list'][$rkey]['rek_created_date'] = Date_API::getFedoraFormattedDateUTC(strtotime($res['rek_created_date']));
				}
				
				$return['list'][$rkey]['rek_file_attachment_name'] = $fans;
			}
		}
		return $return;
	
	 }
	
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
    function ListRecords_old($set, $identifier="", $current_row = 0, $max = 100, $order_by = 'Title', $from="", $until="", $setType)
    {
		$from = str_replace("T", " ", $from);
		$from = str_replace("Z", " ", $from);
		$until = str_replace("Z", " ", $until);		
		$until = str_replace("Z", " ", $until);		
        $order_dir = 'ASC';
		if ($max == "ALL") {
            $max = 9999999;
        }
        $start = $current_row;

        // this query broken into pieces to try and get some speed.

        $dbtp =  APP_TABLE_PREFIX;
//        $order_by = 'Title';
        $restrict_community = '';

		$authArray = Collection::getAuthIndexStmt(array("Viewer"));
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];

			
		$sql_filter = array();
		$sql_filter['where'] = "";
		$sql_filter['elsewhere'] = "";
        if (!empty($identifier)) {
        	$sql_filter['where'][] = "r2.rek_pid = '".Misc::escapeString($identifier)."'";
       	
        } elseif (!empty($set)) {
        	if ($setType == "isMemberOf") {
	        	$bodyStmtPart2 = " INNER JOIN ".$dbtp."record_search_key_ismemberof AS r3
	                      ON r3.rek_ismemberof_pid = r2.rek_pid and r2.rek_ismemberof = '".Misc::escapeString($set)."'";

        	} else { //cont vocab
	        	$bodyStmtPart2 = " INNER JOIN ".$dbtp."record_search_key_subject AS r3
	                      ON r3.rek_subject_pid = r2.rek_pid";
	        	$sql_filter['where'][] = "r3.rek_subject = ".Misc::escapeString($set)."";

        	}
		}
			$sql_filter['where'][] = "r2.rek_status = 2";
									
			$elsewhere = "";
			$bodyStmtPart2 = "";
		

        $bodyStmtPart1 = "FROM  ".$dbtp."record_search_key AS r2 ".$bodyStmtPart2
						."INNER JOIN ".$dbtp."xsd_display_matchfields m1 on m1.xsdmf_id = r2.rek_xsdmf_id";

		$sql_filter['where'][] = " (r2.rek_date >= '".$from."' and r2.rek_date <= '".$until."')";        

        $bodyStmt = $bodyStmtPart1.$bodyStmtPart2;
        
        if ($from != "" && $until != "") {
			$sql_filter['where'][] = " (r2.rek_date >= '".$from."' and r2.rek_date <= '".$until."')";
        	
        } elseif (!empty($from) && empty($until)) {
			$sql_filter['where'][] = " r2.rek_date >= '".$from."'";

        	
        } elseif (!empty($until) && empty($from)) {
			$sql_filter['where'][] = "r6.rek_date <= '".$until."'";
        }
        

/*		if ($order_by != "") {
			
			$bodyStmt .=
                   " LEFT JOIN " . APP_TABLE_PREFIX . "record_matching_field r5 
                    on r5.rek_pid_num = r2.rek_pid_num and r5.rek_pid = r2.rek_pid and r5.rek_xsdmf_id in (".implode(",", $order_byList).")";
		}  */
	    $bodyStmt .=                   
					 ( ($sql_filter['where']) != "" ? "WHERE ".implode("\r\nAND ", $sql_filter['where']) : $elsewhere) . "
					
             ";
		


        $stmt = "
                    SELECT ".APP_SQL_CACHE." *
					LEFT JOIN ".$dbtp."record_search_key_ismemberof AS j1 ON j1.rek_ismemberof_pid = r2.rek_pid
					LEFT JOIN ".$dbtp."record_search_key_subject AS j2 ON j2.rek_subject_pid = r2.rek_pid
                    ".$bodyStmt."
					
                    $joinStmt
                    $authStmt				
					order by ";
/*        if ($order_by != "") {
			$stmt .= " r5.rek_".$data_type." ".$order_dir.", r2.rek_created_date ASC";
        } else { */
			$stmt .= " r2.rek_created_date ASC";        	
//        }

		$stmt = $GLOBALS["db_api"]->dbh->modifyLimitQuery($stmt, $start, $max);
/*
        $stmtWrap = "SELECT ".APP_SQL_CACHE."  r1.*, x1.*, s1.*, k1.*, d1.* 
            FROM ".$dbtp."record_matching_field AS r1
            INNER JOIN ".$dbtp."xsd_display_matchfields AS x1
            ON r1.rek_xsdmf_id = x1.xsdmf_id

				INNER JOIN (".$stmt.") as display on display.rek_pid = r1.rek_pid

            LEFT JOIN ".$dbtp."xsd_loop_subelement s1 
            ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
            LEFT JOIN ".$dbtp."search_key k1 
            ON (k1.sek_id = x1.xsdmf_sek_id)
            LEFT JOIN ".$dbtp."xsd_display d1  
            ON (d1.xdis_id = r1.rek_int and k1.sek_title = 'Display Type')
			
			ORDER BY r1.rek_pid_num ASC, r1.rek_id ASC
            ";                    
*/      
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);		
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
       		$return = array();       	
			$return = OAI::makeReturnList($res);		
			$return = array_values($return);
            return array(
                "list" => $return
            );
        }
    }

    
    
    function makeReturnList($res, $statsFlag = 0) {
		$securityfields = Auth::getAllRoles();
        $return = array();
        
		foreach ($res as $result) {
			if ($result['sek_title'] == 'isMemberOf') {
                $return[$result['rek_pid']]['isMemberOf'][] = $result['rek_varchar'];
			}			
			if (($result['sek_title'] == 'Created Date' || $result['sek_title'] == 'Updated Date') && !(empty($result['rek_date']))) {
				if (!empty($result['rek_date'])) {
					$result['rek_date'] = Date_API::getFedoraFormattedDate($result['rek_date']);
				}
            }
            if  ($result['sek_title'] == 'Subject' && ((($result['xsdmf_html_input'] == "contvocab") || ($result['xsdmf_html_input'] == "contvocab_selector")) && ($result['xsdmf_cvo_save_type'] != 1))) {            
				$return[$result['rek_pid']]['subject_id'][] = $result['rek_'.$result['xsdmf_data_type']];
            	$result['rek_'.$result['xsdmf_data_type']] = Controlled_Vocab::getTitle($result['rek_'.$result['xsdmf_data_type']]);
            }
            if (($result['xsdmf_enabled'] == 1) && ($result['xsdmf_meta_header'] == 1) && (trim($result['xsdmf_meta_header_name']) != "")) {			
            	$value = "";
				$oai_name = str_replace(".", ":", strtolower($result['xsdmf_meta_header_name']));
				if ($return[$result['rek_pid']]['oai_dc'] == "") {
					$return[$result['rek_pid']]['oai_dc'] = '<dc:identifier>'.APP_BASE_URL.'view/'.$result['rek_pid'].'</dc:identifier>'."\n";
				}
				if ($result['xsdmf_data_type'] == "date") {
					if (!empty($result['rmf_date'])) {
						$value = Date_API::getFedoraFormattedDate($result['rek_'.$result['xsdmf_data_type']]);
					}
				} else {
					$value = htmlspecialchars($result['rek_'.$result['xsdmf_data_type']]);
				}
            	$return[$result['rek_pid']]['oai_dc'] .=  '<'.$oai_name.'>'.$value.'</'.$oai_name.'>'."\n";
            }						
			if (@$result['sek_title'] == 'isMemberOf') {
				if (!is_array(@$return[$result['rek_pid']]['isMemberOf'])) {
					$return[$result['rek_pid']]['isMemberOf'] = array();
				}
				if (!in_array($result['rek_varchar'], $return[$result['rek_pid']]['isMemberOf'])) {
					array_push($return[$result['rek_pid']]['isMemberOf'], $result['rek_varchar']);
				}
			}			
			// get the document type
            if (!empty($result['xdis_title'])) {
                if (!is_array(@$return[$result['rek_pid']]['xdis_title'])) {
                    $return[$result['rek_pid']]['xdis_title'] = array();
                }
                if (!in_array($result['xdis_title'],$return[$result['rek_pid']]['xdis_title'])) {
                    array_push($return[$result['rek_pid']]['xdis_title'], $result['xdis_title']);
                }
            }
			if (is_numeric(@$result['sek_id'])) {
				$return[$result['rek_pid']]['pid'] = $result['rek_pid'];
				$search_var = strtolower(str_replace(" ", "_", $result['sek_title']));
				if (@!is_array($return[$result['rek_pid']][$search_var])) {
					$return[$result['rek_pid']][$search_var] = array();
				}
				if (!in_array($result['rek_'.$result['xsdmf_data_type']], 
                            $return[$result['rek_pid']][$search_var])) {
					array_push($return[$result['rek_pid']][$search_var], 
                            $result['rek_'.$result['xsdmf_data_type']]);
//					sort($return[$result['rek_pid']][$search_var]);

				}
			} 
			// get thumbnails
			if ($result['xsdmf_element'] == "!datastream!ID") {
				if (is_numeric(strpos($result['rek_varchar'], "thumbnail_"))) {
					if (!is_array(@$return[$result['rek_pid']]['thumbnails'])) {
						$return[$result['rek_pid']]['thumbnails'] = array();
					}
					array_push($return[$result['rek_pid']]['thumbnails'], $result['rek_varchar']);
				} else {
					if ($result['sek_title'] == 'File Attachment Name') { 
						if (!is_array(@$return[$result['rek_pid']]['File_Attachment'])) {
							$return[$result['rek_pid']]['File_Attachment'] = array();
						}						
					    if ((!is_numeric(strpos($result['rek_'.$result['xsdmf_data_type']], "thumbnail_"))) && (!is_numeric(strpos($result['rek_'.$result['xsdmf_data_type']], "web_"))) && (!is_numeric(strpos($result['rek_'.$result['xsdmf_data_type']], "preview_"))) && (!is_numeric(strpos($result['rek_'.$result['xsdmf_data_type']], "presmd_"))) && (!is_numeric(strpos($result['rek_'.$result['xsdmf_data_type']], "FezACML_"))) )   {
					    	if (!in_array($result['rek_'.$result['xsdmf_data_type']], $return[$result['rek_pid']]['File_Attachment'])) {
								array_push($return[$result['rek_pid']]['File_Attachment'], $result['rek_'.$result['xsdmf_data_type']]);
								$return[$result['rek_pid']]['oai_dc'] .=  '<dc:format>'.APP_BASE_URL.'eserv/'.$result['rek_pid'].'/'.$result['rek_'.$result['xsdmf_data_type']].'</dc:format>'."\n";
					    	}
					    }
					}
					if (!is_array(@$return[$result['rek_pid']]['datastreams'])) {
						$return[$result['rek_pid']]['datastreams'] = array();
					}
					array_push($return[$result['rek_pid']]['datastreams'], $result['rek_varchar']);
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
     * @param   integer $start The point in the returned results to start from.
     * @param   integer $max The maximum number of records to return
     * @return  array The list of collections
     */
    function ListSets($start = 0, $max = 100, $order_by="Created Date")
    {
    	$list = Controlled_Vocab::getChildListAll($start, $max);
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
	 						SELECT ".APP_SQL_CACHE."  distinct r3.rek_pid 
							FROM  
							  " . APP_TABLE_PREFIX . "record_matching_field r3,
							  " . APP_TABLE_PREFIX . "xsd_display_matchfields x3,
							  " . APP_TABLE_PREFIX . "search_key s3
							WHERE x3.xsdmf_sek_id = s3.sek_id AND s3.sek_title = 'isMemberOf' AND x3.xsdmf_id = r3.rek_xsdmf_id 
							  AND r3.rek_varchar='$community_pid'
							) as com1 on com1.rek_pid = r1.rek_pid ";
        } else {
            // list all collections 
            $community_join = "";
        }
        $stmt = "SELECT ".APP_SQL_CACHE." 
            * 
            FROM
            " . APP_TABLE_PREFIX . "record_matching_field r1
            inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields x1 
            ON r1.rek_xsdmf_id = x1.xsdmf_id 
           $community_join 
			inner join (
                    SELECT ".APP_SQL_CACHE."  distinct r2.rek_pid 
                    FROM  
                    " . APP_TABLE_PREFIX . "record_matching_field r2,
                    " . APP_TABLE_PREFIX . "xsd_display_matchfields x2,
                    " . APP_TABLE_PREFIX . "search_key s2				  
                    WHERE r2.rek_xsdmf_id = x2.xsdmf_id 
                    AND x2.xsdmf_sek_id = s2.sek_id 
                    AND s2.sek_title = 'Object Type' 
                    AND r2.rek_int = 2 
                    ) as o1 on o1.rek_pid = r1.rek_pid
			inner join (
                    SELECT ".APP_SQL_CACHE."  distinct rek_pid FROM 
                    " . APP_TABLE_PREFIX . "record_matching_field AS rmf
                    INNER JOIN " . APP_TABLE_PREFIX . "xsd_display_matchfields AS xdm
                    ON rmf.rek_xsdmf_id = xdm.xsdmf_id
                    WHERE rmf.rek_int = 2
                    AND xdm.xsdmf_element='!sta_id'
                    ) as sta1 on sta1.rek_pid = r1.rek_pid					
             left JOIN (
                        SELECT ".APP_SQL_CACHE."  distinct r2.rek_pid as sort_pid

                        FROM  " . APP_TABLE_PREFIX . "record_matching_field r2
                        inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields x2
                        on r2.rek_xsdmf_id = x2.xsdmf_id 
                        inner join " . APP_TABLE_PREFIX . "search_key s2
                        on s2.sek_id = x2.xsdmf_sek_id
                        where s2.sek_title = '$order_by'
                        ) as d3
                on r1.rek_pid = d3.sort_pid
                left join " . APP_TABLE_PREFIX . "xsd_loop_subelement s1 
            on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
            left join " . APP_TABLE_PREFIX . "search_key k1 
            on (k1.sek_id = x1.xsdmf_sek_id)
            order by r1.rek_pid_num ASC
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
                    "prev_page" => ($current_row == 0) ? "-1" : ($current_row - 1),
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
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included OAI Class');
}
?>
