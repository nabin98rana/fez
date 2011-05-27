<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");

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
        if(is_dir($this->bookDir))
        {
            return count(array_filter(scandir($this->bookDir),
                         array($this, 'ct')));
        }
    }

    public function ct($element)
    {
        return !in_array($element, array('.','..'));
    }
}