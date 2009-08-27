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
 * Class to manage paginated links on the frontend pages.
 *
 * @version 1.0
 * @author Jo�o Prado Maia <jpm@mysql.com>
 */

include_once(APP_INC_PATH . "class.error_handler.php");

class Pager
{

	private static $cookie;


	/**
	 * Method used to get the current listing related cookie information.
	 *
	 * @access  public
	 * @return  array The Record listing information
	 */
	function getCookieParams()
	{
		if (empty(self::$cookie)) {
			$return = @unserialize(base64_decode($_COOKIE[APP_LIST_COOKIE]));
			self::$cookie = $return;
		}

		return self::$cookie;
	}

	/**
	 * Method used to get a specific parameter in the Record listing cookie.
	 *
	 * @access  public
	 * @param   string $name The name of the parameter
	 * @return  mixed The value of the specified parameter
	 */
	function getParam($name, $params=array())
	{
		$cookie = Pager::getCookieParams();
		$cookie_key = $_SERVER['SCRIPT_NAME'];
		
		$result = '';
		if (isset($params[$name])) {
			$result =  $params[$name];
		} elseif (isset($_GET[$name])) {
			$result =  $_GET[$name];
		} elseif (isset($_POST[$name])) {
			$result =  $_POST[$name];
		} elseif (isset($cookie[$cookie_key][$name])) {
			$result =  $cookie[$cookie_key][$name];
		}
		//echo "<pre>$name: ". print_r($result,true) ."</pre>\n";
		return $result;
	}

	function setParam($name, $value, &$params=null)
	{
		if (!is_null($params)) {
			$params[$name] = $value;
			return;
		}
		self::$cookie[$name] = $value;
	}

	function sendCookie()
	{
		$encoded = base64_encode(serialize(self::$cookie));
		@setcookie(APP_LIST_COOKIE, $encoded, APP_LIST_COOKIE_EXPIRE);
	}

	/**
	 * Method used to save the current search parameters in a cookie.
	 *
	 * @access  public
	 * @return  array The search parameters
	 */
	function saveSearchParams($params = array(), $cookie_key = '')
	{
		// @@@ CK 21/7/2004 - Added this global for the custom fields check.
		$isMemberOf = Pager::getParam('isMemberOf',$params);
		$sort_by = Pager::getParam('sort_by',$params);
		//        $order_by = Pager::getParam('order_by',$params);
		//  $order_by_dir = Pager::getParam('order_by_dir',$params);
		$sort_order = Pager::getParam('sort_order',$params);
		$rows = Pager::getParam('rows',$params);
		$cookie = array(
            'front_page'     => $front_page ? $front_page : "front_page",
            'rows'           => $rows ? $rows : APP_DEFAULT_PAGER_SIZE,
            "sort_by"        => $sort_by ? $sort_by : "title",                        
            "isMemberOf"     => $isMemberOf != "" ? $isMemberOf : "ALL",            
            "sort_order"     => is_numeric($sort_order) ? $sort_order : 0,
		// quick filter form
            'keywords'       => Pager::getParam('keywords',$params),
		);

		if( $cookie_key == '' )
		{
			$cookie_key     = $_SERVER['SCRIPT_NAME'];
		}

		$cookieToSave       = Pager::getCookieParams(); //Why do we need to get this for? commented out CK 6/12/06 // uncommented for search_key expansion by CK 27/2/07

		// get the remember search param for this user
		$prefs = Prefs::get(Auth::getUserID());
		if (isset($prefs['remember_search_params'])) {
			$rememberSearchParams = $prefs['remember_search_params'];
		}
		else {
			$rememberSearchParams = 'no'; // default to don't remember
		}

		// don't grab cookie values if we don't want to remember search params for this user
		if ($rememberSearchParams == 'no') {
			$existing_cookie = array();
		}
		else {
			$existing_cookie    = $cookieToSave[$cookie_key];
		}
		
		$sek_count = Search_Key::getMaxID();

		/*
		 * Extract search keys from cookie
		 */
		if(is_array($existing_cookie)) {
			foreach ($existing_cookie as $sek_id => $value) {
				if (strpos($sek_id, "searchKey") !== false) {
					$searchKeys[str_replace("searchKey", "", $sek_id)] = $value;
				}
			}
		}

		if ($sek_count > 0) {
			$searchKeyArray = array();
			$from_cookie = false;
			$searchKeyArray = Pager::getParam('search_keys',$params);

			/*
			 * If no search keys were submitted
			 * retrieve from cookie
			 */
			if (empty($searchKeyArray) && is_array($searchKeys)) {
				$searchKeyArray = array();
				$from_cookie = true;

				foreach ($searchKeys as $sek_id => $searchValue) {

					if (isset($existing_cookie['searchKey'.$sek_id])) {
						$existing = array(
                            'searchKey'.$sek_id => $existing_cookie['searchKey'.$sek_id]
						);
						$searchKeyArray = array_merge($searchKeyArray, $existing);
					}
				}
			}

			if( is_array($searchKeyArray)) {

				/*
				 * Format date
				 */
				foreach ($searchKeyArray as $sek_id => $value) {
					$sekdet = Search_Key::getDetails(str_replace("searchKey", "", $sek_id));
					if ($sekdet['sek_html_input'] == 'date') {
							
						if ($searchKeyArray[$sek_id]['start']['Month']) { $searchKeyArray[$sek_id]['start']['Month'] = str_pad($searchKeyArray[$sek_id]['start']['Month'], 2, '0', STR_PAD_LEFT); }
						if ($searchKeyArray[$sek_id]['start']['Day'] != "") { $searchKeyArray[$sek_id]['start']['Day'] = str_pad($searchKeyArray[$sek_id]['start']['Day'], 2, '0', STR_PAD_LEFT); }
						if ($searchKeyArray[$sek_id]['end']['Month'] != "") { $searchKeyArray[$sek_id]['end']['Month'] = str_pad($searchKeyArray[$sek_id]['end']['Month'], 2, '0', STR_PAD_LEFT); }
						if ($searchKeyArray[$sek_id]['end']['Day'] != "") { $searchKeyArray[$sek_id]['end']['Day'] = str_pad($searchKeyArray[$sek_id]['end']['Day'], 2, '0', STR_PAD_LEFT); }
							
						$searchKeyArray[$sek_id]['start_date'] =  $searchKeyArray[$sek_id]['start']['Year'] . '-' . $searchKeyArray[$sek_id]['start']['Month'] . '-' . $searchKeyArray[$sek_id]['start']['Day'];
						$searchKeyArray[$sek_id]['end_date'] =  $searchKeyArray[$sek_id]['end']['Year'] . '-' . $searchKeyArray[$sek_id]['end']['Month'] . '-' . $searchKeyArray[$sek_id]['end']['Day'];
					}
				}
				 
				foreach ($searchKeyArray as $sek_id => $value) {
					if ($from_cookie == true) {
						$tempArray = array($sek_id => $value);
					} else {
						$tempArray = array('searchKey'.$sek_id => $value);
					}
					$cookie = array_merge($cookie, $tempArray);
				}

			}
		}
		 
		if (!empty($cookie["searchKey0"]) && empty($sort_by)) {
			$cookie['sort_by'] = "searchKey0";
		}

		$operator = Pager::getParam('operator',$params);

		if( empty($operator) && !empty($existing_cookie['operator']) ) {
			$operator = $existing_cookie['operator'];
		}

		if ($operator) {
			$cookie['operator'] = $operator;
		}

		$cookieToSave[$cookie_key] = $cookie;
		$encoded = base64_encode(serialize($cookieToSave));
		setcookie(APP_LIST_COOKIE, $encoded, APP_LIST_COOKIE_EXPIRE, "/");
		return $cookie;
	}


	/**
	 * Returns the total number of rows for a specific query. It is used to
	 * calculate the total number of pages of data.
	 *
	 * @access  public
	 * @param   string $stmt The SQL statement
	 * @return  int The total number of rows
	 */
	function getTotalRows($stmt)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = str_replace("\n", "", $stmt);
		$stmt = str_replace("\r", "", $stmt);
		$stmt = preg_replace("/SELECT (.*?) FROM /sei", "'SELECT COUNT(*) AS total_rows FROM '", $stmt);
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return 0;
		}
		if (empty($res)) {
			return 0;
		}
		// the query above works only if there is no left join or any other complex queries
		else if (count($res) == 1) {
			return $res[0]["total_rows"];
		} else {
			return count($res);
		}
	}

	/**
	 * Returns the total number of rows for a specific query. It is used to
	 * calculate the total number of pages of data.
	 *
	 * @access  public
	 * @param   string $stmt The SQL statement
	 * @return  int The total number of rows
	 */
	function getTotalRowsDistinct($stmt, $distinct_field)
	{
		$log = FezLog::get();
		$db = DB_API::get();
		
		$stmt = str_replace("\n", "", $stmt);
		$stmt = str_replace("\r", "", $stmt);
		$stmt = preg_replace("/SELECT (.*?) FROM /sei", "'SELECT COUNT(DISTINCT ".$distinct_field.") AS total_rows FROM '", $stmt);
		try {
			$res = $db->fetchAll($stmt, array(), Zend_Db::FETCH_ASSOC);
		}
		catch(Exception $ex) {
			$log->err($ex);
			return 0;
		}
		if (empty($res)) {
			return 0;
		}
		else if (count($res) == 1) {
			return $res[0]["total_rows"];
		} else {
			return count($res);
		}
	}


	/**
	 * Returns the query string to be used on the paginated links
	 *
	 * @access  private
	 * @return  string The query string
	 */
	function _buildQueryString()
	{
		$query_str = "";
		// gotta check manually here
		$params = $_GET;
		while (list($key, $value) = each($params)) {
			if ($key != "pagerRow") {
				$query_str .= "&" . $key . "=" . urlencode($value);
			}
		}
		return $query_str;
	}


	/**
	 * Returns an array with the paginated links, one in each item.
	 *
	 * @access  public
	 * @param   int $row Current page number (starts from zero)
	 * @param   int $total_rows Total number of rows, as returned by Pager::getTotalRows()
	 * @param   int $per_page Maximum number of rows per page
	 * @param   string $show_links An option to show 'Next'/'Previous' links, page numbering links or both ('sides', 'pages' or 'all')
	 * @param   string $show_blank An option to show 'Next'/'Previous' strings even if there are no appropriate next or previous pages
	 * @param   array $link_str The strings to be used instead of the default 'Next >>' and '<< Previous'
	 * @return  array The list of paginated links
	 * @see     getTotalRows()
	 */
	function getLinks($row, $total_rows, $per_page, $show_links = "all", $show_blank = "off", $link_str = -1)
	{
		// check for emptyness
		if ((empty($total_rows)) || (empty($per_page))) {
			return array();
		}
		if ($link_str == -1) {
			if (APP_CURRENT_LANG == "br") {
				$link_str = array(
                    "previous" => "&lt;&lt; Anterior",
                    "next"     => "Pr�xima &gt;&gt;"
                    );
			} else {
				$link_str = array(
                    "previous" => "&lt;&lt; Previous",
                    "next"     => "Next &gt;&gt;"
                    );
			}
		}
		$extra_vars = Pager::_buildQueryString();
		$file = $_SERVER["SCRIPT_NAME"];
		$number_of_pages = ceil($total_rows / $per_page);
		$subscript = 0;
		for ($current = 0; $current < $number_of_pages; $current++) {
			// if we need to show all links, or the 'side' links,
			// let's add the 'Previous' link as the first item of the array
			if ((($show_links == "all") || ($show_links == "sides")) && ($current == 0)) {
				if ($row != 0) {
					$array[0] = '<A HREF="' . $file . '?pagerRow=' . ($row - 1) . $extra_vars . '">' . $link_str["previous"] . '</A>';
				} elseif (($row == 0) && ($show_blank == "on")) {
					$array[0] = $link_str["previous"];
				}
			}

			// check to show page numbering links or not
			if (($show_links == "all") || ($show_links == "pages")) {
				if ($row == $current) {
					// if we only have one page worth of rows, we should show the '1' page number
					if (($current == ($number_of_pages - 1)) && ($number_of_pages == 1) && ($show_blank == "off")) {
						$array[0] = "<b>" . ($current > 0 ? ($current + 1) : 1) . "</b>";
					} else {
						$array[++$subscript] = "<b>" . ($current > 0 ? ($current + 1) : 1) . "</b>";
					}
				} else {
					$array[++$subscript] = '<A HREF="' . $file . '?pagerRow=' . $current . $extra_vars . '">' . ($current + 1) . '</A>';
				}
			}

			// only add the 'Next' link to the array if we are on the last iteration of this loop
			if ((($show_links == "all") || ($show_links == "sides")) && ($current == ($number_of_pages - 1))) {
				if ($row != ($number_of_pages - 1)) {
					$array[++$subscript] = '<A HREF="' . $file . '?pagerRow=' . ($row + 1) . $extra_vars . '">' . $link_str["next"] . '</A>';
				} elseif (($row == ($number_of_pages - 1)) && ($show_blank == "on")) {
					$array[++$subscript] = $link_str["next"];
				}
			}
		}
		return $array;
	}


	/**
	 * Returns a portion of an array of links, as returned by the Pager::getLinks()
	 * function. This is especially useful for preventing a huge list of links
	 * on the paginated list.
	 *
	 * @access  public
	 * @param   array $array The full list of paginated links
	 * @param   int $current The current page number
	 * @param   int $target_size The maximum number of paginated links
	 * @return  array The list of paginated links
	 * @see     getLinks()
	 */
	function getPortion($array, $current, $target_size = 20)
	{
		$size = count($array);
		if (($size <= 2) || ($size < $target_size)) {
			$temp = $array;
		} else {
			$temp = array();
			if (($current + $target_size) > $size) {
				$temp = array_slice($array, $size - $target_size);
			} else {
				$temp = array_slice($array, $current, $target_size);
				if ($size >= $target_size) {
					array_push($temp, $array[$size-1]);
				}
			}
			if ($current > 0) {
				array_unshift($temp, $array[0]);
			}
		}
		// extra check to make sure
		if (count($temp) == 0) {
			return "";
		} else {
			return $temp;
		}
	}

	/**
	 * Get the current page and rows from cookie and set them in the template as
	 * $page, $page_size.  Returns them for convenience.
	 */
	function doPaging($tpl, $prefix)
	{
		// check for a non-prefixed page size in the get params as this is how the generic javascript sets it
		// otherwise we look for the prefixed one in the cookie.
		$page_size = @$_GET['rows'];
		if (empty($page_size)) {
			$page_size = Pager::getParam($prefix.'page_size');
		}
		$page = Pager::getParam($prefix.'page');
		if (empty($page_size)) {
			$page_size = 10;
		}
		if (!is_numeric($page)) {
			$page = 0;
		}
		self::setParam($prefix.'page_size', $page_size);
		self::setParam($prefix.'page',$page);
		self::sendCookie();
		$tpl->assign(compact('page','page_size'));
		return array($page, $page_size);
	}

}
