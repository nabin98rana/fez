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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//
/**
 * WorkflowTrigger
 * Defines when workflows are triggered
 */
class WorkflowTrigger
{
	/**
	 * @return array The types of triggers supported.  key is the trigger id, value is the name of the trigger.
	 */
	function getTriggerTypes()
	{
		return array(
		0 => "Ingest", // for uploading the actual binaries and datastreams
		1 => "Update", // updating metadata records
		3 => "Delete", // deleting a record
		4 => "Create",  // creating a record
		5 => "Datastream",  // triggers on datastreams
		6 => "Export",  // exporting records
		7 => "Bulk Change",  // Bulk/Mass changes to many objects at once
		8 => "Bulk Change Search" // Changes to objects from a search result
		);
	}


	/**
	 * Maps the trigger name back to a trigger id
	 * Uses just the first letter of the trigger name supplied.
	 * @param string $trigger The trigger name
	 * @return integer trigger id.
	 */
	function getTriggerId($trigger)
	{
		$triggers = WorkflowTrigger::getTriggerTypes();
		return array_search(ucfirst($trigger), $triggers);
	}

	/**
	 * Maps a trigger id to trigger name
	 * @param integer trigger id
	 * @return string trigger name
	 */
	function getTriggerName($trigger)
	{
		$triggers = WorkflowTrigger::getTriggerTypes();
		return $triggers[$trigger];
	}

	// wft_options is a bit field
	// 0x00000001 show in list
	function showInList($wft_options)
	{
		return $wft_options & 0x01 > 0;
	}
	function setShowInList($wft_options, $value = true)
	{
		if ($value) {
			return $wft_options | 0x01;
		} else {
			return $wft_options & (~0x01);
		}
	}

	/**
	 * Updates a trigger from the POST variables
	 */
	function update()
	{
		return WorkflowTrigger::edit('update');
	}

	/**
	 * Inserts a trigger from the POST variables
	 */
	function insert($params = array())
	{
		return WorkflowTrigger::edit('insert', $params);
	}

	/**
	 * Removes a trigger from the POST variables
	 */
	function remove($params = array())
	{
		return WorkflowTrigger::edit('delete',$params);
	}

	function removeByWorkflow($wfl_ids)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "DELETE FROM " . APP_TABLE_PREFIX . "workflow_trigger WHERE wft_wfl_id IN (".Misc::arrayToSQLBindStr($wfl_ids).")";
		try {
			$db->query($stmt, $wfl_ids);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
	}

	/**
	 * Gets the values for updating or inserting a trigger from the POST variables
	 * @return string MySQL style string to be put in the SET part of the query
	 */
	function getPostSetStr($params = array())
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (empty($params)) {
			$params = &$_POST;
		}
		$post_fields = array('wft_pid', 'wft_type_id', 'wft_xdis_id', 'wft_wfl_id',
              'wft_mimetype', 'wft_icon', 'wft_ret_id');
		$set_str = 'SET ';
		foreach ($post_fields as $post_field) {
			$set_str .= " $post_field=".$db->quote(@$params[$post_field]).", ";
		}
		if (isset($params['wft_options'])) {
			$set_str .= " wft_options=".$db->quote(@$params['wft_options'])." ";
		} else {
			$wft_options = WorkflowTrigger::setShowInList(0, Misc::checkBox($params['wft_option_show_in_list']));
			$set_str .= " wft_options='".$wft_options."' ";
		}
		return $set_str;
	}

	/**
	 * Modifies, Creates or Deletes a trigger record (depending on $action).  get's values from the
	 * POST variables
	 */
	function edit($action='insert', $params = array())
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$bind_params = array();
		switch($action) {
			case 'update':
				$wft_id = Misc::GETorPOST('wft_id');
				$wherestr = " WHERE wft_id=".$db->quote($wft_id, 'INTEGER');
				$actionstr ="UPDATE";
				$set_str = WorkflowTrigger::getPostSetStr();
				break;
			case 'insert':
				$wherestr = "";
				$actionstr ="INSERT INTO";
				$set_str = WorkflowTrigger::getPostSetStr($params);
				break;
			case 'delete':
				if (empty($params)) {
					$ids = Misc::GETorPOST("items");
				} else {
					$ids = $params['items'];
				}
				$bind_params = $ids;
				$wherestr = " WHERE wft_id IN (".Misc::arrayToSQLBindStr($ids).")";
				$actionstr ="DELETE FROM";
				$set_str = '';
				break;
		}
		$stmt = $actionstr." " . APP_TABLE_PREFIX . "workflow_trigger ".$set_str." ".$wherestr;
		try {
			$db->query($stmt, $bind_params);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return -1;
		}
		return 1;
	}

	/**
	 * Get a list of workflow triggers
	 * @param string $pid Record that triggers are associcated with
	 * @param string  wherestr extra query refinement
	 * @return array List of trigger items.
	 */
	function getList($pid, $wherestr='')
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "workflow_trigger
				INNER JOIN " . APP_TABLE_PREFIX . "workflow on (wfl_id = wft_wfl_id) WHERE wft_pid=".$db->quote($pid)."
            ".$wherestr." ORDER BY wft_type_id, wft_xdis_id";
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			$res = array();
		}
		foreach ($res as $key => $row) {
			$res[$key]['wft_options_split']['show_in_list'] = WorkflowTrigger::showInList($row['wft_options']);
		}
		return $res;
	}

	function getWorkflowID($wft_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (!is_numeric($wft_id)) {
			return false;
		}
		$stmt = "SELECT wft_wfl_id FROM " . APP_TABLE_PREFIX . "workflow_trigger
				 WHERE wft_id=".$db->quote($wft_id, 'INTEGER');
		
		try {
			$res = $db->fetchOne($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		return $res;
	}

	/**
	 * Get a list of workflow triggers
	 * @param string $pid Record that triggers are associcated with
	 * @param string  wherestr extra query refinement
	 * @return array List of trigger items.
	 */
	function getAssocList($pid, $wherestr='')
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT wft_id, wfl_title
                 FROM " . APP_TABLE_PREFIX . "workflow_trigger
				 INNER JOIN " . APP_TABLE_PREFIX . "workflow on (wfl_id = wft_wfl_id) 
				 WHERE wft_pid=".$db->quote($pid)." ".$wherestr." 
                 ORDER BY wft_type_id, wft_xdis_id";
		
		try {
			$res = $db->fetchPairs($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			$res = array();
		}
		return $res;
	}

	function getListByWorkflow($wfl_id, $wherestr='')
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "workflow_trigger
                WHERE wft_wfl_id=".$db->quote($wfl_id, 'INTEGER')." ".$wherestr." ORDER BY wft_type_id, wft_xdis_id";
		
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			$res = array();
		}
		return $res;
	}

	/**
	 * get list of triggers for a pid of a certain trigger type
	 * @param string pid Record that has the triggers
	 * @param string or integer $trigger The trigger id
	 * @return list of trigger items
	 */
	function getListByTrigger($pid, $trigger)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (!Misc::isInt($trigger)) {
			$trigger = WorkflowTrigger::getTriggerId($trigger);
		}
		return WorkflowTrigger::getList($pid, " AND wft_type_id=".$db->quote($trigger, 'INTEGER')." ");
	}

	/**
	 * get list of triggers for a pid of a certain trigger type
	 * @param string pid Record that has the triggers
	 * @param string or integer $trigger The trigger id
	 * @return list of trigger items
	 */
	function getAssocListByTrigger($pid, $trigger)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (!Misc::isInt($trigger)) {
			$trigger = WorkflowTrigger::getTriggerId($trigger);
		}
		return WorkflowTrigger::getAssocList($pid, " AND wft_type_id=".$db->quote($trigger, 'INTEGER')." ");
	}


	/**
	 * Get list of triggers for a record that have a given trigger type and xdis_id
	 * @param string pid record id
	 * @param string or integer Trigger id
	 * @param integer $xdis_id Display id
	 * @param boolean $strict If strict is true, then the default xdis_id won't be allowed in the list.
	 * This means that triggers where xdis_id = -1 (for Any) won't be allowed in the results.
	 * @return List of triggers
	 */
	function getListByTriggerAndXDIS_ID($pid, $trigger, $xdis_id, $strict=false)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (!Misc::isInt($trigger)) {
			$trigger = WorkflowTrigger::getTriggerId($trigger);
		}
		if (!$strict) {
			$orstr = " OR wft_xdis_id=-1 ";
		} else {
			$orstr = "";
		}
		return WorkflowTrigger::getList($pid, " AND wft_type_id=".$db->quote($trigger, 'INTEGER')."
                AND (wft_xdis_id=".$db->quote($xdis_id, 'INTEGER')." ".$orstr." ) ");
	}

	/**
	 * Get list of triggers for a record that have a given trigger type and ret_id
	 * @param string pid record id
	 * @param string or integer Trigger id
	 * @param integer $ret_id Object Type id
	 * @param boolean $strict If strict is true, then the default ret_id won't be allowed in the list.
	 * This means that triggers where ret_id = 0 (for Any) won't be allowed in the results.
	 * @return List of triggers
	 */
	function getListByTriggerAndRET_ID($pid, $trigger, $ret_id, $strict=false)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (!Misc::isInt($trigger)) {
			$trigger = WorkflowTrigger::getTriggerId($trigger);
		}
		if (!$strict) {
			$orstr = " OR wft_ret_id=0 ";
		} else {
			$orstr = "";
		}
		return WorkflowTrigger::getList($pid, " AND wft_type_id=".$db->quote($trigger, 'INTEGER')."
                AND (wft_ret_id=".$db->quote($ret_id, 'INTEGER')." ".$orstr." ) AND wft_xdis_id != '-2' ");
	}

	/**
	 * Get list of triggers for a record that have a given trigger type and ret_id and xdis_id
	 * @param string pid record id
	 * @param string or integer Trigger id
	 * @param integer $ret_id Object Type id
	 * @param boolean $strict If strict is true, then the default ret_id won't be allowed in the list.
	 * This means that triggers where ret_id = 0 (for Any) won't be allowed in the results.
	 * @return List of triggers
	 */
	function getListByTriggerAndRET_IDAndXDIS_ID($pid, $trigger, $ret_id, $xdis_id, $strict=false)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		if (!Misc::isInt($trigger)) {
			$trigger = WorkflowTrigger::getTriggerId($trigger);
		}
		if (!$strict) {
			$orstrRET = " OR wft_ret_id=0 ";
			$orstrXDIS = " OR wft_xdis_id=-1 ";
		} else {
			$orstrRET = "";
			$orstrXDIS = " OR wft_xdis_id=-1 ";
		}
		return WorkflowTrigger::getList($pid, " AND wft_type_id=".$db->quote($trigger, 'INTEGER')."
                AND ((wft_ret_id=".$db->quote($ret_id, 'INTEGER')." ".$orstrRET." ) AND (wft_xdis_id=".$db->quote($xdis_id,'INTEGER')." ".$orstrXDIS." )) ");
	}


	/**
	 * @param array $options Associative array, can have
	 *                         xdis_id - doctype to match against (-1 for any, -2 workflow selects doctype),
	 *                         ret_id - record type to match against (-1 for any),
	 *                         strict_xdis - don't allow -1 xdis in results,
	 *                         strict_ret - don't allow -1 record type in results
	 */
	function getFilteredList($pid, $options)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		// these values may be overwritten by extract
		$ret_id = 0;
		$xdis_id = -1;
		$strict_ret = true;
		$strict_xdis = false;
		$any_ret = false;
		extract($options);
		if (!Misc::isInt($trigger)) {
			$trigger = WorkflowTrigger::getTriggerId($trigger);
		}
		if (!$strict_ret) {
			$orstr_ret = " OR wft_ret_id=0 ";
		} else {
			$orstr_ret = "";
		}
		if (!$strict_xdis) {
			$orstr_xdis= " OR wft_xdis_id=-1 ";
		} else {
			$orstr_xdis = "";
		}
		$ret_str = '1';
		if (!$any_ret) {
			$ret_str = "wft_ret_id=".$db->quote($ret_id, 'INTEGER');
		}
		return WorkflowTrigger::getList($pid, " AND wft_type_id=".$db->quote($trigger, 'INTEGER')."
                AND (".$ret_str." ".$orstr_ret." ) AND (wft_xdis_id=".$db->quote($xdis_id, 'INTEGER')." ".$orstr_xdis.") ");
	}

	/**
	 * Get an ingest trigger for a datastream based on PID, XDIS_ID and mimetype
	 * @param string pid record id
	 * @param integer $xdis_id Display id
	 * @param string mimetype The mimetype of the datastream
	 * @param boolean $strict_xdis_id If strict is true, then the default xdis_id won't be allowed in the list.
	 * This means that triggers where xdis_id = -1 (for Any) won't be allowed in the results.
	 * @return List of triggers
	 */
	function getIngestTrigger($pid, $xdis_id, $mimetype, $strict_xdis_id=false)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$trigger = WorkflowTrigger::getTriggerId('Ingest');
		if (!empty($mimetype)) {
			$list = WorkflowTrigger::getList($pid, " AND wft_type_id=".$db->quote($trigger, 'INTEGER')."
					AND wft_xdis_id=".$db->quote($xdis_id,'INTEGER')." AND wft_mimetype LIKE ".$db->quote('%'.$mimetype.'%'));
			if (empty($list) && !$strict_xdis_id) {
				$list = WorkflowTrigger::getList($pid, " AND wft_type_id=".$db->quote($trigger, 'INTEGER')."
						AND wft_xdis_id=-1 AND wft_mimetype LIKE ".$db->quote('%'.$mimetype.'%'));
			}
		}
		if (empty($list)) {
			$list = WorkflowTrigger::getList($pid, " AND wft_type_id=".$db->quote($trigger, 'INTEGER')."
                    AND wft_xdis_id=".$db->quote($xdis_id, 'INTEGER')." AND wft_mimetype='' ");
		}
		if (empty($list) && !$strict_xdis_id) {
			$list = WorkflowTrigger::getList($pid, " AND wft_type_id=".$db->quote($trigger, 'INTEGER')."
                    AND wft_xdis_id=-1 AND wft_mimetype='' ");
		}
		return @$list[0];
	}

	/**
	 * Get the trigger details
	 * @param integer $wft_id trigger id
	 * @return array Associtaive array of trigger record details
	 */
	function getDetails($wft_id)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = "SELECT * FROM " . APP_TABLE_PREFIX . "workflow_trigger
            WHERE wft_id=".$db->quote($wft_id, 'INTEGER');
		try {
			$res = $db->fetchRow($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return $res;
		}
		$res['wft_options_split']['show_in_list'] = WorkflowTrigger::showInList($res['wft_options']);
		return $res;
	}

	function exportTriggers($wfl_id, &$wfs_elem)
	{
		$wfts = WorkflowTrigger::getListByWorkflow($wfl_id);
		foreach ($wfts as $wft) {
			$wf_elem = $wfs_elem->ownerDocument->createElement('WorkflowTrigger');
			$wf_elem->setAttribute('wft_id', $wft['wft_id']);
			$wf_elem->setAttribute('wft_pid', $wft['wft_pid']);
			$wf_elem->setAttribute('wft_type_id', $wft['wft_type_id']);
			$wf_elem->setAttribute('wft_xdis_id', $wft['wft_xdis_id']);
			$wf_elem->setAttribute('wft_order', $wft['wft_order']);
			$wf_elem->setAttribute('wft_mimetype', $wft['wft_mimetype']);
			$wf_elem->setAttribute('wft_icon', $wft['wft_icon']);
			$wf_elem->setAttribute('wft_ret_id', $wft['wft_ret_id']);
			$wf_elem->setAttribute('wft_options', $wft['wft_options']);
			$wfs_elem->appendChild($wf_elem);
		}
	}

	function importTriggers($xworkflow, $wfl_id)
	{
		$xpath = new DOMXPath($xworkflow->ownerDocument);
		$xtrigs = $xpath->query('WorkflowTrigger', $xworkflow);
		foreach ($xtrigs as $xtrig) {
			$params = array(
                'wft_pid' => $xtrig->getAttribute('wft_pid'),
                'wft_type_id' => $xtrig->getAttribute('wft_type_id'),
                'wft_xdis_id' => $xtrig->getAttribute('wft_xdis_id'),
                'wft_order' => $xtrig->getAttribute('wft_order'),
                'wft_mimetype' => $xtrig->getAttribute('wft_mimetype'),
                'wft_icon' => $xtrig->getAttribute('wft_icon'),
                'wft_ret_id' => $xtrig->getAttribute('wft_ret_id'),
                'wft_options' => $xtrig->getAttribute('wft_options'),
                'wft_wfl_id' => $wfl_id
			);
			WorkflowTrigger::insert($params);
		}
	}

}
