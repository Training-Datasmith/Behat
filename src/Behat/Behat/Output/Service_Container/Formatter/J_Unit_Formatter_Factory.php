<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Service_Container\Formatter;

use Behat\Behat\Output\Node\Event_Listener\J_Unit\J_Unit_Duration_Listener;
use Behat\Behat\Output\Node\Event_Listener\J_Unit\J_Unit_Feature_Element_Listener;
use Behat\Behat\Output\Node\Event_Listener\J_Unit\J_Unit_Outline_Store_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Hook_Stats_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Scenario_Stats_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Step_Stats_Listener;
use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Behat\Output\Node\Printer\J_Unit\J_Unit_Feature_Printer;
use Behat\Behat\Output\Node\Printer\J_Unit\J_Unit_Scenario_Printer;
use Behat\Behat\Output\Node\Printer\J_Unit\J_Unit_Setup_Printer;
use Behat\Behat\Output\Node\Printer\J_Unit\J_Unit_Step_Printer;
use Behat\Behat\Output\Node\Printer\J_Unit\J_Unit_Suite_Printer;
use Behat\Behat\Output\Statistics\Phase_Statistics;
use Behat\Config\Formatter\J_Unit_Formatter;
use Behat\Testwork\Exception\Service_Container\Exception_Extension;
use Behat\Testwork\Output\Node\Event_Listener\Chain_Event_Listener;
use Behat\Testwork\Output\Node_Event_Listening_Formatter;
use Behat\Testwork\Output\Printer\Factory\Filesystem_Output_Factory;
use Behat\Testwork\Output\Printer\J_Unit_Output_Printer;
use Behat\Testwork\Output\Service_Container\Formatter\Formatter_Factory;
use Behat\Testwork\Output\Service_Container\Output_Extension;
use Behat\Testwork\Path_Options\Service_Container\Path_Options_Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Behat junit formatter factory.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class J_Unit_Formatter_Factory implements Formatter_Factory
{
    /*
     * Available services
     */
    public const ROOT_LISTENER_ID = 'output.node.listener.junit';
    public const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';

    public function build_formatter(ContainerBuilder $container): void
    {
        $this->load_root_node_listener($container);
        $this->load_printer_helpers($container);
        $this->load_core_printers($container);
        $this->load_formatter($container);
    }

    public function process_formatter(ContainerBuilder $container): void
    {
    }

    /**
     * Loads printer helpers.
     */
    private function load_printer_helpers(ContainerBuilder $container): void
    {
        $definition = new Definition(Result_To_String_Converter::class);
        $container->setDefinition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);
    }

    /**
     * Loads the printers used to print the basic JUnit report.
     */
    private function load_core_printers(ContainerBuilder $container): void
    {
        $definition = new Definition(J_Unit_Suite_Printer::class, [new Reference('output.junit.statistics')]);
        $container->setDefinition('output.node.printer.junit.suite', $definition);
        $definition = new Definition(J_Unit_Feature_Printer::class, [new Reference('output.junit.statistics'), new Reference('output.node.listener.junit.duration'), new Reference(Path_Options_Extension::CONFIGURABLE_PATH_PRINTER_ID)]);
        $container->setDefinition('output.node.printer.junit.feature', $definition);
        $definition = new Definition(J_Unit_Scenario_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference('output.node.listener.junit.outline'), new Reference('output.node.listener.junit.duration'), new Reference(Path_Options_Extension::CONFIGURABLE_PATH_PRINTER_ID)]);
        $container->setDefinition('output.node.printer.junit.scenario', $definition);
        $definition = new Definition(J_Unit_Step_Printer::class, [new Reference(Exception_Extension::PRESENTER_ID)]);
        $container->setDefinition('output.node.printer.junit.step', $definition);
        $definition = new Definition(J_Unit_Setup_Printer::class, [new Reference(Exception_Extension::PRESENTER_ID)]);
        $container->setDefinition('output.node.printer.junit.setup', $definition);
    }

    /**
     * Loads the node listeners required for JUnit printers to work.
     */
    private function load_root_node_listener(ContainerBuilder $container): void
    {
        $definition = new Definition(J_Unit_Outline_Store_Listener::class, [new Reference('output.node.printer.junit.suite')]);
        $container->setDefinition('output.node.listener.junit.outline', $definition);
        $definition = new Definition(J_Unit_Duration_Listener::class);
        $container->setDefinition('output.node.listener.junit.duration', $definition);
        $definition = new Definition(Chain_Event_Listener::class, [[new Reference('output.node.listener.junit.duration'), new Definition(J_Unit_Feature_Element_Listener::class, [new Reference('output.node.printer.junit.feature'), new Reference('output.node.printer.junit.scenario'), new Reference('output.node.printer.junit.step'), new Reference('output.node.printer.junit.setup')]), new Reference('output.node.listener.junit.outline')]]);
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);
    }

    /**
     * Loads formatter itself.
     */
    private function load_formatter(ContainerBuilder $container): void
    {
        $definition = new Definition(Phase_Statistics::class);
        $container->setDefinition('output.junit.statistics', $definition);
        $definition = new Definition(Node_Event_Listening_Formatter::class, [J_Unit_Formatter::NAME, 'Outputs the failures in JUnit compatible files.', J_Unit_Formatter::defaults(), $this->create_output_printer_definition(), new Definition(Chain_Event_Listener::class, [[new Reference(self::ROOT_LISTENER_ID), new Definition(Scenario_Stats_Listener::class, [new Reference('output.junit.statistics')]), new Definition(Step_Stats_Listener::class, [new Reference('output.junit.statistics'), new Reference(Exception_Extension::PRESENTER_ID)]), new Definition(Hook_Stats_Listener::class, [new Reference('output.junit.statistics'), new Reference(Exception_Extension::PRESENTER_ID)])]])]);
        $definition->addTag(Output_Extension::FORMATTER_TAG, ['priority' => 100]);
        $container->setDefinition(Output_Extension::FORMATTER_TAG . '.junit', $definition);
    }

    /**
     * Creates output printer definition.
     */
    private function create_output_printer_definition(): Definition
    {
        return new Definition(J_Unit_Output_Printer::class, [new Definition(Filesystem_Output_Factory::class)]);
    }
}
