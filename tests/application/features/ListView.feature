@javascript
Feature: Check list view displays the correct information entered into a pid, collection or community

@now @broken
Scenario: I login as admin and create communities, collections and pids and see all the information displays in lists correctly
  Given I login as administrator
  And I follow "Browse"
  And I follow "Create New Community"
  And I fill in "Name" with "Test Community for list view"
  And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
  And I fill in "Abstract/Summary" with "abstract automated testing"
  And I fill in "Keyword 1" with "keyword automated testing"
  And I press "Publish"
  And I press "Create"
  And I fill in "Title" with "Test Collection for list view"
  And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
  And I fill in "Abstract/Summary" with "abstract automated testing"
  And I fill in "Keyword 1" with "automated testing"
  And I press "Publish"
  And I press "Create"
  And I fill in "Title" with "Test Pid for list view"
  And I fill in "Journal name" with "List test journal"
  And I fill in "Author 1" with "List Test Author name 1"
  And I fill in "Volume number" with "123456"
  And I fill in "Start page" with "123"
  And I fill in "End page" with "987"
  And I fill in "Issue number" with "List Test Issue number"
  And I select "Article" from "Sub-type"
  And I check "Copyright Agreement"
  #this is problemmatic getting a generic label
  And I select "2010" from "xsd_display_fields[6386][Year]"
  And I press "Publish"
  And I follow "Logout"
  And I fill in "Search Entry" with "title:(\"Test Community for list view\")"
  And I press "search_entry_submit"
  Then I should see "Test Community for list view"
  Then I should see "(1)"
  Then I should see "abstract automated testing"
  And I fill in "Search Entry" with "title:(\"Test Collection for list view\")"
  And I press "search_entry_submit"
  Then I should see "Test Collection for list view"
  Then I should see "(1)"
  #Then I should see "abstract automated testing"
  And I fill in "Search Entry" with "title:(\"Test Pid for list view\")"
  And I press "search_entry_submit"
  Then I should see "Test Pid for list view"
  Then I should see "123456"
  Then I should see "List test journal"
  Then I should see "List Test Author name 1"
  Then I should see "List Test Issue number"
  Then I should see "123-987"
  Then I should see "(2010)"

@destructive @purge @broken
  Scenario: Delete old Communities
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Test Community for list view\")"
    And I press "search_entry_submit"
    And I wait for "3" seconds
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I fill "automated test data cleanup" in popup
    And I confirm the popup
    And I turn on waiting checks
    And I follow "Logout"

  @destructive @purge @broken
  Scenario: Delete old Collections
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Test Collection for list view\")"
    And I press "search_entry_submit"
    And I wait for "3" seconds
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I fill "automated test data cleanup" in popup
    And I confirm the popup
    And I turn on waiting checks
    And I follow "Logout"

  @destructive @purge @broken
  Scenario: Delete old pids
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Test Pid for list view\")"
    And I press "search_entry_submit"
    And I wait for "3" seconds
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I fill "automated test data cleanup" in popup
    And I confirm the popup
    And I turn on waiting checks
