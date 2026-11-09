<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Cli\Service_Container;

use Behat\Testwork\Cli\Command;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Provides console services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Cli_Extension implements Extension
{
    /*
     * Available services
     */
    public const COMMAND_ID = 'cli.command';
    public const INPUT_ID = 'cli.input';
    public const OUTPUT_ID = 'cli.output';
    /*
     * Available extension points
     */
    public const CONTROLLER_TAG = 'cli.controller';
    private Service_Processor $processor;

    /**
     * Initializes extension.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }

    /**
     * Returns the extension config key.
     */
    public function get_config_key(): string
    {
        return 'cli';
    }

    public function initialize(Extension_Manager $extension_manager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_command($container);
        $this->load_synthetic_services($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->process_controllers($container);
    }

    /**
     * Loads application command.
     */
    private function load_command(ContainerBuilder $container): void
    {
        $definition = new Definition(Command::class, ['%cli.command.name%', []]);
        $definition->setPublic(true);
        $container->setDefinition(self::COMMAND_ID, $definition);
    }

    private function load_synthetic_services(ContainerBuilder $container): void
    {
        $container->register(self::INPUT_ID)->setSynthetic(true)->setPublic(true);
        $container->register(self::OUTPUT_ID)->setSynthetic(true)->setPublic(true);
    }

    /**
     * Processes all controllers in container.
     */
    private function process_controllers(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::CONTROLLER_TAG);
        $container->getDefinition(self::COMMAND_ID)->replaceArgument(1, $references);
    }
}
