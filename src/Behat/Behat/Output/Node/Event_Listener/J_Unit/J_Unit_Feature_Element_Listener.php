<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Event_Listener\J_Unit;

use Behat\Behat\Event_Dispatcher\Event\After_Feature_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Feature_Tested;
use Behat\Behat\Event_Dispatcher\Event\After_Scenario_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Scenario_Tested;
use Behat\Behat\Event_Dispatcher\Event\After_Step_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Step_Tested;
use Behat\Behat\Event_Dispatcher\Event\Before_Feature_Tested;
use Behat\Behat\Output\Node\Printer\Feature_Printer;
use Behat\Behat\Output\Node\Printer\J_Unit\J_Unit_Scenario_Printer;
use Behat\Behat\Output\Node\Printer\Setup_Printer;
use Behat\Behat\Output\Node\Printer\Step_Printer;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Setup;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Tested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;

/**
 * Listens to feature, scenario and step events and calls appropriate printers.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class J_Unit_Feature_Element_Listener implements Event_Listener
{
    /**
     * @var After_Step_Tested[]
     */
    private array $after_step_tested_events = [];
    /**
     * @var After_Step_Setup[]
     */
    private array $after_step_setup_events = [];
    private ?After_Suite_Setup $after_suite_setup = null;
    private ?After_Feature_Setup $after_feature_setup = null;
    private After_Scenario_Setup $after_scenario_setup;

    /**
     * Initializes listener.
     */
    public function __construct(private readonly Feature_Printer $feature_printer, private readonly J_Unit_Scenario_Printer $scenario_printer, private readonly Step_Printer $step_printer, private readonly Setup_Printer $setup_printer)
    {
    }

    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->capture_suite_setup_event($event);
        $this->print_feature_on_before_event($formatter, $event);
        $this->capture_step_event($event);
        $this->print_scenario_event($formatter, $event);
        $this->print_feature_on_after_event($formatter, $event);
        $this->print_suite_teardown_event($formatter, $event);
    }

    /**
     * Captures any failures in suite setup.
     * They will be printed later when the first scenario is printed.
     */
    private function capture_suite_setup_event(Event $event): void
    {
        if ($event instanceof After_Suite_Setup) {
            $this->after_suite_setup = $event;
        }
    }

    /**
     * Prints any failures in suite teardown.
     */
    private function print_suite_teardown_event(Formatter $formatter, Event $event): void
    {
        if ($event instanceof After_Suite_Tested) {
            // if needed, add a failure node to the last testCase node that has been created
            $this->setup_printer->print_teardown($formatter, $event->get_teardown());
        }
    }

    /**
     * Prints the header for the feature.
     */
    private function print_feature_on_before_event(Formatter $formatter, Event $event): void
    {
        if ($event instanceof Before_Feature_Tested) {
            $this->feature_printer->print_header($formatter, $event->get_feature());

            return;
        }
        if ($event instanceof After_Feature_Setup) {
            // Captures any failures in feature setup.
            // They will be printed later when the first scenario is printed
            $this->after_feature_setup = $event;
        }
    }

    /**
     * Captures step tested event.
     */
    private function capture_step_event(Event $event): void
    {
        if ($event instanceof After_Step_Tested) {
            $this->after_step_tested_events[$event->get_step()->getLine()] = $event;
        }
        if ($event instanceof After_Step_Setup) {
            $this->after_step_setup_events[$event->get_step()->getLine()] = $event;
        }
    }

    /**
     * Prints the scenario tested event.
     */
    private function print_scenario_event(Formatter $formatter, Event $event): void
    {
        if ($event instanceof After_Scenario_Setup) {
            $this->after_scenario_setup = $event;

            return;
        }
        if (!$event instanceof After_Scenario_Tested) {
            return;
        }
        $after_scenario_tested = $event;
        $this->scenario_printer->print_open_tag($formatter, $after_scenario_tested->get_feature(), $after_scenario_tested->get_scenario(), $after_scenario_tested->get_test_result(), $event->get_feature()->getFile());
        if ($this->after_suite_setup instanceof After_Suite_Setup) {
            $this->setup_printer->print_setup($formatter, $this->after_suite_setup->get_setup());
            $this->after_suite_setup = null;
        }
        if ($this->after_feature_setup instanceof After_Feature_Setup) {
            $this->setup_printer->print_setup($formatter, $this->after_feature_setup->get_setup());
            $this->after_feature_setup = null;
        }
        $this->setup_printer->print_setup($formatter, $this->after_scenario_setup->get_setup());
        foreach ($this->after_step_setup_events as $after_step_setup) {
            $this->setup_printer->print_setup($formatter, $after_step_setup->get_setup());
        }
        foreach ($this->after_step_tested_events as $after_step_tested) {
            $this->step_printer->print_step($formatter, $after_scenario_tested->get_scenario(), $after_step_tested->get_step(), $after_step_tested->get_test_result());
            $this->setup_printer->print_teardown($formatter, $after_step_tested->get_teardown());
        }
        $this->setup_printer->print_teardown($formatter, $after_scenario_tested->get_teardown());
        $this->after_step_tested_events = [];
        $this->after_step_setup_events = [];
    }

    /**
     * Prints the feature on AFTER event.
     */
    private function print_feature_on_after_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof After_Feature_Tested) {
            return;
        }
        $this->setup_printer->print_teardown($formatter, $event->get_teardown());
        $this->feature_printer->print_footer($formatter, $event->get_test_result());
    }
}
