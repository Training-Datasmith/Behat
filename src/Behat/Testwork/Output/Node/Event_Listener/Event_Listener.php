<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Node\Event_Listener;

use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;

/**
 * Used to define formatter event listeners.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Event_Listener
{
    /**
     * Notifies listener about an event.
     */
    public function listen_event(Formatter $formatter, Event $event, $event_name);
}
