<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Runtime;

use Behat\Behat\Tester\Outline_Tester;
use Behat\Behat\Tester\Scenario_Tester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\Integer_Test_Result;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Result\Test_Results;
use Behat\Testwork\Tester\Result\Test_With_Setup_Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Successful_Setup;
use Behat\Testwork\Tester\Setup\Successful_Teardown;
use Behat\Testwork\Tester\Setup\Teardown;
use Behat\Testwork\Tester\Specification_Tester;

/**
 * Tester executing feature tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements Specification_Tester<FeatureNode>
 */
final readonly class Runtime_Feature_Tester implements Specification_Tester
{
    public function __construct(private Scenario_Tester $scenario_tester, private Outline_Tester $outline_tester)
    {
    }

    public function set_up(Environment $env, $spec, $skip): Setup
    {
        return new Successful_Setup();
    }

    public function test(Environment $env, $spec, $skip = false): Test_Result
    {
        $results = [];
        foreach ($spec->getScenarios() as $scenario) {
            $tester = $scenario instanceof OutlineNode ? $this->outline_tester : $this->scenario_tester;
            $setup = $tester->set_up($env, $spec, $scenario, $skip);
            $local_skip = !$setup->is_successful() || $skip;
            $test_result = $tester->test($env, $spec, $scenario, $local_skip);
            $teardown = $tester->tear_down($env, $spec, $scenario, $local_skip, $test_result);
            $integer_result = new Integer_Test_Result($test_result->get_result_code());
            $results[] = new Test_With_Setup_Result($setup, $integer_result, $teardown);
        }

        return new Test_Results($results);
    }

    public function tear_down(Environment $env, $spec, $skip, Test_Result $result): Teardown
    {
        return new Successful_Teardown();
    }
}
