<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output;

use Behat\Config\Formatter\Show_Output_Option;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Event_Dispatcher\Testwork_Event_Dispatcher;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;
use Behat\Testwork\Output\Printer\Output_Printer;

/**
 * Formatter built around the idea of event delegation and composition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Node_Event_Listening_Formatter implements Formatter
{
    /**
     * Initializes formatter.
     *
     * @param string        $name
     * @param string        $description
     */
    public function __construct(private $name, private $description, private array $parameters, private readonly Output_Printer $printer, private readonly Event_Listener $listener)
    {
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function get_subscribed_events(): array
    {
        return [Testwork_Event_Dispatcher::BEFORE_ALL_EVENTS => 'listen_event'];
    }

    public static function getSubscribedEvents(): array
    {
        return self::get_subscribed_events();
    }

    /**
     * Proxies event to the listener.
     */
    public function listen_event(Event $event, $event_name = null): void
    {
        if (null === $event_name) {
            $event_name = method_exists($event, 'getName') ? $event->get_name() : $event::class;
        }
        $this->listener->listen_event($this, $event, $event_name);
    }

    public function get_name()
    {
        return $this->name;
    }

    public function get_description()
    {
        return $this->description;
    }

    public function get_output_printer(): Output_Printer
    {
        return $this->printer;
    }

    public function set_parameter($name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function get_parameter($name)
    {
        $value = $this->parameters[$name] ?? null;
        if ($value !== null && $name === Show_Output_Option::OPTION_NAME) {
            return Show_Output_Option::from($value);
        }

        return $value;
    }
}
