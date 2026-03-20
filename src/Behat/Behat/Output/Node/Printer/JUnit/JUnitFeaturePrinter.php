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
use Behat\Behat\Output\Node\Printer\Feature_Printer;
use Behat\Behat\Output\Statistics\Phase_Statistics;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\J_Unit_Output_Printer;
use Behat\Testwork\Path_Options\Printer\Configurable_Path_Printer;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Prints the <testsuite> element.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class J_Unit_Feature_Printer implements Feature_Printer
{
    private ?Feature_Node $current_feature = null;
    public function __construct(private readonly Phase_Statistics $statistics, private readonly ?J_Unit_Duration_Listener $duration_listener = null, private readonly ?Configurable_Path_Printer $configurable_path_printer = null)
    {
    }
    public function print_header(Formatter $formatter, Feature_Node $feature): void
    {
        $this->statistics->reset();
        $this->current_feature = $feature;
        /** @var JUnitOutputPrinter $outputPrinter */
        $output_printer = $formatter->get_output_printer();
        $output_printer->add_testsuite();
    }
    public function print_footer(Formatter $formatter, Test_Result $result): void
    {
        $stats = $this->statistics->get_scenario_stat_counts();
        $total_count = 0 === count($stats) ? 0 : array_sum($stats);
        /** @var JUnitOutputPrinter $outputPrinter */
        $output_printer = $formatter->get_output_printer();
        $feature_attributes = ['name' => $this->current_feature->get_title()];
        $file = $this->current_feature->get_file();
        if ($file && $this->configurable_path_printer instanceof Configurable_Path_Printer) {
            $feature_attributes['file'] = $this->configurable_path_printer->process_paths_in_text($file, applyEditorUrl: false);
        }
        $feature_attributes += ['tests' => $total_count, 'skipped' => $stats[Test_Result::SKIPPED], 'failures' => $stats[Test_Result::FAILED], 'errors' => $stats[Test_Result::PENDING] + $stats[Test_Result::UNDEFINED]];
        if ($formatter->get_parameter('timer') && $this->duration_listener instanceof J_Unit_Duration_Listener) {
            $feature_attributes['time'] = $this->duration_listener->get_feature_duration($this->current_feature);
        }
        $output_printer->extend_testsuite_attributes($feature_attributes);
        $this->statistics->reset();
        $this->current_feature = null;
    }
}