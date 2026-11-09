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

use Behat\Behat\Tester\Result\StepResult;
use Stringable;

/**
 * Behat step stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated in favour of StepStatV2 and to be removed in 4.0
 */
class StepStat implements Stringable
{
    /**
     * @param StepResult::* $resultCode
     */
    public function __construct(
        private readonly string $text,
        private readonly string $path,
        private readonly int $resultCode,
        private readonly ?string $error = null,
        private readonly ?string $stdOut = null,
    ) {
    }

    /**
     * Returns step text.
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Returns step path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns step result code.
     *
     * @return StepResult::*
     */
    public function getResultCode(): int
    {
        return $this->resultCode;
    }

    /**
     * Returns step error (if has one).
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Returns step output (if has one).
     */
    public function getStdOut(): ?string
    {
        return $this->stdOut;
    }

    /**
     * Returns string representation for a stat.
     */
    public function __toString(): string
    {
        return $this->getPath();
    }
}
