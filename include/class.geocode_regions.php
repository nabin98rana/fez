<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class GeocodeRegions
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

		$db = DB_API::get();

		try {
			$db->insert(APP_TABLE_PREFIX . 'geocode_regions', $record);
		}
		catch (Exception $ex) {
			$log = FezLog::get();
			$log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
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
	function update($countryCode, $regionCode, $record)
	{
		if ($countryCode == '' || $regionCode == '')
			return -2;

		$db = DB_API::get();

		try {
			$db->update(APP_TABLE_PREFIX . 'geocode_regions', $record, 'gcr_country_code = ' . $db->quote($countryCode) . ' AND gcr_region_code = ' . $db->quote($regionCode));
		}
		catch (Exception $ex) {
			$log = FezLog::get();
			$log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
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
			$db->delete(APP_TABLE_PREFIX . 'geocode_regions', 'loc_location = ' . $db->quote($location));
		}
		catch (Exception $ex) {
			$log = FezLog::get();
			$log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
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
		$query = 'SELECT * FROM ' . APP_TABLE_PREFIX . 'geocode_regions WHERE loc_location = ?';

		$db = DB_API::get();
		try {
			$db->fetchRow($query, $location, Zend_Db::FETCH_ASSOC);
		}
		catch (Exception $ex) {
			$log = FezLog::get();
			$log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
			return -1;
		}

		return $result;
	}
}