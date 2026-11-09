<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result;

/**
 * Represents an integer test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Integer_Test_Result implements Test_Result
{
    /**
     * Initializes test result.
     */
    public function __construct(private $result_code)
    {
    }

    public function is_passed(): bool
    {
        return self::PASSED == $this->get_result_code();
    }

    /**
     * @return Test_Result::*
     */
    public function get_result_code()
    {
        return $this->result_code;
    }
}
