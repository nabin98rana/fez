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
        $log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views";
        
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
        
        if (empty($res)) {
            return array();
        }
        
        return $res;
    }
    
    function getCviewList()
    {
        $log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views_community
                 LEFT JOIN " . APP_TABLE_PREFIX . "custom_views as sk ON cview_id = cvcom_cview_id
                 LEFT JOIN " . APP_TABLE_PREFIX . "record_search_key ON rek_pid = cvcom_com_pid";
    	try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
        
        if (empty($res)) {
            return array();
        }
        
        return $res;
    }
    
    function getDetails($cview_id)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views
                 WHERE cview_id = ?";
    	try {
			$res = $db->fetchRow($stmt, array($cview_id), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
    }
    
    function getCviewSekDetails($cvsk_id)
    {
        $log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views_search_keys
                 WHERE cvsk_id = ?";
    	try {
			$res = $db->fetchRow($stmt, array($cvsk_id), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res;
    }
    
    function getCommCviewDetails($cvcom_id)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views_community
                 WHERE cvcom_id = ?";
    	try {
			$res = $db->fetchRow($stmt, array($cvcom_id), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
        return $res;
    }
    
    function getSekList($cvsk_cview_id)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "SELECT cvsk.*, sk.*
                 FROM " . APP_TABLE_PREFIX . "custom_views_search_keys as cvsk
                 LEFT JOIN " . APP_TABLE_PREFIX . "search_key as sk ON cvsk.cvsk_sek_id = sk.sek_id
                 WHERE cvsk.cvsk_cview_id = ?".
                 " ORDER BY cvsk.cvsk_order";
		try {
			$res = $db->fetchAll($stmt, array($cvsk_cview_id), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
	
        if (empty($res)) {
            return array();
        } else {
	        for ($i = 0; $i < count($res); $i++) {
				if ($res[$i]["cvsk_sek_name"] != "") {
					$res[$i]["sek_alt_title"] = $res[$i]["cvsk_sek_name"];
				}
				if ($res[$i]["cvsk_sek_desc"] != "") {
					$res[$i]["sek_desc"] = $res[$i]["cvsk_sek_desc"];
				}
				$res[$i]["field_options"] = Search_Key::getOptions($res[$i]["sek_smarty_variable"]);
          	}	
          return $res;            	
        }
    }
    
    
    function getCommCview($community_pid)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "SELECT *
                 FROM " . APP_TABLE_PREFIX . "custom_views
                 LEFT JOIN " . APP_TABLE_PREFIX . "custom_views_community ON cvcom_cview_id = cview_id
                 WHERE cvcom_com_pid = ?";
        
    	try {
			$res = $db->fetchRow($stmt, array($community_pid), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
        return $res;
    }
    
    function insert()
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "custom_views
                 (
                    cview_name
                 ) VALUES (?)";
		try {
			$db->query($stmt, array($_POST["cview_name"]));
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}		
		return 1;
    }
    
    function insertCviewSek()
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "custom_views_search_keys
                 (
                    cvsk_cview_id,
                    cvsk_sek_id,
                    cvsk_sek_name,
                    cvsk_sek_desc
                 ) VALUES (?,?,?,?)";
    	try {
			$db->query($stmt, array($_POST["cview_id"], $_POST["cvsk_sek_id"], $_POST["cvsk_sek_name"], $_POST["cvsk_sek_desc"]));
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
    }
    
    function insertCview() 
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "INSERT INTO
                    " . APP_TABLE_PREFIX . "custom_views_community
                 (
                    cvcom_cview_id,
                    cvcom_hostname,
                    cvcom_com_pid,
					cvcom_default_template
                 ) VALUES (?,?,?,?)";
    	try {
			$db->query($stmt, array($_POST["cview_id"], $_POST["hostname"], $_POST["comm_pid"], $_POST["default_template"]));
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
    }
    
    function update($cview_id)
    {
        $log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "UPDATE " . APP_TABLE_PREFIX . "custom_views
                 SET 
                    cview_name = ? WHERE cview_id = ?";
    	try {
			$db->query($stmt, array($_POST["cview_name"], $cview_id));
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
    }
    
    
    function updateCviewSekDetails($cvsk_id)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "UPDATE " . APP_TABLE_PREFIX . "custom_views_search_keys
                 SET 
                    cvsk_sek_id = ?,
					cvsk_sek_name = ?,
					cvsk_sek_desc = ?
				 WHERE cvsk_id = ?";
    	try {
			$db->query($stmt, array($_POST["cvsk_sek_id"], $_POST["cvsk_sek_name"], $_POST["cvsk_sek_desc"], $cvsk_id));
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
    }


    function searchKeyUsedCview($sek_id)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "SELECT cvsk_sek_id FROM " . APP_TABLE_PREFIX . "custom_views_search_keys
				 WHERE cvsk_sek_id = ?";
        
		try {
			$res = $db->fetchCol($stmt, array($sek_id));
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}

		if (count($res) != 0) {
            return 1;
		} else {
			return 0;
		}
    }
    
    
    function updateCommCview($cvcom_id)
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "UPDATE " . APP_TABLE_PREFIX . "custom_views_community
                 SET 
                    cvcom_cview_id = ?,
                    cvcom_hostname = ?,
                    cvcom_default_template = ?,
					cvcom_com_pid = ?
				 WHERE cvcom_id = ?";
    	try {
			$db->query($stmt, array($_POST["cview_id"], $_POST["hostname"], $_POST["default_template"],
									$_POST["comm_pid"], $cvcom_id));
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
        return 1;
    }
    
    function remove()
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "custom_views
                 WHERE
                    cview_id IN (".Misc::arrayToSQLBindStr($_POST["items"]).")";
    	try {
			$db->query($stmt, $_POST["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}        
		return true;
    }
    
    function removeCview()
    {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "custom_views_community
                 WHERE
                    cvcom_id IN (".Misc::arrayToSQLBindStr($_POST["items"]).")";
        try {
			$db->query($stmt, $_POST["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}        
		return true;
    }
    
    function removeCviewSekKey()
    {
        $log = FezLog::get();
		$db = DB_API::get();
		
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "custom_views_search_keys
                 WHERE
                    cvsk_id IN (".Misc::arrayToSQLBindStr($_POST["items"]).")";
        try {
			$db->query($stmt, $_POST["items"]);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}        
		return true;
    }
}

?>