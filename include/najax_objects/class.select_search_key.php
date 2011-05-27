<?php

/**
 * SelectSearchKey
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectSearchKey {

    function getSearchKeyOptions($sek_id)
    {
        $log = FezLog::get();
        $list_field = Search_Key::getDetails($sek_id);
        $list = array();
        $result = array();

        if ($list_field['sek_html_input'] == 'contvocab' && $list_field['sek_cardinality'] != 1) {
         $cv = new Controlled_Vocab();
         $result = $cv->getAssocListFullDisplay($list_field['sek_cvo_id']);
        }
        
        foreach($result as $pid => $item) {
            $list[] = array('value' => $pid, 'text' => $item);
        }
        return $list;
    }

    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getSearchKeyOptions'));
        NAJAX_Client::publicMethods($this, array('getSearchKeyOptions'));
    }
}
