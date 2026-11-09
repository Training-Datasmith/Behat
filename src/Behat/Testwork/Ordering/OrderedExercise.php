<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Ordering;

use Behat\Testwork\Ordering\Orderer\Noop_Orderer;
use Behat\Testwork\Ordering\Orderer\Orderer;
use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Exercise that is ordered according to a specified algorithm.
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 *
 * @template TSpec
 *
 * @implements Exercise<TSpec>
 */
final class Ordered_Exercise implements Exercise
{
    private \Behat\Testwork\Ordering\Orderer\Noop_Orderer|\Behat\Testwork\Ordering\Orderer\Orderer $orderer;
    /**
     * @var SpecificationIterator<TSpec>[]|null
     */
    private ?array $unordered = null;
    /**
     * @var SpecificationIterator<TSpec>[]|null
     */
    private $ordered;
    /**
     * @param Exercise<TSpec> $decoratedExercise
     */
    public function __construct(private readonly Exercise $decorated_exercise)
    {
        $this->orderer = new Noop_Orderer();
    }
    public function set_up(array $iterators, $skip)
    {
        return $this->decorated_exercise->set_up($this->order($iterators), $skip);
    }
    public function test(array $iterators, $skip)
    {
        return $this->decorated_exercise->test($this->order($iterators), $skip);
    }
    public function tear_down(array $iterators, $skip, Test_Result $result)
    {
        return $this->decorated_exercise->tear_down($this->order($iterators), $skip, $result);
    }
    /**
     * Replace the algorithm being used for prioritisation.
     */
    public function set_orderer(Orderer $orderer): void
    {
        $this->orderer = $orderer;
    }
    /**
     * @param SpecificationIterator<TSpec>[] $iterators
     *
     * @return SpecificationIterator<TSpec>[]
     */
    private function order(array $iterators)
    {
        if (!$this->ordered || $this->unordered != $iterators) {
            $this->unordered = $iterators;
            $this->ordered = $this->orderer->order($iterators);
        }
        return $this->ordered;
    }
}