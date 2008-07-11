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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au                      |
// |          Rhys Palmer <r.palmer@library.uq.edu.au                     |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle Custom Views
 *
 * @version 1.0
 * @author Rhys Palmer <r.palmer@library.uq.edu.au>
 */

class Custom_View
{
    
    function getList()
    {
        $stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views";
        
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } 
        
        if (empty($res)) {
            return array();
        }
        
        return $res;
    }
    
    function getCviewList()
    {
        $stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views_community
                 LEFT JOIN " . APP_TABLE_PREFIX . "custom_views as sk ON cview_id = cvcom_cview_id
                 LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = cvcom_com_pid";
        
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } 
        
        if (empty($res)) {
            return array();
        }
        
        return $res;
    }
    
    function getDetails($cview_id)
    {
        $stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views
                 WHERE cview_id = ". $cview_id;
        
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
    
    function getCviewSekDetails($cvsk_id)
    {
        $stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views_search_keys
                 WHERE cvsk_id = ". $cvsk_id;
        
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
    
    function getCommCviewDetails($cvcom_id)
    {
        $stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views_community
                 WHERE cvcom_id = ". $cvcom_id;
        
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
    
    function getSekList($cvsk_cview_id)
    {
        $stmt = "SELECT cvsk.*, sk.*
                 FROM " . APP_TABLE_PREFIX . "custom_views_search_keys as cvsk
                 LEFT JOIN " . APP_TABLE_PREFIX . "search_key as sk ON cvsk.cvsk_sek_id = sk.sek_id
                 WHERE cvsk.cvsk_cview_id = ". $cvsk_cview_id .
                 " ORDER BY cvsk.cvsk_order";
        
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
	
	        if (empty($res)) {
                return array();
            } else {
	          for ($i = 0; $i < count($res); $i++) {
				if ($res[$i]["cvsk_sek_name"] != "") {
					$res[$i]["sek_alt_title"] = $res[$i]["cvsk_sek_name"];
				}
				$res[$i]["field_options"] = Search_Key::getOptions($res[$i]["sek_smarty_variable"]);
	          }	
	          return $res;            	
            }
        
        }
    }
    
    
    function getCommCview($community_pid)
    {
        $stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views
                 LEFT JOIN " . APP_TABLE_PREFIX . "custom_views_community ON cvcom_cview_id = cview_id
                 WHERE cvcom_com_pid = '". $community_pid . "'";
        
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }
    
    function insert()
    {
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "custom_views
                 (
                    cview_name
                 ) VALUES (
                    '" . Misc::escapeString($_POST["cview_name"]) . "'
                 )";
		
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            
           return 1;
                       
        }
    }
    
    function insertCviewSek()
    {
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "custom_views_search_keys
                 (
                    cvsk_cview_id,
                    cvsk_sek_id,
                    cvsk_sek_name
                 ) VALUES (
                    '" . Misc::escapeString($_POST["cview_id"]) . "',
					'" . Misc::escapeString($_POST["cvsk_sek_id"]) . "',
					'" . Misc::escapeString($_POST["cvsk_sek_name"]) . "'
                 )";
		
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            
           return 1;
                       
        }
    }
    
    function insertCview() 
    {
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "custom_views_community
                 (
                    cvcom_cview_id,
                    cvcom_hostname_id,
                    cvcom_com_pid,
					cvcom_default_template
                 ) VALUES (
                    '" . Misc::escapeString($_POST["cview_id"]) . "',
                    '" . Misc::escapeString($_POST["hostname"]) . "',
					'" . Misc::escapeString($_POST["comm_pid"]) . "',
					'" . Misc::escapeString($_POST["comm_pid"]) . "'
                 )";
        
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            
           return 1;
                       
        }
    }
    
    function update($cview_id)
    {
        $stmt = "UPDATE " . APP_TABLE_PREFIX . "custom_views
                 SET 
                    cview_name = '" . Misc::escapeString($_POST["cview_name"]) . "'
				 WHERE cview_id = ".$cview_id;

        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            
            return 1;
            
        }
    }
    
    
    function updateCviewSekDetails($cvsk_id)
    {
        $stmt = "UPDATE " . APP_TABLE_PREFIX . "custom_views_search_keys
                 SET 
                    cvsk_sek_id = '" . Misc::escapeString($_POST["cvsk_sek_id"]) . "',
					cvsk_sek_name = '" . Misc::escapeString($_POST["cvsk_sek_name"]) . "'
				 WHERE cvsk_id = ".$cvsk_id;
        
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            
            return 1;
            
        }
    }


    function searchKeyUsedCview($sek_id)
    {
        $stmt = "SELECT cvsk_sek_id FROM " . APP_TABLE_PREFIX . "custom_views_search_keys
				 WHERE cvsk_sek_id = '".$sek_id."'";
        
        $res = $GLOBALS["db_api"]->dbh->getCol($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            if (count($res) != 0) {
            	return 1;
			} else {
				return 0;
			}
        }
    }
    
    
    function updateCommCview($cvcom_id)
    {
        $stmt = "UPDATE " . APP_TABLE_PREFIX . "custom_views_community
                 SET 
                    cvcom_cview_id = '" . Misc::escapeString($_POST["cview_id"]) . "',
                    cvcom_hostname = '" . Misc::escapeString($_POST["hostname"]) . "',
                    cvcom_default_template = '" . Misc::escapeString($_POST["default_template"]) . "',
					cvcom_com_pid = '" . Misc::escapeString($_POST["comm_pid"]) . "'
				 WHERE cvcom_id = ".$cvcom_id;
        
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        } else {
            
            return 1;
            
        }
    }
    
    function remove()
    {
        $items = @implode("', '", $_POST["items"]);
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "custom_views
                 WHERE
                    cview_id IN ('".$items."')";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }
    
    function removeCview()
    {
        $items = @implode("', '", $_POST["items"]);
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "custom_views_community
                 WHERE
                    cvcom_id IN ('".$items."')";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }
    
    function removeCviewSekKey()
    {
        $items = @implode("', '", $_POST["items"]);
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "custom_views_search_keys
                 WHERE
                    cvsk_id IN ('".$items."')";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
		  return true;
        }
    }
}

?>