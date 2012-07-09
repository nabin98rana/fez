@javascript
Feature: Test UPO abilities

  Scenario:
    Given I login as UPO
    And I am on "/my_upo_tools.php"
    And I select the test org unit
    And I wait for "2" seconds
    And I select the test org unit username
    Then I should see the test org unit username message
    And I follow "My Research"
    Then I should see button "Fix"
    And I follow "Possibly My Research"
    Then I should see button "Mine"
    And I should see button "Not mine"
    Then I follow "Add Missing Publication"
    And I should see the test org unit username message

