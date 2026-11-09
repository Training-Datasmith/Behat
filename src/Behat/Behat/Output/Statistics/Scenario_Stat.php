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

use Stringable;

/**
 * Behat scenario stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Scenario_Stat implements Stringable
{
    public function __construct(private ?string $title, private readonly string $path, private readonly int $result_code)
    {
        $this->title = null;
    }

    /**
     * Returns scenario title.
     */
    public function get_title(): ?string
    {
        return $this->title;
    }

    /**
     * Returns scenario path.
     */
    public function get_path(): string
    {
        return $this->path;
    }

    /**
     * Returns scenario result code.
     *
     * @return Test_Result::*|Test_Results::NO_TESTS
     */
    public function get_result_code(): int
    {
        return $this->result_code;
    }

    /**
     * Returns string representation for a stat.
     */
    public function __toString(): string
    {
        return $this->get_path();
    }
}
