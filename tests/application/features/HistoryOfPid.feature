# features/CheckHistory.feature
@javascript
Feature: Test that the history for pids is working

  Scenario: I login as admin, make a change and see it is in the history
    Given I login as administrator
    And I go to the test collection list page
    And I select "Journal Article" from "xdis_id_top"
    And I press "Create"
    And I fill in "Title" with "Test History Journal Title"
    And I fill in "Journal name" with "Test History Journal name"
    And I fill in "Author 1" with "Test History Author name"
    And I select "Article" from "Sub-type"
    And I check "Copyright Agreement"
    And I select "2010" from "Publication date"
    And I press "Submit for Approval"
    And I follow "/view/"
    And I follow "Detailed History"
    And I turn off waiting checks
    And I switch to window "_impact"
    And I should see "Finished, Create Generic Record In Selected Collection by Test Admin"
    Then I should not see "Published by"
    And I press "Close"
    And I switch to window ""
    And I turn on waiting checks
    And I follow "More options"
    And I follow "Update Selected Record - Generic"
    And I fill in "edit_reason" with "Testing edit reason in history"
    And I press "Publish"
    And I follow "Detailed History"
    And I turn off waiting checks
    And I switch to window "_impact"
    And I should see "Finished, Create Generic Record In Selected Collection by Test Admin"
    And I should see "Published by Test Admin - Testing edit reason"
    And I should see "Testing edit reason in history"


  @destructive @purge
  Scenario: Delete old pids
    Given I login as administrator
    And I fill in "search_entry" with "title:(\"Test History Journal Title\")"
    And I press "search_entry_submit"
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I fill "automated test data cleanup" in popup
    And I confirm the popup