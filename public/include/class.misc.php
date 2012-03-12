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
 * Class to hold methods and algorythms that woudln't fit in other classes, such
 * as functions to work around PHP bugs or incompatibilities between separate
 * PHP configurations.
 *
 * @version 1.0
 * @author Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>
 * @author Matthew Smith <m.smith@library.uq.edu.au>
 */

include_once(APP_INC_PATH . 'common.inc.php');
include_once(APP_INC_PATH . 'class.fezacml.php');
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.thomson_doctype_mappings.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once('HTML/AJAX/JSON.php');

class Misc
{

public static function multi_implode($glue, $pieces)
 {
     $string='';

     if (is_object($pieces)){
         return str_replace(chr(10), "", var_export($pieces, true));
     }
     
     if(is_array($pieces))
     {
         reset($pieces);
         while(list($key,$value)=each($pieces))
         {
             $string.=$glue.Misc::multi_implode($glue, $value);
         }
     }
     else
     {
         return $pieces;
     }

     return trim($string, $glue);
 }


 public static function smart_ucwords($string, $upper_all_length = false) {

     $delimiters = array(
        " ", "-", ".", "'", "O'", "Mc"
     );
     $exceptions = array(
        "to", "a", "the", "of", "by", "and",
        "with", "in", "as", "or", "for", "its", "de la", "de las", "der", "van de", "van",
        "der", "vit de", "von", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X"
     );

     // check the multibyte functions are enabled in this PHP install or just use the normal ones
    if (function_exists("mb_convert_case")) {
       $string = mb_strtolower($string, "UTF-8");
       $string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
       foreach ($delimiters as $delimiter){
           $words = explode($delimiter, $string);
           $newwords = array();
           foreach ($words as $word){
                   if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions) || (is_numeric($upper_all_length) && (strlen($word) <= $upper_all_length))) {
                           // check exceptions list for any words that should be in upper case
                           $word = mb_strtoupper($word, "UTF-8");
                   }
                   elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)){
                           // check exceptions list for any words that should be in lower case
                           $word = mb_strtolower($word, "UTF-8");
                   }
                   elseif (!in_array($word, $exceptions) ){
                           // convert to uppercase (non-utf8 only)
                           $word = ucfirst($word);
                   }
                   array_push($newwords, $word);
           }
           $string = join($delimiter, $newwords);
        }//foreach
    } else {
       $string = strtolower($string);
       $string = ucfirst($string);
       foreach ($delimiters as $delimiter){
           $words = explode($delimiter, $string);
           $newwords = array();
           foreach ($words as $word){
                   if (in_array(strtoupper($word), $exceptions) || (is_numeric($upper_all_length) && (strlen($word) <= $upper_all_length))) {
                           // check exceptions list for any words that should be in upper case
                           $word = strtoupper($word);
                   }
                   elseif (in_array(strtolower($word), $exceptions)){
                           // check exceptions list for any words that should be in lower case
                           $word = strtolower($word);
                   }
                   elseif (!in_array($word, $exceptions) ){
                           // convert to uppercase (non-utf8 only)
                           $word = ucfirst($word);
                   }
                   array_push($newwords, $word);
           }
           $string = join($delimiter, $newwords);
        }//foreach
    }
    return $string;
 }


  // Method to escape apostrophes etc in a xpath query value.. horrible but this is how it's done
  public function xpathescape($string)
  {
    $result = 'concat(';

    for ($i=0, $j=strlen($string); $i<$j; ++$i) {
      if ($i > 0) {
        $result .= ",";
      }
      if ($string[$i] == '\'') {
        $result .= "\"".$string[$i]."\"";
      } else {
        $result .= '\''.$string[$i].'\'';
      }
    }

    $result .= ')';

    return $result;
  }

  function strlcs($str1, $str2)
  {
    $m = strlen($str1);
    $n = strlen($str2);
    $L = array();
    $z = 0;
    $ret = "";

    for ($i=0; $i<$m; $i++) {
      $L[$i] = array();
      for ($j=0; $j<$n; $j++) {
        $L[$i][$j] = 0;
      }
    }

    for ($i=0; $i<$m; $i++) {
      for ($j=0; $j<$n; $j++) {
        if ( $str1[$i] == $str2[$j] ) {
          $L[$i][$j] = $L[$i-1][$j-1] + 1;
          if ( $L[$i][$j] > $z ) {
            $z = $L[$i][$j];
            $ret = "";
          }
          if ( $L[$i][$j] == $z ) {
            $ret .= substr($str1, $i-$z+1, $z);
          }
        }
      }
    }
    return $ret;
  }

  function strip_breaks($string)
  {
    return preg_replace('#<br\s*?/?>#i', "", $string);
  }

  function addToWhere($sql, $newString, $operator = 'and')
  {
    if ((trim($newString) != "") && (!empty($newString))) {
      if (is_numeric(stripos($sql, "WHERE"))) {
        if (strtolower(substr((trim($newString)), 0, 3)) == $operator) {
          $sql .= $newString;
        } else {
          $sql .= " $operator ".$newString;
        }
      } else {
        if (strtolower(substr((trim($newString)), 0, 3)) == $operator) {
          $sql = " WHERE ".substr((trim($newString)), 3);
        } else {
          $sql = " WHERE ".$newString;
        }
      }
    }
    return $sql;
  }

  function parse_str_ext($toparse)
  {
    $returnarray = array();
    $keyvaluepairs = preg_split("/&/", $toparse);
    foreach ($keyvaluepairs as $pairval) {
      $splitpair = preg_split("/=/", $pairval);
      if (!array_key_exists($splitpair[0], $returnarray)) {
        $returnarray[$splitpair[0]] = array();
      }
      $returnarray[$splitpair[0]][] = $splitpair[1];
    }
    return $returnarray;
  }


  /**
   * Calculate new image dimensions to new constraints
   *
   * @param Original X size in pixels
   * @param Original Y size in pixels
   * @return New X maximum size in pixels
   * @return New Y maximum size in pixels
   */
  function scaleImage($x,$y,$cx,$cy)
  {
    //Set the default NEW values to be the old, in case it doesn't even need scaling
    list($nx, $ny)=array($x,$y);

    // If image is generally smaller, don't even bother
    if ($x>=$cx || $y>=$cx) {
      // Work out ratios
      if ($x>0) {
        $rx=$cx/$x;
      }
      if ($y>0) {
        $ry=$cy/$y;
      }

      //Use the lowest ratio, to ensure we don't go over the wanted image size
      if ($rx>$ry) {
          $r=$ry;
      } else {
          $r=$rx;
      }

      //Calculate the new size based on the chosen ratio
      $nx=intval($x*$r);
      $ny=intval($y*$r);
    }

    //Return the results
    return array($nx,$ny);
  }

  /**
   *  To use instead of php file_get_contents or fopen/fread as curl is much faster
   * @param string $url
   * @param bool $passthru - if true, don't return the retreived content, just echo it
   */
  function processURL($url, $passthru=false, $filehandle=null, $post = null, $contenttype = null, $timeout = 45)
  {
    $log = FezLog::get();

    if (empty($url)) {
      return "";
    }
    $url = str_replace('&amp;', '&', $url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    if ($filehandle != null) {
      curl_setopt($ch, CURLOPT_FILE, $filehandle);
      curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
      curl_setopt($ch, CURLOPT_BUFFERSIZE, 64000);

    } else {
      if (!$passthru) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      }
    }

    if ($post != null) {
      curl_setopt($ch, CURLOPT_POST, 1);
       curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    if ($timeout != null) {
      curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    }
    //$contenttype = "image/jpeg2";
    if ($contenttype != null) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: ".$contenttype));
    }

    if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://') {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }

    $data = curl_exec($ch);
    if ($data) {
      $info = curl_getinfo($ch);
      curl_close($ch);
    } else {
      $info = array();
      $log->err(array(curl_error($ch)." ".$url,__FILE__,__LINE__));
      curl_close($ch);
    }
    return array($data,$info);
  }


  /**
   * @return the numeric part of the pid
   */
  function numPID($pid)
  {
    return substr($pid, strpos($pid, ":")+1);
  }

  function namespacePID($pid)
  {
    return substr($pid, 0, strpos($pid, ":"));
  }

  function comparePIDs($pid1, $pid2)
  {
    // the namespace has the higher precendence
    $res1 = strcmp(Misc::namespacePID($pid1), Misc::namespacePID($pid2));
    if ($res1 === 0) {
      // the namespaces are the same so compare the numbers
      $res2 = Misc::numPID($pid1) - Misc::numPID($pid2);
      if ($res2 === 0) {
        return 0;
      } else {
        return  $res2 / abs($res2);
      }
    } else {
      return $res1;
    }
  }
  /**
   * Just get the http headers from an URL
   * Returns the header and the curl info array.
   */
  function processURL_info($url)
  {
    $log = FezLog::get();

    if (empty($url)) {
      return "";
    }
    $url=str_replace('&amp;', '&', $url);
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://') {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    $data = curl_exec($ch);
    if ($data) {
      $info = curl_getinfo($ch);
      curl_close($ch);
    } else {
      $info = array();
      $log->err(array(curl_error($ch)." ".$url,__FILE__,__LINE__));
      curl_close($ch);
    }
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
    if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://') {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    // if auth is needed, do it here
    if (!empty($user) && !empty($pw)) {
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
    if (isset($matches[1])) {
      $size = $matches[1];
    } else {
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


  function size_hum_read($size)
  {
    /**
     * Returns a human readable size
     */
    $i=0;
    $iec = array("Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
    while (($size/1024)>1) {
      $size=$size/1024;
      $i++;
    }
    return substr($size, 0, strpos($size, '.')+3).$iec[$i];
  }

  function array_merge_preserve($arr1,$arr2)
  {
    if (!is_array($arr1)) {
      $arr1 = array();
    }
    if (!is_array($arr2)) {
      $arr2 = array();
    }
    $keys1 = array_keys($arr1);
    $keys2 = array_keys($arr2);
    $keys = array_merge($keys1, $keys2);
    $vals1 = array_values($arr1);
    $vals2 = array_values($arr2);
    $vals = array_merge($vals1, $vals2);
    $ret = array();
    foreach ($keys as $key) {
      list ($unused,$val) = each($vals);
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
    $log = FezLog::get();

    // start output buffering
    //	   ob_start();
    // initialize curl with given uri
    $uri=str_replace('&amp;', '&', $uri);
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, $uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //	   $ch = curl_init($uri);
    if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://') {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    // if auth is needed, do it here
    if (!empty($user) && !empty($pw)) {
      $headers = array('Authorization: Basic ' .  base64_encode($user.':'.$pw));
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $content = curl_exec($ch);
    if ($content) {
      curl_close($ch);
    } else {
      $info = array();
      $log->err(array(curl_error($ch),__FILE__,__LINE__));
      curl_close($ch);
    }

    // get the output buffer
    //	   $content = ob_get_contents();
    // clean the output buffer and return to previous
    // buffer settings
    //   ob_end_clean();


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
  function array_merge_values($arr1,$arr2)
  {
    $newarray = array();
    foreach ($arr1 as $val) {
      if (!in_array($val, $newarray)) {
        array_push($newarray, $val);
      }
    }
    foreach ($arr2 as $val) {
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
  function const_array($constant)
  {
     $array = explode(",", $constant);
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
  function array_clean($input, $delete = false, $caseSensitive = false, $matchWholeWords = false)
  {
    $return = array();
    foreach ($input as $aryKey => $aryData) {
      if ($delete) {
        if ($caseSensitive) {
          if ($matchWholeWords && $aryData != $delete) {
            $return[$aryKey] = $aryData;
          } else if (!strstr($aryData, $delete)) {
            $return[$aryKey] = $aryData;
          }
        } else {
          if ($matchWholeWords && strtolower($aryData) != strtolower($delete)) {
            $return[$aryKey] = $aryData;
          } else if (!stristr($aryData, $delete)) {
            $return[$aryKey] = $aryData;
          }
        }
      } else {
        if (!empty($aryData)) {
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
  function array_clean_key ($input, $delete = false, $caseSensitive = false, $matchWholeWords = false)
  {
    $return = array();
    foreach ($input as $aryKey => $aryData) {
      if ($delete) {
        if ($caseSensitive) {
          if ($matchWholeWords && $aryKey != $delete) {
            $return[$aryKey] = $aryData;
          } else if (!strstr($aryKey, $delete)) {
            $return[$aryKey] = $aryData;
          }
        } else {
          if ($matchWholeWords && strtolower($aryKey) != strtolower($delete)) {
            $return[$aryKey] = $aryData;
          } else if (!stristr($aryKey, $delete)) {
            $return[$aryKey] = $aryData;
          }
        }
      } else {
        if (!empty($aryData)) {
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
    if (!is_array($input)) {
      return array();
    }
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
  function cleanDatastreamList($dsList)
  {
    $original_dsList = $dsList;
    $return = array();
    foreach ($dsList as $key => $ds) {
      $pid = $key;
      $keep = true;
      if (
          (is_numeric(strpos($ds['ID'], "thumbnail_"))) ||
          (is_numeric(strpos($ds['ID'], "web_"))) ||
          (is_numeric(strpos($ds['ID'], "preview_"))) ||
          (is_numeric(strpos($ds['ID'], "presmd_"))) ||
          (is_numeric(strpos($ds['ID'], "FezACML_")))
      ) {
        $keep = false;
      }
      // now try and find a thumbnail datastream of this datastream
      if (is_numeric(strrpos($ds['ID'], "."))) {
        $thumbnail = "thumbnail_".substr($ds['ID'], 0, strrpos($ds['ID'], ".") + 1)."jpg";
      } else {
        $thumbnail = "thumbnail_".$ds['ID'].".jpg";
      }
      $ds['thumbnail'] = 0;
      foreach ($original_dsList as $o_key => $o_ds) {
        if ($thumbnail == $o_ds['ID']) {  // found the thumbnail datastream so save it against the record
          $ds['thumbnail'] = $thumbnail;
        }
      }
      // now try and find a web datastream of this datastream
      if (is_numeric(strrpos($ds['ID'], "."))) {
        $web = "web_".substr($ds['ID'], 0, strrpos($ds['ID'], ".") + 1)."jpg";
      } else {
        $web = "web_".$ds['ID'].".jpg";
      }
      $ds['web'] = 0;
      foreach ($original_dsList as $o_key => $o_ds) {
        if ($web == $o_ds['ID']) {  // found the web datastream so save it against the record
          $ds['web'] = $web;
        }
      }
      // now try and find a preview datastream of this datastream
      if (is_numeric(strrpos($ds['ID'], "."))) {
        $preview = "preview_".substr($ds['ID'], 0, strrpos($ds['ID'], ".") + 1)."jpg";
      } else {
        $preview = "preview_".$ds['ID'].".jpg";
      }
      $ds['preview'] = 0;
      foreach ($original_dsList as $o_key => $o_ds) {
        if ($preview == $o_ds['ID']) {  // found the preview datastream so save it against the record
          $ds['preview'] = substr($preview, 8);
        }
      }


      // now try and find a preservation metadata datastream of this datastream
      if (is_numeric(strrpos($ds['ID'], "."))) {
        $presmd = "presmd_".substr($ds['ID'], 0, strrpos($ds['ID'], ".") + 1)."xml";
      } else {
        $presmd = "presmd_".$ds['ID'].".xml";
      }
      $ds['presmd'] = 0;
      foreach ($original_dsList as $o_key => $o_ds) {
        if ($presmd == $o_ds['ID']) {  // found the presmd datastream so save it against the record
          $ds['presmd'] = $presmd;
        }
      }
      // now try and find a FezACML metadata datastream of this datastream
      $fezacml = FezACML::getFezACMLDSName($ds['ID']);
      $ds['fezacml'] = 0;

      foreach ($original_dsList as $o_key => $o_ds) {
        if ($fezacml == $o_ds['ID']) {  // found the fezacml datastream so save it against the record
          $ds['fezacml'] = $fezacml;
          // now see if its allowed to show etc
          $record = new Record($pid);
          $FezACML_xdis_id = XSD_Display::getID('FezACML for Datastreams');
          $FezACML_display = new XSD_DisplayObject($FezACML_xdis_id);
          //echo $pid;
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
      if (is_numeric(strpos(@$ds['MIMEType'], 'image/'))) {
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

  function isAllowedDatastream($dsID)
  {
    if (
        (is_numeric(strpos($dsID, "thumbnail_"))) ||
        (is_numeric(strpos($dsID, "web_"))) ||
        (is_numeric(strpos($dsID, "preview_"))) ||
        (is_numeric(strpos($dsID, "presmd_"))) ||
        (is_numeric(strpos($dsID, "FezACML_")))
    ) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Method used to remove certain datastreams from the list of datastreams the user will see.
   *
   * @access  public
   * @param   array $dsList
   * @return  array $return
   */
  function cleanDatastreamListLite($dsList, $pid)
  {
    /*if(!APP_FEDORA_BYPASS =='ON')
    {*/
        $original_dsList = $dsList;
        $return = array();
        foreach ($dsList as $key => $ds) {
          // The following ID's should be removed
          if (
              (is_numeric(strpos($ds['ID'], "thumbnail_")))
              || (is_numeric(strpos($ds['ID'], "MODS")))
              || (is_numeric(strpos($ds['ID'], "web_")))
              || (is_numeric(strpos($ds['ID'], "preview_")))
              || (is_numeric(strpos($ds['ID'], "presmd_")))
              || (is_numeric(strpos($ds['ID'], "stream_")))
              || (is_numeric(strpos($ds['ID'], "FezACML_")))
              || (is_numeric(strpos($ds['ID'], "FezComments")))
          ) {
            continue;
          }

          // now try and find a thumbnail datastream of this datastream
          $thumbnail = "thumbnail_".substr($ds['ID'], 0, strrpos($ds['ID'], ".") + 1)."jpg";
          $ds['thumbnail'] = 0;
          foreach ($original_dsList as $o_key => $o_ds) {
            if ($thumbnail == $o_ds['ID']) {  // found the thumbnail datastream so save it against the record
              $ds['thumbnail'] = $thumbnail;
            }
          }
          // now try and find a stream datastream of this datastream as long as the datastream is a video or audio
          // (streamable datastream), not an image
          $stream = "stream_".substr($ds['ID'], 0, strrpos($ds['ID'], ".") + 1)."flv";
          $ds['stream'] = 0;
          if (!is_numeric(strpos($ds['MIMEType'], 'image'))) {
            foreach ($original_dsList as $o_key => $o_ds) {
              if ($stream == $o_ds['ID']) {  // found the stream datastream so save it against the record
                $ds['stream'] = $stream;
              }
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
              $ds['preview'] = $preview;
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
          $fezacml = FezACML::getFezACMLDSName($ds['ID']);
          $ds['fezacml'] = 0;
          foreach ($original_dsList as $o_key => $o_ds) {
            if ($fezacml == $o_ds['ID']) {  // found the fezacml datastream so save it against the record
              $ds['fezacml_roles'] = Auth::getAuthorisationGroups($pid, $ds['ID']);
            }
          }
          //roles for previewing images
          $acceptable_roles = array("Viewer", "Community_Admin", "Editor", "Creator", "Annotator");
          $ds['canPreview'] = false;
          if (array_key_exists('fezacml_roles', $ds) && is_array($ds['fezacml_roles'])) {
            foreach ($acceptable_roles as $role) {
              if (in_array($role, $ds['fezacml_roles'])) {
                $ds['canPreview'] = true;
              }
            }
          } else {
            $ds['canPreview'] = true;
          }
          $return[$key] = $ds;
        }
        $return = array_values($return);
        return $return;
    //}
  }

  function addDeletedDatastreams( $datastreams,$pid,$requestedVersionDate )
  {
    $log = FezLog::get();

    $request_TS = strtotime($requestedVersionDate);

    $deleted_datastreams = Fedora_API::callGetDatastreams($pid, $requestedVersionDate, 'D');
    if (!$deleted_datastreams) {
      return $datastreams;
    }
    $auditTrail = Fedora_API::getAuditTrail($pid);

    $deleteTrail = array();
    foreach ( $auditTrail as $auditItem ) {
      // shorten audit trail to only delete actions
      // NOTE: This only works because Fez puts the word 'Deleted' in the justification.
      if ( $auditItem['action'] != 'setDatastreamState' ) {
        continue;
      }
      if ( stripos($auditItem['justification'], 'delete') === false ) {
        continue;
      }
      array_push($deleteTrail, $auditItem);
    }

    $return = $datastreams;

    foreach ( $deleted_datastreams as $datastream ) {
      $dsID = $datastream['ID'];

      // find all audit items related to this datastream
      $found = false;
      $include = false;
      foreach ( $deleteTrail as $auditItem ) {
        if ( $dsID != $auditItem['componentID'] ) {
          continue;
        }
        $found = true;
        // check date
        $audit_TS = strtotime($auditItem['date']);
        if ($audit_TS >= $request_TS) {
          $include = true;
        }
      }

      if ( !found ) {
        $log->err(array("Warning: Couldn't find delete audit record for deleted datastream $dsID", __FILE__,__LINE__));
      }
      if ( $include ) {
        array_push($return, $datastream);
      }
    }

    return $return;
  }


  /**
   * Method used to format a filesize in bytes to the appropriate string,
   * showing 'Kb' and 'Mb'.
   *
   * Developer Note: This will be used once the Fedora 2.0 managed content datastream filesize bug is
   * fixed (probably in Fedora 2.1)
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
    } else if (($bytes > $kb) && ($bytes <= $mb)) {
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
    return $str;
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
    $log = FezLog::get();
    $db = DB_API::get();

    $boolean = array();
    $pieces = explode(" ", $value);
    for ($i = 0; $i < count($pieces); $i++) {
      $boolean[] = "$field LIKE " . $db->quote('%'.$pieces[$i].'%');
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
    while ($item = @readdir($dir)) {
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
   * Method used to get a list of HTTP status codes and their meanings.
   *
   * @access  public
   * @return  array The list of HTTP status codes & their explainations.
   */
  function httpStatusCodes()
  {
    return array(
      "0" => "No HTTP Status Code Returned",
      "100" => "Continue",
      "101" => "Switching Protocols",
      "102" => "Processing (WebDAV) (RFC 2518)",
      "122" => "Request-URI too long",
      "200" => "OK",
      "201" => "Created",
      "202" => "Accepted",
      "203" => "Non-Authoritative Information (since HTTP/1.1)",
      "204" => "No Content",
      "205" => "Reset Content",
      "206" => "Partial Content",
      "207" => "Multi-Status (WebDAV) (RFC 4918)",
      "226" => "IM Used (RFC 3229)",
      "300" => "Multiple Choices",
      "301" => "Moved Permanently",
      "302" => "Found",
      "303" => "See Other (since HTTP/1.1)",
      "304" => "Not Modified",
      "305" => "Use Proxy (since HTTP/1.1)",
      "306" => "Switch Proxy",
      "307" => "Temporary Redirect (since HTTP/1.1)",
      "400" => "Bad Request",
      "401" => "Unauthorized",
      "402" => "Payment Required",
      "403" => "Forbidden",
      "404" => "Not Found",
      "405" => "Method Not Allowed",
      "406" => "Not Acceptable",
      "407" => "Proxy Authentication Required[2]",
      "408" => "Request Timeout",
      "409" => "Conflict",
      "410" => "Gone",
      "411" => "Length Required",
      "412" => "Precondition Failed",
      "413" => "Request Entity Too Large",
      "414" => "Request-URI Too Long",
      "415" => "Unsupported Media Type",
      "416" => "Requested Range Not Satisfiable",
      "417" => "Expectation Failed",
      "418" => "I'm a teapot",
      "422" => "Unprocessable Entity (WebDAV) (RFC 4918)",
      "423" => "Locked (WebDAV) (RFC 4918)",
      "424" => "Failed Dependency (WebDAV) (RFC 4918)",
      "425" => "Unordered Collection (RFC 3648)",
      "426" => "Upgrade Required (RFC 2817)",
      "444" => "No Response",
      "449" => "Retry With",
      "450" => "Blocked by Windows Parental Controls",
      "499" => "Client Closed Request",
      "500" => "Internal Server Error",
      "501" => "Not Implemented",
      "502" => "Bad Gateway",
      "503" => "Service Unavailable",
      "504" => "Gateway Timeout",
      "505" => "HTTP Version Not Supported",
      "506" => "Variant Also Negotiates (RFC 2295)",
      "507" => "Insufficient Storage (WebDAV) (RFC 4918)[6]",
      "509" => "Bandwidth Limit Exceeded (Apache bw/limited extension)",
      "510" => "Not Extended (RFC 2774)"
    );
  }

  function httpStatusCodeColourise($code)
  {
    // This could be done better with classes.
    if ($code == "200" || $code == "301" || $code == "302" || $code == "303" || $code == "304" || $code == "307") {
      return "good";
    } elseif ($code == "0") {
      return "unknown";
    } else {
      return "bad";
    }
  }

  /**
   * Method used to get the mime content type in a better way than the default PHP way.
   *
   * @access  public
   * @param   string $f The file name and path
   * @return  string The formatted time
   */
  function mime_content_type($f)
  {
    $ret = '';
    if (stristr(PHP_OS, 'win') && (!stristr(PHP_OS, 'darwin'))) {
      $xmlfile = Workflow::checkForPresMD($f);
      //Error_Handler::logError($xmlfile);
      $dom = DOMDocument::load($xmlfile);
      $xp = new DOMXPath($dom);
      $xp->registerNamespace('j', 'http://hul.harvard.edu/ois/xml/ns/jhove');
      $res = $xp->query('/j:jhove/j:repInfo/j:mimeType');
      if ($res->length > 0) {
        $node = $res->item(0);
        $ret = $node->nodeValue;
      }
      unlink($xmlfile);
    } else if (stristr(PHP_OS, 'solaris')) {
      $ret = mime_content_type($f);
    } else {
      $f = escapeshellarg($f);
      $ret = trim(`file -bi $f`);
    }
    if ($ret == "application/octet-stream") {
      $filename_ext = str_replace("'", "", strtolower(substr($f, (strrpos($f, ".") + 1))));
      //			Error_Handler::logError(array($filename_ext,$ret));
      if ($filename_ext == "wmv") {
        $ret = "video/x-wmv";
      }
      if ($filename_ext == "webm") {
        $ret = "video/webm";
      }
      if ($filename_ext == "mp4") {
        $ret = "video/x-mp4";
      }
      if ($filename_ext == "mkv") {
        $ret = "video/x-matroska";
      }
    }
    if ($ret == "application/msword") {
      // determine if this is really a powerpoint presentation or an excel doc
      $filename_ext = str_replace("'", "", strtolower(substr($f, (strrpos($f, ".") + 1))));
      if ($filename_ext == "ppt") {
        $ret = "application/mspowerpoint";
      } else if ($filename_ext == "xls") {
        $ret = "application/msexcel";
      }
    }
    if ($ret == "application/x-zip") {
      // determine if excel workbook doc was misclassified
      $filename_ext = str_replace("'", "", strtolower(substr($f, (strrpos($f, ".") + 1))));
      if ($filename_ext == "xlsx") {
        $ret = "application/msexcel";
      }
    }
    //Error_Handler::logError(array($f,$ret));
    return $ret;
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
    $text = preg_replace(
        "'(\w+)://([\w\+\-\@\=\?\.\%\/\:\&\;]+)(\.)?'",
        "<a title=\"open \\1://\\2 in a new window\" class=\"".$class.
        "\" href=\"\\1://\\2\" target=\"_\\2\">\\1://\\2</a>", $text
    );
    $text = preg_replace(
        "'(\s+)www.([\w\+\-\@\=\?\.\%\/\:\&\;]+)(\.\s|\s)'",
        "\\1<a title=\"open http://www.\\2 in a new window\" class=\"".$class.
        "\" href=\"http://www.\\2\" target=\"_\\2\">www.\\2</a>\\3", $text
    );
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
  function get_content_type($url,$follow_location = TRUE,$timeout = 5)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow_location);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    if (APP_HTTPS_CURL_CHECK_CERT == "OFF" && APP_FEDORA_APIA_PROTOCOL_TYPE == 'https://') {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }

    curl_exec($ch);
    if (($c = curl_getinfo($ch, CURLINFO_HTTP_CODE)) < 200 || $c >= 300) {
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
  function sql_array_to_string($array)
  {
    $return_str = "";
    $existing_array = array();
    foreach ($array as $key=>$val) {
      $val = array_values($val);
      if (!empty($val)) {
        if (!in_array($val, $existing_array)) {
          if (!empty($val[0])) {
            $return_str .= ",".$val[0];
            array_push($existing_array, $val);
          }
        }
      }
    }
    $return_str = substr($return_str, 1);
    return $return_str;
  }

  /**
   * Accepts an SQL array and returns a string of comma separated values from the array.
   *
   * @access  public
   * @param   array $array
   * @return  string $return_str
   */
  function sql_array_to_string_simple($array)
  {
    $return_str = "";
    $existing_array = array();
    foreach ($array as $key=>$val) {
      if (!empty($val)) {
        if (!in_array($val, $existing_array)) {
          $return_str .= ",".$val;
          array_push($existing_array, $val);
        }
      }
    }
    $return_str = substr($return_str, 1);
    return $return_str;
  }

  function array_to_object($arr, &$obj)
  {
    foreach ($arr as $key => $value) {
      if (is_array($value)) {
        $obj->$key = new stdClass();
        Misc::array_to_object($value, $obj->$key);
      } else {
        $obj->$key = $value;
      }
    }
    return $obj;
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
  function getDatastreamXMLHeaders($datastreamTitles, $xmlString, $existingDatastreams = array())
  {
    $log = FezLog::get();

    $return = array();
    $next_link = Misc::getNextLink($existingDatastreams);
    // For items which repeat, (like ID (ID and versionID)) make the searchable part uppercase and
    // the name difference lowercase
    $searchvars = array("ID", "CONTROL_GROUP", "STATE", "VERSIONABLE", "versionID", "LABEL", "MIMETYPE");

    foreach ($datastreamTitles as $dsTitle) {
      //		$IDPos = stripos($xmlString, 'id="'.$dsTitle['xsdsel_title'].'"'); // stripos is a php5 function
      $IDPos = stripos($xmlString, 'id="'.$dsTitle['xsdsel_title'].''); // stripos is a php5 function
      if (is_numeric($IDPos)) {
        $XMLContentStartPos = $IDPos;
        if (is_numeric(strpos($xmlString, '<foxml:xmlContent>', $IDPos))) {
          $XMLContentEndPos = strpos($xmlString, '<foxml:xmlContent>', $XMLContentStartPos);
        } else if (is_numeric(strpos($xmlString, '<foxml:binaryContent>', $IDPos))) {
          $XMLContentEndPos = strpos($xmlString, '<foxml:binaryContent>', $XMLContentStartPos);
        } else if (is_numeric(strpos($xmlString, '<foxml:contentLocation>', $IDPos))) {
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
          // true for exclude file and link, only want the file and link labels
          $label_res = XSD_Loop_Subelement::getXSDMFInputType($dsTitle['xsdsel_id'], 'text', true);
          if (count($file_res) == 1) {
            if (is_array($_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']])) {
              foreach ($_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']] as $key => $data) {
                if (trim($_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']][$key]) != "") {
                  $return[$dsTitle['xsdsel_title'].$key]['CONTROL_GROUP'] =
                      $return[$dsTitle['xsdsel_title']]['CONTROL_GROUP'];
                  $return[$dsTitle['xsdsel_title'].$key]['STATE'] = $return[$dsTitle['xsdsel_title']]['STATE'];
                  $return[$dsTitle['xsdsel_title'].$key]['VERSIONABLE'] =
                      $return[$dsTitle['xsdsel_title']]['VERSIONABLE'];
                  $return[$dsTitle['xsdsel_title'].$key]['ID'] = str_replace(
                      " ", "_", $_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']][$key]
                  );
                  // change file extension to lower case
                  if (is_numeric(strpos($return[$dsTitle['xsdsel_title'].$key]['ID'], "."))) {
                    $filename_ext = strtolower(
                        substr(
                            $return[$dsTitle['xsdsel_title'].$key]['ID'],
                            (strrpos($return[$dsTitle['xsdsel_title'].$key]['ID'], ".") + 1)
                        )
                    );
                    $return[$dsTitle['xsdsel_title'].$key]['ID'] =
                        substr(
                            $return[$dsTitle['xsdsel_title'].$key]['ID'], 0,
                            strrpos($return[$dsTitle['xsdsel_title'].$key]['ID'], ".") + 1
                        ).$filename_ext;
                  }
                  $return[$dsTitle['xsdsel_title'].$key]['versionID'] =
                      $return[$dsTitle['xsdsel_title'].$key]['ID'].".0";
                  if (trim($_POST['xsd_display_fields'][$label_res[0]['xsdmf_id']][$key]) != "") {
                    $return[$dsTitle['xsdsel_title'].$key]['LABEL'] =
                        $_POST['xsd_display_fields'][$label_res[0]['xsdmf_id']][$key];
                  } else {
                    $return[$dsTitle['xsdsel_title'].$key]['LABEL'] =
                        $_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']][$key];
                  }

                  $error = $_FILES['xsd_display_fields']['error'][$file_res[0]['xsdmf_id']][$key];
                  $errorText = "";
                  switch ($error) {
                    case 1:
                      $errorText = "The file is bigger than this PHP installation allows";
                        break;
                    case 2:
                      $errorText = "The file is bigger than this form allows";
                        break;
                    case 3:
                      $errorText = "Only part of the file was uploaded";
                        break;
                    case 4:
                      $errorText = "No file was uploaded";
                        break;
                  }
                  if ($errorText != "") {
                    Error_Handler::logError($errorText, __FILE__, __LINE__);
                  } else {
                    // To help determine the MIME type, the file needs to have the correct extension.
                    // Some versions of PHP call all uploads <hash>.tmp so we make a copy with the right name before
                    // checking for the MIME type.  Not using file upload 'type' because it is unreliable.

                    // MT: 2010-01-04 - changed to use the name generated above (to lowercase the file extension)
                    $dsIDName = Foxml::makeNCName($return[$dsTitle['xsdsel_title'].$key]['ID']);
                    $temp_store = APP_TEMP_DIR.$dsIDName;

                    // check if this file was uploaded by the flash uploader
                    if ($_FILES['xsd_display_fields']['tmp_name'][$file_res[0]['xsdmf_id']][$key] == 'ALREADYMOVED') {
                      // if so, rename (move) the file into the new location
                      rename(
                          $_FILES['xsd_display_fields']['new_file_location'][$file_res[0]['xsdmf_id']][$key],
                          $temp_store
                      );
                    } else {
                      // otherwise, move the uploaded file into the new location
                      move_uploaded_file(
                          $_FILES['xsd_display_fields']['tmp_name'][$file_res[0]['xsdmf_id']][$key], $temp_store
                      );
                    }
                    $return[$dsTitle['xsdsel_title'].$key]['MIMETYPE'] = Misc::mime_content_type($temp_store);
                    $return[$dsTitle['xsdsel_title'].$key]['File_Location'] = $temp_store;
                    // @unlink($temp_store);
                  }
                }
              }
            } else { // file input is not a array, so only just one file
              $return[$dsTitle['xsdsel_title']]['ID'] = str_replace(
                  " ", "_", $_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']]
              );
              // change file extension to lower case
              if (is_numeric(strpos($return[$dsTitle['xsdsel_title']]['ID'], "."))) {
                $filename_ext = strtolower(
                    substr(
                        $return[$dsTitle['xsdsel_title']]['ID'],
                        (strrpos($return[$dsTitle['xsdsel_title']]['ID'], ".") + 1)
                    )
                );
                $return[$dsTitle['xsdsel_title']]['ID'] = substr(
                    $return[$dsTitle['xsdsel_title']]['ID'], 0,
                    strrpos($return[$dsTitle['xsdsel_title']]['ID'], ".") + 1
                ).$filename_ext;
              }
              $return[$dsTitle['xsdsel_title']]['versionID'] = $return[$dsTitle['xsdsel_title']]['ID'].".0";
              if ($_POST['xsd_display_fields'][$label_res[0]['xsdmf_id']] != "") {
                $return[$dsTitle['xsdsel_title'].$key]['LABEL'] =
                    $_POST['xsd_display_fields'][$label_res[0]['xsdmf_id']];
              } else {
                $return[$dsTitle['xsdsel_title']]['LABEL'] =
                    $_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']];
              }
              $error = $_FILES['xsd_display_fields']['error'][$file_res[0]['xsdmf_id']];
              $errorText = "";
              switch ($error) {
                case 1:
                  $errorText = "The file is bigger than this PHP installation allows";
                    break;
                case 2:
                  $errorText = "The file is bigger than this form allows";
                    break;
                case 3:
                  $errorText = "Only part of the file was uploaded";
                    break;
                case 4:
                  $errorText = "No file was uploaded";
                    break;
              }
              if ($errorText != "") {
                Error_Handler::logError($errorText, __FILE__, __LINE__);
              } else {
                // To help determine the MIME type, the file needs to have the correct extension.
                // Some versions of PHP call all uploads <hash>.tmp so we make a copy with the right name before
                // checking for the MIME type.  Not using file upload 'type' because it is unreliable.

                // MT: 2010-01-04 - changed to use the name generated above (to lowercase the file extension)
                $dsIDName = Foxml::makeNCName($return[$dsTitle['xsdsel_title'].$key]['ID']);
                $temp_store = APP_TEMP_DIR.$dsIDName;

                // check if this file was uploaded by the flash uploader
                if ($_FILES['xsd_display_fields']['tmp_name'][$file_res[0]['xsdmf_id']] == 'ALREADYMOVED') {
                  // if so, rename (move) the file into the new location
                  rename($_FILES['xsd_display_fields']['new_file_location'][$file_res[0]['xsdmf_id']], $temp_store);
                } else {
                  // otherwise, move the uploaded file into the new location
                  move_uploaded_file($_FILES['xsd_display_fields']['tmp_name'][$file_res[0]['xsdmf_id']], $temp_store);
                }

                $return[$dsTitle['xsdsel_title']]['File_Location'] = $temp_store;
                $return[$dsTitle['xsdsel_title']]['MIMETYPE'] = Misc::mime_content_type($temp_store);
                // unlink($temp_store);
              }
            }
          } else if (count($label_res) == 1 && ($dsTitle['xsdsel_title'] == "Link")) {
            // no file inputs are involved so might be a link
            if (is_array($_POST['xsd_display_fields'][$label_res[0]['xsdmf_id']])) {
              foreach ($_POST['xsd_display_fields'][$label_res[0]['xsdmf_id']] as $key => $data) {
                // fixed it so that it requires the url, not the description
                if ($_POST['xsd_display_fields'][$label_res[0]['xsdsel_attribute_loop_xsdmf_id']][$key] != "") {
                  $return[$dsTitle['xsdsel_title'].$key]['CONTROL_GROUP'] =
                      $return[$dsTitle['xsdsel_title']]['CONTROL_GROUP'];
                  $return[$dsTitle['xsdsel_title'].$key]['STATE'] = $return[$dsTitle['xsdsel_title']]['STATE'];
                  $return[$dsTitle['xsdsel_title'].$key]['VERSIONABLE'] =
                      $return[$dsTitle['xsdsel_title']]['VERSIONABLE'];
                  $return[$dsTitle['xsdsel_title'].$key]['ID'] = "link_".$next_link;
                  $next_link++;
                  $return[$dsTitle['xsdsel_title'].$key]['versionID'] = $return[$dsTitle['xsdsel_title'].
                      $key]['ID'].".0";
                  // NOW check the desc/label
                  if ($_POST['xsd_display_fields'][$label_res[0]['xsdmf_id']][$key] != "") {
                    $return[$dsTitle['xsdsel_title'].$key]['LABEL'] =
                        $_POST['xsd_display_fields'][$label_res[0]['xsdmf_id']][$key];
                  } else { // if it wasnt saved then just use the default
                    $return[$dsTitle['xsdsel_title'].$key]['LABEL'] = "Link";
                    // $return[$dsTitle['xsdsel_title'].$key]['LABEL'] = $return[$dsTitle['xsdsel_title']]['LABEL'];
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
              if (@$_POST['xsd_display_fields'][$label_res[0]['xsdmf_id']] != "") {
                $return[$dsTitle['xsdsel_title']]['LABEL'] =
                    $_POST['xsd_display_fields'][$label_res[0]['xsdmf_id']][$key];
              } else {
                $return[$dsTitle['xsdsel_title']]['LABEL'] = "Link";
                // $return[$dsTitle['xsdsel_title']]['LABEL'] = $return[$dsTitle['xsdsel_title']]['LABEL'];
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
   * Process links for versioning.
   *
   * @access  public
   * @param   string $pid The persistant identifier
   * @param   array $datastreamXMLHeaders list of headers for new datastreams
   * @param   array $datastreamXMLContent list of content for new datastreams
   * @param   array $existingDatastreams list of existing datastreams
   * @return  array $return - updated $datastreamXMLHeaders
   */
  function processLinkVersioning($pid,$datastreamXMLHeaders,$datastreamXMLContent,$existingDatastreams)
  {
    $log = FezLog::get();

    $return = array();

    // find all the existing links
    $existingLinks = array();
    $foundLinks = array();
    $i = 0;
    foreach ($existingDatastreams as $dseKey => $dse) {
      if ( $dse['controlGroup'] != "R" ) {
        continue;
      }
      $existingLinks[$i]=$dse;
      $foundLinks[$i]=false;
      $i++;
    }

    // loop through headers of new and changed ds
    foreach ($datastreamXMLHeaders as $dsKey => $dsTitle) {
      if ($dsTitle['CONTROL_GROUP'] != "R" ) {
        $return[$dsKey] = $dsTitle;
        continue;
      }

      // adjust the location the same way it is done
      // when links are created in Record::insertXML()
      $new_location = trim($datastreamXMLContent[$dsKey]);
      if (empty($new_location)) {
        $return[$dsKey] = $dsTitle;
        continue;
      }
      $new_location = str_replace("&amp;", "&", $new_location);

      $new_label = $dsTitle['LABEL'];

      // check to see if the link already exists
      $found = false;
      foreach ($existingLinks as $dseKey => $dse) {
        // Label is the determining factor in identifying an existing link.
        //
        // ex. OLD: Label=Google Location=http://www.google.com
        //     NEW: Label=Google Location=http://www.google.com/ig?hl=en
        // result -> NEW location replaces OLD location in the same datastream
        //
        // ex. OLD: Label=Google Location=http://www.google.com/ig?hl=en
        //     NEW: Label=iGoogle Location=http://www.google.com/ig?hl=en
        // result -> OLD datastream is deleted; NEW creates a new datastream
        //
        // This allows tracking of changes to URLs over time.

        if ( $new_label != $dse['label'] ) {
          continue;
        }

        if ( $new_location == $dse['location'] ) {
          // no changes -- don't include in new header array
        } else {
          // new version -- copy over dsID
          $dsTitle['ID'] = $dse['ID'];
          $return[$dsKey] = $dsTitle;
        }
        $foundLinks[$dseKey] = true;
        $found = true;
        break;
      }

      if ( !$found ) {
        $return[$dsKey] = $dsTitle;
      }
    }

    // remove any links that no longer exist
    foreach ($foundLinks as $idx => $found) {
      if (!$found) {
        $res = Fedora_API::deleteDatastream($pid, $existingLinks[$idx]['ID']);
      }
    }

    return( $return );
  }


  /**
   * Gets the next link number available for this object
   *
   * @access  public
   * @param   array $existingDatastreams
   * @return  integer $max_link The next link available
   */
  function getNextLink($existingDatastreams)
  {
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
   * Clears all the links so they can be regenerated
   *
   * @access  public
   * @param   array $existingDatastreams
   * @return  integer 1 on success, 0 on failure
   */
  function purgeExistingLinks($pid, $existingDatastreams)
  {
    $max_link = 0;
    $new_max_link = 0;

    foreach ($existingDatastreams as $eds) {
      $link_pos = strpos($eds['ID'], "link_");

      if (is_numeric($link_pos)) { // if found a link datatream
        $res = Fedora_API::callPurgeDatastream($pid, $eds['ID']);
        /*$new_max_link = substr($eds['ID'], ($link_pos+5)); // get its number
         if (is_numeric($new_max_link)) {
          if ($new_max_link > $max_link) {
          $max_link = $new_max_link;
          }
          }*/
      }
    }

    return 1;
  }


  function obj2array($obj)
  {
    $out = array();
    foreach ($obj as $key => $val) {
      switch(true) {
        case is_object($val):
          $out[$key] = Misc::obj2array($val);
            break;
        case is_array($val):
          $out[$key] = Misc::obj2array($val);
            break;
        default:
          $out[$key] = $val;
      }
    }
    return $out;
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
  function getDatastreamXMLContent($datastreamTitles, $xmlString)
  {
    $log = FezLog::get();

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
          /*				} elseif (is_numeric(strpos($searchXMLString, '<foxml:binaryContent>'))) {
           $XMLContentStartPos = strpos($searchXMLString, '<foxml:binaryContent>') + 22;
           $XMLContentEndPos = strpos($searchXMLString, '</foxml:binaryContent>', $XMLContentStartPos);
           */
        } else if (is_numeric(strpos($searchXMLString, '<foxml:contentLocation>'))) {
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
   * Removes the Non-XML content datastreams of the given titles in the xml string. These will be added after
   * initial ingest of basic object.
   *
   * @access  public
   * @param   array $datastreamTitles
   * @param   string $xmlString The FOXML object xml string
   * @return  array $return
   */
  function removeNonXMLDatastreams($datastreamTitles, $xmlString)
  {
    $log = FezLog::get();

    $return = $xmlString;
    foreach ($datastreamTitles as $title => $data) {
      $IDPos = stripos($xmlString, 'id="'.$title.'"'); // stripos is a php5 function
      $binaryPos = false;
      if (is_numeric($IDPos)) {
        // Find first close datastream tag after the ID tag
        $searchScopeEnd = strpos($xmlString, "</foxml:datastream>", $IDPos);
        // Get the cut down datastream XML to search for binary content
        $searchXMLString = substr($xmlString, 0, ($searchScopeEnd));
        // get the first opening binaryContent tag position in the xml after ds title, but
        // before a /datastream (close tag)
        $binaryPos = strpos($searchXMLString, '<foxml:binaryContent>', $IDPos);
        // check for contentLocation as well as this is now being added after ingest
        if (!is_numeric($binaryPos)) {
          // get the first opening contentLocation tag position in the xml after ds title, but
          // before a /datastream (close tag)
          $binaryPos = strpos($searchXMLString, '<foxml:contentLocation>', $IDPos);
        }
        if (is_numeric($binaryPos)) { // if you find binaryContent after this tag
          // the space is essential or it will pick '<foxml:datastreamVersion
          $XMLContentStartPos = strrpos(substr($xmlString, 0, $binaryPos), '<foxml:datastream ');
          $XMLContentEndPos = strpos($xmlString, '</foxml:datastream>', $XMLContentStartPos) + 19;
          if (is_numeric($XMLContentStartPos) && is_numeric($XMLContentEndPos)) {
            $tempXML = substr($xmlString, $XMLContentStartPos, ($XMLContentEndPos-$XMLContentStartPos));
            // if a binary datastream is found then remove it from the ingest object
            $return = str_replace($tempXML, "", $return);
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
   * Developer Note: This is a recursive function that traverses through the XML elements and attributes using a
   * DOM Document (PHP5).
   *
   * @access  public
   * @param   DomNode $domnode
   * @param   array $array The main returned array by reference
   * @param   string $top_element_name The top element in the XML hierarchy to search for first
   * @param   string $element_prefix eg OAI_DC:, FOXML: etc
   * @param   array $xsdmf_array	The XSD matching field array passed by reference
   * @param   integer $xdis_id The XSD Display ID of the object, or current child XSD Display ID of the XSD reference
   *                           in a recursive traversal
   * @param   string $parentContent The front hierarchy of the array element passed by a parent XML element
   * @param   string $parentKey If the parent is not unique it may need to pass its unique key to its child elements
   *                            traversals so the correct XSDMF_ID is found
   * @return  void (Uses $array and $xsdmf_array passed as reference recursively)
   */
  function dom_xml_to_simple_array(
      $domnode, &$array, $top_element_name, $element_prefix, &$xsdmf_array, $xdis_id, $parentContent="", $parent_key=""
  )
  {
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
      } else if (
          (strtolower($domnode->nodeName) != "xsd:annotation") &&
          (strtolower($domnode->nodeName) != "xsd:documentation")
      ) {
        // all other conditions (except desc's)
        $xsdmf_id = false;
        if ($domnode->hasAttributes() ) {
          $attributes = $domnode->attributes;
          foreach ($attributes as $index => $domobj) {
            if (is_numeric(strpos($domobj->nodeName, ":"))) {
              $new_element = "!".$parentContent."!".$clean_nodeName."!".
              substr($domobj->nodeName, strpos($domobj->nodeName, ":") +1);
            } else {
              $new_element = "!".$parentContent."!".$clean_nodeName."!".$domobj->nodeName;
            }
            if ($parent_key != "") { // if there are passed parent keys then use them in the search
              // try to match on a sel key
              $xsdmf_id = XSD_HTML_Match::getXSDMF_IDByKeyXDIS_ID($new_element, $domobj->nodeValue, $xdis_str);
            } else {
              // try to match on a sel key
              $xsdmf_id = XSD_HTML_Match::getXSDMF_IDByKeyXDIS_ID($new_element, $domobj->nodeValue, $xdis_str);
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
                } else if (isset($xsdmf_ptr[$xsdmf_id]) && array_key_exists(0, $xsdmf_ptr[$xsdmf_id])) {
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
        }
        // replaced the else statement below because even if it has attributes we want it to check the basic
        // element especially for xsd loop sublelement elements
        // If we still havent got the xsdmf_id then it either doesnt have one or the element doesnt have attributes,
        // so try to find it without the attributes
        if ((is_numeric(strpos(substr($parentContent, 0, 1), "!"))) || ($parentContent == "")) {
          // @@@ CK - 25/8/2005 - unless it is the below with the parent content, FezACML doesnt show properly
          // because it needs the !role!rule etc so any changes to this need to be tested with the FezACML etc
          // @@@ CK 31/5/2005 - Added ! to the front of the string if not there already
          $new_element = $parentContent."!".$clean_nodeName;
        } else {
          // @@@ CK - 25/8/2005 - unless it is the below with the parent content, FezACML doesnt show properley
          // because it needs the !role!rule etc so any changes to this need to be tested with the FezACML etc
          // @@@ CK 31/5/2005 - Added ! to the front of the string
          $new_element = "!".$parentContent."!".$clean_nodeName;
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
            } else if (!empty($xsdmf_ptr[$xsdmf_id]) && array_key_exists(0, $xsdmf_ptr[$xsdmf_id])) {
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
        if (
            (strpos($domnode->nodeName, $element_prefix.":".$top_element_name) === 0) ||
            (strpos($domnode->nodeName, $top_element_name) === 0)
        ) {
          $newParentContent = "";
          Misc::dom_xml_to_simple_array(
              $domnode, $array_ptr, $top_element_name, $element_prefix, $xsdmf_ptr, $xdis_id,
              $newParentContent, $parent_key
          );
        } else {
          if ($parentContent != "") {
            $newParentContent = Misc::strip_element_name(
                $parentContent."!".$domnode->nodeName, $top_element_name, $element_prefix, $parent_key
            );
          } else {
            $newParentContent = Misc::strip_element_name(
                $domnode->nodeName, $top_element_name, $element_prefix, $parent_key
            );
          }
          Misc::dom_xml_to_simple_array(
              $domnode, $array_ptr, $top_element_name, $element_prefix, $xsdmf_ptr,
              $xdis_id, $newParentContent, $parent_key
          );
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
  function XML_Walk($domnode, $callbackobject, $callbackmethod, $callbackdata, $rootnode)
  {
    $log = FezLog::get();

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
    // close element
    $callbackobject->$callbackmethod($domnode, $newcallbackdata, 'close', $rootnode);
    // recurse siblings
    Misc::XML_Walk($domnode->nextSibling, $callbackobject, $callbackmethod, $callbackdata, $rootnode);
  }

  /**
   * Removes any element prefixes (eg DC: etc) from an element name
   *
   * @access  public
   * @param   string $element_name
   * @return  array $element_name
   */
  function strip_element_name($element_name)
  {
    if ( is_numeric(strpos($element_name, "dc:")) ) {
      return $element_name;
    } else {
      $element_name_start = strpos($element_name, ":");
      if (is_numeric($element_name_start)) {
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
  function array_to_sql_string($array)
  {
    foreach ($array as &$item) {
      $item = "'".mysql_escape_string($item)."'";
    }
    return implode(', ', $array);
  }

  /**
   * makes an array into a string WITHOUT THE STRING QUOTES ready to be used in a MySQL query list
   * @param $array The array to be put in the query
   * @return string escaped mysql query element ready to be used like this WHERE x IN ($string)
   */
  function array_to_sql($array)
  {
    foreach ($array as &$item) {
      $item = mysql_escape_string($item);
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
    if (array_key_exists($next_key, $array)) {
      $return = Misc::getNextArrayKey($array, $next_key + 1);
    } else {
      $return = $next_key;
    }
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
  function dom_xsd_to_simple_array($domnode, &$array, $parentContent="")
  {
    $array_ptr = &$array;
    $domnode = $domnode->firstChild;
    while (!is_null($domnode)) {
      if (
          (strtolower($domnode->nodeName) != "xsd:annotation") &&
          (strtolower($domnode->nodeName) != "xsd:documentation")
      ) {
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
        if (strpos($domnode->nodeName, "schema") === 0) {
          Misc::dom_xsd_to_simple_array(
              $domnode, $array_ptr, $parentContent."!".$domnode->nodeName."^".$domobj->nodeValue
          );
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
  function getElementByNameValue($domnode, $elementname)
  {
    foreach ($domnode->getElementsByTagname("element") as $item ) {
      foreach ($item->attributes as $attrib ) {
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
  function getXMLObjectByTypeNameValue($domnode, $type, $elementname)
  {
    $result = $domnode->getElementsByTagname($type);
    foreach ($result as $item ) {
      foreach ($item->attributes as $attrib ) {
        if (($attrib->nodeName == "name") && ($attrib->nodeValue == $elementname)) {
          return $item;
          break;
        }
      }
    }
    if ($type == "complexType") { // check for simpleTypes just in case
      $result = $domnode->getElementsByTagname("simpleType");
      foreach ($result as $item ) {
        foreach ( $item->attributes as $attrib ) {
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
  function getSchemaAttributes($domnode, $top_element_name="", $element_prefix="", $xsd_extra_ns_prefixes=array())
  {
    $res = "";
    $nsURI = '';

    $currentnode = new DomDocument;
    $result = $domnode->getElementsByTagname('schema');
    if (count($result) == 1) {
      foreach ($result as $item ) {
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
   * Developer Note: This is a recursive function that traverses through the XML elements and attributes
   * using a DOM Document (PHP5).
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
  function dom_xsd_to_referenced_array(
      $domnode, $topelement, &$array, $parentnodename="", $searchtype="",
      $superdomnode=null, $supertopelement="", $parentContent="", $refCount = array()
  )
  {
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
            Misc::dom_xsd_to_referenced_array(
                $currentnode, $type, $array_ptr, $current_name, "complexType", $superdomnode,
                $supertopelement, $parentContent, $refCount
            );
          }
          if ($currentnode->hasChildNodes() ) {
            foreach ($currentnode->childNodes as $childnode) {
              Misc::dom_xsd_to_referenced_array(
                  $childnode, '', $array_ptr, $current_name, "", $superdomnode,
                  $supertopelement, $parentContent, $refCount
              );
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
        // Check if there is a ":" in the string if there is then snn is after the :
        if (is_numeric(strpos($currentnode->nodeName, ":"))) {
          $shortnodename = substr($currentnode->nodeName, (strpos($currentnode->nodeName, ":") + 1));
        } else {
          $shortnodename = $currentnode->nodeName;
        }

        if (
           ($shortnodename == $searchtype) && ($shortnodename <> "element")
            && ($shortnodename <> "attribute")
        ) {
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
              Misc::dom_xsd_to_referenced_array(
                  $childnode, "", $array_ptr, $parentnodename, "", $superdomnode,
                  $supertopelement, $parentContent, $refCount
              );
            }
          }
        } else if (
            ($shortnodename == "extension") || ($shortnodename == "any") || ($shortnodename == "anyAttribute") ||
            ($shortnodename == "restriction") || ($shortnodename == "group") || ($shortnodename == "complexContent") ||
            ($shortnodename == "simpleContent") || ($shortnodename == "attributeGroup") ||
            ($shortnodename == "attribute") || ($shortnodename == "enumeration")
        ) {
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
                Misc::dom_xsd_to_referenced_array(
                    $currentnode, $ref, $array_ptr, $current_name, $nextSearch, $superdomnode,
                    $supertopelement, $parentContent, $refCount
                );
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
          // added group and choice (also to above) to this if to test mods - ck
          if (($shortnodename == "attributeGroup") || ($shortnodename == "group")) {
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
              // Flag the ref for child parses so that it only recursives the same ref group once
              // (or it could go in an endless recursive loop) - CK added 13/9/2006
              if (array_key_exists($ref, $refCount)) {
                if ($refCount[$ref] == 1) { //
                  $refCount[$ref] = 2;
                  Misc::dom_xsd_to_referenced_array(
                      $currentnode, $ref, $array_ptr, $current_name, $shortnodename, $superdomnode,
                      $supertopelement, $parentContent, $refCount
                  );
                }
              } else {
                $refCount[$ref] = 1;
                Misc::dom_xsd_to_referenced_array(
                    $currentnode, $ref, $array_ptr, $current_name, $shortnodename,
                    $superdomnode, $supertopelement, $parentContent, $refCount
                );
              }
            }
          }

          if ($currentnode->hasChildNodes() ) {
            foreach ($currentnode->childNodes as $childnode) {
              Misc::dom_xsd_to_referenced_array(
                  $childnode, '', $array_ptr, $current_name, "", $superdomnode,
                  $supertopelement, $parentContent, $refCount
              );
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
            if (($current_name != $supertopelement) && ($current_name != "") && ($current_name != $parentnodename)) {
              $array_ptr = &$array[$current_name];
              $parentContent .= "!".$current_name;
              $array_ptr['fez_hyperlink'] = $parentContent;
            }
          }
          foreach ($current_refs as $ref) {
            Misc::dom_xsd_to_referenced_array(
                $currentnode, $ref, $array_ptr, $current_name, "", $superdomnode,
                $supertopelement, $parentContent, $refCount
            );
          }
          foreach ($current_types as $type) {
            Misc::dom_xsd_to_referenced_array(
                $currentnode, $type, $array_ptr, $current_name, "complexType",
                $superdomnode, $supertopelement, $parentContent, $refCount
            );
          }
          if ($currentnode->hasChildNodes() ) {
            foreach ($currentnode->childNodes as $childnode) {
              Misc::dom_xsd_to_referenced_array(
                  $childnode, '', $array_ptr, $current_name, "", $superdomnode,
                  $supertopelement, $parentContent, $refCount
              );
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
            Misc::dom_xsd_to_referenced_array(
                $currentnode, $ref, $array_ptr, $current_name, "", $superdomnode,
                $supertopelement, $parentContent, $refCount
            );
          }
          foreach ($current_types as $type) {
            Misc::dom_xsd_to_referenced_array(
                $currentnode, $type, $array_ptr, $current_name, "complexType", $superdomnode,
                $supertopelement, $parentContent, $refCount
            );
          }
          if ($currentnode->hasChildNodes() ) {
            foreach ($currentnode->childNodes as $childnode) {
              Misc::dom_xsd_to_referenced_array(
                  $childnode, '', $array_ptr, $current_name, "", $superdomnode,
                  $supertopelement, $parentContent, $refCount
              );
            }
          } else if (
              (count($current_refs) == 0) && (count($current_types) == 0) && ($current_name != $parentnodename)
          ) {
            if (($current_name != $supertopelement) && ($current_name != "")) {
              $array_ptr = &$array[$current_name];
              $parentContent .= "!".$current_name;
              $array_ptr['fez_hyperlink'] = $parentContent;
            }
          }
        }
          break;
      default:
        echo "in default case of node type (".$domnode->nodeType.", ".
              $domnode->nodeName.", ".$domnode->nodeValue.")<br />";
          break;
    }
  }

  function dom_xsd_to_flat_array(
      $domnode, $topelement, &$array, $parentnodename="", $searchtype="",
      $superdomnode=null, $supertopelement="", $parentContent="", $refCount = array()
  )
  {
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
            //$array_ptr = &$array[$current_name];
          }
          foreach ($current_types as $type) {
            Misc::dom_xsd_to_flat_array(
                $currentnode, $type, $array_ptr, $current_name, "complexType", $superdomnode,
                $supertopelement, $parentContent, $refCount
            );
          }
          if ($currentnode->hasChildNodes() ) {
            foreach ($currentnode->childNodes as $childnode) {
              Misc::dom_xsd_to_flat_array(
                  $childnode, '', $array_ptr, $current_name, "", $superdomnode,
                  $supertopelement, $parentContent, $refCount
              );
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
        // Check if there is a ":" in the string if there is then snn is after the :
        if (is_numeric(strpos($currentnode->nodeName, ":"))) {
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
              Misc::dom_xsd_to_flat_array(
                  $childnode, "", $array_ptr, $parentnodename, "", $superdomnode,
                  $supertopelement, $parentContent, $refCount
              );
            }
          }
        } else if (
            ($shortnodename == "extension") || ($shortnodename == "any") || ($shortnodename == "anyAttribute") ||
            ($shortnodename == "restriction") || ($shortnodename == "group") || ($shortnodename == "complexContent") ||
            ($shortnodename == "simpleContent") || ($shortnodename == "attributeGroup") ||
            ($shortnodename == "attribute") || ($shortnodename == "enumeration")
        ) {
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
                Misc::dom_xsd_to_flat_array(
                    $currentnode, $ref, $array_ptr, $current_name, $nextSearch, $superdomnode,
                    $supertopelement, $parentContent, $refCount
                );
              }
              if ($current_name <> $parentnodename) {
                //$array_ptr = &$array[$current_name];
                //$array_ptr['fez_hyperlink'] = $parentContent;
                if ($shortnodename == 'attribute') {
                  $array_ptr[$parentContent] = "attribute";
                  //$array_ptr['fez_nodetype'] = 'attribute';
                } elseif ($shortnodename == 'enumeration') {
                  $array_ptr[$parentContent] = "enumeration";
                  //$array_ptr['fez_nodetype'] = 'enumeration';
                }
              }
            }
          }
          // added group and choice (also to above) to this if to test mods - ck
          if (($shortnodename == "attributeGroup") || ($shortnodename == "group")) {
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
              // Flag the ref for child parses so that it only recursives the same ref group once (or it could go
              // in an endless recursive loop) - CK added 13/9/2006
              if (array_key_exists($ref, $refCount)) {
                if ($refCount[$ref] == 1) { //
                  $refCount[$ref] = 2;
                  Misc::dom_xsd_to_flat_array(
                      $currentnode, $ref, $array_ptr, $current_name, $shortnodename, $superdomnode,
                      $supertopelement, $parentContent, $refCount
                  );
                }
              } else {
                $refCount[$ref] = 1;
                Misc::dom_xsd_to_flat_array(
                    $currentnode, $ref, $array_ptr, $current_name, $shortnodename, $superdomnode,
                    $supertopelement, $parentContent, $refCount
                );
              }
            }
          }

          if ($currentnode->hasChildNodes() ) {
            foreach ($currentnode->childNodes as $childnode) {
              Misc::dom_xsd_to_flat_array(
                  $childnode, '', $array_ptr, $current_name, "", $superdomnode,
                  $supertopelement, $parentContent, $refCount
              );
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
            if (($current_name != $supertopelement) && ($current_name != "") && ($current_name != $parentnodename)) {
              //$array_ptr = &$array[$current_name];
              $parentContent .= "!".$current_name;
              //$array_ptr['fez_hyperlink'] = $parentContent;
              $array_ptr[$parentContent] = "element";
            }
          }
          foreach ($current_refs as $ref) {
            Misc::dom_xsd_to_flat_array(
                $currentnode, $ref, $array_ptr, $current_name, "", $superdomnode,
                $supertopelement, $parentContent, $refCount
            );
          }
          foreach ($current_types as $type) {
            Misc::dom_xsd_to_flat_array(
                $currentnode, $type, $array_ptr, $current_name, "complexType", $superdomnode,
                $supertopelement, $parentContent, $refCount
            );
          }
          if ($currentnode->hasChildNodes() ) {
            foreach ($currentnode->childNodes as $childnode) {
              Misc::dom_xsd_to_flat_array(
                  $childnode, '', $array_ptr, $current_name, "", $superdomnode,
                  $supertopelement, $parentContent, $refCount
              );
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
            Misc::dom_xsd_to_flat_array(
                $currentnode, $ref, $array_ptr, $current_name, "", $superdomnode,
                $supertopelement, $parentContent, $refCount
            );
          }
          foreach ($current_types as $type) {
            Misc::dom_xsd_to_flat_array(
                $currentnode, $type, $array_ptr, $current_name, "complexType", $superdomnode,
                $supertopelement, $parentContent, $refCount
            );
          }
          if ($currentnode->hasChildNodes() ) {
            foreach ($currentnode->childNodes as $childnode) {
              Misc::dom_xsd_to_flat_array(
                  $childnode, '', $array_ptr, $current_name, "", $superdomnode,
                  $supertopelement, $parentContent, $refCount
              );
            }
          } elseif ((count($current_refs) == 0) && (count($current_types) == 0) && ($current_name != $parentnodename)) {
            if (($current_name != $supertopelement) && ($current_name != "")) {
              //$array_ptr = &$array[$current_name];
              $parentContent .= "!".$current_name;
              //$array_ptr['fez_hyperlink'] = $parentContent;
              $array_ptr[$parentContent] = "element";
            }
          }
        }
          break;
      default:
        echo "in default case of node type (".$domnode->nodeType.", ".$domnode->nodeName.", ".
            $domnode->nodeValue.")<br />";
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
  function in_multi_array($needle, $haystack)
  {
    $in_multi_array = false;
    if (in_array($needle, $haystack)) {
      $in_multi_array = true;
    } else {
      foreach ($haystack as $key => $val) {
        if (is_array($val)) {
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
  function array_flatten(&$a,$pref='', $ignore_keys = false)
  {
    $ret=array();
    if (!is_array($a)) {
      return array($a);
    }
    foreach ($a as $i => $j) {
      if (is_array($j)) {
        if (!$ignore_keys) {
          $ret=array_merge($ret, Misc::array_flatten($j, $pref.$i."|"));
          $ret[$pref.$i] = $i;
        } else {
          $ret=array_merge($ret, Misc::array_flatten($j, '', $ignore_keys));
        }
      } else {
        if (!$ignore_keys) {
          $ret[$pref.$i] = $j;
        } else {
          $ret[] = $j;
        }
      }
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
  function getSchemaSubAttributes($a, $top_element_name, $xdis_id, $pid)
  {
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
                $res .= ' '.$i.'="'.$_POST['xsd_display_fields'][$xsdmf_id].'" ';
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
  function getPostedDate($xsdmf_id)
  {
    $return = array();
    $dateType = 0; // full date by default
    if ((!empty($_POST['xsd_display_fields'][$xsdmf_id]['Year'])) &&
    (!empty($_POST['xsd_display_fields'][$xsdmf_id]['Month'])) &&
    (!empty($_POST['xsd_display_fields'][$xsdmf_id]['Day']))) {
      $return['value'] = sprintf(
          '%04d-%02d-%02d', $_POST['xsd_display_fields'][$xsdmf_id]['Year'],
          $_POST['xsd_display_fields'][$xsdmf_id]['Month'],
          $_POST['xsd_display_fields'][$xsdmf_id]['Day']
      );
    } else if (
        (!empty($_POST['xsd_display_fields'][$xsdmf_id]['Year'])) &&
        (!empty($_POST['xsd_display_fields'][$xsdmf_id]['Month']))
    ) {
      $return['value'] = sprintf(
          '%04d-%02d', $_POST['xsd_display_fields'][$xsdmf_id]['Year'],
          $_POST['xsd_display_fields'][$xsdmf_id]['Month']
      );
      $dateType = 2;	// year and month
    } else if (!empty($_POST['xsd_display_fields'][$xsdmf_id]['Year'])) {
      $return['value'] = sprintf('%04d', $_POST['xsd_display_fields'][$xsdmf_id]['Year']);
      $dateType = 1; // year only
    } else {
      $return['value'] = '';
    }
    $return['dateType'] = $dateType;
    //		$return['value'] = $_POST['xsd_display_fields'][$xsdmf_id];
    return $return;
  }


  /**
   * Method used to create an array to be used in an Javascript DTree (dynamic tree) for XSD HTML
   * to Matching Form elements.
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
  function array_to_dtree(
      $a, $xdis_id=0, $element_match_list=array(), $counter=0, $parent_counter=-1, &$open_array = array()
  )
  {
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
              } else if ($j['fez_nodetype'] == 'enumeration') {
                $dtree_image = ", '../images/dtree/enumeration.gif'";
              }
            }
            if (isset($j['fez_hyperlink']) && !is_array($j['fez_hyperlink'])) {
              $ehref = $j['fez_hyperlink'];
              $node_label = $i;
              // make the tree node bold if there is a matchfields entry (i.e. we are using it)

              if (array_key_exists($ehref, $element_match_list)) {
                $node_label = "<b>$node_label</b>";
                //								if (array_key_exists(0, $element_match_list[$ehref])) {
                if (!is_array($element_match_list[$ehref])) {

                } else {
                  // only make the title red if there is only one xsdmf for this element.
                  // Otherwise it is misleading because it suggests that all of the sublooping elements
                  // are disabled when it's probably only one. The hover note shows the disabled thing per
                  // sublooping element.
                  if (count($element_match_list[$ehref]) == 1
                  && $element_match_list[$ehref][0]['xsdmf_enabled'] == 0
                  && is_numeric($element_match_list[$ehref][0]['xsdmf_enabled'])) {
                    $node_label = "<font color=\'red\'>$node_label</font>";
                  }
                  foreach ($element_match_list[$ehref] as $ematch) {
                    $disabled_msg = "";
                    if ($ematch['xsdmf_enabled'] == 0 && is_numeric($ematch['xsdmf_enabled'])) {
                      $disabled_msg = "<font color=\'red\'><b>DISABLED</b></font><br />";
                    }
                    switch ($ematch['xsdmf_html_input']) {
                      case "xsd_loop_subelement":
                        $node_label .= ' <img title="Sublooping Element" src="'.APP_RELATIVE_URL.
                            'images/sel_16.png" />';
                          break;
                      case "date":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Date Selector:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/date_16.png" />';
                          break;
                      case "text":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Text Input:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/text_16.png" />';
                          break;
                      case "hidden":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Text Input:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/hidden_16.png" />';
                          break;
                      case "contvocab_selector":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Controlled Vocabulary Selector:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.'images/contvocab_16.png" />';
                          break;
                      case "author_selector":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Author Selector:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/author_selector_16.png" />';
                          break;
                      case "author_suggestor":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Author Suggestor:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/author_suggestor_16.png" />';
                          break;
                      case "publisher_suggestor":
                          $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                              $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                              '<b>Author Suggestor:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                              '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                              '</span><img src="'.APP_RELATIVE_URL.'images/author_suggestor_16.png" />';
                          break;
                      case "conference_suggestor":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                                $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                                '<b>Author Suggestor:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                                '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                                '</span><img src="'.APP_RELATIVE_URL.'images/author_suggestor_16.png" />';
                          break;
                      case "static":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Static Text:</b> '.$ematch['xsdmf_static_text'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>XSDMF ID: '.$ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.
                            'images/static_16.png" />';
                          break;
                      case "org_selector":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Organisational Structure Selector:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.'images/org_selector_16.png" />';
                          break;
                      case "file_input":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>File Input:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/file_input_16.png" />';
                          break;
                      case "xsdmf_id_ref":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>XSDMF ID Reference:</b> '.$ematch['xsdmf_id_ref'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span> <img src="'.APP_RELATIVE_URL.'images/xsdmf_id_ref_16.png" />';
                          break;
                      case "xsd_ref":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>XSD Display Reference:</b> <br/>Loop: '.$ematch['xsdsel_title'].'<br/>Order: '.
                            $ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].'</span> <img src="'.
                            APP_RELATIVE_URL.'images/xsd_ref_16.png" />';
                          break;
                      case "textarea":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Text Area:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span> <img src="'.APP_RELATIVE_URL.'images/form_16.png" />';
                          break;
                      case "combo":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Combo Box:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span> <img src="'.APP_RELATIVE_URL.'images/combobox_16.png" />';
                          break;
                      case "multiple":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Multiple Combo Box:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/multi_combobox_16.png" />';
                          break;
                      case "dual_multiple":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Dual Multiple Combo Box:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.'images/dual_multiple_16.png" />';
                          break;
                      case "checkbox":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Check Box:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/checkbox_16.png" />';
                          break;
                      case "dynamic":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Dynamic variable value:</b> '.$ematch['xsdmf_dynamic_text'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.'images/dynamic_16.png" />';
                          break;
                      case "rich_text":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Rich Text Editor:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/rich_text_16.png" />';
                          break;
                      case "depositor_org":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Depositor Affiliation:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.'images/depositor_org_16.png" />';
                          break;
                      case "open_access_suggest":
                         $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Open Access Suggestor:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.
                            'images/open_access_suggest_16.png" />';
                          break;
                      default:
                          break;
                    }
                  }
                }
                if (!array_key_exists($parent_counter, $open_array)) {
                  $open_array[$parent_counter] = "tree.openTo(".$parent_counter.", false, false);\n";
                }
              }
              $ehref = urlencode($ehref);
              $ret[1] .= "tree.add(".$counter.", ".$parent_counter.", '".$node_label."', "
              ."'".$match_form_url.$ehref."', '', 'basefrm'".$dtree_image.");\n";
            } else {
              $ret[1] .= "tree.add(".$counter.", ".$parent_counter.", '$i');\n";
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
        if (
            ($i != '#text') && ($i != '#comment') && ($i != 'fez_nodetype') && ($i != 'fez_hyperlink') &&
            (!(is_array($i['fez_hyperlink'])))
        ) {
          if (!empty($j['fez_nodetype'])) {
            if ($j['fez_nodetype'] == 'attribute') {
              $dtree_image = ", '../images/dtree/attribute.gif'";
            } elseif ($j['fez_nodetype'] == 'enumeration') {
              $dtree_image = ", '../images/dtree/enumeration.gif'";
            }
          }
          $ehref = $j['fez_hyperlink'];
          $ehref = urlencode($ehref);
          $ret[1] .= "tree.add(".$counter.", ".$parent_counter.", '$i', '".$match_form_url.$ehref.
                      "', '', 'basefrm'".$dtree_image.");\n";
        }
        $counter = $counter + 1;
      }
      $ret[0] = $counter;
    }
    $ret[2] = array_values($open_array);
    return $ret;
  }


  function findNodeTypes(
      $a, $xdis_id=0, $element_match_list=array(), $counter=0, $parent_counter=-1, &$open_array = array()
  )
  {
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
              } else if ($j['fez_nodetype'] == 'enumeration') {
                $dtree_image = ", '../images/dtree/enumeration.gif'";
              }
            }
            if (isset($j['fez_hyperlink']) && !is_array($j['fez_hyperlink'])) {
              $ehref = $j['fez_hyperlink'];
              $node_label = $i;
              // make the tree node bold if there is a matchfields entry (i.e. we are using it)

              if (array_key_exists($ehref, $element_match_list)) {
                $node_label = "<b>$node_label</b>";
                if (!is_array($element_match_list[$ehref])) {

                } else {
                  // only make the title red if there is only one xsdmf for this element.
                  // Otherwise it is misleading because it suggests that all of the sublooping elements
                  // are disabled when it's probably only one. The hover note shows the disabled thing per
                  // sublooping element.
                  if (
                    count($element_match_list[$ehref]) == 1
                      && $element_match_list[$ehref][0]['xsdmf_enabled'] == 0
                      && is_numeric($element_match_list[$ehref][0]['xsdmf_enabled'])
                  ) {
                    $node_label = "<font color=\'red\'>$node_label</font>";
                  }
                  foreach ($element_match_list[$ehref] as $ematch) {
                    $disabled_msg = "";
                    if ($ematch['xsdmf_enabled'] == 0 && is_numeric($ematch['xsdmf_enabled'])) {
                      $disabled_msg = "<font color=\'red\'><b>DISABLED</b></font><br />";
                    }
                    switch ($ematch['xsdmf_html_input']) {
                      case "xsd_loop_subelement":
                        $node_label .= ' <img title="Sublooping Element" src="'.APP_RELATIVE_URL.
                            'images/sel_16.png" />';
                          break;
                      case "date":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Date Selector:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/date_16.png" />';
                          break;
                      case "text":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Text Input:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/text_16.png" />';
                          break;
                      case "hidden":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Text Input:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/hidden_16.png" />';
                          break;
                      case "contvocab_selector":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Controlled Vocabulary Selector:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.'images/contvocab_16.png" />';
                          break;
                      case "author_selector":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Author Selector:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/author_selector_16.png" />';
                          break;
                      case "author_suggestor":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Author Suggestor:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/author_suggestor_16.png" />';
                          break;
                      case "publisher_suggestor":
                          $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                              $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                              '<b>Author Suggestor:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                              '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                              '</span><img src="'.APP_RELATIVE_URL.'images/author_suggestor_16.png" />';
                            break;
                      case "conference_suggestor":
                            $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                                $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                                '<b>Author Suggestor:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                                '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                                '</span><img src="'.APP_RELATIVE_URL.'images/author_suggestor_16.png" />';
                              break;
                      case "static":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Static Text:</b> '.$ematch['xsdmf_static_text'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>XSDMF ID: '.$ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.
                            'images/static_16.png" />';
                          break;
                      case "org_selector":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Organisational Structure Selector:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.'images/org_selector_16.png" />';
                          break;
                      case "file_input":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>File Input:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/file_input_16.png" />';
                          break;
                      case "xsdmf_id_ref":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>XSDMF ID Reference:</b> '.$ematch['xsdmf_id_ref'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span> <img src="'.APP_RELATIVE_URL.'images/xsdmf_id_ref_16.png" />';
                          break;
                      case "xsd_ref":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>XSD Display Reference:</b> <br/>Loop: '.$ematch['xsdsel_title'].'<br/>Order: '.
                            $ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].'</span> <img src="'.
                            APP_RELATIVE_URL.'images/xsd_ref_16.png" />';
                          break;
                      case "textarea":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Text Area:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span> <img src="'.APP_RELATIVE_URL.'images/form_16.png" />';
                          break;
                      case "combo":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Combo Box:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span> <img src="'.APP_RELATIVE_URL.'images/combobox_16.png" />';
                          break;
                      case "multiple":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Multiple Combo Box:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/multi_combobox_16.png" />';
                          break;
                      case "dual_multiple":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Dual Multiple Combo Box:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.'images/dual_multiple_16.png" />';
                          break;
                      case "checkbox":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Check Box:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/checkbox_16.png" />';
                          break;
                      case "dynamic":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Dynamic variable value:</b> '.$ematch['xsdmf_dynamic_text'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.'images/dynamic_16.png" />';
                          break;
                      case "rich_text":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Rich Text Editor:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.$ematch['xsdsel_title'].
                            '<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.$ematch['xsdmf_id'].
                            '</span><img src="'.APP_RELATIVE_URL.'images/rich_text_16.png" />';
                          break;
                      case "depositor_org":
                        $node_label .= '</a> <a target="basefrm" href="'.$match_form_url.$ehref.'&xsdsel_id='.
                            $ematch["xsdmf_xsdsel_id"].'" class="form_note"> <span class="form_note">'.$disabled_msg.
                            '<b>Depositor Affiliation:</b> '.$ematch['xsdmf_title'].'<br/>Loop: '.
                            $ematch['xsdsel_title'].'<br/>Order: '.$ematch['xsdmf_order'].'<br/>XSDMF ID: '.
                            $ematch['xsdmf_id'].'</span><img src="'.APP_RELATIVE_URL.'images/depositor_org_16.png" />';
                          break;
                      default:
                          break;
                    }
                  }
                }
                if (!array_key_exists($parent_counter, $open_array)) {
                  $open_array[$parent_counter] = "tree.openTo(".$parent_counter.", false, false);\n";
                }
              }
              $ehref = urlencode($ehref);
              $ret[1] .= "tree.add(".$counter.", ".$parent_counter.", '".$node_label."', "
              ."'".$match_form_url.$ehref."', '', 'basefrm'".$dtree_image.");\n";
            } else {
              $ret[1] .= "tree.add(".$counter.", ".$parent_counter.", '$i');\n";
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
        if (
            ($i != '#text') && ($i != '#comment') && ($i != 'fez_nodetype') && ($i != 'fez_hyperlink') &&
            (!(is_array($i['fez_hyperlink'])))
        ) {
          if (!empty($j['fez_nodetype'])) {
            if ($j['fez_nodetype'] == 'attribute') {
              $dtree_image = ", '../images/dtree/attribute.gif'";
            } else if ($j['fez_nodetype'] == 'enumeration') {
              $dtree_image = ", '../images/dtree/enumeration.gif'";
            }
          }
          $ehref = $j['fez_hyperlink'];
          $ehref = urlencode($ehref);
          $ret[1] .= "tree.add(".$counter.", ".$parent_counter.", '$i', '".$match_form_url.$ehref."', '', 'basefrm'".
              $dtree_image.");\n";
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
  function isInt ($x)
  {
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
    if (!is_array($source)) {
      Error_Handler::logError("Not an array", __FILE__, __LINE__);
      return null;
    }
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
    return @$_GET[$key] ? @$_GET[$key] : @$_POST[$key];
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
    $allvars = array_merge($_GET, $_POST);
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
    $log = FezLog::get();
    $db = DB_API::get();

    if (is_array($a)) {
      $b = $a;
      foreach ($a as $key => $value) {
        $b[$key] = $db->quote($value);
      }
      return implode(',', $b);
    }
    return '';
  }

  public static function arrayToSQLBindStr($a)
  {
    $bind = '';

    if (is_array($a)) {
      $bind = @implode(', ', array_fill(0, count($a), '?'));
    } else {
      $bind = '?';
    }
    return $bind;
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
  function stripOneElementArrays($a)
  {
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
    if ((!Misc::isInt($pid) || $pid > 0) && !empty($pid)) {
      return true;
    } else {
      return false;
    }
  }


  function shortFilename($filename, $maxlen)
  {
    $pathstuff = pathinfo($filename);
    $filename = basename($pathstuff['basename'], ".".$pathstuff['extension']);
    $new_filename = substr($filename, 0, $maxlen - strlen($pathstuff['extension']) - 1)
    .".".$pathstuff['extension'];
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
      $output .= "<b>file:</b> ".$bt['line']." - ".$bt['file']."<br />\n";
      $output .= "<b>call:</b> ".$bt['class'].$bt['type'].$bt['function'].($args)."<br />\n";
    }
    $output .= "</div>\n";
    return $output;
  }

  function arraySearchReplace(&$a, $keys, $map)
  {
    foreach ($keys as $field) {
      if (!empty($a[$field])) {
        if (!empty($map[$a[$field]])) {
          $a[$field] = $map[$a[$field]];
        }
      }
    }
  }

  function tableSearchAndReplace($table, $fields, $map, $restrict, $debug = false)
  {
    $log = FezLog::get();
    $db = DB_API::get();

    $params = array(print_r($table, true),print_r($fields, true),print_r($map, true),print_r($restrict, true));
    $log->debug(array(print_r($params, true), __FILE__, __LINE__));

    $wrote = array();
    foreach ($map as $xvalue => $dbvalue) {
      if ($dbvalue != $xvalue) {
        foreach ($fields as $field) {
          if (in_array($xvalue, $wrote[$field])) {
            $log->err(array("DOH!!!!",__FILE__,__LINE__));
          }
          $stmt = "UPDATE ".APP_SQL_DBNAME . "." . APP_TABLE_PREFIX."$table " .
                            "SET $field=".$db->quote($dbvalue)." " .
                            "WHERE $field=".$db->quote($xvalue)." AND $restrict";
          try {
            $db->exec($stmt);
          }
          catch(Exception $ex) {
            $log->err($ex);
          }
          $wrote[$field][] = $dbvalue;
        }
      }
    }
  }

  function convertSize($str)
  {
    $log = FezLog::get();
    // str is number followed by letter
    $res = $str;
    if (preg_match('/(\d+)([KMG])/i', $str, $matches)) {
      $number = $matches[1];
      $letter = $matches[2];
      switch (strtoupper($letter)) {
        case 'K':
          $number *= 1024;
            break;
        case 'M':
          $number *= 1048576;
            break;
        case 'G':
          $number *= 1048576000;
            break;
        default:
          $log->err(array("Can't convert '".$letter."' to a number",__FILE__,__LINE__));
            break;
      }
      $res = $number;
    }
    return $res;
  }

  function fileUploadErr($e)
  {
    $errs = array(
        0=>"There is no error, the file uploaded with success",
        1=>"The uploaded file was too big", // "The uploaded file exceeds the upload_max_filesize directive in php.ini"
        2=>"The uploaded file was too big", // "The uploaded file exceeds the MAX_FILE_SIZE directive that was
                                            //  specified in the HTML form",
        3=>"The uploaded file was only partially uploaded",
        4=>"No file was uploaded",
        6=>"Missing a temporary folder"
    );
    if (!isset($errs[$e])) {
      return "The reason for the failure is unknown";
    }
    return @$errs[$e];
  }

  function MySQLDate($dateArray)
  {
      $dteSql = array();
      $dteSql[] = $dateArray['Year'];
      $dteSql[] = (isset($dateArray['Month'])) ? str_pad($dateArray['Month'], 2, '0', STR_PAD_LEFT) : '00';
      $dteSql[] = (isset($dateArray['Day'])) ? str_pad($dateArray['Day'], 2, '0', STR_PAD_LEFT) : '00';
      $dteSql = implode('-', $dteSql) . ' 00:00:00';

      return $dteSql;
  }

  function MySQLTZ($s)
  {
    $tz = new Date_TimeZone($s);
    $offset = intval($tz->getRawOffset() / 60000 + 0.5);  // discard the seconds and milliseconds part
    if ($offset >= 0) {
      $sign = '+';
    } else {
      $sign = '';
    }
    return sprintf("%s%02d:%02d", $sign, intval($offset / 60), abs($offset % 60));

  }

  function endsWith($haystack, $needle)
  {
    if (strrpos($haystack, $needle) == (strlen($haystack) - strlen($needle)) ) {
      return true;
    }
    return false;
  }

  /**
   * generateAlphabetArray
   *
   * Builds an array containing capital letters A-Z. This method is for printing a list of letters to allow
   * filtration of results by first letter. We may one day want to augment this to include special characters.
   */
  function generateAlphabetArray()
  {
    $alphabetArray = array();
    for ($i = 65; $i <= 90; $i++) {
      array_push($alphabetArray, chr($i));
    }
    return $alphabetArray;
  }

  function array_last($a)
  {
    if (is_array($a) && !empty($a)) {
      return $a[count($a) - 1];
    } else {
      return null;
    }
  }

  function isPid($str)
  {
    if (is_array($str)) {
      return false;
    }
    if (preg_match('/\w+:\d+/', $str) === 1) {
      return true;
    }
    return false;
  }

  function HSV2RGB($H,$S,$V)
  {
    $H = $H / 255;
    $S = $S / 255;
    $V = $V / 255;
    if ( $S == 0 ) {
      $R = $V * 255;
      $G = $V * 255;
      $B = $V * 255;
    } else {
      $var_h = $H * 6;
      if ( $var_h == 6 ) {
        $var_h = 0;      //H must be < 1
      }
      $var_i = intval($var_h); // Or ... var_i = floor( var_h )
      $var_1 = $V * ( 1 - $S );
      $var_2 = $V * ( 1 - $S * ( $var_h - $var_i ) );
      $var_3 = $V * ( 1 - $S * ( 1 - ( $var_h - $var_i ) ) );
      if ( $var_i == 0 ) {
        $var_r = $V; $var_g = $var_3 ; $var_b = $var_1;
      } else if ( $var_i == 1 ) {
        $var_r = $var_2 ; $var_g = $V     ; $var_b = $var_1;
      } else {
        if ( $var_i == 2 ) {
          $var_r = $var_1 ; $var_g = $V     ; $var_b = $var_3;
        } else if ( $var_i == 3 ) {
          $var_r = $var_1 ; $var_g = $var_2 ; $var_b = $V;
        } else if ( $var_i == 4 ) {
          $var_r = $var_3 ; $var_g = $var_1 ; $var_b = $V;
        } else {
          $var_r = $V     ; $var_g = $var_1 ; $var_b = $var_2;
        }
      }
      $R = $var_r * 255;                  //RGB results = 0 ÷ 255
      $G = $var_g * 255;
      $B = $var_b * 255;
    }
    return array(intval($R),intval($G),intval($B));
  }

  /**
   * Unzip the source_file in the destination dir
   *
   * @param   string      The path to the ZIP-file.
   * @param   string      The path where the zipfile should be unpacked, if false the directory of the zip-file is used
   * @param   boolean     Indicates if the files will be unpacked in a directory with the name of the
   *                      zip-file (true) or not (false) (only if the destination directory is set to false!)
   * @param   boolean     Overwrite existing files (true) or not (false)
   *
   * @return  boolean     Succesful or not
   */
  function unzip($src_file, $dest_dir=false, $create_zip_name_dir=true, $overwrite=true)
  {
    $files = array();
    if (function_exists("zip_open")) {
      if (!is_resource(zip_open($src_file))) {
        $src_file=dirname($_SERVER['SCRIPT_FILENAME'])."/".$src_file;
      }

      if (is_resource($zip = zip_open($src_file))) {
        $splitter = ($create_zip_name_dir === true) ? "." : "/";
        if ($dest_dir === false) {
          $dest_dir = substr($src_file, 0, strrpos($src_file, $splitter))."/";
        }

        // Create the directories to the destination dir if they don't already exist
        Misc::create_dirs($dest_dir);

        // For every file in the zip-packet
        while ($zip_entry = zip_read($zip)) {
          // Now we're going to create the directories in the destination directories

          // If the file is not in the root dir
          $pos_last_slash = strrpos(zip_entry_name($zip_entry), "/");
          if ($pos_last_slash !== false) {
            // Create the directory where the zip-entry should be saved (with a "/" at the end)
            Misc::create_dirs($dest_dir.substr(zip_entry_name($zip_entry), 0, $pos_last_slash+1));
          }

          // Open the entry
          if (zip_entry_open($zip, $zip_entry, "r")) {
            // The name of the file to save on the disk
            $file_name = $dest_dir.zip_entry_name($zip_entry);

            // Check if the files should be overwritten or not
            if ($overwrite === true || $overwrite === false && !is_file($file_name)) {
              // Get the content of the zip entry
              $fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

              if (!is_dir($file_name)) {
                file_put_contents($file_name, $fstream);
              }
              // Set the rights
              if (file_exists($file_name)) {
                chmod($file_name, 0777);
                array_push($files, $file_name);
                // echo "<span style=\"color:#1da319;\">file saved: </span>".$file_name."<br />";
              } else {
                // echo "<span style=\"color:red;\">file not found: </span>".$file_name."<br />";
              }
            }

            // Close the entry
            zip_entry_close($zip_entry);
          }
        }
        // Close the zip-file
        zip_close($zip);
      } else {
        return false;
      }

      return $files;
    } else {
      if (version_compare(phpversion(), "5.2.0", "<")) {
        $infoVersion="(use PHP 5.2.0 or later)";
      }
      echo "You need to install/enable the php_zip.dll extension $infoVersion";
    }
  }

  /**
  * checks for user agent whether this is firefox or not
  * @param void
  * @return bool
  * @author svetoslavm##gmail.com
  * @link http://devquickref.com/
  */
  function is_firefox()
  {
    $agent = '';
    // newer versions of php do have useragent here.
    if (empty($agent) && !empty($_SERVER["HTTP_USER_AGENT"])) {
      $agent = $_SERVER["HTTP_USER_AGENT"];
    }
    if (!empty($agent) && preg_match("/firefox/si", $agent)) {
      return true;
    }
    return false;
  }

  function create_dirs($path)
  {
    if (!is_dir($path)) {
      $directory_path = "";
      $directories = explode("/", $path);
      array_pop($directories);

      foreach ($directories as $directory) {
        $directory_path .= $directory."/";
        if (!is_dir($directory_path)) {
          mkdir($directory_path);
          chmod($directory_path, 0777);
        }
      }
    }
  }

  /**
   * Build a URL from array
   *
   * @param array  $query     array of variables to create URL
   * @param array  $exclude   if any values exist in query do put them in URL
   * @param string $parent    array parent name
   *
   * @return string  encoded url
   *
   * @access public
   */
  function query_string_encode($query, $exclude = array(), $parent = '')
  {
    $params = array();
    foreach ($query as $key => $value) {
      $key = urlencode($key);
      if ($parent) {
        $key = $parent .'['. $key .']';
      }

      if (in_array($key, $exclude)) {
        continue;
      }

      if (is_array($value)) {
        $params[] = Misc::query_string_encode($value, $exclude, $key);
      } else {
        if (!empty($value) || trim($value) != '') {
          $params[] = $key .'='. urlencode($value);
        }
      }
    }

    return implode('&', $params);
  }

  /**
   * Check that a variable is off a certain type
   *
   * @param string $variable  variable to check
   * @param string $type      type to check against
   *
   * @return string  return variable if it passes check
   *
   * @access public
   */
  function sanity_check($variable, $type)
  {
    if (!isset($variable)) {
      return false;
    }
    $checkFunction = 'is_' . $type;
    if (!$checkFunction($variable)) {
      return false;
    }
    return $variable;
  }

  function redirect($url, $die = true)
  {
    $log = FezLog::get();
    $db = DB_API::get();
    FezLog::get()->close();

    header('Location: ' . $url);
    if ($die) {
      exit;
    }
  }

  /**
   * OpenURL() constructs an NISO Z39.88 compliant ContextObject for use in OpenURL links and COinS.  It returns
   * the proper query string, which you must embed in a <span></span> thus:   *
   * <span class="Z3988" title="<?php print OpenURL($Document, $People) ?>">Content of your choice goes here</span>
   *
   * This span will work with Zotero. You can also use the output of OpenURL() to link to your library's
   * OpenURL resolver, thus:
   * <a href="http://www.lib.utexas.edu:9003/sfx_local?<?php print OpenURL($Document, $People); ?>"
   *    title="Search for a copy of this document in UT's libraries">Find it at UT!</a>
   *
   * Replace "http://www.lib.utexas.edu:9003/sfx_local?" with the correct resolver for your library.
   *
   * OpenURL() takes two arguments.
   *
   * $Document - a document object, having an array (fields) with the following properties:
   * $row["DocType"]
   *   1 = Article
   *   2 = Book Item (e.g. a chapter, section, etc)
   *   3 = Book
   *   4 = Unpublished MA thesis
   *   5 = Unpublished PhD thesis
   *
   * $row["DocTitle"] - Title of the document.
   * $row["JournalTitle"] - Title of the journal/magazine the article was published in, or false if
   *                        this is not an article.
   *
   * $row["BookTitle"] - Title of the book in which this item was published, or false if this is not a book item.
   *
   * $row["Volume"] - The volume of the journal this article was published in as an integer, or false if this is not
   *                  an article.  Optional.
   * $row["JournalIssue"] - The issue of the journal this article was published in as an integer, or false if this is
   *                        not an article.  Optional.
   * $row["JournalSeason"] Optional.
   *   The season of the journal this article was published in, as a string, where:
   *     Spring
   *     Summer
   *     Fall
   *     Winter
   *     false = not applicable
   * $row["JournalQuarter"] - The quarter of the journal this article was published in as an integer between
   *                          1 and 4, or false. Optional.
   * $row["ISSN"] - The volume of the journal this article was published in, or false.  Optional.
   *
   * $row["BookPublisher"] - The publisher of the book, or false. Optional.
   * $row["PubPlace"] - The publication place, or false.  Optional.
   * $row["ISBN"] - The ISBN of the book.  Optional but highly recommended.
   *
   * $row["StartPage"] - Start page for the article or item, or false if this is a complete book.
   * $row["EndPage"] - End page for the article or item, or false if this is a complete book.
   *
   * $row["DocYear"] - The year in which this document was published.
   * $People - An array of person objects, each having an array, fields, with these properties:
   * $People->fields["DocRelationship"]
   *   An integer indicating what kind of relationship the person has to this document.
   *   0 = author
   *   1 = editor
   *   2 = translator
   * $People->fields["FirstName"] - The person's first name.
   * $People->fields["LastName"] - The person's last name.
   */
  function OpenURL($row)
  {
    // Base of the OpenURL specifying which version of the standard we're using.
    $URL = "ctx_ver=Z39.88-2004";
    $DocType = $row["rek_display_type_lookup"];
    // Metadata format - e.g. article or book.
    if ($DocType == "Journal Article") {
      $URL .= "&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal";
    }
    if ($DocType  == "Book") {
      $URL .= "&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Abook";
    }
    // An ID for your application.  Replace yoursite.com and specify a name for your application.
    $URL .= "&amp;rfr_id=info%3Asid%2F".APP_HOSTNAME."%3A".APP_NAME;
    // Document Genre
    $URL .= "&amp;rft.genre=".str_replace(" ", "", strtolower($DocType));
    // Document Title
    $URL .= "&amp;rft.atitle=".urlencode($row["rek_title"]);
    // Publication Title
    if ($DocType == "Journal Article") {
      $URL .= "&amp;rft.jtitle=".urlencode($row["rek_journal_name"]);
    }
    if ($DocType == "Book Chapter") {
      $URL .= "&amp;rft.btitle=".urlencode($row["rek_book_title"]);
    }
    // Volume, Issue, Season, Quarter, and ISSN (for journals)
    if ($DocType == "Journal Article") {
      if ($row["rek_volume"]) {
          $URL .= "&amp;rft.volume=".urlencode($row["rek_volume"]);
      }
      if ($row["rek_issue_number"]) {
          $URL .= "&amp;rft.issue=".urlencode($row["rek_issue_number"]);
      }
      if ($row["rek_issn"]) {
        $URL .= "&amp;rft.quarter=".urlencode($row["rek_issn"]);
      }
    }
    // Publisher, Publication Place, and ISBN (for books)
    if ($DocType == "Book") {
      $URL .= "&amp;rft.pub=".urlencode($row["rek_publisher"]);
      $URL .= "&amp;rft.place=".urlencode($row["rek_place_of_publication"]);
      $URL .= "&amp;rft.isbn=".urlencode($row["rek_isbn"]);
    }
    if ($row["rek_start_page"]) {
      $URL .= "&amp;rft.spage=".urlencode($row["rek_start_page"]);
    }
    if ($row["rek_end_page"]) {
      $URL .= "&amp;rft.epage=".urlencode($row["rek_end_page"]);
    }
    // Publication year.
    $URL .= "&amp;rft.date=".date("Y", strtotime($row["rek_date"]));
    // Authors
    $i = 0;
    while ($row["rek_author"][$i]) {
      $URL .= "&amp;rft.au=".urlencode($row["rek_author"][$i]);
      $i++;
    }
    return $URL;
  }

  /**
   * Generates a password
   *
   * @param $num_alpha The number of alpha characters to include in the password
   * @param $num_numeric The number of numerals to include
   * @param $num_symbols The number of symbols to include
   * @return string The generated password
   */
  public static function generatePassword($num_alpha = 8, $num_numeric = 0, $num_symbols=0)
  {
    $list_alpha = 'bcdfghjkmnpqrstvwxyzBCFGHJKMNPQRSTVWXYZ';
    $list_numeric = '0123456789';
    $list_symbols = '!@#$%^*()~`{}[]|\&_';

    return str_shuffle(
        substr(str_shuffle($list_alpha), 0, $num_alpha) .
        substr(str_shuffle($list_numeric), 0, $num_numeric) .
        substr(str_shuffle($list_symbols), 0, $num_symbols)
    );
  }

  /**
   * Adds the record to Fedora
   *
   * @param $record The WoS record to add to Fedora
   * @param $author_id (OPTIONAL) The author id of one of the authors on the records
   *
   * @return bool True if succeeded otherwise false
   */
  public static function convertEstiRecordToMods($record, $author_id = false)
  {
    $log = FezLog::get();

    // Get the ESTI document type to eSpace type and eSpace sub-type mappings
    $doc_type_mappings = Thomson_Doctype_Mappings::getList('ESTI');
    foreach ($doc_type_mappings as $mapping) {
      $doc_type_mapping[$mapping['tdm_doctype']] = array($mapping['xdis_title'], $mapping['tdm_subtype']);
    }

    $doc_type = $record->item->doctype->attributes()->code;
    $doc_type = "$doc_type";
    if (! array_key_exists($doc_type, $doc_type_mapping)) {
      $log->err('Unsupported doc type when converting ESTI record to MODS:'.$doc_type);
      return false;
    }

    $date_issued = '';
    if (@$record->item->attributes()->coverdate) {
      preg_match('/(\d{4})(\d{2})/', $record->item->attributes()->coverdate, $matches);
      if (count($matches) == 3) {
        if ($matches[2] == '00') {
          $date_issued = $matches[1];
        } else {
          $date_issued = $matches[1] . '-' . $matches[2];
        }
      } else {
        if (@$record->item->bib_issue->attributes()->year) {
          $date_issued = $record->item->bib_issue->attributes()->year;
        }
      }
    }

    $_record = $record->item;
    unset($record);
    $record = $_record;
    unset($_record);

    $item_title = htmlspecialchars($record->item_title, ENT_QUOTES);
    $source_title = htmlspecialchars($record->source_title, ENT_QUOTES);

    $author_lname = '';
    $authors_matching_count = 0;
    $authors_matching_index = 0;

    if ($author_id) {
      $author_lname = strtolower(Author::getLastname($author_id));
    }

    // MODS
    $mods = array();
    $mods['titleInfo']['title'] = $item_title;

    $mods['name'][0]['id'] = '0';
    $mods['name'][0]['authority'] = APP_ORG_NAME;
    $mods['name'][0]['namePart_personal'] = $record->authors->primaryauthor;
    $mods['name'][0]['role']['roleTerm_text'] = 'author';

    // Attempt to match on last name
    if (
        (!empty($author_lname)) &&
        preg_match('/^'.$author_lname.'/', strtolower($mods['name'][0]['namePart_personal']))
    ) {
      $authors_matching_count++;
      $authors_matching_index = 0;
    }

    $i = 1;
    foreach ($record->authors->author as $author) {
      $mods['name'][$i]['id'] = '0';
      $mods['name'][$i]['authority'] = APP_ORG_NAME;
      $mods['name'][$i]['namePart_personal'] = $author;
      $mods['name'][$i]['role']['roleTerm_text'] = 'author';
      // Attempt to match on last name
      if (
          (!empty($author_lname)) &&
          preg_match('/^'.$author_lname.'/', strtolower($mods['name'][$i]['namePart_personal']))
      ) {
         $authors_matching_count++;
         $authors_matching_index = $i;
      }
      $i++;
    }
    if ($authors_matching_count == 1) {
      $mods['name'][$authors_matching_index]['id'] = $author_id;
    }

    $i = 0;
    if (count($record->keywords->keyword) > 0) {
      foreach ($record->keywords->keyword as $keyword) {
        $mods['subject'][$i]['authority'] = 'keyword';
        $mods['subject'][$i]['topic'] = $keyword;
        $i++;
      }
    }
    if (count($record->keywords_plus->keyword) > 0) {
      foreach ($record->keywords_plus->keyword as $keyword) {
        $mods['subject'][$i]['authority'] = 'keyword';
        $mods['subject'][$i]['topic'] = $keyword;
        $i++;
      }
    }
    $mods['identifier_isi_loc'] = $record->ut;
    $mods['identifier_isbn'] = $record->isbn;
    $mods['language'] = Language::resolveWoSLanguage($record->languages->primarylang);
    $mods['genre'] = $doc_type_mapping[$doc_type][0];
    $mods['genre_type'] = $doc_type_mapping[$doc_type][1];

    if ($doc_type == 'MC' || $doc_type == 'M') {
      // Conference item
      $mods['originInfo']['dateIssued'] = $date_issued;
      $mods['relatedItem']['titleInfo']['title'] = $record->source_title;
      $mods['relatedItem']['originInfo']['dateOther'] = $record->conferences->conference->conf_date;
      $mods['relatedItem']['originInfo']['place']['placeTerm'] =
          $record->conferences->conference->conf_location->conf_city . ' ' .
          $record->conferences->conference->conf_location->conf_state;

      $mods['relatedItem']['name'][0]['namePart_type'] = 'conference';
      $mods['relatedItem']['name'][0]['namePart'] = $record->conferences->conference->conf_title;
      preg_match('/\(([^\)]+)\):/', $record->bib_id, $matches);
      if (count($matches) == 2) {
        $mods['relatedItem']['part']['detail_issue']['number'] = $matches[1];
      }
      $mods['relatedItem']['part']['detail_volume']['number'] = @$record->bib_issue->attributes()->vol;
      if ($record->bib_pages) {
        $pages = @preg_split('/-/', $record->bib_pages);
        if (count($pages) == 2) {
          $mods['relatedItem']['part']['extent_page']['start'] = $pages[0];
          $mods['relatedItem']['part']['extent_page']['end'] = $pages[1];
          $mods['relatedItem']['part']['extent_page']['total'] = $pages[1] - $pages[0];
        }
      }
    } else if ($doc_type == '$' || $doc_type == 'P' || $doc_type == 'U') {
      // Conference paper
      $mods['originInfo']['publisher'] = $record->book_desc->bk_publisher;
      $mods['originInfo']['dateIssued'] = $date_issued;
      $mods['relatedItem']['titleInfo']['title'] = $record->source_title;
      $mods['relatedItem']['name'][0]['namePart_type'] = 'conference';
      $mods['relatedItem']['name'][0]['namePart'] = $record->conferences->conference->conf_title;
      $mods['relatedItem']['originInfo']['place']['placeTerm'] =
          $record->conferences->conference->conf_location->conf_city . ' ' .
          $record->conferences->conference->conf_location->conf_state;
      $mods['relatedItem']['originInfo']['dateOther'] = $record->conferences->conference->conf_date;
      preg_match('/\(([^\)]+)\):/', $record->bib_id, $matches);
      if (count($matches) == 2) {
        $mods['relatedItem']['part']['detail_issue']['number'] = $matches[1];
      }
      $mods['relatedItem']['part']['detail_volume']['number'] = @$record->bib_issue->attributes()->vol;
      if ($record->bib_pages) {
        $pages = @preg_split('/-/', $record->bib_pages);
        if (count($pages) == 2) {
          $mods['relatedItem']['part']['extent_page']['start'] = $pages[0];
          $mods['relatedItem']['part']['extent_page']['end'] = $pages[1];
          $mods['relatedItem']['part']['extent_page']['total'] = $pages[1] - $pages[0];
        }
      }
    } else {
      // Journal Article
      $mods['relatedItem']['originInfo']['dateIssued'] = $date_issued;
      $mods['relatedItem']['name'][0]['namePart_type'] = 'journal';
      $mods['relatedItem']['name'][0]['namePart'] = $source_title;
      preg_match('/\(([^\)]+)\):/', $record->bib_id, $matches);
      if (count($matches) == 2) {
        $mods['relatedItem']['part']['detail_issue']['number'] = $matches[1];
      }
      $mods['relatedItem']['part']['detail_volume']['number'] = @$record->bib_issue->attributes()->vol;
      if ($record->bib_pages) {
        $pages = @preg_split('/-/', $record->bib_pages);
        if (count($pages) == 2) {
          $mods['relatedItem']['part']['extent_page']['start'] = $pages[0];
          $mods['relatedItem']['part']['extent_page']['end'] = $pages[1];
        }
      }
    }
    return $mods;
  }

  /**
   * Converts as ESTI record to a links array for inserting into foxml
   *
   * @param $record The WoS record to add to Fedora
   *
   * @return array
   */
  public static function convertEstiRecordToLinks($record)
  {
    $log = FezLog::get();

    $links = array();
    if (@$record->item->article_nos->article_no) {
      // Currently extract DOI links only
      $doi = $record->item->article_nos->article_no;
      $doi = "$doi";
      preg_match('/^DOI (\d{2})\.(\d{4})\/(.*)$/', $doi, $matches);
      if (count($matches) == 4) {
        $links[0]['url'] = 'http://dx.doi.org/' . $matches[1] . '.' . $matches[2] . '/' .$matches[3];
        // 2010-04-07 MT: default to link_1 because this is currently only called by functions that
        // deal with new records
        $links[0]['id'] = 'link_1';
        $links[0]['created'] = date('c');
        $links[0]['name'] = 'Link to Full Text (DOI)';
      }
    }
    return $links;
  }

  /**
   * Generates an outstanding status message string for a specific pid
   *
   * @param string $pid
   * @return string empty string if no counts remaining, otherwise, a string detailing the outstanding details
   **/
  public static function generateOutstandingStatusString($pid)
  {
    $workflowsCount = WorkflowStatusStatic::getCountForPid($pid);
    $bgpsCount = BackgroundProcessPids::getCountForPid($pid);
    $fulltextQueueDetails = FulltextQueue::getDetailsForPid($pid);

    // if we don't need to do anything, return empty string to calling function
    if ($workflowsCount == 0 && $bgpsCount == 0 && count($fulltextQueueDetails) == 0)
      return "";

    $outputString = "There";
    $outputStarted = false;

    $workflowsString = '';
    if ($workflowsCount) {
      $plural = $workflowsCount == 1 ? '' : 's';
      $wfPrefix = $workflowsCount == 1 ? 'is' : 'are';
      $workflowsString = "{$workflowsCount} workflow{$plural}";
      $outputString .= " {$wfPrefix} <strong>{$workflowsString}</strong>";
      $outputStarted = true;
    }

    $bgpsString = '';
    if ($bgpsCount) {
      $plural = $bgpsCount == 1 ? '' : 'es';
      $bgpPrefix = $bgpsCount == 1 ? 'is' : 'are';
      $bgpString = "{$bgpsCount} background process{$plural}";
      if ($outputStarted) {
        $outputString .= " and <strong>{$bgpString}</strong>";
      } else {
        $outputString .= " {$bgpPrefix} <strong>{$bgpsCount} background process{$plural}</strong>";
      }
      $outputStarted = true;
    }

    if ($outputStarted) {
      $outputString .= " currently processing this item.";
    }

    $countFulltextQueue = count($fulltextQueueDetails);
    if ($countFulltextQueue) {
      if ($countFulltextQueue == 1) {
        $operation = $fulltextQueueDetails[0]['operation'] == FulltextQueue::ACTION_INSERT ?
            'insertion into' : 'deletion from';
      } else {
        $operation = 'insertion into and deletion from';
      }

      $ftqString = "This item is queued for <strong>{$operation}</strong> SOLR.";
      if ($outputStarted) {
        $outputString .= " {$ftqString}";
      } else {
        // replace the whold string
        $outputString = $ftqString;
      }

    }

    return $outputString;
  }
} // end of Misc class

if (!function_exists('json_encode')) {
  function json_encode($a=false)
  {
    if (is_null($a)) {
      return 'null';
    }
    if ($a === false) {
      return 'false';
    }
    if ($a === true) {
      return 'true';
    }
    if (is_scalar($a)) {
      if (is_float($a)) {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a)) {
        static $jsonReplaces = array(
        array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
        array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"')
        );
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      } else {
        return $a;
      }
    }

    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
      if (key($a) !== $i) {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList) {
      foreach ($a as $v)
      $result[] = json_encode($v);

      return '[' . join(',', $result) . ']';
    } else {
      foreach ($a as $k => $v) {
        $result[] = json_encode($k).':'.json_encode($v);
      }
      return '{' . join(',', $result) . '}';
    }
  }
}

if (!function_exists('json_decode')) {
  function json_decode($a=false)
  {
    $json = new HTML_AJAX_Serializer_JSON();
    $array = $json->unserialize($a);
    $object = new stdClass();
    Misc::array_to_object($array, $object);
    return $object;
  }
}
