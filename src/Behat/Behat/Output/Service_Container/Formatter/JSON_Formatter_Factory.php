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

use Behat\Behat\Output\Node\Event_Listener\JSON\JSON_Duration_Listener;
use Behat\Behat\Output\Node\Event_Listener\JSON\JSON_Element_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Hook_Stats_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Scenario_Stats_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Step_Stats_Listener;
use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Behat\Output\Node\Printer\JSON\JSON_Exercise_Printer;
use Behat\Behat\Output\Node\Printer\JSON\JSON_Feature_Printer;
use Behat\Behat\Output\Node\Printer\JSON\JSON_Scenario_Printer;
use Behat\Behat\Output\Node\Printer\JSON\JSON_Setup_Printer;
use Behat\Behat\Output\Node\Printer\JSON\JSON_Step_Printer;
use Behat\Behat\Output\Node\Printer\JSON\JSON_Suite_Printer;
use Behat\Behat\Output\Statistics\Phase_Statistics;
use Behat\Config\Formatter\JSON_Formatter;
use Behat\Testwork\Exception\Service_Container\Exception_Extension;
use Behat\Testwork\Output\Node\Event_Listener\Chain_Event_Listener;
use Behat\Testwork\Output\Node_Event_Listening_Formatter;
use Behat\Testwork\Output\Printer\Factory\File_Output_Factory;
use Behat\Testwork\Output\Printer\JSON_Output_Printer;
use Behat\Testwork\Output\Service_Container\Formatter\Formatter_Factory;
use Behat\Testwork\Output\Service_Container\Output_Extension;
use Behat\Testwork\Path_Options\Service_Container\Path_Options_Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class JSON_Formatter_Factory implements Formatter_Factory
{
    public const ROOT_LISTENER_ID = 'output.node.listener.json';
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

    private function load_printer_helpers(ContainerBuilder $container): void
    {
        $definition = new Definition(Result_To_String_Converter::class);
        $container->setDefinition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);
    }

    private function load_core_printers(ContainerBuilder $container): void
    {
        $definition = new Definition(JSON_Exercise_Printer::class, [new Reference('output.json.statistics.exercise'), new Reference('output.node.listener.json.duration')]);
        $container->setDefinition('output.node.printer.json.exercise', $definition);
        $definition = new Definition(JSON_Suite_Printer::class, [new Reference('output.json.statistics.suite'), new Reference('output.node.listener.json.duration')]);
        $container->setDefinition('output.node.printer.json.suite', $definition);
        $definition = new Definition(JSON_Feature_Printer::class, [new Reference('output.json.statistics.feature'), new Reference('output.node.listener.json.duration'), new Reference(Path_Options_Extension::CONFIGURABLE_PATH_PRINTER_ID)]);
        $container->setDefinition('output.node.printer.json.feature', $definition);
        $definition = new Definition(JSON_Scenario_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference('output.node.listener.json.duration'), new Reference(Path_Options_Extension::CONFIGURABLE_PATH_PRINTER_ID)]);
        $container->setDefinition('output.node.printer.json.scenario', $definition);
        $definition = new Definition(JSON_Step_Printer::class, [new Reference(Exception_Extension::PRESENTER_ID)]);
        $container->setDefinition('output.node.printer.json.step', $definition);
        $definition = new Definition(JSON_Setup_Printer::class, [new Reference(Exception_Extension::PRESENTER_ID)]);
        $container->setDefinition('output.node.printer.json.setup', $definition);
    }

    private function load_root_node_listener(ContainerBuilder $container): void
    {
        $definition = new Definition(JSON_Duration_Listener::class);
        $container->setDefinition('output.node.listener.json.duration', $definition);
        $definition = new Definition(Chain_Event_Listener::class, [[new Reference('output.node.listener.json.duration'), new Definition(JSON_Element_Listener::class, [new Reference('output.node.printer.json.exercise'), new Reference('output.node.printer.json.suite'), new Reference('output.node.printer.json.feature'), new Reference('output.node.printer.json.scenario'), new Reference('output.node.printer.json.step'), new Reference('output.node.printer.json.setup')])]]);
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);
    }

    private function load_formatter(ContainerBuilder $container): void
    {
        $definition = new Definition(Phase_Statistics::class);
        $container->setDefinition('output.json.statistics.exercise', $definition);
        $definition = new Definition(Phase_Statistics::class);
        $container->setDefinition('output.json.statistics.suite', $definition);
        $definition = new Definition(Phase_Statistics::class);
        $container->setDefinition('output.json.statistics.feature', $definition);
        $definition = new Definition(Node_Event_Listening_Formatter::class, [JSON_Formatter::NAME, 'Outputs the failures in JSON files.', JSON_Formatter::defaults(), $this->create_output_printer_definition(), new Definition(Chain_Event_Listener::class, [[new Reference(self::ROOT_LISTENER_ID), new Definition(Scenario_Stats_Listener::class, [new Reference('output.json.statistics.exercise')]), new Definition(Step_Stats_Listener::class, [new Reference('output.json.statistics.exercise'), new Reference(Exception_Extension::PRESENTER_ID)]), new Definition(Hook_Stats_Listener::class, [new Reference('output.json.statistics.exercise'), new Reference(Exception_Extension::PRESENTER_ID)]), new Definition(Scenario_Stats_Listener::class, [new Reference('output.json.statistics.suite')]), new Definition(Step_Stats_Listener::class, [new Reference('output.json.statistics.suite'), new Reference(Exception_Extension::PRESENTER_ID)]), new Definition(Hook_Stats_Listener::class, [new Reference('output.json.statistics.suite'), new Reference(Exception_Extension::PRESENTER_ID)]), new Definition(Scenario_Stats_Listener::class, [new Reference('output.json.statistics.feature')]), new Definition(Step_Stats_Listener::class, [new Reference('output.json.statistics.feature'), new Reference(Exception_Extension::PRESENTER_ID)]), new Definition(Hook_Stats_Listener::class, [new Reference('output.json.statistics.feature'), new Reference(Exception_Extension::PRESENTER_ID)])]])]);
        $definition->addTag(Output_Extension::FORMATTER_TAG, ['priority' => 100]);
        $container->setDefinition(Output_Extension::FORMATTER_TAG . '.json', $definition);
    }

    private function create_output_printer_definition(): Definition
    {
        return new Definition(JSON_Output_Printer::class, [new Definition(File_Output_Factory::class)]);
    }
}
