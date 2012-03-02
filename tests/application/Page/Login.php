<?php

class Page_Login extends Page_Base 
{
    public function __construct($selenium)
    {
        $this->_selenium = $selenium;
        $this->_page_title = "Login - " . APP_NAME;
        
        // Open login page, if we are not there already.
        if ($this->_selenium->getTitle() != $this->_page_title) {
            $this->_selenium->open("/login.php");
        }
    }

    public function verifyLoginForm()
    {
        $this->_selenium->verifyElementPresent("id=login_form");
    }
}
