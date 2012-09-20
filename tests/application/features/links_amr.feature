# features/links_amr.feature
Feature: Links Article Match Retrieve Serive

  @destructive @now @broken
  Scenario: The system automatically finds an ISI Loc for a pid that already exists on another different pid
    and doesn't add it to the pid, instead it emails the helpdesk system with a request to manually resolve which
    pid should get it and which pid should possibly be deleted
    Given I send a empty pid to Links AMR that will get back an existing ISI Loc pid
    Then the empty Links AMR test pid should not get the ISI Loc
    #wait so the helpdesk system has time to process the email
    Then I wait for "80" seconds
    And helpdesk system should have an email with the ISI Loc and pid in the subject line