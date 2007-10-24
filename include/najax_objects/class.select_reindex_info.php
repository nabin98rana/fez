<?php

/**
 * SelectReindexInfo 
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectReindexInfo {

    function getCollections($community_pid)
    {
		$return = array();
		if ($community_pid == "") {
			$return[] = array('value' => "", 'text' => "(no collection)");
			return $return;					
		} else {
			$options = array();				
		    $options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
		    $options["searchKey".Search_Key::getID("Object Type")] = 2; // enforce published records only
		    $options["searchKey".Search_Key::getID("isMemberOf")] = $community_pid; // objects that are a member of selected pid only
		    $list = Record::getListing($options, array("Lister"), 0, 1000, "Title", true);
			$listing = $list['list'];
		}

        foreach($listing as $item) {
            $pid = $item['rek_pid'];
            $return[] = array('value' => $pid, 'text' => $item['rek_title']);
        }
        return $return;
    }

    function getDocTypes($collection_pid)
    {
		if ($collection_pid == "") {
			$childXDisplayOptions = XSD_Display::getAssocListDocTypesAll();
		} else {
			$childXDisplayOptions = Record::getSearchKeyIndexValue($collection_pid, "XSD Display Option");
		}
		//$childXDisplayOptions = Collection::getChildXDisplayOptions($collection_pid);
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