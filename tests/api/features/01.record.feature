
Feature: getting records

  @rec-public
  Scenario:
    Given I'm a user with no groups
    When getting a record 'public_community.record'
    Then I should get root xml node: xmlfeed
    And I should see element 'workflows'

  @rec-nonexistent
  Scenario:
    Given I'm a user with no groups
    When getting a non-existent record
    Then I should get xml
    And the http status should be 404
    And I should get an error message: not-found 

  @rec-super-admin
  Scenario:
    Given I'm a super administrator
    When getting a record 'restricted_community.record'
    Then I should get root xml node: xmlfeed
    And the http status should be 200

  @rec-denied
  Scenario:
    Given I'm a user with no groups
    When getting a record 'restricted_community.record'
    Then I should get root xml node: response
    And the http status should be 401

  @rec-viewer
  Scenario: users with viewer privileges on a collection
    Given I'm a viewer
    When getting a record 'restricted_community.record'
    Then I should get root xml node: xmlfeed
    And the http status should be 200

  @rec-editor
  Scenario: users with viewer privileges on a collection
    Given I'm an editor
    When getting a record 'restricted_community.record'
    Then I should get root xml node: xmlfeed
    And the http status should be 200

  @rec-approver
  Scenario: users with viewer privileges on a collection
    Given I'm an approver
    When getting a record 'restricted_community.record'
    Then I should get root xml node: xmlfeed
    And the http status should be 200


