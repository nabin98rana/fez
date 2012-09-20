# features/CloneRecord.feature
@javascript
Feature: Check Clone Records works correctly

  @destructive @now
  Scenario: Go to a known record and see if it clones correctly with everything selected on the clone screen
   Given I login as administrator
    And I go to the test journal article view page
    And I follow "More options"
    And I follow "Clone Selected Record"
    And I select "ePrints import test ELEVEN" from "collection_pid"
    And I check "is_succession"
    And I check "clone_attached_datastreams"
    And I select "Journal Article Version MODS 1.0" from "new_xdis_id"
    And I press "Clone Record"
    And I fill in "Title" with "Clone Test Title 1"
    And I press "Publish"
    Then I should see "Clone Test Title 1"
    And I should see "Available Versions of this Record"
    And I should see "Young Adults' Suicide Related Knowledge and Attitudes: Implications for suicide awareness education Journal Article (deposited 30-06-2004)"
    And I should see "Thornhill, Jaime"
    And I should see "thornhill_gillie.pdf"
    And I should see "2000-01-01"
    And I should see "Australian Journal of Guidance and Counselling"
    And I should see "Gillies, Robyn"
    And I should see "51"
    And I should see "68"
    And I should see "Article"
    #And I should see "330105 Educational Counselling"
    And I should see "ePrints import test ELEVEN"
    And I go to the test journal article view page
    #And I should see "Available Versions of this Record"
    And I should see "Clone Test Title 1 Journal Article (deposited"

  @destructive @now
  Scenario: Go to a known record and see if it clones correctly with nothing selected except display on the clone screen
    Given I login as administrator
    And I go to the test journal article view page
    And I follow "More options"
    And I follow "Clone Selected Record"
    And I select "Journal Article Version MODS 1.0" from "new_xdis_id"
    And I press "Clone Record"
    And I fill in "Title" with "Clone Test Title 2"
    And I press "Publish"
    Then I should see "Clone Test Title 2"
    And I should see "Thornhill, Jaime"
    And I should not see "thornhill_gillie.pdf"
    And I should see "2000-01-01"
    And I should see "Australian Journal of Guidance and Counselling"
    And I should see "Gillies, Robyn"
    And I should see "51"
    And I should see "68"
    And I should see "Article"
    And I should see "330105 Educational Counselling"
    And I should see "AJICT"
    And I should not see "Available Versions of this Record"
    And I should not see "Young Adults' Suicide Related Knowledge and Attitudes: Implications for suicide awareness education Journal Article (deposited 30-06-2004)"
    And I go to the test journal article view page
    And I should not see "Clone Test Title 2 Journal Article (deposited"


  @destructive @purge
  Scenario: Delete old cloned pids
    Given I login as administrator
    And I fill in "Search Entry" with "title:(\"Clone Test Title\")"
    And I press "search_entry_submit"
    And I wait for "2" seconds
    And I press "Select All"
    And I turn off waiting checks
    And I press "Delete"
    And I confirm the popup
    And I fill "automated test data cleanup" in popup
    And I confirm the popup
    And I go to the test journal article view page
    And I should not see "Clone Test Title Journal Article (deposited"




