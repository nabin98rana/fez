
Feature: creating records

  @create-basic-case
  Scenario: Creating a basic record
    Given I'm an approver
    When starting a new record workflow for collection in: public_community
    Then I should see element 'wfses_id'
    # This means 'publish':
    When posting to create a new record
    Then I should get no errors
    And I should get xml
    And I should get a pid number
    And the record should be created
    And the record should be published
    And the record should belong to collection in: public_community
    And the workflow should be finished

  @create0-basic-case
  Scenario: getting creation workflows for available record types in collection
    Given I'm an editor
    When starting a new record workflow for collection in: public_community
    Then using that response xml
    # required fields are popultaed
    Then get uri for the action 'Save'
    Then using the available required fields in the xml
    Then POSTing that xml to our uri
    Then the http status should be 202
    Then I should get xml with element 'status' and content containing '202'

  @create-basic-case-crawl
  Scenario: creating a record from a specified collection. For CDU this is Past Exam papers
    Given I'm an editor
    Given I'm on the collection page 'public_community.collection'
    When starting a new record workflow for 'public_community.create_record_action_name'
    Then using that response xml
    Then get uri for the action 'Save'
    Then as a new request
    And add the display field 'Member of Collections:' value 'cdu:29713'
    And add the display field 'Member of Collections:' value 'cdu:9444'
    And add the display field 'Member of Collections:' value 'cdu:29715'
    And add the display year date field 'Collection year' value '2007'
    And add the display field 'Title' value 'This is a test'
    # 'Translated title' is in the same looping subelement as 'title':
    And add the display field 'Translated title' value 'This is the translated title'
    And add the display field 'Copyright Agreement' value '1'
    # specifying year date indicates we'll wrap the year like such <xsdmf_value><year>2008</year></xsdmf_value> --- This is important to differentiate year dates from full dates like the form 10-10-2015 which is used for calendar pickers
    # You should use "add the display date field" for those display fields which are the latter dates.
    And add the display year date field 'Published' value '2008'
    # This is not a mods item:
    And add the link datastream 'some-doi-id'
    Then POSTing that xml to our uri
    Then the http status should be 202
    Then I should get xml with element 'status' and content containing '202'
    Then the record should have link datastream 'some-doi-id'
    Then the record should have display field 'Title' with value 'This is a test'
    Then the record should have display field 'Translated title' with value 'This is the translated title'
    # etc

  @create-workflow
  Scenario: getting a workflow in order to create a new record
    Given I'm an editor
    # This will GET workflow/new.php but redirect to edit_metadata
    # after generating a fez_workflow_session.id (wfses_id).
    When starting a new record workflow for collection in: public_community
    Then I should get no errors
    Then I should get xml
    Then I should see element 'wfses_id'
    Then I should get xsd_display fields



  # AUTHORIZATION scenarios
  #
  # Scenarios for users with different roles (other than super administrator)

  @create-invalid-user
  Scenario: Creating a record with invalid wfses_id
    Given I'm a user with no groups
    When starting a new failing record workflow for collection in: public_community
    Then I should get xml
    Then the http status should be 417

  @create-as-viewer
  Scenario: Creating a basic record as a viewer
    Given I'm a viewer
    When starting a new failing record workflow for collection in: public_community
    Then I should get xml
    Then the http status should be 417

  @create-as-editor
  Scenario: Creating a basic record as an editor
    Given I'm an editor
    When starting a new record workflow for collection in: public_community
    Then I should get xml
    Then the http status should be 200

  @create-as-approver
  Scenario: Creating a basic record as an editor
    Given I'm an approver
    When starting a new record workflow for collection in: public_community
    Then I should get xml
    Then the http status should be 200


  # VALIDATION scenarios:
  @create-missing-required
  Scenario: Creating a record with missing required field
    Given I'm an editor
    When starting a new record workflow for collection in: public_community
    Then using the available required fields in the xml
    And remove the display field 'Title' content
    Then get uri for the action 'Save'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '400'


  @create-publish
  Scenario: Creating a record and using the publish action
    Given I'm an approver
    When starting a new record workflow for collection in: public_community
    Then using the available required fields in the xml
    Then get uri for the action 'Publish'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'


  @create-save
  Scenario: Creating a record and using the save action
    Given I'm an editor
    When starting a new record workflow for collection in: public_community
    Then using the available required fields in the xml
    Then get uri for the action 'Save'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'

  @create-thesis-self-submission
  Scenario: Creating a self submission thesis document
    Given I'm a super administrator
    When starting a new record workflow for collection in: thesis
    Then starting the thesis action 'Student Submission of Thesis'
    Then using the available required fields in the xml
    Then get uri for the action 'Upload your Thesis'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'

  @create-thesis-generic
  Scenario: Creating a thesis document based on a generic record
    Given I'm a super administrator
    When starting a new record workflow for collection in: thesis
    Then starting the thesis action 'Create Generic Record In Selected Collection'
    Then using the available required fields in the xml
    Then get uri for the action 'Save'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'

