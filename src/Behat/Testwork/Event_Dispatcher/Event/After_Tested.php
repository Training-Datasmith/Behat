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

use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents an event right after a test was completed.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface After_Tested
{
    /**
     * Returns current test result.
     *
     * @return Test_Result
     */
    public function get_test_result();

    /**
     * Returns current test teardown.
     *
     * @return Teardown
     */
    public function get_teardown();
}
