<?php

use Behat\Behat\Context\ClosuredContextInterface,
  Behat\Behat\Context\TranslatedContextInterface,
  Behat\Behat\Context\BehatContext,
  Behat\Behat\Extension\ExtensionManager,
  Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
  Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\SuiteEvent,
  Behat\Behat\Event\ScenarioEvent,
  Behat\Behat\Event\StepEvent;
use Behat\Behat\Context\Step\Given,
  Behat\Behat\Context\Step\When,
  Behat\Behat\Context\Step\Then;

use Behat\MinkExtension\Context\MinkContext;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

if (file_exists('/var/app/current/public/config.inc.php')) {
  require_once('/var/app/current/public/config.inc.php');
} else {
  require_once('../../public/config.inc.php');
}
require_once 'LoginHelper.php';

require_once(APP_INC_PATH . 'class.auth.php');
require_once(APP_INC_PATH . 'class.fulltext_queue.php');
require_once(APP_INC_PATH . 'class.background_process_list.php');
require_once(APP_INC_PATH . 'class.wok_queue.php');
require_once(APP_INC_PATH . 'class.batchimport.php');
require_once(APP_INC_PATH . 'class.links_amr_queue.php');
require_once(APP_INC_PATH . 'class.eventum.php');
require_once(APP_INC_PATH . 'class.record.php');
require_once(APP_INC_PATH . 'class.foxml.php');

define("TEST_LINKS_AMR_FULL_PID", "UQ:35267");
define("TEST_LINKS_AMR_EMPTY_PID", "UQ:26148");


define("TEST_LINKS_AMR_UT", "000177619700002");

/**
 * @var string An example Journal Article publication pid in the system you can perform non-destructive tests on
 */
define("TEST_JOURNAL_ARTICLE_PID", "UQ:3");

/**
 * @var string An example community pid in the system you can perform non-destructive tests on
 */
define("TEST_COMMUNITY_PID", "UQ:1");

/**
 * @var string An example collection pid in the system you can perform non-destructive tests on
 */
define("TEST_COLLECTION_PID", "UQ:2");

/**
 * @var string An example org unit name so you can test on it
 */
define("TEST_ORG_UNIT_NAME", "Mathematics");

/**
 * @var string An example person in the above TEST_ORG_UNIT_NAME so you can test on it
 */
define("TEST_ORG_UNIT_NAME_USERNAME", "maebilli");

define('BEHAT_ERROR_REPORTING', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING & ~E_USER_WARNING & ~E_USER_NOTICE);


/**
 * Features context.
 */
class FeatureContext extends MinkContext
{

  /**
   * @var string  Screen type used for current test
   */
  public $screen = "desktop";

  /**
   * @var array   List of screen types and their dimensions
   */
  protected $_screens = array(
    'desktop' => array('width' => 1024, 'height' => 768),
    'tablet' => array('width' => 768, 'height' => 1024),
    'mobile' => array('width' => 320, 'height' => 480)
  );

  /**
   * If this current step is a modal step
   *
   * @var string
   */
  private $isModal;

  /**
   * Whether debugging is disabled. When enabled, screenshots of failed scenarios are taken.
   * @var bool
   */
  private $debugDisabled;

  /**
   * Test community/collection/record
   * @var string
   */
  private $testCommunityPid;
  private $testCollectionPid;
  private $testRecordPid;

  /**
   * Variable to temporarily store a record pid
   * @var string
   */
  private $tempRecordPid;

  /**
   * @var string  The Current Subcontext we are working with
   */
  protected $_curSubcontext = null;

  /**
   * Used to temporarily hold record data between steps
   * @var
   */
  private $_tempRecordStore;

  /**
   * Initializes context.
   * Every scenario gets it's own context object.
   *
   * @param   array $parameters context parameters (set them up through behat.yml)
   */
  public function __construct(array $parameters = array())
  {
    // Initialize your context here
    $this->isModal = false;
    // Debug is by default enabled unless explicitly disabled in the config
    $this->debugDisabled = (isset($parameters["debug"]['disabled']) && $parameters["debug"]['disabled'] == 1) ? true : false;
    $this->testCommunityPid = TEST_COMMUNITY_PID;
    $this->testCollectionPid = TEST_COLLECTION_PID;
    $this->testRecordPid = TEST_JOURNAL_ARTICLE_PID;
    Auth::createLoginSession('superadmin_test', 'Test Super Admin', 'c.kortekaas@library.uq.edu.au', '');
    BackgroundProcessList::clearAll();
  }

  /**
   * Checks the DOM is ready.
   * @AfterStep @javascript
   */
  public function afterStepJavascript($event) {
    try {
      $this->getSession()->wait(1000, '(document.readyState === "complete")');
    } catch (\Exception $e) {
    }
  }

  /**
   * @Given /^I click "([^"]*)"$/
   */
  public function iClick($field)
  {
    $element = $this->getSession()->getPage()->findField($field);
    if (null === $element) {
      throw new exception($field . " not found in fields or links");
    }
    $element->click();
  }

  /**
   * Wait a specified number of seconds
   *
   * @Then /^(?:|I )wait for a bit$/
   */
  public function waitForABit()
  {
    sleep(10);

    return;
  }

  /**
   * Wait until the solr queue is empty and the solr processing has finished
   *
   * @AfterStep
   *
   */
  public function waitForSolrAfter()
  {
    /*if (APP_SOLR_INDEXER == "ON" || APP_ES_INDEXER == "ON") {
      for ($x = 0; $x<30; $x++) {
        $finished = FulltextQueue::isFinishedProcessing();
        if ($finished == true) {
          return;
        }
        sleep(1);
      }
      return;
    }*/
  }

  /**
   * Wait until the solr queue is empty and the solr processing has finished
   *
   * @BeforeStep
   *
   */
  public function waitForSolrBefore()
  {
    /*if (APP_SOLR_INDEXER == "ON" || APP_ES_INDEXER == "ON") {
      for ($x = 0; $x<30; $x++) {
        $finished = FulltextQueue::isFinishedProcessing();
        if ($finished == true) {
          return;
        }
        sleep(1);
      }
    }*/
    return;
  }

  /**
   * Wait until the solr queue is empty and the solr processing has finished
   *
   * @Then /^(?:|I )wait for solr$/
   *
   */
  public function waitForSolr()
  {
    if (APP_SOLR_INDEXER == "ON" || APP_ES_INDEXER == "ON") {
      for ($x = 0; $x < 30; $x++) {
        $finished = FulltextQueue::isFinishedProcessing();
        if ($finished == true) {
          return;
        }
        sleep(1);
      }
    }
    return;
  }


  /**
   * Wait until the background processes have finished
   *
   * @Then /^(?:|I )wait for bgps$/
   *
   */
  public function waitForBGPs()
  {
    for ($x = 0; $x < 60; $x++) {
      $finished = BackgroundProcessList::isFinishedProcessing();
      if ($finished == true) {
        return;
      }
      sleep(1);
    }
    return;
  }

  /**
   * Wait until the background processes have finished
   *
   * @AfterStep
   *
   */
  public function waitForBGPsAfter()
  {
    /*for ($x = 0; $x<60; $x++) {
      $finished = BackgroundProcessList::isFinishedProcessing();
      if ($finished == true) {
        return;
      }
      sleep(1);
    }*/
    return;
  }

  /**
   * Wait until the background processes have finished
   *
   * @BeforeStep
   *
   */
  public function waitForBGPsBefore()
  {
    /*for ($x = 0; $x<60; $x++) {
      $finished = BackgroundProcessList::isFinishedProcessing();
      if ($finished == true) {
        return;
      }
      sleep(1);
    }*/
    return;
  }

  /**
   * Wait a specified number of seconds
   *
   * @Then /^(?:|I )wait for "([^"]*)" seconds$/
   */
  public function waitForSeconds($secs)
  {
    sleep($secs);
    return;
  }

  /**
   * @Given /^I login as administrator$/
   */
  public function iLoginAsAdministrator()
  {
    $lh = new loginHelper;
    $lh->iLoginAsAdministrator($this);

  }

  /**
   * @Given /^I login as UPO$/
   */
  public function iLoginAsUPO()
  {
    $lh = new loginHelper;
    $lh->iLoginAsUPO($this);
  }

  /**
   * @Given /^I login as user no groups$/
   */
  public function iLoginAsUserNoGroups()
  {
    $lh = new loginHelper;
    $lh->iLoginAsUserNoGroups($this);

  }

  /**
   * @Given /^I login as thesis officer$/
   */
  public function iLoginAsThesisOfficer()
  {
    $lh = new loginHelper;
    $lh->iLoginAsThesisOfficer($this);

  }

  /**
   * @Given /^I login as super administrator$/
   */
  public function iLoginAsSuperAdministrator()
  {
    $lh = new loginHelper;
    $lh->iLoginAsSuperAdministrator($this);

  }

  /**
   * Disable waiting checks while doing steps involving modals
   *
   * @Then /^(?:|I )turn off waiting checks$/
   */
  public function turnOffWaitingChecks()
  {
    $this->isModal = true;
  }

  /**
   * Enable waiting checks while doing steps not involving modals
   *
   * @Then /^(?:|I )turn on waiting checks$/
   */
  public function turnOnWaitingChecks()
  {
    $this->isModal = false;
  }


  /**
   * Pauses the scenario until the user presses a key. Useful when debugging a scenario.
   *
   * @Then /^(?:|I )put a breakpoint$/
   */
  public function iPutABreakpoint()
  {
    fwrite(STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
    while (fgets(STDIN, 1024) == '') {
    }
    fwrite(STDOUT, "\033[u");

    return;
  }

  /**
   * Checks that an element that should be on every page exists and waits for it, or 10 seconds before proceeding
   *
   * @AfterStep
   */
  public function waitForSearchEntryBoxToAppear($scope)
  {
    // Check this isn't a modal popup
    if (!($this->getSession()->getDriver() instanceof Behat\Mink\Driver\GoutteDriver) &&
      !($this->getSession()->getDriver() instanceof Behat\Mink\Driver\ZombieDriver)
    ) {

      if (!$this->isModal) {
        try {
          $this->getSession()->wait(1000, '(document.readyState === "complete")');
        } catch (Exception $e) {
          if (strpos($e->getMessage(), 'stale') !== false) {
            echo "Found a stale element, retrying wait for search entry box";
            sleep(2);
            $this->waitForSearchEntryBoxToAppear($scope);
          }
        }
        $this->getSession()->wait(5000, 'typeof window.jQuery == "function"');
        $javascriptError = ($this->getSession()->evaluateScript("return window.jsErrors"));
        if (!empty($javascriptError)) {
          throw new Exception("Javascript Error: " . $javascriptError[0]);
        }
      }
    }
  }

  /**
   * @Given /^I press search$/
   */
  public function iPressSearch() {
    try {
      $this->getSession()->wait(1000, '(document.readyState === "complete")');
      $this->getSession()->getPage()->pressButton('search_entry_submit');
    } catch (Exception $e) {
      if (strpos($e->getMessage(), 'stale') !== false) {
        echo "Found a stale element, retrying wait for search entry button";
        sleep(1);
        $this->iPressSearch();
      }
    }
  }

  /**
   * @BeforeFeature
   *
   * @param FeatureEvent $event
   */
  public static function setupFeature($event)
  {
    $feature = $event->getFeature();
    $file = $feature->getFile();
    preg_match('/([\w]+).feature$/', $file, $matches);
    $GLOBALS['behat_current_feature'] = $matches[1];
  }

  /**
   * @AfterScenario
   *
   * @param Behat\Behat\Event\ScenarioEvent $scope
   */
  public function afterScenario($scope)
  {
    $this->getSession()->reset();
  }

  /**
   * @When /^(?:|I )confirm the popup$/
   */
  public function confirmPopup()
  {
    $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
  }

  /**
   * @When /^(?:|I )cancel the popup$/
   */
  public function cancelPopup()
  {
    $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
  }

  /**
   * @When /^(?:|I )should see "([^"]*)" in popup$/
   *
   * @param string $message
   *
   * @return bool
   */
  public function assertPopupMessage($message)
  {
    return $message == $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
  }

  /**
   * @When /^(?:|I )fill "([^"]*)" in popup$/
   *
   * @param string $test
   */
  public function setPopupText($test)
  {
    $this->getSession()->getDriver()->getWebDriverSession()->postAlert_text($test);
  }

  /**
   * Click on the element with the provided xpath query
   *
   * @When /^I click on the element with xpath "([^"]*)"$/
   */
  public function iClickOnTheElementWithXPath($xpath)
  {
    $session = $this->getSession(); // get the mink session
    $element = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
    ); // runs the actual query and returns the element

    // errors must not pass silently
    if (null === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
    }

    // ok, let's click on it
    $element->click();

  }

  /**
   * @When /^I select the first record in the search results with name "([^"]*)"$/
   */
  public function iSelectTheFirstRecordInTheSearchResultsWithName($name)
  {
    $xpath = "//input[@name='{$name}[]']";
    $this->iClickOnTheElementWithXPath($xpath);
  }

  /**
   * Click on the element with the provided CSS Selector
   *
   * @When /^I click on the element with css selector "([^"]*)"$/
   */
  public function iClickOnTheElementWithCSSSelector($cssSelector)
  {
    $session = $this->getSession();
    $element = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('css', $cssSelector) // just changed xpath to css
    );
    if (null === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate CSS Selector: "%s"', $cssSelector));
    }

    $element->click();

  }

  /**
   * @Given /^I carefully fill search entry with "(.*)"$/
   */
  public function iCarefullyFillSearchEntryWith($search)
  {
    try {
      sleep(3);
      $this->fillField("search_entry", $search);
    } catch (Exception $e) {
      if (strpos($e->getMessage(), 'stale') !== false) {
        echo "Found a stale element, retrying filling search_entry";
      }
      sleep(3);
      $this->iCarefullyFillSearchEntryWith($search);
    }
  }

  /**
   * @When /^I go to the "([^"]+)" page$/
   */
  public function iGoToThePage($page)
  {
    $pageObjName = 'Page_' . str_replace(' ', '', $page);
    if (class_exists($pageObjName)) {
      $page = new $pageObjName($this);
    } else {
      throw new exception('Page not found');
    }
  }

  /**
   * @Then /^should see valid JSON$/
   */
  public function shouldSeeValidJSON()
  {
    $json = $this->getSession()->getPage()->getContent();
    $data = json_decode($json);
    if ($data === null) {
      throw new Exception("Response was not JSON");
    }
  }

  /**
   * @Then /^I should see button "([^"]*)"$/
   */
  public function iShouldSeeButton($buttonName)
  {
    $fieldElements = $this->getSession()->getPage()->findButton($buttonName, array('field', 'id|name|value|label'));
    if ($fieldElements === null) {
      throw new Exception("Button not found");
    }
  }

  /**
   * @Then /^I switch to window "([^"]*)"$/
   * null returns to original window
   * Possible works on title, by internal JavaScript "name," or by JavaScript variable. Only tested on "internal JavaScript name"
   */
  public function iSwitchToWindow($name)
  {
    $windows = $this->getSession()->getWindowNames();
    if (empty($name)) {
      $name = $windows[0];
    }
    $this->getSession()->switchToWindow($name);
  }

  /**
   * @Then /^I should see text "([^"]*)" in code$/
   */
  public function iShouldSeeTextInCode($text)
  {
    $pageContent = $this->getSession()->getPage()->getContent();
    $pos = strpos($pageContent, $text);
    if ($pos === false) {
      throw new Exception("Text not found in code");
    };
  }

  /**
   * @Then /^I should not see text "([^"]*)" in code$/
   */
  public function iShouldNotSeeTextInCode($text)
  {
    $pageContent = $this->getSession()->getPage()->getContent();
    $pos = strpos($pageContent, $text);
    if ($pos !== false) {
      throw new Exception("Text found in code");
    };
  }

  /**
   * @Then /^I should see a datastream link for "([^"]*)"$/
   */
  public function iShouldSeeADatastreamLinkFor($value)
  {
    $this->assertSession()->elementTextContains('css', '.ds-link', $this->fixStepArgument($value));
  }

  /**
   * @Then /^I should not see any datastream view links$/
   */
  public function iShouldNotSeeADatastreamLink()
  {
    $this->assertSession()->elementNotExists('css', '.ds-link');
  }

  /**
   * @Given /^I go to the test community page$/
   */
  public function iGoToTheTestCommunityListPage()
  {
    $this->visit("/community/" . $this->testCommunityPid);
  }

  /**
   * @Given /^I go to the test collection list page$/
   */
  public function iGoToTheTestCollectionListPage()
  {
    $this->visit("/collection/" . $this->testCollectionPid);
  }

  /**
   * @Given /^I go to the test journal article view page$/
   */
  public function iGoToTheTestJournalArticleViewPage()
  {
    $this->visit("/view/" . $this->testRecordPid);
  }

  /**
   * @Given /^I store the test community pid for future use$/
   */
  public function iStoreTheTestCommunityPidForFutureUse()
  {
    $this->testCommunityPid = $this->getPidFromUrl();
  }

  /**
   * @Given /^I store the test collection pid for future use$/
   */
  public function iStoreTheTestCollectionPidForFutureUse()
  {
    $this->testCollectionPid = $this->getPidFromUrl();
  }

  /**
   * @Given /^I store the test record pid for future use$/
   */
  public function iStoreTheTestRecordPidForFutureUse()
  {
    $this->testRecordPid = $this->getPidFromUrl();
  }

  private function getPidFromUrl()
  {
    preg_match('/UQ:(\d+)/', $this->getSession()->getCurrentUrl(), $pid);
    return $pid[0];
  }

  /**
   * @Given /^I temporarily store the record pid$/
   */
  public function iTemporarilyStoreTheRecordPid()
  {
    $this->tempRecordPid = $this->getPidFromUrl();
  }

  /**
   * @Given /^I go to the temporary record pid view page$/
   */
  public function iGoToTheTemporaryRecordPidViewPage()
  {
    $this->visit("/view/" . $this->tempRecordPid);
  }

  /**
   * @Given /^I select the test org unit$/
   */
  public function iSelectTheTestOrgUnit()
  {
    $this->selectOption('org_unit_id', TEST_ORG_UNIT_NAME);
  }

  /**
   * @Given /^I select the test org unit username$/
   */
  public function iSelectTheTestOrgUnitUsername()
  {
    $this->iClick(TEST_ORG_UNIT_NAME_USERNAME);
  }

  /**
   * @Then /^I should see the test org unit username message$/
   */
  public function iShouldSeeTheTestOrgUnitUsernameMessage()
  {
    $this->assertPageContainsText("Currently acting as: " . TEST_ORG_UNIT_NAME_USERNAME);
  }

  /**
   * @Given /^I choose the "([^"]*)" group for the "([^"]*)" role$/
   */
  public function iChooseTheGroupForTheRole($group, $role)
  {
    if (APP_FEDORA_BYPASS == 'ON') {
      $this->selectOption('role', $role);
      $this->selectOption('groups_type', 'Fez_Group');
      $this->selectOption('internal_group_list', $group);
      $this->pressButton('Add');
    } else {
      $this->selectOption($role . ' Fez Group helper', $group);
      $this->pressButton($role . ' Fez Group copy left');
    }
  }

  /**
   * @Given /^I add "([^"]*)" to the WOK queue$/
   */
  public function iAddToTheWokQueue($item)
  {
    $wOKQueue = WokQueue::get();
    $wOKQueue->add($item);
    $wOKQueue->commit();
  }

  /**
   * @Given /^I send a empty pid to Links AMR that will get back an existing ISI Loc pid$/
   */
  public function iSendAEmptyPidToLinksAmrThatWillGetBackAnExistingIsiLocPid()
  {
    $queue = new LinksAmrQueue();
    $queue->sendToLinksAmr(array(TEST_LINKS_AMR_EMPTY_PID));
  }

  /**
   * @Then /^the empty Links AMR test pid should not get the ISI Loc$/
   */
  public function theEmptyLinksAmrTestPidShouldNotGetTheIsiLoc()
  {
    $isi_loc = Record::getSearchKeyIndexValue(TEST_LINKS_AMR_EMPTY_PID, "ISI Loc");
    if ($isi_loc != '') {
      throw new Exception("ISI Loc isn't empty for pid");
    }
  }

  /**
   * @Given /^helpdesk system should have an email with the ISI Loc and pid in the subject line$/
   */
  public function helpdeskSystemShouldHaveAnEmailWithTheIsiLocAndPidInTheSubjectLine()
  {
    $issues = Eventum::getLinksIssues(TEST_LINKS_AMR_EMPTY_PID, TEST_LINKS_AMR_UT);
    if (count($issues) == 0) {
      throw new Exception("Can't find the helpdesk issue");
    }
  }

  /**
   * @Given /^I see "([^"]*)" id or wait for "([^"]*)" seconds$/
   */
  public function iSeeIdOrWaitForSeconds($see, $wait)
  {
    try {
      $this->getSession()->wait($wait * 1000, "dojo.byId('$see')");
    } catch (Exception $e) {
      if (strpos($e->getMessage(), 'stale') !== false) {
        echo "Found a stale element, retrying see or wait";
        sleep(2);
        $this->iSeeIdOrWaitForSeconds($see, $wait);
      }
    }
  }

  /**
   * @Given /^I check there are no Javascript errors$/
   *
   * This is currently redundant due to the fact this check is done on all non modal pages
   */
  public function iCheckThereAreNoJavascriptErrors()
  {
    $javascriptError = ($this->getSession()->evaluateScript("return window.jsErrors"));
    if (!empty($javascriptError)) {
      throw new Exception("Javascript Error: " . $javascriptError[0]);
    }
  }

  /**
   * This function saves the records current state temporarily for testing later
   * @Given /^I save record details$/
   */
  public function iSaveRecordDetails()
  {
    preg_match('/UQ:(\d+)/', $this->getSession()->getCurrentUrl(), $pid);
    $data = new Fez_Record_Searchkey($pid[0]);
    $this->_tempRecordStore = $data->getSekData();
    return;
  }

  /**
   * @Given /^I attach a file to the current record$/
   */
  public function iAttachAFileToTheCurrentRecord()
  {
    $pid = $this->getPidFromUrl();
    if ($pid) {
      $fileName = 'test.pdf';
      $file = APP_PATH . '../tests/application/data/' . $fileName;
      $tempFile = APP_TEMP_DIR . $fileName;
      copy($file, $tempFile);
      BatchImport::handleStandardFileImport($pid, $tempFile, $fileName, 0, true);
      unlink($tempFile);
    }
  }

  /**
   * This assumes iSaveRecordDetails has saved the records previous state and now we check it's unchanged
   * @Given /^I check record unchanged$/
   */
  public function iCheckRecordUnchanged()
  {
    preg_match('/UQ:(\d+)/', $this->getSession()->getCurrentUrl(), $pid);
    $data = new Fez_Record_Searchkey($pid[0]);
    $keys = $data->getSekData();
    $errors = '';
    foreach ($keys as $title => $value) {
      if ($keys[$title]['value'] !== $this->_tempRecordStore[$title]['value']) {
        if ($title != 'Updated Date' && $title != 'Collection Year') {
          $errors .= $title . ', ';
        }
      }
    }
    if ($errors) {
      throw new Exception("Miss match on sek titles -  " . $errors . " - post update when there shouldn't be on pid: " . $pid[0]);
    }
  }

  /**
   * @Given /^I go to a random pid$/
   */
  public function iGoToARandomPid()
  {
    $url = '/view/';
    $url .= $this->_returnRandomPid();
    $this->getSession()->visit($this->locatePath($url));
    return;
  }

  /**
   * @Then /^I clean up title "([^"]*)"$/
   */
  public function iCleanUpTitle($title)
  {
    $db = DB_API::get();
    $stmt = "SELECT rek_pid FROM " . APP_TABLE_PREFIX . "record_search_key WHERE rek_title = '" . $title . "' AND rek_status = 2";
    try {
      $res = $db->fetchCol($stmt);
    } catch (Exception $ex) {
      throw new Exception("Error with database " . $stmt);
    }
    foreach ($res as $pid) {
      Record::markAsDeleted($pid);
    }
    return;
  }

  //Returns a randomish pid. It concentrates on recent pids and grabs them in reverse chronological order
  //It's going further back as the day progresses relative the the test,  to make sure any newly created pids don't overrun the random ones
  private function _returnRandomPid()
  {
    $db = DB_API::get();
    $midnight = strtotime(date("d M Y"));
    $limit = 200 + round((time() - $midnight) / 10);
    $stmt = "SELECT rek_pid FROM (SELECT * FROM " . APP_TABLE_PREFIX . "record_search_key ORDER BY rek_created_date DESC LIMIT " . $limit . ") AS T ORDER BY T.rek_created_date, rek_pid LIMIT 1";
    try {
      $res = $db->fetchOne($stmt);
    } catch (Exception $ex) {
      throw new Exception("Error with database " . $stmt);
    }
    return $res;
  }

  /**
   * @param $radioLabel
   * @throws Exception
   *
   * @param string $radioLabel
   * @Given /^I select the "([^"]*)" radio button$/
   */
  public function iSelectTheRadioButton($radioLabel)
  {
    $radioButton = $this->getSession()->getPage()->findField($radioLabel);
    if (null === $radioButton) {
      throw new Exception($this->getSession(), 'form field', 'id|name|label|value', $radioLabel);
    }
    $value = $radioButton->getAttribute('value');
    $this->getSession()->getDriver()->click($radioButton->getXPath());
  }

  /**
   * @param string $javascript
   *
   * @Given /^I run javascript "([^"]*)"$/
   */
  public function iRunJavascript($javascript)
  {
    $this->getSession()->executeScript($javascript);
  }

  /**
   * @Given /^I check the current page is valid XML$/
   */
  public function iCheckTheCurrentPageisValidXML()
  {
    $xml = file_get_contents($this->getSession()->getCurrentUrl());
    $result = simplexml_load_string($xml, 'SimpleXmlElement', LIBXML_DTDVALID + LIBXML_NOWARNING + LIBXML_NOERROR);
    //echo $xml;
    if ($result == false) {
      throw new Exception("XML not valid");
    }
    return;
  }

} // FeatureContext
