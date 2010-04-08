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
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//

/**
 * Class to handle Google Scholar "api", functions taken from Drupal Citation Count module: http://drupal.org/project/citationcounts
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.misc.php");

class Google_Scholar
{
	/**
	 * Method used to .
	 *
	 * @access  public
	 * @return
	 */
	function citationcounts_scholar_citedby($title, $authors, $journal, $year) {
		$scholarsch = array(); // TODO: variable_get(...)

		$querytitle = $title;
		if (isset($scholarsch[$title])) $querytitle = $scholarsch[$title];

		$authquery = "";
		$titlequery = "\"$querytitle\"";
		$intitlequery = "intitle:\"$querytitle\"";
		$journalquery = "";
		$yearquery = "";

		if (isset($journal)) {
			$journalquery = "&as_publication=".urlencode($journal);
		}
		if (is_numeric($year)) {
			$yearquery = "&as_ylo=".urlencode($year)."&as_yhi=".urlencode($year);
		}

		if (isset($authors)) {
			# lastname only:
			#               preg_match_all("/([^A-Za-zÃ¼Ã¤Ã¶Ã]+), /", $authors, $matches);
			preg_match_all("/([A-Za-z]+), /", $authors, $matches);
			#                preg_match_all("/([^,; ]+), /", $authors, $matches);

			foreach($matches[1] AS $key => $value) {

				$authquery.=" author:$value";
				# too complicated/better results w/out "author:..."
				//	                        if (!preg_match("/[^A-Za-z]/", $value)) {
				# only use name if no diacritics (as these are often wrong in scholar)
				//	                                $authquery.=" $value";
				//	                        }
			}
		}
		$query = urlencode($authquery.' '.$intitlequery).$journalquery.$yearquery;
		$articles = Google_Scholar::citationcounts_retrieve_scholar_results($query);
		if (count($articles) > 0) {
			$article = $articles[0];
			$curtitle = trim(str_replace("&hellip;", "", $article['title']));
			if (
				(strpos(Google_Scholar::citationcounts_normtitle($title), Google_Scholar::citationcounts_normtitle($curtitle))!==FALSE) or
				(strpos(Google_Scholar::citationcounts_normtitle($querytitle), Google_Scholar::citationcounts_normtitle($curtitle))!==FALSE)
			)
				return array('citedby' => $article['citations'], 'link' => $article['citations_link']);
			else
				return false;		
		}
		else {
			return false;
		}
	}

	function citationcounts_normtitle($title) {
		$norm = html_entity_decode($title); // eg. &amp; into &
		$norm = strtolower($norm);
		$norm = str_replace("&hellip;","",$norm);
		#$norm = trim($norm); //done here:
		$norm = preg_replace("/[^a-z]/", "", $norm);
		#$norm = substr($norm, 0, 64);
		//	        $_SESSION['citations']['normtitle'][] = $norm;
		return $norm;
	}

	function citationcounts_retrieve_scholar_results($query) {
		//	        $account = user_load(array('uid'=>1));
		$url = "http://scholar.google.com.au/scholar?q=".$query;
		//			echo "querying $url <br />\n";
		//	        $_SESSION['citations']['url'] = $url;

		/*
		 $url_alt = variable_get("citationcounts_url_alt", '');
		 $gsjson = file_get_contents($url_alt.urlencode($url));
		 $_SESSION['citations']['json'] = $gsjson;
		 $articles = json_decode(utf8_encode($gsjson),true); // as array
		 */

		//	        $gs = (file_get_contents($url));

		// set user agent
			$useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
		//$useragent="Linux Mozilla"; // "Engage cloaking device!" = Thanks to ePrints 3 for the inspiration for this line of code!


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		$gs = curl_exec ($ch);
		$info = curl_getinfo($ch);
		if ($gs && $info['http_code'] == 200) {			
			curl_close ($ch);
		} else {
			echo 'Curl error'.curl_error($ch)."\n";
			Error_Handler::logError(curl_error($ch)." ".$url,__FILE__,__LINE__);
			curl_close ($ch);
		}
		$articles = Google_Scholar::citationcounts_parseScholar($gs);
		//			print_r($articles);
		//	        $_SESSION['citations']['arts'] = $articles;
		//if ($articles['status'] == 'scholarblock') {
		//	        if (preg_match("We're sorry", $gs)) {
		/*	                drupal_set_message("status: ".$articles['status']);
		 variable_set("citationcounts_googlescholar_blocked", "1 ".$gsjson);
		 watchdog("citations", "scholar block", WATCHDOG_ERROR);
		 drupal_mail("citationcounts_mailkey", $account->mail, "[caravela] scholar block", "sorry");
		 module_invoke_all('exit');
		 exit(); */
		//return false;
		//	        }
		sleep(rand(43, 74)); // enforce a wait period before the next query
		return $articles;
	}

	function citationcounts_parseScholar($gs) {
		$articles = array();
		$p = "/<p class=g>(.*?)<\/font>  /s";
		preg_match_all($p, $gs, $matches);
		//			print_r($matches);
		foreach($matches[0] as $a) {
			$article = array();

			$p = '|</?b>|';
			$a = preg_replace($p, '', $a);
			//					echo $a;
			$p = '|<font size=-2>\[[^\]]+\]</font>&nbsp;|';
			$a = preg_replace($p, '', $a);
			//					echo $a;
			//	                $p = '|<span class=a>&#x25ba;</span>|';
			//	                $a = preg_replace($p, '', $a);
			//					echo $a;
			$p1 = '|<p class=g><h3 class="r"><a href="([^"]+q=)?([^"]+)"[^>]*>([^<]+)</a>|';
			$p2 = '|<p class=g>([^<]+)<font|';

			preg_match_all($p1, $a, $matches1, PREG_SET_ORDER);
			$article['title'] = $matches1[0][3];
			if (!isset($article['title'])) {
				preg_match_all($p2, $a, $matches2, PREG_SET_ORDER);
				$article['title'] = $matches2[0][1];
			}

			$p = '|>Cited by ([0-9]+)<|';
			preg_match($p, $a, $matches3);
			$article['citations'] = $matches3[1];
			// href="/scholar?hl=en&lr=&ie=UTF-8&cites=2640258626553298920">Cited by 29
			//	                $p = '|cites=([0-9]+)">Cited by |';
			$p = '|&cites=([0-9]+)">Cited by |';
			preg_match($p, $a, $matches4);
			if (is_numeric($matches4[1])) {
				$article['citations_link'] = "http://scholar.google.com.au/scholar?hl=en&lr=&cites=".$matches4[1];
			} else {
				$article['citations_link'] = "";
			}


			$articles[] = $article;
		}
		return $articles;
	}
		
	
	/**
     * Method inserts a new Google Scholar citation count entry
     * 
     * @param $pid The PID to insert the citation count for
     * @param $count The count to insert 
     * @param $link The link to insert 
     * @return bool True if the insert was successful else false
     */
    public static function insertGoogleScholarCitationCount($pid, $count, $link) {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

        $stmt = "INSERT INTO
                    " . $dbtp . "google_scholar_citations
                 (gs_id, gs_pid, gs_count, gs_link, gs_last_checked, gs_created)
                 VALUES
                 (NULL, ?, ?, ?, ?, ?)";

    	try {
			$db->query($stmt, array($pid, $count, $link, time(), time()));
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		
        return true;
    }
    
    
    public static function updateCitationCache($pid) {

        $cites = Google_Scholar::getGoogleScholarCitationCountHistory($pid, 1);
        if (count($cites) == 1) {
            Record::updateGoogleScholarCitationCount($pid, $cites[0]['gs_count'], $cites[0]['gs_link']);
        }
    }


    /**
     * Method updates the last time a Google Scholar citation count was checked
     * 
     * @param $pid The PID to update the last checked date for
     * @param $count The count to update with
     * @return bool True if the update was successful else false
     */
    public static function updateGoogleScholarCitationLastChecked($gs_id) {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

        $stmt = "UPDATE
                   " . $dbtp . "google_scholar_citations
                 SET
                     gs_last_checked = ?
                 WHERE
                    gs_id = ?";
        
		try {
			$db->query($stmt, array(time(), $gs_id));
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
        
        return true;
    }

    /**
     * Returns Google Scholar citation count history for a pid
     * 
     * @param $pid The PID to get the citation count history for 
     * @return array The citation count history 
     */
    public static function getGoogleScholarCitationCountHistory($pid, $limit = false) {
    	$log = FezLog::get();
		$db = DB_API::get();
		
        $dbtp =  APP_TABLE_PREFIX; // Database and table prefix

        $limit = ($limit) ? 'LIMIT '.$limit:null;
        $stmt = "SELECT
                    gs_last_checked,gs_created,gs_count,gs_link
                 FROM
                   " . $dbtp . "google_scholar_citations
                 WHERE
                    gs_pid = ?
                 ORDER BY gs_created DESC
                 $limit";    
		try {
			$res = $db->fetchAll($stmt, array($pid), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return false;
		}
		return $res;
    }

}

