<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\Service_Container;

use Behat\Testwork\Environment\Environment_Manager;
use Behat\Testwork\Environment\Handler\Static_Environment_Handler;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Testwork test environment extension.
 *
 * Extends testwork with environment services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Environment_Extension implements Extension
{
    /*
     * Available services
     */
    public const MANAGER_ID = 'environment.manager';
    /*
     * Available extension points
     */
    public const HANDLER_TAG = 'environment.handler';
    public const READER_TAG = 'environment.reader';
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
        return 'environments';
    }

    public function initialize(Extension_Manager $extension_manager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_manager($container);
        $this->load_static_environment_handler($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->process_handlers($container);
        $this->process_readers($container);
    }

    /**
     * Loads environment manager.
     */
    private function load_manager(ContainerBuilder $container): void
    {
        $definition = new Definition(Environment_Manager::class);
        $container->setDefinition(self::MANAGER_ID, $definition);
    }

    /**
     * Loads static environments handler.
     */
    private function load_static_environment_handler(ContainerBuilder $container): void
    {
        $definition = new Definition(Static_Environment_Handler::class);
        $definition->addTag(self::HANDLER_TAG, ['priority' => 0]);
        $container->setDefinition(self::HANDLER_TAG . '.static', $definition);
    }

    /**
     * Processes all environment handlers.
     */
    private function process_handlers(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::HANDLER_TAG);
        $definition = $container->getDefinition(self::MANAGER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_environment_handler', [$reference]);
        }
    }

    /**
     * Processes all environment readers.
     */
    private function process_readers(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::READER_TAG);
        $definition = $container->getDefinition(self::MANAGER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_environment_reader', [$reference]);
        }
    }
}
