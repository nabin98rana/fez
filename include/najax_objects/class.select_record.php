<?php


/**
 * SelectRecord
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectRecord {

    function getCollections($community_pid)
    {
        $result = Collection::getCreatorListAssoc($community_pid);
		$list = array();
        foreach($result as $pid => $item) {
            $list[] = array('value' => $pid, 'text' => $item);
        }
        return $list;
    }

    function getRecords($collection_pid)
    {
	$options = array();		
    $options["searchKey".Search_Key::getID("Status")] = 2; // enforce published records only
    $options["searchKey".Search_Key::getID("isMemberOf")] = $collection_pid; // objects that are a member of selected pid only
    $list = Record::getListing($options, array("Editor"), 0, 100, "Title", true);		
	$listing = $list['list'];
        $list = array();
        foreach ($listing as $item) {
            $list[] = array('text' => Misc::stripOneElementArrays($item['rek_title']), 'value' => $item['rek_pid']);
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
