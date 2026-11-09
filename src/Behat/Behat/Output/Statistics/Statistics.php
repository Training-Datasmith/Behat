<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Statistics;

use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;

/**
 * Collects and provided exercise statistics.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
interface Statistics
{
    /**
     * Starts timer.
     */
    public function start_timer();

    /**
     * Stops timer.
     */
    public function stop_timer();

    /**
     * Returns timer object.
     *
     * @return Timer
     */
    public function get_timer();

    /**
     * Returns memory usage object.
     *
     * @return Memory
     */
    public function get_memory();

    /**
     * Registers scenario stat.
     */
    public function register_scenario_stat(Scenario_Stat $stat);

    /**
     * Registers step stat.
     */
    public function register_step_stat(Step_Stat $stat);

    /**
     * Registers hook stat.
     */
    public function register_hook_stat(Hook_Stat $stat);

    /**
     * Returns counters for different scenario result codes.
     *
     * @return array<Test_Result::*, int>
     */
    public function get_scenario_stat_counts();

    /**
     * Returns skipped scenario stats.
     *
     * @return Scenario_Stat[]
     */
    public function get_skipped_scenarios();

    /**
     * Returns failed scenario stats.
     *
     * @return Scenario_Stat[]
     */
    public function get_failed_scenarios();

    /**
     * Returns counters for different step result codes.
     *
     * @return array<Step_Result::*, int>
     */
    public function get_step_stat_counts();

    /**
     * Returns failed step stats.
     *
     * @return Step_Stat[]
     */
    public function get_failed_steps();

    /**
     * Returns pending step stats.
     *
     * @return Step_Stat[]
     */
    public function get_pending_steps();

    /**
     * Returns failed hook stats.
     *
     * @return Hook_Stat[]
     */
    public function get_failed_hook_stats();
}
