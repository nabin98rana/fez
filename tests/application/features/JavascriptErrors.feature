@javascript
Feature: Test pages for javascript errors

  @now1
  Scenario: I go to heaps of pages Then see if there are javascript errors present
    Given I am on "/"
    Then I check there are no Javascript errors
    #Given I follow "News"
    #Then I check there are no Javascript errors
    Given I follow "Recently Added"
    Then I check there are no Javascript errors
    Given I follow "Recently Popular"
    Then I check there are no Javascript errors
    Given I follow "Tags"
    Then I check there are no Javascript errors
    Given I am on "/list/"
    Then I check there are no Javascript errors
    Given I am on "/adv_search.php"
    Then I check there are no Javascript errors
    Given I am on "/faq"
    Then I check there are no Javascript errors
    Given I am on "/about"
    Then I check there are no Javascript errors
    Given I am on "/list/?search_keys%5B0%5D=water&submit=&cat=quick_filter&sort_by=searchKey0"
    Then I check there are no Javascript errors
    Given I am on "/login.php"
    Then I check there are no Javascript errors
    Given I am on "news.php"
    Then I check there are no Javascript errors
    Given I go to the test collection list page
    Then I check there are no Javascript errors
    Given I go to the test journal article view page

  @now2
  Scenario: I go to heaps of pages Then see if there are javascript errors present as super administrator
    Given I login as super administrator
    Given I am on "/"
    Then I check there are no Javascript errors
    #Given I follow "News"
    #Then I check there are no Javascript errors
    Given I follow "Recently Added"
    Then I check there are no Javascript errors
    Given I follow "Recently Popular"
    Then I check there are no Javascript errors
    Given I follow "Tags"
    Then I check there are no Javascript errors
    Given I am on "/list/"
    Then I check there are no Javascript errors
    Given I am on "/adv_search.php"
    Then I check there are no Javascript errors
    Given I am on "/faq"
    Then I check there are no Javascript errors
    Given I am on "/about"
    Then I check there are no Javascript errors
    Given I am on "/list/?search_keys%5B0%5D=water&submit=&cat=quick_filter&sort_by=searchKey0"
    Then I check there are no Javascript errors
    Given I am on "/login.php"
    Then I check there are no Javascript errors
    Given I am on "news.php"
    Then I check there are no Javascript errors
    Given I go to the test collection list page
    Then I check there are no Javascript errors
    Given I go to the test journal article view page

    #Admin independent
    Given I am on "/manage/news.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/groups.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/users.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/authors.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/statuses.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/sessions.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/languages.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/orgstructure.php"
    Then I check there are no Javascript errors
    #@bug @broken very slow
    Given I am on "/manage/matching.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/integrity.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/links.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/faqs.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/pages.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/masquerade.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/index_new.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/era_affiliation.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/rid_jobs.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/rid_profile_uploads.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/conferences_id.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/journals.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/conferences_era_2010.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/doctypexsds.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/citations.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/workflows.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/wfbehaviours.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/workflows.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/workflow_triggers.php?pid=-1"
    Then I check there are no Javascript errors
    Given I am on "/manage/controlled_vocab.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/search_keys.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/custom_views.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/custom_views_comm.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/thomson_doctype_mappings.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/configuration.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/ad_hoc.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/io_workflows.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/io_xsds.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/run_webstats.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/upgrade.php"
    Then I check there are no Javascript errors

  @now3
  Scenario:I go to edit pages Then see if there are javascript errors present as super administrator
    Given I login as super administrator
    Then I go to the test collection list page
    Then I select "Journal Article" from "xdis_id_top"
    Then I press "Create"
    Then I check there are no Javascript errors

    And I go to the test journal article view page
    And I follow "Edit Security for Select Record"
    Then I check there are no Javascript errors
    Then I press "Abandon Workflow"

    And I go to the test journal article view page
    And I follow "Edit Security for Select Record"
    Then I check there are no Javascript errors
    Then I press "Abandon Workflow"