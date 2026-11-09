<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Suite\Service_Container;

use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Suite\Cli\Initialization_Controller;
use Behat\Testwork\Suite\Cli\Suite_Controller;
use Behat\Testwork\Suite\Generator\Generic_Suite_Generator;
use Behat\Testwork\Suite\Suite_Bootstrapper;
use Behat\Testwork\Suite\Suite_Registry;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Extends testwork with suite-related services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Suite_Extension implements Extension
{
    /*
     * Available services
     */
    public const REGISTRY_ID = 'suite.registry';
    public const BOOTSTRAPPER_ID = 'suite.bootstrapper';
    /*
     * Available extension points
     */
    public const GENERATOR_TAG = 'suite.generator';
    public const SETUP_TAG = 'suite.setup';
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
        return 'suites';
    }
    public function initialize(Extension_Manager $extension_manager): void
    {
    }
    public function configure(Array_Node_Definition $builder): void
    {
        $builder = $builder->default_value(['default' => ['enabled' => true, 'type' => null, 'settings' => []]])->treat_null_like([])->treat_false_like([])->use_attribute_as_key('name')->normalize_keys(false)->prototype('array')->before_normalization()->if_true(fn($suite): bool => is_array($suite) && count($suite))->then(function (array $suite): array {
            $suite['settings'] ??= [];
            foreach ($suite as $key => $val) {
                $suite_keys = ['enabled', 'type', 'settings'];
                if (!in_array($key, $suite_keys)) {
                    $suite['settings'][$key] = $val;
                    unset($suite[$key]);
                }
            }
            return $suite;
        })->end();
        /** @var ArrayNodeDefinition $builder */
        $children_builder = $builder->normalize_keys(false)->add_defaults_if_not_set()->treat_true_like(['enabled' => true])->treat_null_like(['enabled' => true])->treat_false_like(['enabled' => false])->children();
        $children_builder->boolean_node('enabled')->info('Enables/disables suite')->default_true();
        $children_builder->scalar_node('type')->info('Specifies suite type')->default_value(null);
        $children_builder->array_node('settings')->info('Specifies suite extra settings')->default_value([])->use_attribute_as_key('name')->prototype('variable')->end();
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->set_suite_configurations($container, $config);
        $this->load_registry_controller($container);
        $this->load_bootstrap_controller($container);
        $this->load_registry($container);
        $this->load_bootstrapper($container);
        $this->load_generic_suite_generator($container);
    }
    public function process(Container_Builder $container): void
    {
        $this->process_generators($container);
        $this->process_setups($container);
    }
    /**
     * Generates and sets suites parameter to container.
     */
    private function set_suite_configurations(Container_Builder $container, array $suites): void
    {
        $configured_suites = [];
        foreach ($suites as $name => $config) {
            if (!$config['enabled']) {
                continue;
            }
            $configured_suites[$name] = ['type' => $config['type'], 'settings' => $config['settings']];
        }
        $container->set_parameter('suite.configurations', $configured_suites);
    }
    /**
     * Loads suite registry controller.
     */
    private function load_registry_controller(Container_Builder $container): void
    {
        $definition = new Definition(Suite_Controller::class, [new Reference(self::REGISTRY_ID), '%suite.configurations%']);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 1100]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.suite', $definition);
    }
    /**
     * Loads suite bootstrap controller.
     */
    private function load_bootstrap_controller(Container_Builder $container): void
    {
        $definition = new Definition(Initialization_Controller::class, [new Reference(self::REGISTRY_ID), new Reference(self::BOOTSTRAPPER_ID)]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 900]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.initialization', $definition);
    }
    /**
     * Loads suite registry.
     */
    private function load_registry(Container_Builder $container): void
    {
        $definition = new Definition(Suite_Registry::class);
        $container->set_definition(self::REGISTRY_ID, $definition);
    }
    /**
     * Loads suite bootstrapper.
     */
    private function load_bootstrapper(Container_Builder $container): void
    {
        $definition = new Definition(Suite_Bootstrapper::class);
        $container->set_definition(self::BOOTSTRAPPER_ID, $definition);
    }
    /**
     * Loads generic suite generator.
     */
    private function load_generic_suite_generator(Container_Builder $container): void
    {
        $container->set_parameter('suite.generic.default_settings', []);
        $definition = new Definition(Generic_Suite_Generator::class, ['%suite.generic.default_settings%']);
        $definition->add_tag(Suite_Extension::GENERATOR_TAG, ['priority' => 50]);
        $container->set_definition(Suite_Extension::GENERATOR_TAG . '.generic', $definition);
    }
    /**
     * Processes suite generators.
     */
    private function process_generators(Container_Builder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::GENERATOR_TAG);
        $definition = $container->get_definition(self::REGISTRY_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('registerSuiteGenerator', [$reference]);
        }
    }
    /**
     * Processes suite setups.
     */
    private function process_setups(Container_Builder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::SETUP_TAG);
        $definition = $container->get_definition(self::BOOTSTRAPPER_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('registerSuiteSetup', [$reference]);
        }
    }
}