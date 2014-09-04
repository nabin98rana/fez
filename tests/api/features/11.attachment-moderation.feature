# Basic aims:
# - hide an attachment, even though the record may be public.
# - doing embargo?

Feature: attachment moderation / security

  @edit-attachment-permissions
  Scenario: Editing attachment security - See edit security
    Given I'm an editor
    Given getting a record 'public_community.record'
    Given this record is loaded with attachment 'README.txt'
    When viewing the workflow 'Update Selected Record - Generic'
    Then I should get xml
    And I should see element 'datastreams'
    When getting element path uri '/workflow/datastreams/datastream/edit_permission_uri'
    Then GETting that uri
    Then I should get xml
    Then using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    # test_creator is value 24
    Then add the display field with xsdmf_title 'Fez Group:' and xsdel_title 'Lister' value '24'
    Then add the display field with xsdmf_title 'Fez Group' and xsdel_title 'Lister' value '23'
    Then POSTing that xml to our uri
    Then the http status should be 202
    Then the attachment should have group 'test_creator' set to 'Lister'
    Then remove all attachments

  @viewer-restricted-datastream
  Scenario: If a datastream is restricted, then they shouldn't be able to view it
    Given I'm a super administrator
    # Given getting a record 'restricted_community.record'
    # # Call a bunch of steps to restrict the attachment:
    Given getting a record 'restricted_community.record'
    Given this record is loaded with attachment 'README.txt'
    When viewing the workflow 'Update Selected Record - Generic'
    Then I should get xml
    And I should see element 'datastreams'
    When getting element path uri '/workflow/datastreams/datastream/edit_permission_uri'
    Then GETting that uri
    Then I should get xml
    Then using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    # Set the permissions
    # Careful xsdmf_title of Viewer is 'Fez Group:'  -- which is why you would be using xsdmf_id instead
    # Also, the value '24' and '23' refer to the values given as options within the GET to this update security page
    And add the display field with xsdmf_title 'Fez Group:' and xsdel_title 'Viewer' value '24'
    And add the display field with xsdmf_title 'Fez Group' and xsdel_title 'Lister' value '23'
    And add the display field 'Inherit Security from Parent Hierarchy?' value 'off'
    Then POSTing that xml to our uri
    Then the http status should be 202
    Given I'm a user with no groups
    Given getting a record 'restricted_community.record'
    And the http status should be 200
    And I cannot see attachment 'README.txt'

  # Embargo - See @edit-change-permission-embargo-attachment in editing-records.feature
