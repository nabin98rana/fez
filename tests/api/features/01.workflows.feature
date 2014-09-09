
Feature: getting workflows

  @workflow-get-active
  Scenario: get active workflows
    Given I'm a super administrator
    When getting active workflows
    Then I should get root xml node: workflows
    # We should get a list of active workflows, which could potentially
    # be 0 if there is nothing active.


  @workflow-delete-active
  Scenario: deleting active workflows
    Given I'm a super administrator
    Then delete active workflows


  @workflow-abandon
  Scenario: getting creation workflows for available record types in collection
    Given I'm a super administrator
    Given getting a record 'public_community.record'
    When viewing the workflow 'Update Selected Record - Generic'
    Then get uri for the action 'Abandon Workflow'
    Then GETting that uri
    Then I should get xml with element 'status' and content containing 'OK'
