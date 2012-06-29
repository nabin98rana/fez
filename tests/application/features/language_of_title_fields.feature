Feature: Test Language of Title fields https://www.pivotaltracker.com/story/show/26403903
  Language of title fields should remain hidden on the form, until foreign language is selected on Language field.
  Language of title fields should not have default value.


  @destructive @wip
  Scenario: Enter a record and see if Language of Title fields is as expected
    Given I login as administrator
    And I follow "Browse"
    And I follow "BH test"
    And I follow "Lachlan's Index Test Collection"
    And I select "Book" from "xdis_id_top"
    And I press "Create"
    Then I should not see "Native Script Title"
    Then I should not see "Roman Script Title"
    Then I should not see "Translated title"
    Then I should not see "Language of Title"
    #And put a breakpoint
    And I select "aar" from "xsd_display_fields_helper_12380_0"
    And I press "copy_left_12380"
    Then I should see "Native Script Title"
    Then I should see "Roman Script Title"
    Then I should see "Translated title"
    Then I should see "Language of Title"
    Then I press "Abandon Workflow"