<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Event_Listener\JSON;

use Behat\Behat\Output\Node\Event_Listener\Statistics\Duration_Listener;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Event_Dispatcher\Event\After_Exercise_Completed;
use Behat\Testwork\Event_Dispatcher\Event\After_Exercise_Setup;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Tested;
use Behat\Testwork\Event_Dispatcher\Event\Before_Suite_Tested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Suite\Suite;

final class JSON_Duration_Listener extends Duration_Listener
{
    /**
     * @var array<string, Timer>
     */
    private array $suite_timer_store = [];
    private Timer $exercise_timer;
    /**
     * @var array<string, float>
     */
    private array $suite_result_store = [];
    private ?float $exercise_result = null;

    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        parent::listen_event($formatter, $event, $event_name);
        $this->capture_exercise_start_event($event);
        $this->capture_suite_setup_event($event);
        $this->capture_suite_teardown_event($event);
        $this->capture_exercise_end_event($event);
    }

    public function get_suite_duration(Suite $suite): string
    {
        $key = $this->get_hash($suite);

        return array_key_exists($key, $this->suite_result_store) ? number_format($this->suite_result_store[$key], 3, '.', '') : '';
    }

    public function get_exercise_duration(): string
    {
        return $this->exercise_result !== null ? number_format($this->exercise_result, 3, '.', '') : '';
    }

    private function capture_exercise_start_event(Event $event): void
    {
        if ($event instanceof After_Exercise_Setup) {
            $this->exercise_timer = $this->start_timer();
        }
    }

    private function capture_exercise_end_event(Event $event): void
    {
        if ($event instanceof After_Exercise_Completed) {
            $timer = $this->exercise_timer;
            $timer->stop();
            $this->exercise_result = $timer->get_time();
        }
    }

    private function capture_suite_setup_event(Event $event): void
    {
        if ($event instanceof Before_Suite_Tested) {
            $this->suite_timer_store[$this->get_hash($event->get_suite())] = $this->start_timer();
        }
    }

    private function capture_suite_teardown_event(Event $event): void
    {
        if ($event instanceof After_Suite_Tested) {
            $key = $this->get_hash($event->get_suite());
            if (isset($this->suite_timer_store[$key])) {
                $timer = $this->suite_timer_store[$key];
                $timer->stop();
                $this->suite_result_store[$key] = $timer->get_time();
                unset($this->suite_timer_store[$key]);
            }
        }
    }
}
