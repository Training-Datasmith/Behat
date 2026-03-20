<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Tester\Result;

use Throwable;
/**
 * Represents a result, that possibly produced an exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Exception_Result extends Test_Result
{
    /**
     * Checks that the test result has exception.
     *
     * @return bool
     */
    public function has_exception();
    /**
     * Returns exception that test result has.
     *
     * @return Throwable|null
     */
    public function get_exception();
}