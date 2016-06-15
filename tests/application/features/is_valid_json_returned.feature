# features/is_valid_json_returned.feature
# Needs to be run using goutte
Feature: Check that valid json is being returned

  @jet
  Scenario: Check JSON is valid
    Given I am on "/publisher_suggest_proxy.php"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/publisher_suggest_proxy.php?query=a:4$%@})'}\%22({["
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/org_unit_suggest_proxy.php"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/org_unit_suggest_proxy.php?query=a:4$%@})']\%22({["
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/cv_id_proxy.php"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/cv_id_proxy.php?query=a:4$%@})']\%22({[&parent_id=a:4$%@})']\%22({["
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/suggest_proxy.php"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/suggest_proxy.php?query=a:4$%@})']\%22({[&sek_id=a:4$%@})']\%22({[&xsdmf_id=a:4$%@})']\%22({["
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/author_suggest_proxy.php"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/author_suggest_proxy.php?query=a:4$%@})']\%22({["
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/author_suggest_proxy.php?query=th"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/ajax_pid_outstanding_events.php"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/ajax_pid_outstanding_events.php?pid=a:4$%@})']\%22({[&type=a:4$%@})']\%22({["
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/ajax_pid_outstanding_events.php?pid=a:4$%@})']\%22({[&type=WORKFLOW"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/ajax_pid_outstanding_events.php?pid=a:4$%@})']\%22({[&type=BACKGROUND"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/ajax_pid_outstanding_events.php?pid=a:4$%@})']\%22({[&type=COUNT"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/pid_suggest_proxy.php"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/pid_suggest_proxy.php?query=a:4$%@})']\%22({["
    Then should see valid JSON

  @cloned
  Scenario: Check JSON is valid
    Given I am on "/pid_suggest_proxy.php?query=water"
    Then I should see "water"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/conference_suggest_proxy.php"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/conference_suggest_proxy.php?query=a:4$%@})']\%22({["
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/list/?cat=quick_filter&sort_by=searchKey0&&tpl=11"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/oembed.php?format_type=json"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/oembed.php?format_type=json&maxwidth=a:4$%@})']\%22({[&maxheight=a:4$%@})']\%22({["
    Then should see valid JSON

  @cloned
  Scenario: Check JSON is valid
    Given I am on "/api/latest_metric_changes.php?author_username=uqpburn2"
    Then should see valid JSON

  @jet
  Scenario: Check JSON is valid
    Given I am on "/api/latest_metric_changes.php?author_username=a:4$%@})']\%22({["
    Then should see valid JSON

  @cloned
  Scenario: Check JSON is valid
    Given I am on "/api/publons_reviews.php?author_username=uqpburn2"
    Then should see valid JSON

  @cloned
  Scenario: Check JSON is valid
    Given I am on "/api/publons_reviews.php?author_username=a:4$%@})']\%22({["
    Then should see valid JSON

  @cloned
  Scenario: Check authentication is checked
    Given I am on "/api/publons_reviews.php?author_username=uqpburn2"
    Then the response status code should be 401

  @jet
  Scenario: Check JSON is valid
    Given I am on "/api/ids.php?author_username=a:4$%@})']\%22({["
    Then should see valid JSON

  @jet
  Scenario: Check authentication is checked
    Given I am on "/api/ids.php?author_username=uqpburn2"
    Then the response status code should be 401