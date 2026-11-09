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
use Behat\Behat\Output\Node\Printer\Suite_Printer;
use Behat\Behat\Output\Statistics\Phase_Statistics;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSON_Output_Printer;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\Test_Result;

final class JSON_Suite_Printer implements Suite_Printer
{
    private Suite $current_suite;

    public function __construct(private readonly Phase_Statistics $statistics, private readonly JSON_Duration_Listener $duration_listener)
    {
    }

    public function print_header(Formatter $formatter, Suite $suite): void
    {
        $this->statistics->reset();
        $this->current_suite = $suite;
        $output_printer = $formatter->get_output_printer();
        assert($output_printer instanceof JSON_Output_Printer);
        $output_printer->add_suite();
    }

    public function print_footer(Formatter $formatter, Suite $suite): void
    {
        $stats = $this->statistics->get_scenario_stat_counts();
        $total_count = 0 === count($stats) ? 0 : array_sum($stats);
        $output_printer = $formatter->get_output_printer();
        assert($output_printer instanceof JSON_Output_Printer);
        $suite_attributes = ['name' => $this->current_suite->get_name(), 'tests' => $total_count, 'skipped' => $stats[Test_Result::SKIPPED], 'failed' => $stats[Test_Result::FAILED], 'pending' => $stats[Test_Result::PENDING], 'undefined' => $stats[Test_Result::UNDEFINED]];
        if ($formatter->get_parameter('timer')) {
            $suite_attributes['time'] = (float) $this->duration_listener->get_suite_duration($this->current_suite);
        }
        $output_printer->extend_suite_attributes($suite_attributes);
    }
}
