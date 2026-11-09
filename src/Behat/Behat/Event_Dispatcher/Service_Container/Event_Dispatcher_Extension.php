<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Event_Dispatcher\Service_Container;

use Behat\Behat\Event_Dispatcher\Cli\Stop_On_Failure_Controller;
use Behat\Behat\Event_Dispatcher\Event\Example_Tested;
use Behat\Behat\Event_Dispatcher\Event\Scenario_Tested;
use Behat\Behat\Event_Dispatcher\Tester\Event_Dispatching_Background_Tester;
use Behat\Behat\Event_Dispatcher\Tester\Event_Dispatching_Feature_Tester;
use Behat\Behat\Event_Dispatcher\Tester\Event_Dispatching_Outline_Tester;
use Behat\Behat\Event_Dispatcher\Tester\Event_Dispatching_Scenario_Tester;
use Behat\Behat\Event_Dispatcher\Tester\Event_Dispatching_Step_Tester;
use Behat\Behat\Tester\Service_Container\Tester_Extension;
use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Event_Dispatcher\Service_Container\Event_Dispatcher_Extension as BaseExtension;
use Behat\Testwork\Tester\Service_Container\Tester_Extension as Testwork_Tester_Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extends Testwork Event_Dispatcher_Extension with additional event-dispatching testers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Event_Dispatcher_Extension extends BaseExtension
{
    public function load(ContainerBuilder $container, array $config): void
    {
        parent::load($container, $config);
        $this->load_stop_on_failure_controller($container);
        $this->load_event_dispatching_background_tester($container);
        $this->load_event_dispatching_feature_tester($container);
        $this->load_event_dispatching_outline_tester($container);
        $this->load_event_dispatching_scenario_tester($container);
        $this->load_event_dispatching_example_tester($container);
        $this->load_event_dispatching_step_tester($container);
        $this->load_ticking_step_tester($container);
    }

    /**
     * Loads stop on failure controller.
     */
    protected function load_stop_on_failure_controller(ContainerBuilder $container)
    {
        $definition = new Definition(Stop_On_Failure_Controller::class, [new Reference(Event_Dispatcher_Extension::DISPATCHER_ID)]);
        $definition->addMethodCall('set_stop_on_failure_handler', [new Reference(Testwork_Tester_Extension::STOP_ON_FAILURE_ID)]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 100]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.stop_on_failure', $definition);
    }

    /**
     * Loads event-dispatching background tester.
     */
    protected function load_event_dispatching_background_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Event_Dispatching_Background_Tester::class, [new Reference(Tester_Extension::BACKGROUND_TESTER_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->addTag(Tester_Extension::BACKGROUND_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(Tester_Extension::BACKGROUND_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching feature tester.
     */
    protected function load_event_dispatching_feature_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Event_Dispatching_Feature_Tester::class, [new Reference(Tester_Extension::SPECIFICATION_TESTER_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->addTag(Tester_Extension::SPECIFICATION_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(Tester_Extension::SPECIFICATION_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching outline tester.
     */
    protected function load_event_dispatching_outline_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Event_Dispatching_Outline_Tester::class, [new Reference(Tester_Extension::OUTLINE_TESTER_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->addTag(Tester_Extension::OUTLINE_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(Tester_Extension::OUTLINE_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching scenario tester.
     */
    protected function load_event_dispatching_scenario_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Event_Dispatching_Scenario_Tester::class, [new Reference(Tester_Extension::SCENARIO_TESTER_ID), new Reference(self::DISPATCHER_ID), Scenario_Tested::BEFORE, Scenario_Tested::AFTER_SETUP, Scenario_Tested::BEFORE_TEARDOWN, Scenario_Tested::AFTER]);
        $definition->addTag(Tester_Extension::SCENARIO_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(Tester_Extension::SCENARIO_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching example tester.
     */
    protected function load_event_dispatching_example_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Event_Dispatching_Scenario_Tester::class, [new Reference(Tester_Extension::EXAMPLE_TESTER_ID), new Reference(self::DISPATCHER_ID), Example_Tested::BEFORE, Example_Tested::AFTER_SETUP, Example_Tested::BEFORE_TEARDOWN, Example_Tested::AFTER]);
        $definition->addTag(Tester_Extension::EXAMPLE_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(Tester_Extension::EXAMPLE_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching step tester.
     */
    protected function load_event_dispatching_step_tester(ContainerBuilder $container)
    {
        $definition = new Definition(Event_Dispatching_Step_Tester::class, [new Reference(Tester_Extension::STEP_TESTER_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->addTag(Tester_Extension::STEP_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(Tester_Extension::STEP_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * This method used in the past to load the Ticking_Step_Tester to work around
     * a bug with the scope of declare(ticks) in PHP < 7.1. Since we don't
     * support those PHP versions anymore loading the Ticking_Step_Tester is
     * no longer needed. This method is left here to prevent breaking BC.
     *
     * @todo Remove this method in next major
     *
     * @deprecated
     */
    protected function load_ticking_step_tester(ContainerBuilder $container)
    {
    }
}
