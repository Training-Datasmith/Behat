<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Event_Dispatcher\Tester;

use Behat\Behat\Event_Dispatcher\Event\After_Scenario_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Scenario_Tested;
use Behat\Behat\Event_Dispatcher\Event\Before_Scenario_Teardown;
use Behat\Behat\Event_Dispatcher\Event\Before_Scenario_Tested;
use Behat\Behat\Tester\Scenario_Tester;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Scenario_Interface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\Test_Result;
use Symfony\Component\Event_Dispatcher\Event_Dispatcher_Interface;
/**
 * Scenario tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Event_Dispatching_Scenario_Tester implements Scenario_Tester
{
    /**
     * Initializes tester.
     *
     * @param string                   $beforeEventName
     * @param string                   $afterSetupEventName
     * @param string                   $beforeTeardownEventName
     * @param string                   $afterEventName
     */
    public function __construct(private readonly Scenario_Tester $base_tester, private readonly Event_Dispatcher_Interface $event_dispatcher, private $before_event_name, private $after_setup_event_name, private $before_teardown_event_name, private $after_event_name)
    {
    }
    public function set_up(Environment $env, Feature_Node $feature, Scenario $scenario, $skip)
    {
        $event = new Before_Scenario_Tested($env, $feature, $scenario);
        $this->event_dispatcher->dispatch($event, $this->before_event_name);
        $setup = $this->base_tester->set_up($env, $feature, $scenario, $skip);
        $event = new After_Scenario_Setup($env, $feature, $scenario, $setup);
        $this->event_dispatcher->dispatch($event, $this->after_setup_event_name);
        return $setup;
    }
    public function test(Environment $env, Feature_Node $feature, Scenario $scenario, $skip)
    {
        return $this->base_tester->test($env, $feature, $scenario, $skip);
    }
    public function tear_down(Environment $env, Feature_Node $feature, Scenario $scenario, $skip, Test_Result $result)
    {
        $event = new Before_Scenario_Teardown($env, $feature, $scenario, $result);
        $this->event_dispatcher->dispatch($event, $this->before_teardown_event_name);
        $teardown = $this->base_tester->tear_down($env, $feature, $scenario, $skip, $result);
        $event = new After_Scenario_Tested($env, $feature, $scenario, $result, $teardown);
        $this->event_dispatcher->dispatch($event, $this->after_event_name);
        return $teardown;
    }
}