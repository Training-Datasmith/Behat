<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Tester\Result;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\Search_Result;
use Behat\Behat\Tester\Exception\Pending_Exception;
use Behat\Testwork\Call\Call_Result;
use Behat\Testwork\Tester\Result\Exception_Result;
use Exception;
/**
 * Represents an executed (successfully or not) step result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Executed_Step_Result implements Step_Result, Defined_Step_Result, Exception_Result
{
    /**
     * Initialize test result.
     */
    public function __construct(private Search_Result $search_result, private Call_Result $call_result)
    {
    }
    /**
     * Returns definition search result.
     */
    public function get_search_result(): Search_Result
    {
        return $this->search_result;
    }
    /**
     * Returns definition call result or null if no call were made.
     */
    public function get_call_result(): Call_Result
    {
        return $this->call_result;
    }
    public function get_step_definition(): ?Definition
    {
        return $this->search_result->get_matched_definition();
    }
    public function has_exception(): bool
    {
        return $this->get_exception() instanceof Exception;
    }
    public function get_exception(): ?Exception
    {
        return $this->call_result->get_exception();
    }
    /**
     * @return self::PENDING|self::FAILED|self::PASSED
     */
    public function get_result_code(): int
    {
        if ($this->call_result->has_exception() && $this->call_result->get_exception() instanceof Pending_Exception) {
            return self::PENDING;
        }
        if ($this->call_result->has_exception()) {
            return self::FAILED;
        }
        return self::PASSED;
    }
    public function is_passed(): bool
    {
        return self::PASSED == $this->get_result_code();
    }
}