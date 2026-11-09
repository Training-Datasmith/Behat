<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Event_Dispatcher\Tester;

use Behat\Testwork\Event_Dispatcher\Event\After_Exercise_Completed;
use Behat\Testwork\Event_Dispatcher\Event\After_Exercise_Setup;
use Behat\Testwork\Event_Dispatcher\Event\Before_Exercise_Completed;
use Behat\Testwork\Event_Dispatcher\Event\Before_Exercise_Teardown;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\Test_Result;
use Symfony\Component\Event_Dispatcher\Event_Dispatcher_Interface;
/**
 * Exercise dispatching BEFORE/AFTER events during its execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template TSpec
 *
 * @implements Exercise<TSpec>
 */
final readonly class Event_Dispatching_Exercise implements Exercise
{
    /**
     * Initializes exercise.
     *
     * @param Exercise<TSpec>          $baseExercise
     */
    public function __construct(private Exercise $base_exercise, private Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    public function set_up(array $iterators, $skip)
    {
        $event = new Before_Exercise_Completed($iterators);
        $this->event_dispatcher->dispatch($event, $event::BEFORE);
        $setup = $this->base_exercise->set_up($iterators, $skip);
        $event = new After_Exercise_Setup($iterators, $setup);
        $this->event_dispatcher->dispatch($event, $event::AFTER_SETUP);
        return $setup;
    }
    public function test(array $iterators, $skip = false)
    {
        return $this->base_exercise->test($iterators, $skip);
    }
    public function tear_down(array $iterators, $skip, Test_Result $result)
    {
        $event = new Before_Exercise_Teardown($iterators, $result);
        $this->event_dispatcher->dispatch($event, $event::BEFORE_TEARDOWN);
        $teardown = $this->base_exercise->tear_down($iterators, $skip, $result);
        $event = new After_Exercise_Completed($iterators, $result, $teardown);
        $this->event_dispatcher->dispatch($event, $event::AFTER);
        return $teardown;
    }
}