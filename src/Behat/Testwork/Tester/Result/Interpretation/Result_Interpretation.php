<?php

declare(strict_types=1);
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
 * Interprets particular test result by saying if it's failure or not.
 *
 * @see Result_Interpreter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Result_Interpretation
{
    /**
     * Checks if provided test result should be considered as a failure.
     *
     * @return bool
     */
    public function is_failure(Test_Result $result);
}
