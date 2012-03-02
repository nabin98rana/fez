<?php
class Page_Home extends Page_Base
{
    /**
     * Class constructor
     * @param PHPUnit_Extensions_SeleniumTestCase $selenium 
     */
    public function __construct($selenium)
    {
        $this->_selenium = $selenium;
        $this->_page_title = "Home - " . APP_NAME;
        
        // Open homepage, if we are not there already.
        if ($this->_selenium->getTitle() != $this->_page_title) {
            $this->_selenium->open("/");
        }
    }

    public function clickLogin()
    {
        $this->_selenium->click("css=a.login-btn");
        $this->_selenium->waitForPageToLoad(self::DEFAULT_TIMEOUT);
        return new Page_Login($this->_selenium);
    }
}