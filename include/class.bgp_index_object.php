<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 28/11/2006
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.community.php');
include_once(APP_INC_PATH.'class.record.php');

class BackgroundProcess_Index_Object extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_index_object.php';
        $this->name = 'Index Object';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));

        if (!empty($pid) && !is_numeric($pid)) { 
            Record::setIndexMatchingFieldsRecurse($pid, $this);
            AuthIndex::setIndexAuth($pid,true);
        } else {
            $list = Community::getList(0,10000000);
            foreach ($list['list'] as $item) {
                Record::setIndexMatchingFieldsRecurse($item['pid'], $this);
                AuthIndex::setIndexAuth($item['pid'],true);
            }
        }
        $this->setState(2);
    }
}
 
?>
