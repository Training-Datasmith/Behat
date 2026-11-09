<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Service_Container;

use Behat\Behat\Tester\Service_Container\Tester_Extension;
use Behat\Testwork\Call\Service_Container\Call_Extension;
use Behat\Testwork\Environment\Service_Container\Environment_Extension;
use Behat\Testwork\Hook\Hook_Dispatcher;
use Behat\Testwork\Hook\Hook_Repository;
use Behat\Testwork\Hook\Tester\Hookable_Suite_Tester;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides test hooking services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Hook_Extension implements Extension
{
    /*
     * Available services
     */
    public const DISPATCHER_ID = 'hook.dispatcher';
    public const REPOSITORY_ID = 'hook.repository';

    public function get_config_key(): string
    {
        return 'hooks';
    }

    public function initialize(Extension_Manager $extension_manager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_dispatcher($container);
        $this->load_repository($container);
        $this->load_hookable_testers($container);
    }

    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Loads hook dispatcher.
     */
    protected function load_dispatcher(ContainerBuilder $container)
    {
        $definition = new Definition(Hook_Dispatcher::class, [new Reference(self::REPOSITORY_ID), new Reference(Call_Extension::CALL_CENTER_ID)]);
        $container->setDefinition(self::DISPATCHER_ID, $definition);
    }

    /**
     * Loads hook repository.
     */
    protected function load_repository(ContainerBuilder $container)
    {
        $definition = new Definition(Hook_Repository::class, [new Reference(Environment_Extension::MANAGER_ID)]);
        $container->setDefinition(self::REPOSITORY_ID, $definition);
    }

    /**
     * Loads hookable testers.
     */
    protected function load_hookable_testers(ContainerBuilder $container)
    {
        $definition = new Definition(Hookable_Suite_Tester::class, [new Reference(Tester_Extension::SUITE_TESTER_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->addTag(Tester_Extension::SUITE_TESTER_WRAPPER_TAG, ['priority' => 9999]);
        $container->setDefinition(Tester_Extension::SUITE_TESTER_WRAPPER_TAG . '.hookable', $definition);
    }
}
