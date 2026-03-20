<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Event_Listener\Statistics;

use Behat\Behat\Output\Node\Printer\Statistics_Printer;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Event_Dispatcher\Event\Exercise_Completed;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;
/**
 * Collects general suite stats such as time and memory during its execution and prints it afterwards.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Statistics_Listener implements Event_Listener
{
    /**
     * Initializes listener.
     */
    public function __construct(private Statistics $statistics, private Statistics_Printer $printer)
    {
    }
    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->start_timer_on_before_exercise($event_name);
        $this->print_statistics_on_after_exercise_event($formatter, $event_name);
    }
    /**
     * Starts timer on exercise BEFORE event.
     *
     * @param string $eventName
     */
    private function start_timer_on_before_exercise($event_name): void
    {
        if (Exercise_Completed::BEFORE !== $event_name) {
            return;
        }
        $this->statistics->start_timer();
    }
    /**
     * Prints statistics on after exercise event.
     *
     * @param string    $eventName
     */
    private function print_statistics_on_after_exercise_event(Formatter $formatter, $event_name): void
    {
        if (Exercise_Completed::AFTER !== $event_name) {
            return;
        }
        $this->statistics->stop_timer();
        $this->printer->print_statistics($formatter, $this->statistics);
    }
}