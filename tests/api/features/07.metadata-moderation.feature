
# This includes: restrict access / make public
#
# Also see attachment moderation
# eg
# http://cdu.danb/workflow/edit_metadata.php?id=267&wfs_id=989&format=xml
# will give us the following links:
#   workflow/actions/action
#     - Reject
#     - Save Changes
#     - Submit for Approval
#     - Preview
#     - Publish
#     - Abandon Workflow
#
# Steps:
# - get workflow id wfses_id /workflow/update
# - get workflow id wfses_id

Feature: moderating records

  # METADATA MODERATION

  # NOTE:
  # ------------------------------------------------------------
  # type 0 = no groups user or viewer/lister
  # type 1 = editor (by extension a creator also)
  # type 2 = approver



  # ------------------------------------------------------------
  # Type 1 and type 2 common privileges...
  #
  # Type 2 people should have all the freedoms of type 1, to
  # be sure, we should test both editor and approver roles...


  # Possible Todo: Add in the ability to view all unpublished and published as confirmation.
  # This relates to my_fez_traditional and related views in fez where
  # we can view just the unpublished items or just the ones submitted
  # for approval etc.

  @type0-view-unpublished
  Scenario: type0 viewing unpublished records
    Given I'm a user with no groups
    When getting a record 'public_community.unpublished_record'
    Given the record should be unpublished
    Then the http status should be 401

  @type1-view-unpublished
  Scenario: type1 viewing all unpublished
    Given I'm an editor
    When getting a record 'public_community.unpublished_record'
    Given the record should be unpublished
    Then the http status should be 200

  @type2-view-unpublished
  Scenario: type2 viewing all unpublished
    Given I'm an approver
    When getting a record 'public_community.unpublished_record'
    Given the record should be unpublished
    Then the http status should be 200

  # Normal moderation stuff...

  @type1-view
  Scenario: type1 people viewing options
    Given I'm an editor
    When starting a new record workflow for collection in: public_community
    Then I can't see workflow: publish
    Then I can't see workflow: reject
    Then I can see workflow: save
    Then I can see workflow: submit for approval

  @type1-submit
  Scenario: type1 submitting a record for approval
    Given I'm an editor
    Then I can do 'submit for approval' on new record for collection in: public_community
    Then the record should be in state: submit for approval

  @type1-save
  Scenario: type1 saving a record
    Given I'm an editor
    Then I can do 'save' on new record for collection in: public_community
    Then the record should be in state: in creation

  # ------------------------------------------------------------
  # Additional type 2 privileges...
  #
  # We also test that type 1's don't have these...

  # This shouldn't show if we crawl the available actions, however if
  # we try to do this action anyway, we should 401 it...

  # -----------------------------------------------------------
  # Handling errors.
  # If Zend catches the error it's a 500 (a value is sent which causes an incorrect display type) or fez catches the error it's a 400 (bad status for example)
  @junk-action
  Scenario: I shouldn't be able to send the server junk parameters.
    Given I'm an editor
    When starting a new record workflow for collection in: public_community
    When I try to do a workflow with bad workflow parameter
    Then the http status should be 400

  @junk-action-no-xdis-id
  Scenario: I shouldn't be able to send the server junk parameters.
    Given I'm an editor
    When starting a new record workflow with bad display id
    Then I should get xml
    Then the http status should be 400

  # Trying to publish
  # Note: Currently when you send a publish workflow value fez goes through the steps to publish, ie. save new record then publish new record. It'll do the save record step, then fail out on the workflow publish step.
  @type1-publish-records
  Scenario: type1 people creating records
    Given I'm an editor
    When starting a new record workflow for collection in: public_community
    When I try to do workflow: publish
    Then the http status should be 202
    And it should be in state: unpublished

  @type2-publish-records
  Scenario: type2 people publishing new records
    Given I'm an approver
    When starting a new record workflow for collection in: public_community
    Then I can see workflow: publish
    Then I try to do workflow: publish
    Then the http status should be 202
    Then it should be in state: published

  # There is no functionality at the moment to automate approval. We can however resubmit the record using the Publish action, if we are able to do so. That is what this step does.
  @type2-approver-publish-unpublished
  Scenario: type2 people moderating records
    Given I'm an approver
    When getting a record 'public_community.unpublished_record'
    Given the record should be unpublished
    When viewing the workflow 'Update Selected Record - Generic'
    Then I can see workflow: publish
    Then using the available required fields in the xml
    Then get uri for the action 'Publish'
    Then POSTing that xml to our uri
    Then the http status should be 202
    Then it should be in state: published

  # There is no functionality at the moment to automate rejection. We can however resubmit the record using the reject action, if we are able to do so. That is what this step does.
  @type2-reject-records
  Scenario: type2 people moderating records
    Given I'm an approver
    When getting a record 'public_community.unpublished_record'
    When viewing the workflow 'Update Selected Record - Generic'
    Then I can see workflow: reject
    Then using the available required fields in the xml
    Then get uri for the action 'Reject'
    Then POSTing that xml to our uri
    # The response is a page where you set an email body for the rejection message
    Then get uri for the action 'Reject Finalise'
    Then POSTing the rejection message 'Dear Mr. Tester, You have been rejected.'
    Then the http status should be 202
    Then the record should be in state: in creation

