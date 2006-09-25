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
 * Class to handle controlled vocabularies.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");


class Controlled_Vocab
{

    /**
     * Method used to remove a given list of controlled vocabularies.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;
		// first delete all children
		// get all immediate children
        $items = $HTTP_POST_VARS["items"];
		if (!is_array($items)) { return false; }
		$all_items = $items;
		foreach ($items as $item) {
			$child_items = Controlled_Vocab::getAllTreeIDs($item);
			if (is_array($child_items)) {
				$all_items = array_merge($all_items, $child_items);
			}
		}
        $all_items = ltrim(Controlled_Vocab::implode_r(", ", $all_items), ", ");
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_id IN ($all_items)";
		Controlled_Vocab::deleteRelationship($all_items);
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }

    /**
     * Utility function to implode a multi-dimensional array recursively.
     *
     * @access  public
	 * @param string $glue The joining string for the result to glue the pieces together.
	 * @param array $pieces The array to implode.
     * @return string $out The resulting imploded string from the given multi-dimensional array.
     */	
	function implode_r ($glue, $pieces){
		$out = "";
		foreach ($pieces as $piece) {
			if (is_array ($piece)) {
				$out .= Controlled_Vocab::implode_r($glue, $piece); // recurse
			} else {
				$out .= $glue.$piece;
			}
		}	 
		return $out;
	}

    /**
     * Method using to delete a controlled vocabulary parent-child relationship in Fez.
     *
     * @access  public
	 * @param string $items The string comma separated list of CV ids to remove from parent or child relationships
     * @return boolean
     */		
	function deleteRelationship($items) {
        $stmt = "DELETE FROM 
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship
                 WHERE
                    cvr_parent_cvo_id IN ($items) OR cvr_child_cvo_id IN ($items)";

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
	
	}

    /**
     * Method used to add a new controlled vocabulary to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
        global $HTTP_POST_VARS;
		
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
                 (
                    cvo_title";
		if (is_numeric($HTTP_POST_VARS["cvo_external_id"])) {
			$stmt .= ", cvo_external_id";
		}
		$stmt .= "
                 ) VALUES (
                    '" . Misc::escapeString($HTTP_POST_VARS["cvo_title"]) . "'";
		if (is_numeric($HTTP_POST_VARS["cvo_external_id"])) {
            $stmt .=        "," . trim($HTTP_POST_VARS["cvo_external_id"]) . "";
		}
			$stmt .="
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			// get last db entered id
			$new_id = $GLOBALS["db_api"]->get_last_insert_id();
			if (is_numeric($HTTP_POST_VARS["parent_id"])) {
				Controlled_Vocab::associateParent($HTTP_POST_VARS["parent_id"], $new_id);
			}
			return 1;
        }
    }

    /**
     * Method used to add a new controlled vocabulary to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insertDirect($cvo_title, $cvo_external_id="", $parent_id="")
    {
		
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
                 (
                    cvo_title";
		if ($cvo_external_id != "") {
			$stmt .= ", cvo_external_id";
		}
        $stmt .= "     ) VALUES (
                '" . Misc::escapeString($cvo_title) . "'";
		if ($cvo_external_id != "") {
			$stmt .= "," . $cvo_external_id;
		}
        $stmt .= "					                    
             )";
		echo $stmt."\n";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			// get last db entered id
			$new_id = $GLOBALS["db_api"]->get_last_insert_id();
			if (is_numeric($parent_id)) {
				Controlled_Vocab::associateParent($parent_id, $new_id);
			}
			return 1;
        }
    }	

	/**
     * Method used to import a new controlled vocabulary to the system under a parent.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function import($parent_id, $xmlObj)
    {
        global $HTTP_POST_VARS;

		$xpath_record = $HTTP_POST_VARS["cvi_xpath_record"];
		$xpath_id = $HTTP_POST_VARS["cvi_xpath_id"];
		$xpath_title = $HTTP_POST_VARS["cvi_xpath_title"];
		$xpath_parent_id = $HTTP_POST_VARS["cvi_xpath_parent_id"];		
/*		echo "xpath_record = ".$xpath_record."\n";
		echo "xpath_id = ".$xpath_id."\n";				
		echo "xpath_title = ".$xpath_title."\n";
		echo "xpath_parent_id = ".$xpath_parent_id."\n";		
*/				
        $xmlDoc= new DomDocument();
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->loadXML($xmlObj);

        $xpath = new DOMXPath($xmlDoc);

        $recordNodes = $xpath->query($xpath_record);

        foreach ($recordNodes as $recordNode) {
			$record_id = "";
			if ($xpath_id != "") {
	            $id_fields = $xpath->query($xpath_id, $recordNode);
			
	            foreach ($id_fields as $id_field) {
	                if  ($record_id == "") {
	                    $record_id = $id_field->nodeValue;
	                }
	            }
			}
            $title_fields = $xpath->query($xpath_title, $recordNode);
			$record_title = "";
            foreach ($title_fields as $title_field) {
                if  ($record_title == "") {
                    $record_title = $title_field->nodeValue;
                }
            }
			$record_parent_id = "";
			if ($xpath_parent_id != "") {
				$parent_id_fields = $xpath->query($xpath_parent_id, $recordNode);			
	            foreach ($parent_id_fields as $parent_id_field) {
	                if  ($parent_id_field == "") {
	                    $record_parent_id = $parent_id_field->nodeValue;
	                }
	            }
			}
			if ($record_id != "" && $record_title != "") {
				if ($record_parent_id == "") {
					$record_parent_id = $parent_id;
				}
				Controlled_Vocab::insertDirect($record_title, $record_id, $record_parent_id);
			}
			
        }
    }
	
    /**
     * Method used to add a new controlled vocabulary parent-child relationship to the system.
     *
     * @access  public
	 * @param string $parent_id The parent ID to add to the relationship
	 * @param array $child_id The child ID to add to the relationship
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function associateParent($parent_id, $child_id)
    {
        global $HTTP_POST_VARS;
		
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship
                 (
                    cvr_parent_cvo_id,
                    cvr_child_cvo_id					
                 ) VALUES (
                    " .$parent_id. ",
                    " .$child_id. "					
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }
    /**
     * Method used to update details of a controlled vocabulary.
     *
     * @access  public
     * @param   integer $cvo_id The controlled vocabulary ID
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($cvo_id)
    {
        global $HTTP_POST_VARS;

        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
                 SET 
                    cvo_title = '" . Misc::escapeString($HTTP_POST_VARS["cvo_title"]) . "',
                    cvo_external_id = " . trim($HTTP_POST_VARS["cvo_external_id"]). ",
                    cvo_desc = '" . Misc::escapeString($HTTP_POST_VARS["cvo_desc"]) . "'
                 WHERE cvo_id = $cvo_id";

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }


    /**
     * Method used to get the title of a specific controlled vocabulary.
     *
     * @access  public
     * @param   integer $cvo_id The controlled vocabulary ID
     * @return  string The title of the controlled vocabulary
     */
    function getTitle($cvo_id)
    {
        $stmt = "SELECT
                    cvo_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_id=$cvo_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the list of controlled vocabularies available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of controlled vocabularies in an associative array (for drop down lists).
     */
    function getAssocList()
    {
        $stmt = "SELECT
                    cvo_id,
					cvo_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
			     WHERE cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship)
                 ORDER BY
                    cvo_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
	
    /**
     * Method used to get the list of controlled vocabularies available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of controlled vocabularies in an associative array (for drop down lists).
     */
    function getAssocListAll()
    {
        $stmt = "SELECT
                    cvo_id,
					cvo_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }	

   /**
     * Method used to get the list of controlled vocabularies available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of controlled vocabularies in an associative array (for drop down lists).
     */
    function getAssocListByID($id)	
    {
	// used by the xsd match forms
		if (!is_numeric($id)) {
			return array();
		}

        $stmt = "SELECT
                    cvo_id,
					cvo_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
				 WHERE cvo_id = $id";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }	

   /**
     * Method used to get the list of controlled vocabularies available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of controlled vocabularies in an associative array (for drop down lists).
     */
    function getListByID($id)
    {
        $stmt = "SELECT
                    cvo_id,
					cvo_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
				 WHERE cvo_id = $id";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }	

    /**
     * Method used to get the list of controlled vocabularies available in the 
     * system.
     *
     * @access  public
     * @return  array The list of controlled vocabularies 
     */
    function getList($parent_id=false)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab ";

		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship 
					     WHERE cvr_parent_cvo_id = ".$parent_id." AND cvr_child_cvo_id = cvo_id ";			
		} else {
			$stmt .= " WHERE cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship)";
		}
		$stmt .= "
                 ORDER BY
                    cvo_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
                return $res;
            }
        }
    }

    /**
     * Method used to get the list of controlled vocabularies available in the 
     * system.
     *
     * @access  public
     * @return  array The list of controlled vocabularies 
     */
    function getAssocListFullDisplay($parent_id=false, $indent="", $level=0, $level_limit=false)
    {
	
		if (is_numeric($level_limit)) {
			if ($level == $level_limit) {
				return array();
			}
		}
		$level++;
        $stmt = "SELECT
                    cvo_id,
					concat('".$indent."',cvo_title) as cvo_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab ";

		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship 
					     WHERE cvr_parent_cvo_id = ".$parent_id." AND cvr_child_cvo_id = cvo_id ";			
		} else {
			$stmt .= " WHERE cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship)";
		}
/*		$stmt .= "
                 ORDER BY
                    cvo_title ASC";
*/
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
				$newArray = array();
				$tempArray = array();
				if ($parent_id != false) {
					$indent .= "---------";
				}
				foreach ($res as $key => $data) {
					if ($parent_id != false) {
						$newArray[$key] = $data;
					}
					$tempArray = Controlled_Vocab::getAssocListFullDisplay($key, $indent, $level, $level_limit);					
					if (count($tempArray) > 0) {
						if ($parent_id == false) {
							$newArray['data'][$key] = Misc::array_merge_preserve(@$newArray[$key], $tempArray);
							$newArray['title'][$key] = $data;
						} else {
							$newArray = Misc::array_merge_preserve($newArray, $tempArray);
						}
					}
				}
				$res = $newArray;
                return $res;
            }
        }
    }



    /**
     * Method used to get the associative list of controlled vocabularies available in the 
     * system.
     *
     * @access  public
     * @return  array The list of controlled vocabularies 
     */
    function getParentAssocListFullDisplay($child_id, $indent="")
    {
	 if (empty($child_id)) {
            return array();
	 }
        $stmt = "SELECT
                    cvo_id,
					concat('".$indent."',cvo_title) as cvo_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab ";
			$stmt .=   "," . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship 
					     WHERE cvr_parent_cvo_id = cvo_id AND cvr_child_cvo_id = ".$child_id;			
		$stmt .= "
                 ORDER BY
                    cvo_title ASC";

        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
				$newArray = array();
				$tempArray = array();
				foreach ($res as $key => $data) {
					if ($child_id != false) {
						$newArray[$key] = $data;
					}
					$tempArray = Controlled_Vocab::getParentAssocListFullDisplay($key, $indent);					
					if (count($tempArray) > 0) {
						if ($child_id == false) {
							$newArray['data'][$key] = Misc::array_merge_preserve($tempArray, $newArray[$key]);
							$newArray['title'][$key] = $data;
						} else {
							$newArray = Misc::array_merge_preserve($tempArray, $newArray);
						}
					}
				}
				$res = $newArray;
                return $res;
            }
        }
    }



    /**
     * Method used to get the list of controlled vocabularies available in the 
     * system.
     *
     * @access  public
     * @return  array The list of controlled vocabularies 
     */
    function getParentListFullDisplay($child_id, $indent="")
    {
        $stmt = "SELECT
                    cvo_id,
					concat('".$indent."',cvo_title) as cvo_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab ";
			$stmt .=   "," . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship 
					     WHERE cvr_parent_cvo_id = cvo_id AND cvr_child_cvo_id = ".$child_id;			
		$stmt .= "
                 ORDER BY
                    cvo_title ASC";

        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
				$newArray = array();
				$tempArray = array();
				foreach ($res as $key => $data) {
					if ($child_id != false) {
						$newArray[$key] = $data;
					}
					$tempArray = Controlled_Vocab::getParentListFullDisplay($key, $indent);					
					if (count($tempArray) > 0) {
						if ($child_id == false) {
							$newArray['data'][$key] = array_merge($tempArray, $newArray[$key]);
							$newArray['title'][$key] = $data;
						} else {
							$newArray = array_merge($tempArray, $newArray);
						}
					}
				}
				$res = $newArray;
                return $res;
            }
        }
    }

    /**
     * Recursive function to get all the IDs in a CV tree (to be used in counts for entire CV parents including children).
     *
     * @access  public
	 * @param string $parent_id 
     * @return  array The list of controlled vocabularies 
     */
	function getAllTreeIDs($parent_id=false) {
        $stmt = "SELECT
                    cvo_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab ";
		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship 
						 WHERE cvr_parent_cvo_id = ".$parent_id." AND cvr_child_cvo_id = cvo_id ";			
		} else {
			$stmt .= " WHERE cvo_id not in (SELECT cvr_child_cvo_id from  " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab_relationship)";
		}
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            $newArray = array();
			foreach ($res as $row) {
				$tempArray = array();			
				$tempArray = Controlled_Vocab::getAllTreeIDs($row[0]);
				if (count($tempArray) > 0) {
					$newArray[$row[0]] = $tempArray;
				} else {
					$newArray[$row[0]] = $row[0];
				}
			}
			return $newArray;
		}
	}


    /**
     * Method used to get the details of a specific controlled vocabulary.
     *
     * @access  public
     * @param   integer $cvo_id The controlled vocabulary ID
     * @return  array The controlled vocabulary details
     */
    function getDetails($cvo_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "controlled_vocab
                 WHERE
                    cvo_id=$cvo_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Controlled Vocabulary Class');
}
?>
