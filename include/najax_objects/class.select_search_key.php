<?php

/**
 * SelectSearchKey
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectSearchKey {

    function getSearchKeyOptions($sek_id)
    {
        $list = array();
        $list_field = Search_Key::getDetails($sek_id);
        if (!empty($list_field["sek_smarty_variable"]) && $list_field["sek_smarty_variable"] != "none") {
            eval("\$list['field_options'] = " . $list_field["sek_smarty_variable"] . ";");
        }

		/*$list = array();
        foreach($result as $pid => $item) {
            $list[] = array('value' => $pid, 'text' => $item);
        }*/
        return $list['field_options'];
    }

    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getSearchKeyOptions'));
        NAJAX_Client::publicMethods($this, array('getSearchKeyOptions'));
    }
}
