<?php

/**
 * SelectOrgStructure 
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectOrgStructure {

    function getSubList($org_id)
    {
		if (!is_numeric($org_id)) {
			return array();
		}
        $org_list = Org_Structure::getAssocListFullDisplay($org_id);
        $list = array();
        foreach($org_list as $key => $item) {
            $list[] = array('value' => $key, 'text' => $item);
        }
        return $list; 
    }
   
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getSubList'));
        NAJAX_Client::publicMethods($this, array('getSubList'));
    }
}
