<?php

declare(strict_types=1);

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Tests\Testwork\Event_Dispatcher;

use Behat\Testwork\Event_Dispatcher\Testwork_Event_Dispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class Testwork_Event_Dispatcher_Test extends TestCase
{
    public function testDispatchLegacyCall(): void
    {
        $dispatcher = new Testwork_Event_Dispatcher();
        $event = new class extends Event {};
        $eventName = 'TEST_EVENT';
        $listener = $this->createListenerSpy();

        $dispatcher->addListener($eventName, $listener);
        $dispatcher->dispatch($event, $eventName);

        $this->assertCount(1, $listener->receivedEvents);
        $this->assertEquals($event, $listener->receivedEvents[0]);
    }

    public function testDispatchCurrentCall(): void
    {
        $dispatcher = new Testwork_Event_Dispatcher();
        $event = new class extends Event {};
        $listener = $this->createListenerSpy();

        $dispatcher->addListener(get_class($event), $listener);
        $dispatcher->dispatch($event);

        $this->assertCount(1, $listener->receivedEvents);
        $this->assertEquals($event, $listener->receivedEvents[0]);
    }

    public function testSetNameOnEvent(): void
    {
        $dispatcher = new Testwork_Event_Dispatcher();
        $event = new class extends Event {
            public $name;

            public function setName($name): void
            {
                $this->name = $name;
            }
        };
        $eventName = 'TEST_EVENT';
        $listener = $this->createListenerSpy();

        $dispatcher->addListener($eventName, $listener);
        $dispatcher->dispatch($event, $eventName);

        $this->assertCount(1, $listener->receivedEvents);
        $this->assertEquals($eventName, $event->name);
    }

    public function testBeforeAllListener(): void
    {
        $dispatcher = new Testwork_Event_Dispatcher();
        $event = new class extends Event {};
        $listener = $this->createListenerSpy();

        $dispatcher->addListener(Testwork_Event_Dispatcher::BEFORE_ALL_EVENTS, $listener);
        $dispatcher->dispatch($event);

        $this->assertCount(1, $listener->receivedEvents);
        $this->assertEquals($event, $listener->receivedEvents[0]);
    }

    public function testAfterAllListener(): void
    {
        $dispatcher = new Testwork_Event_Dispatcher();
        $event = new class extends Event {};
        $listener = $this->createListenerSpy();

        $dispatcher->addListener(Testwork_Event_Dispatcher::AFTER_ALL_EVENTS, $listener);
        $dispatcher->dispatch($event);

        $this->assertCount(1, $listener->receivedEvents);
        $this->assertEquals($event, $listener->receivedEvents[0]);
    }

    /**
     * @return callable
     */
    public function createListenerSpy()
    {
        return new class {
            public $receivedEvents = [];

            public function __invoke(Event $event)
            {
                $this->receivedEvents[] = $event;
            }
        };
    }
}
