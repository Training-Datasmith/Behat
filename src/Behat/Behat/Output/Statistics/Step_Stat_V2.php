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
use Stringable;
/**
 * Second iteration of Behat step stat, with a scenario information.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Step_Stat_V2 extends Step_Stat implements Stringable
{
    /**
     * @param StepResult::* $resultCode
     */
    public function __construct(private readonly string $scenario_title, private readonly string $scenario_path, private readonly string $step_text, private readonly string $step_path, private readonly int $result_code, private readonly ?string $error = null, private readonly ?string $std_out = null)
    {
        parent::__construct($step_text, $step_path, $result_code, $error, $std_out);
    }
    /**
     * Returns associated scenario text.
     */
    public function get_scenario_text(): string
    {
        return $this->scenario_title;
    }
    /**
     * Returns associated scenario path.
     */
    public function get_scenario_path(): string
    {
        return $this->scenario_path;
    }
    /**
     * Returns step text.
     */
    public function get_step_text(): string
    {
        return $this->step_text;
    }
    /**
     * Returns step path.
     */
    public function get_step_path(): string
    {
        return $this->step_path;
    }
    /**
     * Returns step result code.
     *
     * @return StepResult::*
     */
    public function get_result_code(): int
    {
        return $this->result_code;
    }
    /**
     * Returns step error (if has one).
     */
    public function get_error(): ?string
    {
        return $this->error;
    }
    /**
     * Returns step output (if has one).
     */
    public function get_std_out(): ?string
    {
        return $this->std_out;
    }
    /**
     * Returns string representation for a stat.
     */
    public function __toString(): string
    {
        return $this->get_path();
    }
}