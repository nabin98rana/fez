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

require_once ('../../public/config.inc.php');
require_once 'LoginHelper.php';
//require_once 'LinksAMRHelper.php';


require_once(APP_INC_PATH . 'class.auth.php');
require_once(APP_INC_PATH . 'class.fulltext_queue.php');
require_once(APP_INC_PATH . 'class.background_process_list.php');
require_once(APP_INC_PATH . 'class.wok_queue.php');


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
define("TEST_JOURNAL_ARTICLE_PID", "UQ:10400");

/**
 * @var string An example collection pid in the system you can perform non-destructive tests on
 */
define("TEST_COLLECTION_PID", "UQ:9761");

/**
 * @var string An example org unit name so you can test on it
 */
define("TEST_ORG_UNIT_NAME", "Mathematics");

/**
 * @var string An example person in the above TEST_ORG_UNIT_NAME so you can test on it
 */
define("TEST_ORG_UNIT_NAME_USERNAME", "maebilli");


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
   * Background screencast service to record tests
   *
   * @var string
   */
  private $screencast;

  /**
   * Whether to record failed scenarios
   * @see behat.yml
   * @var boolean
   */
  private $zoetropeEnabled;

  /**
   * Screenshot directory
   *
   * @var string
   */
  private $screenshotDir;

  /**
   * Id of Xvfb screen (ex : ":99")
   *
   * @var string
   */
  private $screenId;

  /**
   * @var array   List of screen types and their dimensions
   */
  protected $_screens = array(
    'desktop' => array('width' => 1024, 'height' => 768),
    'tablet'  => array('width' => 768,  'height' => 1024),
    'mobile'  => array('width' => 320,  'height' => 480)
  );

  /**
   * If this current step is a modal step
   *
   * @var string
   */
  private $isModal;


  /**
   * @var string  The Current Subcontext we are working with
   */
  protected $_curSubcontext = null;

  //Used to temporarily hold record data between steps
  private $_tempRecordStore;

  /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
      $this->isModal = false;
      $behatchDir = str_replace("/features/bootstrap/notifiers", "",__DIR__);
      // This is relative to the doclinks URL in Jenkins
      $this->screenshotWebDir = '../../ws/build/screenshots/';
      $this->screenshotDir = isset($parameters["debug"]['screenshot_dir']) ? $parameters["debug"]['screenshot_dir'] : $behatchDir;
      $this->screenId = isset($parameters["debug"]['screen_id']) ? $parameters["debug"]['screen_id'] : ":0";
      $this->zoetropeEnabled = (isset($parameters["debug"]['zoetrope']) && $parameters["debug"]['zoetrope'] == 1) ? true : false;
    }

//
// Place your definition and hook methods here:
//
//    /**
//     * @Given /^I have done something with "([^"]*)"$/
//     */
//    public function iHaveDoneSomethingWith($argument)
//    {
//        doSomethingWith($argument);
//    }
//

    /**
     * @Given /^I click "([^"]*)"$/
     */
    public function iClick($field) {
        $element = $this->getSession()->getPage()->findField($field);
        if (null === $element) {
            throw new exception($field." not found in fields or links");
        }
        $element->click();
    }

//$hooks->afterStep('', function($event) {
//  $environment = $event->getEnvironment();
//  if ($environment->getParameter('browser') == 'phantomjs' && $event->getResult() == StepEvent::FAILED) {
//    $environment->getClient()->findById('BEHAT_STATE')->setValue('failshot.png');
//  }
//});


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
    if (APP_SOLR_INDEXER == "ON") {
      for ($x = 0; $x<30; $x++) {
        $finished = FulltextQueue::isFinishedProcessing();
        if ($finished == true) {
          return;
        }
        sleep(1);
      }
      return;
    }
  }

  /**
   * Wait until the solr queue is empty and the solr processing has finished
   *
   * @BeforeStep
   *
   */
  public function waitForSolrBefore()
  {
    if (APP_SOLR_INDEXER == "ON") {
      for ($x = 0; $x<30; $x++) {
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
   * Wait until the solr queue is empty and the solr processing has finished
   *
   * @Then /^(?:|I )wait for solr$/
   *
   */
  public function waitForSolr()
  {
    if (APP_SOLR_INDEXER == "ON") {
      for ($x = 0; $x<30; $x++) {
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
    for ($x = 0; $x<60; $x++) {
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
    for ($x = 0; $x<60; $x++) {
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
   * @BeforeStep
   *
   */
  public function waitForBGPsBefore()
  {
    for ($x = 0; $x<60; $x++) {
      $finished = BackgroundProcessList::isFinishedProcessing();
      if ($finished == true) {
        return;
      }
      sleep(1);
    }
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
    return;
  }

  /**
   * Enable waiting checks while doing steps not involving modals
   *
   * @Then /^(?:|I )turn on waiting checks$/
   */
  public function turnOnWaitingChecks()
  {
    $this->isModal = false;
    return;
  }


  /**
   * Pauses the scenario until the user presses a key. Useful when debugging a scenario.
   *
   * @Then /^(?:|I )put a breakpoint$/
   */
  public function iPutABreakpoint()
  {
    fwrite(STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
    while (fgets(STDIN, 1024) == '') {}
    fwrite(STDOUT, "\033[u");

    return;
  }

  /**
   * Saving a screenshot
   *
   * @When /^I save a screenshot in "([^"]*)"$/
   */
  public function iSaveAScreenshotIn($imageFilename)
  {
    sleep(1);
    $this->saveScreenshot($imageFilename);
  }

  /**
   * Checks that an element that should be on every page exists and waits for it, or 10 seconds before proceeding
   *
   * @AfterStep
   */
  public function waitForSearchEntryBoxToAppear(StepEvent $event)
  {
    // Check this isn't a modal popup
//    $popupText = $this->assertPopupMessage('');
//    if (!$this->getSession()->getDriver()->getWebDriverSession()->getAlert()) {
    if (!($this->getSession()->getDriver() instanceof Behat\Mink\Driver\GoutteDriver) &&
      !($this->getSession()->getDriver() instanceof Behat\Mink\Driver\ZombieDriver)) {

      if (!$this->isModal) {
//        echo "apparently i am NOT modal";
//      $stepTitle = $event->getStep()->getTitle()
//      if ($event->getStep()->getTitle()
        $this->getSession()->wait(60000, "dojo.byId('powered-by')");
        $javascriptError = ($this->getSession()->evaluateScript("return window.jsErrors"));
        if (!empty($javascriptError)) {
          throw new Exception("Javascript Error: ".$javascriptError[0]);
        }
          $pageContent = $this->getSession()->getPage()->getContent();
          $pos = strpos($pageContent, '&lt;/') || strpos($pageContent, '/&gt;');
          if ($pos!==false) {
              echo("&lt; found in Senario: " );
              print_r($event->getStep()->getParent()->getTitle()."\n");
              print_r($event->getStep()->getType().' '.$event->getStep()->getText()."\n");
          };
      }
    }
//      $this->isModal = false;
//    }
//    $this->getSession()->wait(10000, "$('search_entry').length > 0");
  }


  /**
   * @AfterStep
   *
   * Save a screenshot when failing
   * This uses Xvfb
   *
   * @param Behat\Behat\Event\StepEvent $event
   */
  public function failScreenshots(StepEvent $event)
  {
    if (!($this->getSession()->getDriver() instanceof Behat\Mink\Driver\GoutteDriver) &&
      !($this->getSession()->getDriver() instanceof Behat\Mink\Driver\ZombieDriver)) {
      if($event->getResult() == StepEvent::FAILED)
      {
        $sn = $event->getStep()->getParent()->getTitle();
        $sn = Foxml::makeNCName($sn);
        $scenarioName = preg_replace('/\s+/', '_', str_replace(" ", "_", $sn));
        $scenarioName = (strlen($scenarioName) < 200) ? $scenarioName : substr($scenarioName, 0, 200);
        $imageName = sprintf("fail_%s_%s.png", time(), $scenarioName);
        $this->saveScreenshot($imageName);
        if ($this->screencast) {
          $this->screencast->addPosterImage($imageName);
        }
      }
    }
  }


  /**
   * Saving the screenshot
   *
   * @param string $filename
   * @throws Exception
   */
  public function saveScreenshot($filename)
  {
    if($filename == '')
    {
      throw new \Exception("You must provide a filename for the screenshot.");
    }

    if(!is_dir($this->screenshotDir))
    {
      throw new \Exception(sprintf("The directory %s does not exist.", $this->screenshotDir));
    }

    if(!is_writable($this->screenshotDir))
    {
      throw new \Exception(sprintf("The directory %s is not writable.", $this->screenshotDir));
    }

    if($this->screenId == null)
    {
      throw new \Exception("You must provide a screen ID in behat.yml.");
    }

    //is this display available ?
    exec(sprintf("xdpyinfo -display %s >/dev/null 2>&1 && echo OK || echo KO", $this->screenId), $output);
    if(sizeof($output) == 1 && $output[0] == "OK")
    {
      //screen capture
      echo "Saving failed test screenshot out to ".$filename."\n";
      exec(sprintf("DISPLAY=%s import -window root %s/%s", $this->screenId, rtrim($this->screenshotDir, '/'), $filename), $output, $return);
      if(sizeof($output) != 1 || $output[0] !== "OK")
      {
        throw new \Exception(sprintf("Screenshot was not saved :\n%s", implode("\n", $output)));
      }
    }
    else
    {
      throw new \Exception(sprintf("Screen %s is not available.", $this->screenId));
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
  * @BeforeScenario
  *
  * @param Behat\Behat\Event\ScenarioEvent $event
  *
  * This method does the following:
  * - Assign subcontext for current tested feature, if any
  *
  * Goutte driver is used at this stage.
  * $this->getSubcontext($this->_curSubcontext)->getSession()->getDriver();
  * It's the reason we are not setting Mink's cookie here but on Login definition.
  * Please update it if you have better solution.
  */
  public function setupTest($event)
  {
    // Assign subcontext for current tested feature, if any.
    if (empty($this->_curSubcontext) && $this->_curSubcontext !== 'none') {
      if ($this->getSubcontext($GLOBALS['behat_current_feature'])) {
        $this->_curSubcontext = $GLOBALS['behat_current_feature'];
      } else {
        $this->_curSubcontext = 'self';
      }
    }
  }



 /**
  * @BeforeScenario
   *
   * @param Behat\Behat\Event\ScenarioEvent $event
   */
  public function startScreencast($event)
  {
    if ($event instanceof \Behat\Behat\Event\OutlineExampleEvent) {
      $scenarioTitle = $event->getName();
    } else {
      $scenarioTitle = $event->getScenario()->getTitle();
    }

    $testId = time();

    // Check whether we're using a headless driver - disable screencasts if true
    $this->screencast = false;
    if (!($this->getSession()->getDriver() instanceof Behat\Mink\Driver\GoutteDriver) &&
      !($this->getSession()->getDriver() instanceof Behat\Mink\Driver\ZombieDriver) &&
      $this->zoetropeEnabled) {
      $this->screencast = new ZoetropeBackgroundService(
        $this->screenId, $testId, $scenarioTitle, $this->screenshotDir, $this->screenshotWebDir
      );
      $this->screencast->addFeatureName($this->_curSubcontext);
    }
  }

  /**
   * @AfterScenario
   *
   * @param Behat\Behat\Event\ScenarioEvent $event
   */
  public function endScreencast($event)
  {
    if (!($this->getSession()->getDriver() instanceof Behat\Mink\Driver\GoutteDriver) &&
      !($this->getSession()->getDriver() instanceof Behat\Mink\Driver\ZombieDriver)) {
      // Result returns the resulting (highest) step run code so this checks to see
      // if a failure occurred - if one hasn't delete the screencast else the screencast
      // is kept
      if($event->getResult() < StepEvent::FAILED) {
        if ($this->screencast) {
          $this->screencast->delete();
        }
      }
    }

    // Stop the screencast if one is active.
    if (isset($this->screencast)) {
      unset($this->screencast);
    }
  }

/**
 * @AfterScenario
 *
 * @param Behat\Behat\Event\ScenarioEvent $event
 */
public function afterScenario($event)
{
  $this->getSession()->reset();
  if (!($this->getSession()->getDriver() instanceof Behat\Mink\Driver\GoutteDriver) &&
    !($this->getSession()->getDriver() instanceof Behat\Mink\Driver\ZombieDriver)) {
      $this->getSession()->switchToWindow();
  }
}

  /**
   * @BeforeStep
   *
   * @param Behat\Behat\Event\StepEvent $event
   */
  public function beforeStep($event)
  {
    $stepText = $event->getStep()->getType() . ' ' . $event->getStep()->getText();
    if ($this->screencast) {
      $this->screencast->addCaption($stepText);
    }
  }

  /** @AfterStep
   * *
   * @param Behat\Behat\Event\StepEvent $event
   */
  public function afterStep($event)
  {
    $result = $event->getResult();
    if ($this->screencast) {
      $this->screencast->endCaption($result);
    }
  }

  /**
   * @when /^(?:|I )confirm the popup$/
   */
  public function confirmPopup()
  {
    $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
  }

  /**
   * @when /^(?:|I )cancel the popup$/
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
        if ($data===null) {
            throw new Exception("Response was not JSON" );
        };
    }

    /**
     * @Then /^I should see button "([^"]*)"$/
     */
    public function iShouldSeeButton($buttonName) {
        $fieldElements = $this->getSession()->getPage()->findButton($buttonName, array('field', 'id|name|value|label'));
        if ($fieldElements===null) {
            throw new Exception("Button not found" );
        };
    }

    /**
     * @Then /^I switch to window "([^"]*)"$/
     * null returns to original window
     * Possible works on title, by internal JavaScript "name," or by JavaScript variable. Only tested on "internal JavaScript name"
     */
    public function iSwitchToWindow($name) {
        $this->getSession()->switchToWindow($name);
    }

    /**
     * @Then /^I should see text "([^"]*)" in code$/
     */
    public function iShouldSeeTextInCode($text) {
        $pageContent = $this->getSession()->getPage()->getContent();
        $pos = strpos($pageContent, $text);
        if ($pos===false) {
            throw new Exception("Text not found in code" );
        };
    }

    /**
     * @Then /^I should not see text "([^"]*)" in code$/
     */
    public function iShouldNotSeeTextInCode($text) {
        $pageContent = $this->getSession()->getPage()->getContent();
        $pos = strpos($pageContent, $text);
        if ($pos!==false) {
            throw new Exception("Text found in code" );
        };
    }
    /**
     * @Given /^I go to the test journal article view page$/
     */
    public function iGoToTheTestJournalArticleViewPage()
    {
      $this->visit("/view/".TEST_JOURNAL_ARTICLE_PID);
    }

    /**
     * @Given /^I go to the test collection list page$/
     */
    public function iGoToTheTestCollectionListPage()
    {
      $this->visit("/collection/".TEST_COLLECTION_PID);
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
  public function iShouldSeeTheTestOrgUnitUsernameMessage() {
    $this->assertPageContainsText("Currently acting as: ".TEST_ORG_UNIT_NAME_USERNAME);
  }


  /**
   * @Given /^I choose the "([^"]*)" group for the "([^"]*)" role$/
   */
  public function iChooseTheGroupForTheRole($group, $role)
  {

    if (APP_FEDORA_BYPASS == 'ON') {
      //    And I select "10" from "role"
      $roleId = Auth::getRoleIDByTitle($role);
      $this->selectOption('role', $roleId);
      $this->selectOption('groups_type', 'Fez_Group');
      $this->selectOption('internal_group_list', $group);
      $this->pressButton('Add');
    } else {
      $this->selectOption($role.' Fez Group helper', $group);
      $this->pressButton($role.' Fez Group copy left');
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
        $this->getSession()->wait($wait*1000, "dojo.byId('$see')");
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
            throw new Exception("Javascript Error: ".$javascriptError[0]);
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
            if ( $keys[$title]['value'] !== $this->_tempRecordStore[$title]['value']) {
                if ($title != 'Updated Date' && $title != 'Collection Year') {
                    $errors .= $title.', ';
                }
            }
        }
        if ($errors) {
            throw new Exception("Miss match on sek titles -  ". $errors. " - post update when there shouldn't be on pid: ".$pid[0]);
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

    //Returns a randomish pid. It concentrates on recent pids and grabs them in reverse chronological order
    //It's going further back as the day progresses relative the the test,  to make sure any newly created pids don't overrun the random ones
    private function _returnRandomPid()
    {
        $db = DB_API::get();
        $midnight = strtotime(date("d M Y"));
        $limit = 200 + round((time() - $midnight) / 10);
        $stmt = "SELECT rek_pid FROM (SELECT * FROM " . APP_TABLE_PREFIX . "record_search_key ORDER BY rek_created_date DESC LIMIT ".$limit.") AS T ORDER BY T.rek_created_date, rek_pid LIMIT 1";
        try {
            $res = $db->fetchOne($stmt);
        }
        catch(Exception $ex) {
            throw new Exception("Error with database ".$stmt);
        }
        return $res;
    }

    /**
     * @param string $radioLabel
     *
     * @throws ElementNotFoundException
     * @return void
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

} // FeatureContext

/**
 * Generic class for just-in-time daemonized services.
 */
abstract class BackgroundService {
  protected $pid_file_name;
  protected $output_file_name;

  protected function startProcess($command) {
    // Store the output of the background service and it's pid in temporary
    // files in the system tmp directory.
    $directory = sys_get_temp_dir() . '/background-services';
    mkdir($directory);
    $unique_file = tempnam($directory, 'background-service-');
    $this->pid_file_name =  $unique_file . '.pid';
    $this->output_file_name = $unique_file . '.out';
    unlink($unique_file);
    exec(sprintf("%s > %s 2>&1 & echo $! > %s", $command, $this->output_file_name, $this->pid_file_name));

    $remaining_tries = 60;
    while ($remaining_tries > 0 && !$this->isReady()) {
      sleep(2);
      --$remaining_tries;
    }

    if ($remaining_tries == 0) {
      echo 'Background service failed:' . PHP_EOL;
      echo $this->getOutput();
    }
  }

  protected function isReady() {
    return TRUE;
  }

  public function getOutput() {
    return file_get_contents($this->output_file_name);
  }

  public function getPid() {
    return isset($this->pid_file_name) ? file_get_contents($this->pid_file_name) : NULL;
  }

  public function __destruct() {
    $pid = $this->getPid();
    if (isset($pid)) {
      exec('kill ' . $pid);
      unlink($this->pid_file_name);
    }
    unlink($this->output_file_name);
  }
}

/**
 * Records tests using ffmpeg
 */
class ZoetropeBackgroundService extends BackgroundService
{
  private $videoFiles = array();
  private $subtitleFile = null;
  private $jsonFile = null;
  private $fileDir = null;
  private $webDir = null;
  private $deleteOnExit = false;
  private $captionText = null;
  private $captionStarted = null;
  private $captionResult = null;
  private $captionCount = 0;
  private $timer = null;
  private $zModel = null;

  public function __construct($screenId, $testId, $scenarioText, $dir, $webDir)
  {
    $this->fileDir = $dir;
    $this->webDir = $webDir;
//    $this->videoFiles['webm'] = $testId . '.webm';
    $this->videoFiles['mp4'] = $testId . '.mp4';
//    $this->videoFiles['ogg'] = $testId . '.ogg';
    $this->subtitleFile = $testId . '.srt';
    $this->jsonFile = $testId . '.json';

    // -an     No audio.
    // -y      Overwrite output files.
    // -r      Frame rate
    $command = 'ffmpeg -an -f x11grab -y -r 5 -s 1024x768 -i ' . $screenId . '.0+0,0 '
                    . ' -quality good -cpu-used 0 -b:v 1200k -maxrate 1200k -bufsize 2400k -qmin 10 '
              . '-qmax 42 -vf scale=-1:480 -threads 4 -b:a 128k '
//              . '-codec:v libvpx -quality good -cpu-used 0 -b:v 1200k -maxrate 1200k -bufsize 2400k -qmin 10 '
//              . '-qmax 42 -vf scale=-1:480 -threads 4 -codec:a libvorbis -b:a 128k '
//              . $this->fileDir . $this->videoFiles['webm'] . ' '
//              . '-codec:v libtheora -sameq ' . $this->fileDir . $this->videoFiles['ogg'] . ' '
              . '-codec:v libx264 -sameq ' . $this->fileDir . $this->videoFiles['mp4'];

    $this->startProcess($command);
    $this->timer = new BehatStopWatch();
    $this->_initSubRipFile();
    $this->_initZoetrope($testId, $scenarioText);
  }

  /**
   * Creates a ZoetropeVideoModel which is stored on exit as a JSON encoded object
   *
   * @param string $testId
   * @param string $scenarioText
   */
  private function _initZoetrope($testId, $scenarioText)
  {
    $this->zModel = new ZoetropeVideoModel();
    $this->zModel->id = $testId;
//    $this->zModel->media['webm'] = $this->webDir . $this->videoFiles['webm'];
    $this->zModel->media['mp4'] = $this->webDir . $this->videoFiles['mp4'];
//    $this->zModel->media['ogg'] = $this->webDir . $this->videoFiles['ogg'];
    $this->zModel->subtitles = $this->webDir . $this->subtitleFile;
    $this->zModel->poster = '';
    $this->zModel->scenarioText = $scenarioText;
  }

  /**
   * Creates a new WebVVT file for adding captions to
   */
  private function _initSubRipFile()
  {
    file_put_contents($this->fileDir . $this->subtitleFile, '');
  }

  /**
   * Adds a caption to the subtitles of the running screencast
   * @param string $text
   * @param int $result
   */
  public function addCaption($text)
  {
    $this->captionCount++;
    $this->captionStarted = $this->timer->elapsedAsSubripTime();
    $this->captionText = $text;
  }

  /**
   * Ends the caption and save the caption to the subtitles file with the timings
   */
  public function endCaption($result)
  {
    $this->captionResult = $result;
    $elapsed = $this->timer->elapsedAsSubripTime();
    $text =  $this->captionCount . "\n";
    $text .= $this->captionStarted . ' --> ' . $elapsed . "\n";
    $text .= $this->captionText . "\n\n";
    file_put_contents($this->fileDir . $this->subtitleFile, $text, FILE_APPEND);

    $vStep = new ZoetropeVideoModelStep();
    $vStep->definition = $this->captionText;
    $vStep->result = $this->captionResult;
    $vStep->from = $this->captionStarted;
    $vStep->to = $elapsed;
    $this->zModel->steps[] = $vStep;
  }

  /**
   * Adds a poster image to the zoetrope model
   * @param string $image
   */
  public function addPosterImage($image)
  {
    $this->zModel->poster = $this->webDir . $image;
  }

  public function addFeatureName($name)
  {
    $this->zModel->feature = $name;
  }

  /**
   * Deletes a screencast when the service has stopped
   */
  public function delete()
  {
    $this->deleteOnExit = true;
  }

  /**
   * Saves the Zoetrope model to a file
   */
  private function _saveZoetropeModelToFile()
  {
    file_put_contents(
      $this->fileDir . $this->jsonFile,
      json_encode($this->zModel)
    );
  }

  /**
   * Kills running processes on exit
   */
  public function __destruct()
  {
    parent::__destruct();
    sleep(3);

    if ($this->deleteOnExit) {
      $remaining_tries = 60;
      while ($remaining_tries > 0
        && !@unlink($this->fileDir . $this->videoFiles['webm'])
          && !@unlink($this->fileDir . $this->videoFiles['mp4'])
            && !@unlink($this->fileDir . $this->videoFiles['ogg'])) {
        sleep(2);
        --$remaining_tries;
      }
    } else {
      $this->_saveZoetropeModelToFile();
    }
  }
}

/**
 * Zoetrope video model which is json encoded and passed to the Zoetope app
 */
class ZoetropeVideoModel
{
  public $id = null;
  public $media = array();
  public $subtitles = null;
  public $poster = null;
  public $scenarioText = null;
  public $steps = array();
}

/**
 * Used in the above object which contains multiple steps
 */
class ZoetropeVideoModelStep
{
  public $definition = null;
  public $result = null;
  public $from = null;
  public $to = null;
}

/**
 * Timing for generating subtitles
 */
class BehatStopWatch
{
  public $total;
  public $time;

  public function __construct()
  {
    $this->total = $this->time = microtime(true);
  }

  public function elapsed($fmt = false)
  {
    $elapsed = microtime(true) - $this->total;
    return sprintf('%0.5f', (string)round($elapsed,5));
  }

  public function reset()
  {
    $this->total=$this->time=microtime(true);
  }

  public function elapsedAsSubripTime()
  {
    $elapsed = microtime(true) - $this->total;

    $hours = (int)($elapsed / 3600);
    $mins = (int)(($elapsed - ($hours * 3600)) / 60);
    $secs = (int)$elapsed % 60;
    $ms = (int)round(($elapsed - (int)$elapsed) * 1000);

    return sprintf('%02d:%02d:%02d,%03d', $hours, $mins, $secs, $ms);
  }
}

