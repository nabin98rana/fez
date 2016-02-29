<?php

/**
 * NajaxImagePreview
 * This class maps into the javascript through NAJAX.  
 */
class NajaxImagePreview {

    function getPreview($pid, $dsID)
    {
		$real_dsID = "preview_".$dsID;
		list($imagebin,$info) = Misc::processURL("https://" . APP_HOSTNAME . APP_RELATIVE_URL . 'eserv.php?pid='.$pid.'&dsID='.$real_dsID);
		return $imagebin;

    }

    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getPreview' ));
        NAJAX_Client::publicMethods($this, array('getPreview'));
    }
}
