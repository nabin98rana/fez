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
 * Class to handle date convertion issues, which enable the 
 * application of storing all dates in GMT dates and allowing each
 * user to specify a timezone that is supposed to be used across the
 * pages.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */

// this line needed to make sure PEAR knows all Fez dates are stored as UTC (GMT).
// We need to do this before including Pear/Date.php so that it gets the timezone thing right!
if (!defined('APP_DEFAULT_TIMEZONE')) {
    define('APP_DEFAULT_TIMEZONE', 'UTC');                                                        
}
$GLOBALS['_DATE_TIMEZONE_DEFAULT'] = APP_DEFAULT_TIMEZONE;

include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.prefs.php");
include_once(APP_PEAR_PATH . "Date.php");

define("SECOND", 1);
define("MINUTE", SECOND * 60);
define("HOUR", MINUTE * 60);
define("DAY", HOUR * 24);
define("WEEK", DAY * 7);
define("MONTH", WEEK * 4);

class Date_API
{
	
	
      function dateDiff($interval,$dateTimeBegin,$dateTimeEnd) {
        //Parse about any English textual datetime
        //$dateTimeBegin, $dateTimeEnd
        $dateTimeBegin=strtotime($dateTimeBegin);
        if($dateTimeBegin === -1) {
          return("..begin date Invalid");
         }
        $dateTimeEnd=strtotime($dateTimeEnd);
        if($dateTimeEnd === -1) {
          return("..end date Invalid");
         }
        $dif=$dateTimeEnd - $dateTimeBegin;
        switch($interval) {
          case "s"://seconds
               return($dif);
          case "n"://minutes
               return(floor($dif/60)); //60s=1m
          case "h"://hours
               return(floor($dif/3600)); //3600s=1h
          case "d"://days
               return(floor($dif/86400)); //86400s=1d
          case "ww"://Week
               return(floor($dif/604800)); //604800s=1week=1semana
          case "m": //similar result "m" dateDiff Microsoft
               $monthBegin=(date("Y",$dateTimeBegin)*12)+
                 date("n",$dateTimeBegin);
               $monthEnd=(date("Y",$dateTimeEnd)*12)+
                 date("n",$dateTimeEnd);
               $monthDiff=$monthEnd-$monthBegin;
               return($monthDiff);
          case "yyyy": //similar result "yyyy" dateDiff Microsoft
               return(date("Y",$dateTimeEnd) - date("Y",$dateTimeBegin));
          default:
               return(floor($dif/86400)); //86400s=1d
         }
       }	
	
    /**
     * Returns whether the given hour is AM or not.
     *
     * @access  public
     * @param   integer $hour The hour number
     * @return  boolean
     */
    function isAM($hour)
    {
        if (($hour >= 0) && ($hour <= 11)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Returns whether the given hour is PM or not.
     *
     * @access  public
     * @param   integer $hour The hour number
     * @return  boolean
     */
    function isPM($hour)
    {
        if (($hour >= 12) && ($hour <= 23)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Returns the current UNIX timestamp in the GMT timezone.
     *
     * @access  public
     * @return  integer The current UNIX timestamp in GMT
     */
    function getCurrentUnixTimestampGMT()
    {
        return gmmktime();
    }


    /**
     * Method used to get a pretty-like formatted time output for the
     * difference in time between two unix timestamps.
     *
     * @access  public
     * @param   integer $now_ts The current UNIX timestamp
     * @param   integer $old_ts The old UNIX timestamp
     * @return  string The formatted difference in time
     */
    function getFormattedDateDiff($now_ts, $old_ts)
    {
        $value = (integer) (($now_ts - $old_ts) / DAY);
        $ret = sprintf("%d", round($value, 1)) . "d";
        $mod = (integer) (($now_ts - $old_ts) % DAY);
        $mod = (integer) ($mod / HOUR);
        return $ret . " " . $mod . "h";
    }


    /**
     * Method used to get the user's current time (timezone included) as
     * a UNIX timestamp.
     *
     * @access  public
     * @param   integer $timestamp The current UNIX timestamp
     * @param   string $timezone The needed timezone
     * @return  integer The UNIX timestamp representing the user's current time
     */
    function getUnixTimestamp($timestamp, $timezone = FALSE)
    {
        if (empty($timezone)) {
            $timezone = Date_API::getPreferredTimezone();
        }
        $date = new Date($timestamp);
        // now convert to another timezone and return the timestamp
        $date->convertTZById($timezone);
        return $date->getDate(DATE_FORMAT_UNIXTIME);
    }


    /**
     * Method used to get the current date in the GMT timezone in an 
     * RFC822 compliant format.
     *
     * @access  public
     * @return  string The current GMT date
     */
    function getRFC822Date($timestamp)
    {
        $timezone = Date_API::getPreferredTimezone();
        $date = new Date($timestamp);
        // now convert to another timezone and return the date
        $date->convertTZById($timezone);
        return $date->format('%a, %d %b %Y %H:%M:%S') . " GMT";
    }


    /**
     * Method used to get the current date in the GMT timezone.
     *
     * @access  public
     * @return  string The current GMT date
     */
    function getCurrentDateGMT($includeMilliseconds=false)
    {
    	if( $includeMilliseconds )
	        return gmdate('Y-m-d H:i:s.u');
	    return gmdate('Y-m-d H:i:s');
    }


    /**
     * Method used to get the full list of available timezones to be
     * presented to the user.
     *
     * @access  public
     * @return  array The list of timezones
     */
    function getTimezoneList()
    {
        ksort($GLOBALS['_DATE_TIMEZONE_DATA']);     // Because nobody should have to look at that crazy randomised list ever again.
        return Date_TimeZone::getAvailableIDs();
    }


    /**
     * Method used to get the formatted date for a specific timestamp
     * and a specific timezone, provided by the user' preference.
     *
     * @access  public
     * @param   string $timestamp The date timestamp to be formatted
     * @param   string $timezone The timezone name
     * @return  string 
     */
    function getFormattedDate($timestamp, $timezone = FALSE)
    {
        if ($timezone === FALSE) {
            $timezone = Date_API::getPreferredTimezone();
        }
        $date = new Date($timestamp);
        // now convert to another timezone and return the date
        $date->convertTZById($timezone);
        return $date->format('%a, %d %b %Y, %H:%M:%S ') . $date->tz->getShortName();
    }

    /**
     * Method used to get the formatted date for a specific timestamp
     * and a specific timezone, provided by the user' preference.
     *
     * @access  public
     * @param   string $timestamp The date timestamp to be formatted
     * @param   string $timezone The timezone name
     * @return  string 
     */
    function getFormattedSimpleDate($timestamp, $timezone = FALSE)
    {
        if ($timezone === FALSE) {
            $timezone = Date_API::getPreferredTimezone();
        }
        $date = new Date($timestamp);
        // now convert to another timezone and return the date
        $date->convertTZById($timezone);
        return $date->format('%d-%m-%Y');
    }	
	
    /**
     * Method used to get the formatted date for Fedora
     *      
     * @access  public
     * @param   string $timestamp The date timestamp to be formatted
     * @return  string 
     */
    function getFedoraFormattedDate($timestamp = null)
    {
		if ($timestamp == null) {
			$timestamp = Date_API::getUnixTimestamp();
		}
        $date = new Date($timestamp);

		
        return $date->format('%Y-%m-%dT%H:%M:%SZ');
    }
    
    
    /**
     * Method used to get the formatted date for Fedora in UTC
     *      
     * @access  public
     * @param   string $timestamp The date timestamp to be formatted
     * @return  string 
     */
    function getFedoraFormattedDateUTC($timestamp = null)
    {
		if ($timestamp == null) {
//			$timestamp = Date_API::getCurrentUnixTimestampGMT();
		}
        $date = new Date($timestamp);
        $date->setTZbyID(Date_API::getPreferredTimezone());
        $date->toUTC();
		
        return $date->format('%Y-%m-%dT%H:%M:%SZ');
    }

	function getSimpleDateUTC($timestamp = null)
    {
		if ($timestamp == null) {
			$timestamp = Date_API::getCurrentUnixTimestampGMT();
		}
        $date = new Date($timestamp);
        $date->setTZbyID(Date_API::getPreferredTimezone());
        $date->toUTC();
        return $date->format('%Y-%m-%d %H:%M:%S');    }
	
	/**
     * Method used to get the formatted date for a specific timestamp
     * and a specific timezone, provided by the user' preference.
     *
     * @access  public
     * @param   string $timestamp The date timestamp to be formatted
     * @return  string 
     */
    function getSimpleDate($timestamp)
    {
        $timezone = Date_API::getPreferredTimezone();
        $date = new Date($timestamp);
        // now convert to another timezone and return the date
        $date->convertTZById($timezone);
        return $date->format('%d %b %Y');
    }
    
    function getSimpleDateTime($timestamp)
    {
        $timezone = Date_API::getPreferredTimezone();
        $date = new Date($timestamp);
        // now convert to another timezone and return the date
        $date->convertTZById($timezone);
        return $date->format('%d %b %Y %H:%M:%S');
    }


    /**
     * Method used to get the timezone preferred by the user.
     *
     * @access  public
     * @param   integer $usr_id The user ID
     * @return  string The timezone preferred by the user
     */
    function getPreferredTimezone($usr_id = FALSE)
    {
        if ($usr_id === FALSE) {
            $usr_id = Auth::getUserID();
        }
        if (empty($usr_id)) {
           // return Date_API::getDefaultTimezone();
            return APP_DEFAULT_USER_TIMEZONE;
        }
		
        $prefs = Prefs::get($usr_id);
        if (empty($prefs["timezone"])) {
            return APP_DEFAULT_USER_TIMEZONE;
        } else {
            return $prefs["timezone"];
        }
    }


    /**
     * Method used to get the application default timezone.
     *
     * @access  public
     * @return  string The default timezone
     */
    function getDefaultTimezone()
    {
        return APP_DEFAULT_TIMEZONE;
    }


    /**
     * Method used to convert the user date (that might be in a 
     * specific timezone) to a GMT date.
     *
     * @access  public
     * @param   string $date The user based date
     * @return  string The date in the GMT timezone
     */
    function getDateGMT($date=null)
    {
        $dt = new Date($date);
        $dt->setTZbyID(Date_API::getPreferredTimezone());
        $dt->toUTC();
        return $dt->format('%Y-%m-%d %H:%M:%S');
    }


    /**
     * Method used to convert a unix timestamp date to a GMT date.
     *
     * @access  public
     * @param   integer $timestamp The user based date
     * @return  string The date in the GMT timezone
     */
    function getDateGMTByTS($timestamp)
    {
        return gmdate('Y-m-d H:i:s', $timestamp);
    }


    /**
     * Returns a list of weeks (May 2 - May 8, May 9 - May 15).
     * 
     * @access public
     * @param   integer $weeks_past The number of weeks in the past to include.
     * @param   integer $weeks_future The number of weeks in the future to include.
     * @return  array An array of weeks.
     */
    function getWeekOptions($weeks_past, $weeks_future)
    {
        $options = array();
        
        // get current week details
        $current_start = date("U") - (DAY * (date("w") - 1));
        
        // previous weeks
        for ($week = $weeks_past; $week > 0; $week--) {
            $option = Date_API::formatWeekOption($current_start - ($week * WEEK));
            $options[$option[0]] = $option[1];
        }
        
        $option = Date_API::formatWeekOption($current_start);
        $options[$option[0]] = $option[1];
        
        // future weeks
        for ($week = 1; $week <= $weeks_future; $week++) {
            $option = Date_API::formatWeekOption($current_start + ($week * WEEK));
            $options[$option[0]] = $option[1];
        }
        
        return $options;
    }


    /**
     * Returns the current week in the same format formatWeekOption users.
     * 
     * @access  public
     * @return  string A string containg the current week.
     */
    function getCurrentWeek()
    {
        $value_format = "Y-m-d";
        $start = date("U") - (DAY * (date("w") - 1));
        return date($value_format, $start) . "_" . date($value_format, ($start + (DAY * 6)));
    }


    /**
     * Formats a given week start and week end to a format useable by getWeekOptions().
     * 
     * @access  private
     * @param   integer $start The start date of the week.
     * @return  array An array usable as an option in getWeekOptions.
     */
    function formatWeekOption($start)
    {
        $value_format = "Y-m-d";
        $display_format = "M jS";
        $end = ($start + (DAY * 6));
        $value = date($value_format, $start) . "_" . date($value_format, $end);
        $display = date($display_format, $start) . " - " . date($display_format, $end);
        return array($value,$display);
    }
}

// benchmarking the included file (aka setup time)
if (defined('APP_BENCHMARK') && APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Date_API Class');
}
?>
