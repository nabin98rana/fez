<?php

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.record.php");

class AuthNoFedoraDatastreams {

    //delete a security permission given parameters if found
    function deleteRoleSecurityPermissions($did, $role, $arg_id, $inherited = '0') {
        //todo check user has permissions

        $log = FezLog::get();
      	$db = DB_API::get();

        if ( $inherited == '0') {
        $stmt = "DELETE FROM ". APP_TABLE_PREFIX . "auth_datastream_index2_not_inherited
                    WHERE authdii_did = ".$db->quote($did). "AND
                    authdii_role = ". $db->quote($role). " AND
                    authdii_arg_id = ".$db->quote($arg_id);
        } else {
        $stmt = "DELETE FROM ". APP_TABLE_PREFIX . "auth_datastream_index2
                    WHERE authdi_did = ".$db->quote($did). "AND
                    authdi_role = ". $db->quote($role). " AND
                    authdi_arg_id = ".$db->quote($arg_id);
        }

        try {
        	$res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
        	$log->err($ex);
        	return array();
        }
    }

    function addRoleSecurityPermissions($did, $role, $arg_id, $inherited = '0') {
        $log = FezLog::get();
      	$db = DB_API::get();

        if ( $inherited == '0') {
            $stmt = "INSERT INTO ". APP_TABLE_PREFIX . "auth_datastream_index2_not_inherited (authdii_did, authdii_role, authdii_arg_id)
                    VALUES (". $db->quote($did).",".$db->quote($role).",".$db->quote($arg_id).")";
        } else {
            $stmt = "INSERT INTO ". APP_TABLE_PREFIX . "auth_datastream_index2 (authdi_did, authdi_role, authdi_arg_id)
                    VALUES (". $db->quote($did).",".$db->quote($role).",".$db->quote($arg_id). ")";
        }

        try {
        	$res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
        	$log->err($ex);
        	return array();
        }
    }

    //Find all information for the security changing screen
    function getSecurityPermissionsDisplay($did) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "SELECT *, 0 as inherited FROM ". APP_TABLE_PREFIX . "auth_datastream_index2_not_inherited
            LEFT JOIN ". APP_TABLE_PREFIX . "auth_roles
            ON authdii_role = aro_id
            LEFT JOIN ". APP_TABLE_PREFIX . "auth_rule_group_rules
            ON argr_arg_id = authdii_arg_id
            LEFT JOIN ". APP_TABLE_PREFIX . "auth_rules
            ON ar_id = argr_ar_id
            LEFT JOIN ". APP_TABLE_PREFIX . "group
            ON ar_value = grp_id
            LEFT JOIN ". APP_TABLE_PREFIX . "user
            ON ar_value = usr_id
            WHERE authdii_did = ".$db->quote($did);
        try {
        	$res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
        	$log->err($ex);
        	return array();
        }

        $stmt = "SELECT *, 1 as inherited FROM ". APP_TABLE_PREFIX . "auth_datastream_index2
            LEFT JOIN ". APP_TABLE_PREFIX . "auth_roles
            ON authdi_role = aro_id
            LEFT JOIN ". APP_TABLE_PREFIX . "auth_rule_group_rules
            ON argr_arg_id = authdi_arg_id
            LEFT JOIN ". APP_TABLE_PREFIX . "auth_rules
            ON ar_id = argr_ar_id
            LEFT JOIN ". APP_TABLE_PREFIX . "group
            ON ar_value = grp_id
            LEFT JOIN ". APP_TABLE_PREFIX . "user
            ON ar_value = usr_id
            WHERE authdi_did = ".$db->quote($did);
        try {
        	$res2 = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
        	$log->err($ex);
        	return array();
        }

        $results = $res;
        foreach ($res2 as $row2) {
            $unique = true;
            foreach ($res as $row) {
                if (($row[authdii_role] == $row2[authdi_role]) && ($row[ar_id] == $row2[ar_id])){
                    $unique = false;
                }
            }
            if ($unique) {
                $results[] = $row2;
            }
        }
        $res = $results;

        $row=array();
        for ($i=0; $i<count($res); $i++)
        {
            //Breaks the rule up ie. !rule!role!AD_User -> AD_User
            $pieces = explode("!", $res[$i]['ar_rule']);
            $res[$i]['ar_rule_value'] = (count($pieces) == 4) ? $pieces[3] : $res[$i]['ar_rule'];

            //Finds names for the groups and users is applicable
            if($res[$i]['ar_rule_value'] == "Fez_Group")
            {
                $res[$i]['ar_value_value']=$res[$i]['grp_title'];
            } elseif ($res[$i]['ar_rule_value'] == "Fez_User") {
                $res[$i]['ar_value_value']= $res[$i]['usr_full_name'];
            } else
            {
                $res[$i]['ar_value_value'] = $res[$i]['ar_value'];
            }
            //unique row id for security table
            $res[$i]['row'] = $res[$i]['authdii_role'].",".$res[$i]['ar_id'];
        }

        return $res;
    }

    //Does the object inherit permissions from parent
    function isInherited($did) {
        $log = FezLog::get();
      	$db = DB_API::get();


        $stmt =  "SELECT fat_security_inherited
                            FROM ". APP_TABLE_PREFIX . "file_attachments
                            WHERE fat_did = ".$db->quote($did);

        try {
      			$res = $db->fetchOne($stmt);
      		}
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }

        return $res;
    }

        //Does the object inherit permissions from parent
    function isWatermarked($did) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "SELECT fat_watermark
                FROM ". APP_TABLE_PREFIX . "file_attachments
                WHERE fat_did = ".$db->quote($did);

        try {
      			$res = $db->fetchOne($stmt);
      		}
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }

        return $res;
    }
        //Does the object inherit permissions from parent
    function isCopyrighted($did) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "SELECT fat_copyright
                FROM ". APP_TABLE_PREFIX . "file_attachments
                WHERE fat_did = ".$db->quote($did);

        try {
      			$res = $db->fetchOne($stmt);
      		}
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }

        return $res;
    }

        //set inherit permissions
    function setInherited($did) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "UPDATE ". APP_TABLE_PREFIX . "file_attachments
                SET fat_security_inherited = '1'
                WHERE fat_did = ".$db->quote($did);

        try {
      			$res = $db->exec($stmt);
      		}
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }

        AuthNoFedoraDatastreams::recalculatePermissions($did);
        return $res;
    }

    function deleteInherited($did) {
            $log = FezLog::get();
            $db = DB_API::get();

            $stmt = "UPDATE ". APP_TABLE_PREFIX . "file_attachments
                    SET fat_security_inherited = '0'
                    WHERE fat_did = ".$db->quote($did);

            try {
                    $res = $db->exec($stmt);
                }
            catch(Exception $ex) {
                $log->err($ex);
                return array();
            }

            AuthNoFedoraDatastreams::recalculatePermissions($did);
            return $res;
        }
    function setCopyright($did) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "UPDATE ". APP_TABLE_PREFIX . "file_attachments
                SET fat_copyright = '1'
                WHERE fat_did = ".$db->quote($did);

        try {
      			$res = $db->exec($stmt);
      		}
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }

        AuthNoFedoraDatastreams::recalculatePermissions($did);
        return $res;
    }

    function deleteCopyright($did) {
            $log = FezLog::get();
            $db = DB_API::get();

            $stmt = "UPDATE ". APP_TABLE_PREFIX . "file_attachments
                    SET fat_copyright = '0'
                    WHERE fat_did = ".$db->quote($did);

            try {
                    $res = $db->exec($stmt);
                }
            catch(Exception $ex) {
                $log->err($ex);
                return array();
            }

            AuthNoFedoraDatastreams::recalculatePermissions($did);
            return $res;
        }
    function setWatermark($did) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "UPDATE ". APP_TABLE_PREFIX . "file_attachments
                SET fat_watermark = '1'
                WHERE fat_did = ".$db->quote($did);

        try {
      			$res = $db->exec($stmt);
      		}
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }

        AuthNoFedoraDatastreams::recalculatePermissions($did);
        return $res;
    }

    function deleteWatermark($did) {
            $log = FezLog::get();
            $db = DB_API::get();

            $stmt = "UPDATE ". APP_TABLE_PREFIX . "file_attachments
                    SET fat_watermark = '0'
                    WHERE fat_did = ".$db->quote($did);

            try {
                    $res = $db->exec($stmt);
                }
            catch(Exception $ex) {
                $log->err($ex);
                return array();
            }

            AuthNoFedoraDatastreams::recalculatePermissions($did);
            return $res;
        }
    function getAllGroupTypes()
    {
        return array('AD_Group' => 'AD_Group',
                    'in_AD' => 'in_AD',
                    'in_Fez' => 'in_Fez',
                    'AD_User' => 'AD_User',
                    'AD_DistinguishedName' => 'AD_DistinguishedName',
                    'eduPersonTargetedID'  => 'eduPersonTargetedID',
                    'eduPersonAffiliation'  => 'eduPersonAffiliation',
                    'eduPersonScopedAffiliation'  => 'eduPersonScopedAffiliation',
                    'eduPersonPrimaryAffiliation'  => 'eduPersonPrimaryAffiliation',
                    'eduPersonPrincipalName'  => 'eduPersonPrincipalName',
                    'eduPersonOrgUnitDN'  => 'eduPersonOrgUnitDN',
                    'eduPersonOrgDN' => 'eduPersonOrgDN',
                    'eduPersonPrimaryOrgUnitDN' => 'eduPersonPrimaryOrgUnitDN',
                    'Fez_Group' => 'Fez_Group',
                    'Fez_User' => 'Fez_User'
                 );

    }

    function getAllSecurityPermissions($did) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "SELECT authdi_role, argr_ar_id FROM ". APP_TABLE_PREFIX . "auth_datastream_index2
            LEFT JOIN ". APP_TABLE_PREFIX . "auth_rule_group_rules
            ON argr_arg_id = authdi_arg_id
            WHERE authdi_did = ".$db->quote($did);
        try {
        	$res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
        	$log->err($ex);
        	return array();
        }

         return $res;
    }

    function getNonInheritedSecurityPermissions($did, $role=null) {
        $log = FezLog::get();
      	$db = DB_API::get();

        if (empty($role)) {
            $stmt = "SELECT authdii_role, argr_ar_id FROM ". APP_TABLE_PREFIX . "auth_datastream_index2_not_inherited
                LEFT JOIN ". APP_TABLE_PREFIX . "auth_rule_group_rules
                ON argr_arg_id = authdii_arg_id
                WHERE authdii_did = ".$db->quote($did);
        } else {
            $stmt = "SELECT authdii_role, argr_ar_id FROM ". APP_TABLE_PREFIX . "auth_datastream_index2_not_inherited
                LEFT JOIN ". APP_TABLE_PREFIX . "auth_rule_group_rules
                ON argr_arg_id = authdii_arg_id
                WHERE authdii_did = ".$db->quote($did)."
                AND authdii_role = ".$db->quote($role);
        }
        try {
        	$res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
        	$log->err($ex);
        	return array();
        }

         return $res;
    }

    function getPid($did) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "SELECT fat_pid
                FROM ". APP_TABLE_PREFIX . "file_attachments
                WHERE fat_did = ".$db->quote($did);

        try {
      			$res = $db->fetchOne($stmt);
      		}
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }

        return $res;
    }

    function getDid($pid, $dsID) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "SELECT fat_did
                FROM ". APP_TABLE_PREFIX . "file_attachments
                WHERE fat_pid = ".$db->quote($pid)." AND
                fat_filename = ".$db->quote($dsID);

        try {
      			$res = $db->fetchOne($stmt);
      		}
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }

        return $res;
    }

    function getParentsACML($did, $pid='') {

        if(empty($pid))
        {
            $pid = AuthNoFedoraDatastreams::getPid($did);
        }

        $parentPermissions = array();

        if (AuthNoFedoraDatastreams::isInherited($did)){
            $parentPids = array_merge(Record::getParents($pid), array($pid));
            foreach($parentPids as $parentPid) {
                $tempParentPermissions = AuthNoFedora::getAllSecurityPermissions($parentPid);
                $parentPermissions = array_merge($parentPermissions, $tempParentPermissions);
            }
        }
        return $parentPermissions;
    }

    //This assumes parent or non inherited data might be changed
    function recalculatePermissions($did)
    {
        $didParentPermisisons = AuthNoFedoraDatastreams::getParentsACML($did);
        $didNonInheritedPermisisons = AuthNoFedoraDatastreams::getNonInheritedSecurityPermissions($did);
        $didCaculatedPermissions = array_merge($didParentPermisisons,$didNonInheritedPermisisons);

        foreach($didCaculatedPermissions as $didCaculatedPermission) {
            if ($didCaculatedPermission[authi_role]) {
                $newGroups[$didCaculatedPermission[authi_role]][] = $didCaculatedPermission[argr_ar_id];
            } else{
                $newGroups[$didCaculatedPermission[authdii_role]][] = $didCaculatedPermission[argr_ar_id];
            }
        }


            AuthNoFedoraDatastreams::deletePermissions($did);
            foreach ($newGroups as $role => $newGroup) {
                $arg_id = AuthRules::getOrCreateRuleGroupArIds($newGroup);
                AuthNoFedoraDatastreams::addRoleSecurityPermissions($did, $role, $arg_id, '1');
            }
    }

    function deletePermissions($did, $inherited = '1', $role=null)
    {
        $log = FezLog::get();
      	$db = DB_API::get();

        if (empty($role)){
            if ( $inherited == '0') {
                $stmt = "DELETE FROM ". APP_TABLE_PREFIX . "auth_datastream_index2_not_inherited
                            WHERE authdii_did = ".$db->quote($did);
            } else {
                $stmt = "DELETE FROM ". APP_TABLE_PREFIX . "auth_datastream_index2
                            WHERE authdi_did = ".$db->quote($did);
            }
        } else
        {
            if ( $inherited == '0') {
                $stmt = "DELETE FROM ". APP_TABLE_PREFIX . "auth_datastream_index2_not_inherited
                            WHERE authdii_did = ".$db->quote($did)." AND authdii_role = ".$db->quote($role);
            } else {
                $stmt = "DELETE FROM ". APP_TABLE_PREFIX . "auth_datastream_index2
                            WHERE authdi_did = ".$db->quote($did)." AND authdi_role = ".$db->quote($role);
            }
        }

        try {
        	$res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
        	$log->err($ex);
        	return array();
        }
    }

    function addSecurityPermissions($did, $role, $ar_id) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $didNonInheritedPermisisons = AuthNoFedoraDatastreams::getNonInheritedSecurityPermissions($did, $role);
        $oldGroups[$didPermission[authdi_role]][] = $didPermission[argr_ar_id];
        $new = array(array('authdi_role' => $role, 'argr_ar_id' => $ar_id ));
        $didNewPermissions = array_merge($new,$didNonInheritedPermisisons);
        foreach($didNewPermissions as $didNewPermission) {
            $newGroup[] = $didNewPermission[argr_ar_id];
        }

        AuthNoFedoraDatastreams::deletePermissions($did, '0', $role);
        $arg_id = AuthRules::getOrCreateRuleGroupArIds($newGroup);
        AuthNoFedoraDatastreams::addRoleSecurityPermissions($did, $role, $arg_id, '0');

        //Added non inherited permissions now need to recalculate global permisisons
        AuthNoFedoraDatastreams::recalculatePermissions($did);
    }

    function deleteSecurityPermissions($did, $role, $ar_id) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $didNonInheritedPermisisons = AuthNoFedoraDatastreams::getNonInheritedSecurityPermissions($did, $role);

        $newGroup = array();
        foreach($didNonInheritedPermisisons as $didNonInheritedPermisison) {
            if ($didNonInheritedPermisison[argr_ar_id] != $ar_id) {
                $newGroup[] = $didNonInheritedPermisison[argr_ar_id];
            }
        }

        AuthNoFedoraDatastreams::deletePermissions($did, 0, $role);
        $arg_id = AuthRules::getOrCreateRuleGroupArIds($newGroup);
        if ($arg_id) {
            AuthNoFedoraDatastreams::addRoleSecurityPermissions($did, $role, $arg_id, '0');
        }
        //Added non inherited permissions now need to recalculate global permisisons
        AuthNoFedoraDatastreams::recalculatePermissions($did);
    }

    function getAllSecurityPermissionsDescriptions($did) {
        $log = FezLog::get();
      	$db = DB_API::get();

        $stmt = "SELECT ar_rule, aro_role, ar_value FROM ". APP_TABLE_PREFIX . "auth_datastream_index2
            LEFT JOIN ". APP_TABLE_PREFIX . "auth_roles
            ON authdi_role = aro_id
            LEFT JOIN ". APP_TABLE_PREFIX . "auth_rule_group_rules
            ON argr_arg_id = authdi_arg_id
            LEFT JOIN ". APP_TABLE_PREFIX . "auth_rules
            ON ar_id = argr_ar_id
            WHERE authdi_pid = ".$db->quote($did);
        try {
        	$res = $db->fetchAll($stmt);
        }
        catch(Exception $ex) {
        	$log->err($ex);
        	return array();
        }

         return $res;
    }
}