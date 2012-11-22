# features/ThesisUpload.feature
@javascript
Feature: Test that Thesis upload correctly. @bug it can't check the swf uploader

  Scenario: I login as admin, create a pid, then delete it and check it is not longer accessiable
    Given I login as user no groups
    And I am on "/rhdsubmission"
    And I should see "Error: No workflows defined for Create."
    And I follow "Logout"
    Given I login as administrator
    And I am on "/rhdsubmission"
    And I press "Add New"
    And I turn off waiting checks
    And I switch to window "Add_from_Src_to_Dest"
    And I follow "Fields of Research"
    And I follow "01 Mathematical Sciences"
    And I switch to window ""
    And I turn on waiting checks
    And I fill in "Thesis Title" with "Thesis Test Name 2012"
    And I fill in "Author 1" with "Thesis Test Writer Name"
    And I check "Copyright Agreement"
    And I press "Upload your Thesis"
    And I should see "Thesis Submission Completed"
    And I should see "01 Mathematical Sciences"
    And I should see "Thesis Test Writer Name"
    And I should see "Thesis Test Name 2012"
    And I should see "UQ Theses Submission and Review"
    And I fill in "Search Entry" with "title:(\"Thesis Test Name 2012\")"
    And I press "search_entry_submit"
    And I should see "No records could be found."
    And I am on "/my_fez_traditional.php"
    And I fill in "Title" with "Thesis Test Name 2012"
    And I press "search_button"
    And I should see "Thesis Test Writer Name"
    And I follow "Thesis Test Name 2012"
    And I follow "Delete Selected Record"
    And I fill in "historyDetail" with "Test Thesis"
    And I press "Delete"
    And I should see "This record has been deleted."