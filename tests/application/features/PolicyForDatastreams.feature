# features/PolicyForDatastreams.feature
Feature: Check datastream policy works correctly

  @destructive @now
  Scenario: Copy a known record with attachment without permisisons other than inherit to a community. Turn on a data stream policy on the community. Add another Pid. Then check both pids have the new policy.
    Given I login as administrator
    #Create test communities and collections
    And I follow "Browse"
    And I follow "Create New Community"
    And I fill in "Name" with "Test Community Datastream policy"
    And I select "Fedora Collection Display Version Dublin Core 1.0" from "XSD Display Document Types"
    And I fill in "Keyword 1" with "automated testing"
    And I press "Publish"
    And I press "Create"
    And I fill in "Title" with "Test Collection Datastream policy"
    And I select "Journal Article Version MODS 1.0" from "XSD Display Document Types"
    And I select "Test Community Datastream policy" from "Member of Communities"
    And I fill in "Keyword 1" with "automated testing"
    And I press "Publish"
    #clone record 1 to the collection
    And I go to the test journal article view page
    And I follow "More options"
    And I follow "Clone Selected Record"
    And I select "Test Collection Datastream policy" from "collection_pid"
    And I check "clone_attached_datastreams"
    And I select "Journal Article Version MODS 1.0" from "new_xdis_id"
    And I press "Clone Record"
    And I fill in "Title" with "Test Title Datastream policy 1"
    And I press "Publish"
    #Set datastream permissions on collection
    And I fill in "Search Entry" with "title:(\"Test Collection Datastream policy\")"
    And I press "search_entry_submit"
    And I follow "Edit Security for Selected Collection"
    And I select "Thesis Office View, List, Approve only, Printery View" from "Datastream FezACML Policy for datastreams"
    And I press "Save Changes"
    #clone record 2 to the collection
    And I go to the test journal article view page
    And I follow "More options"
    And I follow "Clone Selected Record"
    And I select "Test Collection Datastream policy" from "collection_pid"
    And I check "clone_attached_datastreams"
    And I select "Journal Article Version MODS 1.0" from "new_xdis_id"
    And I press "Clone Record"
    And I fill in "Title" with "Test Title Datastream policy 2"
    And I press "Publish"
    And I should see "thornhill_gillie.pdf"
    And I follow "Logout"
    Given I login as thesis officer
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy 1\")"
    And I press "search_entry_submit"
    And I follow "Test Title Datastream policy 1"
    And I should see "thornhill_gillie.pdf"
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy 2\")"
    And I press "search_entry_submit"
    And I follow "Test Title Datastream policy 2"
    And I should see "thornhill_gillie.pdf"
    And I follow "Logout"
    Given I login as user no groups
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy 1\")"
    And I press "search_entry_submit"
    And I follow "Test Title Datastream policy 1"
    And I should not see "thornhill_gillie.pdf"
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy 2\")"
    And I press "search_entry_submit"
    And I follow "Test Title Datastream policy 2"
    And I should not see "thornhill_gillie.pdf"

  @destructive @now2
  Scenario: I change the policy for datastreams in the Collection. This won't change above datastreams since they have recieved policies to not inherit.
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Test Collection Datastream policy\")"
    And I press "search_entry_submit"
    And I follow "Edit Security for Selected Collection"
    And I select "Fully Embargoed (system admins only)" from "Datastream FezACML Policy for datastreams"
    And I press "Save Changes"
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy 1\")"
    And I press "search_entry_submit"
    And I follow "Test Title Datastream policy 1"
    And I should see "thornhill_gillie.pdf"
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy 2\")"
    And I press "search_entry_submit"
    And I follow "Test Title Datastream policy 2"
    And I should see "thornhill_gillie.pdf"
    And I follow "Logout"
    Given I login as thesis officer
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy 1\")"
    And I press "search_entry_submit"
    And I follow "Test Title Datastream policy 1"
    And I should see "thornhill_gillie.pdf"
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy 2\")"
    And I press "search_entry_submit"
    And I follow "Test Title Datastream policy 2"
    And I should see "thornhill_gillie.pdf"
    And I follow "Logout"

  @destructive @now3
  Scenario: I change the policy for datastreams in the Collection back to nothing. Then add a pid and change it's datastream value.
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Test Collection Datastream policy\")"
    And I press "search_entry_submit"
    And I follow "Edit Security for Selected Collection"
    And I select "Please choose an option" from "Datastream FezACML Policy for datastreams"
    And I press "Save Changes"
    #clone record 1 to the collection
    And I go to the test journal article view page
    And I follow "More options"
    And I follow "Clone Selected Record"
    And I select "Test Collection Datastream policy" from "collection_pid"
    And I check "clone_attached_datastreams"
    And I select "Journal Article Version MODS 1.0" from "new_xdis_id"
    And I press "Clone Record"
    And I fill in "Title" with "Test Title Datastream policy 3"
    And I press "Publish"
    #Set datastream permissions
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy 3\")"
    And I press "search_entry_submit"
    And I follow "Test Title Datastream policy 3"
    And I follow "Edit Security for Select Record"
    And I select "Thesis Office View, List, Approve only, Printery View" from "Datastream FezACML Policy for datastreams"
    And I press "Save Changes"
    And I follow "Logout"
    Given I login as thesis officer
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy 3\")"
    And I press "search_entry_submit"
    And I follow "Test Title Datastream policy 3"
    And I should see "thornhill_gillie.pdf"
    And I follow "Logout"
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy 3\")"
    And I press "search_entry_submit"
    And I follow "Test Title Datastream policy 3"
    And I should not see "thornhill_gillie.pdf"

  @destructive @purge
  Scenario: Delete old Communities
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Test Community Datastream policy\")"
    And I press "search_entry_submit"
    And I wait for "3" seconds
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I fill "automated test data cleanup" in popup
    And I confirm the popup
    And I turn on waiting checks

  @destructive @purge
  Scenario: Delete old Collections
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Test Collection Datastream policy\")"
    And I press "search_entry_submit"
    And I wait for "3" seconds
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I fill "automated test data cleanup" in popup
    And I confirm the popup
    And I turn on waiting checks

  @destructive @purge
  Scenario: Delete old pids
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Test Title Datastream policy\")"
    And I press "search_entry_submit"
    And I wait for "3" seconds
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I fill "automated test data cleanup" in popup
    And I confirm the popup
    And I turn on waiting checks
