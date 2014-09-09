
Feature: getting communities

  @com-public
  Scenario:
    Given I'm a user with no groups
    When getting a community from: public_community
    Then I should get root xml node: xmlfeed
    And it should contain a list of collections

  @com-restricted-denied
  Scenario:
    Given I'm a user with no groups
    When getting a community from: restricted_community
    Then I should get xml
    Then the http status should be 401

  @com-nonexistent
  Scenario:
    Given I'm a nonexistent user
    When getting a community from: restricted_community
    Then I should get xml
    Then the http status should be 401

  @com-no-auth
  Scenario:
    Given I'm not using basic authentication
    When getting a community from: restricted_community
    Then I should get xml
    Then the http status should be 401

  @com-restricted
  Scenario:
    Given I'm a super administrator
    When getting a community from: restricted_community
    Then I should get root xml node: xmlfeed

  @com-viewer
  Scenario:
    Given I'm a viewer
    When getting a community from: restricted_community
    Then I should get root xml node: xmlfeed

  @com-no-groups
  Scenario:
    Given I'm a user with no groups
    When getting a non-existent community
    Then I should get xml
    Then the http status should be 404
