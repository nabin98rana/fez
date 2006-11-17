<?php

include_once(APP_INC_PATH.'class.record.php');

class NajaxRecord extends RecordGeneral
{
    
    function NajaxRecord()
    {
    } 
    
    function setPid($pid)
    {
        return RecordGeneral::RecordGeneral($pid);
    }
    
    
	function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('setValue','setPid'));
        NAJAX_Client::publicMethods($this, array('setValue','setPid'));
    }
}
 
 
?>
