<?php

declare(strict_types=1);
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
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\NamedScenarioInterface;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Output\Formatter;
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
        private ?Configurable_Path_Printer $configurable_path_printer = null,
    ) {
    }

    public function print_open_tag(Formatter $formatter, FeatureNode $feature, ScenarioLikeInterface $scenario, Test_Result $result, ?string $file = null): void
    {
        assert($scenario instanceof NamedScenarioInterface);
        $name = implode(' ', array_map(trim(...), explode("\n", $scenario->getName() ?? '')));
        /** @var J_Unit_Output_Printer $outputPrinter */
        $output_printer = $formatter->get_output_printer();
        $test_case_attributes = ['name' => $name, 'classname' => $feature->getTitle(), 'status' => $this->result_converter->convert_result_to_string($result)];
        if ($formatter->get_parameter('timer') && $this->duration_listener instanceof J_Unit_Duration_Listener) {
            $test_case_attributes['time'] = $this->duration_listener->get_duration($scenario);
        }
        if ($file && $this->configurable_path_printer instanceof Configurable_Path_Printer) {
            $test_case_attributes['file'] = $this->configurable_path_printer->process_paths_in_text($file, apply_editor_url: false);
        }
        $output_printer->add_testcase($test_case_attributes);
    }
}
