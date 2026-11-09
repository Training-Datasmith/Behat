<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Specification;

use Behat\Testwork\Suite\Suite;
use Return_Type_Will_Change;

/**
 * Iterates over specification iterators grouped by their suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template T
 *
 * @implements Specification_Iterator<T>
 */
final class Grouped_Specification_Iterator implements Specification_Iterator
{
    private int $position = 0;

    /**
     * Initializes iterator.
     *
     * @param list<Specification_Iterator<T>> $iterators
     */
    public function __construct(private readonly Suite $suite, private array $iterators)
    {
    }

    /**
     * Groups specifications by their suite.
     *
     * @template TSpec
     *
     * @return array<string, Grouped_Specification_Iterator<TSpec>>
     */
    public static function group(array $specification_iterators): array
    {
        $grouped_specifications = [];
        foreach ($specification_iterators as $specification_iterator) {
            $grouped_specifications[$specification_iterator->get_suite()->get_name()][] = $specification_iterator;
        }

        return array_map(fn (array $iterator): Grouped_Specification_Iterator => new Grouped_Specification_Iterator($iterator[0]->get_suite(), $iterator), $grouped_specifications);
    }

    public function get_suite(): Suite
    {
        return $this->suite;
    }

    public function rewind(): void
    {
        $this->position = 0;
        while (isset($this->iterators[$this->position])) {
            $this->iterators[$this->position]->rewind();
            if ($this->iterators[$this->position]->valid()) {
                break;
            }
            ++$this->position;
        }
    }

    public function next(): void
    {
        if (!isset($this->iterators[$this->position])) {
            return;
        }
        $this->iterators[$this->position]->next();
        while (!$this->iterators[$this->position]->valid()) {
            ++$this->position;
            if (!isset($this->iterators[$this->position])) {
                break;
            }
            $this->iterators[$this->position]->rewind();
        }
    }

    public function valid(): bool
    {
        return isset($this->iterators[$this->position]) && $this->iterators[$this->position]->valid();
    }

    #[Return_Type_Will_Change]
    public function current()
    {
        return $this->iterators[$this->position]->current();
    }

    public function key(): int
    {
        return $this->position + $this->iterators[$this->position]->key();
    }
}
