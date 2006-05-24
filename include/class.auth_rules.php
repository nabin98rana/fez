<?php

class AuthRules 
{

    function getOrCreateRule($rule, $value)
    {
        $dbtp = APP_DEFAULT_DB.'.'.APP_TABLE_PREFIX;
        // does rule exist in table
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
            // for a new rule, have to check if the user matches it
            // other users won't match the rule unless they log out and back in
            Auth::setAuthRulesUsers();
        } else {
            return $res;
        }
        return -1;
    }
    
}

?>
