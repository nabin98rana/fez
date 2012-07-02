<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\StepEvent;

use Behat\MinkExtension\Context\MinkContext;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

require_once 'login_helper.php';



/**
 * Features context.
 */
class FeatureContext extends MinkContext
{

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
   * If this current step is a modal step
   *
   * @var string
   */
  private $isModal;



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
      $this->screenshotDir = isset($parameters["debug"]['screenshot_dir']) ? $parameters["debug"]['screenshot_dir'] : $behatchDir;
      $this->screenId = isset($parameters["debug"]['screen_id']) ? $parameters["debug"]['screen_id'] : ":0";
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
            throw new exception($field." not found");
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
        $lh->iLoginAsAdmin($this);

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
//    if (!$this->getSession()->getDriver()->wdSession->getAlert()) {
      if (!$this->isModal) {
//        echo "apparently i am NOT modal";
//      $stepTitle = $event->getStep()->getTitle()
//      if ($event->getStep()->getTitle()
        $this->getSession()->wait(10000, "dojo.byId('powered-by')");
      }
//      $this->isModal = false;
//    }
//    $this->getSession()->wait(10000, "$('search_entry').length > 0");
  }


  /**
   * Save a screenshot when failing
   * This uses Xvfb
   *
   * @AfterStep
   */
  public function failScreenshots(StepEvent $event)
  {
    if (!($this->getSession()->getDriver() instanceof Behat\Mink\Driver\GoutteDriver) &&
      !($this->getSession()->getDriver() instanceof Behat\Mink\Driver\ZombieDriver)) {
      if($event->getResult() == StepEvent::FAILED)
      {
        $scenarioName = str_replace(" ", "_", $event->getStep()->getParent()->getTitle());
        $this->saveScreenshot(sprintf("fail_%s_%s.png", time(), $scenarioName));
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
   * @when /^(?:|I )confirm the popup$/
   */
  public function confirmPopup()
  {
    $this->getSession()->getDriver()->wdSession->accept_alert();
  }

  /**
   * @when /^(?:|I )cancel the popup$/
   */
  public function cancelPopup()
  {
    $this->getSession()->getDriver()->wdSession->dismiss_alert();
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
    return $message == $this->getSession()->getDriver()->wdSession->getAlert_text();

  }

  /**
   * @When /^(?:|I )fill "([^"]*)" in popup$/
   *
   * @param string $test
   */
  public function setPopupText($test)
  {
    $this->getSession()->getDriver()->wdSession->postAlert_text($test);
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

}
