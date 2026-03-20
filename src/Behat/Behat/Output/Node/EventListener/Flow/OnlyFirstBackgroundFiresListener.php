<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Event_Listener\Flow;

use Behat\Behat\Event_Dispatcher\Event\After_Step_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Step_Tested;
use Behat\Behat\Event_Dispatcher\Event\Background_Tested;
use Behat\Behat\Event_Dispatcher\Event\Feature_Tested;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;
/**
 * Behat only first background fires listener.
 *
 * This listener catches all in-background events and then proxies them further
 * only if they meet one of two conditions:
 *
 *   1. It is a first background
 *   2. It is a failing step
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Only_First_Background_Fires_Listener implements Event_Listener
{
    private bool $first_background_ended = false;
    private bool $in_background = false;
    private bool $step_setup_had_output = false;
    /**
     * Initializes listener.
     */
    public function __construct(private readonly Event_Listener $descendant)
    {
    }
    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->flush_states_if_beginning_of_the_feature($event_name);
        $this->mark_beginning_or_end_of_the_background($event_name);
        if ($this->is_skippable_event($event)) {
            return;
        }
        $this->mark_first_background_printed_after_background($event_name);
        $this->descendant->listen_event($formatter, $event, $event_name);
    }
    /**
     * Flushes state if the event is the BEFORE feature.
     *
     * @param string $eventName
     */
    private function flush_states_if_beginning_of_the_feature($event_name): void
    {
        if (Feature_Tested::BEFORE !== $event_name) {
            return;
        }
        $this->first_background_ended = false;
        $this->in_background = false;
    }
    /**
     * Marks beginning or end of the background.
     *
     * @param string $eventName
     */
    private function mark_beginning_or_end_of_the_background($event_name): void
    {
        if (Background_Tested::BEFORE === $event_name) {
            $this->in_background = true;
        }
        if (Background_Tested::AFTER === $event_name) {
            $this->in_background = false;
        }
    }
    /**
     * Marks first background printed.
     *
     * @param string $eventName
     */
    private function mark_first_background_printed_after_background($event_name): void
    {
        if (Background_Tested::AFTER !== $event_name) {
            return;
        }
        $this->first_background_ended = true;
    }
    /**
     * Checks if provided event is skippable.
     *
     * @return bool
     */
    private function is_skippable_event(Event $event)
    {
        if (!$this->first_background_ended) {
            return false;
        }
        return $event instanceof Background_Tested || $this->is_non_failing_consequent_background_step($event);
    }
    /**
     * Checks if provided event is a non-failing step in consequent background.
     *
     * @return bool
     */
    private function is_non_failing_consequent_background_step(Event $event)
    {
        if (!$this->in_background) {
            return false;
        }
        return !$this->is_step_event_with_output($event);
    }
    /**
     * Checks if provided event is a step event which setup or teardown produced any output.
     */
    private function is_step_event_with_output(Event $event): bool
    {
        if ($this->is_before_step_event_with_output($event)) {
            return true;
        }
        return $this->is_after_step_with_output($event);
    }
    /**
     * Checks if provided event is a BEFORE step with setup that produced output.
     */
    private function is_before_step_event_with_output(Event $event): bool
    {
        if ($event instanceof After_Step_Setup && $event->has_output()) {
            $this->step_setup_had_output = true;
            return true;
        }
        return false;
    }
    /**
     * Checks if provided event is an AFTER step with teardown that produced output.
     */
    private function is_after_step_with_output(Event $event): bool
    {
        if ($event instanceof After_Step_Tested && ($this->step_setup_had_output || $event->has_output())) {
            $this->step_setup_had_output = false;
            return true;
        }
        return false;
    }
}