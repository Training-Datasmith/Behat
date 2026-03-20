<?php

declare (strict_types=1);
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
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Outline_Node;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\Integer_Test_Result;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Result\Test_Results;
use Behat\Testwork\Tester\Result\Test_With_Setup_Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Successful_Setup;
use Behat\Testwork\Tester\Setup\Successful_Teardown;
use Behat\Testwork\Tester\Setup\Teardown;
/**
 * Tester executing outline tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Runtime_Outline_Tester implements Outline_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Scenario_Tester $scenario_tester)
    {
    }
    public function set_up(Environment $env, Feature_Node $feature, Outline_Node $outline, $skip): Setup
    {
        return new Successful_Setup();
    }
    public function test(Environment $env, Feature_Node $feature, Outline_Node $outline, $skip = false): Test_Result
    {
        $results = [];
        foreach ($outline->get_examples() as $example) {
            $setup = $this->scenario_tester->set_up($env, $feature, $example, $skip);
            $local_skip = !$setup->is_successful() || $skip;
            $test_result = $this->scenario_tester->test($env, $feature, $example, $local_skip);
            $teardown = $this->scenario_tester->tear_down($env, $feature, $example, $local_skip, $test_result);
            $integer_result = new Integer_Test_Result($test_result->get_result_code());
            $results[] = new Test_With_Setup_Result($setup, $integer_result, $teardown);
        }
        return new Test_Results($results);
    }
    public function tear_down(Environment $env, Feature_Node $feature, Outline_Node $outline, $skip, Test_Result $result): Teardown
    {
        return new Successful_Teardown();
    }
}