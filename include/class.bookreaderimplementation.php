<?php

include_once("config.inc.php");

class bookReaderImplementation
{
    private $log;
    private $bookDir;

    public function __construct($bookDir)
    {
        $this->log = FezLog::get();
        $this->bookDir = $bookDir;
    }

    /**
     * Return the number of pages in this resource minus '.' and '..'.
     * @return int
     */
    public function countPages()
    {
        return count(array_filter(scandir($this->bookDir),
                         function($element){return !in_array($element, array('.','..'));}));
    }
}