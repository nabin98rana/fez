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

/**
 * This script is to allow smooth migration on Fez system from Fedora based record storaged to non-Fedora (database based).
 * The script ONLY supports removing Fedora for good, it is NOT intended for migrating the other way around.
 *
 * It executes individual migration scripts, such as:
 * - migrates existing record from Fedora,
 * - migrates attached datastreams,
 * - runs sanity checking
 *
 * @version 1.0, 2012-03-08
 * @author Elvi Shu <e.shu at library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */

include_once(APP_INC_PATH . 'class.bgp_index_object.php');
include_once(APP_INC_PATH . 'class.reindex.php');
include_once(APP_INC_PATH . 'class.record_object.php');

class FixFromFedoraToDatabase
{
    protected $_log = null;
    protected $_db = null;
    protected $_env = null;
    protected $_aws = null;
    protected $_shadowTableSuffix = "__shadow";
    protected $_tidy;

    public function __construct()
    {
        $this->_log = FezLog::get();
        $this->_db = DB_API::get();
        $this->_aws = AWS::get();
        $this->_tidy = new tidy;
    }

    public function run()
    {
        $this->fixManagedContent();
    }

    /**
     * Run Fedora managed content migration script & security for the attached files.
     */
    private function fixManagedContent()
    {
        $stmt = "select dsi_dsid, dsi_pid from fez_datastream_info__shadow where dsi_dsid like 'FezACML_%' and dsi_dsid not in (select dsi_dsid from fez_datastream_info) group by dsi_pid, dsi_dsid";

        $ds = [];
        try {
            $res = $this->_db->fetchAssoc($stmt);
            if (empty($res)) {
                return true;
            }
            foreach ($res as $d) {
                $ds[$d['dsi_dsid'] . $d['dsi_pid']] = [
                    'pid' => $d['dsi_pid'],
                    'dsName' => $d['dsi_dsid']
                ];
            }
        } catch (Exception $ex) {
            echo "\n<br /> Failed to fetch. Error: " . $ex->getMessage();
            return false;
        }

        $totalDs = count($ds);
        $counter = 0;
        foreach ($ds as $k => $v) {
            $counter++;
            $pid = $v['pid'];
            $FezACML_dsID = $v['dsName'];

            // Check the DB connection is still active and attempt to re-establish
            // the connection if it has gone away
            if (!$this->_db->isConnected()) {
                if (!$this->reEstablishDBConn()) {
                    echo " - Failed to re-establish the DB connection\n";
                    return false;
                }
            }
            echo "\n - Doing PID $counter/$totalDs ($pid)\n";
            Zend_Registry::set('version', Date_API::getCurrentDateGMT());

            $acml = $this->getFezACML($pid, 'FezACML');
            Fedora_API::callPurgeDatastream($pid, $FezACML_dsID);
            if (!empty($acml)) {
                $location = APP_TEMP_DIR . $FezACML_dsID;
                file_put_contents($location, $acml);
                Fedora_API::callAddDatastream($pid, $FezACML_dsID, $location,
                    'FezACML security for datastream', 'A', 'text/xml');
                unlink($location);
            }

        }
        return true;
    }

    private function getFezACML($pid, $dsID)
    {
        $result = Misc::processURL(APP_FEDORA_GET_URL . "/" . $pid . "/" . $dsID, false, null, null, null, 10, true);
        if ($result['success'] === 0) {
            return '';
        }
        $xmlACML = $result['response'];
        $this->_tidy->parseString($xmlACML, [
            'indent' => TRUE,
            'input-xml' => TRUE,
            'output-xml' => TRUE,
            'wrap' => 0,
        ], 'utf8');
        $this->_tidy->cleanRepair();
        return (string)$this->_tidy;
    }

    private function reEstablishDBConn($retries = 0)
    {
        if ($this->_db->isConnected()) {
            return true;
        }
        try {
            $this->_db->getConnection();
            return true;
        } catch (Exception $e) {
        }

        if ($retries < 5) {
            $retries++;
            sleep($retries);
            return $this->reEstablishDBConn($retries);
        } else {
            return FALSE;
        }
    }
}
