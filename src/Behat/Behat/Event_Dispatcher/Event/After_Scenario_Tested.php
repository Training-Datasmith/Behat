<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Event_Dispatcher\Event;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Event_Dispatcher\Event\After_Tested;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents an event after scenario has been tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class After_Scenario_Tested extends Scenario_Tested implements After_Tested
{
    /**
     * Initializes event.
     */
    public function __construct(Environment $env, private readonly FeatureNode $feature, private readonly Scenario $scenario, private readonly Test_Result $result, private readonly Teardown $teardown)
    {
        parent::__construct($env);
    }

    /**
     * Returns feature.
     */
    public function get_feature(): FeatureNode
    {
        return $this->feature;
    }

    public function get_scenario(): Scenario
    {
        return $this->scenario;
    }

    /**
     * Returns current test result.
     */
    public function get_test_result(): Test_Result
    {
        return $this->result;
    }

    /**
     * Returns current test teardown.
     */
    public function get_teardown(): Teardown
    {
        return $this->teardown;
    }
}
