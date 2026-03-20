<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Output\Node\Event_Listener;

use ArrayIterator;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Countable;
use IteratorAggregate;
/**
 * Used to compose formatter event listeners.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements IteratorAggregate<int, EventListener>
 */
class Chain_Event_Listener implements Event_Listener, Countable, IteratorAggregate
{
    /**
     * Initializes collection.
     *
     * @param EventListener[] $listeners
     */
    public function __construct(private readonly array $listeners)
    {
    }
    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        foreach ($this->listeners as $listener) {
            $listener->listen_event($formatter, $event, $event_name);
        }
    }
    public function count(): int
    {
        return count($this->listeners);
    }
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->listeners);
    }
}