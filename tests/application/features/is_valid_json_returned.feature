# features/is_valid_json_returned.feature
# Needs to be run using goutte
Feature: Check that valid json is being returned

  Scenario: Check JSON is valid
    Given I am on "/publisher_suggest_proxy.php"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/publisher_suggest_proxy.php?query=a:4$%@})'}\%22({{"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/org_unit_suggest_proxy.php"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/org_unit_suggest_proxy.php?query=a:4$%@})']\%22({{"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/cv_id_proxy.php"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/cv_id_proxy.php?query=a:4$%@})']\%22({{&parent_id=a:4$%@})']\%22({{"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/suggest_proxy.php"
    Then should see valid JSON
  Scenario: Check JSON is valid
    Given I am on "/suggest_proxy.php?query=a:4$%@})']\%22({{&sek_id=a:4$%@})']\%22({{&xsdmf_id=a:4$%@})']\%22({{"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/author_suggest_proxy.php"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/author_suggest_proxy.php?query=a:4$%@})']\%22({{"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/author_suggest_proxy.php?query=th"
    Then should see valid JSON
    And put a breakpoint

  Scenario: Check JSON is valid
    Given I am on "/conference_suggest_proxy.php"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/conference_suggest_proxy.php?query=a:4$%@})']\%22({{"
    Then should see valid JSON