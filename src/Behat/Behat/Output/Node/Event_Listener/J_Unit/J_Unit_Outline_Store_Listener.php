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

use Behat\Behat\Event_Dispatcher\Event\Before_Outline_Tested;
use Behat\Behat\Output\Node\Printer\Suite_Printer;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Tested;
use Behat\Testwork\Event_Dispatcher\Event\Before_Suite_Tested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;

/**
 * Listens for Outline events store the current one.
 *
 * @author James Watson <james@sitepulse.org>
 */
final class J_Unit_Outline_Store_Listener implements Event_Listener
{
    private array $line_scenario_map = [];

    /**
     * Initializes listener.
     */
    public function __construct(private readonly Suite_Printer $suite_printer)
    {
    }

    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->capture_outline_on_before_outline_event($event);
        $this->print_header_on_before_suite_tested_event($formatter, $event);
        $this->print_footer_on_after_suite_tested_event($formatter, $event);
    }

    /**
     * Captures outline into the ivar on outline BEFORE event.
     */
    private function capture_outline_on_before_outline_event(Event $event): void
    {
        if (!$event instanceof Before_Outline_Tested) {
            return;
        }
        $outline = $event->get_outline();
        foreach ($outline->getExamples() as $example) {
            $this->line_scenario_map[$example->getLine()] = $outline;
        }
    }

    private function print_header_on_before_suite_tested_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof Before_Suite_Tested) {
            return;
        }
        $this->suite_printer->print_header($formatter, $event->get_suite());
    }

    private function print_footer_on_after_suite_tested_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof After_Suite_Tested) {
            return;
        }
        $this->suite_printer->print_footer($formatter, $event->get_suite());
    }

    /**
     * @return OutlineNode
     *
     * @deprecated this method will be removed in the next major as the JUnit formatter no longer uses this information
     */
    public function get_current_outline(ExampleNode $scenario)
    {
        return $this->line_scenario_map[$scenario->getLine()];
    }
}
