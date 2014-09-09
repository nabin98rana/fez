# features/DataCollectionsAPI.feature
@javascript
Feature: Lets check ANDS API is up
  Scenario: Go there and see
  Given I turn off waiting checks
  And I am on "http://researchdata.ands.org.au/apps/assets/location_capture_widget/js/location_capture_widget.js"
  Then I should see "ANDS Location Capture Widget"
  And I am on "http://researchdata.ands.org.au/apps/assets/location_capture_widget/css/location_capture_widget.css"
  Then I should see "alw_dialog"