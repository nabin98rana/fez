# features/security.feature
#@javascript
Feature: Security
  In order to secure a pid
  As a website user
  I need to be able login as an administrator and go to a web page and edit security and set security so only admins can see it
  And login as as a non-administrator and not be able to access the pid view page

#  Scenario: Logging in as Administrator
#    Given I login as administrator
#    Then I should see "You are logged in as Admin Test User"

  @destructive @now
  Scenario: Create a community, collection, set the collection to viewable by admins only
    Given I login as administrator
    And I follow "Browse"
    And I follow "Create New Community"
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
    And I follow "Security Test Community"
#    And put a breakpoint
    And I follow "Edit Security for Selected Collection"
    #10 is the viewer role.. if you choose "Viewer" instead it stupidly selects Archival_FormatViewer so have to use the number value
    And I select "10" from "role"
    And I select "Fez_Group" from "groups_type"
    And I select "Masqueraders" from "internal_group_list"
    And I press "Add"
    And I press "Save Changes"
    And I follow "Logout"
    When I follow "Home"
    Given I am on "/"
    And I fill in "Search Entry" with "title:(\"Security Test Collection\")"
    And I press "search_entry_submit"
#    And put a breakpoint
    Then I should see "(0 results found)"
#    And put a breakpoint

   @destructive
   Scenario: Create a Community as an administrator and see it as a non-logged in user
     Given I login as administrator
     And I follow "Browse"
     And I follow "Create New Community"
     And I fill in "Name" with "Security Test Community"
     And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
     And I fill in "Keyword(s) 1" with "automated testing"
     And I press "Publish"
     And I follow "Logout"
     When I follow "Home"
     And I fill in "Search Entry" with "title:(\"Security Test Community\")"
     And I wait for a bit
     And I press "search_entry_submit"
#     And I put a breakpoint
     Then I should see "(1 results found)"


  @destructive
  Scenario: Delete Security Test Collections
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Security Test Collection\")"
    And I press "search_entry_submit"
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I fill "automated test data cleanup" in popup
    And I confirm the popup
    And I turn on waiting checks
    When I follow "Home"
  # wait for solr to catch up its indexing
    And I wait for a bit
    And I fill in "Search Entry" with "title:(\"Security Test Collection\")"
    And I press "search_entry_submit"
    Then I should see "(0 results found)"

#@now
#Scenario: I should search for community and find none
#  Given I login as administrator
#  When I follow "Home"
#  And I fill in "Search Entry" with "title:(\"Security Test Community\")"
#  And I press "search_entry_submit"
#  And I put a breakpoint
#  Then I should see "(0 results found)"


  @destructive
Scenario: Delete Security Test Communitys
  Given I login as administrator
  And I fill in "Search Entry" with "title:(\"Security Test Community\")"
  And I press "search_entry_submit"
  And I press "Select All"
  And I turn off waiting checks
  And I press "Delete"
  And I confirm the popup
  And I fill "automated test data cleanup" in popup
  And I confirm the popup
  And I turn on waiting checks
  When I follow "Home"
  # wait for solr to catch up its indexing
  And I wait for a bit
  And I fill in "Search Entry" with "title:(\"Security Test Community\")"
  And I press "search_entry_submit"
#  And I put a breakpoint
  Then I should see "(0 results found)"

#    When I fill in "front_search" with "spaghetti monster"
#    And I press "submit-button"
#    Then I should see "(0 results found)"
