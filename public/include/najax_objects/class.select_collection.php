<?php

/**
 * SelectCreateInfo 
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectCollection {

    function getCollections($community_pid)
    {
        $result = Collection::getCreatorListAssoc($community_pid);
		$list = array();
        foreach($result as $pid => $item) {
            $list[] = array('value' => $pid, 'text' => $item);
        }
        return $list;
    }

    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getCollections'));
        NAJAX_Client::publicMethods($this, array('getCollections'));
    }
}
