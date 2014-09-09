# http://cdu.danb/history.php?pid=cdu:24141

Feature: viewing edit / audit history

  @view-edit-history
  Scenario:
    When getting a record 'public_community.record'
    Then I should get root xml node: xmlfeed
    Then getting element path uri 'detailed_history_uri'
    Then GETting that uri
    Then I should get xml
    # should see at least one change made for the edit history
    And I should see element 'change'
