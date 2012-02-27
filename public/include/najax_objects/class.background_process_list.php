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
    
    function getUserBgProcs()
    {
        $runningProcs = array();
        $bgpsCount = 0;
        $returndata = array();
        $bgList = new BackgroundProcessList();
        $list = $bgList->getList(Auth::getUserID());
        $returndata['proclist'] = array();
        
        foreach($list as $lItem)
        {
            if($lItem['bgp_state'] == 1)
            {
                $bgpsCount++;
            }
            
            if($lItem['bgp_state'] == 1)
            {
                $returndata['proclist'][] = $lItem;
            }
        }
        
        $returndata['bgpsCount'] = $bgpsCount; 
        
        if($bgpsCount)
        {
            $returndata['hgImg'] = "bgps_running.png";
        }
        else
        {
            $returndata['hgImg'] = "bgps_stat.png";
        }
        
        return $returndata;
    }

    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getDetails','getLog', 'getUserBgProcs' ));
        NAJAX_Client::publicMethods($this, array('getDetails','getLog', 'getUserBgProcs'));
    }
}
