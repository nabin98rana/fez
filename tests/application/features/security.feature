# features/security.feature
@javascript @destructive @jet @nodata
Feature: Security
  In order to secure a pid
  As a website user
  I need to be able login as an administrator and go to a web page and edit security and set security so only admins can see it
  And login as as a non-administrator and not be able to access the pid view page

  Scenario: Logging in as Administrator
    Given I login as administrator
    Then I should see "You are logged in as Test Admin"

  Scenario: Create a Community as an administrator and see it as a non-logged in user
    Given I login as administrator
    And I follow "Browse"
    And I follow "Create New Community"
    And I wait for "2" seconds
    And I fill in "Name" with "Security Test Community"
    And I fill in "Keyword 1" with "automated testing"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I press "Publish"
    And I wait for solr
    And I wait for bgps
    And I follow "Logout"
    And I am on the homepage
    And I carefully fill search entry with "title:(\"Security Test Community\")"
    And I press search
    Then I should see "(1 results found)"

  Scenario: Create a community, collection, set the collection to viewable by admins only
    Given I login as administrator
    And I follow "Browse"
    And I follow "Create New Community"
    And I wait for "2" seconds
    And I fill in "Name" with "Security Test Community Open"
    And I fill in "Keyword 1" with "automated testing"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I press "Publish"
    And I wait for solr
    And I wait for bgps
    And I press "Create"
    And I wait for "2" seconds
    And I fill in "Title" with "Security Test Collection Masqueraders"
    And I fill in "Keyword 1" with "automated testing"
    And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
    And I select "Security Test Community" from "Member of Communities"
    And I press "Publish"
    And I wait for solr
    And I wait for bgps
    And I follow "Security Test Community"
    And I follow "Edit Security for Selected Collection"
    And I uncheck "Inherit Security from Parent Hierarchy?"
    And I choose the "Masqueraders" group for the "Lister" role
    And I choose the "Masqueraders" group for the "Viewer" role
    And I press "Save Changes"
    And I follow "Logout"
    And I wait for solr
    And I wait for bgps
    And I am on the homepage
    And I carefully fill search entry with "title:(\"Security Test Collection Masqueraders\")"
    And I press search
    Then I should see "(0 results found)"

  Scenario: Create a new secure lister community,
  create a collection belonging the secure community and the open community and the
  collection should still be searchable / listable to a non-logged in user due to multiple
  inheritance being more open rather than more restrictive
    Given I login as administrator
    And I follow "Browse"
    And I follow "Create New Community"
    And I wait for "2" seconds
    And I fill in "Name" with "Security Test Community Masqueraders"
    And I fill in "Keyword 1" with "automated testing"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I press "Publish"
    And I wait for solr
    And I wait for bgps
    And I am on the homepage
    And I carefully fill search entry with "title:(\"Security Test Community Masqueraders\")"
    And I press search
    And I follow "Edit Security for Selected Community"
    And I choose the "Masqueraders" group for the "Lister" role
    And I choose the "Masqueraders" group for the "Viewer" role
    And I press "Save Changes"
    And I wait for solr
    And I wait for bgps
    And I follow "Browse"
    And I follow "Security Test Community Masqueraders"
    And I press "Create"
    And I wait for "2" seconds
    And I fill in "Title" with "Security Test Collection Multiple Inheritance Open"
    And I fill in "Keyword 1" with "automated testing"
    And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
    And I select "Security Test Community Masqueraders" from "Member of Communities"
    And I additionally select "Security Test Community Open" from "Member of Communities"
    And I press "Publish"
    And I follow "Logout"
    And I wait for solr
    And I wait for bgps
    And I am on the homepage
    And I carefully fill search entry with "title:(\"Security Test Collection Multiple Inheritance Open\")"
    And I press search
    Then I should see "(1 results found)"

  Scenario: When an administrator deletes the open unsecured community then
  the child collection will start being inaccessible to logged in users as it is now
  only in the secure community
    Given I login as administrator
    And I am on the homepage
    And I carefully fill search entry with "title:(\"Security Test Community Open\")"
    And I press search
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I wait for "2" seconds
    And I confirm the popup
    And I turn on waiting checks
    And I wait for solr
    And I wait for bgps
    And I am on the homepage
    And I carefully fill search entry with "title:(\"Security Test Collection Multiple Inheritance Open\")"
    And I press search
    And I wait for "2" seconds
    And I follow "Edit Security for Selected Collection"
    And I press "Save Changes"
    And I follow "Logout"
    And I wait for solr
    And I wait for bgps
    And I am on the homepage
    And I carefully fill search entry with "title:(\"Security Test Collection Multiple Inheritance Open\")"
    And I press search
    Then I should see "(0 results found)"

  @purge
  Scenario: Delete old Communities, collections and pids
    Given I am on "/"
    Then I clean up title "Security Test Community"
    Then I clean up title "Security Test Community Open"
    Then I clean up title "Security Test Community Masqueraders"
    Then I clean up title "Security Test Collection Multiple Inheritance Open"
    Then I clean up title "Security Test Collection"
    Then I clean up title "Security Test Collection Masqueraders"
