<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\ScenarioNode;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Event\BackgroundEvent,
    Behat\Behat\Event\StepEvent;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Background tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundTester implements NodeVisitorInterface
{
    private $logicalParent;
    private $container;
    private $dispatcher;
    private $context;
    private $skip = false;
    /**
     * Allow step instability instead of immediate fail.
     *
     * @var     boolean
     */
    private $allowInstability = false;

    /**
     * Initializes tester.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
        $this->dispatcher = $container->get('behat.event_dispatcher');
    }
    /**
     * Set whether the step will allow instability instead of
     * immediate failure.
     *
     * @param   boolean $allowInstability
     */
    public function setAllowInstability($allowInstability)
    {
        $this->allowInstability = $allowInstability;
    }

    /**
     * Sets logical parent of the step, which is always a ScenarioNode.
     *
     * @param ScenarioNode $parent
     */
    public function setLogicalParent(ScenarioNode $parent)
    {
        $this->logicalParent = $parent;
    }

    /**
     * Sets run context.
     *
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface $context)
    {
        $this->context = $context;
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
     * Visits & tests BackgroundNode.
     *
     * @param AbstractNode $background
     *
     * @return integer
     */
    public function visit(AbstractNode $background)
    {
        $this->dispatcher->dispatch('beforeBackground', new BackgroundEvent($background));

        $result = 0;
        $skip   = false;

        // Visit & test steps
        foreach ($background->getSteps() as $step) {
            $tester = $this->container->get('behat.tester.step');
            $tester->setLogicalParent($this->logicalParent);
            $tester->setContext($this->context);
            $tester->skip($skip || $this->skip);
            $tester->setAllowInstability($this->allowInstability);

            $stResult = $step->accept($tester);

            //if (0 !== $stResult) {
            if (StepEvent::UNSTABLE < $stResult) {
                $skip = true;
            }
            $result = max($result, $stResult);
        }

        $this->dispatcher->dispatch('afterBackground', new BackgroundEvent($background, $result, $skip));

        return $result;
    }
}
