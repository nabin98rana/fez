<?php

class loginHelper {
  /**
   * @Given /^I login as administrator$/
   */
  static function iLoginAsAdministrator($page) {
    $page->visit("/login.php");
    $page->getSession()->wait(5000, "dojo.byId('powered-by')");
    $page->fillField("username", "admin_test");
    $page->fillField("passwd", "Ilovedonkey5");
    $page->pressButton("Login");
    $page->visit("/");

  }

  /**
   * @Given /^I login as UPO$/
   */
  static function iLoginAsUPO($page) {
    $page->visit("/login.php");
    $page->getSession()->wait(5000, "dojo.byId('powered-by')");
    $page->fillField("username", "upo_test");
    $page->fillField("passwd", "Ilovedonkey5");
    $page->pressButton("Login");
    $page->visit("/");

  }

  /**
   * @Given /^I login as super administrator$/
   */
  static function iLoginAsSuperAdministrator($page) {
    $page->visit("/login.php");
    $page->getSession()->wait(5000, "dojo.byId('powered-by')");
    $page->fillField("username", "superadmin_test");
    $page->fillField("passwd", "Ilovedonkey5");
    $page->pressButton("Login");
    $page->visit("/");
  }

  /**
   * @Given /^I login as user no groups$/
   */
  static function iLoginAsUserNoGroups($page) {
    $page->visit("/login.php");
    $page->getSession()->wait(5000, "dojo.byId('powered-by')");
    $page->fillField("username", "user_test");
    $page->fillField("passwd", "Ilovedonkey5");
    $page->pressButton("Login");
    $page->visit("/");
  }

  /**
   * @Given /^I login as thesis officer$/
   */
  static function iLoginAsThesisOfficer($page) {
    $page->visit("/login.php");
    $page->getSession()->wait(5000, "dojo.byId('powered-by')");
    $page->fillField("username", "thesisofficer_test");
    $page->fillField("passwd", "Ilovedonkey5");
    $page->pressButton("Login");
    $page->visit("/");
  }
}
