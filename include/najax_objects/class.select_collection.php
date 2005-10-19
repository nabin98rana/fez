<?php

/**
 * SelectCreateInfo 
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectCollection {

    function getCollections($community_pid)
    {
        $collections = Collection::getEditList($community_pid);
        $list = array();
        foreach($collections as $item) {
            $pid = $item['pid'];
            $list[] = array('value' => $pid, 'text' => $item['title']);
        }
        return $list;
    }

    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getCollections' ));
        NAJAX_Client::publicMethods($this, array('getCollections'));
    }
}

?>
