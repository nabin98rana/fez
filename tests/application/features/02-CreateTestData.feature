@javascript
Feature: Create test data feature

  @jet @jetx
  Scenario: I login as admin and create a test community/collection/record
    Given I login as administrator
    And I follow "Browse"
    And I follow "Create New Community"
    And I fill in "Name" with "Test Community"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I fill in "Keyword 1" with "automated testing"
    And I press "Publish"
    And I store the test community pid for future use
    And I press "Create"
    And I fill in "Title" with "Test Collection"
    And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
    And I select "Test Community" from "Member of Communities"
    And I fill in "Keyword 1" with "automated testing"
    And I press "Publish"
    And I store the test collection pid for future use
    And I press "Create"
    And I fill in "Title" with "Test Record"
    And I fill in "Journal name" with "Test Journal"
    And I fill in "Author 1" with "Test Author"
    And I select "Article" from "Sub-type"
    And I check "Copyright Agreement"
    And I select "2015" from "Publication date"
    And I press "Publish"
    And I store the test record pid for future use
    And I follow "Logout"
