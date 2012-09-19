Feature: Check news displays correctly and Admin news displays only to the correct group

  @now
  Scenario: I login as admin and create a news story and check I can see it but non admin users can't
    Given I login as administrator
    And I am on "/manage/news.php"
    And I select "Active" from "status"
    And I select "Yes" from "admin_only"
    And I fill in "title" with "Admin Only News Title"
    And I fill in "message" with "Admin Only News message"
    And I press "Create News Entry"
    And I select "Active" from "status"
    And I select "No" from "admin_only"
    And I fill in "title" with "User News Title"
    And I fill in "message" with "User News message"
    And I press "Create News Entry"
    Given I am on "/"
    Then I should see "Admin Only News Title"
    And I should see "User News Title"
    And I follow "Admin Only News Title"
    Then I should see "Admin Only News message"
    And I should see "User News Message"
    And I follow "Logout"
    Then I should not see "Admin Only News Title"
    And I should see "User News Title"
    And I follow "User News Title"
    Then I should not see "Admin Only News message"
    And I should see "User News Message"


  @purge @broken @destructive
  Scenario: Delete last two news articles created
  Given I login as administrator
  And I am on "/manage/news.php"
  Then I should see "Admin Only News Title"
  #we assume the new news items are on the top
  And I should see "User News Title"
  And I check "items[]"
  And I press "Delete"
  And I confirm the popup
  And I check "items[]"
  And I press "Delete"
  And I confirm the popup
