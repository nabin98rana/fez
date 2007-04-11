<?php

//include_once(APP_INC_PATH.'class.author.php');
include_once(APP_INC_PATH.'class.error_handler.php');

class Suggestor
{   


    var $class_name = '';
    var $include_name = '';
    var $show_all = true;
    
    function getSuggestion($search, $min_char = 0)
    {
//		file_put_contents('/usr/local/apache/htdocs/dev-fez/error_handler.log', 'monkey', false);
//		exec("echo 'monkey' >> /usr/local/apache/htdocs/dev-fez/error_handler.log");
		if (strlen($search) < $min_char) { // not used but this could limit word searches to be at least 2 characters for the suggest search
			return array();
		}
        include_once(APP_INC_PATH.$this->include_name);
        $obj = new $this->class_name;
        $res = $obj->suggest(($search));
        $list = array();
        foreach($res as $key => $item) {
//            $list[] = array('value' => $key, 'text' => $item);
            $list[] = array('value' => $item, 'id' => $key);
        }
//		file_put_contents('/usr/local/apache/htdocs/dev-fez/error_handler.log', 'monkey', false);		
        return $list; 

//        return array_values($res);
    } 

/*    function getSuggestion($search)
    {
        $res = Author::suggest(trim($search));
//		Error_Handler::logError(array('test', 'test'), __FILE__, __LINE__);
        $list = array();
        foreach($res as $key => $item) {
            $list[] = array('id' => $key, 'value' => $item);
        }
        return $list; 
    }
*/


/*    function getId($search)
    {
        include_once(APP_INC_PATH.$this->include_name);
        $obj = new $this->class_name;
        $id = $obj->getIdFromName(trim($search));
        if (!$id) {
            $id = $obj->insertNameOnly(trim($search));
        }
        return $id;
    } */

    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getSuggestion'));
        NAJAX_Client::publicMethods($this, array('getSuggestion'));
    }
}

?>