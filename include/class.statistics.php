<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006 The University of Queensland,               |
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

    /**
     * Method used to scan a web server log for Fez statistics.
     *
     * @access  public
     * @return  boolean
     */
    function gatherStats() {
		$timeStarted = date('Y-m-d H:i:s');
		$counter = 0;
		$counter_inserted = 0;
		$requestDateLatest = 0;
		$datetestA = strtotime(Statistics::getLastestLogEntry());
		$requestDateLatest = $datetestA;
		$logf = WEBSERVER_LOG_DIR . WEBSERVER_LOG_FILE;
		$archive_name = APP_HOSTNAME;
		$handle = fopen($logf, "r");
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			// if there are any view.php or eserv.php entries than the abstract was viewed or an datastream/file was viewed
			if	((preg_match("/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}) - - \[(.*?)\] \"GET ".preg_quote(APP_RELATIVE_URL,'/')."\/?view\.php\?.*pid=([a-zA-Z]*:[0-9]+).* HTTP\/1..\" 200 .*/i",$buffer,$matches)) ||
				(preg_match("/^(\S{1,}\.\S{1,}\.\S{1,}\.\S{1,}) - - \[(.*?)\] \"GET ".preg_quote(APP_RELATIVE_URL,'/')."\/?eserv\.php\?.*pid=([a-zA-Z]*:[0-9]+)&dsID=(\S*).* HTTP\/1..\" 200 .*/i",$buffer,$matches)) ||
				(preg_match("/^(\S{1,}\.\S{1,}\.\S{1,}\.\S{1,}) - - \[(.*?)\] \"GET \/robots\.txt.*/i",$buffer,$matches)))
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
				$image_matches = preg_match("/^.*\?.+((thumbnail_)|(ls=0)|(preview_)|(presmd_)).*/i", $buffer);
				if ($image_matches > 0) {
					continue;
				}

				$gi = geoip_open(APP_GEOIP_PATH."GeoLiteCity.dat",GEOIP_STANDARD);
				$record = geoip_record_by_addr($gi,$ip);
				$country_code = $record->country_code;
				$country_name = $record->country_name;
				$city = $record->city;
				$region = $record->region;
				// below commented out lines are other GeoIP information you could possibly use if you are interested
/*				print $record->postal_code . "\n";
				print $record->latitude . "\n";
				print $record->longitude . "\n";
				print $record->dma_code . "\n";
				print $record->area_code . "\n";				*/
				geoip_close($gi);
				if ($pid != "") {
				   $stmt = "INSERT INTO
								" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_all
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
								stl_pid_num,																
								stl_dsid
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
								" . Misc::numPID($pid) . ",								
								'" . $dsid . "'
							 )"; 
					$res = $GLOBALS["db_api"]->dbh->query($stmt);
					if (PEAR::isError($res)) {
						Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
						return -1; //abort
					} else {
						$counter_inserted++;
						//continue
					}
					
				}
			}
		}
		fclose($handle);
		$timeFinished = date('Y-m-d H:i:s');
		Statistics::setLogRun($requestDateLatest, $counter, $counter_inserted, $timeStarted, $timeFinished);
    }

	function setLogRun($request_date, $counter, $counter_inserted, $timeStarted, $timeFinished) {
	/*
		Keep track of where we are. Should avoid duplication of results
		if the script is run more than once on the same log file
	*/
		$stmt = "INSERT INTO
				" . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_proc (stp_latestlog, stp_lastproc, stp_count, stp_count_inserted, stp_timestarted, stp_timefinished)  values ('".date('Y-m-d H:i:s', $request_date)."', '".date('Y-m-d', $request_date)."', $counter, $counter_inserted, '$timeStarted', '$timeFinished')";
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1; //abort
		} else {
			//continue
		}
	}

	function isRobot($ip) {	
		// check if the ip is in the Fez robots listing so far
		$stmt = "select count(*) from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_robots where str_ip = '$ip'";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (($res > 0) && (!empty($res))) {
			$robot = 1;
		} else {
			$robot = 0;
		}
		return $robot; 
	}
	
	function addRobot($ip, $hostname) {	
		// add this ip to the Fez robots listing
		$stmt = "insert into " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_robots (str_ip, str_hostname, str_date_added) values ('$ip', '$hostname', now())";
		$res = $GLOBALS["db_api"]->dbh->query($stmt);
		if (PEAR::isError($res)) {
			Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
			return -1; //abort
		} else {
			//continue
		}

	}	

	function getLastestLogEntry() {	
		// First get the date of latest log entry
		$stmt = "select max(stp_latestlog) from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_proc";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if ((count($res) == 1) && (!empty($res))) {
			$latestLog = $res;
		} else {
			$latestLog = 0;
		}
		return $latestLog; 
	}
	
	function getStatsByDatastream($pid, $dsid) {	
		$stmt = "select count(*)  
			 	 from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_all
				 where stl_pid_num = ".Misc::numPID($pid)." and stl_pid = '$pid' and stl_dsid = '$dsid'";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if ((count($res) == 1) && (!empty($res))) {
			$count = $res;
		} else {
			$count = 0;
		}
		return $count; 
	}

	function getStatsByAbstractView($pid, $year='all', $month='all', $range='all') {	
        $limit = '';
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit = " and year(stl_request_date) = $year";
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(stl_request_date) = $month";
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and stl_request_date >= CURDATE()-INTERVAL 1 MONTH";
		}

		$stmt = "select count(*)  
			 	 from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_all
				 where stl_pid_num = ".Misc::numPID($pid)." and stl_pid = '$pid' and stl_dsid = '' $limit";
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
			$limit = " and year(stl_request_date) = $year";
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(stl_request_date) = $month";
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and stl_request_date >= CURDATE()-INTERVAL 1 MONTH";
		}

		$stmt = "select count(*)  
			 	 from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_all
				 where stl_pid_num = ".Misc::numPID($pid)." and stl_pid = '$pid' and stl_dsid <> '' $limit";
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

	
	function getStatsByCountryAbstractView($pid, $year='all', $month='all', $range='all') {	
		if ($pid != 'all') {
			$limit = "where stl_pid_num = ".Misc::numPID($pid)." and stl_pid = '$pid' and stl_dsid = ''";
		} else {
			$limit = "where stl_dsid = '' ";		
		}
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit .= " and year(stl_request_date) = $year";
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(stl_request_date) = $month";
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and stl_request_date >= CURDATE()-INTERVAL 1 MONTH";
		}

		$stmt = "select count(*) as stl_country_count, stl_country_name, stl_country_code  
			 	 from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_all
				 $limit
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
			$limit = "where stl_pid_num = ".Misc::numPID($pid)." and stl_pid = '$pid' and stl_dsid = ''";
		} else {
			$limit = "where stl_dsid = '' ";		
		}
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit .= " and year(stl_request_date) = $year";
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(stl_request_date) = $month";
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and stl_request_date >= CURDATE()-INTERVAL 1 MONTH";
		}
		$limit .= " and stl_country_name = '".$country."'";
		$stmt = "select count(*) as stl_country_count, stl_country_name, stl_country_code, stl_region, stl_city
			 	 from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_all
				 $limit
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
			$limit = "where stl_pid_num = ".Misc::numPID($pid)." and stl_pid = '$pid' and stl_dsid <> ''";
		} else {
			$limit = "where stl_dsid <> '' ";		
		}
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit .= " and year(stl_request_date) = $year";
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(stl_request_date) = $month";
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and stl_request_date >= CURDATE()-INTERVAL 1 MONTH";
		}
		
		$stmt = "select count(*) as stl_country_count, stl_country_name, stl_country_code  
			 	 from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_all
				 $limit
				 group by stl_country_name, stl_country_code
				 order by stl_country_count desc, stl_country_name asc";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt,  DB_FETCHMODE_ASSOC);
		if (!empty($res)) {
			$count = $res;
		} else {
			$count = array();
		}
		return $count; 
	}	

	function getStatsByCountrySpecificAllFileDownloads($pid, $year='all', $month='all', $range='all', $country) {	
		if ($pid != 'all') {
			$limit = "where stl_pid_num = ".Misc::numPID($pid)." and stl_pid = '$pid' and stl_dsid <> ''";
		} else {
			$limit = "where stl_dsid <> '' ";		
		}
		if ($year != 'all' && is_numeric($year)) {
			$year = Misc::escapeString($year);
			$limit .= " and year(stl_request_date) = $year";
			if ($month != 'all' && is_numeric($month)) {
				$month = Misc::escapeString($month);
				$limit .= " and month(stl_request_date) = $month";
			}
		} elseif ($range != 'all' && $range == '4w') {
			$limit .= " and stl_request_date >= CURDATE()-INTERVAL 1 MONTH";
		}
		$limit .= " and stl_country_name = '".$country."'";
		
		$stmt = "select count(*) as stl_country_count, stl_country_name, stl_country_code, stl_region, stl_city
			 	 from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_all
				 $limit
				 group by stl_country_name, stl_country_code, stl_region, stl_city
				 order by stl_country_name asc, stl_region asc, stl_city asc, stl_country_count desc";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt,  DB_FETCHMODE_ASSOC);
		if (!empty($res)) {
			$count = $res;
		} else {
			$count = array();
		}
		return $count; 
	}	

	function getStatsByObject($pid) {	
		$stmt = "select count(*)  
			 	 from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_all
				 where stl_pid_num = ".Misc::numPID($pid)." and stl_pid = '$pid'";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if ((count($res) == 1) && (!empty($res))) {
			$count = $res;
		} else {
			$count = 0;
		}
		return $count; 
	}

	function getStatsByAbstractViewHistory($pid) {	
		if ($pid != 'all') {
			$limit = "where stl_pid_num = ".Misc::numPID($pid)." and stl_pid = '$pid' and stl_dsid = ''";
		} else {
			$limit = "where stl_dsid = ''";		
		}

		$stmt = "select count(*) as count,month(stl_request_date) as monthnum,date_format(stl_request_date,'%b') as month,year(stl_request_date) as year 
	 	 from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_all
		 $limit 		
 		 group by year(stl_request_date), month(stl_request_date) 
		 order by stl_request_date desc";

		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (!empty($res)) {
			$count = $res;
		} else {
			$count = array();
		}
		return $count; 
	}

	function getStatsByDownloadHistory($pid) {	
		if ($pid != 'all') {
			$limit = "where stl_pid_num = ".Misc::numPID($pid)." and stl_pid = '$pid' and stl_dsid <> ''";
		} else {
			$limit = "where stl_dsid <> ''";		
		}
		
		$stmt = "select count(*) as count,month(stl_request_date) as monthnum,date_format(stl_request_date,'%b') as month,year(stl_request_date) as year 
	 	 from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_all
		 $limit		
 		 group by year(stl_request_date), month(stl_request_date) 
		 order by stl_request_date desc";
		$res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
		if (!empty($res)) {
			$count = $res;
		} else {
			$count = array();
		}
		return $count; 
	}


	function getLastLogRun() {	
		// First get the date (day) of last log run
		$stmt = "select max(stp_lastproc) from " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "statistics_proc";
		$res = $GLOBALS["db_api"]->dbh->getOne($stmt);
		if (count($res) == 1) {
			$lastProc = $res;
		} else {
			$lastProc = 0;
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
		usort($merged, "comparar"); 
		return $merged;
	}
	

}
function comparar($a, $b) {
   return strnatcasecmp($a["stl_region"], $b["stl_region"]);
}

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Statistics Class');
}
?>
