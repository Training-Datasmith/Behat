<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Event_Dispatcher\Tester;

use Behat\Behat\Event_Dispatcher\Event\After_Step_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Step_Tested;
use Behat\Behat\Event_Dispatcher\Event\Before_Step_Teardown;
use Behat\Behat\Event_Dispatcher\Event\Before_Step_Tested;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Behat\Tester\Step_Tester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Step tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Event_Dispatching_Step_Tester implements Step_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Step_Tester $base_tester, private EventDispatcherInterface $event_dispatcher)
    {
    }

    public function set_up(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        $event = new Before_Step_Tested($env, $feature, $step);
        $this->event_dispatcher->dispatch($event, $event::BEFORE);
        $setup = $this->base_tester->set_up($env, $feature, $step, $skip);
        $event = new After_Step_Setup($env, $feature, $step, $setup);
        $this->event_dispatcher->dispatch($event, $event::AFTER_SETUP);

        return $setup;
    }

    public function test(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        return $this->base_tester->test($env, $feature, $step, $skip);
    }

    public function tear_down(Environment $env, FeatureNode $feature, StepNode $step, $skip, Step_Result $result)
    {
        $event = new Before_Step_Teardown($env, $feature, $step, $result);
        $this->event_dispatcher->dispatch($event, $event::BEFORE_TEARDOWN);
        $teardown = $this->base_tester->tear_down($env, $feature, $step, $skip, $result);
        $event = new After_Step_Tested($env, $feature, $step, $result, $teardown);
        $this->event_dispatcher->dispatch($event, $event::AFTER);

        return $teardown;
    }
}
