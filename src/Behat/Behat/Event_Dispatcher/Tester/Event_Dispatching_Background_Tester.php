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

use Behat\Behat\Event_Dispatcher\Event\After_Background_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Background_Tested;
use Behat\Behat\Event_Dispatcher\Event\Background_Tested;
use Behat\Behat\Event_Dispatcher\Event\Before_Background_Teardown;
use Behat\Behat\Event_Dispatcher\Event\Before_Background_Tested;
use Behat\Behat\Tester\Background_Tester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\Test_Result;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Background tester dispatching BEFORE/AFTER events.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Event_Dispatching_Background_Tester implements Background_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Background_Tester $base_tester, private EventDispatcherInterface $event_dispatcher)
    {
    }

    public function set_up(Environment $env, FeatureNode $feature, $skip)
    {
        $event = new Before_Background_Tested($env, $feature, $feature->getBackground());
        $this->event_dispatcher->dispatch($event, $event::BEFORE);
        $setup = $this->base_tester->set_up($env, $feature, $skip);
        $event = new After_Background_Setup($env, $feature, $feature->getBackground(), $setup);
        $this->event_dispatcher->dispatch($event, $event::AFTER_SETUP);

        return $setup;
    }

    public function test(Environment $env, FeatureNode $feature, $skip)
    {
        return $this->base_tester->test($env, $feature, $skip);
    }

    public function tear_down(Environment $env, FeatureNode $feature, $skip, Test_Result $result)
    {
        $event = new Before_Background_Teardown($env, $feature, $feature->getBackground(), $result);
        $this->event_dispatcher->dispatch($event, Background_Tested::BEFORE_TEARDOWN);
        $teardown = $this->base_tester->tear_down($env, $feature, $skip, $result);
        $event = new After_Background_Tested($env, $feature, $feature->getBackground(), $result, $teardown);
        $this->event_dispatcher->dispatch($event, Background_Tested::AFTER);

        return $teardown;
    }
}
