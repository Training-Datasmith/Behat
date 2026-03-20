<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Statistics;

use Behat\Behat\Tester\Result\Step_Result;
use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * A TotalStatistics decorator to get statistics per phase.
 *
 * This is useful to show the amount of failures
 * in a single suite for instance.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class Phase_Statistics implements Statistics
{
    private Total_Statistics $statistics;
    public function __construct()
    {
        $this->statistics = new Total_Statistics();
    }
    /**
     * Resets the statistics.
     */
    public function reset(): void
    {
        $this->statistics = new Total_Statistics();
    }
    /**
     * Starts timer.
     */
    public function start_timer(): void
    {
        $this->statistics->start_timer();
    }
    /**
     * Stops timer.
     */
    public function stop_timer(): void
    {
        $this->statistics->stop_timer();
    }
    /**
     * Returns timer object.
     */
    public function get_timer(): Timer
    {
        return $this->statistics->get_timer();
    }
    /**
     * Returns memory usage object.
     */
    public function get_memory(): Memory
    {
        return $this->statistics->get_memory();
    }
    /**
     * Registers scenario stat.
     */
    public function register_scenario_stat(Scenario_Stat $stat): void
    {
        $this->statistics->register_scenario_stat($stat);
    }
    /**
     * Registers step stat.
     */
    public function register_step_stat(Step_Stat $stat): void
    {
        $this->statistics->register_step_stat($stat);
    }
    /**
     * Registers hook stat.
     */
    public function register_hook_stat(Hook_Stat $stat): void
    {
        $this->statistics->register_hook_stat($stat);
    }
    /**
     * Returns counters for different scenario result codes.
     *
     * @return array<TestResult::*, int>
     */
    public function get_scenario_stat_counts(): array
    {
        return $this->statistics->get_scenario_stat_counts();
    }
    /**
     * Returns skipped scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function get_skipped_scenarios()
    {
        return $this->statistics->get_skipped_scenarios();
    }
    /**
     * Returns failed scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function get_failed_scenarios()
    {
        return $this->statistics->get_failed_scenarios();
    }
    /**
     * Returns counters for different step result codes.
     *
     * @return array<StepResult::*, int>
     */
    public function get_step_stat_counts()
    {
        return $this->statistics->get_step_stat_counts();
    }
    /**
     * Returns failed step stats.
     *
     * @return StepStat[]
     */
    public function get_failed_steps()
    {
        return $this->statistics->get_failed_steps();
    }
    /**
     * Returns pending step stats.
     *
     * @return StepStat[]
     */
    public function get_pending_steps()
    {
        return $this->statistics->get_pending_steps();
    }
    /**
     * Returns failed hook stats.
     *
     * @return HookStat[]
     */
    public function get_failed_hook_stats()
    {
        return $this->statistics->get_failed_hook_stats();
    }
}