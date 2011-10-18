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
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>,                    |
// |          Rhys Palmer <r.rpalmer@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+

define('BGP_UNDEFINED', 0);
define('BGP_RUNNING',   1);
define('BGP_FINISHED',  2);

include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.background_process_pids.php");
/**
 * This is a virtual class.
 * Subclass this to make a background process with a customised 'run' method.
 */
class BackgroundProcess {
	var $bgp_id;
	var $details;
	var $inputs;
	var $include; // set this to the include file where the subclass is declared
	var $name; // set this to the name of the process where the subclass is declared
	var $states = array(
	0 => 'Undefined',
	1 => 'Running',
	2 => 'Done'
	);
	var $local_session = array();
	var $progress = 0;
	var $wfses_id = null; // id of workflow session to resume when this background process finishes


	/***** Mixed *****/

	function __construct($bgp_id=null)
	{
		$this->bgp_id = $bgp_id;
	}

	function getDetails()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		if (!$this->details || $this->details['bgp_id'] != $this->bgp_id) {
			$dbtp =  APP_TABLE_PREFIX;
			$stmt = "SELECT * FROM ".$dbtp."background_process WHERE bgp_id=".$db->quote($this->bgp_id,'INTEGER');
			try {
				$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
			}
			catch(Exception $ex) {
				$log->err($ex);
				return array();
			}
			$this->details = $res[0];
		}
		return $this->details;
	}

	function serialize()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$serialized = serialize($this);
		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "UPDATE ".$dbtp."background_process SET bgp_serialized=".$db->quote($serialized)." WHERE bgp_id=".$db->quote($this->bgp_id, 'INTEGER');
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
	}

	function setProgress($percent)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "UPDATE ".$dbtp."background_process SET bgp_progress=".$db->quote($percent, 'INTEGER')." WHERE bgp_id=".$db->quote($this->bgp_id, 'INTEGER');
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
		$this->setHeartbeat();
	}

	function incrementProgress()
	{
		$this->setProgress(++$this->progress);
	}

	function setStatus($msg)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		echo $msg."\n";
		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "UPDATE ".$dbtp."background_process SET bgp_status_message=".$db->quote($msg)." WHERE bgp_id=".$db->quote($this->bgp_id, 'INTEGER');
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
		$this->setHeartbeat();
	}

	function setState($state)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "UPDATE ".$dbtp."background_process SET bgp_state=".$db->quote($state)." WHERE bgp_id=".$db->quote($this->bgp_id, 'INTEGER');
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
		$this->setHeartbeat();
	}

	function setHeartbeat()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		$utc_date = Date_API::getSimpleDateUTC();
		$stmt = "UPDATE ".$dbtp."background_process SET bgp_heartbeat=".$db->quote($utc_date)." WHERE bgp_id=".$db->quote($this->bgp_id, 'INTEGER');
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
	}

	function setExportFilename($filename, $headers)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "UPDATE ".$dbtp."background_process SET
            bgp_filename=".$db->quote($filename).",
            bgp_headers=".$db->quote($headers)."
            WHERE bgp_id=".$db->quote($this->bgp_id, 'INTEGER');
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
	}

	function getExportFile()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "SELECT bgp_filename, bgp_headers, bgp_usr_id
            FROM ".$dbtp."background_process WHERE bgp_id=".$db->quote($this->bgp_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}
    $usr_id = Auth::getUserID();

    $isAdministrator = Auth::isAdministrator();     

		if ($usr_id == $res['bgp_usr_id'] || $isAdministrator == true) {
			$headers = explode("\n", $res['bgp_headers']);
			foreach ($headers as $head) {
				header($head);
			}
			readfile($res['bgp_filename']);
		} else {
			echo "Not authorised: Username doesn't match";
		}
		exit;
	}
	
	/**
	 * Marks a pid as finished (by removing it from the table)
	 *
	 * @param string $pid
	 * @return void
	 **/
	public function markPidAsFinished($pid) 
	{
		$log = FezLog::get();
		BackgroundProcessPids::removePid($this->bgp_id, $pid);
	}

	/***** APACHE SIDE *****/

	/**
	 * Start a background process
	 * @param string $inputs A serialized array or object that is the inputs to the process to be run.
	 *                       e.g. serialize(compact('pid','dsID'))
	 * @param int $usr_id The user who will own the process.
	 */
	function register($inputs, $usr_id, $wfses_id = null)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$this->inputs = $inputs;
		$this->wfses_id = $wfses_id; // optional workflow session		
		$dbtp =  APP_TABLE_PREFIX;
		// keep background log files in a subdir so that they don't clutter up the /tmp dir so much
		if (!is_dir(APP_TEMP_DIR."fezbgp")) {
			mkdir(APP_TEMP_DIR."fezbgp");
		}

		$utc_date = Date_API::getSimpleDateUTC();
		$stmt = "INSERT INTO ".$dbtp."background_process (bgp_usr_id,bgp_started,bgp_name,bgp_include)
            VALUES (".$db->quote($usr_id).", ".$db->quote($utc_date).", ".$db->quote($this->name).",".$db->quote($this->include).")";
		try {
			$db->exec($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		$this->bgp_id = $db->lastInsertId($dbtp."background_process", "bgp_id");
		
		// insert the pids into the bgp pids table
		$bgpPids = new BackgroundProcessPids();
		$bgpPids->insertPids($this->bgp_id, $inputs);
		
		$this->serialize();
		$command = APP_PHP_EXEC." \"".APP_PATH."misc/run_background_process.php\" ".$this->bgp_id." \""
		.APP_PATH."\" > ".APP_TEMP_DIR."fezbgp/fezbgp_".$this->bgp_id.".log";
		if ((stristr(PHP_OS, 'win')) && (!stristr(PHP_OS, 'darwin'))) { // Windows Server
			pclose(popen("start /min /b ".$command,'r'));
		} else {
			exec($command." 2>&1 &");
		}
		return $this->bgp_id;
	}


	/***** CLI SIDE *****/

	/**
	 * subclass this function for your background process
	 */
	function run()
	{
	}

	/**
	 * Authenticate the background process
	 */
	function setAuth()
	{
		$log = FezLog::get();
		$db = DB_API::get();

		global $auth_isBGP, $auth_bgp_obj;
		$auth_isBGP = true;
		$GLOBALS['auth_bgp_obj'] = &$this;

		$session =& $this->local_session;

		$dbtp =  APP_TABLE_PREFIX;
		$stmt = "SELECT * FROM ".$dbtp."background_process WHERE bgp_id=".$db->quote($this->bgp_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
		}

		$usr_id = $res['bgp_usr_id'];
		$usr_obj = new User;
		$user_det = $usr_obj->getDetailsByID($usr_id);
		$username = $user_det['usr_username'];

		if( strcmp($username,"") == 0 )
		{
			$log->warn(array("WARNING: username is blank.  Cannot authenticate for running background process test.  Most likely the user (id: '".$usr_id."') doesn't exist or has been deleted."), __FILE__, __LINE__);
		}
		// the password is not used.  The auth system won't be able to access any AD info
		Auth::LoginAuthenticatedUser($username, 'blah');
	}

	function getSession()
	{
		return $this->local_session;
	}


    /**
     * Collects & returns details required for ETA calculation.
     *
     * @return array : The configurations that required for calculating bgp ETA.
     */
    function getETAConfig()
    {
        $eta_cfg = array();
        $eta_cfg['bgp_details'] = $this->getDetails();
        $eta_cfg['timezone'] = Date_API::getPreferredTimezone($bgp_details["bgp_usr_id"]);

        return $eta_cfg;
    }


    /**
     * Returns an array of calculated ETA, which consists of time per object, expected finish time.
     *
     * @param integer $record_counter : The counter number of PID that being processed.
     * @param integer $record_count : The total number of PIDs to process on BGP.
     * @param array $cfg : The configurations to assist ETA calculation.
     * @return array : The ETA calculation results.
     */
    function getETA($record_counter=0, $record_count=0, $cfg=null)
    {
        $eta = array();

        $utc_date = Date_API::getSimpleDateUTC();

        $records_left = $record_count - $record_counter;

        $time_per_object = Date_API::dateDiff("s", $cfg['bgp_details']['bgp_started'], $utc_date);
        $time_per_object = round(($time_per_object / $record_counter), 2);
        $eta['time_per_object'] = $time_per_object;

        $exp_finish_time = new Date($utc_date);
        $exp_finish_time->addSeconds($time_per_object * $records_left);
        $eta['expected_finish'] = Date_API::getFormattedDate($exp_finish_time->getTime(), $cfg['timezone']);

        $eta['progress'] = intval(100 * $record_counter / $record_count);

        return $eta;
    }


}