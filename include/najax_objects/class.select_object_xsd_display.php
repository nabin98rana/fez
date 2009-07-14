<?php

/**
 * SelectObjectXSDDisplay 
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectObjectXSDDisplay {

    function getXSDDisplaysByObjectType($ret_id)
    {
		if ($ret_id != 0) {
			$display_list = XSD_Display::getAssocListByObjectType($ret_id);
		} else {
			$display_list = XSD_Display::getAssocListDocTypesAll();
		}
        $list = array();
		$display_list = array(-2 => 'None', -1 => 'Any') + $display_list;
        foreach($display_list as $key => $item) {
            $list[] = array('value' => $key, 'text' => $item);
        }

        return $list;
    }
   
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getXSDDisplaysByObjectType'));
        NAJAX_Client::publicMethods($this, array('getXSDDisplaysByObjectType'));
    }
}
