<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Progress;

use Behat\Behat\Output\Node\Printer\Counter_Printer;
use Behat\Behat\Output\Node\Printer\List_Printer;
use Behat\Behat\Output\Node\Printer\Statistics_Printer;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Config\Formatter\Show_Output_Option;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\Test_Result;

/**
 * Behat progress statistics printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Progress_Statistics_Printer implements Statistics_Printer
{
    /**
     * Initializes printer.
     */
    public function __construct(private Counter_Printer $counter_printer, private List_Printer $list_printer)
    {
    }

    public function print_statistics(Formatter $formatter, Statistics $statistics): void
    {
        $printer = $formatter->get_output_printer();
        $printer->writeln();
        $printer->writeln();
        $hook_stats = $statistics->get_failed_hook_stats();
        $this->list_printer->print_failed_hooks_list($printer, 'failed_hooks_title', $hook_stats);
        $short_summary = $formatter->get_parameter('short_summary');
        if ($short_summary) {
            $scenario_stats = $statistics->get_skipped_scenarios();
            $this->list_printer->print_scenarios_list($printer, 'skipped_scenarios_title', Test_Result::SKIPPED, $scenario_stats);
            $scenario_stats = $statistics->get_failed_scenarios();
            $failed_step_stats = $statistics->get_failed_steps();
            $this->list_printer->print_scenarios_list($printer, 'failed_scenarios_title', Test_Result::FAILED, $scenario_stats, $failed_step_stats);
        } else {
            $show_output = $formatter->get_parameter(Show_Output_Option::OPTION_NAME);
            $step_stats = $statistics->get_failed_steps();
            $this->list_printer->print_step_list($printer, 'failed_steps_title', Test_Result::FAILED, $step_stats, $show_output);
            $step_stats = $statistics->get_pending_steps();
            $this->list_printer->print_step_list($printer, 'pending_steps_title', Test_Result::PENDING, $step_stats, $show_output);
        }
        $this->counter_printer->print_counters($printer, 'scenarios_count', $statistics->get_scenario_stat_counts());
        $this->counter_printer->print_counters($printer, 'steps_count', $statistics->get_step_stat_counts());
        if ($formatter->get_parameter('timer')) {
            $timer = $statistics->get_timer();
            $memory = $statistics->get_memory();
            $formatter->get_output_printer()->writeln(sprintf('%s (%s)', $timer, $memory));
        }
    }
}
