<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Hook\Tester;

use Behat\Behat\Hook\Scope\After_Feature_Scope;
use Behat\Behat\Hook\Scope\Before_Feature_Scope;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Hook_Dispatcher;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Setup;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Teardown;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Specification_Tester;
/**
 * Feature tester which dispatches hooks during its execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements SpecificationTester<FeatureNode>
 */
final readonly class Hookable_Feature_Tester implements Specification_Tester
{
    /**
     * Initializes tester.
     *
     * @param SpecificationTester<FeatureNode> $baseTester
     */
    public function __construct(private Specification_Tester $base_tester, private Hook_Dispatcher $hook_dispatcher)
    {
    }
    public function set_up(Environment $env, $spec, $skip)
    {
        $setup = $this->base_tester->set_up($env, $spec, $skip);
        if ($skip) {
            return $setup;
        }
        $scope = new Before_Feature_Scope($env, $spec);
        $hook_call_results = $this->hook_dispatcher->dispatch_scope_hooks($scope);
        return new Hooked_Setup($setup, $hook_call_results);
    }
    public function test(Environment $env, $spec, $skip)
    {
        return $this->base_tester->test($env, $spec, $skip);
    }
    public function tear_down(Environment $env, $spec, $skip, Test_Result $result)
    {
        $teardown = $this->base_tester->tear_down($env, $spec, $skip, $result);
        if ($skip) {
            return $teardown;
        }
        $scope = new After_Feature_Scope($env, $spec, $result);
        $hook_call_results = $this->hook_dispatcher->dispatch_scope_hooks($scope);
        return new Hooked_Teardown($teardown, $hook_call_results);
    }
}