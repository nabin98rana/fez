<?php

include_once(APP_INC_PATH.'class.record.php');
include_once(APP_INC_PATH . "class.bgp_index_auth.php");

class AuthIndex {
    var $get_auth_done_pids = array();
    var $bgp;
    var $pid_cache = array();
    var $pid_count = 0;

    function setIndexAuth($pid)
    {
       $bgp = new BackgroundProcess_Index_Auth; 
       $bgp->register(serialize(compact('pid')), Auth::getUserID());
    }

    function setBGP(&$bgp)
    {
        $this->bgp = &$bgp;
    }

    function setIndexAuthBGP($pid, $topcall=true)
    {
        $this->bgp->setHeartbeat();
        $this->bgp->setProgress(++$this->pid_count);
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        // topcall means this is the first call and not a recursion.  We want to clear all our caches at the
        // start but then use them as we recurse.
        if ($topcall) {
            // clear the parent cache
            Record::getParents($pid, true);
        }
        $res = AuthIndex::getIndexAuth($pid,$topcall);
        $rows = array();
        $values = '';
        $has_list_rules = false;
        if (!empty($res)) {
            // add some pre-processed special rules
            foreach ($res as $source_pid => $groups) {
                foreach ($groups as $role => $group) {
                    foreach ($group as $row) {
                        // check for rules on listing to determine if this pid is public or not
                        if ($row['role'] == 'Lister') {
                            $has_list_rules = true;
                        }
                    }   

                }
            }
        }
        // if no lister rules are found, then this pid is publically listable
        if (!$has_list_rules) {
            $res[$pid]['Lister'][] = array('pid' => $pid, 'role' => 'Lister', 
                    'rule' => 'public_list', 'value' => 1);
        }
        // get the group ids
        foreach ($res as $source_pid => $groups) {
            foreach ($groups as $role => $group) {
                $arg_id = AuthRules::getOrCreateRuleGroup($group,$topcall);
                $values .= "('$pid', '$role', '$arg_id'),";
                $rows[] = array('authi_pid' => $pid, 'authi_role' => $role, 'authi_arg_id' => $arg_id);
            }
        }
        $values = rtrim($values,', ');
        // Only check for change of rules at top of recursion, otherwise it slows things down too much.
        if ($topcall) {
            // check if the auth rules have changed for this pid - if they haven't then we don't need to recurse.
            $stmt = "SELECT * FROM {$dbtp}auth_index2 WHERE authi_pid='$pid' ";
            $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            }
            $rules_changed = false;
            // check for added rules
            foreach ($res as $dbrow) {
                $found = false;
                foreach ($rows as $crow) {
                    if ($crow['authi_role'] == $dbrow['authi_role']
                            && $crow['authi_arg_id'] == $dbrow['authi_arg_id']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $rules_changed = true;
                    break;
                }
            }
            if (!$rules_changed) {
                // check for deleted rules
                foreach ($rows as $crow) {
                    $found = false;
                    foreach ($res as $dbrow) {
                        if ($crow['authi_role'] == $dbrow['authi_role']
                                && $crow['authi_arg_id'] == $dbrow['authi_arg_id']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $rules_changed = true;
                        break;
                    }
                }
            }
        } else {
            // We are already recursing 
            $rules_changed = true;
        }
        if ($rules_changed) {
            AuthIndex::clearIndexAuth($pid);
            $stmt = "INSERT INTO {$dbtp}auth_index2 (authi_pid,authi_role,authi_arg_id) VALUES $values ";
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            }
            // get children and update their indexes.
            $rec = new RecordGeneral($pid);
            $children = $rec->getChildrenPids();
            if (!empty($children)) {
                $this->bgp->setStatus("Recursing into ".$rec->getTitle());
            }
            foreach ($children as $child_pid) {
                AuthIndex::setIndexAuthBGP($child_pid,false);
            }
            if (!empty($children)) {
                $this->bgp->setStatus("Finished Index Auth for ".$rec->getTitle());
            }
        }
        if ($topcall) {
            $this->cleanIndex();
        }
        return 1;
    }

    function getIndexAuth($pids, $clearcache=false)
    {
        $pid_cache = &$this->pid_cache;
        $done_pids = &$this->get_auth_done_pids;

        if ($clearcache) {
            $pid_cache = array();
        }
        if (empty($pids)) {
            return array();
        } elseif (!is_array($pids)) {
            $pids = array($pids);
        }
        // don't get the same pids twice
        $pids = array_diff($pids,$done_pids);
        if (empty($pids)) {
            return array();
        }
        foreach ($pids as $pid) {
            $auth_groups = array();
            if (!isset($pid_cache[$pid])) {
            $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
            $stmt = "SELECT rmf_rec_pid as pid, xsdmf_parent_key_match as role, xsdmf_element as rule, rmf_varchar as value 
                FROM {$dbtp}record_matching_field AS r1 
                    INNER JOIN {$dbtp}xsd_display_matchfields AS x1 ON    
                rmf_rec_pid = '$pid'
                AND (r1.rmf_dsid IS NULL or r1.rmf_dsid = '') 
                AND (xsdmf_element in ('!rule!role!Fez_User',
                            '!rule!role!AD_Group',
                            '!rule!role!AD_User',
                            '!rule!role!AD_DistinguishedName',
                            '!rule!role!Fez_Group',
                            '!rule!role!in_AD',
                            '!rule!role!in_Fez',
                            '!inherit_security',
                            '!rule!role!eduPersonTargetedID',
                            '!rule!role!eduPersonAffiliation',
                            '!rule!role!eduPersonScopedAffiliation',
                            '!rule!role!eduPersonPrimaryAffiliation',
                            '!rule!role!eduPersonPrincipalName',
                            '!rule!role!eduPersonOrgUnitDN',
                            '!rule!role!eduPersonPrimaryOrgUnitDN')
                    )
                    AND r1.rmf_xsdmf_id=x1.xsdmf_id
                    ";
            $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                $res = array();
            }	
            $found_inherit_off = false;
            if (!empty($res)) {
                // split into roles
                $groups = Misc::collateArray($res, 'role');
                foreach ($groups as $role => $group) {
                    $auth_groups[$pid][$role] = $group;
                }
                
                // check the inherit flag and merge
                foreach ($res as $row) {
                    if ($row['rule'] == '!inherit_security') {
                        if (!empty($row['value']) && $row['value'] != 'on') {
                            $found_inherit_off = true;
                        }
                    } 
                }
            }

            if (!$found_inherit_off) {
                // get security from parents 
                    $parents1 = Record::getParents($pid);
                $parents = array_keys(Misc::keyArray($parents1, 'pid'));
                    $auth_groups = array_merge_recursive($auth_groups, 
                            AuthIndex::getIndexAuth($parents,false));
            }
                $pid_cache[$pid] = $auth_groups;
            }
            $done_pids[] = $pid;
        }
        $auth_groups = array();
        foreach ($pids as $pid) {
            $auth_groups = array_merge_recursive($auth_groups, $pid_cache[$pid]);
        }
        return $auth_groups;
    }

    function clearIndexAuth($pids)
    {
        if (empty($pids)) {
            return -1;
        } elseif (!is_array($pids)) {
            $pids = array($pids);
        }
        $pids_str = Misc::arrayToSQL($pids);
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        $stmt = "DELETE FROM {$dbtp}auth_index2 WHERE authi_pid IN ($pids_str) ";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }	
        return 1;
    }

    function highestRuleGroup()
    {
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        $stmt = "SELECT max(arg_id) FROM {$dbtp}auth_rule_groups";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
        return $res;
    }

    /**
     *  If there are too many rules in the index that are not used anywhere then delete them.
     */
    function cleanIndex()
    {
        // check for unused rules
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        $stmt = "select count(*) from {$dbtp}auth_rule_groups where not exists (
            select * FROM {$dbtp}auth_index2 where authi_arg_id=arg_id)";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = 0;
        }
        if ($res > 1000) {
            // found a lot of unused rules so lets get rid of them
            $stmt = "delete from {$dbtp}auth_rule_groups where not exists (
                select * FROM {$dbtp}auth_index2 where authi_arg_id=arg_id)";
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            }
            $stmt = "delete from {$dbtp}auth_rule_group_rules where not exists (
                select * FROM {$dbtp}auth_rule_groups where argr_arg_id=arg_id)";
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            }
            $stmt = "delete from {$dbtp}auth_rules where not exists (
                select * FROM {$dbtp}auth_rule_group_rules where argr_ar_id=ar_id)";
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            }
        }
     }
}


?>
