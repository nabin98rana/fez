#features/EditFormDoesNotLoseData.feature
@javascript @destructive @jet @datadependant
Feature: Check on a simple edit then immediate save, all data stays the same in correct format and nothing is lost or incorrectly changed.

  Scenario: Edit then save, check nothing changed that should not.
    Given I login as administrator
    And I go to a random pid
    And I save record details
    And I follow "Update Selected Record - Generic"
    And I press "Save Changes"
    And I check record unchanged
