<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Event_Dispatcher\Event;

use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event right after a test setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface After_Setup
{
    /**
     * Returns current test setup.
     *
     * @return Setup
     */
    public function get_setup();
}
