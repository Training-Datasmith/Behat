<?php

declare (strict_types=1);
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
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
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
    public function configure(Array_Node_Definition $builder): void
    {
        $builder->add_defaults_if_not_set()->children()->scalar_node('error_reporting')->info('Call executor will catch exceptions matching this level')->default_value(error_reporting());
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_call_center($container);
        $this->load_call_handlers($container, $config['error_reporting']);
    }
    public function process(Container_Builder $container): void
    {
        $this->process_call_filters($container);
        $this->process_call_handlers($container);
        $this->process_result_filters($container);
        $this->process_exception_handlers($container);
    }
    /**
     * Loads call center service.
     */
    private function load_call_center(Container_Builder $container): void
    {
        $definition = new Definition(Call_Center::class);
        $container->set_definition(self::CALL_CENTER_ID, $definition);
    }
    /**
     * Loads prebuilt call handlers.
     *
     * @param int $errorReporting
     */
    private function load_call_handlers(Container_Builder $container, $error_reporting): void
    {
        $definition = new Definition(Runtime_Call_Handler::class, [$error_reporting]);
        $definition->add_tag(self::CALL_HANDLER_TAG, ['priority' => 50]);
        $container->set_definition(self::CALL_HANDLER_TAG . '.runtime', $definition);
    }
    /**
     * Registers all call filters to the CallCenter.
     */
    private function process_call_filters(Container_Builder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, Call_Extension::CALL_FILTER_TAG);
        $definition = $container->get_definition(Call_Extension::CALL_CENTER_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('registerCallFilter', [$reference]);
        }
    }
    /**
     * Registers all call handlers to the CallCenter.
     */
    private function process_call_handlers(Container_Builder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, Call_Extension::CALL_HANDLER_TAG);
        $definition = $container->get_definition(Call_Extension::CALL_CENTER_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('registerCallHandler', [$reference]);
        }
    }
    /**
     * Registers all call result filters to the CallCenter.
     */
    private function process_result_filters(Container_Builder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, Call_Extension::RESULT_FILTER_TAG);
        $definition = $container->get_definition(Call_Extension::CALL_CENTER_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('registerResultFilter', [$reference]);
        }
    }
    /**
     * Registers all exception handlers to the CallCenter.
     */
    private function process_exception_handlers(Container_Builder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, Call_Extension::EXCEPTION_HANDLER_TAG);
        $definition = $container->get_definition(Call_Extension::CALL_CENTER_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('registerExceptionHandler', [$reference]);
        }
    }
}