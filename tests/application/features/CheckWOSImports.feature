# features/CheckWOSImports.feature
@javascript
Feature: WOS imports. Check imports from wos work correctly

  @destructive @now1 @insulated
  Scenario: Use add on entry form on a known wok article and ensure it imports correctly. "Influence of malt roasting on the oxidative stability of sweet wort" WOS:000304837700019
    Given I login as administrator
    And I go to the test collection list page
    And I select "Journal Article" from "xdis_id_top"
    And I press "Create"
    And I fill in "Title" with "Influence of malt roasting on the oxidative stability of sweet wort"
    And I select "Article" from "Sub-type"
    And I fill in "ISI LOC" with "Testing"
    And I wait for "2" seconds
    And I see "ctWosRec" id or wait for "300" seconds
    And I click on the element with css selector "span#ctWosRec"
    And I see "pub_add" id or wait for "300" seconds
    And I follow "Add record"
    And I see "pub_link" id or wait for "300" seconds
    Then I press "Abandon Workflow"
    And I fill in "Search Entry" with "title:(\"Influence of malt roasting on the oxidative stability of sweet wort\")"
    And I press "search_entry_submit"
    And I follow "Influence of malt roasting on the oxidative stability of sweet wort"
    And I should see "Influence of malt roasting on the oxidative stability of sweet wort"
    And I should see "Journal of Agricultural and Food Chemistry"
    And I should see "2012-06"
    And I should see "Article"
    And I should see "60"
    And I should see "0021-8561"
    And I should see "5652"
    And I should see "5659"
    And I should see "eng"
    And I should see "malt"
    And I should see "sweet wort"
    And I should see "Hoff S."
    And I should see "Lund M.N."
    And I should see "Scopus Import"
    And I should see "10.1021/jf300749r"

  @destructive @core @purge
Scenario: Delete WOS imports
  Given I login as administrator
  And I fill in "Search Entry" with "title:(\"Influence of malt roasting on the oxidative stability of sweet wort\")"
  And I press "search_entry_submit"
  And I press "Select All"
  And I turn off waiting checks
  And I press "Delete"
  And I confirm the popup
  And I fill "automated test data cleanup" in popup
  And I confirm the popup

  @destructive @now2
  Scenario: Add to WOS queue and make sure it imports
    Given I login as administrator
    And I turn off waiting checks
    And I add "000304837700019" to the WOK queue
    Given I am on "/misc/process_wok_queue.php"
    And I am on "/"
    And I turn on waiting checks
    And I wait for "10" seconds
    And I fill in "Search Entry" with "title:(\"Influence of malt roasting on the oxidative stability of sweet wort\")"
    And I press "search_entry_submit"
    And I follow "Influence of malt roasting on the oxidative stability of sweet wort"
    And I should see "Influence of malt roasting on the oxidative stability of sweet wort"
    And I should see "Journal of Agricultural and Food Chemistry"
    And I should see "2012-06"
    And I should see "Article"
    And I should see "60"
    And I should see "0021-8561"
    And I should see "5652"
    And I should see "5659"
    And I should see "eng"
    And I should see "malt"
    And I should see "sweet wort"
    And I should see "Hoff S."
    And I should see "Lund M.N."
    And I should see "Scopus Import"
    And I should see "10.1021/jf300749r"

  @destructive @core @now2
Scenario: Delete WOS imports
  Given I login as administrator
  And I fill in "Search Entry" with "title:(\"Influence of malt roasting on the oxidative stability of sweet wort\")"
  And I press "search_entry_submit"
  And I press "Select All"
  And I turn off waiting checks
  And I press "Delete"
  And I confirm the popup
  And I fill "automated test data cleanup" in popup
  And I confirm the popup
  And I turn on waiting checks
  And I am on "/"
