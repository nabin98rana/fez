@javascript @destructive @jet @nodata
Feature: Changing Collection Security

  Scenario: I login as admin and set a Collection security to allow inheriting pids to view, then turn it off and check pids can no longer be viewed
    Given I login as administrator
    And I follow "Browse"
    And I follow "Create New Community"
    And I wait for "2" seconds
    And I fill in "Name" with "Test Community Security to be changed after pid created"
    And I fill in "Keyword 1" with "automated testing"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I press "Publish"
    And I wait for bgps
    And I wait for solr
    And I temporarily store the record pid
    And I press "Create"
    And I wait for "2" seconds
    And I fill in "Title" with "Test Collection Security to be changed after pid created"
    And I fill in "Keyword 1" with "automated testing"
    And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
    And I select "Test Community Security to be changed after pid created" from "Member of Communities"
    And I press "Publish"
    And I wait for bgps
    And I wait for solr
    And I press "Create"
    And I wait for "2" seconds
    And I fill in "Title" with "Test Pid Security to be changed after pid created"
    And I select "2010" from "Publication date"
    And I select "10" from "Publication date month"
    And I select "20" from "Publication date day"
    And I fill in "Journal name" with "Security Test Journal name"
    And I fill in "Author 1" with "Security Test Author name"
    And I select "Article" from "Sub-type"
    And I check "Copyright Agreement"
    And I press "Publish"
    And I follow "Logout"
    And I wait for solr
    And I wait for bgps
    And I am on the homepage
    And I carefully fill search entry with "title:(\"Test Community Security to be changed after pid created\")"
    And I press "search_entry_submit"
    Then I should not see "No records could be found"
    And I carefully fill search entry with "title:(\"Test Collection Security to be changed after pid created\")"
    And I press "search_entry_submit"
    Then I should not see "No records could be found"
    And I carefully fill search entry with "title:(\"Test Pid Security to be changed after pid created\")"
    And I press "search_entry_submit"
    Then I should not see "No records could be found"
    Given I login as administrator
    And I go to the temporary record pid view page
    When I follow "Edit Security for Select Record"
    Given I choose the "Masqueraders" group for the "Lister" role
    Given I choose the "Masqueraders" group for the "Viewer" role
    And I press "Save"
    And I wait for solr
    And I wait for bgps
    And I wait for "10" seconds
    And I carefully fill search entry with "title:(\"Test Pid Security to be changed after pid created\")"
    And I press "search_entry_submit"
    Then I should not see "No records could be found"
    And I carefully fill search entry with "title:(\"Test Collection Security to be changed after pid created\")"
    And I press "search_entry_submit"
    Then I should not see "No records could be found"
    And I carefully fill search entry with "title:("Test Community Security to be changed after pid created")"
    And I follow "Test Collection Security to be changed after pid created"
    And I follow "Test Pid Security to be changed after pid created"
    And I follow "Logout"
    And I am on the homepage
    And I wait for solr
    And I wait for bgps
    And I carefully fill search entry with "title:(\"Test Collection Security to be changed after pid created\")"
    And I press "search_entry_submit"
    Then I should see "No records could be found"
    And I carefully fill search entry with "title:(\"Test Pid Security to be changed after pid created\")"
    And I press "search_entry_submit"
    Then I should see "No records could be found"

  @purge
  Scenario: Delete old Communities, collections and pids
    Given I am on "/"
    Then I clean up title "Test Community Security to be changed after pid created"
    Then I clean up title "Test Collection Security to be changed after pid created"
    Then I clean up title "Test Pid Security to be changed after pid created"
