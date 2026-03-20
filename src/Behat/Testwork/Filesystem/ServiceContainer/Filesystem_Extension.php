<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Filesystem\Service_Container;

use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Filesystem\Console_Filesystem_Logger;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Provides filesystem services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Filesystem_Extension implements Extension
{
    /*
     * Available services
     */
    public const LOGGER_ID = 'filesystem.logger';
    public function get_config_key(): string
    {
        return 'filesystem';
    }
    public function initialize(Extension_Manager $extension_manager): void
    {
    }
    public function configure(Array_Node_Definition $builder): void
    {
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_filesystem_logger($container);
    }
    public function process(Container_Builder $container): void
    {
    }
    /**
     * Loads filesystem logger.
     */
    private function load_filesystem_logger(Container_Builder $container): void
    {
        $definition = new Definition(Console_Filesystem_Logger::class, ['%paths.base%', new Reference(Cli_Extension::OUTPUT_ID)]);
        $container->set_definition(self::LOGGER_ID, $definition);
    }
}