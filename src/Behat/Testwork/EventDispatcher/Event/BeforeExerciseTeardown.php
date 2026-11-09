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
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Represents an event right before exercise teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Before_Exercise_Teardown extends Exercise_Completed implements Before_Teardown
{
    /**
     * Initializes event.
     *
     * @param SpecificationIterator<mixed>[] $specificationIterators
     */
    public function __construct(private readonly array $specification_iterators, private readonly Test_Result $result)
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
     * Returns exercise test result.
     */
    public function get_test_result(): Test_Result
    {
        return $this->result;
    }
}