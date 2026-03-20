<?php

declare (strict_types=1);
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
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
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
    public function configure(Array_Node_Definition $builder)
    {
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_dispatcher($container);
        $this->load_repository($container);
        $this->load_hookable_testers($container);
    }
    public function process(Container_Builder $container)
    {
    }
    /**
     * Loads hook dispatcher.
     */
    protected function load_dispatcher(Container_Builder $container)
    {
        $definition = new Definition(Hook_Dispatcher::class, [new Reference(self::REPOSITORY_ID), new Reference(Call_Extension::CALL_CENTER_ID)]);
        $container->set_definition(self::DISPATCHER_ID, $definition);
    }
    /**
     * Loads hook repository.
     */
    protected function load_repository(Container_Builder $container)
    {
        $definition = new Definition(Hook_Repository::class, [new Reference(Environment_Extension::MANAGER_ID)]);
        $container->set_definition(self::REPOSITORY_ID, $definition);
    }
    /**
     * Loads hookable testers.
     */
    protected function load_hookable_testers(Container_Builder $container)
    {
        $definition = new Definition(Hookable_Suite_Tester::class, [new Reference(Tester_Extension::SUITE_TESTER_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->add_tag(Tester_Extension::SUITE_TESTER_WRAPPER_TAG, ['priority' => 9999]);
        $container->set_definition(Tester_Extension::SUITE_TESTER_WRAPPER_TAG . '.hookable', $definition);
    }
}