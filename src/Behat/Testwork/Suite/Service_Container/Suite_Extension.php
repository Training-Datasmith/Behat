<?php

declare(strict_types=1);
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
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder = $builder->defaultValue(['default' => ['enabled' => true, 'type' => null, 'settings' => []]])->treatNullLike([])->treatFalseLike([])->useAttributeAsKey('name')->normalizeKeys(false)->prototype('array')->beforeNormalization()->ifTrue(fn ($suite): bool => is_array($suite) && count($suite))->then(function (array $suite): array {
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
        $children_builder = $builder->normalizeKeys(false)->addDefaultsIfNotSet()->treatTrueLike(['enabled' => true])->treatNullLike(['enabled' => true])->treatFalseLike(['enabled' => false])->children();
        $children_builder->booleanNode('enabled')->info('Enables/disables suite')->defaultTrue();
        $children_builder->scalarNode('type')->info('Specifies suite type')->defaultValue(null);
        $children_builder->arrayNode('settings')->info('Specifies suite extra settings')->defaultValue([])->useAttributeAsKey('name')->prototype('variable')->end();
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->set_suite_configurations($container, $config);
        $this->load_registry_controller($container);
        $this->load_bootstrap_controller($container);
        $this->load_registry($container);
        $this->load_bootstrapper($container);
        $this->load_generic_suite_generator($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->process_generators($container);
        $this->process_setups($container);
    }

    /**
     * Generates and sets suites parameter to container.
     */
    private function set_suite_configurations(ContainerBuilder $container, array $suites): void
    {
        $configured_suites = [];
        foreach ($suites as $name => $config) {
            if (!$config['enabled']) {
                continue;
            }
            $configured_suites[$name] = ['type' => $config['type'], 'settings' => $config['settings']];
        }
        $container->setParameter('suite.configurations', $configured_suites);
    }

    /**
     * Loads suite registry controller.
     */
    private function load_registry_controller(ContainerBuilder $container): void
    {
        $definition = new Definition(Suite_Controller::class, [new Reference(self::REGISTRY_ID), '%suite.configurations%']);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 1100]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.suite', $definition);
    }

    /**
     * Loads suite bootstrap controller.
     */
    private function load_bootstrap_controller(ContainerBuilder $container): void
    {
        $definition = new Definition(Initialization_Controller::class, [new Reference(self::REGISTRY_ID), new Reference(self::BOOTSTRAPPER_ID)]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 900]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.initialization', $definition);
    }

    /**
     * Loads suite registry.
     */
    private function load_registry(ContainerBuilder $container): void
    {
        $definition = new Definition(Suite_Registry::class);
        $container->setDefinition(self::REGISTRY_ID, $definition);
    }

    /**
     * Loads suite bootstrapper.
     */
    private function load_bootstrapper(ContainerBuilder $container): void
    {
        $definition = new Definition(Suite_Bootstrapper::class);
        $container->setDefinition(self::BOOTSTRAPPER_ID, $definition);
    }

    /**
     * Loads generic suite generator.
     */
    private function load_generic_suite_generator(ContainerBuilder $container): void
    {
        $container->setParameter('suite.generic.default_settings', []);
        $definition = new Definition(Generic_Suite_Generator::class, ['%suite.generic.default_settings%']);
        $definition->addTag(Suite_Extension::GENERATOR_TAG, ['priority' => 50]);
        $container->setDefinition(Suite_Extension::GENERATOR_TAG . '.generic', $definition);
    }

    /**
     * Processes suite generators.
     */
    private function process_generators(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::GENERATOR_TAG);
        $definition = $container->getDefinition(self::REGISTRY_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_suite_generator', [$reference]);
        }
    }

    /**
     * Processes suite setups.
     */
    private function process_setups(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::SETUP_TAG);
        $definition = $container->getDefinition(self::BOOTSTRAPPER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_suite_setup', [$reference]);
        }
    }
}
