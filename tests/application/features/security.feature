# features/security.feature
@javascript
Feature: Security
  In order to secure a pid
  As a website user
  I need to be able login as an administrator and go to a web page and edit security and set security so only admins can see it
  And login as as a non-administrator and not be able to access the pid view page

#  Scenario: Logging in as Administrator
#    Given I login as administrator
#    Then I should see "You are logged in as Admin Test User"

  @destructive @core
  Scenario: Create a Community as an administrator and see it as a non-logged in user
    Given I login as administrator
    And I follow "Browse"
    And I follow "Create New Community"
    And I fill in "Name" with "Security Test Community"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I fill in "Keyword 1" with "automated testing"
    And I press "Publish"
    And I follow "Logout"
    When I am on "/"
    And I fill in "Search Entry" with "title:(\"Security Test Community\")"
    And I press "search_entry_submit"
    Then I should see "(1 results found)"

  @destructive
  Scenario: Create a community, collection, set the collection to viewable by admins only
    Given I login as administrator
    And I follow "Browse"
    And I follow "Create New Community"
    And I fill in "Name" with "Security Test Community Open"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I fill in "Keyword 1" with "automated testing"
    And I press "Publish"
    And I press "Create"
    And I fill in "Title" with "Security Test Collection Masqueraders"
    And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
    And I select "Security Test Community" from "Member of Communities"
    And I fill in "Keyword 1" with "automated testing"
    And I press "Publish"
    And I follow "Security Test Community"
    And I follow "Edit Security for Selected Collection"
    And I wait for a bit
    And I uncheck "Inherit Security from Parent Hierarchy?"
    And I choose the "Masqueraders" group for the "Lister" role
    And I press "Save Changes"
    And I follow "Logout"
    Given I am on "/"
    And I fill in "Search Entry" with "title:(\"Security Test Collection\")"
    And I press "search_entry_submit"
    Then I should see "(0 results found)"

  @destructive @core
  Scenario: Create a new secure lister community,
  create a collection belonging the secure community and the open community and the
  collection should still be searchable / listable to a non-logged in user due to multiple
  inheritance being more open rather than more restrictive
  Given I login as administrator
  And I follow "Browse"
  And I follow "Create New Community"
  And I fill in "Name" with "Security Test Community UPOs"
  And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
  And I fill in "Keyword 1" with "automated testing"
  And I press "Publish"
  And I fill in "Search Entry" with "title:(\"Security Test Community UPOs\")"
  And I press "search_entry_submit"
  And I follow "Edit Security for Selected Community"
  And I wait for a bit
  And I choose the "Unit Publication Officers" group for the "Lister" role
  And I press "Save Changes"
  And I wait for a bit
  And I follow "Browse"
  And I follow "Security Test Community UPOs"
  And I press "Create"
  And I fill in "Title" with "Security Test Collection Multiple Inheritance Open"
  And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
  And I select "Security Test Community UPOs" from "Member of Communities"
  And I additionally select "Security Test Community Open" from "Member of Communities"
  And I fill in "Keyword 1" with "automated testing"
  And I press "Publish"
  And I follow "Logout"
  And I fill in "Search Entry" with "title:(\"Security Test Collection Multiple Inheritance Open\")"
  And I press "search_entry_submit"
  Then I should see "(1 results found)"


#  @destructive
#  Scenario: Using an open community and creating a new open collection in it, and setting the collection
#  to have a  'Datastream FezACML Policy for datastreams' set to only UPO groups can view attached files
#  and the record view screen is viewable but the PDFs are only accessible to UPOs
#  Given I login as administrator
#  And I fill in "Search Entry" with "title:(\"Security Test Community Open\")"
#  And I press "search_entry_submit"
#  And I follow "Security Test Community Open"
#  And I press "Create"
#  And I fill in "Title" with "Security Test Collection Open Records But Secure Files"
#  And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
#  And I select "Security Test Community Open" from "Member of Communities"
#  And I fill in "Keyword 1" with "automated testing"
#  And I press "Publish"
#  And I fill in "Search Entry" with "title:(\"Security Test Collection Open Records But Secure Files\")"
#  And I press "search_entry_submit"
#  And I follow "Edit Security for Selected Collection"
#  And I choose






@destructive @core
  Scenario: When an administrator deletes the open unsecured community then
  the child collection will start being inaccessible to logged in users as it is now
  only in the secure community
  Given I login as administrator
  And I fill in "Search Entry" with "title:(\"Security Test Community Open\")"
  And I press "search_entry_submit"
  And I follow "More options"
  And I follow "Delete Selected Record"
  And I follow "Logout"
  And I fill in "Search Entry" with "title:(\"Security Test Collection Multiple Inheritance Open\")"
  And I press "search_entry_submit"
  Then I should see "(0 results found)"


@destructive @core @purge
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
    When I am on "/"
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


  @destructive @core @purge
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
  When I am on "/"
  And I wait for a bit
  And I fill in "Search Entry" with "title:(\"Security Test Community\")"
  And I press "search_entry_submit"
  Then I should see "(0 results found)"

#    When I fill in "front_search" with "spaghetti monster"
#    And I press "submit-button"
#    Then I should see "(0 results found)"
