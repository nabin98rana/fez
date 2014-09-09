Feature: copying and moving between collections

  @copy1
  Scenario: copy to collections
    Given I'm an editor
    Given getting a record 'public_community.record'
    When viewing the workflow 'Update Selected Record - Generic'
    Then using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    And add the display field 'Member of Collections:' value 'cdu:29713'
    Then POSTing that xml to our uri
    Then the http status should be 202
    When getting a record 'public_community.record'
    Then 'public_community.record' should belong to 'cdu:29713'