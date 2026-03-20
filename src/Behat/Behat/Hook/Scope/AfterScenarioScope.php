<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Hook\Scope;

use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Scenario_Interface;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Scope\After_Test_Scope;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Represents an AfterScenario hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class After_Scenario_Scope implements Scenario_Scope, After_Test_Scope
{
    /**
     * Initializes scope.
     */
    public function __construct(private Environment $environment, private Feature_Node $feature, private Scenario_Interface $scenario, private Test_Result $result)
    {
    }
    /**
     * Returns hook scope name.
     */
    public function get_name(): string
    {
        return self::AFTER;
    }
    /**
     * Returns hook suite.
     *
     * @return Suite
     */
    public function get_suite()
    {
        return $this->environment->get_suite();
    }
    /**
     * Returns hook environment.
     */
    public function get_environment(): Environment
    {
        return $this->environment;
    }
    /**
     * Returns scope feature.
     */
    public function get_feature(): Feature_Node
    {
        return $this->feature;
    }
    /**
     * Returns scenario.
     */
    public function get_scenario(): Scenario_Interface
    {
        return $this->scenario;
    }
    /**
     * Returns test result.
     */
    public function get_test_result(): Test_Result
    {
        return $this->result;
    }
}