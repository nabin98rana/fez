# features/security.feature
#@javascript
Feature: Security
  In order to secure a pid
  As a website user
  I need to be able login as an administrator and go to a web page and edit security and set security so only admins can see it
  And login as as a non-administrator and not be able to access the pid view page

  Scenario: Logging in as Administrator
    Given I login as administrator
    Then I should see "You are logged in as Admin Test User"


  Scenario: Create a community
    Given I login as administrator
    And I click "BROWSE"
    And I click "Create New Community"
    And I fill in "Name" with "Security Test Community"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I fill in "Keyword(s) 1" with "automated testing"
    And I press "Publish"
    And I press "Create"
    And I fill in "Title" with "Security Test Collection"
    And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
    And I select "Security Test Community" from "Member of Communities"
    And I fill in "Keyword(s) 1" with "automated testing"
    And I press "Publish"


# Scenario: Delete a collection

# Scenario: Delete a community

#    When I fill in "front_search" with "spaghetti monster"
#    And I press "submit-button"
#    Then I should see "(0 results found)"
