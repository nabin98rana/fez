<?php
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

/**
 * This class acts as a helper for miscellaneous processes. 
 * Most methods in this class should be static and provide independent assistance to the main processes/workflows.
 * 
 * @todo: 
 * This class allows methods migration on class.misc.php to this class, 
 * in order to follow Zend class naming convention, 
 * by moving all methods on class Misc to here and extending this class on Misc: class Misc extends Fez_Misc
 * 
 *
 * @version 1.0, 2012-03-01
 * @author Elvi Shu <e.shu@library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 */
class Fez_Misc {

    
    /**
     * Converts the existence of Ms Word special characters with standard Ascii characters on a string parameter.
     * 
     * @param string $str String to convert
     * @return string String that cleaned from special characters
     */
    public static function convertMsWordSpecialCharacters($str = null)
    {
        if (empty($str) || is_null($str)) {
            return "";
        }

        // Get search characters and its replacement
        $search = Fez_Misc::getMsWordSpecialCharacters();
        $replace = Fez_Misc::getMsWordSpecialCharactersReplacement();

        $str = str_replace($search, $replace, $str);
        
        return $str;
    }

    
    /**
     * Returns an array of special characters produced by our friendly neighbour Ms Word.
     * These chracters tend to have more than one Ascii codes.
     * 
     * @return array Array of special characters. 
     */
    public static function getMsWordSpecialCharacters()
    {
        $characters = array(
            "‚", // baseline single quote   
            "„", // baseline double quote
            "…", // ellipsis

            "‹", // left single guillemet
            "›", // right single guillemet
            "«", // left double guillemet
            "»", // right double guillemet

            "’", // left single quote
            "‘", // right single quote
            "“", // left double quote
            "”", // right double quote

            "ˆ", // circumflex accent
            "•", // bullet
            "–", // ndash 
            "—", // mdash
            "~", // tilde
            "†", // dagger (a second footnote)
            "‡", // double dagger (a third footnote)
            "ƒ", // florin
            "‰", // permile
            "Š", // S Hacek
            "š", // s Hacek
            "™", // trademark
            
            "α", // alpha
            "β", // beta
            "γ", // gamma
            "δ", // delta
            "ε", // epsilon
            "ζ", // zeta
            "η", // eta
            "φ"  // phi
        );
        return $characters;
    }

    
    /**
     * Returns an array of replacement characters for Ms Word special characters.
     * These replacement characters work closely with array returned by getMsWordSpecialCharacters() method.
     *
     * @return array Array of replacement characters
     */
    public static function getMsWordSpecialCharactersReplacement()
    {
        $replace = array(
            ",",    // baseline single quote   
            "'",    // baseline double quote
            "...",  // ellipsis

            "<",    // left single guillemet
            ">",    // right single guillemet
            "<<",   // left double guillemet
            ">>",   // right double guillemet

            "'",    // left single quote
            "'",    // right single quote
            "'",    // left double quote
            "'",    // right double quote

            "^",    // circumflex accent
            "-",    // bullet
            "-",    // ndash 
            "-",    // mdash
            "-",    // tilde
            "**",   // dagger (a second footnote)
            "***",  // double dagger (a third footnote)
            "NLG",  // florin
            "o/oo", // permile
            "Sh",   // S Hacek
            "sh",   // s Hacek
            "tm",   // trademark
            
            "a",    // alpha
            "b",    // beta
            "g",    // gamma
            "d",    // delta
            "e",    // epsilon
            "zd",   // zeta
            "i",    // eta
            "phi"   // phi
            
        );

        return $replace;
    }

}
