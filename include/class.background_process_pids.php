<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007, 2010 The University of Queensland,   |
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
// | Authors: Marko Tsoi <m.tsoi@library.uq.edu.au>                       |
// +----------------------------------------------------------------------+
//
//

/**
 * For tracking of the pids associated with background processes
 *
 * @package BackgroundProcess
 **/

class BackgroundProcessPids
{
	/**
	 * Gets the count of the number of background processes running for the pid
	 *
	 * @param string pid
	 * @return int
	 **/
	public function getCountForPid($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$prefix = APP_TABLE_PREFIX;
		$q = "SELECT COUNT(*) FROM {$prefix}background_process_pids ";
		$q .= "JOIN {$prefix}background_process ON (bgpid_bgp_id = bgp_id) ";
		$q .= "WHERE bgpid_pid = ? ";
		$q .= "AND bgp_state = " . BGP_RUNNING;
		$count = $db->fetchOne($q, $pid);
		return $count;
		// $details = self::getForPid($pid);
		// return count($details);
	}
	
	/**
	 * Get all the background processes for the pid
	 *
	 * @param string $pid
	 * @return array
	 **/
	public function getForPid($pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();

		$prefix = APP_TABLE_PREFIX;
		$q = "SELECT bgp_id AS bgpId, bgp_state AS state, bgp_status_message AS statusMessage, bgp_started AS dateStarted, usr_full_name AS username ";
		$q .= "FROM {$prefix}background_process_pids ";
		$q .= "JOIN {$prefix}background_process ON (bgpid_bgp_id = bgp_id) ";
		$q .= "JOIN {$prefix}user ON (bgp_usr_id = usr_id) ";
		$q .= "WHERE bgpid_pid = ? "; // find only for the specified pid
		$q .= "AND bgp_state = " . BGP_RUNNING;

		$details = $db->fetchAll($q, $pid);
		return $details;
	}
	
	/**
	 * Takes the serialised/compacted input that is passed to the BackgroundProcess::register() function
	 * and attemps to extract pids from that. Then inserts those into the database
	 *
	 * @param int $bgpId
	 * @param string $inputs compacted serialised input passed into BackgroundProcess::register() function
	 * @return void
	 **/
	public function insertPids($bgpId, $inputs)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$arrayToCheck = unserialize($inputs);
		$keysToKeep = array('pid', 'pids', 'params');
		$possiblePids = array();
		$keys = array();

		if (is_array($arrayToCheck)) {
			$keys = array_keys($arrayToCheck);	
		}


		foreach ($keysToKeep as $key) {
			if (isset($arrayToCheck[$key])) {
				$possiblePids[$key] = $arrayToCheck[$key];
			}
		}
		
		$this->pidsToInsert = array();
		foreach($possiblePids as $key => $pids) {
			if (!is_array($pids)) {
			 	if (is_string($pids) && self::isValidPid($pids)) {
					$this->pidsToInsert[] = $pids;
				}
			} else {
				array_walk_recursive($pids, array($this, 'checkPidCallback'));
			}
		}
		
		foreach ($this->pidsToInsert as $pid) {
			$params = array('bgpid_bgp_id' => $bgpId, 'bgpid_pid' => $pid);
			$db->insert(APP_TABLE_PREFIX."background_process_pids", $params);
		}
	}
	
	/**
	 * Inserts a single pid
	 *
	 * @param int $bgpId
	 * @param string $pid
	 * @return void
	 **/
	public function insertPid($bgpId, $pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$db->insert(APP_TABLE_PREFIX."background_process_pids", array('bgpid_bgp_id'=>$bgpId, 'bgpid_pid'=>$pid));
	}
	
	/**
	 * Removes a pid from a particular background process id
	 *
	 * @param int $bgpId
	 * @param string $pid
	 * @return void
	 **/
	public function removePid($bgpId, $pid)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$prefix = APP_TABLE_PREFIX;
		$q = "DELETE FROM {$prefix}background_process_pids WHERE bgpid_bgp_id = ? AND bgpid_pid = ? ";
		
		$db->query($q, array($bgpId, $pid));
	}
	
	/**
	 * Determines if an item is a valid pid
	 *
	 * @return void
	 **/
	protected function checkPidCallback($item, $key)
	{
		if (is_string($item) && self::isValidPid($item)) {
			$this->pidsToInsert[] = $item;
		}
	}
	
	/**
	 * Is this a valid pid?
	 *
	 * @param string $stringToCheck
	 * @return boolean
	 **/
	protected function isValidPid($stringToCheck)
	{
		if (strpos(trim($stringToCheck), APP_PID_NAMESPACE . ":") === 0) {
			return Misc::isPid($stringToCheck);
		} else {
			return false;
		}
	}
}