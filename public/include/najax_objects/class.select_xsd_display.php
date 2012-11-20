<?php

/**
 * SelectXSDDisplay 
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectXSDDisplay {

    function getMatchingFields($xdis_id)
    {
        $xsdmf_list = XSD_HTML_Match::getAssocList($xdis_id);
        $list = array();
        foreach($xsdmf_list as $key => $item) {
            if (!empty($item)) {
                $list[] = array('value' => $key, 'text' => $item);
            }
        }
        //Error_Handler::logError($list);
        return $list; 
    }
   
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getMatchingFields'));
        NAJAX_Client::publicMethods($this, array('getMatchingFields'));
    }
}
