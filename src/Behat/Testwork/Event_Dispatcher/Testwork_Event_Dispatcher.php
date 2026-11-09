<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Event_Dispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Extends Symfony2 event dispatcher with catch-all listeners.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Testwork_Event_Dispatcher extends EventDispatcher
{
    public const BEFORE_ALL_EVENTS = '*~';
    public const AFTER_ALL_EVENTS = '~*';
    public const DISPATCHER_VERSION = 2;

    public function getListeners(?string $event_name = null): array
    {
        if (null === $event_name || self::BEFORE_ALL_EVENTS === $event_name) {
            return parent::getListeners($event_name);
        }

        return array_merge(parent::getListeners(self::BEFORE_ALL_EVENTS), parent::getListeners($event_name), parent::getListeners(self::AFTER_ALL_EVENTS));
    }

    public function dispatch(object $event, ?string $event_name = null): object
    {
        if (method_exists($event, 'setName')) {
            $event->setName($event_name);
        }

        return parent::dispatch($event, $event_name);
    }
}
