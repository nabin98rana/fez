<?php
// +----------------------------------------------------------------------+
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
 * Class that can do spell checking of user input, and present it in different ways.
 *
 * This class uses aspell to check spelling. It will sort the return from aspell
 * based on similarity. It is safe to use this class, even if the pspell
 * extension is not installed. It will then always return empty arrays
 * when checking for spelling.
 *
 * @package default
 * @version 1.0
 * @since 26. September 2006
 * @author Stian Berger (stian at twodeadducks dot net)
 */
 class spellcheck {
 	/**
 	 * Stores the misspelled word globaly, to use it in the sorting algorithm.
 	 * @var string;
 	 */
	var $word;
	/**
	 * The link to aspell, trough the pspell extension.
	 * @var int
	 */
	var $pspell_link;
	/**
	 * Resource for the spell configuration.
	 * Only used when we use pspell_new_config for the pspell_link.
	 * @var int
	 */
	var $conf;

/**
 * Constructor function.
 *
 * Creates a link to aspell through the pspell extension.
 * @param string $lang Aspell dictionary.
 */
	function spellcheck($lang="no") {
		if(!function_exists("pspell_new")) {
			//trigger_error("Could not find pspell.",E_USER_NOTICE);
			
			return false;
		}
		//pspell_new_config() creates lots of bad suggestions ???
/*		$this->conf = pspell_config_create($this->lang);
		pspell_config_ignore($this->conf,3);
		pspell_config_mode($this->conf, PSPELL_FAST);
		$this->pspell_link = pspell_new_config($this->conf);*/
		$this->pspell_link = pspell_new($lang,"","","utf-8",PSPELL_FAST);
	}
	/**
	 * Methosd that checks spelling of a string.
	 *
	 * The method splits the string into an array, and sends each word
	 * to aspell for checkup. The result is then sorted and returned in
	 * an array containing offset of the word, the bad word and all the
	 * suggestions.
	 *
	 * If pspell extension doesn't exists or we failed creating a link to aspell,
	 * this method returns an empty array. Same behavior as if no words
	 * are spelled wrong.
	 *
	 * <samp>
	 * Array(
	 * 	[word offset] => Array (
	 * 		[word] => bad word
	 * 		[alt] => Array (
	 * 			Array containing alternative spellings
	 * 		)
	 * 	)
	 * </samp>
	 *
	 * @param string	$string	String to check
	 * @param bool		$all	If you want to return all checked words regardless if typo or not. Default is false.
	 * @return array Suggestions
	 */
	function check_spelling($string,$all=false) {
		if(!preg_match_all("/[a-zæøå]{3,}/i",$string,$words,PREG_OFFSET_CAPTURE)) {
			return array();
		}
		if(!function_exists('pspell_check')) {
			return array();
		}
		if($this->pspell_link === false) {
			return array();
		}
		$suggestions = array();
		foreach($words[0] as $word_and_offset) {
			$word = $word_and_offset[0];
			$offset = $word_and_offset[1];
			$this->word = $word;
			$alternatives = pspell_suggest($this->pspell_link, $word);
			$check = pspell_check($this->pspell_link, $word);
			$dist = levenshtein($word,$alternatives[0]);;
			usort($alternatives,array(&$this,"alt_sort"));
			if((strtolower($alternatives[0]) != strtolower($word) && $dist < 4) || $all) {
				$suggestions[$offset]['word'] = $word;
				$suggestions[$offset]['alt'] = $alternatives;
			}
		}
		return $suggestions;
	}

	/**
	 * Function that automatically selects first suggestion (wich should be the closest).
	 *
	 * This mehod is used for making something similar to googles "did you mean: ..."
	 *
	 * @param string $string Search string
	 * @return string Same string as input but with bad words replaced.
	 */
	function query_suggest($string) {
		$result = $this->check_spelling($string);
		if(!empty($result)) {
			foreach($result as $suggest) {
				$string = preg_replace("/\b{$suggest['word']}\b/i",str_replace("-"," ",$suggest['alt'][0]),$string);
			}
			return $string;
		}
		return false;
	}

	/**
	 * Method to highlight bad words in a text.
	 *
	 * The method will put bad words in an abbr tag with an onclick javascript
	 * event that calls for a function called suggest. The JS suggest function
	 * wil generate a dropdown menu beneath the bad word with alternate spellings.
	 * NB: Method needs an external JS function to work properly.
	 * TODO: Regex to skip tag text.
	 *
	 * @param string $string Text to check
	 * @return string Same as input but with highlighted bad words.
	 */
	function check_text($string) {
		$result = $this->check_spelling($string);
		$offset_shift = 0;
		if(!empty($result)) {
			foreach($result as $offset => $suggest) {
				$word = $suggest['word'];
				$bad_length = strlen($word);
				$word = "<abbr id=\"{$word}_{$offset}\" onClick=\"suggest('{$word}_{$offset}',new Array('".implode("','",$suggest['alt'])."'))\" title=\"{$suggest['alt'][0]}\">$word</abbr>";
				$string = substr_replace($string,$word,$offset+$offset_shift,$bad_length);
				$offset_shift += strlen($word)-$bad_length;
			}
		}
		return $string;
	}

	/**
	 * Method used by usort, to sort the returned suggestions from aspell.
	 *
	 * Description of what goes on is described line by line in the code.
	 *
	 * @access private
	 * @param string $a First compare value
	 * @param string $b Second compare value
	 * @return int Sort weight value
	 */
	function alt_sort($a,$b) {
		$meta = metaphone($this->word);
		//splits characters into an array, so that we can check for introduction of new characters
		//with array_diff.
		$split_word = str_split($this->word);
		$split_a = str_split($a);
		$split_b = str_split($b);
		// Pure levenshtein distance between suggestion and word.
		$lev_a = levenshtein($a,$this->word,1,1,1);
		$lev_b = levenshtein($b,$this->word,1,1,1);
		// Levenshtein distance between the metaphone of suggestion compared with metaphone of original word.
		$met_a = levenshtein(metaphone($a),$meta,1,1,1);
		$met_b = levenshtein(metaphone($b),$meta,1,1,1);
		//Similar metaphones are pushed forward.
		$return += strcmp($met_a,$met_b);
		$return -= strcmp($met_b,$met_a);
		//If word already exists in the list, push it forward.
		$return -= (strtolower($a) == strtolower($this->word)) ? 1 : 0;
		$return += (strtolower($b) == strtolower($this->word)) ? 1 : 0;
		//Words that introduce new characters is pushed backward.
		$return += count(array_merge(array_diff($split_word,$split_a),array_diff($split_a,$split_word)));
		$return -= count(array_merge(array_diff($split_word,$split_b),array_diff($split_b,$split_word)));
		//Words that are split with space or - are pushed way back.
		$return += strpbrk($a,"- ") ? 6 : 0;
		$return -= strpbrk($b,"- ") ? 6 : 0;
		//Words that needs the least deletes, inserts and replacements are pushed forward.
		$return += strcmp($lev_a,$lev_b);
		$return -= strcmp($lev_b,$lev_a);
		return $return;
	}
}
?>
