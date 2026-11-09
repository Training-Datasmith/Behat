<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Runtime;

use Behat\Testwork\Environment\Environment_Manager;
use Behat\Testwork\Specification\Grouped_Specification_Iterator;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\Integer_Test_Result;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Result\Test_Results;
use Behat\Testwork\Tester\Result\Test_With_Setup_Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Successful_Setup;
use Behat\Testwork\Tester\Setup\Successful_Teardown;
use Behat\Testwork\Tester\Setup\Teardown;
use Behat\Testwork\Tester\Suite_Tester;

/**
 * Tester executing exercises in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template TSpec
 *
 * @implements Exercise<TSpec>
 */
final readonly class Runtime_Exercise implements Exercise
{
    /**
     * Initializes tester.
     */
    public function __construct(private Environment_Manager $env_manager, private Suite_Tester $suite_tester)
    {
    }

    public function set_up(array $iterators, $skip): Setup
    {
        return new Successful_Setup();
    }

    public function test(array $iterators, $skip = false): Test_Result
    {
        $results = [];
        foreach (Grouped_Specification_Iterator::group($iterators) as $iterator) {
            $environment = $this->env_manager->build_environment($iterator->get_suite());
            $setup = $this->suite_tester->set_up($environment, $iterator, $skip);
            $local_skip = !$setup->is_successful() || $skip;
            $test_result = $this->suite_tester->test($environment, $iterator, $local_skip);
            $teardown = $this->suite_tester->tear_down($environment, $iterator, $local_skip, $test_result);
            $integer_result = new Integer_Test_Result($test_result->get_result_code());
            $results[] = new Test_With_Setup_Result($setup, $integer_result, $teardown);
        }

        return new Test_Results($results);
    }

    public function tear_down(array $iterators, $skip, Test_Result $result): Teardown
    {
        return new Successful_Teardown();
    }
}
