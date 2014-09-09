# Tests edit_metatdata pathway...
Feature: editing records

  @edit-basic-case
  Scenario: getting creation workflows for available record types in collection
    Given I'm an editor
    Given getting a record 'public_community.record'
    When viewing the workflow 'Update Selected Record - Generic'
    Then using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    # The subject values are retrieved from doing a GET to that cont vocab listing
    And add the display field 'Subject' value '2792'
    And add the display field 'Subject' value '2791'
    And add the display field 'Refereed?' value 'off'
    Then POSTing that xml to our uri
    Then the http status should be 202
    Then I should get xml with element 'status' and content containing '202'

  @edit-basic-missing-required
  Scenario: getting creation workflows for available record types in collection
    Given I'm an editor
    Given getting a record 'public_community.record'
    When viewing the workflow 'Update Selected Record - Generic'
    Then using the available required fields in the xml
    And remove the display field 'Title' content
    Then get uri for the action 'Save Changes'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '400'

  @edit-view-workflow
  Scenario: getting a workflow in order to update a record
    Given I'm an editor
    When starting an edit record workflow
    Then I should get xml
    Then I should get root xml node: workflow
    Then I should see element 'wfses_id'
    And I should get xsd_display fields

  # AUTHORIZATION scenarios:

  @edit-closed-record
  Scenario: Editing as a viewer should get an access denied message
    Given I'm an editor
    Given getting a record 'public_community.record'
    When viewing the workflow 'Update Selected Record - Generic'
    Then using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    Given I'm a viewer
    Then POSTing that xml to our uri
    # You would think this would result in a 401, but in actuality it should be 500 since the workflow paramter does not exist for the user.
    Then the http status should be 500

