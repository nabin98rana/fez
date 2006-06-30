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
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.foxml.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.foxml.php");
include_once(APP_INC_PATH . "class.auth_rules.php");

/**
  * Record
  * Static class for accessing record related queries
  * See RecordObject for an object oriented representation of a record.
  */
class Record
{

   /**
    * Method used to get the parents of a given record available in the 
    * system. 
    *
    * @access  public
    * @param   string $pid The persistant identifier	 
    * @return  array The list
    */
    function getParents($pid, $clearcache=false)
    {

		static $returns;

        if ($clearcache) {
            $returns = array();
        }

        if (isset($returns[$pid])) {
            return $returns[$pid];
        }

		$stmt = "SELECT 
					* 
				 FROM
					" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1 inner join 
					" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on r1.rmf_xsdmf_id = x1.xsdmf_id inner join
					" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s1 on s1.sek_id = x1.xsdmf_sek_id inner join
					(SELECT r2.rmf_varchar as parent_pid
						FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,							
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
						WHERE (s2.sek_title = 'isMemberOf' AND r2.rmf_xsdmf_id = x2.xsdmf_id AND s2.sek_id = x2.xsdmf_sek_id AND r2.rmf_rec_pid = '".$pid."'))
					as p1 on p1.parent_pid = r1.rmf_rec_pid
					";	
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);						
		$return = array();
		foreach ($res as $result) {
			if (is_numeric($result['sek_id'])) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$search_var = strtolower(str_replace(" ", "_", $result['sek_title']));
				if (@!is_array($return[$result['rmf_rec_pid']][$search_var])) {
					$return[$result['rmf_rec_pid']][$search_var] = array();
				}
				if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']][$search_var])) {
					array_push($return[$result['rmf_rec_pid']][$search_var], $result['rmf_'.$result['xsdmf_data_type']]);
					sort($return[$result['rmf_rec_pid']][$search_var]);
				}
			}
		}
		$details = array_values($return);			
		$returns[$pid] = $details;
		return $details; 
    }


   /**
    * Method used to get all of the parents of a given record available in the 
    * system. 
    *
    * @access  public
    * @param   string $pid The persistant identifier	 
    * @return  array The list
    */
    function getParentsAll($pid)
    {

		static $returns;

        if (isset($returns[$pid])) {
            return $returns[$pid];
        }

		$stmt = "SELECT 
					* 
				 FROM
					" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r1 inner join 
					" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1 on r1.rmf_xsdmf_id = x1.xsdmf_id inner join
					" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s1 on s1.sek_id = x1.xsdmf_sek_id inner join
					(SELECT r2.rmf_varchar as parent_pid
						FROM  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r2,
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2,							
							  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s2
						WHERE (s2.sek_title = 'isMemberOf' AND r2.rmf_xsdmf_id = x2.xsdmf_id AND s2.sek_id = x2.xsdmf_sek_id AND r2.rmf_rec_pid = '".$pid."'))
					as p1 on p1.parent_pid = r1.rmf_rec_pid
					";	
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);						
		$return = array();
		foreach ($res as $result) {
			if (is_numeric($result['sek_id'])) {
				$return[$result['rmf_rec_pid']]['pid'] = $result['rmf_rec_pid'];
				$search_var = strtolower(str_replace(" ", "_", $result['sek_title']));
				if (@!is_array($return[$result['rmf_rec_pid']][$search_var])) {
					$return[$result['rmf_rec_pid']][$search_var] = array();
				}
				if (!in_array($result['rmf_'.$result['xsdmf_data_type']], $return[$result['rmf_rec_pid']][$search_var])) {
					array_push($return[$result['rmf_rec_pid']][$search_var], $result['rmf_'.$result['xsdmf_data_type']]);
					sort($return[$result['rmf_rec_pid']][$search_var]);
				}
			}
		}
		$details = array_values($return);
		$recursive_details = array();
		foreach ($details as $key => $row) {
			$temp = Record::getParents($row['pid']);
			foreach ($temp as $trow) {
				array_push($recursive_details, $trow);
			}
		}
		foreach ($recursive_details as $rrow) {
			array_push($details, $rrow);
		}
		$details = array_reverse($details);
		$returns[$pid] = $details;
		return $details; 
    }


    /**
     * Method used to update the details of a specific Record. Now calls the class.
     *
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function update($pid, $exclude_list=array(), $specify_list=array())
    {
        $record = new RecordObject($pid);
        if ($record->fedoraInsertUpdate($exclude_list, $specify_list)) {
            return 1;
        } else {
            return -1;
        }
    }

    /**
     * Method used to edit the security (FezACML) details of a specific Datastream. 
     *
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @param   string $dsID The datastream ID of the datastream
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function editDatastreamSecurity($pid, $dsID)
    {
//        $record = new RecordObject($pid);
		$xdis_id = XSD_Display::getID('FezACML for Datastreams');
		$display = new XSD_DisplayObject($xdis_id);
        list($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema) 
            = $display->getXsdAsReferencedArray();
		$indexArray = array();
		$header .= "<".$xsd_element_prefix.$xsd_top_element_name." ";
		$header .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid);
		$header .= ">\n";    
		$xmlObj .= Foxml::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "", $indexArray, '', '', '');
        $xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">\n";
		$xmlObj = $header . $xmlObj;
		$FezACML_dsID = "FezACML_".$dsID.".xml";
		if (Fedora_API::datastreamExists($pid, $FezACML_dsID)) {
			Fedora_API::callModifyDatastreamByValue($pid, $FezACML_dsID, "A", "FezACML security for datastream - ".$dsID, 
					$xmlObj, "text/xml", "true");
		} else {
			Fedora_API::getUploadLocation($pid, $FezACML_dsID, $xmlObj, "FezACML security for datastream - ".$dsID, 
					"text/xml", "X");
		}	
		Record::insertIndexBatch($pid, $dsID, $indexArray, array(), array(), array("FezACML"));
//		exit;
    }

   /**
     * Method used to update the Admin details (FezMD) of a specific Record.
     *
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @param   integer $xdis_id The XSD Display ID of the record
     * @return  integer 1 if the update worked, -1 otherwise
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

   /**
     * Method used to increment the file download counter of a specific Record.
     *
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @return  integer 1 if the update worked, -1 otherwise
     */
    function incrementFileDownloads($pid)
    {
		if (!empty($pid)) {					
			$record = new RecordObject($pid);
			if ($record->incrementFileDownloads()) {
				return 1;
			} else {
				return -1;
			}
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
     * Method used to add to the Fez Index in a batch.
     *
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @param   string $dsID The ID of the datastream (optional)
     * @param   array $indexArray The array of XSDMF entries to the Fez index
     * @param   string $datastreamXMLHeaders 
     * @return  void
     */
	function insertIndexBatch($pid, $dsID='', $indexArray, $datastreamXMLHeaders, $exclude_list=array(), $specify_list=array()) {
		// first delete all indexes about this pid
		Record::removeIndexRecord($pid, $dsID, 'keep', $exclude_list, $specify_list);
		if (!is_array($indexArray)) {
			return false;
		}
		foreach ($indexArray as $index) {
			if ($index[1] == 1)  { // if this xsdmf is designated to be indexed then insert it as long as it has a value
				foreach ($datastreamXMLHeaders as $dsKey => $dsHeader) { // get the real ds names for the file uploads
					if ($index[6] == $dsKey) {
						$index[6] = $dsHeader['ID'];
					}
				}
				if ($index[6] != "") {
                    // pid, dsID, xsdmf_id, data_type, value
					Record::insertIndexMatchingField($index[0], $dsID, $index[2], $index[5], $index[6]);
				}
			}
		}	
	}

    /**
     * Method used to remove an entry in the Fez Index.
     *
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @param   string $dsID The ID of the datastream (optional)
     * @param   string $dsDelete A flag to check if the datastream_id should be kept 
     * @return  void
     */
    function removeIndexRecord($pid, $dsID='', $dsDelete='all', $exclude_list=array(), $specify_list=array()) {
        if (empty($pid)) {
            return -1;
        }
		$exclude_str = implode("', '", $exclude_list);
		$specify_str = implode("', '", $specify_list);

        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
				 WHERE rmf_rec_pid = '" . $pid . "'";
		if ($dsID != '') {
			$stmt .= " and rmf_dsid = '".$dsID."' ";
		} else {
			if ($dsDelete=='keep') {		
				$stmt .= " and (rmf_dsid IS NULL or rmf_dsid = '') "; // we don't want to delete the datastream fezacml indexes unless we are deleteing the whole object
			}
		}
		if ($dsDelete=='keep') {
			$stmt .= " and (rmf_xsdmf_id not in (select distinct(xsdmf_id) from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_element = '!datastream!ID')";
		}
		if ($specify_str != "") {				
			$stmt .= " and rmf_xsdmf_id in (select distinct(x2.xsdmf_id) from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2 inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d1 on x2.xsdmf_xdis_id=d1.xdis_id inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd as xsd1 on (xsd1.xsd_id = d1.xdis_xsd_id and xsd1.xsd_title in ('".$specify_str."'))) ";
			if ($dsDelete=='keep') {
				$stmt .= ")";
			}
		} elseif ($exclude_str != "") {
			$stmt .= " and rmf_xsdmf_id in (select distinct(x2.xsdmf_id) from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x2 inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d1 on x2.xsdmf_xdis_id=d1.xdis_id inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd as xsd1 on (xsd1.xsd_id = d1.xdis_xsd_id and xsd1.xsd_title not in ('".$exclude_str."'))) ";			
			if ($dsDelete=='keep') {
				$stmt .= ")";
			}
		} else {
			if ($dsDelete=='keep') {
				$stmt .= ")";
			}
		}

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return $pid;
        }

        Record::clearIndexAuth($pid);
    }

    /**
     * Method used to remove an entry in the Fez Index by its value
     *
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @param   string $value The value to check for when deleting
     * @param   string $data_type defaults to varchar, but can be date, int, text etc
     * @return  string The $pid if successful, otherwise -1
     */
    function removeIndexRecordByValue($pid, $value, $data_type='varchar')
    {
         $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
				 WHERE rmf_rec_pid = '" . $pid . "' and rmf_".$data_type."='".$value."'";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return $pid;
        }
    }

    /**
     * Method used to remove an entry in the Fez Index by its XSD Matching Field ID
     *
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @param   string $xsdmf_id The XSD Matching Field ID to check for when deleting
     * @return  string The $pid if successful, otherwise -1
     */
    function removeIndexRecordByXSDMF_ID($pid, $xsdmf_id)
    {
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
				 WHERE rmf_rec_pid = '" . $pid . "' and rmf_xsdmf_id=".$xsdmf_id;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            return $pid;
        }
    }

    /**
     * Method used to insert an entry in the Fez Index.
     *
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @param   string $dsID The ID of the datastream (optional)
     * @param   integer $xsdmf_id The XSD Matching Field ID 
     * @param   string $data_type The data_type of the index to save the value into
     * @param   string $value The value of the index to be saved
     * @return  string The $pid if successful, otherwise -1
     */
    function insertIndexMatchingField($pid, $dsID='', $xsdmf_id, $data_type, $value)
    {
        $xsdsel_id = '';
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field
                 (
				 	rmf_rec_pid,
				 	rmf_dsid,
                    rmf_xsdmf_id,";
		if ($xsdsel_id != "") {
		  $stmt .= "rmf_xsdsel_id,";
		}
		$stmt .= "                    
			rmf_".$data_type."
		 ) VALUES (
			'" . $pid . "',
			'" . $dsID . "',
			" . $xsdmf_id . ",";
		if ($xsdsel_id != "") {
			$stmt .= "'$xsdsel_id', ";
		}
        $stmt .= "'".Misc::escapeString(trim($value)) . "')";
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
            "sort_by"        => $sort_by ? $sort_by : "rec_id",
            "sort_order"     => $sort_order ? $sort_order : "DESC",
            // quick filter form
            'keywords'       => Record::getParam('keywords')
        );
		$existing_cookie = Record::getCookieParams();
		global $HTTP_POST_VARS, $HTTP_GET_VARS;
        $encoded = base64_encode(serialize($cookie));
        setcookie(APP_LIST_COOKIE, $encoded, APP_LIST_COOKIE_EXPIRE);
        return $cookie;
    }


    /**
     * Method used to get the current sorting options used in the grid layout
     * of the Record listing page.
     *
	 * Developer Note: Not used yet..
	 * 
     * @access  public
     * @param   array $options The current search parameters
     * @return  array The sorting options
     */
    function getSortingInfo($options)
    {
        global $HTTP_SERVER_VARS;
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
        return $items;
    }

    /**
     * Method used to get the FezACML datastream XML content of the record
	 * 
     * @access  public
     * @param   string $pid The persistent identifier of the object
     * @param   string $dsID (optional) The datastream ID
     * @return  domdocument $xmldoc A Dom Document of the XML or false if not found
     */
	function getACML($pid, $dsID="") {
        static $acml_cache;
		if ($dsID != "") {
	        if (isset($acml_cache['ds'][$dsID][$pid])) {
				return $acml_cache['ds'][$dsID][$pid];
			} else {
				$ds_search = 'FezACML_'.$dsID.'.xml';
			}
		} else {
			$ds_search = 'FezACML';
		}
        if (isset($acml_cache['pid'][$pid])) {
            return $acml_cache['pid'][$pid];
        }
	
		$DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $ds_search);
		$xmlACML = @$DSResultArray['stream'];
		if ($xmlACML != "") {
			$xmldoc= new DomDocument();
			$xmldoc->preserveWhiteSpace = false;
			$xmldoc->loadXML($xmlACML);
			if ($dsID != "") {
				$acml_cache['ds'][$dsID][$pid] = $xmldoc;
			} else {
				$acml_cache['pid'][$pid] = $xmldoc;
			}
			return $xmldoc;
		} else {
			if ($dsID != "") {
				$acml_cache['ds'][$dsID][$pid] = false;
			} else {
				$acml_cache['pid'][$pid] = false;
			}
			return false;
		}
	}


    /**
     * Method used to get the details for a specific Record gotten directly from the Fedora repository.
     *
     * @access  public
     * @param   string $pid The persistent identifier of the object
     * @param   string $xdis_id  The XSD Display ID of the object
     * @return  array $xsdmf_array The details for the XML object against its XSD Matching Field IDs
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
				echo $xmlDatastream;
                $array_ptr = array();
                Misc::dom_xml_to_simple_array($xmlnode, $array_ptr, $xsd_top_element_name, $xsd_element_prefix, $xsdmf_array, $xdis_id);
            }
		}
		return $xsdmf_array;
    }

    /**
     * Method used to get the default record XDIS_ID
     *
     * Developer Note: Need to make this able to be set in Administrative interface and stored in the Fez database,
	 * although this is not really much used anymore.
     *	 
     * @access  public
     * @return  integer $xdis_id The XSD Display ID of a generic Fez record
     */
    function getRecordXDIS_ID() {
		// will make this more dynamic later. (probably feed from a mysql table which can be configured in the gui admin interface).
		// this isn't realy used much anymore
		$xdis_id = 5;
		return $xdis_id;
    }

    /**
     * Find unpublished records that the user has a role on.
     *
     * @access  public
     * @param string $username The username of the search is performed on
     * @return array $res2 The index details of records associated with the user
     */
    function getAssigned($username,$currentPage=0,$pageRows="ALL",$order_by="Title")
    {
        if ($pageRows == "ALL") {
            $pageRows = 9999999;
        }
        $start = $currentPage * $pageRows;

        $currentRow = $currentPage * $pageRows;
        $sekdet = Search_Key::getDetailsByTitle($order_by);
        $data_type = $sekdet['xsdmf_data_type'];
		$dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;

		$authArray = Collection::getAuthIndexStmt(array("Editor", "Approver"));
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];
		$order_dir = "ASC";
        if (!empty($authStmt)) {
            $r4_join_field = "ai.authi_pid";
        } else {
            $r4_join_field = "r2.rmf_rec_pid";
        }

        $bodyStmtPart1 = "FROM  {$dbtp}record_matching_field AS r2
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x2
                      ON r2.rmf_xsdmf_id = x2.xsdmf_id AND match(x2.xsdmf_element) against ('\"!sta_id\"' in boolean mode) and r2.rmf_varchar!='2'


                    $authStmt

                    ";
        $bodyStmt = "$bodyStmtPart1

                    LEFT JOIN " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "record_matching_field r5 on r5.rmf_rec_pid = r2.rmf_rec_pid
                    inner join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x5
                    on r5.rmf_xsdmf_id = x5.xsdmf_id
                    left join " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "search_key s5			
					on (s5.sek_id = x5.xsdmf_sek_id and s5.sek_title = '$order_by')  
					where (r5.rmf_varchar is null) or s5.sek_title = '$order_by'					
					group by r5.rmf_rec_pid

					
                    
             ";

        $countStmt = "
                    SELECT count(distinct r2.rmf_rec_pid)
                    $bodyStmtPart1
            ";

        $stmt = "SELECT  r1.*, x1.*, s1.*, k1.*, d1.* 
            FROM {$dbtp}record_matching_field AS r1
            INNER JOIN {$dbtp}xsd_display_matchfields AS x1
            ON r1.rmf_xsdmf_id = x1.xsdmf_id
            INNER JOIN (
                    SELECT distinct r2.rmf_rec_pid, min(r5.rmf_$data_type) as sort_column
                    $bodyStmt
					order by sort_column $order_dir, r2.rmf_rec_pid desc
                    LIMIT $start, $pageRows
                    ) as display ON display.rmf_rec_pid=r1.rmf_rec_pid 
            LEFT JOIN {$dbtp}xsd_loop_subelement s1 
            ON (x1.xsdmf_xsdsel_id = s1.xsdsel_id) 
            LEFT JOIN {$dbtp}search_key k1 
            ON (k1.sek_id = x1.xsdmf_sek_id)
            LEFT JOIN {$dbtp}xsd_display d1  
            ON (d1.xdis_id = r1.rmf_varchar and k1.sek_title = 'Display Type')
            ORDER BY display.sort_column $order_dir, r1.rmf_rec_pid DESC ";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
			if (PEAR::isError($res)) {
				Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				$res = array();
			}
		/*} else {
			$res = array();
		}*/
        $list = Collection::makeReturnList($res);
		
		$totalRows = $GLOBALS["db_api"]->dbh->getOne($countStmt);
//        $totalRows = count($list);
//        $list = array_slice($list,$currentRow, $pageRows);
        $totalPages = intval($totalRows / $pageRows);
        if ($totalRows % $pageRows) {
            $totalPages++;
        }
        $nextPage = ($currentPage >= $totalPages) ? -1 : $currentPage + 1;
        $prevPage = ($currentPage <= 0) ? -1 : $currentPage - 1;
        $lastPage = $totalPages - 1;
        $currentLastRow = $currentRow + count($list);
        $info = compact('totalRows', 'pageRows', 'currentRow','currentLastRow','currentPage','totalPages',
                'nextPage','prevPage','lastPage');
        return compact('info','list');
    }

    /**
     * Publishs all objects that don't have a status ID set, really only used for 
     * development testing, but left in for now
     *
     * @access  public
     * @return void
     */
    function publishAllUnsetStatusPids($sta_id=2)
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
                $r->setStatusId($sta_id);
            }
        }
    }

    /**
     * Gets the index records for a datastream
     *
     * @access  public
     * @param   string $pid The persistent identifier of the object
     * @param   string $dsID The datastream ID
     * @param   string $xsd_title The title of the XSD 
     * @return array
     */
    function getIndexDatastream($pid, $dsID, $xsd_title)
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX; // Database and table prefix
        $stmt = "SELECT * FROM 
        {$dbtp}record_matching_field r1
		inner join {$dbtp}xsd_display_matchfields x1 on r1.rmf_xsdmf_id = x1.xsdmf_id and rmf_rec_pid = '".$pid."' and rmf_dsid = '".$dsID."'
        inner join {$dbtp}xsd_display d1 on x1.xsdmf_xdis_id = d1.xdis_id
		inner join {$dbtp}xsd x2 on x2.xsd_id = d1.xdis_xsd_id and x2.xsd_title = '".$xsd_title."'
		left join {$dbtp}xsd_loop_subelement s1 on s1.xsdsel_id = x1.xsdmf_xsdsel_id";

        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		return $res;
    }

    /**
     * Sets the index during batch import. Could also be used in future versions for objects in 
     * Fedora that are not in the index yet.
	 * EG a "Re-index Fedora" type of admin function.
     *
     * @access  public
     * @param   string $xdis_id  The XSD Display ID of the object
     * @param   string $pid The persistent identifier of the object
     * @return  void
     */
    function setIndexMatchingFields($pid, $dsID='') 
    {
        // careful what you do with the record object - don't want to use the index while reindexing 
        $record = new RecordObject($pid);
        $xdis_id = $record->getXmlDisplayId();
        if (!is_numeric($xdis_id)) {
            $xdis_id = XSD_Display::getXDIS_IDByTitle('Generic Document');
        }
//		echo $xdis_id; exit;
        $display = new XSD_DisplayObject($xdis_id);
        $array_ptr = array();
        $xsdmf_array = $display->getXSDMF_Values($pid);		
		Record::removeIndexRecord($pid); // remove any existing index entry for that PID // CK added 9/1/06 - still working on this
//        print_r($xsdmf_array);
        foreach ($xsdmf_array as $xsdmf_id => $xsdmf_value) {
            if (!is_array($xsdmf_value) && !empty($xsdmf_value) && (trim($xsdmf_value) != "")) {					
                $xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
                Record::insertIndexMatchingField($pid, $dsID, $xsdmf_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_value);					
            } elseif (is_array($xsdmf_value)) {
                foreach ($xsdmf_value as $xsdmf_child_value) {
                    if ($xsdmf_child_value != "") {
                        $xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
                        Record::insertIndexMatchingField($pid, $dsID, $xsdmf_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_child_value);
                    }
                }
            }
        }
        Record::setIndexAuth($pid);
    }


    function setIndexAuth($pid, $topcall=true)
    {
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        // topcall means this is the first call and not a recursion.  We want to clear all our caches at the
        // start but then use them as we recurse.
        if ($topcall) {
            // clear the parent cache
            Record::getParents($pid, true);
        }
        $res = Record::getIndexAuth($pid,$topcall);
        $rows = array();
        $values = '';
        $has_list_rules = false;
        if (!empty($res)) {
            // add some pre-processed special rules
            foreach ($res as $source_pid => $groups) {
                foreach ($groups as $role => $group) {
                    foreach ($group as $row) {
                        // check for rules on listing to determine if this pid is public or not
                        if ($row['role'] == 'Lister') {
                            $has_list_rules = true;
                        }
                    }   

                }
            }
        }
        // if no lister rules are found, then this pid is publically listable
        if (!$has_list_rules) {
            $res[$pid]['Lister'][] = array('pid' => $pid, 'role' => 'Lister', 
                    'rule' => 'public_list', 'value' => 1);
        }
        // get the group ids
        foreach ($res as $source_pid => $groups) {
            foreach ($groups as $role => $group) {
                $arg_id = AuthRules::getOrCreateRuleGroup($group,$topcall);
                $values .= "('$pid', '$role', '$arg_id'),";
                $rows[] = array('authi_pid' => $pid, 'authi_role' => $role, 'authi_arg_id' => $arg_id);
            }
        }
        $values = rtrim($values,', ');
        // Only check for change of rules at top of recursion, otherwise it slows things down too much.
        if ($topcall) {
            // check if the auth rules have changed for this pid - if they haven't then we don't need to recurse.
            $stmt = "SELECT * FROM {$dbtp}auth_index2 WHERE authi_pid='$pid' ";
            $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            }
            $rules_changed = false;
            // check for added rules
            foreach ($res as $dbrow) {
                $found = false;
                foreach ($rows as $crow) {
                    if ($crow['authi_role'] == $dbrow['authi_role']
                            && $crow['authi_arg_id'] == $dbrow['authi_arg_id']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $rules_changed = true;
                    break;
                }
            }
            if (!$rules_changed) {
                // check for deleted rules
                foreach ($rows as $crow) {
                    $found = false;
                    foreach ($res as $dbrow) {
                        if ($crow['authi_role'] == $dbrow['authi_role']
                                && $crow['authi_arg_id'] == $dbrow['authi_arg_id']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $rules_changed = true;
                        break;
                    }
                }
            }
        } else {
            // We are already recursing 
            $rules_changed = true;
        }
        if ($rules_changed) {
            Record::clearIndexAuth($pid);
            $stmt = "INSERT INTO {$dbtp}auth_index2 (authi_pid,authi_role,authi_arg_id) VALUES $values ";
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            }
            // get children and update their indexes.
            $rec = new RecordGeneral($pid);
            $children = $rec->getChildrenPids();
            foreach ($children as $child_pid) {
                Record::setIndexAuth($child_pid,false);
            }
        }
        return 1;
    }

    function getIndexAuth($pids, $clearcache=false, &$done_pids = array())
    {
        static $pid_cache;

        if ($clearcache) {
            $pid_cache = array();
        }
        if (empty($pids)) {
            return array();
        } elseif (!is_array($pids)) {
            $pids = array($pids);
        }
        // don't get the same pids twice
        $pids = array_diff($pids, $done_pids);
        if (empty($pids)) {
            return array();
        }
        foreach ($pids as $pid) {
            $auth_groups = array();
            if (!isset($pid_cache[$pid])) {
            $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
            $stmt = "SELECT rmf_rec_pid as pid, xsdmf_parent_key_match as role, xsdmf_element as rule, rmf_varchar as value 
                FROM {$dbtp}record_matching_field AS r1 
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x1 ON    
                rmf_rec_pid = '$pid'
                AND (r1.rmf_dsid IS NULL or r1.rmf_dsid = '') 
                AND (xsdmf_element in ('!rule!role!Fez_User',
                            '!rule!role!AD_Group',
                            '!rule!role!AD_User',
                            '!rule!role!AD_DistinguishedName',
                            '!rule!role!Fez_Group',
                            '!rule!role!in_AD',
                            '!rule!role!in_Fez',
                            '!inherit_security',
                            '!rule!role!eduPersonTargetedID',
                            '!rule!role!eduPersonAffiliation',
                            '!rule!role!eduPersonScopedAffiliation',
                            '!rule!role!eduPersonPrimaryAffiliation',
                            '!rule!role!eduPersonPrinicipalName',
                            '!rule!role!eduPersonOrgUnitDN',
                            '!rule!role!eduPersonPrimaryOrgUnitDN')
                    )
                    AND r1.rmf_xsdmf_id=x1.xsdmf_id
                    ";
            $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            }	
            $found_inherit_off = false;
            if (!empty($res)) {
                // split into roles
                $groups = Misc::collateArray($res, 'role');
                foreach ($groups as $role => $group) {
                    $auth_groups[$pid][$role] = $group;
                }
                
                // check the inherit flag and merge
                foreach ($res as $row) {
                    if ($row['rule'] == '!inherit_security') {
                        if (!empty($row['value']) && $row['value'] != 'on') {
                            $found_inherit_off = true;
                        }
                    } 
                }
            }

            if (!$found_inherit_off) {
                // get security from parents 
                    $parents1 = Record::getParents($pid);
                $parents = array_keys(Misc::keyArray($parents1, 'pid'));
                    $auth_groups = array_merge_recursive($auth_groups, 
                            Record::getIndexAuth($parents,false,$done_pids));
            }
                $pid_cache[$pid] = $auth_groups;
            }
            $done_pids[] = $pid;
        }
        $auth_groups = array();
        foreach ($pids as $pid) {
            $auth_groups = array_merge_recursive($auth_groups, $pid_cache[$pid]);
        }
        return $auth_groups;
    }

    function clearIndexAuth($pids)
    {
        if (empty($pids)) {
            return -1;
        } elseif (!is_array($pids)) {
            $pids = array($pids);
        }
        $pids_str = Misc::arrayToSQL($pids);
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        $stmt = "DELETE FROM {$dbtp}auth_index2 WHERE authi_pid IN ($pids_str) ";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }	
        return 1;
    }

    /**
	 * Sets up a template for insertion into Fedora. Used in workflows. 
     *
     * @access  public
     * @return  array Array of datastreamTitles, xmlObj and indexArray
     */
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
		$xmlObj = Foxml::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "", $indexArray, 0, $created_date, $updated_date);
		$xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">";
        $xmlObj = Foxml::setDCTitle('__makeInsertTemplate_DCTitle__', $xmlObj);
        // hose the index array as we'll generate it from the ingested object later
        $indexArray = array();
		$datastreamTitles = $display->getDatastreamTitles();
        return compact('datastreamTitles', 'xmlObj', 'indexArray', 'xdis_id'); 
    }

    /**
	 * Inserts an object template into Fedora. Used in workflows. 
     *
     * @access  public
     * @param   string $pid The persistant identifier of the object
     * @param   array $dsarray The array of datastreams
     * @return  void
     */
    function insertFromTemplate($pid, $xdis_id, $title, $dsarray)
    {
        extract($dsarray);
        // find all instances of '__makeInsertTemplate_PID__' in xmlObj and replace with the correct PID
        // xmlObj is still a text representation at this stage.
        $xmlObj = str_replace('__makeInsertTemplate_PID__', $pid, $xmlObj);
        $xmlObj = str_replace('__makeInsertTemplate_DCTitle__', $title, $xmlObj);
        Record::insertXML($pid, compact('datastreamTitles', 'xmlObj', 'indexArray','xdis_id'), true);
    }

    /**
	 * Inserts an object xml into Fedora. Used in workflows. 
     *
     * @access  public
     * @param   string $pid The persistant identifier of the object
     * @param   array $dsarray The array of datastreams	 
     * @param   boolean $ingestObject Should we insert as a new object into fedora (false if updating an 
     *                                exisitng object).
     * @return  void
     */   
    function insertXML($pid, $dsarray, $ingestObject)
    {
        $existingDatastreams = array();  // may be overwritten by extract

        extract($dsarray);
        //echo "<pre>".htmlspecialchars($xmlObj)."</pre>";
        $params = array();

		$datastreamXMLHeaders = Misc::getDatastreamXMLHeaders($datastreamTitles, $xmlObj, $existingDatastreams);

		$datastreamXMLContent = Misc::getDatastreamXMLContent($datastreamXMLHeaders, $xmlObj);
		
        if (@is_array($datastreamXMLHeaders["File_Attachment0"])) { // it must be a multiple file upload so remove the generic one
			$datastreamXMLHeaders = Misc::array_clean_key($datastreamXMLHeaders, "File_Attachment", true, true);
		}
		if (@is_array($datastreamXMLHeaders["Link0"])) { // it must be a multiple link item so remove the generic one
			$datastreamXMLHeaders = Misc::array_clean_key($datastreamXMLHeaders, "Link", true, true);
		}
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

            if (!defined('APP_NO_TIDY') || !APP_NO_TIDY) {
                $tidy = new tidy;
                $tidy->parseString($xmlObj, $config, 'utf8');
                $tidy->cleanRepair();
                $xmlObj = "$tidy";
            }
            $result = Fedora_API::callIngestObject($xmlObj);
            if (is_array($result)) {
                Error_Handler::logError($xmlObj, __FILE__,__LINE__);
            }
        }
		
/*		if (@is_array($datastreamXMLHeaders["File_Attachment0"])) { // it must be a multiple file upload so remove the generic one
			$datastreamXMLHeaders = Misc::array_clean_key($datastreamXMLHeaders, "File_Attachment", true, true);
		}
*/		
		$convert_check = false;

//		Record::insertIndexBatch($pid, '', $indexArray, $datastreamXMLHeaders, $exclude_list, $specify_list);

        // ingest the datastreams
		foreach ($datastreamXMLHeaders as $dsKey => $dsTitle) {		

			$dsIDName = $dsTitle['ID'];

			if (is_numeric(strpos($dsIDName, "."))) {
				$filename_ext = strtolower(substr($dsIDName, (strrpos($dsIDName, ".") + 1)));
				$dsIDName = substr($dsIDName, 0, strrpos($dsIDName, ".") + 1).$filename_ext;
			}
			if ($dsIDName == "DC") { // Dublic core is special, it cannot be deleted
		    	Fedora_API::callModifyDatastreamByValue($pid, $dsIDName, $dsTitle['STATE'], $dsTitle['LABEL'],
                        $datastreamXMLContent[$dsKey], $dsTitle['MIMETYPE'], false); 
			} else {
				if ($dsTitle['CONTROL_GROUP'] == "R" ) { // if its a redirect we don't need to upload the file
                    if (Fedora_API::datastreamExists($pid, $dsIDName)) {
                        Fedora_API::callPurgeDatastream($pid, $dsIDName);
//				    	Fedora_API::callModifyDatastreamByValue($pid, $dsIDName, $dsTitle['STATE'], $dsTitle['LABEL'],
//    	                    $datastreamXMLContent[$dsKey], $dsTitle['MIMETYPE'], "false"); 

                    } 
                    $location = trim($datastreamXMLContent[$dsKey]);
                    if (!empty($location)) {
//						Fedora_API::getUploadLocation($pid, $dsTitle['ID'], $datastreamXMLContent[$dsKey], $dsTitle['LABEL'],
//							$dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
                        Fedora_API::callAddDatastream($pid, $dsTitle['ID'], $datastreamXMLContent[$dsKey], 
                                $dsTitle['LABEL'], $dsTitle['STATE'], $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
                    }
                } elseif ($dsTitle['CONTROL_GROUP'] == "X") {
					if (Fedora_API::datastreamExists($pid, $dsIDName)) {
				    	Fedora_API::callModifyDatastreamByValue($pid, $dsIDName, $dsTitle['STATE'], $dsTitle['LABEL'],
    	                    $datastreamXMLContent[$dsKey], $dsTitle['MIMETYPE'], "false"); 
					} else {
						Fedora_API::getUploadLocation($pid, $dsTitle['ID'], $datastreamXMLContent[$dsKey], $dsTitle['LABEL'],
							$dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
					}
 				} else { // control group == 'M'

					if (is_numeric(strpos($dsIDName, chr(92)))) {
						$dsIDName = substr($dsIDName, strrpos($dsIDName, chr(92))+1);
					}
					if (is_numeric(strpos($dsTitle['LABEL'], chr(92)))) {
						$dsTitle['LABEL'] = substr($dsTitle['LABEL'], strrpos($dsTitle['LABEL'], chr(92))+1);
					}
                    $ncName = Foxml::makeNCName($dsIDName);
					if (Fedora_API::datastreamExists($pid, $ncName)) {
						Fedora_API::callPurgeDatastream($pid, $ncName);
					}
					Fedora_API::getUploadLocation($pid, $ncName, $datastreamXMLContent[$dsKey], $dsTitle['LABEL'], 
							$dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
			
					$presmd_check = Workflow::checkForPresMD(Foxml::makeNCName($dsIDName));
					if ($presmd_check != false) {
						if (is_numeric(strpos($presmd_check, chr(92)))) {
							$presmd_check = substr($presmd_check, strrpos($presmd_check, chr(92))+1);
						}
						if (Fedora_API::datastreamExists($pid, $presmd_check)) {
							Fedora_API::callPurgeDatastream($pid, $presmd_check);
						}
						Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check, $presmd_check, 
								"text/xml", "X");
                        if (is_file(APP_DELETE_DIR.basename($presmd_check))) {
                            $deleteCommand = APP_DELETE_CMD." ".APP_DELETE_DIR.basename($presmd_check);
                            exec($deleteCommand);
                        }
					}
				}
			}			
		} 

        // run the workflows on the ingested datastreams.
        // we do this in a seperate loop so that all the supporting metadata streams are ready to go
		foreach ($datastreamXMLHeaders as $dsKey => $dsTitle) {
			if ($dsTitle['CONTROL_GROUP'] == "M" ) {
				Workflow::processIngestTrigger($pid, Foxml::makeNCName($dsTitle['ID']), $dsTitle['MIMETYPE']);
				//clear the managed content file temporarily saved in the APP_TEMP_DIR
				$ncNameDelete = Foxml::makeNCName($dsTitle['ID']);
				if (is_file(APP_DELETE_DIR.$ncNameDelete)) {
					$deleteCommand = APP_DELETE_CMD." ".APP_DELETE_DIR.$ncNameDelete;
					exec($deleteCommand);
				}
			}
        }

		Record::setIndexMatchingFields($pid);
		

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
    var $display;
    var $details;
    var $record_parents;
    var $status_array = array(
            0 => 'Undefined',
            1 => 'Unpublished',
            2 => 'Published'
            );
 
    /**
     * RecordGeneral
     * If instantiated with a pid, then this object is linked with the record with the pid, otherwise we are inserting
     * a record.
	 * 
     * @access  public
     * @param   string $pid The persistant identifier of the object
     * @return  void
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
	  * 
      * @access  public
      * @return  void
      */
    function refresh()
    {
        $this->checked_auth = false;
    }

    /**
     * getXmlDisplayId
     * Retrieve the display id for this record
	 * 
     * @access  public
     * @return  void
     */
    function getXmlDisplayId() {
        if (!$this->no_xdis_id) {
            if (empty($this->xdis_id)) {
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
     * getImageFezACML
     * Retrieve the FezACML image details eg copyright message and watermark boolean settings
	 * 
     * @access  public
     * @return  void
     */
    function getImageFezACML($dsID) {
		if (!empty($dsID)) {
			$xdis_array = Fedora_API::callGetDatastreamContentsField($this->pid, 'FezACML'.$dsID.'.xml', array('image_copyright', 'image_watermark'));
			if (isset($xdis_array['image_copyright'][0])) {
				$this->image_copyright[$dsID] = $xdis_array['image_copyright'][0];
			} 
			if (isset($xdis_array['image_watermark'][0])) {
				$this->image_watermark[$dsID] = $xdis_array['image_watermark'][0];
			} 
		}
    }

    /**
     * getAuth
     * Retrieve the authroisation groups allowed for this record with the current user.
	 * 
     * @access  public
     * @return  void
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
	 * 
	 * @param  array $roles The allowed roles to access the object
	 * @param  $redirect
     * @access  public
     * @return  void	 
     */
    function checkAuth($roles, $redirect=true) {
        global $HTTP_SERVER_VARS;
        $this->getAuth();
        $ret_url = $HTTP_SERVER_VARS['PHP_SELF'];
        if (!empty($HTTP_SERVER_VARS['QUERY_STRING'])) {
            $ret_url .= "?".$HTTP_SERVER_VARS['QUERY_STRING'];
        }
		return Auth::checkAuthorisation($this->pid, "", $roles, 
                    $ret_url, $this->auth_groups, $redirect); 
    }
    
    /**
     * canView 
     * Find out if the current user can view this record
	 * 
     * @access  public
	 * @param  $redirect
     * @return  void	 	 
     */
    function canView($redirect=true) {
        if ($this->getPublishedStatus() == 2) {
            return $this->checkAuth($this->viewer_roles, $redirect);
        } else {
            return $this->canEdit($redirect);
        }
    }
    
    /**
     * canEdit
     * Find out if the current user can edit this record
	 * 
     * @access  public
	 * @param  $redirect
     * @return  void	
     */
    function canEdit($redirect=false) {
        return $this->checkAuth($this->editor_roles, $redirect);
    }

    /**
     * canCreate
     * Find out if the current user can create this record
	 * 
     * @access  public
	 * @param  $redirect
     * @return  void	
     */
    function canCreate($redirect=false) {
        return $this->checkAuth($this->creator_roles, $redirect);
    }

    function getPublishedStatus($astext = false)
    {
        $this->getDetails();
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!sta_id'); 
        $status = $this->details[$xsdmf_id];
        if (!$astext) {
            return $status;
        } else {
            return $this->status_array[$status];
        }
    }


    /**
     * setStatusID
     * Used to assocaiate a display for this record
     *
     * @access  public
     * @param  integer $sta_id The new Status ID of the object
     * @return  void    
     */
    function setStatusId($sta_id)
    {
        $this->setFezMD_Datastream('sta_id', $sta_id);
        $this->getDisplay();
        $this->display->getXSD_HTML_Match(); 
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!sta_id'); 
        Record::removeIndexRecordByXSDMF_ID($this->pid, $xsdmf_id);
        Record::insertIndexMatchingField($this->pid, '', $xsdmf_id, "varchar", $sta_id);
        return 1;
    }

    /**
     * setFezMD_Datastream
     * Used to associate a display for this record
     *
     * @access  public
     * @param  $key
     * @param  $value     
     * @return  void    
     */
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
        //Error_handler::logError($newXML,__FILE__,__LINE__);
        if ($newXML != "") {
            Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", false);
        }
    }

    /**
     * getDisplay
     * Get a display object for this record
     *
     * @access  public
     * @return  array $this->details The display of the object, or null
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
    
    function getDocumentType()
    {
        $this->getDisplay();
        return $this->display->getTitle();
    }

    /**
     * getDetails
     * Users a more object oriented approach with the goal of storing query results so that we don't need to make 
     * so many queries to view a record.
     *
     * @access  public
     * @return  array $this->details The details of the object
     */
    function getDetails()
    {
        if (is_null($this->details)) {
            // Get the Datastreams.
            $this->getDisplay();
            if ($this->display) {
                $this->details = $this->display->getXSDMF_Values($this->pid);
            }
        }
        return $this->details;
    }

    /**
     * getTitle
     * Get the dc:title for the record
     *
     * @access  public
     * @return  array $this->details[$xsdmf_id] The Dublin Core title of the object
     */
    function getTitle()
    {
        $this->getDetails();
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!dc:title'); 
        return $this->details[$xsdmf_id];
    }

    /**
     * getDCType
     * Get the dc:type for the record
     *
     * @access  public
     * @return  array $this->details[$xsdmf_id] The Dublin Core type of the object
     */
    function getDCType()
    {
        $this->getDetails();
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!dc:type'); 
        return $this->details[$xsdmf_id];
    }

    function getDetailsByXSDMF_element($xsdmf_element)
    {
        $this->getDetails();
        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID($xsdmf_element); 
        return @$this->details[$xsdmf_id];
    }

    /**
     * isCollection
     * Is the record a Collection
     *
     * @access  public
     * @return  boolean
     */
    function isCollection()
    {
        return ($this->getDCType() == 'Fez_Collection') ? true : false;
    }

    /**
     * isCommunity
     * Is the record a Community
     *
     * @access  public
     * @return  boolean
     */
    function isCommunity()
    {
        return ($this->getDCType() == 'Fez_Community') ? true : false;
    }


    /**
      function getParents() 	      * getParents
     * Get the parent pids of an object
     *
     * @access  public
     * @return  array list of parents
     */
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

    function getWorkflowsByTriggerAndRET_ID($trigger, $ret_id, $strict=false)
    {
        $this->getParents();
        $triggers = WorkflowTrigger::getListByTriggerAndRET_ID($this->pid, $trigger, $ret_id, $strict);
        foreach ($this->record_parents as $ppid) {
            $triggers = array_merge($triggers, 
                    WorkflowTrigger::getListByTriggerAndRET_ID($ppid, $trigger, $ret_id, $strict));
        } 
        // get defaults
        $triggers = array_merge($triggers, 
                WorkflowTrigger::getListByTriggerAndRET_ID(-1, $trigger, $ret_id, $strict));
        return $triggers;
    }

    function getFilteredWorkflows($options)
    {
        $this->getParents();
        $triggers = WorkflowTrigger::getFilteredList($this->pid, $options);
        foreach ($this->record_parents as $ppid) {
            $triggers = array_merge($triggers, 
                    WorkflowTrigger::getFilteredList($ppid, $options));
        }
        // get defaults
        $triggers = array_merge($triggers, 
                WorkflowTrigger::getFilteredList(-1, $options));
        return $triggers;
    }


    function getChildrenPids()
    {
        $dbtp = APP_DEFAULT_DB . "." . APP_TABLE_PREFIX;
        $stmt = "SELECT rmf_rec_pid 
            FROM {$dbtp}record_matching_field
            INNER JOIN {$dbtp}xsd_display_matchfields 
            ON rmf_xsdmf_id=xsdmf_id AND rmf_varchar='{$this->pid}'
            INNER JOIN {$dbtp}search_key on xsdmf_sek_id=sek_id AND sek_title='isMemberOf'
             ";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        }
        return $res;
    }

    function export()
    {
        return Fedora_API::export($this->pid);
    }

    function getObjectXML()
    {
        return Fedora_API::getObjectXMLByPID($this->pid);
    }

    function getDatastreams()
    {
        return Fedora_API::callGetDatastreams($this->pid);
    }
    function checkExists()
    {
        return Fedora_API::objectExists($this->pid);
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
		if (is_numeric(trim(@$xdis_array['file_downloads'][0]))) {
			$this->file_downloads = trim($xdis_array['file_downloads'][0]);
		} else {
			$this->file_downloads = 0;
		}
    }
    
    /**
     * updateAdminDatastream
     * Used to assocaiate a display for this record
	 * 
     * @access  public
	 * @param  integer $xdis_id The XSD Display ID of the object
     * @return  void	
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
		if ($newXML != "") {
			Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", true);
			$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement("!xdis_id", 15);
			Record::removeIndexRecordByXSDMF_ID($this->pid, $xsdmf_id);
			Record::insertIndexMatchingField($this->pid, '', $xsdmf_id, "varchar", $this->xdis_id);
		}
    }
   /**
     * Method used to increment the file download counter of a specific Record.
     *
     * @access  public
     * @return  void
     */
    function incrementFileDownloads() {
		$xdis_array = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD');
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
		if ($newXML != "") {
			Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", true);
			$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement("!file_downloads", 15);
			Record::removeIndexRecordByXSDMF_ID($this->pid, $xsdmf_id);
			Record::insertIndexMatchingField($this->pid, '', $xsdmf_id, "int", $this->file_downloads);

	    }
    }

    /**
     * fedoraInsertUpdate 
     * Process a submitted record insert or update form
     *
     * @access  public
     * @return  void
     */
    function fedoraInsertUpdate($exclude_list=array(), $specify_list=array())
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
			$this->updated_date = $this->created_date;
			$existingDatastreams = array();
			$file_downloads = 0;
        } else {
			$existingDatastreams = Fedora_API::callGetDatastreams($this->pid);
			$this->getObjectDates();
			if (empty($this->created_date)) {
				$this->created_date = date("Y-m-d H:i:s");
			}
			$this->updated_date = date("Y-m-d H:i:s");
			if (!is_numeric($this->file_downloads)) {
				$this->getFileDownloadsCount();
			} 
			$file_downloads = $this->file_downloads;
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


		$xmlObj = Foxml::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "", $indexArray, $file_downloads, $this->created_date, $this->updated_date);

		$xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">";
		$datastreamTitles = $display->getDatastreamTitles($exclude_list, $specify_list); 
        Record::insertXML($pid, compact('datastreamTitles', 'exclude_list', 'specify_list', 'xmlObj', 'indexArray', 'existingDatastreams', 'xdis_id'), $ingestObject);
		return $pid;
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

    function regenerateImages()
    {
        $pid = $this->pid;

        // get a list of datastreams from the object
        $ds = Fedora_API::callGetDatastreams($pid);
        
        // ingest the datastreams
        foreach ($ds as $dsKey => $dsTitle) {
            $dsIDName = $dsTitle['ID'];
            if ($dsTitle['controlGroup'] == 'M' 
                    && is_numeric(strpos($dsTitle['MIMEType'],"image/")) 
                    && !Misc::hasPrefix($dsIDName, 'preview_')
                    && !Misc::hasPrefix($dsIDName, 'web_')
                    && !Misc::hasPrefix($dsIDName, 'thumbnail_')
               ) 
            {
                // first extract the image and save temporary copy
                $urldata = APP_FEDORA_GET_URL."/".$pid."/".$dsIDName; 
                copy($urldata,APP_TEMP_DIR.$dsIDName); 

                // delete and re-ingest - need to do this because sometimes the object made it
                // into the repository even though it's dsID is illegal.
                Fedora_API::callPurgeDatastream($pid, $dsIDName); 
                $new_dsID = Foxml::makeNCName($dsIDName);
                Fedora_API::getUploadLocationByLocalRef($pid, $new_dsID, APP_TEMP_DIR.$dsIDName, $dsFilename, 
                        $dsTitle['MIMEType'], "M");


                // preservation metadata
                $presmd_check = Workflow::checkForPresMD($new_dsID);
                if ($presmd_check != false) {
                    // strip directory off the name
                    $pres_dsID = basename($presmd_check);
                    if (Fedora_API::datastreamExists($pid, $pres_dsID)) {
                        $xml = file_get_contents($presmd_check);
                        Fedora_API::callModifyDatastreamByValue($pid, $pres_dsID, "A", 
                                "Preservation Metadata", $xml, "text/xml", true);
                    } else {
                        Fedora_API::getUploadLocationByLocalRef($pid, $pres_dsID, $presmd_check, $presmd_check, 
                                "text/xml", "X");
                    }
                    if (is_file($presmd_check)) {
                        $deleteCommand = APP_DELETE_CMD." ".$presmd_check;
                        exec($deleteCommand);
                    }
                }
                // process it's ingest workflows
                Workflow::processIngestTrigger($pid, $dsIDName, $dsTitle['MIMEType']);
                //clear the managed content file temporarily saved in the APP_TEMP_DIR
                if (is_file(APP_TEMP_DIR.$dsIDName)) {
                    $deleteCommand = APP_DELETE_CMD." ".APP_TEMP_DIR.$dsTitle['ID'];
                    exec($deleteCommand);
                }
            }
        }

		Record::setIndexMatchingFields($pid);

    }
}


// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Record Class');
}
?>
