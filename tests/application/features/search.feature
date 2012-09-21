# features/search.feature
@javascript
Feature: Search
  In order to see a word definition
  As a website user
  I need to be able to search for a word

  @smoke @broken
  Scenario: Searching for a pid that does exist
    Given I am on "/"
    When I fill in "front_search" with "water"
    And I press "submit-button"
    Then I should see "Effect of drinking saline water"

  Scenario: Searching for a pid that does NOT exist
    Given I am on "/"
    When I fill in "front_search" with "spaghetti monster"
    And I press "submit-button"
    Then I should see "(0 results found)"