<?php

declare(strict_types=1);
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
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(Cli_Extension::CONTROLLER_TAG . '.order');
        $references = $this->processor->find_and_sort_tagged_services($container, self::ORDERER_TAG);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_orderer', [$reference]);
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
    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    /**
     * Loads extension services into temporary container.
     */
    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_order_controller($container);
        $this->load_ordered_exercise($container);
        $this->load_default_orderers($container);
    }

    /**
     * Loads order controller.
     */
    private function load_order_controller(ContainerBuilder $container): void
    {
        $definition = new Definition(Order_Controller::class, [new Reference(Event_Dispatcher_Extension::DISPATCHER_ID), new Reference(Tester_Extension::EXERCISE_WRAPPER_TAG . '.ordering')]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 250]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.order', $definition);
    }

    /**
     * Loads exercise wrapper that enables ordering.
     */
    private function load_ordered_exercise(ContainerBuilder $container): void
    {
        $definition = new Definition(Ordered_Exercise::class, [new Reference(Tester_Extension::EXERCISE_ID)]);
        $definition->addTag(Tester_Extension::EXERCISE_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(Tester_Extension::EXERCISE_WRAPPER_TAG . '.ordering', $definition);
    }

    /**
     * Defines default orderers.
     */
    private function load_default_orderers(ContainerBuilder $container): void
    {
        $definition = new Definition(Reverse_Orderer::class);
        $definition->addTag(self::ORDERER_TAG, ['priority' => -9999]);
        $container->setDefinition(Tester_Extension::EXERCISE_WRAPPER_TAG . '.ordering.reverse', $definition);
        $definition = new Definition(Random_Orderer::class);
        $definition->addTag(self::ORDERER_TAG, ['priority' => -9999]);
        $container->setDefinition(Tester_Extension::EXERCISE_WRAPPER_TAG . '.ordering.random', $definition);
    }
}
