<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Tester\Result\Interpretation;

use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Interprets test results strictly - everything that is not an explicit pass is a failure.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Strict_Interpretation implements Result_Interpretation
{
    public function is_failure(Test_Result $result): bool
    {
        return !$result->is_passed();
    }
}