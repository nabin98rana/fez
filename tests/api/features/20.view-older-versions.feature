Feature: viewing older versions

  @old-version
  Scenario: viewing an older version
    Given I'm a super administrator
    Given getting a record 'public_community.record'
    # /versions/version/view_uri
    When getting element path uri '/xmlfeed/versions/version/view_uri'
    Then GETting that uri
    # 201 is part of the year. We're just confirming we're getting something back
    Then I should get xml with element 'rec_version' and content containing '201'