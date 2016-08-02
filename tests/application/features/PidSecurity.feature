@javascript @destructive @jet @datadependant
Feature: Pid security

  Scenario: I login as admin and set a pids security to only view for a certain group then all users as list it and only that group can view it
    Given I login as administrator
    And I go to the test collection list page
    And I select "Journal Article" from "xdis_id_top"
    And I press "Create"
    And I wait for "2" seconds
    And I fill in "Title" with "Security Test Journal Title2012"
    And I fill in "Journal name" with "Security Test Journal name"
    And I fill in "Author 1" with "Security Test Author name"
    And I select "Article" from "Sub-type"
    And I check "Copyright Agreement"
    And I select "2010" from "Publication date"
    And I select "10" from "Publication date month"
    And I select "20" from "Publication date day"
    And I press "Publish"
    And I wait for solr
    And I wait for bgps
    And I follow "More options"
    And I follow "Edit Security for Select Record"
    And I uncheck "Inherit"
    Given I choose the "Masqueraders" group for the "Viewer" role
    And I press "Save"
    And I switch to window ""
    And I wait for solr
    And I wait for bgps
    And I follow "Logout"
    And I am on the homepage
    And I see "search_entry" id or wait for "5" seconds
    And I carefully fill search entry with "title:(\"Security Test Journal Title2012\")"
    And I press search
    When I follow "Click to view Journal Article"
    Then I should see "Login to"
    Given I login as administrator
    And I am on the homepage
    And I see "search_entry" id or wait for "5" seconds
    And I carefully fill search entry with "title:(\"Security Test Journal Title2012\")"
    And I press search
    When I follow "Click to view Journal Article"
    Then I should see "Security Test Journal Title2012"

  Scenario: I login as admin and set a pids security to only create for a certain group as check that group can create
    Given I login as administrator
    And I am on the homepage
    And I carefully fill search entry with "title:(\"Security Test Journal Title2012\")"
    And I press search
    When I follow "Click to view Journal Article"
    And I follow "More options"
    And I follow "Edit Security for Select Record"
    Given I choose the "Masqueraders" group for the "Editor" role
    And I press "Save"
    And I switch to window ""
    And I follow "Logout"
    And I am on the homepage
    And I wait for solr
    And I wait for bgps
    Given I login as administrator
    And I see "search_entry" id or wait for "5" seconds
    And I carefully fill search entry with "title:(\"Security Test Journal Title2012\")"
    And I press search
    When I follow "Click to view Journal Article"
    Then I should see "Security Test Journal Title2012"
    Then I should see "Workflows"

  Scenario: I login as admin and set a pids security to list for a certain group and check only that group can list
    Given I login as administrator
    And I am on the homepage
    And I see "search_entry" id or wait for "5" seconds
    And I carefully fill search entry with "title:(\"Security Test Journal Title2012\")"
    And I press search
    When I follow "Click to view Journal Article"
    And I follow "More options"
    And I follow "Edit Security for Select Record"
    Given I choose the "Masqueraders" group for the "Lister" role
    And I press "Save"
    And I switch to window ""
    And I follow "Logout"
    And I am on the homepage
    And I wait for solr
    And I wait for bgps
    #test non logged in cannot see it
    And I see "search_entry" id or wait for "5" seconds
    And I carefully fill search entry with "title:(\"Security Test Journal Title2012\")"
    And I press search
    Then I should see "No records could be found"
    #test admins can see it
    Given I login as administrator
    And I am on the homepage
    And I see "search_entry" id or wait for "5" seconds
    And I carefully fill search entry with "title:(\"Security Test Journal Title2012\")"
    And I press search
    When I follow "Click to view Journal Article"
    Then I should see "Security Test Journal Title2012"
    #Then I should see "Workflows"

  @purge
  Scenario: Delete old pids
    Given I am on "/"
    Then I clean up title "Security Test Journal Title2012"
