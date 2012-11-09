@javascript @smoke
Feature: Test pages for javascript errors
#We have added Javascript tests to all pages tested so for now we just have to visit pages that do not have tests on them already

  @now1
  Scenario: I go to heaps of pages Then see if there are javascript errors present
    Given I am on "/"
    Given I follow "Recently Added"
    Given I am on "/"
    Given I follow "Recently Popular"
    Given I am on "/"
    Given I follow "Tags"
    Given I am on "/"
    Given I am on "/list/"
    Given I am on "/adv_search.php"
    Given I am on "/faq"
    Given I am on "/about"
    Given I am on "/list/?search_keys%5B0%5D=water&submit=&cat=quick_filter&sort_by=searchKey0"
    Given I am on "/login.php"
    Given I am on "news.php"
    Given I go to the test collection list page
    Given I go to the test journal article view page

  @now2
  Scenario: I go to heaps of pages Then see if there are javascript errors present as super administrator
    Given I login as super administrator
    Given I am on "/"
    Given I follow "Recently Added"
    Given I am on "/"
    Given I follow "Recently Popular"
    Given I am on "/"
    Given I follow "Tags"
    Given I am on "/list/"
    Given I am on "/adv_search.php"
    Given I am on "/faq"
    Given I am on "/about"
    Given I am on "/list/?search_keys%5B0%5D=water&submit=&cat=quick_filter&sort_by=searchKey0"
    Given I am on "/login.php"
    Given I am on "news.php"
    Given I am on "/favourites"
    Given I am on "/search_favourites.php"
    Given I go to the test collection list page
    Given I go to the test journal article view page
    Given I am on "/my_research_claimed.php"
    Given I am on "/my_research_possible.php"
    Given I am on "/my_research_add.php"
    Given I am on "/my_upo_tools.php"
    #Admin independent
    Given I am on "/my_processes.php"
    Given I am on "/my_collections.php"
    #@bug
    #Given I am on "/my_created_items.php"
    Given I am on "/my_fez_traditional.php"
    Given I am on "/my_work_in_progress.php"
    Given I am on "/submitted_for_approval.php"
    Given I am on "/in_review.php"
    Given I am on "/my_active_workflows.php"
    Given I am on "/manage/"
    Given I am on "/manage/news.php"
    Given I am on "/manage/groups.php"
    Given I am on "/manage/users.php"
    Given I am on "/manage/authors.php"
    Given I am on "/manage/statuses.php"
    Given I am on "/manage/sessions.php"
    Given I am on "/manage/languages.php"
    Given I am on "/manage/orgstructure.php"
    #@bug @broken very slow
    Given I am on "/manage/matching.php"
    Given I am on "/manage/integrity.php"
    Given I am on "/manage/links.php"
    Given I am on "/manage/faqs.php"
    Given I am on "/manage/pages.php"
    Given I am on "/manage/masquerade.php"
    Given I am on "/manage/index_new.php"
    Given I am on "/manage/era_affiliation.php"
    Given I am on "/manage/rid_jobs.php"
    Given I am on "/manage/rid_profile_uploads.php"
    Given I am on "/manage/conferences_id.php"
    Given I am on "/manage/journals.php"
    Given I am on "/manage/conferences_era_2010.php"
    Given I am on "/manage/doctypexsds.php"
    Given I am on "/manage/citations.php"
    Given I am on "/manage/workflows.php"
    Given I am on "/manage/wfbehaviours.php"
    Given I am on "/manage/workflows.php"
    Given I am on "/manage/workflow_triggers.php?pid=-1"
    Given I am on "/manage/controlled_vocab.php"
    Given I am on "/manage/search_keys.php"
    Given I am on "/manage/custom_views.php"
    Given I am on "/manage/custom_views_comm.php"
    Given I am on "/manage/thomson_doctype_mappings.php"
    Given I am on "/manage/configuration.php"
    Given I am on "/manage/ad_hoc.php"
    Given I am on "/manage/ad_hoc_workflow.php"
    Given I am on "/manage/io_workflows.php"
    Given I am on "/manage/io_xsds.php"
    Given I am on "/manage/run_webstats.php"
    Given I am on "/manage/upgrade.php"

  #Mostly espace specific.
    Given I am on "/manage/xsd_source_edit.php?cat=edit&xsd_id=28"
    Given I am on "/uqabro13"
  #These frames don't use dojo so we turn off waiting checks
    Given I turn off waiting checks
    Given I am on "/manage/top.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/xsd_tree_start.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/xsd_tree_start.php"
    Then I check there are no Javascript errors
    Given I am on "/manage/xsd_tree.php?xdis_id=178"
    Then I check there are no Javascript errors
    Given I am on "/manage/xsd_tree_match.php?xdis_id=178"
    Then I check there are no Javascript errors