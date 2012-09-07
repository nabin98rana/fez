<?php

class Fez_Filter_Process_Request implements Fez_Filter_Process_ProcessInterface
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
    
    public function __construct(array $data)
    {
        //Save a db object into the object
        $this->db = DB_API::get();
        
        //Fez logger
        $this->log = FezLog::get();
        
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
                    //Is it a regular field?
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
        if(array_key_exists('xsd_display_fields', $this->data))
        {
    		$elementsToFilter = array_merge(array_keys($this->data), 
        						array_keys($this->data['xsd_display_fields']));
        }
        else 
        {
        	$elementsToFilter = array_keys($this->data);
        }
        
        $tokens = rtrim(str_repeat('?,', count($elementsToFilter)), ',');
        $sql = "SELECT ift_input_name, ift_filter_class FROM " 
            . APP_TABLE_PREFIX . "input_filter WHERE ift_input_name IN ($tokens)";
        
        try 
        {
	        $stmt = $this->db->query($sql, $elementsToFilter);
        	$rawFilters = $stmt->fetchAll();
	        
	        $filters = array();
	
	        for($i=0;$i<count($rawFilters);$i++)
	        {
	            $filters[$rawFilters[$i]['ift_input_name']][] = 
	            								$rawFilters[$i]['ift_filter_class'];
	        }
	        
	        $this->filters = $filters;
        }
        catch(Exception $e)
        {
        	$this->log->err($e->getMessage());
        }
    }
}