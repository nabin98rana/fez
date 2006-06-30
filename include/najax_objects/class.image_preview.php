<?php

/**
 * NajaxImagePreview
 * This class maps into the javascript through NAJAX.  
 */
class NajaxImagePreview {

    function getPreview($pid, $dsID, $width, $height, $regen, $copyright_message="", $watermark=false)
    {
        $hash = md5("$pid$dsID$width$height");
		$real_dsID = "preview_".$dsID;
//        $imagebin = file_get_contents(APP_BASE_URL.'eserv.php?pid='.$pid.'&dsID='.$real_dsID); // http seems to fail for IE. Some bug in IE.
//        $imagebin = file_get_contents( "https://" . APP_HOSTNAME . APP_RELATIVE_URL . 'eserv.php?pid='.$pid.'&dsID='.$real_dsID);
		list($imagebin,$info) = Misc::processURL("https://" . APP_HOSTNAME . APP_RELATIVE_URL . 'eserv.php?pid='.$pid.'&dsID='.$real_dsID);
		return $imagebin;

    }

    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getPreview' ));
        NAJAX_Client::publicMethods($this, array('getPreview'));
    }
}




?>
