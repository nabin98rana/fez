<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004 MySQL AB                                    |
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
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: s.class.misc.php 1.44 04/01/26 13:34:39-06:00 joao@kickass. $
//


/**
 * Class to hold methods and algorythms that woudln't fit in other classes, such
 * as functions to work around PHP bugs or incompatibilities between separate 
 * PHP configurations.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */
include_once(APP_INC_PATH . "class.error_handler.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.xsd_display.php");
include_once(APP_INC_PATH . "class.xsd_loop_subelement.php");
include_once(APP_PEAR_PATH . "XML/Serializer.php");
include_once(APP_PEAR_PATH . "XML/Unserializer.php");

class Misc
{

    /**
     * Method used to simulate the correct behavior of array_diff().
     *
     * @access  public
     * @param   array $foo The first array
     * @param   array $bar The second array
     * @return  array The different values
     */
    function arrayDiff($foo, $bar)
    {
        if (!is_array($bar)) {
            $bar = array();
        }
        $diffs = array();
        $foo_values = array_values($foo);
        $bar_values = array_values($bar);
        if (count($foo_values) > count($bar_values)) {
            $total = count($foo_values);
            $first = &$foo_values;
            $second = &$bar_values;
        } else {
            $total = count($bar_values);
            $first = &$bar_values;
            $second = &$foo_values;
        }
        for ($i = 0; $i < $total; $i++) {
            if ((!empty($first[$i])) && (!@in_array($first[$i], $second))) {
                $diffs[] = $first[$i];
            }
            if ((!empty($second[$i])) && (!@in_array($second[$i], $first))) {
                $diffs[] = $second[$i];
            }
        }
        return $diffs;
    }


    /**
     * Method used to get the title given to the current installation of Eventum.
     *
     * @access  public
     * @return  string The installation title
     */
    function getToolCaption()
    {
        $setup = Setup::load();
        return $setup['tool_caption'] ? $setup['tool_caption'] : APP_NAME;
    }


    /**
     * Method used to print a prompt asking the user for information.
     *
     * @access  public
     * @param   string $message The message to print
     * @param   string $default_value The default value to be used if the user just press <enter>
     * @return  string The user response
     */
    function prompt($message, $default_value)
    {
        echo $message;
        if ($default_value !== FALSE) {
            echo " [default: $default_value] -> ";
        } else {
            echo " [required] -> ";
        }
        flush();
        $input = trim(Misc::getInput(true));
        if (empty($input)) {
            if ($default_value === FALSE) {
                die("ERROR: Required parameter was not provided!\n");
            } else {
                return $default_value;
            }
        } else {
            return $input;
        }
    }


    /**
     * Method used to get the standard input.
     *
     * @access  public
     * @return  string The standard input value
     */
    function getInput($is_one_liner = FALSE)
    {
        static $return;

        if (!empty($return)) {
            return $return;
        }

        $terminator = "\n";

        $stdin = fopen("php://stdin", "r");
        $input = '';
        while (!feof($stdin)) {
            $buffer = fgets($stdin, 256);
            $input .= $buffer;
            if (($is_one_liner) && (strstr($input, $terminator))) {
                break;
            }
        }
        fclose($stdin);
        $return = $input;
        return $input;
    }


    /**
     * Method used to check the spelling of a given text.
     *
     * @access  public
     * @param   string $text The text to check the spelling against
     * @return  array Information about the mispelled words, if any
     */
    function checkSpelling($text)
    {
        $temptext = tempnam("/tmp", "spelltext");
        if ($fd = fopen($temptext, "w")) {
            $textarray = explode("\n", $text);
            fwrite($fd, "!\n");
            foreach ($textarray as $key => $value) {
                // adding the carat to each line prevents the use of aspell commands within the text...
                fwrite($fd,"^$value\n");
            }
            fclose($fd);
//			echo "monkey".$temptext;
            $return = shell_exec("cat $temptext | /usr/local/bin/aspell -a");
            unlink($temptext);
        }
        $lines = explode("\n", $return);
        // remove the first line that is only the aspell copyright banner
        array_shift($lines);
        // remove all blank lines
        foreach ($lines as $key => $value) {
            if (empty($value)) {
                unset($lines[$key]);
            }
        }
        $lines = array_values($lines);

        $misspelled_words = array();
        $spell_suggestions = array();
        for ($i = 0; $i < count($lines); $i++) {
            if (substr($lines[$i], 0, 1) == '&') {
                // found suggestions for this word
                $first_part = substr($lines[$i], 0, strpos($lines[$i], ':'));
                $pieces = explode(' ', $first_part);
                $misspelled_word = $pieces[1];
                $last_part = substr($lines[$i], strpos($lines[$i], ':')+2);
                $suggestions = explode(', ', $last_part);
            } elseif (substr($lines[$i], 0, 1) == '#') {
                // found no suggestions for this word
                $pieces = explode(' ', $lines[$i]);
                $misspelled_word = $pieces[1];
                $suggestions = array();
            } else {
                // no spelling mistakes could be found
                continue;
            }
            // prevent duplicates...
            if (in_array($misspelled_word, $misspelled_words)) {
                continue;
            }
            $misspelled_words[] = $misspelled_word;
            $spell_suggestions[$misspelled_word] = $suggestions;
        }

        return array(
            'total_words' => count($misspelled_words),
            'words'       => $misspelled_words,
            'suggestions' => $spell_suggestions
        );
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
     * Method used to replace all special whitespace characters (\n, 
     * \r and \t) by their string equivalents. It is usually used in
     * JavaScript code.
     *
     * @access  public
     * @param   string $str The string to be escaped
     * @return  string The escaped string
     */
    function escapeWhitespace($str)
    {
        $str = str_replace("\n", '\n', $str);
        $str = str_replace("\r", '\r', $str);
        $str = str_replace("\t", '\t', $str);
        return $str;
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
	* @@@ CK - 19/1/2005 - Added this so could sort listings of issues by assigned users on the array resultset after the sql has processed
	* @return Returns the array sorted as required
	* @param $aryData Array containing data to sort
	* @param $strIndex Name of column to use as an index
	* @param $strSortBy Column to sort the array by
	* @param $strSortType String containing either asc or desc [default to asc]
	* @desc Naturally sorts an array using by the column $strSortBy
	*/
	function array_natsort($aryData, $strIndex, $strSortBy, $strSortType=false)
	{
	// if the parameters are invalid
	if (!is_array($aryData) || !$strIndex || !$strSortBy)
	// return the array
	return $aryData;
	
	// create our temporary arrays
	$arySort = $aryResult = array();
	
	// loop through the array
	foreach ($aryData as $aryRow)
	// set up the value in the array
	$arySort[$aryRow[$strIndex]] = $aryRow[$strSortBy];
	
	// apply the natural sort
	natsort($arySort);

	// if the sort type is descending
	if ($strSortType=="desc")
	// reverse the array
	arsort($arySort);
	
	// loop through the sorted and original data
	foreach ($arySort as $arySortKey => $arySorted)
		foreach ($aryData as $aryOriginal)
		// if the key matches
		if ($aryOriginal[$strIndex]==$arySortKey)
			// add it to the output array
			array_push($aryResult, $aryOriginal);
	// return the return
	return $aryResult;
	}

	function const_array($constant) {
	 $array = explode(",",$constant);
	 return $array;
	}

	// @@@ CK - 20/1/2005 - Added from the PHP manual under array_filter comments. Used in newquick.php for filtering AsktIT generic usernames from Logged By list.
	// Modified to handle associative arrays, eg replace while $i etc with foreach key => data.
	function array_clean ($input, $delete = false, $caseSensitive = false, $matchWholeWords = false)
	{
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


	function cleanDatastreamList($dsList) {
		$original_dsList = $dsList;		
		$return = array();
		$p_ds = Misc::const_array(APP_FEDORA_PROTECTED_DATASTREAMS);
		foreach ($dsList as $key => $ds) {		
			$keep = true;
			foreach ($p_ds as $protected_ds) {
				if (($ds['ID'] == $protected_ds) || (is_numeric(strpos($ds['ID'], "thumbnail_"))) || (is_numeric(strpos($ds['ID'], "presmd_"))) )   {
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
				// now try and find a preservation metadata datastream of this datastream
				$presmd = "presmd_".substr($ds['ID'], 0, strrpos($ds['ID'], ".") + 1)."xml";
				$ds['presmd'] = 0;
				foreach ($original_dsList as $o_key => $o_ds) {
					if ($presmd == $o_ds['ID']) {  // found the presmd datastream so save it against the record
						$ds['presmd'] = $presmd;
					}
				}

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
 * The Util:: class provides generally useful methods of different kinds.
 *
 * $Horde: framework/Util/Util.php,v 1.366 2004/03/30 17:03:58 jan Exp $
 *
 * Copyright 1999-2004 Chuck Hagenbuch <chuck@horde.org>
 * Copyright 1999-2004 Jon Parise <jon@horde.org>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @author  Jon Parise <jon@horde.org>
 * @version $Revision: 1.366 $
 * @since   Horde 3.0
 * @package Horde_Util
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
     * Method used to get a random file from the 'daily tips' directory.
     *
     * @access  public
     * @param   object $tpl The template object
     * @return  string Random filename
     */
    function getRandomTip($tpl)
    {
        $tip_dir = $tpl->smarty->template_dir . "/tips";
        $files = Misc::getFileList($tip_dir);
        $i = rand(0, (integer)count($files));
        // some weird bug in the rand() function where sometimes the 
        // second parameter is non-inclusive makes us have to do this
        if (!isset($files[$i])) {
            return Misc::getRandomTip($tpl);
        } else {
            return $files[$i];
        }
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
     * Method used to get the list of priorities as an associative array in the
     * style of (id => title)
     *
     * @access  public
     * @return  array The list of priorities
     */
    function getAssocPriorities()
    {
        $stmt = "SELECT
                    pri_id,
                    pri_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "priority";
        $res = $GLOBALS["db_api"]->dbh->getAssoc($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the full list of priorities.
     *
     * @access  public
     * @return  array The list of priorities
     */
    function getPriorities()
    {
        $stmt = "SELECT
                    pri_id,
                    pri_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "priority";
        $res = $GLOBALS["db_api"]->dbh->getAll($stmt, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
    }


    /**
     * Method used to get the title for a priority ID.
     *
     * @access  public
     * @param   integer $id The priority ID
     * @return  string The priority title
     */
    function getPriorityTitle($id)
    {
        $stmt = "SELECT
                    pri_title
                 FROM
                    " . APP_DEFAULT_DB . "." . APP_TABLE_PREFIX . "priority
                 WHERE
                    pri_id=$id";
        $res = $GLOBALS["db_api"]->dbh->getOne($stmt);
        if (PEAR::isError($res)) {
            Error_Handler::logError(array($res->getMessage(), $res->getDebugInfo()), __FILE__, __LINE__);
            return "";
        } else {
            return $res;
        }
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

	function mime_content_type($f) {
		$f = escapeshellarg($f);
		return trim( `file -bi $f` );
	}


    /**
     * Method used as a callback with the regular expression code that parses
     * text and creates links to other issues.
     *
     * @access  public
     * @param   array $matches Regular expression matches
     * @return  string The link to the appropriate issue
     */
    function callbackIssueLinks($matches)
    {
        include_once(APP_INC_PATH . "class.issue.php");
        // check if the issue is still open
        if (Issue::isClosed($matches[5])) {
            $class = 'closed_link';
        } else {
            $class = 'link';
        }
        $issue_title = Issue::getTitle($matches[5]);
        return "<a title=\"issue " . $matches[5] . " - $issue_title\" class=\"" . $class . "\" href=\"view.php?id=" . $matches[5] . "\">" . $matches[1] . $matches[2] . $matches[3] . $matches[4] . $matches[5] . "</a>";
    }


    /**
     * Method used to parse the given string for references to issues in the
     * system, and creating links to those if any are found.
     *
     * @access  public
     * @param   string $text The text to search against
     * @param   string $class The CSS class to use on the actual links
     * @return  string The parsed string
     */
    function activateIssueLinks($text, $class = "link")
    {
        $text = preg_replace_callback("/(issue)(:)?(\s)(\#)?(\d+)/i", array('Misc', 'callbackIssueLinks'), $text);
        $text = preg_replace_callback("/(bug)(:)?(\s)(\#)?(\d+)/i", array('Misc', 'callbackIssueLinks'), $text);
        return $text;
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
     * Method used to format the reply of someone's email that is available in
     * the system.
     *
     * @access  public
     * @param   string $str The string to be formatted
     * @return  string the formatted string
     */
    function formatReply($str)
    {
        $lines = explode("\n", str_replace("\r", "", $str));
        // COMPAT: the next line requires PHP >= 4.0.6
        $lines = array_map(array("Misc", "indent"), $lines);
        return implode("\n", $lines);
    }


    /**
     * Method used to format a RFC 822 compliant date for the given unix 
     * timestamp.
     *
     * @access  public
     * @param   integer $ts The unix timestamp
     * @return  string The formatted date string
     */
    function formatReplyDate($ts)
    {
        // Sat, Sep 28, 2002 at 06:28:58PM -0400
        $first = date("D, M d, Y", $ts);
        $rest = date("H:i:sA O", $ts);
        return $first . " at " . $rest;
    }

    /**
	 * @@@ CK - 17/9/2004
     * Method used to extract a desired string from a big text block (eg mail body) based on a string before it
     *
	 * Note the search is not case sensitive (although it would be easy to change this if required).	
	 *
     * @access  public
     * @param   The start search string (to the left of the desired string)
     * @param   The end search string (to the right of the desired string)
     * @param   The block of text to search through
     * @return  string the desired string, or if something failed returns false
     */
    function extractStringFromBlock($startSearch, $endSearch, $blockText)
    {
		$desiredValue = false;
		$blockOneLine = strtolower(str_replace("\n", " ", $blockText)) . " "; // eol -> catch for end of line
		$searchStartPos = strpos($blockOneLine, $startSearch);
		if ($searchStartPos !== false) { 
			$searchStartPos = ($searchStartPos + strlen($startSearch)); 
			$searchEndPos = strpos($blockOneLine, $endSearch, $searchStartPos);
			if ($searchEndPos !== false) {
				$desiredValue = trim(substr($blockOneLine, $searchStartPos, ($searchEndPos - $searchStartPos)));
			}			
		}
		return $desiredValue;
    }


  /**
   * @@@ CK - 25/02/2005
   * Added these XSLT wrappers for PHP5 XSL module 
   *
   */


   function xslt_create() {
       return new XsltProcessor();
   }

   function xslt_process($xsltproc,
                         $xml_arg,
                         $xsl_arg,
                         $xslcontainer = null,
                         $args = null,
                         $params = null) {


       // Start with preparing the arguments
       $xml_arg = str_replace('arg:', '', $xml_arg);
       //$xsl_arg = str_replace('arg:', '', $xsl_arg); //original
       $xsl_arg = file_get_contents($xsl_arg);

       // Create instances of the DomDocument class
       $xml = new DomDocument;
       $xsl = new DomDocument;

       // Load the xml document and the xsl template
       $xml->loadXML($args[$xml_arg]);
       //$xsl->loadXML($args[$xsl_arg]);
       $xsl->loadXML($xsl_arg);

       // Load the xsl template
       $xsltproc->importStyleSheet($xsl);

       // Set parameters when defined
       if ($params) {
           foreach ($params as $param => $value) {
               $xsltproc->setParameter("", $param, $value);
           }
       }
       // Start the transformation
       $processed = $xsltproc->transformToXML($xml);

       // Put the result in a file when specified
       if ($xslcontainer) {
           return @file_put_contents($xslcontainer, $processed);
       } else {
           return $processed;
       }

   }

   function xslt_free($xsltproc) {
       unset($xsltproc);
   }

function xslt_errno($xh) {return 7;}
function xslt_error($xh) {return '?';}


// CK - From php manual - something to turn php5 domcouments into simple arrays (so you can pass them off to smarty)
function dom_to_simple_array($domnode, &$array) {
  $array_ptr = &$array;
  $domnode = $domnode->firstChild;
  while (!is_null($domnode)) {
   if (! (trim($domnode->nodeValue) == "") ) {
     switch ($domnode->nodeType) {
       case XML_TEXT_NODE: {
         $array_ptr['cdata'] = $domnode->nodeValue;
         break;
       }
       case XML_ELEMENT_NODE: {
         $array_ptr = &$array[$domnode->nodeName][];
         if ($domnode->hasAttributes() ) {
           $attributes = $domnode->attributes;
           if (!is_array ($attributes)) {
             break;
           }
           foreach ($attributes as $index => $domobj) {
             $array_ptr[$index] = $array_ptr[$domobj->name] = $domobj->value;
           }
         }
         break;
       }
     }
     if ( $domnode->hasChildNodes() ) {
       Misc::dom_to_simple_array($domnode, $array_ptr);
     }
   }
   $domnode = $domnode->nextSibling;
  }
}



/* function dom_xml_to_simple_array($domnode, &$array) {
  $array_ptr = &$array;
  $domnode = $domnode->firstChild;

  while (!is_null($domnode)) {

     switch ($domnode->nodeType) {
       case XML_ELEMENT_NODE: {
         $array_ptr = &$array[$domnode->nodeName][];
         if ($domnode->hasAttributes() ) {
           $attributes = $domnode->attributes; 
		   foreach ($attributes as $index => $domobj) {
				$array_ptr[$domobj->nodeName] = $domobj->nodeValue;
		   }
         }
         break;
       }
     }
     if ( $domnode->hasChildNodes() ) {
       Misc::dom_xsd_to_simple_array($domnode, $array_ptr);
     }
   $domnode = $domnode->nextSibling;
  }
} */

function sql_array_to_string($array){
	$return_str = "";
	foreach($array as $key=>$val) {
		$return_str .= ",".$val[0];
	}
	$return_str = substr($return_str, 1);
	return $return_str;
}

function array_diff_keys()
{
   $args = func_get_args();
   $res = $args[0];
   print_r($res);
   if(!is_array($res)) {
       return array();
   }
   for($i=1;$i<count($args);$i++) {
       if(!is_array($args[$i])) {
           continue;
       }
       foreach ($args[$i] as $key => $data) {
           unset($res[$key]);
       }
   }
   return $res;
}

function getDatastreamXMLHeaders($datastreamTitles, $xmlString) {
	global $HTTP_POST_FILES;
//	print_r($HTTP_POST_FILES);
	$return = array();
	$searchvars = array("ID", "CONTROL_GROUP", "STATE", "VERSIONABLE", "versionID", "LABEL", "MIMETYPE"); // For items which repeat, (like ID (ID and versionID)) make the searchable part uppercase and the name difference lowercase
	foreach ($datastreamTitles as $dsTitle) {
		$IDPos = stripos($xmlString, 'id="'.$dsTitle['xsdsel_title'].'"'); // stripos is a php5 function
		if (is_numeric($IDPos)) {
			$XMLContentStartPos = $IDPos;
			if (is_numeric(strpos($xmlString, '<foxml:xmlContent>', $IDPos))) {
				$XMLContentEndPos = strpos($xmlString, '<foxml:xmlContent>', $XMLContentStartPos);
			} elseif (is_numeric(strpos($xmlString, '<foxml:binaryContent>', $IDPos))) {
				$XMLContentEndPos = strpos($xmlString, '<foxml:binaryContent>', $XMLContentStartPos);
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
				$file_res = XSD_Loop_Subelement::getXSDMFInputType($dsTitle['xsdsel_id'], 'file_input');
//				print_r($file_res);
				// PROBLEM IS HERE!!
//				echo "COUNT -> ".count($file_res);
				if (count($file_res) == 1) {

					$return[$dsTitle['xsdsel_title']]['ID'] = str_replace(" ", "_", $HTTP_POST_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']]);
					$return[$dsTitle['xsdsel_title']]['LABEL'] = $HTTP_POST_FILES['xsd_display_fields']['name'][$file_res[0]['xsdmf_id']];
					$return[$dsTitle['xsdsel_title']]['MIMETYPE'] = $HTTP_POST_FILES['xsd_display_fields']['type'][$file_res[0]['xsdmf_id']];

				} 

			}
		}
	}

//	if 	$return[$dsTitle['xsdsel_title']][$sv]
//	print_r($return);


	return $return;
}

function getDatastreamXMLContent($datastreamTitles, $xmlString) {
//	Retrieves the inline xml content datastream of the given titles in the xml string 
//     - used to pull out the datastream from a created XML object after an update html post.
	$return = array();
//	echo $xmlString;
	foreach ($datastreamTitles as $dsTitle) {
		$IDPos = stripos($xmlString, 'id="'.$dsTitle['xsdsel_title'].'"'); // stripos is a php5 function
		if (is_numeric($IDPos)) {
			$searchScopeEnd = strpos($xmlString, "</foxml:datastream>", $IDPos);
			$searchXMLString = substr($xmlString, $IDPos, ($searchScopeEnd - $IDPos));
//			echo $searchXMLString."\n\n";
			if (is_numeric(strpos($searchXMLString, '<foxml:xmlContent>'))) {
//			if (is_numeric(strpos(substr($xmlString, $IDPos, '<foxml:xmlContent>', $IDPos))) {
				$XMLContentStartPos = strpos($searchXMLString, '<foxml:xmlContent>') + 18;
				$XMLContentEndPos = strpos($searchXMLString, '</foxml:xmlContent>', $XMLContentStartPos);
			} elseif (is_numeric(strpos($searchXMLString, '<foxml:binaryContent>'))) {
//				$XMLContentStartPos = strpos($searchXMLString, '<foxml:binaryContent>') + 21;
				$XMLContentStartPos = strpos($searchXMLString, '<foxml:binaryContent>') + 22;
				$XMLContentEndPos = strpos($searchXMLString, '</foxml:binaryContent>', $XMLContentStartPos);
			}
			if (is_numeric($XMLContentStartPos) && is_numeric($XMLContentEndPos)) {
				$tempXML = substr($searchXMLString, $XMLContentStartPos, ($XMLContentEndPos-$XMLContentStartPos));
//				echo "\n\nTEMPXML -> ".$tempXML."\n\n";
//				$tempXML = substr($xmlString, $XMLContentStartPos, ($XMLContentEndPos-$XMLContentStartPos));
				$return[$dsTitle['xsdsel_title']] = $tempXML;
			}
		}
	}
//	print_r($return);
	return $return;
}

function removeNonXMLDatastreams($datastreamTitles, $xmlString) {

	$return = $xmlString;
//	echo $xmlString;
	foreach ($datastreamTitles as $dsTitle) {
//		echo "DSTITLE -> ";
//		echo $dsTitle['xsdsel_title']."\n\n";
		$IDPos = stripos($xmlString, 'id="'.$dsTitle['xsdsel_title'].'"'); // stripos is a php5 function
		$binaryPos = false;
		if (is_numeric($IDPos)) {
			$searchScopeEnd = strpos($xmlString, "</foxml:datastream>", $IDPos);
//			$searchXMLString = substr($xmlString, $IDPos, ($searchScopeEnd - $IDPos));
			$searchXMLString = substr($xmlString, 0, ($searchScopeEnd - $IDPos));
			
//			$binaryPos = strpos($searchXMLString, '<foxml:binaryContent>');
			$binaryPos = strrpos($searchXMLString, '<foxml:binaryContent>', $IDPos); // get the last binaryContent position in the xml after ds title, but before a /datastream (close tag)
            if (is_numeric($binaryPos)) { // if you find binaryContent after this tag
//				$XMLContentStartPos = strrpos($xmlString, '<foxml:datastream', $IDPos);
//				echo substr($xmlString, 0, $binaryPos);
//				$XMLContentStartPos = strrpos(substr($xmlString, 0, $binaryPos), '<foxml:datastream '); // the space is essential or it will pick '<foxml:datastreamVersion
//				echo "HEEEERE!!!";
//				echo substr($xmlString, 0, $binaryPos); echo "\n\n";
//				$XMLContentStartPos = strpos(substr($xmlString, 0, $binaryPos), '<foxml:datastream '); // the space is essential or it will pick '<foxml:datastreamVersion
                $XMLContentStartPos = strrpos(substr($xmlString, 0, $binaryPos), '<foxml:datastream '); // the space is essential or it will pick '<foxml:datastreamVersion
                $XMLContentEndPos = strpos($xmlString, '</foxml:datastream>', $XMLContentStartPos) + 19;
                if (is_numeric($XMLContentStartPos) && is_numeric($XMLContentEndPos)) {
                    $tempXML = substr($xmlString, $XMLContentStartPos, ($XMLContentEndPos-$XMLContentStartPos));
//				echo "remove temp XML -> ";
//				echo $tempXML."\n\n\n";
                    $return = str_replace($tempXML, "", $return); // if a binary datastream is found then remove it from the ingest object
                }
            }
		}
	}
//	echo "RETURN CLEANED XMLOBJ -> ".$return."\n\n";
	return $return;
}


function dom_xml_to_simple_array($domnode, &$array, $top_element_name, $element_prefix, &$xsdmf_array, $xdis_id, $parentContent="", $parent_key="") {
//	echo "\n\n Parent Key -> ".$parent_key."\n\n";
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
//				$array_ptr = &$array[$clean_nodeName];

			    foreach ($attributes as $index => $domobj) {
					 if (is_numeric(strpos($domobj->nodeName, ":"))) {
				   		$new_element = "!".$parentContent."!".$clean_nodeName."!".substr($domobj->nodeName, strpos($domobj->nodeName, ":") +1);
					 } else {
				   		$new_element = "!".$parentContent."!".$clean_nodeName."!".$domobj->nodeName;
					 }
//					echo "NEW ATTRIBUTE - ".$parent_key." - ".$top_element_name." ".$element_prefix." -> ".$new_element."\n\n";
					if ($parent_key != "") { // if there are passed parent keys then use them in the search
//						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByParentKeyXDIS_ID($new_element, $parent_key, $xdis_str);
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
//			 else  // else for HasAttributes (so has none)
//			echo "\n ATTRIB XSDMFID!!! -> "." $xsdmf_id"."\n\n";


////			$array_ptr = &$array[$clean_nodeName];


				// If we still havent got the xsdmf_id then it either doesnt have one or the element doesnt have attributes, so try to find it without the attributes
				if ((is_numeric(strpos(substr($parentContent, 0, 1), "!"))) || ($parentContent == "")) {
					$new_element = $parentContent."!".$clean_nodeName; // @@@ CK 31/5/2005 - Added ! to the front of the string
				} else {
					$new_element = "!".$parentContent."!".$clean_nodeName; // @@@ CK 31/5/2005 - Added ! to the front of the string
				}
	
				if (!is_numeric($xsdmf_id)) {

					if ($parent_key != "") { // if there are passed parent keys then use them in the search
//					echo "NEW ELEMENT  ".$parent_key." ".$top_element_name." ".$element_prefix." -> ".$new_element."\n\n";
						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByParentKeyXDIS_ID($new_element, $parent_key, $xdis_str);		
//						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_ID($new_element, $xsd_sel_id, $xdis_str);
//						echo "FOUND XSDMF_ID -> ".$xsdmf_id."\n\n";
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
//				print_r($xsdmf_details);
				if (!empty($xsdmf_details['xsdmf_parent_key_match'])) {

//						echo "WAKO -> "."!".$xsdmf_details['xsdmf_parent_key_match'].$clean_nodeName;
					$array_ptr = &$array["!".$xsdmf_details['xsdmf_parent_key_match']."!".$clean_nodeName];
//					$array_ptr[$while_count]["!".$xsdmf_details['xsdmf_parent_key_match']."!".$new_element] = $domobj->nodeValue;
//					$array_ptr[$while_count]["!".$xsdmf_details['xsdmf_parent_key_match']."!".$new_element] = $domobj->nodeValue;
				} else {
					$array_ptr = &$array[$clean_nodeName];
					$array_ptr[$while_count][$new_element] = $domnode->nodeValue;
				}
//				$array_ptr[$while_count][$new_element] = $domobj->nodeValue;

/*				if ($parent_key != "") {
					$array_ptr[$while_count][$new_element] = $domobj->nodeValue;
				} else {
					$array_ptr[$while_count][$new_element] = $domobj->nodeValue;
				} */

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


//			 // end of if has attributes // commented out for now, see else statement above
			$while_count++;

		} // End of if #text or other non desc
       
		// Now see if it has any chidren nodes and go recursive into those
		if ( $domnode->hasChildNodes() ) {
			// if the current field is a loop sublelement then get its child sel_ids and pass them down in a for loop

/*			if (is_array($xsd_sel_ids) && (count($xsd_sel_ids) > 0)) {

				foreach ($xsd_sel_ids as $new_xsd_sel_id) {
					if ((strpos($domnode->nodeName, $element_prefix.":".$top_element_name) === 0) || (strpos($domnode->nodeName, $top_element_name) === 0)) {
						$newParentContent = "";
						Misc::dom_xml_to_simple_array($domnode, $array_ptr, $top_element_name, $element_prefix, $xsdmf_ptr, $xdis_id, $newParentContent, $new_xsd_sel_id);
					} else {
						if ($parentContent != "") {
							$newParentContent = Misc::strip_element_name($parentContent."!".$domnode->nodeName, $top_element_name, $element_prefix, $new_xsd_sel_id);
						} else {
							$newParentContent = Misc::strip_element_name($domnode->nodeName, $top_element_name, $element_prefix, $new_xsd_sel_id);
						}
						Misc::dom_xml_to_simple_array($domnode, $array_ptr, $top_element_name, $element_prefix, $xsdmf_ptr, $xdis_id, $newParentContent, $new_xsd_sel_id);
					}
					$domnode = $domnode->nextSibling;
				}
			} else {
*/
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
//			}

		}
		$domnode = $domnode->nextSibling;
		// test this below


	} // End of while loop
}

/**
  * XML_Walk
  * A little bit like a sax parser (xml_parse) only using an object and method for all of the events.
  * It is more flexible than an even parser as the domNode object is available to the callback.
  * @param array $callbackdata Used to store data that will be available to sub nodes but not to siblings.  
  * The callback function should return changes to this data for use by child node callbacks.
  */
function XML_Walk($domnode, $callbackobject, $callbackmethod, $callbackdata) {
    if (is_null($domnode)) {
        return;
    }
    $newcallbackdata = $callbackobject->$callbackmethod($domnode, $callbackdata, 'startopen');
    // process attributes
    if ($domnode->hasAttributes() ) {
        $attributes = $domnode->attributes; 
        foreach ($attributes as $index => $domobj) {
            $callbackobject->$callbackmethod($domobj, $newcallbackdata);
        }
    }
    $newcallbackdata = $callbackobject->$callbackmethod($domnode, $newcallbackdata, 'endopen');
    // recurse children
    Misc::XML_Walk($domnode->firstChild, $callbackobject, $callbackmethod, $newcallbackdata);
    // recurse siblings
    $callbackobject->$callbackmethod($domnode, $newcallbackdata, 'close');
    Misc::XML_Walk($domnode->nextSibling, $callbackobject, $callbackmethod, $callbackdata);
}

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

function getNextArrayKey($array, $next_key=0)
 {
   if (array_key_exists($next_key, $array)) 
     $return = Misc::getNextArrayKey($array, $next_key + 1);
   else
     $return = $next_key;
   return $return;
 }

function dom_xsd_to_simple_array($domnode, &$array, $parentContent="") {
  $array_ptr = &$array;
  $domnode = $domnode->firstChild;

  while (!is_null($domnode)) {

//     switch ($domnode->nodeType) {
//       case XML_ELEMENT_NODE: 
//         $array_ptr = &$array;
	if ((strtolower($domnode->nodeName) != "xsd:annotation") && (strtolower($domnode->nodeName) != "xsd:documentation")) {
         if ($domnode->hasAttributes() ) {
           $attributes = $domnode->attributes; 
		   $tmp = "";
		   foreach ($attributes as $index => $domobj) {
				$tmp .= " ".$domobj->nodeName."=".$domobj->nodeValue;
		   }
//		   $tmp2 = $array;
//		   $tmp2 = Misc::array_flatten($array_ptr);
////		   $array_ptr = &$array[$domnode->nodeName.$tmp];
//
//		   $array_ptr = &$array[$domnode->nodeName.$tmp];
		   $array_ptr = &$array[$domnode->nodeName.$tmp];
//		   &$array[$domnode->nodeName.$tmp] = 'hippo';
			if (!( $domnode->hasChildNodes() )) {
			   $array_ptr['espace_hyperlink'] = $parentContent."!".$domnode->nodeName.str_replace(" ", "^", $tmp);
			}
//		   $array_ptr = &$array;
//		   $array_ptr[$parentContent] = "test";
//		   &$array['test'][];
//		   $array_ptr[$domnode->parentNode->nodeName];
//		   $array_ptr[$domnode->parentNode->nodeName] = "hehe";
//		   $array_ptr = "muahahha";
         } else {
		   $array_ptr = &$array[$domnode->nodeName];
		   
//		   $array_ptr['textvalue'] = $domnode->nodeValue;
//		   $array_ptr[$domnode->nodeName] = $domnode->nodeValue;
		 }
//         break;
	}       
       
//     }
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

function IsEmptyDNL($dnl) {
	foreach ($dnl as $dn) {
		echo $dn->nodeName." ".$dn->nodeValue."<br />";
	}
}

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

function PrintDomTree($DomNode) {
	if ($ChildDomNode = $DomNode->first_child()) {
	static $depth = 0;
	$whitespace = "\n
	".str_repeat(" ", ($depth * 2));
	while ($ChildDomNode) {
	if ($ChildDomNode->node_type() == XML_TEXT_NODE) {
	echo trim($ChildDomNode->node_value());
	} elseif ($ChildDomNode->node_type() == XML_ELEMENT_NODE) {
	$HasTag = 1;
	echo $whitespace;
	echo "<", $ChildDomNode->node_name();
	if ($ChildDomNode->has_attributes()) {
	$Array = $ChildDomNode->attributes();
	foreach ($Array AS $DomAttribute) {
	echo " ", $DomAttribute->name(), "=\"", $DomAttribute->value(), "\"";
	}
	}
	echo ">";
	if ($ChildDomNode->has_child_nodes()) {
	$depth++;
	if (PrintDomTree($ChildDomNode)) {
	echo $whitespace;
	}
	$depth--;
	}
	echo "</", $ChildDomNode->node_name(), ">";
	}
	$ChildDomNode = $ChildDomNode->next_sibling();
	}
	return $HasTag;
	}
}

function dom_xsd_to_referenced_array($domnode, $topelement, &$array, $parentnodename="", $searchtype="", $superdomnode, $supertopelement="", $parentContent="") {
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
/*	if ($parentContent == "") {

//		$supertopelement = $topelement;
	}  */
	switch ($domnode->nodeType) {
		case (XML_DOCUMENT_NODE): 
//			echo "in document node (".$domnode->nodeType.", ".$domnode->nodeName.", ".$domnode->nodeValue.")</br>";
			$currentnode = new DomDocument;
			$currentnode = Misc::getElementByNameValue($domnode, $topelement);
		    // print_r($currentnode);
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
//				echo "CURRENT NAME DOC NODE = ".$current_name."<br />";
				if ($current_name != "") {
					$array_ptr = &$array[$current_name];
//					echo "assigning";
				}
//				echo "->"; print_r($array); echo "<-";
/*				foreach ($current_refs as $index => $ref) {
					Misc::dom_xsd_to_referenced_array($currentnode, $ref, $array_ptr, $current_name);
				}
*/

				foreach ($current_types as $type) {
					Misc::dom_xsd_to_referenced_array($currentnode, $type, $array_ptr, $current_name, "complexType", $superdomnode, $supertopelement, $parentContent);
				}

				if ($currentnode->hasChildNodes() ) {
					foreach ($currentnode->childNodes as $childnode) {
						Misc::dom_xsd_to_referenced_array($childnode, '', $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent);
					}
				}
//				$array_ptr = &$array[$currentnode->nodeName.$tmp];
			} else {
				echo "Can't find element ".$topelement;
			}
			break;
		case XML_COMMENT_NODE:
			break;
		case XML_TEXT_NODE:
			break;
		case XML_ELEMENT_NODE:
//  		    echo "1 in element node (".$domnode->nodeType.", ".$domnode->nodeName.", ".$domnode->nodeValue.")<br />";
//			echo "2 top element = ".$topelement."<br />";
			$currentnode = new DomDocument;
			if ($topelement <> '') {
				$currentnode = Misc::getXMLObjectByTypeNameValue($superdomnode, $searchtype, $topelement);
//				$currentnode = Misc::getElementByNameValue($superdomnode, $topelement);
			} else {
				$currentnode = $domnode;
			}
//			echo($currentnode);
//			echo $searchtype;
//echo "hah";			echo $currentnode->nodeName;
			if (is_numeric(strpos($currentnode->nodeName, ":"))) { // Check if there is a ":" in the string if there is then snn is after the :
				$shortnodename = substr($currentnode->nodeName, (strpos($currentnode->nodeName, ":") + 1));
			} else {
				$shortnodename = $currentnode->nodeName;
			}

//			echo "snn = ".$shortnodename."<br />".$searchtype;
			if (($shortnodename == $searchtype) && ($shortnodename <> "element")) {
//			echo "snn = ".$shortnodename." - ".$searchtype."<br />";
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
						Misc::dom_xsd_to_referenced_array($childnode, "", $array_ptr, $parentnodename, "", $superdomnode, $supertopelement, $parentContent);
//						Misc::dom_xsd_to_referenced_array($childnode, "", $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent);
					}
				}
//			} elseif ($shortnodename == "enumeration") {
			} elseif (($shortnodename == "extension")  || ($shortnodename == "any") || ($shortnodename == "anyAttribute") || ($shortnodename == "restriction") || ($shortnodename == "group") || ($shortnodename == "simpleContent") || ($shortnodename == "attributeGroup") || ($shortnodename == "attribute") || ($shortnodename == "enumeration"))  {
//				echo "3"; echo "In group choice <br>";
//				echo "->".$shortnodename.", ". $currentnode->nodeValue."<-";
//				if (($shortnodename == "attribute") || ($shortnodename == "enumeration") || ($shortnodename == "extension") || ($shortnodename == "restriction")) {
				if (($shortnodename == "attribute") || ($shortnodename == "extension")) {
					if ($currentnode->hasAttributes() ) {
						$attributes = $currentnode->attributes;	
						foreach ($attributes as $index => $attrib) {
							if ($attrib->nodeName == "base") {
								$shorttypevalue = substr($attrib->nodeValue, (strpos($attrib->nodeValue, ":") + 1));
								if (!in_array($shorttypevalue, $standard_types)) {
									array_push($current_refs, $attrib->nodeValue);
								}
							}
							if ($attrib->nodeName == "name") {
								$current_name = $attrib->nodeValue;
								$parentContent .= "!".$current_name;
							}
							if ($attrib->nodeName == "value") {
								$current_name = $attrib->nodeValue;
								$parentContent .= "!".$current_name;
							}
						}
						foreach ($current_refs as $ref) {
//							echo "IMA GOING IN!!!!".$ref;
							Misc::dom_xsd_to_referenced_array($currentnode, $ref, $array_ptr, $current_name, "complexType", $superdomnode, $supertopelement, $parentContent);
						}
						if ($current_name <> $parentnodename) {
							$array_ptr = &$array[$current_name];
							$array_ptr['espace_hyperlink'] = $parentContent;
							if ($shortnodename == 'attribute') {
								$array_ptr['espace_nodetype'] = 'attribute';
							} elseif ($shortnodename == 'enumeration') {
								$array_ptr['espace_nodetype'] = 'enumeration';
							}
						}



					}
				}

			    if ($shortnodename == "attributeGroup") {
//					if ($currentnode->hasAttributes() ) {
						$attributes = $currentnode->attributes;	
						foreach ($attributes as $index => $attrib) {
						if ($attrib->nodeName == "ref") {
								array_push($current_refs, $attrib->nodeValue);
							}
						}					
						foreach ($current_refs as $ref) {
							Misc::dom_xsd_to_referenced_array($currentnode, $ref, $array_ptr, $current_name, "attributeGroup", $superdomnode, $supertopelement, $parentContent);
						}
//					}
				}
				if ($currentnode->hasChildNodes() ) {
					foreach ($currentnode->childNodes as $childnode) {
						Misc::dom_xsd_to_referenced_array($childnode, '', $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent);
					}
				}
			} elseif ($shortnodename == "element") {
//				echo "SUPER!!!".$supertopelement." ".$currentnode->nodeName."<br />";
//				echo "3"; print_r($currentnode); echo "<br>";
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

//					if (($current_name != $supertopelement) && ($current_name != "") && (substr($parentContent ,(strrpos($parentContent, "%21"))) != $current_name)) {					
					if (($current_name != $supertopelement) && ($current_name != "")) {
						$array_ptr = &$array[$current_name];
						$parentContent .= "!".$current_name;
					    $array_ptr['espace_hyperlink'] = $parentContent;
					}
				}


				foreach ($current_refs as $ref) {
					Misc::dom_xsd_to_referenced_array($currentnode, $ref, $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent);
				}
				foreach ($current_types as $type) {
					Misc::dom_xsd_to_referenced_array($currentnode, $type, $array_ptr, $current_name, "complexType", $superdomnode, $supertopelement, $parentContent);
				}

				if ($currentnode->hasChildNodes() ) {
	//					echo "childs!";
					foreach ($currentnode->childNodes as $childnode) {
						Misc::dom_xsd_to_referenced_array($childnode, '', $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent);
					}
				}

			} else {				
//				echo "3 $shortnodename"; print_r($currentnode->nodeName); print_r($currentnode); echo $searchtype."<br>";
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
					/*if (!array_key_exists($current_name, $array)) {
						$array_ptr = &$array[$current_name];
					}*/

/*					if (isset($current_name, $array)) {
						$array_ptr = &$array[$current_name];
					}
*/
				}



				foreach ($current_refs as $ref) {
					Misc::dom_xsd_to_referenced_array($currentnode, $ref, $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent);
				}
				foreach ($current_types as $type) {
					Misc::dom_xsd_to_referenced_array($currentnode, $type, $array_ptr, $current_name, "complexType", $superdomnode, $supertopelement, $parentContent);
				}

				if ($currentnode->hasChildNodes() ) {
	//					echo "childs!";
					foreach ($currentnode->childNodes as $childnode) {
						Misc::dom_xsd_to_referenced_array($childnode, '', $array_ptr, $current_name, "", $superdomnode, $supertopelement, $parentContent);
					}
				} elseif ((count($current_refs) == 0) && (count($current_types) == 0) && ($current_name != $parentnodename)) {
					if (($current_name != $supertopelement) && ($current_name != "")) {					
						$array_ptr = &$array[$current_name];
						$parentContent .= "!".$current_name;
						$array_ptr['espace_hyperlink'] = $parentContent;
					}
				}
			}
				
			break;
		default:
			echo "in default case of node type (".$domnode->nodeType.", ".$domnode->nodeName.", ".$domnode->nodeValue.")<br />";
			break;
	}



//	print_r($array);
//	return $array_ptr;
}



function array_flatten(&$a,$pref='') {
   $ret=array();
   foreach ($a as $i => $j)
       if (is_array($j))
           $ret=array_merge($ret,Misc::array_flatten($j,$pref.$i."|"));
       else
           $ret[$pref.$i] = $j;
   return $ret;
}



function array_flatten_seperate($a) {
  $ret=array();
  $ret['xsd_path']=array();
  $ret['xsd_element']=array();
  foreach ($a as $i => $j) {
	array_push($ret['xsd_path'], $i);
	array_push($ret['xsd_element'], $j);
  }
  return $ret;
}

/*function array_to_dtree($a, $counter=0, $parent_counter=-1) {

$ret = array();
$ret[0] = 0;
$ret[1] = "";
foreach ($a as $i => $j) {
	if (is_array($j)) {
		if (!(is_numeric($i))) {
//			echo "in a";
			$ret[0] = $counter;
			$ret[1] .= "tree.add($counter, $parent_counter, '$i');\n";
//			$ret[1] .= "tree.add($counter, 0, $i)\n";			
			$tmp = array();
//			$tmp = Misc::array_to_dtree($j, $counter + 1, $counter);
//			if ($counter == -1) {
//				$tmp = Misc::array_to_dtree($j, $counter + 1, 0);
//			} else {
				$tmp = Misc::array_to_dtree($j, $counter + 1, $counter);
//			}

			$counter = $tmp[0];
			$ret[1] .= $tmp[1];
			$counter = $counter + 1;
		} else {
//			echo "in b";
			$tmp = array();
			$tmp = Misc::array_to_dtree($j, $counter, $parent_counter);
			$counter = $tmp[0];
			$ret[1] .= $tmp[1];
		}
	} else {		
//		echo "in c";
		$ret[1] .= "tree.add($counter, $parent_counter, '$j', '', 'basefrm');\n";
		$counter = $counter + 1;
	}
	$ret[0] = $counter;
}
return $ret;
}*/

/*function array_to_dtree($a, $counter=0, $parent_counter=-1) {

$ret = array();
$ret[0] = 0;
$ret[1] = "";
foreach ($a as $i => $j) {
	if (is_array($j)) {
		if (!(is_numeric($i))) {
//			echo "in a";
			$ret[0] = $counter;
			$ret[1] .= "tree.add($counter, $parent_counter, '$i');\n";
//			$ret[1] .= "tree.add($counter, 0, $i)\n";			
			$tmp = array();
//			$tmp = Misc::array_to_dtree($j, $counter + 1, $counter);
//			if ($counter == -1) {
//				$tmp = Misc::array_to_dtree($j, $counter + 1, 0);
//			} else {
				$tmp = Misc::array_to_dtree($j, $counter + 1, $counter);
//			}

			$counter = $tmp[0];
			$ret[1] .= $tmp[1];
			$counter = $counter + 1;
		} else {
//			echo "in b";
			$tmp = array();
			$tmp = Misc::array_to_dtree($j, $counter, $parent_counter);
			$counter = $tmp[0];
			$ret[1] .= $tmp[1];
		}
	} else {		
//		echo "in c";
		$ret[1] .= "tree.add($counter, $parent_counter, '$i = $j', '', 'basefrm');\n";
		$counter = $counter + 1;
	}
	$ret[0] = $counter;
}
return $ret;
}*/

function getSchemaSubAttributes($a, $top_element_name, $xdis_id, $pid) {
	global $HTTP_POST_VARS;
	$res = "";
	foreach ($a[$top_element_name] as $i => $j) {
		if (is_array($j)) {
			if (!empty($j['espace_nodetype'])) {
				if ($j['espace_nodetype'] == 'attribute') {
					$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement(urldecode($j['espace_hyperlink']), $xdis_id);					
					if (is_numeric($xsdmf_id)) {
						$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
						if ($xsdmf_details['xsdmf_espace_variable'] == "pid") {
							$res .= ' '.$i.'="'.$pid.'"';
						} elseif ($xsdmf_details['xsdmf_espace_variable'] == "xdis_id") {
							$res .= ' '.$i.'="'.$top_xdis_id.'"';
						} else {
							$res .= ' '.$i.'="'.$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id].'"';
						}
					}
				}
			}
		}
	}
	return $res;
}


function array_to_xml_instance($a, $xmlObj="", $element_prefix, $sought_node_type="", $tagIndent="", $parent_sel_id="", $xdis_id, $pid, $top_xdis_id, $attrib_loop_index="", &$indexArray=array()) {
	// @@@ CK - 6/5/2005 - Added xdis_id

//	echo $xdis_id."\n";
	global $HTTP_POST_VARS, $HTTP_POST_FILES; //echo "IN"; print_r($a); 
//	echo "<br />woot ".$parent_sel_id."<br />";
	$tagIndent .= "    ";
	foreach ($a as $i => $j) {
		if (is_array($j)) {
			if ($sought_node_type == 'attributes') {
				if ((!empty($j['espace_nodetype'])) && (!empty($j['espace_hyperlink']))) {
					if ($j['espace_nodetype'] == 'attribute') {
						// get Post attribute value if it exists
//						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement(urldecode($j['espace_hyperlink']));
						if (is_numeric($parent_sel_id)) {
							$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_ID(urldecode($j['espace_hyperlink']), $parent_sel_id, $xdis_id);
						} else {
							$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement(urldecode($j['espace_hyperlink']), $xdis_id);
						}

//						echo (urldecode($j['espace_hyperlink']))."=".$xsdmf_id."<br />\n";
						$attrib_value = "";

						if (is_numeric($xsdmf_id)) { // only add the attribute if there is an xsdmf set against it
							$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
							if ($xsdmf_details['xsdmf_enforced_prefix']) {
								$element_prefix = $xsdmf_details['xsdmf_enforced_prefix'];
							}
							if ($xsdmf_details['xsdmf_html_input'] == 'combo') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
								$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]);
								array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id])));
							} elseif ($xsdmf_details['xsdmf_html_input'] == 'multiple') {
//								if (($attrib_loop_index != "") && (is_numeric($attrib_loop_index))) { // if there is an attrib loop then just get that key index of the post variable
								if (((is_numeric($attrib_loop_index)))) { // if there is an attrib loop then just get that key index of the post variable
									$attrib_value = $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id][$attrib_loop_index];
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id][$attrib_loop_index]));
								} else {
									foreach ($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id] as $multiple_element) {
										if ($attrib_value == "") {
											if ($xsdmf_details['xsdmf_smarty_variable'] == "") {									
												$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
												array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element)));
											} else {
												$attrib_value = $multiple_element;
												array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
											}
										} else {
											// Give a tag to each value, eg DC language - english & french need own language tags
											// close the previous
											if (!is_numeric(strpos($i, ":"))) {
												$attrib_value .= "</".$element_prefix.$i.">\n";
											} else {
												$attrib_value .= "</".$i.">\n";
											}
											//open a new tag
											if (!is_numeric(strpos($i, ":"))) {
												$attrib_value .= "<".$element_prefix.$i;
											} else {
												$attrib_value .= "<".$i;
											} 
											//finish the new open tag
											if ($xsdmf_details['xsdmf_valueintag'] == 1) {
												$attrib_value .= ">\n";
											} else {
												$attrib_value .= "/>\n";
											}
	//										$attrib_value .= $xsdmf_details['xsdmf_value_prefix'];
											if ($xsdmf_details['xsdmf_smarty_variable'] == "") {
												$attrib_value .= XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
											} else {
												$attrib_value .= $multiple_element;
												array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
	//											echo "HERE -> ".$attrib_value."\n";
											}

										}
									} // end of foreach loop
								} // end of if attribute loop check

							} elseif ($xsdmf_details['xsdmf_html_input'] == 'xsdmf_id_ref') { // this assumes the xsdmf_id_ref will only refer to an xsdmf_id which is a text/textarea/combo/multiple, will have to modify if we need more
								$xsdmf_details_ref = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_details['xsdmf_id_ref']);
								if ($xsdmf_details_ref['xsdmf_html_input'] == 'combo') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
									$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']]);
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']])));									
								} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'multiple') {
									foreach ($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']] as $multiple_element) {
										if ($attrib_value == "") {
											if ($xsdmf_details['xsdmf_smarty_variable'] == "") {
												$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
												array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element)));
											} else {
												$attrib_value = $multiple_element;
												array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
											}
										} else {
											// Give a tag to each value, eg DC language - english & french need own language tags
											// close the previous
											if (!is_numeric(strpos($i, ":"))) {
												$attrib_value .= "</".$element_prefix.$i.">\n";
											} else {
												$attrib_value .= "</".$i.">\n";
											}
											//open a new tag
											if (!is_numeric(strpos($i, ":"))) {
												$attrib_value .= "<".$element_prefix.$i;
											} else {
												$attrib_value .= "<".$i;
											} 
											//finish the new open tag
											if ($xsdmf_details_ref['xsdmf_valueintag'] == 1) {
												$attrib_value .= ">\n";
											} else {
												$attrib_value .= "/>\n";
											}
											if ($xsdmf_details['xsdmf_smarty_variable'] == "") {									
												$attrib_value .= XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
												array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element)));
											} else {
												$attrib_value .= $multiple_element;
												array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
											}
										}
									}
								} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'text' 
                                        || $xsdmf_details_ref['xsdmf_html_input'] == 'textarea') {
                                    $attrib_value = $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']];
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']]));
								}
							} elseif ($xsdmf_details['xsdmf_html_input'] == 'static') {
								if ($xsdmf_details['xsdmf_espace_variable'] == "pid") {
									$attrib_value = $pid;
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $pid));
								} elseif ($xsdmf_details['xsdmf_espace_variable'] == "xdis_id") {
									$attrib_value = $top_xdis_id;
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $top_xdis_id));
								} else {
									$attrib_value = $xsdmf_details['xsdmf_static_text'];
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_static_text']));
								}
							} elseif ($xsdmf_details['xsdmf_html_input'] == 'text' || $xsdmf_details['xsdmf_html_input'] == 'textarea') {
//								if ($xsd_display_fields[i].xsdmf_multiple == 1) {
								if ($xsdmf_details['xsdmf_multiple'] == 1) {
									foreach ($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id] as $multiple_element) {
										if (!empty($multiple_element)) {
											if ($attrib_value == "") {
												$attrib_value = $multiple_element;
												array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
											} else {
												// Give a tag to each value, eg DC language - english & french need own language tags
												// close the previous
												if (!is_numeric(strpos($i, ":"))) {
													$attrib_value .= "</".$element_prefix.$i.">\n";
												} else {
													$attrib_value .= "</".$i.">\n";
												}
												//open a new tag
												if (!is_numeric(strpos($i, ":"))) {
													$attrib_value .= "<".$element_prefix.$i;
												} else {
													$attrib_value .= "<".$i;
												} 
												//finish the new open tag
												if ($xsdmf_details_ref['xsdmf_valueintag'] == 1) {
													$attrib_value .= ">\n";
												} else {
													$attrib_value .= "/>\n";
												}
												$attrib_value .= $multiple_element;
												array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
											}
										}
									}
								} else {
									if ($xsdmf_details['xsdmf_espace_variable'] == "pid") {
										$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $pid;
										array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . $pid));									
									} elseif ($xsdmf_details['xsdmf_espace_variable'] == "xdis_id") {
										$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $top_xdis_id;
										array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . $top_xdis_id));
									} else {
										$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id];
										array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]));
									}
								}
							} else {
									if ($xsdmf_details['xsdmf_espace_variable'] == "pid") {
										$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $pid;
										array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . $pid));									
									} elseif ($xsdmf_details['xsdmf_espace_variable'] == "xdis_id") {
										$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $top_xdis_id;
										array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . $top_xdis_id));									
									} else {
										$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id];
										array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]));									
									}
							}
							if ($xsdmf_details['xsdmf_enforced_prefix']) {
								$xmlObj .= ' '.$xsdmf_details['xsdmf_enforced_prefix'].$i.'="'.$xsdmf_details['xsdmf_value_prefix'] . $attrib_value.'"';
							} else {
								$xmlObj .= ' '.$i.'="'.$xsdmf_details['xsdmf_value_prefix'] . $attrib_value.'"';
							}
						}
					}
				}
			} elseif (!empty($j['espace_hyperlink'])) {
				if (!isset($j['espace_nodetype']) || $j['espace_nodetype'] != 'attribute') {
//					echo $j['espace_hyperlink']."<br />";
					if (is_numeric($parent_sel_id)) { //echo "WAF";
						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElementSEL_ID(urldecode($j['espace_hyperlink']), $parent_sel_id, $xdis_id);
					} else { //echo "KAF";
						$xsdmf_id = XSD_HTML_Match::getXSDMF_IDByElement(urldecode($j['espace_hyperlink']), $xdis_id);
					}
//					echo $xsdmf_id." <- ";
					if (is_numeric($xsdmf_id)) { // if the xsdmf_id exists - then this is the only time we want to add to the xml instance object for non attributes
//						echo "xsdmf_id is numeric -> ".$xsdmf_id."<-";
						$xmlObj .= $tagIndent;
	//					$xmlObj .= "<".$i;
						$xsdmf_details = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_id);
						if ($xsdmf_details['xsdmf_enforced_prefix']) {
							$element_prefix = $xsdmf_details['xsdmf_enforced_prefix'];
						}

						if ($xsdmf_details['xsdmf_html_input'] != 'xsd_loop_subelement') { // subloop element attributes get treated differently

							if (!is_numeric(strpos($i, ":"))) {
								$xmlObj .= "<".$element_prefix.$i;
							} else {
								$xmlObj .= "<".$i;
							} 
							$xmlObj .= Misc::array_to_xml_instance($j, "", $element_prefix, "attributes", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, "", $indexArray);
							if ($xsdmf_details['xsdmf_valueintag'] == 1) {
								$xmlObj .= ">\n";
							} else {
								$xmlObj .= "/>\n";
							}
						}	
						//if (is_numeric($xsdmf_id)) { // if the xsdmf_id exists
						// if the $xsdmd_id is of type 'xsd_loop_subelement' then get all the sub element ids and loop through them, looping through the subelements
						$attrib_value = "";
						if ($xsdmf_details['xsdmf_html_input'] == 'combo') {
							$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]);
							array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id])));									
						} elseif ($xsdmf_details['xsdmf_html_input'] == 'multiple' 
                                && isset($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id])) {
							foreach ($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id] as $multiple_element) {
								if ($attrib_value == "") {
									$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element)));									
								} else {
									// Give a tag to each value, eg DC language - english & french need own language tags
									// close the previous
									if (!is_numeric(strpos($i, ":"))) {
										$attrib_value .= "</".$element_prefix.$i.">\n";
									} else {
										$attrib_value .= "</".$i.">\n";
									}
									//open a new tag
									if (!is_numeric(strpos($i, ":"))) {
										$attrib_value .= "<".$element_prefix.$i;
									} else {
										$attrib_value .= "<".$i;
									} 
									//finish the new open tag
									if ($xsdmf_details['xsdmf_valueintag'] == 1) {
										$attrib_value .= ">\n";
									} else {
										$attrib_value .= "/>\n";
									}
									$attrib_value .= XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));									
								}
							}
						} elseif ($xsdmf_details['xsdmf_html_input'] == 'xsdmf_id_ref') { // this assumes the xsdmf_id_ref will only refer to an xsdmf_id which is a text/textarea/combo/multiple, will have to modify if we need more
						$xsdmf_details_ref = XSD_HTML_Match::getDetailsByXSDMF_ID($xsdmf_details['xsdmf_id_ref']);
						if ($xsdmf_details_ref['xsdmf_html_input'] == 'combo') { // Combo boxes only allow for one choice so don't have to go through the pain of the multiple
							$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']]);
							array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']])));
						} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'multiple') {
							foreach ($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details['xsdmf_id_ref']] as $multiple_element) {
								if ($attrib_value == "") {
									$attrib_value = XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
								} else {
									// Give a tag to each value, eg DC language - english & french need own language tags
									// close the previous
									if (!is_numeric(strpos($i, ":"))) {
										$attrib_value .= "</".$element_prefix.$i.">\n";
									} else {
										$attrib_value .= "</".$i.">\n";
									}
									//open a new tag
									if (!is_numeric(strpos($i, ":"))) {
										$attrib_value .= "<".$element_prefix.$i;
									} else {
										$attrib_value .= "<".$i;
									} 
									//finish the new open tag
									if ($xsdmf_details_ref['xsdmf_valueintag'] == 1) {
										$attrib_value .= ">\n";
									} else {
										$attrib_value .= "/>\n";
									}
									$attrib_value .= XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element);
									array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], XSD_HTML_Match::getOptionValueByMFO_ID($multiple_element)));									
								}
							}
						} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'file_input' || $xsdmf_details_ref['xsdmf_html_input'] == 'file_selector') {
//							echo "POST FILES !!! - >";
//							print_r($HTTP_POST_FILES);
//							$attrib_value = $HTTP_POST_FILES['
						} elseif ($xsdmf_details_ref['xsdmf_html_input'] == 'text' || $xsdmf_details_ref['xsdmf_html_input'] == 'textarea') {
							if ($xsdmf_details_ref['xsdmf_multiple'] == 1) {
								foreach ($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id] as $multiple_element) {
									if (!empty($multiple_element)) {
										if ($attrib_value == "") {
											$attrib_value = $multiple_element;
											array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
										} else {
											// Give a tag to each value, eg DC language - english & french need own language tags
											// close the previous
											if (!is_numeric(strpos($i, ":"))) {
												$attrib_value .= "</".$element_prefix.$i.">\n";
											} else {
												$attrib_value .= "</".$i.">\n";
											}
											//open a new tag
											if (!is_numeric(strpos($i, ":"))) {
												$attrib_value .= "<".$element_prefix.$i;
											} else {
												$attrib_value .= "<".$i;
											} 
											//finish the new open tag
											if ($xsdmf_details_ref['xsdmf_valueintag'] == 1) {
												$attrib_value .= ">\n";
											} else {
												$attrib_value .= "/>\n";
											}
											$attrib_value .= $multiple_element;
											array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
										}
									}
								}
							} else {
								$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']];
								array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_details_ref['xsdmf_id']]));
							}
						}
					} elseif ($xsdmf_details['xsdmf_html_input'] == 'static') {
						if ($xsdmf_details['xsdmf_espace_variable'] == "pid") {
							$attrib_value = $pid;
							array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $pid));													
						} elseif ($xsdmf_details['xsdmf_espace_variable'] == "xdis_id") {
							$attrib_value = $top_xdis_id;
							array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $top_xdis_id));													
//							$attrib_value = $xdis_id;
						} else {
							$attrib_value = $xsdmf_details['xsdmf_static_text'];
							array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_static_text']));						
						}
					} elseif (($xsdmf_details['xsdmf_html_input'] == 'file_input' 
                            || $xsdmf_details['xsdmf_html_input'] == 'file_selector')
                            && !empty($HTTP_POST_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']])) {
//						echo "POST FILES !!! - >";
//						print_r($HTTP_POST_FILES);
//						$attrib_value = base64_encode(fread(fopen($HTTP_POST_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']], "r"), $HTTP_POST_FILES["xsd_display_fields"]["size"][$xsdmf_details['xsdmf_id']]));
						$attrib_value = (fread(fopen($HTTP_POST_FILES["xsd_display_fields"]["tmp_name"][$xsdmf_details['xsdmf_id']], "r"), $HTTP_POST_FILES["xsd_display_fields"]["size"][$xsdmf_details['xsdmf_id']]));
						// put a full text indexer here for pdfs and word docs
//						echo $attrib_value;
//							$attrib_value = $HTTP_POST_VARS[

					} elseif ($xsdmf_details['xsdmf_html_input'] == 'text' || $xsdmf_details['xsdmf_html_input'] == 'textarea') {			
						if ($xsdmf_details['xsdmf_multiple'] == 1) {
							foreach ($HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id] as $multiple_element) {
								if (!empty($multiple_element)) {
//									echo $multiple_element;
									if ($attrib_value == "") {
										$attrib_value = $multiple_element;
										array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));						
									} else {
										// Give a tag to each value, eg DC language - english & french need own language tags
										// close the previous
										if (!is_numeric(strpos($i, ":"))) {
											$attrib_value .= "</".$element_prefix.$i.">\n";
										} else {
											$attrib_value .= "</".$i.">\n";
										}
										//open a new tag
										if (!is_numeric(strpos($i, ":"))) {
											$attrib_value .= "<".$element_prefix.$i;
										} else {
											$attrib_value .= "<".$i;
										} 
										//finish the new open tag
										if ($xsdmf_details['xsdmf_valueintag'] == 1) {
											$attrib_value .= ">\n";
										} else {
											$attrib_value .= "/>\n";
										}
										$attrib_value .= $multiple_element;
										array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $multiple_element));
									}
								}
							}
						} else {
							if ($xsdmf_details['xsdmf_espace_variable'] == "pid") {
								$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $pid;
								array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . $pid));								
							} elseif ($xsdmf_details['xsdmf_espace_variable'] == "xdis_id") {
								$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $top_xdis_id;
								array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . $top_xdis_id));								
							} else {
								$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id];
								array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . @$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]));
							}
						}
					} else { // not necessary in this side
						if ($xsdmf_details['xsdmf_espace_variable'] == "pid") {
							$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $pid;
							array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . $pid));
						} elseif ($xsdmf_details['xsdmf_espace_variable'] == "xdis_id") {
							$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . $top_xdis_id;
							array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . $top_xdis_id));
						} else {
							$attrib_value = $xsdmf_details['xsdmf_value_prefix'] . @$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id];
							array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type'], $xsdmf_details['xsdmf_value_prefix'] . @$HTTP_POST_VARS['xsd_display_fields'][$xsdmf_id]));
						}
					} 
						$xmlObj .= $attrib_value; // The actual value to store inside the element tags, if one exists
						// @@@ CK - 3/8/2005 - Added indexArray
//						array_push($indexArray, array($pid, $xsdmf_details['xsdmf_indexed'], $xsdmf_id, $xdis_id, $parent_sel_id, $xsdmf_details['xsdmf_data_type']));
						
//						print_r($xsdmf_details);
						if ($xsdmf_details['xsdmf_html_input'] == 'xsd_loop_subelement') {
							$sel = XSD_Loop_Subelement::getListByXSDMF($xsdmf_id);
							if (count($sel) > 0) { //if there are xsd sublooping elements attached to it then prepare their headers and go recursive!
								foreach($sel as $sel_record) {
									if (is_numeric($sel_record['xsdsel_attribute_loop_xsdmf_id']) && ($sel_record['xsdsel_attribute_loop_xsdmf_id'] != 0)) {
//										$attrib_loop_details = XSD_HTML_Match::getDetailsByXSDMF_ID($sel_record['xsdsel_attribute_loop_xsdmf_id']);
										if (is_array($HTTP_POST_VARS['xsd_display_fields'][$sel_record['xsdsel_attribute_loop_xsdmf_id']])) {
											$attrib_loop_count = count($HTTP_POST_VARS['xsd_display_fields'][$sel_record['xsdsel_attribute_loop_xsdmf_id']]);
										} else {
											$attrib_loop_count = 1;
										}
									} else {
										$attrib_loop_count = 1;
									}
									for ($x=0;$x<$attrib_loop_count;$x++) { // if this sel id is a loop of attributes then it will loop through each, otherwise it will just go through once
										if (!is_numeric(strpos($i, ":"))) {
											$xmlObj .= "<".$element_prefix.$i;
										} else {
											$xmlObj .= "<".$i;
										} 
										$xmlObj .= Misc::array_to_xml_instance($j, "", $element_prefix, "attributes", $tagIndent, $sel_record['xsdsel_id'], $xdis_id, $pid, $top_xdis_id, $x, $indexArray);
	//									$xmlObj .= Misc::array_to_xml_instance($j, "", $element_prefix, "attributes", $tagIndent, $sel_record['xsdsel_id'], $xsdmf_details['xsdmf_xsdsel_id']);
										if ($xsdmf_details['xsdmf_valueintag'] == 1) {
											$xmlObj .= ">\n";
										} else {
											$xmlObj .= "/>\n";
										}
	
	
	//									$xmlObj .= Misc::array_to_xml_instance($array_ptr, "", $xsd_element_prefix, "", $tagIndent, $sel_record['xsdsel_id']);
	//									$xmlObj .= Misc::array_to_xml_instance($j, "", $xsd_element_prefix, "", $tagIndent, $sel_record['xsdsel_id']);
	//									$xmlObj .= Misc::array_to_xml_instance($j, "", $element_prefix, "", $tagIndent, $sel_record['xsdsel_id'], $xsdmf_details['xsdmf_xsdsel_id']);
										$xmlObj .= Misc::array_to_xml_instance($j, "", $element_prefix, "", $tagIndent, $sel_record['xsdsel_id'], $xdis_id, $pid, $top_xdis_id, "", $indexArray);
										if ($xsdmf_details['xsdmf_valueintag'] == 1) {
											if (!is_numeric(strpos($i, ":"))) {
												$xmlObj .= "</".$element_prefix.$i.">\n";
											} else {
												$xmlObj .= "</".$i.">\n";
											}
										}
									} // end attrib for loop
								}
							}
//						} elseif ($parent_sel_id != "") {
						}
						$rel = XSD_Relationship::getListByXSDMF($xsdmf_id);
						if (count($rel) > 0) { //if there are xsd relationships attached to it then prepare their headers and go recursive!
							foreach($rel as $rel_record) {
								$tagIndent .= "    ";
								$xsd_id = XSD_Display::getParentXSDID($rel_record['xdis_id']);
								$xsd_str = Doc_Type_XSD::getXSDSource($xsd_id);
								$xsd_str = $xsd_str[0]['xsd_file'];

								$xsd_details = Doc_Type_XSD::getDetails($xsd_id);
	
								$xsd = new DomDocument();
								$xsd->loadXML($xsd_str);

								$xsd_element_prefix = $xsd_details['xsd_element_prefix'];
								$xsd_top_element_name = $xsd_details['xsd_top_element_name'];
								$xsd_extra_ns_prefixes = explode(",", $xsd_details['xsd_extra_ns_prefixes']); // get an array of the extra namespace prefixes

								$xml_schema = Misc::getSchemaAttributes($xsd, $xsd_top_element_name, $xsd_element_prefix, $xsd_extra_ns_prefixes);

								if ($xsd_element_prefix != "") {
									$xsd_element_prefix .= ":";
								}

								$array_ptr = array();

								Misc::dom_xsd_to_referenced_array($xsd, $xsd_top_element_name, &$array_ptr, "", "", $xsd);
//								print_r($array_ptr);

								$xmlObj .= $tagIndent."<".$xsd_element_prefix.$xsd_top_element_name." ";

								$xmlObj .= Misc::getSchemaSubAttributes($array_ptr, $xsd_top_element_name, $xdis_id, $pid);
								$xmlObj .= $xml_schema;
								$xmlObj .= ">\n";

								$xmlObj .= Misc::array_to_xml_instance($array_ptr, "", $xsd_element_prefix, "", $tagIndent, "", $rel_record['xsdrel_xdis_id'], $pid, $top_xdis_id, "", $indexArray);
//								$xmlObj .= Misc::array_to_xml_instance($array_ptr, "", $xsd_element_prefix, "", $tagIndent, "", $xdis_id);					
////								$xmlObj .= Misc::array_to_xml_instance($array_ptr, "", $xsd_element_prefix, "", $tagIndent, $parent_sel_id);					
								$xmlObj .= $tagIndent."</".$xsd_element_prefix.$xsd_top_element_name.">\n";
							}
						}
						//}
						$xmlObj .= Misc::array_to_xml_instance($j, "", $element_prefix, "", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, "", $indexArray);
						$xmlObj .= $tagIndent;
						if ($xsdmf_details['xsdmf_html_input'] != 'xsd_loop_subelement') { // subloop element attributes get treated differently
							if ($xsdmf_details['xsdmf_valueintag'] == 1) {
								if (!is_numeric(strpos($i, ":"))) {
									$xmlObj .= "</".$element_prefix.$i.">\n";
								} else {
									$xmlObj .= "</".$i.">\n";
								}
							}
						}
					}
				} else {
						$xmlObj .= Misc::array_to_xml_instance($j, "", $element_prefix, "", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, "", $indexArray);
//						$xmlObj .= $tagIndent;
				} // new if is numeric
			} else {
				$xmlObj = Misc::array_to_xml_instance($j, $xmlObj, $element_prefix, "", $tagIndent, $parent_sel_id, $xdis_id, $pid, $top_xdis_id, "", $indexArray);
			}
		}
	}	
	return $xmlObj;
}


function array_to_dtree($a, $xdis_id=0, $element_match_list=array(), $counter=0, $parent_counter=-1) {
$match_form_url = APP_BASE_URL."manage/xsd_tree_match_form.php?xdis_id=".$xdis_id."&xml_element=";
$ret = array();
$dtree_image = "";
$ret[0] = 0;
$ret[1] = "";
foreach ($a as $i => $j) {
	$dtree_image = "";
	if (is_array($j)) {
		if (!(is_numeric($i))) { // if not number like [0]
//			echo "in a";
			$ret[0] = $counter;
//			if (!(empty($i[0]['display']))) {
//				$ret[1] .= "tree.add($counter, $parent_counter, '".$i[0]['display']."');\n";
//			} else {
			if (($i != '#text') && ($i != '#comment')) {
				if (!empty($j['espace_nodetype'])) {
					if ($j['espace_nodetype'] == 'attribute') {
						$dtree_image = ", '../images/dtree/attribute.gif'";
					} elseif ($j['espace_nodetype'] == 'enumeration') {
						$dtree_image = ", '../images/dtree/enumeration.gif'";
					}
				}

				if 	(isset($j['espace_hyperlink']) && !is_array($j['espace_hyperlink']))  {
                    $ehref = $j['espace_hyperlink'];
                    $node_label = $i;
                    // make the tree node bold if there is a matchfields entry (i.e. we are using it)
                    if (in_array($ehref, $element_match_list)) {
                        $node_label = "<b>$node_label</b>";
                    }
                    $ehref = urlencode($ehref);
	  			  $ret[1] .= "tree.add($counter, $parent_counter, '$node_label', "
                      ."'$match_form_url$ehref', '', 'basefrm'".$dtree_image.");\n";
				} else {
				  $ret[1] .= "tree.add($counter, $parent_counter, '$i');\n";
				} 
			}
//			$ret[1] .= "tree.add($counter, 0, $i)\n";			
			$tmp = array();
//			$tmp = Misc::array_to_dtree($j, $counter + 1, $counter);
//			if ($counter == -1) {
//				$tmp = Misc::array_to_dtree($j, $counter + 1, 0);
//			} else {
				$tmp = Misc::array_to_dtree($j, $xdis_id, $element_match_list, $counter + 1, $counter);
//			}

			$counter = $tmp[0];
			$ret[1] .= $tmp[1];
			$counter = $counter + 1;
		} else {
//			echo "in b";
			$tmp = array();
			$tmp = Misc::array_to_dtree($j, $xdis_id, $element_match_list, $counter, $parent_counter);
			$counter = $tmp[0];
			$ret[1] .= $tmp[1];
		}
	} else {		
//		echo "in c";
//		$ret[1] .= "tree.add($counter, $parent_counter, '$i = $j', '', 'basefrm');\n";
		if (($i != '#text') && ($i != '#comment') && ($i != 'espace_nodetype') && ($i != 'espace_hyperlink') && (!(is_array($i['espace_hyperlink'])))) {
			if (!empty($j['espace_nodetype'])) {
				if ($j['espace_nodetype'] == 'attribute') {
					$dtree_image = ", '../images/dtree/attribute.gif'";
				} elseif ($j['espace_nodetype'] == 'enumeration') {
					$dtree_image = ", '../images/dtree/enumeration.gif'";
				}
			}
            $ehref = $j['espace_hyperlink'];
            $ehref = urlencode($ehref);
			$ret[1] .= "tree.add($counter, $parent_counter, '$i', '".$match_form_url.$ehref."', '', 'basefrm'".$dtree_image.");\n";
		} 
		$counter = $counter + 1;
	}
	$ret[0] = $counter;
}
return $ret;
}

    function collateArray($source, $ifield)
    {
        $dest = array();
        foreach ($source as $item) {
            $dest[$item[$ifield]][] = $item;
        }
        return $dest;
    }

    function collate2ColArray($source, $kfield, $vfield)
    {
        $dest = array();
        foreach ($source as $item) {
            $dest[$item[$kfield]][] = $item[$vfield];
        }
        return $dest;
    }

} // end of Misc class

// benchmarking the included file (aka setup time)
if (APP_BENCHMARK) {
    $GLOBALS['bench']->setMarker('Included Misc Class');
}
?>
