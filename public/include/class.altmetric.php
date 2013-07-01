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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle Altmetric service calls.
 *
 * @version 1.0, June 2013
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 *
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.validation.php");
include_once(APP_INC_PATH . "class.date.php");

class Altmetric
{
    /**
     * Given a DOI fetch information about an article
     *
     * @param string $doi The DOI of the article to fetch info for
     * @return string
     */
    public function fetchInformation($doi)
    {
        $url = ALTMETRIC_API_URL . '/fetch/doi/' . $doi . '?key=' . ALTMETRIC_API_KEY;
        $info = $this->doServiceRequest($url);

        if (@$info->altmetric_id) {
            $this->saveAltmetricInfo($doi, $info);
        }

        return $info;
    }


    /**
     * Method inserts/updates Altmetric information retrieved from the service
     *
     * @param $doi   The DOI of the article
     * @param $info The Altmetric info retrieved
     * @return bool True if the insert was successful else false
     */
    private static function saveAltmetricInfo($doi, $info)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

        $data = array(
            'as_amid' => $info->altmetric_id,
            'as_doi'  => $doi,
            'as_score' => round($info->score),
            'as_last_checked' => time()
        );

        try {
            $as_id = $db->fetchOne(
                'SELECT as_id FROM ' . $dbtp . 'altmetric WHERE as_amid = ?',
                array($info->altmetric_id)
            );
        }
        catch(Exception $ex) {
            $log->err($ex);
            return false;
        }
        if ($as_id) {
            // Update existing entry
            try {
                $db->update($dbtp . 'altmetric', $data, 'as_id = ' . $db->quote($as_id, 'INTEGER'));
            }
            catch(Exception $ex) {
                $log->err($ex);
                return false;
            }
        } else {
            // Insert new entry
            try {
                $data['as_created'] = time();
                $db->insert($dbtp . 'altmetric', $data);
            }
            catch(Exception $ex) {
                $log->err($ex);
                return false;
            }
        }

        return true;
    }


    /**
     * Returns the full path to the file that keeps the process ID of the
     * running script.
     *
     * @return  string The full path of the process file
     */
    private function getProcessFilename()
    {
        return APP_PATH . 'misc/altmetric.pid';
    }

    /**
     * Checks whether it is safe or not to fetch data from the service
     * download script.
     *
     * @return  boolean
     */
    public function isSafeToRun()
    {
        $pid = $this->getProcessID();
        $pid_file = $this->getProcessFilename();
        $safe_to_run = false;

        // Check for the process file, and also check that it has not been
        // orphaned by a previous script crash - this is based on the
        // assumption that if it was last modified over 24 hours ago the
        // previous script probably died
        if (! ($pid && (filemtime($pid_file) >= (time() - 86400)))) {
            // create the pid file and say it's safe to run
            $fp = @fopen($pid_file, 'w');
            @fwrite($fp, getmypid());
            @fclose($fp);
            $safe_to_run = true;
        }

        return $safe_to_run;
    }


    /**
     * Removes the process file to allow other instances of this script to run.
     *
     * @return  void
     */
    public function endRun()
    {
        @unlink($this->getProcessFilename());
    }

    /**
     * Returns the process ID of the script from a file
     *
     * @return  integer The process ID of the script
     */
    private function getProcessID()
    {
        $pid = false;
        $pid_file = $this->getProcessFilename();

        if (@file_exists($pid_file)) {
            $pid = trim(implode('', file($pid_file)));
        }
        return $pid;
    }


    /**
     * Method used to perform a service request
     *
     * @param   string $url The service endpoint URL
     * @return  mixed The JSON returned by the service or false if the service request failed
     */
    private function doServiceRequest($url)
    {
        $log = FezLog::get();

        // Do the service request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        $json = curl_exec($ch);

        if (curl_errno($ch)) {
            $log->err(array(curl_error($ch)." ".$url, __FILE__, __LINE__));
            return false;
        } else {
            curl_close($ch);
        }

        return json_decode($json);
    }
}