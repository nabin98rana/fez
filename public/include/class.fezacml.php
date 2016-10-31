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


include_once(APP_INC_PATH . "class.error_handler.php");

class FezACML
{

  /**
   * Method used to edit the security (FezACML) details of a PID.
   *
   * @param   string $pid The persistent identifier of the record
   * @param   string $xdis_title The FezACML XSD title
   */
  public static function editPidSecurity($pid, $xdis_title)
  {
    FezACML::editSecurity($pid, $xdis_title);
  }

  /**
   * Method used to edit the security (FezACML) details of a specific Datastream.
   *
   * @param   string $pid The persistent identifier of the record
   * @param   string $dsID The datastream ID of the datastream
   */
  public static function editDatastreamSecurity($pid, $dsID)
  {
    FezACML::editSecurity($pid, FezACML::getXdisTitlePrefix() . 'Datastreams', $dsID);
  }

  /**
   * Method used to edit the security (FezACML) details of a PID or a datastream.
   *
   * @param   string $pid The persistent identifier of the record
   * @param   string $xdis_title The FezACML XSD title
   * @param   string $dsID (Optional) The datastream ID of the datastream
   */
  private static function editSecurity($pid, $xdis_title, $dsID = '')
  {
    $xdis_id = XSD_Display::getID($xdis_title);
    $display = new XSD_DisplayObject($xdis_id);
    list($array_ptr, $xsd_element_prefix, $xsd_top_element_name, $xml_schema) = $display->getXsdAsReferencedArray();
    $indexArray = array();
    $header = "<" . $xsd_element_prefix . $xsd_top_element_name . " ";
    $header .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid);
    $header .= ">\n";
    $xmlObj = Foxml::array_to_xml_instance(
      $array_ptr, $xmlObj, $xsd_element_prefix, "", "", "", $xdis_id, $pid, $xdis_id, "",
      $indexArray, '', '', '', '', '', ''
    );
    $xmlObj .= "</" . $xsd_element_prefix . $xsd_top_element_name . ">\n";
    $xmlObj = $header . $xmlObj;

    $config = [
      'indent' => TRUE,
      'input-xml' => TRUE,
      'output-xml' => TRUE,
      'wrap' => 0
    ];
    $tidy = new tidy;
    $tidy->parseString($xmlObj, $config, 'utf8');
    $tidy->cleanRepair();
    $xmlObj = $tidy;

    $FezACML_dsID = FezACML::getFezACMLPidName($pid);
    $logMessage = "FezACML security for PID - " . $pid;
    if (! empty($dsID)) {
      $FezACML_dsID = FezACML::getFezACMLDSName($dsID);
      $logMessage = "FezACML security for datastream - " . $dsID;
    }
    Fedora_API::callModifyDatastreamByValue($pid, $FezACML_dsID, "A",
      $logMessage, $xmlObj, "text/xml", "inherit");
  }

  /**
   * Generate a minimal fezACML template that sets security to be
   * inherited from parent.
   *
   * @return  string FezACML xml document
   */
  public static function makeQuickTemplateInherit()
  {
    return "<FezACML><inherit_security>on</inherit_security></FezACML>";
  }


  /**
   * Method used to get the list of all quick templates available in the system.
   *
   * @return  array The associative array of quick templates
   */
  public static function getQuickTemplateAssocList()
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    qat_id,
                    qat_title
                 FROM
                    " . APP_TABLE_PREFIX . "auth_quick_template";
    try {
      $res = $db->fetchPairs($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return '';
    }

    return $res;
  }

  /**
   * Method used to get the quick template ID from the title.
   *
   * @return  int The ID of the quick auth template
   */
  public static function getQuickTemplateIdByTitle($title)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT
                    qat_id
                 FROM
                    " . APP_TABLE_PREFIX . "auth_quick_template
                 WHERE qat_title = " . $db->quote($title);
    try {
      $res = $db->fetchOne($stmt);
      return $res;

    } catch (Exception $ex) {
      $log->err($ex);
      return '';
    }
  }

  public static function datastreamQuickRuleExists($pid, $rule)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $stmt = "SELECT qrp_qac_id FROM " . APP_TABLE_PREFIX . "auth_quick_rules_pid 
              WHERE qrp_pid = " . $db->quote($pid) . " AND qrp_qac_id = " . $db->quote($rule);

    try {
      $res = $db->fetchOne($stmt);
      if ($res) {
        return true;
      }
    } catch (Exception $ex) {
      $log->err($ex);
    }

    return false;
  }

  public static function getUsersByRolePidAssoc($pid, $role)
  {
    $return = array();
    $acmlBase = Record::getACML($pid);
    if ($acmlBase == "") {
      return $return;
    }
    $xpath = new DOMXPath($acmlBase);
    $userSearch = $xpath->query('/FezACML/rule/role[@name="' . $role . '"]/Fez_User');
    if ($userSearch->length != 0) {
      foreach ($userSearch as $userRow) {
        if (is_numeric($userRow->nodeValue)) {
          $userDisplayName = User::getDisplayNameByID($userRow->nodeValue);
          $return[$userRow->nodeValue] = $userDisplayName;
        }
      }
    }
    return $return;
  }

  public static function updateUsersByRolePid($pid, $fezacml_user_list, $role, $remove_only_list = array())
  {
    $xmlString = Fedora_API::callGetDatastreamContents($pid, 'FezACML', true);

    if (empty($xmlString) || !is_string($xmlString)) {
      //	            return -3;
      //create new fezacml template with inherit on
      $xmlString = '<FezACML xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                <rule>
                  <role name="' . $role . '" />
                </rule>
                <inherit_security>on</inherit_security>
              </FezACML>';
    }

    $doc = DOMDocument::loadXML($xmlString);
    $xpath = new DOMXPath($doc);


    $fieldNodeList = $xpath->query('/FezACML/rule/role[@name="' . $role . '"]/Fez_User');


    if ($fieldNodeList->length == 0) {
      $parentNodeList = $xpath->query('/FezACML/rule/role[@name="' . $role . '"]');
      if ($parentNodeList->length == 0) {
        return -1;
      }
      $parentNode = $parentNodeList->item(0);
    } else {
      foreach ($fieldNodeList as $fieldNode) { // first delete all the Fez_Users
        $parentNode = $fieldNode->parentNode;
        if (count($remove_only_list) == 0) {
          $parentNode->removeChild($fieldNode);
        } elseif (in_array($fieldNode->nodeValue, $remove_only_list) || $fieldNode->nodeValue == "") { // if a list of remove only ids is set, only remove ones in this list (eg to send a list of ids in a fez group)
          $parentNode->removeChild($fieldNode);
        }
      }
    }
    if (is_array($fezacml_user_list)) {
      foreach ($fezacml_user_list as $fez_user) {
        $newNode = $doc->createElement('Fez_User', $fez_user);
        $parentNode->appendChild($newNode);
      }
    }
    $newXML = $doc->SaveXML();
    $FezACML = "FezACML";
    if (Fedora_API::datastreamExists($pid, $FezACML)) {
      Fedora_API::callModifyDatastreamByValue($pid, $FezACML, "A", "FezACML",
        $newXML, "text/xml", "inherit");
    } else {
      Fedora_API::getUploadLocation($pid, $FezACML, $newXML, "FezACML",
        "text/xml", "X", null, "true");
    }
    Record::setIndexMatchingFields($pid);
    return 1;
  }


  public static function getQuickTemplateValue($qat_id)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    if (!is_numeric($qat_id)) {
      return false;
    }

    $stmt = "SELECT
                    qat_value
                 FROM
                    " . APP_TABLE_PREFIX . "auth_quick_template
				 WHERE qat_id = " . $db->quote($qat_id, 'INTEGER');

    try {
      $res = $db->fetchOne($stmt);
    } catch (Exception $ex) {
      $log->err($ex);
      return '';
    }

    return $res;
  }

  public static function getFezACMLDSName($dsID)
  {
    $FezACML_dsID = $dsID;
    if (strpos($dsID, "FezACML_") !== 0) {
      $FezACML_dsID = "FezACML_" . str_replace(" ", "_", $dsID) . ".xml";
    }
    return $FezACML_dsID;
  }

  public static function getFezACMLPidName($pid)
  {
    $FezACML_DS_name = "FezACML";
    if (APP_FEDORA_BYPASS == 'ON' && strpos($pid, "FezACML_") !== 0) {
      $FezACML_DS_name = "FezACML_" . str_replace(":", "_", $pid) . ".xml";
    }
    return $FezACML_DS_name;
  }

  public static function getXdisTitlePrefix()
  {
    return 'FezACML for ';
  }
}
