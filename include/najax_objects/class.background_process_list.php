<?php
include_once(APP_INC_PATH.'class.background_process_list.php');

/**
 * Allows the background process display list to update on the page
 */
class NajaxBackgroundProcessList {

    function getDetails($bgp_id)
    {
        $res = BackgroundProcessList::getDetails($bgp_id);
			
		  $res["bgp_started"] = Date_API::getFormattedDate($res["bgp_started"], APP_DEFAULT_USER_TIMEZONE);
		  $res["bgp_heartbeat"] = Date_API::getFormattedDate($res["bgp_heartbeat"], APP_DEFAULT_USER_TIMEZONE);
		
        return $res;
    }

    function getLog($bgp_id)
    {
        $result = BackgroundProcessList::getLog($bgp_id);
        return nl2br(htmlspecialchars(wordwrap($result, 70, "\n", true)));
    }

    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getDetails','getLog' ));
        NAJAX_Client::publicMethods($this, array('getDetails','getLog'));
    }
}
