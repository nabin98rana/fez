# features/CloneRecord.feature
@javascript @destructive @jet
Feature: Check Clone Records works correctly

  Scenario: Go to a known record and see if it clones correctly with everything selected on the clone screen
   Given I login as administrator
   And I go to the test journal article view page
   And I follow "More options"
   And I follow "Clone Selected Record"
   And I select "Test Data Collection" from "collection_pid"
   And I check "clone_attached_datastreams"
   And I select "Journal Article Version MODS 1.0" from "new_xdis_id"
   And I press "Clone Record"
   And I fill in "Title" with "Clone Test Title 1"
   And I select "Article" from "Sub-type"
   And I press "Publish"
   And I wait for bgps
   And I wait for solr
   Then I should see "Clone Test Title 1"
   And I should see "Article"
   And I should see "Test Data Collection"
   And I should see "test.pdf"

  @purge
  Scenario: Delete old Pid
   Given I am on "/"
   Then I clean up title "Clone Test Title 1"
