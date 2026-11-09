<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Hook\Tester;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Hook_Dispatcher;
use Behat\Testwork\Hook\Scope\After_Suite_Scope;
use Behat\Testwork\Hook\Scope\Before_Suite_Scope;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Setup;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Teardown;
use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Suite_Tester;
/**
 * Suite tester which dispatches hooks during its execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template TSpec
 *
 * @implements SuiteTester<TSpec>
 */
final readonly class Hookable_Suite_Tester implements Suite_Tester
{
    /**
     * Initializes tester.
     *
     * @param SuiteTester<TSpec> $baseTester
     */
    public function __construct(private Suite_Tester $base_tester, private Hook_Dispatcher $hook_dispatcher)
    {
    }
    public function set_up(Environment $env, Specification_Iterator $iterator, $skip)
    {
        $setup = $this->base_tester->set_up($env, $iterator, $skip);
        if ($skip) {
            return $setup;
        }
        $scope = new Before_Suite_Scope($env, $iterator);
        $hook_call_results = $this->hook_dispatcher->dispatch_scope_hooks($scope);
        return new Hooked_Setup($setup, $hook_call_results);
    }
    public function test(Environment $env, Specification_Iterator $iterator, $skip)
    {
        return $this->base_tester->test($env, $iterator, $skip);
    }
    public function tear_down(Environment $env, Specification_Iterator $iterator, $skip, Test_Result $result)
    {
        $teardown = $this->base_tester->tear_down($env, $iterator, $skip, $result);
        if ($skip) {
            return $teardown;
        }
        $scope = new After_Suite_Scope($env, $iterator, $result);
        $hook_call_results = $this->hook_dispatcher->dispatch_scope_hooks($scope);
        return new Hooked_Teardown($teardown, $hook_call_results);
    }
}