<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JSON;

use Behat\Behat\Output\Node\Event_Listener\JSON\JSON_Duration_Listener;
use Behat\Behat\Output\Node\Printer\Helper\Result_To_String_Converter;
use Behat\Behat\Output\Node\Printer\Scenario_Printer;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\NamedScenarioInterface;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSON_Output_Printer;
use Behat\Testwork\Path_Options\Printer\Configurable_Path_Printer;
use Behat\Testwork\Tester\Result\Test_Result;

final class JSON_Scenario_Printer implements Scenario_Printer
{
    private ScenarioLikeInterface $current_scenario;
    private FeatureNode $current_feature;

    public function __construct(private readonly Result_To_String_Converter $result_converter, private readonly JSON_Duration_Listener $duration_listener, private readonly Configurable_Path_Printer $configurable_path_printer)
    {
    }

    public function print_header(Formatter $formatter, FeatureNode $feature, ScenarioLikeInterface $scenario): void
    {
        $this->current_scenario = $scenario;
        $this->current_feature = $feature;
        $output_printer = $formatter->get_output_printer();
        assert($output_printer instanceof JSON_Output_Printer);
        $output_printer->add_scenario();
    }

    public function print_footer(Formatter $formatter, Test_Result $result): void
    {
        $scenario = $this->current_scenario;
        assert($scenario instanceof NamedScenarioInterface);
        $name = implode(' ', array_map(trim(...), explode("\n", $scenario->getTitle() ?? '')));
        $scenario_attributes = ['name' => $name];
        if ($formatter->get_parameter('timer')) {
            $scenario_attributes['time'] = (float) $this->duration_listener->get_duration($scenario);
        }
        $scenario_attributes['status'] = $this->result_converter->convert_result_to_string($result);
        $file = $this->current_feature->getFile();
        if ($file) {
            $scenario_attributes['file'] = $this->configurable_path_printer->process_paths_in_text($file, apply_editor_url: false);
        }
        $output_printer = $formatter->get_output_printer();
        assert($output_printer instanceof JSON_Output_Printer);
        $output_printer->add_current_scenario_attributes($scenario_attributes, true);
    }
}
