<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Deprecation\Result\Interpretation;

use Behat\Testwork\Deprecation\Deprecation_Collector;
use Behat\Testwork\Tester\Result\Interpretation\Result_Interpretation;
use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Interprets test results as failures if any Behat deprecations were triggered.
 */
final readonly class Deprecation_Interpretation implements Result_Interpretation
{
    public function __construct(private Deprecation_Collector $collector)
    {
    }
    public function is_failure(Test_Result $result): bool
    {
        return $this->collector->has_deprecations();
    }
}