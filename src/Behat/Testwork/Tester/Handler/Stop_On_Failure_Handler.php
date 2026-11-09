<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Handler;

use Behat\Behat\Event_Dispatcher\Event\After_Scenario_Tested;
use Behat\Behat\Event_Dispatcher\Event\Example_Tested;
use Behat\Behat\Event_Dispatcher\Event\Scenario_Tested;
use Behat\Testwork\Event_Dispatcher\Event\After_Exercise_Aborted;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Aborted;
use Behat\Testwork\Event_Dispatcher\Event\Exercise_Completed;
use Behat\Testwork\Event_Dispatcher\Event\Suite_Tested;
use Behat\Testwork\Tester\Result\Result_Interpreter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Enables stop on failure via config.
 */
final readonly class Stop_On_Failure_Handler
{
    public function __construct(private EventDispatcherInterface $event_dispatcher, private Result_Interpreter $result_interpreter)
    {
    }

    public function register_listeners(): void
    {
        $this->event_dispatcher->addListener(Scenario_Tested::AFTER, $this->exit_on_failure(...), -100);
        $this->event_dispatcher->addListener(Example_Tested::AFTER, $this->exit_on_failure(...), -100);
    }

    /**
     * Exits if scenario is a failure and if stopper is enabled.
     */
    public function exit_on_failure(After_Scenario_Tested $event): void
    {
        if (Result_Interpreter::PASS === $this->result_interpreter->interpret_result($event->get_test_result())) {
            return;
        }
        $this->event_dispatcher->dispatch(new After_Suite_Aborted($event->get_environment()), Suite_Tested::AFTER);
        $this->event_dispatcher->dispatch(new After_Exercise_Aborted(), Exercise_Completed::AFTER);
        exit(1);
    }
}
