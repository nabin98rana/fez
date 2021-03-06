<?php

/**
 * SelectCreateInfo 
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectCreateInfo {

    function getCollections($community_pid)
    {
		$return = array();
		if ($community_pid == "") {
			$return[] = array('value' => "", 'text' => "(no collection)");
			return $return;					
		} else {
	        $result = Collection::getCreatorListAssoc($community_pid);
			$list = array();
		}

        foreach($result as $pid => $item) {
            $list[] = array('value' => $pid, 'text' => $item);
        }
        return $list;
    }

    function getDocTypes($collection_pid)
    {
	
		if ($collection_pid == "") {
			$childXDisplayOptions = XSD_Display::getAssocListDocTypes();
		} else {
			$childXDisplayOptions = Record::getSearchKeyIndexValue($collection_pid, "XSD Display Option");
		}
        $list = array();
        foreach ($childXDisplayOptions as $key => $item) {
            if (!empty($item)) {
                $list[] = array('text' => $item, 'value' => $key);
            }
        }
        return $list;
    }
    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getCollections', 'getDocTypes'));
        NAJAX_Client::publicMethods($this, array('getCollections', 'getDocTypes'));
    }
}
