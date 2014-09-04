
Feature: viewing internal notes updating them

  @update-internal-note
  Scenario: update internal note
    Given I'm an editor
    When starting an edit record workflow
    And using the available required fields in the xml
    Then get uri for the action 'Save Changes'
    Then update internal note with 'This is a behat note'
    Then POSTing that xml to our uri
    Then I should get xml with element 'status' and content containing '202'
    Then using pid from the response
    Then I should get a pid number
    Then internal note for this pid should be 'This is a behat note'

  # Note: We can also view internal note by editing a record.

  @view-internal-note1
  Scenario: editors and approvers viewing internal note (suggestions)
    Given I'm an editor
    When getting a record 'public_community.record'
    And the internal note is already set to 'behat note' for 'public_community.record'
    Then I should get xml with element 'internal_notes' and content containing 'behat note'

  @view-internal-note2
  Scenario: viewers can't see note
    Given I'm a viewer
    When getting a record 'public_community.record'
    And the internal note is already set to 'behat note' for 'public_community.record'
    Then I should get xml but not see element 'internal_notes'
