<?php

class loginHelper {

  /* Wait for the logout/login pages to render correctly
   *
   *
   */
  private function waitForLogoutLogin($page) {
    $page->visit("/login.php");
    $page->getSession()->wait(10000, "dojo.byId('powered-by')");
  }

  /**
   * @Given /^I login as administrator$/
   */
  public function iLoginAsAdministrator($page) {
    $this->waitForLogoutLogin($page);
    $page->fillField("username", "admin_test");
    $page->fillField("passwd", "Ilovedonkey5");
    $page->pressButton("Login");
    $page->visit("/");

  }

  /**
   * @Given /^I login as UPO$/
   */
  public function iLoginAsUPO($page) {
    $this->waitForLogoutLogin($page);
    $page->fillField("username", "upo_test");
    $page->fillField("passwd", "Ilovedonkey5");
    $page->pressButton("Login");
    $page->visit("/");

  }

  /**
   * @Given /^I login as super administrator$/
   */
  public function iLoginAsSuperAdministrator($page) {
    $this->waitForLogoutLogin($page);
    $page->fillField("username", "superadmin_test");
    $page->fillField("passwd", "Ilovedonkey5");
    $page->pressButton("Login");
    $page->visit("/");
  }

  /**
   * @Given /^I login as user no groups$/
   */
  public function iLoginAsUserNoGroups($page) {
    $this->waitForLogoutLogin($page);
    $page->fillField("username", "user_test");
    $page->fillField("passwd", "Ilovedonkey5");
    $page->pressButton("Login");
    $page->visit("/");
  }

  /**
   * @Given /^I login as thesis officer$/
   */
  public function iLoginAsThesisOfficer($page) {
    $this->waitForLogoutLogin($page);
    $page->fillField("username", "thesisofficer_test");
    $page->fillField("passwd", "Ilovedonkey5");
    $page->pressButton("Login");
    $page->visit("/");
  }
}
