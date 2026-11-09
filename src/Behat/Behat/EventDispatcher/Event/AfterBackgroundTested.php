<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Event_Dispatcher\Event;

use Behat\Gherkin\Node\Background_Node;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Scenario_Like_Interface;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Event_Dispatcher\Event\After_Tested;
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Setup\Teardown;
/**
 * Represents an event in which background was tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class After_Background_Tested extends Background_Tested implements After_Tested
{
    /**
     * Initializes event.
     */
    public function __construct(Environment $env, private readonly Feature_Node $feature, private readonly Background_Node $background, private readonly Test_Result $result, private readonly Teardown $teardown)
    {
        parent::__construct($env);
    }
    /**
     * Returns feature.
     */
    public function get_feature(): Feature_Node
    {
        return $this->feature;
    }
    /**
     * Returns scenario node.
     *
     * @deprecated Use getBackground() instead
     *
     * @return ScenarioLikeInterface
     */
    public function get_scenario(): \Behat\Gherkin\Node\Background_Node
    {
        return $this->background;
    }
    /**
     * Returns background node.
     */
    public function get_background(): Background_Node
    {
        return $this->background;
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