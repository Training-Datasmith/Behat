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
/**
 * Represents a skipped step result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Skipped_Step_Result implements Step_Result, Defined_Step_Result
{
    /**
     * Initializes step result.
     */
    public function __construct(private Search_Result $search_result)
    {
    }
    /**
     * Returns definition search result.
     */
    public function get_search_result(): Search_Result
    {
        return $this->search_result;
    }
    public function get_step_definition(): ?Definition
    {
        return $this->search_result->get_matched_definition();
    }
    public function is_passed(): bool
    {
        return false;
    }
    /**
     * @return self::SKIPPED
     */
    public function get_result_code(): int
    {
        return self::SKIPPED;
    }
}