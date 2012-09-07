<?php

class Fez_Filter_Admin_XSDMF implements Fez_Filter_AdminInterface
{
    /**
     * Database object
     * @var DB_API
     */
    protected $db;
    
    /**
     * Fez log object
     * @var Fez_Log
     */
    protected $log;
    
    public function __construct()
    {
        //Save a db object into the object
        $this->db = DB_API::get();
        
        //Fez logger
        $this->log = FezLog::get();
    }
    
    /**
     * Insert or update a filter assignment
     * @param string $filterClass
     * @param string $inputName
     */
    public function save($filterClass, $inputName)
    {
    	$inputExists = $this->inputExists($inputName);
    	$classExists = class_exists($filterClass, false);
    	$result = false;
    	$sql = "";
    	
    	if($inputExists && $classExists) //update with a valid classname
    	{
    		$sql = "UPDATE " . APP_TABLE_PREFIX 
    			. "input_filter SET ift_filter_class = ? WHERE ift_input_name = ?";
    		
    	}
    	elseif((!$inputExists) && $classExists) //insert a valid classname
    	{
    		$sql = "INSERT INTO " . APP_TABLE_PREFIX 
    			. "input_filter (ift_filter_class, ift_input_name) VALUES (?,?)";
    	}
    	elseif($inputExists && (!$classExists)) //invalid or null classname passed - no longer needed
    	{
    		$this->delete($inputName);
    	}
    	
    	try 
    	{
    		$result = ($sql) ? $this->db->query($sql, array($filterClass, $inputName)) : false;
    	}
    	catch(Exception $e)
    	{
    		$this->log->err($e->getMessage());
    	}
    }
    
    /**
     * Check to see if an entry exists
     * @param string $inputName
     * @return mixed
     */
    public function inputExists($inputName)
    {
    	$result = false;
    	$sql = "SELECT ift_input_name, ift_filter_class FROM " 
    		. APP_TABLE_PREFIX . "input_filter WHERE ift_input_name = ?";
    	
    	try 
    	{
    		$stmt = $this->db->query($sql, array($inputName));
    		$result = $stmt->fetch();
    		$result = $result['ift_filter_class'];
    	}
    	catch(Exception $e)
    	{
    		$this->log->err($e->getMessage());
    	}
    	
    	return $result;
    }
    
    /**
     * Remove an entry from the database
     * @param string $inputName
     * @return mixed
     */
    public function delete($inputName)
    {
    	$result = false;
    	$sql = "DELETE FROM " . APP_TABLE_PREFIX 
    		. "input_filter WHERE ift_input_name = ?";
    	
    	try 
    	{
    		$result = $this->db->query($sql, array($inputName));
    	}
    	catch(Exception $e)
    	{
    		$this->log->err($e->getMessage());
    	}
    	
    	return $result;
    }
}