# Test behat is working, should pass.

Feature: hello world

  @helloworld
  Scenario: testing this framework
    Given this is a hello world
    Then I should get:
      """
      hello world
      """
