<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Tester\Result;

/**
 * Represents an undefined step result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Undefined_Step_Result implements Step_Result
{
    public function is_passed(): bool
    {
        return false;
    }
    /**
     * @return self::UNDEFINED
     */
    public function get_result_code(): int
    {
        return self::UNDEFINED;
    }
}