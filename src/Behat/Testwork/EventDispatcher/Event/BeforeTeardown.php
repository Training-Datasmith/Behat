<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Event_Dispatcher\Event;

use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Represents an event right before a teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Before_Teardown
{
    /**
     * Returns current test result.
     *
     * @return TestResult
     */
    public function get_test_result();
}