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

use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Tester\Setup\Setup;
/**
 * Represents an event in which exercise is prepared to be executed.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class After_Exercise_Setup extends Exercise_Completed implements After_Setup
{
    /**
     * Initializes event.
     *
     * @param SpecificationIterator<mixed>[] $specificationIterators
     */
    public function __construct(private readonly array $specification_iterators, private readonly Setup $setup)
    {
    }
    /**
     * @return SpecificationIterator[]
     */
    public function get_specification_iterators(): array
    {
        return $this->specification_iterators;
    }
    /**
     * Returns exercise setup result.
     */
    public function get_setup(): Setup
    {
        return $this->setup;
    }
}