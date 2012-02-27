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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle Thomson Document Type Mappings
 *
 * @version 1.0
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 */

class Thomson_Doctype_Mappings
{
  /**
   * Returns a list of existing doc type mappings
   * 
   * @param mixed $service Limit the list to the specified service
   *
   * @return array
   */
  public static function getList($service = false)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $stmt = "SELECT t.tdm_id, t.tdm_xdis_id, t.tdm_doctype, t.tdm_service, t.tdm_subtype, x.xdis_title ".
            "FROM " . APP_TABLE_PREFIX . "thomson_doctype_mappings t ".
            "LEFT JOIN " . APP_TABLE_PREFIX . "xsd_display x ON t.tdm_xdis_id = x.xdis_id";
    
    $bind = array();
    if ($service) {
      $stmt .= " WHERE tdm_service = ?";
      $bind = array($service);
    }
    try {
      $res = $db->fetchAll($stmt, $bind, Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    
    if (empty($res)) {
        return array();
    }
    
    return $res;
  }
  
  /**
   * Method used to get the list of XSD Displays for a given XSD.
   * where the display has not been mapped to a ref type
   *
   * @param   integer $xsd_id The XSD ID to search the list for.
   * 
   * @return  array The list of XSD Displays
   */
  public static function getXsdDispList($xsd_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "xsd_display
             WHERE
                xdis_xsd_id = ".$db->quote($xsd_id, 'INTEGER')." 
                AND xdis_object_type = 3 
                AND xdis_version = 'MODS 1.0'                
             ORDER BY
                xdis_title ASC";
    try {
      $res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return array();
    }
    
    $result = $res;
    for ($i=0; $i<count($res); $i++) {
      $result[$i]['subtypes'] = self::getSubtypes($res[$i]['xdis_id']);
    }
    return $result;
  }
  
  /**
   * For a XSD display return all of its subtypes
   *
   * @param int $tdm_xsd_id
   * 
   * @return array
   */
  public static function getSubtypes($tdm_xsd_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    // Get the loop subelement id
    $stmt = "SELECT 
                xsdsel_id
              FROM 
                " . APP_TABLE_PREFIX . "xsd_loop_subelement f
              WHERE 
                xsdsel_id IN (
                  SELECT xsdmf_xsdsel_id FROM " . APP_TABLE_PREFIX . "xsd_display_matchfields f WHERE xsdmf_xdis_id=?
                )
                AND xsdsel_title='MODS'";
        
    try {
      $xsdsel_id = $db->fetchOne($stmt, array($tdm_xsd_id));
    }
    catch(Exception $ex) {
      $log->err($ex);
      return array();
    }
    
    if (empty($xsdsel_id)) {
        return array();
    }
    
    // Get the display match fields
    $stmt = "SELECT
                xsdmf_id
              FROM
                " . APP_TABLE_PREFIX . "xsd_display_matchfields f
              WHERE xsdmf_xdis_id=?
                AND xsdmf_xsdsel_id=?
                AND xsdmf_element='!datastream!datastreamVersion!xmlContent'";
        
    try {
      $xsdmf_id = $db->fetchOne($stmt, array($tdm_xsd_id, $xsdsel_id));
    }
    catch(Exception $ex) {
      $log->err($ex);
      return array();
    }
    
    if (empty($xsdmf_id)) {
        return array();
    }
    
    // Get the display id
    $stmt = "SELECT
                xsdrel_xdis_id
              FROM
                " . APP_TABLE_PREFIX . "xsd_relationship f
              WHERE
                xsdrel_xsdmf_id=?";
        
    try {
      $xsdrel_xdis_id = $db->fetchOne($stmt, array($xsdmf_id));
    }
    catch(Exception $ex) {
      $log->err($ex);
      return array();
    }
    
    if (empty($xsdrel_xdis_id)) {
        return array();
    }
    
    // Get the display match field id
    $stmt = "SELECT
                xsdmf_id
              FROM
                " . APP_TABLE_PREFIX . "xsd_display_matchfields f
              WHERE
                xsdmf_xdis_id=? AND xsdmf_xpath='/mods:mods/mods:genre/@type';";
        
    try {
      $xsdmf_id = $db->fetchOne($stmt, array($xsdrel_xdis_id));
    }
    catch(Exception $ex) {
      $log->err($ex);
      return array();
    }
    
    if (empty($xsdmf_id)) {
        return array();
    }
    
    // Get the match field options (the subtypes)
    $stmt = "SELECT
                mfo_id, mfo_value
              FROM
                " . APP_TABLE_PREFIX . "xsd_display_mf_option f
              WHERE
                mfo_fld_id=?
              ORDER BY mfo_value ASC;";
        
    try {
      $res = $db->fetchPairs($stmt, array($xsdmf_id));
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
  
  /**
   * Returns an existing doc type mapping
   * 
   * @param int $tdm_id
   *
   * @return array
   */
  public static function get($tdm_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $stmt = "SELECT *
             FROM " . APP_TABLE_PREFIX . "thomson_doctype_mappings
             WHERE tdm_xdis_id = ?";
        
    try {
      $res = $db->fetchAll($stmt, array($tdm_id), Zend_Db::FETCH_ASSOC);
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
  
  /**
   * Inserts a mapping
   *
   * @param int $tdm_xdis_id
   * @param string $tdm_doctype
   * @param string $tdm_service
   * @param string $tdm_subtype
   * 
   * @return bool
   */
  public static function insert($tdm_xdis_id, $tdm_doctype, $tdm_service, $tdm_subtype = '')
  {
    $log = FezLog::get();
    $db = DB_API::get();
  
    $stmt = "INSERT INTO " . APP_TABLE_PREFIX . "thomson_doctype_mappings
             (tdm_xdis_id, tdm_doctype, tdm_service, tdm_subtype) VALUES (?, ?, ?, ?)";
    try {
      $db->query($stmt, array($tdm_xdis_id, $tdm_doctype, $tdm_service, $tdm_subtype));
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }   
    return true;
  }
  
  /**
   * Updates an existing mapping
   *
   * @param int $tdm_id
   * @param int $tdm_xdis_id
   * @param string $tdm_doctype
   * @param string $tdm_service
   * @param string $tdm_subtype
   * 
   * @return bool
   */
  public static function update($tdm_id, $tdm_xdis_id, $tdm_doctype, $tdm_service, $tdm_subtype)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    $stmt = "UPDATE " . APP_TABLE_PREFIX . "thomson_doctype_mappings
             SET tdm_xdis_id = ?, tdm_doctype = ?, tdm_service = ?, tdm_subtype = ? WHERE tdm_id = ?";
    try {
      $db->query($stmt, array($tdm_xdis_id, $tdm_doctype, $tdm_service, $tdm_subtype, $tdm_id));
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return true;
  }
  
  /**
   * Deletes an existing mapping
   *
   * @param mixed $tdm_id
   * 
   * @return bool
   */
  public static function delete($tdm_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    
    if (is_array($tdm_id)) {
      $stmt = "DELETE FROM " . APP_TABLE_PREFIX . "thomson_doctype_mappings
               WHERE tdm_id IN (".Misc::arrayToSQLBindStr($tdm_id).")";
    } else {  
      $stmt = "DELETE FROM " . APP_TABLE_PREFIX . "thomson_doctype_mappings
               WHERE tdm_id = ?";
      $tdm_id = array($tdm_id);
    }
    try {
      $db->query($stmt, $tdm_id);
    }
    catch(Exception $ex) {
      $log->err($ex);
      return false;
    }
    return true;
  }  
}
