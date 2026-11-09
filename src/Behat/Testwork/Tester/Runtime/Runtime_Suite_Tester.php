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

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Tester\Result\Integer_Test_Result;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Result\Test_Results;
use Behat\Testwork\Tester\Result\Test_With_Setup_Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Successful_Setup;
use Behat\Testwork\Tester\Setup\Successful_Teardown;
use Behat\Testwork\Tester\Setup\Teardown;
use Behat\Testwork\Tester\Specification_Tester;
use Behat\Testwork\Tester\Suite_Tester;

/**
 * Tester executing suite tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template TSpec
 *
 * @implements Suite_Tester<TSpec>
 */
final readonly class Runtime_Suite_Tester implements Suite_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Specification_Tester $spec_tester)
    {
    }

    public function set_up(Environment $env, Specification_Iterator $iterator, $skip): Setup
    {
        return new Successful_Setup();
    }

    public function test(Environment $env, Specification_Iterator $iterator, $skip = false): Test_Result
    {
        $results = [];
        foreach ($iterator as $specification) {
            $setup = $this->spec_tester->set_up($env, $specification, $skip);
            $local_skip = !$setup->is_successful() || $skip;
            $test_result = $this->spec_tester->test($env, $specification, $local_skip);
            $teardown = $this->spec_tester->tear_down($env, $specification, $local_skip, $test_result);
            $integer_result = new Integer_Test_Result($test_result->get_result_code());
            $results[] = new Test_With_Setup_Result($setup, $integer_result, $teardown);
        }

        return new Test_Results($results);
    }

    public function tear_down(Environment $env, Specification_Iterator $iterator, $skip, Test_Result $result): Teardown
    {
        return new Successful_Teardown();
    }
}
