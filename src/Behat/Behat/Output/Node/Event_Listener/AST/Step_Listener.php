<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Event_Listener\AST;

use Behat\Behat\Event_Dispatcher\Event\After_Step_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Step_Tested;
use Behat\Behat\Event_Dispatcher\Event\Example_Tested;
use Behat\Behat\Event_Dispatcher\Event\Scenario_Like_Tested;
use Behat\Behat\Event_Dispatcher\Event\Scenario_Tested;
use Behat\Behat\Output\Node\Printer\Setup_Printer;
use Behat\Behat\Output\Node\Printer\Step_Printer;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;

/**
 * Listens to step events and call appropriate printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Step_Listener implements Event_Listener
{
    private ?ScenarioLikeInterface $scenario = null;

    public function __construct(private readonly Step_Printer $step_printer, private readonly ?Setup_Printer $setup_printer = null)
    {
    }

    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->capture_scenario_on_scenario_event($event);
        $this->forget_scenario_on_after_event($event_name);
        $this->print_step_setup_on_before_event($formatter, $event);
        $this->print_step_on_after_event($formatter, $event);
    }

    /**
     * Captures scenario into the ivar on scenario/background/example BEFORE event.
     */
    private function capture_scenario_on_scenario_event(Event $event): void
    {
        if (!$event instanceof Scenario_Like_Tested) {
            return;
        }
        $this->scenario = $event->get_scenario();
    }

    /**
     * Removes scenario from the ivar on scenario/background/example AFTER event.
     */
    private function forget_scenario_on_after_event($event_name): void
    {
        if (!in_array($event_name, [Scenario_Tested::AFTER, Example_Tested::AFTER])) {
            return;
        }
        $this->scenario = null;
    }

    private function print_step_setup_on_before_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof After_Step_Setup) {
            return;
        }
        if ($this->setup_printer instanceof Setup_Printer) {
            $this->setup_printer->print_setup($formatter, $event->get_setup());
        }
    }

    /**
     * Prints step on AFTER event.
     */
    private function print_step_on_after_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof After_Step_Tested) {
            return;
        }
        $this->step_printer->print_step($formatter, $this->scenario, $event->get_step(), $event->get_test_result());
        if ($this->setup_printer instanceof Setup_Printer) {
            $this->setup_printer->print_teardown($formatter, $event->get_teardown());
        }
    }
}
