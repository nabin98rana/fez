<?php

class loginHelper {

  /* Wait for the logout/login pages to render correctly
   *
   *
   */
  private function waitForLogoutLogin($page) {
    try {
      $page->visit("/login.php");
      $page->getSession()->wait(10000, "dojo.byId('powered-by')");
    } catch (Exception $e) {
      if (strpos($e->getMessage(), 'stale') !== false) {
        echo "Found a stale element, retrying login";
        sleep(2);
        $this->waitForLogoutLogin($page);
      }
    }
  }

  /**
   * @Given /^I login as administrator$/
   */
  public function iLoginAsAdministrator($page) {
    try {
      $this->waitForLogoutLogin($page);
      $page->fillField("username", "admin_test");
      $page->fillField("passwd", "Ilovedonkey5");
      $page->pressButton("Login");
      $page->visit("/");
    } catch (Exception $e) {
      if (strpos($e->getMessage(), 'stale') !== false) {
        echo "Found a stale element, retrying login";
        sleep(2);
        $this->iLoginAsAdministrator($page);
      }
    }
  }

  /**
   * @Given /^I login as UPO$/
   */
  public function iLoginAsUPO($page) {
    try {
      $this->waitForLogoutLogin($page);
      $page->fillField("username", "upo_test");
      $page->fillField("passwd", "Ilovedonkey5");
      $page->pressButton("Login");
      $page->visit("/");
    } catch (Exception $e) {
      if (strpos($e->getMessage(), 'stale') !== false) {
        echo "Found a stale element, retrying login";
        sleep(2);
        $this->iLoginAsUPO($page);
      }
    }
  }

  /**
   * @Given /^I login as super administrator$/
   */
  public function iLoginAsSuperAdministrator($page) {
    try {
      $this->waitForLogoutLogin($page);
      $page->fillField("username", "superadmin_test");
      $page->fillField("passwd", "Ilovedonkey5");
      $page->pressButton("Login");
      $page->visit("/");
    } catch (Exception $e) {
      if (strpos($e->getMessage(), 'stale') !== false) {
        echo "Found a stale element, retrying login";
        sleep(2);
        $this->iLoginAsSuperAdministrator($page);
      }
    }
  }

  /**
   * @Given /^I login as user no groups$/
   */
  public function iLoginAsUserNoGroups($page) {
    try {
      $this->waitForLogoutLogin($page);
      $page->fillField("username", "user_test");
      $page->fillField("passwd", "Ilovedonkey5");
      $page->pressButton("Login");
      $page->visit("/");
    } catch (Exception $e) {
      if (strpos($e->getMessage(), 'stale') !== false) {
        echo "Found a stale element, retrying login";
        sleep(2);
        $this->iLoginAsUserNoGroups($page);
      }
    }
  }

  /**
   * @Given /^I login as thesis officer$/
   */
  public function iLoginAsThesisOfficer($page) {
    try {
      $this->waitForLogoutLogin($page);
      $page->fillField("username", "thesisofficer_test");
      $page->fillField("passwd", "Ilovedonkey5");
      $page->pressButton("Login");
      $page->visit("/");
    } catch (Exception $e) {
      if (strpos($e->getMessage(), 'stale') !== false) {
        echo "Found a stale element, retrying login";
        sleep(2);
        $this->iLoginAsThesisOfficer($page);
      }
    }
  }
}
