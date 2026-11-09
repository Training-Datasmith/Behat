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
use Behat\Behat\Output\Node\Printer\Feature_Printer;
use Behat\Behat\Output\Statistics\Phase_Statistics;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSON_Output_Printer;
use Behat\Testwork\Path_Options\Printer\Configurable_Path_Printer;
use Behat\Testwork\Tester\Result\Test_Result;

final class JSON_Feature_Printer implements Feature_Printer
{
    private FeatureNode $current_feature;

    public function __construct(private readonly Phase_Statistics $statistics, private readonly JSON_Duration_Listener $duration_listener, private readonly Configurable_Path_Printer $configurable_path_printer)
    {
    }

    public function print_header(Formatter $formatter, FeatureNode $feature): void
    {
        $this->statistics->reset();
        $this->current_feature = $feature;
        $output_printer = $formatter->get_output_printer();
        assert($output_printer instanceof JSON_Output_Printer);
        $output_printer->add_feature();
    }

    public function print_footer(Formatter $formatter, Test_Result $result): void
    {
        $stats = $this->statistics->get_scenario_stat_counts();
        $total_count = 0 === count($stats) ? 0 : array_sum($stats);
        $output_printer = $formatter->get_output_printer();
        assert($output_printer instanceof JSON_Output_Printer);
        $feature_attributes = ['name' => $this->current_feature->getTitle() ?? ''];
        $file = $this->current_feature->getFile();
        if ($file) {
            $feature_attributes['file'] = $this->configurable_path_printer->process_paths_in_text($file, apply_editor_url: false);
        }
        $feature_attributes += ['tests' => $total_count, 'skipped' => $stats[Test_Result::SKIPPED], 'failed' => $stats[Test_Result::FAILED], 'pending' => $stats[Test_Result::PENDING], 'undefined' => $stats[Test_Result::UNDEFINED]];
        if ($formatter->get_parameter('timer')) {
            $feature_attributes['time'] = (float) $this->duration_listener->get_feature_duration($this->current_feature);
        }
        $output_printer->extend_feature_attributes($feature_attributes);
    }
}
