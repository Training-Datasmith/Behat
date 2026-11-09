<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Printer\Helper;

use Behat\Testwork\Tester\Result\Test_Result;
/**
 * Converts result objects into a string representation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Result_To_String_Converter
{
    /**
     * Converts provided test result to a string.
     */
    public function convert_result_to_string(Test_Result $result): string
    {
        return $this->convert_result_code_to_string($result->get_result_code());
    }
    /**
     * Converts provided result code to a string.
     *
     * @param int $resultCode
     */
    public function convert_result_code_to_string($result_code): string
    {
        return match ($result_code) {
            Test_Result::SKIPPED => 'skipped',
            Test_Result::PENDING => 'pending',
            Test_Result::FAILED => 'failed',
            Test_Result::UNDEFINED => 'undefined',
            default => 'passed',
        };
    }
}