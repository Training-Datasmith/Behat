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
use Behat\Testwork\Event_Dispatcher\Event\Before_Teardown;
use Behat\Testwork\Tester\Result\Test_Result;

/**
 * Represents an event before scenario teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Before_Scenario_Teardown extends Scenario_Tested implements Before_Teardown
{
    /**
     * Initializes event.
     */
    public function __construct(Environment $env, private readonly FeatureNode $feature, private readonly Scenario $scenario, private readonly Test_Result $result)
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
}
