<?php
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
// | Author: Aaron Brown <a.brown@library.uq.edu.au>                           |
// +----------------------------------------------------------------------+

/**
 * The purpose of this script is to
 * set up non inherited and version permisisons for all pids
 * Assumes pid computed permissions are correct in fez_auth_index2 table.
 *
 * This is a one-off migration script as part of Fedora-less project.
 */
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php';
include_once(APP_INC_PATH . "class.db_api.php");
include_once(APP_INC_PATH . "class.dsresource.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.auth_no_fedora_datastreams.php");
error_reporting(1);
set_time_limit(0);


$stmt = "SELECT rek_pid FROM " . APP_TABLE_PREFIX . "record_search_key";

try {
    $res = $db->fetchCol($stmt);
} catch (Exception $ex) {
    $log->err($ex);
    echo "Failed to retrieve pid data. Error: " . $ex;
}

$i=0;
$dsID = 'FezACML';
foreach ($res as $pid) {
    //$pid =  'UQ:67393';
    $acml = Record::getACML($pid);
    $security_inherited = inheritesPermissions($acml);
    if ($security_inherited) {
        AuthNoFedora::setInherited($pid);
    } else {
        AuthNoFedora::setInherited($pid,0);
    }
    addDatastreamSecurity($acml, $pid);

    $parms = array('pid' => $pid, 'dsID' => $dsID);
    $datastreamVersions = Fedora_API::openSoapCall('getDatastreamHistory', $parms);
    $i=0;

    //If only one verion it returns a object not array of objects
    if (is_array($datastreamVersions[0])) {
        foreach($datastreamVersions as $datastreamVersion) {
            if ($i>0)  {
                $asOfDateTime = $datastreamVersion[createDate];
                $parms = array('pid' => $pid, 'dsID' => $dsID, 'asOfDateTime'=> $asOfDateTime);
                $tempXML = Fedora_API::openSoapCallAccess('GetDatastreamDissemination', $parms);
                $datastreamVersionXml = new DomDocument();
                $datastreamVersionXml->preserveWhiteSpace = false;
                $datastreamVersionXml->loadXML($tempXML[stream]);
                addDatastreamSecurity($datastreamVersionXml, $pid, $asOfDateTime);

            }
            $i++;
        }
    }

    echo 'Done: '.$pid.'<br />';
}

function inheritesPermissions ($acml) {

    if ($acml == false) {
        //if no acml then defualt is inherit
        $inherit = true;
    } else {
        $xpath = new DOMXPath($acml);
        $inheritSearch = $xpath->query('/FezACML[inherit_security="on"]');
        $inherit = false;
        if( $inheritSearch->length > 0 ) {
            $inherit = true;
        }
    }
    return $inherit;
}

function addDatastreamSecurity($acml, $pid, $date = false) {
    if (!empty($acml)) {
        AuthNoFedora::deletePermissions($pid, 0);

        // loop through the ACML docs found for the current pid or in the ancestry
        $xpath = new DOMXPath($acml);
        $roleNodes = $xpath->query('/FezACML/rule/role');

        foreach ($roleNodes as $roleNode) {
            $arIds = array();
            $role = $roleNode->getAttribute('name');
            // Use XPath to get the sub groups that have values
            $groupNodes = $xpath->query('./*[string-length(normalize-space()) > 0]', $roleNode);

            /* todo
             * Empty rules override non-empty rules. Example:
             * If a pid belongs to 2 collections, 1 collection has lister restricted to fez users
             * and 1 collection has no restriction for lister, we want no restrictions for lister
             * for this pid.
             */

            foreach ($groupNodes as $groupNode) {
                $group_type = $groupNode->nodeName;
                $group_values = explode(',', $groupNode->nodeValue);
                foreach ($group_values as $group_value) {

                    //off is the same as lack of, so should be the same
                    if ($group_value != "off") {
                        $group_value = trim($group_value, ' ');
                        $arIds[] = AuthRules::getOrCreateRule("!rule!role!".$group_type, $group_value);
                    }
                }
            }
            if (count($arIds)) {
                $arg_id = AuthRules::getOrCreateRuleGroupArIds($arIds);
                if (!$date) {
                    AuthNoFedora::addRoleSecurityPermissions($pid, Auth::getRoleIDByTitle($role), $arg_id);
                } else {
                    AuthNoFedora::addRoleSecurityPermissionsShadow($pid, Auth::getRoleIDByTitle($role), $arg_id, $date);
                }
            }

        }
    }
}