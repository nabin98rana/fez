# features/search.feature
@javascript @smoke @jet
Feature: Search
  In order to see a word definition
  As a website user
  I need to be able to search for a word

  Scenario Outline: Searching for pids
    Given I am on "/"
    When I fill in "front_search" with <searchterm>
    And I press "submit-button"
    Then I should see <output>

  Examples:
  | searchterm                   | output               |
  | "Test Record"                | "Test Author"        |
  | "spaghetti monster_invalid"  | "(0 results found)"  |

