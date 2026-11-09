<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Event_Listener\AST;

use Behat\Behat\Event_Dispatcher\Event\Scenario_Like_Tested;
use Behat\Behat\Output\Node\Printer\Scenario_Printer;
use Behat\Behat\Output\Node\Printer\Setup_Printer;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Event_Dispatcher\Event\After_Setup;
use Behat\Testwork\Event_Dispatcher\Event\After_Tested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;
/**
 * Listens to scenario events and calls appropriate printers (header/footer).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Scenario_Node_Listener implements Event_Listener
{
    public function __construct(private string $before_event_name, private string $after_event_name, private Scenario_Printer $scenario_printer, private ?Setup_Printer $setup_printer = null)
    {
    }
    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        if (!$event instanceof Scenario_Like_Tested) {
            return;
        }
        $this->print_header_on_before_event($formatter, $event, $event_name);
        $this->print_footer_on_after_event($formatter, $event, $event_name);
    }
    /**
     * Prints scenario/background header on BEFORE event.
     */
    private function print_header_on_before_event(Formatter $formatter, Scenario_Like_Tested $event, string $event_name): void
    {
        if ($this->before_event_name !== $event_name || !$event instanceof After_Setup) {
            return;
        }
        if ($this->setup_printer instanceof Setup_Printer) {
            $this->setup_printer->print_setup($formatter, $event->get_setup());
        }
        $this->scenario_printer->print_header($formatter, $event->get_feature(), $event->get_scenario());
    }
    /**
     * Prints scenario/background footer on AFTER event.
     */
    private function print_footer_on_after_event(Formatter $formatter, Scenario_Like_Tested $event, string $event_name): void
    {
        if ($this->after_event_name !== $event_name || !$event instanceof After_Tested) {
            return;
        }
        if ($this->setup_printer instanceof Setup_Printer) {
            $this->setup_printer->print_teardown($formatter, $event->get_teardown());
        }
        $this->scenario_printer->print_footer($formatter, $event->get_test_result());
    }
}