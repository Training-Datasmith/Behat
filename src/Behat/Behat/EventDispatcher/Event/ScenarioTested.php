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

use Behat\Testwork\Event_Dispatcher\Event\Lifecycle_Event;
/**
 * Represents a scenario event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Scenario_Tested extends Lifecycle_Event implements Scenario_Like_Tested
{
    public const BEFORE = 'tester.scenario_tested.before';
    public const AFTER_SETUP = 'tester.scenario_tested.after_setup';
    public const BEFORE_TEARDOWN = 'tester.scenario_tested.before_teardown';
    public const AFTER = 'tester.scenario_tested.after';
    final public function get_node()
    {
        return $this->get_scenario();
    }
}