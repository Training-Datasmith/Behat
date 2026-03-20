<?php

declare (strict_types=1);
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
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
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
    public function configure(Array_Node_Definition $builder): void
    {
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_command($container);
        $this->load_synthetic_services($container);
    }
    public function process(Container_Builder $container): void
    {
        $this->process_controllers($container);
    }
    /**
     * Loads application command.
     */
    private function load_command(Container_Builder $container): void
    {
        $definition = new Definition(Command::class, ['%cli.command.name%', []]);
        $definition->set_public(true);
        $container->set_definition(self::COMMAND_ID, $definition);
    }
    private function load_synthetic_services(Container_Builder $container): void
    {
        $container->register(self::INPUT_ID)->set_synthetic(true)->set_public(true);
        $container->register(self::OUTPUT_ID)->set_synthetic(true)->set_public(true);
    }
    /**
     * Processes all controllers in container.
     */
    private function process_controllers(Container_Builder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::CONTROLLER_TAG);
        $container->get_definition(self::COMMAND_ID)->replace_argument(1, $references);
    }
}