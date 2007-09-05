<?php

/**
 * SelectCreateInfo 
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectCreateInfo {

    function getCollections($community_pid)
    {
        $result = Collection::getEditListAssoc($community_pid);
		$list = array();
        foreach($result as $pid => $item) {
            $list[] = array('value' => $pid, 'text' => $item);
        }
        return $list;
    }

    function getDocTypes($collection_pid)
    {
		$childXDisplayOptions = Record::getSearchKeyIndexValue($collection_pid, "XSD Display Option");
//	$childXDisplayOptions = Collection::getChildXDisplayOptions($collection_pid);
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
