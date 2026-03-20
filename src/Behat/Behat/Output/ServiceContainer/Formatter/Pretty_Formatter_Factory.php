<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Service_Container\Formatter;

use Behat\Behat\Definition\Service_Container\Definition_Extension;
use Behat\Behat\Event_Dispatcher\Event\Background_Tested;
use Behat\Behat\Event_Dispatcher\Event\Outline_Tested;
use Behat\Behat\Event_Dispatcher\Event\Scenario_Tested;
use Behat\Behat\Output\Node\Event_Listener\AST\Feature_Listener;
use Behat\Behat\Output\Node\Event_Listener\AST\Outline_Listener;
use Behat\Behat\Output\Node\Event_Listener\AST\Outline_Table_Listener;
use Behat\Behat\Output\Node\Event_Listener\AST\Scenario_Node_Listener;
use Behat\Behat\Output\Node\Event_Listener\AST\Step_Listener;
use Behat\Behat\Output\Node\Event_Listener\AST\Suite_Listener;
use Behat\Behat\Output\Node\Event_Listener\Flow\Fire_Only_Siblings_Listener;
use Behat\Behat\Output\Node\Event_Listener\Flow\First_Background_Fires_First_Listener;
use Behat\Behat\Output\Node\Event_Listener\Flow\Only_First_Background_Fires_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Hook_Stats_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Scenario_Stats_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Statistics_Listener;
use Behat\Behat\Output\Node\Event_Listener\Statistics\Step_Stats_Listener;
use Behat\Behat\Output\Node\Printer\Counter_Printer;
use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Behat\Output\Node\Printer\Helper\Step_Text_Painter;
use Behat\Behat\Output\Node\Printer\Helper\Width_Calculator;
use Behat\Behat\Output\Node\Printer\List_Printer;
use Behat\Behat\Output\Node\Printer\Pretty\Pretty_Example_Printer;
use Behat\Behat\Output\Node\Printer\Pretty\Pretty_Example_Row_Printer;
use Behat\Behat\Output\Node\Printer\Pretty\Pretty_Feature_Printer;
use Behat\Behat\Output\Node\Printer\Pretty\Pretty_Outline_Printer;
use Behat\Behat\Output\Node\Printer\Pretty\Pretty_Outline_Table_Printer;
use Behat\Behat\Output\Node\Printer\Pretty\Pretty_Path_Printer;
use Behat\Behat\Output\Node\Printer\Pretty\Pretty_Scenario_Printer;
use Behat\Behat\Output\Node\Printer\Pretty\Pretty_Setup_Printer;
use Behat\Behat\Output\Node\Printer\Pretty\Pretty_Skipped_Step_Printer;
use Behat\Behat\Output\Node\Printer\Pretty\Pretty_Statistics_Printer;
use Behat\Behat\Output\Node\Printer\Pretty\Pretty_Step_Printer;
use Behat\Behat\Output\Printer\Console_Output_Factory;
use Behat\Behat\Output\Statistics\Total_Statistics;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Testwork\Exception\Service_Container\Exception_Extension;
use Behat\Testwork\Output\Node\Event_Listener\Chain_Event_Listener;
use Behat\Testwork\Output\Node\Event_Listener\Flow\Fire_Only_If_Formatter_Parameter_Listener;
use Behat\Testwork\Output\Node_Event_Listening_Formatter;
use Behat\Testwork\Output\Printer\Stream_Output_Printer;
use Behat\Testwork\Output\Service_Container\Formatter\Formatter_Factory;
use Behat\Testwork\Output\Service_Container\Output_Extension;
use Behat\Testwork\Path_Options\Service_Container\Path_Options_Extension;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Translator\Service_Container\Translator_Extension;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Behat pretty formatter factory.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Pretty_Formatter_Factory implements Formatter_Factory
{
    private readonly Service_Processor $processor;
    /*
     * Available services
     */
    public const ROOT_LISTENER_ID = 'output.node.listener.pretty';
    public const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';
    /*
     * Available extension points
     */
    public const ROOT_LISTENER_WRAPPER_TAG = 'output.node.listener.pretty.wrapper';
    /**
     * Initializes extension.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }
    public function build_formatter(Container_Builder $container): void
    {
        $this->load_root_node_listener($container);
        $this->load_core_printers($container);
        $this->load_table_outline_printer($container);
        $this->load_expanded_outline_printer($container);
        $this->load_hook_printers($container);
        $this->load_statistics_printer($container);
        $this->load_printer_helpers($container);
        $this->load_formatter($container);
    }
    public function process_formatter(Container_Builder $container): void
    {
        $this->process_listener_wrappers($container);
    }
    /**
     * Loads pretty formatter node event listener.
     */
    protected function load_root_node_listener(Container_Builder $container)
    {
        $definition = new Definition(Chain_Event_Listener::class, [[new Definition(Suite_Listener::class, [new Reference('output.node.printer.pretty.suite_setup')]), new Definition(Feature_Listener::class, [new Reference('output.node.printer.pretty.feature'), new Reference('output.node.printer.pretty.feature_setup')]), $this->proxy_sibling_events(Background_Tested::BEFORE, Background_Tested::AFTER, [new Definition(Scenario_Node_Listener::class, [Background_Tested::AFTER_SETUP, Background_Tested::AFTER, new Reference('output.node.printer.pretty.scenario')]), new Definition(Step_Listener::class, [new Reference('output.node.printer.pretty.step'), new Reference('output.node.printer.pretty.step_setup')])]), $this->proxy_sibling_events(Scenario_Tested::BEFORE, Scenario_Tested::AFTER, [new Definition(Scenario_Node_Listener::class, [Scenario_Tested::AFTER_SETUP, Scenario_Tested::AFTER, new Reference('output.node.printer.pretty.scenario'), new Reference('output.node.printer.pretty.scenario_setup')]), new Definition(Step_Listener::class, [new Reference('output.node.printer.pretty.step'), new Reference('output.node.printer.pretty.step_setup')])]), $this->proxy_sibling_events(Outline_Tested::BEFORE, Outline_Tested::AFTER, [$this->proxy_events_if_parameter_is_set('expand', false, new Definition(Outline_Table_Listener::class, [new Reference('output.node.printer.pretty.outline_table'), new Reference('output.node.printer.pretty.example_row'), new Reference('output.node.printer.pretty.example_setup'), new Reference('output.node.printer.pretty.example_step_setup')])), $this->proxy_events_if_parameter_is_set('expand', true, new Definition(Outline_Listener::class, [new Reference('output.node.printer.pretty.outline'), new Reference('output.node.printer.pretty.example'), new Reference('output.node.printer.pretty.example_step'), new Reference('output.node.printer.pretty.example_setup'), new Reference('output.node.printer.pretty.example_step_setup')]))])]]);
        $container->set_definition(self::ROOT_LISTENER_ID, $definition);
    }
    /**
     * Loads formatter itself.
     */
    protected function load_formatter(Container_Builder $container)
    {
        $definition = new Definition(Total_Statistics::class);
        $container->set_definition('output.pretty.statistics', $definition);
        $definition = new Definition(Node_Event_Listening_Formatter::class, [Pretty_Formatter::NAME, 'Prints the feature as is.', Pretty_Formatter::defaults(), $this->create_output_printer_definition(), new Definition(Chain_Event_Listener::class, [[$this->rearrange_background_events(new Reference(self::ROOT_LISTENER_ID)), new Definition(Statistics_Listener::class, [new Reference('output.pretty.statistics'), new Reference('output.node.printer.pretty.statistics')]), new Definition(Scenario_Stats_Listener::class, [new Reference('output.pretty.statistics')]), new Definition(Step_Stats_Listener::class, [new Reference('output.pretty.statistics'), new Reference(Exception_Extension::PRESENTER_ID)]), new Definition(Hook_Stats_Listener::class, [new Reference('output.pretty.statistics'), new Reference(Exception_Extension::PRESENTER_ID)])]])]);
        $definition->add_tag(Output_Extension::FORMATTER_TAG, ['priority' => 100]);
        $container->set_definition(Output_Extension::FORMATTER_TAG . '.pretty', $definition);
    }
    /**
     * Loads feature, scenario and step printers.
     */
    protected function load_core_printers(Container_Builder $container)
    {
        $definition = new Definition(Pretty_Feature_Printer::class);
        $container->set_definition('output.node.printer.pretty.feature', $definition);
        $definition = new Definition(Pretty_Path_Printer::class, [new Reference('output.node.printer.pretty.width_calculator'), '%paths.base%', new Reference(Path_Options_Extension::CONFIGURABLE_PATH_PRINTER_ID)]);
        $container->set_definition('output.node.printer.pretty.path', $definition);
        $definition = new Definition(Pretty_Scenario_Printer::class, [new Reference('output.node.printer.pretty.path')]);
        $container->set_definition('output.node.printer.pretty.scenario', $definition);
        $definition = new Definition(Pretty_Step_Printer::class, [new Reference('output.node.printer.pretty.step_text_painter'), new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference('output.node.printer.pretty.path'), new Reference(Exception_Extension::PRESENTER_ID)]);
        $container->set_definition('output.node.printer.pretty.step', $definition);
        $definition = new Definition(Pretty_Skipped_Step_Printer::class, [new Reference('output.node.printer.pretty.step_text_painter'), new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference('output.node.printer.pretty.path')]);
        $container->set_definition('output.node.printer.pretty.skipped_step', $definition);
    }
    /**
     * Loads table outline printer.
     */
    protected function load_table_outline_printer(Container_Builder $container)
    {
        $definition = new Definition(Pretty_Outline_Table_Printer::class, [new Reference('output.node.printer.pretty.scenario'), new Reference('output.node.printer.pretty.skipped_step'), new Reference(self::RESULT_TO_STRING_CONVERTER_ID)]);
        $container->set_definition('output.node.printer.pretty.outline_table', $definition);
        $definition = new Definition(Pretty_Example_Row_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference(Exception_Extension::PRESENTER_ID), new Reference(Translator_Extension::TRANSLATOR_ID)]);
        $container->set_definition('output.node.printer.pretty.example_row', $definition);
    }
    /**
     * Loads expanded outline printer.
     */
    protected function load_expanded_outline_printer(Container_Builder $container)
    {
        $definition = new Definition(Pretty_Outline_Printer::class, [new Reference('output.node.printer.pretty.scenario'), new Reference('output.node.printer.pretty.skipped_step'), new Reference(self::RESULT_TO_STRING_CONVERTER_ID)]);
        $container->set_definition('output.node.printer.pretty.outline', $definition);
        $definition = new Definition(Pretty_Example_Printer::class, [new Reference('output.node.printer.pretty.path')]);
        $container->set_definition('output.node.printer.pretty.example', $definition);
        $definition = new Definition(Pretty_Step_Printer::class, [new Reference('output.node.printer.pretty.step_text_painter'), new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference('output.node.printer.pretty.path'), new Reference(Exception_Extension::PRESENTER_ID), 8]);
        $container->set_definition('output.node.printer.pretty.example_step', $definition);
    }
    /**
     * Loads hook printers.
     */
    protected function load_hook_printers(Container_Builder $container)
    {
        $definition = new Definition(Pretty_Setup_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference(Exception_Extension::PRESENTER_ID), 0, true, true]);
        $container->set_definition('output.node.printer.pretty.suite_setup', $definition);
        $definition = new Definition(Pretty_Setup_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference(Exception_Extension::PRESENTER_ID), 0, false, true]);
        $container->set_definition('output.node.printer.pretty.feature_setup', $definition);
        $definition = new Definition(Pretty_Setup_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference(Exception_Extension::PRESENTER_ID), 2]);
        $container->set_definition('output.node.printer.pretty.scenario_setup', $definition);
        $definition = new Definition(Pretty_Setup_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference(Exception_Extension::PRESENTER_ID), 4]);
        $container->set_definition('output.node.printer.pretty.step_setup', $definition);
        $definition = new Definition(Pretty_Setup_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference(Exception_Extension::PRESENTER_ID), 8]);
        $container->set_definition('output.node.printer.pretty.example_step_setup', $definition);
        $definition = new Definition(Pretty_Setup_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference(Exception_Extension::PRESENTER_ID), 6]);
        $container->set_definition('output.node.printer.pretty.example_setup', $definition);
    }
    /**
     * Loads statistics printer.
     */
    protected function load_statistics_printer(Container_Builder $container)
    {
        $definition = new Definition(Counter_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference(Translator_Extension::TRANSLATOR_ID)]);
        $container->set_definition('output.node.printer.counter', $definition);
        $definition = new Definition(List_Printer::class, [new Reference(self::RESULT_TO_STRING_CONVERTER_ID), new Reference(Exception_Extension::PRESENTER_ID), new Reference(Translator_Extension::TRANSLATOR_ID), '%paths.base%', new Reference(Path_Options_Extension::CONFIGURABLE_PATH_PRINTER_ID)]);
        $container->set_definition('output.node.printer.list', $definition);
        $definition = new Definition(Pretty_Statistics_Printer::class, [new Reference('output.node.printer.counter'), new Reference('output.node.printer.list')]);
        $container->set_definition('output.node.printer.pretty.statistics', $definition);
    }
    /**
     * Loads printer helpers.
     */
    protected function load_printer_helpers(Container_Builder $container)
    {
        $definition = new Definition(Width_Calculator::class);
        $container->set_definition('output.node.printer.pretty.width_calculator', $definition);
        $definition = new Definition(Step_Text_Painter::class, [new Reference(Definition_Extension::PATTERN_TRANSFORMER_ID), new Reference(self::RESULT_TO_STRING_CONVERTER_ID)]);
        $container->set_definition('output.node.printer.pretty.step_text_painter', $definition);
        $definition = new Definition(Result_To_String_Converter::class);
        $container->set_definition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);
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
     * Creates root listener definition.
     *
     * @return Definition
     */
    protected function rearrange_background_events($listener)
    {
        return new Definition(First_Background_Fires_First_Listener::class, [new Definition(Only_First_Background_Fires_Listener::class, [$listener])]);
    }
    /**
     * Creates contextual proxy listener.
     *
     * @param string       $beforeEventName
     * @param string       $afterEventName
     * @param Definition[] $listeners
     *
     * @return Definition
     */
    protected function proxy_sibling_events($before_event_name, $after_event_name, array $listeners)
    {
        return new Definition(Fire_Only_Siblings_Listener::class, [$before_event_name, $after_event_name, new Definition(Chain_Event_Listener::class, [$listeners])]);
    }
    /**
     * Creates contextual proxy listener.
     *
     * @param string $name
     *
     * @return Definition
     */
    protected function proxy_events_if_parameter_is_set($name, $value, Definition $listener)
    {
        return new Definition(Fire_Only_If_Formatter_Parameter_Listener::class, [$name, $value, $listener]);
    }
    /**
     * Processes all registered pretty formatter node listener wrappers.
     */
    protected function process_listener_wrappers(Container_Builder $container)
    {
        $this->processor->process_wrapper_services($container, self::ROOT_LISTENER_ID, self::ROOT_LISTENER_WRAPPER_TAG);
    }
}