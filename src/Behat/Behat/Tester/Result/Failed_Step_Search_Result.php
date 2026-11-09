<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Result;

use Behat\Behat\Definition\Exception\Search_Exception;
use Behat\Testwork\Tester\Result\Exception_Result;

/**
 * Represents a step test result with a failed definition search.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Failed_Step_Search_Result implements Step_Result, Exception_Result
{
    /**
     * Initializes result.
     */
    public function __construct(private Search_Exception $search_exception)
    {
    }

    public function has_exception(): bool
    {
        return true;
    }

    public function get_exception(): Search_Exception
    {
        return $this->search_exception;
    }

    public function is_passed(): bool
    {
        return false;
    }

    /**
     * @return self::FAILED
     */
    public function get_result_code(): int
    {
        return self::FAILED;
    }
}
