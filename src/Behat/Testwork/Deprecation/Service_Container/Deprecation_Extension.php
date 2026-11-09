<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Deprecation\Service_Container;

use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Deprecation\Cli\Deprecation_Controller;
use Behat\Testwork\Deprecation\Deprecation_Collector;
use Behat\Testwork\Deprecation\Deprecation_Printer;
use Behat\Testwork\Event_Dispatcher\Service_Container\Event_Dispatcher_Extension;
use Behat\Testwork\Path_Options\Service_Container\Path_Options_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Tester\Service_Container\Tester_Extension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides deprecation collection and reporting services.
 */
final class Deprecation_Extension implements Extension
{
    public const COLLECTOR_ID = 'deprecation.collector';
    public const PRINTER_ID = 'deprecation.printer';
    public const CONTROLLER_ID = 'deprecation.controller';

    public function get_config_key(): string
    {
        return 'deprecations';
    }

    public function initialize(Extension_Manager $extension_manager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder->addDefaultsIfNotSet()->children()->booleanNode('print_behat_deprecations')->defaultFalse()->end()->booleanNode('fail_on_behat_deprecations')->defaultFalse()->end()->end();
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_collector($container);
        $this->load_printer($container);
        $this->load_controller($container, $config['print_behat_deprecations'], $config['fail_on_behat_deprecations']);
    }

    public function process(ContainerBuilder $container): void
    {
    }

    private function load_collector(ContainerBuilder $container): void
    {
        $definition = new Definition(Deprecation_Collector::class);
        $definition->setFactory([Deprecation_Collector::class, 'get_instance']);
        $container->setDefinition(self::COLLECTOR_ID, $definition);
    }

    private function load_printer(ContainerBuilder $container): void
    {
        $definition = new Definition(Deprecation_Printer::class, [new Reference(self::COLLECTOR_ID), new Reference(Path_Options_Extension::CONFIGURABLE_PATH_PRINTER_ID)]);
        $container->setDefinition(self::PRINTER_ID, $definition);
    }

    private function load_controller(ContainerBuilder $container, bool $print_deprecations, bool $fail_on_deprecations): void
    {
        $definition = new Definition(Deprecation_Controller::class, [new Reference(self::COLLECTOR_ID), new Reference(self::PRINTER_ID), new Reference(Tester_Extension::RESULT_INTERPRETER_ID), $print_deprecations || $fail_on_deprecations, $fail_on_deprecations]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 9999]);
        $definition->addTag(Event_Dispatcher_Extension::SUBSCRIBER_TAG);
        $container->setDefinition(self::CONTROLLER_ID, $definition);
    }
}
