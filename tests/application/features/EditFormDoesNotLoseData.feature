#features/EditFormDoesNotLoseData.feature
@javascript @smoke @broken
Feature: Check on a simple edit then immediate save, all data stays the same in correct format and nothing is lost or incorrectly changed.

  @destructive @now1
  Scenario: Edit then save, check nothing changed that should not.
    Given I login as administrator
    And I am on "/view/UQ:10722"
    And I save record details
    And I follow "Update Selected Record - Generic"
    And I press "Save Changes"
    And I check record unchanged