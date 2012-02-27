<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once(APP_INC_PATH . "class.db_api.php");

class GeocodeLocationCache
{
	/**
	 * Insert a record into the table
	 *
	 * @param array $record
	 * @return integer 1 = success, -1 = an error occurred, -2 = required fields not set
	 */
	function insert($record)
	{
		if (!isset($record['location']))
			return -2;

		$insert = "INSERT INTO " . APP_TABLE_PREFIX . "geocode_location_cache (loc_location, loc_latitude, loc_longitude, loc_accuracy) VALUES ";
		$insert .= "('" . Misc::escapeString($record['location']) . "', {$record['latitude']}, {$record['longitude']}, {$record['accuracy']})";

		$insertRecord = array();
		$insertRecord['loc_location'] = $record['location'];
		$insertRecord['loc_latitude'] = $record['latitude'];
		$insertRecord['loc_longitude'] = $record['longitude'];
		$insertRecord['loc_accuracy'] = $record['accuracy'];

		$db = DB_API::get();

		try {
			$db->insert(APP_TABLE_PREFIX . 'geocode_location_cache', $insertRecord);
		}
		catch (Exception $ex) {
			$log = FezLog::get();
			$log->err($ex);
			return -1;
		}
        return 1;
	}

	/**
	 * Updates a row in the database based on the primary key
	 *
	 * @param array $record
	 * @return integer 1 = success, -1 = an error occurred, -2 = primary key was not set
	 */
	function update($record)
	{
		if (!isset($record['loc_location']))
			return -2;

		$query .= implode(',', $updateParams);
		$query .= "WHERE loc_location = ? ";

		$db = DB_API::get();

		try {
			$db->update(APP_TABLE_PREFIX . 'geocode_location_cache', $record, 'loc_location = ' . $db->quote($record['loc_location']));
		}
		catch (Exception $ex) {
			$log = FezLog::get();
			$log->err($ex);
			return -1;
		}

		return 1;
	}

	/**
	 * Removes a row from the table
	 *
	 * @param string $location
	 * @return integer 1 = success, -1 = error occurred
	 */
	function remove($location)
	{
		$db = DB_API::get();

		try {
			$db->delete(APP_TABLE_PREFIX . 'geocode_location_cache', 'loc_location = ' . $db->quote($location));
		}
		catch (Exception $ex) {
			$log = FezLog::get();
			$log->err($ex);
			return -1;
		}

		return 1;
	}

	/**
	 * Gets a single row based on the primary key
	 *
	 * @param string $location
	 * @return array|integer an array of results if successful, -1 if an error occurred
	 */
	function get($location)
	{
		$query = 'SELECT * FROM ' . APP_TABLE_PREFIX . 'geocode_location_cache WHERE loc_location = ?';

		$db = DB_API::get();
		$log = FezLog::get();

		try {
			$result = $db->fetchRow($query, array($location), Zend_Db::FETCH_ASSOC);
		}
		catch (Exception $ex) {
			$log->err($ex);
			return -1;
		}

		return $result;
	}
}