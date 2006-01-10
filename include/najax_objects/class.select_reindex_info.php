<?php

/**
 * SelectReindexInfo 
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectReindexInfo {

    function getCollections($community_pid)
    {
        $collections = Collection::getCommunityAssocList($community_pid);
        $list = array();
        foreach($collections as $item) {
            $pid = $item['pid'];
            $list[] = array('value' => $pid, 'text' => $item['title']);
        }
        return $list;
    }

    function getDocTypes($collection_pid)
    {
	$childXDisplayOptions = Collection::getChildXDisplayOptions($collection_pid);
        $list = array();
        foreach ($childXDisplayOptions as $key => $item) {
            $list[] = array('text' => $item, 'value' => $key);
        }
        return $list;
    }
    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getCollections', 'getDocTypes'));
        NAJAX_Client::publicMethods($this, array('getCollections', 'getDocTypes'));
    }
}


?>