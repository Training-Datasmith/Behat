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
use Behat\Behat\Output\Node\Printer\Exercise_Printer;
use Behat\Behat\Output\Statistics\Phase_Statistics;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSON_Output_Printer;
use Behat\Testwork\Tester\Result\Test_Result;

final readonly class JSON_Exercise_Printer implements Exercise_Printer
{
    public function __construct(private Phase_Statistics $statistics, private JSON_Duration_Listener $duration_listener)
    {
    }

    public function print_header(Formatter $formatter): void
    {
        $this->statistics->reset();
        $output_printer = $formatter->get_output_printer();
        assert($output_printer instanceof JSON_Output_Printer);
        $output_printer->create_new_file();
    }

    public function print_footer(Formatter $formatter): void
    {
        $stats = $this->statistics->get_scenario_stat_counts();
        $total_count = 0 === count($stats) ? 0 : array_sum($stats);
        $output_printer = $formatter->get_output_printer();
        assert($output_printer instanceof JSON_Output_Printer);
        $exercise_attributes = ['tests' => $total_count, 'skipped' => $stats[Test_Result::SKIPPED], 'failed' => $stats[Test_Result::FAILED], 'pending' => $stats[Test_Result::PENDING], 'undefined' => $stats[Test_Result::UNDEFINED]];
        if ($formatter->get_parameter('timer')) {
            $exercise_attributes['time'] = (float) $this->duration_listener->get_exercise_duration();
        }
        $output_printer->extend_exercise_attributes($exercise_attributes);
        $formatter->get_output_printer()->flush();
    }
}
