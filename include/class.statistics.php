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
// |          Matthew Smith <m.smith@library.uq.edu.au>,                  |
// |          Lachlan Kuhn <l.kuhn@library.uq.edu.au>                     |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle system statistics from web server logs.
 *
 * This Class is based on the excellent stats extension for ePrints developed by
 * University of Tasmania and University of Melbourne. Special thanks go to Arthur Sale
 * from UTAS for making this code available to the community.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 * 
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "geoip.inc");
include_once(APP_INC_PATH . "geoipcity.inc");
include_once(APP_INC_PATH . "geoipregionvars.php");

class Statistics
{

    var $bgp;       // For background process

    /**
     * Method used to scan a web server log for Fez statistics.
     *
     * @access  public
     * @return  boolean
     */
    function gatherStats() {
        Statistics::checkSetup();
		$timeStarted = date('Y-m-d H:i:s');
		$counter = 0;
		$counter_inserted = 0;
		$changedPids = array();
		$requestDateLatest = 0;
		$datetestA = strtotime(Statistics::getLastestLogEntry());
		$requestDateLatest = $datetestA;
		$logf = WEBSERVER_LOG_DIR . WEBSERVER_LOG_FILE;
		$archive_name = APP_HOSTNAME;
		$handle = fopen($logf, "r");
		
		if(!$handle) 
		{
		  return false;	
		}
		
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			// if there are any view or eserv entries than the abstract was viewed or an datastream/file was viewed
			if	((preg_match("/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}) - - \[(.*?)\] \"GET ".preg_quote(APP_RELATIVE_URL,'/')."\/?view\.php\?.*pid=([a-zA-Z]*:[0-9]+).* HTTP\/1..\" 200 .*/i",$buffer,$matches)) ||
				(preg_match("/^(\S{1,}\.\S{1,}\.\S{1,}\.\S{1,}) - - \[(.*?)\] \"GET ".preg_quote(APP_RELATIVE_URL,'/')."\/?eserv\.php\?.*pid=([a-zA-Z]*:[0-9]+)&dsID=(\S*).* HTTP\/1..\" 200 .*/i",$buffer,$matches)) ||
				(preg_match("/^(\S{1,}\.\S{1,}\.\S{1,}\.\S{1,}) - - \[(.*?)\] \"GET \/robots\.txt.*/i",$buffer,$matches)) ||
				(preg_match("/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}) - - \[(.*?)\] \"GET ".preg_quote(APP_RELATIVE_URL,'/')."\/?view\/([a-zA-Z]*:[0-9]+).* HTTP\/1..\" 200 .*/i",$buffer,$matches)) ||
				(preg_match("/^(\S{1,}\.\S{1,}\.\S{1,}\.\S{1,}) - - \[(.*?)\] \"GET ".preg_quote(APP_RELATIVE_URL,'/')."\/?eserv\/([a-zA-Z]*:[0-9]+)\/(\S*).* HTTP\/1..\" 200 .*/i",$buffer,$matches)))
			{
//				print_r($matches); // debug
				$pid = "";
				$country_code = '';
				$insertid = '';
				$view_type = '';
				$uniquebits = '';
				$ip = $matches[1];
				if ($ip != "") {
					if (Statistics::isRobot($ip) == 1) {
						continue;
					}
					$hostname = Statistics::gethostbyaddr_with_cache($ip);				
					$robot_matches = 0;
					$robot_matches = preg_match("/^.*(robots\.txt).*/i", $buffer); // If someone asks for robots.txt, they usually are a robot so exclude their IP from the stats and add to the robot list (list can be changed by admin if false positive robot)
					if ($robot_matches > 0) {
						Statistics::addRobot($ip, $hostname);
						continue;
					}				
					// Try and find any of the major web crawlers and exclude them from the stats (they will usually already have been picked up by the above robots.txt check)
					$crawler_matches = 0;
					$crawler_matches = preg_match("/^.*((googlebot)|(slurp)|(jeeves)|(yahoo)|(msn)).*/i", $hostname);
					if ($crawler_matches > 0) {
						Statistics::addRobot($ip, $hostname);
						continue;
					}					
				} else { // if there is no ip then skip this line
					continue;
				}
				$date = $matches[2];
				$pid = $matches[3];
				if (count($matches) == 5) {
					$dsid = $matches[4];
				} else {
					$dsid = "";
				}
				$uniquebits = $buffer;
				$counter++;
				preg_match("/^.*:([0-9]+:[0-9]+:[0-9]+) .*/i", $date, $timematch);
				$date = preg_replace("/:.*/","",$date);
				$date = preg_replace("/\//", " ", $date);
				$when = getdate(strtotime($date));
				$request_date = $when["year"]."-".$when["mon"]."-".$when["mday"];
				$request_date .= " ".$timematch[1];
				$datetestB = strtotime($request_date);
				if (($datetestB > $requestDateLatest) || ($requestDateLatest == 0)) {
					$requestDateLatest = $datetestB;
				}
				if ($datetestB <= $datetestA) { // make sure the log entry is newer than the last log run date
					continue;
				}
				// Try and find any thumbnails and preview copies of images as these should not be counted towards the file downloads for an image datastream
				$image_matches = 0;
				$image_matches = preg_match("/^.*\/((thumbnail_)|(preview_)|(presmd_)).*/i", $buffer);
				if ($image_matches > 0) {
					continue;
				}

				$gi = geoip_open(APP_GEOIP_PATH."GeoLiteCity.dat",GEOIP_STANDARD);
				$record = geoip_record_by_addr($gi,$ip);
				$country_code = $record->country_code;
				$country_name = $record->country_name;
				$city = $record->city;
				$region = $record->region;

                 // Make this stuff SQL-safe.
                $archive_name = Misc::escapeString($archive_name);
                $ip = Misc::escapeString($ip);
                $hostname = Misc::escapeString($hostname);
                $request_date = Misc::escapeString($request_date);
                $country_code = Misc::escapeString($country_code);
                $country_name = Misc::escapeString($country_name);
                $region = Misc::escapeString($region);
                $city = Misc::escapeString($city);
                $pid = Misc::escapeString($pid);
                $dsid = Misc::escapeString($dsid);
                $pidNum = Misc::escapeString(Misc::numPID($pid));

				// below commented out lines are other GeoIP information you could possibly use if you are interested
/*				print $record->postal_code . "\n";
				print $record->latitude . "\n";
				print $record->longitude . "\n";
				print $record->dma_code . "\n";
				print $record->area_code . "\n";				*/
				geoip_close($gi);
				if ($pid != "") {
				   $stmt = "INSERT INTO
								" . APP_TABLE_PREFIX . "statistics_all
							 (
								stl_archive_name,
								stl_ip,
								stl_hostname,
								stl_request_date,
								stl_country_code,
								stl_country_name,																								
								stl_region,
								stl_city,
								stl_pid,																							
								stl_dsid,
                                stl_pid_num
							 ) VALUES (
								'" . $archive_name . "',
								'" . $ip . "',
								'" . $hostname . "',
								'" . $request_date . "',
								'" . $country_code . "',
								'" . $country_name . "',
								'" . $region . "',
								'" . $city . "',
								'" . $pid . "',			
								'" . $dsid . "',
								'" . $pidNum . "'
							 )"; 
					$res = $GLOBALS["db_api"]->dbh->query($stmt);
					if (PEAR::isError($res)) {
						Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
						return -1; //abort
					} else {
						$counter_inserted++;
						
						if( APP_SOLR_INDEXER == "ON" ) {
						    $changedPids[$pid] = true;
						}
					}
					
				}
			}
		}
		
		
		fclose($handle);
		Statistics::updateSummaryStats();
		if( APP_SOLR_INDEXER == "ON" ) {
		    foreach ($changedPids as $pid => $null) {
                FulltextQueue::singleton()->add($pid);
		    }
		}
		$timeFinished = date('Y-m-d H:i:s');
		Statistics::setLogRun($requestDateLatest, $counter, $counter_inserted, $timeStarted, $timeFinished);
    }


    function clearBufferByDate($date) {
        $stmt = "DELETE FROM
                    " . APP_TABLE_PREFIX . "statistics_buffer
                 WHERE
                    str_request_date <= '".date('Y-m-d H:i:s', $date)."'";
        $res = $GLOBALS["db_api"]->dbh->query($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        } else {
            return true; 
        }
    }



	    /**
	     * Method used to scan a web server log for Fez statistics.
	     *
	     * @access  public
	     * @return  boolean
	     */
	    function gatherStatsFromBuffer() {
	        Statistics::checkSetup();
			$timeStarted = date('Y-m-d H:i:s');
			$counter = 0;
			$counter_inserted = 0;
			$increments = array();
			$requestDateLatest = 0;
			$changedPids = array();
			$datetestA = strtotime(Statistics::getLastestLogEntry());
			$requestDateLatest = $datetestA;
//			$logf = WEBSERVER_LOG_DIR . WEBSERVER_LOG_FILE;
			$archive_name = APP_HOSTNAME;
//			$handle = fopen($logf, "r");

			$buffer = Statistics::getAllFromBuffer();
			if (count($buffer) == 0 ) { return false; }
			foreach ($buffer as $brow) {
	//				print_r($matches); // debug
					$pid = "";
					$country_code = '';
					$insertid = '';
					$view_type = '';
					$uniquebits = '';
					$ip = $brow['str_ip'];
					if ($ip != "") {
						if (Statistics::isRobot($ip) == 1) {
							continue;
						}
						$hostname = Statistics::gethostbyaddr_with_cache($ip);				
						$robot_matches = 0;
						// Try and find any of the major web crawlers and exclude them from the stats (they will usually already have been picked up by the above robots.txt check)
						$crawler_matches = 0;
						$crawler_matches = preg_match("/^.*((googlebot)|(slurp)|(jeeves)|(yahoo)|(msn)).*/i", $hostname);
						if ($crawler_matches > 0) {
							Statistics::addRobot($ip, $hostname);
							continue;
						}					
					} else { // if there is no ip then skip this line
						continue;
					}
					$date = $brow['str_request_date'];
					$pid = $brow['str_pid'];
					$dsid = $brow['str_dsid'];

					$uniquebits = $buffer;
					$counter++;
//					preg_match("/^.*:([0-9]+:[0-9]+:[0-9]+) .*/i", $date, $timematch);
//					$date = preg_replace("/:.*/","",$date);
//					$date = preg_replace("/\//", " ", $date);
//					$when = getdate(strtotime($date));
//					$request_date = $when["year"]."-".$when["mon"]."-".$when["mday"];
//					$request_date .= " ".$timematch[1]; */

					$request_date = $date;
					
					$datetestB = strtotime($request_date);
					if (($datetestB > $requestDateLatest) || ($requestDateLatest == 0)) {
						$requestDateLatest = $datetestB;
					}
					if ($datetestB <= $datetestA) { // make sure the log entry is newer than the last log run date
						continue;
					}
					// Try and find any thumbnails and preview copies of images as these should not be counted towards the file downloads for an image datastream
					$image_matches = 0;
					$image_matches = preg_match("/^((thumbnail_)|(preview_)|(presmd_)).*/i", $dsid);
					if ($image_matches > 0) {
						continue;
					}

					$gi = geoip_open(APP_GEOIP_PATH."GeoLiteCity.dat",GEOIP_STANDARD);
					$record = geoip_record_by_addr($gi,$ip);
					$country_code = $record->country_code;
					$country_name = $record->country_name;
					$city = $record->city;
					$region = $record->region;

	                 // Make this stuff SQL-safe.
	                $archive_name = Misc::escapeString($archive_name);
	                $ip = Misc::escapeString($ip);
	                $hostname = Misc::escapeString($hostname);
	                $request_date = Misc::escapeString($request_date);
	                $country_code = Misc::escapeString($country_code);
	                $country_name = Misc::escapeString($country_name);
	                $region = Misc::escapeString($region);
	                $city = Misc::escapeString($city);
	                $pid = Misc::escapeString($pid);
	                $dsid = Misc::escapeString($dsid);
	                $pidNum = Misc::escapeString(Misc::numPID($pid));
					$usr_id = Misc::escapeString($brow['str_usr_id']);
					if (!is_numeric($usr_id)) {
						$usr_id = "NULL";
					}
					// below commented out lines are other GeoIP information you could possibly use if you are interested
	/*				print $record->postal_code . "\n";
					print $record->latitude . "\n";
					print $record->longitude . "\n";
					print $record->dma_code . "\n";
					print $record->area_code . "\n";				*/
					geoip_close($gi);
					if ($pid != "") {
					   $stmt = "INSERT INTO
									" . APP_TABLE_PREFIX . "statistics_all
								 (
									stl_archive_name,
									stl_ip,
									stl_hostname,
									stl_request_date,
									stl_country_code,
									stl_country_name,																								
									stl_region,
									stl_city,
									stl_pid,																							
									stl_dsid,
	                                stl_pid_num,
									stl_usr_id
								 ) VALUES (
									'" . $archive_name . "',
									'" . $ip . "',
									'" . $hostname . "',
									'" . $request_date . "',
									'" . $country_code . "',
									'" . $country_name . "',
									'" . $region . "',
									'" . $city . "',
									'" . $pid . "',			
									'" . $dsid . "',
									'" . $pidNum . "',
									" . $usr_id . "
								 )"; 
						$res = $GLOBALS["db_api"]->dbh->query($stmt);
						if (PEAR::isError($res)) {
							Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
							return -1; //abort
						} else {
							$counter_inserted++;
							if (!is_array($increments[$pid])) {
								$increments[$pid] = array();
								$increments[$pid]['views'] = 0;
								$increments[$pid]['downloads'] = 0;
							}
							if ($dsid != "" && !is_null($dsid)) {
								$increments[$pid]['downloads']++;
							} else {
								$increments[$pid]['views']++;
							}
							if( APP_SOLR_INDEXER == "ON" ) {
							    $changedPids[$pid] = true;
							}
						}

					}
				}

			Statistics::updateSummaryStatsByIncrement($increments);
			if( APP_SOLR_INDEXER == "ON" ) {
				if (count($changedPids) > 0) {
				    foreach ($changedPids as $pid => $null) {
		                FulltextQueue::singleton()->add($pid);
				    }
				}
			}

			$timeFinished = date('Y-m-d H:i:s');
			Statistics::setLogRun($requestDateLatest, $counter, $counter_inserted, $timeStarted, $timeFinished);
			Statistics::clearBufferByDate($requestDateLatest);
	    }


	    function getAllFromBuffer()
	    {
	        $stmt = "SELECT
						*
	                 FROM
	                    " . APP_TABLE_PREFIX . "statistics_buffer
	                 ORDER BY
	                    str_id ASC";
	        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
	        if (PEAR::isError($res)) {
	            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
	            return "";
	        } else {
	            return $res;
	        }
	    }


	function setLogRun($request_date, $counter, $counter_inserted, $timeStarted, $timeFinished) {
	/*
		Keep track of where we are. Should avoid duplication of results
		if the script is run more than once on the same log file
	*/
		$stmt = "INSERT INTO
				" . APP_TABLE_PREFIX . "statistics_proc (stp_latestlog, stp_lastproc, stp_count, stp_count_inserted, stp_timestarted, stp_timefinished)  values ('".date('Y-m-d H:i:s', $request_date)."', '".date('Y-m-d', $request_date)."', ".$counter.", ".$counter_inserted.", '".$timeStarted."', '".$timeFinished."')";
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1; //abort
		}
	}

	function updateSummaryStats() {
		$stmt = "UPDATE 
				" . APP_TABLE_PREFIX . "record_search_key r1
				SET rek_file_downloads = (
				SELECT COUNT(*) FROM " . APP_TABLE_PREFIX . "statistics_all
				WHERE stl_dsid <> '' AND stl_pid = r1.rek_pid),
				rek_views = (
				SELECT COUNT(*) FROM " . APP_TABLE_PREFIX . "statistics_all
				WHERE stl_dsid = '' AND stl_pid = r1.rek_pid)";
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1; //abort
		}
	}


	function updateSummaryStatsByIncrement($stats = array()) {
		if (count($stats) == 0) { return false; }
		foreach ($stats as $pid => $val) {
			if (is_numeric($val['views'])) {
				if ($val['views'] > 0) {
					$stmt = "UPDATE 
							" . APP_TABLE_PREFIX . "record_search_key r1
							SET rek_views = rek_views + ".$val['views']."
							WHERE r1.rek_pid = ".$pid.")";
					$res = $GLOBALS["db_api"]->dbh->query($stmt);
					if (PEAR::isError($res)) {
						Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
						return -1; //abort
					}
				}
			}
			if (is_numeric($val['downloads'])) {
				if ($val['downloads'] > 0) {
					$stmt = "UPDATE 
							" . APP_TABLE_PREFIX . "record_search_key r1
							SET rek_file_downloads = rek_file_downloads + ".$val['downloads']."
							WHERE r1.rek_pid = ".$pid.")";
					$res = $GLOBALS["db_api"]->dbh->query($stmt);
					if (PEAR::isError($res)) {
						Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
						return -1; //abort
					}
				}
			}
		}
	}

	function updateSummaryStatsOnPid($pid) {
		$stmt = "UPDATE 
				" . APP_TABLE_PREFIX . "record_search_key r1
				SET rek_file_downloads = (
						SELECT COUNT(*) FROM " . APP_TABLE_PREFIX . "statistics_all
						WHERE stl_dsid <> '' AND stl_pid = '".$pid."'),
					rek_views = (
						SELECT COUNT(*) FROM " . APP_TABLE_PREFIX . "statistics_all
						WHERE stl_dsid = '' AND stl_pid = '".$pid."')
				WHERE rek_pid = '".$pid."'";
		
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1; //abort
		}
	}

	function isRobot($ip) {	
		// check if the ip is in the Fez robots listing so far
		$stmt = "select count(*) from " . APP_TABLE_PREFIX . "statistics_robots where str_ip = '".$ip."'";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1; //abort
		} else {
			if (($res > 0) && (!empty($res))) {
				$robot = 1;
			} else {
				$robot = 0;
			}
			return $robot; 
		}
	}
	
	function addRobot($ip, $hostname) {	
		// add this ip to the Fez robots listing
		$stmt = "insert into " . APP_TABLE_PREFIX . "statistics_robots (str_ip, str_hostname, str_date_added) values ('".$ip."', '".$hostname."', now())";
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1; //abort
		}

	}	

	function addBuffer($pid, $ds_id = null) {	
		//Filter out basics, but dont do robots etc until the buffer moves to the main table as checking for robots will be to costly timewise
		// Try and find any thumbnails and preview copies of images as these should not be counted towards the file downloads for an image datastream
		if (!is_null($ds_id)) {
			$image_matches = 0;
			$image_matches = preg_match("/^((thumbnail_)|(preview_)|(presmd_)).*/i", $ds_id);
			if ($image_matches > 0) {
				return false;
			}	
		}
		$usr_id = Auth::getUserID();
		//print_r($_SERVER);
		$pid = Misc::escapeString($pid);	
		$remote_address = Misc::escapeString($_SERVER['REMOTE_ADDR']);
		$request_date = Misc::escapeString(date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']));
		$stmt = "INSERT DELAYED INTO " . APP_TABLE_PREFIX . "statistics_buffer 
		(str_ip, str_request_date, str_pid";
		if (!is_null($ds_id)) {
			$stmt .= ", str_dsid";
		}
		if (is_numeric($usr_id)) {
			$stmt .= ", str_usr_id";
		}
		$stmt .= "
		) 
		VALUES 
		('".$remote_address."', '".$request_date."', '".$pid."'";
		if (!is_null($ds_id)) {
			$stmt .= ", '".Misc::escapeString($ds_id)."'";
		}
		if (is_numeric($usr_id)) {
			$stmt .= ", ".$usr_id;
		}
		$stmt .= ")";
//		echo $stmt;
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1; //abort
		} 

	}	


	function getRecentPopularItems($limit) {
	    
	    $stmt = "SELECT stl_pid, COUNT(*) as downloads
                 FROM fez_statistics_all
                 WHERE stl_dsid <> '' AND stl_request_date > '".date('Y-m-d H:i:s',strtotime("-1 week"))."'
                 GROUP BY stl_pid
                 ORDER BY downloads DESC
                 LIMIT $limit";
	    
	    $res = $GLOBALS["db_api"]->dbh->getAll($stmt,  DB_FETCHMODE_ASSOC);
	    if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
        }
	    
        return $res;
	}
	
	function getLastestLogEntry() {	
		// First get the date of latest log entry
		$stmt = "select max(stp_latestlog) from " . APP_TABLE_PREFIX . "statistics_proc";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if ((count($res) == 1) && (!empty($res))) {
				$latestLog = $res;
			} else {
				$latestLog = 0;
			}
			return $latestLog; 
		}
	}



        function cleanupFalseHitsBatch($limit, $offset, $min_date = false) {
            $stmt = "SELECT stl_id, stl_pid, stl_dsid, stl_ip, stl_request_date
                 FROM fez_statistics_all ";
			if ($min_date !== false) {
				$stmt .= " WHERE stl_request_date >= '".$min_date."' ";
			}
            $stmt .= " ORDER BY stl_request_date ASC LIMIT $limit OFFSET $offset";
            $res = $GLOBALS["db_api"]->dbh->getAll($stmt,  DB_FETCHMODE_ASSOC);
            if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return false;
            } else {
               return $res;
           }
        }

        function cleanupFalseHitsCount($date_min = false) {
            $stmt = "SELECT count(*)
                 FROM fez_statistics_all";

			if ($date_min !== false) {
				$stmt .= " WHERE stl_request_date >= '".$date_min."'";
			}
//            $stmt .= " WHERE stl_pid = 'UQ:107702'";

            $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
            if (PEAR::isError($res)) {
              Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
              return false;
            } else {
               return $res;
           }
        }

        function getMinBadDate() {
            $stmt = "SELECT MAX(stl_request_date)
                 FROM fez_statistics_all
 				 WHERE stl_counter_bad = 1";

            $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
            if (PEAR::isError($res)) {
              Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
              return false;
            } else {
				if (is_null($res)) {
					$res = false;
				}
               return $res;
           }
        }

        function setCounterBad($stl_id) {
                $stmt = "UPDATE 
                                " . APP_TABLE_PREFIX . "statistics_all
                                SET stl_counter_bad = 1
                                WHERE stl_id = ".$stl_id;
                $res = $GLOBALS["db_api"]->dbh->query($stmt);
                if (PEAR::isError($res)) {
                        Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
                        return -1; //abort
                }
        }

	function stripOldHistory($history = false, $latest_request_date, $seconds_limit = 10) {
		if ($history === false) { return false; }
		$newhistory = array();
		$latest_req_time = strtotime($latest_request_date);
		foreach ($history as $hkey => $hval) {
			if ($hval['stl_request_date']) {
				if (strtotime($hval['stl_request_date']) <= $latest_req_time) {
					$seconds_diff = Date_API::dateDiff("s", $hval['stl_request_date'], $latest_request_date);
					if ($seconds_diff <= $seconds_limit) {
						$newhistory[$hkey] = $hval;
					}
				}
			}			
		}
		echo "only keeping history of size ".count($newhistory)."\n";
		return $newhistory;
	}


	function cleanupFalseHits() {
		$seconds_limit = 10; // 10 seconds COUNTER draft 3 recommended limit
		$min_date = Statistics::getMinBadDate();
		$stats_count = Statistics::cleanupFalseHitsCount($min_date);
		$history = array();
		$newhistory = array();
		$max_id = 0;
		$remove_count = 0;
		$batch_limit = 1000;
		$stats_count = $stats_count/$batch_limit;
		for($x=0;$x<=$stats_count;$x++) {
			$y = $x*$batch_limit; 
			$res = Statistics::cleanupFalseHitsBatch($batch_limit, $y, $min_date);
			foreach ($res as $key => $val) {
				// echo "TESTING out: "; echo($val['stl_id']." - ".$val['stl_ip']." - ".$val['stl_pid']." - ".$val['stl_dsid']." - ".$val['stl_request_date']); echo "\n";
				$newkey = $val['stl_pid']."|".$val['stl_dsid']."|".$val['stl_ip'];
				if (count($history) > 0 || count($newhistory) > 0) {
					$history = $newhistory;
					if (array_key_exists($newkey, $history)) {
						if (strtotime($history[$newkey]['stl_request_date']) <= strtotime($val['stl_request_date'])) {
							$seconds_diff = Date_API::dateDiff("s", $history[$newkey]['stl_request_date'], $val['stl_request_date']);
							// echo $hval['stl_id']." vs ".$val['stl_id']." = seconds diff of $seconds_diff "; echo "\n";
							if ($seconds_diff <= $seconds_limit) {
								Statistics::setCounterBad($val['stl_id']);
								$remove_count++;
								//	echo $remove_count." would mark bad "; echo($val['stl_id']." - ".$val['stl_ip']." - ".$val['stl_pid']." - ".$val['stl_dsid']." - ".$val['stl_request_date']); echo "\n";
							}
						}						
					}
					$newhistory[$newkey] = $val;
				} else {
					// echo "HISTORY is nothing so making newhistory ".$val['stl_id']."\n";
					$newhistory[$newkey] = $val;			
				}
				if (count($newhistory) > 10000) {
					$newhistory = Statistics::stripOldHistory($newhistory, $val['stl_request_date'], $seconds_limit);
				}
			}
		}
		//echo "FINAL - would mark bad ".$remove_countnt." of ".$stats_count;	
	}

	
	function getStatsByDatastream($pid, $dsid) {	
		$stmt = "select count(*)  
			 	 from " . APP_TABLE_PREFIX . "statistics_all
				 where stl_pid = '".$pid."' and stl_dsid = '".$dsid."'";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if ((count($res) == 1) && (!empty($res))) {
				$count = $res;
			} else {
				$count = 0;
			}
			return $count; 
		}
	}

	function getStatsByAbstractView($pid, $year='all', $month='all', $range='all') {	
        $limit = '';
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit = " and year(date(stl_request_date)) = $year";
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(date(stl_request_date)) = $month";
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and date(stl_request_date) >= CURDATE()-INTERVAL 1 MONTH";
		}

		$stmt = "select count(*)  
			 	 from " . APP_TABLE_PREFIX . "statistics_all
				 where stl_pid = '".$pid."' AND stl_dsid = '' ".$limit;
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
		if (!empty($res)) {
			$count = $res;
		} else {
			$count = 0;
		}
		return $count; 
	}

	
	function getStatsByAllFileDownloads($pid, $year='all', $month='all', $range='all') {	
		$limit = "";
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit = " and year(date(stl_request_date)) = ".$year;
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(date(stl_request_date)) = ".$month;
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and date(stl_request_date) >= CURDATE()-INTERVAL 1 MONTH";
		}

		$stmt = "select count(*)  
			 	 from " . APP_TABLE_PREFIX . "statistics_all
				 where stl_pid = '".$pid."' and stl_dsid <> '' ".$limit;
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        if (!empty($res)) {
			$count = $res;
		} else {
			$count = 0;
		}
		return $count; 
	}

	function getStatsByUserAbstractView($pid, $year='all', $month='all', $range='all') {	
		if ($pid != 'all') {
			$limit = "where stl_pid = '".$pid."' and stl_dsid = ''";
		} else {
			$limit = "where stl_dsid = '' ";		
		}
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit .= " and year(date(stl_request_date)) = ".$year;
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(date(stl_request_date)) = ".$month;
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and date(stl_request_date) >= CURDATE()-INTERVAL 1 MONTH";
		}

		$stmt = "select count(*) as usr_count, usr_full_name  
			 	 from " . APP_TABLE_PREFIX . "statistics_all
				 inner join " . APP_TABLE_PREFIX . "user on usr_id = stl_usr_id
				 ".$limit."
				 group by usr_full_name
				 order by usr_count desc, usr_full_name asc";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt,  DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        if (!empty($res)) {
			$count = $res;
		} else {
			$count = array();
		}
		return $count; 
	}	


	
	function getStatsByCountryAbstractView($pid, $year='all', $month='all', $range='all') {	
		if ($pid != 'all') {
			$limit = "where stl_pid = '".$pid."' and stl_dsid = ''";
		} else {
			$limit = "where stl_dsid = '' ";		
		}
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit .= " and year(date(stl_request_date)) = ".$year;
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(date(stl_request_date)) = ".$month;
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and date(stl_request_date) >= CURDATE()-INTERVAL 1 MONTH";
		}

		$stmt = "select count(*) as stl_country_count, stl_country_name, stl_country_code  
			 	 from " . APP_TABLE_PREFIX . "statistics_all
				 ".$limit."
				 group by stl_country_name, stl_country_code
				 order by stl_country_count desc, stl_country_name asc";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt,  DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        if (!empty($res)) {
			$count = $res;
		} else {
			$count = array();
		}
		return $count; 
	}	

	function getStatsByCountrySpecificAbstractView($pid, $year='all', $month='all', $range='all',$country) {	
		if ($pid != 'all') {
			$limit = "where stl_pid = '".$pid."' and stl_dsid = ''";
		} else {
			$limit = "where stl_dsid = '' ";		
		}
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit .= " and year(date(stl_request_date)) = ".$year;
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(date(stl_request_date)) = ".$month;
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and date(stl_request_date) >= CURDATE()-INTERVAL 1 MONTH";
		}
		$limit .= " and stl_country_name = '".$country."'";
		$stmt = "select count(*) as stl_country_count, stl_country_name, stl_country_code, stl_region, stl_city
			 	 from " . APP_TABLE_PREFIX . "statistics_all
				 ".$limit."
				 group by stl_country_name, stl_country_code, stl_region, stl_city
				 order by stl_country_name asc, stl_region asc, stl_city asc, stl_country_count desc";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt,  DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            $res = array();
        }
        if (!empty($res)) {
			$count = $res;
		} else {
			$count = array();
		}
		return $count; 
	}	

	function getStatsByCountryAbstractsDownloads($pid, $year='all', $month='all', $range='all') {	

		$aa = Statistics::getStatsByCountryAbstractView($pid, $year, $month, $range);	
		$ad = Statistics::getStatsByCountryAllFileDownloads($pid, $year, $month, $range);	
		return Statistics::mergeCountries($aa, $ad);
	}

	function getStatsByCountrySpecificAbstractsDownloads($pid, $year='all', $month='all', $range='all', $country) {	

		$aa = Statistics::getStatsByCountrySpecificAbstractView($pid, $year, $month, $range, $country);	
		$ad = Statistics::getStatsByCountrySpecificAllFileDownloads($pid, $year, $month, $range, $country);	
			
		$return = Statistics::mergeCountriesSpecific($aa, $ad);
		foreach ($return as $key => &$ret) {
			if ($ret['stl_country_name'] == 'Australia') {
				if ($ret['stl_region'] == '02') {
					$return[$key]['stl_region'] = 'New South Wales';
				} elseif ($ret['stl_region'] == '07') {
					$return[$key]['stl_region'] = 'Victoria';
				} elseif ($ret['stl_region'] == '01') {
					$return[$key]['stl_region'] = 'Australian Capital Territory';
				} elseif ($ret['stl_region'] == '06') {
					$return[$key]['stl_region'] = 'Tasmania';
				} elseif ($ret['stl_region'] == '04') {
					$return[$key]['stl_region'] = 'Queensland';
				} elseif ($ret['stl_region'] == '05') {
					$return[$key]['stl_region'] = 'South Australia';
				} elseif ($ret['stl_region'] == '08') {
					$return[$key]['stl_region'] = 'Western Australia';
				} elseif ($ret['stl_region'] == '09') {
					$return[$key]['stl_region'] = 'Northern Territory';
				} else {
					$return[$key]['stl_city'] = '';
					$return[$key]['stl_region'] = APP_SHORT_ORG_NAME.' Intranet';
				}
			}
		} 
		return $return;
	}

	function getStatsByCountryAllFileDownloads($pid, $year='all', $month='all', $range='all') {	
		if ($pid != 'all') {
			$limit = "where stl_pid = '$pid' and stl_dsid <> ''";
		} else {
			$limit = "where stl_dsid <> '' ";		
		}
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit .= " and year(date(stl_request_date)) = ".$year;
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(date(stl_request_date)) = ".$month;
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and date(stl_request_date) >= CURDATE()-INTERVAL 1 MONTH";
		}
		
		$stmt = "select count(*) as stl_country_count, stl_country_name, stl_country_code  
			 	 from " . APP_TABLE_PREFIX . "statistics_all
				 ".$limit."
				 group by stl_country_name, stl_country_code
				 order by stl_country_count desc, stl_country_name asc";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt,  DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if (!empty($res)) {
				$count = $res;
			} else {
				$count = array();
			}
			return $count; 
		}
	}	

	function getStatsByUserDownloads($pid, $year='all', $month='all', $range='all') {	
		if ($pid != 'all') {
			$limit = "where stl_pid = '$pid' and stl_dsid <> ''";
		} else {
			$limit = "where stl_dsid <> '' ";		
		}
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit .= " and year(date(stl_request_date)) = ".$year;
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(date(stl_request_date)) = ".$month;
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and date(stl_request_date) >= CURDATE()-INTERVAL 1 MONTH";
		}
		
		$stmt = "select count(*) as usr_count, usr_full_name
			 	 from " . APP_TABLE_PREFIX . "statistics_all
				 inner join " . APP_TABLE_PREFIX . "user on usr_id = stl_usr_id
				 ".$limit."
				 group by usr_full_name
				 order by usr_count desc, usr_full_name asc";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt,  DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else { 
			if (!empty($res)) {
				$count = $res;
			} else {
				$count = array();
			}
			return $count; 
		}
	}


	function getStatsByCountrySpecificAllFileDownloads($pid, $year='all', $month='all', $range='all', $country) {	
		if ($pid != 'all') {
			$limit = "where stl_pid = '".$pid."' and stl_dsid <> ''";
		} else {
			$limit = "where stl_dsid <> '' ";		
		}
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit .= " and year(stl_request_date) = ".$year;
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(stl_request_date) = ".$month;
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and stl_request_date >= CURDATE()-INTERVAL 1 MONTH";
		}
		$limit .= " and stl_country_name = '".$country."'";
		
		$stmt = "select count(*) as stl_country_count, stl_country_name, stl_country_code, stl_region, stl_city
			 	 from " . APP_TABLE_PREFIX . "statistics_all
				 ".$limit."
				 group by stl_country_name, stl_country_code, stl_region, stl_city
				 order by stl_country_name asc, stl_region asc, stl_city asc, stl_country_count desc";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt,  DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if (!empty($res)) {
				$count = $res;
			} else {
				$count = array();
			}
			return $count; 
		}
	}	

	function getStatsByObject($pid) {	
		$stmt = "select count(*)  
			 	 from " . APP_TABLE_PREFIX . "statistics_all
				 where stl_pid = '".$pid."'";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if ((count($res) == 1) && (!empty($res))) {
				$count = $res;
			} else {
				$count = 0;
			}
			return $count; 
		}
	}

	function getStatsByAbstractViewHistory($pid) {	
		if ($pid != 'all') {
			$limit = "where stl_pid = '".$pid."' and stl_dsid = ''";
		} else {
			$limit = "where stl_dsid = ''";		
		}

		$stmt = "select count(*) as count,month(date(stl_request_date)) as monthnum,date_format(date(stl_request_date),'%b') as month,year(date(stl_request_date)) as year 
	 	 from " . APP_TABLE_PREFIX . "statistics_all
		 ".$limit." 		
 		 group by year, month, monthnum
		 order by year DESC, monthnum DESC";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if (!empty($res)) {
				$count = $res;
			} else {
				$count = array();
			}
			return $count; 
		}
	}

	function getStatsByDownloadHistory($pid) {	
		if ($pid != 'all') {
			$limit = "where stl_pid = '".$pid."' and stl_dsid <> ''";
		} else {
			$limit = "where stl_dsid <> ''";		
		}
		
		$stmt = "select count(*) as count,month(date(stl_request_date)) as monthnum,date_format(date(stl_request_date),'%b') as month,year(date(stl_request_date)) as year 
	 	 from " . APP_TABLE_PREFIX . "statistics_all
		 ".$limit."		
 		 group by year, month, monthnum 
		 order by year DESC, monthnum DESC";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			$count = $res;
		}
		return $count; 
	}


	function getLastLogRun() {	
		// First get the date (day) of last log run
		$stmt = "select max(stp_lastproc) from " . APP_TABLE_PREFIX . "statistics_proc";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
			if (count($res) == 1) {
				$lastProc = $res;
			} else {
				$lastProc = 0;
			}
		}
		return $lastProc; 
	}

	function gethostbyaddr_with_cache($a) {
	 // Thanks to stephane at metacites dot net (10-Sep-2002 03:17) in the PHP Manual gethostbyaddr user contributed notes for this function!
		global $dns_cache;
		if ($dns_cache[$a]) {
	    	return $dns_cache[$a];
		} else {
			$temp = @gethostbyaddr($a);
			$dns_cache[$a] = $temp;
			return $temp;
		}
	}
	
/* ------------------------------------------------------------------------------
 This function computes an integer datestamp to a month resolution.
 (c) Copyright 2004 Arthur Sale, University of Tasmania
 Parameters:
 	An array and an index into it	
 Returns:
	Result: the datestamp
------------------------------------------------------------------------------ */
	function datestamp(&$x, $j)
	// reference parameter for efficiency, not changed
	// invalid dates return zero, ie earlier than any possible date.
	{
		if ($j < count($x)) {
			// $mo is in {0..11}
			$mo = (int) strpos('JanFebMarAprMayJunJulAugSepOctNovDec', $x[$j]["month"])/3;
			return (int) $x[$j]["year"]*12 + $mo;
		} else {
			return 0;
		}
	}

/* ------------------------------------------------------------------------------
 This function merges two date arrays, to produce a single consolidated array.
 Note that either of the two arrays might be missing a view for a month or more.
 Precondition: both arrays are ordered in descending order of date (latest first)
 	as is the result array.
 (c) Copyright 2004 Arthur Sale, University of Tasmania
 Parameters:
 	Two date-ordered arrays: abstracts and downloads	
 Returns:
	Result: merged array also ordered by date
------------------------------------------------------------------------------ */
	function mergeDates(&$aa, &$ad)
	// reference parameters for efficiency, not changed
	{
		$merged = array();
		// start of all arrays
		$i = 0;
		$indexa = 0;
		$indexd = 0;
		// set up the rest of the loop invariant
		$datea = Statistics::datestamp($aa,0);
		$dated = Statistics::datestamp($ad,0);
		// $datei is initialized to the most recent date for which an access is recorded
		$datei = max($datea, $dated);
		
		while (($datea + $dated) != 0) {
			// Loop invariant: All elements prior to $indexa in $aa and $indexd in $ad 
			//     have been transferred to $merged (ie all dates *after* $datei)
			// Progress: $date1 is decremented by 1 (ie one month *earlier*)
			// Termination: No more elements, signalled by both datestamps == 0.
			if (($datea == $datei) and ($dated == $datei)) {
//				Both are valid and equal to the current date being displayed, the majority case
				$merged[$i] = array(
					"downloads" => $ad[$indexd]["count"],
					"abstracts" => $aa[$indexa]["count"],
					"monthnum"     => $ad[$indexd]["monthnum"], // same value as aa
					"month"     => $ad[$indexd]["month"], // same value as aa
					"year"      => $ad[$indexd]["year"]   // same value as aa
				);
				$indexa++; $indexd++;
				$datea = Statistics::datestamp($aa,$indexa);
				$dated = Statistics::datestamp($ad,$indexd);
			} elseif ($datea == $datei) {
//				aa (but not ad) is equal to the current date being displayed
				$merged[$i] = array(
					"downloads" => 0,
					"abstracts" => $aa[$indexa]["count"],
					"monthnum"     => $aa[$indexa]["monthnum"],
					"month"     => $aa[$indexa]["month"],
					"year"      => $aa[$indexa]["year"]
				);
				$indexa++;
				$datea = Statistics::datestamp($aa,$indexa);
			} elseif ($dated == $datei) {
//				ad (but not aa) is equal to the current date being displayed
				$merged[$i] = array(
					"downloads" => $ad[$indexd]["count"],
					"abstracts" => 0,
					"monthnum"     => $ad[$indexd]["monthnum"], 
					"month"     => $ad[$indexd]["month"], 
					"year"      => $ad[$indexd]["year"]  
				);
				$indexd++;
				$dated = Statistics::datestamp($ad,$indexd);
			} else {
//				Neither aa nor ad are equal to the current date being displayed
//				So generate an empty record
				$monthname = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
				$merged[$i] = array(
					"downloads" => 0,
					"abstracts" => 0,
					"monthnum"  => ($datei % 12) + 1,
					"month"     => $monthname[intval($datei % 12)], 
					"year"      => intval($datei / 12)
				);
			}
			// Progress: one more entry in $merged, and date earlier by one month
			$i++;
			$datei--;
		}
		return $merged;
	}

	function getMonthName($month) {
		$monthname = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');	
		return $monthname[$month -1];
	}


    function comparar($a, $b) {
        return strnatcasecmp($a["stl_region"], $b["stl_region"]);
    }


	/* ------------------------------------------------------------------------------
	 This function merges two country arrays, to produce a single consolidated array.
	 Note that either of the two arrays might be missing a country in the other.
	 
	 (c) Copyright 2004 Arthur Sale, University of Tasmania
	 
	 Precondition: both arrays are ordered in descending order of views.
	 Postcondition: result array contains records for every country in $aa and $ad, 
		and is ordered in descending order of download views.
	------------------------------------------------------------------------------ */
	function mergeCountries($aa, &$ad)
	// reference parameter $ad for efficiency, not changed, 
	//	however the $aa value parameter is altered and is not prapogated back
	{
		$merged = array();
		// Copy acrosss the download array, adding counts from the abstract array as needed.
		for ($i=0; $i<count($ad); $i++) {
			$merged[$i] = array(
				"stl_country_code"      => $ad[$i]["stl_country_code"],
				"stl_country_name"      => $ad[$i]["stl_country_name"],
				"stl_country_downloads" => $ad[$i]["stl_country_count"],
				"stl_country_abstracts" => 0
				);
			$c_code = $merged[$i]["stl_country_code"];
			for ($j=0; $j<count($aa); $j++) {
				if ($c_code == $aa[$j]["stl_country_code"]) {
					// matching country in abstracts
					$merged[$i]["stl_country_abstracts"] = $aa[$j]["stl_country_count"];
					// render this entry dead in future with reserved country code
					$aa[$j]["stl_country_code"] = '==';
					// and get out of the loop
					break;
				}
			} // for on $j
		} // for on $i
		
		// Copy what is left of the abstract array
		$i = count($merged);
		for ($j=0; $j<count($aa); $j++) {
				if ($aa[$j]["stl_country_code"] != '==') {
					// country with only abstract views, so copy
					$merged[$i] = array(
						"stl_country_code"      => $aa[$j]["stl_country_code"],
						"stl_country_name"      => $aa[$j]["stl_country_name"],
						"stl_country_downloads" => 0,
						"stl_country_abstracts" => $aa[$j]["stl_country_count"]
						);
					// and increment $i
					$i++;
				}
		} // for on $j
		return $merged;
	}


	/* ------------------------------------------------------------------------------
	 This function merges two country arrays, to produce a single consolidated array.
	 Note that either of the two arrays might be missing a country in the other.
	 
	 (c) Copyright 2004 Arthur Sale, University of Tasmania
	 
	 Precondition: both arrays are ordered in descending order of views.
	 Postcondition: result array contains records for every country in $aa and $ad, 
		and is ordered in descending order of download views.
	------------------------------------------------------------------------------ */
	function mergeCountriesSpecific($aa, &$ad)
	// reference parameter $ad for efficiency, not changed, 
	//	however the $aa value parameter is altered and is not prapogated back
	{
		$merged = array();
		// Copy acrosss the download array, adding counts from the abstract array as needed.
		for ($i=0; $i<count($ad); $i++) {
			$merged[$i] = array(
				"stl_country_code"      => $ad[$i]["stl_country_code"],
				"stl_country_name"      => $ad[$i]["stl_country_name"],
				"stl_region"      => $ad[$i]["stl_region"],
				"stl_city"      => utf8_encode($ad[$i]["stl_city"]),
				"stl_country_downloads" => $ad[$i]["stl_country_count"],
				"stl_country_abstracts" => 0
				);
			$c_code = $merged[$i]["stl_country_code"];
			$region = $merged[$i]["stl_region"];
			$city = $merged[$i]["stl_city"];			
			for ($j=0; $j<count($aa); $j++) {
				if (($c_code == $aa[$j]["stl_country_code"]) && ($region == $aa[$j]["stl_region"]) && ($city == $aa[$j]["stl_city"])) {
					// matching country in abstracts
					$merged[$i]["stl_country_abstracts"] = $aa[$j]["stl_country_count"];
					// render this entry dead in future with reserved country code
					$aa[$j]["stl_country_code"] = '==';
					$aa[$j]["stl_region"] = '==';
					$aa[$j]["stl_city"] = '==';					
					// and get out of the loop
					break;
				}
			} // for on $j
		} // for on $i
		
		// Copy what is left of the abstract array
		$i = count($merged);
		for ($j=0; $j<count($aa); $j++) {
				if ($aa[$j]["stl_country_code"] != '==') {
					// country with only abstract views, so copy
					$merged[$i] = array(
						"stl_country_code"      => $aa[$j]["stl_country_code"],
						"stl_country_name"      => $aa[$j]["stl_country_name"],
						"stl_region"      => $aa[$j]["stl_region"],
						"stl_city"      => utf8_encode($aa[$j]["stl_city"]),
						"stl_country_downloads" => 0,
						"stl_country_abstracts" => $aa[$j]["stl_country_count"]
						);
					// and increment $i
					$i++;
				}
		} // for on $j
		
		if (is_array($merged)) {
			usort($merged, array("Statistics", "comparar")); 
		}
		return $merged;
	}



    /**
     * Method used to inspect the config file variables and report any obvious problems
     * that will clearly prevent the stats from being scraped. This function mainly exists
     * to prevent ugly PHP errors
     *
     * @access  public
     * @return  boolean
     */
    function checkSetup()
    {
        $failure = '';
        if (WEBSERVER_LOG_STATISTICS == "OFF") {
            $failure = "You must set WEBSERVER_LOG_STATISTICS to 'ON' in order to generate log reports. Please check the config file.";
        } elseif (!is_dir(WEBSERVER_LOG_DIR)) {
            $failure = "Please ensure that WEBSERVER_LOG_DIR is set to a valid directory in the config file.";
        } elseif (!is_file(WEBSERVER_LOG_DIR . WEBSERVER_LOG_FILE)) {
            $failure = "Please ensure that WEBSERVER_LOG_FILE is set to a valid log in the config file.";
        } elseif (!is_dir(APP_GEOIP_PATH)) {
            $failure = "Please ensure that APP_GEOIP_PATH is set to a valid directory in the config file.";
        }

        if (!empty($failure)) {
            echo 'Could not run stats - ' . $failure;
            exit;
        }
    }

    function setBGP(&$bgp) {
        $this->bgp = &$bgp;
    }

}


// benchmarking the included file (aka setup time)
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Statistics Class');
}
?>
