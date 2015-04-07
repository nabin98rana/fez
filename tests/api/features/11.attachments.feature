
# This is for the purpose of getting the right cvo id to use when creating a record.

Feature: attachments

  @delete-attachment
  Scenario: deleting an attachment
    Given I'm an editor
    Given getting a record 'public_community.record'
    Given this record is loaded with attachment 'fixtures/sample-file-1.txt'
    When viewing the workflow 'Update Selected Record - Generic'
    Then I should get xml
    And I should see element 'datastreams'
    When getting element path uri '/workflow/datastreams/datastream/delete_uri'
    And POSTing that uri with body ''
    Then I should get xml with element 'status' and content containing '200'
    # Then viewing the record should have one less datastream - get workflows available

  @purge-attachment
  Scenario: purging an attachment
    Given I'm a super administrator
    Given getting a record 'public_community.record'
    Given this record is loaded with attachment 'fixtures/sample-file-2.txt'
    When viewing the workflow 'Update Selected Record - Generic'
    Then I should get xml
    And I should see element 'datastreams'
    When getting element path uri '/workflow/datastreams/datastream/purge_uri'
    And POSTing that uri with body ''
    Then I should get xml with element 'status' and content containing '200'
    # Then viewing the record should have one less datastream - get workflows available

  # UPLOADING
  #
  # We have to
  # 1) edit the record
  # 2) do the attachment upload
  # 3) alter any attributes in the edit record metadata
  # 4) submit the edit as per a normal edit operation

  @upload-attachment
  Scenario: uploading an attachment
    Given I'm an editor
    Given getting a record 'public_community.record'
    Given this record has no attachments
    When viewing the workflow 'Update Selected Record - Generic'
    Then I should get xml
    Then save the uri for display field 'File Upload'
    And POSTing that uri with attachment 'fixtures/sample-file-1.txt' as upload '1'
    Then I should get xml with element 'status' and content containing '202'
    # Then viewing the record should have one more datastream - get workflows available

  @download-attachment
  Scenario: downloading the attachment
    Given I'm an editor
    Given getting a record 'public_community.record'
    Given this record is loaded with attachment 'fixtures/sample-file-1.txt'
    Then I should be able to download the first attachment
    And the http status should be 200


  # **** Attachment security -- See editing-security feature. That is how this is edited. It is exposed via URI in edit-record xml returned.
  #Scenario: viewing / downloading close attachment
    # Test trying to do this a user that has no viewing privileges
    # ie the 'no groups' user in conf.ini?


  # The process for uplodaing is convoluted. We start the edit
  # workflow. Upload the files, and submit the edit xml with the
  # details of those uploads.

  # More details on upload permissions can be found
  # in //public/include/class.datastream.php

  @edit-upload-multi-attachment
  Scenario: updating a basic record with an attachment with permissions 1 (Accepted version) and embargo date set
    Given I'm an editor
    Given getting a record 'public_community.record'
    Given this record has no attachments
    When viewing the workflow 'Update Selected Record - Generic'
    And using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    When GETing the uri for display field 'File Upload'
    And POSTing that uri with attachment 'fixtures/sample-file-1.txt' as upload '1'
    And POSTing that uri with attachment 'fixtures/sample-file-2.txt' as upload '2'
    Then I should get xml with element 'status' and content containing '202'
    And add the datastream with display field id from the File Upload field being upload '1' with description 'This is an attachment' and permission '1' with embargo date '2012-12-15'
    And add the datastream with display field id from the File Upload field being upload '2' with description 'This is also an attachment' and permission '2' with embargo date '2012-12-15'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'
    Then getting a record 'public_community.record'
    Then I should get xml with datastream name 'sample-file-1.txt'
    Then I should get xml with datastream name 'sample-file-2.txt'
    Then remove all attachments
    # Note this doesn't purge the record now. Add in clean up all datastreams...
    # TODO add verification step for permissions

  @edit-upload-attachment
  Scenario: Editing a record and uploading one attachment
    Given I'm an editor
    Given getting a record 'public_community.record'
    Given this record has no attachments
    When viewing the workflow 'Update Selected Record - Generic'
    And using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    When GETing the uri for display field 'File Upload'
    And POSTing that uri with attachment 'conf.ini' as upload '1'
    Then I should get xml with element 'status' and content containing '202'
    And add the datastream with display field id from the File Upload field being upload '1' with description 'This is an attachment' and permission '1' with embargo date '2012-12-15'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'
    Then getting a record 'public_community.record'
    Then I should get xml with datastream name 'conf.ini'
    Then remove all attachments

  @edit-invalid-permission-attachment
  Scenario: Editing a record. Uploading an attachment without the required permission setting.
    Given I'm an editor
    Given getting a record 'public_community.record'
    Given this record has no attachments
    When viewing the workflow 'Update Selected Record - Generic'
    And using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    When GETing the uri for display field 'File Upload'
    And POSTing that uri with attachment 'conf.ini' as upload '1'
    Then I should get xml with element 'status' and content containing '202'
    # Note how the permission value isn't there
    And add the datastream with display field id from the File Upload field being upload '1' with description 'This is an attachment' and permission '' with embargo date '2012-12-15'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '400'

  @edit-upload-attachment-missing-id
  Scenario: Editing a record. Uploading an attachment without the required permission setting.
    Given I'm an editor
    Given getting a record 'public_community.record'
    Given this record has no attachments
    When viewing the workflow 'Update Selected Record - Generic'
    And using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    When GETing the uri for display field 'File Upload'
    And POSTing that uri with attachment 'conf.ini' as upload '1'
    Then I should get xml with element 'status' and content containing '202'
    # Note how the permission value isn't there
    And add the datastream with display field id from the File Upload field being upload '1' with description 'This is an attachment' and permission '1' with embargo date '2012-12-15'
    And remove the datastream id field
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '400'


  @edit-rename-attachment
  Scenario: Renaming an attachment
    Given I'm an editor
    Given getting a record 'public_community.record'
    # First remove all the attachments to just make sure we're starting with a blank slate
    Then remove all attachments
    Given this record is loaded with attachment 'fixtures/sample-file-1.txt'
    When viewing the workflow 'Update Selected Record - Generic'
    And using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    And change the datastream name of 'sample-file-1.txt' to 'renamed.txt'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'
    Then getting a record 'public_community.record'
    Then I should get xml with datastream name 'renamed.txt'
    Then remove all attachments


  # NOTE: Changing the permissions or embargo date require you to specify the filename and embargo_date and datastream permission
  @edit-change-permission-embargo-attachment
  Scenario: Change the permission of an attachment.
    Given I'm an editor
    Given getting a record 'public_community.record'
    Given this record is loaded with attachment 'fixtures/sample-file-1.txt'
    When viewing the workflow 'Update Selected Record - Generic'
    And using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    And change the datastream permission of 'sample-file-1.txt' to '3' with embargo date '2011-11-11'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'
    # TODO confirm this by checking the permission

  @edit-change-permission-embargo-attachment-missing-fields
  Scenario: Change the permission of an attachment.
    Given I'm an editor
    Given getting a record 'public_community.record'
    Given this record is loaded with attachment 'fixtures/sample-file-1.txt'
    When viewing the workflow 'Update Selected Record - Generic'
    And using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    And change the datastream permission of 'fixtures/sample-file-1.txt' to '' with embargo date '2011-11-11'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '400'
    # TODO confirm this by checking the permission

  @edit-change-file-description-attachment
  Scenario: Change the file attachment description of an attachment.
    Given I'm an editor
    Given getting a record 'public_community.record'
    Given this record is loaded with attachment 'fixtures/sample-file-1.txt'
    When viewing the workflow 'Update Selected Record - Generic'
    And using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    And change the datastream description of 'fixtures/sample-file-1.txt' to 'This is our new description'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'


