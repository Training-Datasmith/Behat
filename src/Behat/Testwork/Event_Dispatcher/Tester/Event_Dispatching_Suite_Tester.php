<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Event_Dispatcher\Tester;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Setup;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Tested;
use Behat\Testwork\Event_Dispatcher\Event\Before_Suite_Teardown;
use Behat\Testwork\Event_Dispatcher\Event\Before_Suite_Tested;
use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Suite_Tester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Suite tester dispatching BEFORE/AFTER events during testing.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template TSpec
 *
 * @implements Suite_Tester<TSpec>
 */
final readonly class Event_Dispatching_Suite_Tester implements Suite_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Suite_Tester $base_tester, private EventDispatcherInterface $event_dispatcher)
    {
    }

    public function set_up(Environment $env, Specification_Iterator $iterator, $skip)
    {
        $event = new Before_Suite_Tested($env, $iterator);
        $this->event_dispatcher->dispatch($event, $event::BEFORE);
        $setup = $this->base_tester->set_up($env, $iterator, $skip);
        $event = new After_Suite_Setup($env, $iterator, $setup);
        $this->event_dispatcher->dispatch($event, $event::AFTER_SETUP);

        return $setup;
    }

    public function test(Environment $env, Specification_Iterator $iterator, $skip = false)
    {
        return $this->base_tester->test($env, $iterator, $skip);
    }

    public function tear_down(Environment $env, Specification_Iterator $iterator, $skip, Test_Result $result)
    {
        $event = new Before_Suite_Teardown($env, $iterator, $result);
        $this->event_dispatcher->dispatch($event, $event::BEFORE_TEARDOWN);
        $teardown = $this->base_tester->tear_down($env, $iterator, $skip, $result);
        $event = new After_Suite_Tested($env, $iterator, $result, $teardown);
        $this->event_dispatcher->dispatch($event, $event::AFTER);

        return $teardown;
    }
}
