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
use Behat\Testwork\Tester\Result\Test_Result;
use Behat\Testwork\Tester\Result\Test_Results;

/**
 * Collects and provided exercise statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Total_Statistics implements Statistics
{
    private readonly Timer $timer;
    private readonly Memory $memory;
    /**
     * @var array<Test_Result::*, int>
     */
    private array $scenario_counters = [];
    /**
     * @var array<Step_Result::*, int>
     */
    private array $step_counters = [];
    /**
     * @var Scenario_Stat[]
     */
    private array $failed_scenario_stats = [];
    /**
     * @var Scenario_Stat[]
     */
    private array $skipped_scenario_stats = [];
    /**
     * @var Step_Stat[]
     */
    private array $failed_step_stats = [];
    /**
     * @var Step_Stat[]
     */
    private array $pending_step_stats = [];
    /**
     * @var Hook_Stat[]
     */
    private array $failed_hook_stats = [];

    /**
     * Initializes statistics.
     */
    public function __construct()
    {
        $this->reset_all_counters();
        $this->timer = new Timer();
        $this->memory = new Memory();
    }

    public function reset_all_counters(): void
    {
        $this->scenario_counters = $this->step_counters = [Test_Result::PASSED => 0, Test_Result::FAILED => 0, Test_Result::UNDEFINED => 0, Test_Result::PENDING => 0, Test_Result::SKIPPED => 0];
    }

    /**
     * Starts timer.
     */
    public function start_timer(): void
    {
        $this->timer->start();
    }

    /**
     * Stops timer.
     */
    public function stop_timer(): void
    {
        $this->timer->stop();
    }

    /**
     * Returns timer object.
     */
    public function get_timer(): Timer
    {
        return $this->timer;
    }

    /**
     * Returns memory usage object.
     */
    public function get_memory(): Memory
    {
        return $this->memory;
    }

    /**
     * Registers scenario stat.
     */
    public function register_scenario_stat(Scenario_Stat $stat): void
    {
        if (Test_Results::NO_TESTS === $stat->get_result_code()) {
            return;
        }
        ++$this->scenario_counters[$stat->get_result_code()];
        if (Test_Result::FAILED === $stat->get_result_code()) {
            $this->failed_scenario_stats[] = $stat;
        }
        if (Test_Result::SKIPPED === $stat->get_result_code()) {
            $this->skipped_scenario_stats[] = $stat;
        }
    }

    /**
     * Registers step stat.
     */
    public function register_step_stat(Step_Stat $stat): void
    {
        ++$this->step_counters[$stat->get_result_code()];
        if (Test_Result::FAILED === $stat->get_result_code()) {
            $this->failed_step_stats[] = $stat;
        }
        if (Test_Result::PENDING === $stat->get_result_code()) {
            $this->pending_step_stats[] = $stat;
        }
    }

    /**
     * Registers hook stat.
     */
    public function register_hook_stat(Hook_Stat $stat): void
    {
        if ($stat->is_successful()) {
            return;
        }
        $this->failed_hook_stats[] = $stat;
    }

    /**
     * Returns counters for different scenario result codes.
     *
     * @return array<Test_Result::*, int>
     */
    public function get_scenario_stat_counts(): array
    {
        return $this->scenario_counters;
    }

    /**
     * Returns skipped scenario stats.
     *
     * @return Scenario_Stat[]
     */
    public function get_skipped_scenarios()
    {
        return $this->skipped_scenario_stats;
    }

    /**
     * Returns failed scenario stats.
     *
     * @return Scenario_Stat[]
     */
    public function get_failed_scenarios()
    {
        return $this->failed_scenario_stats;
    }

    /**
     * Returns counters for different step result codes.
     *
     * @return array<Step_Result::*, int>
     */
    public function get_step_stat_counts()
    {
        return $this->step_counters;
    }

    /**
     * Returns failed step stats.
     *
     * @return Step_Stat[]
     */
    public function get_failed_steps()
    {
        return $this->failed_step_stats;
    }

    /**
     * Returns pending step stats.
     *
     * @return Step_Stat[]
     */
    public function get_pending_steps()
    {
        return $this->pending_step_stats;
    }

    /**
     * Returns failed hook stats.
     *
     * @return Hook_Stat[]
     */
    public function get_failed_hook_stats()
    {
        return $this->failed_hook_stats;
    }
}
