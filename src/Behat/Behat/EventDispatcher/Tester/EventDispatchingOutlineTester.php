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

use Behat\Behat\Event_Dispatcher\Event\After_Outline_Setup;
use Behat\Behat\Event_Dispatcher\Event\After_Outline_Tested;
use Behat\Behat\Event_Dispatcher\Event\Before_Outline_Teardown;
use Behat\Behat\Event_Dispatcher\Event\Before_Outline_Tested;
use Behat\Behat\Tester\Outline_Tester;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Outline_Node;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\Test_Result;
use Symfony\Component\Event_Dispatcher\Event_Dispatcher_Interface;
/**
 * Outline tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Event_Dispatching_Outline_Tester implements Outline_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Outline_Tester $base_tester, private Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    public function set_up(Environment $env, Feature_Node $feature, Outline_Node $outline, $skip)
    {
        $event = new Before_Outline_Tested($env, $feature, $outline);
        $this->event_dispatcher->dispatch($event, $event::BEFORE);
        $setup = $this->base_tester->set_up($env, $feature, $outline, $skip);
        $event = new After_Outline_Setup($env, $feature, $outline, $setup);
        $this->event_dispatcher->dispatch($event, $event::AFTER_SETUP);
        return $setup;
    }
    public function test(Environment $env, Feature_Node $feature, Outline_Node $outline, $skip)
    {
        return $this->base_tester->test($env, $feature, $outline, $skip);
    }
    public function tear_down(Environment $env, Feature_Node $feature, Outline_Node $outline, $skip, Test_Result $result)
    {
        $event = new Before_Outline_Teardown($env, $feature, $outline, $result);
        $this->event_dispatcher->dispatch($event, $event::BEFORE_TEARDOWN);
        $teardown = $this->base_tester->tear_down($env, $feature, $outline, $skip, $result);
        $event = new After_Outline_Tested($env, $feature, $outline, $result, $teardown);
        $this->event_dispatcher->dispatch($event, $event::AFTER);
        return $teardown;
    }
}