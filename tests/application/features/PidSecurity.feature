@javascript @broken
Feature: Pid security

  Scenario: I login as admin and set a pids security to only view for a certain group then all users as list it and only that group can view it
    Given I login as administrator
    And I go to the test collection list page
    And I select "Journal Article" from "xdis_id_top"
    And I press "Create"
    And I fill in "Title" with "Security Test Journal Title2012"
    And I fill in "Journal name" with "Security Test Journal name"
    And I fill in "Author 1" with "Security Test Author name"
    And I select "Article" from "Sub-type"
    And I check "Copyright Agreement"
    #this is problemmatic getting a generic label
    And I select "2010" from "xsd_display_fields[6386][Year]"
    And I press "Publish"
    And I wait for "4" seconds
    And I follow "More options"
    And I follow "Edit Security for Select Record"
    And I uncheck "Inherit"
    #select Viewer is selecting Archival Format Viewer for me :(
    And I select "10" from "role"
    And I select "Fez_Group" from "groups_type"
    And I select "Unit Publication Officers" from "internal_group_list"
    And I press "Save"
    And I follow "Logout"
    Given I am on "/"
    And I fill in "Search Entry" with "title:(\"Security Test Journal Title2012\")"
    And I press "search_entry_submit"
    Then I should not see "No records could be found"
    When I follow "Click to view Journal Article"
    Then I should see "Login to"
    Given I login as UPO
    And I fill in "Search Entry" with "title:(\"Security Test Journal Title2012\")"
    And I press "search_entry_submit"
    When I follow "Click to view Journal Article"
    Then I should see "Security Test Journal Title2012"
    Then I should not see "Workflows"

  Scenario: I login as admin and set a pids security to only create for a certain group as check that group can create
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Security Test Journal Title2012\")"
    And I press "search_entry_submit"
    When I follow "Click to view Journal Article"
    And I follow "More options"
    And I follow "Edit Security for Select Record"
    And I select "Editor" from "role"
    And I select "Fez_Group" from "groups_type"
    And I select "Unit Publication Officers" from "internal_group_list"
    And I press "Save"
    And I follow "Logout"
    Given I login as UPO
    And I fill in "Search Entry" with "title:(\"Security Test Journal Title2012\")"
    And I press "search_entry_submit"
    When I follow "Click to view Journal Article"
    Then I should see "Security Test Journal Title2012"
    Then I should see "Workflows"

  Scenario: I login as admin and set a pids security to list for a certain group and check only that group can list
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Security Test Journal Title2012\")"
    And I press "search_entry_submit"
    When I follow "Click to view Journal Article"
    And I follow "More options"
    And I follow "Edit Security for Select Record"
    And I select "Lister" from "role"
    And I select "Fez_Group" from "groups_type"
    And I select "Unit Publication Officers" from "internal_group_list"
    And I press "Save"
    And I follow "Logout"
    #test non logged in cannot see it
    And I fill in "Search Entry" with "title:(\"Security Test Journal Title2012\")"
    And I press "search_entry_submit"
    Then I should see "No records could be found"
    #test upos can see it
    Given I login as UPO
    And I fill in "Search Entry" with "title:(\"Security Test Journal Title2012\")"
    And I press "search_entry_submit"
    When I follow "Click to view Journal Article"
    Then I should see "Security Test Journal Title2012"
    Then I should see "Workflows"

  Scenario: I login as admin and remove all permissions and check non login users and UPO can view but not touch
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Security Test Journal Title2012\")"
    And I press "search_entry_submit"
    When I follow "Click to view Journal Article"
    And I follow "More options"
    And I follow "Edit Security for Select Record"
    And I check "items[]"
    And I press "Save"
    And I follow "More options"
    And I follow "Edit Security for Select Record"
    And I check "items[]"
    And I press "Save"
    And I follow "More options"
    And I follow "Edit Security for Select Record"
    And I check "items[]"
    And I press "Save"
    And I follow "Logout"
    And I fill in "Search Entry" with "title:(\"Security Test Journal Title2012\")"
    And I press "search_entry_submit"
    When I follow "Click to view Journal Article"
    Then I should see "Security Test Journal Title2012"
    Then I should not see "Workflows"
    Given I login as UPO
    And I fill in "Search Entry" with "title:(\"Security Test Journal Title2012\")"
    And I press "search_entry_submit"
    When I follow "Click to view Journal Article"
    Then I should see "Security Test Journal Title2012"
    Then I should not see "Workflows"

   @destructive @now
  Scenario: Delete old pids
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Security Test Journal Title2012\")"
    And I press "search_entry_submit"
    And I wait for "2" seconds
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I fill "automated test data cleanup" in popup
    And I confirm the popup

