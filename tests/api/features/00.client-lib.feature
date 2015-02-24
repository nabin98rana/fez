# Some tests for the client lib.

Feature: using client libs
  Scenario: loading xml fixture
    Given DisplayType loads metadata: enter_metadata-example.xml
    Given Workflow loads metadata: enter_metadata-example.xml
    Given Workflow loads collection: collection-example.xml
