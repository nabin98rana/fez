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
 * Class to handle form validation in the server-side, duplicating the
 * javascript based validation available in most forms, to make sure
 * the data integrity is the best possible.
 *
 * @version 1.0
 * @author Jo�o Prado Maia <jpm@mysql.com>
 */

class Validation
{
    /**
     * Method used to check whether a string is totally compromised of
     * whitespace characters, such as spaces, tabs or newlines.
     *
     * @access  public
     * @param   string $str The string to check against
     * @return  boolean
     */
    function isWhitespace($str)
    {
        $str = trim($str);
        if (strlen($str) == 0) {
            return true;
        } else {
            return false;
        }
    }


	function isUserFileName($str) {
		
		$string="";
		$fileCount=0;
		$filePath=APP_PATH; # Specify the path you want to look in. 
		$dir = opendir($filePath); # Open the path
		while ($file = readdir($dir)) { 
			$filePrefix = substr($file, 0, strrpos($file, "."));
//		  if (eregi("\.php",$file)) { # Look at only files with a .php extension
//		    $string .= "$file<br />";
//			echo "$filePrefix<br />";
			if ($str == strtolower($filePrefix)) {
				return true;
			}
		    $fileCount++;
//		  }
		}
		//Also check its not already a username
		$authorDetails = Author::getDetailsByUsername($str);
		if ($authorDetails != '' && count($authorDetails) != 0) {
			return true;
		}
		return false;
	}

    /**
     * Method used to check whether an email address is a valid one.
     *
     * @access  public
     * @param   string $str The email address to check against
     * @return  boolean
     */
    function isEmail($str)
    {
        $valid_chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 
                                'j', 'l', 'k', 'm', 'n', 'o', 'p', 'q', 'r',
                                's', 't', 'u', 'w', 'v', 'x', 'y', 'z');
        $extended_chars = array('.', '_', '-', '@');
        $str = strtolower($str);

        // we need at least one @ symbol
        if (!strstr($str, '@')) {
            return false;
        }
        // and no more than one @ symbol
        if (strpos($str, '@') != strrpos($str, '@')) {
            return false;
        }
        // check for invalid characters in the email address
        for ($i = 0; $i < strlen($str); $i++) {
            if ((!in_array(substr($str, $i, 1), $valid_chars)) && 
                    (!in_array(substr($str, $i, 1), $extended_chars))) {
                return false;
            }
        }
        // email addresses need at least one dot
        if (!strstr($str, '.')) {
            return false;
        }
        // no two dots alongside each other
        if (strstr($str, '..')) {
            return false;
        }
        // the last character cannot be one of the extended ones
        if (in_array(substr($str, strlen($str)-1, 1), $extended_chars)) {
            return false;
        }
        return true;
    }


    /**
     * Method used to check whether a string has only valid (ASCII) 
     * characters.
     *
     * @access  public
     * @param   string $str The string to check against
     * @return  boolean
     */
    function hasValidChars($str)
    {
        $valid_chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 
                                'j', 'l', 'k', 'm', 'n', 'o', 'p', 'q', 'r',
                                's', 't', 'u', 'w', 'v', 'x', 'y', 'z');

        for ($i = 0; $i < strlen($str); $i++) {
            if (!in_array(substr($str, $i, 1), $valid_chars)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks the input string is a valid ORCID identifier
     *
     * @param string $str The string to check
     * @return bool True if valid else false
     */
    function isORCID($str)
    {
        return preg_match('/^\d{4}\-\d{4}\-\d{4}\-\d{4}$/', $str);
    }

    /**
     * Checks the input string is a valid People Australia identifier
     *
     * @param string $str The string to check
     * @return bool True if valid else false
     */
    function isPeopleAustraliaID($str)
    {
        return preg_match('/^(http:\/\/nla\.gov\.au\/)?nla\.party\-\d+$/', $str);
    }

    /**
     * Checks the input string is a valid Scopus ID
     *
     * @param string $str The string to check
     * @return bool True if valid else false
     */
    function isScopusID($str)
    {
        return preg_match('/^\d+$/', $str);
    }

    /**
     * Checks the input string is a valid Google Scholar identifier
     *
     * @param string $str The string to check
     * @return bool True if valid else false
     */
    function isGoogleScholarID($str)
    {
        return preg_match('/^[A-Za-z0-9_]+$/', $str);
    }
}
