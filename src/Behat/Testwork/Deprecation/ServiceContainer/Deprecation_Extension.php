<?php

declare (strict_types=1);
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
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
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
    public function configure(Array_Node_Definition $builder): void
    {
        $builder->add_defaults_if_not_set()->children()->boolean_node('print_behat_deprecations')->default_false()->end()->boolean_node('fail_on_behat_deprecations')->default_false()->end()->end();
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_collector($container);
        $this->load_printer($container);
        $this->load_controller($container, $config['print_behat_deprecations'], $config['fail_on_behat_deprecations']);
    }
    public function process(Container_Builder $container): void
    {
    }
    private function load_collector(Container_Builder $container): void
    {
        $definition = new Definition(Deprecation_Collector::class);
        $definition->set_factory(Deprecation_Collector::get_instance(...));
        $container->set_definition(self::COLLECTOR_ID, $definition);
    }
    private function load_printer(Container_Builder $container): void
    {
        $definition = new Definition(Deprecation_Printer::class, [new Reference(self::COLLECTOR_ID), new Reference(Path_Options_Extension::CONFIGURABLE_PATH_PRINTER_ID)]);
        $container->set_definition(self::PRINTER_ID, $definition);
    }
    private function load_controller(Container_Builder $container, bool $print_deprecations, bool $fail_on_deprecations): void
    {
        $definition = new Definition(Deprecation_Controller::class, [new Reference(self::COLLECTOR_ID), new Reference(self::PRINTER_ID), new Reference(Tester_Extension::RESULT_INTERPRETER_ID), $print_deprecations || $fail_on_deprecations, $fail_on_deprecations]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 9999]);
        $definition->add_tag(Event_Dispatcher_Extension::SUBSCRIBER_TAG);
        $container->set_definition(self::CONTROLLER_ID, $definition);
    }
}