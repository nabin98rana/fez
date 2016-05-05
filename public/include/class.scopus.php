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
// | Authors: Andrew Martlew <a.martlew@library.uq.edu.au>                |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to handle access to the Scopus XAbstracts Metadata Service
 *
 * @version 1.0
 * @author Andrew Martlew <a.martlew@library.uq.edu.au>
 * 
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . 'nusoap.php');
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . 'class.scopus_service.php');

class Scopus
{
	function __construct()
	{
				
	}


	/**
	 * Method used to get the pid and scopus eid from the returned eid tagging table - only really meant for UQ consumption but might be interesting to some other Australian users.
	 *
	 * @access  public
	 * @return  array The list of languages
	 */
	function getReturnedEIDTaggingList($page = 0, $max = 0) {
		$log = FezLog::get();
		$db = DB_API::get();

		if (!is_numeric($page) || !is_numeric($max)) {
			return false;
		}
		$limit = "";
		if ($max != 0) {
			$limit = "LIMIT ".$max." OFFSET ".($page * $max);
		}

		$stmt = "
SELECT uq_pid, sco_eid FROM
era_2012_eid_returned_results_all a
INNER JOIN fez_record_search_key ON rek_pid = uq_pid
INNER JOIN fez_record_search_key_ismemberof ON rek_pid = rek_ismemberof_pid
LEFT JOIN fez_record_search_key_scopus_id ON uq_pid = rek_scopus_id_pid AND rek_scopus_id = sco_eid
LEFT JOIN fez_xsd_display ON xdis_id = rek_display_type
WHERE
sco_matched_on = 'title'
AND sco_status = 'Successfully Matched'
AND ((rek_genre_type = 'Fully Published Paper' AND xdis_title = 'Conference Paper') OR xdis_title != 'Conference Paper')
AND rek_ismemberof NOT IN ('UQ:244548')
AND (sco_start_page = uq_start_page OR sco_end_page = uq_end_page)
AND rek_scopus_id IS NULL
GROUP BY rek_pid
		".$limit;
		
		try {
			$res = $db->fetchAll($stmt);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return '';
		}
		
		return $res;
	}	

  
     /**
    * Retrieve cited by count information for a list of articles
    *
    * @param array $input_keys  //eid or doi
    * @return mixed Array of PIDs which contains for each pid, an array of eid, scopusID and citedByCount; or false
    * eg Array
    *(
    *    [UQ:30001] => Array
    *        (
    *            [eid] => 2-s2.0-0035584235    // or [doi]
    *            [scopusID] => 0035584235
    *            [citedByCount] => 2
    *        )
    *    [UQ: etc...
    *
    * JH 20160111
    */
    public static function getCitedByCount($input_keys)
      {

        // Developer info:  
        //http://dev.elsevier.com/index.html 
        //http://dev.elsevier.com/tecdoc_cited_by_in_scopus.html  *Note: Scopus ID example is not quite right...
        //http://api.elsevier.com/documentation/SCOPUSSearchAPI.wadl
            
        // Test input
        /*
        $input_keys = array('UQ:30031' => array('eid' => '2-s2.0-77749318564'),
        'UQ:30032' => array('eid' => '2-s2.0-78650218172'),
        );

        $input_keys = array('UQ:30031' => array('doi' => '10.1149/1.2840015'),
        'UQ:30032' => array('doi' => '10.1016/S0020-7519(07)00459-6'),
        );

        */

        // Initialize arrays
        $result = array();
        $log = FezLog::get();

        // Prevent update if we don't have credentials.
        if (! defined(APP_SCOPUS_API_KEY)) {
            $log->err("getCitedByCount: APP_SCOPUS_API_KEY not set, not updating scopus counts.");
            return false;
        }

        // Set up Get request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return as string
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'X-ELS-APIKey: ' . APP_SCOPUS_API_KEY,
          'Accept: application/xml'
          ));
          
        // Set up API URL  
        $url = 'http://api.elsevier.com/content/search/index:SCOPUS?query=';
            
        //  Loop through the input keys (includes PIDs and Scopus IDs) to set up the default result array
        foreach($input_keys as $pid => $v) {
          foreach($v as $type => $_v) {
           
            // Build return array
            $scopus_info = array();
            $scopus_info[$type] = $_v;

            if ($type == 'eid') {  //make sure prefix is striped
              $scopus_info['scopusID'] = str_ireplace('2-s2.0-', '', $_v);
            }
            // Set default citation count to 0
            $scopus_info['citedByCount'] = 0;
            $result[$pid] = $scopus_info;
            
            // Add this Scopus ID or DOI to the query string
            if ($type == 'doi') {
                $url = $url . "doi(" . $_v . ")+OR+";
            } else {
              $url = $url . "eid(" . $_v . ")+OR+";
            }
          }
          
        }
        // Remove last +OR+ and add field info, start and count info, etc to URL
        $url = substr($url, 0, strlen($url)-4) . '&field=citedby-count,doi&suppressNavLinks&start=0&count=' . sizeof($input_keys);

        // Set the CURL URL
        curl_setopt($ch, CURLOPT_URL,$url);

        // Make Get request
        $scopus_result=curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // Handle unauthorised or other bad statuses.
        if ($httpcode != 200) {
            $log->err("getCitedByCount: http status was $httpcode not updating scopus count(s)." .
                      PHP_EOL . "Scopus api:" . PHP_EOL . $scopus_result);
            return false;
        }

        // Load results to xml document for xpath parsing
        $doc = DOMDocument::loadXML($scopus_result);

        if ($doc === false) {
            $log->err($scopus_result);
            return false;
        }

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace("atom","http://www.w3.org/2005/Atom"); // must register namespace
        $countEntries = $xpath->query("/atom:search-results/atom:entry/atom:citedby-count");
        $doiEntries = $xpath->query("/atom:search-results/atom:entry/atom:citedby-count");

        // Parse the xml and put the resulting citation counts in the scopus_counts array
        foreach ($countEntries as $entry) {
          $scopusID = $entry->previousSibling->previousSibling->nodeValue;
          $scopusID = substr($scopusID, strrpos($scopusID,'/')+1);
          $count = $entry->nodeValue;
          $doi = $entry->previousSibling->nodeValue;
          foreach($result as $pid => $value) {
              if (($result[$pid]['doi'] == $doi) || ($result[$pid]['scopusID'] == $scopusID)) {
                  $result[$pid]['doi'] = $doi;
                  $result[$pid]['eid'] = '2-s2.0-'.trim($scopusID);
                  $result[$pid]['scopusID'] = trim($scopusID);
                  $result[$pid]['citedByCount'] = $count;
              }
          }

        }
        return $result;

    }	
    
	/**
	 * Retrieve cited by count information for a list of articles
	 *
	 * @param array $input_keys
	 * @return SimpleXMLElement The object containing records found in Scopus matching the input key(s) specified 
	 */

    /**
     * Returns a list of Scopus document types
     * @return array|string
     */
    public function getAssocDocTypes()
    {
        $log = FezLog::get();
        $db = DB_API::get();

        static $returns;

        if (!empty($returns)) {
          return $returns;
        }

        $stmt = "SELECT
                        sdt_code, concat_ws(' - ',   sdt_code, sdt_description) as doctype
                     FROM
                        " . APP_TABLE_PREFIX . "scopus_doctypes
                     ORDER BY
                        sdt_description";

        try {
            $res = $db->fetchPairs($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }

        if ($GLOBALS['app_cache']) {
            // make sure the static memory var doesnt grow too large and cause a fatal out of memory error
            if (!is_array($returns) || count($returns) > 10) {
                $returns = array();
            }
            $returns = $res;
        }
        return $res;

    }

    /**
     * Returns the description of a Scopus doc type code.
     *
     * @param int $sdt_code Scopus Doc Type code
     * @return string The description of a Scopus Doc Type code
     */
    public function getTitle($sdt_code)
    {
        $log = FezLog::get();
        $db = DB_API::get();

        $stmt = "SELECT
                    sdt_description
                 FROM
                    " . APP_TABLE_PREFIX . "scopus_doctypes
                 WHERE
                    sdt_code=".$db->quote($sdt_code, 'STRING');
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            $log->err($ex);
            return '';
        }
        return $res;
    }

}