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

use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Scenario_Interface;
/**
 * Represents an event of scenario-like structure (Scenario, Background, Example).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Scenario_Like_Tested extends Gherkin_Node_Tested
{
    /**
     * Returns feature node.
     *
     * @return FeatureNode
     */
    public function get_feature();
    /**
     * Returns scenario node.
     *
     * @return ScenarioInterface
     */
    public function get_scenario();
}