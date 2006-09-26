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
 * Class to handle the business logic related to the administration
 * of custom fields in the system.
 *
 * @version 1.0
 * @author Jo�o Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.xsd_relationship.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");

class XSD_Display
{

    /**
     * Method used to remove a given list of XSD Displays, cascading to all their child dependant XSD matchings.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        global $HTTP_POST_VARS;

        $items = @implode(", ", $HTTP_POST_VARS["items"]);
        $stmt = "DELETE FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id IN ($items)";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            // also remove any xsdmf's, sels and relationships that are connected to this display
            $stmt = "DELETE FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_loop_subelement
                     WHERE
                        xsdsel_xsdmf_id IN ( SELECT xsdmf_id FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_xdis_id IN (" . $items . "))";

            $GLOBALS["db_api"]->dbh->query($stmt);
            $stmt = "DELETE FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship
                     WHERE
                        xsdrel_xsdmf_id IN ( SELECT xsdmf_id FROM " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields where xsdmf_xdis_id IN (" . $items . "))";

            $GLOBALS["db_api"]->dbh->query($stmt);

            $stmt = "DELETE FROM
                        " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields
                     WHERE
                        xsdmf_xdis_id IN (" . $items . ")";
            $GLOBALS["db_api"]->dbh->query($stmt);

		  return true;
        }
    }

    /**
     * Method used to clone an existing display in the system.
     *
     * @access  public
	 * @param   integer $xdis_id The XSD Display ID to clone
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function cloneDisplay($xdis_id) {
        global $HTTP_POST_VARS;

		$master_res = XSD_Display::getDetails($xdis_id);

        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 (
                    xdis_title,
                    xdis_xsd_id,
                    xdis_version,
					xdis_object_type
                 ) VALUES (
                    'Clone of " .$master_res["xdis_title"] . "',
                    " .$master_res["xdis_xsd_id"] . ",
                    '1.0',
			        " .$master_res["xdis_object_type"] . "
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
		    $new_xdis_id = $GLOBALS["db_api"]->get_last_insert_id();
			 // get a list of all the non-sel-child elements (where xsdmf_xsdsel_id = null)
			 $xsdmf_res = XSD_HTML_Match::getNonSELChildListByDisplay($xdis_id);
			 foreach ($xsdmf_res as $xsdmf_row) {
				// insert the record
				XSD_HTML_Match::insertFromArray($new_xdis_id, $xsdmf_row);
				// get the new xsdmf_id
				$new_xsdmf_id = $GLOBALS["db_api"]->get_last_insert_id();
				// get the sels for the current row
				$xsd_sel_res = XSD_Loop_Subelement::getSimpleListByXSDMF($xsdmf_row['xsdmf_id']);
				// is the xsdmf a parent in the xsd_loop_subelement table? if so then create a clone entry for its sel entry
				if (count($xsd_sel_res) > 0) {
					foreach ($xsd_sel_res as $xsd_sel_row) {
						XSD_Loop_Subelement::insertFromArray($new_xsdmf_id, $xsd_sel_row);
						$new_sel_id = $GLOBALS["db_api"]->get_last_insert_id();
						$child_xsdmf_sel_res = XSD_HTML_Match::getSELChildListByDisplay($xdis_id, $xsd_sel_row['xsdsel_id']);
						// does the clone parent SEL record have any child sel elements? if so then insert clones for those too
						foreach ($child_xsdmf_sel_res as $child_xsdmf_sel_row) {
							XSD_HTML_Match::insertFromArraySEL($new_xdis_id, $new_sel_id, $child_xsdmf_sel_row);
							$new_child_xsdmf_id = $GLOBALS["db_api"]->get_last_insert_id();
							// do any of the children have xsd relationships? if so then insert them
							$xsdrel_res = XSD_Relationship::getSimpleListByXSDMF($child_xsdmf_sel_row['xsdmf_id']);
							foreach ($xsdrel_res as $xsdrel_row) {
								XSD_Relationship::insertFromArray($new_child_xsdmf_id, $xsdrel_row);
							}						 
						}
						// does the sel loop over an attribute loop candidate? if so then point to the new cloned versions xsdmf_id of it.
						if (is_numeric($xsd_sel_row['xsdsel_attribute_loop_xsdmf_id'])) {
							$new_attribute_loop_candidate = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($xsd_sel_row['xsdsel_attribute_loop_xsdmf_id'], $new_xdis_id);
							if (is_numeric($new_attribute_loop_candidate)) {
								XSD_Loop_Subelement::updateAttributeLoopCandidate($new_sel_id, $new_attribute_loop_candidate, $new_xdis_id);
							}
						}
						// does the sel have an indicator? if so then point to the new cloned versions xsdmf_id of it.
						if (is_numeric($xsd_sel_row['xsdsel_indicator_xsdmf_id'])) {
							$new_indicator = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($xsd_sel_row['xsdsel_indicator_xsdmf_id'], $new_xdis_id);
							if (is_numeric($new_indicator)) {
								XSD_Loop_Subelement::updateIndicator($new_sel_id, $new_indicator, $new_xdis_id);
							}
						}						

					}						
				}
				// does the clone parent SEL have any xsd relationships? if so insert them
				$xsdrel_res = XSD_Relationship::getSimpleListByXSDMF($xsdmf_row['xsdmf_id']);				
				foreach ($xsdrel_res as $xsdrel_row) {
					XSD_Relationship::insertFromArray($new_xsdmf_id, $xsdrel_row);
				}				
			 }
			$new_res = XSD_HTML_Match::getList($new_xdis_id);			 
			// after everything has been inserted check any id refs that need pointing to a new xsdmf id
			foreach ($new_res as $new_row) {
				// is the clone an xsdmf id reference of an xsd display that was the old xsd display (rather than an external one) then we need to make it point to the new version of the xsdmf id in this new xsd display
				if (is_numeric($new_row['xsdmf_id_ref'])) {
					$old_id_ref_xdis_id =  XSD_HTML_Match::getXDIS_IDByXSDMF_ID($new_row['xsdmf_id_ref']);
					if ($old_id_ref_xdis_id == $xdis_id) { // if the old one refered to an xsdmfid in its own display than make the new one refer to its new display id version of that xsdmf id as well
						$new_xsdmf_id_ref = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($new_row['xsdmf_id_ref'], $new_xdis_id); //what is the new display version of this old id ref
						if (is_numeric($new_xsdmf_id_ref)) {
							XSD_HTML_Match::updateXSDMF_ID_REF($new_row['xsdmf_id'], $new_xsdmf_id_ref, $new_xdis_id); //save it
						}
					}
				}
				if (is_numeric($new_row['xsdmf_asuggest_xsdmf_id'])) {
					$old_xdis_id =  XSD_HTML_Match::getXDIS_IDByXSDMF_ID($new_row['xsdmf_asuggest_xsdmf_id']);
					if ($old_xdis_id == $xdis_id) { // if the old one refered to an xsdmfid in its own display than make the new one refer to its new display id version of that xsdmf id as well
						$new_xsdmf_id_asuggest = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($new_row['xsdmf_asuggest_xsdmf_id'], $new_xdis_id); //what is the new display version of this old xsdmf id
						if (is_numeric($new_xsdmf_id_asuggest)) {
							XSD_HTML_Match::updateAuthorSuggestTarget($new_row['xsdmf_id'], $new_xsdmf_id_asuggest, $new_xdis_id); //save it
						}
					}
				}				
				if (is_numeric($new_row['xsdmf_org_fill_xsdmf_id'])) {
					$old_xdis_id =  XSD_HTML_Match::getXDIS_IDByXSDMF_ID($new_row['xsdmf_org_fill_xsdmf_id']);
					if ($old_xdis_id == $xdis_id) { // if the old one refered to an xsdmfid in its own display than make the new one refer to its new display id version of that xsdmf id as well
						$new_xsdmf_id_org_fill = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($new_row['xsdmf_org_fill_xsdmf_id'], $new_xdis_id); //what is the new display version of this old xsdmf id
						if (is_numeric($new_xsdmf_id_org_fill)) {
							XSD_HTML_Match::updateOrgFillTarget($new_row['xsdmf_id'], $new_xsdmf_id_org_fill, $new_xdis_id); //save it
						}
					}
				}				
				if (is_numeric($new_row['xsdmf_parent_option_child_xsdmf_id'])) {
					$old_xdis_id =  XSD_HTML_Match::getXDIS_IDByXSDMF_ID($new_row['xsdmf_parent_option_child_xsdmf_id']);
					if ($old_xdis_id == $xdis_id) { // if the old one refered to an xsdmfid in its own display than make the new one refer to its new display id version of that xsdmf id as well
						$new_xsdmf_id_parent_option_child = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($new_row['xsdmf_parent_option_child_xsdmf_id'], $new_xdis_id); //what is the new display version of this old xsdmf id
						if (is_numeric($new_xsdmf_id_parent_option_child)) {
							XSD_HTML_Match::updateParentOptionTarget($new_row['xsdmf_id'], $new_xsdmf_id_parent_option_child, $new_xdis_id); //save it
						}
					}
				}
				if (is_numeric($new_row['xsdmf_attached_xsdmf_id'])) {
					$new_xsdmf_id_attached = XSD_HTML_Match::getXSDMF_IDByOriginalXSDMF_ID($new_row['xsdmf_attached_xsdmf_id'], $new_xdis_id); //what is the new display version of this old xsdmf id
					if (is_numeric($new_xsdmf_id_attached)) {
						XSD_HTML_Match::updateAttachedTarget($new_row['xsdmf_id'], $new_xsdmf_id_attached); //save it
					}										
				}				
			}
			 
			 			 
			 return 1; 
        }
    }


    /**
     * Method used to add a new XSD Display to the system.
     *
     * @access  public
	 * @param   integer $xsd_id The XSD ID the display will be based on.
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert($xsd_id, $params=array())
    {
    	if (empty($params)) {
            $params = &$_POST;
        }
		
        $stmt = "INSERT INTO
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 (
                    xdis_title,
                    xdis_xsd_id,
                    xdis_version,
                    xdis_object_type
                 ) VALUES (
                    '" . Misc::escapeString($params["xdis_title"]) . "',
                    $xsd_id,
                    '" . Misc::escapeString($params["xdis_version"]) . "',
                    " .$params["xdis_object_type"] . "
                 )";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return $GLOBALS['db_api']->get_last_insert_id();
        }
    }

    /**
     * Method used to update a XSD Display in the system.
     *
     * @access  public
	 * @param   integer $xdis_id The XSD Display ID to clone	 
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($xdis_id)
    {
        global $HTTP_POST_VARS;
		
        $stmt = "UPDATE
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 SET 
                    xdis_title = '" . Misc::escapeString($HTTP_POST_VARS["xdis_title"]) . "',
                    xdis_version = '" . Misc::escapeString($HTTP_POST_VARS["xdis_version"]) . "',
					xdis_object_type = " .$HTTP_POST_VARS["xdis_object_type"] . "
                 WHERE xdis_id = $xdis_id";

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }


    /**
     * Method used to get the list of XSD Displays for a given XSD.
     *
     * @access  public
	 * @param   integer $xsd_id The XSD ID to search the list for. 
     * @param   string $where extra SQL on the where clause
     * @return  array The list of XSD Displays
     */
    function getList($xsd_id, $where = '')
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_xsd_id = $xsd_id $where
                 ORDER BY
                    xdis_title ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the associative list of XSD Displays available in the 
     * system.
     *
     * @access  public
     * @return  array The associative list of XSD Displays
     */
    function getAssocList()
    {
        $stmt = "SELECT
                    xdis_id,
					concat(xdis_title, ' Version ', xdis_version) as xdis_desc
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 ORDER BY
                    xdis_title, xdis_version ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the associative list of document types available in the 
     * system.
     *
     * @access  public
     * @return  array The list
     */
    function getAssocListCollectionDocTypes()
    {
        $stmt = "SELECT
                    xdis_id,
					concat(xdis_title, ' Version ', xdis_version) as xdis_desc
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display 
				 WHERE xdis_object_type = 2				 
                 ORDER BY
                    xdis_title, xdis_version ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the associative list of document types available in the 
     * system.
     *
     * @access  public
	 * @param   integer $ret_id The Object Type ID to search the list for. 	 
     * @return  array The list 
     */
    function getAssocListByObjectType($ret_id)
    {
        $stmt = "SELECT
                    xdis_id,
					concat(xdis_title, ' Version ', xdis_version) as xdis_desc
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display 
				 WHERE xdis_object_type = $ret_id			 
                 ORDER BY
                    xdis_title, xdis_version ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the associative list of document types available in the 
     * system.
     *
     * @access  public
     * @return  array The list
     */
    function getAssocListDocTypes()
    {
        $stmt = "SELECT
                    xdis_id,
					concat(xdis_title, ' Version ', xdis_version) as xdis_desc
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
				 WHERE xdis_object_type = 3					 
                 ORDER BY
                    xdis_title, xdis_version ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the associative list of document types available in the 
     * system.
     *
     * @access  public
     * @return  array The list
     */
    function getAssocListDocTypesAll()
    {
        $stmt = "SELECT
                    xdis_id,
					concat(xdis_title, ' Version ', xdis_version) as xdis_desc
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
				 WHERE xdis_object_type != 4			 
                 ORDER BY
                    xdis_title, xdis_version ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the parent XSD ID of an XSD Display
     *
     * @access  public
	 * @param   integer $xdis_id The XSD Display ID 
     * @return  array The custom field details
     */
    function getParentXSDID($xdis_id)
    {
        $stmt = "SELECT
                    xdis_xsd_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id=$xdis_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the XSD Display ID of a XSD Display title
     *
     * @access  public
	 * @param   string $xdis_title The XSD title to search by.
     * @return  integer $res the xdis_id
     */
    function getID($xdis_title)
    {
		static $returns;

        if (isset($returns[$xdis_title])) {
            return $returns[$xdis_title];
        }
        $stmt = "SELECT
                   xdis_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_title = '".$xdis_title."'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			$returns[$xdis_title] = $res;
            return $res;
        }
    }

    /**
     * Method used to get the XSD Display ID of a XSD Display title related to another XSD Display
     *
     * @access  public
	 * @param   string $xsdsel_title The XSD sublooping element title to search by.
	 * @param   string $related_xdis_id The XSD display ID this one must be related to
     * @return  integer $res the xdis_id
     */
    function getIDInRelationship($xdis_title, $related_xdis_id)
    {
        $stmt = "SELECT
                   d1.xdis_id 
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display d1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_relationship r1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields x1,
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display_matchfields s1
                 WHERE
				    r1.xsdrel_xsdmf_id = x1.xsdmf_id AND x1.xsdmf_xdis_id = $related_xdis_id AND s1.xsdsel_id = x1.xsdmf_xsdsel_id and
                    s1.xsdsel_title = '".$xdis_title."'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the XSD Display IDs of a list of XSD Display titles
     *
     * @access  public
	 * @param   integer $xdis_titles The XSD titles to search by.
     * @return  array $res An array of IDs 
     */
    function getIDs($xdis_titles)
    {
        $stmt = "SELECT
                   xdis_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_title in ('".implode("','", $xdis_titles)."')";
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the XSD Display title when given a 
     *
     * @access  public
	 * @param   integer $xdis_id The XSD ID to search by.
     * @return  array $res The title 
     */
    function getTitle($xdis_id)
    {
        $stmt = "SELECT
                   xdis_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id = $xdis_id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the XSD Display ID with a given title
     *
     * @access  public
	 * @param   integer $xdis_title The XSD title to search by.
     * @return  array $res The xdis_id 
     */
    function getXDIS_IDByTitle($xdis_title)
    {
        $stmt = "SELECT
                   xdis_id
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_title = '$xdis_title'";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

	
    /**
     * Method used to get the maximum XSD Display ID
     *
     * @access  public
     * @return  array The XSD Display max id
     */
    function getMaxID()
    {
        $stmt = "SELECT
                    max(xdis_id)
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the details of a specific XSD Display.
     *
     * @access  public
	 * @param   integer $xdis_id The XSD Display ID 
     * @return  array The details of the XSD Display 
     */
    function getDetails($xdis_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "xsd_display
                 WHERE
                    xdis_id=$xdis_id";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
    
    function exportDisplays(&$xnode, $xsd_id)
    {
    	$list = XSD_Display::getList($xsd_id);
        foreach ($list as $item) {
            $xdis = $xnode->ownerDocument->createElement('display');
            $xdis->setAttribute('xdis_id', $item['xdis_id']);
            $xdis->setAttribute('xdis_title', $item['xdis_title']);
            $xdis->setAttribute('xdis_version', $item['xdis_version']);
            $xdis->setAttribute('xdis_object_type', $item['xdis_object_type']);
            XSD_HTML_Match::exportMatchFields($xdis, $item['xdis_id']);
            $xnode->appendChild($xdis);
        }
    }
    
    /**
     * Need two passes, first pass inserts everything
     * Second pass needs to correct links in pretty much all the tables.
     * A good way to do this would be to keep tables of mapped ids, at the end we go through the tables and
     * make sure any inserted items point to the right things.  NOTE: queries must ensure that only inserted 
     * items are updated - we don't want to change exisiting items to point to new items by accident 
     */
    function importDisplays($xdoc, $xsd_id, &$maps, &$feedback)
    {
    	$xpath = new DOMXPath($xdoc->ownerDocument);
        $xdisplays = $xpath->query('display', $xdoc);
        foreach ($xdisplays as $xdis) {
            $title = Misc::escapeString($xdis->getAttribute('xdis_title'));
            $version = $xdis->getAttribute('xdis_version');
            if (!is_numeric($version)) {
            	$feedback[] = "Not importing Display $title $version - xdis_version must be a number";
                continue;
            }
            $list = XSD_Display::getList($xsd_id,"AND xdis_title='$title'");
            if (!empty($list)) {
            	foreach($list as $exist_item) {
            		if (floatval($exist_item['xdis_version']) > floatval($version)) {
            			$do_import = false;
                        $feedback[] =  "Not importing Display $title $version";
                        break;
            		} elseif (floatval($exist_item['xdis_version']) == floatval($version)) {
                        $do_import = false;
                        $maps['xdis_map'][$xdis->getAttribute('xdis_id')] = $exist_item['xdis_id'];
                        $feedback[] = "Not importing Display $title $version";
                        break;
                    }
            	}
            } else {
            	$do_import = true;
            }
            if ($do_import) {
            	$feedback[] =  "Importing Display $title";
                $params = array(
                    'xdis_title' => $xdis->getAttribute('xdis_title'),
                    'xdis_version' => $xdis->getAttribute('xdis_version'),
                    'xdis_object_type' => $xdis->getAttribute('xdis_object_type'),
                );
                $xdis_id = XSD_Display::insert($xsd_id, $params);
                $maps['xdis_map'][$xdis->getAttribute('xdis_id')] = $xdis_id;
                XSD_HTML_Match::importMatchFields($xdis, $xdis_id, $maps);
            }
        }
    }
    
    
}

/**
 * XSD_DisplayObject
 * Manage access to the display tables in the database.
 */
class XSD_DisplayObject
{
    var $xdis_id;
    var $matchfields;
    var $retrieved_mf = false;
    var $xsd_html_match;

    /**
     * XSD_DisplayObject
     * Instantiate with a display id
	 * 
     * @access  public
     * @param   integer $xdis_id The XSD Display of the object
     * @return  void	 
     */
    function XSD_DisplayObject($xdis_id)
    {
        $this->xdis_id = $xdis_id;
    }

    /**
     * refresh
     * Clear the local copies of query results.  Use to make the object requery the database if it has changed.
	 * 
     * @access  public
     * @return  void	 
     */
    function refresh()
    {
        $this->retrieved_mf = false;
        $this->xsdmf_array = array();
    }

    /**
     * getMatchFieldsList
     * Get the list of fields that can be matched for this display.
	 * 
     * @access  public
     * @param   array optional $exclude_list The list of datastream IDs to exclude, takes preference over the specify list
     * @param   array optional $specify_list The list of datastream IDs to specify 
     * @return  array $res The list of fields that can be matched by the display 
     */ 
    function getMatchFieldsList($exclude_list=array(), $specify_list=array())
    {
        if ($this->retrieved_mf) {
            return $this->matchfields;
        }
        $res = XSD_HTML_Match::getListByDisplay($this->xdis_id, $exclude_list, $specify_list);
        $this->retrieved_mf = true;
        $this->matchfields = $res;
        return $res;
    }

    /**
     * getXsdAsReferencedArray
     * Converts an XSD specification file to an array  
	 * 
     * @access  public
     * @return  array An array of XSD details
     */
    function getXsdAsReferencedArray()
    {
        $xdis_id = $this->xdis_id;
		$xsd_id = XSD_Display::getParentXSDID($xdis_id);
		$xsd_details = Doc_Type_XSD::getDetails($xsd_id);
		$xsd_element_prefix = $xsd_details['xsd_element_prefix'];
		$xsd_top_element_name = $xsd_details['xsd_top_element_name'];
		$xsd_extra_ns_prefixes = explode(",", $xsd_details['xsd_extra_ns_prefixes']); 
		$xsd_str = Doc_Type_XSD::getXSDSource($xsd_id);
		$xsd_str = $xsd_str[0]['xsd_file'];

		$xsd = new DomDocument();
		$xsd->loadXML($xsd_str);

		if ($xsd_element_prefix != "") {
			$xsd_element_prefix .= ":";
		}
		$xml_schema = Misc::getSchemaAttributes($xsd, $xsd_top_element_name, $xsd_element_prefix, $xsd_extra_ns_prefixes); // for the namespace uris etc
		$array_ptr = array();
		Misc::dom_xsd_to_referenced_array($xsd, $xsd_top_element_name, &$array_ptr, "", "", $xsd);
        return array($array_ptr,$xsd_element_prefix, $xsd_top_element_name, $xml_schema);
    }

    /**
     * getDatastreamTitles
     * Get the datastreams that are used with this display.
	 * 
     * @access  public
     * @return  array A list of datastream titles used with the display
     */ 
    function getDatastreamTitles($exclude_list=array(), $specify_list=array())
    {
		return XSD_Loop_Subelement::getDatastreamTitles($this->xdis_id,$exclude_list, $specify_list);
    }

    /**
     * getXSDMF_Values
     * Return a list of match fields with the values from the datastream for the record with the
     * given pid.
	 * 
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @return  array The list of match fields with the values from the datastream	 
     */  
    function getXSDMF_Values($pid)
    {
        $this->processXSDMF($pid); 
        return $this->xsdmf_array[$pid];
    }

    function getXSD_HTML_Match()
    {
        if (!$this->xsd_html_match) {
            $xdis_list = XSD_Relationship::getListByXDIS($this->xdis_id);
            array_push($xdis_list, array("0" => $this->xdis_id));
            $xdis_str = Misc::sql_array_to_string($xdis_list);
            $this->xsd_html_match = new XSD_HTML_MatchObject($xdis_str);
        }
        return $this->xsd_html_match;
    }

    /**
     * processXSDMF
     * Get the values from elements in the datastreams that match against the match fields
     * for this display
	 * 
     * @access  public
     * @param   string $pid The persistent identifier of the record
     * @return  void	 
     */ 
    function processXSDMF($pid) 
    {
        if (!isset($this->xsdmf_array[$pid])) {
            $this->xsdmf_array[$pid] = array();
            $this->xsdmf_current = &$this->xsdmf_array[$pid];
            $this->getXSD_HTML_Match();
            // Find datastreams that may be used by this display
            $datastreamTitles = $this->getDatastreamTitles();
//			$datastreams = Fedora_API::callGetDatastreams($pid);
			$datastreams = Fedora_API::callListDatastreams($pid);			
			foreach ($datastreams as $ds_key => $ds_value) {
				// get the matchfields for the FezACML of the datastream if any exists
				if ($ds_value['controlGroup'] == 'M') {
					$FezACML_xdis_id = XSD_Display::getID('FezACML for Datastreams');
					$FezACML_DS_name = "FezACML_".$ds_value['ID'].".xml";
					if (Fedora_API::datastreamExistsInArray($datastreams, $FezACML_DS_name)) {
						$FezACML_DS = Fedora_API::callGetDatastreamDissemination($pid, $FezACML_DS_name);						
						if (isset($FezACML_DS['stream'])) {
							$save_xdis_str = "";
							$save_xdis_str = $this->xsd_html_match->xdis_str;
							$this->xsd_html_match->set_xdis_str($FezACML_xdis_id);
							$this->processXSDMFDatastream($FezACML_DS['stream'], $FezACML_xdis_id);
							$this->xsd_html_match->xdis_str = $save_xdis_str;
							$this->xsd_html_match->gotMatchCols = false; // make sure it refreshes for the other xsd displays
						} 
					}
				}
			}
			
            foreach ($datastreamTitles as $dsValue) {
				// first check if the XSD Display datastream is a template for a file attachment or a link as these are handled differently
				if ($dsValue['xsdsel_title'] == "File_Attachment") {
					// get all the binary managed content datastream details and add an index record for each									
					$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_ID("!datastream!ID", $dsValue['xsdsel_id'], $dsValue['xsdmf_xdis_id']);
					
					foreach ($datastreams as $ds) {
						if ($ds['controlGroup'] == 'M') {
							if (!is_array(@$this->xsdmf_current[$xsdmf_id])) {
								$this->xsdmf_current[$xsdmf_id] = array();
							}
							array_push($this->xsdmf_current[$xsdmf_id], $ds['ID']); 
						}
					}
                } elseif ($dsValue['xsdsel_title'] == "DOI") {
                    // find the datastream for DOI and set it's value 
                    $xsdmf_id = $dsValue['xsdmf_id'];
				
                    $xsdmf_details = $this->xsd_html_match->getDetailsByXSDMF_ID($xsdmf_id);
				
                    foreach ($datastreams as $ds) {
                        if ($ds['controlGroup'] == 'R' && $ds['ID'] == 'DOI') {
                            $value = trim($ds['location']);
                            if (!empty($value) && strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
                                $value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $value);
                            }
                            $this->xsdmf_current[$xsdmf_id] = $value;
                        }
					}
				} else {
					// find out if this record has the xml based datastream 
					if (Fedora_API::datastreamExistsInArray($datastreams, $dsValue['xsdsel_title'])) {
						$DSResultArray = Fedora_API::callGetDatastreamDissemination($pid, $dsValue['xsdsel_title']);
						if (isset($DSResultArray['stream'])) {
							$xmlDatastream = $DSResultArray['stream'];
							// get the matchfields for the datastream (using the sub-display for this stream)						
							$this->processXSDMFDatastream($xmlDatastream, $dsValue['xsdmf_xdis_id']);							
						}
					}
				}
            }
        }
    }

   /**
     * processXSDMFDatastream
     * Find values for all the matchfields on a given Datastream and xdis_id
	 * 
     * @access  public
     * @return  void	 
     */
    function processXSDMFDatastream($xmlDatastream, $xsdmf_xdis_id) 
    {
        $xsd_id = XSD_Display::getParentXSDID($xsdmf_xdis_id);
        $xsd_details = Doc_Type_XSD::getDetails($xsd_id);
        $this->xsd_element_prefix = $xsd_details['xsd_element_prefix'];
        $this->xsd_top_element_name = $xsd_details['xsd_top_element_name'];
        $xmlnode = new DomDocument();
        @$xmlnode->loadXML($xmlDatastream);
        $cbdata = array('parentContent' => '', 'parent_key' => '');
        $this->mfcb_rootdone = false;
        Misc::XML_Walk($xmlnode, $this, 'matchFieldsCallback', $cbdata, $xmlnode);
//		print_r($this->xsdmf_array);
    }

    /**
      * matchFieldsCallback
      * Used by XML_Walk to recurse through an xsd and work out the match fields.
      *
      * @param array $cbdata - data that is passed to each callback but is part of the recursive data - i.e. it is 
      * not remembered when recursing out.  The record object itself stores data that should persist while recursing.
	  * 
      * @access  public
	  * @param DomNode $domNode The node of the dom document
	  * @param array $cbdata The XSD array to be filled
	  * @param string $context The callback context
	  * @param rootNode $domNode The root dom document
      * @return  array $cbdata The XSD array being filled.	 
      */
    function matchFieldsCallback($domNode, $cbdata, $context=NULL, $rootNode)
    {
        $clean_nodeName = Misc::strip_element_name($domNode->nodeName);
        $xsdmf_ptr = &$this->xsdmf_current;
        $xsdmf_id = NULL;
		$currentSEL = "";
        // look for the xsdmf_id		
        switch ($domNode->nodeType)
        {
            case XML_ELEMENT_NODE:
                switch ($context) {
                    case 'startopen':
                        // this is processed before we have walked the attributes for this element
                        // Store the current node name for use when called back for the attributes.
                        $cbdata['clean_nodeName'] = $clean_nodeName;
						$parentContent = $cbdata['parentContent'];
						if ((is_numeric(strpos(substr($parentContent, 0, 1), "!"))) || ($parentContent == "")) {
							$new_element = $parentContent."!".$clean_nodeName; 
						} else {
							$new_element = "!".$parentContent."!".$clean_nodeName; 
						}

						if (!is_numeric(@$cbdata['currentSEL'])) {	
							$xsdmf_id = $this->xsd_html_match->getXSDMF_IDByXDIS_IDAll($new_element);
							if (is_array($xsdmf_id)) {
								if (count($xsdmf_id) > 1) {
									foreach ($xsdmf_id as $row) {
										if ($row['xsdmf_html_input'] == 'xsd_loop_subelement' && is_numeric($row['xsdsel_indicator_xsdmf_id']) && $row['xsdsel_indicator_xsdmf_id'] != 0 && $row['xsdsel_indicator_value'] != "") {
											$indicator_xpath = $row['xsd_element_prefix'].":".ltrim(str_replace("!", "/".$row['xsd_element_prefix'].":", $row['indicator_element']), "/");
											$currentNodeLength = strlen($domNode->nodeName);
											$currentNodePos = strpos($indicator_xpath, $domNode->nodeName);
											$indicator_xpath = ".".substr($indicator_xpath, $currentNodePos + $currentNodeLength);
											$xpath = new DOMXPath($rootNode);
											$xpath->registerNamespace("mods", "http://www.loc.gov/mods/v3");
//											echo "DEBUG: ".$indicator_xpath."<br />";
											$indicatorNodes = $xpath->query($indicator_xpath, $domNode);
											if ($indicatorNodes->length > 0) {
												$indicatorValue = $indicatorNodes->item(0)->nodeValue; //should only ever be one search result in the array
												if ($indicatorValue == $row['xsdsel_indicator_value']) {
													$currentSEL = $row['indicator_xsdsel_id'];
												}
											} else { // search for attributes next
												$attribPos = strrpos($indicator_xpath, "/");
												if (is_numeric($attribPos)) {
													$attrib = substr($indicator_xpath, $attribPos+1);
													$indicator_xpath = substr($indicator_xpath, 0, $attribPos);													
													$attrib = "@".str_replace($row['xsd_element_prefix'].":", "", $attrib);
													$indicator_xpath .= "/".$attrib;
													$indicatorNodes = $xpath->query($indicator_xpath, $domNode);
													if ($indicatorNodes->length > 0) {
														$indicatorValue = $indicatorNodes->item(0)->nodeValue; //should only ever be one search result in the array
														if ($indicatorValue == $row['xsdsel_indicator_value']) {
															$currentSEL = $row['indicator_xsdsel_id'];
														}														
													} 
												}
											}												
										}
									}
									if (is_numeric($currentSEL)) {
										$cbdata['currentSEL'] = $currentSEL;
									}
								}
							}								
						}
						$xsdmf_id = NULL;
                        break;
                    case 'endopen':
                        // this is processed after we have walked the attributes for this element
                        {
                            $parentContent = $cbdata['parentContent'];
                            if ((is_numeric(strpos(substr($parentContent, 0, 1), "!"))) || ($parentContent == "")) {
                                $new_element = $parentContent."!".$clean_nodeName; 
                            } else {
                                $new_element = "!".$parentContent."!".$clean_nodeName; 
                            }
                            if ($cbdata['parent_key'] != "") { 
                                // if there are passed parent keys then use them in the search
                                $xsdmf_id = $this->xsd_html_match->getXSDMF_IDByParentKeyXDIS_ID($new_element, 
                                        $cbdata['parent_key']);		
							} elseif (is_numeric(@$cbdata['currentSEL'])) {						
								$xsdmf_id = $this->xsd_html_match->getXSDMF_IDBySELXDIS_ID($new_element, $cbdata['currentSEL']);
                            } else {
                                $xsdmf_id = $this->xsd_html_match->getXSDMF_IDByXDIS_IDAll($new_element);
								if (is_array($xsdmf_id)) {
									if (count($xsdmf_id) > 1) {
										foreach ($xsdmf_id as $row) {
											if ($row['xsdmf_html_input'] == 'xsd_loop_subelement' && is_numeric($row['xsdsel_indicator_xsdmf_id']) && $row['xsdsel_indicator_xsdmf_id'] != 0 && $row['xsdsel_indicator_value'] != "") {
												$indicator_xpath = $row['xsd_element_prefix'].":".ltrim(str_replace("!", "/".$row['xsd_element_prefix'].":", $row['indicator_element']), "/");
												$currentNodeLength = strlen($domNode->nodeName);
												$currentNodePos = strpos($indicator_xpath, $domNode->nodeName);
												$indicator_xpath = ".".substr($indicator_xpath, $currentNodePos + $currentNodeLength);
												$xpath = new DOMXPath($rootNode);
												$xpath->registerNamespace("mods", "http://www.loc.gov/mods/v3");
												$indicatorNodes = $xpath->query($indicator_xpath, $domNode);
												if ($indicatorNodes->length > 0) {
													$indicatorValue = $indicatorNodes->item(0)->nodeValue; //should only ever be one search result in the array
													if ($indicatorValue == $row['xsdsel_indicator_value']) {
														$currentSEL = $row['indicator_xsdsel_id'];
													}
												} else { // search for attributes next
													$attribPos = strrpos($indicator_xpath, "/");
													if (is_numeric($attribPos)) {
														$attrib = substr($indicator_xpath, $attribPos+1);
														$indicator_xpath = substr($indicator_xpath, 0, $attribPos);													
														$attrib = "@".str_replace($row['xsd_element_prefix'].":", "", $attrib);
														$indicator_xpath .= "/".$attrib;
														$indicatorNodes = $xpath->query($indicator_xpath, $domNode);
														if ($indicatorNodes->length > 0) {
															$indicatorValue = $indicatorNodes->item(0)->nodeValue; //should only ever be one search result in the array
															if ($indicatorValue == $row['xsdsel_indicator_value']) {
																$currentSEL = $row['indicator_xsdsel_id'];
															}														
														} 
													}
												}												
											}
										}
										if (is_numeric($currentSEL)) {
											$xsdmf_id = $this->xsd_html_match->getXSDMF_IDBySELXDIS_ID($new_element, $currentSEL);
										}
									} else {
										$xsdmf_id = @$xsdmf_id[0]['xsdmf_id'];
									}
								}								
                            }
                        }
                        break;
                    case 'close':
                        // this is processed after have walked the attributes and children for this element
                        break;
                }
                break;
            case XML_ATTRIBUTE_NODE:
                if ((is_numeric(strpos(substr($cbdata['parentContent'], 0, 1), "!"))) || ($cbdata['parentContent'] == "")) {
	                $new_element = "{$cbdata['parentContent']}!{$cbdata['clean_nodeName']}!$clean_nodeName";
				} else {				
	                $new_element = "!{$cbdata['parentContent']}!{$cbdata['clean_nodeName']}!$clean_nodeName";
				}

                // Is there a match field for this attribute?
                // look for key match on the attribute value first - this is where the matchfield needs the 
                // attribute to be set to a certain value to match.
                $xsdmf_id = $this->xsd_html_match->getXSDMF_IDByKeyXDIS_ID($new_element, $domNode->nodeValue); 
                if (empty($xsdmf_id)) {
                    // look for a straight attribute match
					if (is_numeric(@$cbdata['currentSEL'])) {
						$xsdmf_id = $this->xsd_html_match->getXSDMF_IDBySELXDIS_ID($new_element, $cbdata['currentSEL']);
					} else {
						$xsdmf_id = $this->xsd_html_match->getXSDMF_IDByXDIS_IDAll($new_element);
						if (is_array($xsdmf_id)) {
							if (count($xsdmf_id) > 1) {
								foreach ($xsdmf_id as $row) {
									if ($row['xsdmf_html_input'] == 'xsd_loop_subelement' && is_numeric($row['xsdsel_indicator_xsdmf_id']) && $row['xsdsel_indicator_xsdmf_id'] != 0 && $row['xsdsel_indicator_value'] != "") {
										$indicator_xpath = $row['xsd_element_prefix'].":".ltrim(str_replace("!", "/".$row['xsd_element_prefix'].":", $row['indicator_element']), "/");
										$currentNodeLength = strlen($domNode->nodeName);
										$currentNodePos = strpos($indicator_xpath, $domNode->nodeName);
										$indicator_xpath = ".".substr($indicator_xpath, $currentNodePos + $currentNodeLength);
										$xpath = new DOMXPath($rootNode);
										$xpath->registerNamespace("mods", "http://www.loc.gov/mods/v3");
										$indicatorNodes = $xpath->query($indicator_xpath, $domNode);
										if ($indicatorNodes->length > 0) {
											$indicatorValue = $indicatorNodes->item(0)->nodeValue; //should only ever be one search result in the array
											if ($indicatorValue == $row['xsdsel_indicator_value']) {
												$currentSEL = $row['indicator_xsdsel_id'];
											}
										} 												
									}
								}
								if (is_numeric($currentSEL)) {
									$xsdmf_id = $this->xsd_html_match->getXSDMF_IDBySELXDIS_ID($new_element, $currentSEL);
								}
							} else {
								$xsdmf_id = $xsdmf_id[0]['xsdmf_id'];
							}
						}								
					}
	                if (empty($xsdmf_id)) {
						// if still can't find it, try it further up the tree - eg for MODS name|ID looked for in name|namePart
		                $new_element = "!{$cbdata['parentContent']}!$clean_nodeName";
						$xsdmf_id = $this->xsd_html_match->getXSDMF_IDByXDIS_ID($new_element);						
					}
                }	
                break;
            default:
                break; 
        }
        if (is_numeric($xsdmf_id)) {
            // We have found a match!
            // Get the value for the match and store it in the result
            $xsdmf_details = $this->xsd_html_match->getDetailsByXSDMF_ID($xsdmf_id);
            if (strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
                $ptr_value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $domNode->nodeValue);
            } else {
                $ptr_value = $domNode->nodeValue;
            }
            // Store the matchfields value against the matchfield id in the result array.
            // If there's already a value for this match field, then make an array for the value.
            if (isset($xsdmf_ptr[$xsdmf_id])) {
                if (is_array($xsdmf_ptr[$xsdmf_id])) {
                    // add to the array of values
					
                    $xsdmf_ptr[$xsdmf_id][] = $ptr_value;
                } else {
                    // make an array from the single value
                    $xsdmf_ptr[$xsdmf_id] = array($xsdmf_ptr[$xsdmf_id], $ptr_value);
                }
            } else {
                // store the value
                $xsdmf_ptr[$xsdmf_id] = $ptr_value;
            }
        }
        if ((($domNode->nodeType == XML_ELEMENT_NODE) && ($context == 'endopen'))
               || $domNode->nodeType == XML_ATTRIBUTE_NODE) {
            // Store the parent key for key match fields.
            if (!empty($xsdmf_details)) {
                if (($xsdmf_details['xsdmf_is_key'] == 1) && ($xsdmf_details['xsdmf_key_match'] != '')) {
                    $cbdata['parent_key'] = $xsdmf_details['xsdmf_key_match'];
                }
            }			
            // Store the indicator sublooping element further down the tree.
            if ($currentSEL != '') {
	            $cbdata['currentSEL'] = $currentSEL;
            }			

        }
        if (($domNode->nodeType == XML_ELEMENT_NODE) && ($context == 'endopen')) {
            // update the parentContent match path
            if (!$this->mfcb_rootdone) {
                $cbdata['parentContent'] = "";
                $this->mfcb_rootdone = true;
            } else {
                if ($cbdata['parentContent'] != "") {
                    $cbdata['parentContent'] = $cbdata['parentContent']."!".$clean_nodeName;
                } else {
                    $cbdata['parentContent'] = $clean_nodeName;
                }
            }

        }
        return $cbdata;
    }

    function getTitle()
    {
        return XSD_Display::getTitle($this->xdis_id);
    }
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included XSD Display Class');
}
?>
