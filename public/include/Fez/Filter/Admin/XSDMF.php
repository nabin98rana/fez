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
    public function save(array $filterClasses, $inputName)
    {
    	$toDelete = array_diff($this->inputExists($inputName), $filterClasses);
    	foreach($filterClasses as $filterClass)
    	{
	    	$inputExists = $this->inputFilterExists($inputName, $filterClass);
	    	$classExists = class_exists($filterClass, false);
	    	$result = false;
	    	$sql = "";
	    	
	    	if($inputExists && $classExists) //update with a valid classname
	    	{
	    		$sql = "UPDATE " . APP_TABLE_PREFIX 
	    			. "input_filter SET ift_filter_class = ? WHERE ift_input_name = ? AND ift_filter_class = ?";
	    		$binding = array($filterClass, $inputName, $filterClass);
	    		
	    	}
	    	elseif((!$inputExists) && $classExists) //insert a valid classname
	    	{
	    		$sql = "INSERT INTO " . APP_TABLE_PREFIX 
	    			. "input_filter (ift_filter_class, ift_input_name) VALUES (?,?)";
	    		$binding = array($filterClass, $inputName);
	    	}
	    	
	    	
	    	try 
	    	{
	    		$result = ($sql) ? $this->db->query($sql, $binding) : false;
	    	}
	    	catch(Exception $e)
	    	{
	    		$this->log->err($e->getMessage());
	    	}
    	}
    	
    	//Delete the ones that are in the db but not in the post for this input
    	if($toDelete)
    	{
    		foreach($toDelete as $deleteClass)
    		{
    			$this->delete($inputName, $deleteClass);
    		}
    	}
    }
    
    public function inputFilterExists($inputName, $filterName)
    {
    	$result = false;
    	$sql = "SELECT ift_input_name, ift_filter_class FROM "
    	. APP_TABLE_PREFIX . "input_filter WHERE ift_input_name = ? "
    	. "AND ift_filter_class = ?";
    	 
    	try
    	{
    		$stmt = $this->db->query($sql, array($inputName, $filterName));
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
     * Check to see if an entry exists
     * @param string $inputName
     * @return mixed
     */
    public function inputExists($inputName)
    {
    	$results = array();
    	$sql = "SELECT ift_input_name, ift_filter_class FROM " 
    		. APP_TABLE_PREFIX . "input_filter WHERE ift_input_name = ?";
    	
    	try 
    	{
    		$stmt = $this->db->query($sql, array($inputName));
    		$resultsRaw = $stmt->fetchAll();
    		foreach($resultsRaw as $result)
    		{
    			$results[] = $result['ift_filter_class'];
    		}
    	}
    	catch(Exception $e)
    	{
    		$this->log->err($e->getMessage());
    	}
    	
    	return $results;
    }
    
    /**
     * Remove an entry from the database
     * @param string $inputName
     * @return mixed
     */
    public function delete($inputName, $filterClassName=null)
    {
    	$result = false;
    	if($filterClassName)
    	{
    		$sql = "DELETE FROM " . APP_TABLE_PREFIX
    		. "input_filter WHERE ift_input_name = ? AND ift_filter_class = ?";
    		$binding = array($inputName, $filterClassName);
    	}
    	else 
    	{
	    	$sql = "DELETE FROM " . APP_TABLE_PREFIX 
	    		. "input_filter WHERE ift_input_name = ?";
	    	$binding = array($inputName);
    	}
    	
    	try 
    	{
    		$result = $this->db->query($sql, $binding);
    	}
    	catch(Exception $e)
    	{
    		$this->log->err($e->getMessage());
    	}
    	
    	return $result;
    }
}