@javascript
Feature: Create test data feature

  @jet @only @seed
  Scenario: I login as admin and create a test community/collection/record
    Given I login as administrator
    And I follow "Browse"
    And I follow "Create New Community"
    And I wait for "2" seconds
    And I fill in "Keyword 1" with "automated testing"
    And I fill in "Name" with "Test Data Community"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I press "Publish"
    And I store the test community pid for future use
    And I wait for solr
    And I wait for bgps
    And I press "Create"
    And I wait for "2" seconds
    And I fill in "Title" with "Test Data Collection"
    And I fill in "Keyword 1" with "automated testing"
    And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
    And I additionally select "Thesis Version MODS 1.0" from "XSD Display Document Types"
    And I additionally select "Book Version MODS 1.0" from "XSD Display Document Types"
    And I select "Test Data Community" from "Member of Communities"
    And I press "Publish"
    And I store the test collection pid for future use
    And I wait for solr
    And I wait for bgps
    And I select "Journal Article" from "xdis_id_top"
    And I press "Create"
    And I wait for "2" seconds
    And I fill in "Title" with "Test Data Record"
    And I fill in "Journal name" with "Test Journal"
    And I fill in "Author 1" with "Test Author"
    And I select "Article" from "Sub-type"
    And I select "2015" from "Publication date"
    And I check "Copyright Agreement"
    And I press "Publish"
    And I attach a file to the current record
    And I store the test record pid for future use
    And I wait for solr
    Then I follow "Logout"
