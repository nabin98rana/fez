<?php

class Functional_HomepageTest extends Functional_Base {

    /**
     * Tests the LibStats front page as an anonymous user
     */
    public function testWelcomeText()
    {
        $this->open("/");
        $this->verifyTextPresent("Welcome to " . APP_ORG_NAME . "'s institutional digital repository");

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

}
