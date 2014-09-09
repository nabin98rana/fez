# This is the the "reason for edit/create" box that appears at the bottom when editing
# A record.

Feature: feedback facility

  @feedback-create0
  Scenario: recording a reason for edit (new record)
    Given I'm an editor
    When starting a new record workflow for collection in: public_community
    Then get uri for the action 'Save'
    Then using the available required fields in the xml
    Then update edit reason with 'Reason to create'
    Then POSTing that xml to our uri
    # Now let's check it...
    Then getting pid from the response
    Then getting element path uri 'detailed_history_uri'
    Then GETting that uri
    Then I should see element 'change'
    And I should get xml with element 'change/detail' and content matching /Reason to create/

  @feedback-edit0
  Scenario: recording a reason for edit (existing record)
    Given I'm an editor
    When starting an edit record workflow
    And using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    Then update edit reason with 'Updating the reason'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'
    # Now let's check it...
    When getting a record 'public_community.record'
    Then getting element path uri 'detailed_history_uri'
    Then GETting that uri
    Then I should see element 'change'
    And I should get xml with element 'change/detail' and content matching /Updating the reason/

