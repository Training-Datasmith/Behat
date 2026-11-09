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
use Behat\Behat\Event_Dispatcher\Event\Outline_Tested;
use Behat\Behat\Event_Dispatcher\Event\Step_Tested;
use Behat\Behat\Output\Node\Printer\Example_Row_Printer;
use Behat\Behat\Output\Node\Printer\Outline_Table_Printer;
use Behat\Behat\Output\Node\Printer\Setup_Printer;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Listens to outline table events and calls appropriate printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Outline_Table_Listener implements Event_Listener
{
    private ?OutlineNode $outline = null;
    private ?Setup $example_setup = null;
    private bool $header_printed = false;
    /**
     * @var After_Step_Setup[]
     */
    private array $step_before_tested_events = [];
    /**
     * @var After_Step_Tested[]
     */
    private array $step_after_tested_events = [];

    public function __construct(private readonly Outline_Table_Printer $table_printer, private readonly Example_Row_Printer $example_row_printer, private readonly Setup_Printer $example_setup_printer, private readonly Setup_Printer $step_setup_printer)
    {
    }

    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        if ($event instanceof Step_Tested) {
            $this->capture_step_event($event);

            return;
        }
        $this->capture_outline_on_before_outline_event($event);
        $this->forget_outline_on_after_outline_event($event_name);
        $this->capture_example_setup_on_before_event($event);
        $this->print_header_on_after_example_event($formatter, $event, $event_name);
        $this->print_example_row_on_after_example_event($formatter, $event, $event_name);
        $this->print_footer_on_after_event($formatter, $event);
    }

    /**
     * Captures step tested event.
     */
    private function capture_step_event(Step_Tested $event): void
    {
        if ($event instanceof After_Step_Setup) {
            $this->step_before_tested_events[$event->get_step()->getLine()] = $event;
        } elseif ($event instanceof After_Step_Tested) {
            $this->step_after_tested_events[$event->get_step()->getLine()] = $event;
        }
    }

    /**
     * Captures outline into the ivar on outline BEFORE event.
     */
    private function capture_outline_on_before_outline_event(Event $event): void
    {
        if (!$event instanceof Before_Outline_Tested) {
            return;
        }
        $this->outline = $event->get_outline();
        $this->header_printed = false;
    }

    /**
     * Captures example setup on example BEFORE event.
     */
    private function capture_example_setup_on_before_event(Event $event): void
    {
        if (!$event instanceof After_Scenario_Setup) {
            return;
        }
        $this->example_setup = $event->get_setup();
    }

    /**
     * Removes outline from the ivar on outline AFTER event.
     */
    private function forget_outline_on_after_outline_event($event_name): void
    {
        if (Outline_Tested::AFTER !== $event_name) {
            return;
        }
        $this->outline = null;
    }

    /**
     * Prints outline header (if has not been printed yet) on example AFTER event.
     */
    private function print_header_on_after_example_event(Formatter $formatter, Event $event, $event_name): void
    {
        if (!$event instanceof After_Scenario_Tested || Example_Tested::AFTER !== $event_name) {
            return;
        }
        if ($this->header_printed) {
            return;
        }
        $feature = $event->get_feature();
        $step_test_results = $this->get_step_test_results();
        $this->table_printer->print_header($formatter, $feature, $this->outline, $step_test_results);
        $this->header_printed = true;
    }

    /**
     * Prints example row on example AFTER event.
     */
    private function print_example_row_on_after_example_event(Formatter $formatter, Event $event, $event_name): void
    {
        if (!$event instanceof After_Scenario_Tested || Example_Tested::AFTER !== $event_name) {
            return;
        }
        $example = $event->get_scenario();
        $this->example_setup_printer->print_setup($formatter, $this->example_setup);
        foreach ($this->step_before_tested_events as $before_event) {
            $this->step_setup_printer->print_setup($formatter, $before_event->get_setup());
        }
        assert($example instanceof ExampleNode);
        $this->example_row_printer->print_example_row($formatter, $this->outline, $example, $this->step_after_tested_events);
        foreach ($this->step_after_tested_events as $after_event) {
            $this->step_setup_printer->print_teardown($formatter, $after_event->get_teardown());
        }
        $this->example_setup_printer->print_teardown($formatter, $event->get_teardown());
        $this->example_setup = null;
        $this->step_before_tested_events = [];
        $this->step_after_tested_events = [];
    }

    /**
     * Prints outline footer on outline AFTER event.
     */
    private function print_footer_on_after_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof After_Outline_Tested) {
            return;
        }
        $this->table_printer->print_footer($formatter, $event->get_test_result());
    }

    /**
     * Returns currently captured step events results.
     *
     * @return Step_Result[]
     */
    private function get_step_test_results(): array
    {
        return array_map(fn (After_Step_Tested $event): Step_Result => $event->get_test_result(), $this->step_after_tested_events);
    }
}
