# features/Delete.feature
@javascript @destructive @jet @datadependant
Feature: Test that deleting communities/collections/records works correctly

  Scenario: Delete record
    Given I login as administrator
    And I go to the test collection list page
    And I wait for "2" seconds
    And I select "Journal Article" from "xdis_id_top"
    And I press "Create"
    And I fill in "Title" with "Security Test Name 2012"
    And I fill in "Journal name" with "Security Test Journal Publication"
    And I fill in "Author 1" with "Security Test Writer Name"
    And I select "Article" from "Sub-type"
    And I check "Copyright Agreement"
    And I select "2010" from "Publication date"
    And I select "10" from "Publication date month"
    And I select "20" from "Publication date day"
    And I press "Publish"
    And I wait for bgps
    And I wait for solr
    And I follow "More options"
    And I follow "Delete Selected Record"
    And I fill in "History Detail" with "Testing record deletion"
    And I press "Delete"
    And I should see "This record has been deleted."
    And I should not see "Title"
    And I should not see "Journal Name"
    And I should not see "Sub-type"
    And I turn off waiting checks
    And I follow "Detailed History"
    And I switch to window "_impact"
    And I should see "Delete Selected Record"
    And I press "Close"
    And I switch to window ""
    And I turn on waiting checks
    And I follow "Logout"
    And I wait for solr
    And I wait for bgps
    When I move backward one page
    And I should see "This record has been deleted."
    And I should not see "Title"
    And I should not see "Journal Name"
    And I should not see "Sub-type"
    And I should not see "Detailed History"
    And I wait for solr
    And I wait for bgps
    And I carefully fill search entry with "title:(\"Security Test Name 2012\")"
    And I press "search_entry_submit"
    Then I should see "(0 results found)"

  Scenario: Delete Collection
    Given I login as administrator
    And I go to the test community page
    And I press "Create"
    And I wait for "2" seconds
    And I fill in "Title" with "Delete Test Collection"
    And I fill in "Keyword 1" with "automated testing"
    And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
    And I select "Test Data Community" from "Member of Communities"
    And I press "Publish"
    And I wait for "2" seconds
    And I wait for solr
    And I wait for bgps
    And I carefully fill search entry with "title:(\"Delete Test Collection\")"
    And I press "search_entry_submit"
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I wait for "2" seconds
    And I confirm the popup
    And I turn on waiting checks
    And I am on the homepage
    And I wait for solr
    And I wait for bgps
    And I wait for "2" seconds
    And I carefully fill search entry with "title:(\"Delete Test Collection\")"
    And I press "search_entry_submit"
    Then I should see "(0 results found)"

  Scenario: Delete Community
    Given I login as administrator
    And I follow "Browse"
    And I follow "Create New Community"
    And I wait for "2" seconds
    And I fill in "Keyword 1" with "automated testing"
    And I fill in "Name" with "Delete Test Community"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I press "Publish"
    And I wait for "2" seconds
    And I wait for solr
    And I wait for bgps
    And I carefully fill search entry with "title:(\"Delete Test Community\")"
    And I press "search_entry_submit"
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I wait for "2" seconds
    And I confirm the popup
    And I turn on waiting checks
    And I am on the homepage
    And I wait for solr
    And I wait for bgps
    And I wait for "2" seconds
    And I carefully fill search entry with "title:(\"Delete Test Community\")"
    And I press "search_entry_submit"
    Then I should see "(0 results found)"
