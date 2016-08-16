#features/PolicyForDatastreams.feature
@javascript @destructive @jet @datadependant
Feature: Check datastream policy works correctly

  Scenario: Copy a known record with attachment without permissions other than inherit to a community. Turn on a data stream policy on the community. Add another Pid. Then check both pids have the new policy.
    Given I login as administrator
    And I follow "Browse"
    And I follow "Create New Community"
    And I wait for "2" seconds
    And I fill in "Keyword 1" with "automated testing"
    And I fill in "Name" with "Test Datastream Policy Community"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I press "Publish"
    And I wait for solr
    And I wait for bgps
    And I press "Create"
    And I wait for "2" seconds
    And I fill in "Title" with "Test Datastream Policy Collection"
    And I fill in "Keyword 1" with "automated testing"
    And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
    And I select "Test Datastream Policy Community" from "Member of Communities"
    And I press "Publish"
    And I wait for solr
    And I wait for bgps
    And I go to the test journal article view page
    And I follow "More options"
    And I wait for "2" seconds
    And I follow "Clone Selected Record"
    And I select "Test Datastream Policy Collection" from "collection_pid"
    And I check "clone_attached_datastreams"
    And I select "Journal Article Version MODS 1.0" from "new_xdis_id"
    And I press "Clone Record"
    And I fill in "Title" with "Test Title Datastream policy 1"
    And I press "Publish"
    And I wait for bgps
    And I wait for solr
    And I carefully fill search entry with "title:(\"Test Datastream Policy Collection\")"
    And I press search
    And I follow "Edit Security for Selected Collection"
    And I select "Thesis officers only" from "Datastream FezACML Policy for datastreams"
    And I turn off waiting checks
    And I press "Save Changes"
    And I turn on waiting checks
    And I am on the homepage
    And I wait for "3" seconds
    And I go to the test journal article view page
    And I follow "More options"
    And I follow "Clone Selected Record"
    And I select "Test Datastream Policy Collection" from "collection_pid"
    And I check "clone_attached_datastreams"
    And I select "Journal Article Version MODS 1.0" from "new_xdis_id"
    And I press "Clone Record"
    And I fill in "Title" with "Test Title Datastream policy 2"
    And I press "Publish"
    And I follow "Logout"
    And I wait for bgps
    And I wait for solr
    And I wait for "3" seconds
    Given I login as thesis officer
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 1\")"
    And I press search
    And I follow "Test Title Datastream policy 1"
    And I should see a datastream link for "test.pdf"
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 2\")"
    And I press search
    And I follow "Test Title Datastream policy 2"
    And I should see a datastream link for "test.pdf"
    And I follow "Logout"
    Given I login as user no groups
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 1\")"
    And I press search
    And I follow "Test Title Datastream policy 1"
    And I should see "test.pdf"
    And I should not see any datastream view links
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 2\")"
    And I press search
    And I follow "Test Title Datastream policy 2"
    And I should see "test.pdf"
    And I should not see any datastream view links

  Scenario: I change the policy for datastreams in the Collection. This won't change above datastreams since they have recieved policies to not inherit.
    Given I login as administrator
    And I carefully fill search entry with "title:(\"Test Datastream Policy Collection\")"
    And I press search
    And I follow "Edit Security for Selected Collection"
    And I select "UPOs only" from "Datastream FezACML Policy for datastreams"
    And I turn off waiting checks
    And I press "Save Changes"
    And I turn on waiting checks
    And I wait for bgps
    And I wait for solr
    And I am on the homepage
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 1\")"
    And I press search
    And I follow "Test Title Datastream policy 1"
    And I should see a datastream link for "test.pdf"
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 2\")"
    And I press search
    And I follow "Test Title Datastream policy 2"
    And I should see a datastream link for "test.pdf"
    And I follow "Logout"
    Given I login as thesis officer
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 1\")"
    And I press search
    And I follow "Test Title Datastream policy 1"
    And I should see "test.pdf"
    And I should not see any datastream view links
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 2\")"
    And I press search
    And I follow "Test Title Datastream policy 2"
    And I should see "test.pdf"
    And I should not see any datastream view links
    And I follow "Logout"

  Scenario: I change the policy for datastreams in the Collection back to nothing. Then add a pid and change it's datastream policy. Then check Datastream follows the pid policy
    Given I login as administrator
    And I carefully fill search entry with "title:(\"Test Datastream Policy Collection\")"
    And I press search
    And I follow "Edit Security for Selected Collection"
    And I select "Please choose an option" from "Datastream FezACML Policy for datastreams"
    And I turn off waiting checks
    And I press "Save Changes"
    And I turn on waiting checks
    And I wait for bgps
    And I wait for solr
    And I am on the homepage
    And I wait for "3" seconds
    And I go to the test journal article view page
    And I follow "More options"
    And I follow "Clone Selected Record"
    And I select "Test Datastream Policy Collection" from "collection_pid"
    And I check "clone_attached_datastreams"
    And I select "Journal Article Version MODS 1.0" from "new_xdis_id"
    And I press "Clone Record"
    And I fill in "Title" with "Test Title Datastream policy 3"
    And I press "Publish"
    And I wait for bgps
    And I wait for solr
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 3\")"
    And I press search
    And I follow "Test Title Datastream policy 3"
    And I follow "Edit Security for Select Record"
    And I select "UPOs only" from "Datastream FezACML Policy for datastreams"
    And I turn off waiting checks
    And I press "Save Changes"
    And I turn on waiting checks
    And I switch to window ""
    And I follow "Logout"
    And I am on the homepage
    And I wait for solr
    And I wait for bgps
    And I wait for "3" seconds
    Given I login as thesis officer
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 3\")"
    And I press search
    And I follow "Test Title Datastream policy 3"
    And I should see "test.pdf"
    And I should not see any datastream view links
    And I follow "Logout"
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 3\")"
    And I press search
    And I follow "Test Title Datastream policy 3"
    And I should see "test.pdf"
    And I should not see any datastream view links

  Scenario: The policy for datastreams in the Collection is nothing. Then add a pid. Then change datastream security (keeping inheritance) Then change Pid datastream policy. It should blow away any permissions.
    Given I login as administrator
    And I go to the test journal article view page
    And I follow "More options"
    And I follow "Clone Selected Record"
    And I select "Test Datastream Policy Collection" from "collection_pid"
    And I check "clone_attached_datastreams"
    And I select "Journal Article Version MODS 1.0" from "new_xdis_id"
    And I press "Clone Record"
    And I fill in "Title" with "Test Title Datastream policy 4"
    And I press "Publish"
    And I wait for bgps
    And I wait for solr
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 4\")"
    And I press search
    And I follow "Test Title Datastream policy 4"
    And I follow "More options"
    And I follow "Update Selected Record - Generic"
    And I follow "Edit Security for Selected Datastream"
    Given I choose the "UPOs" group for the "Lister" role
    And I turn off waiting checks
    And I wait for "3" seconds
    And I press "Save Changes"
    And I wait for bgps
    And I wait for solr
    And I turn on waiting checks
    And I am on the homepage
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 4\")"
    And I press search
    And I follow "Test Title Datastream policy 4"
    And I follow "Edit Security for Select Record"
    And I select "Thesis officers only" from "Datastream FezACML Policy for datastreams"
    And I turn off waiting checks
    And I press "Save Changes"
    And I wait for bgps
    And I wait for solr
    And I turn on waiting checks
    And I follow "Logout"
    And I am on the homepage
    And I wait for "3" seconds
    Given I login as UPO
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 4\")"
    And I press search
    And I follow "Test Title Datastream policy 4"
    And I should see "test.pdf"
    And I should not see any datastream view links
    And I follow "Logout"
    Given I login as thesis officer
    And I carefully fill search entry with "title:(\"Test Title Datastream policy 4\")"
    And I press search
    And I follow "Test Title Datastream policy 4"
    And I should see a datastream link for "test.pdf"

  @purge
  Scenario: Delete old Communities, Collections and Pids
    Given I am on "/"
    Then I clean up title "Test Datastream Policy Community"
    Then I clean up title "Test Datastream Policy Collection"
    #Then I clean up title "Test Title Datastream policy 1"
    #Then I clean up title "Test Title Datastream policy 2"
    #Then I clean up title "Test Title Datastream policy 3"
    #Then I clean up title "Test Title Datastream policy 4"