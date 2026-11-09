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

use Behat\Behat\Output\Node\Event_Listener\AST\Step_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Hook_Stats_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Scenario_Stats_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Statistics_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Step_Stats_Listener;
use Behat\Behat\Output\Node\Printer\Counter_Printer;
use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Behat\Output\Node\Printer\List_Printer;
use Behat\Behat\Output\Node\Printer\Progress\Progress_Statistics_Printer;
use Behat\Behat\Output\Node\Printer\Progress\Progress_Step_Printer;
use Behat\Behat\Output\Printer\Console_Output_Factory;
use Behat\Behat\Output\Statistics\Total_Statistics;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Testwork\Exception\Service_Container\Exception_Extension;
use Behat\Testwork\Output\Node\Event_Listener\Chain_Event_Listener;
use Behat\Testwork\Output\Node_Event_Listening_Formatter;
use Behat\Testwork\Output\Printer\Stream_Output_Printer;
use Behat\Testwork\Output\Service_Container\Formatter\Formatter_Factory;
use Behat\Testwork\Output\Service_Container\Output_Extension;
use Behat\Testwork\Path_Options\Service_Container\Path_Options_Extension;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Translator\Service_Container\Translator_Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Behat progress formatter factory.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Progress_Formatter_Factory implements Formatter_Factory
{
    private readonly Service_Processor $processor;
    /*
     * Available services
     */
    public const ROOT_LISTENER_ID = 'output.node.listener.progress';
    public const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';
    /*
     * Available extension points
     */
    public const ROOT_LISTENER_WRAPPER_TAG = 'output.node.listener.progress.wrapper';

    /**
     * Initializes extension.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }

    public function build_formatter(ContainerBuilder $container): void
    {
        $this->load_root_node_listener($container);
        $this->load_core_printers($container);
        $this->load_printer_helpers($container);
        $this->load_formatter($container);
    }

    public function process_formatter(ContainerBuilder $container): void
    {
        $this->process_listener_wrappers($container);
    }

    /**
     * Loads progress formatter node event listener.
     */
    protected function load_root_node_listener(ContainerBuilder $container)
    {
        $definition = new Definition(Step_Listener::class, [new Reference('output.node.printer.progress.step')]);
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);
    }

    /**
     * Loads feature, scenario and step printers.
     */
    protected function load_core_printers(ContainerBuilder $container)
    {
        $definition = new Definition(Counter_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference(Translator_Extension::TRANSLATOR_ID)]);
        $container->setDefinition('output.node.printer.counter', $definition);
        $definition = new Definition(List_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference(Exception_Extension::PRESENTER_ID), new Reference(Translator_Extension::TRANSLATOR_ID), '%paths.base%', new Reference(Path_Options_Extension::CONFIGURABLE_PATH_PRINTER_ID)]);
        $container->setDefinition('output.node.printer.list', $definition);
        $definition = new Definition(Progress_Step_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID)]);
        $container->setDefinition('output.node.printer.progress.step', $definition);
        $definition = new Definition(Progress_Statistics_Printer::class, [new Reference('output.node.printer.counter'), new Reference('output.node.printer.list')]);
        $container->setDefinition('output.node.printer.progress.statistics', $definition);
    }

    /**
     * Loads printer helpers.
     */
    protected function load_printer_helpers(ContainerBuilder $container)
    {
        $definition = new Definition(Result_To_String_Converter::class);
        $container->setDefinition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);
    }

    /**
     * Loads formatter itself.
     */
    protected function load_formatter(ContainerBuilder $container)
    {
        $definition = new Definition(Total_Statistics::class);
        $container->setDefinition('output.progress.statistics', $definition);
        $definition = new Definition(Node_Event_Listening_Formatter::class, [Progress_Formatter::NAME, 'Prints one character per step.', Progress_Formatter::defaults(), $this->create_output_printer_definition(), new Definition(Chain_Event_Listener::class, [[new Reference(self::ROOT_LISTENER_ID), new Definition(Statistics_Listener::class, [new Reference('output.progress.statistics'), new Reference('output.node.printer.progress.statistics')]), new Definition(Scenario_Stats_Listener::class, [new Reference('output.progress.statistics')]), new Definition(Step_Stats_Listener::class, [new Reference('output.progress.statistics'), new Reference(Exception_Extension::PRESENTER_ID)]), new Definition(Hook_Stats_Listener::class, [new Reference('output.progress.statistics'), new Reference(Exception_Extension::PRESENTER_ID)])]])]);
        $definition->addTag(Output_Extension::FORMATTER_TAG, ['priority' => 100]);
        $container->setDefinition(Output_Extension::FORMATTER_TAG . '.progress', $definition);
    }

    /**
     * Creates output printer definition.
     *
     * @return Definition
     */
    protected function create_output_printer_definition()
    {
        return new Definition(Stream_Output_Printer::class, [new Definition(Console_Output_Factory::class)]);
    }

    /**
     * Processes all registered pretty formatter node listener wrappers.
     */
    protected function process_listener_wrappers(ContainerBuilder $container)
    {
        $this->processor->process_wrapper_services($container, self::ROOT_LISTENER_ID, self::ROOT_LISTENER_WRAPPER_TAG);
    }
}
