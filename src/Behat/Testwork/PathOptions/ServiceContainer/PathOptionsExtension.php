<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Path_Options\Service_Container;

use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Path_Options\Cli\Path_Options_Controller;
use Behat\Testwork\Path_Options\Printer\Configurable_Path_Printer;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Config\Definition\Builder\Node_Builder;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Provides management of paths in the output.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Path_Options_Extension implements Extension
{
    public const CONFIGURABLE_PATH_PRINTER_ID = 'configurable.path.printer';
    public function get_config_key(): string
    {
        return 'path_options';
    }
    public function initialize(Extension_Manager $extension_manager): void
    {
    }
    public function configure(Array_Node_Definition $builder): void
    {
        $builder = $builder->add_defaults_if_not_set()->children()->scalar_node('print_absolute_paths')->default_false()->end();
        /** @var NodeBuilder $builder */
        $builder->scalar_node('editor_url')->default_null()->end();
        $builder->array_node('remove_prefix')->scalar_prototype()->default_value([])->end();
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_configurable_path_printer($container, $config['print_absolute_paths'], $config['editor_url'] ?? null, $config['remove_prefix']);
        $this->load_path_options_controller($container);
    }
    public function process(Container_Builder $container): void
    {
    }
    /**
     * @param string[] $removePrefix
     */
    private function load_configurable_path_printer(Container_Builder $container, bool $print_absolute_paths, ?string $editor_url, array $remove_prefix = []): void
    {
        $definition = new Definition(Configurable_Path_Printer::class, ['%paths.base%', $print_absolute_paths, $editor_url, $remove_prefix]);
        $container->set_definition(self::CONFIGURABLE_PATH_PRINTER_ID, $definition);
    }
    private function load_path_options_controller(Container_Builder $container): void
    {
        $definition = new Definition(Path_Options_Controller::class, [new Reference(self::CONFIGURABLE_PATH_PRINTER_ID)]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 1000]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.path', $definition);
    }
}