<?php

class Fez_Filter
{
    /**
     * Data to be filtered
     * @var array
     */
	protected $data = array();
    
	/**
	 * Element/filter association
	 * @var array
	 */
    protected $filters = array();
    
    /**
     * Database object
     * @var DB_API
     */
    protected $db;
    
    /**
     * Filter object storage to 
     * promote object reuse.
     * @var array
     */
    protected $filterObjects = array();
    
    /**
     * Fez log object
     * @var Fez_Log
     */
    protected $log;
    
    public function __construct($data=null)
    {
        //Save a db object into the object
        $this->db = DB_API::get();
        
        //Fez logger
        $this->log = FezLog::get();
        
        if($data)
        {
	        $this->data = $data;
	        
	        try 
	        {
	        	$this->getFilters();
	        }
	        catch(Exception $e)
	        {
	        	$this->log->err($e->getMessage());
	        }
        }
    }
    
    /**
     * Fetch a filter object or instantiate 
     * one if it  doesn't exist yet.
     * @param string $filterClassName
     * @return boolean
     */
    protected function fetchFilter($filterClassName)
    {
        //If the filter object alrady exists, just return it.
        if(array_key_exists($filterClassName, $this->filterObjects))
        {
            return $this->filterObjects[$filterClassName];
        }
        else 
        {
            //If the $className is valid create a filter object, store it and return it.
            if(class_exists($filterClassName, false))
            {
                $this->filterObjects[$filterClassName] = new $filterClassName();
                return $this->filterObjects[$filterClassName];
            }
        }
        
        return false;
    }
    
    /**
     * Perform filtration on the object's data
     * @return array
     */
    public function process()
    {
    	foreach($this->filters as $elementToFilter => $filters)
        {
            foreach($filters as $filter)
            {
                if($filterObj = $this->fetchFilter($filter))
                {
                    //Is it a regular POST field?
                	if(isset($this->data[$elementToFilter]))
                    {
                        $this->data[$elementToFilter] = 
                        		$filterObj->filter($this->data[$elementToFilter]);
                    }
                    
                    //Is it a xsd_display_field?
                    if(isset($this->data['xsd_display_fields'][$elementToFilter]))
                    {
                        $this->data['xsd_display_fields'][$elementToFilter] = 
                        		$filterObj->filter($this->data['xsd_display_fields'][$elementToFilter]);
                    }
                }
            }
        }
        
        return $this->data;
    }
    
    /**
     * Retrieve an array of filter class names
     * pertaining to the data suplied to the 
     * object and save the array into the object.
     */
    protected function getFilters()
    {        
        $elementsToFilter = array_merge(array_keys($this->data), 
        						array_keys($this->data['xsd_display_fields']));
        
        $inputs = "'".implode("','", $elementsToFilter)."'";
        $sql = "SELECT ift_input_name, ift_filter_class FROM " 
            . APP_TABLE_PREFIX . "input_filter WHERE ift_input_name IN ($inputs)";
        //Add some param binding and try/catch here.
        $rawFilters = $this->db->fetchAll($sql);
        
        $filters = array();

        for($i=0;$i<count($rawFilters);$i++)
        {
            $filters[$rawFilters[$i]['ift_input_name']][] = 
            								$rawFilters[$i]['ift_filter_class'];
        }
        
        $this->filters = $filters;
    }
    
    /**
     * Insert or update a filter assignment
     * @param string $filterClass
     * @param string $inputName
     */
    public function save($filterClass, $inputName)
    {
    	$ex = $this->inputExists($inputName);
    	$result = false;
    	
    	if($ex)
    	{
    		$sql = "UPDATE " . APP_TABLE_PREFIX 
    			. "input_filter SET ift_filter_class = ? WHERE ift_input_name = ?";
    		
    	}
    	else 
    	{
    		$sql = "INSERT INTO " . APP_TABLE_PREFIX 
    			. "input_filter (ift_filter_class, ift_input_name) VALUES (?,?)";
    	}
    	
    	try 
    	{
    		$result = $this->db->query($sql, array($filterClass, $inputName));
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