<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Helper_Container\Service_Container;

use Behat\Behat\Context\Service_Container\Context_Extension;
use Behat\Behat\Helper_Container\Argument\Services_Resolver_Factory;
use Behat\Behat\Helper_Container\Call\Filter\Services_Resolver;
use Behat\Behat\Helper_Container\Exception\Wrong_Services_Configuration_Exception;
use Behat\Testwork\Call\Service_Container\Call_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Behat helper container extension.
 *
 * Extends Behat with helper containers support.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Helper_Container_Extension implements Extension
{
    /*
     * Available extension points
     */
    public const HELPER_CONTAINER_TAG = 'helper_container.container';
    private Service_Processor $processor;

    /**
     * Initializes compiler pass.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }

    public function get_config_key(): string
    {
        return 'helper_container';
    }

    public function initialize(Extension_Manager $extension_manager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $definition = new Definition(Services_Resolver_Factory::class, [new Reference('service_container')]);
        $definition->addTag(Context_Extension::SUITE_SCOPED_RESOLVER_FACTORY_TAG, ['priority' => 0]);
        $container->setDefinition(Context_Extension::SUITE_SCOPED_RESOLVER_FACTORY_TAG . '.helper_container', $definition);
        $definition = new Definition(Services_Resolver::class);
        $definition->addTag(Call_Extension::CALL_FILTER_TAG, ['priority' => 0]);
        $container->setDefinition(Call_Extension::CALL_FILTER_TAG . '.helper_container', $definition);
    }

    public function process(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::HELPER_CONTAINER_TAG);
        foreach ($references as $reference) {
            if ($container->getDefinition((string) $reference)->isShared()) {
                throw new Wrong_Services_Configuration_Exception(sprintf('Container services must not be configured as shared, but `@%s` is.', $reference));
            }
        }
    }
}
