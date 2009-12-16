<?php
 
class Fez_Session_Data extends Zend_Db_Table_Abstract  
{
    protected $_name;

	public function __construct() {
		$this->_name = APP_TABLE_PREFIX . "sessions";
		parent::__construct();
	}
}
