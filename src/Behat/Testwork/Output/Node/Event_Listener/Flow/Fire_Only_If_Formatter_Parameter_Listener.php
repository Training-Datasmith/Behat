<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Node\Event_Listener\Flow;

use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;

/**
 * Catches all events, but proxies them only if formatter has specific parameter set to a specific value.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Fire_Only_If_Formatter_Parameter_Listener implements Event_Listener
{
    /**
     * Initializes listener.
     *
     * @param string        $name
     */
    public function __construct(private $name, private $value, private readonly Event_Listener $descendant)
    {
    }

    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        if ($this->value !== $formatter->get_parameter($this->name)) {
            return;
        }
        $this->descendant->listen_event($formatter, $event, $event_name);
    }
}
