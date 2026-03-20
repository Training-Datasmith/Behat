<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Printer\JSON;

use Behat\Behat\Output\Node\Event_Listener\JSON\Json_Duration_Listener;
use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Behat\Output\Node\Printer\Scenario_Printer;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Named_Scenario_Interface;
use Behat\Gherkin\Node\Scenario_Like_Interface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Json_Output_Printer;
use Behat\Testwork\Path_Options\Printer\Configurable_Path_Printer;
use Behat\Testwork\Tester\Result\Test_Result;
final class Json_Scenario_Printer implements Scenario_Printer
{
    private Scenario_Like_Interface $current_scenario;
    private Feature_Node $current_feature;
    public function __construct(private readonly Result_To_String_Converter $result_converter, private readonly Json_Duration_Listener $duration_listener, private readonly Configurable_Path_Printer $configurable_path_printer)
    {
    }
    public function print_header(Formatter $formatter, Feature_Node $feature, Scenario_Like_Interface $scenario): void
    {
        $this->current_scenario = $scenario;
        $this->current_feature = $feature;
        $output_printer = $formatter->get_output_printer();
        assert($output_printer instanceof Json_Output_Printer);
        $output_printer->add_scenario();
    }
    public function print_footer(Formatter $formatter, Test_Result $result): void
    {
        $scenario = $this->current_scenario;
        assert($scenario instanceof Named_Scenario_Interface);
        $name = implode(' ', array_map(trim(...), explode("\n", $scenario->get_name() ?? '')));
        $scenario_attributes = ['name' => $name];
        if ($formatter->get_parameter('timer')) {
            $scenario_attributes['time'] = (float) $this->duration_listener->get_duration($scenario);
        }
        $scenario_attributes['status'] = $this->result_converter->convert_result_to_string($result);
        $file = $this->current_feature->get_file();
        if ($file) {
            $scenario_attributes['file'] = $this->configurable_path_printer->process_paths_in_text($file, applyEditorUrl: false);
        }
        $output_printer = $formatter->get_output_printer();
        assert($output_printer instanceof Json_Output_Printer);
        $output_printer->add_current_scenario_attributes($scenario_attributes, true);
    }
}