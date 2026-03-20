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
 * Behat step stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated in favour of StepStatV2 and to be removed in 4.0
 */
class Step_Stat implements Stringable
{
    /**
     * @param StepResult::* $resultCode
     */
    public function __construct(private readonly string $text, private readonly string $path, private readonly int $result_code, private readonly ?string $error = null, private readonly ?string $std_out = null)
    {
    }
    /**
     * Returns step text.
     */
    public function get_text(): string
    {
        return $this->text;
    }
    /**
     * Returns step path.
     */
    public function get_path(): string
    {
        return $this->path;
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