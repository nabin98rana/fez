<?php

class GeocodeCities
{
	/**
	 * Insert a record into the table
	 *
	 * @param array $record
	 * @return integer 1 = success, -1 = an error occurred, -2 = required fields not set
	 */
	function insert($record)
	{
		$insertRecord = array();
		$insertRecord['gcity_country_code'] = $record['country_code'];
		$insertRecord['gcity_region_code'] = $record['region_code'];
		$insertRecord['gcity_location_name'] = $record['location_name'];
		$insertRecord['gcity_city'] = $record['city'];
		$insertRecord['gcity_latitude'] = $record['latitude'];
		$insertRecord['gcity_longitude'] = $record['longitude'];

		$db = DB_API::get();
		$log = FezLog::get();

		try {
			$db->insert(APP_TABLE_PREFIX . 'geocode_cities', $insertRecord);
		}
		catch (Exception $ex) {
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
	function update($record, $countryCode, $regionCode, $cityName)
	{
		if ($countryCode == '' || $regionCode == '' || $cityName == '')
			return -2;

		$db = DB_API::get();
		$log = FezLog::get();

		try {
			$db->update(APP_TABLE_PREFIX . 'geocode_cities',
				$record,
				'gcity_country_code = ' . $db->quote($countryCode) . 
				' AND gcity_region_code = ' . $db->quote($regionCode) .
				' AND gcity_city = ' . $db->quote($cityName));
		}
		catch (Exception $ex) {
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
	function remove($countryCode, $regionCode, $cityName)
	{
		$db = DB_API::get();
		$log = FezLog::get();

		try {
			$db->delete(APP_TABLE_PREFIX . 'geocode_regions',
				'gcity_country_code = ' . $db->quote($countryCode) . 
				' AND gcity_region_code = ' . $db->quote($regionCode) .
				' AND gcity_city = ' . $db->quote($cityName));
		}
		catch (Exception $ex) {
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
		$query = 'SELECT * FROM ' . APP_TABLE_PREFIX . 'geocode_regions WHERE loc_location = ?';

		$db = DB_API::get();
		$log = FezLog::get();

		try {
			$db->fetchRow($query, $location);
		}
		catch (Exception $ex) {
			$log->err($ex);
			return -1;
		}

		return $result;
	}
}