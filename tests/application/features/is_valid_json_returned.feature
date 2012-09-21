# features/is_valid_json_returned.feature
# Needs to be run using goutte
Feature: Check that valid json is being returned

  Scenario: Check JSON is valid
    Given I am on "/publisher_suggest_proxy.php"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/publisher_suggest_proxy.php?query=a:4$%@})'}\%22({["
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/org_unit_suggest_proxy.php"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/org_unit_suggest_proxy.php?query=a:4$%@})']\%22({["
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/cv_id_proxy.php"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/cv_id_proxy.php?query=a:4$%@})']\%22({[&parent_id=a:4$%@})']\%22({["
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/suggest_proxy.php"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/suggest_proxy.php?query=a:4$%@})']\%22({[&sek_id=a:4$%@})']\%22({[&xsdmf_id=a:4$%@})']\%22({["
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/author_suggest_proxy.php"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/author_suggest_proxy.php?query=a:4$%@})']\%22({["
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/author_suggest_proxy.php?query=th"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/ajax_pid_outstanding_events.php"
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/ajax_pid_outstanding_events.php?pid=a:4$%@})']\%22({[&type=a:4$%@})']\%22({["
    Then should see valid JSON

  Scenario: Check JSON is valid
    Given I am on "/ajax_pid_outstanding_events.php?pid=a:4$%@})']\%22({[&type=WORKFLOW"
    Then should see valid JSON

  @broken   
  Scenario: Check JSON is valid
    Given I am on "/ajax_pid_outstanding_events.php?pid=a:4$%@})']\%22({[&type=BACKGROUND"
    Then should see valid JSON

  @broken   
  Scenario: Check JSON is valid
    Given I am on "/ajax_pid_outstanding_events.php?pid=a:4$%@})']\%22({[&type=COUNT"
    Then should see valid JSON

  @broken   
  Scenario: Check JSON is valid
    Given I am on "/conference_suggest_proxy.php"
    Then should see valid JSON

  @broken   
  Scenario: Check JSON is valid
    Given I am on "/conference_suggest_proxy.php?query=a:4$%@})']\%22({["
    Then should see valid JSON

  @broken   
  Scenario: Check JSON is valid
    Given I am on "/oembed.php?format=json"
    Then should see valid JSON

  @broken   
  Scenario: Check JSON is valid
    Given I am on "/oembed.php?format=json&maxwidth=a:4$%@})']\%22({[&maxheight=a:4$%@})']\%22({["
    Then should see valid JSON

  @broken   
  Scenario: Check JSON is valid
    Given I am on "/grid_proxy.php"
    Then should see valid JSON

  @broken   
  Scenario: Check JSON is valid
    Given I am on "/grid_proxy.php?_search=a:4$%@})']\%22({[&sidx=a:4$%@})']\%22({[&sord=a:4$%@})']\%22({[&page=a:4$%@})']\%22({[&rows=a:4$%@})']\%22({["
    Then should see valid JSON
