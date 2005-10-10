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
 * Class designed to handle all business logic related to the Records in the
 * system, such as adding or updating them or listing them in the grid mode.
 *
 * @author  João Prado Maia <jpm@mysql.com>
 * @version $Revision: 1.114 $
 */

include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");


//@@@ CK - 28/10/2004 - Modified the list headings to be like the actual list headings so the CSV would show the same thing
$list_headings = array(
    'Date of Issue',
    'Title',
    'Authors'
);

/**
  * Record
  * Static class for accessing record related queries
  * See RecordObject for an object oriented representation of a record.
  */
class Record
{


    function getIndexParents($pid)
    {

        $stmt = "SELECT
                    * 
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 left join
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1 on (x1.xsdmf_xsdsel_id = s1.xsdsel_id)
                 WHERE
				    r1.rmf_xsdmf_id = x1.xsdmf_id and 
                    rmf_rec_pid in (
						SELECT r2.rmf_varchar 
						FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2
						WHERE (rmf_xsdmf_id = 91 AND rmf_varchar = '2' AND r2.rmf_rec_pid = '".$pid."') OR
							(rmf_xsdmf_id = 149 AND rmf_varchar = '3' AND r2.rmf_rec_pid = '".$pid."')
						)
					";
//		echo $stmt;			
		$returnfields = array("title", "description", "ret_id", "xdis_id", "sta_id", "Editor", "Creator", "Lister", "Viewer", "Approver", "Community Administrator", "Annotator", "Comment_Viewer", "Commentor");
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        //$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
//		print_r($res);
		$return = array();
		
		foreach ($res as $result) {		
			if (in_array($result['xsdsel_title'], $returnfields) && ($result['xsdmf_element'] != '!rule!role!name') && is_numeric(strpos($result['xsdmf_element'], '!rule!role!')) ) {
				if (!is_array($return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']])) {
					$return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']] = array();
				}
				array_push($return[$result['rmf_rec_pid']]['FezACML'][$result['xsdsel_title']][$result['xsdmf_element']], $result['rmf_'.$result['xsdmf_data_type']]); // need to array_push because there can be multiple groups/users for a role
			}
			if (in_array($result['xsdmf_fez_title'], $returnfields)) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$return[$result['rmf_rec_pid']][$result['xsdmf_fez_title']] = $result['rmf_'.$result['xsdmf_data_type']];
			}
		}
//		print_r($return);
//		$return = Auth::getIndexAuthorisationGroups($return);
		
		
		//get groups after you get all the acmls? yeah probably
//		$return = array_values($return);
//		$return = Auth::getIndexAuthorisationGroups($return);

		return $return;
		

    }

    function getParents($pid)
    {

		$itql = "select \$collTitle \$collDesc \$title \$description \$object from <#ri>
					where  (<info:fedora/".$pid."> <dc:title> \$collTitle) and
                    (<info:fedora/".$pid."> <dc:description> \$collDesc) and
					(<info:fedora/".$pid."> <fedora-rels-ext:isMemberOf> \$object ) and
					((\$object <dc:title> \$title) or
					(\$object <dc:description> \$description))
					order by \$title asc";

//		echo $itql;
		$returnfields = array();
		array_push($returnfields, "pid"); 
		array_push($returnfields, "title");
		array_push($returnfields, "identifier");
		array_push($returnfields, "description");

		$details = Fedora_API::getITQLQuery($itql, $returnfields);
//		print_r($details);
		return $details;
    }


    /**
     * Method used to update the details of a specific Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer 1 if the update worked, -1 or -2 otherwise
     */
    function update($pid)
    {
        $record = new RecordObject($pid);
        if ($record->fedoraInsertUpdate()) {
            return 1;
        } else {
            return -1;
        }
    }

   /**
     * Method used to update the details of a specific Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  integer 1 if the update worked, -1 or -2 otherwise
     */
    function updateAdminDatastream($pid, $xdis_id)
    {
        $record = new RecordObject($pid);
        if ($record->updateAdminDatastream($xdis_id)) {
            return 1;
        } else {
            return -1;
        }
    }


    function incrementFileDownloads($pid)
    {
        $record = new RecordObject($pid);
        if ($record->incrementFileDownloads()) {
            return 1;
        } else {
            return -1;
        }
    }


    /**
     * Method used to add a new Record using the normal report form.
     *
     * @access  public
     * @return  integer The new Record ID
     */
    function insert()
    {
        $record = new RecordObject();
        return $record->fedoraInsertUpdate();
    }

    /**
     * Method used to add a new quick Record using the quick report form.
     *
     * @access  public
     * @return  integer The new Record ID
     */

	function insertIndexBatch($pid, $indexArray, $datastreamXMLHeaders) {

		// first delete all indexes about this pid
		Record::removeIndexRecord($pid, 'keep');
		if (!is_array($indexArray)) {
			return false;
		}

//		array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $value)
		foreach ($indexArray as $index) {
			if ($index[1] == 1)  { // if this xsdmf is designated to be indexed then insert it as long as it has a value
				foreach ($datastreamXMLHeaders as $dsKey => $dsHeader) { // get the real ds names for the file uploads
					if ($index[6] == $dsKey) {
						$index[6] = $dsHeader['ID'];
					}
				}
				if ($index[6] != "") {
					Record::insertIndexMatchingField($index[0], $index[2], $index[5], $index[6]);
				}
			}
		}
	
	}




    function removeIndexRecord($pid, $dsDelete='all')
    {
 
//		echo "monkey = ".$initial_status;
        // add new Record
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
				 WHERE rmf_rec_pid = '" . $pid . "'";
		if ($dsDelete=='keep') {
			$stmt .= " and rmf_xsdmf_id not in (select distinct(xsdmf_id) from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_fez_title = 'datastream_id')";
		}
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return $pid;
        }
    }

    function removeIndexRecordByValue($pid, $value, $data_type='varchar')
    {
 
//		echo "monkey = ".$initial_status;
        // add new Record
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
				 WHERE rmf_rec_pid = '" . $pid . "' and rmf_".$data_type."='".$value."'";
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return $pid;
        }
    }

    function removeIndexRecordByXSDMF_ID($pid, $xsdmf_id)
    {
 
//		echo "monkey = ".$initial_status;
        // add new Record
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
				 WHERE rmf_rec_pid = '" . $pid . "' and rmf_xsdmf_id=".$xsdmf_id;
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return $pid;
        }
    }


    function insertIndexMatchingField($pid, $xsdmf_id, $data_type, $value)
    {
 
//		echo "monkey = ".$initial_status;
        // add new Record
        $xsdsel_id = '';
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
                 (
				 	rmf_rec_pid,
                    rmf_xsdmf_id,";
				if ($xsdsel_id != "") {
				  $stmt .= "rmf_xsdsel_id,";
				}
				$stmt .= "                    
					rmf_".$data_type."
                 ) VALUES (
                    '" . $pid . "',
                    " . $xsdmf_id . ",";
				if ($xsdsel_id != "") {
                	$stmt .= $xsdsel_id . ", ";
				}
					if ($data_type != "int") {
            			$stmt .= "'".Misc::escapeString($value) . "')";
					} else {
            			$stmt .= $value . ")";
					}
//		echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return $pid;
        }
    }


    /**
     * Method used to get the current listing related cookie information.
     *
     * @access  public
     * @return  array The Record listing information
     */
    function getCookieParams()
    {
        global $HTTP_COOKIE_VARS;
        return @unserialize(base64_decode($HTTP_COOKIE_VARS[APP_LIST_COOKIE]));
    }

    /**
     * Method used to get a specific parameter in the Record listing cookie.
     *
     * @access  public
     * @param   string $name The name of the parameter
     * @return  mixed The value of the specified parameter
     */
    function getParam($name)
    {
        global $HTTP_POST_VARS, $HTTP_GET_VARS;
        $cookie = Record::getCookieParams();

        if (isset($HTTP_GET_VARS[$name])) {
            return $HTTP_GET_VARS[$name];
        } elseif (isset($HTTP_POST_VARS[$name])) {
            return $HTTP_POST_VARS[$name];
        } elseif (isset($cookie[$name])) {
            return $cookie[$name];
        } else {
            return "";
        }
    }


    /**
     * Method used to save the current search parameters in a cookie.
     *
     * @access  public
     * @return  array The search parameters
     */
    function saveSearchParams()
    {	
		// @@@ CK 21/7/2004 - Added this global for the custom fields check.
			
        $sort_by = Record::getParam('sort_by');
        $sort_order = Record::getParam('sort_order');
        $rows = Record::getParam('rows');
        $cookie = array(
            'rows'           => $rows ? $rows : APP_DEFAULT_PAGER_SIZE,
//            'pagerRow'       => Record::getParam('pagerRow'), //stop saving the page in the cookie..
            'hide_closed'    => Record::getParam('hide_closed'),
            "sort_by"        => $sort_by ? $sort_by : "rec_id",
            "sort_order"     => $sort_order ? $sort_order : "DESC",
            // quick filter form
            'keywords'       => Record::getParam('keywords'),
            'collections'       => Record::getParam('collections'),
//            'time_tracking_category' => Record::getParam('time_tracking_category'),
            'users'          => Record::getParam('users'),
            'status'         => Record::getParam('status'),
//            'has_attachments' => Record::getParam('has_attachments'), // @@@ CK - added 7/9/2004
//            'priority'       => Record::getParam('priority'),
//            'category'       => Record::getParam('category'),
            // advanced search form
            'show_authorized_Records'        => Record::getParam('show_authorized_records'),
            'show_notification_list_Records' => Record::getParam('show_notification_list_records'),
        );
			$existing_cookie = Record::getCookieParams();
						//print_r($existing_cookie);

			global $HTTP_POST_VARS, $HTTP_GET_VARS;
            // need to process any custom fields ?
/*            $custom_count = Record::getParam('custom_count');
			if (empty($custom_count) || !is_numeric($custom_count)) {
	            $custom_count = @count($HTTP_GET_VARS["custom_fields"]);
			}
			for($x=0;$x<$custom_count;$x++) {
				
			}
*/
//			$custom_count = Custom_Field::getMaxID();
//			$tempArray = array('custom_count' => $custom_count);
//			$cookie = array_merge ($cookie, $tempArray);

/*            if ($custom_count > 0) {
				$from_cookie = false;		
		    	if (isset($HTTP_GET_VARS['custom_fields'])) {
					$customArray = $HTTP_GET_VARS['custom_fields'];
  				} elseif (isset($HTTP_POST_VARS['custom_fields'])) {
					$customArray = $HTTP_POST_VARS['custom_fields'];
				} else {
					$from_cookie = true;
					for($x=0;$x<$custom_count;$x++) {
						$existing_cookie = Record::getCookieParams();
						if (isset($existing_cookie['custom'.$x])) {
							$customArray = array_merge($customArray, array('custom'.$x => $existing_cookie['custom'.$x]));
						}
					}   
			    }
                foreach ($customArray as $fld_id => $value) {				
					if ($from_cookie == true) {
/*						if ($fld_id == 2) {
							foreach ($value as $branch => $branchValue) {
								
							}
						} else { */ 
/*
							$tempArray = array($fld_id => $value);				
//						}
					} else {
						$tempArray = array('custom'.$fld_id => $value);				
					}
					$cookie = array_merge ($cookie, $tempArray);
					if (!empty($fld_id)) {

					}
                }
			}
*/
        // now do some magic to properly format the date fields
/*        $date_fields = array(
            'created_date',
            'updated_date',
            'last_response_date',
            'first_response_date',
            'closed_date'
        );
        foreach ($date_fields as $field_name) {
            $field = Record::getParam($field_name);
            if (empty($field)) {
                continue;
            }
            $end_field_name = $field_name . '_end';
            $end_field = Record::getParam($end_field_name);
            @$cookie[$field_name] = array(
                'Year'        => $field['Year'],
                'Month'       => $field['Month'],
                'Day'         => $field['Day'],
                'start'       => $field['Year'] . '-' . $field['Month'] . '-' . $field['Day'],
                'filter_type' => $field['filter_type'],
                'end'         => $end_field['Year'] . '-' . $end_field['Month'] . '-' . $end_field['Day']
            );
            @$cookie[$end_field_name] = array(
                'Year'        => $end_field['Year'],
                'Month'       => $end_field['Month'],
                'Day'         => $end_field['Day']
            );
        }*/
        $encoded = base64_encode(serialize($cookie));
        setcookie(APP_LIST_COOKIE, $encoded, APP_LIST_COOKIE_EXPIRE);
		//print_r($cookie);
        return $cookie;
    }


    /**
     * Method used to get the current sorting options used in the grid layout
     * of the Record listing page.
     *
     * @@@ CK - 28/10/2004 - Added library branch sorting
     *
     * @access  public
     * @param   array $options The current search parameters
     * @return  array The sorting options
     */
    function getSortingInfo($options)
    {
        global $HTTP_SERVER_VARS;
		// @@@ CK - 18/1/2005 - need to work in assigned_users somehow.
        $fields = array(
            "rec_id",
            "rec_date",
            "rec_summary"
        );
        $items = array(
            "links"  => array(),
            "images" => array()
        );
        for ($i = 0; $i < count($fields); $i++) {
            if ($options["sort_by"] == $fields[$i]) {
                $items["images"][$fields[$i]] = "images/" . strtolower($options["sort_order"]) . ".gif";
                if (strtolower($options["sort_order"]) == "asc") {
                    $sort_order = "desc";
                } else {
                    $sort_order = "asc";
                }
                $items["links"][$fields[$i]] = $HTTP_SERVER_VARS["PHP_SELF"] . "?sort_by=" . $fields[$i] . "&sort_order=" . $sort_order;
            } else {
                $items["links"][$fields[$i]] = $HTTP_SERVER_VARS["PHP_SELF"] . "?sort_by=" . $fields[$i] . "&sort_order=asc";
            }
        }
//		print_r($items);
        return $items;
    }


    /**
     * Method used to get the list of Records to be displayed in the grid layout.
     *
     * @access  public
     * @param   integer $col_id The current project ID
     * @param   array $options The search parameters
     * @param   integer $current_row The current page number
     * @param   integer $max The maximum number of rows per page
     * @return  array The list of Records to be displayed
     */
    function getListing($options, $current_row = 0, $max = 5, $get_reporter = FALSE)
    {
		$details = Fedora_API::getListObjectsXML($options, $max);

		foreach ($details as $darray_key => $darray) {
			foreach ($darray as $dkey => $dvalue) { // turn any array values into a comma seperated string value
				if (is_array($dvalue)) {
					$details[$darray_key][$dkey] = implode(", ", $dvalue);
				}
			}
		}	
        $details = Auth::ProcessListResults($details);
		return $details;
    }



	function getIndexACML($pid, $xdis_id) {
    $stmt = "SELECT
				* 
			 FROM
				" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1,
				" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1,
				" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement s1
			 WHERE
				r1.rmf_rec_pid = '".$pid."'";
//		echo $stmt;
		$returnfields = array("title", "description", "ret_id", "xdis_id", "sta_id");
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        //$res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
//		print_r($res);
		$return = array();
		foreach ($res as $result) {
			if (in_array($result['xsdmf_fez_title'], $returnfields)) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$return[$result['rmf_rec_pid']][$result['xsdmf_fez_title']] = $result['rmf_'.$result['xsdmf_data_type']];
			}
		}
		$return = array_values($return);
//		print_r($return);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
/*            for ($i = 0; $i < count($res); $i++) {
                $res[$i]["projects"] = @implode(", ", array_values(XSD_HTML_Match::getAssociatedCollections($res[$i]["fld_id"])));
                if (($res[$i]["fld_type"] == "combo") || ($res[$i]["fld_type"] == "multiple")) {
                    $res[$i]["field_options"] = @implode(", ", array_values(XSD_HTML_Match::getOptions($res[$i]["fld_id"])));
                }
            }
*/
            return $return;
        }
	}
	
	function getACML($pid) {

		$DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, 'FezACML');
		$xmlACML = @$DSResultArray['stream'];		

		if ($xmlACML != "") {
			$xmldoc= new DomDocument();
			$xmldoc->preserveWhiteSpace = false;
			$xmldoc->loadXML($xmlACML);
            return $xmldoc;
		} else {
			return false;
		}
	}


    /**
     * Method used to get the details for a specific Record.
     *
     * @access  public
     * @param   integer $record_id The Record ID
     * @return  array The details for the specified Record
     */
    function getDetails($pid, $xdis_id)
    {

		// Get the Datastreams.
		$datastreamTitles = XSD_Loop_Subelement::getDatastreamTitles($xdis_id);
		foreach ($datastreamTitles as $dsValue) {
			$DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $dsValue['xsdsel_title']);
            if (isset($DSResultArray['stream'])) {
                $xmlDatastream = $DSResultArray['stream'];
                $xsd_id = XSD_Display::getParentXSDID($dsValue['xsdmf_xdis_id']);
                $xsd_details = Doc_Type_XSD::getDetails($xsd_id);
                $xsd_element_prefix = $xsd_details['xsd_element_prefix'];
                $xsd_top_element_name = $xsd_details['xsd_top_element_name'];

                $xmlnode = new DomDocument();
                $xmlnode->loadXML($xmlDatastream);
                $array_ptr = array();
                Misc::dom_xml_to_simple_array($xmlnode, $array_ptr, $xsd_top_element_name, $xsd_element_prefix, $xsdmf_array, $xdis_id);
            }
		}

		return $xsdmf_array;
    }

    function getRecordXDIS_ID() {
		// will make this more dynamic later. (probably feed from a mysql table which can be configured in the gui admin interface).
		$xdis_id = 5;
		return $xdis_id;
    }

    /**
     * getAssociated
     * Find any records that the user has a role on
     */
    function getAssociated($username)
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX; // Database and table prefix
        $stmt = "SELECT * FROM 
        {$dbtp}record_matching_field AS rmf
        INNER JOIN {$dbtp}xsd_display_matchfields AS xdmf ON xdmf.xsdmf_id=rmf.rmf_xsdmf_id
            WHERE xdmf.xsdmf_element='!sta_id' AND rmf.rmf_varchar='1'";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        $res1 = $res;
        $res2 = array();
        foreach ($res1 as $row) {
            $r = new RecordObject($row['rmf_rec_pid']);
            $res2[] = array(
                    'pid' => $row['rmf_rec_pid'],
                    'title' => $r->getTitle(),
                    'type' => $r->getDCType()
                    ); 
        }
        return $res2;
   }


    /**
     * getAssigned
     * Find unpublished records that the user has a role on
     */
    function getAssigned($username)
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX; // Database and table prefix
        $stmt = "SELECT rmf.rmf_rec_pid FROM
                {$dbtp}record_matching_field AS rmf
                INNER JOIN {$dbtp}xsd_display_matchfields AS xdmf 
                ON xdmf.xsdmf_id=rmf.rmf_xsdmf_id
                WHERE xdmf.xsdmf_element='!sta_id' 
                AND rmf.rmf_varchar='1'
                ";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        $res1 = array();
        if (User::isUserAdministrator($username)) {
            $res1 = $res;
        } else {
            // reject records that current user can't edit
        }
        $res2 = array();
        foreach ($res1 as $row) {
            $r = new RecordObject($row['rmf_rec_pid']);
            if ($r->getXmlDisplayId()) {
                $res2[] = array(
                        'pid' => $row['rmf_rec_pid'],
                        'title' => $r->getTitle(),
                        'type' => $r->getDCType()
                        ); 
            }
        }
        return $res2;
    }

    function publishAllUnsetStatusPids()
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX; // Database and table prefix
        $stmt = "SELECT distinct rmf_rec_pid FROM 
        {$dbtp}record_matching_field 
        WHERE rmf_rec_pid NOT IN (
                SELECT rmf.rmf_rec_pid FROM
                {$dbtp}record_matching_field AS rmf
                INNER JOIN {$dbtp}xsd_display_matchfields AS xdmf 
                ON xdmf.xsdmf_id=rmf.rmf_xsdmf_id
                WHERE xdmf.xsdmf_element='!sta_id' 
                )";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        foreach ($res as $row) {
            $r = new RecordObject($row['rmf_rec_pid']);
            if ($r->getXmlDisplayId()) {
                echo $r->getTitle()."<br/>\n";
                $r->setStatusId(2);
            }
        }
    }


    function setIndexMatchingFields($xdis_id, $pid)
    {
        $array_ptr = array();
        $xsdmf_array = array();
        //			echo $xmlObj;
        // want to do this on a per datastream basis, not the entire xml object
        $datastreamTitles = XSD_Loop_Subelement::getDatastreamTitles($xdis_id);
        foreach ($datastreamTitles as $dsValue) {
            $DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $dsValue['xsdsel_title']);
            if (isset($DSResultArray['stream'])) {
                $xmlDatastream = $DSResultArray['stream'];
                $xsd_id = XSD_Display::getParentXSDID($dsValue['xsdmf_xdis_id']);
                $xsd_details = Doc_Type_XSD::getDetails($xsd_id);
                $xsd_element_prefix = $xsd_details['xsd_element_prefix'];
                $xsd_top_element_name = $xsd_details['xsd_top_element_name'];

                $xmlnode = new DomDocument();
                $xmlnode->loadXML($xmlDatastream);
                $array_ptr = array();
                Misc::dom_xml_to_simple_array($xmlnode, $array_ptr, $xsd_top_element_name, $xsd_element_prefix, $xsdmf_array, $xdis_id);
            }
        }
        foreach ($xsdmf_array as $xsdmf_id => $xsdmf_value) {
            if (!is_array($xsdmf_value) && !empty($xsdmf_value) && (trim($xsdmf_value) != "")) {					
                $xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
                Record::insertIndexMatchingField($pid, $xsdmf_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_value);					
            } elseif (is_array($xsdmf_value)) {
                foreach ($xsdmf_value as $xsdmf_child_value) {
                    if ($xsdmf_child_value != "") {
                        $xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
                        Record::insertIndexMatchingField($pid, $xsdmf_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_child_value);
                    }
                }
            }
        }
    }


    function makeInsertTemplate()
    {
        global $HTTP_POST_VARS, $HTTP_POST_FILES;

		$existingDatastreams = array();
        $created_date = date("Y-m-d H:i:s");
        $updated_date = $created_date;
        $pid = '__makeInsertTemplate_PID__';
        $xdis_id = $HTTP_POST_VARS["xdis_id"];
        $display = new XSD_DisplayObject($xdis_id);
        list($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema) = $display->getXsdAsReferencedArray();
		$indexArray = array();

		$xmlObj = '<?xml version="1.0"?>'."\n";
		$xmlObj .= "<".$xsd_element_prefix.$xsd_top_element_name." ";
		$xmlObj .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid); // for the pid, fedora uri etc
		$xmlObj .= $xml_schema;
		$xmlObj .= ">\n";

		$xmlObj = Misc::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "", $indexArray, 0, $created_date, $updated_date);

		$xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">";

		$datastreamTitles = $display->getDatastreamTitles();
		
       return compact('datastreamTitles', 'xmlObj', 'indexArray'); 
    }

    function insertFromTemplate($pid, $dsarray)
    {
        extract($dsarray);
        // find all instances of '__makeInsertTemplate_PID__' in xmlObj and replace with the correct PID
        // xmlObj is still a text representation at this stage.
        $xmlObj = str_replace('__makeInsertTemplate_PID__', $pid, $xmlObj);
        // fix up the indexArray so that the PIDs are correct
        foreach ($indexArray as &$item) {
            $item[0] = $pid;
        }
        Record::insertXML($pid, compact('datastreamTitles', 'xmlObj', 'indexArray'), true);
    }
   
    function insertXML($pid, $dsarray, $ingestObject)
    {
        extract($dsarray);
        $params = array();

		$datastreamXMLHeaders = Misc::getDatastreamXMLHeaders($datastreamTitles, $xmlObj, array());
		//print_r($datastreamXMLHeaders);
		if (@is_array($datastreamXMLHeaders["File_Attachment0"])) { // it must be a multiple file upload so remove the generic one
			$datastreamXMLHeaders = Misc::array_clean_key($datastreamXMLHeaders, "File_Attachment", true, true);
		}
		if (@is_array($datastreamXMLHeaders["Link0"])) { // it must be a multiple file upload so remove the generic one
			$datastreamXMLHeaders = Misc::array_clean_key($datastreamXMLHeaders, "Link", true, true);
		}

		$datastreamXMLContent = Misc::getDatastreamXMLContent($datastreamXMLHeaders, $xmlObj);
        if ($ingestObject) {
            // Actually Ingest the object Into Fedora
            // We only have to do this when first creating the object, subsequent updates should just work with the 
            // datastreams.
            // will have to exclude the non X control group xml and add the datastreams after the base ingestion.

            $xmlObj = Misc::removeNonXMLDatastreams($datastreamXMLHeaders, $xmlObj);

            $config = array(
                    'indent'         => true,
                    'input-xml'   => true,
                    'output-xml'   => true,
                    'wrap'           => 200);

            $tidy = new tidy;
            $tidy->parseString($xmlObj, $config, 'utf8');
            $tidy->cleanRepair();
            $xmlObj = $tidy;
            Fedora_API::callIngestObject($xmlObj);
        }
		$convert_check = false;
		Record::insertIndexBatch($pid, $indexArray, $datastreamXMLHeaders);
        // ingest the datastreams
		foreach ($datastreamXMLHeaders as $dsKey => $dsTitle) {
			$dsIDName = $dsTitle['ID'];

			if (is_numeric(strpos($dsIDName, "."))) {
				$filename_ext = strtolower(substr($dsIDName, (strrpos($dsIDName, ".") + 1)));
				$dsIDName = substr($dsIDName, 0, strrpos($dsIDName, ".") + 1).$filename_ext;
			}

			if (Fedora_API::datastreamExists($pid, $dsTitle['ID'])) {
				Fedora_API::callModifyDatastreamByValue($pid, $dsIDName, $dsTitle['STATE'], $dsTitle['LABEL'], 
                        $datastreamXMLContent[$dsKey], $dsTitle['MIMETYPE'], $dsTitle['VERSIONABLE']);
			} else {
				if ($dsTitle['CONTROL_GROUP'] == "R") { // if its a redirect we don't need to upload the file
//				    echo "R content = ".$datastreamXMLContent[$dsKey];
					Fedora_API::callAddDatastream($pid, $dsTitle['ID'], $datastreamXMLContent[$dsKey], 
                            $dsTitle['LABEL'], $dsTitle['STATE'], $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
				} else {
					Fedora_API::getUploadLocation($pid, $dsIDName, $datastreamXMLContent[$dsKey], $dsTitle['LABEL'], 
                            $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
				}
			}

            
			$presmd_check = Workflow::checkForPresMD($dsIDName);
			if ($presmd_check != false) {
				Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check, $presmd_check, 
                        "text/xml", "X");
			}

		} 
        // run the workflows on the ingested datastreams.
        // we do this in a seperate loop so that all the supporting metadata streams are ready to go
		foreach ($datastreamXMLHeaders as $dsKey => $dsTitle) {
            Workflow::processIngestTrigger($pid, $dsTitle['ID'], $dsTitle['MIMETYPE']);
        }

    }

}


/**
 * class RecordGeneral
 * For general record stuff - shared by collections and communities as well as records. 
 */
class RecordGeneral
{
    var $pid;
    var $xdis_id;
    var $no_xdis_id = false;  // true if we couldn't find the xdis_id
    var $viewer_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator"); 
    var $editor_roles;
    var $creator_roles;
    var $checked_auth = false;
    var $auth_groups;

    /**
     * RecordObject
     * If instantiated with a pid, then this object is linked with the record with the pid, otherwise we are inserting
     * a record.
     */
    function RecordGeneral($pid=null)
    {
        $this->pid = $pid;
        $this->editor_roles = Misc::array_clean($this->viewer_roles, "Viewer");
        $this->creator_roles = $this->editor_roles;
    }

    /**
      * refresh
      * Reset the status of the record object so that all values will be re-queried from the database.
      * Call this function if the database is expected to have changed in relation to this record.
      */
    function refresh()
    {
        $this->checked_auth = false;
    }

    /**
     * getXmlDisplayId
     * Retrieve the display id for this record
     */
    function getXmlDisplayId() {
        if (!$this->no_xdis_id) {
            if (is_null($this->xdis_id)) {
                $xdis_array = Fedora_API::callGetDatastreamContentsField($this->pid, 'FezMD', array('xdis_id'));
                if (isset($xdis_array['xdis_id'][0])) {
                    $this->xdis_id = $xdis_array['xdis_id'][0];
                } else {
                    $this->no_xdis_id = true;
                    return null;
                }
            }
            return $this->xdis_id;
        }
        return null;
    }



    /**
     * getAuth
     * Retrieve the authroisation groups allowed for this record with the current user.
     */
    function getAuth() {
        if (!$this->checked_auth) {
            $this->getXmlDisplayId();
            $this->auth_groups = Auth::getAuthorisationGroups($this->pid);
            $this->checked_auth = true;
        }
		
        return $this->auth_groups;
    }

    /**
     * checkAuth
     * Find out if the current user can perform the given roles for this record
     */
    function checkAuth($roles, $redirect=true) {
        global $HTTP_SERVER_VARS;
        $this->getAuth();
		return Auth::checkAuthorisation($this->pid, $roles, 
                    $HTTP_SERVER_VARS['PHP_SELF']."?".$HTTP_SERVER_VARS['QUERY_STRING'], $this->auth_groups, $redirect); 
    }
    
    /**
     * canView 
     * Find out if the current user can view this record
     */
    function canView($redirect=true) {
        return $this->checkAuth($this->viewer_roles, $redirect);
    }
    
    /**
     * canEdit
     * Find out if the current user can edit this record
     */
    function canEdit($redirect=false) {
        return $this->checkAuth($this->editor_roles, $redirect);
    }

    /**
     * canCreate
     * Find out if the current user can create this record
     */
    function canCreate($redirect=false) {
        return $this->checkAuth($this->creator_roles, $redirect);
    }


}

/**
  * RecordObject
  * Manages the interface to the database and fedora for records.
  * Stores local copies of record properties to save multiple accesses to the database.
  */
class RecordObject extends RecordGeneral
{
    var $created_date;
    var $updated_date;	
    var $file_downloads; //for statistics of file datastream downloads from eserv.php
    var $default_xdis_id = 5;
    var $display;
    var $details;
    var $record_parents;
    
    /**
     * getXmlDisplayId
     * Retrieve the display id for this record
     */
    function getObjectDates() {
		$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
		if (isset($xdis_array['created_date'][0])) {
			$this->created_date = $xdis_array['created_date'][0];
		} else {
			$this->created_date = NULL;
		}
		if (isset($xdis_array['updated_date'][0])) {
			$this->updated_date = $xdis_array['updated_date'][0];
		} else {
			$this->updated_date = NULL;
		}

    }

    /**
     * getFileDownloadsCount
     * Retrieve the count of file downloads for this record
     */
    function getFileDownloadsCount() {
		$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
//		print_r($xdis_array);
		if (is_numeric(trim($xdis_array['file_downloads'][0]))) {
			$this->file_downloads = trim($xdis_array['file_downloads'][0]);
		} else {
			$this->file_downloads = 0;
		}
    }
    
    /**
     * updateAdminDatastream
     * Used to assocaiate a display for this record
     */
    function updateAdminDatastream($xdis_id) {
		$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
        $this->xdis_id = $xdis_id;
		$newXML = '<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">';
		$foundElement = false;
		foreach ($xdis_array as $xkey => $xdata) {
			foreach ($xdata as $xinstance) {
				if ($xkey == "xdis_id") {
					$foundElement = true;
					$newXML .= "<".$xkey.">".$this->xdis_id."</".$xkey.">";				
				} elseif ($xinstance != "") {
					$newXML .= "<".$xkey.">".$xinstance."</".$xkey.">";
				}
			}
		}
		if ($foundElement != true) {
			$newXML .= "<xdis_id>".$this->xdis_id."</xdis_id>";
		}
		$newXML .= "</FezMD>";
//		echo $newXML;
		if ($newXML != "") {
			Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", true);
			$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement("!xdis_id", 15);
			Record::removeIndexRecordByXSDMF_ID($this->pid, $xsdmf_id);
			Record::insertIndexMatchingField($this->pid, $xsdmf_id, "varchar", $this->xdis_id);
		}
    }

    function setStatusId($sta_id)
    {
        $this->setFezMD_Datastream('sta_id', $sta_id);
        $this->getDisplay();
        $this->display->processXSDMF($this->pid); 
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!sta_id'); 
        Record::removeIndexRecordByXSDMF_ID($this->pid, $xsdmf_id);
        Record::insertIndexMatchingField($this->pid, $xsdmf_id, "varchar", $sta_id);
    }

    function setFezMD_Datastream($key, $value) 
    {
        $items = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
        $newXML = '<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">';
        $foundElement = false;
        foreach ($items as $xkey => $xdata) {
            foreach ($xdata as $xinstance) {
                if ($xkey == $key) {
                    $foundElement = true;
                    $newXML .= "<".$xkey.">".$value."</".$xkey.">";				
                } elseif ($xinstance != "") {
                    $newXML .= "<".$xkey.">".$xinstance."</".$xkey.">";
                }
            }
        }
        if ($foundElement != true) {
            $newXML .= "<$key>".$value."</$key>";
        }
        $newXML .= "</FezMD>";
        //		echo $newXML;
        if ($newXML != "") {
            Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", true);
        }
    }

    function incrementFileDownloads() {
		$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
//		print_r($xdis_array);
		if (isset($xdis_array['file_downloads'][0])) {
			$this->file_downloads = $xdis_array['file_downloads'][0];
		} else {
			$this->file_downloads = 0;
		}
		$this->file_downloads++;
		$newXML = '<FezMD xmlns:xsi="http://www.w3.org/2001/XMLSchema">';
		$foundElement = false;
		foreach ($xdis_array as $xkey => $xdata) {
			foreach ($xdata as $xinstance) {
				if ($xkey == "file_downloads") {
					$foundElement = true;
					$newXML .= "<".$xkey.">".$this->file_downloads."</".$xkey.">";				
				} elseif ($xinstance != "") {
					$newXML .= "<".$xkey.">".$xinstance."</".$xkey.">";
				}
			}
		}
		if ($foundElement != true) {
			$newXML .= "<file_downloads>".$this->file_downloads."</file_downloads>";
		}
		$newXML .= "</FezMD>";
//		echo $newXML;
		if ($newXML != "") {
			Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", true);
			$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement("!file_downloads", 15);
			Record::removeIndexRecordByXSDMF_ID($this->pid, $xsdmf_id);
			Record::insertIndexMatchingField($this->pid, $xsdmf_id, "int", $this->file_downloads);
	    }
    }

    /**
     * fedoraInsertUpdate 
     * Process a submitted record insert or update form
     */
    function fedoraInsertUpdate()
    {
        global $HTTP_POST_VARS, $HTTP_POST_FILES;

        // If pid is null then we need to ingest the object as well
        // otherwise we are updating an existing object
        $ingestObject = false;
		$existingDatastreams = array();
        if (empty($this->pid)) {
            $this->pid = Fedora_API::getNextPID();
            $ingestObject = true;
			$this->created_date = date("Y-m-d H:i:s");
			$this->updated_date = $created_date;
        } else {
			$existingDatastreams = Fedora_API::callGetDatastreams($this->pid);
			$this->getObjectDates();
			if (empty($this->created_date)) {
				$this->created_date = date("Y-m-d H:i:s");
			}
			$this->updated_date = date("Y-m-d H:i:s");
		}
        $pid = $this->pid;

            
        if (empty($this->xdis_id)) {
            $this->xdis_id = $HTTP_POST_VARS["xdis_id"];
        }
        $xdis_id = $this->xdis_id;
        $this->getDisplay();
        $display = &$this->display;
        list($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema) 
            = $display->getXsdAsReferencedArray();

		$xmlObj = '<?xml version="1.0"?>'."\n";
		$xmlObj .= "<".$xsd_element_prefix.$xsd_top_element_name." ";
		$xmlObj .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid); // for the pid, fedora uri etc
		$xmlObj .= $xml_schema;
		$xmlObj .= ">\n";

 		// @@@ CK - 6/5/2005 - Added xdis so xml building could search using the xml display ids
		$indexArray = array();
		if (!is_numeric($this->file_downloads)) {
			$this->getFileDownloadsCount();
		} 
		$file_downloads = $this->file_downloads;

		$xmlObj = Misc::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "", $indexArray, $file_downloads, $this->created_date, $this->updated_date);

		$xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">";
		//echo $xmlObj;
		$datastreamTitles = $display->getDatastreamTitles();
        Record::insertXML($pid, compact('datastreamTitles', 'xmlObj', 'indexArray'), $ingestObject);
		return $pid;
    }
    
    /**
     * getDisplay
     * Get a display object for this record
     */ 
    function getDisplay()
    {
        $this->getXmlDisplayId();
        if (!empty($this->xdis_id)) {
            if (is_null($this->display)) {
                $this->display = new XSD_DisplayObject($this->xdis_id);
            }
            return $this->display;
        } else {
            return null;
        }
    }

    /**
     * getDetails
     * Users a more object oriented approach with the goal of storing query results so that we don't need to make 
     * so many queries to view a record.
     */
    function getDetails()
    {
        if (is_null($this->details)) {
            // Get the Datastreams.
            $this->getDisplay();
            if ($this->display) {
                $this->details = $this->display->getXSDMF_Values($this->pid);
            } else {
                echo "No display for PID {$this->pid} ".__FILE__.__LINE__."<br/>";
            }
        }
        return $this->details;
    }

    /**
      * getTitle
      * Get the dc:title for the record
      */
    function getTitle()
    {
        $this->getDetails();
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!dc:title'); 
        return $this->details[$xsdmf_id];
    }

    function getDCType()
    {
        $this->getDetails();
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!dc:type'); 
        return $this->details[$xsdmf_id];
    }

    function isCollection()
    {
        return ($this->getDCType() == 'Fez_Collection') ? true : false;
    }

    function isCommunity()
    {
        return ($this->getDCType() == 'Fez_Community') ? true : false;
    }


    function getParents()
    {
        if (!$this->record_parents) {
            $this->record_parents = Record::getParents($this->pid);
        }
        return $this->record_parents;
    }

    function getWorkflowsByTrigger($trigger)
    {
        $this->getParents();
        $triggers = WorkflowTrigger::getListByTrigger($this->pid, $trigger);
        foreach ($this->record_parents as $ppid) {
            $triggers = array_merge($triggers, WorkflowTrigger::getListByTrigger($ppid, $trigger));
        }
        // get defaults
        $triggers = array_merge($triggers, WorkflowTrigger::getListByTrigger(-1, $trigger));
        return $triggers;
    }

    function getWorkflowsByTriggerAndXDIS_ID($trigger, $xdis_id, $strict=false)
    {
        $this->getParents();
        $triggers = WorkflowTrigger::getListByTriggerAndXDIS_ID($this->pid, $trigger, $xdis_id, $strict);
        foreach ($this->record_parents as $ppid) {
            $triggers = array_merge($triggers, 
                    WorkflowTrigger::getListByTriggerAndXDIS_ID($ppid, $trigger, $xdis_id, $strict));
        }
        // get defaults
        $triggers = array_merge($triggers, 
                WorkflowTrigger::getListByTriggerAndXDIS_ID(-1, $trigger, $xdis_id, $strict));
        return $triggers;
    }

    function getIngestTrigger($mimetype)
    {
        $this->getXmlDisplayId();
        $trigger = WorkflowTrigger::getIngestTrigger($this->pid, $this->xdis_id, $mimetype);
        if (!$trigger) {
            $this->getParents();
            foreach ($this->record_parents as $ppid) {
                $trigger = WorkflowTrigger::getIngestTrigger($ppid, $this->xdis_id, $mimetype);
                if ($trigger) {
                    break;
                }
            }
            if (!$trigger) {
                // get defaults
                $trigger = WorkflowTrigger::getIngestTrigger(-1, $this->xdis_id, $mimetype);
            }
        }
        return $trigger;
    }

    function getPublishedStatus()
    {
        $this->getDetails();
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!sta_id'); 
        return $this->details[$xsdmf_id];
    }

}


// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Record Class');
}
?>
