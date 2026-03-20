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

use Behat\Behat\Tester\Background_Tester;
use Behat\Behat\Tester\Scenario_Tester;
use Behat\Behat\Tester\Step_Container_Tester;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Scenario_Interface as Scenario;
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
 * Tester executing scenario or example tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Runtime_Scenario_Tester implements Scenario_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Step_Container_Tester $container_tester, private Background_Tester $background_tester)
    {
    }
    public function set_up(Environment $env, Feature_Node $feature, Scenario $scenario, $skip): Setup
    {
        return new Successful_Setup();
    }
    public function test(Environment $env, Feature_Node $feature, Scenario $scenario, $skip = false): Test_Result
    {
        $results = [];
        if ($feature->has_background()) {
            $background_result = $this->test_background($env, $feature, $skip);
            $skip = !$background_result->is_passed() || $skip;
            $results[] = $background_result;
        }
        $results = array_merge($results, $this->container_tester->test($env, $feature, $scenario, $skip));
        return new Test_Results($results);
    }
    public function tear_down(Environment $env, Feature_Node $feature, Scenario $scenario, $skip, Test_Result $result): Teardown
    {
        return new Successful_Teardown();
    }
    /**
     * Tests background of the provided feature against provided environment.
     *
     * @param bool     $skip
     */
    private function test_background(Environment $env, Feature_Node $feature, $skip): Test_With_Setup_Result
    {
        $setup = $this->background_tester->set_up($env, $feature, $skip);
        $skip_setup = !$setup->is_successful() || $skip;
        $test_result = $this->background_tester->test($env, $feature, $skip_setup);
        $teardown = $this->background_tester->tear_down($env, $feature, $skip_setup, $test_result);
        $integer_result = new Integer_Test_Result($test_result->get_result_code());
        return new Test_With_Setup_Result($setup, $integer_result, $teardown);
    }
}