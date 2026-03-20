<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Output\Service_Container;

use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Event_Dispatcher\Service_Container\Event_Dispatcher_Extension;
use Behat\Testwork\Output\Cli\Output_Controller;
use Behat\Testwork\Output\Output_Manager;
use Behat\Testwork\Output\Service_Container\Formatter\Formatter_Factory;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Provides output management services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Output_Extension implements Extension
{
    /*
     * Available services
     */
    public const MANAGER_ID = 'output.manager';
    /*
     * Available extension points
     */
    public const FORMATTER_TAG = 'output.formatter';
    private readonly Service_Processor $processor;
    /**
     * Initializes extension.
     *
     * @param string                $defaultFormatter
     * @param FormatterFactory[] $factories
     */
    public function __construct(private $default_formatter, private array $factories, ?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }
    /**
     * Registers formatter factory.
     */
    public function register_formatter_factory(Formatter_Factory $factory): void
    {
        $this->factories[] = $factory;
    }
    public function get_config_key(): string
    {
        return 'formatters';
    }
    public function initialize(Extension_Manager $extension_manager): void
    {
    }
    public function configure(Array_Node_Definition $builder): void
    {
        $builder = $builder->default_value([$this->default_formatter => ['enabled' => true]])->use_attribute_as_key('name')->prototype('array')->before_normalization()->if_true(fn($a): bool => is_array($a) && !isset($a['enabled']))->then(fn($a): array => array_merge($a, ['enabled' => true]))->end();
        /** @var ArrayNodeDefinition $builder */
        $builder->use_attribute_as_key('name')->treat_true_like(['enabled' => true])->treat_null_like(['enabled' => true])->treat_false_like(['enabled' => false])->prototype('variable')->end();
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_output_controller($container);
        $this->load_formatters($container);
        $this->load_manager($container, $config);
    }
    public function process(Container_Builder $container): void
    {
        $this->process_formatters($container);
        $this->process_dynamically_registered_formatters($container);
    }
    /**
     * Loads output controller.
     */
    private function load_output_controller(Container_Builder $container): void
    {
        $definition = new Definition(Output_Controller::class, [new Reference(self::MANAGER_ID)]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 1000]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.output', $definition);
    }
    /**
     * Loads output manager.
     */
    private function load_manager(Container_Builder $container, array $formatters): void
    {
        $definition = new Definition(Output_Manager::class, [new Reference(Event_Dispatcher_Extension::DISPATCHER_ID)]);
        foreach ($formatters as $name => $parameters) {
            if ($parameters['enabled']) {
                $definition->add_method_call('enableFormatter', [$name]);
            } else {
                $definition->add_method_call('disableFormatter', [$name]);
            }
            unset($parameters['enabled']);
            $definition->add_method_call('setFormatterParameters', [$name, $parameters]);
        }
        $container->set_definition(self::MANAGER_ID, $definition);
    }
    /**
     * Loads default formatters using registered factories.
     */
    private function load_formatters(Container_Builder $container): void
    {
        foreach ($this->factories as $factory) {
            $factory->build_formatter($container);
        }
    }
    /**
     * Processes formatters using registered factories.
     */
    private function process_formatters(Container_Builder $container): void
    {
        foreach ($this->factories as $factory) {
            $factory->process_formatter($container);
        }
    }
    /**
     * Processes all available output formatters.
     */
    private function process_dynamically_registered_formatters(Container_Builder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::FORMATTER_TAG);
        $definition = $container->get_definition(self::MANAGER_ID);
        $previous_calls = $definition->get_method_calls();
        $definition->set_method_calls();
        foreach ($references as $reference) {
            $definition->add_method_call('registerFormatter', [$reference]);
        }
        foreach ($previous_calls as $call) {
            $definition->add_method_call($call[0], $call[1]);
        }
    }
}