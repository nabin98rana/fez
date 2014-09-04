# Ex. See http://cdu.local/workflow/edit_security.php?id=156&wfs_id=917&format=xml
Feature: editing security

  @editsec1
  Scenario:
    Given I'm an administrator
    Given getting a record 'edit_security.record'
    When viewing the workflow 'Edit Security for Select Record'
    Then I should get no errors
    Then I should get xml
    Then the http status should be 200

  @editsec-set-group
  Scenario: setting a group
    Given I'm an administrator
    Given 'edit_security.record' is set with no groups
    Given getting a record 'edit_security.record'
    And viewing the workflow 'Edit Security for Select Record'
    Then using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    Then add the display field with xsdmf_title 'Fez Group' and xsdel_title 'Lister' value 'test_creator'
    Then POSTing that xml to our uri
    Then the http status should be 202
    Then I should get xml with element 'status' and content containing '202'
    Then record 'edit_security.record' should have group 'creator_group'


# test that editors and approvers can't do this
