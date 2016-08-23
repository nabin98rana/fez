# features/BulkAssignAuthorId.feature
@javascript @destructive @jet @datadependant @seed
Feature: Check bulk assigning Author IDs works correctly

  Scenario: Assign an author ID to an author on a record using the Assign Author ID bulk workflow
   Given I login as administrator
   And I go to the test journal article view page
   And I follow "More options"
   And I follow "Clone Selected Record"
   And I select "Test Data Collection" from "collection_pid"
   And I check "clone_attached_datastreams"
   And I select "Journal Article Version MODS 1.0" from "new_xdis_id"
   And I press "Clone Record"
   And I fill in "Title" with "Bulk Assign Author ID Test Title 1"
   And I fill in "Author 2" with "UQ Author"
   And I press "Publish"
   And I wait for bgps
   And I wait for solr
   And I carefully fill search entry with "title:(\"Bulk Assign Author ID Test Title 1\")"
   And I press search
   And I select the first record in the search results
   And I select "Bulk Assign Author ID" from "Run bulk workflow"
   And I press "Run Workflow"
   And I fill in "Author Text" with "UQ Author"
   And I fill in "Author ID" with "1"
   And I press "Bulk Assign Author ID"
   And I wait for bgps
   And I wait for solr
   And I carefully fill search entry with "title:(\"Bulk Assign Author ID Test Title 1\")"
   When I follow "Click to view Journal Article"
   And I follow "UQ Author"
   Then I should see "ID - UQ Author"
