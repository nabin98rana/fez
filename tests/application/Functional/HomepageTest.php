<?php
/**
 * Test suite for Homepage Testing
 */
class Functional_HomepageTest extends Functional_Base 
{
    /**
     * Tests the LibStats front page as an anonymous user
     */
    public function testGotoHomepage()
    {
        $this->open("/");
        $this->verifyTextPresent("Welcome to " . APP_ORG_NAME . "'s institutional digital repository");
//        try {
//            $this->assertTrue($this->isTextPresent("Welcome to The University of Queensland's institutional digital repository"));
//            echo 'success';
//        }
//        catch (PHPUnit_Framework_AssertionFailedError $e) {
//            array_push($this->verificationErrors, $e->toString());
//            echo 'get lost';
//        }
        

        // exported from Selenium IDE
//        $this->open("/");
//        try {
//            $this->assertTrue($this->isElementPresent("id=intro-text"));
//        }
//        catch (PHPUnit_Framework_AssertionFailedError $e) {
//            array_push($this->verificationErrors, $e->toString());
//        }
//        try {
//            $this->assertTrue($this->isTextPresent("Welcome to The University of Queensland's institutional digital repository"));
//        }
//        catch (PHPUnit_Framework_AssertionFailedError $e) {
//            array_push($this->verificationErrors, $e->toString());
//        }
    }
    
    public function testGotoLogin()
    {
        $homePage = new Page_Home($this);
        $loginPage = $homePage->clickLogin();
        $loginPage->verifyLoginForm();
    }

}
