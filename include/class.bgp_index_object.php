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
include_once(APP_INC_PATH . "class.reindex.php");


@define('INDEX_TYPE_FEDORAINDEX', 1);
@define('INDEX_TYPE_REINDEX', 2);


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
        $GLOBALS['app_cache'] = false; //SWITCH OFF the $GLOBALS['app_cache'] var for the bgp indexing
        $this->setStatus("Disabled app_cache for background -> app_cache = ".$GLOBALS['app_cache']." <-");
        $reindex = new Reindex;

        if (empty($terms)) {
            $terms = '';
        }
        if (empty($params)) {
            $params = array();
        }
        if (empty($index_type)) {
            $index_type = INDEX_TYPE_REINDEX;
        }
        $reindex->bgp = $this;
        if ($index_type == INDEX_TYPE_FEDORAINDEX) {
            $reindex->reindexMissingList($params,$terms);
        } else {
            $reindex->reindexFullList($params,$terms);
        }
        $this->setState(2);        
    }
}
 
?>
