<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\OutlineNode;

use Behat\Behat\Exception\BehaviorException,
    Behat\Behat\Event\FeatureEvent,
    Behat\Behat\Event\StepEvent;;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Feature tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureTester implements NodeVisitorInterface
{
    private $container;
    private $dispatcher;
    private $parameters;
    private $skip = false;
    /**
     * Count of retry attempts for the tester.
     *
     * @var     integer
     */
    private $allowedRetryAttempts = 0;
    /**
     * Current retry attempt count.
     *
     * @var     integer
     */
    private $retryAttempt = 0;

    /**
     * Initializes tester.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
        $this->dispatcher = $container->get('behat.event_dispatcher');
        $this->parameters = $container->get('behat.context.dispatcher')->getContextParameters();
    }

  /**
   * Set count of retry attempts for the tester.
   *
   * @param   integer $count
   */
    public function setAllowedRetryAttempts($count)
    {
        $this->allowedRetryAttempts = $count;
    }

    /**
     * Check wheter there are retry attempts left.
     *
     * @return  Boolean
     */
    public function isAttemptsLeft()
    {
          return $this->retryAttempt < $this->allowedRetryAttempts;
    }

    /**
     * Sets tester to dry-run mode.
     *
     * @param Boolean $skip
     */
    public function setSkip($skip = true)
    {
        $this->skip = (bool) $skip;
    }

    /**
     * Visits & tests FeatureNode.
     *
     * @param AbstractNode $feature
     *
     * @return integer
     *
     * @throws BehaviorException if unknown scenario type (neither Outline or Scenario) found in feature
     */
    public function visit(AbstractNode $feature)
    {
        $this->dispatcher->dispatch(
            'beforeFeature', new FeatureEvent($feature, $this->parameters)
        );

        $result = 0;
        $this->retryAttempt = 0;
        $skip   = false;

        // Visit & test scenarios
        //foreach ($feature->getScenarios() as $scenario) {
        $scenarioIterator = new \ArrayIterator($feature->getScenarios());
        while ($scenarioIterator->valid()) {
            $scenario = $scenarioIterator->current();
            if ($scenario instanceof OutlineNode) {
                $tester = $this->container->get('behat.tester.outline');
            } elseif ($scenario instanceof ScenarioNode) {
                $tester = $this->container->get('behat.tester.scenario');
            } else {
                throw new BehaviorException(
                    'Unknown scenario type found: ' . get_class($scenario)
                );
            }

            $tester->setSkip($skip || $this->skip);
            //$result = max($result, $scenario->accept($tester));
            $tester->setAllowInstability($this->isAttemptsLeft());
            $scResult = $scenario->accept($tester);
            $result = max($result, $scResult);

            if (StepEvent::UNSTABLE !== $scResult) {
                $scenarioIterator->next();
            }
            $this->retryAttempt++;
        }

        $this->dispatcher->dispatch(
            'afterFeature', new FeatureEvent($feature, $this->parameters, $result)
        );

        return $result;
    }
}
