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
 * Class to handle organisational structures.
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


class Org_Structure
{

    /**
     * Method used to remove a given list of organsational structures.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
		// first delete all children
		// get all immediate children
        $items = $_POST["items"];
		if (!is_array($items)) { return false; }
		$all_items = $items;
		foreach ($items as $item) {
			$child_items = Org_Structure::getAllTreeIDs($item);
			if (is_array($child_items)) {
				$all_items = array_merge($all_items, $child_items);
			}
		}
        $all_items = ltrim(Org_Structure::implode_r(", ", $all_items), ", ");
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 WHERE
                    org_id IN (".$all_items.")";
		Org_Structure::deleteRelationship($all_items);
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
				$out .= Org_Structure::implode_r($glue, $piece); // recurse
			} else {
				$out .= $glue.$piece;
			}
		}	 
		return $out;
	}

    /**
     * Method using to delete a organisational structure parent-child relationship in Fez.
     *
     * @access  public
	 * @param string $items The string comma separated list of org ids to remove from parent or child relationships
     * @return boolean
     */		
	function deleteRelationship($items) {
        $stmt = "DELETE FROM 
                    " . APP_TABLE_PREFIX . "org_structure_relationship
                 WHERE
                    orr_parent_org_id IN (".$items.") OR orr_child_org_id IN (".$items.")";

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
	
	}

    /**
     * Method used to add a new organisational structure to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "org_structure
                 (
                    org_title,
					org_desc,
					org_ext_table,
					org_image_filename
                 ) VALUES (
                    '" . Misc::escapeString($_POST["org_title"]) . "',
                    '" . Misc::escapeString($_POST["org_desc"]) . "',
                    '" . Misc::escapeString($_POST["org_ext_table"]) . "',
                    '" . Misc::escapeString($_POST["org_image_filename"]) . "'										
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			// get last db entered id
			$new_id = $GLOBALS["db_api"]->get_last_insert_id();
			Org_Structure::associateParent($_POST["parent_id"], $new_id);
			return 1;
        }
    }


    /**
     * Method used to add a new organisational structure parent-child relationship to the system.
     *
     * @access  public
	 * @param string $parent_id The parent ID to add to the relationship
	 * @param array $child_id The child ID to add to the relationship
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function associateParent($parent_id, $child_id)
    {
        // no need to associate null parent
        if (empty($parent_id)) {
            return -1;
        }

		
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "org_structure_relationship
                 (
                    orr_parent_org_id,
                    orr_child_org_id					
                 ) VALUES (
                    '" .$parent_id. "',
                    '" .$child_id. "'					
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
     * Method used to update details of a organisational structure.
     *
     * @access  public
     * @param   integer $org_id The organisational structure ID
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($org_id)
    {
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "org_structure
                 SET 
                    org_title = '" . Misc::escapeString($_POST["org_title"]) . "',
                    org_desc = '" . Misc::escapeString($_POST["org_desc"]) . "',
                    org_ext_table = '" . Misc::escapeString($_POST["org_ext_table"]) . "',
                    org_image_filename = '" . Misc::escapeString($_POST["org_image_filename"]) . "'
                 WHERE org_id = ".$org_id;

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }


    /**
     * Method used to get the title of a specific organisational structure.
     *
     * @access  public
     * @param   integer $org_id The organisational structure ID
     * @return  string The title of the organisational structure
     */
    function getTitle($org_id)
    {
        $stmt = "SELECT
                    org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 WHERE
                    org_id=".$org_id;
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the default org id of a user from their HR feed data
     *
     * @access  public
     * @param   integer $org_id The organisational structure ID
     * @return  string The title of the organisational structure
     */
    function getDefaultOrgIDByUsername($username)
    {
        $stmt = "SELECT
                    org_id
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 INNER JOIN hr_position_vw ON AOU = org_extdb_id
                 INNER JOIN " . APP_TABLE_PREFIX . "author ON WAMIKEY = aut_org_staff_id and aut_org_username = '".$username."'
                 WHERE org_extdb_name='HR'
                 OR org_extdb_name='RRTD'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of organsational structures available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of organsational structures in an associative array (for drop down lists).
     */
    function getAssocList()
    {
        $stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
			     WHERE org_id not in (SELECT orr_child_org_id from  " . APP_TABLE_PREFIX . "org_structure_relationship)
                 ORDER BY
                    org_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of organsational structures available in the 
     * system returned in an associative array for drop down lists. This method
	 * returns only those org units that are tagged as coming from HR.
     *
     * @access  public
     * @return  array The list of HR organsational structures in an associative array (for drop down lists).
     */
    function getAssocListHR()
    {
        $stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
			     WHERE org_id not in (SELECT orr_child_org_id from  " . APP_TABLE_PREFIX . "org_structure_relationship)
				 AND org_extdb_name = 'hr'
				 OR org_extdb_name = 'rrtd'
                 ORDER BY
                    org_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of organsational structures available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of organsational structures in an associative array (for drop down lists).
     */
    function getAssocListAll()
    {
        $stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 ORDER BY
                    org_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }	

   /**
     * Method used to get the list of organsational structures available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of organsational structures in an associative array (for drop down lists).
     */
    function getAssocListByID($id)	
    {
	// used by the xsd match forms
        $stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
				 WHERE org_id = ".$id;
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }	

   /**
     * Method used to get the list of organsational structures available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of organsational structures in an associative array (for drop down lists).
     */
    function getAssocListByLevel($org_level)	
    {
	// used by the xsd match forms
        $stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
				 WHERE org_ext_table = '".$org_level."'";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }	

   /**
     * Method used to get the list of organsational structures levels available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of organsational structure levels in an associative array (for drop down lists).
     */
    function getAssocListLevels()	
    {
	// used by the xsd match forms
        $stmt = "SELECT
                    distinct org_ext_table,
					org_ext_table
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }	



   /**
     * Method used to get the list of organsational structures available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of organsational structures in an associative array (for drop down lists).
     */
    function getListByID($id)
    {
        $stmt = "SELECT
                    org_id,
					org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
				 WHERE org_id = ".$id;
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }	


   /**
     * Method used to get the list of all organsational structures available in the 
     * system for drop down lists, with supplemental information.
     *
     * @access  public
     * @return  array The list of organsational structures (for drop down lists).
     */
    function getListAll()
    {
        $stmt = "SELECT
                    org_id,
                    org_title,
                    org_ext_table 
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 ORDER BY 
                    org_title";
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
     * Method used to get the list of organsational structures available in the 
     * system.
     *
     * @access  public
     * @return  array The list of organsational structures 
     */
    function getList($parent_id=false)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure ";

		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_TABLE_PREFIX . "org_structure_relationship 
					     WHERE orr_parent_org_id = ".$parent_id." AND orr_child_org_id = org_id ";			
		} else {
			$stmt .= " WHERE org_id not in (SELECT orr_child_org_id from  " . APP_TABLE_PREFIX . "org_structure_relationship)";
		}
		$stmt .= "
                 ORDER BY
                    org_title ASC";
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
     * Method used to get the list of organsational structures available in the 
     * system.
     *
     * @access  public
     * @return  array The list of organsational structures 
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
                    org_id,
					concat('".$indent."',org_title) as org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure ";

		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_TABLE_PREFIX . "org_structure_relationship 
					     WHERE orr_parent_org_id = ".$parent_id." AND orr_child_org_id = org_id ";			
		} else {
			$stmt .= " WHERE org_id not in (SELECT orr_child_org_id from  " . APP_TABLE_PREFIX . "org_structure_relationship)";
		}
/*		$stmt .= "
                 ORDER BY
                    org_title ASC";
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
					$tempArray = Org_Structure::getAssocListFullDisplay($key, $indent, $level, $level_limit);					
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
     * Method used to get the associative list of organsational structures available in the 
     * system.
     *
     * @access  public
     * @return  array The list of organsational structures 
     */
    function getParentAssocListFullDisplay($child_id, $indent="")
    {
        $stmt = "SELECT
                    org_id,
					concat('".$indent."',org_title) as org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure ";
			$stmt .=   "," . APP_TABLE_PREFIX . "org_structure_relationship 
					     WHERE orr_parent_org_id = org_id AND orr_child_org_id = ".$child_id;			
		$stmt .= "
                 ORDER BY
                    org_title ASC";

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
					$tempArray = Org_Structure::getParentAssocListFullDisplay($key, $indent);					
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
     * Method used to get the list of authors associated with organsational structures available in the 
     * system.
     *
     * @access  public
	 * @param string $org_id The organisation ID
     * @return  array The list of authors in the given organisation ID
     */
	function getAuthorsByOrgID($org_id) {
        $stmt = "SELECT distinct
                    aut_id,
                    concat_ws(', ',   aut_lname, aut_mname, aut_fname, aut_id) as aut_fullname
                 FROM
                    " . APP_TABLE_PREFIX . "author INNER JOIN ".
			            APP_SQL_DBNAME . "." . APP_TABLE_PREFIX . "author_org_structure ON (auo_org_id = ".$org_id." AND aut_id = auo_aut_id)
				 WHERE auo_assessed = 'Y'
                 ORDER BY
                    aut_fullname ASC";
                    //echo $stmt;
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }



	}

    /**
     * Method used to get the list of organsational structures available in the 
     * system.
     *
     * @access  public
     * @return  array The list of organsational structures 
     */
    function getParentListFullDisplay($child_id, $indent="")
    {
        $stmt = "SELECT
                    org_id,
					concat('".$indent."',org_title) as org_title
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure ";
			$stmt .=   "," . APP_TABLE_PREFIX . "org_structure_relationship 
					     WHERE orr_parent_org_id = org_id AND orr_child_org_id = ".$child_id;			
		$stmt .= "
                 ORDER BY
                    org_title ASC";

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
					$tempArray = Org_Structure::getParentListFullDisplay($key, $indent);					
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
     * @return  array The list of organsational structures 
     */
	function getAllTreeIDs($parent_id=false) {
        $stmt = "SELECT
                    org_id
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure ";
		if (is_numeric($parent_id)) {
			$stmt .=   "," . APP_TABLE_PREFIX . "org_structure_relationship 
						 WHERE orr_parent_org_id = ".$parent_id." AND orr_child_org_id = org_id ";			
		} else {
			$stmt .= " WHERE org_id not in (SELECT orr_child_org_id from  " . APP_TABLE_PREFIX . "org_structure_relationship)";
		}
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return array();
        } else {
            $newArray = array();
			foreach ($res as $row) {
				$tempArray = array();			
				$tempArray = Org_Structure::getAllTreeIDs($row[0]);
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
     * Method used to get the details of a specific organisational structure.
     *
     * @access  public
     * @param   integer $org_id The organisational structure ID
     * @return  array The organisational structure details
     */
    function getDetails($org_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "org_structure
                 WHERE
                    org_id=".$org_id;
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
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Organisational Structure Class');
}
?>
