<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Printer\J_Unit;

use Behat\Behat\Output\Node\Event_Listener\J_Unit\J_Unit_Duration_Listener;
use Behat\Behat\Output\Node\Event_Listener\J_Unit\J_Unit_Outline_Store_Listener;
use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Named_Scenario_Interface;
use Behat\Gherkin\Node\Scenario_Like_Interface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\J_Unit_Output_Printer;
use Behat\Testwork\Path_Options\Printer\Configurable_Path_Printer;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Prints the <testcase> element.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final readonly class J_Unit_Scenario_Printer
{
    public function __construct(
        private Result_To_String_Converter $result_converter,
        /*
         * @deprecated the outlineStoreListener is no longer used but kept for BC. It will be removed in the next major
         */
        J_Unit_Outline_Store_Listener $outline_store_listener,
        private ?J_Unit_Duration_Listener $duration_listener = null,
        private ?Configurable_Path_Printer $configurable_path_printer = null
    )
    {
    }
    public function print_open_tag(Formatter $formatter, Feature_Node $feature, Scenario_Like_Interface $scenario, Test_Result $result, ?string $file = null): void
    {
        assert($scenario instanceof Named_Scenario_Interface);
        $name = implode(' ', array_map(trim(...), explode("\n", $scenario->get_name() ?? '')));
        /** @var JUnitOutputPrinter $outputPrinter */
        $output_printer = $formatter->get_output_printer();
        $test_case_attributes = ['name' => $name, 'classname' => $feature->get_title(), 'status' => $this->result_converter->convert_result_to_string($result)];
        if ($formatter->get_parameter('timer') && $this->duration_listener instanceof J_Unit_Duration_Listener) {
            $test_case_attributes['time'] = $this->duration_listener->get_duration($scenario);
        }
        if ($file && $this->configurable_path_printer instanceof Configurable_Path_Printer) {
            $test_case_attributes['file'] = $this->configurable_path_printer->process_paths_in_text($file, applyEditorUrl: false);
        }
        $output_printer->add_testcase($test_case_attributes);
    }
}