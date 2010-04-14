<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005-2010 The University of Queensland,                |
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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>        |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+

define(APP_LOGGING_ENABLED, false);

include_once('../config.inc.php');

include_once(APP_INC_PATH . 'class.language.php');
include_once(APP_INC_PATH . "class.record.php");

/*
*****************************************************************************************
Basic usage information
*****************************************************************************************
Manually run this query against your Fez database:

	SELECT rek_language, COUNT(rek_language) AS instances
	FROM fez_record_search_key_language
	LEFT JOIN fez_language ON rek_language = lng_alpha3_bibliographic
	WHERE lng_alpha3_bibliographic IS NULL
	GROUP BY rek_language
	ORDER BY COUNT(rek_language) DESC;
	
This will give a list of distinct languages that are not already in the appropriate format,
as well as the number of times each occurs. Each 'language' (and its ISO-639-2 mapping) 
should be given a representation in the array below.

*****************************************************************************************
*/

$langMapping = array(
	"en" => array('eng'),
	"English" => array('eng'),
	"German" => array('ger'),
	"Chinese" => array('chi'),
	"eng, fre" => array('eng', 'fre'),
	"eng; fre" => array('eng', 'fre'),
	"Spanish" => array('spa'),
	"French" => array('fre'),
	"Dutch" => array('dut'),
	"fr" => array('fre'),
	"eng, ger" => array('eng', 'ger'),
	"Jap" => array('jpn'),
	"Portugese" => array('por'),
	"Japanese" => array('jpn'),
	"Portuguese" => array('por'),
	"sp" => array('spa'),
	"Polish" => array('pol'),
	"eng/ger" => array('eng', 'ger'),
	"eng, rus" => array('eng', 'rus'),
	"eng, spa" => array('eng', 'spa'),
	"eng; ger" => array('eng', 'ger'),
	"emg" => array('eng'),
	"English, German" => array('eng', 'ger'),
	"ger; eng" => array('ger', 'eng'),
	"eng, chi" => array('eng', 'chi'),
	"Indonesian" => array('ind'),
	"eng.; afrikaans" => array('eng', 'afr'),
	"eng, man" => array('eng', 'chi'),
	"en, Fr, ch" => array('eng', 'fre', 'chi'),
	"eng; chi" => array('eng', 'chi'),
	"engl" => array('eng'),
	"eng, french" => array('eng', 'fre'),
	"eng." => array('eng'),
	"eng, ita" => array('eng', 'ita'),
	"no" => array('nor'),
	"Italian" => array('ita'),
	"Vietnamese" => array('vie'),
	"ger, eng" => array('ger', 'eng'),
	"Yiddish" => array('yid'),
	"ja" => array('jpn'),
	"eng: ger" => array('eng', 'ger'),
	"eng; jap" => array('eng', 'jpn'),
	"Enlgish" => array('eng'),
	"chi, eng" => array('chi', 'eng'),
	"Hebrew" => array('heb'),
	"Korean" => array('kor'),
	"eng; spa" => array('eng', 'spa'),
	"eng; Chinese" => array('eng', 'chi'),
	"eng & French" => array('eng', 'fre'),
	"eng; cro" => array('eng', 'hrv'),
	"fre; eng" => array('fre', 'eng'),
	"eng and fr" => array('eng', 'fre'),
	"eng, jap" => array('eng', 'jpn'),
	"English and Chinese" => array('eng', 'chi'),
	"ita, eng" => array('ita', 'eng'),
	"eng; french" => array('eng', 'fre'),
	"por, spa" => array('por', 'spa'),
	"English, French, German, Italian, Latin, Spanish" => array('eng', 'fre', 'ger', 'ita', 'lat', 'spa'),
	"ger,eng" => array('ger', 'eng'),
	"eng and spanish" => array('eng', 'spa'),
	"eng, slo" => array('eng', 'slo'),
	"por,eng" => array('por', 'eng'),
	"eng/German" => array('eng', 'ger'),
	"eng; ger; fr" => array('eng', 'ger', 'fre'),
	"can" => array('chi'),
	"eng, can" => array('eng', 'chi'),
	"por; spa" => array('por', 'spa'),
	"Ch" => array('chi'),
	"English, Turkish" => array('eng', 'tur'),
	"en, fr" => array('eng', 'fre'),
	"jap and eng" => array('jpn', 'eng'),
	"eng, spa, fre" => array('eng', 'spa', 'fre'),
	"eng: kor" => array('eng', 'kor'),
	"eng; man" => array('eng', 'chi'),
	"Ch, eng" => array('chi', 'eng'),
	"German, English" => array('ger', 'eng'),
	"eng, chinese" => array('eng', 'chi'),
	"jap, eng" => array('jpn', 'eng'),
	"eng, spanish" => array('eng', 'spa'),
	"Portugese, English" => array('por', 'eng'),
	"eng: spanish" => array('eng', 'spa'),
	"eng; por" => array('eng', 'por'),
	"entg" => array('eng'),
	"En, French" => array('eng', 'fre'),
	"gw" => array('ger'),
	"eng, danish" => array('eng', 'dan'),
	"jp" => array('jpn'),
	"eng, tha" => array('eng', 'tha'),
	"eng: turkish" => array('eng', 'tur'),
	"eng; Portuguese" => array('eng', 'por'),
	"chi,eng" => array('chi', 'eng'),
	"En, French, Spanish" => array('eng', 'fre', 'spa'),
	"eng, dut" => array('eng', 'dut'),
	"eng, thi" => array('eng', 'tha'),
	"Romanian" => array('rum'),
	"eng:ger" => array('eng', 'ger'),
	"chi; eng" => array('chi', 'eng'),
	"Fr, Ch, En" => array('fre', 'chi', 'eng'),
	"enf" => array('eng'),
	"Hungarian" => array('hun'),
	"eng, dutch" => array('eng', 'dut'),
	"eng,chinese" => array('eng', 'chi'),
	"rus, eng" => array('rus', 'eng'),
	"eng; spa; ger" => array('eng', 'spa', 'ger'),
	"fr, en" => array('fre', 'eng'),
	"eng & chinese" => array('eng', 'chi'),
	"In" => array('ind'),
	"Latin and French" => array('lat', 'fre'),
	"eng,ger" => array('eng', 'ger'),
	"Russian" => array('rus'),
	"eng; spanish (Abstract)" => array('eng', 'spa'),
	"Chinese, English" => array('chi', 'eng'),
	"fre and eng" => array('fre', 'eng'),
	"indo, eng" => array('ind', 'eng'),
	"eng, fre, spa" => array('eng', 'fre', 'spa'),
	"Mandarin" => array('chi'),
	"eng-" => array('eng'),
	"Danish/Eng" => array('dan', 'eng'),
	"eng ; dut" => array('eng', 'dut'),
	"Mandarin Chinese" => array('chi'),
	"spa; eng" => array('spa', 'eng'),
	"eng; dan" => array('eng', 'dan'),
	"Engliash" => array('eng'),
	"dk" => array('dan'),
	"eng and Croation" => array('eng', 'hrv'),
	"Indonesian, English" => array('ind', 'eng'),
	"eng; Dutch" => array('eng', 'dut'),
	"French & English" => array('fre', 'eng'),
	"Indonesian; eng" => array('ind', 'eng'),
	"ng" => array('dan'),
	"eng.tha" => array('eng', 'tha'),
	"Spanish, English" => array('spa', 'eng'),
	"English & French" => array('eng', 'fre'),
	"French, eng" => array('fre', 'eng'),
	"eng and fre" => array('eng', 'fre'),
	"Israeli" => array('heb'),
	"eng/chi" => array('eng', 'chi'),
	"swe ; eng" => array('swe', 'eng'),
	"eng; fre; spa" => array('eng', 'fre', 'spa'),
	"ge" => array('ger'),
	"eng and french" => array('eng', 'fre'),
	"eng, Latvian" => array('eng', 'lav'),
	"pol; eng" => array('pol', 'eng'),
	"eng/fr" => array('eng', 'fre'),
	"Text in Dutch" => array('dut'),
	"English, Chinese" => array('eng', 'chi'),
	"ebg" => array('eng'),
	"ger ; eng" => array('ger', 'eng'),
	"eng and Israeli" => array('eng', 'heb'),
	"eng/fre" => array('eng', 'fre'),
	"eng; French; Spanish; German" => array('eng', 'fre', 'spa', 'ger'),
	"English, Dutch" => array('eng', 'dut'),
	"eg" => array('eng'),
	"eng and korean" => array('eng', 'kor'),
	"italian; eng" => array('ita', 'eng'),
	"eng, portugese" => array('eng', 'por'),
	"portugese" => array('por'),
	"english" => array('eng'),
	"EN" => array('eng'),
	"en, fr, ch" => array('eng', 'fre', 'chi'),
	"german" => array('ger'),
	"En" => array('eng'),
	"chinese" => array('chi'),
	"eng & french" => array('eng', 'fre'),
	"spanish" => array('spa'),
	"english and chinese" => array('eng', 'chi'),
	"eng; chinese" => array('eng', 'chi'),
	"eng " => array('eng'),
	"eng, French" => array('eng', 'fre'),
	"polish" => array('pol'),
	"ENGLISH" => array('eng'),
	"jap" => array('jpn'),
	"Engl" => array('eng'),
	"fre; ned; eng" => array('fre', 'dut', 'eng'),
	"french" => array('fre'),
	"Fr" => array('fre'),
	"e" => array('eng'),
	"end" => array('eng'),
	"spn" => array('spa'),
	"Eng." => array('eng'),
	"Enlish" => array('eng'),
	"chn" => array('chi')
	);

$query = "	
			SELECT DISTINCT(rek_language_pid)
			FROM fez_record_search_key_language
			LEFT JOIN fez_language ON rek_language = lng_alpha3_bibliographic
			WHERE lng_alpha3_bibliographic IS NULL
			ORDER BY rek_language_pid ASC;
		";

$db = DB_API::get();
$log = FezLog::get();

try {
        $result = $db->fetchAll($query);
} catch (Exception $ex) {
        $log = FezLog::get();
        $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
        return;
}

echo "Total records with languages: " . count($result) . "\n";

/* Build a list of PIDs */
$pids = array();
foreach ($result as $item) {
	$pids[] = $item['rek_pid'];
}

$search_keys = array("Language"); // The search key we are updating

foreach ($pids as $pid) {
	echo $pid . "\n";
	$record = new RecordGeneral($pid);
	$languages = getLanguagesForPID($pid);
	$mapping = $langMapping[$languages];
	$history = "was updated based on automagic language mapping";
	
	if (count($mapping) == 0) {
		echo "ERROR - no mappings found for " . $languages . "\n";
	} else {
		$mapCount = 1;
		foreach ($mapping as $map) {
			if (count($mapping) > 1) {
				// Multiple languages
				if ($mapCount == 1) {
					$record->addSearchKeyValueList("MODS", "Metadata Object Description Schema", array("Language"), array($map), true, $history);
				} else {
					$record->addSearchKeyValueList("MODS", "Metadata Object Description Schema", array("Language"), array($map), false, $history);
				}
			} else {
				// Just one language
				$record->addSearchKeyValueList("MODS", "Metadata Object Description Schema", array("Language"), array($map), true, $history);
			}
			$mapCount++;
		}
	}
	echo "\n";
}



function getLanguagesForPID($pid)
{
	$db = DB_API::get();
	$log = FezLog::get();

	$query = "
				SELECT rek_language
				FROM fez_record_search_key_language
				WHERE rek_language_pid = '" . $pid . "'
				ORDER BY rek_language_order ASC
			";
	
	try {
	        $result = $db->fetchRow($query);
	} catch (Exception $ex) {
	        $log = FezLog::get();
	        $log->err('Message: '.$ex->getMessage().', File: '.__FILE__.', Line: '.__LINE__);
	        return;
	}
	
	return $result['rek_language'];

}
