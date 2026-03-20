<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Event_Dispatcher\Service_Container;

use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Event_Dispatcher\Cli\Sigint_Controller;
use Behat\Testwork\Event_Dispatcher\Tester\Event_Dispatching_Exercise;
use Behat\Testwork\Event_Dispatcher\Tester\Event_Dispatching_Suite_Tester;
use Behat\Testwork\Event_Dispatcher\Testwork_Event_Dispatcher;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Tester\Service_Container\Tester_Extension;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Provides event dispatching service.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Event_Dispatcher_Extension implements Extension
{
    /*
     * Available services
     */
    public const DISPATCHER_ID = 'event_dispatcher';
    /*
     * Available extension points
     */
    public const SUBSCRIBER_TAG = 'event_dispatcher.subscriber';
    protected \Behat\Testwork\Service_Container\Service_Processor $processor;
    /**
     * Initializes extension.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }
    public function get_config_key(): string
    {
        return 'events';
    }
    public function initialize(Extension_Manager $extension_manager)
    {
    }
    public function configure(Array_Node_Definition $builder)
    {
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_sigint_controller($container);
        $this->load_event_dispatcher($container);
        $this->load_event_dispatching_exercise($container);
        $this->load_event_dispatching_suite_tester($container);
    }
    public function process(Container_Builder $container): void
    {
        $this->process_subscribers($container);
    }
    /**
     * Loads sigint controller.
     */
    protected function load_sigint_controller(Container_Builder $container)
    {
        $definition = new Definition(Sigint_Controller::class, [new Reference(Event_Dispatcher_Extension::DISPATCHER_ID)]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 9999]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.sigint', $definition);
    }
    /**
     * Loads event dispatcher.
     */
    protected function load_event_dispatcher(Container_Builder $container)
    {
        $definition = new Definition(Testwork_Event_Dispatcher::class);
        $container->set_definition(self::DISPATCHER_ID, $definition);
    }
    /**
     * Loads event-dispatching exercise.
     */
    protected function load_event_dispatching_exercise(Container_Builder $container)
    {
        $definition = new Definition(Event_Dispatching_Exercise::class, [new Reference(Tester_Extension::EXERCISE_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->add_tag(Tester_Extension::EXERCISE_WRAPPER_TAG);
        $container->set_definition(Tester_Extension::EXERCISE_WRAPPER_TAG . '.event_dispatching', $definition);
    }
    /**
     * Loads event-dispatching suite tester.
     */
    protected function load_event_dispatching_suite_tester(Container_Builder $container)
    {
        $definition = new Definition(Event_Dispatching_Suite_Tester::class, [new Reference(Tester_Extension::SUITE_TESTER_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->add_tag(Tester_Extension::SUITE_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->set_definition(Tester_Extension::SUITE_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }
    /**
     * Registers all available event subscribers.
     */
    protected function process_subscribers(Container_Builder $container)
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::SUBSCRIBER_TAG);
        $definition = $container->get_definition(self::DISPATCHER_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('addSubscriber', [$reference]);
        }
    }
}