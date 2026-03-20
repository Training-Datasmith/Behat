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

use Behat\Behat\Output\Node\Printer\Setup_Printer;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Setup;
use Behat\Testwork\Event_Dispatcher\Event\After_Suite_Tested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;
/**
 * Behat suite listener.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Suite_Listener implements Event_Listener
{
    /**
     * Initializes listener.
     */
    public function __construct(private Setup_Printer $setup_printer)
    {
    }
    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        if ($event instanceof After_Suite_Setup) {
            $this->setup_printer->print_setup($formatter, $event->get_setup());
        }
        if ($event instanceof After_Suite_Tested) {
            $this->setup_printer->print_teardown($formatter, $event->get_teardown());
        }
    }
}