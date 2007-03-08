<?php

class AuthRules 
{
    function getOrCreateRuleGroup($group,$clearcache=false)
    {
        static $gcache;

        if ($clearcache) {
            $gcache = array();
        }
        $rmd5 = AuthRules::getMd5($group);
        // check cache for rule group
        if (isset($gcache[$rmd5])) {
            return $gcache[$rmd5];
        }
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        // does rule exist in table
        $stmt = "SELECT arg_id, arg_md5 FROM {$dbtp}auth_rule_groups WHERE arg_md5='$rmd5' ";
        $res = $GLOBALS["db_api"]->dbh->getRow($stmt,DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = null;
        }
        if (empty($res)) {
            // rule group doesn't exist so add it
            $stmt = "INSERT INTO {$dbtp}auth_rule_groups (arg_md5) VALUES ('$rmd5') ";
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            }
            $arg_id = $GLOBALS["db_api"]->get_last_insert_id();

            $values = '';
            foreach($group as $row) {
                $ar_id = AuthRules::getOrCreateRule($row['rule'], $row['value']);
                $values .= "('$arg_id',  '$ar_id'),";
            }
            $values = rtrim($values, ', ');

            // make an insert statement
            $stmt = "INSERT INTO {$dbtp}auth_rule_group_rules (argr_arg_id,argr_ar_id) VALUES $values ";
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                return -1;
            }
        } else {
            $arg_id = $res['arg_id'];
        }
        $gcache[$rmd5] = $arg_id;
        return $arg_id;
    }

    function getOrCreateRule($rule, $value)
    {
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        // does rule exist in table
        $rule = Misc::escapeString($rule);
        $value = Misc::escapeString($value);
        $stmt = "SELECT ar_id FROM {$dbtp}auth_rules WHERE ar_rule='$rule' and ar_value='$value' ";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = null;
        }
        if (empty($res)) {
            // if the rule is not yet in the table, then add it
            $stmt = "INSERT INTO {$dbtp}auth_rules (ar_rule,ar_value) VALUES ('$rule', '$value') ";
            $res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($res)) {
                Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            } else {
                return $GLOBALS["db_api"]->get_last_insert_id();
            }
        } else {
            return $res;
        }
        return -1;
    }

    /**
      * Get's an md5 that should be the same for rule groups that have the same rules regardless of order
      */
    function getMd5($group) {
        $row_strs = array();
        foreach ($group as $row) {
            $row_strs[] = trim($row['rule']).trim($row['value']);
        }
        asort($row_strs);
        $row_strs = array_unique($row_strs);
        return md5(implode(';',$row_strs));
    }
}

?>
