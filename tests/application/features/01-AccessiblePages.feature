# features/AccessiblePages.feature
@javascript
Feature: Check that all pages still give correct output

  @cloned
  Scenario: A user needs contact information
    Given I am on "/about"
    Then I should see "General Enquiries"
    And I should see "69775"
    And I should see "UQ eSpace"

  @cloned
  Scenario: A user needs faq information
    Given I am on "/faq"
    Then I should see "Frequently Asked Questions"
    And I should see "How do I find out more about the NHMRC and ARC Mandates?"

  @cloned
  Scenario: A user wants to browse
    Given I am on "/list/"
    And I should see "List of Communities"
    And I should not see "Create New Community"
    And I should not see "(0 results found)"

  @jet @clean
  Scenario: A user needs contact information
    Given I am on "/about"
    Then I should see "About this site"

  @jet @clean
  Scenario: A user needs faq information
    Given I am on "/faq"
    Then I should see "Frequently Asked Questions"

  @jet @clean
  Scenario: A user wants to browse
    Given I am on "/list/"
    And I should see "List of Communities"
    And I should see "(0 results found)"

  @jet
  Scenario: A user wants to do an advanced search
    Given I am on "/adv_search.php"
    And I should see "Advanced Search"
#    And I should see "All Fields"
    And I should see "Title"

  @jet
  Scenario: Users without logins should not see manage and other logged in pages
    Given I am on "/manage"
    Then I should see "You must first login to access this resource"
    When I fill in "username" with "doesnotexist"
    And I fill in "passwd" with "does not exist '>?)|"
    And I press "Login"
    Then I should see "Error: The username / password combination could not be found in the system"
    Given I am on "/my_fez_traditional.php"
    Then I should see "Login to"
    Given I am on "/preferences.php"
    Then I should see "Login to"
    Given I am on "/my_fez_traditional.php"
    Then I should see "Login to"
    Given I am on "/search_favourites.php"
    Then I should see "Login to"
    Given I am on "/my_processes.php"
    Then I should see "Login to"
    Given I am on "/favourites"
    Then I should see "Login to"

  @jet @basicadmintest
  Scenario: Testing Administrator view
    And I login as administrator
    And I am on "/manage"
    Then I should see "Administration Main"
    And I should not see "Maintenance"
    And I should not see "Configuration"
    Given I am on "/my_fez_traditional.php"
    Then I should see "Background Processes"
    And I should see "Active Work"

  @jet
  Scenario: Testing Super Administrator view
    Given I login as super administrator
    And I am on "/manage"
    Then I should see "Administration Main"
    And I should see "Maintenance"
    And I should see "Configuration"
    Given I am on "/my_fez_traditional.php"
    Then I should see "Background Processes"
    And I should see "Active Workflows"
    And I should see "Submitted for Approval"
    And I should see "In Review"

  @jet
  Scenario: Testing UPO view
    Given I login as UPO
    And I am on "/manage"
    Then I should see "Sorry, but you do not have the required permission level to access this screen"
