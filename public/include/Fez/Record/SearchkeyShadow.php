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
//


/**
 * Class to handle Shadow functions.
 *
 * @version 1.0
 * @author Aaron Brown <a.brown@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");

class Fez_Record_SearchkeyShadow
{
    protected $_log = null;
    protected $_db = null;
    protected $_pid = null;
    protected $_version = null;

    /**
     * Class constructor.
     * Assign Database and Fezlog Object to local properties.
     * Assign PID and shadow version.
     *
     * @param string $pid
     */
    public function __construct($pid = null)
    {
        $this->_log = FezLog::get();
        $this->_db = DB_API::get();

        $this->_setPid($pid);

        $this->_setVersion();
    }

    /**
     * Set the version timestamp to be used on Shadow table(s) operations.
     * Utilise the version registered on Zend Register from earlier process.
     */
    protected function _setVersion()
    {
        if (!Zend_Registry::isRegistered('version')) {
            Zend_Registry::set('version', Date_API::getCurrentDateGMT());
        }
        $this->_version = Zend_Registry::get('version');
    }

    /**
     * Set the PID for this process
     * @param string $pid
     */
    protected function _setPid($pid = null)
    {
        if (empty($pid)) {
            return false;
        }
        $this->_pid = $pid;
    }


    /**
     * Returns PID
     * @return string
     */
    public function getPid()
    {
        return $this->_pid;
    }


    /**
     * Returns the version used on this PID Search Key process.
     * @return datetime
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Copy the given search key to the appropriate shadow table
     *
     * @param string $sekTable
     */
    public function copySearchKeyToShadow($sekTable)
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $date = $this->_version;
        $pid = $this->_pid;

        $stmt = "INSERT INTO ". APP_TABLE_PREFIX . "record_search_key_". $sekTable."__shadow
               SELECT *, ".$db->quote($date). " FROM ". APP_TABLE_PREFIX . "record_search_key_". $sekTable."
                        WHERE rek_".$sekTable."_pid = ".$db->quote($pid);
        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }
        return $res;
    }

    /**
     * Copy the main record search key to the record search key shadow table
     *
     * @param
     */
    public function copyRecordSearchKeyToShadow()
    {
        $log = FezLog::get();
        $db = DB_API::get();
        $date = $this->_version;
        $pid = $this->_pid;

        $stmt = "INSERT INTO ". APP_TABLE_PREFIX . "record_search_key__shadow
               SELECT *, ".$db->quote($date). " FROM ". APP_TABLE_PREFIX . "record_search_key
                        WHERE rek_pid = ".$db->quote($pid);
        try {
            $res = $db->exec($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return array();
        }
        return $res;
    }

}

