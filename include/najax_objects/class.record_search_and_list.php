<?php
/*
 * Fez 
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 4/04/2007
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
 include_once(APP_INC_PATH.'class.collection.php');
 include_once(APP_INC_PATH.'class.record.php');
 
 class RecordSearchAndList
 {
    function getSuggestion($terms)
    {
        $list = Collection::searchListing($terms,0, 25, 'Relevance', 3);
        $res = array();
        foreach ($list['list'] as $item) {
            //$record = new RecordGeneral($item['pid']);
            $text = $item['title']; //$record->getCitation();
            $value = $item['pid'];
            $res[] = compact('value','text');
        }
        return $res;
    }
 
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getSuggestion'));
        NAJAX_Client::publicMethods($this, array('getSuggestion'));
    }
 
 
 }
?>
