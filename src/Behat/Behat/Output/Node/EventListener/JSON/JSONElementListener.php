<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Event_Listener\JSON;

use Behat\Behat\Event_Dispatcher\Event\After_Feature_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Feature_Tested;
use Behat\Behat\Event_Dispatcher\Event\After_Scenario_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Scenario_Tested;
use Behat\Behat\Event_Dispatcher\Event\After_Step_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Step_Tested;
use Behat\Behat\Output\Node\Printer\Exercise_Printer;
use Behat\Behat\Output\Node\Printer\Feature_Printer;
use Behat\Behat\Output\Node\Printer\Scenario_Printer;
use Behat\Behat\Output\Node\Printer\Setup_Printer;
use Behat\Behat\Output\Node\Printer\Step_Printer;
use Behat\Behat\Output\Node\Printer\Suite_Printer;
use Behat\Gherkin\Node\Scenario_Like_Interface;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Event_Dispatcher\Event\After_Exercise_Completed;
use Behat\Testwork\Event_Dispatcher\Event\After_Exercise_Setup;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Setup;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Tested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;
final class Json_Element_Listener implements Event_Listener
{
    private Scenario_Like_Interface $current_scenario;
    public function __construct(private readonly Exercise_Printer $exercise_printer, private readonly Suite_Printer $suite_printer, private readonly Feature_Printer $feature_printer, private readonly Scenario_Printer $scenario_printer, private readonly Step_Printer $step_printer, private readonly Setup_Printer $setup_printer)
    {
    }
    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->on_exercise_start($formatter, $event);
        $this->on_suite_start($formatter, $event);
        $this->on_feature_start($formatter, $event);
        $this->on_scenario_start($formatter, $event);
        $this->on_step_start($formatter, $event);
        $this->on_step_end($formatter, $event);
        $this->on_scenario_end($formatter, $event);
        $this->on_feature_end($formatter, $event);
        $this->on_suite_end($formatter, $event);
        $this->on_exercise_end($formatter, $event);
    }
    private function on_exercise_start(Formatter $formatter, Event $event): void
    {
        if ($event instanceof After_Exercise_Setup) {
            $this->exercise_printer->print_header($formatter);
        }
    }
    private function on_exercise_end(Formatter $formatter, Event $event): void
    {
        if ($event instanceof After_Exercise_Completed) {
            $this->exercise_printer->print_footer($formatter);
        }
    }
    private function on_suite_start(Formatter $formatter, Event $event): void
    {
        if ($event instanceof After_Suite_Setup) {
            $this->suite_printer->print_header($formatter, $event->get_suite());
            $this->setup_printer->print_setup($formatter, $event->get_setup());
        }
    }
    private function on_suite_end(Formatter $formatter, Event $event): void
    {
        if ($event instanceof After_Suite_Tested) {
            $this->setup_printer->print_teardown($formatter, $event->get_teardown());
            $this->suite_printer->print_footer($formatter, $event->get_suite());
        }
    }
    private function on_feature_start(Formatter $formatter, Event $event): void
    {
        if ($event instanceof After_Feature_Setup) {
            $this->feature_printer->print_header($formatter, $event->get_feature());
            $this->setup_printer->print_setup($formatter, $event->get_setup());
        }
    }
    private function on_feature_end(Formatter $formatter, Event $event): void
    {
        if ($event instanceof After_Feature_Tested) {
            $this->setup_printer->print_teardown($formatter, $event->get_teardown());
            $this->feature_printer->print_footer($formatter, $event->get_test_result());
        }
    }
    private function on_scenario_start(Formatter $formatter, Event $event): void
    {
        if ($event instanceof After_Scenario_Setup) {
            $this->scenario_printer->print_header($formatter, $event->get_feature(), $event->get_scenario());
            $this->setup_printer->print_setup($formatter, $event->get_setup());
            $this->current_scenario = $event->get_scenario();
        }
    }
    private function on_scenario_end(Formatter $formatter, Event $event): void
    {
        if ($event instanceof After_Scenario_Tested) {
            $this->scenario_printer->print_footer($formatter, $event->get_test_result());
            $this->setup_printer->print_teardown($formatter, $event->get_teardown());
        }
    }
    private function on_step_start(Formatter $formatter, \Behat\Testwork\Event\Event $event): void
    {
        if ($event instanceof After_Step_Setup) {
            $this->setup_printer->print_setup($formatter, $event->get_setup());
        }
    }
    private function on_step_end(Formatter $formatter, Event $event): void
    {
        if ($event instanceof After_Step_Tested) {
            $this->step_printer->print_step($formatter, $this->current_scenario, $event->get_step(), $event->get_test_result());
            $this->setup_printer->print_teardown($formatter, $event->get_teardown());
        }
    }
}