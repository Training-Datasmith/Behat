<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Service_Container;

use Behat\Testwork\Call\Call_Center;
use Behat\Testwork\Call\Handler\Runtime_Call_Handler;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Provides call services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Call_Extension implements Extension
{
    /*
     * Available services
     */
    public const CALL_CENTER_ID = 'call.center';
    /*
     * Available extension points
     */
    public const CALL_FILTER_TAG = 'call.call_filter';
    public const CALL_HANDLER_TAG = 'call.call_handler';
    public const RESULT_FILTER_TAG = 'call.result_filter';
    public const EXCEPTION_HANDLER_TAG = 'call.exception_handler';
    private Service_Processor $processor;

    /**
     * Initializes extension.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }

    public function get_config_key(): string
    {
        return 'calls';
    }

    public function initialize(Extension_Manager $extension_manager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder->addDefaultsIfNotSet()->children()->scalarNode('error_reporting')->info('Call executor will catch exceptions matching this level')->defaultValue(error_reporting());
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_call_center($container);
        $this->load_call_handlers($container, $config['error_reporting']);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->process_call_filters($container);
        $this->process_call_handlers($container);
        $this->process_result_filters($container);
        $this->process_exception_handlers($container);
    }

    /**
     * Loads call center service.
     */
    private function load_call_center(ContainerBuilder $container): void
    {
        $definition = new Definition(Call_Center::class);
        $container->setDefinition(self::CALL_CENTER_ID, $definition);
    }

    /**
     * Loads prebuilt call handlers.
     */
    private function load_call_handlers(ContainerBuilder $container, $error_reporting): void
    {
        $definition = new Definition(Runtime_Call_Handler::class, [$error_reporting]);
        $definition->addTag(self::CALL_HANDLER_TAG, ['priority' => 50]);
        $container->setDefinition(self::CALL_HANDLER_TAG . '.runtime', $definition);
    }

    /**
     * Registers all call filters to the Call_Center.
     */
    private function process_call_filters(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, Call_Extension::CALL_FILTER_TAG);
        $definition = $container->getDefinition(Call_Extension::CALL_CENTER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_call_filter', [$reference]);
        }
    }

    /**
     * Registers all call handlers to the Call_Center.
     */
    private function process_call_handlers(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, Call_Extension::CALL_HANDLER_TAG);
        $definition = $container->getDefinition(Call_Extension::CALL_CENTER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_call_handler', [$reference]);
        }
    }

    /**
     * Registers all call result filters to the Call_Center.
     */
    private function process_result_filters(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, Call_Extension::RESULT_FILTER_TAG);
        $definition = $container->getDefinition(Call_Extension::CALL_CENTER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_result_filter', [$reference]);
        }
    }

    /**
     * Registers all exception handlers to the Call_Center.
     */
    private function process_exception_handlers(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, Call_Extension::EXCEPTION_HANDLER_TAG);
        $definition = $container->getDefinition(Call_Extension::CALL_CENTER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_exception_handler', [$reference]);
        }
    }
}
