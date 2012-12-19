<?php

/**
 * Servant class to execute liken functionality
 * for RecordImport classes
 * @author Chris Maj <c.maj@library.uq.edu.au>
 * @since November 2012
 *
 */
class FieldLiken
{
    /**
     * The ingested record
     * @var RecordImport
     */
    protected $record;
    
    /**
     * Liken methods to run
     * @var array
     */
    protected $likenMethods = array();
    
    public function __construct(RecordImport $record)
    {
        $this->record = $record;
    }
    
    /**
     * Public interface for running methods
     */
    public function run()
    {
        $this->reflect();
        $this->liken();
    }
    
    /**
     * Determine what comparison methods to run
     */
    protected function reflect()
    {
        $ref = new ReflectionClass($this->record);
        $methods = $ref->getMethods();
        
        foreach($methods as $method)
        {
            if($likenMethod = preg_match("/^liken.*/", $method->name))
            {
                $this->likenMethods[] = $method->name;
            }
        }
    }
    
    /**
     * Run comparison methods
     */
    protected function liken()
    {
        foreach($this->likenMethods as $likenMethod)
        {
            $this->record->$likenMethod();
        }
    }
}