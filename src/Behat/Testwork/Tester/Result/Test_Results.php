<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Aggregates multiple test results into a collection and provides informational API on top of that.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements IteratorAggregate<int, Test_Result>
 */
final readonly class Test_Results implements Test_Result, Countable, IteratorAggregate
{
    public const NO_TESTS = -100;

    /**
     * Initializes test results collection.
     *
     * @param Test_Result[] $results
     */
    public function __construct(private array $results = [])
    {
    }

    public function is_passed(): bool
    {
        return self::PASSED == $this->get_result_code();
    }

    /**
     * @return Test_Result::*|Test_Results::NO_TESTS
     */
    public function get_result_code()
    {
        $result_code = self::NO_TESTS;
        foreach ($this->results as $result) {
            $result_code = max($result_code, $result->get_result_code());
        }

        return $result_code;
    }

    public function count(): int
    {
        return count($this->results);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }

    /**
     * Returns test results array.
     *
     * @return Test_Result[]
     */
    public function to_array(): array
    {
        return $this->results;
    }
}
