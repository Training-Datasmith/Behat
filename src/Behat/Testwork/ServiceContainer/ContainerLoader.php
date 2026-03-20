<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Service_Container;

use Behat\Testwork\Service_Container\Configuration\Configuration_Tree;
use Behat\Testwork\Service_Container\Exception\Extension_Exception;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Parameter_Bag\Parameter_Bag;
/**
 * Loads Symfony DI container with testwork extension services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Container_Loader
{
    private Configuration_Tree $configuration;
    private Processor $processor;
    /**
     * Initialize extension.
     */
    public function __construct(private Extension_Manager $extension_manager, ?Configuration_Tree $configuration = null, ?Processor $processor = null)
    {
        $this->configuration = $configuration ?: new Configuration_Tree();
        $this->processor = $processor ?: new Processor();
    }
    /**
     * Loads container extension.
     */
    public function load(Container_Builder $container, array $configs): void
    {
        $configs = $this->initialize_extensions($container, $configs);
        $config = $this->process_config($configs);
        $this->load_extensions($container, $config);
    }
    /**
     * Processes config against extensions.
     */
    private function process_config(array $configs): array
    {
        $tree = $this->configuration->get_config_tree($this->extension_manager->get_extensions());
        return $this->processor->process($tree, $configs);
    }
    /**
     * Initializes extensions using provided config.
     */
    private function initialize_extensions(Container_Builder $container, array $configs): array
    {
        $extensions = [];
        foreach ($configs as $i => $config) {
            $extensions[$i] = [];
            if (array_key_exists('extensions', $config)) {
                if (null === $config['extensions']) {
                    $extensions = [];
                    // Disable all extensions
                    break;
                }
                foreach ($config['extensions'] as $extension_locator => $extension_config) {
                    $extensions[$i][$extension_locator] = $extension_config;
                }
            }
        }
        foreach (array_keys($configs) as $i) {
            unset($configs[$i]['extensions']);
        }
        foreach ($extensions as $i => $extension_configs) {
            foreach ($extension_configs as $extension_locator => $extension_config) {
                $extension = $this->extension_manager->activate_extension($extension_locator);
                $configs[$i][$extension->get_config_key()] = $extension_config;
            }
        }
        $this->extension_manager->initialize_extensions();
        $container->set_parameter('extensions', $this->extension_manager->get_extension_classes());
        return $configs;
    }
    /**
     * Loads all extensions into container using provided config.
     *
     * @throws ExtensionException
     */
    private function load_extensions(Container_Builder $container, array $config): void
    {
        // Load default extensions first
        foreach ($this->extension_manager->get_extensions() as $extension) {
            $extension_config = [];
            if (isset($config[$extension->get_config_key()])) {
                $extension_config = $config[$extension->get_config_key()];
                unset($config[$extension->get_config_key()]);
            }
            $this->load_extension($container, $extension, $extension_config);
        }
        // Load activated extensions
        foreach ($config as $extension_config_key => $extension_config) {
            if (null === $extension = $this->extension_manager->get_extension($extension_config_key)) {
                throw new Extension_Exception(sprintf('None of the activated extensions use `%s` config section.', $extension_config_key), $extension_config_key);
            }
            $this->load_extension($container, $extension, $extension_config);
        }
    }
    /**
     * Loads extension configuration.
     */
    private function load_extension(Container_Builder $container, Extension $extension, array $config): void
    {
        $temp_container = new Container_Builder(new Parameter_Bag(['paths.base' => $container->get_parameter('paths.base'), 'extensions' => $container->get_parameter('extensions')]));
        $temp_container->add_object_resource($extension);
        $extension->load($container, $config);
        $container->merge($temp_container);
        $container->add_compiler_pass($extension);
    }
}