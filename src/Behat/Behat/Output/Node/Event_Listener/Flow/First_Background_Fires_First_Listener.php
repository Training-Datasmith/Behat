<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Event_Listener\Flow;

use Behat\Behat\Event_Dispatcher\Event\Background_Tested;
use Behat\Behat\Event_Dispatcher\Event\Example_Tested;
use Behat\Behat\Event_Dispatcher\Event\Feature_Tested;
use Behat\Behat\Event_Dispatcher\Event\Outline_Tested;
use Behat\Behat\Event_Dispatcher\Event\Scenario_Tested;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;

/**
 * Behat first background fires first listener.
 *
 * This listener catches first scenario and background events in the feature and makes sure
 * that background event are always fired before scenario events, thus following Gherkin format.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class First_Background_Fires_First_Listener implements Event_Listener
{
    private bool $first_background_ended = false;
    /**
     * @var array<array{0: Event, 1: string}>
     */
    private array $delayed_until_background_end = [];

    /**
     * Initializes listener.
     */
    public function __construct(private readonly Event_Listener $descendant)
    {
    }

    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->flush_states_if_beginning_of_the_feature($event_name);
        $this->mark_first_background_printed_after_background($event_name);
        if ($this->is_event_delayed_until_first_background_printed($event)) {
            $this->delayed_until_background_end[] = [$event, $event_name];

            return;
        }
        $this->descendant->listen_event($formatter, $event, $event_name);
        $this->fire_delayed_events_on_after_background($formatter, $event_name);
    }

    /**
     * Flushes state if the event is the BEFORE feature.
     */
    private function flush_states_if_beginning_of_the_feature($event_name): void
    {
        if (Feature_Tested::BEFORE !== $event_name) {
            return;
        }
        $this->first_background_ended = false;
    }

    /**
     * Marks first background printed.
     */
    private function mark_first_background_printed_after_background($event_name): void
    {
        if (Background_Tested::AFTER !== $event_name) {
            return;
        }
        $this->first_background_ended = true;
    }

    /**
     * Checks if provided event should be postponed until background is printed.
     *
     * @return bool
     */
    private function is_event_delayed_until_first_background_printed(Event $event)
    {
        if (!$event instanceof Scenario_Tested && !$event instanceof Outline_Tested && !$event instanceof Example_Tested) {
            return false;
        }

        return !$this->first_background_ended && $event->get_feature()->hasBackground();
    }

    /**
     * Fires delayed events on AFTER background event.
     */
    private function fire_delayed_events_on_after_background(Formatter $formatter, $event_name): void
    {
        if (Background_Tested::AFTER !== $event_name) {
            return;
        }
        foreach ($this->delayed_until_background_end as $event_info) {
            [$event, $event_name] = $event_info;
            $this->descendant->listen_event($formatter, $event, $event_name);
        }
        $this->delayed_until_background_end = [];
    }
}
