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
 * Class to hold methods and algorythms that woudln't fit in other classes, such
 * as functions to work around PHP bugs or incompatibilities between separate 
 * PHP configurations.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");

class Misc
{


	/*
	 *  To use instead of php file_get_contents or fopen/fread as curl is much faster
     * @param string $url
     * @param bool $passthru - if true, don't return the retreived content, just echo it
	 */
	function processURL($url,$passthru=false) {
	   if (empty($url)) { return ""; }
	   $url=str_replace('&amp;','&',$url);
	   $ch=curl_init();
	   curl_setopt($ch, CURLOPT_URL, $url);
	   if (APP_HTTPS_CURL_CHECK_CERT == "OFF")  {
         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
         curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
       }
       if (!$passthru) {
           curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
       }
       $data = curl_exec ($ch);
       $info = curl_getinfo($ch); 
	   curl_close ($ch);
	   return array($data,$info);  
	}

    /**
      * Just get the http headers from an URL
      * Returns the header and the curl info array.
      */
	function processURL_info($url) 
    {
        if (empty($url)) { return ""; }
	   $url=str_replace('&amp;','&',$url);
	   $ch=curl_init();
	   curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt ($ch, CURLOPT_NOBODY, 1);
       curl_setopt ($ch, CURLOPT_HEADER, 1);
       curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	   if (APP_HTTPS_CURL_CHECK_CERT == "OFF")  {
         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
         curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
       }
       $data = curl_exec ($ch);
       $info = curl_getinfo($ch); 
	   curl_close ($ch);
	   return array($data,$info);  
    }

	/*
	* (mixed)remote_filesize($uri,$user='',$pw='')
	* returns the size of a remote stream in bytes or
	* the string 'unknown'. Also takes user and pw
	* incase the site requires authentication to access
	* the uri
	*/
	function remote_filesize($uri,$user='',$pw='')
	{
	   // start output buffering
	   ob_start();
	   // initialize curl with given uri
	   $ch = curl_init($uri);
	   // make sure we get the header
	   curl_setopt($ch, CURLOPT_HEADER, 1);
	   // make it a http HEAD request
	   curl_setopt($ch, CURLOPT_NOBODY, 1);
	   if (APP_HTTPS_CURL_CHECK_CERT == "OFF")  {
         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
         curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
       }
	   // if auth is needed, do it here
	   if (!empty($user) && !empty($pw))
	   {
		   $headers = array('Authorization: Basic ' .  base64_encode($user.':'.$pw)); 
		   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	   }
	   $okay = curl_exec($ch);
	   curl_close($ch);
	   // get the output buffer
	   $head = ob_get_contents();
	   // clean the output buffer and return to previous
	   // buffer settings
	   ob_end_clean();
	  
	   // gets you the numeric value from the Content-Length
	   // field in the http header
	   $regex = '/Content-Length:\s([0-9].+?)\s/';
	   $count = preg_match($regex, $head, $matches);
	  
	   // if there was a Content-Length field, its value
	   // will now be in $matches[1]
	   if (isset($matches[1]))
	   {
		   $size = $matches[1];
	   }
	   else
	   {
		   $size = 'unknown';
	   }
	  
	   return $size;
	}

    /**
     * Method used to merge two arrays preserving the array keys.
     *
     * @access  public
     * @param   array $arr1 The first array to merge
     * @param   array $arr2 The second array to merge
     * @return  array $ret The merged array with the keys intact
     */
	function array_merge_preserve($arr1,$arr2) {
		if(!is_array($arr1))
			   $arr1 = array();
		if(!is_array($arr2))
			   $arr2 = array();
		$keys1 = array_keys($arr1);
		$keys2 = array_keys($arr2);
		$keys  = array_merge($keys1,$keys2);
		$vals1 = array_values($arr1);
		$vals2 = array_values($arr2);
		$vals  = array_merge($vals1,$vals2);
		$ret    = array();
		foreach($keys as $key) {
			   list($unused,$val) = each($vals);
			   $ret[$key] = $val;
		}
		return $ret;
	}

	/*
	* (mixed)remote_filesize($uri,$user='',$pw='')
	* returns the file. Also takes user and pw
	* incase the site requires authentication to access
	* the uri
	*/
	function getFileURL($uri,$user='',$pw='')
	{
	   // start output buffering
	   ob_start();
	   // initialize curl with given uri
	   $ch = curl_init($uri);
	   if (APP_HTTPS_CURL_CHECK_CERT == "OFF")  {
         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
         curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
       }
	   // if auth is needed, do it here
	   if (!empty($user) && !empty($pw))
	   {
		   $headers = array('Authorization: Basic ' .  base64_encode($user.':'.$pw)); 
		   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	   }
	   $content = curl_exec($ch);
	   curl_close($ch);
	   // get the output buffer
//	   $content = ob_get_contents();
	   // clean the output buffer and return to previous
	   // buffer settings
	   ob_end_clean();
	  
	  
	   return $content;
	}

    /**
     * Method used to merge two arrays based on the values, not the keys.
     *
     * @access  public
     * @param   array $arr1 The first array to merge
     * @param   array $arr2 The second array to merge
     * @return  array $ret The merged array with new keys
     */
	function array_merge_values($arr1,$arr2) {
		$newarray = array();
		foreach($arr1 as $val) {
			if (!in_array($val, $newarray)) {
				array_push($newarray, $val);
			}
		}
		foreach($arr2 as $val) {
			if (!in_array($val, $newarray)) {
				array_push($newarray, $val);
			}
		}
		return $newarray;
	}

    /**
     * Method used to get the full contents of the given file.
     *
     * @access  public
     * @param   string $full_path The full path to the file
     * @return  string The full contents of the file
     */
    function getFileContents($full_path)
    {
        if (!@file_exists($full_path)) {
            return '';
        }
        $fp = @fopen($full_path, "rb");
        if (!$fp) {
            return '';
        }
        $contents = @fread($fp, filesize($full_path));
        @fclose($fp);
        return $contents;
    }

    /**
     * Method used to simulate array_map()'s functionality in a deeply nested
     * array. The PHP built-in function does not allow that.
     *
     * @access  public
     * @param   array $in_array The array to run the function against
     * @param   string $in_func The function to run
     * @param   array $in_args The array of arguments to pass to the function
     * @param   integer $in_index Internal parameter to specify which index of the array we are currently mapping
     * @return  array The mapped array
     */
    function array_map_deep(&$in_array, $in_func, $in_args = array(), $in_index = 1)
    {
       // fix people from messing up the index of the value
       if ($in_index < 1) {
           $in_index = 1;
       }
       foreach (array_keys($in_array) as $key) {
           // we need a reference, not a copy, normal foreach won't do
           $value =& $in_array[$key];
           // we need to copy args because we are doing 
           // manipulation on it farther down
           $args = $in_args;
           if (is_array($value)) {
               Misc::array_map_deep($value, $in_func, $in_args, $in_index);
           } else {
               array_splice($args, $in_index - 1, $in_index - 1, $value);
               $value = call_user_func_array($in_func, $args);
           }
       }
       return $in_array;
    }

    /**
     * Method used to turn a comma separated constant string into an array
     *
     * @access  public
     * @param   string $constant
     * @return  array $array 
     */
	function const_array($constant) {
	 $array = explode(",",$constant);
	 return $array;
	}

    /**
     * Method used to filter an array from unwanted data.
     *
     * @access  public
     * @param   array $input
     * @param   string $delete
     * @param   boolean $caseSensitive
     * @param   boolean $matchWholeWords	 
     * @return  array $return
     */
	function array_clean($input, $delete = false, $caseSensitive = false, $matchWholeWords = false) {
        $return = array();
		foreach ($input as $aryKey => $aryData) {
			if($delete)	{
				if($caseSensitive)	{
                    if ($matchWholeWords && $aryData != $delete) {
						$return[$aryKey] = $aryData;
                    } elseif(!strstr($aryData ,$delete)) {
						$return[$aryKey] = $aryData;
					}
				} else {
                    if ($matchWholeWords && strtolower($aryData) != strtolower($delete)) {
						$return[$aryKey] = $aryData;
                    } elseif(!stristr($aryData, $delete)) {
						$return[$aryKey] = $aryData;
					}
				}
			} else {
				if(!empty($aryData)) {
					$return[] = $input[$aryKey];
				}
			}
		}
		return $return;
	}

    /**
     * Method used to filter an array from unwanted data by array keys.
     *
     * @access  public
     * @param   array $input
     * @param   string $delete
     * @param   boolean $caseSensitive
     * @param   boolean $matchWholeWords	 
     * @return  array $return
     */
	function array_clean_key ($input, $delete = false, $caseSensitive = false, $matchWholeWords = false) {
        $return = array();
		foreach ($input as $aryKey => $aryData) {
			if($delete)	{
				if($caseSensitive)	{
                    if ($matchWholeWords && $aryKey != $delete) {
						$return[$aryKey] = $aryData;
                    } elseif(!strstr($aryKey ,$delete)) {
						$return[$aryKey] = $aryData;
					}
				} else {
                    if ($matchWholeWords && strtolower($aryKey) != strtolower($delete)) {
						$return[$aryKey] = $aryData;
                    } elseif(!stristr($aryKey, $delete)) {
						$return[$aryKey] = $aryData;
					}
				}
			} else {
				if(!empty($aryData)) {
					$return[] = $input[$aryKey];
				}
			}
		}
		return $return;
	}
	
    /**
     * Method used to remove search or browse results that the user should not see.
     *
     * @access  public
	 * @param   array $input
     * @return  array $return
     */
	function cleanListResults($input)
	{
        $return = array();
		foreach ($input as $aryKey => $aryData) {
			if ($input[$aryKey]['isLister'] == 1) {
				$return["'".$aryKey."'"] = $aryData;
			}
		}
		$return = array_values($return);		
		return $return;
	}

    /**
     * Method used to limit the returned search or browse results that display per page.
     *
     * @access  public
     * @return  array $return
     */
	function limitListResults($input, $start, $end)
	{
        $return = array();
		if (!is_array($input)) { return array(); }
		foreach ($input as $aryKey => $aryData) {
			if (($aryKey >= $start) && ($aryKey < $end)) {
				$return["'".$aryKey."'"] = $aryData;
			}
		}
		$return = array_values($return);
		return $return;
	}

    /**
     * Method used to remove certain datastreams from the list of datastreams the user will see.
     *
     * @access  public
	 * @param   array $dsList
     * @return  array $return
     */
	function cleanDatastreamList($dsList) {
		$original_dsList = $dsList;		
		$return = array();
		foreach ($dsList as $key => $ds) {		
			$pid = $key;
			$keep = true;
            if ((is_numeric(strpos($ds['ID'], "thumbnail_"))) || (is_numeric(strpos($ds['ID'], "web_"))) || (is_numeric(strpos($ds['ID'], "preview_"))) || (is_numeric(strpos($ds['ID'], "presmd_"))) || (is_numeric(strpos($ds['ID'], "FezACML_"))) )   {
                $keep = false;
            }
			// now try and find a thumbnail datastream of this datastream
			$thumbnail = "thumbnail_".substr($ds['ID'], 0, strrpos($ds['ID'], ".") + 1)."jpg";
			$ds['thumbnail'] = 0;
			foreach ($original_dsList as $o_key => $o_ds) {
				if ($thumbnail == $o_ds['ID']) {  // found the thumbnail datastream so save it against the record
					$ds['thumbnail'] = $thumbnail;
				}
			}
			// now try and find a web datastream of this datastream
			$web = "web_".substr($ds['ID'], 0, strrpos($ds['ID'], ".") + 1)."jpg";
			$ds['web'] = 0;
			foreach ($original_dsList as $o_key => $o_ds) {
				if ($web == $o_ds['ID']) {  // found the web datastream so save it against the record
					$ds['web'] = $web;
				}
			}
			// now try and find a preview datastream of this datastream
			$preview = "preview_".substr($ds['ID'], 0, strrpos($ds['ID'], ".") + 1)."jpg";
			$ds['preview'] = 0;
			foreach ($original_dsList as $o_key => $o_ds) {
				if ($preview == $o_ds['ID']) {  // found the preview datastream so save it against the record
					$ds['preview'] = substr($preview, 8);
				}
			}


			// now try and find a preservation metadata datastream of this datastream
			$presmd = "presmd_".substr($ds['ID'], 0, strrpos($ds['ID'], ".") + 1)."xml";
			$ds['presmd'] = 0;
			foreach ($original_dsList as $o_key => $o_ds) {
				if ($presmd == $o_ds['ID']) {  // found the presmd datastream so save it against the record
					$ds['presmd'] = $presmd;
				}
			}
			// now try and find a FezACML metadata datastream of this datastream
			$fezacml = "FezACML_".$ds['ID'].".xml";
			$ds['fezacml'] = 0;
			foreach ($original_dsList as $o_key => $o_ds) {
				if ($fezacml == $o_ds['ID']) {  // found the fezacml datastream so save it against the record
					$ds['fezacml'] = $fezacml;
					// now see if its allowed to show etc
					$record = new Record($pid);
					$FezACML_xdis_id = XSD_Display::getID('FezACML for Datastreams');
					$FezACML_display = new XSD_DisplayObject($FezACML_xdis_id);
					$FezACML_display->getXSDMF_Values($key);
/*					echo "PID - ".$pid;
					echo "XDIS ID - ".$FezACML_xdis_id;
					echo "HERe -> "; print_r($FezACML_display->matchfields); */
					if ($return[$key]['FezACML'][0]['!inherit_security'][0] == "on") {
						$parentsACMLs = $return[$key]['FezACML'];				
						$return[$key]['security'] = "include";
					} else {
						$return[$key]['security'] = "inherit";
						$parentsACMLs = array();
					} 
					Auth::getIndexParentACMLMemberList(&$parentsACMLs, $key, $row['isMemberOf']);
					$return[$key]['FezACML'] = $parentsACMLs;
	
				}
			}
            if (is_numeric(strpos(@$ds['MIMEType'],'image/'))) {
                $ds['canPreview'] = true;
            } else {
                $ds['canPreview'] = false;
            }
                
			if ($keep == true) {
				$return[$key] = $ds;
			}
		}
		$return = array_values($return);
		return $return;
	}

    /**
     * Method used to format a filesize in bytes to the appropriate string,
     * showing 'Kb' and 'Mb'.
     *
     * Developer Note: This will be used once the Fedora 2.0 managed content datastream filesize bug is fixed (probably in Fedora 2.1)
     *
     * @access  public
     * @param   integer $bytes The filesize to format
     * @return  string The formatted filesize
     */
    function formatFileSize($bytes)
    {
        $kb = 1024;
        $mb = 1024 * 1024;
        if ($bytes <= $kb) {
            return "$bytes bytes";
        } elseif (($bytes > $kb) && ($bytes <= $mb)) {
            $kbytes = $bytes / 1024;
            return sprintf("%.1f", round($kbytes, 1)) . " Kb";
        } else {
            $mbytes = ($bytes / 1024) / 1024;
            return sprintf("%.1f", round($mbytes, 1)) . " Mb";
        }
    }


    /**
     * Removes magic quotes
     *
     * @access  public
     * @param   array $var 
     * @return  array $var 
     */
    function dispelMagicQuotes(&$var)
    {
        static $magic_quotes;
        if (!isset($magic_quotes)) {
            $magic_quotes = get_magic_quotes_gpc();
        }
        if ($magic_quotes) {
            if (!is_array($var)) {
                $var = stripslashes($var);
            } else {
                array_walk($var, array('Misc', 'dispelMagicQuotes'));
            }
        }
        return $var;
    }


    /**
     * Method used to escape a string before using it in a query.
     *
     * @access  public
     * @param   string $str The original string
     * @return  string The escaped (or not) string
     */
    function escapeString($str)
    {
        return $GLOBALS["db_api"]->escapeString($str);
    }


    /**
     * Method used to prepare a set of fields and values for a boolean search
     *
     * Developer Note: Not used yet, but should be soon.
	 * 
     * @access  public
     * @param   string $field The field name
     * @param   string $value The value for that field
     * @return  string The prepared boolean search string
     */
    function prepareBooleanSearch($field, $value)
    {
        $boolean = array();
        $pieces = explode(" ", $value);
        for ($i = 0; $i < count($pieces); $i++) {
            $boolean[] = "$field LIKE '%" . Misc::escapeString($pieces[$i]) . "%'";
        }
        return "(" . implode(" OR ", $boolean) . ")";
    }

    /**
     * Method used to get the full list of files contained in a specific 
     * directory.
     *
     * @access  public
     * @param   string $directory The path to list the files from
     * @return  array The list of files
     */
    function getFileList($directory)
    {
        $files = array();
        $dir = @opendir($directory);
        while ($item = @readdir($dir)){
            if (($item == '.') || ($item == '..') || ($item == 'CVS') || ($item == 'SCCS')) {
                continue;
            }
            $files[] = $item;
        }
        return $files;
    }

    /**
     * Method used to format the given number of minutes in a string showing
     * the number of hours and minutes (02:30)
     *
     * @access  public
     * @param   integer $minutes The number of minutes to format
     * @param   boolean $omit_days If days should not be used, hours will just show up as greater then 24.
     * @return  string The formatted time
     */
    function getFormattedTime($minutes, $omit_days = false)
    {
        $hours = $minutes / 60;
        $mins = $minutes % 60;
        if ($hours > 24 && $omit_days == false) {
            $days = $hours / 24;
            $hours = $hours % 24;
            return sprintf("%02dd %02dh %02dm", $days, $hours, $mins);
        } else {
            return sprintf("%02dh %02dm", $hours, $mins);
        }
    }

    /**
     * Method used to get the mime content type in a better way than the default PHP way.
     *
     * @access  public
     * @param   string $f The file name and path
     * @return  string The formatted time
     */
	function mime_content_type($f) {
		if ((stristr(PHP_OS, 'win')) && (!stristr(PHP_OS, 'darwin'))) {
			return mime_content_type($f);
		} else {
			$f = escapeshellarg($f);
			return trim( `file -bi $f` );
		}
	}

    /**
     * Method used to parse the given string for references to URLs and create
     * real links out of those.
     *
     * @param   string $text The text to search against
     * @param   string $class The CSS class to use on the actual links
     * @return  string The parsed string
     */
    function activateLinks($text, $class = "link")
    {
        $text = preg_replace("'(\w+)://([\w\+\-\@\=\?\.\%\/\:\&\;]+)(\.)?'", "<a title=\"open \\1://\\2 in a new window\" class=\"$class\" href=\"\\1://\\2\" target=\"_\\2\">\\1://\\2</a>", $text);
        $text = preg_replace("'(\s+)www.([\w\+\-\@\=\?\.\%\/\:\&\;]+)(\.\s|\s)'", "\\1<a title=\"open http://www.\\2 in a new window\" class=\"$class\" href=\"http://www.\\2\" target=\"_\\2\">www.\\2</a>\\3" , $text);
        return $text;
    }


    /**
     * Method used to indent a given string.
     *
     * @access  public
     * @param   string $str The string to be indented
     * @return  string The indented string
     */
    function indent($str)
    {
        return "> " . $str;
    }

    /**
     * Uses CURL to get the content type of a url document (mime_content_type cannot do this, so CURL to the rescue).
     *
     * @access  public
     * @param   string $url The HTTP url
     * @param   string $follow_location 
     * @param   integer $timeout The http session timeout
     * @return  string The formatted time
     */
	function get_content_type($url,$follow_location = TRUE,$timeout = 5) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,$follow_location);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
 	    if (APP_HTTPS_CURL_CHECK_CERT == "OFF")  {
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}

		curl_exec($ch);
		if (($c = curl_getinfo($ch,CURLINFO_HTTP_CODE)) < 200 || $c >= 300) {
			curl_close($ch);
			return FALSE;
		}	
		$type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close($ch);
		return $type;
	}

    /**
     * Accepts an SQL array and returns a string of comma separated values from the array.
     *
     * @access  public
     * @param   array $array 
     * @return  string $return_str
     */
	function sql_array_to_string($array){
		$return_str = "";
		foreach($array as $key=>$val) {
			$return_str .= ",".$val[0];
		}
		$return_str = substr($return_str, 1);
		return $return_str;
	}

    /**
     * Gets an array with the datastream header details for each datastream from a FOXML xml string.
     *
     * @access  public
     * @param   array $datastreamTitles A list of the titles too get the headers for
     * @param   string $xmlString The FOXML xml object in a string
     * @param   array $existingDatastreams Optional Used to check for any "Link" hyperlink datastreams
     * @return  array $return
     */
	function getDatastreamXMLHeaders($datastreamTitles, $xmlString, $existingDatastreams = array()) {
		global $HTTP_POST_FILES;
		global $HTTP_POST_VARS;	
		$return = array();
		$next_link = Misc::getNextLink($existingDatastreams);
		$searchvars = array("ID", "CONTROL_GROUP", "STATE", "VERSIONABLE", "versionID", "LABEL", "MIMETYPE"); // For items which repeat, (like ID (ID and versionID)) make the searchable part uppercase and the name difference lowercase
		foreach ($datastreamTitles as $dsTitle) {
	//		$IDPos = stripos($xmlString, 'id="'.$dsTitle['xsdsel_title'].'"'); // stripos is a php5 function
			$IDPos = stripos($xmlString, 'id="'.$dsTitle['xsdsel_title'].''); // stripos is a php5 function
			if (is_numeric($IDPos)) {
				$XMLContentStartPos = $IDPos;
				if (is_numeric(strpos($xmlString, '<foxml:xmlContent>', $IDPos))) {
					$XMLContentEndPos = strpos($xmlString, '<foxml:xmlContent>', $XMLContentStartPos);
				} elseif (is_numeric(strpos($xmlString, '<foxml:binaryContent>', $IDPos))) {
					$XMLContentEndPos = strpos($xmlString, '<foxml:binaryContent>', $XMLContentStartPos);
				} elseif (is_numeric(strpos($xmlString, '<foxml:contentLocation>', $IDPos))) {
					$XMLContentEndPos = strpos($xmlString, '<foxml:contentLocation>', $XMLContentStartPos);
				}	
				if (is_numeric($XMLContentStartPos) && is_numeric($XMLContentEndPos)) {
					$tempXML = substr($xmlString, $XMLContentStartPos, ($XMLContentEndPos-$XMLContentStartPos));
					$tempStartPos = 0;
					$newStartPos = false;
					foreach ($searchvars as $sv) {
						$tempStartString = preg_replace("/[a-z]/", "", $sv.'="');
						if (is_numeric($tempStartPos) && ($tempStartPos != 0)) {
							$newStartPos = $tempStartPos;
						}	
						$tempStartPos = stripos($tempXML, $tempStartString, $newStartPos); 
						if (is_numeric($tempStartPos)) {
							$tempStartPos += strlen($tempStartString);
							$tempEndPos = strpos($tempXML, '"', $tempStartPos);
							$return[$dsTitle['xsdsel_title']][$sv] = substr($tempXML, $tempStartPos, ($tempEndPos - $tempStartPos));
						}
					}
					// Now for file uploads get the Datastream ID and Label (and maybe the MIMEType later?) from the actual file
					$file_res = array();
					$label_res = array();				
					$file_res = XSD_Loop_Subelement::getXSDMFInputType($dsTitle['xsdsel_id'], 'file_input');
					$label_res = XSD_Loop_Subelement::getXSDMFInputType($dsTitle['xsdsel_id'], 'text', true); // true for exclude file and link, only want the file and link labels
					if (count($file_res) == 1) {
						if (is_array($HTTP_POST_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']])) {
							foreach ($HTTP_POST_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']] as $key => $data) {
								if (trim($HTTP_POST_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']][$key]) != "") {
									$return[$dsTitle['xsdsel_title'].$key]['CONTROL_GROUP'] = $return[$dsTitle['xsdsel_title']]['CONTROL_GROUP'];
									$return[$dsTitle['xsdsel_title'].$key]['STATE'] = $return[$dsTitle['xsdsel_title']]['STATE'];
									$return[$dsTitle['xsdsel_title'].$key]['VERSIONABLE'] = $return[$dsTitle['xsdsel_title']]['VERSIONABLE'];
									$return[$dsTitle['xsdsel_title'].$key]['ID'] = str_replace(" ", "_", $HTTP_POST_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']][$key]);
									if (is_numeric(strpos($return[$dsTitle['xsdsel_title'].$key]['ID'], "."))) {
										$filename_ext = strtolower(substr($return[$dsTitle['xsdsel_title'].$key]['ID'], (strrpos($return[$dsTitle['xsdsel_title'].$key]['ID'], ".") + 1)));
										$return[$dsTitle['xsdsel_title'].$key]['ID'] = substr($return[$dsTitle['xsdsel_title'].$key]['ID'], 0, strrpos($return[$dsTitle['xsdsel_title'].$key]['ID'], ".") + 1).$filename_ext;
									}
									$return[$dsTitle['xsdsel_title'].$key]['versionID'] = $return[$dsTitle['xsdsel_title'].$key]['ID'].".0";																
									if (trim($HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdmf_id']][$key]) != "") {
										$return[$dsTitle['xsdsel_title'].$key]['LABEL'] = $HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdmf_id']][$key];
									} else {
										$return[$dsTitle['xsdsel_title'].$key]['LABEL'] = $HTTP_POST_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']][$key];
									}
									$return[$dsTitle['xsdsel_title'].$key]['MIMETYPE'] = $HTTP_POST_FILES['xsd_display_fields']['type'][$file_res[0]['xsdmf_id']][$key];
								}
							}							
						} else { // file input is not a array, so only just one file
							$return[$dsTitle['xsdsel_title']]['CONTROL_GROUP'] = $return[$dsTitle['xsdsel_title']]['CONTROL_GROUP'];
							$return[$dsTitle['xsdsel_title']]['STATE'] = $return[$dsTitle['xsdsel_title']]['STATE'];
							$return[$dsTitle['xsdsel_title']]['VERSIONABLE'] = $return[$dsTitle['xsdsel_title']]['VERSIONABLE'];
							$return[$dsTitle['xsdsel_title']]['ID'] = str_replace(" ", "_", $HTTP_POST_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']]);
							if (is_numeric(strpos($return[$dsTitle['xsdsel_title']]['ID'], "."))) {
								$filename_ext = strtolower(substr($return[$dsTitle['xsdsel_title']]['ID'], (strrpos($return[$dsTitle['xsdsel_title']]['ID'], ".") + 1)));
								$return[$dsTitle['xsdsel_title']]['ID'] = substr($return[$dsTitle['xsdsel_title']]['ID'], 0, strrpos($return[$dsTitle['xsdsel_title']]['ID'], ".") + 1).$filename_ext;
							}
							$return[$dsTitle['xsdsel_title']]['versionID'] = $return[$dsTitle['xsdsel_title']]['ID'].".0";																							
							if ($HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdmf_id']] != "") {
								$return[$dsTitle['xsdsel_title'].$key]['LABEL'] = $HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdmf_id']];
							} else {
								$return[$dsTitle['xsdsel_title']]['LABEL'] = $HTTP_POST_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']];
							}
							$return[$dsTitle['xsdsel_title']]['MIMETYPE'] = $HTTP_POST_FILES['xsd_display_fields']['type'][$file_res[0]['xsdmf_id']];
						}
					} elseif (count($label_res) == 1 && ($dsTitle['xsdsel_title'] == "Link")) { // no file inputs are involved so might be a link
//					} elseif (($dsTitle['xsdsel_title'] == "Link")) { // no file inputs are involved so might be a link
						if (is_array($HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdmf_id']])) {
							foreach ($HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdmf_id']] as $key => $data) {
//								if ($HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdmf_id']][$key] != "") { // was just checking the desc existed
								// fixed it so that it requires the url, not the description
								if ($HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdsel_attribute_loop_xsdmf_id']][$key] != "") {											
									$return[$dsTitle['xsdsel_title'].$key]['CONTROL_GROUP'] = $return[$dsTitle['xsdsel_title']]['CONTROL_GROUP'];
									$return[$dsTitle['xsdsel_title'].$key]['STATE'] = $return[$dsTitle['xsdsel_title']]['STATE'];
									$return[$dsTitle['xsdsel_title'].$key]['VERSIONABLE'] = $return[$dsTitle['xsdsel_title']]['VERSIONABLE'];
									$return[$dsTitle['xsdsel_title'].$key]['ID'] = "link_".$next_link;
									$next_link++;
									$return[$dsTitle['xsdsel_title'].$key]['versionID'] = $return[$dsTitle['xsdsel_title'].$key]['ID'].".0";																
									if ($HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdmf_id']][$key] != "") { // NOW check the desc/label
										$return[$dsTitle['xsdsel_title'].$key]['LABEL'] = $HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdmf_id']][$key];
									} else { // if it wasnt saved then just use the default
										$return[$dsTitle['xsdsel_title'].$key]['LABEL'] = "Link";
//										$return[$dsTitle['xsdsel_title'].$key]['LABEL'] = $return[$dsTitle['xsdsel_title']]['LABEL'];
									}
									$return[$dsTitle['xsdsel_title'].$key]['MIMETYPE'] = $return[$dsTitle['xsdsel_title']]['MIMETYPE'];
								}
							}							
						} else {
							$return[$dsTitle['xsdsel_title']]['CONTROL_GROUP'] = $return[$dsTitle['xsdsel_title']]['CONTROL_GROUP'];
							$return[$dsTitle['xsdsel_title']]['STATE'] = $return[$dsTitle['xsdsel_title']]['STATE'];
							$return[$dsTitle['xsdsel_title']]['VERSIONABLE'] = $return[$dsTitle['xsdsel_title']]['VERSIONABLE'];
							$return[$dsTitle['xsdsel_title']]['ID'] = "link_".$next_link;
							$next_link++;
							$return[$dsTitle['xsdsel_title']]['versionID'] = $return[$dsTitle['xsdsel_title']]['ID'].".0";																
							if (@$HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdmf_id']] != "") {
								$return[$dsTitle['xsdsel_title']]['LABEL'] = $HTTP_POST_VARS['xsd_display_fields'][$label_res[0]['xsdmf_id']][$key];
							} else {
								$return[$dsTitle['xsdsel_title']]['LABEL'] = "Link";
//								$return[$dsTitle['xsdsel_title']]['LABEL'] = $return[$dsTitle['xsdsel_title']]['LABEL'];
							}
							$return[$dsTitle['xsdsel_title']]['MIMETYPE'] = $return[$dsTitle['xsdsel_title']]['MIMETYPE'];
						}
	
					}
	
				}
			}
		}
		return $return;
	}

    /**
     * Gets the next link number available for this object
     *
     * @access  public
     * @param   array $existingDatastreams 
     * @return  integer $max_link The next link available
     */
	function getNextLink($existingDatastreams) {
		$max_link = 0;
		$new_max_link = 0;	
	
		foreach ($existingDatastreams as $eds) {
			$link_pos = strpos($eds['ID'], "link_");
			if (is_numeric($link_pos)) { // if found a link datatream
				$new_max_link = substr($eds['ID'], ($link_pos+5)); // get its number
				if (is_numeric($new_max_link)) {
					if ($new_max_link > $max_link) {
						$max_link = $new_max_link;
					}
				}
			}
		}
		$max_link++;
		return $max_link;
	}


    /**
     * Retrieves the inline xml content datastream of the given titles in the xml string.
     * Used to pull out the datastream from a created XML object after an update html post.
	 * 
     * @access  public
     * @param   array $datastreamTitles
     * @param   string $xmlString The FOXML object xml string
     * @return  array $return
     */
	function getDatastreamXMLContent($datastreamTitles, $xmlString) {
		$return = array();
//		echo $xmlString;
//		print_r($datastreamTitles);
		foreach ($datastreamTitles as $title => $data) {
			$IDPos = stripos($xmlString, 'id="'.$title.'"'); // stripos is a php5 function
			if (is_numeric($IDPos)) {
				$searchScopeEnd = strpos($xmlString, "</foxml:datastream>", $IDPos);
				$searchXMLString = substr($xmlString, $IDPos, ($searchScopeEnd - $IDPos));
				if (is_numeric(strpos($searchXMLString, '<foxml:xmlContent>'))) {
					$XMLContentStartPos = strpos($searchXMLString, '<foxml:xmlContent>') + 18;
					$XMLContentEndPos = strpos($searchXMLString, '</foxml:xmlContent>', $XMLContentStartPos);
				} elseif (is_numeric(strpos($searchXMLString, '<foxml:binaryContent>'))) {
					$XMLContentStartPos = strpos($searchXMLString, '<foxml:binaryContent>') + 22;
					$XMLContentEndPos = strpos($searchXMLString, '</foxml:binaryContent>', $XMLContentStartPos);
				} elseif (is_numeric(strpos($searchXMLString, '<foxml:contentLocation>'))) {
					$XMLContentStartPos = strpos($searchXMLString, '<foxml:contentLocation>') + 23;
					$XMLContentEndPos = strpos($searchXMLString, '</foxml:contentLocation>', $XMLContentStartPos);
				}
				if (is_numeric($XMLContentStartPos) && is_numeric($XMLContentEndPos)) {
					$tempXML = substr($searchXMLString, $XMLContentStartPos, ($XMLContentEndPos-$XMLContentStartPos));
					$return[$title] = $tempXML;
				}
			}
		}
		return $return;
	}

    /**
     * Removes the Non-XML content datastreams of the given titles in the xml string. These will be added after initial ingest of basic object.
	 * 
     * @access  public
     * @param   array $datastreamTitles
     * @param   string $xmlString The FOXML object xml string
     * @return  array $return
     */
	function removeNonXMLDatastreams($datastreamTitles, $xmlString) {
	
		$return = $xmlString;
//		print_r($datastreamTitles);
		foreach ($datastreamTitles as $title => $data) {
			$IDPos = stripos($xmlString, 'id="'.$title.'"'); // stripos is a php5 function
			$binaryPos = false;
			if (is_numeric($IDPos)) {
				// Find first close datastream tag after the ID tag
				$searchScopeEnd = strpos($xmlString, "</foxml:datastream>", $IDPos);
				// Get the cut down datastream XML to search for binary content
				$searchXMLString = substr($xmlString, 0, ($searchScopeEnd));
				$binaryPos = strpos($searchXMLString, '<foxml:binaryContent>', $IDPos); // get the first opening binaryContent tag position in the xml after ds title, but before a /datastream (close tag)
//				$binaryPos = strrpos($searchXMLString, '<foxml:binaryContent>', $IDPos); // get the last opening binaryContent tag position in the xml after ds title, but before a /datastream (close tag)
				if (!is_numeric($binaryPos)) { // check for contentLocation as well as this is now being added after ingest
					$binaryPos = strpos($searchXMLString, '<foxml:contentLocation>', $IDPos); // get the first opening contentLocation tag position in the xml after ds title, but before a /datastream (close tag)
//					$binaryPos = strrpos($searchXMLString, '<foxml:contentLocation>', $IDPos); // get the last opening contentLocation tag position in the xml after ds title, but before a /datastream (close tag)
				}
				if (is_numeric($binaryPos)) { // if you find binaryContent after this tag
					$XMLContentStartPos = strrpos(substr($xmlString, 0, $binaryPos), '<foxml:datastream '); // the space is essential or it will pick '<foxml:datastreamVersion
//					$XMLContentEndPos = strrpos($xmlString, '</foxml:datastream>', $XMLContentStartPos) + 19; // get the last one
					$XMLContentEndPos = strpos($xmlString, '</foxml:datastream>', $XMLContentStartPos) + 19;
					if (is_numeric($XMLContentStartPos) && is_numeric($XMLContentEndPos)) {
                        $tempXML = substr($xmlString, $XMLContentStartPos, ($XMLContentEndPos-$XMLContentStartPos));
						$return = str_replace($tempXML, "", $return); // if a binary datastream is found then remove it from the ingest object
					}
				}
			}
			$xmlString = $return;
		}
		return $return;		
	}

    /**
     * Creates a simple array out of an XML object with the xml heirarchy expressed with ! separators
	 * This is mainly used by batch import now to handle entire FOXML xml objects, as XSD_Display::processXSDMF handles
	 * datastream XML to array conversions.
	 * 
	 * Developer Note: This is a recursive function that traverses through the XML elements and attributes using a DOM Document (PHP5).	 
	 * 
     * @access  public
     * @param   DomNode $domnode
     * @param   array $array The main returned array by reference
     * @param   string $top_element_name The top element in the XML hierarchy to search for first
     * @param   string $element_prefix eg OAI_DC:, FOXML: etc
     * @param   array $xsdmf_array	The XSD matching field array passed by reference
     * @param   integer $xdis_id The XSD Display ID of the object, or current child XSD Display ID of the XSD reference in a recursive traversal
     * @param   string $parentContent The front hierarchy of the array element passed by a parent XML element
     * @param   string $parentKey If the parent is not unique it may need to pass its unique key to its child elements traversals so the correct XSDMF_ID is found
     * @return  void (Uses $array and $xsdmf_array passed as reference recursively)
     */
	function dom_xml_to_simple_array($domnode, &$array, $top_element_name, $element_prefix, &$xsdmf_array, $xdis_id, $parentContent="", $parent_key="") {
		$array_ptr = &$array;
		$xsdmf_ptr = &$xsdmf_array;
		$domnode = $domnode->firstChild;
		$while_count = 0;
		$xsdmf_details = "";
		// Get XDIS and all SUBXDIS
		$xdis_list = XSD_Relationship::getListByXDIS($xdis_id);
		array_push($xdis_list, array("0" => $xdis_id));
		$xdis_str = Misc::sql_array_to_string($xdis_list);
		while (!is_null($domnode)) {
			$clean_nodeName = Misc::strip_element_name($domnode->nodeName, $top_element_name, $element_prefix);
			if ($clean_nodeName == '#text') {
				$array_ptr = &$array[$clean_nodeName];
				$array_ptr = $domnode->nodeValue;
			} elseif ((strtolower($domnode->nodeName) != "xsd:annotation") && (strtolower($domnode->nodeName) != "xsd:documentation")) { // all other conditions (except desc's)
				$xsdmf_id = false;
				if ($domnode->hasAttributes() ) {
					$attributes = $domnode->attributes; 
					foreach ($attributes as $index => $domobj) {
						 if (is_numeric(strpos($domobj->nodeName, ":"))) {
							$new_element = "!".$parentContent."!".$clean_nodeName."!".substr($domobj->nodeName, strpos($domobj->nodeName, ":") +1);
						 } else {
							$new_element = "!".$parentContent."!".$clean_nodeName."!".$domobj->nodeName;
						 }
						if ($parent_key != "") { // if there are passed parent keys then use them in the search
							$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByKeyXDIS_ID($new_element, $domobj->nodeValue, $xdis_str); // try to match on a sel key
						} else {
							$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByKeyXDIS_ID($new_element, $domobj->nodeValue, $xdis_str); // try to match on a sel key
						}
						if (is_numeric($xsdmf_id)) {
							$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
							if (strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
								$ptr_value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $domobj->nodeValue);
							} else {
								$ptr_value = $domobj->nodeValue;
							}
							$xsdmf_ptr[$xsdmf_id] = $ptr_value;
						} else {
							$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByXDIS_ID($new_element, $xdis_str);
							if (is_numeric($xsdmf_id)) {
								$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
								if (strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
									$ptr_value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $domobj->nodeValue);
								} else {
									$ptr_value = $domobj->nodeValue;
								}
								if ((array_key_exists($xsdmf_id, $xsdmf_ptr)) && (!array_key_exists(0, $xsdmf_ptr[$xsdmf_id]))) {
									$tmp_value = $xsdmf_ptr[$xsdmf_id];
									$xsdmf_ptr[$xsdmf_id] = array();
									$xsdmf_ptr[$xsdmf_id][0] = $tmp_value;
									$next_array_key = 0;
									$next_array_key = Misc::getNextArrayKey($xsdmf_ptr[$xsdmf_id]);
									$xsdmf_ptr[$xsdmf_id][$next_array_key] = $ptr_value;
								} elseif (isset($xsdmf_ptr[$xsdmf_id]) && array_key_exists(0, $xsdmf_ptr[$xsdmf_id])) {
									$next_array_key = 0;
									$next_array_key = Misc::getNextArrayKey($xsdmf_ptr[$xsdmf_id]);
									$xsdmf_ptr[$xsdmf_id][$next_array_key] = $ptr_value;
								} else {
									$xsdmf_ptr[$xsdmf_id] = $ptr_value;
								}
							}
						} 
						if (!empty($xsdmf_details) && $xsdmf_details['xsdmf_parent_key_match'] != "") {
							$array_ptr = &$array["!".$xsdmf_details['xsdmf_parent_key_match']."!".$clean_nodeName];
						} else {
							$array_ptr = &$array[$clean_nodeName];
						}
						$array_ptr[$while_count][$new_element] = $domobj->nodeValue;
					} // end foreach
				} // replaced the else statement below because even if it has attributes we want it to check the basic element especially for xsd loop sublelement elements
				// If we still havent got the xsdmf_id then it either doesnt have one or the element doesnt have attributes, so try to find it without the attributes
				if ((is_numeric(strpos(substr($parentContent, 0, 1), "!"))) || ($parentContent == "")) {
					// @@@ CK - 25/8/2005 - unless it is the below with the parent content, FezACML doesnt show properley because it needs the !role!rule etc
					// so any changes to this need to be tested with the FezACML etc
					$new_element = $parentContent."!".$clean_nodeName; // @@@ CK 31/5/2005 - Added ! to the front of the string if not there already
				} else {			
					// @@@ CK - 25/8/2005 - unless it is the below with the parent content, FezACML doesnt show properley because it needs the !role!rule etc
					// so any changes to this need to be tested with the FezACML etc
					$new_element = "!".$parentContent."!".$clean_nodeName; // @@@ CK 31/5/2005 - Added ! to the front of the string
				}
				if (!is_numeric($xsdmf_id)) {
					if ($parent_key != "") { // if there are passed parent keys then use them in the search
						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByParentKeyXDIS_ID($new_element, $parent_key, $xdis_str);		
					} else {
						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByXDIS_ID($new_element, $xdis_str);
					}
					if (is_numeric($xsdmf_id)) {
						$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
						if (strlen($xsdmf_details['xsdmf_value_prefix']) > 0) {
							$ptr_value = str_replace($xsdmf_details['xsdmf_value_prefix'], "", $domnode->nodeValue);
						} else {
							$ptr_value = $domnode->nodeValue;
						}
						if (!empty($xsdmf_ptr) && (array_key_exists($xsdmf_id, $xsdmf_ptr)) 
							   && (!is_array($xsdmf_ptr[$xsdmf_id]) || !array_key_exists(0, $xsdmf_ptr[$xsdmf_id]))) {
							$tmp_value = $xsdmf_ptr[$xsdmf_id];
							$xsdmf_ptr[$xsdmf_id] = array();
							$xsdmf_ptr[$xsdmf_id][0] = $tmp_value;
							$next_array_key = 0;
							$next_array_key = Misc::getNextArrayKey($xsdmf_ptr[$xsdmf_id]);
							$xsdmf_ptr[$xsdmf_id][$next_array_key] = $ptr_value;
						} elseif (!empty($xsdmf_ptr[$xsdmf_id]) && array_key_exists(0, $xsdmf_ptr[$xsdmf_id])) {
							$next_array_key = 0;
							$next_array_key = Misc::getNextArrayKey($xsdmf_ptr[$xsdmf_id]);
							$xsdmf_ptr[$xsdmf_id][$next_array_key] = $ptr_value;
						} else {
							$xsdmf_ptr[$xsdmf_id] = $ptr_value;
						}
					}
				}
				if (!empty($xsdmf_details['xsdmf_parent_key_match'])) {
						$array_ptr = &$array["!".$xsdmf_details['xsdmf_parent_key_match']."!".$clean_nodeName];
				} else {
					$array_ptr = &$array[$clean_nodeName];
					$array_ptr[$while_count][$new_element] = $domnode->nodeValue;
				}
				$array_ptr[$while_count][$new_element] = $domnode->nodeValue;
				// IF is a SEL then go recursive
				// @@@ CK - 31/5/2005 - Added to handle the subelement loops
				if (!empty($xsdmf_details)) {
					if (($xsdmf_details['xsdmf_is_key'] == 1) && ($xsdmf_details['xsdmf_key_match'] != '')) {
					   $parent_key = $xsdmf_details['xsdmf_key_match'];
					}
					if ($xsdmf_details['xsdmf_html_input'] == 'xsd_loop_subelement') {
						$xsd_sel_ids = "";
						$xsd_sel_ids = XSD_Loop_Subelement::getSELIDsByXSDMF($xsdmf_id);
					} 
				}
				$while_count++;
			} // End of if #text or other non desc      
			// Now see if it has any chidren nodes and go recursive into those
			if ( $domnode->hasChildNodes() ) {
				// if the current field is a loop sublelement then get its child sel_ids and pass them down in a for loop
				// FOR very first element we don't want to carry that parentContent down to the children, for the rest we do
				if ((strpos($domnode->nodeName, $element_prefix.":".$top_element_name) === 0) || (strpos($domnode->nodeName, $top_element_name) === 0)) {
					$newParentContent = "";
					Misc::dom_xml_to_simple_array($domnode, $array_ptr, $top_element_name, $element_prefix, $xsdmf_ptr, $xdis_id, $newParentContent, $parent_key);
				} else {
					if ($parentContent != "") {
						$newParentContent = Misc::strip_element_name($parentContent."!".$domnode->nodeName, $top_element_name, $element_prefix, $parent_key);
					} else {
						$newParentContent = Misc::strip_element_name($domnode->nodeName, $top_element_name, $element_prefix, $parent_key);
					}
					Misc::dom_xml_to_simple_array($domnode, $array_ptr, $top_element_name, $element_prefix, $xsdmf_ptr, $xdis_id, $newParentContent, $parent_key);
				}
			}
			$domnode = $domnode->nextSibling;
		} // End of while loop
	}

	/**
	  * XML_Walk
	  * A little bit like a sax parser (xml_parse) only using an object and method for all of the events.
	  * It is more flexible than an even parser as the domNode object is available to the callback.
	  * @param array $callbackdata Used to store data that will be available to sub nodes but not to siblings.  
	  * The callback function should return changes to this data for use by child node callbacks.
	  */
	function XML_Walk($domnode, $callbackobject, $callbackmethod, $callbackdata, $rootnode) {
		if (is_null($domnode)) {
			return;
		}


		$newcallbackdata = $callbackobject->$callbackmethod($domnode, $callbackdata, 'startopen', $rootnode);

		// process attributes
		if ($domnode->hasAttributes() ) {
			$attributes = $domnode->attributes; 
			foreach ($attributes as $index => $domobj) {
				$newcallbackdata = $callbackobject->$callbackmethod($domobj, $newcallbackdata, NULL, $rootnode);
			}
		}
		$newcallbackdata = $callbackobject->$callbackmethod($domnode, $newcallbackdata, 'endopen', $rootnode);
//		$newcallbackdata = $callbackobject->$callbackmethod($domnode, $newcallbackdata, 'endopen', $rootnode);
		// recurse children
		Misc::XML_Walk($domnode->firstChild, $callbackobject, $callbackmethod, $newcallbackdata, $rootnode);
		// recurse siblings
		$callbackobject->$callbackmethod($domnode, $newcallbackdata, 'close', $rootnode);
		Misc::XML_Walk($domnode->nextSibling, $callbackobject, $callbackmethod, $callbackdata, $rootnode);
	}

    /**
     * Removes any element prefixes (eg DC: etc) from an element name
	 * 
     * @access  public
     * @param   string $element_name 
     * @return  array $element_name
     */
	function strip_element_name($element_name) {
		 if ( is_numeric(strpos($element_name, "dc:")) ) {
			  return $element_name;
		 } else {
			 $element_name_start = strpos($element_name, ":");
			 if (is_numeric($element_name_start ) ) {
				return substr($element_name, $element_name_start+1);
			} else {
				return $element_name;
			}
		 }
	}

    /**
      * makes an array into a string ready to be used in a MySQL query list
      * @param $array The array to be put in the query
      * @return string escaped mysql query element ready to be used like this WHERE x IN ($string)
      */
    function array_to_sql_string($array) {
        foreach ($array as &$item) {
            $item = "'".mysql_escape_string($item)."'";
        }
        return implode(', ', $array);
    }

    /**
     * Gets the next numeric incremental key in an array
	 * 
     * @access  public
     * @param   array $array 
     * @return  integer $next_key
     */
	function getNextArrayKey($array, $next_key=0)
	 {
	   if (array_key_exists($next_key, $array)) 
		 $return = Misc::getNextArrayKey($array, $next_key + 1);
	   else
		 $return = $next_key;
	   return $return;
	 }

    /**
     * Creates a simple array from a dom node.
	 * 
	 * Developer Note: This has been replaced by XML Walk, but will be left in for now.	 
	 * 
     * @access  public
     * @param   DomNode $domnode 
     * @param   array $array passed by reference
     * @param   string $parentContent 
     * @return  void 
     */
	function dom_xsd_to_simple_array($domnode, &$array, $parentContent="") {
	  $array_ptr = &$array;
	  $domnode = $domnode->firstChild;	
	  while (!is_null($domnode)) {	
		if ((strtolower($domnode->nodeName) != "xsd:annotation") && (strtolower($domnode->nodeName) != "xsd:documentation")) {
			 if ($domnode->hasAttributes() ) {
			   $attributes = $domnode->attributes; 
			   $tmp = "";
			   foreach ($attributes as $index => $domobj) {
					$tmp .= " ".$domobj->nodeName."=".$domobj->nodeValue;
			   }
			   $array_ptr = &$array[$domnode->nodeName.$tmp];
				if (!( $domnode->hasChildNodes() )) {
				   $array_ptr['fez_hyperlink'] = $parentContent."!".$domnode->nodeName.str_replace(" ", "^", $tmp);
				}
			 } else {
			   $array_ptr = &$array[$domnode->nodeName];
			 }
		}       
		 if ( $domnode->hasChildNodes() ) {
		   if (strpos($domnode->nodeName, "schema") === 0)  {
			   Misc::dom_xsd_to_simple_array($domnode, $array_ptr, $parentContent."!".$domnode->nodeName."^".$domobj->nodeValue);
		   } else {
			   Misc::dom_xsd_to_simple_array($domnode, $array_ptr, $parentContent."!".$domnode->nodeName);
		   }
		 }
	   $domnode = $domnode->nextSibling;
	  }
	}

    /**
     * Searches through a DomNode for an element with a given element name value.
	 * 
	 * Developer Note: This has been replaced by XML Walk, but will be left in for now.	 
	 * 
     * @access  public
     * @param   DomNode $domnode 
     * @param   string $element_name
     * @return  DomNode $item or false if not found
     */
	function getElementByNameValue($domnode, $elementname) {
		foreach($domnode->getElementsByTagname("element") as $item ) {
			foreach( $item->attributes as $attrib ){
				if (($attrib->nodeName == "name") && ($attrib->nodeValue == $elementname)) {
					return $item;
					break;
				}
			}
		}
		return false;
	}

    /**
     * Searches through a DomNode for an element with a given element name value and type.
	 * 
     * @access  public
     * @param   DomNode $domnode 
     * @param   string $type
     * @param   string $element_name
     * @return  DomNode $item or false if not found
     */
	function getXMLObjectByTypeNameValue($domnode, $type, $elementname) {
		$result = $domnode->getElementsByTagname($type);
		foreach($result as $item ) {
			foreach( $item->attributes as $attrib ){
				if (($attrib->nodeName == "name") && ($attrib->nodeValue == $elementname)) {
					return $item;
					break;
				}
			}
		}
		if ($type == "complexType") { // check for simpleTypes just in case
			$result = $domnode->getElementsByTagname("simpleType");
			foreach($result as $item ) {
				foreach( $item->attributes as $attrib ){
					if (($attrib->nodeName == "name") && ($attrib->nodeValue == $elementname)) {
						return $item;
						break;
					}
				}
			}
		}
		return false;
	}

    /**
     * Returns an XSD schema string.
	 * 
     * @access  public
     * @param   DomNode $domnode 
     * @param   string $top_element_name
     * @param   string $element_prefix
     * @param   array $xsd_extra_ns_prefixes
     * @return  DomNode $item or false if not found
     */
	function getSchemaAttributes($domnode, $top_element_name="", $element_prefix="", $xsd_extra_ns_prefixes=array()) {
		$res = "";
		$nsURI = '';
		
		$currentnode = new DomDocument;
		$result = $domnode->getElementsByTagname('schema');
		if (count($result) == 1) {
			foreach($result as $item ) {
				$currentnode = $item;
			}
			if ($currentnode !== false) {
				foreach ($xsd_extra_ns_prefixes as $extra_prefix) {
					if ($currentnode->lookupNamespaceURI($extra_prefix) != false) {
						$nsURI .= ' xmlns:'.$extra_prefix.'="'.$currentnode->lookupNamespaceURI($extra_prefix).'"';
					}
				}
	
				if ($currentnode->lookupNamespaceURI($top_element_name) != false) {
					$nsURI .= ' xmlns:'.$top_element_name.'="'.$currentnode->lookupNamespaceURI($top_element_name).'"';
				}
				// CK - 7/5/2005 - This is probably redundant but will leave here commented out, if it becomes necessary
	/*			if ($currentnode->lookupNamespaceURI($element_prefix) != false) {
					$nsURI .= ' xmlns:'.$element_prefix.'="'.$currentnode->lookupNamespaceURI($element_prefix).'"';
				} */
	
				if ($currentnode->hasAttributes() ) {
					$attributes = $currentnode->attributes; 
					foreach ($attributes as $index => $attrib) {
						if ($attrib->nodeName == 'targetNamespace') {
							if ($element_prefix != "") {
								$res .= 'xmlns:'.str_replace(":", "", $element_prefix).'="'.$attrib->nodeValue.'"';
							} else {
								$res .= 'xmlns="'.$attrib->nodeValue.'"';
							}
						} 
					}
				}
			}
			if ($currentnode->namespaceURI != "") {
				$nsURI .= ' xmlns:xsi="'.$currentnode->namespaceURI.'"';
			} else {
				$nsURI .= "";
			}
			return 	$res.$nsURI;
		} else {
			return false;
		}
	}

    /**
     * Creates a referenced array out of an XSD object with the xml heirarchy expressed with ! separators
	 *
	 * Developer Note: This is a recursive function that traverses through the XML elements and attributes using a DOM Document (PHP5).	 
	 * 
     * @access  public
     * @param   DomNode $domnode
     * @param   array $array The main returned array by reference
     * @param   string $top_element_name The top element in the XSD hierarchy to search for first
     * @param   array $array	The XSD array passed by reference
     * @param   string $parentnodename
     * @param   string $searchtype
     * @param   DomNode $superdomnode The top level node, kept down the recursion traversals for top level lookups
     * @param   string $supertopelement The top level element in the XSD hierarchy to search for first	 
     * @param   string $parentContent The front hierarchy of the array element passed by a parent XML element
     * @return  void ($array passed as reference recursively)
     */
	function dom_xsd_to_referenced_array($domnode, $topelement, &$array, $parentnodename="", $searchtype="", $superdomnode, $supertopelement="", $parentContent="", $refCount = array()) {
        //echo "Node:(".$domnode->nodeName."), topelement:($topelement), parentnodename:($parentnodename), searchtype:($searchtype), superdomnode:(".$superdomnode->nodeName."), superdomnode:($supertopelement), parentContent:($parentContent)<br/>\n";
		$array_ptr = &$array;	
		$standard_types = array("int", "string", "dateTime", "float", "anyURI", "base64Binary", "NMTOKEN", "lang");
		$current_refs = array();
		$current_types = array();
		$current_name = $parentnodename;
		if ($searchtype == "") {
			$searchtype = "element";
		}
		if ($supertopelement == "") {
			$supertopelement = $topelement;
		}
		switch ($domnode->nodeType) {
			case (XML_DOCUMENT_NODE): 
				$currentnode = new DomDocument;
				$currentnode = Misc::getElementByNameValue($domnode, $topelement);
				if ($currentnode !== false) {
					if ($currentnode->hasAttributes() ) {
						$attributes = $currentnode->attributes; 
						foreach ($attributes as $index => $attrib) {		
							if (($attrib->nodeName == "ref") || ($attrib->nodeName == "base")) {
								array_push($current_refs, $attrib->nodeValue);
							}
							if ($attrib->nodeName == "type") {
								array_push($current_types, $attrib->nodeValue);
							}
							if ($attrib->nodeName == "name") {
								$current_name = $attrib->nodeValue;
							}
						}
					}
					if ($current_name != "") {
						$array_ptr = &$array[$current_name];
					}
					foreach ($current_types as $type) {
						Misc::dom_xsd_to_referenced_array($currentnode, $type, $array_ptr, $current_name, "complexType", $superdomnode, $supertopelement, $parentContent, $refCount);
					}	
					if ($currentnode->hasChildNodes() ) {
						foreach ($currentnode->childNodes as $childnode) {
							Misc::dom_xsd_to_referenced_array($childnode, '', $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent, $refCount);
						}
					}
				} else {
					echo "Can't find element ".$topelement;
				}
				break;
			case XML_COMMENT_NODE:
				break;
			case XML_TEXT_NODE:
				break;
			case XML_ELEMENT_NODE:
				$currentnode = new DomDocument;
				if ($topelement <> '') {
					$currentnode = Misc::getXMLObjectByTypeNameValue($superdomnode, $searchtype, $topelement);
				} else {
					$currentnode = $domnode;
				}
				if (is_numeric(strpos($currentnode->nodeName, ":"))) { // Check if there is a ":" in the string if there is then snn is after the :
					$shortnodename = substr($currentnode->nodeName, (strpos($currentnode->nodeName, ":") + 1));
				} else {
					$shortnodename = $currentnode->nodeName;
				}	
				if (($shortnodename == $searchtype) && ($shortnodename <> "element") 
                        && ($shortnodename <> "attribute")) {
					if ($currentnode->hasAttributes() ) {
						$attributes = $currentnode->attributes;
						foreach ($attributes as $index => $attrib) {
							if ($attrib->nodeName == "name") {
								$current_name = $attrib->nodeValue;
							}
						}
					}	
					if ($currentnode->hasChildNodes() ) {
						foreach ($currentnode->childNodes as $childnode) {
							Misc::dom_xsd_to_referenced_array($childnode, "", $array_ptr, $parentnodename, "", $superdomnode, $supertopelement, $parentContent, $refCount);
						}
					}
				} elseif (($shortnodename == "extension") || ($shortnodename == "any") || ($shortnodename == "anyAttribute") || ($shortnodename == "restriction") || ($shortnodename == "group") || ($shortnodename == "complexContent") || ($shortnodename == "simpleContent") || ($shortnodename == "attributeGroup") || ($shortnodename == "attribute") || ($shortnodename == "enumeration"))  {
//					echo $parentnodename." - ".$shortnodename."<br />";
					if (($shortnodename == "attribute") || ($shortnodename == "extension")) {
						if ($currentnode->hasAttributes() ) {
							$attributes = $currentnode->attributes;	
                            $nextSearch = "complexType";
							foreach ($attributes as $index => $attrib) {
								if ($attrib->nodeName == "base") {
                                    if (is_numeric(strpos($attrib->nodeValue, ":"))) {
                                        $shorttypevalue = substr($attrib->nodeValue, (strpos($attrib->nodeValue, ":") + 1));
                                    } else {
                                        $shorttypevalue = $attrib->nodeValue;
                                    }
									if (!in_array($shorttypevalue, $standard_types)) {
										array_push($current_refs, $shorttypevalue);
									}
                                    $nextSearch = "complexType";
								}
								if ($attrib->nodeName == "name") {
									$current_name = $attrib->nodeValue;
									$parentContent .= "!".$current_name;
								}
								if ($attrib->nodeName == "value") {
									$current_name = $attrib->nodeValue;
									$parentContent .= "!".$current_name;
								}
                                if ($attrib->nodeName == "ref") {
                                    if (is_numeric(strpos($attrib->nodeValue, ":"))) {
                                        $shortrefvalue = 
                                            substr($attrib->nodeValue, (strpos($attrib->nodeValue, ":") + 1));
                                    } else {
                                        $shortrefvalue = $attrib->nodeValue;
                                    }
									if (!in_array($shortrefvalue, $standard_types)) {
                                        array_push($current_refs, $shortrefvalue);
									}
                                    $nextSearch = "attribute";
                                }
                            }
							foreach ($current_refs as $ref) {
								Misc::dom_xsd_to_referenced_array($currentnode, $ref, $array_ptr, $current_name, $nextSearch, $superdomnode, $supertopelement, $parentContent, $refCount);
							}
							if ($current_name <> $parentnodename) {
								$array_ptr = &$array[$current_name];
								$array_ptr['fez_hyperlink'] = $parentContent;
								if ($shortnodename == 'attribute') {
									$array_ptr['fez_nodetype'] = 'attribute';
								} elseif ($shortnodename == 'enumeration') {
									$array_ptr['fez_nodetype'] = 'enumeration';
								}
							}	
						}
					}	
					if (($shortnodename == "attributeGroup") || ($shortnodename == "group")) { // added group and choice (also to above) to this if to test mods - ck 
						$attributes = $currentnode->attributes;	
						foreach ($attributes as $index => $attrib) {
                            if ($attrib->nodeName == "ref") {
                                if (is_numeric(strpos($attrib->nodeValue, ":"))) {
                                    $shortrefvalue = substr($attrib->nodeValue, (strpos($attrib->nodeValue, ":") + 1));
                                } else {
                                    $shortrefvalue = $attrib->nodeValue;
                                }
                                array_push($current_refs, $shortrefvalue);
                            }
						}					

						foreach ($current_refs as $ref) {
							// Flag the ref for child parses so that it only recursives the same ref group once (or it could go in an endless recursive loop) - CK added 13/9/2006
							if (array_key_exists($ref, $refCount)) {
								if ($refCount[$ref] == 1) { // 
									$refCount[$ref] = 2;
									Misc::dom_xsd_to_referenced_array($currentnode, $ref, $array_ptr, $current_name, $shortnodename, $superdomnode, $supertopelement, $parentContent, $refCount);						
								} 
							} else {
								$refCount[$ref] = 1;
								Misc::dom_xsd_to_referenced_array($currentnode, $ref, $array_ptr, $current_name, $shortnodename, $superdomnode, $supertopelement, $parentContent, $refCount);
							}
						}
					}
					
					if ($currentnode->hasChildNodes() ) {
						foreach ($currentnode->childNodes as $childnode) {
							Misc::dom_xsd_to_referenced_array($childnode, '', $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent, $refCount);
						}
					}
				} elseif ($shortnodename == "element") {
					if ($currentnode->hasAttributes() ) {
						$attributes = $currentnode->attributes;	
						foreach ($attributes as $index => $attrib) {
							if (($attrib->nodeName == "ref") || ($attrib->nodeName == "base")) {
								$shorttypevalue = substr($attrib->nodeValue, (strpos($attrib->nodeValue, ":") + 1));
								if (!in_array($shorttypevalue, $standard_types)) {
									array_push($current_refs, $attrib->nodeValue);
								}
							}
							if ($attrib->nodeName == "name") {
								$current_name = $attrib->nodeValue;							
							}
							if ($attrib->nodeName == "type") {
                                if (is_numeric(strpos($attrib->nodeValue, ":"))) {
                                    $shorttypevalue = substr($attrib->nodeValue, (strpos($attrib->nodeValue, ":") + 1));
                                } else {
                                    $shorttypevalue = $attrib->nodeValue;
                                }
								if (!in_array($shorttypevalue, $standard_types)) {
									array_push($current_types, $shorttypevalue);
								}
							}
						}
						if (($current_name != $supertopelement) && ($current_name != "")) {
							$array_ptr = &$array[$current_name];
							$parentContent .= "!".$current_name;
							$array_ptr['fez_hyperlink'] = $parentContent;
						}
					}		
					foreach ($current_refs as $ref) {
						Misc::dom_xsd_to_referenced_array($currentnode, $ref, $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent, $refCount);
					}
					foreach ($current_types as $type) {
						Misc::dom_xsd_to_referenced_array($currentnode, $type, $array_ptr, $current_name, "complexType", $superdomnode, $supertopelement, $parentContent, $refCount);
					}	
					if ($currentnode->hasChildNodes() ) {
						foreach ($currentnode->childNodes as $childnode) {
							Misc::dom_xsd_to_referenced_array($childnode, '', $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent, $refCount);
						}
					}	
				} elseif ($currentnode->nodeName != "") {
					if ($currentnode->hasAttributes() ) {
						$attributes = $currentnode->attributes;	
						foreach ($attributes as $index => $attrib) {
							if (($attrib->nodeName == "ref") || ($attrib->nodeName == "base")) {
								$shorttypevalue = substr($attrib->nodeValue, (strpos($attrib->nodeValue, ":") + 1));
								if (!in_array($shorttypevalue, $standard_types)) {
									array_push($current_refs, $attrib->nodeValue);
								}
							}
							if ($attrib->nodeName == "name") {
								$current_name = $attrib->nodeValue;
							}
							if ($attrib->nodeName == "type") {
								$shorttypevalue = substr($attrib->nodeValue, (strpos($attrib->nodeValue, ":") + 1));
								if (!in_array($shorttypevalue, $standard_types)) {
									array_push($current_types, $attrib->nodeValue);
								}
							}
						}
					}
					foreach ($current_refs as $ref) {
						Misc::dom_xsd_to_referenced_array($currentnode, $ref, $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent, $refCount);
					}
					foreach ($current_types as $type) {
						Misc::dom_xsd_to_referenced_array($currentnode, $type, $array_ptr, $current_name, "complexType", $superdomnode, $supertopelement, $parentContent, $refCount);
					}	
					if ($currentnode->hasChildNodes() ) {
						foreach ($currentnode->childNodes as $childnode) {
							Misc::dom_xsd_to_referenced_array($childnode, '', $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent, $refCount);
						}
					} elseif ((count($current_refs) == 0) && (count($current_types) == 0) && ($current_name != $parentnodename)) {
						if (($current_name != $supertopelement) && ($current_name != "")) {					
							$array_ptr = &$array[$current_name];
							$parentContent .= "!".$current_name;
							$array_ptr['fez_hyperlink'] = $parentContent;
						}
					}
				}					
				break;
			default:
				echo "in default case of node type (".$domnode->nodeType.", ".$domnode->nodeName.", ".$domnode->nodeValue.")<br />";
				break;
		}
	}

    /**
     * Checks if a string is in a multi-dimensional array
	 * 
     * @access  public
     * @param   string $needle
     * @param   array $haystack
     * @return  boolean
     */	
	function in_multi_array($needle, $haystack) {
	   $in_multi_array = false;
	   if (in_array($needle, $haystack)) {
		   $in_multi_array = true;
	   } else {
		   foreach ($haystack as $key => $val) {
			   if(is_array($val)) {
				   if (Misc::in_multi_array($needle, $val)) {
					   $in_multi_array = true;
					   break;
				   }
			   }
		   }
	   }
	   return $in_multi_array;
	}
	
    /**
     * Flattens a multi-dimensional array
	 * 
     * @access  public
     * @param   string $needle
     * @param   array $haystack
     * @return  boolean
     */	
	function array_flatten(&$a,$pref='') {
	   $ret=array();
	   foreach ($a as $i => $j)
		   if (is_array($j)) {
			   $ret=array_merge($ret,Misc::array_flatten($j,$pref.$i."|"));
			   $ret[$pref.$i] = $i;
		   } else {
			   $ret[$pref.$i] = $j;
			}
	   return $ret;
	}

    /**
     * Returns an XSD schema string, usually from an XSD schema inside another parent one.
	 * 
     * @access  public
     * @param   arrray $a
     * @param   string $top_element_name
     * @param   string $xdis_id The XSD Display ID
     * @param   string $pid The persistent identifier
     * @return  array $res
     */	
	function getSchemaSubAttributes($a, $top_element_name, $xdis_id, $pid) {
		global $HTTP_POST_VARS;
		$res = "";
		foreach ($a[$top_element_name] as $i => $j) {
			if (is_array($j)) {
				if (!empty($j['fez_nodetype'])) {
					if ($j['fez_nodetype'] == 'attribute') {
						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement(urldecode($j['fez_hyperlink']), $xdis_id);					
						if (is_numeric($xsdmf_id)) {
							$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
							if ($xsdmf_details['xsdmf_fez_variable'] == "pid") {
								$res .= ' '.$i.'="'.$pid.'" ';
							} elseif ($xsdmf_details['xsdmf_fez_variable'] == "xdis_id") {
								$res .= ' '.$i.'="'.$top_xdis_id.'" ';
							} else {
								$res .= ' '.$i.'="'.$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id].'" ';
							}
						}
					}
				}
			}
		}
		return $res;
	}
	
    /**
     * Gets a date string from a form's HTTP Post variables date selector.
	 * 
     * @access  public
     * @param   integer $xsdmf_id
     * @return  string The date
     */
	function getPostedDate($xsdmf_id) {
		global $HTTP_POST_VARS;
		$return = array();
		$dateType = 0; // full date by default
		if ((!empty($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Year'])) &&
			 (!empty($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Month'])) &&
			 (!empty($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Day']))) {
			$return['value'] = sprintf('%s-%s-%s', $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Year'],
												$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Month'],
												$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Day']);
		} elseif ((!empty($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Year'])) &&
			 (!empty($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Month']))) {
			$return['value'] = sprintf('%s-%s-01', $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Year'],
												$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Month']);
			$dateType = 2;	// year and month
		} elseif (!empty($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Year'])) {
			$return['value'] = sprintf('%s-01-01',$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]['Year']);
			$dateType = 1; // year only 
		} else {
			$return['value'] = '';
		}
		$return['dateType'] = $dateType;
//		$return['value'] = $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id];
		return $return;
	}


    /**
     * Method used to create an array to be used in an Javascript DTree (dynamic tree) for XSD HTML to Matching Form elements.
	 * 
	 * Developer Note: This is a recursive function passing variables by reference.
	 * 	 
     * @access  public
     * @param   array $a The XSD Schema array to loop through
     * @param   integer $xdis_id The current XSD Display ID
     * @param   array $element_match_list
     * @param   integer $counter The current tree element counter
     * @param   integer $parent_counter The current parent tree element counter
     * @return  array $ret The DTree all ready to be implanted into the HTML.
     */
	function array_to_dtree($a, $xdis_id=0, $element_match_list=array(), $counter=0, $parent_counter=-1, &$open_array = array()) {
		$match_form_url = APP_BASE_URL."manage/xsd_tree_match_form.php?xdis_id=".$xdis_id."&xml_element=";
		$ret = array();
		$dtree_image = "";
		$ret[0] = 0;
		$ret[1] = "";
		foreach ($a as $i => $j) {
			$dtree_image = "";
			if (is_array($j)) {
				if (!(is_numeric($i))) { // if not number like [0]
					$ret[0] = $counter;
					if (($i != '#text') && ($i != '#comment')) {
						if (!empty($j['fez_nodetype'])) {
							if ($j['fez_nodetype'] == 'attribute') {
								$dtree_image = ", '../images/dtree/attribute.gif'";
							} elseif ($j['fez_nodetype'] == 'enumeration') {
								$dtree_image = ", '../images/dtree/enumeration.gif'";
							}
						}	
						if 	(isset($j['fez_hyperlink']) && !is_array($j['fez_hyperlink']))  {
							$ehref = $j['fez_hyperlink'];
							$node_label = $i;
							// make the tree node bold if there is a matchfields entry (i.e. we are using it)
							if (array_key_exists($ehref, $element_match_list)) {
								$node_label = "<b>$node_label</b>";
								if (array_key_exists(0, $element_match_list[$ehref])) {
									
								} else {
									switch ($element_match_list[$ehref]['xsdmf_html_input']) {
										case "xsd_loop_subelement":
										   $node_label .= ' <img title="Sublooping Element" src="'.APP_RELATIVE_URL.'images/sel_16.png" />';										   
										   break;
										case "date":
										   $node_label .= ' <img title="Date Selector" src="'.APP_RELATIVE_URL.'images/date_16.png" />';										   
										   break;
										case "text":
										   $node_label .= ' <img title="Text Input" src="'.APP_RELATIVE_URL.'images/text_16.png" />';										   
										   break;
										case "contvocab_selector":
										   $node_label .= ' <img title="Controlled Vocabulary Selector" src="'.APP_RELATIVE_URL.'images/contvocab_16.png" />';										   
										   break;
										case "author_selector":
										   $node_label .= ' <img title="Author Selector" src="'.APP_RELATIVE_URL.'images/author_selector_16.png" />';										   
										   break;
										case "author_suggestor":
										   $node_label .= ' <img title="Author Suggestor" src="'.APP_RELATIVE_URL.'images/author_suggestor_16.png" />';										   
										   break;
										case "static":
										   $node_label .= ' <img title="Static Text: '.$element_match_list[$ehref]['xsdmf_static_text'].'" src="'.APP_RELATIVE_URL.'images/static_16.png" />';										   
										   break;
										case "org_selector":
										   $node_label .= ' <img title="Organisational Structure Selector" src="'.APP_RELATIVE_URL.'images/org_selector_16.png" />';										   
										   break;
										case "file_input":
										   $node_label .= ' <img title="File Input" src="'.APP_RELATIVE_URL.'images/file_input_16.png" />';										   
										   break;
										case "xsdmf_id_ref":
										   $node_label .= ' <img title="XSDMF ID Reference" src="'.APP_RELATIVE_URL.'images/xsdmf_id_ref_16.png" />';										   
										   break;
										case "xsd_ref":
										   $node_label .= ' <img title="XSD Display Reference" src="'.APP_RELATIVE_URL.'images/xsd_ref_16.png" />';										   
										   break;
										case "textarea":
										   $node_label .= ' <img title="Text Area" src="'.APP_RELATIVE_URL.'images/form_16.png" />';										   
										   break;
										case "combo":
										   $node_label .= ' <img title="Combo Box" src="'.APP_RELATIVE_URL.'images/form_16.png" />';										   
										   break;
										case "multiple":
										   $node_label .= ' <img title="Multiple Combo Box" src="'.APP_RELATIVE_URL.'images/form_16.png" />';										   
										   break;
										case "checkbox":
										   $node_label .= ' <img title="Check Box" src="'.APP_RELATIVE_URL.'images/form_16.png" />';										   
										   break;
										case "dynamic":
										   $node_label .= ' <img title="Dynamic variable value" src="'.APP_RELATIVE_URL.'images/dynamic_16.png" />';										   
										   break;
										default:
											break;
									}
								}
								if (!array_key_exists($parent_counter, $open_array)) {
								    $open_array[$parent_counter] = "tree.openTo($parent_counter, false, false);\n";
								}
							}
							$ehref = urlencode($ehref);
						  $ret[1] .= "tree.add($counter, $parent_counter, '$node_label', "
							  ."'$match_form_url$ehref', '', 'basefrm'".$dtree_image.");\n";
						} else {
						  $ret[1] .= "tree.add($counter, $parent_counter, '$i');\n";
						} 
					}
					$tmp = array();
					$tmp = Misc::array_to_dtree($j, $xdis_id, $element_match_list, $counter + 1, $counter, $open_array);			
					$counter = $tmp[0];
					$ret[1] .= $tmp[1];
					$counter = $counter + 1;
				} else {
					$tmp = array();
					$tmp = Misc::array_to_dtree($j, $xdis_id, $element_match_list, $counter, $parent_counter, $open_array);
					$counter = $tmp[0];
					$ret[1] .= $tmp[1];
				}
			} else {		
				if (($i != '#text') && ($i != '#comment') && ($i != 'fez_nodetype') && ($i != 'fez_hyperlink') && (!(is_array($i['fez_hyperlink'])))) {
					if (!empty($j['fez_nodetype'])) {
						if ($j['fez_nodetype'] == 'attribute') {
							$dtree_image = ", '../images/dtree/attribute.gif'";
						} elseif ($j['fez_nodetype'] == 'enumeration') {
							$dtree_image = ", '../images/dtree/enumeration.gif'";
						}
					}
					$ehref = $j['fez_hyperlink'];
					$ehref = urlencode($ehref);
					$ret[1] .= "tree.add($counter, $parent_counter, '$i', '".$match_form_url.$ehref."', '', 'basefrm'".$dtree_image.");\n";
				} 
				$counter = $counter + 1;
			}
			$ret[0] = $counter;
		}
		$ret[2] = array_values($open_array);
		return $ret;
	}

    /**
     * isInt
     * Robust test of string to make sure it is an integer
	 * 
     * @access  public
     * @param   string $x
     * @return  boolean
     */
    function isInt ($x) {
        return (is_numeric($x) ? intval(0+$x) == $x : false);
    } 
    
    function collateArray($source, $ifield)
    {
        $dest = array();
        foreach ($source as $item) {
            $dest[$item[$ifield]][] = $item;
        }
        return $dest;
    }

    /**
     * Collates two arrays.
	 * 
     * @access  public
     * @param   array $source
     * @param   string $kfield Key field
     * @param   string $vfield Value field	 
     * @param   boolean $unique
     * @return  boolean
     */
    function collate2ColArray($source, $kfield, $vfield, $unique=false)
    {
        $dest = array();
        foreach ($source as $item) {
            $dest[$item[$kfield]][] = $item[$vfield];
            if ($unique) {
                $dest[$item[$kfield]] = array_unique($dest[$item[$kfield]]);
            }
        }
        return $dest;
    }

    /**
     * Separates the keys and the values into array keys.
	 * 
     * @access  public
     * @param   array $source
     * @param   string $kfield Key field
     * @param   string $vfield Value field	 
     * @return  array $dest The result
     */
    function keyPairs($source, $kfield, $vfield)
    {
        $dest = array();
        foreach ($source as $item) {
            $dest[$item[$kfield]] = $item[$vfield];
        }
        return $dest;
    }

    /**
     * Puts adds a key into the array.
	 * 
     * @access  public
     * @param   array $source
     * @param   string $kfield Key field
     * @return  array $dest The result
     */
    function keyArray($source, $kfield)
    {
        $dest = array();
        foreach ($source as $item) {
            $dest[$item[$kfield]] = $item;
        }
        return $dest;
    }

    /**
     * Returns 1 if the checkbox was checked, otherwise 0
	 * 
     * @access  public
     * @param   checkbox $check
     * @return  boolean
     */
    function checkBox($check)
    {
        $result = 0;
        if (!empty($check)) {
            $result = 1;
        }
        return $result;
    }

    /**
     * Returns the value of a GET or POST HTML variable based on key
	 * 
     * @access  public
     * @param   string $key
     * @return  POST or GET var
     */
    function GETorPOST($key)
    {
        global $HTTP_POST_VARS, $HTTP_GET_VARS;
        return @$HTTP_GET_VARS[$key] ? @$HTTP_GET_VARS[$key] : @$HTTP_POST_VARS[$key];
    }

    /**
     * Returns the value of a GET or POST HTML variable based on key, without the key in the value if it is found.
	 * 
     * @access  public
     * @param   string $key
     * @return  POST or GET var
     */
    function GETorPOST_prefix($key)
    {
        global $HTTP_POST_VARS, $HTTP_GET_VARS;
        $allvars = array_merge($HTTP_GET_VARS, $HTTP_POST_VARS);
        foreach ($allvars as $vkey => $value) {
            $res = strstr($vkey, $key);
            if ($res) {
                return substr($res, strlen($key));
            }
        }
        return false;
    }


    function arrayToSQL($a)
    {
        if (is_array($a)) {
            $b = $a;
            foreach ($a as $key => $value) {
                $b[$key] = "'".mysql_escape_string($value)."'";
            }
            return implode(',', $b);
        }
        return '';
    } 


    /**
    * stripOneElementArrays
    * This function takes out nested one element arrays but only non-associative arrays -
    * The one member arrays have the one member at [0].
	* 
	* @access  public
	* @param   array $a
	* @return  array
    */
    function stripOneElementArrays($a) {
        if (is_array($a)) {
            $k = array_keys($a);
            if (count($a) == 0) {
                return null;
            } elseif (count($a) == 1 && Misc::isInt($k[0]) && $k[0] == 0) {
                return Misc::stripOneElementArrays($a[0]);
            } else {
                foreach ($a as $key => $item) {
                    if (is_array($item)) {
                        $newitem = Misc::stripOneElementArrays($item);
                    } else {
                        $newitem = $item;
                    }
                    if ($newitem) {
                        if (Misc::isInt($key) && $key == 0) {
                            $b[] = $newitem;
                        } else {
                            $b[$key] = $newitem;
                        }
                    }
                }
                $k = array_keys($b);
                if (is_array($b) && empty($b)) {
                    return null;
                } elseif (count($b) == 1 && Misc::isInt($k[0]) && $k[0] == 0) {
                    return $b[0];
                } else {
                    return $b;
                }
            }
        } else {
            return $a;
        }
    }

    /**
     * Basic check to see if PID is valid
	 * 
     * @access  public
     * @param   string $pid
     * @return  boolean
     */
    function isValidPid($pid) 
    {
        if (!Misc::isInt($pid) || $pid > 0) {
            return true;
        } else {
            return false;
        }
    }   


    function shortFilename($filename, $maxlen)
    {
        $pathstuff = pathinfo($filename);
        $filename = basename($pathstuff['basename'], ".{$pathstuff['extension']}");
        $new_filename = substr($filename, 0, $maxlen - strlen($pathstuff['extension']) - 1)
            .".{$pathstuff['extension']}";
        return $new_filename;
    }

    function hasPrefix($string, $pre) 
    {
        return strpos($string, $pre) === 0;
    }

    function addPrefix($string, $pre)
    {
        if (!empty($string)) {
            return $pre.$string;
        } else {
            return '';
        }
    }

function backtrace()
{
   $output = "<div style='text-align: left; font-family: monospace;'>\n";
   $output .= "<b>Backtrace:</b><br />\n";
   $backtrace = debug_backtrace();
   foreach ($backtrace as $bt) {
       $args = '';
       foreach ($bt['args'] as $a) {
           if (!empty($args)) {
               $args .= ', ';
           }
           switch (gettype($a)) {
           case 'integer':
           case 'double':
               $args .= $a;
               break;
           case 'string':
               $a = htmlspecialchars(substr($a, 0, 64)).((strlen($a) > 64) ? '...' : '');
               $args .= "\"$a\"";
               break;
           case 'array':
               $args .= 'Array('.count($a).')';
               break;
           case 'object':
               $args .= 'Object('.get_class($a).')';
               break;
           case 'resource':
               $args .= 'Resource('.strstr($a, '#').')';
               break;
           case 'boolean':
               $args .= $a ? 'True' : 'False';
               break;
           case 'NULL':
               $args .= 'Null';
               break;
           default:
               $args .= 'Unknown';
           }
       }
       $output .= "<br />\n";
       $output .= "<b>file:</b> {$bt['line']} - {$bt['file']}<br />\n";
       $output .= "<b>call:</b> {$bt['class']}{$bt['type']}{$bt['function']}($args)<br />\n";
   }
   $output .= "</div>\n";
   return $output;
}

  
} // end of Misc class

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Misc Class');
}
?>
