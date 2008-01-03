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
 * Class to handle search keys.
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
include_once(APP_INC_PATH . "class.author.php");
include_once(APP_INC_PATH . "class.status.php");


class Search_Key
{

	
	function stripSearchKeys($options = array()) {

		$new_options = array();
		foreach ($options as $key => $value) {
			if (!is_numeric(strpos($key, "searchKey"))) {
				$new_options[$key] = $value;
			}
		}
		$new_options["searchKey_count"] = $options["searchKey_count"];
		return $new_options;
	}
	
	
	
    /**
     * Method used to remove a given list of search keys.
     *
     * @access  public
     * @return  boolean
     */
    function remove()
    {
        $items = @implode(", ", $_POST["items"]);
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_id IN (".$items.")";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }


    /**
     * Method used to add a new search key to the system.
     *
     * @access  public
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function insert()
    {
		if (@$_POST["sek_simple_used"]) {
			$sek_simple_used = 1;
		} else {
			$sek_simple_used = 0;
		}
		if (@$_POST["sek_adv_visible"]) {
			$sek_adv_visible = 1;
		} else {
			$sek_adv_visible = 0;
		}
		if (@$_POST["sek_myfez_visible"]) {
			$sek_myfez_visible = 1;
		} else {
			$sek_myfez_visible = 0;
		}
		
		
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "search_key
                 (
                    sek_title,
					sek_alt_title,
					sek_meta_header,
					sek_simple_used,
					sek_adv_visible,
					sek_myfez_visible,";
				if (is_numeric($_POST["sek_order"])) {
					$stmt .= " sek_order, ";
				}
				if (is_numeric($_POST["sek_relationship"])) {
					$stmt .= " sek_relationship, ";
				}				
		$stmt .= "
					sek_data_type,
					sek_html_input,
					sek_fez_variable,
					sek_lookup_function,
					sek_smarty_variable ";
				if (is_numeric($_POST["sek_cvo_id"])) {
					$stmt .= " ,sek_cvo_id ";
				}
		$stmt .= "				
                 ) VALUES (
                    '" . Misc::escapeString($_POST["sek_title"]) . "',
					'" . Misc::escapeString($_POST["sek_alt_title"]) . "',
					'" . Misc::escapeString($_POST["sek_meta_header"]) . "',
					" . $sek_simple_used .",
					" . $sek_adv_visible .",
					" . $sek_myfez_visible .",";
					if (is_numeric($_POST["sek_order"])) {
	                    $stmt .=  $_POST["sek_order"] . ",";
					}
					if (is_numeric($_POST["sek_relationship"])) {
	                    $stmt .=  $_POST["sek_relationship"] . ",";
					}					
					$stmt .= "
                    '" . Misc::escapeString($_POST["sek_data_type"]) . "',					
                    '" . Misc::escapeString($_POST["field_type"]) . "',					
                    '" . Misc::escapeString($_POST["sek_fez_variable"]) . "',
					'" . Misc::escapeString($_POST["sek_lookup_function"]) . "',					
                    '" . Misc::escapeString($_POST["sek_smarty_variable"]) . "'";
					if (is_numeric($_POST["sek_cvo_id"])) {
	                    $stmt .=  "," . $_POST["sek_cvo_id"];
					}
		$stmt .= "
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
     * Method used to update details of a search key.
     *
     * @access  public
     * @param   integer $sek_id The search key ID
     * @return  integer 1 if the insert worked, -1 otherwise
     */
    function update($sek_id)
    {
		if (@$_POST["sek_simple_used"]) {
			$sek_simple_used = 1;
		} else {
			$sek_simple_used = 0;
		}
		if (@$_POST["sek_adv_visible"]) {
			$sek_adv_visible = 1;
		} else {
			$sek_adv_visible = 0;
		}
		if (@$_POST["sek_myfez_visible"]) {
			$sek_myfez_visible = 1;
		} else {
			$sek_myfez_visible = 0;
		}
		
		
        $stmt = "UPDATE
                    " . APP_TABLE_PREFIX . "search_key
                 SET 
                    sek_title = '" . Misc::escapeString($_POST["sek_title"]) . "',
					sek_alt_title = '" . Misc::escapeString($_POST["sek_alt_title"]) . "',
                    sek_meta_header = '" . Misc::escapeString($_POST["sek_meta_header"]) . "',
					sek_simple_used = ".$sek_simple_used.",
					sek_myfez_visible = ".$sek_myfez_visible.",
					sek_adv_visible = ".$sek_adv_visible.",";
					if ($_POST["sek_order"]) {
						$stmt .= "sek_order = ".$_POST["sek_order"].",";
					}
					if ($_POST["sek_relationship"]) {
						$stmt .= "sek_relationship = ".$_POST["sek_relationship"].",";
					}
					$stmt .= "
                    sek_html_input = '" . Misc::escapeString($_POST["field_type"]) . "',
                    sek_smarty_variable = '" . Misc::escapeString($_POST["sek_smarty_variable"]) . "',
					sek_lookup_function = '" . Misc::escapeString($_POST["sek_lookup_function"]) . "',
					sek_data_type = '" . Misc::escapeString($_POST["sek_data_type"]) . "',
                    sek_fez_variable = '" . Misc::escapeString($_POST["sek_fez_variable"]) . "'";
					if (is_numeric($_POST["sek_cvo_id"])) {
						$stmt .= ",sek_cvo_id = ".$_POST["sek_cvo_id"];
					}
				$stmt .= "
                 WHERE sek_id = ".$sek_id;

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
			return 1;
        }
    }

    /**
     * Method used to get the ID of a specific search key by the title.
     *
     * @access  public
     * @param   integer $sek_title The search key title
     * @return  string The ID of the search key
     */
    function getID($sek_title)
    {
    	$stmt = "SELECT
                     sek_id
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_title='".$sek_title."'";
    	$res = $GLOBALS["db_api"]->dbh->getOne($stmt);

    	if (PEAR::isError($res)) {
    		Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
    		return '';
    	} else {
    		return $res;
    	}
    }

    /**
     * Method used to get the title of a specific search key.
     *
     * @access  public
     * @param   integer $sek_id The search key ID
     * @return  string The title of the search key
     */
    function getTitle($sek_id)
    {
        $stmt = "SELECT
                     IF(sek_alt_title <> '', sek_alt_title, sek_title)
                 FROM
                   " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_id=".$sek_id;
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the fez variable of a specific search key.
     *
     * @access  public
     * @param   integer $sek_id The search key ID
     * @return  string The fez variable of the search key
     */
    function getFezVariable($sek_id)
    {
        $stmt = "SELECT
                    sek_fez_variable
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_id=".$sek_id;
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);

        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return '';
        } else {
            return $res;
        }
    }    

    /**
     * Method used to get the max sek_id.
     *
     * @access  public
     * @return  array The search keys max sek id
     */
    function getMaxID()
    {
        $stmt = "SELECT
                    MAX(sek_id)
                 FROM
                    " . APP_TABLE_PREFIX . "search_key";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
    
    
    /**
     * Method used to get the list of search keys available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of search keys in an associative array (for drop down lists).
     */
    function getAssocList()
    {
        $stmt = "SELECT
                    sek_id,
					IF(sek_alt_title <> '', sek_alt_title, sek_title)
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 ORDER BY
                    sek_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }

    /**
     * Method used to get the list of search keys available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of search keys in an associative array (for drop down lists).
     */
    function getAssocListAdvanced($hide_unused=0)
    {
        $stmt = "SELECT
                    sek_id,
					IF(sek_alt_title <> '', sek_alt_title, sek_title)
                 FROM
                    " . APP_TABLE_PREFIX . "search_key ";
        if ($hide_unused == 1) {
        	$stmt .= " INNER JOIN " . APP_TABLE_PREFIX . "xsd_display_matchfields
                    on xsdmf_sek_id=sek_id ";
        }
        $stmt .= "
				 WHERE sek_adv_visible = 1                    
                 ORDER BY
                    sek_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
          return $res;
        }
    }	

    /**
     * Method used to get the list of search keys available in the 
     * system returned in an associative array for drop down lists.
     *
     * @access  public
     * @return  array The list of search keys in an associative array (for drop down lists).
     */
    function getListAdvanced()
    {
        $stmt = "SELECT
                    sek_id,
					IF(sek_alt_title <> '', sek_alt_title, sek_title),
					sek_fez_variable
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
				 WHERE sek_adv_visible = 1                    
                 ORDER BY
                    sek_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC); 
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
          for ($i = 0; $i < count($res); $i++) {
          	$res[$i]["field_options"] = Search_Key::getOptions($res[$i]["sek_smarty_variable"]);
          }	
          return $res;
        }
    }	
    
    
   
    
    
    /**
     * Method used to get the list of search key options associated
     * with a given search key ID.
     *          
     * @access  public
     * @param   integer $sek_smarty_variable The search key variable
     * @return  array The list of search key options
     */
    function getOptions($sek_smarty_variable)
    {    	
    	$return = array();
    	if (!empty($sek_smarty_variable)) {
    		eval("\$return = ". $sek_smarty_variable.";");
    		
    	}
    	return $return;
    		
    }    
    
    /**
     * Method used to get the list of search keys available in the 
     * system.
     *
     * @access  public
     * @return  array The list of search keys 
     */
    function getList()
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 ORDER BY
                    sek_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
/*	          for ($i = 0; $i < count($res); $i++) {
	          	$res[$i]["field_options"] = Search_Key::getOptions($res[$i]["sek_smarty_variable"]);
	          }*/	
	          return $res;	            	
            }
        }
    }

    /**
     * Method used to get the list of search keys available in the 
     * system.
     *
     * @access  public
     * @return  array The list of search keys 
     */
    function getAdvSearchList()
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
				 WHERE sek_adv_visible = 1
                 ORDER BY
                    sek_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
	          for ($i = 0; $i < count($res); $i++) {
	          	$res[$i]["field_options"] = Search_Key::getOptions($res[$i]["sek_smarty_variable"]);
	          }	
	          return $res;            	
            }
        }
    }

    /**
     * Method used to get the list of search keys available in the 
     * system for the my fez search page.
     *
     * @access  public
     * @return  array The list of search keys 
     */
    function getMyFezSearchList()
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
				 WHERE sek_myfez_visible = 1
                 ORDER BY
                    sek_order ASC";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            if (empty($res)) {
                return array();
            } else {
	          for ($i = 0; $i < count($res); $i++) {
	          	$res[$i]["field_options"] = Search_Key::getOptions($res[$i]["sek_smarty_variable"]);
	          }
	          return $res;
            }
        }
    }    

    /**
     * Method used to get the list of simple/quick search keys available in the
     * system.
     *
     * @access  public
     * @return  array The list of search keys
     */
    function getQuickSearchList()
    {
    	$stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
				 WHERE sek_simple_used = 1
                 ORDER BY
                    sek_order ASC";
    	$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
    	if (PEAR::isError($res)) {
    		Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
    		return "";
    	} else {
    		if (empty($res)) {
    			return array();
    		} else {
    			for ($i = 0; $i < count($res); $i++) {
    				$res[$i]["field_options"] = Search_Key::getOptions($res[$i]["sek_smarty_variable"]);
    				if ($res[$i]["sek_html_input"] == "contvocab") {
						$cvo_list = Controlled_Vocab::getAssocListFullDisplay(false, "", 0, 2);
						$res[$i]['field_options'][0] = $cvo_list['data'][$res[$i]['sek_cvo_id']];
    					$res[$i]['cv_titles'][0] = $cvo_list['title'][$res[$i]['sek_cvo_id']];
    					$res[$i]['cv_ids'][0] = $res[$i]['sek_cvo_id'];
    				} elseif ($res[$i]["sek_html_input"] == "combo") {
    				//	$ret_list = Object_Type::getAssocList();    				    		
    				//  $xdis_list = XSD_Display::getAssocListDocTypes();
    				}
    			}
    			return $res;
    		}
    	}
    }
    
    /**
     * Method used to get the list of search keys available in the 
     * system.
     *
     * @access  public
     * @return  array The list of search keys 
     */
    function getSimpleList()
    {
        $stmt = "SELECT
                    sek_id
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
				 WHERE
				   sek_simple_used = 1";
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
     * Method used to get the details of a specific search key.
     *
     * @access  public
     * @param   integer $sek_id The search key ID
     * @return  array The search key details
     */
    function getDetails($sek_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                    left join " . APP_TABLE_PREFIX . "xsd_display_matchfields
                    on xsdmf_sek_id=sek_id                    
                 WHERE
                    sek_id=".$sek_id;
        
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
    		$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);        	
            return $res;
        }
    }

    /**
     * Method used to get the details of a specific search key from a passed XSDMF match
     *
     * @access  public
     * @param   integer $xsdmf_id The xsd matching field ID
     * @return  array The search key details
     */
    function getDetailsByXSDMF_ID($xsdmf_id)
    {
        $stmt = "SELECT
                    s1.*
                 FROM
                    " . APP_TABLE_PREFIX . "search_key as s1
                    inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields as x1
                    on xsdmf_sek_id=sek_id                    
                 WHERE
                    xsdmf_id=".$xsdmf_id;
        
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if ($res['sek_id']) {
    			$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);
			}
            return $res;
        }
    }


    function getAllDetailsByXSDMF_ID($xsdmf_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key as s1
                    inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields as x1
                    on xsdmf_sek_id=sek_id                    
                 WHERE
                    xsdmf_id=".$xsdmf_id;
        
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if ($res['sek_id']) {
    			$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);
			}
            return $res;
        }
    }

    /**
     * Method used to get the basic details of a specific search key.
     *
     * @access  public
     * @param   integer $sek_id The search key ID
     * @return  array The search key details
     */
    function getBasicDetails($sek_id)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_id=".$sek_id;
        
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
    		$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);        	
            return $res;
        }
    }

    function makeSQLTableName($sek_title) {
    	return str_replace(" ", "_", trim(strtolower($sek_title)));
    }
    /**
     * Method used to get the details of a specific search key.
     *
     * @access  public
     * @param   string $sek_title The search key title
     * @return  array The search key details
     */
    function getDetailsByTitle($sek_title)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                    inner join " . APP_TABLE_PREFIX . "xsd_display_matchfields
                    on xsdmf_sek_id=sek_id
                 WHERE
                    sek_title='".$sek_title."'";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
    		$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);        	
            return $res;
        }
    }



    /**
     * Method used to get the details of a specific search key.
     *
     * @access  public
     * @param   string $sek_title The search key title
     * @return  array The search key details
     */
    function getBasicDetailsByTitle($sek_title)
    {
        $stmt = "SELECT
                    *
                 FROM
                    " . APP_TABLE_PREFIX . "search_key
                 WHERE
                    sek_title='".$sek_title."'";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
    		$res['sek_title_db'] = Search_Key::makeSQLTableName($res['sek_title']);        	
            return $res;
        }
    }

}


// benchmarking the included file (aka setup time)
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Search_Key Class');
}
?>
