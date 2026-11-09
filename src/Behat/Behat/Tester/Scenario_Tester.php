<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prepares and tests provided scenario object against provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Scenario_Tester
{
    /**
     * Sets up example for a test.
     *
     * @param bool     $skip
     *
     * @return Setup
     */
    public function set_up(Environment $env, FeatureNode $feature, Scenario $scenario, $skip);

    /**
     * Tests example.
     *
     * @param bool     $skip
     *
     * @return Test_Result
     */
    public function test(Environment $env, FeatureNode $feature, Scenario $scenario, $skip);

    /**
     * Tears down example after a test.
     *
     * @param bool     $skip
     *
     * @return Teardown
     */
    public function tear_down(Environment $env, FeatureNode $feature, Scenario $scenario, $skip, Test_Result $result);
}
