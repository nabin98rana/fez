<?php

class loginHelper {

  /**
   * Wait for the logout/login pages to render correctly
   *
   * @param string $page
   * @param string $user
   * @param string $pass
   * @param bool $loadLoginPage
   */
  private function login($page, $user, $pass, $loadLoginPage = true) {
    if ($loadLoginPage) {
      try {
        $page->visit("/login.php");
        $page->getSession()->wait(1000, '(document.readyState === "complete")');
      } catch (Exception $e) {
        if (strpos($e->getMessage(), 'stale') !== false) {
          echo "Found a stale element, retrying login";
        }
        sleep(2);
        $this->login($page, $user, $pass);
      }
    }

    try {
      $page->fillField("username", $user);
      $page->fillField("passwd", $pass);
      $page->pressButton("Login");
      $page->visit("/");
    } catch (Exception $e) {
      if (strpos($e->getMessage(), 'stale') !== false) {
        echo "Found a stale element, retrying login";
      }
      sleep(2);
      $this->login($page, $user, $pass, false);
    }
  }

  /**
   * @Given /^I login as administrator$/
   */
  public function iLoginAsAdministrator($page) {
    $this->login($page, "admin_test", "Ilovedonkey5");
  }

  /**
   * @Given /^I login as UPO$/
   */
  public function iLoginAsUPO($page) {
    $this->login($page, "upo_test", "Ilovedonkey5");
  }

  /**
   * @Given /^I login as super administrator$/
   */
  public function iLoginAsSuperAdministrator($page) {
    $this->login($page, "superadmin_test", "Ilovedonkey5");
  }

  /**
   * @Given /^I login as user no groups$/
   */
  public function iLoginAsUserNoGroups($page) {
    $this->login($page, "user_test", "Ilovedonkey5");
  }

  /**
   * @Given /^I login as thesis officer$/
   */
  public function iLoginAsThesisOfficer($page) {
    $this->login($page, "thesisofficer_test", "Ilovedonkey5");
  }
}
