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

use Behat\Behat\Event_Dispatcher\Event\After_Feature_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Feature_Tested;
use Behat\Behat\Event_Dispatcher\Event\Feature_Tested;
use Behat\Behat\Output\Node\Printer\Feature_Printer;
use Behat\Behat\Output\Node\Printer\Setup_Printer;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;

/**
 * Listens to feature events and calls appropriate printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Feature_Listener implements Event_Listener
{
    /**
     * Initializes listener.
     */
    public function __construct(private Feature_Printer $feature_printer, private Setup_Printer $setup_printer)
    {
    }

    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        if (!$event instanceof Feature_Tested) {
            return;
        }
        $this->print_header_on_before_event($formatter, $event);
        $this->print_footer_on_after_event($formatter, $event);
    }

    /**
     * Prints feature header on BEFORE event.
     */
    private function print_header_on_before_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof After_Feature_Setup) {
            return;
        }
        $this->setup_printer->print_setup($formatter, $event->get_setup());
        $this->feature_printer->print_header($formatter, $event->get_feature());
    }

    /**
     * Prints feature footer on AFTER event.
     */
    private function print_footer_on_after_event(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof After_Feature_Tested) {
            return;
        }
        $this->setup_printer->print_teardown($formatter, $event->get_teardown());
        $this->feature_printer->print_footer($formatter, $event->get_test_result());
    }
}
