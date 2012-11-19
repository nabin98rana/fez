# features/statuscodes.feature
# Needs to be run using goutte
@smoke
Feature: Check statuscodes are correctly working. Not found should return 404 response with help(ie menu) on where to go

  Scenario: Go to a pid that does not exist
    Given I am on "/view/UQ:6666666"
    Then the response status code should be 404
    And I should see "HOME"
    And I should see "SEARCH"

  Scenario: Go to a pid that does exist
    Given I go to the test journal article view page
    Then the response status code should be 200
    And I should see "HOME"
    And I should see "SEARCH"

  Scenario: Go to a resource that does not exist
    Given I am on "/eserv/UQ:157902/fdfddf.pdf"
    Then the response status code should be 404
    And I should see "HOME"
    And I should see "SEARCH"

  Scenario: Go to a resource that contains un allowed characters
    Given I am on "/eserv/UQ:157902/fdfddf.pdf/fdfdff"
    Then the response status code should be 404
    And I should see "HOME"
    And I should see "SEARCH"

  @bug @broken
  Scenario: Go to a php file that doesn't exist
    Given I am on "/fdkglsdfglk.php"
    Then the response status code should be 404
    And I should see "HOME"
    And I should see "SEARCH"
