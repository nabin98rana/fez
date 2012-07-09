# features/TestListView.feature
Feature: Test the different views work when in list view

  Scenario: I login as admin see that all the views in list work
    Given I login as administrator
    And I go to "/adv_search.php"
    And I fill in "search_keys[0]" with "Water"
    And I press "Search"
    Then I should see "Water"
    Then I select "RSS Feed" from "tpl"
    Then I should see text "<rss xmlns:media=" in code
#    And I put a breakpoint
    And I should see text "</rss>" in code
    When I move backward one page
    And I select "XML Feed" from "tpl"
    Then I should see text "<xmlfeed>" in code
    And I should see text "</xmlfeed>" in code
    When I move backward one page
    And I select "Citations Only" from "tpl"
    And I should not see "Author Name"
    And I should not see text "Star/unstar this record" in code
    And I should not see text "More options" in code
    When I move backward one page
    And I select "Image Gallery View" from "tpl"
    And I should not see "Author Name"
    And I should not see text "Star/unstar this record" in code
    And I should not see text "More options" in code
    When I move backward one page
    And I select "HTML Code" from "tpl"
    Then I should see "Copy this html to your web page for a dynamically created list of this search"
    And I should see "<script"
    And I should see "</script>"
    When I move backward one page


