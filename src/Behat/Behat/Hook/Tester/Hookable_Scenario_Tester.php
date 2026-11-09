<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Tester;

use Behat\Behat\Hook\Scope\After_Scenario_Scope;
use Behat\Behat\Hook\Scope\Before_Scenario_Scope;
use Behat\Behat\Tester\Scenario_Tester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Hook_Dispatcher;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Setup;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Teardown;
use Behat\Testwork\Tester\Result\Test_Result;

/**
 * Scenario tester which dispatches hooks during its execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Hookable_Scenario_Tester implements Scenario_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Scenario_Tester $base_tester, private Hook_Dispatcher $hook_dispatcher)
    {
    }

    public function set_up(Environment $env, FeatureNode $feature, Scenario $scenario, $skip)
    {
        $setup = $this->base_tester->set_up($env, $feature, $scenario, $skip);
        if ($skip) {
            return $setup;
        }
        $scope = new Before_Scenario_Scope($env, $feature, $scenario);
        $hook_call_results = $this->hook_dispatcher->dispatch_scope_hooks($scope);

        return new Hooked_Setup($setup, $hook_call_results);
    }

    public function test(Environment $env, FeatureNode $feature, Scenario $scenario, $skip)
    {
        return $this->base_tester->test($env, $feature, $scenario, $skip);
    }

    public function tear_down(Environment $env, FeatureNode $feature, Scenario $scenario, $skip, Test_Result $result)
    {
        $teardown = $this->base_tester->tear_down($env, $feature, $scenario, $skip, $result);
        if ($skip) {
            return $teardown;
        }
        $scope = new After_Scenario_Scope($env, $feature, $scenario, $result);
        $hook_call_results = $this->hook_dispatcher->dispatch_scope_hooks($scope);

        return new Hooked_Teardown($teardown, $hook_call_results);
    }
}
