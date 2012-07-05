@javascript
Feature: Test Language of Title fields https://www.pivotaltracker.com/story/show/26403903
  Language of title fields should remain hidden on the form, until foreign language is selected on Language field.
  Language of title fields should not have default value.


  @destructive @wip
  Scenario: Enter a record and see if Language of Title fields is as expected
    Given I login as administrator
    And I go to the test collection list page
    And I select "Book" from "xdis_id_top"
    And I press "Create"
    Then I should not see "Native Script Title"
    Then I should not see "Roman Script Title"
    Then I should not see "Translated title"
    Then I should not see "Language of Title"
#    And put a breakpoint
    And I select "aar" from "Language helper"
    And I press "Language copy left"
    Then I should see "Native Script Title"
    Then I should see "Roman Script Title"
    Then I should see "Translated title"
    Then I should see "Language of Title"
    Then I press "Abandon Workflow"