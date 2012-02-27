<?php

include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.inc.php';
include_once APP_INC_PATH.'class.db_api.php';
include_once APP_INC_PATH.'class.googlemap.php';
include_once APP_INC_PATH.'class.geocode_cities.php';
include_once APP_INC_PATH.'class.geocode_location_cache.php';
include_once APP_INC_PATH.'class.geocode_regions.php';

$log = FezLog::get();

$logString = "Starting Geocoding: " . date('Y-m-d H:i:s');
echo $logString . "\n";
$log->info($logString);

try {
	$logString = "Geocoding Regions";
	echo "{$logString}\n";
	$log->info($logString);
	geocodeRegions();

	$logString = "Generating temporary table";
	echo "{$logString}\n";
	$log->info($logString);
	generateTemporaryTable();

	$logString = "Geocoding Cities";
	echo "{$logString}\n";
	$log->info($logString);
	geocodeCities();
	
	$logString = "Geocoding the location cache";
	echo "{$logString}\n";
	$log->info($logString);
	geocodeLocationCache();
}
catch (Exception $ex) {
	$logString = "Exhausted quota of requests for google geocoding for current 24 hours";
	echo "\tWarning: {$logString}\n";
	$log->warn($logString);
}

$logString = "Finished Geocoding: " . date('Y-m-d H:i:s');
echo "{$logString}\n";
$log->info($logString);

// ========================================
// Geocode the various regions if necessary
// ========================================
function geocodeRegions()
{
	$query = 'SELECT * FROM ' . APP_TABLE_PREFIX . 'geocode_regions WHERE gcr_latitude IS NULL ';

	$db = DB_API::get();
	$log = FezLog::get();

	try {
		$regions = $db->fetchAll($query);
	}
	catch (Exception $ex) {
		$log = FezLog::get();
		$log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
		return;
	}

	// geocode regions
	foreach($regions as $region)
	{
		$address = $region['gcr_location_name'] . ', ' . $region['gcr_country_code'];
		$gmap = new GoogleMap();

		try {
			$hasGeocodeResult = $gmap->getInfoLocation($address);
		}
		catch (Exception $ex) {
			$log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
			throw $ex;
		}

		if ($hasGeocodeResult)
		{
			$rec = array();
			$rec['gcr_latitude'] = $gmap->getLatitude();
			$rec['gcr_longitude'] = $gmap->getLongitude();
			GeocodeRegions::update($rec, $region['gcr_country_code'], $region['gcr_region_code']);
		}
		sleep(1);
	}
}

// ============================
// Generate the temporary table
// ============================
function generateTemporaryTable()
{
	$db = DB_API::get();
	$log = FezLog::get();
	
	// get list of locations
	// create a temporary table (speeds up processing by order of magnitude)
	$query = 'DROP TABLE tmp_geocode_locations';
	try {
		$db->exec($query);
	}
	catch (Exception $ex) {
		null; // don't do anything here.
	}

	$query = 'CREATE TABLE tmp_geocode_locations AS SELECT DISTINCT stl_city, stl_region, stl_country_code FROM ' . APP_TABLE_PREFIX . 'statistics_all';
	try {
		$db->exec($query);
	}
	catch (Exception $ex) {
		$log->err('Message: ' . $ex->getMessage() . ', File: ' . __FILE__ . ', Line: ' . __LINE__);
		throw $ex;
	}
}

// ====================================================
// Geocode any cities that haven't been geocoded so far
// ====================================================
function geocodeCities()
{
	$db = DB_API::get();
	$log = FezLog::get();

	$query = 'SELECT stl_country_code, gcr_location_name, stl_region, stl_city ';
	$query .= 'FROM tmp_geocode_locations loc ';
	$query .= 'JOIN ' . APP_TABLE_PREFIX . 'geocode_regions ON (stl_country_code = gcr_country_code AND stl_region = gcr_region_code) ';
	$query .= 'WHERE NOT EXISTS ';
	$query .= '( SELECT * FROM ' . APP_TABLE_PREFIX . 'geocode_cities city ';
	$query .= 'WHERE city.gcity_country_code = loc.stl_country_code AND city.gcity_region_code = loc.stl_region AND city.gcity_city = loc.stl_city ';
	$query .= ') ';

	try {
		$cities = $db->fetchAll($query);
	}
	catch (Exception $ex) {
		$log->err('Message: ' . $ex->getMessage() . ', File: ' . __FILE__ . ', Line: ' . __LINE__);
		throw $ex;
	}

	// geocode and update the cities in the database
	foreach($cities as $city)
	{
		$address = $city['stl_city'] . ', ' . $city['gcr_location_name'] . ', ' . $city['stl_country_code'];
		$gmap = new GoogleMap();

		try {
			$hasGeocodeResult = $gmap->getInfoLocation($address);
		}
		catch (Exception $ex) {
			$log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
			throw $ex;
		}

		if ($hasGeocodeResult)
		{
			$rec = array();
			$rec['country_code'] = $city['stl_country_code'];
			$rec['region_code'] = $city['stl_region'];
			$rec['location_name'] = $city['gcr_location_name'];
			$rec['city'] = $city['stl_city'];
			$rec['latitude'] = $gmap->getLatitude();
			$rec['longitude'] = $gmap->getLongitude();
			GeocodeCities::insert($rec);
		}

		sleep(1);
	}
}

// =================================================================
// Add and geocode any locations in {APP_TABLE_PREFIX}statistics_all
// =================================================================
function geocodeLocationCache()
{
	$db = DB_API::get();
	$log = FezLog::get();

	$query = 'SELECT location FROM ( ';
	$query .= "SELECT concat(stl_city, ', ', gcr_location_name, ', ', stl_country_code) as location FROM tmp_geocode_locations ";
	$query .= "JOIN fez_geocode_regions ON (stl_country_code = gcr_country_code AND stl_region = gcr_region_code) ";
	$query .= ") temp_table ";
	$query .= "WHERE NOT EXISTS (SELECT * FROM " . APP_TABLE_PREFIX . "geocode_location_cache cache where temp_table.location = cache.loc_location) ";

	try {
		$locations = $db->fetchAll($query);
	}
	catch (Exception $ex) {
		$log->err('Message: ' . $ex->getMessage() . ', File: ' . __FILE__ . ', Line: ' . __LINE__);
		throw $ex;
	}

	// for each location, geocode and insert into the location cache
	foreach ($locations as $location)
	{
		$address = $location['location'];

		$gmap = new GoogleMap();

		try {
			$hasGeocodeResult = $gmap->getInfoLocation($address);
		}
		catch (Exception $ex) {
			$log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
			throw $ex;
		}

		if ($hasGeocodeResult)
		{
			// do geocoding on the cities
			$record = array();
			$record['location'] = $address;
			$record['latitude'] = $gmap->getLatitude();
			$record['longitude'] = $gmap->getLongitude();
			$record['accuracy'] = $gmap->getAccuracy();
			GeocodeLocationCache::insert($record);
		}

		// sleep so we don't get kicked off the google api for too many requests in time period
		sleep(1);
	}
}

