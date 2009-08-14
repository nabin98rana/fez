<?php

include_once("config.inc.php");

include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.statistics.php");

define('SCALE_FACTOR', 1.5);
define('MIN_DIMENSION', 15);
define('CONTINENT_COLOUR', '#000000');
define('COUNTRY_COLOUR', '#00ff00');
define('REGION_COLOUR', '#0000ff');
define('CITY_COLOUR', '#ff0000');

// get the max value for a dataset
function getMaxValue($results, $dataType)
{
	$max = 0;
	foreach ($results as $row)
	{
		$value = getValue($row, $dataType);
		$max = max($max, $value);
	}

	return $max;
}

// get a value based on the datatype
function getValue($details, $dataType)
{
	switch($dataType)
	{
		case 'BOTH':
			$value = $details['abstracts'] + $details['downloads'];
			break;
		case 'ABSTRACTS_ONLY':
			$value = $details['abstracts'];
			break;
		case 'DOWNLOADS_ONLY':
			$value = $details['downloads'];
			break;
	}

	return $value;

}

// get zoom level and the bounds of the box and the datatype
$zoomLevel = $_GET['zoom'];
$neLatitude = $_GET['neLat'];
$neLongitude = $_GET['neLong'];
$swLatitude = $_GET['swLat'];
$swLongitude = $_GET['swLong'];
$dataType = $_GET['dataType'];

// determine which level we're at, if we're at the continent or country level, then show all
if (in_array($zoomLevel, array(0,1)))
{
	$queryResults = Statistics::getContinentMapSummary();

	$max = getMaxValue($queryResults, $dataType);

	$results = array();

	$height = 64;
	$width = 64;
	$colour = CONTINENT_COLOUR;

	// massage the array to fit the structure we expect
	foreach ($queryResults as $continentName => $details)
	{
		$value = getValue($details, $dataType);
		// ignore any zero values
		if ($value == 0)
			continue;

		$dimension = round((($value) / $max) * 100 / SCALE_FACTOR);
		if ($dimension < MIN_DIMENSION)
			$dimension = MIN_DIMENSION;

		$details['value'] = $value;
		$details['continent_name'] = $continentName;
		$details['height'] = $dimension;
		$details['width'] = $dimension;
		$details['colour'] = $colour;
		$results[] = $details;
	}
}
else //if ($zoomLevel <= 3)
{
	// get all country and continent details
	$queryResults = Statistics::getCountryMapSummary($neLatitude, $neLongitude, $swLatitude, $swLongitude);

	$max = getMaxValue($queryResults, $dataType);

	$height = 48;
	$width = 48;
	$colour = COUNTRY_COLOUR;

	$results = array();
	
	foreach ($queryResults as $index => $row)
	{
		$value = getValue($row, $dataType);
		// ignore any zero values
		if ($value == 0)
			continue;

		$row['value'] = $value;
		$dimension = round((($value) / $max) * 100 / SCALE_FACTOR);
		if ($dimension < MIN_DIMENSION)
			$dimension = MIN_DIMENSION;
		$row['height'] = $dimension;
		$row['width'] = $dimension;
		$row['colour'] = $colour;
		$results[] = $row;
	}
}

// if we've zoomed in enough, show regions
if ($zoomLevel >= 5)
{
	// if we're looking at region level, then restrict it by the bounding box
	$regionResults = Statistics::getCountryRegionMapSummary($neLatitude, $neLongitude, $swLatitude, $swLongitude);
	$height = 32;
	$width = 32;
	$colour = REGION_COLOUR;
	$max = getMaxValue($regionResults, $dataType);

	$regionRows = array();

	foreach ($regionResults as $index => $row)
	{
		$value = getValue($row, $dataType);
		// ignore any zero value rows
		if ($value == 0)
			continue;

		$row['value'] = $value;
		$dimension = round((($value) / $max) * 100 / SCALE_FACTOR);
		if ($dimension < MIN_DIMENSION)
			$dimension = MIN_DIMENSION;
		$row['height'] = $dimension;
		$row['width'] = $dimension;
		$row['colour'] = $colour;
		$regionRows[] = $row;
	}

	$results = array_merge($results, $regionRows);
}

if ($zoomLevel >= 8)
{
	// if we're looking at the city level, then search those
	$cityResults = Statistics::getCityMapSummary($neLatitude, $neLongitude, $swLatitude, $swLongitude);
	$height = 32;
	$width = 32;
	$colour = CITY_COLOUR;
	$max = getMaxValue($cityResults, $dataType);

	foreach ($cityResults as $index => $row)
	{
		$value = getValue($row, $dataType);
		// ignore any zero value rows
		if ($value == 0)
			continue;

		$row['value'] = $value;
		$dimension = round((($value) / $max) * 100 / SCALE_FACTOR);
		if ($dimension < MIN_DIMENSION)
			$dimension = MIN_DIMENSION;
		$row['height'] = $dimension;
		$row['width'] = $dimension;
		$row['colour'] = $colour;
		$cityRows[] = $row;
	}

	$results = array_merge($results, $cityRows);
}

//echo "<pre>Results: "; print_r($results); echo "</pre>\n";


// If we have results, then output the results
if (count($results))
{
	// set the header
	header('content-type: text/plain;');

	// now output the javascript
	echo "var points = [\n"; // open array

	$boundaries = array();

	foreach ($results as $index => $details)
	{
		$abstracts = number_format($details['abstracts']);
		$downloads = number_format($details['downloads']);

		if (isset($details['city']))
		{
			$fixedName = str_replace("\n", ' ', $details['city'].', '.$details['region'].',  '.$details['country_name']);
			$count = $details['value'];

			// set up the minimums
			if (!isset($boundaries['city']['min']))
			{
				$boundaries['city']['min']['count'] = $count;
				$boundaries['city']['min']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}
			elseif ($boundaries['city']['min']['count'] > $count)
			{
				$boundaries['city']['min']['count'] = $count;
				$boundaries['city']['min']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}

			// now set the maximums
			if (!isset($boundaries['city']['max']))
			{
				$boundaries['city']['max']['count'] = $count;
				$boundaries['city']['max']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}
			elseif ($boundaries['city']['max']['count'] < $count)
			{
				$boundaries['city']['max']['count'] = $count;
				$boundaries['city']['max']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}
		}
		elseif (isset($details['region']))
		{
			$fixedName = str_replace("\n", ' ', $details['region'].', '.$details['country_name']);
			$count = $details['value'];

			// set up the minimums
			if (!isset($boundaries['region']['min']))
			{
				$boundaries['region']['min']['count'] = $count;
				$boundaries['region']['min']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}
			elseif ($boundaries['region']['min']['count'] > $count)
			{
				$boundaries['region']['min']['count'] = $count;
				$boundaries['region']['min']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}

			// now set the maximums
			if (!isset($boundaries['region']['max']))
			{
				$boundaries['region']['max']['count'] = $count;
				$boundaries['region']['max']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}
			elseif ($boundaries['region']['max']['count'] < $count)
			{
				$boundaries['region']['max']['count'] = $count;
				$boundaries['region']['max']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}
		}
		elseif (isset($details['continent_name']))
		{
			$fixedName = str_replace("\n", ' ', $details['continent_name']);
			$count = $details['value'];

			// set up the minimums
			if (!isset($boundaries['continent']['min']))
			{
				$boundaries['continent']['min']['count'] = $count;
				$boundaries['continent']['min']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}
			elseif ($boundaries['continent']['min']['count'] > $count)
			{
				$boundaries['continent']['min']['count'] = $count;
				$boundaries['continent']['min']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}

			// now set the maximums
			if (!isset($boundaries['continent']['max']))
			{
				$boundaries['continent']['max']['count'] = $count;
				$boundaries['continent']['max']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}
			elseif ($boundaries['continent']['max']['count'] < $count)
			{
				$boundaries['continent']['max']['count'] = $count;
				$boundaries['continent']['max']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}

		}
		else
		{
			$fixedName = str_replace("\n", ' ', $details['country_name']);
			$count = $details['value'];

			// set up the minimums
			if (!isset($boundaries['country']['min']))
			{
				$boundaries['country']['min']['count'] = $count;
				$boundaries['country']['min']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}
			elseif ($boundaries['country']['min']['count'] > $count)
			{
				$boundaries['country']['min']['count'] = $count;
				$boundaries['country']['min']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}

			// now set the maximums
			if (!isset($boundaries['country']['max']))
			{
				$boundaries['country']['max']['count'] = $count;
				$boundaries['country']['max']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}
			elseif ($boundaries['country']['max']['count'] < $count)
			{
				$boundaries['country']['max']['count'] = $count;
				$boundaries['country']['max']['details'] = array('name'=>$fixedName, 'abstracts'=>$abstracts, 'downloads'=>$downloads);
			}

		}
		$latitude = $details['latitude'];
		$longitude = $details['longitude'];
		$colour = $details['colour'];
		$height = $details['height'];
		$width = $details['width'];

		$outputArray[] = "{lat:$latitude,lng:$longitude,width:$width,height:$height,colour:\"{$colour}\",name:\"{$fixedName}\",abstracts:\"{$abstracts}\",downloads:\"{$downloads}\"}";
	}

	echo implode(",\n", $outputArray);

	echo "];\n\n"; // close array

//	echo "<pre>Boundaries: "; print_r($boundaries); echo "</pre>\n"; // DEBUG

	echo "var sidebarDetails = [\n"; // open sidebar array

	$typeOutput = false;
	foreach ($boundaries as $boundaryType => $boundaryDetails)
	{
		if ($typeOutput)
			echo "},\n";
		else
			$typeOutput = true;

		echo "{level: \"{$boundaryType}\", levelName: \"" . ucfirst($boundaryType) . "\", ";

		$levelOutput = false;
		foreach($boundaryDetails as $level => $levelDetails)
		{
			$d = $levelDetails['details'];
			if ($levelOutput)
				echo ",";
			else
				$levelOutput = true;
			echo "{$level}:{name:\"{$d['name']}\"";
			if ($dataType == 'BOTH' || $dataType == 'ABSTRACTS_ONLY')
			{
				echo ",abstracts:\"{$d['abstracts']}\"";
			}
			if ($dataType == 'BOTH' || $dataType == 'DOWNLOADS_ONLY')
			{
				echo ",downloads:\"{$d['downloads']}\"";
			}
			echo "}";
		}
	}
	echo "}\n";

	echo "];"; // close sidebar array
}

