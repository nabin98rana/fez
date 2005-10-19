<?php


/**
 * SelectRecord
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectRecord {

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

    function getRecords($collection_pid)
    {
	$listing = Collection::getEditListing($collection_pid);
        $list = array();
        foreach ($listing as $item) {
            $list[] = array('text' => Misc::stripOneElementArrays($item['title']), 'value' => $item['pid']);
        }
        return $list;
    }
    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getCollections', 'getRecords'));
        NAJAX_Client::publicMethods($this, array('getCollections', 'getRecords'));
    }
}

?>
