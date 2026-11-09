<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result;

use Behat\Testwork\Tester\Result\Interpretation\Result_Interpretation;

/**
 * Interprets provided test result (as 1 or 0) using registered interpretations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Result_Interpreter
{
    public const PASS = 0;
    public const FAIL = 1;
    /**
     * @var Result_Interpretation[]
     */
    private array $interpretations = [];

    /**
     * Registers result interpretation.
     */
    public function register_result_interpretation(Result_Interpretation $interpretation): void
    {
        $this->interpretations[] = $interpretation;
    }

    /**
     * Interprets result as a UNIX return code (0 for success, 1 for failure).
     */
    public function interpret_result(Test_Result $result): int
    {
        foreach ($this->interpretations as $interpretation) {
            if ($interpretation->is_failure($result)) {
                return self::FAIL;
            }
        }

        return self::PASS;
    }
}
