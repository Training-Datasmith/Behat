<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Aggregates multiple call results into a collection and provides an informational API on top of that.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements IteratorAggregate<int, Call_Result>
 */
final readonly class Call_Results implements Countable, IteratorAggregate
{
    /**
     * Initializes call results collection.
     *
     * @param Call_Result[] $results
     */
    public function __construct(private array $results = [])
    {
    }

    /**
     * Merges results from provided collection into the current one.
     */
    public static function merge(Call_Results $first, Call_Results $second): self
    {
        return new self(array_merge($first->to_array(), $second->to_array()));
    }

    /**
     * Checks if any call in collection throws an exception.
     */
    public function has_exceptions(): bool
    {
        foreach ($this->results as $result) {
            if ($result->has_exception()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if any call in collection produces an output.
     */
    public function has_std_outs(): bool
    {
        foreach ($this->results as $result) {
            if ($result->has_std_out()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns amount of results.
     */
    public function count(): int
    {
        return count($this->results);
    }

    /**
     * Returns collection iterator.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }

    /**
     * Returns call results array.
     *
     * @return Call_Result[]
     */
    public function to_array(): array
    {
        return $this->results;
    }
}
