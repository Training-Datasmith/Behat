<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Tester;

use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;
/**
 * Prepares and tests provided exercise specifications.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template TSpec
 */
interface Exercise
{
    /**
     * Sets up exercise for a test.
     *
     * @param SpecificationIterator<TSpec>[] $iterators
     * @param bool                 $skip
     *
     * @return Setup
     */
    public function set_up(array $iterators, $skip);
    /**
     * Tests suites specifications.
     *
     * @param SpecificationIterator<TSpec>[] $iterators
     * @param bool                 $skip
     *
     * @return TestResult
     */
    public function test(array $iterators, $skip);
    /**
     * Tears down exercise after a test.
     *
     * @param SpecificationIterator<TSpec>[] $iterators
     * @param bool                 $skip
     *
     * @return Teardown
     */
    public function tear_down(array $iterators, $skip, Test_Result $result);
}