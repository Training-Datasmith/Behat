<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Event_Listener\Flow;

use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;

/**
 * Behat fire only siblings listener.
 *
 * This listener catches all events, but proxies them to further listeners only if they
 * live inside specific event lifecycle (between BEFORE and AFTER events).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Fire_Only_Siblings_Listener implements Event_Listener
{
    private bool $in_context = false;

    /**
     * Initializes listener.
     */
    public function __construct(private $before_event_name, private $after_event_name, private readonly Event_Listener $descendant)
    {
    }

    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        if ($this->before_event_name === $event_name) {
            $this->in_context = true;
        }
        if ($this->in_context) {
            $this->descendant->listen_event($formatter, $event, $event_name);
        }
        if ($this->after_event_name === $event_name) {
            $this->in_context = false;
        }
    }
}
