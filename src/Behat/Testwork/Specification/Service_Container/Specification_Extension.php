<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Specification\Service_Container;

use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Specification\Specification_Finder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Extends testwork with test specification services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Specification_Extension implements Extension
{
    /*
     * Available services
     */
    public const FINDER_ID = 'specifications.finder';
    /*
     * Available extension points
     */
    public const LOCATOR_TAG = 'specifications.locator';
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
        return 'specifications';
    }

    public function initialize(Extension_Manager $extension_manager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_finder($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->process_locators($container);
    }

    /**
     * Loads specification finder.
     */
    private function load_finder(ContainerBuilder $container): void
    {
        $definition = new Definition(Specification_Finder::class);
        $container->setDefinition(self::FINDER_ID, $definition);
    }

    /**
     * Processes specification locators.
     */
    private function process_locators(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::LOCATOR_TAG);
        $definition = $container->getDefinition(self::FINDER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_specification_locator', [$reference]);
        }
    }
}
