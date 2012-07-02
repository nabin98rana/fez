Feature: Test UPO abilities

  @wip
  Scenario:
    Given I login as UPO
    And I am on "/my_upo_tools.php"
    And I select "Mathematics" from "org_unit_id"
    And I wait for "2" seconds
    And I click "majeccle"
    And I should see "Currently acting as: majeccle"
    And I follow "My Research"
    Then I should see button "Fix"
    And I follow "Possibly My Research"
    Then I should see button "Mine"
    And I should see button "Not mine"
    Then I follow "Add Missing Publication"
    Then I should see "Currently acting as: majeccle"

