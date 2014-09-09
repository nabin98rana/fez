
Feature: getting collections

  @col-basic
  Scenario:
    When getting a collection from: public_community
    Then I should get root xml node: xmlfeed
    And it should contain a list of records

  # AUTHORIZATION SCENARIOS

  # For this, you need to add one of the groups of your api (basic
  # auth) user to the LISTER role for the collection.
  # Then, the collection won't be viewable.

  @col-denied
  Scenario: user with no groups trying to access group with lister role
    Given I'm a user with no groups
    When getting a collection from: restricted_community
    Then I should get xml
    Then the http status should be 401

  @col-lister
  Scenario:
    Given I'm a lister
    When getting a collection from: restricted_community
    Then I should get xml
    Then I should get root xml node: xmlfeed

  @col-viewer
  Scenario: user with no groups trying to access group with lister role
    Given I'm a viewer
    When getting a collection from: restricted_community
    Then I should get xml
    Then I should get root xml node: xmlfeed

  @col-super-admin
  Scenario:
    Given I'm a super administrator
    When getting a collection from: restricted_community
    Then I should get xml
    Then I should get root xml node: xmlfeed

  @col-nonexistent-user
  Scenario:
    Given I'm a nonexistent user
    When getting a collection from: restricted_community
    Then I should get xml
    Then the http status should be 401

  @col-no-auth
  Scenario:
    Given I'm not using basic authentication
    When getting a collection from: restricted_community
    Then I should get xml
    Then the http status should be 401

  @col-nonexistent
  Scenario:
    Given I'm a user with no groups
    When getting a non-existent collection
    Then I should get xml
    Then the http status should be 404

  @col-large-num-of-recs
  Scenario: getting a collection with large number of records
    Given I'm a user with no groups
    When getting a large collection from: public_community with 25 rows at page 0
    Then I should get root xml node: xmlfeed
    Then I should get 25 records in the collection
    Then rows should be 25 and pager_row should be 0

  @col-large-num-of-recs-page2
  Scenario: getting a collection with large number of records at the second page
    Given I'm a user with no groups
    When getting a large collection from: public_community with 25 rows at page 1
    Then I should get root xml node: xmlfeed
    Then I should get 25 records in the collection
    Then rows should be 25 and pager_row should be 1

  @col-large-num-of-recs-num-rows
  Scenario: getting a collection with large number of records with different num of rows
    Given I'm a user with no groups
    When getting a large collection from: public_community with 50 rows at page 0
    Then I should get root xml node: xmlfeed
    Then I should get 50 records in the collection
    Then rows should be 50 and pager_row should be 0

  @col-viewer-download-attachment
  Scenario: viewer downloading an attachment from a public
    Given I'm a viewer
    When getting a collection from: public_community
    Then I should get xml
    Then I should get root xml node: xmlfeed
    Then I should be able to download the first datastream_link attachment
    And the http status should be 200
