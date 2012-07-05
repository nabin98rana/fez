Feature: Pid security

Scenario: I login as admin and set a pids security to only view for a certain group then all users as list it and only that group can view it
  Given I login as administrator
  And I follow "Browse"
  And I follow "BH test"
  And I follow "Lachlan's Index Test Collection"
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
  And I wait for "5" seconds
  And I follow "More options"
  And I follow "Edit Security for Select Record"
  And I uncheck "inherit"
  #select Viewer is selecting Archival Format Viewer for me :(
  And I select "10" from "role"
  And I select "Fez_Group" from "groups_type"
  And I wait for "2" seconds
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



