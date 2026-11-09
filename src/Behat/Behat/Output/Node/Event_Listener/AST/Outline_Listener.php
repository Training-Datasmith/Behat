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

use Behat\Behat\Event_Dispatcher\Event\After_Outline_Tested;
use Behat\Behat\Event_Dispatcher\Event\After_Scenario_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Scenario_Tested;
use Behat\Behat\Event_Dispatcher\Event\After_Step_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Step_Tested;
use Behat\Behat\Event_Dispatcher\Event\Before_Outline_Tested;
use Behat\Behat\Event_Dispatcher\Event\Example_Tested;
use Behat\Behat\Output\Node\Printer\Example_Printer;
use Behat\Behat\Output\Node\Printer\Outline_Printer;
use Behat\Behat\Output\Node\Printer\Setup_Printer;
use Behat\Behat\Output\Node\Printer\Step_Printer;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;

/**
 * Listens to expanded outline events and calls appropriate printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Outline_Listener implements Event_Listener
{
    private ?ExampleNode $example = null;

    public function __construct(private readonly Outline_Printer $outline_printer, private readonly Example_Printer $example_printer, private readonly Step_Printer $step_printer, private readonly Setup_Printer $example_setup_printer, private readonly Setup_Printer $step_setup_printer)
    {
    }

    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->print_and_capture_outline_header_on_before_event($formatter, $event);
        $this->print_and_forget_outline_footer_on_after_event($formatter, $event);
        $this->print_example_header_on_before_example_event($formatter, $event);
        $this->print_example_footer_on_after_example_event($formatter, $event, $event_name);
        $this->print_step_setup_on_before_step_event($formatter, $event);
        $this->print_step_on_after_step_event($formatter, $event);
    }

    /**
     * Prints outline header and captures outline into ivar on BEFORE event.
     */
    private function print_and_capture_outline_header_on_before_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof Before_Outline_Tested) {
            return;
        }
        $this->outline_printer->print_header($formatter, $event->get_feature(), $event->get_outline());
    }

    /**
     * Prints outline footer and removes outline from ivar on AFTER event.
     */
    private function print_and_forget_outline_footer_on_after_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof After_Outline_Tested) {
            return;
        }
        $this->outline_printer->print_footer($formatter, $event->get_test_result());
    }

    /**
     * Prints example header on example BEFORE event.
     */
    private function print_example_header_on_before_example_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof After_Scenario_Setup) {
            return;
        }
        $this->example_setup_printer->print_setup($formatter, $event->get_setup());
        $scenario = $event->get_scenario();
        assert($scenario instanceof ExampleNode);
        $this->example = $scenario;
        $this->example_printer->print_header($formatter, $event->get_feature(), $this->example);
    }

    /**
     * Prints example footer on example AFTER event.
     */
    private function print_example_footer_on_after_example_event(Formatter $formatter, Event $event, $event_name): void
    {
        if (!$event instanceof After_Scenario_Tested || Example_Tested::AFTER !== $event_name) {
            return;
        }
        $this->example_printer->print_footer($formatter, $event->get_test_result());
        $this->example_setup_printer->print_teardown($formatter, $event->get_teardown());
        $this->example = null;
    }

    /**
     * Prints step setup on step BEFORE event.
     */
    private function print_step_setup_on_before_step_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof After_Step_Setup) {
            return;
        }
        $this->step_setup_printer->print_setup($formatter, $event->get_setup());
    }

    /**
     * Prints example step on step AFTER event.
     */
    private function print_step_on_after_step_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof After_Step_Tested) {
            return;
        }
        $this->step_printer->print_step($formatter, $this->example, $event->get_step(), $event->get_test_result());
        $this->step_setup_printer->print_teardown($formatter, $event->get_teardown());
    }
}
