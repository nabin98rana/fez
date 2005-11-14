<?php

/**
 * NajaxImagePreview
 * This class maps into the javascript through NAJAX.  
 */
class NajaxImagePreview {

    function getPreview($pid, $dsID, $width, $height, $regen)
    {
        $hash = md5("$pid$dsID$width$height");

        $fname = APP_TEMP_DIR."fez_image_resize_cache_$hash.jpg";

        // Create the output file if it does not exist
        if(!is_file($fname) || $regen) {
            $imagefname = tempnam(APP_TEMP_DIR, "fez_image_");
            $imagebin = file_get_contents(APP_BASE_URL.'eserv.php?pid='.$pid.'&dsID='.$dsID);
            file_put_contents($imagefname, $imagebin);

            $command = APP_CONVERT_CMD." -resize {$width}x{$height} '$imagefname' '$fname'";
            exec(escapeshellcmd($command));
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
