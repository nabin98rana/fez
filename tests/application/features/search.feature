# features/search.feature
@javascript
Feature: Search
  In order to see a word definition
  As a website user
  I need to be able to search for a word

  @smoke
  Scenario Outline: Searching for pids
    Given I am on "/"
    When I fill in "front_search" with <searchterm>
    And I press "submit-button"
    Then I should see <output>

  Examples:
  | searchterm                                                             | output               |
  | "Water: AWA/IWA 2nd Australian young water professionals conference"   | "Fogelman"           |
  | "spaghetti monster_invalid"                                            | "(0 results found)"  |

