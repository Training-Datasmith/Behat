<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Tester\Setup;

/**
 * Represents a result of test tearDown action.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Teardown
{
    /**
     * Returns true if fixtures have been handled successfully.
     *
     * @return bool
     */
    public function is_successful();
    /**
     * Checks if tear down has produced any output.
     *
     * @return bool
     */
    public function has_output();
}