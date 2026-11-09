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

use Behat\Behat\Tester\Scenario_Tester;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Scenario_Interface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Environment_Manager;
use Behat\Testwork\Tester\Result\Integer_Test_Result;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Result\Test_With_Setup_Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Successful_Setup;
use Behat\Testwork\Tester\Setup\Successful_Teardown;
use Behat\Testwork\Tester\Setup\Teardown;
/**
 * Scenario tester that isolates the environment for each scenario.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Isolating_Scenario_Tester implements Scenario_Tester
{
    /**
     * Initialises tester.
     */
    public function __construct(private Scenario_Tester $decorated_tester, private Environment_Manager $env_manager)
    {
    }
    public function set_up(Environment $env, Feature_Node $feature, Scenario $scenario, $skip): Setup
    {
        return new Successful_Setup();
    }
    public function test(Environment $env, Feature_Node $feature, Scenario $scenario, $skip): Test_Result
    {
        $isolated_environment = $this->env_manager->isolate_environment($env, $scenario);
        $setup = $this->decorated_tester->set_up($isolated_environment, $feature, $scenario, $skip);
        $local_skip = !$setup->is_successful() || $skip;
        $test_result = $this->decorated_tester->test($isolated_environment, $feature, $scenario, $local_skip);
        $teardown = $this->decorated_tester->tear_down($isolated_environment, $feature, $scenario, $local_skip, $test_result);
        $integer_result = new Integer_Test_Result($test_result->get_result_code());
        return new Test_With_Setup_Result($setup, $integer_result, $teardown);
    }
    public function tear_down(Environment $env, Feature_Node $feature, Scenario $scenario, $skip, Test_Result $result): Teardown
    {
        return new Successful_Teardown();
    }
}