<?php
include_once(APP_INC_PATH.'class.error_handler.php');
class Suggestor
{   
    var $class_name = '';
    var $include_name = '';
    var $show_all = true;
    var $method = 'suggest';
    
    function getSuggestion($search, $min_char = 0)
    {
		if (strlen($search) < $min_char) { // not used but this could limit word searches to be at least 2 characters for the suggest search
			return array();
		}
        include_once(APP_INC_PATH.$this->include_name);
        $obj = new $this->class_name;
        $res = call_user_func(array($obj, $this->method), $search);
        $list = array();
        foreach($res as $key => $item) {
            $list[] = array('value' => $item, 'id' => $key);
        }
        return $list; 

    } 
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getSuggestion'));
        NAJAX_Client::publicMethods($this, array('getSuggestion'));
    }
}
?>