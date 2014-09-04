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
   * Generate a minimal fezACML template that sets security to be
   * inherited from parent.
   *
   * @return  string FezACML xml document
   */
  static function makeQuickTemplateInherit()
  {
      return "<FezACML><inherit_security>on</inherit_security></FezACML>";
  }


	/**
	 * Method used to get the list of all active users available in the system
	 * as an associative array of user IDs => user full names.
	 *
	 * @access  public
	 * @param   integer $role The role ID of the user
	 * @return  array The associative array of users
	 */
	function getQuickTemplateAssocList()
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
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $res;
	}

    function getQuickTemplateAssocListNoFedora()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT
                    qai_id,
                    qai_title
                 FROM
                    " . APP_TABLE_PREFIX . "auth_quick_rules_id";
        try {
            $res = $db->fetchPairs($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }

        return $res;
    }

    function getDatastreamQuickRule($pid) {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT qrp_qac_id FROM ". APP_TABLE_PREFIX ."auth_quick_rules_pid WHERE qrp_pid = ".$db->quote($pid);

        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }

        return $res;
    }

    function updateDatastreamQuickRule($pid, $rule) {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "REPLACE INTO " . APP_TABLE_PREFIX . "auth_quick_rules_pid
                 SET qrp_pid = ".$db->quote($pid).", qrp_qac_id = ".$db->quote($rule, 'INTEGER');
        ;
        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }

        return $res;
    }

    function getList()
	{
		return array();
	}

	function getPopularList()
	{
		return array();
	}

    //@package fedora & fedora_bypass
	function getUsersByRolePidAssoc($pid, $role)
	{
        if ( APP_FEDORA_BYPASS == "ON"){
            $roleId = AuthNoFedora::getRoleToRoleId($role);
            $permissions = AuthNoFedora::getNonInheritedSecurityPermissions($pid, $roleId);
            foreach ($permissions as $permission) {
                $values = AuthNoFedora::getAuthRuleValue($permission['argr_ar_id']);
                if ($values['ar_rule'] == '!rule!role!Fez_User') {
                    $return[$values['ar_value']] = User::getDisplayNameByID($values['ar_value']);
                }
            }
            return $return;
        }
           //{
            $return = array();
            $acmlBase = Record::getACML($pid);
            if ($acmlBase == "") {
                return array();
            }
            $xpath = new DOMXPath($acmlBase);
            $userSearch = $xpath->query('/FezACML/rule/role[@name="'.$role.'"]/Fez_User');
            if ($userSearch->length != 0) {
                foreach ($userSearch as $userRow) {
                    if (is_numeric($userRow->nodeValue)) {
                        $userDisplayName = User::getDisplayNameByID($userRow->nodeValue);
                        $return[$userRow->nodeValue] = $userDisplayName;
                    }
                }
            }
        //}
		return $return;
	}

    //@package fedora & fedora_bypass
	function updateUsersByRolePid($pid, $fezacml_user_list, $role, $remove_only_list = array())
	{
        if ( APP_FEDORA_BYPASS == "ON"){
            $roleId = AuthNoFedora::getRoleToRoleId($role);
            $permissions = AuthNoFedora::getNonInheritedSecurityPermissions($pid, $roleId);

            //remove from current permissions users
            foreach ($permissions as $permission) {
                $values = AuthNoFedora::getAuthRuleValue($permission['argr_ar_id']);
                if ($values['ar_rule'] == '!rule!role!Fez_User') {
                    if ((!in_array($values['ar_value'], $fezacml_user_list) && in_array($values['ar_value'], $remove_only_list))
                        || ((!in_array($values['ar_value'], $fezacml_user_list)) && empty($remove_only_list))) {
                        AuthNoFedora::deleteSecurityPermissions($pid, $roleId, $permission['argr_ar_id']);
                    }
                }
            }

            foreach ($fezacml_user_list as $user) {
                $arId = AuthRules::getOrCreateRule('!rule!role!Fez_User', $user);
                $permisison['authii_role'] = $roleId;
                $permisison['argr_ar_id'] = $arId;
                if (!in_array($permisison, $permissions)) {
                    AuthNoFedora::addSecurityPermissions($pid, $roleId, $arId);
                }
            }
            return 1;

        } else {
            $xmlString = Fedora_API::callGetDatastreamContents($pid, 'FezACML', true);

            if(empty($xmlString) || !is_string($xmlString)) {
                //	            return -3;
                //create new fezacml template with inherit on
                $xmlString = '<FezACML xmlns:xsi="http://www.w3.org/2001/XMLSchema">
                  <rule>
                    <role name="'.$role.'" />
                  </rule>
                  <inherit_security>on</inherit_security>
                </FezACML>';
            }

            $doc = DOMDocument::loadXML($xmlString);
            $xpath = new DOMXPath($doc);


            $fieldNodeList = $xpath->query('/FezACML/rule/role[@name="'.$role.'"]/Fez_User');


            if ($fieldNodeList->length == 0) {
                $parentNodeList = $xpath->query('/FezACML/rule/role[@name="'.$role.'"]');
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
	}




	function getQuickTemplateValue($qat_id)
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
				 WHERE qat_id = ".$db->quote($qat_id, 'INTEGER');

		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}

		return $res;
	}

	function getFezACMLDSName($dsID)
	{
		$FezACML_dsID = "FezACML_".str_replace(" ", "_", $dsID).".xml";
		return $FezACML_dsID;
	}

}
