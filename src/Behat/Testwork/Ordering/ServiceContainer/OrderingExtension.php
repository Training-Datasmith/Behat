<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Ordering\Service_Container;

use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Event_Dispatcher\Service_Container\Event_Dispatcher_Extension;
use Behat\Testwork\Ordering\Cli\Order_Controller;
use Behat\Testwork\Ordering\Ordered_Exercise;
use Behat\Testwork\Ordering\Orderer\Random_Orderer;
use Behat\Testwork\Ordering\Orderer\Reverse_Orderer;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Tester\Service_Container\Tester_Extension;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Provides specification execution ordering.
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final readonly class Ordering_Extension implements Extension
{
    public const ORDERER_TAG = 'tester.orderer';
    private Service_Processor $processor;
    /**
     * Initializes extension.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @api
     */
    public function process(Container_Builder $container): void
    {
        $definition = $container->get_definition(Cli_Extension::CONTROLLER_TAG . '.order');
        $references = $this->processor->find_and_sort_tagged_services($container, self::ORDERER_TAG);
        foreach ($references as $reference) {
            $definition->add_method_call('registerOrderer', [$reference]);
        }
    }
    /**
     * Returns the extension config key.
     */
    public function get_config_key(): string
    {
        return 'ordering';
    }
    /**
     * Initializes other extensions.
     *
     * This method is called immediately after all extensions are activated but
     * before any extension `configure()` method is called. This allows extensions
     * to hook into the configuration of other extensions providing such an
     * extension point.
     */
    public function initialize(Extension_Manager $extension_manager): void
    {
    }
    /**
     * Setups configuration for the extension.
     */
    public function configure(Array_Node_Definition $builder): void
    {
    }
    /**
     * Loads extension services into temporary container.
     */
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_order_controller($container);
        $this->load_ordered_exercise($container);
        $this->load_default_orderers($container);
    }
    /**
     * Loads order controller.
     */
    private function load_order_controller(Container_Builder $container): void
    {
        $definition = new Definition(Order_Controller::class, [new Reference(Event_Dispatcher_Extension::DISPATCHER_ID), new Reference(Tester_Extension::EXERCISE_WRAPPER_TAG . '.ordering')]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 250]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.order', $definition);
    }
    /**
     * Loads exercise wrapper that enables ordering.
     */
    private function load_ordered_exercise(Container_Builder $container): void
    {
        $definition = new Definition(Ordered_Exercise::class, [new Reference(Tester_Extension::EXERCISE_ID)]);
        $definition->add_tag(Tester_Extension::EXERCISE_WRAPPER_TAG, ['priority' => -9999]);
        $container->set_definition(Tester_Extension::EXERCISE_WRAPPER_TAG . '.ordering', $definition);
    }
    /**
     * Defines default orderers.
     */
    private function load_default_orderers(Container_Builder $container): void
    {
        $definition = new Definition(Reverse_Orderer::class);
        $definition->add_tag(self::ORDERER_TAG, ['priority' => -9999]);
        $container->set_definition(Tester_Extension::EXERCISE_WRAPPER_TAG . '.ordering.reverse', $definition);
        $definition = new Definition(Random_Orderer::class);
        $definition->add_tag(self::ORDERER_TAG, ['priority' => -9999]);
        $container->set_definition(Tester_Extension::EXERCISE_WRAPPER_TAG . '.ordering.random', $definition);
    }
}