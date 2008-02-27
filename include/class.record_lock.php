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
// |          Lachlun Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

/**
 * RecordLock
 * This is a static class that handles getting a lock and listing locks
 */ 
class RecordLock
{
    const CONTEXT_NONE = 0;
    const CONTEXT_WORKFLOW = 1;
    
    function getLock($pid, $usr_id, $context=self::CONTEXT_NONE, $extra_context=0)
    {
        if (empty($pid) || !is_string($pid)) {
            return -1;
        }
        if (empty($usr_id) || !is_numeric($usr_id) || preg_match('/^[+]?[0-9]+$/', $usr_id) !== 1) {
            return -1;
        } 
        if (!is_numeric($context) || preg_match('/^[+]?[0-9]+$/', $context) !== 1) {
            return -1;
        } 
        if (!is_numeric($extra_context) || preg_match('/^[+]?[0-9]+$/', $extra_context) !== 1) {
            return -1;
        } 
        $dbtp = APP_TABLE_PREFIX;
        // Check if there is already a lock
        $owner = self::getOwner($pid);
        if ($owner < 0) {
            return -1;  // there was an error
        } elseif ($owner > 0) {
            if ($owner == $usr_id) {
                return 1;  // this user already has the lock
            } else {
                return -1; // someone else has the lock
            }
        }
        
        // Insert the row into the DB.  If two users simultaneously run this, then two rows will be created.
        // After the rows has been inserted, check that no-one else has the PID and delete the row we just made if there is
        $stmt = "INSERT INTO ".$dbtp."record_locks " .
                "SET rl_pid='".$pid."', " .
                    "rl_usr_id=".$usr_id.", " .
                    "rl_context_type=".$context.", " .
                    "rl_context_value=".$extra_context;
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
        }
        $last_id = $GLOBALS["db_api"]->dbh->getLastInsertId($dbtp."record_locks", 'rl_id');
        $stmt = "SELECT * FROM ".$dbtp."record_locks WHERE rl_pid='".$pid."'";
        $lock_res = $GLOBALS["db_api"]->dbh->getAll($stmt);
        if (PEAR::isError($lock_res)) {
            Error_Handler::logError(array($lock_res->getMessage(), $lock_res->getDebugInfo()), __FILE__, __LINE__);
            $lock_res = array();
        }
        if (count($lock_res) > 1) {
            // there is already a lock on the record so delete the one we just made
            $stmt = "DELETE FROM ".$dbtp."record_locks WHERE rl_id=".$last_id;
            $del_res = $GLOBALS["db_api"]->dbh->query($stmt);
            if (PEAR::isError($del_res)) {
                Error_Handler::logError(array($del_res->getMessage(), $del_res->getDebugInfo()), __FILE__, __LINE__);
            }
            $res = -1;
        } else {
           $res = 1;
        }
        return $res;
    }
   
    function releaseLock($pid)
    {
        if (empty($pid) || !is_string($pid)) {
            return -1;
        }
        $dbtp = APP_TABLE_PREFIX;
        $stmt = "DELETE FROM ".$dbtp."record_locks WHERE rl_pid='".$pid."'";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return -1;
        }
        return 1;
    }
   
    function getList($usr_id=null)
    {
        if ($usr_id !== null && (empty($usr_id) || !is_numeric($usr_id) || preg_match('/^[+]?[0-9]+$/', $usr_id) !== 1)) {
            return -1;
        }
        if (!empty($usr_id)) {
            $where = "WHERE rl_usr_id='".$usr_id."'";
        } else {
            $where = '';
        }
        $dbtp = APP_TABLE_PREFIX;
        $stmt = "SELECT * FROM ".$dbtp."record_locks ".$where." ORDER BY rl_id";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        if (empty($res)) {
            $res = array();
        }
        return $res;
    }
   
    /**
     * getOwner
     * Finds out if someone has this lock.  Also checks for expired locks and releases them.
     * @return int - 0 for no owner, -1 on error, otherwise the usr_id
     */
    function getOwner($pid)
    {
        if (empty($pid) || !is_string($pid)) {
            return -1;
        }
        $dbtp = APP_TABLE_PREFIX;
        $stmt = "SELECT * FROM ".$dbtp."record_locks WHERE rl_pid='".$pid."'";
        $lock_res = $GLOBALS["db_api"]->dbh->getRow($stmt, DB_FETCHMODE_ASSOC);
        $res = 0;
        if (PEAR::isError($lock_res)) {
            Error_Handler::logError(array($lock_res->getMessage(), $lock_res->getDebugInfo()), __FILE__, __LINE__);
            $res = -1;
        } else {
            if (!empty($lock_res) && !empty($lock_res['rl_usr_id'])) {
                $res = $lock_res['rl_usr_id'];
            }
        }
        // Check that this lock hasn't expired
        $delete_lock = false;
        switch ($lock_res['rl_context_type']) {
            case self::CONTEXT_WORKFLOW:
                // if the workflow that created this lock no longer exists, then we need to break the lock
                $delete_lock = true;
                if (!empty($lock_res['rl_context_value'])) {
                    $stmt = "SELECT wfses_id FROM ".$dbtp."workflow_sessions WHERE wfses_id=".$lock_res['rl_context_value'];
                    $ctxt_res = $GLOBALS["db_api"]->dbh->getAll($stmt);
                    if (PEAR::isError($ctxt_res)) {
                        Error_Handler::logError(array($ctxt_res->getMessage(), $ctxt_res->getDebugInfo()), __FILE__, __LINE__);
                        $ctxt_res = null;
                    }
                    if (count($ctxt_res) > 0) {
                        $delete_lock = false;
                    }
                }
            break;
            default:
            break;
        }
        if ($delete_lock) {
            self::releaseLock($pid);
            $res = 0;
        }
        
        return $res;
    }
}

?>
