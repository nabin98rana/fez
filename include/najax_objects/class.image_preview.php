<?php

/**
 * NajaxImagePreview
 * This class maps into the javascript through NAJAX.  
 */
class NajaxImagePreview {

    function getPreview($pid, $dsID, $width, $height, $regen, $copyright_message="", $watermark=false)
    {
        $hash = md5("$pid$dsID$width$height");

        $fname = APP_TEMP_DIR."fez_image_resize_cache_$hash.jpg";

        // Create the output file if it does not exist
        if(!is_file($fname) || $regen) {
            $imagefname = tempnam(APP_TEMP_DIR, "fez_image_");
            $imagebin = file_get_contents(APP_BASE_URL.'eserv.php?pid='.$pid.'&dsID='.$dsID);
            file_put_contents($imagefname, $imagebin);

            $command = APP_CONVERT_CMD." -resize {$width}x{$height}\> '".escapeshellcmd($imagefname)."' '".escapeshellcmd($fname)."'";
            exec($command);			
			if ($copyright != "") {
				$command = APP_CONVERT_CMD.' '.escapeshellcmd($fname).' -font Arial -pointsize 20 -draw "gravity center fill black text 0,12 \'Copyright'.$copyright_message.'\' fill white  text 1,11 \'Copyright\'" '.escapeshellcmd($fname).'';
				exec($command);
			}
			if ($watermark == true) {
				$command = APP_COMPOSITE_CMD." -dissolve 15 -tile ".escapeshellcmd(APP_PATH)."/images/".APP_WATERMARK." ".escapeshellcmd($fname)." ".escapeshellcmd($fname)."";
				exec($command);			
			}
            unlink($imagefname);

        }
        return $fname;
    }

    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getPreview' ));
        NAJAX_Client::publicMethods($this, array('getPreview'));
    }
}




?>
