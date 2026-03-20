<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Autoloader\Service_Container;

use Behat\Testwork\Autoloader\Cli\Autoloader_Controller;
use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Composer\Autoload\Class_Loader;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Extends Testwork with class-loading services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Autoloader_Extension implements Extension
{
    /*
     * Available services
     */
    public const CLASS_LOADER_ID = 'class_loader';
    /**
     * Initializes extension.
     */
    public function __construct(private array $default_paths = [])
    {
    }
    /**
     * Returns the extension config key.
     */
    public function get_config_key(): string
    {
        return 'autoload';
    }
    public function initialize(Extension_Manager $extension_manager): void
    {
    }
    public function configure(Array_Node_Definition $builder): void
    {
        $builder = $builder->before_normalization()->if_string()->then(fn($path): array => ['' => $path])->end()->default_value($this->default_paths)->treat_true_like($this->default_paths)->treat_null_like([])->treat_false_like([]);
        /** @var ArrayNodeDefinition $builder */
        $builder->prototype('scalar')->end();
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_autoloader($container);
        $this->load_controller($container);
        $this->set_loader_prefixes($container, $config);
    }
    public function process(Container_Builder $container): void
    {
        $this->process_loader_prefixes($container);
    }
    /**
     * Loads Symfony2 autoloader.
     */
    private function load_autoloader(Container_Builder $container): void
    {
        $definition = new Definition(Class_Loader::class);
        $container->set_definition(self::CLASS_LOADER_ID, $definition);
    }
    /**
     * Loads controller.
     */
    private function load_controller(Container_Builder $container): void
    {
        $definition = new Definition(Autoloader_Controller::class, [new Reference(self::CLASS_LOADER_ID)]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 9999]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.autoloader', $definition);
    }
    /**
     * Sets provided prefixes to container.
     */
    private function set_loader_prefixes(Container_Builder $container, array $prefixes): void
    {
        $container->set_parameter('class_loader.prefixes', $prefixes);
    }
    /**
     * Processes container loader prefixes.
     */
    private function process_loader_prefixes(Container_Builder $container): void
    {
        $loader_definition = $container->get_definition(self::CLASS_LOADER_ID);
        $prefixes = $container->get_parameter('class_loader.prefixes');
        foreach ($prefixes as $prefix => $path) {
            $loader_definition->add_method_call('add', [$prefix, $path]);
        }
    }
}