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

use Behat\Behat\Event_Dispatcher\Event\After_Feature_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Feature_Tested;
use Behat\Behat\Event_Dispatcher\Event\Before_Feature_Teardown;
use Behat\Behat\Event_Dispatcher\Event\Before_Feature_Tested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Specification_Tester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Feature tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements Specification_Tester<FeatureNode>
 */
final readonly class Event_Dispatching_Feature_Tester implements Specification_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Specification_Tester $base_tester, private EventDispatcherInterface $event_dispatcher)
    {
    }

    public function set_up(Environment $env, $spec, $skip)
    {
        $event = new Before_Feature_Tested($env, $spec);
        $this->event_dispatcher->dispatch($event, $event::BEFORE);
        $setup = $this->base_tester->set_up($env, $spec, $skip);
        $event = new After_Feature_Setup($env, $spec, $setup);
        $this->event_dispatcher->dispatch($event, $event::AFTER_SETUP);

        return $setup;
    }

    public function test(Environment $env, $spec, $skip)
    {
        return $this->base_tester->test($env, $spec, $skip);
    }

    public function tear_down(Environment $env, $spec, $skip, Test_Result $result)
    {
        $event = new Before_Feature_Teardown($env, $spec, $result);
        $this->event_dispatcher->dispatch($event, $event::BEFORE_TEARDOWN);
        $teardown = $this->base_tester->tear_down($env, $spec, $skip, $result);
        $event = new After_Feature_Tested($env, $spec, $result, $teardown);
        $this->event_dispatcher->dispatch($event, $event::AFTER);

        return $teardown;
    }
}
