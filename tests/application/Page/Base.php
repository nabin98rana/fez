<?php

class Page_Base
{
    /**
     * Default time out for wait* methods
     */
    const DEFAULT_TIMEOUT = 30000;

    /**
     * @var PHPUnit_Extensions_SeleniumTestCase
     */
    protected $_selenium;
    
    protected $_page_title;
    
    /**
     * Class constructor
     */
    public function __construct()
    {
    }
}