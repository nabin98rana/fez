<?php

class Fulltext_Tools {
	
	// MIME-Types the extaction method understands
	private static $mime_types = array('application/pdf','text/plain');
	
	
    /**
     * KJ/ETH: Calculates extracts from fulltext. Helper function which is
     * not used by Solr.
     *
     * @param string $res
     * @param string $key
     */
    function getTextExtract($res, $key) {
    	$extractWidth = 180;
    	$markerColors = array('yellow', 'lightblue', 'lightgreen', 'rgb(255,160,160)');
    	$collectedExtract = '';
    	$gotAbstract = false;

    	// clean up fulltext document
	    $str = preg_replace('/'.chr(255).'/', 'X', $str);
	    $str = preg_replace('/'.chr(12).'/', '', $str);
	    //$str = preg_replace('/\. \./', '', $str);
	    //$str = preg_replace('/,,/', 'xx', $str);

	    $keyParts = spliti(' ', $key);
		//print_r($keyParts);

    	for ($i=0; $i<count($res); $i++) {
    		$str = $res[$i]['rek_file_attachment_content'];


	    	if ($key > '') {
				// try to find abstract also with fulltext search
	    		$abstractStart = strpos($str, "Abstract");
	    		if ($abstractStart !== FALSE) {
	    			$abstractText = substr($str, $abstractStart, 3*$extractWidth) . " ";
	    			if (strlen($abstractText) > 150 && !$gotAbstract) {
	    				$collectedExtract .= $abstractText;
	    				$collectedExtract = preg_replace('/(Abstract) /i', '<span style="font-weight:bold;color:darkblue;">$1: </span>', $collectedExtract);
	    				$collectedExtract .= "... ";
	    				$gotAbstract = true;
	    			}

		    	}

		    	foreach ($keyParts as $part) {

		    		//print $color;
					$pos = stripos($str, $part);
			    	if ($pos !== FALSE) {
			    		$width = $extractWidth;
			    		$left = $pos - $width;
			    		$right = $pos + $width;
			    		if ($left < 0) $left = 0;
			    		if ($right > strlen($str)) $right=strlen($str);

			    		$extract = substr($str, $left, $right-$left);

			    		// clean up extract...
			    		// mark keyword(s) with HTML
			    		$collectedExtract .= $extract . " ";
			    		//$collectedExtract .= " ... ";
			       	}

		    	}
	    	}

	    	if ($key == '' || $collectedExtract == '') {
	    		// no search key: browse mode!

	    		// try to find abstract
	    		$abstractStart = strpos($str, "Abstract");
	    		if ($abstractStart !== FALSE) {
	    			$abstractText = substr($str, $abstractStart, 3*$extractWidth) . " ";
	    			if (strlen($abstractText) > 20 && !$gotAbstract) {
	    				$collectedExtract .= $abstractText;
	    				$collectedExtract = preg_replace('/(Abstract) /i', '<span style="font-weight:bold;color:green;">$1: </span>', $collectedExtract);
	    				$collectedExtract .= "... ";
	    				$gotAbstract = true;
	    			}
	    			// beginning
	    			$collectedExtract .= substr($str, 0, min($extractWidth, strlen($str))) . " ";

	    		} else {
		    		// beginning
	    			$collectedExtract .= substr($str, 0, min($extractWidth, strlen($str))) . " ";

		    		// middle & end
		    		$collectedExtract .= substr($str, round(strlen($str)/2), $extractWidth);
		    		$collectedExtract .= substr($str, strlen($str)-$extractWidth, $extractWidth);
	    		}

	    	}
    	}

    	// highlight search keys
    	$markerCount = 0;
    	if ($key > '') {
    		//print_r($keyParts);

	    	foreach ($keyParts as $part) {
	    		$color = $markerColors[$markerCount % count($markerColors)];
	    		$part = preg_replace('/[+|-|*|.|(|)|"|\']/', '', $part);
	    		$collectedExtract = preg_replace('/('.preg_quote($part).')/i', '<span style="background-color:'.$color.';">$1</span>', $collectedExtract);
	    		$markerCount++;
	    	}
    	}


    	return utf8_encode($collectedExtract);
    }
    
    
    
    /**
     * Converts a document (as file) of suitable type to plaintext.
     *
     * @param unknown_type $mimetype
     * @param unknown_type $filename
     * @return unknown
     */
    public static function convertFile($mimetype, $filename)
    {
    	// add a random value to make sure this process keeps its own copy
    	// this could be important when having multiple indexing processes
        $textfilename = $filename."_".rand().".txt";

        // convert to plain text
        $plaintext = '';
        switch ($mimetype) {
            case 'application/pdf':
                exec(APP_PDFTOTEXT_EXEC.' -q '.$filename.' '.$textfilename);
                break;

            case 'text/plain':
                $textfilename = $filename;
                break;

            default:
                // if we couldn't convert the file, then return a blank textfilename
                $textfilename = null;
                break;
        }

        return $textfilename;
    }


    /**
     * Checks whether text can be extracted on this kind of document type.
     *
     * @param unknown_type $mimetype
     * @return unknown
     */
    public static function checkMimeType($mimetype)
    {
        return in_array($mimetype, self::$mime_types);
    }
    
}

?>