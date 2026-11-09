<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prepares and tests provided suite specifications against provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template TSpec
 */
interface Suite_Tester
{
    /**
     * Sets up suite for a test.
     *
     * @param Specification_Iterator<TSpec> $iterator
     * @param bool               $skip
     *
     * @return Setup
     */
    public function set_up(Environment $env, Specification_Iterator $iterator, $skip);

    /**
     * Tests provided suite specifications.
     *
     * @param Specification_Iterator<TSpec> $iterator
     * @param bool               $skip
     *
     * @return Test_Result
     */
    public function test(Environment $env, Specification_Iterator $iterator, $skip);

    /**
     * Tears down suite after a test.
     *
     * @param Specification_Iterator<TSpec> $iterator
     * @param bool               $skip
     *
     * @return Teardown
     */
    public function tear_down(Environment $env, Specification_Iterator $iterator, $skip, Test_Result $result);
}
