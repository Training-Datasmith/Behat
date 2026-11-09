<?php

declare(strict_types=1);
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
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder = $builder->addDefaultsIfNotSet()->children()->scalarNode('print_absolute_paths')->defaultFalse()->end();
        /** @var NodeBuilder $builder */
        $builder->scalarNode('editor_url')->defaultNull()->end();
        $builder->arrayNode('remove_prefix')->scalarPrototype()->defaultValue([])->end();
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_configurable_path_printer($container, $config['print_absolute_paths'], $config['editor_url'] ?? null, $config['remove_prefix']);
        $this->load_path_options_controller($container);
    }

    public function process(ContainerBuilder $container): void
    {
    }

    private function load_configurable_path_printer(ContainerBuilder $container, bool $print_absolute_paths, ?string $editor_url, array $remove_prefix = []): void
    {
        $definition = new Definition(Configurable_Path_Printer::class, ['%paths.base%', $print_absolute_paths, $editor_url, $remove_prefix]);
        $container->setDefinition(self::CONFIGURABLE_PATH_PRINTER_ID, $definition);
    }

    private function load_path_options_controller(ContainerBuilder $container): void
    {
        $definition = new Definition(Path_Options_Controller::class, [new Reference(self::CONFIGURABLE_PATH_PRINTER_ID)]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 1000]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.path', $definition);
    }
}
