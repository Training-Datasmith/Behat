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

use Behat\Behat\Hook\Scope\After_Step_Scope;
use Behat\Behat\Hook\Scope\Before_Step_Scope;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Behat\Tester\Step_Tester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Hook_Dispatcher;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Setup;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Teardown;

/**
 * Step tester which dispatches hooks during its execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Hookable_Step_Tester implements Step_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Step_Tester $base_tester, private Hook_Dispatcher $hook_dispatcher)
    {
    }

    public function set_up(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        $setup = $this->base_tester->set_up($env, $feature, $step, $skip);
        if ($skip) {
            return $setup;
        }
        $scope = new Before_Step_Scope($env, $feature, $step);
        $hook_call_results = $this->hook_dispatcher->dispatch_scope_hooks($scope);

        return new Hooked_Setup($setup, $hook_call_results);
    }

    public function test(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        return $this->base_tester->test($env, $feature, $step, $skip);
    }

    public function tear_down(Environment $env, FeatureNode $feature, StepNode $step, $skip, Step_Result $result)
    {
        $teardown = $this->base_tester->tear_down($env, $feature, $step, $skip, $result);
        if ($skip) {
            return $teardown;
        }
        $scope = new After_Step_Scope($env, $feature, $step, $result);
        $hook_call_results = $this->hook_dispatcher->dispatch_scope_hooks($scope);

        return new Hooked_Teardown($teardown, $hook_call_results);
    }
}
