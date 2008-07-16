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
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.pager.php");
include_once(APP_INC_PATH . "class.object_type.php");
include_once(APP_INC_PATH . "class.workflow.php");
include_once(APP_INC_PATH . "class.status.php");
include_once(APP_INC_PATH . "class.history.php");
include_once(APP_INC_PATH . "class.foxml.php");
include_once(APP_INC_PATH . "class.fezacml.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.foxml.php");
include_once(APP_INC_PATH . "class.auth_rules.php");
include_once(APP_INC_PATH . "class.auth_index.php");
include_once(APP_INC_PATH . "class.xml_helper.php");
include_once(APP_INC_PATH . "class.record_lock.php");
include_once(APP_INC_PATH . "class.fulltext_queue.php");
include_once(APP_INC_PATH . "class.exiftool.php");

define('SK_JOIN',           0);
define('SK_LEFT_JOIN',      1);
define('SK_WHERE',          2);
define('SK_SORT_ORDER',     3);
define('SK_KEY_ID',         4);
define('SK_MAX_COUNT',      5);
define('SK_FULLTEXT_REL',   6);
define('SK_SEARCH_TXT',     7);
define('SK_GROUP_BY',       8);
define('SK_ORDER_BY',       9);


/**
  * Record
  * Static class for accessing record related queries
  * See RecordObject for an object oriented representation of a record.
  */
class Record
{

    const status_undefined = 0;
    const status_unpublished = 1;
    const status_published = 2;

   /**
    * Method used to get the parents of a given record available in the
    * system.
    *
    * @access  public
    * @param   string $pid The persistant identifier
    * @return  array The list
    */
    function getParents($pid, $clearcache=false, $searchKey='isMemberOf')
    {
    	$sek_title = Search_Key::makeSQLTableName($searchKey);
		$stmt = "SELECT ".APP_SQL_CACHE."
					m1.rek_".$sek_title."
				 FROM
					" . APP_TABLE_PREFIX . "record_search_key_".$sek_title." m1
				 WHERE m1.rek_".$sek_title."_pid = '".$pid."'";
		$res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
            return $res;
        }
    }

   /**
    * Method used to get the parents of a given record available in the
    * system.
    *
    * @access  public
    * @param   string $pid The persistant identifier
    * @return  array The list
    */
    function getParentsDetails($pid, $clearcache=false, $searchKey='isMemberOf')
    {
    	$sek_title = Search_Key::makeSQLTableName($searchKey);
		$stmt = "SELECT ".APP_SQL_CACHE."
					r1.*
				 FROM
					" . APP_TABLE_PREFIX . "record_search_key r1 inner join
					" . APP_TABLE_PREFIX . "record_search_key_".$sek_title." m1 on r1.rek_pid = m1.rek_".$sek_title." AND m1.rek_".$sek_title."_pid = '".$pid."'";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
            return $res;
        }
    }

   /**
    * Method used to get the parents of a given record available in the
    * system.
    *
    * @access  public
    * @param   string $pid The persistant identifier
    * @return  array The list
    */
    function getChildrensDetails($pid, $clearcache=false, $searchKey='isMemberOf')
    {
    	$sek_title = Search_Key::makeSQLTableName($searchKey);
		$stmt = "SELECT ".APP_SQL_CACHE."
					r1.*
				 FROM
					" . APP_TABLE_PREFIX . "record_search_key r1 inner join
					" . APP_TABLE_PREFIX . "record_search_key_".$sek_title." m1 on r1.rek_pid = m1.rek_".$sek_title."_pid AND m1.rek_".$sek_title." = '".$pid."'";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
            return $res;
        }
    }

    function getCollectionChildrensDetails($pid, $clearcache=false, $searchKey='isMemberOf')
    {

    	$sek_title = Search_Key::makeSQLTableName($searchKey);

		$stmt = "SELECT ".APP_SQL_CACHE."
					r1.*
				 FROM
					" . APP_TABLE_PREFIX . "record_search_key r1 inner join
					" . APP_TABLE_PREFIX . "record_search_key_".$sek_title." m1 on r1.rek_pid = m1.rek_".$sek_title."_pid AND m1.rek_".$sek_title." = '".$pid."'
				 WHERE r1.rek_object_type = 2";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
            return $res;
        }
    }


	function generateDerivationTree($pid, $derivations, &$dTree, $shownPids=array()) {
			if (!array($derivations)) {
				return;
			}
			$dTree .= "<ul>";
			foreach ($derivations as $devkey => $dev) { // now build HTML of the citation
				if (!in_array($dev['rek_pid'], $shownPids)) {
					if ($dev['pid'] != $pid) {
						$xdis_title = XSD_Display::getTitle($dev['rek_display_type']);
						$dTree .= "<li>";
						$dTree .= "<a href='".APP_RELATIVE_URL."view/".$dev['rek_pid']."'>".$dev['rek_title']."</a> <i>".$xdis_title."</i> (deposited ".Date_API::getFormattedSimpleDate($dev['rek_created_date']).")";
						$dTree .= "</li>";
					} else {
						$dTree .= "<li>";
						$dTree .= "".$dev['rek_title']." <b>(Current Record)</b>";
						$dTree .= "</li>";
					}
					array_push($shownPids, $dev['rek_pid']);
					if (is_array($dev['children'])) {
						Record::generateDerivationTree($pid, $dev['children'], &$dTree, &$shownPids);
					}
				}
			}
			$dTree .= "</ul>";

	}

   /**
    * Method used to get all of the parents of a given record available in the
    * system.
    *
    * @access  public
    * @param   string $pid The persistant identifier
    * @param   string $searchKey The search key - defaults to isMemberOf, but can be isDerivationOf or any other similar setup RELS-EXT element
    * @return  array The list
    */
    function getParentsAll($pid, $searchKey="isMemberOf", $flatTree=true)
    {

		static $returns;

        if (isset($returns[$pid][$searchKey])) {
            return $returns[$pid][$searchKey];
        }

		$res = Record::getParentsDetails($pid, false, $searchKey);
		$recursive_details = array();
		$details = $res;
		foreach ($details as $key => $row) {
			$temp = Record::getParentsDetails($row['rek_pid'], false, $searchKey, $flatTree);
			foreach ($temp as $trow) {
				array_push($recursive_details, $trow);
			}
		}
		foreach ($recursive_details as $rrow) {
			if ($flatTree == true) {
				array_push($details, $rrow);
			} else {
				if (!is_array($recursive_details['children'])) {
					$details['parents'] = array();
				}
				array_push($details['parents'], $rrow);
			}
		}
		$details = array_reverse($details);
		if ($GLOBALS['app_cache']) {
		  $returns[$pid][$searchKey] = $details;
        }
		return $details;
    }

   /**
    * Method used to get all of the children of a given record available in the
    * system.
    *
    * @access  public
    * @param   string $pid The persistant identifier
    * @param   string $searchKey The search key - defaults to isMemberOf, but can be isDerivationOf or any other similar setup RELS-EXT element
    * @return  array The list
    */
    function getChildrenAll($pid, $searchKey="isMemberOf", $flatTree=true)
    {

		static $returns;

        if (isset($returns[$pid][$searchKey])) {
            return $returns[$pid][$searchKey];
        }
        $dbtp =  APP_TABLE_PREFIX;

        $details = Record::getChildrensDetails($pid);
		$recursive_details = array();
		foreach ($details as $key => $row) {
			$temp = Record::getChildrensDetails($row['rek_pid'], $searchKey, false);
			foreach ($temp as $trow) {
				if ($flatTree == true) {
					array_push($details, $trow);
				} else {
					if (!is_array($details[$key]['children'])) {
						$details[$key]['children'] = array();
					}
					array_push($details[$key]['children'], $trow);
				}

			}
		}
		$details = array_reverse($details);
		if ($GLOBALS['app_cache']) {
		  $returns[$pid][$searchKey] = $details;
        }
		return $details;
    }


    function getCollectionChildrenAll($pid, $searchKey="isMemberOf", $flatTree=true)
    {

		static $returns;

        if (isset($returns[$pid][$searchKey])) {
            return $returns[$pid][$searchKey];
        }
        $dbtp =  APP_TABLE_PREFIX;

        $details = Record::getCollectionChildrensDetails($pid);
		$recursive_details = array();
		foreach ($details as $key => $row) {
			$temp = Record::getCollectionChildrensDetails($row['rek_pid'], false, $searchKey);
			foreach ($temp as $trow) {
				if ($flatTree == true) {
					array_push($details, $trow);
				} else {
					if (!is_array($details[$key]['children'])) {
						$details[$key]['children'] = array();
					}
					array_push($details[$key]['children'], $trow);
				}

			}
		}
		$details = array_reverse($details);
		if ($GLOBALS['app_cache']) {
		  $returns[$pid][$searchKey] = $details;
        }
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
        list($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema) = $display->getXsdAsReferencedArray();
		$indexArray = array();
		$header .= "<".$xsd_element_prefix.$xsd_top_element_name." ";
		$header .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid);
		$header .= ">\n";
		$xmlObj = Foxml::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "", $indexArray, '', '', '', '', '', '');
        $xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">\n";
		$xmlObj = $header . $xmlObj;
		$FezACML_dsID = FezACML::getFezACMLDSName($dsID);		
		if (Fedora_API::datastreamExists($pid, $FezACML_dsID)) {
			Fedora_API::callModifyDatastreamByValue($pid, $FezACML_dsID, "A", "FezACML security for datastream - ".$dsID,
					$xmlObj, "text/xml", "true");
		} else {
			Fedora_API::getUploadLocation($pid, $FezACML_dsID, $xmlObj, "FezACML security for datastream - ".$dsID,
					"text/xml", "X");
		}
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
     * Method used to add a new Record using the normal report form.
     *
     * @access  public
     * @return  integer The new Record ID
     */
    function insert()
    {
        $record = new RecordObject();
        $ret = $record->fedoraInsertUpdate();
        return $ret;
    }

    /**
     * Method used to remove an entry in the Fez Index.
     *
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @param   string $dsID The ID of the datastream (optional)
     * @param   string $dsDelete A flag to check if th e datastream_id should be kept
     * @return  void
     */
    function removeIndexRecord($pid) {
        if (empty($pid)) {
            return -1;
        }
        
		// get list of the Related 1-M search keys, delete those first, then delete the 1-1 core table entries
		$sekDet = Search_Key::getList();
		foreach ($sekDet as $sval) {
			if ($sval['sek_relationship'] == 1) { // if is a 1-M needs its own delete sql, otherwise if a 0 (1-1) the core delete will do it
				$sekTable = Search_Key::makeSQLTableName($sval['sek_title']);
		        $stmt = "DELETE FROM
		                    " . APP_TABLE_PREFIX . "record_search_key_".$sekTable."
						 WHERE rek_".$sekTable."_pid = '" . $pid . "'";
				$res = $GLOBALS["db_api"]->dbh->query($stmt);
		        if (PEAR::isError($res)) {
		            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
		        }
			}
		}

        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "record_search_key
				 WHERE rek_pid = '" . $pid . "'";
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        }
        
        //
        // KJ: remove from fulltext index
        //
        if ( APP_SOLR_INDEXER == "ON" ) {
        	Logger::debug("Record::removeIndexRecord() REMOVING ".$pid." FROM QUEUE");
        	FulltextQueue::singleton()->remove($pid);
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
		$sekDet = Search_Key::getDetailsByXSDMF_ID($xsdmf_id);
		
		// if couldnt find  a search key, 
		// we won't be able to remove this from the index 
		if (!isset($sekDet['sek_id'])) {
			return -1;
		}

        if ($sekDet['sek_relationship'] == 1) {
			$sekTableName = "_".$sekDet['sek_title_db'];
		} else {
			$sekTableName = "";
		}

		/* 
		 * Should only be neccessary to delete in this function for non-core things, 
		 * as they can just be updated on the insert. 
		 * If a full delete the other main function would be being used so this should be safe.
		 */
		if ($sekDet['sek_relationship'] == 1) { 
	        $stmt = "DELETE FROM
	                    " . APP_TABLE_PREFIX . "record_search_key".$sekTableName."
					 WHERE rek".$sekTableName."_pid = '" . $pid . "' and rek_".$sekDet['sek_title_db']."_xsdmf_id=".$xsdmf_id;
	        $res = $GLOBALS["db_api"]->dbh->query($stmt);
	        if (PEAR::isError($res)) {
	            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
	            return -1;
	        }
		}
		
	    return $pid;
    }

    /**
     * Method used to insert an entry in the Fez Index.
     *
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @param   string $dsID The ID of the datastream (optional)
     * @param   integer $xsdmf_id The XSD Matching Field ID
     * @param   string $value The value of the index to be saved
     * @return  string The $pid if successful, otherwise -1
     */
    function insertIndexMatchingField($pid, $dsID='', $xsdmf_id, $value)
    {
		$sekDet = Search_Key::getDetailsByXSDMF_ID($xsdmf_id);
		$data_type = $sekDet['sek_data_type'];

		$cardinality_extra_column = "";
		$cardinality_extra_value = "";

        // MySQL doesn't always handle date string conversions so convert to MySQL style date manually
        if ($data_type == 'date') {
            if (is_numeric($value) && strlen($value) == 4) {
                // It appears we've just been fed a year. We'll pad this, so it can be added to the index.
                $value = $value . "-01-01 00:00:00";
            } elseif (strlen($value) == 7) {
                // YYYY-MM. We could arguably write some better string inspection stuff here, but this will do for now.
                $value = $value . "-01 00:00:00";
            } else {
                // Looks like a regular fully-formed date.
				$value = strtotime($value);
                $date = new Date($value);
                $value = $date->format('%Y-%m-%d %T');
            }
        }

		if ($sekDet['sek_id'] == "") { //if couldnt find  a search key, we won't insert this into the index 
			return -1;
		}
		
        /* rek_varchar updates featuring extra-long strings cause the query below to die. We'll truncate
           the field for now, but this obviously needs to be done a little better in the future. */
        if ($sekDet['sek_data_type'] == 'varchar') {
            $value = substr($value, 0, 254);        // Only use the left-most 255 chars
        }
        
        
        if($sekDet['sek_data_type'] == 'varchar' || $sekDet['sek_data_type'] == 'text') {
            $value = preg_replace('/\n/', ' ', $value);
        }
        
        if ($sekDet['sek_relationship'] == 1) {
			$sekTableName = "_".$sekDet['sek_title_db'];
			if ($sekDet['sek_cardinality'] == 1) {
				$cardinality_extra_column = ", rek".$sekTableName."_order";
				$next_order = Record::getSearchKeyIndexNextOrder($pid, $sekDet['sek_title_db']);
				$cardinality_extra_value = ", ".$next_order;
			}
		} else {
			$sekTableName = "";
		}
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "record_search_key".$sekTableName."
                 (
				 	rek".$sekTableName."_pid,
                    rek_".$sekDet['sek_title_db']."_xsdmf_id,";
		$stmt .= "
			rek_".$sekDet['sek_title_db'].$cardinality_extra_column."
		 ) VALUES (
			'" . $pid . "',
			" . $xsdmf_id . ",";
		if ($sekDet['sek_data_type'] != 'int') { //quote and escape varchar/date/text values
        	$value = "'".Misc::escapeString(trim($value)) . "'";
		}
		$stmt .= $value . $cardinality_extra_value . ")";
		
		// on duplicate key only works with mysql, 
		// but will save time over the else statement below.. maybe
		if (APP_SQL_DBTYPE == "mysql") { 
			$stmt .= " ON DUPLICATE KEY UPDATE rek_".$sekDet['sek_title_db']."_xsdmf_id = ".$xsdmf_id.", rek_".$sekDet['sek_title_db']." = ".$value;
		} else { // this will work with postgresql, might be better to do seperate queries for general dbs if the pgsql way is not ansi
	        if ($sekDet['sek_relationship'] == 0) { //only check for dupes on core 1-1 table inserts as the others could have the same value legitimatly (eg two j smith author strings)
				$stmt = "IF EXISTS( SELECT * FROM " . APP_TABLE_PREFIX . "record_search_key".$sekTableName."
				  WHERE rek_pid = '".$pid."' )
				  UPDATE " . APP_TABLE_PREFIX . "record_search_key".$sekTableName."
				  SET rek_".$sekDet['sek_title_db']."_xsdmf_id = ".$xsdmf_id.", rek_".$sekDet['sek_title_db']." = ".$value."
				  WHERE rek_pid = '".$pid."'
				ELSE
				  INSERT INTO " . APP_TABLE_PREFIX . "record_search_key".$sekTableName."
				  (rek".$sekTableName."_pid, rek_".$sekDet['sek_title_db']."_xsdmf_id, rek_".$sekDet['sek_title_db'].")
				 VALUES
				  (".$pid.", ".$xsdmf_id.", ".$value.")";
			} else {
				$stmt = "INSERT INTO " . APP_TABLE_PREFIX . "record_search_key".$sekTableName."
				  (rek".$sekTableName."_pid, rek_".$sekDet['sek_title_db']."_xsdmf_id, rek_".$sekDet['sek_title_db'].$cardinality_extra_column.")
				 VALUES
				  (".$pid.", ".$xsdmf_id.", ".$value.$cardinality_extra_value.")";
			}
			
		}
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            
            //
	        // KJ: update fulltext index
			//
			if (APP_SOLR_INDEXER == "ON") {
        		Logger::debug("Record::insertMatchingField() ADDING ".$pid." TO QUEUE");
				FulltextQueue::singleton()->add($pid);
	        }
            
            return $pid;
        }
    }
    
    function updateSearchKeys($pid, $sekData) {
    	
    	$ret = true;
    	
    	/*
    	 *  Update 1-to-1 search keys
    	 */
    	$stmt[] = 'rek_pid';
    	$valuesIns[] = "'".$pid."'";
    	foreach ($sekData[0] as $sek_column => $sek_value) {
            $stmt[] = "rek_{$sek_column}, rek_{$sek_column}_xsdmf_id";
            
            if($sek_value['xsdmf_value'] == 'NULL') {
            	$xsdmf_value = $sek_value['xsdmf_value'];
            } else {
            	$xsdmf_value = "'".Misc::escapeString(trim($sek_value['xsdmf_value'])) . "'";
            }
            
            $valuesIns[] = "$xsdmf_value, {$sek_value['xsdmf_id']}";
            $valuesUpd[] = "rek_{$sek_column} = $xsdmf_value, rek_{$sek_column}_xsdmf_id = {$sek_value['xsdmf_id']}";
    	}
    	
    	$stmtIns = "INSERT INTO " . APP_TABLE_PREFIX . "record_search_key (" . implode(",", $stmt) . ") ";
    	$stmtIns .= " VALUES (" . implode(",", $valuesIns) . ")";
    	
    	if (APP_SQL_DBTYPE == "mysql") { 
    		$stmt = $stmtIns ." ON DUPLICATE KEY UPDATE " . implode(",", $valuesUpd);
    	} else {
    		
            $stmt = "IF EXISTS( SELECT * FROM " . APP_TABLE_PREFIX . "record_search_key WHERE rek_pid = '".$pid."' )
                        UPDATE " . APP_TABLE_PREFIX . "record_search_key
                        SET " . implode(",", $valuesUpd) . "
                        WHERE rek_pid = '".$pid."'
                     ELSE ".$stmtIns;
    	}
  		
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $ret = false;
        }

    	/*
         *  Update 1-to-Many search keys
         */
        foreach ($sekData[1] as $sek_table => $sek_value) {
        	
        	$stmt = "";
        	if(!empty($sek_value['xsdmf_value'])) {
	        	
        		$cardinalityCol = "";
	        	if(is_array($sek_value['xsdmf_value'])) {
	        		$cardinalityCol = ",rek_".$sek_table."_order";
	        	}
	        	
	        	$stmt = "INSERT INTO " . APP_TABLE_PREFIX . "record_search_key_".$sek_table."
	                        (rek_".$sek_table."_pid, rek_".$sek_table."_xsdmf_id, rek_".$sek_table . $cardinalityCol.")
	                    VALUES ";
	                    
	        	if(is_array($sek_value['xsdmf_value'])) {
	        		
		            $cardinalityVal = 1;
		            foreach ($sek_value['xsdmf_value'] as $value ) {
                        $stmtVars[] = "('$pid',{$sek_value['xsdmf_id']},'".Misc::escapeString($value)."',$cardinalityVal)";
                        
                        $cardinalityVal++;
		            }
		            $stmt .= implode(",", $stmtVars);
		            unset($stmtVars);
		            
	        	} else {
	        		
	        		$stmt .= "('$pid',{$sek_value['xsdmf_id']},'".Misc::escapeString($sek_value['xsdmf_value']). "')";
	        		
	        	}
	        	
                $res = $GLOBALS["db_api"]->dbh->query($stmt);
                if (PEAR::isError($res)) {
                    Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                    $ret = false;
                }
        	}
        }
        
        //
        // KJ: update fulltext index
        //
        if (APP_SOLR_INDEXER == "ON") {
            Logger::debug("Record::insertMatchingField() ADDING ".$pid." TO QUEUE");
            FulltextQueue::singleton()->add($pid);
        }
        
        return $ret;
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
        $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
        $stmt = "SELECT ".APP_SQL_CACHE."  * FROM
        ".$dbtp."record_matching_field r1
        inner join ".$dbtp."xsd_display_matchfields x1 on r1.rek_xsdmf_id = x1.xsdmf_id and rek_pid = '".$pid."' and rek_dsid = '".$dsID."'
        inner join ".$dbtp."xsd_display d1 on x1.xsdmf_xdis_id = d1.xdis_id
        inner join ".$dbtp."xsd x2 on x2.xsd_id = d1.xdis_xsd_id and x2.xsd_title = '".$xsd_title."'
        left join ".$dbtp."xsd_loop_subelement s1 on s1.xsdsel_id = x1.xsdmf_xsdsel_id";
//      echo $stmt;
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
    function setIndexMatchingFields($pid)
    {
        $record = new RecordObject($pid);
        $record->setIndexMatchingFields();
        
        AuthIndex::setIndexAuth($pid); //set the security index
    }

    function setIndexMatchingFieldsRecurse($pid, $bgp=null, $fteindex = true)
    {
        if (!empty($bgp)) {
            $bgp->setStatus("Processing ".$pid);
            $bgp->incrementProgress();
        }
        $record = new RecordObject($pid);
        $record->setIndexMatchingFields();
        if (!$record->isCommunity() && !$record->isCollection() && $fteindex) {
            FulltextIndex::indexPid($pid);
        }
        // recurse children
        // NOTE: this only finds objects that are already indexed correctly at least when it comes to
        //          memberOf
        $children = $record->getChildrenPids();
        foreach ($children as $child_pid) {
            Record::setIndexMatchingFieldsRecurse($child_pid, $bgp, $fteindex);
        }
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
		$ds_pattern = false;
		if ($dsID != "") {
	        if (isset($acml_cache['ds'][$dsID][$pid])) {
				return $acml_cache['ds'][$dsID][$pid];
			} else {
				$dsIDCore = preg_replace("/(web_|preview_|thumb_|stream_)/", "", $dsID);
				$dsIDCore = substr($dsIDCore, 0, strrpos($dsIDCore, "."));
				$ds_pattern = '/^FezACML_'.$dsIDCore.'(.*)\.xml$/';
//				$ds_search = 'FezACML_'.$dsID.'.xml';
			}
		} else {
			$ds_search = 'FezACML';
	        if (isset($acml_cache['pid'][$pid])) {
	            return $acml_cache['pid'][$pid];
	        }
		}
        $dsExists = Fedora_API::datastreamExists($pid, $ds_search, true, $ds_pattern);
        if ($dsExists != false) {
			if ($ds_pattern != false) {
				$DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $dsExists);
			} else {
				$DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $ds_search);
			}
			$xmlACML = @$DSResultArray['stream'];
			$xmldoc= new DomDocument();
			$xmldoc->preserveWhiteSpace = false;
			$xmldoc->loadXML($xmlACML);
			if ($GLOBALS['app_cache']) {
			  if ($dsID != "") {
			  	$acml_cache['ds'][$dsID][$pid] = $xmldoc;
			  } else {
				$acml_cache['pid'][$pid] = $xmldoc;
			  }
            }
			return $xmldoc;
		} else {
		  if ($GLOBALS['app_cache']) {
			if ($dsID != "") {
				$acml_cache['ds'][$dsID][$pid] = false;
			} else {
				$acml_cache['pid'][$pid] = false;
			}
			return false;
          }
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
     * Get details about a pid(s)
     *
     * @param string/array $pid  the pid(s) to get details about
     *
     * @return array  the pid and their details ie. title, description etc
     * @access public
     */
    function getDetailsLite($pid)
    {
        if( $pid == '' )
            return array();
        
        $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
        $order = '';
        
        if( is_array($pid)) {
            $where = "rek_pid IN ('" . implode("','", $pid) . "')";
            $order = "rek_created_date";
        } else {
            $where = "rek_pid = '$pid'";
        }
        
        $sql =  "SELECT * " .
                "FROM {$dbtp}record_search_key " .
                "WHERE $where";
                
        if( $order ) {
            $sql .= " ORDER BY $order DESC";
        }
        
        $res = $GLOBALS["db_api"]->dbh->getAll($sql, DB_FETCHMODE_ASSOC);
        
	    if (PEAR::isError($res)) {
	        Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return false;
	    } else {
	        $usr_id = Auth::getUserID();
	        Record::getAuthWorkflowsByPIDS($res, $usr_id);
	        return $res;
	    }
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
    
    
    function getRecentRecords()
    {
        $sql =  'SELECT * ' . 
                'FROM ' . APP_TABLE_PREFIX . 'recently_added_items ';
                
        $res = $GLOBALS["db_api"]->dbh->getAll($sql, DB_FETCHMODE_FLIPPED);
        
	    if (PEAR::isError($res)) {
	        Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
	    } else {
	        return $res;
	    }
    }
    
    function getRecentDLRecords()
    {
        $sql =  'SELECT * ' . 
                'FROM ' . APP_TABLE_PREFIX . 'recently_downloaded_items '.
                'ORDER BY rdi_downloads DESC ';
                
        $res = $GLOBALS["db_api"]->dbh->getAll($sql, DB_FETCHMODE_FLIPPED); //DB_FETCHMODE_ASSOC
        
	    if (PEAR::isError($res)) {
	        Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
	    } else {
	        return $res;
	    }
    }
    
    
    /**
     * .
     *
     * @access  public
     * @param string $options The search parameters
     * @return array $res2 The index details of records associated with the search params
     */
	function getListing($options, $approved_roles=array(9,10), $current_page=0,$page_rows="ALL", $sort_by="Title", $getSimple=false, $citationCache=false, $filter=array(), $operator='AND') {
        if (APP_SOLR_SWITCH == "ON" ) {			
			return Record::getSearchListing($options, $approved_roles, $current_page,$page_rows, $sort_by, $getSimple, $citationCache, $filter, $operator);
		} else {			
			$options = array_merge($options, $filter);
		}
	
        if ($page_rows == "ALL") {
            $page_rows = 9999999;
        }
        $start = $current_page * $page_rows;
		$dbtp =  APP_TABLE_PREFIX; // Database and table prefix
        $current_row = $current_page * $page_rows;
		
		if( Search_Key::getMaxID() == 0 )
		{
		    Error_Handler::logError('No Search Keys', __FILE__, __LINE__);
		    return array();
		}
		
		// make sure the sort by is setup well
		if (!is_numeric(strpos($sort_by, "searchKey"))) {
			$sort_by_id = Search_Key::getID($sort_by);
			if ($sort_by_id != "") {
				$sort_by = "searchKey".$sort_by_id;
			} else {
				$sort_by_id = Search_Key::getID("Title");
				$sort_by = "searchKey".$sort_by_id;
			}
		}
		
		//echo $sort_by . '<br />';
        $searchKey_join = Record::buildSearchKeyJoins($options, $sort_by, $operator, $filter);
        
		$authArray = Collection::getAuthIndexStmt($approved_roles, "r1.rek_pid");
		$authStmt = $authArray['authStmt'];

        $stmt = " FROM {$dbtp}record_search_key AS r1 ".
			    $searchKey_join[SK_JOIN].$searchKey_join[SK_LEFT_JOIN].$authStmt." ".
			    $searchKey_join[SK_WHERE];
    
    	if (APP_SQL_DBTYPE == "mysql") { // If the DB is mysql then you can use SQL_NUM_ROWS, even with a limit and get better performance, otherwise you need to do a seperate query to get the total count
    		$total_rows = 1;
    		$stmt =  "SELECT ".APP_SQL_CACHE." SQL_CALC_FOUND_ROWS DISTINCT r1.* ".$searchKey_join[SK_FULLTEXT_REL]." ".$stmt.$searchKey_join[SK_GROUP_BY];
    		$stmt .= " ORDER BY ".$searchKey_join[SK_SORT_ORDER]." r".$searchKey_join[SK_KEY_ID].".rek_pid DESC ";
    
    	} else {
      		$countStmt =  "SELECT ".APP_SQL_CACHE." COUNT(r1.rek_pid) ".$stmt;
    		$total_rows = $GLOBALS["db_api"]->dbh->getOne($countStmt);
    		$stmt =  "SELECT ".APP_SQL_CACHE." r1.* ".$searchKey_join[SK_FULLTEXT_REL]." ".$stmt.$searchKey_join[SK_GROUP_BY];
    	    $stmt .= " ORDER BY ".$searchKey_join[SK_SORT_ORDER]." r".$searchKey_join[SK_KEY_ID].".rek_pid DESC ";
    	}
    	$usr_id = Auth::getUserID();
    	//echo $stmt . "<br />";
		if ($total_rows > 0) {
			$stmt = $GLOBALS["db_api"]->dbh->modifyLimitQuery($stmt, $start, $page_rows);
//			Error_Handler::logError(array($stmt, $res->getDebugInfo()), __FILE__, __LINE__);
			//echo "<pre>".$stmt."</pre>"; //exit;
			$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
			if (PEAR::isError($res)) {
				Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
				$res = array();
			} else { //now add on the other search keys, security roles, workflows, if necessary
				if (APP_SQL_DBTYPE == "mysql") {
					$total_rows = $GLOBALS["db_api"]->dbh->getOne('SELECT FOUND_ROWS()');
				}
                if (count($res) > 0) {
					if ($getSimple == false || empty($getSimple)) {
						if ($citationCache == false) {	
	                    	Record::getSearchKeysByPIDS($res);
						}
	                    Record::identifyThumbnails($res, $citationCache);
	                    Record::getAuthWorkflowsByPIDS($res, $usr_id);
					}
                }
            }
	    } else {
			$res = array();
		}
		
		if ($citationCache == true) {
			$res = Citation::renderIndexCitations($res, 'APA', true, false);
		}

		$thumb_counter = 0;
		if (!empty($res)) {
			// needed for viewer
			$res = Auth::getIndexAuthCascade($res);
			foreach ($res as $key => $rec) {
				if ($res[$key]['thumbnail'][0] != "") {
					$thumb_counter++;
				}
				$res[$key]['isLister'] = true;
			}		
			

		}
		$list = $res;
		if ($total_rows != 0) {
			$thumb_ratio = $thumb_counter / $total_rows;
		} else {
			$thumb_ratio = 0;
		}
		
        $total_pages = intval($total_rows / $page_rows);
        if ($total_rows % $page_rows) {
            $total_pages++;
        }
        $search_info = rtrim($searchKey_join[SK_SEARCH_TXT], ', ');
        if ($searchKey_join[SK_WHERE] == "") {
        	$noOrder = 1;
        } else {
        	$noOrder = 0;
        }
        $next_page = ($current_page >= $total_pages) ? -1 : $current_page + 1;
        $prev_page = ($current_page <= 0) ? -1 : $current_page - 1;
        $last_page = $total_pages - 1;
        $current_last_row = $current_row + count($list);
        if (($current_page - 5) > 0) {
            $start_range = $current_page - 5;
        } else {
            $start_range = 0;
        }
        if (($current_page + 5) >= $last_page) {
            $end_range = $last_page + 1;
        } else {
            $end_range = $current_page + 5;
        }
        $printable_page = $current_page + 1;
        $info = compact('total_rows', 'page_rows', 'current_row','current_last_row','current_page','total_pages',
                'next_page','prev_page','last_page', 'noOrder', 'search_info', 'start_range', 'end_range', 'printable_page', 'thumb_ratio');
        return compact('info','list');
    }

    
    function getListingForCitation($options, $approved_roles, $sort_by="Title", $filter=array(), $operator='AND') {
        
        $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
        $searchKey_join = Record::buildSearchKeyJoins($options, $sort_by, $operator, $filter);
        
		$authArray = Collection::getAuthIndexStmt($approved_roles, "r1.rek_pid");
		$authStmt = $authArray['authStmt'];
		$joinStmt = $authArray['joinStmt'];

		$stmt =  "SELECT DISTINCT r1.* " .
                  "FROM {$dbtp}record_search_key AS r1 ".
            	   $searchKey_join[SK_JOIN].$searchKey_join[SK_LEFT_JOIN].$authStmt." ".
	               $searchKey_join[SK_WHERE];
	    
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		
		if (PEAR::isError($res)) {
			Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			$res = array();
		} else {
            
			if (count($res) > 0) {
				
				$usr_id = Auth::getUserID();
            	Record::getSearchKeysByPIDS($res);
            	Record::getChildCountByPIDS($res, $usr_id);
                    
			}
        }
        
	    return array(
	       'info'  =>  '',
	       'list'  =>  $res,
	    );
    }
    
    /**
     * Extracts and prepares search specific parameters from the $_GET/$_POST request.
     * This performs kind of a parameter validation and packs variables in
     * arrays.
     * 
     * KJ/ETH 2/08
     *
     * @param unknown_type $request
     */
    
	private function extractSearchParameters() {
		// WORD SEARCH
		
		// w(\d+)	keyword number $1, example w1=test
		// wop=[and|or]	global default operator, used if no individual operators were set
		// wf(\d+)	name of field number $1
		
		$wop = Misc::GETorPOST("wop");
		if (!$wop) {
			$wop = "AND";
		}
		// start with search word #1
		$num = 1;
		$params['words'] = array();
		
		while (Misc::GETorPOST("w$num")) {
			$w = Misc::GETorPOST("w$num");
			$wf = Misc::GETorPOST("wf$num");
			if (!$wf || $wf=='ALL') {
				// default: search everything/all fields
				$wf = "ALL";
			} else {
				// map search key id to name
				$wf = strtolower($wf);
				/*
				$id = preg_replace("/searchKey(\d*)/",'$1', $wf);
				if ($id) {
					$wf = Search_key::getTitle($id);
				}
				*/
			}
			$wopn = Misc::GETorPOST("wop$num");
			if (!$wopn) {
				// use default operator
				$wopn = $wop;
			}
			$keyword = array('wf' => $wf, 'w' => $w, 'op' => $wopn);			
			$params['words'][$num] = $keyword;
			
			$num++;
		}
	
		// EXPERT SEARCH
		$q = Misc::GETorPOST('search_keys');
		if ($q) {
			$params['direct'] = array('q' => $q[0]);
		}
		
		
		// FILTERS
		// dates
		// ...
		//var_dump($params);
		return $params;
	}
	
	/**
     * Searches repository for matching documents/collections/communities.
     *
     * @access  public
     * @param string $options The search parameters
     * @return array $res2 The index details of records associated with the search params
     */
	function getSearchListing($options, $approved_roles=array(9,10), $current_page=0, $page_rows="ALL", $sort_by="", $getSimple=false, $citationCache=false, $filter=array(), $operator="AND") {
    	// paging preparation
        if ($page_rows == "ALL") {
            $page_rows = 9999999;
        }
        
        // make sure the sort by is setup well
		if (!is_numeric(strpos($sort_by, "searchKey"))) {
			$sort_by_id = Search_Key::getID($sort_by);
			if ($sort_by_id != "") {
				$sort_by = "searchKey".$sort_by_id;
			} else {
				$sort_by_id = Search_Key::getID("Title");
				$sort_by = "searchKey".$sort_by_id;
			}
		}
		
        $start = $current_page * $page_rows;
        $current_row = $current_page * $page_rows; 
        
		$searchKey_join   = self::buildSearchKeyFilterSolr($options, $sort_by, $operator);
		$filter_join      = self::buildSearchKeyFilterSolr($filter, "", $operator);
		
		$index = new FulltextIndex_Solr();

		$res = $index->searchAdvancedQuery($searchKey_join, $filter_join, $approved_roles, $start, $page_rows);
		$total_rows = $res['total_rows'];
		$facets = $res['facets'];
		$res = $res['docs'];
        
		$usr_id = Auth::getUserID();
        // disable citation caching for the moment - CK commented out forced citation true on 17/6/08, was a Rhys thin
//		$citationCache = true;

		if (count($res) > 0) {
			if ($getSimple == false || empty($getSimple)) {
				if ($citationCache == false) {	
                	Record::getSearchKeysByPIDS($res);
				}
                Record::identifyThumbnails($res, $citationCache);
                Record::getAuthWorkflowsByPIDS($res, $usr_id);
			}
            Record::getChildCountByPIDS($res, $usr_id);
        }
		$thumb_counter = 0; 
		// KJ/ETH: if the object came up to here, it can be listed (Solr filter!)
		if (!empty($res)) {		
			
			// needed for viewer			
			$res = Auth::getIndexAuthCascade($res);
									
			foreach ($res as $key => $rec) {
				if ($res[$key]['thumbnail'][0] != "") {
					$thumb_counter++;
				}
				$res[$key]['isLister'] = true;
			}		
		}
		if ($total_rows != 0) {
			$thumb_ratio = $thumb_counter / $total_rows;
		} else {
			$thumb_ratio = 0;
		}
		// query display...
		$search_info = rtrim($searchKey_join[SK_SEARCH_TXT], ', ');

		$list = $res;
        $total_pages = intval($total_rows / $page_rows);
        if ($total_rows % $page_rows) {
            $total_pages++;
        }

        $noOrder = 0;  // KJ: don't know what this is...

        $next_page = ($current_page >= $total_pages) ? -1 : $current_page + 1;
        $prev_page = ($current_page <= 0) ? -1 : $current_page - 1;
        $last_page = $total_pages - 1;
        $current_last_row = $current_row + count($list);
        if (($current_page - 10) > 0) {
            $start_range = $current_page - 10;
        } else {
            $start_range = 0;
        }
        if (($current_page + 10) >= $last_page) {
            $end_range = $last_page + 1;
        } else {
            $end_range = $current_page + 10;
        }
        $printable_page = $current_page + 1;

        // return result
        $info = compact('total_rows', 'page_rows', 'current_row','current_last_row',
        				'current_page','total_pages','next_page','prev_page','last_page',
        				'noOrder', 'search_info', 'start_range', 'end_range', 'printable_page', 'thumb_ratio');

        return compact('info','list','facets');
    }
    
    function identifyThumbnails(&$result, $citationCache = false) {
	
		if ($citationCache == true) { //need to go and get the left join for file_attachments if simple			
	        $pids = array();
	        for ($i = 0; $i < count($result); $i++) {
	            $pids[] = $result[$i]["rek_pid"];
	        }
			if (count($pids) == 0) {
				return;
		  	}
	        $sek_details = Search_Key::getBasicDetailsByTitle("File Attachment Name");	
			$sek_sql_title = $sek_details['sek_title_db'];
			$res = array();
			$res = Record::getSearchKeyByPIDS($sek_sql_title, $pids);
            $t = array();
            $p = array();
            for ($i = 0; $i < count($res); $i++) {
                if (!is_array($t[$res[$i]["rek_".$sek_sql_title."_pid"]])) {
                    $t[$res[$i]["rek_".$sek_sql_title."_pid"]] = array();
                }
                $t[$res[$i]["rek_".$sek_sql_title."_pid"]][] =  $res[$i]["rek_".$sek_sql_title];
            }
            // now populate the $result variable again
            for ($i = 0; $i < count($result); $i++) {
                if (!is_array($result[$i]["rek_".$sek_sql_title])) {
                    $result[$i]["rek_".$sek_sql_title] = array();
                }
                $result[$i]["rek_".$sek_sql_title] = $t[$result[$i]["rek_pid"]];
            }
		}
		
    	for ($i = 0; $i < count($result); $i++) {
    		for ($x = 0; $x < count($result[$i]['rek_file_attachment_name']); $x++) {
    			if (is_numeric(strpos($result[$i]['rek_file_attachment_name'][$x], "thumbnail_"))) {
					if (!is_array(@$result[$i]['thumbnail'])) {
						$result[$i]['thumbnail'] = array();
					}
					array_push($result[$i]['thumbnail'], $result[$i]['rek_file_attachment_name'][$x]);
					if (APP_EXIFTOOL_SWITCH == 'ON') {
						$exif_details = Exiftool::getDetails($result[$i]['rek_pid'], $result[$i]['rek_file_attachment_name'][$x]);
						if (count($exif_details) != 0) {
							if (!is_array(@$result[$i]['thumbnail_width'])) {
								$result[$i]['thumbnail_width'] = array();
							}
							if (!is_array(@$result[$i]['thumbnail_height'])) {
								$result[$i]['thumbnail_height'] = array();
							}
							array_push($result[$i]['thumbnail_width'], $exif_details['exif_image_width']);
							array_push($result[$i]['thumbnail_height'], $exif_details['exif_image_height']);
						}
					}
    			}
    			if (is_numeric(strpos($result[$i]['rek_file_attachment_name'][$x], "stream_"))) {
					if (!is_array(@$result[$i]['stream'])) {
						$result[$i]['stream'] = array();
					}
					array_push($result[$i]['stream'], $result[$i]['rek_file_attachment_name'][$x]);
    			}
    		}
    	}
    }

    function getSearchKeysByPIDS(&$result)
    {
        $pids = array();
        for ($i = 0; $i < count($result); $i++) {
            $pids[] = $result[$i]["rek_pid"];
        }
		if (count($pids) == 0) {
			return;
	  	}
        $sek_details = Search_Key::getList(false);
        foreach ($sek_details as $sekKey => $sekData) {
            $sek_sql_title = Search_Key::makeSQLTableName($sekData['sek_title']);
            if ($sekData['sek_relationship'] == 0) { //already have the data, just need to do any required lookups for 1-1
                if ($sekData['sek_lookup_function'] != "") {
                    for ($i = 0; $i < count($result); $i++) {
                       if ($result[$i]['rek_'.$sek_sql_title]) {
                           eval('$result[$i]["rek_'.$sek_sql_title.'_lookup"] = '.$sekData['sek_lookup_function'].'('.$result[$i]['rek_'.$sek_sql_title].');');
                       } else {
                           $result[$i]['rek_'.$sek_sql_title.'_lookup'] = "";
                       }
                    }
                }

            } else {
                $res = Record::getSearchKeyByPIDS($sek_sql_title, $pids);
                $t = array();
                $p = array();
                for ($i = 0; $i < count($res); $i++) {
                    if (!is_array($t[$res[$i]["rek_".$sek_sql_title."_pid"]])) {
                        $t[$res[$i]["rek_".$sek_sql_title."_pid"]] = array();
                    }
                	if ($sekData['sek_lookup_function'] != "") {
	                    if (!is_array($p[$res[$i]["rek_".$sek_sql_title."_pid"]])) {
	                        $p[$res[$i]["rek_".$sek_sql_title."_pid"]] = array();
	                    }
						eval('$res[$i]["rek_'.$sek_sql_title.'_lookup"] = '.$sekData['sek_lookup_function'].'('.$res[$i]['rek_'.$sek_sql_title].');');
                    	$p[$res[$i]["rek_".$sek_sql_title."_pid"]][] =  $res[$i]["rek_".$sek_sql_title."_lookup"];
					}
                    $t[$res[$i]["rek_".$sek_sql_title."_pid"]][] =  $res[$i]["rek_".$sek_sql_title];
                }
                // now populate the $result variable again
                for ($i = 0; $i < count($result); $i++) {
                    if (!is_array($result[$i]["rek_".$sek_sql_title])) {
                        $result[$i]["rek_".$sek_sql_title] = array();
                    }
                    $result[$i]["rek_".$sek_sql_title] = $t[$result[$i]["rek_pid"]];
                	if ($sekData['sek_lookup_function'] != "") {
                    	$result[$i]["rek_".$sek_sql_title."_lookup"] = $p[$result[$i]["rek_pid"]];
					}
                }
            }
        }

                //print_r($result);
    }

  function getSearchKeyByPIDS($sek_sql_title, $pids = array()) {
	  if (count($pids) == 0) {
		return array();
	  }
	  $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
      $pids = implode("', '", $pids);
      $stmt = "SELECT
                    rek_" . $sek_sql_title ."_pid,
                    rek_" . $sek_sql_title ."
                 FROM
                    " . $dbtp . "record_search_key_" . $sek_sql_title . "
                 WHERE
                    rek_".$sek_sql_title."_pid IN ('".$pids."')
                 ORDER BY
					rek_" . $sek_sql_title ."_id ASC ";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
            return $res;
        }
  }

  function getParentTitlesByPIDS(&$result) {
      $pids = array();
      for ($i = 0; $i < count($result); $i++) {
      	for ($y = 0; $y < count($result[$i]["rek_ismemberof"]); $y++) {
		  if (!in_array($result[$i]["rek_ismemberof"][$y], $pids)) {
          	$pids[] = $result[$i]["rek_ismemberof"][$y];
		  }
		}
      }
	  if (count($pids) == 0) {
		return array();
	  }
	  $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
      $pids = implode("', '", $pids);
      $stmt = "SELECT
					rek_pid,
                    rek_title
                 FROM
                    " . $dbtp . "record_search_key
                 WHERE
                    rek_pid IN ('".$pids."')";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
            // now populate the $result variable again
			$sek_sql_title = "ismemberof_title";
	      for ($i = 0; $i < count($result); $i++) {
	      	for ($y = 0; $y < count($result[$i]["rek_ismemberof"]); $y++) {
                if (!is_array($result[$i]["rek_".$sek_sql_title])) {
                    $result[$i]["rek_".$sek_sql_title] = array();
                }
                $result[$i]["rek_".$sek_sql_title][$y] = $res[$result[$i]["rek_ismemberof"][$y]];
            }
		  } 
          return $result;
        }
  }

	function getAuthWorkflowsByPIDS(&$result, $usr_id) {
  		
		for ($i = 0; $i < count($result); $i++) {
           $pids[] = $result[$i]["rek_pid"];
		}
		
		if (count($pids) == 0) {
			return;
		}
		$dbtp =  APP_TABLE_PREFIX; // Database and table prefix
		$pids = implode("', '", $pids);
      
		if (!Auth::isAdministrator() && (is_numeric($usr_id))) {
      
			// TODO: OR rek_assigned_group_id IN (2,3))
	      	$stmt = "SELECT rek_pid, authi_pid, authi_role, wfl_id, wfl_title, wft_id, wft_icon
	                 FROM ".$dbtp."record_search_key 
	                 INNER JOIN ".$dbtp."auth_index2 ON rek_pid = authi_pid 
	                 INNER JOIN ".$dbtp."auth_rule_group_users ON authi_arg_id = argu_arg_id and argu_usr_id = ".$usr_id." 
	                 LEFT JOIN ".$dbtp."record_search_key_assigned_user_id ON rek_pid = rek_assigned_user_id_pid 
	                 LEFT JOIN ".$dbtp."workflow_roles ON authi_role = wfr_aro_id OR (authi_role = 7 AND wfr_aro_id = 8 AND rek_status != 2 AND (rek_assigned_user_id IN (".$usr_id.") ) )
	                 LEFT JOIN ".$dbtp."workflow ON wfr_wfl_id = wfl_id 
	                 LEFT JOIN ".$dbtp."workflow_trigger ON wfl_id = wft_wfl_id 
	                      							AND (wft_pid = -1 or wft_pid = authi_pid)
	                    							AND (wft_xdis_id = -1 or wft_xdis_id = rek_display_type) 
	                    							AND (wft_ret_id = 0 or wft_ret_id = rek_object_type)
	 				WHERE rek_pid IN ('".$pids."') and (wft_options = 1 or wfl_id IS NULL)
	                ORDER BY wft_order ASC ";
                               
		} elseif (!is_numeric($usr_id)) { //no workflows for a non-logged in person - but may get lister and/or viewer roles
      
			$stmt = "SELECT rek_pid, authi_pid, authi_role
                 	 FROM {$dbtp}record_search_key 
                     INNER JOIN ".$dbtp."auth_index2 ON rek_pid = authi_pid
				     INNER JOIN ".$dbtp."auth_rule_group_rules on authi_arg_id = argr_arg_id
                     INNER JOIN ".$dbtp."auth_rules ON ar_rule='public_list' AND ar_value='1' AND argr_ar_id=ar_id
      				 WHERE rek_pid IN ('".$pids."')";
		} else {
			
			$stmt = "SELECT DISTINCT rek_pid, authi_arg_id, wfl_id, wfl_title, wft_id, wft_icon ".
             		"FROM {$dbtp}record_search_key " .
					"LEFT JOIN ".$dbtp."auth_index2 on authi_pid = rek_pid " .
					"INNER JOIN ".$dbtp."workflow_trigger ON wft_options = 1 " .
										"AND (wft_pid = -1 or wft_pid = rek_pid) " .
                     					"AND (wft_xdis_id = -1 or wft_xdis_id = rek_display_type) " .
                     					"AND (wft_ret_id = 0 or wft_ret_id = rek_object_type) " .
                    "INNER JOIN ".$dbtp."workflow on wfl_id = wft_wfl_id " .
 					"WHERE rek_pid IN ('".$pids."') " .
                  	"ORDER BY wft_order ASC";
		}
		
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
        	
        	if(count($res) == 0) {
         		return;
        	}
        	
        	$tmp = array();
        	for ($i = 0; $i < count($res); $i++) {
        		
                if (@!in_array($res[$i]["authi_role"], $tmp[$res[$i]["rek_pid"]]["authi_role"])) {
                    $tmp[$res[$i]["rek_pid"]]["authi_role"][] = $res[$i]["authi_role"];
                }
                
                if (@!in_array($res[$i]["wfl_id"], $tmp[$res[$i]["rek_pid"]]["wfl_id"])) {
                    $tmp[$res[$i]["rek_pid"]]["wfl_id"][] = $res[$i]["wfl_id"];
                }
                
                if (@!in_array($res[$i]["wft_id"], $tmp[$res[$i]["rek_pid"]]["wft_id"])) {
                    $tmp[$res[$i]["rek_pid"]]["wft_id"][] = $res[$i]["wft_id"];
                }
                
                if (@!in_array($res[$i]["wfl_title"], $tmp[$res[$i]["rek_pid"]]["wfl_title"])) {
                    $tmp[$res[$i]["rek_pid"]]["wfl_title"][] = $res[$i]["wfl_title"];
                }
	            
                if (@!in_array($res[$i]["wft_icon"], $tmp[$res[$i]["rek_pid"]]["wft_icon"])) {
                    $tmp[$res[$i]["rek_pid"]]["wft_icon"][] = $res[$i]["wft_icon"];
                }
        	}
        	
        	for ($i = 0; $i < count($result); $i++) {
        		if($tmp[$result[$i]["rek_pid"]]) {
                	$result[$i] = array_merge($result[$i], $tmp[$result[$i]["rek_pid"]]);
        		}
            }
        }
    }



	function getChildCountByPIDS(&$result, $usr_id) 
	{
        $pids = array();
        for ($i = 0; $i < count($result); $i++) {
            if ($result[$i]["rek_object_type"] != "3") {
                $pids[] = $result[$i]["rek_pid"];
            }
        }
        
        if (count($pids) == 0) {
            return;
        }
        $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
        $pids = implode("', '", $pids);
        $authArray = Collection::getAuthIndexStmt(array("Lister"), "r1.rek_pid");
        $authStmt = $authArray['authStmt'];
        
        $stmt = "SELECT
                    rek_ismemberof, count(rek_ismemberof) as rek_ismemberof_count
                FROM
                    " . $dbtp . "record_search_key_ismemberof as r2 inner join
                    " . $dbtp . "record_search_key as r1 ON rek_pid = rek_ismemberof_pid and rek_status = 2
                    $authStmt
                WHERE 
                    rek_ismemberof IN ('".$pids."')
                GROUP BY 
                    rek_ismemberof ";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
            $t = array();
            for ($i = 0; $i < count($res); $i++) {
                $t[$res[$i]["rek_ismemberof"]] =  $res[$i]["rek_ismemberof_count"];
            }
            
            // now populate the $result variable again
            for ($i = 0; $i < count($result); $i++) {
                $result[$i]["rek_ismemberof_count"] = $t[$result[$i]["rek_pid"]];
            }
        }
    }

	function getOrgStaffIDsByPIDS(&$result) {
		$aut_ids = array();
       for ($i = 0; $i < count($result); $i++) {
			if ($result[$i]["rek_object_type"] == "3") {
				if (is_array($result[$i]["rek_author_id"])) {
					$aut_ids = array_merge($aut_ids, $result[$i]["rek_author_id"]);
				}
				if (is_array($result[$i]["rek_contributor_id"])) {
					$aut_ids = array_merge($aut_ids, $result[$i]["rek_contributor_id"]);
				}
			}
       }
	  if (count($aut_ids) == 0) {
		return;
	  }
	  $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
      $aut_ids = implode(", ", $aut_ids);

      $stmt = "SELECT
                    aut_id, aut_org_staff_id
                 FROM
                    " . $dbtp . "author
                 WHERE
                    aut_id IN (".$aut_ids.")";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {	
            $t = array();
            for ($i = 0; $i < count($res); $i++) {
                $t[$res[$i]["aut_id"]] =  $res[$i]["aut_org_staff_id"];
            }
            // now populate the $result variable again
            for ($i = 0; $i < count($result); $i++) {
	            for ($y = 0; $y < count($result[$i]['rek_author_id']); $y++) {
					if (!is_array($result[$i]["rek_author_id_external"])) {
						$result[$i]["rek_author_id_external"] = array();
					}
					if (is_numeric($t[$result[$i]['rek_author_id'][$y]])) {
                		$result[$i]["rek_author_id_external"][] = $t[$result[$i]['rek_author_id'][$y]];
					} else {
						$result[$i]["rek_author_id_external"][] = 0;
					}
				}
	            for ($y = 0; $y < count($result[$i]['rek_contributor_id']); $y++) {
					if (!is_array($result[$i]["rek_contributor_id_external"])) {
						$result[$i]["rek_contributor_id_external"] = array();
					}
					if (is_numeric($t[$result[$i]['rek_contributor_id'][$y]])) {
                		$result[$i]["rek_contributor_id_external"][] = $t[$result[$i]['rek_contributor_id'][$y]];
					} else {
						$result[$i]["rek_contributor_id_external"][] = 0;
					}
				}
            }
            //return $res;
        }
  }


    function getParentsByPIDS(&$result) {
        $pids = array();
        
		for ($i = 0; $i < count($result); $i++) {
			if (!empty($result[$i]["rek_ismemberof"])) {
			    if(is_array($result[$i]["rek_ismemberof"])) {
    				foreach($result[$i]["rek_ismemberof"] as $mpid) {
    					if (!in_array($mpid, $pids)) {
    	            		$pids[$mpid] = $mpid;
    					}
    				}
			    } else {
			        $pids[$result[$i]["rek_ismemberof"]] = $result[$i]["rek_ismemberof"];
			    }
			}
		}
		
        if (count($pids) == 0) {
            return;
        }
        
        $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
        $pids = implode("', '", $pids);
        $authArray = Collection::getAuthIndexStmt(array("Lister"), "r1.rek_pid");
        $authStmt = $authArray['authStmt'];
        
        $stmt = "SELECT
                   rek_pid, rek_title
                 FROM
                    " . $dbtp . "record_search_key as r1
                    $authStmt
                 WHERE
                    rek_pid IN ('".$pids."') ";
        
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
            $t = array();
            for ($i = 0; $i < count($res); $i++) {
                $t[$res[$i]["rek_pid"]] =  $res[$i]["rek_title"];
            }
            
            // now populate the $result variable again
            for ($i = 0; $i < count($result); $i++) {
        		$temp  = $result[$i]["rek_ismemberof"];
        		
        		if (is_array($temp)) {
        		    
                    $result[$i]["rek_ismemberof"] = array(
                        "rek_pid" =>  $temp,
                    );
        		    
        			foreach ($temp as $tpid) {
                    	$result[$i]["rek_ismemberof"]["rek_title"][] = $t[$tpid];
        			}
        		} else {
        		    
        		    $result[$i]["rek_ismemberof"] = array(
                        "rek_pid" => array(
                            $temp
                        ),
                    );
        		    
        		    $result[$i]["rek_ismemberof"]["rek_title"][] = $t[$temp];
        		}
            }
        }
    }

	function getCitationIndex($pid) {
		$dbtp =  APP_TABLE_PREFIX; // Database and table prefix
		$stmt = "SELECT
                rek_citation
             FROM
                " . $dbtp . "record_search_key
             WHERE
                rek_pid = '".$pid."'";
    	$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
    	if (PEAR::isError($res)) {
        	Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
    	} else {
			if ($res == "") {
               $res = Record::getSearchKeyIndexValue($pid, "Title");
			}		
			return $res;
		}		
	}	

	function getSearchKeyIndexNextOrder($pid, $sek_title) {
		$dbtp =  APP_TABLE_PREFIX; // Database and table prefix

		$stmt = "SELECT
                   count(rek_".$sek_title.") as sek_count
                FROM
                   " . $dbtp . "record_search_key_".$sek_title."
                WHERE
                   rek_".$sek_title."_pid = '".$pid."'";
       	$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
       	if (PEAR::isError($res)) {
           	Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
       	} else {
			if (is_numeric($res)) {
				$res++;
	       		return $res; 
			} else {
				return 0;
			}
		}
	}

	function getSearchKeyIndexValue($pid, $searchKeyTitle, $getLookup=true, $sek_details="") {
		$dbtp =  APP_TABLE_PREFIX; // Database and table prefix
		
        if(!$sek_details) {
            $sek_details = Search_Key::getBasicDetailsByTitle($searchKeyTitle);
		}
		$sek_title = Search_Key::makeSQLTableName($sek_details['sek_title']);
		
		if ($sek_details['sek_relationship'] == 1) { //1-M so will return an array
			$stmt = "SELECT
                    rek_".$sek_title."
                 FROM
                    " . $dbtp . "record_search_key_".$sek_title."
                 WHERE
                    rek_".$sek_title."_pid = '".$pid."'";
        	$res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        	if (PEAR::isError($res)) {
            	Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        	} else {
        		if ($getLookup == true && $sek_details['sek_lookup_function'] != "") {
        			$temp = array();
        			foreach ($res as $rkey => $rdata) {
        				eval("\$temp_value = ".$sek_details["sek_lookup_function"]."(".$rdata.");");
        				$temp[$rdata] = $temp_value;
        			}
        			$res = $temp;
        		}
        		return $res;
        	}
		} else { //1-1 so will return single value
			$stmt = "SELECT
                    rek_".$sek_title."
                 FROM
                    " . $dbtp . "record_search_key
                 WHERE
                    rek_pid = '".$pid."'";
        	$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        	if (PEAR::isError($res)) {
            	Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        	} else {
        		if ($getLookup == true && $sek_details['sek_lookup_function'] != "") {
        			$temp = array();
        			eval("\$temp_value = ".$sek_details["sek_lookup_function"]."(".$res.");");
        			$temp[$res] = $temp_value;
        			$res = $temp;
        		}
        		return $res;
        	}
		}
		
	}


    function buildSearchKeyJoins($options, $sort_by, $operator, $filter) 
    {
        $searchKey_join = array();
        $searchKey_join[SK_JOIN] = ""; // initialise the return sql searchKey fields join string
		$searchKey_join[SK_LEFT_JOIN] = ""; // initialise the return sql left joins string - so count doesnt need to do it
		$searchKey_join[SK_WHERE] = ""; // initialise the return sql where string
		$searchKey_join[SK_SORT_ORDER] = ""; // initialise the return sql searchKey Order/Sort by join string
        $searchKey_join[SK_KEY_ID] = 1; // initialise the first join searchKey ID
        $searchKey_join[SK_MAX_COUNT] = 0; // initialise the max count of extra searchKey field joins
		$searchKey_join[SK_FULLTEXT_REL] = ""; //initialise the return sql term relevance matching when fulltext indexing is used
		$searchKey_join[SK_SEARCH_TXT] = ""; // initialise the search info string
		$searchKey_join[SK_GROUP_BY] = ""; //initialise the group by string
		$searchKey_join[SK_ORDER_BY] = ""; //initialise the order by return string
		
		$searchKey_join['sk_where_AND'] = '';
		$searchKey_join['sk_where_OR'] = '';
		
		foreach ($options as $sek_id => $value) {
			if (strpos($sek_id, "searchKey") !== false) {
				$searchKeys[str_replace("searchKey", "", $sek_id)] = $value;
			}
		}
		
		$joinType = "";
		$x = 0;
		$sortRestriction = "";
		$dbtp =  APP_TABLE_PREFIX; // Database and table prefix //only mysql supports db prefixing, so will remove it - no reason not to
        
        $operatorToUse = trim($operator);
        
        /*
         * Fulltext SQL (Special Case)
         */
    	if ($searchKeys["0"]  && trim($searchKeys["0"]) != "") { //this will have to replaced with lots of union select joins like eventum
            
    	    $joinType = " INNER JOIN ";
    	    if( $operatorToUse == 'OR' )
    	    {
    	        $joinType = " LEFT JOIN ";
    	    }
    	    
    	    $escapedInput = Misc::escapeString($searchKeys["0"]);
    		
    		$searchKey_join[SK_KEY_ID] = 1;
    		$searchKey_join[SK_SEARCH_TXT] .= "Title, Abstract, Keywords:\"".trim(htmlspecialchars($searchKeys["0"]))."\", ";
			$searchKey_join[SK_JOIN] .= $joinType." (SELECT rek_pid, MATCH(rek_pid, rek_title, rek_description) AGAINST ('$escapedInput') AS Relevance ".
													" FROM {$dbtp}record_search_key ".
													" WHERE MATCH (rek_pid, rek_title, rek_description) AGAINST ('*$escapedInput*' IN BOOLEAN MODE)".
													" UNION ".
													" SELECT rek_keywords_pid AS rek_pid, MATCH(rek_keywords) AGAINST ('$escapedInput') AS Relevance ".
													" FROM {$dbtp}record_search_key_keywords ".
													" WHERE MATCH (rek_keywords) AGAINST ('*$escapedInput*' IN BOOLEAN MODE))".
													" AS search ON search.rek_pid = r1.rek_pid ";
            
    		$searchKey_join[SK_GROUP_BY] = " GROUP BY r1.rek_pid ";
    		$termRelevance = ", SUM(search.Relevance) as Relevance";
    		$searchKey_join[SK_FULLTEXT_REL] = $termRelevance;
    	}

    	/*
    	 * For each search key build SQL if data was submitted
    	 */
    	$tableJoinID = 1;
    	foreach ($searchKeys as $sek_id => $searchValue ) {
    	    
    	 	 if (!empty($searchValue) && trim($searchValue) != "") {
                
    	 	    $sekdet = Search_Key::getDetails($sek_id);
    	 	    
    	 	    if(empty($sekdet['sek_id']))
                    continue;
    	 	    
    	 	    $options["sql"] = array();
    	 	    $temp_value = "";
    	 	    $joinID = '';
    	 	    $sqlColumnName = '';
    	 	    $operatorToUse = trim($operator);
    	 	    
    	 	    /*
    	 	     * joinID is the prefix when using a column in the SQL
    	 	     *
    	 	     * For search keys that have a many-to-many relationship we are
    	 	     * going to join the table to the search query and prefex it with
    	 	     * $x ie.JOIN table r3. So all columns in 'table' will need to use 'r3'
    	 	     *
    	 	     * 1-to-1 search keys will be in default table 
    	 	     * So you default prefix - r1
    	 	     */
    	 	    if ($sekdet['sek_relationship'] == "1") {
    	 	        $joinID = ++$tableJoinID;
    	 	    }
    	 	    else {
    	 	        $joinID = $searchKey_join[SK_KEY_ID];
    	 	    }
    	 	    
    	 	    $sqlColumnName = "r{$joinID}.rek_".$sekdet['sek_title_db'];
    	 	    
    	 	    /*
    	 	     * Build the SQL for this particular search key
    	 	     */
    	 	    if (is_array($searchValue)) {
    	 	        
    	 	        if( isset($searchValue['override_op']) ) {
    	                $operatorToUse = $searchValue['override_op'];
    	                unset($searchValue['override_op']);
 	    	        }
    	 	        
    	 	        // Multiple type is 'All Of' or 'Any of'
    	 	        $multiple_type = '';
    	 	        if( @isset($searchValue['multiple_type']) ) {
	 	    	        $multiple_type = $searchValue['multiple_type'];
	 	    	        unset($searchValue['multiple_type']);
	 	    	        
	 	    	        /*
	 	    	         * Multiple type is always submitted for multiselect controls, 
	 	    	         * so if it was the only thing in the array, nothing was actually
	 	    	         * selected - so skip this
	 	    	         */
	 	    	        if( count($searchValue) == 0 ) {
	 	    	            continue;
	 	    	        }
	 	    	    }
    	 	        
    	 	    	if ($sekdet['sek_data_type'] == "int") {
    	 	    	    
    	 	    	    if( $multiple_type == 'all' ) {
    	 	    	        $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName = " . implode(" AND $sqlColumnName = ", $searchValue);
    	 	    	    } else {
    	 	    	        $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName IN (".implode(",", $searchValue).")";
    	 	    	    }
						
						$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"";
						$temp_counter = 0;
						foreach ($searchValue as $temp_int) {
							if (is_numeric($temp_int) && (!empty($sekdet["sek_lookup_function"]))) {
								eval("\$temp_value = ".$sekdet["sek_lookup_function"]."(".$temp_int.");");
								if ($temp_counter != 0) {
									$searchKey_join[SK_SEARCH_TXT] .= ",";
								}
    	 	    				$searchKey_join[SK_SEARCH_TXT] .= " ".trim(htmlspecialchars($temp_value));
    	 	    				$temp_counter++;
							}
						}
						$searchKey_join[SK_SEARCH_TXT] .= "\", ";
						
    	 	    	} elseif ($sekdet['sek_data_type'] == "date") {
    	 	    	    
						if (!empty($searchValue) && $searchValue['filter_enabled'] == 1) {
						    $sqlDate = '';
						    switch ($searchValue['filter_type']) {
								case 'greater':
									$sqlDate = " >= '".Misc::escapeString($searchValue['start_date'])."' ";
									break;
								case 'less':
									$sqlDate = " <= '".Misc::escapeString($searchValue['start_date'])."' ";
									break;
								case 'between':
						            $sqlDate = " BETWEEN '".Misc::escapeString($searchValue['start_date'])."' AND '".Misc::escapeString($searchValue['end_date'])."'";
									break;
							}
							
							$searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName . $sqlDate;
							$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\" $sqlDate \", ";
						}
						
    	 	    	} else {
    	 	    	    
    	 	    	    if( $multiple_type == 'all' ) {
    	 	    	        $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName = '" . implode("' AND $sqlColumnName = '", $searchValue) . "'";
    	 	    	    } else {
    	 	    	        $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName IN ('".implode("','", $searchValue)."')";
    	 	    	    }
						
						$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(implode("','", $searchValue))."\", ";
    	 	    	}
    	 	    	
    	 	    } else { // Array was not submitted for this search key
                    
    	 	        if ($searchValue == "-1") { //where empty or not set
 	        			$searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName is null ";
	 	   	    	} elseif ($searchValue == "-2") { //this user
	 	        		$usr_id = Auth::getUserID();
	 	        		$searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName = $usr_id";
	 	   	    	} elseif ($searchValue == "-4") { //not published
	 	        		$published_id = Status::getID("Published");
	 	        		$searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName != $published_id";
	 	   	    	} elseif ($searchValue == "-3") { //myself or un-assigned
	 	        		$usr_id = Auth::getUserID();
	 	        		
	 	        		$tmpSql = " (($sqlColumnName = '".$usr_id."') ";
	 	        		
	 	        		if ($sekdet['sek_relationship'] == 1) {
    		        		 $tmpSql .= "OR NOT EXISTS
            							(SELECT * 
            							 FROM {$dbtp}record_search_key_".$sekdet['sek_title_db']." AS sr 
            							 WHERE sr.rek_".$sekdet['sek_title_db']."_pid = r{$joinID}.rek_pid))";
    	             	} else {
    	             		$tmpSql .= "OR ($sqlColumnName IS NULL OR $sqlColumnName = ''))";
    	             	}
	 	        		
	 	        		$searchKey_join["sk_where_$operatorToUse"][] =  $tmpSql;
	 	        	}
    	 	    	elseif ($sekdet['sek_data_type'] == "int") {
    	 	    		
    	 	    		if (is_numeric($searchValue)) {
    	 	    		    $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName = ".$searchValue;
    	 	    		    
    	 	    			if (!empty($sekdet["sek_lookup_function"])) {
    	 	    				eval("\$temp_value = ".$sekdet["sek_lookup_function"]."(".$searchValue.");");
    	 	    				$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars($temp_value)."\", ";
    	 	    			} else {
    	 	    				$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(trim($searchValue))."\", ";
    	 	    			}
    	 	    		}
    	 	    		
    	 	    	} elseif (($sekdet['sek_data_type'] == 'text' || $sekdet['sek_data_type'] == 'varchar') 
	 	                     && ($sekdet['sek_html_input'] == 'text' || $sekdet['sek_html_input'] == 'textarea')) {
    	 	        	
						if ($sekdet['sek_title_db'] == "pid") {
						    // Check if user has done a google like search by adding *
						    $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName like '".str_replace("*", "%", Misc::escapeString($searchValue))."' ";
						} else {
						    $searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName like '%".Misc::escapeString($searchValue)."%' ";
						}
    	 	        	
    	 	        	$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(trim($searchValue))."\", ";
 	        	
    	 	    	} else {
    	 	    		$searchKey_join["sk_where_$operatorToUse"][] = "$sqlColumnName = '".Misc::escapeString($searchValue)."'";
    	 	    		$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(trim($searchValue))."\", ";
    	 	    	}
	 	        	
    	 	    }
	 	    		
	 	    		/*
	 	    		 * If this search key has a 1-To-Many relationship
	 	    		 * it will have its own table, so we need to join to it
	 	    		 */
 	        	if ($sekdet['sek_relationship'] == 1) {
                    
 	        	    $joinType = " INNER JOIN ";
 	        	    if( $operatorToUse == 'OR' )
 	        	    {
 	        	        $joinType = " LEFT JOIN ";
 	        	    }
 	        	    
                    $searchKey_join[SK_JOIN] .= "\n$joinType {$dbtp}record_search_key_".$sekdet['sek_title_db']." as r{$joinID} on r{$joinID}.rek_".$sekdet['sek_title_db']."_pid = r".$searchKey_join[SK_KEY_ID].".rek_pid ";
 	        	}
	 	        
    	  	}
    	}
    	
        /*
         * Only do a sort if the query has be limited in some way, 
         * otherwise it is far too slow
         */
        if (!empty($sort_by)) { //  && $tableJoinID != 1
            
            $sek_id = str_replace("searchKey", "", $sort_by);
            if ($sek_id != '') {
                if ($sek_id == '0' && (trim($searchKeys[0]) != "")) {
                    
             		if ($options["sort_order"] == 0) {
	             		$searchKey_join[SK_SORT_ORDER] .= " Relevance ASC, ";
					} else {
						$searchKey_join[SK_SORT_ORDER] .= " Relevance DESC, ";
					}
					
             	} else {
             	    
		        	$sekdet = Search_Key::getDetails($sek_id);
		        	
		        	if( !empty($sekdet['sek_id']) ) {
		        	    
    	             	if ($sekdet['sek_relationship'] == 1) {
    		        		$searchKey_join[SK_LEFT_JOIN] .= " LEFT JOIN {$dbtp}record_search_key_".$sekdet['sek_title_db']." as rsort on rsort.rek_".$sekdet['sek_title_db']."_pid = r".$searchKey_join[SK_KEY_ID].".rek_pid ".$sortRestriction;
    						$searchKey_join[SK_SORT_ORDER] .= " rsort";
    	             	} else {
    	             		$searchKey_join[SK_SORT_ORDER] .= "r".$searchKey_join[SK_KEY_ID];
    	             	}
    	             	
    				    if ($options["sort_order"] == "1") {
    	             		$searchKey_join[SK_SORT_ORDER] .= ".rek_".$sekdet['sek_title_db']." DESC, ";
    	             	} else {
    	             		$searchKey_join[SK_SORT_ORDER] .= ".rek_".$sekdet['sek_title_db']." ASC, ";
    	             	}
    	             	
		        	}
             	}
            }
             
        }
        
        /*
         * Create single sql WHERE clause string
         *
         * This is done so we can seperate the AND's and OR's
         * in the WHERE clause with brackets
         */
        if( is_array($searchKey_join['sk_where_AND']) || is_array($searchKey_join['sk_where_OR']) ) {
            
            $sk_where_and = false;
            $searchKey_join[SK_WHERE] = " WHERE ";
            
            if( is_array($searchKey_join['sk_where_AND']) ) {
                $searchKey_join[SK_WHERE] .= " ( " . implode(' AND ', $searchKey_join['sk_where_AND']) . " ) ";
                $sk_where_and = true;
            }
            
            if( is_array($searchKey_join['sk_where_OR']) ) {
                if( $sk_where_and )
                    $searchKey_join[SK_WHERE] .= " AND ";
                
                $searchKey_join[SK_WHERE] .= " ( " . implode(' OR ', $searchKey_join['sk_where_OR']) . " ) ";
            }
            
        }
        
        return $searchKey_join;
    }




    function buildSearchKeyFilterSolr($options, $sort_by, $operator = "AND") 
    {
        $searchKey_join = array();
        $searchKey_join[SK_JOIN] = ""; // initialise the return sql searchKey fields join string
		$searchKey_join[SK_LEFT_JOIN] = ""; // initialise the return sql left joins string - so count doesnt need to do it
		$searchKey_join[SK_WHERE] = ""; // initialise the return sql where string
		$searchKey_join[SK_SORT_ORDER] = ""; // initialise the return sql searchKey Order/Sort by join string
        $searchKey_join[SK_KEY_ID] = 1; // initialise the first join searchKey ID
        $searchKey_join[SK_MAX_COUNT] = 0; // initialise the max count of extra searchKey field joins
		$searchKey_join[SK_FULLTEXT_REL] = ""; //initialise the return sql term relevance matching when fulltext indexing is used
		$searchKey_join[SK_SEARCH_TXT] = ""; // initialise the search info string
		$searchKey_join[SK_GROUP_BY] = ""; //initialise the group by string
		$searchKey_join[SK_ORDER_BY] = ""; //initialise the order by return string
	
		$searchKey_join['sk_where_AND'] = '';
		$searchKey_join['sk_where_OR'] = '';
		
		foreach ($options as $sek_id => $value) {
			if (strpos($sek_id, "searchKey") !== false) {
				$searchKeys[str_replace("searchKey", "", $sek_id)] = $value;
			}
		}
		
		$joinType = "";
		$x = 0;
		$sortRestriction = "";
		$dbtp =  APP_TABLE_PREFIX; // Database and table prefix //only mysql supports db prefixing, so will remove it - no reason not to
        
        $operatorToUse = trim($operator);
        
        /*
         * Fulltext SQL (Special Case)
         */
    	if ($searchKeys["0"]  && trim($searchKeys["0"]) != "") { //this will have to replaced with lots of union select joins like eventum
            
    		$escapedInput = $searchKeys["0"];
    		$searchKey_join[SK_KEY_ID] = 1;
    		$searchKey_join[SK_SEARCH_TXT] .= "All Fields:\"".trim(htmlspecialchars($searchKeys["0"]))."\", ";

			$solr_titles = Search_Key::getSolrTitles();
			foreach ($solr_titles as $skey => $svalue) {
				$escapedInput = str_replace($skey.":", $svalue.":", $escapedInput);				
			}
			$pattern = '/(?<!'.implode("|", $solr_titles).')(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
			$replace = '\\\$1';
			preg_replace($pattern, $replace, $escapedInput);			
    		$searchKey_join["sk_where_AND"][] = "" .$escapedInput;
    	}

    	/*
    	 * For each search key build SQL if data was submitted
    	 */
		if (is_array($searchKeys)) {
        	foreach ($searchKeys as $sek_id => $searchValue ) {
        	    
        	 	 if (!empty($searchValue) && trim($searchValue) != "") {
                    
        	 	    $sekdet = Search_Key::getDetails($sek_id);
        	 	    $suffix = Record::getSolrSuffix($sekdet);
        	 	    if(empty($sekdet['sek_id']))
                        continue;
        	 	    
        	 	    $options["sql"] = array();
        	 	    $temp_value = "";
        	 	    $joinID = '';
        	 	    $sqlColumnName = '';
        	 	    $operatorToUse = trim($operator);
        	 	    
        	 	    $sqlColumnName = $sekdet['sek_title_db'];
        	 	    
        	 	    /*
        	 	     * Build the SQL for this particular search key
        	 	     */
        	 	    if (is_array($searchValue)) {
    	
    	
    		 	        if (isset($searchValue['override_op']) ) {
        	                $operatorToUse = $searchValue['override_op'];
        	                unset($searchValue['override_op']);
     	    	        }
        	 	        
        	 	        // Multiple type is 'All Of' or 'Any of'
        	 	        $multiple_type = '';
        	 	        if( @isset($searchValue['multiple_type']) ) {
    	 	    	        $multiple_type = $searchValue['multiple_type'];
    	 	    	        unset($searchValue['multiple_type']);
    	 	    	        
    	 	    	        /*
    	 	    	         * Multiple type is always submitted for multiselect controls, 
    	 	    	         * so if it was the only thing in the array, nothing was actually
    	 	    	         * selected - so skip this
    	 	    	         */
    	 	    	        if( count($searchValue) == 0 ) {
    	 	    	            continue;
    	 	    	        }
    	 	    	    }
    	
    	
        	 	    	if ($sekdet['sek_data_type'] == "int") {
        	 	        	if ($searchValue[0] != "any") {
    	    	 	    	    if( $multiple_type == 'all' ) {
    	    	 	    	        $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":(" . Record::escapeSolr(implode(" AND ", $searchValue)).")";
    	    	 	    	    } else {
    	    	 	    	        $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":(".Record::escapeSolr(implode(" OR ", $searchValue)).")";
    	    	 	    	    }
    						
    							$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"";
    							$temp_counter = 0;
    							foreach ($searchValue as $temp_int) {
    								if (is_numeric($temp_int) && (!empty($sekdet["sek_lookup_function"]))) {
    									eval("\$temp_value = ".$sekdet["sek_lookup_function"]."(".Record::escapeSolr($temp_int).");");
    									if ($temp_counter != 0) {
    										$searchKey_join[SK_SEARCH_TXT] .= ",";
    									}
    	    	 	    				$searchKey_join[SK_SEARCH_TXT] .= " ".trim(htmlspecialchars($temp_value));
    	    	 	    				$temp_counter++;
    								}
    							}
    							$searchKey_join[SK_SEARCH_TXT] .= "\", ";
    						}
        	 	    	} elseif ($sekdet['sek_data_type'] == "date") {
        	 	    	    
    						if (!empty($searchValue) && $searchValue['filter_enabled'] == 1) {
    						    $sqlDate = '';
    						    switch ($searchValue['filter_type']) {
    								case 'greater':
    									$sqlDate = "[".Record::escapeSolr(Date_API::getFedoraFormattedDate($searchValue['start_date']))." TO * ] ";
    									break;
    								case 'less':
    									$sqlDate = "[ * TO ".Record::escapeSolr(Date_API::getFedoraFormattedDate($searchValue['start_date']))."] ";
    									break;
    								case 'between':
    						            $sqlDate = " [".Record::escapeSolr(Date_API::getFedoraFormattedDate($searchValue['start_date']))." TO ".Record::escapeSolr(Date_API::getFedoraFormattedDate($searchValue['end_date']))."]";
    									break;
    							}
    							
    							$searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName .$suffix. ":" .$sqlDate;
    							$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\" $sqlDate \", ";
    						}
    						
        	 	    	} else {
        	 	    	    if( $multiple_type == 'all' ) {
        	 	    	        $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.": " . implode(" AND ".$sqlColumnName."_ms:", Record::escapeSolr($searchValue)) . "";
        	 	    	    } else {
        	 	    	        $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":(".implode(" OR ", Record::escapeSolr($searchValue)).")";
        	 	    	    }
    						
    						$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(implode("','", $searchValue))."\", ";
        	 	    	}
        	 	    	
        	 	    } else { // Array was not submitted for this search key
                        
        	 	        if ($searchValue == "-1") { //where empty or not set
     	        			$searchKey_join["sk_where_$operatorToUse"][] = "-".$sqlColumnName.$suffix.":[* TO *]";
    	 	   	    	} elseif ($searchValue == "-2") { //this user
    	 	        		$usr_id = Auth::getUserID();
    	 	        		$searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":".$usr_id;
    	 	   	    	} elseif ($searchValue == "-4") { //not published
    	 	        		$published_id = Status::getID("Published");
    	 	        		$searchKey_join["sk_where_$operatorToUse"][] = "-".$sqlColumnName.$suffix.":".$published_id;
    	 	   	    	} elseif ($searchValue == "-3") { //myself or un-assigned
    	 	        		$usr_id = Auth::getUserID();
    	 	        		
    	 	        		$tmpSql = " ((".$sqlColumnName.$suffix.":".$usr_id.") ";
    	 	        		
       	             		$tmpSql .= "OR (-".$sqlColumnName.$suffix.":[* TO *]))";
    	 	        		
    	 	        		$searchKey_join["sk_where_$operatorToUse"][] =  $tmpSql;
    	 	        	} elseif ($sekdet['sek_data_type'] == "int") {
        	 	    		
        	 	    		if (is_numeric($searchValue)) {
        	 	    		    $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":".Record::escapeSolr($searchValue);
        	 	    		    
        	 	    			if (!empty($sekdet["sek_lookup_function"])) {
        	 	    				eval("\$temp_value = ".$sekdet["sek_lookup_function"]."(".Record::escapeSolr($searchValue).");");
        	 	    				$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars($temp_value)."\", ";
        	 	    			} else {
        	 	    				$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(trim($searchValue))."\", ";
        	 	    			}
        	 	    		}
        	 	    		
        	 	    	} elseif (($sekdet['sek_data_type'] == 'text' || $sekdet['sek_data_type'] == 'varchar') 
    	 	                     && ($sekdet['sek_html_input'] == 'text' || $sekdet['sek_html_input'] == 'textarea')) {
        	 	        	
    						if ($sekdet['sek_title_db'] == "pid") {
    						    // Check if user has done a google like search by adding *
    						    $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":(".Record::escapeSolr($searchValue).") ";
    						} else {
    						    $searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":(".Record::escapeSolr($searchValue).") ";
    						}
        	 	        	
        	 	        	$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(trim($searchValue))."\", ";
     	        	
        	 	    	} else {
        	 	    		$searchKey_join["sk_where_$operatorToUse"][] = $sqlColumnName.$suffix.":".Record::escapeSolr($searchValue)."";
        	 	    		$searchKey_join[SK_SEARCH_TXT] .= $sekdet['sek_title'].":\"".htmlspecialchars(trim($searchValue))."\", ";
        	 	    	}
    	 	        	
        	 	    }
    	 	        
        	  	}
        	}
        	
            
            if( is_array($searchKey_join['sk_where_AND']) || is_array($searchKey_join['sk_where_OR']) ) {
                
                $sk_where_and = false;
                $searchKey_join[SK_WHERE] = "  ";
                
                if( is_array($searchKey_join['sk_where_AND']) ) {
                    $searchKey_join[SK_WHERE] .= " (" . implode(' AND ', $searchKey_join['sk_where_AND']) . ") ";
                    $sk_where_and = true;
                }
                
                if( is_array($searchKey_join['sk_where_OR']) ) {
                    if( $sk_where_and )
                        $searchKey_join[SK_WHERE] .= " AND ";
                    
                    $searchKey_join[SK_WHERE] .= " (" . implode(' OR ', $searchKey_join['sk_where_OR']) . ") ";
                }
                
            }
		}
		
		/*
         * Only do a sort if the query has be limited in some way, 
         * otherwise it is far too slow
         */
        if (!empty($sort_by)) { //  && $tableJoinID != 1
            
            $sek_id = str_replace("searchKey", "", $sort_by);
            if ($sek_id != '') {
                if ($sek_id == '0' && (trim($searchKeys[0]) != "")) {
                    
             		if ($options["sort_order"] == 0) {
	             		$searchKey_join[SK_SORT_ORDER] .= " score asc ";
					} else {
						$searchKey_join[SK_SORT_ORDER] .= " score desc ";
					}
					
             	} else {
             	    
		        	$sekdet = Search_Key::getDetails($sek_id);		
		        	
		        	if( !empty($sekdet['sek_id']) ) {
    	             	$sort_suffix = Record::getSolrSuffix($sekdet, 1);
    				    if ($options["sort_order"] == "1") {
    	             		$searchKey_join[SK_SORT_ORDER] .= $sekdet['sek_title_db'].$sort_suffix." desc ";
    	             	} else {
    	             		$searchKey_join[SK_SORT_ORDER] .= $sekdet['sek_title_db'].$sort_suffix." asc ";
    	             	}
		        	}
             	}
            }             
        }        

        return $searchKey_join;
    }


	function getSolrSuffix($sek_det, $sort=0, $facet=0) {
		$suffix = "";
		$sek_data_type = $sek_det['sek_data_type'];
		$sek_relationship = $sek_det['sek_relationship'];
		if (($sek_data_type == 'int') && ($sek_relationship == 0)) {
			$suffix = "_i";
		} elseif (($sek_data_type == 'int') && ($sek_relationship == 1)) {
			$suffix = "_mi";
		} elseif (($sek_data_type == 'varchar' || $sek_data_type == 'text') && $sek_relationship == 0) {
			$suffix = "_t";
			if ($sort == 1) {
				$suffix .= "_s";
			} elseif($facet == 1) {
			    //$suffix .= "f";
			}
		} elseif (($sek_data_type == 'varchar' || $sek_data_type == 'text') && $sek_relationship == 1) {
			$suffix = "_m";
			if ($sort == 1) {
				$suffix .= "s";
			} elseif($facet == 1) {
			    //$suffix .= "f";
			} else {
			    $suffix .= "t";
			}
		} elseif (($sek_data_type == 'date') && ($sek_relationship == 0)) {
			$suffix = "_dt";
		} elseif (($sek_data_type == 'date') && ($sek_relationship == 1)) {
			$suffix = "_mdt";			
		}
		return $suffix;
		
	}

	function escapeSolr($string) {
		$solr_service = new Apache_Solr_Service();
		return $solr_service->escape($string);
	}


    /**
     * Find all records where the user is creator  (based on getAssigned)
     *
     * Note: if user has "create" on a collection and individual
     * records do not have a specific creator assigned, this may have
     * unexpected results.
     *
     * @access  public
     * @param string $username The username of the search is performed on
     * @return array $res2 The index details of records associated with the user
     */
    function getCreated($options, $current_page=0,$page_rows="ALL",$sort_by='', $sort_order=0)
    {
    	$usr_id = Auth::getUserID();
    	$options["searchKey".Search_Key::getID("Depositor")] = $usr_id;
    	return Record::getListing($options, array("Lister"), $current_page, $page_rows, $sort_by);
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
        $dbtp =  APP_TABLE_PREFIX; // Database and table prefix
        $stmt = "SELECT ".APP_SQL_CACHE."   rek_pid FROM
        ".$dbtp."record_search_key
        WHERE rek_status is null";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        foreach ($res as $row) {
            $r = new RecordObject($row['rek_pid']);
            if ($r->getXmlDisplayId()) {
                echo $r->getTitle()."<br/>\n";
                $r->setStatusId($sta_id);
            }
        }
    }
    /**
	 * Sets up a template for insertion into Fedora. Used in workflows.
     *
     * @access  public
     * @return  array Array of datastreamTitles, xmlObj and indexArray
     */
    function makeInsertTemplate()
    {
        $created_date = Date_API::getFedoraFormattedDateUTC();
        $updated_date = $created_date;
        $pid = '__makeInsertTemplate_PID__';
        $xdis_id = $_POST["xdis_id"];
        $display = new XSD_DisplayObject($xdis_id);
        list($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema) = $display->getXsdAsReferencedArray();
        // find the title elements for this display (!dc:title or MODS)
        $display->getXSD_HTML_Match();
        $xsdmf_id = $display->xsd_html_match->getXSDMF_IDByXDIS_ID('!titleInfo!title');
        $inherit_xsdmf_id = $display->xsd_html_match->getXSDMF_IDByXDIS_ID('!inherit_security');
		if ($inherit_xsdmf_id) {
            // fake the form input for inherit security
            $_POST['xsd_display_fields'][$inherit_xsdmf_id] = 'on';
		}

        if ($xsdmf_id) {
            // fake the form input for the object title
            $_POST['xsd_display_fields'][$xsdmf_id] = '__makeInsertTemplate_DCTitle__';
        } else {
            $xsdmf_id = $display->xsd_html_match->getXSDMF_IDByXDIS_ID('!dc:title');
            if ($xsdmf_id) {
                // fake the form input for the object title
                $_POST['xsd_display_fields'][$xsdmf_id] = '__makeInsertTemplate_DCTitle__';
            }
        }
        
		$indexArray = array();
		$xmlObj = '<?xml version="1.0"?>'."\n";
		$xmlObj .= "<".$xsd_element_prefix.$xsd_top_element_name." ";
		$xmlObj .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid); // for the pid, fedora uri etc
		$xmlObj .= $xml_schema;
		$xmlObj .= ">\n";
		
		$xmlObj = Foxml::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "", $indexArray, 0, $created_date, $updated_date, Auth::getUserID(),array(Auth::getUserID()));
		$xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">";
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
                    'indent'       => true,
                    'input-xml'    => true,
                    'output-xml'   => true,
                    'wrap'         => 0,
            );
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
        
		$convert_check = false;

        // ingest the datastreams
		foreach ($datastreamXMLHeaders as $dsKey => $dsTitle) {

			$dsIDName = $dsTitle['ID'];

			if (is_numeric(strpos($dsIDName, "."))) {
				$filename_ext = strtolower(substr($dsIDName, (strrpos($dsIDName, ".") + 1)));
				$dsIDName = substr($dsIDName, 0, strrpos($dsIDName, ".") + 1).$filename_ext;
			}
			if (($dsIDName == "DC") && (!$ingestObject)) { // Dublic core is special, it cannot be deleted
		    	Fedora_API::callModifyDatastreamByValue($pid, $dsIDName, $dsTitle['STATE'], $dsTitle['LABEL'],
                        $datastreamXMLContent[$dsKey], $dsTitle['MIMETYPE'], false);
			} else {
				if ($dsTitle['CONTROL_GROUP'] == "R" ) { // if its a redirect we don't need to upload the file
                    if (Fedora_API::datastreamExists($pid, $dsIDName)) {
                        Fedora_API::callPurgeDatastream($pid, $dsIDName);
                    }
                    $location = trim($datastreamXMLContent[$dsKey]);
                    if (!empty($location)) {
                        $location = str_replace("&amp;", "&", $location); 
                        Fedora_API::callAddDatastream($pid, $dsTitle['ID'], $datastreamXMLContent[$dsKey],
                                $dsTitle['LABEL'], $dsTitle['STATE'], $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
                    }
                } elseif (($dsTitle['CONTROL_GROUP'] == "X") && (!$ingestObject)) {
					if (Fedora_API::datastreamExists($pid, $dsIDName)) {
				    	Fedora_API::callModifyDatastreamByValue($pid, $dsIDName, $dsTitle['STATE'], $dsTitle['LABEL'],
    	                    $datastreamXMLContent[$dsKey], $dsTitle['MIMETYPE'], "false");
					} else {
						Fedora_API::getUploadLocation($pid, $dsTitle['ID'], $datastreamXMLContent[$dsKey], $dsTitle['LABEL'],
							$dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);
					}
 				} elseif (($dsTitle['CONTROL_GROUP'] == "M")) { // control group == 'M'

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
					
					Fedora_API::getUploadLocationByLocalRef($pid, $ncName, $dsTitle['File_Location'], $dsTitle['LABEL'], $dsTitle['MIMETYPE'], $dsTitle['CONTROL_GROUP']);	
			        Record::generatePresmd($pid, $dsIDName);
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
                if (is_file(APP_TEMP_DIR.$ncNameDelete)) {
                    unlink(APP_TEMP_DIR.$ncNameDelete);
                }
			}
        }

		Record::setIndexMatchingFields($pid);
    }
    
    
    function insertRecentRecords($pids)
    {
        $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "recently_added_items" . 
                " VALUES ('" . implode("'),('", $pids) . "')";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        }
    }
    
    function deleteRecentRecords()
    {
        $stmt = "DELETE FROM " . APP_TABLE_PREFIX . "recently_added_items";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        }
    }
    
    
    function insertRecentDLRecords($pids)
    {
        $first = true;
        $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "recently_downloaded_items" . 
                " VALUES ";
        foreach ($pids as $pid) {
            if(!$first) {
                $stmt .= ",";
            } else {
                $first = false;
            }
            $stmt .= "('".$pid['stl_pid']. "'," . $pid['downloads'].")";
        }
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        }
    }
    
    function deleteRecentDLRecords()
    {
        $stmt = "DELETE FROM " . APP_TABLE_PREFIX . "recently_downloaded_items";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        }
    }
    
    
    

    function generatePresmd($pid, $dsIDName)
    {
	
	
		//Jhove
		$ncName = Foxml::makeNCName($dsIDName);
        $presmd_check = Workflow::checkForPresMD($ncName);
        if ($presmd_check != false) {
            if (is_numeric(strpos($presmd_check, chr(92)))) {
                $presmd_check = substr($presmd_check, strrpos($presmd_check, chr(92))+1);
            }
            if (Fedora_API::datastreamExists($pid, $presmd_check)) {
                Fedora_API::callPurgeDatastream($pid, $presmd_check);
            }
            Fedora_API::getUploadLocationByLocalRef($pid, $presmd_check, $presmd_check, $presmd_check,
                    "text/xml", "M");
            if (is_file(APP_TEMP_DIR.basename($presmd_check))) {
                unlink(APP_TEMP_DIR.basename($presmd_check));
            }
        }
		//ExifTool
		
		Exiftool::saveExif($pid, $dsIDName);


    }



    /**
     * propagateExistingPremisDatastreamToFez
     *
     * This method looks up the PremisEvent datastream of the nominated Fedora object, and checks for its
     * existence. If found, and any events are marked TBG (To Be Generated), the details are written back
     * to the Fez premis_event table, so that the underlying object may be re-built from Fez.
     *
     * @access  public
     * @param   $pid    The PID of the Fedora object we are processing.
     */

    function propagateExistingPremisDatastreamToFez($pid) {

        $datastreams = Fedora_API::callGetDatastreams($pid);
        if (empty($datastreams)) {
            return;     // No datastreams at all; let's bail out.
        }

        foreach ($datastreams as $ds_key => $ds_value) {
            if ($ds_value['ID'] == 'PremisEvent') {
                $value = Fedora_API::callGetDatastreamContents($pid, 'PremisEvent', true);
                //$value = $value['stream'];
                /* It's time for a spot of DOMage */
                $xmlDoc = new DOMDocument();
                $xmlDoc->preserveWhiteSpace = false;
                $xmlDoc->loadxml($value);
                $xpath = new DOMXPath($xmlDoc);
                $xpath->registerNamespace("premis", "http://www.loc.gov/standards/premis");
                $events = $xpath->query("//premis:event");

                foreach ($events as $event) {
                    if ($event->firstChild->nodeValue == "[TBG]") {
                        // Assemble $historyDetail
                        $params = $event->getElementsByTagName("eventType");
                        foreach ($params as $param) {
                               $historyDetail = $param -> nodeValue;
                        }
                        // Assemble $historyDetailExtra
                        $params = $event->getElementsByTagName("eventDetail");
                        foreach ($params as $param) {
                               $historyDetailExtra = $param -> nodeValue;
                        }

                        /* This field is auto-generated at the other end. We can't actually touch this. */
                        // premis:eventDateTime
                        /* The following fields will be populated by the workflow. We may as well just disregard these too */
                        // premis:linkingAgentIdentifierType
                        // premis:linkingAgentIdentifierValue
                        // premis:linkingAgentIdentifierType
                        // premis:linkingAgentIdentifierValue
                        // premis:linkingObjectIdentifier

                        // Invoke the function that writes the details back to the Fez database.
                        History::addHistory($pid, null, "", "", true, $historyDetail, $historyDetailExtra);
                    }
                }
            }
        }
        return;
    }
    
    
    function propagateCommentsDatastreamToFez($pid)
    {
        include_once(APP_INC_PATH . "class.user_comments.php");
        
        $datastreams = Fedora_API::callGetDatastreams($pid);
        if (empty($datastreams)) {
            return;     // No datastreams at all; let's bail out.
        }
        
        $usr_comments = new UserComments($pid);
        
        foreach ($datastreams as $ds_key => $ds_value) 
        {
            if ($ds_value['ID'] == 'FezComments') 
            {
                $value = Fedora_API::callGetDatastreamContents($pid, 'FezComments', true);
                //echo $value;exit;
                
                $xml = new SimpleXMLElement($value);
                //echo $xml->asXML();
                
                foreach ($xml->comment as $comment) 
                {
                    echo $comment->text . '<br />';
                    //$usr_comments->addUserComment($comment->text, $comment->rating, $comment->user_id);
                }
            }
        }
        
        return;
    }
    
    function isDeleted($pid)
    {

        if (APP_FEDORA_APIA_DIRECT == "ON") {
            $fda = new Fedora_Direct_Access();
            return $fda->isDeleted($pid);
        }
       	$res = Fedora_API::searchQuery('pid='.$pid, array('pid', 'state'));
        if ($res[0]['state'] == 'D') {
        	return true;
        }
        return false;
    }

	function markAsDeleted($pid)
	{
	    // tell fedora that the object is deleted.
        Fedora_API::callModifyObject($pid, 'D', null);

        // delete it from the Fez index.
        Record::removeIndexRecord($pid);
    }


    function markAsActive($pid, $do_index = true)
    {
    	// tell fedora that the object is active.
        Fedora_API::callModifyObject($pid, 'A', null);

		if ($do_index) {
        	// add it to the Fez index.
        	Record::setIndexMatchingFields($pid);
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
    var $viewer_roles;
    var $lister_roles;
    var $editor_roles;
    var $creator_roles;
    var $deleter_roles;
    var $approver_roles;
    var $checked_auth = false;
    var $auth_groups;
    var $display;
    var $details;
    var $record_parents;
    var $status_array = array(
            Record::status_undefined => 'Undefined',
            Record::status_unpublished => 'Unpublished',
            Record::status_published => 'Published'
            );
    var $title;

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
        $this->lister_roles = explode(',',APP_LISTER_ROLES);
        $this->viewer_roles = explode(',',APP_VIEWER_ROLES);
        $this->editor_roles = explode(',',APP_EDITOR_ROLES);
        $this->creator_roles = explode(',',APP_CREATOR_ROLES);
        $this->deleter_roles = explode(',',APP_DELETER_ROLES);
        $this->approver_roles = explode(',',APP_APPROVER_ROLES);

    }

    function getPid()
    {
        return $this->pid;
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
                if (!$this->checkExists()) {
                	Error_Handler::logError("Record ".$this->pid." doesn't exist",__FILE__,__LINE__);
                    return null;
                }
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

    function getXmlDisplayIdUseIndex()
    {
    	$dbtp = APP_TABLE_PREFIX;
        if (!$this->no_xdis_id) {
            if (empty($this->xdis_id)) {
				$stmt = "SELECT rek_display_type FROM ".$dbtp."record_search_key
						WHERE rek_pid = '".$this->pid."'";
				$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
				$this->xdis_id = $res;
		        if (PEAR::isError($res)) {
		            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()),
		            			        __FILE__, __LINE__);
		            $this->xdis_id = null;
		            $this->no_xdis_id = true;
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
    function checkAuth($roles, $redirect=true) 
    {
        $this->getAuth();
        $ret_url = $_SERVER['REQUEST_URI'];
/*	        $ret_url = $_SERVER['PHP_SELF'];
	        if (!empty($_SERVER['QUERY_STRING'])) {
	            $ret_url .= "?".$_SERVER['QUERY_STRING'];
	        } */
		return Auth::checkAuthorisation($this->pid, "", $roles, $ret_url, $this->auth_groups, $redirect);
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
		if (Auth::isAdministrator()) { return true; }
        if ($this->getPublishedStatus() == 2) {
            return $this->checkAuth($this->viewer_roles, $redirect);
        } else {
            return $this->canCreate($redirect); //changed this so that creators can view the objects even when they are not published
//            return $this->canEdit($redirect);
        }
    }

    /**
     * canList
     * Find out if the current user can list this record
	 *
     * @access  public
	 * @param  $redirect
     * @return  void
     */
    function canList($redirect=true) {
		if (Auth::isAdministrator()) { return true; }
        if ($this->getPublishedStatus() == 2) {
            return $this->checkAuth($this->lister_roles, $redirect);
        } else {
            return $this->canCreate($redirect); //changed this so that creators can view the objects even when they are not published
//            return $this->canEdit($redirect);
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
		if (Auth::isAdministrator()) { return true; }
        return $this->checkAuth($this->editor_roles, $redirect);
    }


    /**
     * canDelete
     * Find out if the current user can edit this record
     *
     * @access  public
     * @param  $redirect
     * @return  void
     */
    function canDelete($redirect=false) {
        if (Auth::isAdministrator()) { return true; }
        return $this->checkAuth($this->deleter_roles, $redirect);
    }

    /**
     * canApprove
     * Find out if the current user can publish this record
     *
     * @access  public
     * @param  $redirect
     * @return  void
     */
    function canApprove($redirect=false) {
        if (Auth::isAdministrator()) { return true; }
        return $this->checkAuth($this->approver_roles, $redirect);
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

    function getRecordType()
    {
        $this->getDetails();
        $this->getXmlDisplayId();
        if (!empty($this->xdis_id)) {
	        $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!ret_id');
        	$ret_id = $this->details[$xsdmf_id];
        	return $ret_id;
        } else {
        	return null;
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
        Record::insertIndexMatchingField($this->pid, '', $xsdmf_id, $sta_id);
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
            $newXML .= "<".$key.">".$value."</".$key.">";
        }
        $newXML .= "</FezMD>";
        //Error_handler::logError($newXML,__FILE__,__LINE__);
        if ($newXML != "") {
            Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez extension metadata", $newXML, "text/xml", false);
        }
    }

    /**
     * _Datastream
     * Used to associate a display for this record
     *
     * @access  public
     * @param  $key
     * @param  $value
     * @return  void
     */
    function updateRELSEXT($key, $value, $removeCurrent = true)
    {
		$newXML = "";
        $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'RELS-EXT', true);
        
        if(empty($xmlString) || !is_string($xmlString)) {
            return -3;
        }
        
		$doc = DOMDocument::loadXML($xmlString);
		$xpath = new DOMXPath($doc);
		$fieldNodeList = $xpath->query("/rdf:RDF//rel:isMemberOf");
        
        if($fieldNodeList->length == 0) {
           
        	/*
        	 * There was a point in time when incorrect RELS-EXT xml 
        	 * was created, with an incorrect namespace 'rdf:isMemberOf'
        	 * instead of 'rel:isMemberOf'.
        	 */
            $fieldNodeList = $xpath->query("/rdf:RDF//rdf:isMemberOf");
            if($fieldNodeList->length == 0) {
                return -2;
            }
        }
		
		
		foreach ($fieldNodeList as $fieldNode) { // first delete all the isMemberOfs
			$parentNode = $fieldNode->parentNode;
			if ( $removeCurrent ) {
                $parentNode->removeChild($fieldNode);
			}
		}
		$newNode = $doc->createElementNS('info:fedora/fedora-system:def/relations-external#', 'rel:isMemberOf');
	    $newNode->setAttribute('rdf:resource', 'info:fedora/'.$value);
		$parentNode->appendChild($newNode);
		$newXML = $doc->SaveXML();
        if ($newXML != "") {
            Fedora_API::callModifyDatastreamByValue($this->pid, "RELS-EXT", "A", "Relationships to other objects", $newXML, "text/xml", false);
			Record::setIndexMatchingFields($this->pid);
			return 1;
        }
        
        return -1;
    }
    
    /**
     * Remove record from collection
     *
     * @param string $collection  the pid of the collection
     *
     * @return bool  TRUE if removed OK. FALSE if not removed.
     *
     * @access public
     * @since Method available since RC1
     */
    function removeFromCollection($collection)
    {
        if( $collection == "" ) {
            return false;
        }
        
        $newXML = "";
        $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'RELS-EXT', true);
        
        $doc = DOMDocument::loadXML($xmlString);
        $xpath = new DOMXPath($doc);
        
        $fieldNodeList = $xpath->query("//rel:isMemberOf[@rdf:resource='info:fedora/$collection']");
        
		if( $fieldNodeList->length == 0 ) {
            return false;
		}
		  
		$collectionNode   = $fieldNodeList->item(0);
		$parentNode       = $collectionNode->parentNode;
		$parentNode->removeChild($collectionNode);
		
		$newXML = $doc->SaveXML();
        if ($newXML != "") {
            Fedora_API::callModifyDatastreamByValue($this->pid, "RELS-EXT", "A", "Relationships to other objects", $newXML, "text/xml", false);
			Record::setIndexMatchingFields($this->pid);
			if( APP_SOLR_INDEXER == "ON" ) {
            	FulltextQueue::singleton()->add($this->pid);
            }
			return true;
        }
        
        return false;
    }
    

    /**
     * updateFezMD_User
     * Used to assign this record to a user
     *
     * @access  public
     * @param  $key
     * @param  $value
     * @return  void
     */
    function updateFezMD_User($key, $value)
    {

		$newXML = "";
        $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD', true);
		$doc = DOMDocument::loadXML($xmlString);
		$xpath = new DOMXPath($doc);
		$fieldNodeList = $xpath->query("//usr_id");
		if ($fieldNodeList->length > 0) {
			foreach ($fieldNodeList as $fieldNode) { // first delete all the existing user associations
				$parentNode = $fieldNode->parentNode;
				Error_Handler::logError($fieldNode->nodeName.$fieldNode->nodeValue,__FILE__,__LINE__);
				$parentNode->removeChild($fieldNode);
			}
		} else {
			$parentNode = $doc->lastChild;
		}
		$newNode = $doc->createElement('usr_id');
	    $newNode->nodeValue = $value;
		$parentNode->insertBefore($newNode);
		$newXML = $doc->SaveXML();
        if ($newXML != "") {
            Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez Admin Metadata", $newXML, "text/xml", false);
			Record::setIndexMatchingFields($this->pid);
        }
    }

    /**
     * assignGroupFezMD
     * Used to assign this record to a group
     *
     * @access  public
     * @param  $key
     * @param  $value
     * @return  void
     */
    function updateFezMD_Group($key, $value)
    {

		$newXML = "";
        $xmlString = Fedora_API::callGetDatastreamContents($this->pid, 'FezMD', true);
		$doc = DOMDocument::loadXML($xmlString);
		$xpath = new DOMXPath($doc);
		$fieldNodeList = $xpath->query("//grp_id");
		if ($fieldNodeList->length > 0) {
			foreach ($fieldNodeList as $fieldNode) { // first delete all the existing group associations
				$parentNode = $fieldNode->parentNode;
				Error_Handler::logError($fieldNode->nodeName.$fieldNode->nodeValue,__FILE__,__LINE__);
				$parentNode->removeChild($fieldNode);
			}
		} else {
			$parentNode = $doc->lastChild;
		}
		$newNode = $doc->createElement('grp_id');
	    $newNode->nodeValue = $value;
		$parentNode->insertBefore($newNode);
//		Error_Handler::logError($doc->SaveXML(),__FILE__,__LINE__);
		$newXML = $doc->SaveXML();
        if ($newXML != "") {
            Fedora_API::callModifyDatastreamByValue($this->pid, "FezMD", "A", "Fez Admin Metadata", $newXML, "text/xml", false);
			Record::setIndexMatchingFields($this->pid);
        }
    }

    /**
     * Function can update a single xsdmf in the XML but doesn't work for sublooping elements.
	 * @param integer $xsdmf_id the mapping to update
	 * @param string $value what to set the element to
	 * @param integer $idx the index of the item if this is a multiple item
	 * @return boolean true on success, false on failure.
     */
    function setValue($xsdmf_id, $value, $idx)
    {
    	$this->getDisplay();
        $this->display->getXSD_HTML_Match();
        $cols = $this->display->xsd_html_match->getDetailsByXSDMF_ID($xsdmf_id);
        // which datastream to get XML for?
        // first find the xdis id that the xsdmf_id matches in (not the base xdis_id since this will be in a
        // refered display)
        $xdis_id = $cols['xsdmf_xdis_id'];
        $xsd_id = XSD_Display::getParentXSDID($xdis_id);
        $xsd_details = Doc_Type_XSD::getDetails($xsd_id);
        $dsID = $xsd_details['xsd_title'];
        if ($dsID == 'OAI DC') {
        	$dsID = 'DC';
        }
        //Error_Handler::logError($dsID,__FILE__,__LINE__);
        $xsdmf_element = $cols['xsdmf_element'];
        $steps = explode('!',$xsdmf_element);
        // get rid of blank on the front
        array_shift($steps);
        $doc = DOMDocument::loadXML($xsd_details['xsd_file']);
        $xsd_array = array();
        Misc::dom_xsd_to_referenced_array($doc, $xsd_details['xsd_top_element_name'], $xsd_array,"","",$doc);
        $sXml = Fedora_API::callGetDatastreamContents($this->pid, $dsID, true);
        if (!empty($sXml) && $sXml != false) {
            $doc = DOMDocument::loadXML($sXml);
            // it would be good if we could just do a xpath query here but unfortunately, the xsdmf_element
            // is missing information like namespaces and attribute '@' thing.
            if ($this->setValueRecurse($value, $doc->documentElement, $steps,
                                            $xsd_array[$xsd_details['xsd_top_element_name']], $idx)) {
                Fedora_API::callModifyDatastreamByValue($this->pid, $dsID, "A", "setValue", $doc->saveXML(), "text/xml", false);
                Record::setIndexMatchingFields($this->pid);
            	return true;
            }
        } else {
            return false;
        }
    }

    function setValueRecurse($value, $node, $remaining_steps, $xsd_array, $vidx, $current_idx=0)
    {
        $next_step = array_shift($remaining_steps);
        $next_xsd_array = $xsd_array[$next_step];
        $theNode = null;
        if (isset($next_xsd_array['fez_nodetype']) && $next_xsd_array['fez_nodetype'] == 'attribute') {
            $node->setAttribute($next_step, $value);
            return true;
        } else {
            $use_idx = false;  // should we look the element that matches vidx?  Only if this is the end of the path
            $att_step = $remaining_steps[0];
            $att_xsd = $next_xsd_array[$att_step];
            if (isset($att_xsd['fez_nodetype']) && $att_xsd['fez_nodetype'] == 'attribute') {
            	$use_idx = true;
            }
            if (count($remaining_steps) == 0) {
                $use_idx = true;
            }
            $idx = 0;
            foreach ($node->childNodes as $childNode) {
                // remove namespace
                $next_step_name = $next_step;
                if (!strstr($next_step_name, '!dc:')) {
                    $next_step_name = preg_replace('/![^:]+:/', '!', $next_step_name);
                }
                if ($childNode->nodeName == $next_step_name) {
                    if ($use_idx) {
                        if ($idx == $vidx) {
                            $theNode = $childNode;
                            break;
                        }
                        $idx++;
                    } else {
                        $theNode = $childNode;
                        break;
                    }
                }
            }
        }
        if (is_null($theNode)) {
            $theNode = $node->ownerDocument->createElement($next_step);
            $node->appendChild($theNode);
        }
        if (count($remaining_steps)) {
            if ($this->setValueRecurse($value, $theNode, $remaining_steps, $next_xsd_array, $vidx, $idx)) {
        	   return true;
            }
        } else {
        	if (!empty($value)) {
                $theNode->nodeValue = $value;
            } else {
            	$theNode->parentNode->removeChild($theNode);
            }
            return true;
        }
        return false;
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
        	// if it has no xdis id (display id) log an error and return a null
			Error_Handler::logError("The PID ".$this->pid." does not have an display id (FezMD->xdis_id). This object is currently in an erroneous state.",__FILE__,__LINE__);
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
    function getDetails($dsID = "", $xdis_id = "")
    {
        if (is_null($this->details) || $dsID != "") {
            // Get the Datastreams.
			if ($xdis_id == "") {
            	$this->getDisplay();
			} else {
				$this->display = new XSD_DisplayObject($xdis_id);
				$this->display->getXSD_HTML_Match();
			}
            if ($this->display) {
				if ($dsID != "") {
					$this->details = $this->display->getXSDMF_Values_Datastream($this->pid, $dsID);
				} else {
                	$this->details = $this->display->getXSDMF_Values($this->pid);
				}
            } else {
  				Error_Handler::logError("The PID ".$this->pid." has an error getting it's display details. This object is currently in an erroneous state.",__FILE__,__LINE__);
            }
        }
        
        return $this->details;
    }


    /**
     * Clear the cached details in this record.  Used when the record has been altered to force
     * details to be reparsed from the fedora object.
     */
    function clearDetails()
    {
    	$this->details = null;
    }

    /**
     * getFieldValueBySearchKey
     * Get the value or values of a metadata field that matches a given search key
     *
     * @access  public
     * @param $sek_title string - The name of the search key to get the field value for, e.g. 'Title'
     * @return  array $this->details[$xsdmf_id] The Dublin Core title of the object
     */
    function getFieldValueBySearchKey($sek_title)
    {
        $this->getDetails();
        $this->getXmlDisplayId();
        if (!empty($this->xdis_id)) {
             $sek_id = Search_Key::getID($sek_title);
             if (!$sek_id) {
                 return null;
             }
             $res = array();
             foreach ($this->display->xsd_html_match->getMatchCols() as $xsdmf ) {
                 if ($xsdmf['xsdmf_sek_id'] == $sek_id) {
                     $res[] = $this->details[$xsdmf['xsdmf_id']];
                 }
             }
             return $res;
        } else {
            // if it has no xdis id (display id) log an error and return a null
            Error_Handler::logError("The PID ".$this->pid." does not have an display id (FezMD->xdis_id). This object is currently in an erroneous state.",__FILE__,__LINE__);
            return null;
        }
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
        if (empty($this->title)) {
		    $this->getDetails();
		    $this->getXmlDisplayId();
			if (!empty($this->xdis_id)) {
			     $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!dc:title');
			     $this->title = $this->details[$xsdmf_id];
			} else {
		        // if it has no xdis id (display id) log an error and return a null
        	    Error_Handler::logError("Fez cannot display PID " . $this->pid .
        	    	" because it does not have a display id (FezMD/xdis_id). ",__FILE__,__LINE__);
	            return null;
    		}
		}
    	return $this->title;
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
        $this->getXmlDisplayId();
        if (!empty($this->xdis_id)) {
            $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID('!dc:type');
        } else {
            // if it has no xdis id (display id) log an error and return a null
            Error_Handler::logError("The PID ".$this->pid." does not have an display id (FezMD->xdis_id). This object is currently in an erroneous state.",__FILE__,__LINE__);
            return null;
        }
        return $this->details[$xsdmf_id];
    }

    function getXSDMF_ID_ByElement($xsdmf_element)
    {
    	$this->getDisplay();
        $this->display->getXSD_HTML_Match();
    	return $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID($xsdmf_element);
    }

    /**
     * getDetailsByXSDMF_element
     *
     * Returns the value of an element in a datastream addressed by element
     *
     * @param string $xsdmf_element - The path to the XML element in a datastream.
     *      Use XSD_HTML_Match::escapeXPath to convert an xpath - /oai_dc:dc/dc:title to an xsdmf_element string !dc:title
     * @returns mixed - Array of values or single value for each element match in XML tree
     */
    function getDetailsByXSDMF_element($xsdmf_element)
    {
        $this->getDetails();

        $this->getXmlDisplayId();
        if (!empty($this->xdis_id)) {
          $xsdmf_id = $this->display->xsd_html_match->getXSDMF_IDByXDIS_ID($xsdmf_element);
        return @$this->details[$xsdmf_id];
        } else {
            // if it has no xdis id (display id) log an error and return a null
            Error_Handler::logError("The PID ".$this->pid." does not have an display id (FezMD->xdis_id). This object is currently in an erroneous state.",__FILE__,__LINE__);
            return null;
        }
    }

    function getDetailsByXSDMF_ID($xsdmf_id)
    {
        $this->getDetails();
        return @$this->details[$xsdmf_id];
    }

    /**
     * getXSDMFDetailsByElement
     *
     * Returns XSDMF values to describe how the element should be treated in a HTML form or display
     *
     * @param string $xsdmf_element - The path to the XML element in a datastream.
     *      Use XSD_HTML_Match::escapeXPath to convert an xpath - /oai_dc:dc/dc:title to an xsdmf_element string !dc:title
     * @returns array - Keypairs from the XSDMF table for the element on this record and record type to
     *      describe how the element should be treated in a HTML form or display.
     */
    function getXSDMFDetailsByElement($xsdmf_element)
    {
    	$this->getDisplay();
        $this->display->getXSD_HTML_Match();
        return $this->display->xsd_html_match->getDetailsByElement($xsdmf_element);
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
        return ($this->getRecordType() == 2) ? true : false;
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
        return ($this->getRecordType() == 1) ? true : false;
    }


    /**
     * function getParents()
     * getParents
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


    function getChildrenPids($clearcache=false, $searchKey='isMemberOf')
    {
		$pid = $this->pid;
    	$sek_title = Search_Key::makeSQLTableName($searchKey);
		$stmt = "SELECT ".APP_SQL_CACHE."
					m1.rek_".$sek_title."_pid
				 FROM
					" . APP_TABLE_PREFIX . "record_search_key_".$sek_title." m1
				 WHERE m1.rek_".$sek_title." = '".$pid."'";
		$res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        } else {
            return $res;
        }
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
    function getDatastreamContents($dsID, $filehandle=null) {
		return Fedora_API::callGetDatastreamContents($this->pid, $dsID, false, $filehandle);
    }

    function setIndexMatchingFields()
    {
        // careful what you do with the record object - don't want to use the index while reindexing
        $pid = $this->pid;
        $xdis_id = $this->getXmlDisplayId();
        if (!is_numeric($xdis_id)) {
            $xdis_id = XSD_Display::getXDIS_IDByTitle('Generic Document');
        }
        $display = new XSD_DisplayObject($xdis_id);
        $xsdmf_array = $display->getXSDMF_Values($pid);
        
        $searchKeyData = array();
        
        foreach ($xsdmf_array as $xsdmf_id => $xsdmf_value) {
        	$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
        	if ($xsdmf_details['xsdmf_sek_id'] != "") {
        		Record::removeIndexRecordByXSDMF_ID($pid,$xsdmf_id);
        		$sekDetails = Search_Key::getBasicDetails($xsdmf_details['xsdmf_sek_id']);
        		
                if ($sekDetails['sek_data_type'] == 'date') {
                	if(!empty($xsdmf_value)) {
	                    if (is_numeric($xsdmf_value) && strlen($xsdmf_value) == 4) {
	                        // It appears we've just been fed a year. We'll pad this, 
	                        // so it can be added to the index.
	                        $xsdmf_value = $xsdmf_value . "-01-01 00:00:00";
	                    } elseif (strlen($xsdmf_value) == 7) {
	                        // YYYY-MM. We could arguably write some better string inspection stuff here, 
	                        // but this will do for now.
	                        $xsdmf_value = $xsdmf_value . "-01 00:00:00";
	                    } else {
	                        // Looks like a regular fully-formed date.
							$xsdmf_value = strtotime($xsdmf_value);
	                        $date = new Date($xsdmf_value);
	                        $xsdmf_value = $date->format('%Y-%m-%d %T');
	                    }
                	} else {
                		$xsdmf_value = "NULL";
                	}
                }
                
                if(@empty($searchKeyData[$sekDetails['sek_relationship']][$sekDetails['sek_title_db']]['xsdmf_value'])) {
	        		
                	$searchKeyData[$sekDetails['sek_relationship']][$sekDetails['sek_title_db']] = array(
			        		  "xsdmf_id"        => $xsdmf_id,
			        		  "xsdmf_value"     => $xsdmf_value,
	        		);
	        		
                }
        	}
        }
        
        Record::updateSearchKeys($pid, $searchKeyData);
		Citation::updateCitationCache($pid, "");
    }

    /**
     * copyToNewPID
     * This makes a copy of the fedora object with the current PID to a new PID.  The getNextPID call on fedora is
     * used to get the new PID. All datastreams are extracted from the original object and reingested to the new object.
     * Premis history is not brought across, the first entry in the new premis history identifies the PID of the
     * source object.   The $new_xdis_id specifies a change of content model.  If $new_xdis_id is null, then the
     * xdis_id of the source object is used.  If $is_succession is true, the RELS-EXT will have a isSuccessor element
     * pointing back to the sourec object.
     * @param integer $new_xdis_id - optional new content model
     * @param boolean $is_succession - optional link back to original
     * @return string - the new PID for success, false for failure.  Calls Error_Handler::logError if there is a problem.
     */
    function copyToNewPID($new_xdis_id = null, $is_succession = false, $clone_attached_datastreams=false, $collection_pid=null)
    {
        if (empty($this->pid)) {
            return false;
        }
        if (empty($new_xdis_id)) {
        	$new_xdis_id = $this->getXmlDisplayIdUseIndex();
        }
        $pid = $this->pid;
        $new_pid = Fedora_API::getNextPID();
        // need to get hold of a copy of the fedora XML, and substitute the PIDs in it then ingest it.
        $xml_str = Fedora_API::getObjectXMLByPID($pid);
        $xml_str = str_replace($pid, $new_pid, $xml_str);  // change to new pid
        // strip off datastreams - we'll add them later.  This gets rid of the internal fedora audit datastream
        $doc = DOMDocument::loadXML($xml_str);
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('foxml','info:fedora/fedora-system:def/foxml#');
        $xpath->registerNamespace('fedoraxsi','http://www.w3.org/2001/XMLSchema-instance');
        $xpath->registerNamespace('audit','info:fedora/fedora-system:def/audit#');
        $nodes = $xpath->query('/foxml:digitalObject/foxml:datastream');
        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }
        $new_xml = $doc->saveXML();
        Fedora_API::callIngestObject($new_xml);

        $datastreams = Fedora_API::callGetDatastreams($pid); // need the full get datastreams to get the controlGroup etc
        if (empty($datastreams)) {
            Error_Handler::logError("The PID ".$pid." doesn't appear to be in the fedora repository - perhaps it was not ingested correctly.  " .
                    "Please let the Fez admin know so that the Fez index can be repaired.",__FILE__,__LINE__);
            return false;
        }

        // exclude these prefixes if we're not cloning the binaries
        $exclude_prefix = array('presmd','thumbnail','web','preview', 'stream');

        foreach ($datastreams as $ds_key => $ds_value) {
            if (!$clone_attached_datastreams) {
                // don't process derived datastreams if we're not copying the binaries
                if (in_array(substr($ds_value['ID'],0,strpos($ds_value['ID'],'_')), $exclude_prefix)) {
                    continue;
                }
            }
            switch ($ds_value['ID']) {
                case 'DC':
                    $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
                    Fedora_API::callModifyDatastreamByValue($new_pid, $ds_value['ID'], $ds_value['state'],
                        $ds_value['label'], $value, $ds_value['MIMEType'], "false");
//					if (!array_key_exists("MODS", $datastreams)) {
					if (!Misc::in_multi_array("MODS", $datastreams)) {
						// transform the DC into a MODS datastream and attach it
						$dc_to_mods_xsl = APP_INC_PATH . "xslt/dc_to_mods.xsl";
						$xsl_dom = DOMDocument::load($dc_to_mods_xsl);
						$dc_dom = DOMDocument::loadXML($value);
						// transform the DC to MODS with the XSLT
						$proc = new XSLTProcessor();
						$proc->importStyleSheet($xsl_dom);
						$transformResult = $proc->transformToXML($dc_dom);
	                    Fedora_API::getUploadLocation($new_pid, "MODS", $transformResult, "Metadata Object Description Schema", "text/xml", "X", "MODS");
					}
                break;
                case 'BookMD':
				break;

                case 'FezMD':
                    // let's fix up a few things in FezMD
                    $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
                    $doc = DOMDocument::loadXML($value);
                    XML_Helper::setElementNodeValue($doc, '/FezMD', 'created_date',
                        Date_API::getFedoraFormattedDateUTC());
                    XML_Helper::setElementNodeValue($doc, '/FezMD', 'updated_date',
                        Date_API::getFedoraFormattedDateUTC());
                    XML_Helper::setElementNodeValue($doc, '/FezMD', 'depositor', Auth::getUserID());
                    XML_Helper::setElementNodeValue($doc, '/FezMD', 'xdis_id', $new_xdis_id);
                    $value = $doc->saveXML();
                    Fedora_API::getUploadLocation($new_pid, $ds_value['ID'], $value, $ds_value['label'],
                            $ds_value['MIMEType'], $ds_value['controlGroup']);
                break;
                case 'RELS-EXT':
                    // set the successor thing in RELS-EXT
                    $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
                    $value = str_replace($pid, $new_pid, $value);
                    if ($is_succession || !empty($collection_pid)) {
                        $doc = DOMDocument::loadXML($value);
                        //    <rel:isDerivationOf rdf:resource="info:fedora/MSS:379"/>
                        if ($is_succession) {
                            $node = XML_Helper::getOrCreateElement($doc, '/rdf:RDF/rdf:description', 'rel:isDerivationOf',
                                 array('rdf'=>"http://www.w3.org/1999/02/22-rdf-syntax-ns#",
                                    'rel'=>"info:fedora/fedora-system:def/relations-external#"));
                            $node->setAttributeNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", 'resource', $pid);
                        }
                        if (!empty($collection_pid)) {
                            $node = XML_Helper::getOrCreateElement($doc, '/rdf:RDF/rdf:description', 'rel:isMemberOf',
                                 array('rdf'=>"http://www.w3.org/1999/02/22-rdf-syntax-ns#",
                                    'rel'=>"info:fedora/fedora-system:def/relations-external#"));
                            $node->setAttributeNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", 'resource', $collection_pid);
                        }
                        $value = $doc->saveXML();
                    }
                    Fedora_API::getUploadLocation($new_pid, $ds_value['ID'], $value, $ds_value['label'],
                            $ds_value['MIMEType'], $ds_value['controlGroup']);
                break;
                default:
                    if (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'X') {
                        $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
                        $value = str_replace($pid, $new_pid, $value);
                        Fedora_API::getUploadLocation($new_pid, $ds_value['ID'], $value, $ds_value['label'],
                            $ds_value['MIMEType'], $ds_value['controlGroup']);
                    } elseif (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'M'
                                && $clone_attached_datastreams) {
                        $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
                        Fedora_API::getUploadLocation($new_pid, $ds_value['ID'], $value, $ds_value['label'],
                            $ds_value['MIMEType'], $ds_value['controlGroup']);
                    } elseif (isset($ds_value['controlGroup']) && $ds_value['controlGroup'] == 'R'
                                && $clone_attached_datastreams) {
                        $value = Fedora_API::callGetDatastreamContents($pid, $ds_value['ID'], true);
                        Fedora_API::callAddDatastream($new_pid, $ds_value['ID'], $value, $ds_value['label'],
                            $ds_value['state'],$ds_value['MIMEType'], $ds_value['controlGroup']);
                    }
                break;
            }
        }
        Record::setIndexMatchingFields($new_pid);

        return $new_pid;
    }

    /**
     * Generate a string which is a citation for this record.  Uses a citation template.
     */
    function getCitation()
    {
        $details = $this->getDetails();
        $xsdmfs = $this->display->xsdmf_array;

        return Citation::renderCitation($this->xdis_id, $details, $xsdmfs);
    }
    /**
     * Mark the fedora state of the record as deleted.  This keeps the record around in case we want to undelete it
     * later. We tell the Fez indexer not to index Fedora Deleted objects.
     */
    function markAsDeleted()
    {
    	return Record::markAsDeleted($this->pid);
    }

    /**
     * Mark the fedora state of the record as active.  Also restores the fez index of the object.
     */
    function markAsActive($do_index = true)
    {
    	return Record::markAsActive($this->pid, $do_index);
    }
    
    function isDeleted()
    {
    	return Record::isDeleted($this->pid);
    }
    
   

    function getLock($context=self::CONTEXT_NONE, $extra_context=null)
    {
        return RecordLock::getLock($this->pid, Auth::getUserID(),$context,$extra_context);
    }

    function releaseLock()
    {
        return RecordLock::releaseLock($this->pid);
    }

    function getLockOwner()
    {
        return RecordLock::getOwner($this->pid);
    }

    function isLocked()
    {
        return RecordLock::getOwner($this->pid) > 0 ? true : false;
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
	var $depositor;
	var $assign_grp_id;
	var $assign_usr_id;
    var $file_downloads; //for statistics of file datastream downloads from eserv.php
    var $default_xdis_id = 5;
    var $status;



    function RecordObject($pid=null)
    {
        RecordGeneral::RecordGeneral($pid);
    }

    /**
     * getXmlDisplayId
     * Retrieve the display id for this record
     */
    function getObjectAdminMD() {
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
		if (isset($xdis_array['depositor'][0])) {
			$this->depositor = $xdis_array['depositor'][0];
		} else {
			$this->depositor = NULL;
		}
    	if (isset($xdis_array['grp_id'][0])) {
			$this->assign_grp_id = $xdis_array['grp_id'][0];
		} else {
			$this->assign_grp_id = NULL;
		}
		if (isset($xdis_array['usr_id'][0])) {
			if (!is_array($this->assign_usr_id)) {
				$this->assign_usr_id = array();
			}
			foreach ($xdis_array['usr_id'] as $assign_usr_id) {
				array_push($this->assign_usr_id, $assign_usr_id);
			}
		} else {
			$this->assign_usr_id = array();
		}
        if (isset($xdis_array['sta_id'][0])) {
            $this->status = $xdis_array['sta_id'][0];
        } else {
            $this->status = NULL;
        }


    }

    /**
     * updateAdminDatastream
     * Used to associate a display for this record
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
			Record::insertIndexMatchingField($this->pid, '', $xsdmf_id, $this->xdis_id);
		}
    }

    /**
     * fedoraInsertUpdate
     * Process a submitted record insert or update form
     *
     * @access  public
     * @return  void
     */
    function fedoraInsertUpdate($exclude_list=array(), $specify_list=array(), $params = array())
    {
        if (!empty($params)) {
            // dirty double hack as this function and all the ones it calls assumes this is
            // to do with a form submission
            $_POST = $params;
        }

        // If pid is null then we need to ingest the object as well
        // otherwise we are updating an existing object
        $ingestObject = false;
		$existingDatastreams = array();
        if (empty($this->pid)) {
            $this->pid = Fedora_API::getNextPID();
            $ingestObject = true;
			$this->created_date = Date_API::getFedoraFormattedDateUTC();
			$this->updated_date = $this->created_date;
			$this->depositor = Auth::getUserID();
			$this->assign_usr_id = array(Auth::getUserID());
			$existingDatastreams = array();
        } else {
			$existingDatastreams = Fedora_API::callGetDatastreams($this->pid);
			Misc::purgeExistingLinks($this->pid, $existingDatastreams);
			$this->getObjectAdminMD();
			if (empty($this->created_date)) {
				$this->created_date = Date_API::getFedoraFormattedDateUTC();
			}
			$this->updated_date = Date_API::getFedoraFormattedDateUTC();
			$this->getXmlDisplayId();
		}
        $pid = $this->pid;


        if (empty($this->xdis_id)) {
            $this->xdis_id = $_POST["xdis_id"];
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

		$xmlObj = Foxml::array_to_xml_instance($array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "", $indexArray, 0, $this->created_date, $this->updated_date, $this->depositor, $this->assign_usr_id, $this->assign_grp_id);

		$xmlObj .= "</".$xsd_element_prefix.$xsd_top_element_name.">";
		
		$datastreamTitles = $display->getDatastreamTitles($exclude_list, $specify_list);
        Record::insertXML($pid, compact('datastreamTitles', 'exclude_list', 'specify_list', 'xmlObj', 'indexArray', 'existingDatastreams', 'xdis_id'), $ingestObject);
        $this->clearDetails();  // force the details to be refreshed from fedora.
		return $pid;
    }

    function getIngestTrigger($mimetype)
    {
        $this->getXmlDisplayId();
        $trigger = WorkflowTrigger::getIngestTrigger($this->pid, $this->xdis_id, $mimetype);
        if (!$trigger) {
            $this->getParents();
			if (is_array($this->record_parents)) {
	            foreach ($this->record_parents as $ppid) {
	                $trigger = WorkflowTrigger::getIngestTrigger($ppid, $this->xdis_id, $mimetype);
	                if ($trigger) {
	                    break;
	                }
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
                    && (is_numeric(strpos($dsTitle['MIMEType'],"image/")) || is_numeric(strpos($dsTitle['MIMEType'],"video/")) || is_numeric(strpos($dsTitle['MIMEType'],"audio/")))
                    && !Misc::hasPrefix($dsIDName, 'preview_')
                    && !Misc::hasPrefix($dsIDName, 'web_')
                    && !Misc::hasPrefix($dsIDName, 'stream_')
                    && !Misc::hasPrefix($dsIDName, 'thumbnail_')
               )
            {
                // first extract the image and save temporary copy
                $urldata = APP_FEDORA_GET_URL."/".$pid."/".$dsIDName;
//              copy($urldata,APP_TEMP_DIR.$dsIDName);
				/*$urlReturn = Misc::ProcessURL($urldata);
				$handle = fopen(APP_TEMP_DIR.$dsIDName, "w");
				fwrite($handle, $urlReturn[0]);
				fclose($handle);*/
				
//				$urlReturn = Misc::ProcessURL($urldata);
				$handle = fopen(APP_TEMP_DIR.$dsIDName, "w");
				Misc::processURL($urldata, false, $handle);
				fclose($handle);
                // delete and re-ingest - need to do this because sometimes the object made it
                // into the repository even though it's dsID is illegal.
                Fedora_API::callPurgeDatastream($pid, $dsIDName);
                $new_dsID = Foxml::makeNCName($dsIDName);
                Fedora_API::getUploadLocationByLocalRef($pid, $new_dsID, APP_TEMP_DIR.$dsIDName, $dsIDName,
                        $dsTitle['MIMEType'], "M");
                // preservation metadata
                $presmd_check = Workflow::checkForPresMD($new_dsID);

                if ($presmd_check != false) {
                    // strip directory off the name
                    $pres_dsID = basename($presmd_check);
                    if (Fedora_API::datastreamExists($pid, $pres_dsID)) {
						//$presData = Misc::ProcessURL($presmd_check);
//						$xml = $presData[0];
						//($pid, $dsID, $dsLabel, $dsLocation=NULL, $mimetype) {
//						Fedora_API::callModifyDatastreamByReference($pid, $pres_dsID,
//                                "Preservation Metadata", $presmd_check, "text/xml");
//                        Fedora_API::callModifyDatastreamByValue($pid, $pres_dsID, "A",
//                                "Preservation Metadata", $xml, "text/xml", true);
						Fedora_API::callPurgeDatastream($pid, $pres_dsID);
                        Fedora_API::getUploadLocationByLocalRef($pid, $pres_dsID, $presmd_check, $presmd_check,
                                "text/xml", "M");

                    } else {
                        Fedora_API::getUploadLocationByLocalRef($pid, $pres_dsID, $presmd_check, $presmd_check,
                                "text/xml", "M");
                    }
                    if (is_file($presmd_check)) {
                        $deleteCommand = APP_DELETE_CMD." ".$presmd_check;
                        exec($deleteCommand);
                    }
                }
				Exiftool::saveExif($pid, $dsIDName);
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
    } // end of function
} // end of class


// benchmarking the included file (aka setup time)
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Record Class');
}
?>
