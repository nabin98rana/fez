<?php

class Functional_HomepageTest extends Functional_Base
{
  /**
   * Tests the LibStats front page as an anonymous user
   */
  public function testWelcomeText()
  {
    $this->open("/");
    $this->verifyTextPresent("Welcome to ". APP_ORG_NAME ."'s institutional digital repository");
  }

}
